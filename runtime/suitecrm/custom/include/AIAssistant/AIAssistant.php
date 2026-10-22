<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

/**
 * AI Assistant for Opportunities: summary, next action, follow-up email, and a list of deals needing attention.
 * Uses Claude when ANTHROPIC_API_KEY is set; otherwise answers with rules over the same CRM data ("offline" mode).
 */
class AIAssistant
{
    const MODEL = 'claude-sonnet-5-5';

    // What the salesperson should do next, per sales stage.
    private static $stageActions = array(
        'Prospecting' => 'Qualify the lead and confirm what the customer is looking for.',
        'Follow Up' => 'Contact the customer by phone.',
        'Test Drive' => "Confirm the customer's test drive appointment.",
        'Quotation' => 'Follow up with the customer regarding the quotation.',
        'Follow up 2' => 'Follow up again and ask whether the customer is ready to book.',
        'Booking' => 'Check whether the required booking information is complete.',
        'Pending Booking Refund' => 'Check the refund request and contact the customer.',
        'Booking Refund' => 'Confirm the booking refund has been processed.',
        'Bank Loan Submission' => 'Check the loan submission status with the bank.',
        'LOU' => 'Confirm the Letter of Undertaking (LOU) has been received.',
        'Allocation Complete' => 'Tell the customer the vehicle is allocated and arrange the downpayment.',
        'Full Downpayment' => 'Confirm the full downpayment has been received.',
        'Issue Downpayment Receipt' => 'Issue the downpayment receipt to the customer.',
        'Registration' => 'Check the vehicle registration progress.',
        'Delivery' => 'Arrange the delivery date with the customer.',
        'Finance Disbursements' => 'Confirm the bank has disbursed the loan.',
        'Completed' => 'Thank the customer and ask for a review or referral.',
    );

    // Stage wording used inside emails.
    private static $stageSubjects = array(
        'Prospecting' => 'Your Vehicle Enquiry',
        'Follow Up' => 'Follow-up Regarding Your Vehicle Enquiry',
        'Test Drive' => 'Your Test Drive',
        'Quotation' => 'Follow-up on Your Quotation',
        'Follow up 2' => 'Checking In on Your Vehicle Enquiry',
        'Booking' => 'Completing Your Booking',
        'Bank Loan Submission' => 'Update on Your Loan Application',
        'LOU' => 'Update on Your Loan Application',
        'Allocation Complete' => 'Your Vehicle Has Been Allocated',
        'Full Downpayment' => 'Your Downpayment',
        'Issue Downpayment Receipt' => 'Your Downpayment Receipt',
        'Registration' => 'Your Vehicle Registration',
        'Delivery' => 'Arranging Your Vehicle Delivery',
        'Finance Disbursements' => 'Your Loan Disbursement',
        'Completed' => 'Thank You for Choosing Us',
    );

    public static function mode()
    {
        return getenv('ANTHROPIC_API_KEY') ? 'claude' : 'offline';
    }

    // ---- Data -----------------------------------------------------------------------------------------------

