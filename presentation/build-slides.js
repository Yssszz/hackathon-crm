// Generates slides.html from the screenshots in img/ and the element positions in img/boxes.json.
// Run: node build-slides.js, then render the PDF from slides.html.
const fs = require('fs');
const path = require('path');

const boxes = JSON.parse(fs.readFileSync(path.join(__dirname, 'img', 'boxes.json'), 'utf8'));
// boxes.dashlet is measured from the real page by hero.js

const pct = (n) => (n * 100).toFixed(2) + '%';
const hl = (b, pad = 0.006) => `<div class="hl" style="left:${pct(b.x - pad)};top:${pct(b.y - pad * 1.6)};width:${pct(b.w + pad * 2)};height:${pct(b.h + pad * 3.2)}"></div>`;
const pin = (n, b) => `<div class="pin" style="left:${pct(b.x)};top:${pct(b.y)}">${n}</div>`;
const frame = (img, url, width, extra = '') => `
  <div class="frame" style="width:${width}px">
    <div class="bar"><i style="background:#ff5f57"></i><i style="background:#febc2e"></i><i style="background:#28c840"></i><span class="url">${url}</span></div>
    <div class="view"><img src="img/${img}">${extra}</div>
  </div>`;

const icon = {
  read: '<svg viewBox="0 0 24 24" width="54" height="54" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M6 3h9l4 4v14H6z"/><path d="M14 3v5h5"/><path d="M9 12h7M9 16h7"/></svg>',
  decide: '<svg viewBox="0 0 24 24" width="54" height="54" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="5" r="2.2"/><circle cx="5" cy="19" r="2.2"/><circle cx="19" cy="19" r="2.2"/><path d="M12 7.2v4.3M12 11.5L5 16.8M12 11.5l7 5.3"/></svg>',
  write: '<svg viewBox="0 0 24 24" width="54" height="54" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M4 20l1-4L16.5 4.5a2.1 2.1 0 013 3L8 19l-4 1z"/><path d="M14 7l3 3"/></svg>',
  spark: '<svg viewBox="0 0 24 24" width="30" height="30" fill="currentColor"><path d="M12 2l1.9 5.6L19.5 9l-5.6 1.9L12 16.5l-1.9-5.6L4.5 9l5.6-1.4L12 2z"/></svg>',
};

const slides = [];

// 1 Title
slides.push(`
<section class="dark s1">
  <div class="copy">
    <div class="tag light">AI Assistant for the CS Euro Auto CRM</div>
    <h1>Sales Copilot</h1>
    <div class="lead">AI that tells your sales team what to do next</div>
    <div class="foot">Hackathon 2026</div>
  </div>
  <div class="hero">${frame('new-summary.png', 'localhost:8080 &middot; Opportunity', 1130)}</div>
</section>`);

// 2 Story
slides.push(`
<section class="s2">
  <div class="tag">Monday, 9:00 am</div>
  <h2>A salesperson opens the CRM&hellip;</h2>
  <div class="bigrow">
    <div class="bignum"><b>91</b><span>deals<br>in &ldquo;Follow Up&rdquo;</span></div>
    <div class="plus">=</div>
    <div class="bignum alt"><b>RM3.1M</b><span>waiting for<br>a decision</span></div>
  </div>
  <div class="quote">&ldquo;Which one do I call first?&rdquo;</div>
  <div class="src">Production CRM, from the demo video</div>
  <div class="num">2</div>
</section>`);

// 3 Idea
slides.push(`
<section>
  <div class="tag">Our idea</div>
  <h2>Three steps become one click.</h2>
  <div class="duel">
    <div class="lane old">
      <div class="lh">Before</div>
      <div class="pillrow"><div class="sp">1 &nbsp;Read the record</div><div class="sp">2 &nbsp;Decide what to do</div><div class="sp">3 &nbsp;Write the email</div></div>
      <div class="time">minutes per customer <i>(estimate)</i></div>
    </div>
    <div class="lane new">
      <div class="lh">After</div>
      <div class="clickbtn"><span class="spark">&#10022;</span> AI Summary</div>
      <div class="time">under 1 second <i>(measured)</i></div>
    </div>
  </div>
  <div class="pill ok">One assistant added to the existing page. Nothing removed.</div>
  <div class="num">3</div>
</section>`);

