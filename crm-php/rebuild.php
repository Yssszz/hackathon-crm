<?php
require __DIR__ . '/bootstrap.php';
require_once 'modules/Administration/QuickRepairAndRebuild.php';
$r = new RepairAndClear();
$r->repairAndClearAll(array('clearAll'), array(translate('LBL_ALL_MODULES')), false, false);
echo "rebuilt\n";
