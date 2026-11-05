const fs=require('fs');
const {chromium}=require('../storage/cms-tools/node_modules/playwright');
const pages=JSON.parse(fs.readFileSync('database/content/website.json','utf8')).pages;
(async()=>{const browser=await chromium.launch({executablePath:'C:/Program Files/Google/Chrome/Application/chrome.exe',headless:true});try{const context=await browser.newContext();const page=await context.newPage();const failures=[],errors=[];let current='';
page.on('response',r=>{if(r.status()>=400)failures.push({page:current,url:r.url(),status:r.status(),type:r.request().resourceType()});});page.on('requestfailed',r=>failures.push({page:current,url:r.url(),error:r.failure()?.errorText,type:r.resourceType()}));page.on('pageerror',e=>errors.push({page:current,error:e.message}));
for(const record of pages){current=record.slug||'index.html';await page.goto('http://127.0.0.1:8787/'+current,{waitUntil:'networkidle'});await page.evaluate(()=>document.fonts.ready);}
const report={pages:pages.length,failures,errors};fs.writeFileSync('storage/app/cms-verification/final-resource-report.json',JSON.stringify(report,null,2));console.log(JSON.stringify(report,null,2));}finally{await browser.close();}})().catch(e=>{console.error(e);process.exit(1);});