    public static function collect($id)
    {
        $opp = BeanFactory::getBean('Opportunities', $id);
        if (!$opp || empty($opp->id)) {
            return null;
        }
        $db = $GLOBALS['db'];
        $now = time();
        $qid = $db->quote($opp->id);

        $contact = null;
        $row = $db->fetchByAssoc($db->query(
            "SELECT c.id FROM contacts c JOIN opportunities_contacts oc ON oc.contact_id = c.id AND oc.deleted = 0
             WHERE oc.opportunity_id = '$qid' AND c.deleted = 0 ORDER BY oc.date_modified LIMIT 1"
        ));
        if ($row) {
            $contact = BeanFactory::getBean('Contacts', $row['id']);
        }
        $account = $db->fetchByAssoc($db->query(
            "SELECT a.name FROM accounts a JOIN accounts_opportunities ao ON ao.account_id = a.id AND ao.deleted = 0
             WHERE ao.opportunity_id = '$qid' AND a.deleted = 0 LIMIT 1"
        ));

        $activities = array();
        $queries = array(
            'Call' => "SELECT name, status, date_start AS at FROM calls WHERE parent_type = 'Opportunities' AND parent_id = '$qid' AND deleted = 0",
            'Meeting' => "SELECT name, status, date_start AS at FROM meetings WHERE parent_type = 'Opportunities' AND parent_id = '$qid' AND deleted = 0",
            'Task' => "SELECT name, status, date_due AS at FROM tasks WHERE parent_type = 'Opportunities' AND parent_id = '$qid' AND deleted = 0",
            'Note' => "SELECT name, 'Held' AS status, date_entered AS at FROM notes WHERE parent_type = 'Opportunities' AND parent_id = '$qid' AND deleted = 0",
        );
        foreach ($queries as $type => $sql) {
            $res = $db->query($sql);
            while ($r = $db->fetchByAssoc($res)) {
                $ts = $r['at'] ? strtotime($r['at'] . ' UTC') : null;
                $activities[] = array('type' => $type, 'name' => $r['name'], 'status' => $r['status'], 'ts' => $ts);
            }
        }
        usort($activities, function ($a, $b) {
            return ($b['ts'] ?: 0) - ($a['ts'] ?: 0);
        });

        $lastDays = null;
        $lastName = null;
        $upcoming = null;
        $testDriveDone = false;
        $quotationSentDays = null;
        foreach ($activities as $a) {
            $done = $a['ts'] && $a['ts'] <= $now && !in_array($a['status'], array('Planned', 'Not Started'), true);
            if ($done && $lastDays === null) {
                $lastDays = (int)floor(($now - $a['ts']) / 86400);
                $lastName = $a['name'];
            }
            if ($a['ts'] && $a['ts'] > $now && ($upcoming === null || $a['ts'] < $upcoming['ts'])) {
                $upcoming = $a;
            }
            if ($done && $a['type'] === 'Meeting' && stripos($a['name'], 'test drive') !== false) {
                $testDriveDone = true;
            }
            if ($done && stripos($a['name'], 'quotation') !== false && $quotationSentDays === null) {
                $quotationSentDays = (int)floor(($now - $a['ts']) / 86400);
            }
        }

        $owner = $opp->assigned_user_id ? BeanFactory::getBean('Users', $opp->assigned_user_id) : null;
        $createdTs = strtotime($opp->date_entered . ' UTC');
        $currencySymbol = 'RM';

        return array(
            'id' => $opp->id,
            'name' => $opp->name,
            'customer' => $contact ? trim($contact->first_name . ' ' . $contact->last_name) : ($account ? $account['name'] : $opp->name),
            'phone' => $contact ? ($contact->phone_mobile ?: $contact->phone_work) : '',
            'email' => $contact ? $contact->email1 : '',
            'product' => isset($opp->product_c) ? trim($opp->product_c) : '',
            'amount' => (float)$opp->amount,
            'currency' => $currencySymbol,
            'stage' => $opp->sales_stage,
            'closeDate' => $opp->date_closed,
            'description' => trim((string)$opp->description),
            'owner' => $owner ? trim($owner->first_name . ' ' . $owner->last_name) : '',
            'daysSinceCreated' => $createdTs ? (int)floor(($now - $createdTs) / 86400) : null,
            'activities' => array_map(function ($a) use ($now) {
                return array('type' => $a['type'], 'name' => $a['name'], 'status' => $a['status'],
                    'daysAgo' => $a['ts'] ? (int)floor(($now - $a['ts']) / 86400) : null,
                    'date' => $a['ts'] ? self::localTime($a['ts']) : null);
            }, array_slice($activities, 0, 8)),
            'activityCount' => count($activities),
            'lastActivityDays' => $lastDays,
            'lastActivityName' => $lastName,
            'upcoming' => $upcoming ? array('type' => $upcoming['type'], 'name' => $upcoming['name'], 'date' => self::localTime($upcoming['ts'])) : null,
            'testDriveDone' => $testDriveDone,
            'quotationSentDays' => $quotationSentDays,
        );
    }

    private static function money($f)
    {
        return $f['currency'] . number_format($f['amount'], 2);
    }

    private static function productText($f)
    {
        if ($f['product'] === '') {
            return 'a vehicle';
        }
        return (preg_match('/^[aeiou]/i', $f['product']) ? 'an ' : 'a ') . $f['product'];
    }

    private static function ago($days)
    {
        return $days === 0 ? 'today' : ($days === 1 ? 'yesterday' : $days . ' days ago');
    }

    // Dealership staff work in Malaysia time; the database stores UTC.
    private static function localTime($ts)
    {
        $d = new DateTime('@' . $ts);
        $d->setTimezone(new DateTimeZone('Asia/Kuala_Lumpur'));
        return $d->format('d M Y, g:i A');
    }

