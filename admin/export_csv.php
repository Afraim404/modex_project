<?php
require_once '../includes/config.php';
require_once 'admin_helpers.php';
requireAdmin();

$db     = getDB();
$status = isset($_GET['status']) ? sanitize($_GET['status']) : '';

$where  = ['1=1'];
$params = [];
if ($status) { $where[] = 'o.status=?'; $params[] = $status; }
$w = implode(' AND ', $where);

$stmt = $db->prepare("SELECT o.order_number,o.customer_name,o.customer_phone,o.customer_email,o.delivery_address,o.district,o.division,CASE WHEN o.is_inside_dhaka=1 THEN 'Inside Dhaka' ELSE 'Outside Dhaka' END as zone,o.delivery_charge,o.special_note,o.status,o.created_at,GROUP_CONCAT(CONCAT(oi.product_name,' x',oi.quantity) ORDER BY oi.id SEPARATOR ' | ') as products,SUM(oi.price*oi.quantity) as ptotal,SUM(oi.price*oi.quantity)+o.delivery_charge as grand_total FROM orders o LEFT JOIN order_items oi ON o.id=oi.order_id WHERE $w GROUP BY o.id ORDER BY o.created_at DESC");
$stmt->execute($params);
$orders = $stmt->fetchAll();

$filename = 'modex_orders_' . date('Y-m-d') . ($status?"_$status":'') . '.csv';

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Pragma: no-cache');

$out = fopen('php://output', 'w');
fputs($out, "\xEF\xBB\xBF"); // BOM for Excel

fputcsv($out, ['Order #','Name','Phone','Email','Address','District','Division','Zone','Products','Products Total','Delivery','Grand Total','Status','Date','Note']);
foreach ($orders as $o) {
    fputcsv($out, [
        $o['order_number'], $o['customer_name'], $o['customer_phone'], $o['customer_email']??'',
        $o['delivery_address'], $o['district'], $o['division']??'', $o['zone'],
        $o['products']??'', number_format($o['ptotal']??0,2), number_format($o['delivery_charge'],2),
        number_format($o['grand_total']??0,2), ucfirst($o['status']),
        date('d M Y H:i', strtotime($o['created_at'])), $o['special_note']??''
    ]);
}
fclose($out);
exit;
