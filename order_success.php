<?php
require_once 'includes/config.php';
require_once 'includes/layout_top.php';
require_once 'includes/layout_bottom.php';

$orderNumber = sanitize($_GET['order'] ?? '');
if (!$orderNumber) { header('Location: /'); exit; }

$db   = getDB();
$stmt = $db->prepare("SELECT o.*, GROUP_CONCAT(oi.product_name ORDER BY oi.id SEPARATOR ', ') as items FROM orders o LEFT JOIN order_items oi ON o.id=oi.order_id WHERE o.order_number=? GROUP BY o.id");
$stmt->execute([$orderNumber]);
$order = $stmt->fetch();
if (!$order) { header('Location: /'); exit; }

layoutTop('Order Placed!');
?>
<div class="success-page">
  <div class="success-card">
    <div class="success-icon">&#10003;</div>
    <h2 style="font-size:1.55rem;margin-bottom:.5rem">Order Placed!</h2>
    <p style="color:var(--text-secondary)">Thank you, <strong style="color:var(--text-primary)"><?= htmlspecialchars($order['customer_name']) ?></strong>.</p>
    <p style="color:var(--text-secondary);font-size:.875rem;margin-top:.35rem">Your order is pending confirmation.</p>
    <div class="order-num-badge"><?= htmlspecialchars($order['order_number']) ?></div>

    <div style="background:var(--surface2);border-radius:var(--radius-sm);padding:1rem;margin:.9rem 0;text-align:left;font-size:.875rem">
      <div style="display:flex;justify-content:space-between;padding:.32rem 0;border-bottom:1px solid var(--border)"><span style="color:var(--text-secondary)">Products</span><span style="max-width:55%;text-align:right;font-size:.82rem"><?= htmlspecialchars($order['items']??'—') ?></span></div>
      <div style="display:flex;justify-content:space-between;padding:.32rem 0;border-bottom:1px solid var(--border)"><span style="color:var(--text-secondary)">District</span><span><?= htmlspecialchars($order['district']) ?></span></div>
      <div style="display:flex;justify-content:space-between;padding:.32rem 0;border-bottom:1px solid var(--border)"><span style="color:var(--text-secondary)">Delivery</span><span>&#2547;<?= number_format($order['delivery_charge'],0) ?></span></div>
      <div style="display:flex;justify-content:space-between;padding:.32rem 0"><span style="color:var(--text-secondary)">Status</span><span class="badge b-pending">Pending</span></div>
    </div>

    <p style="font-size:.78rem;color:var(--text-muted)">We will call <strong style="color:var(--text-primary)"><?= htmlspecialchars($order['customer_phone']) ?></strong> to confirm your order.</p>
    <div style="display:flex;flex-direction:column;gap:.6rem;margin-top:1.5rem">
      <a href="/track.php?phone=<?= urlencode($order['customer_phone']) ?>" class="btn-outline" style="display:block;text-align:center">&#128269; Track My Order</a>
      <a href="/" class="btn-primary" style="display:block;text-align:center">Back to Home</a>
    </div>
  </div>
</div>
<?php layoutBottom(); ?>
