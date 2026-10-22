<?php
// Puts the demo CRM back to its starting point: demo opportunity at Quotation, no Booking Refund alerts.
require __DIR__ . '/bootstrap.php';

$db = $GLOBALS['db'];
$db->query("UPDATE alerts SET deleted = 1 WHERE name = 'Booking Refund'");
$db->query("UPDATE opportunities SET deleted = 1 WHERE name LIKE 'T_ %'");
$id = $db->getOne("SELECT id FROM opportunities WHERE name = 'GCR DEMO TEST OPP12' AND deleted = 0");
if ($id) {
    // Direct update so no hook fires while resetting.
    $db->query("UPDATE opportunities SET sales_stage = 'Quotation' WHERE id = '$id'");
}
echo json_encode(['reset' => true]) . "\n";
