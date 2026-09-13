<?php
function layoutTop($pageTitle = '') {
    $cartCount = cartCount();
    $title = $pageTitle ? $pageTitle . ' | MODEX' : 'MODEX — Premium 3D Products';
    echo '<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0,maximum-scale=5.0">
<meta name="theme-color" content="#0a0a0a">
<title>' . htmlspecialchars($title) . '</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&family=Inter:wght@300;400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
<nav class="navbar">
  <div class="nav-inner">
    <a href="/" class="nav-logo">MOD<span>EX</span></a>
    <ul class="nav-links" id="navLinks">
      <li><a href="/">Home</a></li>
      <li><a href="/products.php">Products</a></li>
      <li><a href="/cart.php">Cart <span class="cart-badge" id="cartBadge">' . ($cartCount > 0 ? $cartCount : '') . '</span></a></li>
      <li><a href="/track.php">Track Order</a></li>
      <li><a href="/about.php">About</a></li>
      <li><a href="/contact.php">Contact</a></li>
    </ul>
    <a href="/cart.php" class="nav-cart-btn">&#128722; <span id="cartCount">' . $cartCount . '</span></a>
    <a href="/order.php" class="btn-order-nav">Order Now</a>
    <button class="nav-toggle" id="navToggle" aria-label="Menu" aria-expanded="false">
      <span></span><span></span><span></span>
    </button>
  </div>
</nav>';
}
