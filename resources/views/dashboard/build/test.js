const os = require("os");
const fs = require("fs");
const path = require("path");


const html = fs.readFileSync(path.join(__dirname, "../index.blade.php"), 'utf8');
let htmlOutput = html.replace(/vendor.js\?v=/, 'vendor_prod.js?v=' + stats.hash)
  .replace(/pc.js\?v=/, 'pc_prod.js?v=' + stats.hash);
fs.writeFileSync(path.join(__dirname, "../index.blade.php"), htmlOutput);