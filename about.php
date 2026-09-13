<?php
require_once 'includes/config.php';
require_once 'includes/layout_top.php';
require_once 'includes/layout_bottom.php';
layoutTop('About Us');
?>
<div class="page-hero">
  <span class="section-eyebrow">About MODEX</span>
  <h1>Crafted with <span style="color:var(--accent)">Precision</span></h1>
  <p>We design and 3D print premium products built to last, delivered across Bangladesh.</p>
</div>
<section class="section" style="padding-top:2rem">
  <div class="section-inner" style="max-width:860px;margin:0 auto">
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:3rem;align-items:center;margin-bottom:3.5rem">
      <div>
        <span class="section-eyebrow">Our Story</span>
        <h2 style="font-size:1.7rem;letter-spacing:-1px;margin-bottom:.9rem">Born from a passion for precision</h2>
        <p style="color:var(--text-secondary);line-height:1.8;margin-bottom:.9rem">MODEX started with a simple idea: Bangladesh deserves access to premium 3D printed products that are both functional and beautifully designed.</p>
        <p style="color:var(--text-secondary);line-height:1.8">We combine modern 3D printing technology with careful craftsmanship to deliver products you'll be proud to own. Every piece goes through rigorous quality checks before it ships to you.</p>
      </div>
      <div style="border-radius:var(--radius);overflow:hidden;border:1px solid var(--border)">
        <img src="/assets/images/modex-logo-banner.jpg" alt="MODEX" style="width:100%;height:280px;object-fit:cover;display:block">
      </div>
    </div>

    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:1.25rem;margin-bottom:3rem">
      <?php foreach ([['&#127919;','Quality First','Only high-grade PLA, PETG, and ABS materials.'],['&#128296;','Precision Printing','Calibrated for maximum accuracy and clean finishes.'],['&#128666;','Fast Delivery','All 64 districts covered with care.']] as $v): ?>
      <div style="background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);padding:1.4rem;text-align:center">
        <div style="font-size:1.9rem;margin-bottom:.65rem"><?= $v[0] ?></div>
        <h3 style="font-size:.95rem;margin-bottom:.4rem"><?= $v[1] ?></h3>
        <p style="font-size:.83rem;color:var(--text-secondary)"><?= $v[2] ?></p>
      </div>
      <?php endforeach; ?>
    </div>

    <div style="text-align:center;background:var(--surface);border:1px solid var(--border-accent);border-radius:var(--radius);padding:2.5rem">
      <h2 style="font-size:1.45rem;margin-bottom:.65rem">Ready to experience MODEX quality?</h2>
      <p style="color:var(--text-secondary);margin-bottom:1.4rem">Browse our catalog and order today.</p>
      <a href="/products.php" class="btn-primary">Shop Products</a>
      <a href="/contact.php" class="btn-outline" style="display:inline-block;margin-left:.65rem">Contact Us</a>
    </div>
  </div>
</section>
<?php layoutBottom(); ?>