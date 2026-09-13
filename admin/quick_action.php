<?php
require_once '../includes/config.php';
requireAdmin();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: /admin/orders.php'); exit; }
$id       = (int)($_POST['order_id'] ?? 0);
$status   = sanitize($_POST['new_status'] ?? '');
$redirect = sanitize($_POST['redirect'] ?? '/admin/orders.php');
$allowed  = ['pending','confirmed','processing','shipped','delivered','cancelled'];
if ($id && in_array($status, $allowed)) {
    getDB()->prepare("UPDATE orders SET status=? WHERE id=?")->execute([$status, $id]);
}
header('Location: ' . $redirect);
exit;
