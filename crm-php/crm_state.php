<?php
// Reads the demo opportunity and its Booking Refund alerts from the real CRM.
require_once __DIR__ . '/bootstrap.php';

function crmState()
{
    $db = $GLOBALS['db'];
    $id = $db->getOne("SELECT id FROM opportunities WHERE name = 'GCR DEMO TEST OPP12' AND deleted = 0");
    $opp = BeanFactory::getBean('Opportunities', $id);

    $alerts = array();
    $res = $db->query("SELECT a.description, u.user_name FROM alerts a JOIN users u ON u.id = a.assigned_user_id WHERE a.name = 'Booking Refund' AND a.description LIKE 'GCR DEMO TEST OPP12%' AND a.deleted = 0 ORDER BY a.date_entered");
    while ($row = $db->fetchByAssoc($res)) {
        $alerts[] = array('to' => $row['user_name'], 'message' => $row['description']);
    }

    return array(
        'opportunity' => array('id' => $opp->id, 'name' => $opp->name, 'stage' => $opp->sales_stage),
        'stages' => array_keys($GLOBALS['app_list_strings']['sales_stage_dom']),
        'notifications' => $alerts,
        'detailUrl' => 'index.php?module=Opportunities&action=DetailView&record=' . $opp->id,
    );
}

if (basename($_SERVER['SCRIPT_NAME']) === 'crm_state.php') {
    echo json_encode(crmState()) . "\n";
}