// 4 Before / after look
slides.push(`
<section>
  <div class="tag">The interface</div>
  <h2>Same CRM. A modern look.</h2>
  <div class="compare">
    <div class="side"><div class="badge old">Before</div>${frame('old-list.png', 'localhost:8080 &middot; Opportunities', 790)}</div>
    <div class="mid">&rarr;</div>
    <div class="side"><div class="badge new">After</div>${frame('new-list.png', 'localhost:8080 &middot; Opportunities', 790)}</div>
  </div>
  <div class="cap">A style layer on top of the same data and the same features. No CRM core file was changed.</div>
  <div class="num">4</div>
</section>`);

// 5 Summary + next action
slides.push(`
<section>
  <div class="tag">Feature 1 + 2</div>
  <h2>One click: summary and next action</h2>
  <div class="demo">
    ${frame('new-summary.png', 'localhost:8080 &middot; Opportunity', 1110,
      pin(1, { x: boxes.chips.x + boxes.chips.w - 0.012, y: boxes.chips.y + boxes.chips.h / 2 }) + pin(2, { x: boxes.summaryCard.x + boxes.summaryCard.w * 0.86, y: boxes.summaryCard.y + boxes.summaryCard.h }) + pin(3, { x: boxes.action.x + boxes.action.w, y: boxes.action.y + boxes.action.h * 0.78 }))}
    <ul class="legend">
      <li><b>1</b><span>The facts at a glance</span></li>
      <li><b>2</b><span>A plain-language summary</span></li>
      <li><b>3</b><span>The next action, its priority and why</span></li>
    </ul>
  </div>
  <div class="num">5</div>
</section>`);

// 6 Email
slides.push(`
<section>
  <div class="tag">Feature 3</div>
  <h2>A follow-up email, ready to edit</h2>
  <div class="demo">
    <img class="panelshot" src="img/new-panel-email.png">
    <ul class="legend plain">
      <li><b class="dotb"></b><span>Fitted to the sales stage</span></li>
      <li><b class="dotb"></b><span>Edit, copy or regenerate</span></li>
      <li><b class="dotb"></b><span>Never sent automatically</span></li>
    </ul>
  </div>
  <div class="num">6</div>
</section>`);

// 7 Dashboard
slides.push(`
<section>
  <div class="tag">Bonus</div>
  <h2>Who to call first</h2>
  <div class="demo">
    ${frame('new-dashboard.png', 'localhost:8080 &middot; Home', 1110, hl(boxes.dashlet))}
    <ul class="legend plain">
      <li><b class="dotb red"></b><span>Urgent</span></li>
      <li><b class="dotb amber"></b><span>Coming up</span></li>
      <li><b class="dotb"></b><span>Click a name, the deal opens</span></li>
    </ul>
  </div>
  <div class="num">7</div>
</section>`);

// 8 Results
slides.push(`
<section>
  <div class="tag">Results</div>
  <h2>From minutes to one click</h2>
  <div class="two">
    <div class="box a"><h3>Impact</h3><div class="big2">Minutes<br>&rarr; under 1 second</div>
      <p>Read, decide and write by hand (estimate) vs. one click (measured).</p><div class="tiles"><div><b>0.2 s</b><span>typical AI response in our demo</span></div><div><b>17</b><span>sales stages understood</span></div></div></div>
    <div class="box b"><h3>Next</h3><ol class="nx">
      <li>Connect production data</li>
      <li>Claude for natural writing and free-text notes</li>
      <li>Send emails from the CRM after approval</li></ol></div>
  </div>
  <div class="note">Today: rule-based logic on real CRM data, working end to end in the CRM. Next: a language model.</div>
  <div class="num">8</div>
</section>`);

// 9 Thanks
slides.push(`
<section class="dark s9">
  <h1>Thank you</h1>
  <div class="lead">Questions?</div>
</section>`);

