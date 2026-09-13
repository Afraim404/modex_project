<?php
require_once 'includes/config.php';
require_once 'includes/layout_top.php';
require_once 'includes/layout_bottom.php';

$db   = getDB();
$cart = getCart();

// Redirect to cart if empty
if (empty($cart)) {
    header('Location: /cart.php');
    exit;
}

// Single source of truth for districts
$insideDhakaDistricts = ['Dhaka','Gazipur','Narayanganj','Manikganj','Munshiganj','Narsingdi','Rajbari'];
$allDistricts = ['Bagerhat','Bandarban','Barguna','Barishal','Bhola','Bogura','Brahmanbaria','Chandpur','Chattogram','Chuadanga',"Cox's Bazar",'Cumilla','Dhaka','Dinajpur','Faridpur','Feni','Gaibandha','Gazipur','Gopalganj','Habiganj','Jamalpur','Jashore','Jhalokati','Jhenaidah','Joypurhat','Khagrachhari','Khulna','Kishoreganj','Kurigram','Kushtia','Lakshmipur','Lalmonirhat','Madaripur','Magura','Manikganj','Meherpur','Moulvibazar','Munshiganj','Mymensingh','Naogaon','Narail','Narayanganj','Narsingdi','Natore','Netrokona','Nilphamari','Noakhali','Pabna','Panchagarh','Patuakhali','Pirojpur','Rajbari','Rajshahi','Rangamati','Rangpur','Satkhira','Shariatpur','Sherpur','Sirajganj','Sunamganj','Sylhet','Tangail','Thakurgaon'];

// Calculate subtotal from session cart
$subtotal = 0;
foreach ($cart as $item) {
    $subtotal += $item['price'] * $item['qty'];
}

$errors = [];

// ── Handle POST ────────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Read raw POST (sanitize only before DB/display, not before validation)
    $name        = trim($_POST['customer_name']   ?? '');
    $phone       = trim($_POST['customer_phone']  ?? '');
    $email       = trim($_POST['customer_email']  ?? '');
    $address     = trim($_POST['delivery_address'] ?? '');
    $district    = trim($_POST['district']        ?? '');
    $division    = trim($_POST['division']        ?? '');
    $insideDhaka = isset($_POST['is_inside_dhaka']) ? (int)$_POST['is_inside_dhaka'] : 0;
    $note        = trim($_POST['special_note']    ?? '');

    // Validate
    if (strlen($name) < 2)
        $errors[] = 'Full name is required (at least 2 characters).';
    if (!preg_match('/^(\+?880|0)1[3-9]\d{8}$/', $phone))
        $errors[] = 'Enter a valid Bangladeshi phone number (e.g. 01XXXXXXXXX or +8801XXXXXXXXX).';
    if (strlen($address) < 10)
        $errors[] = 'Full delivery address is required (at least 10 characters).';
    if (!$district || !in_array($district, $allDistricts))
        $errors[] = 'Please select a valid district.';

    // Auto-correct zone if district was selected but zone radio was somehow wrong
    if ($district && in_array($district, $allDistricts)) {
        $insideDhaka = in_array($district, $insideDhakaDistricts) ? 1 : 0;
    }

    if (empty($errors)) {
        $deliveryCharge = $insideDhaka ? DELIVERY_INSIDE_DHAKA : DELIVERY_OUTSIDE_DHAKA;
        $orderNumber    = generateOrderNumber();

        $db->beginTransaction();
        try {
            $stmt = $db->prepare("INSERT INTO orders (order_number,customer_name,customer_phone,customer_email,delivery_address,district,division,is_inside_dhaka,delivery_charge,special_note) VALUES (?,?,?,?,?,?,?,?,?,?)");
            $stmt->execute([
                $orderNumber,
                sanitize($name),
                sanitize($phone),
                sanitize($email),
                sanitize($address),
                sanitize($district),
                sanitize($division),
                $insideDhaka,
                $deliveryCharge,
                sanitize($note),
            ]);
            $orderId = $db->lastInsertId();

            foreach ($cart as $pid => $item) {
                $istmt = $db->prepare("INSERT INTO order_items (order_id,product_id,product_name,quantity,price) VALUES (?,?,?,?,?)");
                $istmt->execute([$orderId, $pid, $item['name'], $item['qty'], $item['price']]);
            }

            $db->commit();
            $_SESSION['cart'] = [];
            $_SESSION['delivery_zone'] = $insideDhaka;
            header('Location: /order_success.php?order=' . urlencode($orderNumber));
            exit;
        } catch (Exception $e) {
            $db->rollBack();
            $errors[] = 'Something went wrong. Please try again.';
        }
    }

    // On failed POST: keep submitted values for repopulation
    $formDistrict    = htmlspecialchars($district,  ENT_QUOTES, 'UTF-8');
    $formName        = htmlspecialchars($name,       ENT_QUOTES, 'UTF-8');
    $formPhone       = htmlspecialchars($phone,      ENT_QUOTES, 'UTF-8');
    $formEmail       = htmlspecialchars($email,      ENT_QUOTES, 'UTF-8');
    $formAddress     = htmlspecialchars($address,    ENT_QUOTES, 'UTF-8');
    $formDivision    = htmlspecialchars($division,   ENT_QUOTES, 'UTF-8');
    $formNote        = htmlspecialchars($note,       ENT_QUOTES, 'UTF-8');
} else {
    // First load — blank form; zone from session or default Inside Dhaka
    $insideDhaka  = (int)($_SESSION['delivery_zone'] ?? 1);
    $formDistrict = $formName = $formPhone = $formEmail = $formAddress = $formDivision = $formNote = '';
}

