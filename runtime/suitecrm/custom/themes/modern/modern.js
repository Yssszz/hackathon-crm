/* Modern look helpers for the CRM. Adds colour-coded sales-stage pills; it only wraps text, it never changes data. */
(function () {
  var PHASES = {
    'Prospecting': 'lead', 'Follow Up': 'lead', 'Follow up 2': 'lead',
    'Test Drive': 'eval', 'Quotation': 'eval',
    'Booking': 'deal', 'Bank Loan Submission': 'deal', 'LOU': 'deal', 'Allocation Complete': 'deal',
    'Full Downpayment': 'deal', 'Issue Downpayment Receipt': 'deal',
    'Registration': 'delivery', 'Delivery': 'delivery', 'Finance Disbursements': 'delivery',
    'Completed': 'done',
    'Pending Booking Refund': 'refund', 'Booking Refund': 'refund'
  };

  function pill(text) {
    var s = document.createElement('span');
    s.className = 'stage-pill ph-' + PHASES[text];
    s.textContent = text;
    return s;
  }

  // Replace the stage text with a pill. Cells keep their other children (such as the inline-edit icon).
  function wrapText(el) {
    if (el.querySelector('.stage-pill')) { return; }
    for (var i = 0; i < el.childNodes.length; i++) {
      var n = el.childNodes[i];
      if (n.nodeType === 3 && PHASES.hasOwnProperty(n.nodeValue.trim())) {
        el.replaceChild(pill(n.nodeValue.trim()), n);
        return;
      }
    }
    // the text may sit in a single span, as on the detail view
    var s = el.querySelector('span.sugar_field');
    if (s && !s.querySelector('.stage-pill') && PHASES.hasOwnProperty((s.textContent || '').trim())) {
      var t = s.textContent.trim();
      s.textContent = '';
      s.appendChild(pill(t));
    }
  }

  function decorate(root) {
    var scope = root || document;
    var nodes = scope.querySelectorAll('[field="sales_stage"]');
    for (var i = 0; i < nodes.length; i++) { wrapText(nodes[i]); }
  }

  var timer = null;
  function schedule() {
    if (timer) { return; }
    timer = setTimeout(function () { timer = null; try { decorate(document); } catch (e) { /* styling only */ } }, 150);
  }

  function start() {
    decorate(document);
    if (window.MutationObserver) {
      new MutationObserver(schedule).observe(document.body, { childList: true, subtree: true });
    }
  }

  if (document.readyState === 'loading') { document.addEventListener('DOMContentLoaded', start); } else { start(); }
})();
