<?php
require_once 'includes/config.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: /contact.php'); exit; }
// Add mail() here if you want email notifications
// mail('info@modex.com.bd', 'New Contact: '.$_POST['subject'], $_POST['message'], 'From: '.$_POST['email']);
header('Location: /contact.php?sent=1');
exit;
