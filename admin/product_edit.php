<?php
require_once '../includes/config.php';
require_once 'admin_helpers.php';
requireAdmin();

$db = getDB();
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$stmt = $db->prepare("SELECT * FROM products WHERE id=?");
$stmt->execute([$id]);
$p = $stmt->fetch();
if (!$p) { header('Location: /admin/products.php'); exit; }

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name   = sanitize($_POST['name'] ?? '');
    $desc   = sanitize($_POST['description'] ?? '');
    $price  = floatval($_POST['price'] ?? 0);
    $cat    = sanitize($_POST['category'] ?? '');
    $active = isset($_POST['is_active']) ? 1 : 0;

    $specKeys = $_POST['spec_key'] ?? [];
    $specVals = $_POST['spec_val'] ?? [];
    $specs = [];
    foreach ($specKeys as $i => $k) {
        if (trim($k) !== '' && isset($specVals[$i]) && trim($specVals[$i]) !== '')
            $specs[trim($k)] = trim($specVals[$i]);
    }

    if (strlen($name) < 2) $errors[] = 'Product name is required.';
    if ($price <= 0)        $errors[] = 'Price must be greater than 0.';

    $currentImages = json_decode($p['images']??'[]', true);
    foreach ($_POST['delete_images'] ?? [] as $dimg) {
        if (($k = array_search($dimg, $currentImages)) !== false) {
            unset($currentImages[$k]);
            @unlink('../assets/images/' . $dimg);
        }
    }
    $currentImages = array_values($currentImages);

    if (!empty($_FILES['images']['name'][0])) {
        foreach ($_FILES['images']['tmp_name'] as $i => $tmp) {
            if ($_FILES['images']['error'][$i] === 0) {
                $ext = strtolower(pathinfo($_FILES['images']['name'][$i], PATHINFO_EXTENSION));
                if (in_array($ext, ['jpg','jpeg','png','webp'])) {
                    $fname = 'prod_' . uniqid() . '.' . $ext;
                    if (move_uploaded_file($tmp, '../assets/images/' . $fname)) $currentImages[] = $fname;
                }
            }
        }
    }

    if (empty($errors)) {
        $db->prepare("UPDATE products SET name=?,description=?,price=?,category=?,images=?,specifications=?,is_active=? WHERE id=?")
           ->execute([$name,$desc,$price,$cat,json_encode($currentImages),json_encode($specs),$active,$id]);
        header('Location: /admin/products.php?saved=1');
        exit;
    }

    $p = array_merge($p, ['name'=>$name,'description'=>$desc,'price'=>$price,'category'=>$cat,'is_active'=>$active,'images'=>json_encode($currentImages),'specifications'=>json_encode($specs)]);
}

$imgs  = json_decode($p['images']??'[]', true);
$specs = json_decode($p['specifications']??'{}', true);

adminHead('Edit Product');
adminNav('products');
echo '<div class="admin-main"><main>';
?>
<a href="/admin/products.php" class="back-link">&#8592; Back to Products</a>
<div class="admin-topbar"><h1>Edit: <?= htmlspecialchars($p['name']) ?></h1></div>

<?php if (!empty($errors)): ?>
<div class="alert alert-error" style="margin-bottom:1rem">
  <?php foreach ($errors as $e): ?><p>&#9888; <?= $e ?></p><?php endforeach; ?>
</div>
<?php endif; ?>

