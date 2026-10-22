<?php
// Boots the real SuiteCRM from the command line so tests and seeds use the actual application code.
define('sugarEntry', true);
chdir(getenv('SUITECRM_ROOT') ?: __DIR__ . '/../runtime/suitecrm');
require_once 'include/entryPoint.php';
global $current_user;
$current_user = BeanFactory::getBean('Users', '1');
