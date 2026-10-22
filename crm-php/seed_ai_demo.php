<?php
// Seeds realistic dealership customers, opportunities and activities for the AI Assistant demo.
// Safe to re-run: it rebuilds the activities of the demo opportunities each time.
require __DIR__ . '/bootstrap.php';

$db = $GLOBALS['db'];
$consultant = $db->getOne("SELECT id FROM users WHERE user_name = 'gcr_dev3' AND deleted = 0");
if (!$consultant) {
    die("Run seed.php first\n");
}

// name, product, amount, stage, created N days ago, description, activities [type, name, status, N days ago (negative = in the future), hours offset]
$deals = array(
    array('Tan Wei Ming', 'Peugeot 5008 Allure Plus SUV', 154911, 'Quotation', 18, 'GCR DEMO TEST OPP12',
        'Customer prefers pearl white. Wants to compare the monthly instalment with Maybank and HLB. Has a trade-in (2016 Honda City). Quotation sent via WhatsApp, waiting for the customer to decide.',
        array(array('Call', 'Enquiry call', 'Held', 14), array('Meeting', 'Test drive - Peugeot 5008', 'Held', 11), array('Note', 'Quotation sent via WhatsApp', 'Held', 6))),
    array('Nurul Huda', 'Proton X50', 100000, 'Follow Up', 9, 'Nurul Huda - Proton X50',
        'Walk-in enquiry. Budget around RM100,000. Interested in the Flagship variant.', array()),
    array('Siti Aminah', 'Hyundai Ioniq 5', 189000, 'Test Drive', 5, 'Siti Aminah - Hyundai Ioniq 5',
        'Interested in the long-range model. Wants to check charging options at home.',
        array(array('Call', 'Enquiry call', 'Held', 3), array('Meeting', 'Test drive - Hyundai Ioniq 5', 'Planned', -1))),
    array('Lim Chee Keong', 'GWM Cannon', 125800, 'Follow Up', 12, 'Lim Chee Keong - GWM Cannon',
        'Contractor looking for a double-cab pickup for work use.', array()),
    array('Raju a/l Muthu', 'Hyundai Santa Fe', 178888, 'Quotation', 10, 'Raju - Hyundai Santa Fe',
        'Family of five. Asked for a discount on accessories.',
        array(array('Call', 'Discussed quotation and accessories', 'Held', 1))),
    array('Wong Mei Ling', 'Peugeot 3008', 160000, 'Test Drive', 8, 'Wong Mei Ling - Peugeot 3008',
        'Compared with the Honda CR-V. Liked the interior.',
        array(array('Meeting', 'Test drive - Peugeot 3008', 'Held', 2))),
    array('Faizal bin Hamid', 'Hyundai Santa Fe', 182000, 'Booking', 20, 'Faizal - Hyundai Santa Fe',
        'Booking fee RM1,000 paid. Waiting for the IC copy and payment proof.',
        array(array('Note', 'Booking form received - IC photo pending', 'Held', 2))),
);

function backdate($table, $id, $field, $daysAgo)
{
    $when = gmdate('Y-m-d H:i:s', time() - $daysAgo * 86400);
    $GLOBALS['db']->query("UPDATE $table SET $field = '$when' WHERE id = '$id'");
}

foreach ($deals as $d) {
    list($person, $product, $amount, $stage, $created, $oppName, $desc, $acts) = $d;
    $parts = explode(' ', $person, 2);

    // Contact + account
    $contactId = $db->getOne("SELECT id FROM contacts WHERE last_name = '" . $db->quote($parts[1]) . "' AND first_name = '" . $db->quote($parts[0]) . "' AND deleted = 0");
    $contact = $contactId ? BeanFactory::getBean('Contacts', $contactId) : BeanFactory::newBean('Contacts');
    $contact->first_name = $parts[0];
    $contact->last_name = $parts[1];
    $contact->phone_mobile = '01' . rand(1, 9) . '-' . rand(2000000, 9999999);
    $contact->email1 = strtolower(preg_replace('/[^a-z]/i', '', $parts[0])) . '@example.com';
    $contact->assigned_user_id = $consultant;
    $contact->save();

    $accId = $db->getOne("SELECT id FROM accounts WHERE name = '" . $db->quote($person) . "' AND deleted = 0");
    $account = $accId ? BeanFactory::getBean('Accounts', $accId) : BeanFactory::newBean('Accounts');
    $account->name = $person;
    $account->assigned_user_id = $consultant;
    $account->save();

    // Opportunity
    $oppId = $db->getOne("SELECT id FROM opportunities WHERE name = '" . $db->quote($oppName) . "' AND deleted = 0");
    $opp = $oppId ? BeanFactory::getBean('Opportunities', $oppId) : BeanFactory::newBean('Opportunities');
    $opp->name = $oppName;
    $opp->sales_stage = $stage;
    $opp->amount = $amount;
    $opp->currency_id = '-99';
    $opp->product_c = $product;
    $opp->description = $desc;
    $opp->date_closed = date('Y-m-d', strtotime('+21 days'));
    $opp->assigned_user_id = $consultant;
    $opp->account_id = $account->id;
    $opp->save();
    backdate('opportunities', $opp->id, 'date_entered', $created);
    backdate('opportunities', $opp->id, 'date_modified', $created);

    $opp->load_relationship('contacts');
    $opp->contacts->add($contact->id);

    // Activities: clear and recreate so re-running keeps the dates relative to today.
    foreach (array('calls', 'meetings', 'tasks', 'notes') as $t) {
        $db->query("UPDATE $t SET deleted = 1 WHERE parent_type = 'Opportunities' AND parent_id = '{$opp->id}'");
    }
    foreach ($acts as $a) {
        list($type, $name, $status, $daysAgo) = $a;
        $bean = BeanFactory::newBean($type === 'Call' ? 'Calls' : ($type === 'Meeting' ? 'Meetings' : 'Notes'));
        $bean->name = $name;
        $bean->parent_type = 'Opportunities';
        $bean->parent_id = $opp->id;
        $bean->assigned_user_id = $consultant;
        if ($type !== 'Note') {
            $bean->status = $status;
            $bean->date_start = gmdate('Y-m-d H:i:s', time() - $daysAgo * 86400);
            $bean->duration_hours = 0;
            $bean->duration_minutes = 30;
            if ($type === 'Call') {
                $bean->direction = 'Outbound';
            }
        }
        $bean->save();
        $table = $type === 'Call' ? 'calls' : ($type === 'Meeting' ? 'meetings' : 'notes');
        if ($type === 'Note') {
            backdate($table, $bean->id, 'date_entered', $daysAgo);
        }
    }
    echo "seeded: $oppName ($stage)\n";
}
