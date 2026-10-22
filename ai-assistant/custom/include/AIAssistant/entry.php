<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

// Entry point: index.php?entryPoint=aiAssistant&op=analyze|email|suggestions&record=<opportunity id>
require_once 'custom/include/AIAssistant/AIAssistant.php';

header('Content-Type: application/json');

function aiRespond($status, $body)
{
    http_response_code($status);
    echo json_encode($body);
    sugar_cleanup(true);
}

$op = isset($_REQUEST['op']) ? $_REQUEST['op'] : '';

if ($op === 'suggestions') {
    aiRespond(200, array('mode' => AIAssistant::mode(), 'items' => AIAssistant::suggestions()));
}

$record = isset($_REQUEST['record']) ? $_REQUEST['record'] : '';
if (!preg_match('/^[A-Za-z0-9\-]{1,36}$/', $record)) {
    aiRespond(400, array('error' => 'Missing or invalid record'));
}
$opp = BeanFactory::getBean('Opportunities', $record);
if (!$opp || empty($opp->id) || !$opp->ACLAccess('view')) {
    aiRespond(404, array('error' => 'Opportunity not found'));
}
$facts = AIAssistant::collect($record);

if ($op === 'analyze') {
    aiRespond(200, array('mode' => AIAssistant::mode(), 'facts' => AIAssistant::chips($facts), 'result' => AIAssistant::analyze($facts)));
}
if ($op === 'email') {
    $variant = isset($_REQUEST['variant']) ? (int)$_REQUEST['variant'] : 0;
    aiRespond(200, array('mode' => AIAssistant::mode(), 'to' => $facts['email'], 'customer' => $facts['customer'], 'result' => AIAssistant::email($facts, $variant)));
}
aiRespond(400, array('error' => 'Unknown operation'));
