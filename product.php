<?php
require_once 'includes/config.php';
require_once 'includes/layout_top.php';
require_once 'includes/layout_bottom.php';
$db = getDB();
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$stmt = $db->prepare("SELECT * FROM products WHERE id=? AND is_active=1");
$stmt->execute([$id]);
$p = $stmt->fetch();
if (!$p) { header('Location: /products.php'); exit; }
$imgs  = json_decode($p['images']??'[]', true);
$specs = json_decode($p['specifications']??'{}', true);
$related = $db->prepare("SELECT * FROM products WHERE is_active=1 AND id!=? AND category=? LIMIT 3");
$related->execute([$id, $p['category']]);
$relatedProducts = $related->fetchAll();
if (empty($relatedProducts)) {
    $r2 = $db->prepare("SELECT * FROM products WHERE is_active=1 AND id!=? LIMIT 3");
    $r2->execute([$id]);
    $relatedProducts = $r2->fetchAll();
}
layoutTop($p['name']);
?>
<div class="product-detail">
  <div class="product-detail-inner">
    <div>
      <div class="product-main-img" id="mainImgWrap">
        <?php if (!empty($imgs[0]) && file_exists('assets/images/'.$imgs[0])): ?>
          <img src="/assets/images/<?= htmlspecialchars($imgs[0]) ?>" alt="<?= htmlspecialchars($p['name']) ?>" id="mainImg" onclick="openLightbox(this.src)">
        <?php else: ?>
          <div class="product-img-placeholder" style="flex-direction:column;gap:1rem">
            <svg width="80" height="80" viewBox="0 0 64 64" fill="none"><path d="M32 8L56 22V42L32 56L8 42V22L32 8Z" stroke="currentColor" stroke-width="2"/><path d="M32 8V56M8 22L56 22M8 42L56 42" stroke="currentColor" stroke-width="1.5" stroke-dasharray="4 3"/></svg>
            <span style="color:var(--text-muted)">3D Product</span>
          </div>
        <?php endif; ?>
      </div>
      <?php if (count($imgs) > 1): ?>
      <div style="display:flex;gap:.5rem;margin-top:.65rem;flex-wrap:wrap">
        <?php foreach ($imgs as $i => $img): if (!file_exists('assets/images/'.$img)) continue; ?>
        <img src="/assets/images/<?= htmlspecialchars($img) ?>" class="thumb-img"
             onclick="switchImg(this,'/assets/images/<?= htmlspecialchars($img) ?>')"
             style="width:62px;height:62px;object-fit:cover;border-radius:6px;border:2px solid <?= $i===0?'var(--accent)':'var(--border)' ?>;cursor:pointer;transition:border-color .2s">
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
    </div>

    <div>
      <?php if ($p['category']): ?><div class="product-cat"><?= htmlspecialchars($p['category']) ?></div><?php endif; ?>
      <h1 style="font-size:1.9rem;letter-spacing:-1px;margin-bottom:.5rem"><?= htmlspecialchars($p['name']) ?></h1>
      <div class="price-big"><?= formatTaka($p['price']) ?></div>
      <p style="color:var(--text-secondary);line-height:1.75;margin-bottom:1.25rem"><?= nl2br(htmlspecialchars($p['description'])) ?></p>

      <?php if (!empty($specs)): ?>
      <table class="specs-table">
        <?php foreach ($specs as $k => $v): ?>
        <tr><td><?= htmlspecialchars($k) ?></td><td><?= htmlspecialchars($v) ?></td></tr>
        <?php endforeach; ?>
      </table>
      <?php endif; ?>

      <!-- Add to Cart box -->
      <div class="add-to-cart-box">
        <form method="POST" action="/cart_action.php">
          <input type="hidden" name="action" value="add">
          <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
          <div class="qty-row">
            <label>Quantity</label>
            <div class="qty-control">
              <button type="button" class="qty-btn" onclick="detailChangeQty(-1)">&#8722;</button>
              <input type="number" name="qty" id="detail-qty" value="1" min="1" max="99" style="width:52px;text-align:center;background:var(--surface3);border:1px solid var(--border);border-radius:var(--radius-sm);padding:.45rem;color:var(--text-primary);font-size:1rem">
              <button type="button" class="qty-btn" onclick="detailChangeQty(1)">+</button>
            </div>
          </div>
          <div style="display:flex;gap:.65rem;flex-wrap:wrap">
            <button type="submit" name="redirect" value="/cart.php" class="btn-primary" style="flex:1">&#128722; Add to Cart</button>
            <button type="submit" name="redirect" value="/order.php" class="btn-outline" style="flex:1">&#128230; Order Now</button>
          </div>
        </form>
        <a href="https://wa.me/8801XXXXXXXXX?text=Hi%20MODEX%2C%20I%27m%20interested%20in%20<?= urlencode($p['name']) ?>" target="_blank"
           style="display:block;text-align:center;margin-top:.75rem;font-size:.83rem;color:#25d366">&#128232; Order via WhatsApp</a>
      </div>

      <div style="background:var(--surface2);border-radius:var(--radius-sm);border:1px solid var(--border);overflow:hidden">
        <div style="display:grid;grid-template-columns:1fr 1fr">
          <div style="padding:.82rem 1rem;border-right:1px solid var(--border);text-align:center">
            <div style="font-size:1.05rem;font-weight:700;color:var(--accent);font-family:'Space Grotesk',sans-serif">&#2547;80</div>
            <div style="font-size:.72rem;color:var(--text-muted);margin-top:.18rem">Inside Dhaka</div>
          </div>
          <div style="padding:.82rem 1rem;text-align:center">
            <div style="font-size:1.05rem;font-weight:700;color:var(--accent);font-family:'Space Grotesk',sans-serif">&#2547;120</div>
            <div style="font-size:.72rem;color:var(--text-muted);margin-top:.18rem">Outside Dhaka</div>
          </div>
        </div>
        <div style="padding:.55rem 1rem;border-top:1px solid var(--border);font-size:.75rem;color:var(--text-muted);text-align:center">&#10003; All 64 districts &nbsp;&#10003; Cash on delivery &nbsp;&#10003; Confirm by phone</div>
      </div>
      <a href="/products.php" style="display:inline-block;margin-top:1.1rem;font-size:.83rem;color:var(--text-muted)">&#8592; Back to products</a>
    </div>
  </div>

  <?php if (!empty($relatedProducts)): ?>
  <div style="max-width:1100px;margin:3rem auto 0;padding:0 1.5rem">
    <div class="section-header"><span class="section-eyebrow">More Products</span><h2 class="section-title" style="font-size:1.4rem">You May Also Like</h2></div>
    <div class="products-grid">
      <?php foreach ($relatedProducts as $rp): $ri = json_decode($rp['images']??'[]',true); ?>
      <div class="product-card">
        <div class="product-img" onclick="window.location='/product.php?id=<?= $rp['id'] ?>'">
          <?php if (!empty($ri[0]) && file_exists('assets/images/'.$ri[0])): ?>
            <img src="/assets/images/<?= htmlspecialchars($ri[0]) ?>" alt="<?= htmlspecialchars($rp['name']) ?>">
          <?php else: ?>
            <div class="product-img-placeholder"><svg viewBox="0 0 64 64" fill="none" width="40"><path d="M32 8L56 22V42L32 56L8 42V22L32 8Z" stroke="currentColor" stroke-width="2"/></svg></div>
          <?php endif; ?>
        </div>
        <div class="product-body">
          <div class="product-name"><?= htmlspecialchars($rp['name']) ?></div>
          <div class="product-footer">
            <div class="product-price"><?= formatTaka($rp['price']) ?></div>
            <form method="POST" action="/cart_action.php" style="display:inline">
              <input type="hidden" name="action" value="add">
              <input type="hidden" name="product_id" value="<?= $rp['id'] ?>">
              <input type="hidden" name="qty" value="1">
              <input type="hidden" name="redirect" value="/product.php?id=<?= $p['id'] ?>">
              <button type="submit" class="btn-sm">&#128722; Cart</button>
            </form>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
  <?php endif; ?>
</div>
<?php layoutBottom(); ?>
