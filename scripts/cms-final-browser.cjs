const fs=require('fs'),path=require('path'),http=require('http'),crypto=require('crypto');
const {spawnSync}=require('child_process');
const {chromium}=require('../storage/cms-tools/node_modules/playwright');
const {PNG}=require('../storage/cms-tools/node_modules/pngjs');
const cheerio=require('../storage/cms-tools/node_modules/cheerio');
const root=path.resolve('storage/app/cms-verification/final');fs.mkdirSync(root,{recursive:true});
const source=path.resolve('../dr.mohamed-baz');const base='http://127.0.0.1:8791';const original='http://127.0.0.1:8792';
const php='C:/laragon/bin/php/php-8.1.10-Win32-vs16-x64/php.exe';
const env={...process.env,APP_ENV:'cms',APP_URL:base,DB_CONNECTION:'sqlite',DB_DATABASE:path.resolve('storage/app/cms-final-audit.sqlite')};
const bootstrap="require 'vendor/autoload.php';$app=require 'bootstrap/app.php';$app->make(Illuminate\\Contracts\\Console\\Kernel::class)->bootstrap();";
function run(code){const p=spawnSync(php,['-d','extension=pdo_sqlite','-r',bootstrap+code],{env,encoding:'utf8'});if(p.status)throw new Error(p.stderr||p.stdout);return p.stdout;}
const seed=JSON.parse(fs.readFileSync('database/content/website.json','utf8'));
const server=http.createServer((req,res)=>{let file=path.resolve(source,decodeURIComponent(req.url.split('?')[0]).replace(/^\//,'')||'index.html');if(!file.startsWith(source+path.sep)){res.writeHead(403);return res.end();}fs.readFile(file,(err,data)=>{const ext=path.extname(file);res.writeHead(err?404:200,{'Content-Type':({'.html':'text/html;charset=utf-8','.js':'application/javascript','.css':'text/css','.png':'image/png','.jpg':'image/jpeg','.webp':'image/webp'})[ext]||'application/octet-stream'});res.end(err?'Missing':data);});});
const email='final-audit-'+crypto.randomBytes(5).toString('hex')+'@example.test',password=crypto.randomBytes(24).toString('base64url');
const report={publicPages:[],links:[],placeholders:[],visuals:[],adminScreens:[],edits:[],errors:[]};
function safe(file){return file.replace(/[?=&/]/g,'-')||'index';}
async function settle(page){await page.evaluate(()=>document.fonts.ready);await page.locator('[data-range]').evaluateAll(xs=>xs.forEach(x=>{x.dispatchEvent(new Event('pointerdown'));x.value=50;x.dispatchEvent(new Event('input'));}));}
function compare(a,b,file){const p=PNG.sync.read(a),q=PNG.sync.read(b);const r={file,baseline:[p.width,p.height],integrated:[q.width,q.height],changedPixels:null};if(p.width===q.width&&p.height===q.height){let count=0;for(let i=0;i<p.data.length;i+=4)if(p.data[i]!==q.data[i]||p.data[i+1]!==q.data[i+1]||p.data[i+2]!==q.data[i+2]||p.data[i+3]!==q.data[i+3])count++;r.changedPixels=count;}report.visuals.push(r);return r;}
async function saveForm(page){await Promise.all([page.waitForNavigation({waitUntil:'networkidle'}),page.locator('form').filter({has:page.locator('input[name=_method][value=PUT]')}).first().locator('button').filter({hasText:/Save/}).first().click()]);if(await page.locator('.alert-danger').count())throw new Error(await page.locator('.alert-danger').innerText());}
(async()=>{await new Promise(resolve=>server.listen(8792,'127.0.0.1',resolve));let browser;
run(`App\\Models\\Admin::create(['name'=>'Final browser audit','email'=>'${email}','password'=>Illuminate\\Support\\Facades\\Hash::make('${password}'),'role_id'=>0]);`);
try{
 browser=await chromium.launch({executablePath:'C:/Program Files/Google/Chrome/Application/chrome.exe',headless:true});
 const context=await browser.newContext({viewport:{width:1440,height:900},deviceScaleFactor:1});const page=await context.newPage();page.on('pageerror',e=>report.errors.push({url:page.url(),error:e.message}));
 const links=new Map();
 for(const width of [1440,768,390]){
  await page.setViewportSize({width,height:900});const reference=await context.newPage();await reference.setViewportSize({width,height:900});
  for(const record of seed.pages){const slug=record.slug||'index.html';const filename=width+'-'+safe(slug)+'.png';
   const response=await page.goto(base+'/'+slug,{waitUntil:'networkidle'});await settle(page);await reference.goto(original+'/'+slug,{waitUntil:'networkidle'});await settle(reference);
   const a=await reference.screenshot({path:path.join(root,'original-'+filename),fullPage:true});const b=await page.screenshot({path:path.join(root,'laravel-'+filename),fullPage:true});compare(a,b,filename);
   if(width===1440){const html=await page.content();const $=cheerio.load(html);const missing=await page.locator('img').evaluateAll(xs=>xs.filter(x=>!x.naturalWidth).map(x=>x.getAttribute('src')));report.publicPages.push({key:record.key,url:slug,status:response.status(),missingImages:missing,duplicateIds:await page.locator('[id]').evaluateAll(xs=>xs.map(x=>x.id).filter((x,i,a)=>a.indexOf(x)!==i))});
    $('a[href]').each((_,el)=>{const href=$(el).attr('href');if(href==='#'){report.placeholders.push({page:slug,href,label:$(el).text().trim()||$(el).attr('aria-label')});return;}let target;try{target=new URL(href,page.url());}catch{return;}if(target.origin===base)links.set(target.pathname+target.search+target.hash,{page:slug,target:target.href});});
   }
  }await reference.close();console.log('Captured all 22 pages at '+width+'px.');
 }
 for(const entry of links.values()){const res=await context.request.get(entry.target);const body=await res.text();let anchor=null;const hash=new URL(entry.target).hash;if(hash){const $=cheerio.load(body);anchor=$('[id]').toArray().some(el=>$(el).attr('id')===decodeURIComponent(hash.slice(1)));}report.links.push({...entry,status:res.status(),anchorExists:anchor});}
 await page.setViewportSize({width:1440,height:900});await page.goto(base+'/admin/login');await page.locator('[name=email]').fill(email);await page.locator('[name=password]').fill(password);await Promise.all([page.waitForURL('**/admin/cms'),page.locator('form button[type=submit]').click()]);
 const records=JSON.parse(run("echo json_encode(['section'=>App\\Models\\CmsSection::where('key','index-s1')->first()->toArray(),'page'=>App\\Models\\CmsPage::where('key','index')->first()->toArray(),'item'=>App\\Models\\CmsSection::where('key','index-s2')->first()->items()->first()->toArray()]);"));
 for(const route of ['','/pages','/pages?type=service','/pages?type=article','/globals','/media','/submissions','/users','/pages/'+records.page.id,'/sections/'+records.section.id,'/items/'+records.item.id]){const res=await page.goto(base+'/admin/cms'+route,{waitUntil:'networkidle'});report.adminScreens.push({route,status:res.status()});if(res.status()!==200)throw new Error('Admin screen failed: '+route);}
 // A no-op editor save must not collapse whitespace around inline spans/icons.
 await page.goto(base+'/');await settle(page);const unchanged=await page.screenshot({fullPage:true});
 await page.goto(base+'/admin/cms/sections/'+records.section.id);await saveForm(page);await page.goto(base+'/');await settle(page);compare(unchanged,await page.screenshot({fullPage:true}),'after-unchanged-cms-save.png');
 const content=records.section.content;
 const edits={f3:'رؤية أوضح بعد تحرير المحتوى ',f5:'نص فقرة تمت مراجعته من خلال لوحة إدارة المحتوى.',f6:'/contact.html#appointment',f7:'احجز الآن ',f11:'/website/assets/images/Blog/blog-2.jpg'};
 await page.goto(base+'/admin/cms/sections/'+records.section.id);for(const [key,value] of Object.entries(edits))await page.locator('#field-'+key).fill(value);await saveForm(page);
 await page.goto(base+'/');await settle(page);
 const reference=await context.newPage();await reference.goto(original+'/index.html',{waitUntil:'networkidle'});await settle(reference);
 await reference.evaluate(e=>{document.querySelector('.home-hero h1').firstChild.textContent=e.f3;document.querySelector('.home-hero__content > p').textContent=e.f5;const button=document.querySelector('.hero-actions a');button.firstChild.textContent=e.f7;button.setAttribute('href',e.f6);document.querySelector('.doctor-arch img').src=e.f11.replace('/website/','/');},edits);await reference.locator('.doctor-arch img').evaluate(img=>img.decode());
 compare(await reference.screenshot({path:path.join(root,'edited-original.png'),fullPage:true}),await page.screenshot({path:path.join(root,'edited-laravel.png'),fullPage:true}),'equivalent-content-after-cms-edits.png');await reference.close();report.edits.push({heading:true,paragraph:true,image:true,buttonLabel:true,buttonLink:await page.locator('.hero-actions a').first().getAttribute('href')});
 // Restore only the isolated audit copy, through the same editor.
 await page.goto(base+'/admin/cms/sections/'+records.section.id);for(const [key,value] of Object.entries(content)){const input=page.locator('#field-'+key);if(await input.count())await input.fill(value);}await saveForm(page);
 await page.goto(base+'/admin/cms/pages/'+records.page.id);await page.locator('[name="seo[title]"]').fill('Final audit SEO title');await page.locator('[name="seo[description]"]').fill('Final audit description');await page.locator('[name="seo[og_title]"]').fill('Final audit social title');await saveForm(page);await page.goto(base+'/');if(await page.title()!=='Final audit SEO title')throw new Error('SEO editor did not update title');report.edits.push({seoTitle:true,seoDescription:await page.locator('meta[name=description]').getAttribute('content'),openGraph:await page.locator('meta[property="og:title"]').getAttribute('content')});
 // Real media upload and alt edit through Velzon forms.
 await page.goto(base+'/admin/cms/media');const png=new PNG({width:8,height:8});png.data.fill(255);await page.locator('[name=file]').setInputFiles({name:'final-audit.png',mimeType:'image/png',buffer:PNG.sync.write(png)});await page.locator('form').filter({has:page.locator('[name=file]')}).locator('button').click();await page.waitForLoadState('networkidle');if(!await page.locator('h6').filter({hasText:'final-audit.png'}).count())throw new Error('Media upload did not appear');report.edits.push({mediaUpload:true});
 await page.goto(base+'/contact.html');await page.locator('[name=name]').fill('Final audit patient');await page.locator('[name=phone]').fill('+201001234567');await page.locator('[name=details]').fill('Final audit browser submission');await page.locator('form [type=submit]').click();await page.getByRole('status').filter({hasText:'تم إرسال طلبك بنجاح'}).waitFor();report.edits.push({contactSubmitted:true});await page.goto(base+'/admin/cms/submissions');if(!await page.getByText('Final audit browser submission').count())throw new Error('Submission missing from admin');
 const unauthorized=await browser.newContext();const denied=await unauthorized.request.put(base+'/admin/cms/pages/'+records.page.id,{data:{title:'Forbidden'},maxRedirects:0});report.anonymousMutationStatus=denied.status();await unauthorized.close();
 fs.writeFileSync(path.join(root,'browser-report.json'),JSON.stringify(report,null,2));
 const failures=report.publicPages.filter(p=>p.status!==200).length+report.adminScreens.filter(p=>p.status!==200).length+report.errors.length+report.visuals.filter(p=>p.changedPixels!==0).length;
 console.log(JSON.stringify({pages:report.publicPages.length,comparisons:report.visuals.length,visualMismatches:report.visuals.filter(p=>p.changedPixels!==0).length,adminScreens:report.adminScreens.length,jsErrors:report.errors.length,brokenLinks:report.links.filter(l=>l.status>=400||l.anchorExists===false),failures},null,2));if(failures)process.exitCode=1;
}finally{if(browser)await browser.close();server.close();}})().catch(e=>{fs.writeFileSync(path.join(root,'browser-failure.txt'),e.stack||String(e));console.error(e);server.close();process.exit(1);});
