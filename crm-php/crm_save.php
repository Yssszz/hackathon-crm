<?php
// Saves a new sales stage on the demo opportunity through the real bean, so logic hooks fire like in the CRM.
require_once __DIR__ . '/crm_state.php';

$stage = isset($argv[1]) ? $argv[1] : '';
if (!array_key_exists($stage, $GLOBALS['app_list_strings']['sales_stage_dom'])) {
    fwrite(STDERR, "Unknown stage\n");
    exit(1);
}
$id = $GLOBALS['db']->getOne("SELECT id FROM opportunities WHERE name = 'GCR DEMO TEST OPP12' AND deleted = 0");
$opp = BeanFactory::getBean('Opportunities', $id);
$opp->sales_stage = $stage;
$opp->save();

echo json_encode(crmState()) . "\n";
