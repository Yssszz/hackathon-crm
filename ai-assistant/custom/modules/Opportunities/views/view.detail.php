<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

require_once 'modules/Opportunities/views/view.detail.php';
require_once 'custom/include/AIAssistant/AIAssistant.php';

/**
 * Opportunity detail page with the AI Assistant panel above the normal page.
 * Everything the stock page shows is rendered unchanged by the parent class.
 */
class CustomOpportunitiesViewDetail extends OpportunitiesViewDetail
{
    public function display()
    {
        if (!empty($this->bean->id)) {
            echo $this->renderAssistant($this->bean->id);
        }
        parent::display();
    }

    private function renderAssistant($id)
    {
        $mode = AIAssistant::mode();
        $modeLabel = $mode === 'claude' ? 'Powered by Claude' : 'Offline mode';
        $modeTitle = $mode === 'claude' ? 'Text is written by Claude from this CRM record' : 'No language model: text is built by rules from this CRM record';
        $url = 'index.php?entryPoint=aiAssistant&record=' . urlencode($id);

        return <<<HTML
<style>
#ai-assistant{--b:#4f46e5;--b2:#7c3aed;--ink:#111827;--mut:#6b7280;--line:#e7e9f0;background:#fff;border:1px solid var(--line);border-radius:16px;box-shadow:0 1px 2px rgba(16,24,40,.05),0 8px 24px rgba(16,24,40,.07);margin:6px 0 20px;overflow:hidden;font-size:14.5px;color:#374151}
#ai-assistant *{box-sizing:border-box}
#ai-assistant .aia-head{display:flex;align-items:center;gap:14px;padding:16px 20px;background:linear-gradient(100deg,#f5f3ff 0%,#eef2ff 55%,#fff 100%);border-bottom:1px solid var(--line)}
#ai-assistant .aia-badge{width:42px;height:42px;border-radius:12px;background:linear-gradient(135deg,var(--b),var(--b2));display:flex;align-items:center;justify-content:center;box-shadow:0 6px 16px rgba(79,70,229,.35)}
#ai-assistant .aia-title{font-size:17px;font-weight:750;color:var(--ink);letter-spacing:-.01em;line-height:1.2}
#ai-assistant .aia-sub{font-size:13px;color:var(--mut);margin-top:2px}
#ai-assistant .aia-mode{margin-left:6px;font-size:12px;font-weight:600;padding:3px 10px;border-radius:99px;background:#f3f4f6;color:#4b5563;cursor:help}
#ai-assistant .aia-mode.claude{background:#dcfce7;color:#166534}
#ai-assistant .aia-grow{flex:1}
#ai-assistant .aia-btn{border:1px solid #d9dce6;background:#fff;color:#374151;border-radius:10px;padding:9px 16px;font-size:14px;font-weight:650;cursor:pointer;display:inline-flex;align-items:center;gap:8px;transition:all .15s}
#ai-assistant .aia-btn:hover{background:#eef0ff;border-color:#c7cbff;color:var(--b)}
#ai-assistant .aia-btn.primary{background:linear-gradient(135deg,var(--b),var(--b2));border-color:transparent;color:#fff;box-shadow:0 6px 16px rgba(79,70,229,.3)}
#ai-assistant .aia-btn.primary:hover{filter:brightness(1.07);color:#fff}
#ai-assistant .aia-btn[disabled]{opacity:.6;cursor:wait}
#ai-assistant .aia-body{padding:18px 20px}
#ai-assistant .aia-hint{color:var(--mut);display:flex;align-items:center;gap:10px}
#ai-assistant .aia-chips{display:flex;flex-wrap:wrap;gap:10px;margin-bottom:16px}
#ai-assistant .aia-chip{background:#f8f9fc;border:1px solid var(--line);border-radius:12px;padding:8px 14px;min-width:130px}
#ai-assistant .aia-chip small{display:block;font-size:11.5px;font-weight:600;color:var(--mut);letter-spacing:.02em;margin-bottom:2px}
#ai-assistant .aia-chip b{font-size:14.5px;color:var(--ink);font-weight:650}
#ai-assistant .aia-grid{display:grid;grid-template-columns:1.25fr 1fr;gap:16px}
@media (max-width:900px){#ai-assistant .aia-grid{grid-template-columns:1fr}}
#ai-assistant .aia-card{border:1px solid var(--line);border-radius:14px;padding:16px 18px;background:#fff}
#ai-assistant .aia-label{font-size:12px;font-weight:700;letter-spacing:.06em;text-transform:uppercase;color:var(--mut);margin-bottom:8px;display:flex;align-items:center;gap:8px}
#ai-assistant .aia-card p{margin:0;font-size:15px;line-height:1.55;color:var(--ink)}
#ai-assistant .aia-action{border-left:5px solid #9ca3af}
#ai-assistant .aia-action.high{border-left-color:#dc2626;background:linear-gradient(90deg,#fff5f5,#fff 40%)}
#ai-assistant .aia-action.medium{border-left-color:#d97706;background:linear-gradient(90deg,#fffbeb,#fff 40%)}
#ai-assistant .aia-action.low{border-left-color:#059669;background:linear-gradient(90deg,#ecfdf5,#fff 40%)}
#ai-assistant .aia-act{font-size:17px;font-weight:700;color:var(--ink);line-height:1.35}
#ai-assistant .aia-pill{display:inline-block;margin-top:8px;font-size:12px;font-weight:700;padding:3px 10px;border-radius:99px}
#ai-assistant .high .aia-pill{background:#fee2e2;color:#b91c1c}
#ai-assistant .medium .aia-pill{background:#fef3c7;color:#92400e}
#ai-assistant .low .aia-pill{background:#d1fae5;color:#065f46}
#ai-assistant .aia-why{margin-top:12px;font-size:14px;color:#4b5563;line-height:1.5}
#ai-assistant .aia-why b{color:var(--ink)}
#ai-assistant .aia-foot{padding:0 20px 18px;display:flex;align-items:center;gap:14px}
#ai-assistant .aia-meta{font-size:12.5px;color:var(--mut)}
#ai-assistant .aia-email{margin:0 20px 20px;border:1px solid var(--line);border-radius:14px;overflow:hidden;display:none}
#ai-assistant .aia-email-head{display:flex;align-items:center;gap:10px;padding:12px 16px;background:#f8f9fc;border-bottom:1px solid var(--line);font-weight:700;color:var(--ink)}
#ai-assistant .aia-tag{font-size:12px;font-weight:600;padding:2px 9px;border-radius:99px;background:#ecfdf5;color:#047857}
#ai-assistant .aia-row{display:flex;align-items:center;gap:10px;padding:10px 16px;border-bottom:1px solid var(--line-2,#f0f1f6)}
#ai-assistant .aia-row label{width:64px;color:var(--mut);font-size:13px;font-weight:600;margin:0}
#ai-assistant .aia-row input{flex:1;border:0 !important;box-shadow:none !important;font-size:14.5px;font-weight:600;color:var(--ink) !important;background:transparent !important;padding:4px 0 !important;outline:0}
#ai-assistant .aia-row .to{flex:1;color:var(--ink)}
#ai-assistant textarea.aia-text{width:100%;min-height:230px;border:0 !important;box-shadow:none !important;padding:14px 16px !important;font-size:14.5px;line-height:1.6;color:var(--ink) !important;resize:vertical;outline:0;border-radius:0 !important}
#ai-assistant .aia-email-foot{display:flex;align-items:center;gap:10px;padding:12px 16px;border-top:1px solid var(--line);background:#fcfcfe;flex-wrap:wrap}
#ai-assistant .aia-err{color:#b91c1c;padding:12px 20px}
#ai-assistant .skel{height:14px;border-radius:7px;background:linear-gradient(90deg,#eef0f6 25%,#f7f8fc 50%,#eef0f6 75%);background-size:200% 100%;animation:aiaSh 1.1s infinite linear;margin:10px 0}
@keyframes aiaSh{to{background-position:-200% 0}}
</style>
<div id="ai-assistant" data-url="$url">
  <div class="aia-head">
    <div class="aia-badge"><svg width="22" height="22" viewBox="0 0 24 24" fill="#fff"><path d="M12 2l1.9 5.6L19.5 9l-5.6 1.9L12 16.5l-1.9-5.6L4.5 9l5.6-1.4L12 2zm7 11l.9 2.6 2.6.9-2.6.9L19 20l-.9-2.6-2.6-.9 2.6-.9L19 13zM5 14l.7 1.9 1.9.7-1.9.7L5 19.2l-.7-1.9-1.9-.7 1.9-.7L5 14z"/></svg></div>
    <div><div class="aia-title">AI Assistant</div><div class="aia-sub">Summary, next action and follow-up email for this deal</div></div>
    <span class="aia-mode $mode" title="$modeTitle">$modeLabel</span>
    <span class="aia-grow"></span>
    <button type="button" class="aia-btn primary" id="ai-btn-summary">AI Summary</button>
  </div>

  <div class="aia-body" id="ai-body-area">
    <div class="aia-hint" id="ai-hint">Click <b>AI Summary</b> to read this opportunity and get a recommended next step.</div>
    <div id="ai-loading" style="display:none"><div class="skel" style="width:70%"></div><div class="skel" style="width:95%"></div><div class="skel" style="width:60%"></div></div>
    <div id="ai-error" class="aia-err" style="display:none"></div>
    <div id="ai-result" style="display:none">
      <div class="aia-chips" id="ai-chips"></div>
      <div class="aia-grid">
        <div class="aia-card"><div class="aia-label">Customer summary</div><p id="ai-summary"></p></div>
        <div class="aia-card aia-action" id="ai-action-card">
          <div class="aia-label">AI recommendation &middot; next action</div>
          <div class="aia-act" id="ai-action"></div>
          <span class="aia-pill" id="ai-pill"></span>
          <div class="aia-why"><b>Why:</b> <span id="ai-reason"></span></div>
        </div>
      </div>
    </div>
  </div>

  <div class="aia-foot">
    <button type="button" class="aia-btn" id="ai-btn-email"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3 7l9 6 9-6"/></svg>Generate Follow-up Email</button>
    <span class="aia-meta" id="ai-status"></span>
  </div>

  <div class="aia-email" id="ai-sec-email">
    <div class="aia-email-head">Follow-up email <span class="aia-tag">Draft &middot; not sent automatically</span></div>
    <div class="aia-row"><label>To</label><span class="to" id="ai-to"></span></div>
    <div class="aia-row"><label>Subject</label><input id="ai-subject" type="text"></div>
    <textarea class="aia-text" id="ai-body"></textarea>
    <div class="aia-email-foot">
      <button type="button" class="aia-btn primary" id="ai-btn-copy">Copy email</button>
      <button type="button" class="aia-btn" id="ai-btn-regen">Regenerate</button>
      <span class="aia-meta">Review and edit before you use it.</span>
    </div>
  </div>
</div>
<script>
(function () {
  var root = document.getElementById('ai-assistant');
  var base = root.getAttribute('data-url');
  var variant = 0;
  function el(id) { return document.getElementById(id); }
  function show(id, on, disp) { el(id).style.display = on ? (disp || 'block') : 'none'; }
  function esc(s) { var d = document.createElement('div'); d.textContent = s == null ? '' : s; return d.innerHTML; }
  function call(op, extra) {
    return fetch(base + '&op=' + op + (extra || ''), { credentials: 'same-origin' })
      .then(function (r) { return r.json(); })
      .then(function (d) { if (d.error) { throw new Error(d.error); } return d; });
  }
  function status(msg) { el('ai-status').textContent = msg || ''; }
  function fail(e) { show('ai-loading', false); el('ai-error').textContent = 'AI Assistant error: ' + e.message; show('ai-error', true); }
  function secs(t0) { return ((Date.now() - t0) / 1000).toFixed(1); }

  el('ai-btn-summary').onclick = function () {
    var t0 = Date.now(), btn = el('ai-btn-summary');
    btn.disabled = true; show('ai-hint', false); show('ai-error', false); show('ai-result', false); show('ai-loading', true); status('');
    call('analyze').then(function (d) {
      var r = d.result, f = d.facts;
      var chips = [['Customer', f.customer], ['Vehicle', f.vehicle], ['Budget', f.budget], ['Stage', f.stage], ['Last activity', f.last]];
      el('ai-chips').innerHTML = chips.map(function (c) { return '<div class="aia-chip"><small>' + esc(c[0]) + '</small><b>' + esc(c[1]) + '</b></div>'; }).join('');
      el('ai-summary').textContent = r.summary;
      el('ai-action').textContent = r.action;
      el('ai-reason').textContent = r.reason;
      el('ai-action-card').className = 'aia-card aia-action ' + r.priority;
      el('ai-pill').textContent = r.priority.charAt(0).toUpperCase() + r.priority.slice(1) + ' priority · ' + r.when;
      show('ai-loading', false); show('ai-result', true);
      status('Generated in ' + secs(t0) + ' s');
    }).catch(fail).then(function () { btn.disabled = false; });
  };

  function makeEmail() {
    var t0 = Date.now(), btn = el('ai-btn-email');
    btn.disabled = true; status('Writing the follow-up email...');
    return call('email', '&variant=' + variant).then(function (d) {
      el('ai-to').textContent = (d.customer || '') + (d.to ? ' <' + d.to + '>' : '');
      el('ai-subject').value = d.result.subject;
      el('ai-body').value = d.result.body;
      show('ai-sec-email', true);
      status('Email generated in ' + secs(t0) + ' s');
    }).catch(fail).then(function () { btn.disabled = false; });
  }
  el('ai-btn-email').onclick = function () { variant = 0; makeEmail(); };
  el('ai-btn-regen').onclick = function () { variant += 1; makeEmail(); };
  el('ai-btn-copy').onclick = function () {
    var text = 'Subject: ' + el('ai-subject').value + '\\n\\n' + el('ai-body').value;
    var done = function () { status('Copied to clipboard'); };
    if (navigator.clipboard && navigator.clipboard.writeText) {
      navigator.clipboard.writeText(text).then(done, function () { el('ai-body').select(); document.execCommand('copy'); done(); });
    } else { el('ai-body').select(); document.execCommand('copy'); done(); }
  };
})();
</script>
HTML;
    }
}
