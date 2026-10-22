<?php
// Gives the first Home dashboard tab a plain title instead of "SuiteCRM Dashboard".
require __DIR__ . '/bootstrap.php';

$userName = isset($argv[1]) ? $argv[1] : 'admin';
$title = isset($argv[2]) ? $argv[2] : 'Sales Overview';
$userId = $GLOBALS['db']->getOne("SELECT id FROM users WHERE user_name = '" . $GLOBALS['db']->quote($userName) . "' AND deleted = 0");
$user = BeanFactory::getBean('Users', $userId);
$pages = $user->getPreference('pages', 'Home');
if (!is_array($pages) || !isset($pages[0])) {
    fwrite(STDERR, "$userName has no saved Home layout yet\n");
    exit(1);
}
$pages[0]['pageTitle'] = $title;
$user->setPreference('pages', $pages, 0, 'Home');
$user->savePreferencesToDB();
echo "renamed first dashboard tab for $userName to \"$title\"\n";
