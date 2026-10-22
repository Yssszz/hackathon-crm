<?php
require __DIR__ . '/bootstrap.php';
$opp = BeanFactory::newBean('Opportunities');
echo json_encode(['user' => $current_user->user_name, 'bean' => get_class($opp), 'stages' => count($GLOBALS['app_list_strings']['sales_stage_dom'])]) . "\n";
