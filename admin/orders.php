<?php
require_once '../includes/config.php';
require_once 'admin_helpers.php';
requireAdmin();

$db      = getDB();
$status  = isset($_GET['status']) ? sanitize($_GET['status']) : '';
$search  = isset($_GET['q'])      ? sanitize($_GET['q'])      : '';
$perPage = 20;
$page    = max(1, (int)($_GET['page'] ?? 1));
$offset  = ($page-1)*$perPage;

$where  = ['1=1'];
$params = [];
if ($status) { $where[] = 'o.status=?'; $params[] = $status; }
if ($search) { $where[] = '(o.customer_name LIKE ? OR o.customer_phone LIKE ? OR o.order_number LIKE ?)'; $params[] = "%$search%"; $params[] = "%$search%"; $params[] = "%$search%"; }
$w = implode(' AND ', $where);

$total = $db->prepare("SELECT COUNT(DISTINCT o.id) FROM orders o WHERE $w"); $total->execute($params); $totalCount = $total->fetchColumn();
$totalPages = max(1, ceil($totalCount/$perPage));

$stmt = $db->prepare("SELECT o.*,GROUP_CONCAT(oi.product_name ORDER BY oi.id SEPARATOR ', ') as items,SUM(oi.price*oi.quantity) as ptotal FROM orders o LEFT JOIN order_items oi ON o.id=oi.order_id WHERE $w GROUP BY o.id ORDER BY o.created_at DESC LIMIT $perPage OFFSET $offset");
$stmt->execute($params);
$orders = $stmt->fetchAll();

$statuses = ['pending','confirmed','processing','shipped','delivered','cancelled'];

function buildURL($extra=[]) {
    global $status,$search,$page;
    $p = array_filter(['status'=>$status,'q'=>$search]);
    foreach ($extra as $k=>$v) { if ($v) $p[$k]=$v; else unset($p[$k]); }
    return $p ? '?'.http_build_query($p) : '?';
}

adminHead('Orders');
adminNav('orders');
echo '<div class="admin-main"><main>';
?>
<div class="admin-topbar">
  <h1>Orders <span style="font-size:.85rem;color:var(--text-muted);font-weight:400">(<?= $totalCount ?>)</span></h1>
  <a href="/admin/export_csv.php<?= $status?"?status=$status":'' ?>" class="abtn abtn-view">&#8595; Export CSV</a>
</div>

<div style="display:flex;gap:.45rem;flex-wrap:wrap;margin-bottom:1rem;overflow-x:auto;padding-bottom:.25rem">
  <a href="/admin/orders.php<?= $search?"?q=".urlencode($search):'' ?>" class="abtn abtn-view" style="<?= !$status?'border-color:var(--accent);color:var(--accent)':'' ?>">All (<?= $db->query("SELECT COUNT(*) FROM orders")->fetchColumn() ?>)</a>
  <?php foreach ($statuses as $s): ?>
  <a href="?status=<?= $s ?><?= $search?'&q='.urlencode($search):'' ?>" class="badge b-<?= $s ?>" style="padding:4px 10px;cursor:pointer;text-decoration:none;<?= $status===$s?'outline:2px solid currentColor':'' ?>"><?= ucfirst($s) ?> (<?= $db->query("SELECT COUNT(*) FROM orders WHERE status='$s'")->fetchColumn() ?>)</a>
  <?php endforeach; ?>
</div>

<form method="GET" style="display:flex;gap:.5rem;margin-bottom:1.25rem;flex-wrap:wrap">
  <?php if ($status): ?><input type="hidden" name="status" value="<?= htmlspecialchars($status) ?>"><?php endif; ?>
  <input name="q" value="<?= htmlspecialchars($search) ?>" placeholder="Search name, phone, order#…" style="background:var(--surface);border:1px solid var(--border);border-radius:var(--radius-sm);padding:.48rem .9rem;color:var(--text-primary);font-size:.83rem;font-family:Inter,sans-serif;flex:1;min-width:190px">
  <button type="submit" class="abtn abtn-confirm">Search</button>
  <?php if ($search||$status): ?><a href="/admin/orders.php" class="abtn abtn-cancel">Clear</a><?php endif; ?>
</form>

