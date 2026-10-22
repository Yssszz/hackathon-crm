<?php
// Puts the "AI Follow-up Suggestions" dashlet at the top of a user's Home dashboard (first column of the first page).
require __DIR__ . '/bootstrap.php';

$userName = isset($argv[1]) ? $argv[1] : 'admin';
$userId = $GLOBALS['db']->getOne("SELECT id FROM users WHERE user_name = '" . $GLOBALS['db']->quote($userName) . "' AND deleted = 0");
$user = BeanFactory::getBean('Users', $userId);

$dashlets = $user->getPreference('dashlets', 'Home');
$pages = $user->getPreference('pages', 'Home');
if (!is_array($dashlets) || !is_array($pages)) {
    fwrite(STDERR, "$userName has no saved Home layout yet; log in once first.\n");
    exit(1);
}
foreach ($dashlets as $d) {
    if (isset($d['className']) && $d['className'] === 'AIFollowUpDashlet') {
        echo "already on the dashboard\n";
        exit(0);
    }
}

$id = create_guid();
$dashlets[$id] = array(
    'className' => 'AIFollowUpDashlet',
    'module' => 'Home',
    'fileLocation' => 'custom/modules/Home/Dashlets/AIFollowUpDashlet/AIFollowUpDashlet.php',
);
array_unshift($pages[0]['columns'][0]['dashlets'], $id);
$user->setPreference('dashlets', $dashlets, 0, 'Home');
$user->setPreference('pages', $pages, 0, 'Home');
$user->savePreferencesToDB();
echo "added dashlet $id for $userName\n";
