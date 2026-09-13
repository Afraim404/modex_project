<?php
define('DB_HOST', 'localhost');
define('DB_NAME', 'modexdco_modex_db');
define('DB_USER', 'modexdco_modex_db');
define('DB_PASS', 'bUTA4BXHzJfAKdZAZ94E');
define('DB_CHARSET', 'utf8mb4');

define('SITE_NAME', 'MODEX');
define('SITE_URL', 'https://modex-3d.com');
define('DELIVERY_INSIDE_DHAKA', 80);
define('DELIVERY_OUTSIDE_DHAKA', 120);
define('ADMIN_SESSION', 'modex_admin');
function getDB() {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $dsn = "mysql:host=".DB_HOST.";dbname=".DB_NAME.";charset=".DB_CHARSET;
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        } catch (PDOException $e) {
            die("Database connection failed. Please check your config.php");
        }
    }
    return $pdo;
}

function generateOrderNumber() {
    return 'MDX-' . strtoupper(substr(uniqid(), -6)) . '-' . date('Ymd');
}

function sanitize($input) {
    return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
}

function isAdminLoggedIn() {
    return isset($_SESSION[ADMIN_SESSION]) && $_SESSION[ADMIN_SESSION] === true;
}

function requireAdmin() {
    if (!isAdminLoggedIn()) {
        header('Location: /admin/login.php');
        exit;
    }
}

function formatTaka($amount) {
    return '&#2547;' . number_format($amount, 0);
}

function getCart() {
    return $_SESSION['cart'] ?? [];
}

function cartCount() {
    $cart = getCart();
    return array_sum(array_column($cart, 'qty'));
}

session_start();
