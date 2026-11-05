const fs = require('fs');
const path = require('path');
const http = require('http');
const {chromium} = require('../storage/cms-tools/node_modules/playwright');
const source = path.resolve('../dr.mohamed-baz');
const mode = process.argv[2] || 'baseline';
const out = path.resolve('storage/app/cms-verification', mode);
fs.mkdirSync(out, {recursive:true});
const pages = ['index.html','about.html','services.html','articles.html','contact.html','service-details.html?service=exam','article-details.html?article=1'];
const types = {'.html':'text/html; charset=utf-8','.js':'application/javascript','.css':'text/css','.jpg':'image/jpeg','.png':'image/png','.webp':'image/webp'};
const server = http.createServer((req,res)=>{const file=path.resolve(source,decodeURIComponent(req.url.split('?')[0]).replace(/^\//,''));if(!file.startsWith(source+path.sep)){res.writeHead(403);return res.end();}fs.readFile(file,(e,b)=>{res.writeHead(e?404:200,{'Content-Type':types[path.extname(file)]||'application/octet-stream'});res.end(e?'Missing':b);});});
(async()=>{if(mode==='baseline')await new Promise(r=>server.listen(8786,'127.0.0.1',r));const browser=await chromium.launch({executablePath:'C:/Program Files/Google/Chrome/Application/chrome.exe',headless:true});const report=[];
for(const width of [1440,768,390]){const page=await browser.newPage({viewport:{width,height:900},deviceScaleFactor:1});for(const url of pages){const errors=[];page.on('pageerror',e=>errors.push(e.message));const response=await page.goto((mode==='baseline'?'http://127.0.0.1:8786/':'http://127.0.0.1:8787/')+url,{waitUntil:'networkidle'});if(response.status()!==200)throw new Error(url+': HTTP '+response.status());await page.evaluate(()=>document.fonts.ready);await page.locator('[data-range]').evaluateAll(els=>els.forEach(el=>{el.dispatchEvent(new Event('pointerdown'));el.value=50;el.dispatchEvent(new Event('input'));}));await page.screenshot({path:path.join(out,`${width}-${url.split('?')[0]}.png`),fullPage:true});
if(url==='index.html'&&width===390){await page.locator('[data-menu-toggle]').evaluate(e=>e.click());await page.locator('[data-mobile-services-trigger]').evaluate(e=>e.click());await page.waitForTimeout(500);await page.evaluate(()=>window.scrollTo(0,0));await page.screenshot({path:path.join(out,`${width}-mobile-navigation.png`),fullPage:false});}
if(url==='articles.html'&&width===1440){await page.locator('[data-article-search]').fill('جفاف');await page.screenshot({path:path.join(out,`${width}-articles-filtered.png`),fullPage:true});}
report.push({width,url,errors,images:await page.locator('img').evaluateAll(xs=>xs.filter(x=>!x.complete||!x.naturalWidth).map(x=>x.getAttribute('src')))});}await page.close();}
fs.writeFileSync(path.join(out,'report.json'),JSON.stringify(report,null,2));await browser.close();server.close();console.log(`${mode}: ${report.length} page captures saved in ${out}`);})().catch(e=>{console.error(e);process.exit(1);});