    // Short facts shown as chips above the summary.
    public static function chips($f)
    {
        return array(
            'customer' => $f['customer'],
            'email' => $f['email'],
            'vehicle' => $f['product'] !== '' ? $f['product'] : '-',
            'budget' => $f['amount'] > 0 ? $f['currency'] . number_format($f['amount'], 0) : '-',
            'stage' => $f['stage'],
            'last' => $f['lastActivityDays'] === null ? 'No activity yet' : ucfirst(self::ago($f['lastActivityDays'])),
        );
    }

    // ---- Summary + recommendation ---------------------------------------------------------------------------

    public static function analyze($f)
    {
        $rules = self::analyzeByRules($f);
        if (self::mode() === 'claude') {
            $ai = self::askClaude(
                'You help car-dealership salespeople. From the CRM facts, reply with JSON only: ' .
                '{"summary": "2-4 short plain sentences about the customer, vehicle, budget, stage and recent activity", ' .
                '"action": "one short imperative sentence", "reason": "one short sentence based on the facts"}. ' .
                'Use only the facts given. Do not invent dates, prices or promises.',
                json_encode(self::forPrompt($f))
            );
            if ($ai && !empty($ai['summary']) && !empty($ai['action']) && !empty($ai['reason'])) {
                $rules['summary'] = $ai['summary'];
                $rules['action'] = $ai['action'];
                $rules['reason'] = $ai['reason'];
                $rules['source'] = 'claude';
            }
        }
        return $rules;
    }

    private static function analyzeByRules($f)
    {
        $stage = $f['stage'];

        // Summary
        $s = array();
        $line = $f['customer'] . ' is interested in ' . self::productText($f);
        if ($f['amount'] > 0) {
            $line .= ' with an estimated budget of ' . $f['currency'] . number_format($f['amount'], 0);
        }
        $s[] = $line . '.';
        $s[] = 'The opportunity is currently in the ' . $stage . ' stage.';
        if ($f['activityCount'] === 0 || $f['lastActivityDays'] === null) {
            $s[] = 'There has been no recent customer activity.';
        } else {
            $s[] = 'The last activity was ' . self::ago($f['lastActivityDays']) . ' (' . $f['lastActivityName'] . ').';
        }
        if ($f['testDriveDone']) {
            $s[] = 'A test drive has been completed.';
        } elseif ($f['upcoming'] && stripos($f['upcoming']['name'], 'test drive') !== false) {
            $s[] = 'A test drive is scheduled for ' . $f['upcoming']['date'] . '.';
        }
        if ($f['closeDate'] && strtotime($f['closeDate']) < time()) {
            $s[] = 'The expected close date (' . $f['closeDate'] . ') has passed.';
        }

        // Next action
        $action = isset(self::$stageActions[$stage]) ? self::$stageActions[$stage] : 'Review the opportunity and contact the customer.';
        if ($stage === 'Test Drive' && $f['testDriveDone']) {
            $action = 'Follow up on the completed test drive and offer a quotation.';
        } elseif ($stage === 'Test Drive' && !$f['upcoming']) {
            $action = 'Book a test drive appointment with the customer.';
        }

        // Reason + priority
        $days = $f['lastActivityDays'];
        $priority = 'low';
        if ($days === null) {
            $age = $f['daysSinceCreated'];
            $reason = 'No activity has been recorded' . ($age !== null ? ' since this opportunity was created ' . $age . ' days ago.' : '.');
            $priority = ($age !== null && $age >= 5) ? 'high' : 'medium';
        } elseif ($stage === 'Quotation' && $f['quotationSentDays'] !== null && $days >= 3) {
            $reason = 'The quotation was sent ' . $f['quotationSentDays'] . ' days ago and there is no recent customer activity.';
            $priority = 'high';
        } elseif ($days >= 7) {
            $reason = 'The last activity was ' . $days . ' days ago, so the customer may be losing interest.';
            $priority = 'high';
        } elseif ($days >= 3) {
            $reason = 'The last activity was ' . $days . ' days ago.';
            $priority = 'medium';
        } else {
            $reason = 'The last activity was ' . self::ago($days) . ', so the deal is moving.';
        }
        if ($f['upcoming'] && $priority === 'low') {
            $reason .= ' Upcoming: ' . $f['upcoming']['name'] . ' on ' . $f['upcoming']['date'] . '.';
        }
        $whenToAct = array('high' => 'today', 'medium' => 'within 2 days', 'low' => 'as planned');

        return array(
            'summary' => implode(' ', $s),
            'action' => $action,
            'reason' => $reason,
            'priority' => $priority,
            'when' => $whenToAct[$priority],
            'source' => 'rules',
        );
    }

