// AI layer. Uses the Claude API when ANTHROPIC_API_KEY is set, otherwise recorded responses.
const MODEL = 'claude-sonnet-5-5';
const live = () => Boolean(process.env.ANTHROPIC_API_KEY);

async function ask(system, user) {
  const res = await fetch('https://api.anthropic.com/v1/messages', {
    method: 'POST',
    headers: {
      'content-type': 'application/json',
      'x-api-key': process.env.ANTHROPIC_API_KEY,
      'anthropic-version': '2023-06-01',
    },
    body: JSON.stringify({ model: MODEL, max_tokens: 2000, system, messages: [{ role: 'user', content: user }] }),
  });
  if (!res.ok) throw new Error(`Claude API ${res.status}`);
  const data = await res.json();
  const text = data.content.map((b) => b.text || '').join('');
  const json = text.match(/\{[\s\S]*\}/);
  if (!json) throw new Error('No JSON in reply');
  return JSON.parse(json[0]);
}

const CONTEXT = 'You help developers of a SuiteCRM-based car-dealership CRM (CS Euro Auto, Malaysia). ' +
  'Sales stages: Follow Up, Test Drive, Quotation, Follow up 2, Booking, Pending Booking Refund, Booking Refund, ' +
  'Bank Loan Submission, LOU, Allocation Complete, Full Downpayment, Issue Downpayment Receipt, Registration, ' +
  'Delivery, Finance Disbursements, Completed, Prospecting. Reply with one JSON object only.';

const PHP = `<?php
// custom/modules/Opportunities/BookingRefundNotify.php
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

class BookingRefundNotify
{
    // before_save: compare with the stage stored in the database (fetched_row is not reliable here).
    public function detect($bean, $event, $arguments)
    {
        $stored = empty($bean->id) ? null : $GLOBALS['db']->getOne(
            "SELECT sales_stage FROM opportunities WHERE id = '" . $GLOBALS['db']->quote($bean->id) . "' AND deleted = 0"
        );
        $bean->bookingRefundJustSet = $bean->sales_stage === 'Booking Refund' && $stored !== 'Booking Refund';
    }

    // after_save: notify only when detect() saw the stage change to Booking Refund.
    public function notify($bean, $event, $arguments)
    {
        if (empty($bean->bookingRefundJustSet)) {
            return;
        }
        $bean->bookingRefundJustSet = false;

        $message = $bean->name . ' moved to Booking Refund';
        $recipients = [$bean->assigned_user_id, $this->branchManagerId($bean)];

        foreach (array_unique(array_filter($recipients)) as $userId) {
            $alert = BeanFactory::newBean('Alerts');
            $alert->name = 'Booking Refund';
            $alert->description = $message;
            $alert->url_redirect = 'index.php?module=Opportunities&action=DetailView&record=' . $bean->id;
            $alert->target_module = 'Opportunities';
            $alert->assigned_user_id = $userId;
            $alert->type = 'info';
            $alert->is_read = 0;
            $alert->save();
        }
    }

    private function branchManagerId($bean)
    {
        // The consultant's "Reports to" user is their branch manager.
        $consultant = BeanFactory::getBean('Users', $bean->assigned_user_id);
        return $consultant ? $consultant->reports_to_id : null;
    }
}`;

const HOOK_REG = "$hook_array['before_save'][] = array(1, 'Booking Refund detect', 'custom/modules/Opportunities/BookingRefundNotify.php', 'BookingRefundNotify', 'detect');\n" +
  "$hook_array['after_save'][] = array(1, 'Booking Refund notify', 'custom/modules/Opportunities/BookingRefundNotify.php', 'BookingRefundNotify', 'notify');";

const recorded = {
  requirement(text) {
    return {
      story: 'As a sales consultant, I want to be told when a booking is refunded, so that I can follow up with the customer. ' +
        'As a branch manager, I want the same alert so that I can track refunds in my branch.',
      criteria: [
        'Trigger when sales stage changes to Booking Refund',
        'Notify the assigned sales consultant',
        'Notify the branch manager (the consultant\'s "Reports to" user)',
        'No duplicate notice when the record is re-saved at the same stage',
      ],
      affected: { module: 'Opportunities', stage: 'Booking Refund', roles: 'Sales consultant, Branch manager', hook: 'after_save' },
    };
  },
  code() {
    return { file: 'custom/modules/Opportunities/BookingRefundNotify.php', php: PHP, hookRegistration: HOOK_REG };
  },
  debug(failure) {
    return {
      cause: 'The Sales consultant promotion (250) is added to the net price instead of being subtracted with the other promotions, ' +
        `so the result is ${failure.actual} instead of ${failure.expected}, a difference of 500 (2 x 250).`,
      location: 'custom/modules/Opportunities/QuotationCalc.php, netSellingPrice()',
      confidence: 'high',
      patch: {
        before: ["        $promos = $q['brandPromotion'] + $q['csEuroPromotion'];", "        return $q['vehiclePrice'] + $fees - $promos + $q['salesConsultantPromotion'];"],
        after: ["        $promos = $q['brandPromotion'] + $q['csEuroPromotion'] + $q['salesConsultantPromotion'];", "        return $q['vehiclePrice'] + $fees - $promos;"],
      },
    };
  },
  release(facts) {
    return {
      version: 'v1.4.0',
      lines: [
        ['New', 'Sales consultant and branch manager are notified when an opportunity moves to Booking Refund.'],
        ['Fixed', 'Net selling price now subtracts the Sales consultant promotion with the other promotions.'],
        ['Tested', `${facts.passed} automated tests, all passing; ${facts.stageCoverage} of ${facts.stageTotal} sales stages covered.`],
        ['Files changed', '2 added (BookingRefundNotify.php, logic_hooks.php), 1 modified (QuotationCalc.php)'],
      ],
    };
  },
};

async function withFallback(name, system, user, args) {
  if (live()) {
    try { return { ...(await ask(system, user)), source: 'claude' }; }
    catch (e) { console.warn(`[ai] ${name} live call failed (${e.message}), using recorded response`); }
  }
  await new Promise((r) => setTimeout(r, 900));
  return { ...recorded[name](args), source: 'recorded' };
}

module.exports = {
  live,
  requirement: (text) => withFallback('requirement', CONTEXT,
    `Turn this request into JSON {"story": string, "criteria": string[4], "affected": {"module","stage","roles","hook"}}: ${text}`, text),
  code: (req) => withFallback('code', CONTEXT,
    `Write a SuiteCRM after_save logic hook for this requirement. JSON {"file","php","hookRegistration"}: ${JSON.stringify(req)}`),
  debug: (failure) => withFallback('debug', CONTEXT,
    `A test failed. JSON {"cause","location","confidence","patch":{"before":string[],"after":string[]}}: ${JSON.stringify(failure)}`, failure),
  release: (facts) => withFallback('release', CONTEXT,
    `Write release notes. JSON {"version","lines":[[label,text]]}: ${JSON.stringify(facts)}`, facts),
};
