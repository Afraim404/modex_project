<?php
require_once '../includes/config.php';
require_once 'admin_helpers.php';
requireAdmin();

$db = getDB();
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Handle status update — BEFORE any output
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_status'])) {
    $ns = sanitize($_POST['new_status']);
    $allowed = ['pending','confirmed','processing','shipped','delivered','cancelled'];
    if (in_array($ns, $allowed)) {
        $db->prepare("UPDATE orders SET status=? WHERE id=?")->execute([$ns, $id]);
    }
    header("Location: /admin/order_detail.php?id=$id&updated=1");
    exit;
}

$stmt = $db->prepare("SELECT * FROM orders WHERE id=?");
$stmt->execute([$id]);
$order = $stmt->fetch();
if (!$order) { header('Location: /admin/orders.php'); exit; }

$iStmt = $db->prepare("SELECT * FROM order_items WHERE order_id=?");
$iStmt->execute([$id]);
$items = $iStmt->fetchAll();
$ptotal = array_sum(array_map(fn($i) => $i['price']*$i['quantity'], $items));
$statuses   = ['pending','confirmed','processing','shipped','delivered','cancelled'];
$statusFlow = ['pending','confirmed','processing','shipped','delivered'];

adminHead('Order Detail');
adminNav('orders');
echo '<div class="admin-main"><main>';
?>
<a href="/admin/orders.php" class="back-link">&#8592; Back to Orders</a>

<?php if (isset($_GET['updated'])): ?><div class="alert alert-success" style="margin-bottom:1.25rem">&#10003; Status updated.</div><?php endif; ?>

<div class="admin-topbar">
  <h1>Order: <span style="color:var(--accent)"><?= htmlspecialchars($order['order_number']) ?></span></h1>
  <div style="display:flex;gap:.65rem;align-items:center">
    <span class="badge b-<?= $order['status'] ?>" style="font-size:.83rem;padding:4px 12px"><?= ucfirst($order['status']) ?></span>
    <button onclick="window.print()" class="abtn abtn-view">&#128438; Print</button>
  </div>
</div>

<!-- Status update -->
<div style="background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);padding:1.1rem;margin-bottom:1.25rem">
  <div style="font-size:.7rem;text-transform:uppercase;letter-spacing:1px;color:var(--text-muted);margin-bottom:.65rem">Update Status</div>
  <div class="status-actions">
    <?php foreach ($statuses as $s): ?>
    <form method="POST" style="display:inline">
      <input type="hidden" name="new_status" value="<?= $s ?>">
      <button type="submit" class="abtn <?= $order['status']===$s?'abtn-confirm':($s==='cancelled'?'abtn-cancel':'abtn-view') ?>"
        <?= $order['status']===$s?'disabled style="opacity:1;cursor:default;outline:2px solid currentColor"':'' ?>>
        <?= ucfirst($s) ?>
      </button>
    </form>
    <?php endforeach; ?>
  </div>
</div>

<!-- Progress bar -->
<?php if ($order['status'] !== 'cancelled'): ?>
<div style="background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);padding:1.1rem;margin-bottom:1.25rem;overflow-x:auto">
  <div style="display:flex;align-items:center;min-width:480px">
    <?php $ci = array_search($order['status'], $statusFlow); ?>
    <?php foreach ($statusFlow as $i => $s): $done=$i<$ci; $active=$i===$ci; $labels=['Pending','Confirmed','Processing','Shipped','Delivered']; ?>
    <div style="flex:1;text-align:center">
      <div style="width:30px;height:30px;border-radius:50%;margin:0 auto .35rem;display:flex;align-items:center;justify-content:center;font-size:.75rem;font-weight:700;background:<?= $done||$active?'var(--accent)':'var(--surface2)' ?>;color:<?= $done||$active?'var(--black)':'var(--text-muted)' ?>;border:2px solid <?= $done||$active?'var(--accent)':'var(--border)' ?>">
        <?= $done?'&#10003;':($i+1) ?>
      </div>
      <div style="font-size:.68rem;color:<?= $active?'var(--accent)':($done?'var(--text-primary)':'var(--text-muted)') ?>;font-weight:<?= $active?'600':'400' ?>"><?= $labels[$i] ?></div>
    </div>
    <?php if ($i<count($statusFlow)-1): ?>
    <div style="flex:1;height:2px;background:<?= $done?'var(--accent)':'var(--border)' ?>;margin-bottom:1.1rem"></div>
    <?php endif; ?>
    <?php endforeach; ?>
  </div>