    // ---- Follow-up email ------------------------------------------------------------------------------------

    public static function email($f, $variant = 0)
    {
        $email = self::emailByRules($f, (int)$variant);
        if (self::mode() === 'claude') {
            $ai = self::askClaude(
                'You write follow-up emails for a car-dealership salesperson. Reply with JSON only: {"subject": "...", "body": "..."}. ' .
                'The body starts with "Dear <customer name>," and has a short friendly follow-up fitted to the current sales stage, ' .
                'mentions the vehicle and, where relevant, the quoted amount, ends with a professional closing and the salesperson name. ' .
                'Use only the facts given. Do not invent prices, discounts, dates or promises. Keep it under 130 words. ' .
                'Write a different wording from earlier versions when the variant number changes.',
                json_encode(array('variant' => (int)$variant, 'facts' => self::forPrompt($f)))
            );
            if ($ai && !empty($ai['subject']) && !empty($ai['body'])) {
                $email = array('subject' => $ai['subject'], 'body' => $ai['body'], 'source' => 'claude');
            }
        }
        return $email;
    }

    private static function emailByRules($f, $variant)
    {
        $stage = $f['stage'];
        $product = $f['product'] !== '' ? $f['product'] : 'your chosen vehicle';
        $subject = isset(self::$stageSubjects[$stage]) ? self::$stageSubjects[$stage] : 'Following Up on Your Enquiry';
        if ($stage === 'Quotation' && $f['product'] !== '') {
            $subject = 'Follow-up on Your ' . $f['product'] . ' Quotation';
        } elseif ($stage === 'Test Drive' && $f['product'] !== '') {
            $subject = 'Your ' . $f['product'] . ' Test Drive';
        } elseif ($stage === 'Follow Up' && $f['product'] !== '') {
            $subject = 'Follow-up Regarding Your ' . $f['product'] . ' Enquiry';
        }

        $openers = array(
            'Thank you for your interest in the ' . $product . '.',
            'It was a pleasure speaking with you about the ' . $product . '.',
            'Thank you for considering us for your next vehicle, the ' . $product . '.',
        );

        $quoted = $f['amount'] > 0 ? ' (' . self::money($f) . ')' : '';
        $stageText = array(
            'Prospecting' => 'We would like to understand what you are looking for so we can recommend the right model for you. Could we arrange a short call?',
            'Follow Up' => 'We would like to follow up on your enquiry and see if you need any additional information. Please let us know if you would like a quotation or to arrange a test drive.',
            'Test Drive' => $f['upcoming'] && stripos($f['upcoming']['name'], 'test drive') !== false
                ? 'This is a friendly reminder about your test drive on ' . $f['upcoming']['date'] . '. Please let us know if the time still suits you, or if you would like to change it.'
                : ($f['testDriveDone']
                    ? 'We hope you enjoyed the test drive. We would be happy to prepare a quotation for you, or answer any questions you may have.'
                    : 'We would be happy to arrange a test drive at a time that suits you. Please let us know when you are available.'),
            'Quotation' => 'We would like to follow up on the quotation' . $quoted . ' we shared with you. Please let us know if you have any questions about the pricing or financing, or if you would like to proceed with a booking.',
            'Follow up 2' => 'We are checking in to see whether you have made a decision. Please let us know if there is anything we can clarify, or if you are ready to book.',
            'Booking' => 'Thank you for your booking. To move forward, we need to confirm that all booking information and documents are complete. Please let us know if anything is missing on your side.',
            'Bank Loan Submission' => 'Your loan application has been submitted to the bank. We will update you as soon as we receive their response; please contact us if you need to provide any further documents.',
            'LOU' => 'We are waiting for the bank\'s Letter of Undertaking. We will update you as soon as it is received.',
            'Allocation Complete' => 'We are pleased to inform you that your vehicle has been allocated. Please let us know a convenient time to arrange the downpayment.',
            'Full Downpayment' => 'We have noted your downpayment. Please let us know if you have any questions about the next steps.',
            'Issue Downpayment Receipt' => 'Your downpayment receipt is ready. Please contact us if you would like us to resend it.',
            'Registration' => 'Your vehicle registration is in progress. We will keep you updated on the timeline.',
            'Delivery' => 'We are preparing for your vehicle delivery. Please let us know which date and time suit you best.',
            'Finance Disbursements' => 'We are following up with the bank on the loan disbursement and will keep you informed.',
            'Completed' => 'Thank you for choosing us. We hope you are enjoying your vehicle; if you know anyone looking for a car, we would be glad to help them too.',
        );
        $middle = isset($stageText[$stage]) ? $stageText[$stage] : 'We would like to follow up on your enquiry. Please let us know how we can help.';

        $closers = array(
            'Thank you, and we look forward to hearing from you.',
            'Please feel free to reply to this email or call us at any time.',
            'We look forward to assisting you.',
        );
        $name = $f['customer'];
        $sig = ($f['owner'] ? $f['owner'] : 'Your Sales Consultant') . "\nCS Euro Auto Sdn. Bhd.";
        $body = "Dear $name,\n\n" . $openers[$variant % 3] . "\n\n" . $middle . "\n\n" . $closers[$variant % 3] . "\n\nBest regards,\n" . $sig;

        return array('subject' => $subject, 'body' => $body, 'source' => 'rules');
    }

