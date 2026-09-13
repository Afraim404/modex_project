<?php
require_once 'includes/config.php';

$action     = $_POST['action'] ?? '';
$product_id = (int)($_POST['product_id'] ?? 0);
$qty        = max(1, (int)($_POST['qty'] ?? 1));
$delta      = (int)($_POST['delta'] ?? 0);
$redirect   = $_POST['redirect'] ?? '/cart.php';

if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];

$db = getDB();

if ($action === 'add' && $product_id > 0) {
    $stmt = $db->prepare("SELECT id,name,price FROM products WHERE id=? AND is_active=1");
    $stmt->execute([$product_id]);
    $p = $stmt->fetch();
    if ($p) {
        if (isset($_SESSION['cart'][$product_id])) {
            $_SESSION['cart'][$product_id]['qty'] += $qty;
        } else {
            $_SESSION['cart'][$product_id] = [
                'id'    => $p['id'],
                'name'  => $p['name'],
                'price' => $p['price'],
                'qty'   => $qty,
            ];
        }
        if (isset($_POST['ajax'])) {
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'count' => cartCount(), 'message' => 'Added to cart']);
            exit;
        }
    }
}

if ($action === 'update' && $product_id > 0) {
    if (isset($_SESSION['cart'][$product_id])) {
        $newQty = $_SESSION['cart'][$product_id]['qty'] + $delta;
        if ($newQty <= 0) {
            unset($_SESSION['cart'][$product_id]);
        } else {
            $_SESSION['cart'][$product_id]['qty'] = min(99, $newQty);
        }
    }
}

if ($action === 'remove' && $product_id > 0) {
    unset($_SESSION['cart'][$product_id]);
}

if ($action === 'clear') {
    $_SESSION['cart'] = [];
}

header('Location: ' . $redirect);
exit;
