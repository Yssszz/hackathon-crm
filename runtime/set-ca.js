// Points PHP's curl and openssl at the bundled CA file so HTTPS calls (Claude API) verify.
const fs = require('fs');
const path = require('path');
const ini = path.join(__dirname, 'php', 'php.ini');
const ca = path.join(__dirname, 'php', 'cacert.pem').replace(/\\/g, '/');
let t = fs.readFileSync(ini, 'utf8');
t = t.replace(/^;?curl\.cainfo\s*=.*$/m, `curl.cainfo = "${ca}"`).replace(/^;?openssl\.cafile\s*=.*$/m, `openssl.cafile = "${ca}"`);
fs.writeFileSync(ini, t);
console.log('ca file set to', ca);