$delivery = $insideDhaka ? DELIVERY_INSIDE_DHAKA : DELIVERY_OUTSIDE_DHAKA;

layoutTop('Place Order');
?>
<div class="page-wrap">
  <div class="page-inner" style="max-width:760px">
    <h1 class="page-title">Place Your <span style="color:var(--accent)">Order</span></h1>
    <p style="color:var(--text-secondary);margin-bottom:1.75rem">Review your cart and enter delivery details. We confirm by phone before shipping.</p>

    <!-- Server-side error box (only shown on failed POST) -->
    <?php if (!empty($errors)): ?>
    <div class="alert alert-error" id="server-errors">
      <?php foreach ($errors as $err): ?><p>&#9888; <?= $err ?></p><?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- Cart summary -->
    <div class="form-card">
      <h3>&#128230; Your Cart (<?= cartCount() ?> item<?= cartCount()!=1?'s':'' ?>)</h3>
      <?php foreach ($cart as $pid => $item): ?>
      <div style="display:flex;justify-content:space-between;align-items:center;padding:.5rem 0;border-bottom:1px solid var(--border);font-size:.875rem">
        <span style="font-weight:500"><?= htmlspecialchars($item['name']) ?> <span style="color:var(--text-muted)">x<?= (int)$item['qty'] ?></span></span>
        <span style="color:var(--accent);font-weight:600"><?= formatTaka($item['price'] * $item['qty']) ?></span>
      </div>
      <?php endforeach; ?>
      <div style="text-align:right;margin-top:.65rem">
        <a href="/cart.php" style="font-size:.78rem;color:var(--text-muted)">&#9998; Edit Cart</a>
      </div>
    </div>

    <form method="POST" id="orderForm">
      <!-- Customer Info -->
      <div class="form-card">
        <h3>&#128100; Your Information</h3>
        <div class="form-grid">
          <div class="form-group">
            <label>Full Name *</label>
            <input type="text" id="customer_name" name="customer_name" placeholder="Your full name" value="<?= $formName ?>" required autocomplete="name">
          </div>
          <div class="form-group">
            <label>Phone Number * <span style="color:var(--accent);font-size:.7rem">(we call to confirm)</span></label>
            <input type="tel" id="customer_phone" name="customer_phone" placeholder="01XXXXXXXXX" value="<?= $formPhone ?>" required autocomplete="tel">
          </div>
          <div class="form-group" style="grid-column:1/-1">
            <label>Email <span style="color:var(--text-muted)">(optional)</span></label>
            <input type="email" name="customer_email" placeholder="your@email.com" value="<?= $formEmail ?>" autocomplete="email">
          </div>
        </div>
      </div>

      <!-- Delivery -->
      <div class="form-card">
        <h3>&#128666; Delivery Address</h3>
        <div class="form-grid">
          <div class="form-group" style="grid-column:1/-1">
            <label>Full Address * <span style="color:var(--text-muted);font-size:.7rem">(House, Road, Area, Thana)</span></label>
            <textarea id="delivery_address" name="delivery_address" placeholder="e.g. House 12, Road 5, Mirpur-10, Dhaka" required><?= $formAddress ?></textarea>
          </div>
          <div class="form-group">
            <label>District *</label>
            <select id="district" name="district" required onchange="updateDeliveryZone(this.value)">
              <option value="">-- Select District --</option>
              <?php foreach ($allDistricts as $d): ?>
              <option value="<?= htmlspecialchars($d) ?>" <?= ($formDistrict === htmlspecialchars($d, ENT_QUOTES, 'UTF-8')) ? 'selected' : '' ?>><?= htmlspecialchars($d) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group">
            <label>Division <span style="color:var(--text-muted)">(optional)</span></label>
            <input type="text" name="division" placeholder="e.g. Dhaka Division" value="<?= $formDivision ?>">
          </div>
        </div>

        <div style="margin-top:1.1rem">
          <label style="display:block;font-size:.75rem;font-weight:500;color:var(--text-secondary);text-transform:uppercase;letter-spacing:.5px;margin-bottom:.65rem">Delivery Zone *</label>
          <div class="delivery-calc">
            <div class="delivery-opt">
              <label>
                <input type="radio" name="is_inside_dhaka" value="1" id="zone_inside" <?= $insideDhaka ? 'checked' : '' ?>>
                Inside Dhaka <span class="price-tag">&#2547;<?= DELIVERY_INSIDE_DHAKA ?></span>
              </label>
            </div>
            <div class="delivery-opt">
              <label>
                <input type="radio" name="is_inside_dhaka" value="0" id="zone_outside" <?= !$insideDhaka ? 'checked' : '' ?>>
                Outside Dhaka <span class="price-tag">&#2547;<?= DELIVERY_OUTSIDE_DHAKA ?></span>
              </label>
            </div>
          </div>
        </div>

        <div class="form-group" style="margin-top:1.1rem">
          <label>Special Note <span style="color:var(--text-muted)">(optional)</span></label>
          <textarea name="special_note" placeholder="Any special delivery instructions..."><?= $formNote ?></textarea>
        </div>
      </div>

      <!-- Order total -->
      <div class="order-summary-box">
        <h3>Order Summary</h3>
        <div class="summary-row"><span>Products subtotal</span><span id="order-subtotal" data-subtotal="<?= $subtotal ?>"><?= formatTaka($subtotal) ?></span></div>
        <div class="summary-row"><span>Delivery charge</span><span id="delivery-charge-display">&#2547;<?= $delivery ?></span></div>
        <div class="summary-row total"><span>Total to pay</span><span id="order-total">&#2547;<?= number_format($subtotal + $delivery, 0) ?></span></div>
        <p style="font-size:.72rem;color:var(--text-muted);margin-top:.5rem">&#128222; Cash on delivery. We call to confirm before shipping.</p>
      </div>

      <!-- JS client-side error box (unique ID, hidden by default) -->
      <div id="client-errors" style="display:none" class="alert alert-error"></div>

      <button type="submit" class="btn-primary btn-full" id="submitBtn">&#9989; Place Order</button>
      <p style="text-align:center;margin-top:.65rem;font-size:.78rem;color:var(--text-muted)">
        Or order via <a href="https://wa.me/8801XXXXXXXXX" target="_blank" style="color:#25d366">WhatsApp &#128232;</a>
      </p>
    </form>
  </div>
</div>

<!-- Pass PHP delivery constants to JS so prices stay in sync -->
<script>
  window.DELIVERY_INSIDE  = <?= DELIVERY_INSIDE_DHAKA ?>;
  window.DELIVERY_OUTSIDE = <?= DELIVERY_OUTSIDE_DHAKA ?>;
  window.INSIDE_DHAKA_DISTRICTS = <?= json_encode($insideDhakaDistricts) ?>;
</script>
<?php layoutBottom(); ?>
