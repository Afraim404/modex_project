<?php
require_once '../includes/config.php';
require_once 'admin_helpers.php';
requireAdmin();

$db     = getDB();
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

    $images = [];
    if (!empty($_FILES['images']['name'][0])) {
        $uploadDir = '../assets/images/';
        foreach ($_FILES['images']['tmp_name'] as $i => $tmp) {
            if ($_FILES['images']['error'][$i] === 0) {
                $ext = strtolower(pathinfo($_FILES['images']['name'][$i], PATHINFO_EXTENSION));
                if (in_array($ext, ['jpg','jpeg','png','webp'])) {
                    $fname = 'prod_' . uniqid() . '.' . $ext;
                    if (move_uploaded_file($tmp, $uploadDir . $fname)) $images[] = $fname;
                } else {
                    $errors[] = 'Only JPG, PNG, WebP allowed.';
                }
            }
        }
    }

    if (empty($errors)) {
        $db->prepare("INSERT INTO products (name,description,price,category,images,specifications,is_active) VALUES (?,?,?,?,?,?,?)")
           ->execute([$name,$desc,$price,$cat,json_encode($images),json_encode($specs),$active]);
        header('Location: /admin/products.php?saved=1');
        exit;
    }
}

adminHead('Add Product');
adminNav('products');
echo '<div class="admin-main"><main>';
?>
<a href="/admin/products.php" class="back-link">&#8592; Back to Products</a>
<div class="admin-topbar"><h1>Add New Product</h1></div>

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
        <div class="admin-form-group"><label>Product Name *</label><input type="text" name="name" value="<?= htmlspecialchars($_POST['name']??'') ?>" placeholder="e.g. MODEX Alpha Stand" required></div>
        <div class="admin-form-group"><label>Description</label><textarea name="description" rows="4" placeholder="Describe your product..."><?= htmlspecialchars($_POST['description']??'') ?></textarea></div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:.9rem">
          <div class="admin-form-group"><label>Price (&#2547;) *</label><input type="number" name="price" value="<?= htmlspecialchars($_POST['price']??'') ?>" placeholder="850" min="1" step="1" required></div>
          <div class="admin-form-group"><label>Category</label><input type="text" name="category" value="<?= htmlspecialchars($_POST['category']??'') ?>" placeholder="e.g. Stands"></div>
        </div>
        <div style="display:flex;align-items:center;gap:.5rem;margin-top:.25rem">
          <input type="checkbox" name="is_active" id="ia" value="1" checked style="accent-color:var(--accent);width:15px;height:15px">
          <label for="ia" style="font-size:.83rem;color:var(--text-secondary);cursor:pointer">Show on website</label>
        </div>
      </div>

      <div class="detail-card" style="margin-top:1rem">
        <h3>Specifications</h3>
        <p style="font-size:.78rem;color:var(--text-muted);margin-bottom:.9rem">e.g. Material → PLA, Height → 15cm</p>
        <div id="specsWrap">
          <?php for ($i=0;$i<4;$i++): ?>
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:.5rem;margin-bottom:.45rem">
            <input type="text" name="spec_key[]" placeholder="Key" style="background:var(--surface2);border:1px solid var(--border);border-radius:var(--radius-sm);padding:.52rem .7rem;color:var(--text-primary);font-size:.83rem;font-family:Inter,sans-serif">
            <input type="text" name="spec_val[]" placeholder="Value" style="background:var(--surface2);border:1px solid var(--border);border-radius:var(--radius-sm);padding:.52rem .7rem;color:var(--text-primary);font-size:.83rem;font-family:Inter,sans-serif">
          </div>
          <?php endfor; ?>
        </div>
        <button type="button" onclick="addSpec()" class="abtn abtn-view" style="margin-top:.35rem;font-size:.78rem">+ Add Row</button>
      </div>
    </div>

    <div>
      <div class="detail-card">
        <h3>Product Images</h3>
        <p style="font-size:.78rem;color:var(--text-muted);margin-bottom:.75rem">JPG, PNG or WebP. First image = main image.</p>
        <input type="file" name="images[]" multiple accept="image/jpeg,image/png,image/webp"
               style="background:var(--surface2);border:1px dashed var(--border);border-radius:var(--radius-sm);padding:.9rem;width:100%;color:var(--text-secondary);font-size:.83rem;cursor:pointer"
               onchange="previewImages(this)">
        <div id="imgPreview" style="display:flex;flex-wrap:wrap;gap:.45rem;margin-top:.65rem"></div>
      </div>
      <div style="display:flex;flex-direction:column;gap:.65rem;margin-top:1rem">
        <button type="submit" class="btn-primary btn-full">Save Product</button>
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
function previewImages(input) {
  const p = document.getElementById('imgPreview');
  p.innerHTML = '';
  Array.from(input.files).forEach(f => {
    const r = new FileReader();
    r.onload = e => {
      const img = document.createElement('img');
      img.src = e.target.result;
      img.style.cssText = 'width:68px;height:68px;object-fit:cover;border-radius:6px;border:1px solid var(--border)';
      p.appendChild(img);
    };
    r.readAsDataURL(f);
  });
}
</script>
<?php echo '</main></div>'; adminFoot(); ?>
