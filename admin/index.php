<?php
require_once '../includes/config.php';
require_once 'admin_helpers.php';
requireAdmin();

$db = getDB();
$stats = [
    'total'     => $db->query("SELECT COUNT(*) FROM orders")->fetchColumn(),
    'pending'   => $db->query("SELECT COUNT(*) FROM orders WHERE status='pending'")->fetchColumn(),
    'confirmed' => $db->query("SELECT COUNT(*) FROM orders WHERE status='confirmed'")->fetchColumn(),
    'delivered' => $db->query("SELECT COUNT(*) FROM orders WHERE status='delivered'")->fetchColumn(),
    'cancelled' => $db->query("SELECT COUNT(*) FROM orders WHERE status='cancelled'")->fetchColumn(),
    'products'  => $db->query("SELECT COUNT(*) FROM products WHERE is_active=1")->fetchColumn(),
];
$revenue = $db->query("SELECT COALESCE(SUM(oi.price*oi.quantity+o.delivery_charge),0) FROM orders o LEFT JOIN order_items oi ON o.id=oi.order_id WHERE o.status IN ('confirmed','processing','shipped','delivered')")->fetchColumn();
$todayCount = $db->query("SELECT COUNT(*) FROM orders WHERE DATE(created_at)=CURDATE()")->fetchColumn();
$recent = $db->query("SELECT o.*,GROUP_CONCAT(oi.product_name ORDER BY oi.id SEPARATOR ', ') as items,SUM(oi.price*oi.quantity) as ptotal FROM orders o LEFT JOIN order_items oi ON o.id=oi.order_id GROUP BY o.id ORDER BY o.created_at DESC LIMIT 8")->fetchAll();

adminHead('Dashboard');
adminNav('index');
echo '<div class="admin-main"><main>';
?>
<div class="admin-topbar">
  <h1>Dashboard</h1>
  <span style="font-size:.8rem;color:var(--text-muted)"><?= date('l, d F Y') ?></span>
</div>

<div class="admin-cards">
  <div class="admin-card c-accent"><div class="admin-card-num"><?= $stats['total'] ?></div><div class="admin-card-label">Total Orders</div></div>
  <div class="admin-card c-warn"><div class="admin-card-num"><?= $stats['pending'] ?></div><div class="admin-card-label">&#128276; Pending</div></div>
  <div class="admin-card c-accent"><div class="admin-card-num"><?= $stats['confirmed'] ?></div><div class="admin-card-label">Confirmed</div></div>
  <div class="admin-card c-success"><div class="admin-card-num"><?= $stats['delivered'] ?></div><div class="admin-card-label">Delivered</div></div>
  <div class="admin-card c-danger"><div class="admin-card-num"><?= $stats['cancelled'] ?></div><div class="admin-card-label">Cancelled</div></div>
  <div class="admin-card"><div class="admin-card-num"><?= $stats['products'] ?></div><div class="admin-card-label">Active Products</div></div>
  <div class="admin-card c-success"><div class="admin-card-num" style="font-size:1.3rem">&#2547;<?= number_format($revenue,0) ?></div><div class="admin-card-label">Total Revenue</div></div>
  <div class="admin-card c-warn"><div class="admin-card-num"><?= $todayCount ?></div><div class="admin-card-label">Orders Today</div></div>
</div>

<?php if ($stats['pending'] > 0): ?>
<div class="alert alert-info" style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.5rem">
  <span>&#9888; <strong><?= $stats['pending'] ?></strong> pending order<?= $stats['pending']>1?'s':'' ?> waiting for confirmation.</span>
  <a href="/admin/orders.php?status=pending" style="color:var(--accent);font-weight:600;font-size:.83rem">Review &rarr;</a>
</div>
<?php endif; ?>

<div class="admin-table-wrap">
  <div class="admin-table-header">
    <h2>Recent Orders</h2>
    <div style="display:flex;gap:.5rem">
      <a href="/admin/export_csv.php" class="abtn abtn-view" style="font-size:.75rem">&#8595; CSV</a>
      <a href="/admin/orders.php" class="abtn abtn-confirm">View All</a>
    </div>
  </div>
  <table class="atbl">
    <thead><tr><th>Order #</th><th>Customer</th><th>Phone</th><th>Products</th><th>District</th><th>Total</th><th>Status</th><th>Date</th><th>Action</th></tr></thead>
    <tbody>
    <?php foreach ($recent as $o): ?>
    <tr>
      <td style="font-family:'Space Grotesk',sans-serif;color:var(--accent);font-size:.75rem"><?= htmlspecialchars($o['order_number']) ?></td>
      <td style="font-weight:500"><?= htmlspecialchars($o['customer_name']) ?></td>
      <td><a href="tel:<?= htmlspecialchars($o['customer_phone']) ?>" style="color:var(--accent)"><?= htmlspecialchars($o['customer_phone']) ?></a></td>
      <td style="max-width:140px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;color:var(--text-secondary);font-size:.8rem" title="<?= htmlspecialchars($o['items']??'') ?>"><?= htmlspecialchars($o['items']??'—') ?></td>
      <td style="font-size:.83rem"><?= htmlspecialchars($o['district']) ?></td>
      <td style="font-weight:600">&#2547;<?= number_format(($o['ptotal']??0)+$o['delivery_charge'],0) ?></td>
      <td><span class="badge b-<?= $o['status'] ?>"><?= ucfirst($o['status']) ?></span></td>
      <td style="color:var(--text-muted);font-size:.75rem"><?= date('d M, h:i A',strtotime($o['created_at'])) ?></td>
      <td style="white-space:nowrap">
        <a href="/admin/order_detail.php?id=<?= $o['id'] ?>" class="abtn abtn-view">View</a>
        <?php if ($o['status']==='pending'): ?>
        <form method="POST" action="/admin/quick_action.php" style="display:inline">
          <input type="hidden" name="order_id" value="<?= $o['id'] ?>">
          <input type="hidden" name="new_status" value="confirmed">
          <input type="hidden" name="redirect" value="/admin/index.php">
          <button type="submit" class="abtn abtn-confirm" onclick="return confirm('Confirm this order?')">&#10003;</button>
        </form>
        <?php endif; ?>
      </td>
    </tr>
    <?php endforeach; ?>
    <?php if (empty($recent)): ?><tr><td colspan="9" style="text-align:center;padding:2.5rem;color:var(--text-muted)">No orders yet.</td></tr><?php endif; ?>
    </tbody>
  </table>
</div>
<?php echo '</main></div>'; adminFoot(); ?>
