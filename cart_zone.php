<?php
require_once 'includes/config.php';
$_SESSION['delivery_zone'] = isset($_POST['zone']) ? (int)$_POST['zone'] : 1;
header('Location: /cart.php');
exit;
