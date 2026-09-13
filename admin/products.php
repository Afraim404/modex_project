<?php
require_once '../includes/config.php';
require_once 'admin_helpers.php';
requireAdmin();

$db = getDB();

// All redirects BEFORE any output
if (isset($_GET['toggle'])) {
    $db->prepare("UPDATE products SET is_active=1-is_active WHERE id=?")->execute([(int)$_GET['toggle']]);
    header('Location: /admin/products.php'); exit;
}
if (isset($_GET['delete'])) {
    $db->prepare("DELETE FROM products WHERE id=?")->execute([(int)$_GET['delete']]);
    header('Location: /admin/products.php?deleted=1'); exit;
}

$products = $db->query("SELECT * FROM products ORDER BY id DESC")->fetchAll();

adminHead('Products');
adminNav('products');
echo '<div class="admin-main"><main>';
?>
<div class="admin-topbar">
  <h1>Products <span style="font-size:.85rem;color:var(--text-muted);font-weight:400">(<?= count($products) ?>)</span></h1>
  <a href="/admin/product_add.php" class="btn-primary" style="padding:.48rem 1.1rem;font-size:.83rem">+ Add Product</a>
</div>

<?php if (isset($_GET['deleted'])): ?><div class="alert alert-error" style="margin-bottom:1rem">Product deleted.</div><?php endif; ?>
<?php if (isset($_GET['saved'])): ?><div class="alert alert-success" style="margin-bottom:1rem">&#10003; Product saved.</div><?php endif; ?>

<div class="admin-table-wrap">
  <table class="atbl">
    <thead><tr><th>ID</th><th>Image</th><th>Name</th><th>Category</th><th>Price</th><th>Status</th><th>Added</th><th>Actions</th></tr></thead>
    <tbody>
    <?php foreach ($products as $p): $imgs=json_decode($p['images']??'[]',true); ?>
    <tr>
      <td style="color:var(--text-muted);font-size:.78rem">#<?= $p['id'] ?></td>
      <td>
        <?php if (!empty($imgs[0]) && file_exists('../assets/images/'.$imgs[0])): ?>
          <img src="/assets/images/<?= htmlspecialchars($imgs[0]) ?>" style="width:46px;height:46px;object-fit:cover;border-radius:6px;border:1px solid var(--border)">
        <?php else: ?>
          <div style="width:46px;height:46px;background:var(--surface2);border:1px solid var(--border);border-radius:6px;display:flex;align-items:center;justify-content:center">&#128247;</div>
        <?php endif; ?>
      </td>
      <td style="font-weight:500"><?= htmlspecialchars($p['name']) ?></td>
      <td style="font-size:.83rem;color:var(--text-secondary)"><?= htmlspecialchars($p['category']??'—') ?></td>
      <td style="font-weight:600;color:var(--accent)">&#2547;<?= number_format($p['price'],0) ?></td>
      <td>
        <a href="?toggle=<?= $p['id'] ?>" class="badge <?= $p['is_active']?'b-active':'b-hidden' ?>" style="cursor:pointer;text-decoration:none">
          <?= $p['is_active']?'Active':'Hidden' ?>
        </a>
      </td>
      <td style="color:var(--text-muted);font-size:.78rem"><?= date('d M Y',strtotime($p['created_at'])) ?></td>
      <td style="white-space:nowrap">
        <a href="/product.php?id=<?= $p['id'] ?>" target="_blank" class="abtn abtn-view">View</a>
        <a href="/admin/product_edit.php?id=<?= $p['id'] ?>" class="abtn abtn-edit">Edit</a>
        <a href="?delete=<?= $p['id'] ?>" class="abtn abtn-delete" onclick="return confirm('Delete this product?')">Del</a>
      </td>
    </tr>
    <?php endforeach; ?>
    <?php if (empty($products)): ?><tr><td colspan="8" style="text-align:center;padding:2.5rem;color:var(--text-muted)">No products yet. <a href="/admin/product_add.php" style="color:var(--accent)">Add one</a>.</td></tr><?php endif; ?>
    </tbody>
  </table>
</div>
<?php echo '</main></div>'; adminFoot(); ?>
