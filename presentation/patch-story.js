// One-off patch: rewrites slides 2 and 3 of build-slides.js into a story ("Monday morning") and a "3 steps -> 1 click" slide.
const fs = require('fs');
const file = __dirname + '/build-slides.js';
let s = fs.readFileSync(file, 'utf8');
const a = s.indexOf('// 2 Problem');
const b = s.indexOf('// 4 Before / after look');
if (a < 0 || b < 0) throw new Error('markers not found (already patched?)');

const slides = `// 2 Story
slides.push(\`
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
</section>\`);

// 3 Idea
slides.push(\`
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
</section>\`);

`;
s = s.slice(0, a) + slides + s.slice(b);

const css = `/* 2 story */
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
`;
s = s.replace('/* 3 */', css + '/* 3 */');
fs.writeFileSync(file, s);
console.log('patched build-slides.js');