    // ---- Deals needing attention ----------------------------------------------------------------------------

    public static function suggestions($limit = 10)
    {
        $db = $GLOBALS['db'];
        $res = $db->query(
            "SELECT id FROM opportunities WHERE deleted = 0 AND sales_stage NOT IN ('Completed', 'Booking Refund') ORDER BY date_modified DESC LIMIT 60"
        );
        $items = array();
        while ($row = $db->fetchByAssoc($res)) {
            $f = self::collect($row['id']);
            if (!$f) {
                continue;
            }
            $days = $f['lastActivityDays'];
            $age = $f['daysSinceCreated'];
            $stage = $f['stage'];
            $level = null;
            $text = null;
            if ($days === null && $age !== null && $age >= 7) {
                $level = 'red';
                $text = 'Follow-up overdue (no activity for ' . $age . ' days)';
            } elseif (in_array($stage, array('Quotation', 'Follow up 2'), true) && $f['quotationSentDays'] !== null && $days !== null && $days >= 3) {
                $level = 'red';
                $text = 'Quotation sent, no response (' . $days . ' days)';
            } elseif ($days !== null && $days >= 7) {
                $level = 'red';
                $text = 'Follow-up overdue (' . $days . ' days since last activity)';
            } elseif ($f['testDriveDone'] && $stage === 'Test Drive') {
                $level = 'yellow';
                $text = 'Test drive completed - send a quotation';
            } elseif ($f['upcoming'] && stripos($f['upcoming']['name'], 'test drive') !== false) {
                $level = 'yellow';
                $text = 'Test drive on ' . $f['upcoming']['date'] . ' - confirm appointment';
            } elseif ($days === null && $age !== null && $age >= 3) {
                $level = 'yellow';
                $text = 'No contact yet (' . $age . ' days)';
            }
            if ($level) {
                $items[] = array('level' => $level, 'customer' => $f['customer'], 'text' => $text, 'id' => $f['id'], 'product' => $f['product']);
            }
        }
        usort($items, function ($a, $b) {
            return strcmp($a['level'] === 'red' ? '0' : '1', $b['level'] === 'red' ? '0' : '1');
        });
        return array_slice($items, 0, $limit);
    }

    // ---- Claude ---------------------------------------------------------------------------------------------

    private static function forPrompt($f)
    {
        unset($f['id'], $f['email'], $f['phone']);
        return $f;
    }

    private static function askClaude($system, $user)
    {
        $key = getenv('ANTHROPIC_API_KEY');
        if (!$key || !function_exists('curl_init')) {
            return null;
        }
        $ch = curl_init('https://api.anthropic.com/v1/messages');
        curl_setopt_array($ch, array(
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 25,
            CURLOPT_HTTPHEADER => array('content-type: application/json', 'x-api-key: ' . $key, 'anthropic-version: 2023-06-01'),
            CURLOPT_POSTFIELDS => json_encode(array(
                'model' => self::MODEL,
                'max_tokens' => 900,
                'system' => $system,
                'messages' => array(array('role' => 'user', 'content' => $user)),
            )),
        ));
        $out = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($out === false || $status !== 200) {
            $GLOBALS['log']->error('AIAssistant: Claude call failed with HTTP ' . $status);
            return null;
        }
        $data = json_decode($out, true);
        $text = '';
        foreach (isset($data['content']) ? $data['content'] : array() as $block) {
            $text .= isset($block['text']) ? $block['text'] : '';
        }
        if (preg_match('/\{.*\}/s', $text, $m)) {
            return json_decode($m[0], true);
        }
        return null;
    }
}
