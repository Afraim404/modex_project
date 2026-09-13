<?php
require_once 'includes/config.php';
require_once 'includes/layout_top.php';
require_once 'includes/layout_bottom.php';
$db    = getDB();
$order = null;
$items = [];
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' || isset($_GET['phone'])) {
    $phone  = sanitize($_POST['phone']  ?? $_GET['phone']  ?? '');
    $onum   = sanitize($_POST['order_number'] ?? '');
    if (!$phone && !$onum) {
        $error = 'Please enter your phone number or order number.';
    } else {
        if ($onum) {
            $stmt = $db->prepare("SELECT * FROM orders WHERE order_number=?");
            $stmt->execute([$onum]);
        } else {
            $stmt = $db->prepare("SELECT * FROM orders WHERE customer_phone=? ORDER BY created_at DESC LIMIT 1");
            $stmt->execute([$phone]);
        }
        $order = $stmt->fetch();
        if (!$order) {
            $error = 'No order found. Please check your details.';
        } else {
            $iStmt = $db->prepare("SELECT * FROM order_items WHERE order_id=?");
            $iStmt->execute([$order['id']]);
            $items = $iStmt->fetchAll();
        }
    }
}

$statusFlow = ['pending','confirmed','processing','shipped','delivered'];
$statusLabels = ['Pending','Confirmed','Processing','Shipped','Delivered'];
$statusDesc   = ['Order received, awaiting confirmation.','Order confirmed by MODEX.','Your product is being prepared.','On the way to you!','Delivered successfully!'];

layoutTop('Track Order');
?>
<div class="page-wrap">
  <div class="page-inner" style="max-width:540px">
    <h1 class="page-title">Track Your <span style="color:var(--accent)">Order</span></h1>
    <p style="color:var(--text-secondary);margin-bottom:1.75rem">Enter your phone number or order number to check status.</p>

    <div class="form-card">
      <h3>&#128269; Find Your Order</h3>
      <form method="POST">
        <div class="form-group" style="margin-bottom:.65rem">
          <label>Phone Number</label>
          <input type="tel" name="phone" placeholder="01XXXXXXXXX" value="<?= htmlspecialchars($_POST['phone']??$_GET['phone']??'') ?>">
        </div>
        <div style="text-align:center;color:var(--text-muted);font-size:.78rem;margin:.5rem 0">— or —</div>
        <div class="form-group" style="margin-bottom:.9rem">
          <label>Order Number</label>
          <input type="text" name="order_number" placeholder="MDX-XXXXXX-XXXXXXXX" value="<?= htmlspecialchars($_POST['order_number']??'') ?>">
        </div>
        <button type="submit" class="btn-primary btn-full">Track Order</button>
      </form>
    </div>

    <?php if ($error): ?><div class="alert alert-error"><?= $error ?></div><?php endif; ?>

    <?php if ($order): ?>
    <div class="form-card" style="margin-top:1.25rem">
      <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:1.1rem">
        <div>
          <div style="font-size:.7rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:1px">Order</div>
          <div style="font-family:'Space Grotesk',sans-serif;font-size:.95rem;font-weight:600;color:var(--accent)"><?= htmlspecialchars($order['order_number']) ?></div>
        </div>
        <span class="badge b-<?= $order['status'] ?>"><?= ucfirst($order['status']) ?></span>
      </div>

      <?php if (!empty($items)): ?>
      <div style="background:var(--surface2);border-radius:var(--radius-sm);padding:.75rem 1rem;margin-bottom:1.1rem">
        <?php foreach ($items as $item): ?>
        <div style="display:flex;justify-content:space-between;font-size:.83rem;padding:.22rem 0">
          <span><?= htmlspecialchars($item['product_name']) ?> &times;<?= $item['quantity'] ?></span>
          <span>&#2547;<?= number_format($item['price']*$item['quantity'],0) ?></span>
        </div>
        <?php endforeach; ?>
        <div style="display:flex;justify-content:space-between;font-size:.83rem;padding:.3rem 0;border-top:1px solid var(--border);margin-top:.35rem">
          <span style="color:var(--text-muted)">Delivery</span>
          <span>&#2547;<?= number_format($order['delivery_charge'],0) ?></span>
        </div>
      </div>
      <?php endif; ?>

      <?php if ($order['status'] !== 'cancelled'): ?>
      <div class="tracking-steps">
        <?php $currentIdx = array_search($order['status'], $statusFlow); ?>
        <?php foreach ($statusFlow as $i => $s): ?>
        <?php $done=$i<$currentIdx; $active=$i===$currentIdx; ?>
        <div class="tracking-step">
          <div class="step-dot <?= $done?'done':($active?'active':'') ?>"><?= $done?'&#10003;':($active?'&#9679;':'') ?></div>
          <div class="tracking-step-info">
            <h4 style="color:<?= $active?'var(--accent)':($done?'var(--text-primary)':'var(--text-muted)') ?>"><?= $statusLabels[$i] ?></h4>
            <p><?= $statusDesc[$i] ?></p>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
      <?php else: ?>
      <div class="alert alert-error">This order was cancelled. Please contact us for help.</div>
      <?php endif; ?>

      <div style="margin-top:1.1rem;padding-top:.9rem;border-top:1px solid var(--border);font-size:.78rem;color:var(--text-muted)">
        Ordered: <?= date('d M Y, h:i A', strtotime($order['created_at'])) ?><br>
        Delivering to: <?= htmlspecialchars($order['district']) ?>
      </div>
    </div>
    <?php endif; ?>

    <div style="text-align:center;margin-top:1.25rem">
      <a href="/" class="btn-outline" style="font-size:.875rem">&#8592; Home</a>
    </div>
  </div>
</div>
<?php layoutBottom(); ?>
