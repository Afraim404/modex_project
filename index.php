<?php
require_once 'includes/config.php';
require_once 'includes/layout_top.php';
require_once 'includes/layout_bottom.php';
$db = getDB();
$products     = $db->query("SELECT * FROM products WHERE is_active=1 ORDER BY id LIMIT 6")->fetchAll();
$testimonials = $db->query("SELECT * FROM testimonials WHERE is_active=1 ORDER BY id")->fetchAll();
$totalOrders  = $db->query("SELECT COUNT(*) FROM orders")->fetchColumn();
layoutTop('Home');
?>
<section class="hero">
  <div class="hero-bg"></div>
  <div class="hero-grid"></div>
  <div class="hero-content">
    <span class="hero-tag">Premium 3D Printed Products</span>
    <h1>Designed to<br><span>Stand Out</span></h1>
    <p>Precision-crafted 3D products built with high-grade materials. Delivered anywhere in Bangladesh.</p>
    <div class="hero-actions">
      <a href="/products.php" class="btn-primary">Browse Products</a>
      <a href="/cart.php" class="btn-outline">&#128722; View Cart</a>
    </div>
  </div>
  <div class="hero-scroll">Scroll</div>
</section>

<section class="section" style="padding-top:2rem;padding-bottom:2rem">
  <div class="section-inner">
    <div class="stats-grid">
      <div class="stat-item"><div class="stat-num"><?= max(50,$totalOrders+47) ?>+</div><div class="stat-label">Orders Delivered</div></div>
      <div class="stat-item"><div class="stat-num"><?= count($products) ?>+</div><div class="stat-label">Products</div></div>
      <div class="stat-item"><div class="stat-num">64</div><div class="stat-label">Districts Covered</div></div>
      <div class="stat-item"><div class="stat-num">100%</div><div class="stat-label">Satisfaction</div></div>
    </div>
  </div>
</section>

<section class="section">
  <div class="section-inner">
    <div class="section-header center">
      <span class="section-eyebrow">Our Products</span>
      <h2 class="section-title">Precision-Crafted Pieces</h2>
      <p class="section-sub">Every product is printed with care using high-grade materials.</p>
    </div>
    <div class="products-grid">
      <?php foreach ($products as $p): $imgs = json_decode($p['images']??'[]',true); ?>
      <div class="product-card">
        <div class="product-img" onclick="window.location='/product.php?id=<?= $p['id'] ?>'">
          <?php if (!empty($imgs[0]) && file_exists('assets/images/'.$imgs[0])): ?>
            <img src="/assets/images/<?= htmlspecialchars($imgs[0]) ?>" alt="<?= htmlspecialchars($p['name']) ?>">
          <?php else: ?>
            <div class="product-img-placeholder">
              <svg viewBox="0 0 64 64" fill="none"><path d="M32 8L56 22V42L32 56L8 42V22L32 8Z" stroke="currentColor" stroke-width="2"/><path d="M32 8V56M8 22L56 22M8 42L56 42" stroke="currentColor" stroke-width="1.5" stroke-dasharray="4 3"/></svg>
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
                <input type="hidden" name="redirect" value="/">
                <button type="submit" class="btn-sm" title="Add to Cart">&#128722; Cart</button>
              </form>
              
            </div>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php if (count($products) >= 6): ?>
    <div style="text-align:center;margin-top:2rem"><a href="/products.php" class="btn-outline">View All Products</a></div>
    <?php endif; ?>
  </div>
</section>

<div class="delivery-banner">
  <div class="delivery-inner">
    <div class="delivery-item"><h3>&#2547;80</h3><p>Inside Dhaka</p></div>
    <div class="delivery-item" style="color:var(--text-muted)">|</div>
    <div class="delivery-item"><h3>&#2547;120</h3><p>Outside Dhaka</p></div>
    <div class="delivery-item" style="color:var(--text-muted)">|</div>
    <div class="delivery-item"><h3>64</h3><p>Districts Covered</p></div>
  </div>
</div>

<section class="section">
  <div class="section-inner">
    <div class="section-header center">
      <span class="section-eyebrow">How It Works</span>
      <h2 class="section-title">Order in 3 Simple Steps</h2>
    </div>
    <div class="process-steps">
      <div class="process-step"><div class="step-num">1</div><div><div class="step-title">Add to Cart</div><div class="step-desc">Browse products and add what you like to your cart.</div></div></div>
      <div class="process-step"><div class="step-num">2</div><div><div class="step-title">Place Order</div><div class="step-desc">Fill in your name, phone, and delivery address.</div></div></div>
      <div class="process-step"><div class="step-num">3</div><div><div class="step-title">Receive at Door</div><div class="step-desc">We confirm by phone, then deliver across Bangladesh.</div></div></div>
    </div>
  </div>
</section>

<?php if (!empty($testimonials)): ?>
<section class="section" style="padding-top:0">
  <div class="section-inner">
    <div class="section-header center">
      <span class="section-eyebrow">Reviews</span>
      <h2 class="section-title">What Customers Say</h2>
    </div>
    <div class="testimonials-grid">
      <?php foreach ($testimonials as $t): ?>
      <div class="testimonial-card">
        <div class="stars"><?= str_repeat('★',(int)$t['rating']) ?></div>
        <div class="testimonial-text">"<?= htmlspecialchars($t['review']) ?>"</div>
        <div class="testimonial-author">— <?= htmlspecialchars($t['customer_name']) ?></div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<section class="section" style="padding-top:0">
  <div class="section-inner" style="text-align:center">
    <div style="background:var(--surface);border:1px solid var(--border-accent);border-radius:var(--radius);padding:3rem 2rem;max-width:580px;margin:0 auto">
      <h2 style="font-size:1.7rem;margin-bottom:.65rem">Ready to Order?</h2>
      <p style="color:var(--text-secondary);margin-bottom:1.75rem">Get your premium 3D product delivered across Bangladesh.</p>
      <a href="/products.php" class="btn-primary">Shop Now</a>
    </div>
  </div>
</section>
<?php layoutBottom(); ?>