<form method="POST" enctype="multipart/form-data">
  <div style="display:grid;grid-template-columns:1.5fr 1fr;gap:1.25rem;align-items:start">
    <div>
      <div class="detail-card">
        <h3>Basic Information</h3>
        <div class="admin-form-group"><label>Product Name *</label><input type="text" name="name" value="<?= htmlspecialchars($p['name']) ?>" required></div>
        <div class="admin-form-group"><label>Description</label><textarea name="description" rows="4"><?= htmlspecialchars($p['description']??'') ?></textarea></div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:.9rem">
          <div class="admin-form-group"><label>Price (&#2547;) *</label><input type="number" name="price" value="<?= $p['price'] ?>" min="1" step="1" required></div>
          <div class="admin-form-group"><label>Category</label><input type="text" name="category" value="<?= htmlspecialchars($p['category']??'') ?>"></div>
        </div>
        <div style="display:flex;align-items:center;gap:.5rem;margin-top:.25rem">
          <input type="checkbox" name="is_active" id="ia" value="1" <?= $p['is_active']?'checked':'' ?> style="accent-color:var(--accent);width:15px;height:15px">
          <label for="ia" style="font-size:.83rem;color:var(--text-secondary);cursor:pointer">Show on website</label>
        </div>
      </div>

      <div class="detail-card" style="margin-top:1rem">
        <h3>Specifications</h3>
        <div id="specsWrap">
          <?php if (!empty($specs)): foreach ($specs as $k => $v): ?>
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:.5rem;margin-bottom:.45rem">
            <input type="text" name="spec_key[]" value="<?= htmlspecialchars($k) ?>" style="background:var(--surface2);border:1px solid var(--border);border-radius:var(--radius-sm);padding:.52rem .7rem;color:var(--text-primary);font-size:.83rem;font-family:Inter,sans-serif">
            <input type="text" name="spec_val[]" value="<?= htmlspecialchars($v) ?>" style="background:var(--surface2);border:1px solid var(--border);border-radius:var(--radius-sm);padding:.52rem .7rem;color:var(--text-primary);font-size:.83rem;font-family:Inter,sans-serif">
          </div>
          <?php endforeach; else: for ($i=0;$i<4;$i++): ?>
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:.5rem;margin-bottom:.45rem">
            <input type="text" name="spec_key[]" placeholder="Key" style="background:var(--surface2);border:1px solid var(--border);border-radius:var(--radius-sm);padding:.52rem .7rem;color:var(--text-primary);font-size:.83rem;font-family:Inter,sans-serif">
            <input type="text" name="spec_val[]" placeholder="Value" style="background:var(--surface2);border:1px solid var(--border);border-radius:var(--radius-sm);padding:.52rem .7rem;color:var(--text-primary);font-size:.83rem;font-family:Inter,sans-serif">
          </div>
          <?php endfor; endif; ?>
        </div>
        <button type="button" onclick="addSpec()" class="abtn abtn-view" style="margin-top:.35rem;font-size:.78rem">+ Add Row</button>
      </div>
    </div>

    <div>
      <div class="detail-card">
        <h3>Images</h3>
        <?php if (!empty($imgs)): ?>
        <div style="display:flex;flex-wrap:wrap;gap:.45rem;margin-bottom:.75rem">
          <?php foreach ($imgs as $img): ?>
          <div style="position:relative">
            <img src="/assets/images/<?= htmlspecialchars($img) ?>" style="width:76px;height:76px;object-fit:cover;border-radius:6px;border:1px solid var(--border)">
            <label style="position:absolute;top:3px;right:3px;background:rgba(255,77,77,.85);border-radius:3px;padding:1px 4px;cursor:pointer;font-size:.62rem;color:white" title="Delete">
              <input type="checkbox" name="delete_images[]" value="<?= htmlspecialchars($img) ?>" style="display:none"> &#10005;
            </label>
          </div>
          <?php endforeach; ?>
        </div>
        <p style="font-size:.72rem;color:var(--text-muted);margin-bottom:.75rem">Click &#10005; on an image to delete it</p>
        <?php else: ?><p style="color:var(--text-muted);font-size:.83rem;margin-bottom:.75rem">No images yet.</p><?php endif; ?>
        <div class="admin-form-group">
          <label>Add New Images</label>
          <input type="file" name="images[]" multiple accept="image/jpeg,image/png,image/webp"
                 style="background:var(--surface2);border:1px dashed var(--border);border-radius:var(--radius-sm);padding:.75rem;width:100%;color:var(--text-secondary);font-size:.78rem">
        </div>
      </div>
      <div style="display:flex;flex-direction:column;gap:.65rem;margin-top:1rem">
        <button type="submit" class="btn-primary btn-full">Save Changes</button>
        <a href="/admin/products.php" class="btn-outline" style="text-align:center;display:block;padding:.8rem">Cancel</a>
      </div>
    </div>
  </div>
</form>
<script>
function addSpec() {
  const w = document.getElementById('specsWrap');
  const d = document.createElement('div');
  d.style.cssText = 'display:grid;grid-template-columns:1fr 1fr;gap:.5rem;margin-bottom:.45rem';
  d.innerHTML = '<input type="text" name="spec_key[]" placeholder="Key" style="background:var(--surface2);border:1px solid var(--border);border-radius:var(--radius-sm);padding:.52rem .7rem;color:var(--text-primary);font-size:.83rem;font-family:Inter,sans-serif"><input type="text" name="spec_val[]" placeholder="Value" style="background:var(--surface2);border:1px solid var(--border);border-radius:var(--radius-sm);padding:.52rem .7rem;color:var(--text-primary);font-size:.83rem;font-family:Inter,sans-serif">';
  w.appendChild(d);
}
</script>
<?php echo '</main></div>'; adminFoot(); ?>
