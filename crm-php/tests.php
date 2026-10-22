<?php
// Test suite for the Booking Refund feature and the quotation maths, run against the real SuiteCRM.
// Prints one JSON line: {"results": [...], "passed": n, "failed": n, "stageCoverage": n, "stageTotal": n}
require __DIR__ . '/bootstrap.php';

$quotationFile = 'custom/modules/Opportunities/QuotationCalc.php';
if (file_exists($quotationFile)) {
    require_once $quotationFile;
}

$consultantId = $GLOBALS['db']->getOne("SELECT id FROM users WHERE user_name = 'gcr_dev3' AND deleted = 0");
$run = substr(uniqid(), -6);

function makeOpp($name, $stage, $consultantId)
{
    $opp = BeanFactory::newBean('Opportunities');
    $opp->name = $name;
    $opp->sales_stage = $stage;
    $opp->amount = 154911;
    $opp->currency_id = '-99';
    $opp->date_closed = date('Y-m-d', strtotime('+30 days'));
    $opp->assigned_user_id = $consultantId;
    $opp->save();
    return $opp;
}

function alertUsers($oppName)
{
    $names = array();
    $db = $GLOBALS['db'];
    $res = $db->query("SELECT u.user_name FROM alerts a JOIN users u ON u.id = a.assigned_user_id WHERE a.description LIKE '%" . $db->quote($oppName) . "%' AND a.deleted = 0");
    while ($row = $db->fetchByAssoc($res)) {
        $names[] = $row['user_name'];
    }
    return $names;
}

function fmt($n)
{
    return number_format($n, 2, '.', ',');
}

$tests = array(
    array('Notify sales consultant when stage becomes Booking Refund', array('Booking Refund'), function () use ($consultantId, $run) {
        $name = "T1 $run";
        $opp = makeOpp($name, 'Quotation', $consultantId);
        $opp->sales_stage = 'Booking Refund';
        $opp->save();
        $n = count(array_keys(alertUsers($name), 'gcr_dev3'));
        return array($n === 1, '1 notification', "$n notifications");
    }),
    array('Notify branch manager', array('Booking Refund'), function () use ($consultantId, $run) {
        $name = "T2 $run";
        $opp = makeOpp($name, 'Quotation', $consultantId);
        $opp->sales_stage = 'Booking Refund';
        $opp->save();
        $n = count(array_keys(alertUsers($name), 'bm_butterworth'));
        return array($n === 1, '1 notification', "$n notifications");
    }),
    array('No notice for other stages', array('Test Drive', 'Booking'), function () use ($consultantId, $run) {
        $name = "T3 $run";
        $opp = makeOpp($name, 'Quotation', $consultantId);
        foreach (array('Booking', 'Test Drive') as $stage) {
            $opp->sales_stage = $stage;
            $opp->save();
        }
        $n = count(alertUsers($name));
        return array($n === 0, '0 notifications', "$n notifications");
    }),
    array('No duplicate notice on re-save', array('Booking Refund'), function () use ($consultantId, $run) {
        $name = "T4 $run";
        $opp = makeOpp($name, 'Quotation', $consultantId);
        $opp->sales_stage = 'Booking Refund';
        $opp->save();
        $opp = BeanFactory::getBean('Opportunities', $opp->id);
        $opp->amount = 160000;
        $opp->save();
        $n = count(alertUsers($name));
        return array($n === 2, '2 notifications total', "$n notifications total");
    }),
    array('Every pipeline stage can be saved on an opportunity', array('Follow Up', 'Test Drive', 'Quotation', 'Booking', 'Booking Refund'), function () use ($consultantId, $run) {
        $stages = array_keys($GLOBALS['app_list_strings']['sales_stage_dom']);
        $bad = array();
        foreach ($stages as $stage) {
            $opp = makeOpp("T5 $run $stage", $stage, $consultantId);
            $saved = BeanFactory::getBean('Opportunities', $opp->id);
            if ($saved->sales_stage !== $stage) {
                $bad[] = $stage;
            }
        }
        return array(!$bad, count($stages) . ' stages saved', (count($stages) - count($bad)) . ' stages saved');
    }),
    array('Net selling price = price + fees - promotions', array('Quotation'), function () {
        if (!class_exists('QuotationCalc')) {
            return array(false, '154,911.00', 'QuotationCalc not found');
        }
        $q = array('vehiclePrice' => 158991, 'inspectionFee' => 20, 'adminFee' => 100, 'numberPlate' => 100,
            'colourEffect' => 150, 'brandPromotion' => 3500, 'csEuroPromotion' => 700, 'salesConsultantPromotion' => 250);
        $got = QuotationCalc::netSellingPrice($q);
        return array($got == 154911, fmt(154911), fmt($got));
    }),
    array('Stage change (Kanban drag) updates the stage', array('Test Drive', 'Quotation'), function () use ($consultantId, $run) {
        $opp = makeOpp("T7 $run", 'Quotation', $consultantId);
        $opp->sales_stage = 'Test Drive';
        $opp->save();
        $got = BeanFactory::getBean('Opportunities', $opp->id)->sales_stage;
        return array($got === 'Test Drive', 'Test Drive', $got);
    }),
    array('Stage list matches the Sales Stage dropdown', array(), function () {
        $stages = array_keys($GLOBALS['app_list_strings']['sales_stage_dom']);
        return array(count($stages) === 17 && $stages[0] === 'Follow Up', '17 stages, Follow Up first', count($stages) . ' stages, ' . $stages[0] . ' first');
    }),
);

$results = array();
$covered = array();
foreach ($tests as $t) {
    try {
        list($ok, $expected, $actual) = $t[2]();
    } catch (Throwable $e) {
        list($ok, $expected, $actual) = array(false, 'no error', $e->getMessage());
    }
    $results[] = array('name' => $t[0], 'stages' => $t[1], 'ok' => (bool)$ok, 'expected' => $expected, 'actual' => $actual);
    $covered = array_merge($covered, $t[1]);
}

// Remove the records the tests created, so the demo CRM stays clean.
$GLOBALS['db']->query("UPDATE opportunities SET deleted = 1 WHERE name LIKE 'T_ $run%'");
$GLOBALS['db']->query("UPDATE alerts SET deleted = 1 WHERE description LIKE 'T_ $run%'");

$passed = count(array_filter($results, function ($r) { return $r['ok']; }));
echo "\n" . json_encode(array(
    'results' => $results,
    'passed' => $passed,
    'failed' => count($results) - $passed,
    'stageCoverage' => count(array_unique($covered)),
    'stageTotal' => count($GLOBALS['app_list_strings']['sales_stage_dom']),
)) . "\n";
