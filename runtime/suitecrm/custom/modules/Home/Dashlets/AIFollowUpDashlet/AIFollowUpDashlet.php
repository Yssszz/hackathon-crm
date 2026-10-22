<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

require_once 'include/Dashlets/Dashlet.php';
require_once 'custom/include/AIAssistant/AIAssistant.php';

/**
 * Home dashlet listing opportunities that need attention. Each row opens the Opportunity.
 * Styling lives in custom/themes/modern/modern.css (classes starting with "aif-").
 */
class AIFollowUpDashlet extends Dashlet
{
    public $title = 'AI Follow-up Suggestions';

    public function __construct($id, $def = null)
    {
        parent::__construct($id);
        $this->isConfigurable = false;
        $this->hasScript = false;
        $this->isRefreshable = true;
    }

    public function display()
    {
        $items = AIAssistant::suggestions();
        if (!$items) {
            return parent::display() . '<div class="aif aif-empty"><b>All caught up</b><span>Nothing needs attention right now.</span></div>';
        }

        $urgent = 0;
        $soon = 0;
        $rows = '';
        foreach ($items as $i) {
            $isUrgent = $i['level'] === 'red';
            $isUrgent ? $urgent++ : $soon++;
            $href = 'index.php?module=Opportunities&action=DetailView&record=' . urlencode($i['id']);
            $rows .= '<a class="aif-row ' . ($isUrgent ? 'urgent' : 'soon') . '" href="' . htmlspecialchars($href) . '">'
                . '<span class="aif-dot"></span>'
                . '<span class="aif-main"><b>' . htmlspecialchars($i['customer']) . '</b>'
                . '<small>' . htmlspecialchars($i['text']) . '</small></span>'
                . ($i['product'] !== '' ? '<span class="aif-product">' . htmlspecialchars($i['product']) . '</span>' : '')
                . '<span class="aif-go">&rsaquo;</span></a>';
        }

        $head = '<div class="aif-head">'
            . ($urgent ? '<span class="aif-chip urgent">' . $urgent . ' urgent</span>' : '')
            . ($soon ? '<span class="aif-chip soon">' . $soon . ' coming up</span>' : '')
            . '</div>';

        return parent::display() . '<div class="aif">' . $head . $rows . '</div>';
    }

    public function displayOptions()
    {
        return '';
    }
}