const css = `
@page { size: 1920px 1080px; margin: 0; }
* { box-sizing: border-box; margin: 0; padding: 0; }
:root { --ink:#111827; --muted:#6b7280; --nav:#0f172a; --brand:#4f46e5; --violet:#7c3aed; --soft:#eef0ff; --line:#e4e7f0; --good:#059669; }
html, body { width: 1920px; font-family: "Segoe UI Variable Text","Segoe UI",Inter,Arial,sans-serif; color: var(--ink); -webkit-font-smoothing: antialiased; }
section { width: 1920px; height: 1080px; padding: 84px 120px 70px; position: relative; page-break-after: always; overflow: hidden; background: #f7f8fc; display: flex; flex-direction: column; }
section:last-child { page-break-after: auto; }
section::before { content: ""; position: absolute; right: -240px; top: -260px; width: 700px; height: 700px; border-radius: 50%; background: radial-gradient(circle, rgba(124,58,237,.10), transparent 68%); }
.tag { font-size: 25px; letter-spacing: .16em; text-transform: uppercase; color: var(--brand); font-weight: 750; margin-bottom: 16px; position: relative; }
.tag.light { color: #a5b4fc; }
h1 { font-size: 150px; line-height: 1; font-weight: 800; letter-spacing: -.03em; }
h2 { font-size: 68px; line-height: 1.08; font-weight: 800; letter-spacing: -.025em; position: relative; }
.lead { font-size: 44px; color: #cbd5e1; margin-top: 26px; max-width: 760px; line-height: 1.25; }
.num { position: absolute; right: 64px; bottom: 36px; font-size: 22px; color: #a5abc0; font-weight: 600; }
.dark { background: linear-gradient(135deg, #0b1020 0%, #1e1b4b 55%, #3b2a8f 100%); color: #fff; }
.dark::before { background: radial-gradient(circle, rgba(167,139,250,.28), transparent 65%); }

/* browser frame */
.frame { border-radius: 18px; background: #fff; overflow: hidden; box-shadow: 0 34px 90px rgba(15,23,42,.28), 0 0 0 1px rgba(15,23,42,.10); }
.frame .bar { height: 44px; background: #eef0f6; display: flex; align-items: center; padding: 0 16px; gap: 8px; border-bottom: 1px solid #e0e3ec; }
.frame .bar i { width: 12px; height: 12px; border-radius: 50%; display: block; }
.frame .url { margin-left: 14px; background: #fff; border-radius: 8px; padding: 4px 14px; font-size: 15px; color: var(--muted); }
.frame .view { position: relative; line-height: 0; }
.frame img { width: 100%; display: block; }
.pin { position: absolute; width: 52px; height: 52px; margin: -26px 0 0 -26px; border-radius: 50%; background: linear-gradient(135deg, var(--brand), var(--violet)); color: #fff; font: 800 28px/52px "Segoe UI", sans-serif; text-align: center; box-shadow: 0 0 0 6px rgba(255,255,255,.95), 0 10px 24px rgba(79,70,229,.55); }
.hl { position: absolute; border: 5px solid var(--violet); border-radius: 16px; box-shadow: 0 0 0 7px rgba(124,58,237,.18); }

/* 1 */
.s1 { justify-content: center; }
.s1 .copy { position: relative; z-index: 2; max-width: 900px; }
.s1 .lead { max-width: 900px; font-size: 42px; }
.s1 .foot { position: absolute; left: 0; bottom: -330px; font-size: 26px; color: #94a3b8; }
.s1 .hero { position: absolute; right: -250px; top: 190px; z-index: 1; transform: perspective(2400px) rotateY(-9deg) rotateX(2deg); transform-origin: left center; }

/* 2 */
.steps3 { display: flex; align-items: stretch; gap: 22px; margin-top: 56px; }
.step3 { flex: 1; background: #fff; border: 1px solid var(--line); border-radius: 28px; padding: 40px 42px; position: relative; box-shadow: 0 14px 40px rgba(15,23,42,.07); }
.step3 .ico { color: var(--brand); width: 92px; height: 92px; border-radius: 24px; background: var(--soft); display: flex; align-items: center; justify-content: center; }
.step3 .n { position: absolute; right: 36px; top: 30px; font-size: 70px; font-weight: 800; color: #e7e9f7; }
.step3 .big { font-size: 64px; font-weight: 800; margin-top: 26px; letter-spacing: -.02em; }
.step3 .small { font-size: 32px; color: #4b5563; margin-top: 8px; line-height: 1.25; }
.arrow3 { align-self: center; font-size: 70px; color: #b5b9d4; }
.stat { margin-top: auto; background: linear-gradient(100deg, #1e1b4b, #4338ca); color: #fff; border-radius: 28px; padding: 36px 56px; display: flex; align-items: center; gap: 40px; }
.stat b { font-size: 130px; line-height: 1; letter-spacing: -.03em; }
.stat span { font-size: 42px; display: block; color: #e0e7ff; line-height: 1.2; }
.stat small { display: block; font-size: 23px; color: #a5b4fc; margin-top: 10px; }

/* 2 story */
.s2 { background: linear-gradient(160deg,#fff 0%,#eef0ff 100%); }
.bigrow { display:flex; align-items:center; gap:46px; margin-top:64px; }
.bignum { background:#fff; border:1px solid var(--line); border-radius:36px; padding:38px 56px; display:flex; align-items:center; gap:30px; box-shadow:0 24px 60px rgba(79,70,229,.14); }
.bignum b { font-size:170px; line-height:1; font-weight:800; letter-spacing:-.04em; background:linear-gradient(135deg,#4f46e5,#7c3aed); -webkit-background-clip:text; background-clip:text; color:transparent; }
.bignum span { font-size:36px; font-weight:650; color:#4b5563; line-height:1.25; }
.bignum.alt b { font-size:130px; }
.plus { font-size:90px; color:#b5b9d4; font-weight:300; }
.quote { margin-top:auto; font-size:64px; font-weight:800; letter-spacing:-.02em; color:#1e1b4b; }
.src { margin-top:12px; font-size:24px; color:#8b90a8; }
/* 3 duel */
.duel { display:flex; gap:44px; margin-top:56px; flex:1; align-items:stretch; margin-bottom:34px; }
.lane { flex:1; border-radius:34px; padding:46px 50px; display:flex; flex-direction:column; }
.lane.old { background:#eceef6; color:#5b6275; }
.lane.new { background:linear-gradient(135deg,#1e1b4b,#4338ca); color:#fff; box-shadow:0 30px 70px rgba(67,56,202,.35); }
.lh { font-size:28px; font-weight:800; letter-spacing:.14em; text-transform:uppercase; opacity:.7; }
.pillrow { margin-top:40px; display:flex; flex-direction:column; gap:16px; }
.sp { background:#fff; color:#4b5563; border-radius:18px; padding:20px 28px; font-size:36px; font-weight:650; }
.clickbtn { margin:44px 0 0; align-self:flex-start; background:#fff; color:#4f46e5; border-radius:22px; padding:34px 54px; font-size:60px; font-weight:800; letter-spacing:-.02em; box-shadow:0 18px 44px rgba(0,0,0,.28); }
.clickbtn .spark { color:#7c3aed; }
.time { margin-top:auto; padding-top:30px; font-size:40px; font-weight:750; }
.time i { font-size:24px; font-weight:500; opacity:.7; font-style:normal; }
/* 3 */
.flow { display: flex; align-items: center; gap: 20px; margin-top: 70px; }
.flow + .flow { margin-top: 44px; }
.flow .lab { width: 150px; font-size: 28px; font-weight: 800; letter-spacing: .12em; text-transform: uppercase; }
.flow i { font-style: normal; font-size: 40px; color: #b0b5cf; }
.flow .chip { flex: 1; text-align: center; padding: 58px 14px; border-radius: 24px; font-size: 36px; font-weight: 650; line-height: 1.2; }
.before .lab { color: #9aa1b4; } .before .chip { background: #eceef6; color: #5b6275; }
.after .lab { color: var(--brand); } .after .chip { background: linear-gradient(135deg, #4f46e5, #7c3aed); color: #fff; box-shadow: 0 14px 34px rgba(79,70,229,.35); }
.pill { margin-top: auto; align-self: flex-start; font-size: 32px; font-weight: 700; padding: 22px 40px; border-radius: 99px; }
.pill.ok { background: #d1fae5; color: #065f46; }

/* 4 */
.compare { display: flex; align-items: center; gap: 40px; margin-top: 40px; }
.compare .side { position: relative; padding-top: 34px; }
.compare .mid { font-size: 80px; color: var(--brand); font-weight: 800; }
.badge { position: absolute; top: -6px; left: 8px; z-index: 3; font-size: 25px; font-weight: 800; letter-spacing: .1em; text-transform: uppercase; padding: 9px 24px; border-radius: 99px; box-shadow: 0 8px 20px rgba(0,0,0,.18); }
.badge.old { background: #6b7280; color: #fff; } .badge.new { background: linear-gradient(135deg, #4f46e5, #7c3aed); color: #fff; }
.cap { margin-top: auto; font-size: 30px; color: #4b5563; }

/* 5,6,7 */
.demo { display: flex; align-items: center; gap: 56px; margin-top: 34px; flex: 1; }
.legend { list-style: none; flex: 1; }
.legend li { display: flex; align-items: center; gap: 22px; font-size: 40px; font-weight: 650; margin-bottom: 40px; line-height: 1.2; }
.legend li b { flex: none; width: 56px; height: 56px; border-radius: 50%; background: linear-gradient(135deg, var(--brand), var(--violet)); color: #fff; font-size: 30px; text-align: center; line-height: 56px; }
.legend.plain li b.dotb { width: 30px; height: 30px; background: var(--brand); }
.legend.plain li b.red { background: #dc2626; } .legend.plain li b.amber { background: #f59e0b; }
.panelshot { width: 925px; border-radius: 20px; box-shadow: 0 30px 80px rgba(15,23,42,.22), 0 0 0 1px rgba(15,23,42,.08); display: block; }

/* 8 */
.two { display: flex; gap: 50px; margin-top: 48px; flex: 1; }
.box { flex: 1; border-radius: 30px; padding: 56px; }
.box.a { background: linear-gradient(135deg, #1e1b4b, #4338ca); color: #fff; }
.box.b { background: #fff; border: 1px solid var(--line); box-shadow: 0 14px 40px rgba(15,23,42,.07); }
.box h3 { font-size: 27px; letter-spacing: .16em; text-transform: uppercase; opacity: .7; margin-bottom: 28px; }
.big2 { font-size: 84px; font-weight: 800; line-height: 1.05; letter-spacing: -.02em; }
.box p { font-size: 33px; margin-top: 30px; line-height: 1.3; color: #c7d2fe; }
.nx { list-style: none; counter-reset: n; }
.nx li { counter-increment: n; font-size: 40px; font-weight: 650; margin-bottom: 34px; padding-left: 78px; position: relative; line-height: 1.2; }
.nx li::before { content: counter(n); position: absolute; left: 0; top: -4px; width: 54px; height: 54px; border-radius: 50%; background: var(--soft); color: var(--brand); font-size: 28px; font-weight: 800; text-align: center; line-height: 54px; }
.tiles { display: flex; gap: 20px; margin-top: 38px; }
.tiles > div { flex: 1; background: rgba(255,255,255,.12); border-radius: 20px; padding: 22px 26px; }
.tiles b { display: block; font-size: 52px; line-height: 1.1; color: #fff; }
.tiles span { display: block; font-size: 22px; color: #c7d2fe; margin-top: 4px; line-height: 1.25; }
.note { margin-top: 26px; font-size: 25px; color: #6b7280; }

/* 9 */
.s9 { justify-content: center; align-items: center; text-align: center; }
.s9 .lead { margin: 26px auto 0; }
`;

const html = `<!doctype html><html lang="en"><head><meta charset="utf-8"><title>Sales Copilot</title><style>${css}</style></head><body>${slides.join('\n')}</body></html>`;
fs.writeFileSync(path.join(__dirname, 'slides.html'), html);
console.log('slides.html written,', slides.length, 'slides');
