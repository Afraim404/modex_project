<?php
require_once 'includes/config.php';
require_once 'includes/layout_top.php';
require_once 'includes/layout_bottom.php';
$db       = getDB();
$category = isset($_GET['cat']) ? sanitize($_GET['cat']) : '';
$search   = isset($_GET['q'])   ? sanitize($_GET['q'])   : '';
$where = ['is_active=1'];
$params = [];
if ($category) { $where[] = 'category=?'; $params[] = $category; }
if ($search)   { $where[] = '(name LIKE ? OR description LIKE ?)'; $params[] = "%$search%"; $params[] = "%$search%"; }
$sql  = 'SELECT * FROM products WHERE ' . implode(' AND ',$where) . ' ORDER BY id';
$stmt = $db->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();
$cats = $db->query("SELECT DISTINCT category FROM products WHERE is_active=1 AND category IS NOT NULL AND category!='' ORDER BY category")->fetchAll(PDO::FETCH_COLUMN);
layoutTop('Products');
?>
<div class="page-hero" style="padding-bottom:2rem">
  <span class="section-eyebrow">Catalog</span>
  <h1>Our <span style="color:var(--accent)">Products</span></h1>
  <p>Precision-crafted 3D printed products. Add to cart and order to your door.</p>
</div>
<section class="section" style="padding-top:1rem">
  <div class="section-inner">
    <div style="display:flex;gap:.75rem;flex-wrap:wrap;margin-bottom:1.75rem;align-items:center">
      <form method="GET" style="display:flex;gap:.65rem;flex:1;flex-wrap:wrap;align-items:center">
        <input name="q" value="<?= htmlspecialchars($search) ?>" placeholder="Search products…" style="background:var(--surface);border:1px solid var(--border);border-radius:var(--radius-sm);padding:.52rem 1rem;color:var(--text-primary);font-size:.875rem;font-family:Inter,sans-serif;flex:1;min-width:160px">
        <?php foreach ($cats as $c): ?>
          <a href="?cat=<?= urlencode($c) ?><?= $search?'&q='.urlencode($search):'' ?>" class="btn-outline" style="padding:.45rem .9rem;font-size:.78rem;<?= $category===$c?'border-color:var(--accent);color:var(--accent)':'' ?>"><?= htmlspecialchars($c) ?></a>
        <?php endforeach; ?>
        <?php if ($category||$search): ?><a href="/products.php" class="btn-outline" style="padding:.45rem .9rem;font-size:.78rem">Clear</a><?php endif; ?>
        <button type="submit" class="btn-primary" style="padding:.52rem 1.1rem;font-size:.875rem">Search</button>
      </form>
    </div>
    <div class="products-grid">
      <?php foreach ($products as $p): $imgs = json_decode($p['images']??'[]',true); ?>
      <div class="product-card">
        <div class="product-img" onclick="window.location='/product.php?id=<?= $p['id'] ?>'">
          <?php if (!empty($imgs[0]) && file_exists('assets/images/'.$imgs[0])): ?>
            <img src="/assets/images/<?= htmlspecialchars($imgs[0]) ?>" alt="<?= htmlspecialchars($p['name']) ?>">
          <?php else: ?>
            <div class="product-img-placeholder">
              <svg viewBox="0 0 64 64" fill="none" width="48"><path d="M32 8L56 22V42L32 56L8 42V22L32 8Z" stroke="currentColor" stroke-width="2"/><path d="M32 8V56M8 22L56 22M8 42L56 42" stroke="currentColor" stroke-width="1.5" stroke-dasharray="4 3"/></svg>
              <span>3D Product</span>
            </div>
          <?php endif; ?>
          <?php if ($p['category']): ?><span class="product-badge"><?= htmlspecialchars($p['category']) ?></span><?php endif; ?>
        </div>
        <div class="product-body">
          <?php if ($p['category']): ?><div class="product-cat"><?= htmlspecialchars($p['category']) ?></div><?php endif; ?>
          <div class="product-name"><?= htmlspecialchars($p['name']) ?></div>
          <div class="product-desc"><?= htmlspecialchars($p['description']) ?></div>
          <div class="product-footer">
            <div class="product-price"><?= formatTaka($p['price']) ?></div>
            <div class="product-actions">
              <form method="POST" action="/cart_action.php" style="display:inline">
                <input type="hidden" name="action" value="add">
                <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
                <input type="hidden" name="qty" value="1">
                <input type="hidden" name="redirect" value="/products.php<?= $category||$search ? '?'.http_build_query(array_filter(['cat'=>$category,'q'=>$search])) : '' ?>">
                <button type="submit" class="btn-sm">&#128722; Cart</button>
              </form>
              
            </div>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
      <?php if (empty($products)): ?>
        <div style="grid-column:1/-1;text-align:center;padding:4rem;color:var(--text-muted)">No products found.</div>
      <?php endif; ?>
    </div>
  </div>
</section>
<?php layoutBottom(); ?>