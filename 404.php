<?php
require_once 'includes/config.php';
require_once 'includes/layout_top.php';
require_once 'includes/layout_bottom.php';
http_response_code(404);
layoutTop('Page Not Found');
?>
<div class="page-404">
  <div>
    <div class="num">404</div>
    <h2>Page Not Found</h2>
    <p style="color:var(--text-secondary);margin-bottom:1.75rem">The page you're looking for doesn't exist.</p>
    <div style="display:flex;gap:.75rem;justify-content:center;flex-wrap:wrap">
      <a href="/" class="btn-primary">Go Home</a>
      <a href="/products.php" class="btn-outline">Browse Products</a>
    </div>
  </div>
</div>
<?php layoutBottom(); ?>
