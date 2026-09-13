<?php
require_once 'includes/config.php';
require_once 'includes/layout_top.php';
require_once 'includes/layout_bottom.php';
$cart = getCart();
$db   = getDB();

// Refresh prices from DB
$subtotal = 0;
foreach ($cart as $pid => $item) {
    $subtotal += $item['price'] * $item['qty'];
}

$insideDhaka = isset($_SESSION['delivery_zone']) ? (int)$_SESSION['delivery_zone'] : 1;
$delivery    = $insideDhaka ? DELIVERY_INSIDE_DHAKA : DELIVERY_OUTSIDE_DHAKA;
$total       = $subtotal + (empty($cart) ? 0 : $delivery);

layoutTop('Cart');
?>
<div class="page-wrap">
  <div class="page-inner">
    <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:1rem">
      <div>
        <h1 class="page-title">Your Cart</h1>
        <p style="color:var(--text-secondary);font-size:.875rem"><?= cartCount() ?> item<?= cartCount()!=1?'s':'' ?> in your cart</p>
      </div>
      <?php if (!empty($cart)): ?>
      <form method="POST" action="/cart_action.php">
        <input type="hidden" name="action" value="clear">
        <input type="hidden" name="redirect" value="/cart.php">
        <button type="submit" class="btn-danger" onclick="return confirm('Clear your cart?')">&#128465; Clear Cart</button>
      </form>
      <?php endif; ?>
    </div>

    <?php if (isset($_GET['added'])): ?>
    <div class="alert alert-success" style="margin-top:1rem">&#10003; Product added to cart!</div>
    <?php endif; ?>

    <?php if (empty($cart)): ?>
    <div class="cart-empty">
      <div class="empty-icon">&#128722;</div>
      <h2 style="font-size:1.4rem;margin-bottom:.5rem">Your cart is empty</h2>
      <p style="margin-bottom:1.5rem">Add some products to get started.</p>
      <a href="/products.php" class="btn-primary">Browse Products</a>
    </div>
    <?php else: ?>
    <div class="cart-grid">
      <div class="cart-items">
        <?php foreach ($cart as $pid => $item): ?>
        <div class="cart-item">
          <div class="cart-item-img">
            <?php
              $pStmt = $db->prepare("SELECT images FROM products WHERE id=?");
              $pStmt->execute([$pid]);
              $pRow = $pStmt->fetch();
              $imgs = json_decode($pRow['images']??'[]', true);
            ?>
            <?php if (!empty($imgs[0]) && file_exists('assets/images/'.$imgs[0])): ?>
              <img src="/assets/images/<?= htmlspecialchars($imgs[0]) ?>" alt="<?= htmlspecialchars($item['name']) ?>">
            <?php else: ?>
              <div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;font-size:1.8rem">&#128247;</div>
            <?php endif; ?>
          </div>
          <div class="cart-item-info">
            <div class="cart-item-name"><?= htmlspecialchars($item['name']) ?></div>
            <div class="cart-item-price"><?= formatTaka($item['price']) ?> each</div>
            <div class="cart-item-controls">
              <button type="button" class="qty-btn" onclick="cartChangeQty(<?= $pid ?>, -1)">&#8722;</button>
              <span class="qty-display"><?= $item['qty'] ?></span>
              <button type="button" class="qty-btn" onclick="cartChangeQty(<?= $pid ?>, 1)">+</button>
              <span style="color:var(--text-muted);font-size:.78rem;margin-left:.5rem">= <?= formatTaka($item['price'] * $item['qty']) ?></span>
            </div>
          </div>
          <form method="POST" action="/cart_action.php">
            <input type="hidden" name="action" value="remove">
            <input type="hidden" name="product_id" value="<?= $pid ?>">
            <input type="hidden" name="redirect" value="/cart.php">
            <button type="submit" class="btn-danger" style="padding:.3rem .6rem;font-size:.8rem" title="Remove">&#10005;</button>
          </form>
        </div>
        <?php endforeach; ?>

        <!-- Delivery zone selector -->
        <div class="form-card" style="margin-top:1rem">
          <h3>&#128666; Delivery Zone</h3>
          <form method="POST" action="/cart_zone.php" id="zoneForm">
            <div class="delivery-calc">
              <div class="delivery-opt">
                <label>
                  <input type="radio" name="zone" value="1" <?= $insideDhaka?'checked':'' ?> onchange="document.getElementById('zoneForm').submit()">
                  Inside Dhaka <span class="price-tag">&#2547;<?= DELIVERY_INSIDE_DHAKA ?></span>
                </label>
              </div>
              <div class="delivery-opt">
                <label>
                  <input type="radio" name="zone" value="0" <?= !$insideDhaka?'checked':'' ?> onchange="document.getElementById('zoneForm').submit()">
                  Outside Dhaka <span class="price-tag">&#2547;<?= DELIVERY_OUTSIDE_DHAKA ?></span>
                </label>
              </div>
            </div>
          </form>
        </div>
      </div>

      <!-- Summary -->
      <div class="cart-summary">
        <h3>Order Summary</h3>
        <div class="summary-row"><span>Subtotal</span><span><?= formatTaka($subtotal) ?></span></div>
        <div class="summary-row"><span>Delivery</span><span><?= formatTaka($delivery) ?></span></div>
        <div class="summary-row total"><span>Total</span><span><?= formatTaka($total) ?></span></div>
        <a href="/order.php" class="btn-primary btn-full" style="margin-top:1.25rem;display:block;text-align:center">Proceed to Order &#8594;</a>
        <a href="/products.php" class="btn-outline btn-full" style="margin-top:.65rem;display:block;text-align:center">Continue Shopping</a>
        <p style="font-size:.72rem;color:var(--text-muted);text-align:center;margin-top:.9rem">&#128222; We confirm by phone before delivery. Cash on delivery.</p>
      </div>
    </div>
    <?php endif; ?>
  </div>
</div>
<?php layoutBottom(); ?>
