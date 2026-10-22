// Copies the AI Assistant and the modern theme into the SuiteCRM custom/ folder and rebuilds the CRM cache.
// Run: node install-ai-assistant.js
const fs = require('fs');
const path = require('path');
const { execFileSync } = require('child_process');

const src = path.join(__dirname, 'ai-assistant', 'custom');
const dst = path.join(__dirname, 'runtime', 'suitecrm', 'custom');

function copyDir(from, to) {
  fs.mkdirSync(to, { recursive: true });
  for (const e of fs.readdirSync(from, { withFileTypes: true })) {
    const f = path.join(from, e.name), t = path.join(to, e.name);
    if (e.isDirectory()) copyDir(f, t);
    else { fs.copyFileSync(f, t); console.log('copied', path.relative(__dirname, t)); }
  }
}

copyDir(src, dst);

// Load the modern stylesheet on every page (login included) through SuiteCRM's supported custom template override.
// The override is the stock _head.tpl plus one <link>; the core file itself is not touched.
const tpls = path.join(__dirname, 'runtime', 'suitecrm', 'themes', 'SuiteP', 'tpls');
const outDir = path.join(dst, 'themes', 'SuiteP', 'tpls');
const v = Date.now();
const link = `    <link rel="stylesheet" type="text/css" href="custom/themes/modern/modern.css?v=${v}">\n` +
  `    <script type="text/javascript" src="custom/themes/modern/modern.js?v=${v}" defer></script>\n`;
fs.mkdirSync(outDir, { recursive: true });
fs.writeFileSync(path.join(outDir, '_head.tpl'), fs.readFileSync(path.join(tpls, '_head.tpl'), 'utf8').replace('</head>', link + '</head>'));
// header.tpl includes _head.tpl by a fixed path, so the header template is overridden too, pointing at our copy.
fs.writeFileSync(path.join(outDir, 'header.tpl'),
  fs.readFileSync(path.join(tpls, 'header.tpl'), 'utf8').replace('themes/SuiteP/tpls/_head.tpl', 'custom/themes/SuiteP/tpls/_head.tpl'));
console.log('wrote custom/themes/SuiteP/tpls/_head.tpl and header.tpl');

// Second, independent route: SuiteCRM builds its main theme stylesheet and script by appending the files in
// custom/themes/SuiteP/css/<colour>/style.css and custom/themes/SuiteP/js/style.js. Putting the modern theme there
// means every page gets it, even one drawn by a template that does not load the <link> above.
const modernCss = fs.readFileSync(path.join(src, 'themes', 'modern', 'modern.css'), 'utf8')
  .replace('url("fonts/inter-var.woff2")', 'url("/custom/themes/modern/fonts/inter-var.woff2")');
for (const sub of ['Dawn', 'Day', 'Dusk', 'Night', 'Noon']) {
  const d = path.join(dst, 'themes', 'SuiteP', 'css', sub);
  fs.mkdirSync(d, { recursive: true });
  fs.writeFileSync(path.join(d, 'style.css'), modernCss);
}
const jsDir = path.join(dst, 'themes', 'SuiteP', 'js');
fs.mkdirSync(jsDir, { recursive: true });
fs.writeFileSync(path.join(jsDir, 'style.js'), fs.readFileSync(path.join(src, 'themes', 'modern', 'modern.js'), 'utf8'));
console.log('wrote theme-level style.css (5 colour sub-themes) and style.js');

const php = path.join(__dirname, 'runtime', 'php', 'php.exe');
const ini = path.join(__dirname, 'runtime', 'php', 'php.ini');
console.log(execFileSync(php, ['-c', ini, 'crm-php/rebuild.php'], { cwd: __dirname }).toString().trim());
// new ?v= on every theme file, so browsers never keep serving an older cached copy
console.log(execFileSync(php, ['-c', ini, 'crm-php/bump_assets.php'], { cwd: __dirname }).toString().trim().split('\n').pop());
