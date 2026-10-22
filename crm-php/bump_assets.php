<?php
// Bumps SuiteCRM's "custom JS/CSS version". SuiteCRM puts this number in the ?v= of every theme stylesheet and script,
// so changing it makes browsers download the new files instead of reusing an old cached copy.
require __DIR__ . '/bootstrap.php';
require_once 'modules/Configurator/Configurator.php';

$cfg = new Configurator();
$cfg->loadConfig();
$current = isset($cfg->config['js_custom_version']) ? (int)$cfg->config['js_custom_version'] : 0;
$cfg->config['js_custom_version'] = $current + 1;
$cfg->handleOverride();
echo 'js_custom_version: ' . $current . ' -> ' . ($current + 1) . "\n";
