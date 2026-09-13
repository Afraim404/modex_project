<?php
function adminNav($active='') {
    $links = [
        'index'       => ['&#9632;', 'Dashboard', '/admin/index.php'],
        'orders'      => ['&#128230;', 'Orders',    '/admin/orders.php'],
        'products'    => ['&#128247;', 'Products',  '/admin/products.php'],
        'testimonials'=> ['&#11088;',  'Reviews',   '/admin/testimonials.php'],
        'settings'    => ['&#9881;',   'Settings',  '/admin/settings.php'],
    ];
    echo '<aside class="admin-sidebar">';
    echo '<div class="sidebar-brand">MOD<span>EX</span></div>';
    echo '<nav class="sidebar-nav">';
    echo '<div class="sidebar-section">Main</div>';
    foreach ($links as $key => $l) {
        $cls = ($active === $key) ? ' class="active"' : '';
        echo '<a href="'.$l[2].'"'.$cls.'><span class="ni">'.$l[0].'</span>'.$l[1].'</a>';
    }
    echo '<div class="sidebar-section">More</div>';
    echo '<a href="/" target="_blank"><span class="ni">&#127760;</span>View Site</a>';
    echo '<a href="/admin/logout.php"><span class="ni">&#8592;</span>Logout</a>';
    echo '</nav></aside>';
}

function adminHead($title='MODEX Admin') {
    echo '<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0,maximum-scale=5.0">
<meta name="robots" content="noindex,nofollow">
<title>'.htmlspecialchars($title).' — MODEX Admin</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;600;700&family=Inter:wght@300;400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/assets/css/style.css">
</head>
<body class="admin-wrap">';
}

function adminFoot() {
    echo '</main></div>
<script src="/assets/js/main.js"></script>
</body></html>';
}
