<?php
// Seeds the demo data: a branch manager, a sales consultant who reports to them, and the demo opportunity.
require __DIR__ . '/bootstrap.php';

function ensureUser($name, $first, $last, $reportsTo = '')
{
    $id = $GLOBALS['db']->getOne("SELECT id FROM users WHERE user_name = '$name' AND deleted = 0");
    $u = $id ? BeanFactory::getBean('Users', $id) : BeanFactory::newBean('Users');
    $u->user_name = $name;
    $u->first_name = $first;
    $u->last_name = $last;
    $u->status = 'Active';
    $u->employee_status = 'Active';
    $u->is_admin = 0;
    $u->reports_to_id = $reportsTo;
    $u->user_hash = User::getPasswordHash('Demo#2026');
    $u->save();
    return $u->id;
}

$manager = ensureUser('bm_butterworth', 'Branch', 'Manager Butterworth');
$consultant = ensureUser('gcr_dev3', 'GCR', 'Dev3', $manager);

$id = $GLOBALS['db']->getOne("SELECT id FROM opportunities WHERE name = 'GCR DEMO TEST OPP12' AND deleted = 0");
$opp = $id ? BeanFactory::getBean('Opportunities', $id) : BeanFactory::newBean('Opportunities');
$opp->name = 'GCR DEMO TEST OPP12';
$opp->sales_stage = 'Quotation';
$opp->amount = 154911;
$opp->currency_id = '-99';
$opp->date_closed = date('Y-m-d', strtotime('+30 days'));
$opp->assigned_user_id = $consultant;
$opp->save();
echo json_encode(['manager' => $manager, 'consultant' => $consultant, 'opportunity' => $opp->id]) . "\n";