</div>
<?php endif; ?>

<div class="order-detail-grid">
  <div class="detail-card">
    <h3>Customer</h3>
    <div class="detail-row"><span>Name</span><span><?= htmlspecialchars($order['customer_name']) ?></span></div>
    <div class="detail-row"><span>Phone</span><span><a href="tel:<?= htmlspecialchars($order['customer_phone']) ?>" style="color:var(--accent)"><?= htmlspecialchars($order['customer_phone']) ?></a> <a href="https://wa.me/<?= ltrim(preg_replace('/^0/','88',$order['customer_phone']),'+') ?>" target="_blank" style="color:#25d366;font-size:.72rem">WA</a></span></div>
    <?php if ($order['customer_email']): ?><div class="detail-row"><span>Email</span><span><?= htmlspecialchars($order['customer_email']) ?></span></div><?php endif; ?>
    <div class="detail-row"><span>Ordered</span><span><?= date('d M Y, h:i A',strtotime($order['created_at'])) ?></span></div>
  </div>
  <div class="detail-card">
    <h3>Delivery</h3>
    <div class="detail-row"><span>Address</span><span style="text-align:right;max-width:58%"><?= nl2br(htmlspecialchars($order['delivery_address'])) ?></span></div>
    <div class="detail-row"><span>District</span><span><?= htmlspecialchars($order['district']) ?></span></div>
    <div class="detail-row"><span>Zone</span><span style="color:<?= $order['is_inside_dhaka']?'var(--success)':'var(--text-primary)' ?>;font-weight:600"><?= $order['is_inside_dhaka']?'Inside Dhaka':'Outside Dhaka' ?></span></div>
    <div class="detail-row"><span>Delivery Fee</span><span style="color:var(--accent);font-weight:600">&#2547;<?= number_format($order['delivery_charge'],0) ?></span></div>
  </div>
</div>

<div class="admin-table-wrap" style="margin-bottom:1.25rem">
  <div class="admin-table-header"><h2>Products Ordered</h2></div>
  <table class="atbl">
    <thead><tr><th>#</th><th>Product</th><th>Price</th><th>Qty</th><th>Subtotal</th></tr></thead>
    <tbody>
    <?php foreach ($items as $i => $item): $sub=$item['price']*$item['quantity']; ?>
    <tr>
      <td style="color:var(--text-muted)"><?= $i+1 ?></td>
      <td style="font-weight:500"><?= htmlspecialchars($item['product_name']) ?></td>
      <td>&#2547;<?= number_format($item['price'],0) ?></td>
      <td><?= $item['quantity'] ?></td>
      <td style="font-weight:600">&#2547;<?= number_format($sub,0) ?></td>
    </tr>
    <?php endforeach; ?>
    <tr style="background:var(--surface2)"><td colspan="4" style="text-align:right;font-weight:600;color:var(--text-secondary)">Products Total</td><td style="font-weight:700">&#2547;<?= number_format($ptotal,0) ?></td></tr>
    <tr style="background:var(--surface2)"><td colspan="4" style="text-align:right;font-weight:600;color:var(--text-secondary)">Delivery</td><td style="font-weight:700">&#2547;<?= number_format($order['delivery_charge'],0) ?></td></tr>
    <tr style="background:var(--surface2)"><td colspan="4" style="text-align:right;font-weight:700;color:var(--accent)">Grand Total</td><td style="font-weight:700;color:var(--accent);font-size:1rem">&#2547;<?= number_format($ptotal+$order['delivery_charge'],0) ?></td></tr>
    </tbody>
  </table>
</div>

<?php if ($order['special_note']): ?>
<div class="detail-card">
  <h3>Special Note</h3>
  <p style="font-size:.875rem;color:var(--text-secondary);line-height:1.65"><?= nl2br(htmlspecialchars($order['special_note'])) ?></p>
</div>
<?php endif; ?>
<?php echo '</main></div>'; adminFoot(); ?>
