const http = require('http');
const fs = require('fs');
const path = require('path');
const { execFile } = require('child_process');
const ai = require('./ai');

const PORT = process.env.PORT || 3000;
const CRM_URL = process.env.CRM_URL || 'http://localhost:8080';
const PHP = path.join(__dirname, 'runtime', 'php', 'php.exe');
const PHP_INI = path.join(__dirname, 'runtime', 'php', 'php.ini');
const CUSTOM = path.join(__dirname, 'runtime', 'suitecrm', 'custom', 'modules', 'Opportunities');
const HOOK_FILE = path.join(CUSTOM, 'BookingRefundNotify.php');
const LOGIC_HOOKS = path.join(CUSTOM, 'logic_hooks.php');
const QUOTATION_FILE = path.join(CUSTOM, 'QuotationCalc.php');

const STAGES = ['Follow Up', 'Test Drive', 'Quotation', 'Follow up 2', 'Booking', 'Pending Booking Refund', 'Booking Refund',
  'Bank Loan Submission', 'LOU', 'Allocation Complete', 'Full Downpayment', 'Issue Downpayment Receipt', 'Registration',
  'Delivery', 'Finance Disbursements', 'Completed', 'Prospecting'];

// The planted bug: the sales consultant promotion is added instead of subtracted.
const BUGGY_QUOTATION = `<?php
// custom/modules/Opportunities/QuotationCalc.php
// Quotation maths: net selling price shown on the opportunity.
class QuotationCalc
{
    public static function netSellingPrice(array $q)
    {
        $fees = $q['inspectionFee'] + $q['adminFee'] + $q['numberPlate'] + $q['colourEffect'];
        $promos = $q['brandPromotion'] + $q['csEuroPromotion'];
        return $q['vehiclePrice'] + $fees - $promos + $q['salesConsultantPromotion'];
    }
}
`;

const session = { requirement: null, failure: null, tests: null, code: null };
const isDeployed = () => fs.existsSync(HOOK_FILE) && fs.existsSync(LOGIC_HOOKS);

// Runs a PHP script that boots the real SuiteCRM; the last output line is JSON.
function php(script, args = []) {
  return new Promise((resolve, reject) => {
    // Relative, forward-slash script path: SuiteCRM rejects "C:" and backslashes in $_SERVER as bad data.
    execFile(PHP, ['-c', PHP_INI, `crm-php/${script}`, ...args],
      { cwd: __dirname, maxBuffer: 10 * 1024 * 1024, timeout: 120000 }, (err, stdout, stderr) => {
        const line = stdout.trim().split('\n').pop();
        try { resolve(JSON.parse(line)); }
        catch { reject(new Error(`${script} failed: ${(stderr || stdout || err?.message || '').slice(0, 300)}`)); }
      });
  });
}

function resetAll() {
  fs.rmSync(HOOK_FILE, { force: true });
  fs.rmSync(LOGIC_HOOKS, { force: true });
  fs.writeFileSync(QUOTATION_FILE, BUGGY_QUOTATION);
  session.requirement = session.failure = session.tests = session.code = null;
  return php('reset.php');
}

function readBody(req) {
  return new Promise((resolve) => {
    let raw = '';
    req.on('data', (c) => (raw += c));
    req.on('end', () => { try { resolve(JSON.parse(raw || '{}')); } catch { resolve({}); } });
  });
}

function send(res, status, body) {
  res.writeHead(status, { 'content-type': 'application/json' });
  res.end(JSON.stringify(body));
}

const routes = {
  'GET /api/state': async () => ({ live: ai.live(), deployed: isDeployed(), stages: STAGES, crmUrl: CRM_URL }),
  'POST /api/reset': async () => { await resetAll(); return { ok: true }; },
  'POST /api/requirement': async (b) => {
    session.requirement = await ai.requirement(b.text || '');
    return session.requirement;
  },
  'POST /api/code': async () => {
    session.code = await ai.code(session.requirement);
    return session.code;
  },
  // Deploy writes the generated hook into the real SuiteCRM custom/ folder and registers it.
  'POST /api/deploy': async () => {
    if (!session.code) throw new Error('Generate the code first');
    fs.mkdirSync(CUSTOM, { recursive: true });
    fs.writeFileSync(HOOK_FILE, session.code.php);
    fs.writeFileSync(LOGIC_HOOKS, `<?php\n$hook_version = 1;\n$hook_array = Array();\n$hook_array['before_save'] = Array();\n$hook_array['after_save'] = Array();\n${session.code.hookRegistration}\n`);
    return { deployed: true, at: new Date().toLocaleTimeString('en-GB'), env: 'DEV (SuiteCRM 7.15.2, localhost:8080)' };
  },
  'POST /api/tests': async () => {
    session.tests = await php('tests.php');
    session.failure = session.tests.results.find((r) => !r.ok) || null;
    return session.tests;
  },
  'POST /api/debug': async () => {
    if (!session.failure) return { error: 'No failing test to investigate' };
    return ai.debug(session.failure);
  },
  'POST /api/fix': async (b) => {
    const src = fs.readFileSync(QUOTATION_FILE, 'utf8');
    const before = b.patch.before.join('\n');
    if (!src.includes(before)) return { error: 'The proposed patch does not match the file' };
    fs.writeFileSync(QUOTATION_FILE, src.replace(before, b.patch.after.join('\n')));
    return { applied: true };
  },
  'POST /api/release': async () => {
    const t = session.tests || { passed: 0, stageCoverage: 0, stageTotal: STAGES.length };
    return ai.release({ requirement: session.requirement, passed: t.passed, stageCoverage: t.stageCoverage, stageTotal: t.stageTotal });
  },
  'GET /api/crm': async () => php('crm_state.php'),
  'POST /api/crm/save': async (b) => php('crm_save.php', [b.stage]),
};

const MIME = { '.html': 'text/html', '.js': 'text/javascript', '.css': 'text/css' };

http.createServer(async (req, res) => {
  const url = req.url.split('?')[0];
  const handler = routes[`${req.method} ${url}`];
  if (handler) {
    try { send(res, 200, await handler(await readBody(req))); }
    catch (e) { send(res, 500, { error: e.message }); }
    return;
  }
  const file = path.join(__dirname, 'public', url === '/' ? 'index.html' : url);
  if (!file.startsWith(path.join(__dirname, 'public')) || !fs.existsSync(file)) {
    res.writeHead(404); res.end('Not found'); return;
  }
  res.writeHead(200, { 'content-type': MIME[path.extname(file)] || 'application/octet-stream' });
  fs.createReadStream(file).pipe(res);
}).listen(PORT, () => {
  console.log(`AI Dev Copilot on http://localhost:${PORT} (${ai.live() ? 'live Claude' : 'recorded AI'}), CRM at ${CRM_URL}`);
  resetAll().then(() => console.log('Demo state reset')).catch((e) => console.warn('Reset failed:', e.message));
});