<div class="admin-table-wrap">
  <table class="atbl">
    <thead><tr><th>Order #</th><th>Customer</th><th>Phone</th><th>Products</th><th>District</th><th>Zone</th><th>Total</th><th>Status</th><th>Date</th><th>Actions</th></tr></thead>
    <tbody>
    <?php foreach ($orders as $o): ?>
    <tr>
      <td style="font-family:'Space Grotesk',sans-serif;color:var(--accent);font-size:.75rem"><?= htmlspecialchars($o['order_number']) ?></td>
      <td style="font-weight:500"><?= htmlspecialchars($o['customer_name']) ?></td>
      <td><a href="tel:<?= htmlspecialchars($o['customer_phone']) ?>" style="color:var(--accent)"><?= htmlspecialchars($o['customer_phone']) ?></a></td>
      <td style="max-width:130px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;color:var(--text-secondary);font-size:.78rem" title="<?= htmlspecialchars($o['items']??'') ?>"><?= htmlspecialchars($o['items']??'—') ?></td>
      <td style="font-size:.83rem"><?= htmlspecialchars($o['district']) ?></td>
      <td><span style="font-size:.72rem;color:<?= $o['is_inside_dhaka']?'var(--success)':'var(--text-secondary)' ?>"><?= $o['is_inside_dhaka']?'Dhaka':'Outside' ?></span></td>
      <td style="font-weight:600">&#2547;<?= number_format(($o['ptotal']??0)+$o['delivery_charge'],0) ?></td>
      <td><span class="badge b-<?= $o['status'] ?>"><?= ucfirst($o['status']) ?></span></td>
      <td style="color:var(--text-muted);font-size:.75rem"><?= date('d M Y',strtotime($o['created_at'])) ?></td>
      <td style="white-space:nowrap;display:flex;gap:.3rem;padding-top:.9rem">
        <a href="/admin/order_detail.php?id=<?= $o['id'] ?>" class="abtn abtn-view">View</a>
        <?php if ($o['status']==='pending'): ?>
        <form method="POST" action="/admin/quick_action.php" style="display:inline">
          <input type="hidden" name="order_id" value="<?= $o['id'] ?>">
          <input type="hidden" name="new_status" value="confirmed">
          <input type="hidden" name="redirect" value="/admin/orders.php<?= buildURL(['page'=>$page]) ?>">
          <button type="submit" class="abtn abtn-confirm" onclick="return confirm('Confirm order?')">&#10003;</button>
        </form>
        <?php endif; ?>
        <?php if (!in_array($o['status'],['cancelled','delivered'])): ?>
        <form method="POST" action="/admin/quick_action.php" style="display:inline">
          <input type="hidden" name="order_id" value="<?= $o['id'] ?>">
          <input type="hidden" name="new_status" value="cancelled">
          <input type="hidden" name="redirect" value="/admin/orders.php<?= buildURL(['page'=>$page]) ?>">
          <button type="submit" class="abtn abtn-cancel" onclick="return confirm('Cancel order?')">&#10005;</button>
        </form>
        <?php endif; ?>
      </td>
    </tr>
    <?php endforeach; ?>
    <?php if (empty($orders)): ?><tr><td colspan="10" style="text-align:center;padding:2.5rem;color:var(--text-muted)">No orders found.</td></tr><?php endif; ?>
    </tbody>
  </table>
</div>

<?php if ($totalPages > 1): ?>
<div class="pagination">
  <?php if ($page>1): ?><a href="<?= buildURL(['page'=>$page-1]) ?>">&laquo;</a><?php endif; ?>
  <?php for ($i=1;$i<=$totalPages;$i++): ?>
    <?php if ($i===$page): ?><span class="pg-active"><?= $i ?></span>
    <?php elseif($i<=3||$i>=$totalPages-2||abs($i-$page)<=2): ?><a href="<?= buildURL(['page'=>$i]) ?>"><?= $i ?></a>
    <?php elseif(abs($i-$page)===3): ?><span style="border:none;background:none;color:var(--text-muted)">…</span><?php endif; ?>
  <?php endfor; ?>
  <?php if ($page<$totalPages): ?><a href="<?= buildURL(['page'=>$page+1]) ?>">&raquo;</a><?php endif; ?>
</div>
<?php endif; ?>
<?php echo '</main></div>'; adminFoot(); ?>
