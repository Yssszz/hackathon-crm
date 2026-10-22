<?php
// Prints what the AI Assistant says for each demo opportunity (offline rules unless ANTHROPIC_API_KEY is set).
require __DIR__ . '/bootstrap.php';
require_once 'custom/include/AIAssistant/AIAssistant.php';

$db = $GLOBALS['db'];
$res = $db->query("SELECT id FROM opportunities WHERE deleted = 0 AND name NOT LIKE 'T_ %' ORDER BY name");
$only = isset($argv[1]) ? $argv[1] : null;
echo 'mode: ' . AIAssistant::mode() . "\n";
while ($row = $db->fetchByAssoc($res)) {
    $f = AIAssistant::collect($row['id']);
    if ($only && stripos($f['name'], $only) === false) {
        continue;
    }
    $a = AIAssistant::analyze($f);
    echo "\n=== {$f['name']} [{$f['stage']}] ===\n";
    echo "SUMMARY: {$a['summary']}\nACTION: {$a['action']} ({$a['priority']}, {$a['when']})\nREASON: {$a['reason']}\n";
    if ($only) {
        $e = AIAssistant::email($f, 0);
        echo "\nEMAIL v0\nSubject: {$e['subject']}\n{$e['body']}\n";
        $e = AIAssistant::email($f, 1);
        echo "\nEMAIL v1\nSubject: {$e['subject']}\n{$e['body']}\n";
    }
}
echo "\n=== SUGGESTIONS ===\n";
foreach (AIAssistant::suggestions() as $s) {
    echo ($s['level'] === 'red' ? 'RED    ' : 'YELLOW ') . $s['customer'] . ' - ' . $s['text'] . "\n";
}
