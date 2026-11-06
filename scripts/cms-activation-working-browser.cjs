// Authorized local SQLite dashboard verification. No migrations, imports or resets.
// Creates uniquely marked fixtures, restores edited values, and checks every original CMS row.
const fs = require('fs'), path = require('path'), crypto = require('crypto');
const { spawnSync } = require('child_process');
const { chromium } = require('../storage/cms-tools/node_modules/playwright');
const { PNG } = require('../storage/cms-tools/node_modules/pngjs');
const phpBinary = 'C:/laragon/bin/php/php-8.1.10-Win32-vs16-x64/php.exe';
const database = path.resolve(process.env.CMS_ACTIVATION_DATABASE || 'storage/app/website-v1.sqlite').replace(/\\/g, '/');
const base = process.env.CMS_ACTIVATION_URL || 'http://127.0.0.1:8787';
if (!/^http:\/\/127\.0\.0\.1:878[7-9]$|^http:\/\/127\.0\.0\.1:8794$/.test(base)) throw new Error('Local activation test endpoints only.');
if (process.env.CMS_ACTIVATION_DATABASE && !database.startsWith('C:/laragon/backups/elbaz-cms/activation-')) throw new Error('Rehearsal database must be an isolated activation clone.');
const token = crypto.randomBytes(6).toString('hex'), tag = 'CMS ACTIVATION TEST ' + token;
const email = 'activation-' + token + '@example.test', password = crypto.randomBytes(32).toString('base64url');
const out = 'storage/app/cms-verification/' + (process.env.CMS_ACTIVATION_DATABASE ? 'activation-working-rehearsal' : 'activation-working'); fs.mkdirSync(out, { recursive: true });
const report = { target:{database,base,rehearsal:!!process.env.CMS_ACTIVATION_DATABASE,smoke:process.env.CMS_ACTIVATION_SMOKE||null}, checks: [], jsErrors: [], fixtures: { tag }, cleanup: null };
const env = { ...process.env, APP_ENV: 'cms', APP_URL:base, DB_CONNECTION: 'sqlite', DB_DATABASE: database, CMS_ACTIVATION_SECRET: password };
function php(code, data = {}) {
  const boot = "<?php require 'vendor/autoload.php';$app=require 'bootstrap/app.php';$app->make(Illuminate\\Contracts\\Console\\Kernel::class)->bootstrap();$d=json_decode(getenv('CMS_ACTIVATION_DATA'),true);";
  const result = spawnSync(phpBinary, ['-d', 'extension=pdo_sqlite'], { env: { ...env, CMS_ACTIVATION_DATA: JSON.stringify(data) }, input: boot + code, encoding: 'utf8' });
  if (result.status) throw new Error(result.stderr || result.stdout);
  return result.stdout ? JSON.parse(result.stdout) : null;
}
const tables = ['cms_pages','cms_sections','cms_items','cms_media','cms_submissions','cms_redirects','cms_trash','cms_tombstones'];
function snapshot() { return php("$r=[];foreach($d as $t)$r[$t]=Illuminate\\Support\\Facades\\DB::table($t)->orderBy('id')->get();echo json_encode($r);", tables); }
function check(name, pass, detail) { report.checks.push({ name, pass: !!pass, detail }); console.log((pass ? 'PASS ' : 'FAIL ') + name); }
const owned = { pages: [], items: [], media: [], sections: [], trash: [], slugs: [] }, edits = [];
let original, browser, account, rehearsalOwner, debugPage;
function idsAfter(before, after) { const ids = new Set(before.map(r=>r.id)); return after.filter(r=>!ids.has(r.id)); }
async function main() {
  const ready = php("echo json_encode(['db'=>Illuminate\\Support\\Facades\\DB::connection()->getDatabaseName(),'schema'=>Illuminate\\Support\\Facades\\Schema::hasTable('cms_trash'),'menu'=>App\\Services\\CmsMenu::isConsolidated(App\\Models\\CmsSection::where('key','global-header')->first()),'owner'=>App\\Models\\Admin::where('email','admin@demo.com')->where('role_id',0)->exists()]);");
  if (path.resolve(ready.db) !== path.resolve(database) || !ready.schema || !ready.menu) throw new Error('Working database activation preconditions failed.');
  if (!ready.owner && process.env.CMS_ACTIVATION_DATABASE) rehearsalOwner=php("echo json_encode(App\\Models\\Admin::create(['name'=>'Rehearsal fixture owner','email'=>'admin@demo.com','password'=>Illuminate\\Support\\Facades\\Hash::make(getenv('CMS_ACTIVATION_SECRET')),'role_id'=>0])->id);");
  else if (!ready.owner) throw new Error('The interactive owner account has not been created.');
  original = snapshot();
  account = php("$a=App\\Models\\Admin::create(['name'=>$d['tag'],'email'=>$d['email'],'password'=>Illuminate\\Support\\Facades\\Hash::make(getenv('CMS_ACTIVATION_SECRET')),'role_id'=>0]);echo json_encode($a->id);", { tag, email });
  browser = await chromium.launch({ executablePath: 'C:/Program Files/Google/Chrome/Application/chrome.exe', headless: true });
  const context = await browser.newContext({ viewport: { width: 1440, height: 900 } });
  context.setDefaultTimeout(30000); context.setDefaultNavigationTimeout(90000);
  const admin = await context.newPage(), page = await context.newPage();
  debugPage=admin;
  for (const tab of [admin,page]) { tab.on('pageerror',e=>report.jsErrors.push(e.message)); tab.on('dialog',d=>d.accept()); }
  const go = url => admin.goto(base + url, { waitUntil: 'domcontentloaded' });
  async function submit(button) {
    await Promise.all([admin.waitForNavigation({ waitUntil: 'domcontentloaded' }), button.click()]);
    if (await admin.locator('.alert-danger').count()) throw new Error('Dashboard validation failed: '+await admin.locator('.alert-danger').innerText());
  }
  const loginResponse=await go('/admin/login'); if(loginResponse.status()!==200)throw new Error('Login screen returned '+loginResponse.status()); await admin.locator('[name=email]').fill(email); await admin.locator('[name=password]').fill(password);
  await submit(admin.locator('form button[type=submit]').first());
  check('working-site login and dashboard', admin.url() === base+'/admin/cms');
  await admin.screenshot({ path: out+'/dashboard.png', fullPage:true });
  // An uploaded fixture is selected using the actual media selector.
  await go('/admin/cms/media'); const png=new PNG({width:640,height:430}); png.data.fill(200);
  const mediaName=tag+'.png'; await admin.locator('[name=file]').setInputFiles({name:mediaName,mimeType:'image/png',buffer:PNG.sync.write(png)});
  await submit(admin.locator('form').filter({has:admin.locator('[name=file]')}).locator('button'));
  const media=php("echo json_encode(App\\Models\\CmsMedia::where('name',$d)->firstOrFail()->only(['id','path']));",mediaName); owned.media.push(media);
  const hero=original.cms_sections.find(r=>r.key==='index-s1'); const heroContent=JSON.parse(hero.content);
  const heroValues={f3:tag+' heading ',f5:tag+' paragraph',f6:'/contact.html#appointment',f7:tag+' button ',f11:media.path};
  await go('/admin/cms/sections/'+hero.id);
  for(const [key,value] of Object.entries(heroValues)) if(key!=='f11') await admin.locator('#field-'+key).fill(value);
  await admin.locator('[data-media-select="field-f11"]').selectOption(media.path);
  edits.push({id:hero.id,original:hero.content,expected:JSON.stringify({...heroContent,...heroValues}),updated_at:hero.updated_at,page_links:hero.page_links});
  await submit(admin.locator('button',{hasText:'Save section'}));
  await page.goto(base+'/',{waitUntil:'networkidle'});
  check('heading and paragraph reach the frontend',(await page.locator('.home-hero').innerText()).includes(tag+' heading')&&(await page.locator('.home-hero').innerText()).includes(tag+' paragraph'));
  check('button label and destination reach the frontend',(await page.locator('.hero-actions a').first().innerText()).includes(tag+' button')&&(await page.locator('.hero-actions a').first().getAttribute('href')).includes('contact.html#appointment'));
  check('uploaded and selected image renders',await page.locator('.home-hero img').first().evaluate(i=>i.naturalWidth>0&&i.getAttribute('src').includes('/storage/cms/')));
  await go('/admin/cms/sections/'+hero.id); for(const key of Object.keys(heroValues)) await admin.locator('#field-'+key).fill(heroContent[key]);
  await submit(admin.locator('button',{hasText:'Save section'})); edits[0].expected=hero.content;
  async function create(type) {
    const before=snapshot(); const slug='cms-activation-'+token+'-'+type;
    await go('/admin/cms/pages?type='+type); await admin.locator('[name=title]').fill(tag+' '+type); await admin.locator('[name=slug]').fill(slug); await admin.locator('[name=summary]').fill(tag+' summary');
    if(type==='article') await admin.locator('[name=category]').fill(tag+' category');
    await submit(admin.locator('button',{hasText:type==='service'?'Create service draft':'Create article draft'}));
    const id=Number(admin.url().split('/').pop()); owned.pages.push(id); owned.slugs.push(slug);
    const after=snapshot(); owned.items.push(...idsAfter(before.cms_items,after.cms_items).map(r=>r.id));
    await admin.locator('select[name=is_visible]').selectOption('1'); await admin.locator('[name="seo[title]"]').fill(tag+' SEO'); await admin.locator('[name="seo[description]"]').fill(tag+' SEO description'); await admin.locator('[name="seo[noindex]"]').selectOption('1');
    await submit(admin.locator('button',{hasText:'Save page & SEO'}));
    await page.goto(base+'/'+slug,{waitUntil:'domcontentloaded'});
    check(type+' publication and SEO',await page.title()===tag+' SEO'&&(await page.locator('meta[name=description]').getAttribute('content'))===tag+' SEO description'&&(await page.locator('meta[name=robots]').getAttribute('content')).includes('noindex'));
    await page.goto(base+'/'+(type==='service'?'services':'articles')+'.html',{waitUntil:'domcontentloaded'});
    check(type+' automatic listing appears',await page.locator(type==='service'?'.services-grid article':'.article-card').filter({has:page.locator('a[href="/'+slug+'"]')}).count()===1);
    return {id,slug,editor:'/admin/cms/pages/'+id};
  }
  const service=await create('service');
  // Collection CRUD is confined to a new fixture page.
  const ownSection=php("echo json_encode(App\\Models\\CmsSection::where('page_id',$d)->whereHas('items')->firstOrFail()->id);",service.id);
  await go('/admin/cms/sections/'+ownSection); await submit(admin.locator('button',{hasText:'Add similar'}).first());
  const itemId=Number(admin.url().split('/').pop()); owned.items.push(itemId);
  await admin.locator('[name=label]').fill(tag+' collection'); const textKey=php("echo json_encode(collect(App\\Models\\CmsItem::findOrFail($d)->schema)->search(fn($f)=>$f['type']==='text'));",itemId); if(textKey===false)throw new Error('Fixture collection has no text field.'); await admin.locator('#field-'+textKey).fill(tag+' collection content');
  await submit(admin.locator('button',{hasText:'Save item'}));
  await page.goto(base+'/'+service.slug,{waitUntil:'domcontentloaded'}); check('collection addition and edit render',(await page.locator('body').innerText()).includes(tag+' collection content'));
  await go('/admin/cms/sections/'+ownSection); const itemRow=()=>admin.locator('tr',{hasText:tag+' collection'}).first();
  const positionBefore=php("echo json_encode(App\\Models\\CmsItem::findOrFail($d)->position);",itemId);
  await submit(itemRow().locator('[aria-label="Move up"]'));
  check('collection ordering persists',php("echo json_encode(App\\Models\\CmsItem::findOrFail($d)->position);",itemId)<positionBefore);
  await submit(itemRow().locator('button',{hasText:'Hide',exact:true})); await page.goto(base+'/'+service.slug,{waitUntil:'domcontentloaded'}); check('hidden item leaves frontend',!(await page.locator('body').innerText()).includes(tag+' collection content'));
  await submit(itemRow().locator('button',{hasText:'Show',exact:true})); await page.goto(base+'/'+service.slug,{waitUntil:'domcontentloaded'}); check('shown item returns',(await page.locator('body').innerText()).includes(tag+' collection content'));
  await go(service.editor); const types=await admin.locator('[name=type] option').evaluateAll(xs=>xs.map(x=>x.value).filter(Boolean));
  if(!types.length) throw new Error('No compatible section design offered.'); await admin.locator('[name=type]').selectOption(types[0]); await submit(admin.locator('button',{hasText:'Add section',exact:true}));
  const newSection=Number(admin.url().split('/').pop()); owned.sections.push(newSection);
  await admin.locator('[name=label]').fill(tag+' section'); await submit(admin.locator('button',{hasText:'Save section'}));
  await go(service.editor); const sectionRow=()=>admin.locator('tr',{hasText:tag+' section'}).first(); await submit(sectionRow().locator('button',{hasText:'Show',exact:true})); const sectionPosition=php("echo json_encode(App\\Models\\CmsSection::findOrFail($d)->position);",newSection); await submit(sectionRow().locator('[aria-label="Move up"]'));
  const sectionState=php("echo json_encode(App\\Models\\CmsSection::findOrFail($d)->only(['position','is_visible']));",newSection);
  check('trusted section can be added, shown and reordered',sectionState.is_visible===true&&sectionState.position<sectionPosition);
  await go(service.editor); await admin.locator('select[name=is_visible]').selectOption('0'); await submit(admin.locator('button',{hasText:'Save page & SEO'})); await page.goto(base+'/services.html',{waitUntil:'domcontentloaded'}); check('unpublishing service hides its listing',await page.locator('a[href="/'+service.slug+'"]').count()===0);
  await go(service.editor); await admin.locator('select[name=is_visible]').selectOption('1'); await submit(admin.locator('button',{hasText:'Save page & SEO'})); await page.goto(base+'/services.html',{waitUntil:'domcontentloaded'}); check('republishing service restores listing',await page.locator('.services-grid a[href="/'+service.slug+'"]').count()===1);
  if(process.env.CMS_ACTIVATION_SMOKE==='service'){await context.close();return;}
  const article=await create('article');
  await go('/admin/cms/menu'); await admin.locator('[name=label]').fill(tag+' desktop'); await admin.locator('[name=mobile_label]').fill(tag+' mobile'); await admin.locator('[name=target_page_id]').selectOption(String(article.id)); await submit(admin.locator('button',{hasText:'Add menu item'}));
  const menuId=php("echo json_encode(App\\Models\\CmsItem::where('group_key','menu')->where('target_page_id',$d)->where('label','like','CMS ACTIVATION TEST%')->firstOrFail()->id);",article.id); owned.items.push(menuId);
  await page.goto(base+'/',{waitUntil:'domcontentloaded'}); check('desktop menu label renders',(await page.locator('.main-nav').innerText()).includes(tag+' desktop'));
  await page.setViewportSize({width:390,height:900}); await page.locator('[data-menu-toggle]').click(); check('mobile menu label renders',(await page.locator('.mobile-menu__inner').innerText()).includes(tag+' mobile'));
  await go(article.editor); await admin.locator('select[name=is_visible]').selectOption('0'); await submit(admin.locator('button',{hasText:'Save page & SEO'})); await page.goto(base+'/',{waitUntil:'domcontentloaded'}); check('unpublished article leaves menu',await page.locator('.main-nav a[href="/'+article.slug+'"]').count()===0);
  await go(article.editor); await admin.locator('select[name=is_visible]').selectOption('1'); await submit(admin.locator('button',{hasText:'Save page & SEO'}));
  for(const width of [1440,768,390]) { await page.setViewportSize({width,height:900}); await page.goto(base+'/'+service.slug,{waitUntil:'networkidle'}); const state=await page.evaluate(()=>({overflow:document.documentElement.scrollWidth>document.documentElement.clientWidth,ids:[...document.querySelectorAll('[id]')].map(e=>e.id)})); check('new content layout at '+width,!state.overflow&&state.ids.length===new Set(state.ids).size); await page.screenshot({path:out+'/content-'+width+'.png',fullPage:true}); }
  const messages=original.cms_sections.find(r=>r.key==='global-messages'), messageContent=JSON.parse(messages.content);
  await go('/admin/cms/messages'); await admin.locator('#field-appointment_success').fill(tag+' success'); edits.push({id:messages.id,original:messages.content,expected:JSON.stringify({...messageContent,appointment_success:tag+' success'}),updated_at:messages.updated_at,page_links:messages.page_links}); await submit(admin.locator('button',{hasText:'Save section'}));
  await page.goto(base+'/contact.html',{waitUntil:'networkidle'}); const form=page.locator('form').first(); await form.locator('[name=name]').fill(tag); await form.locator('[name=phone]').fill('+201001234567'); await form.locator('[type=submit]').click(); await form.locator('[role=status]',{hasText:tag+' success'}).waitFor(); check('managed message and real form submission',true);
  await go('/admin/cms/messages'); await admin.locator('#field-appointment_success').fill(messageContent.appointment_success); await submit(admin.locator('button',{hasText:'Save section'})); edits[1].expected=messages.content;
  await go('/admin/cms/health'); check('content health loads and names clinic image',(await admin.locator('body').innerText()).includes('clinic.jpg'));
  await go(service.editor); await submit(admin.locator('button',{hasText:'Move page to trash'}));
  await go('/admin/cms/trash'); await submit(admin.locator('tr',{hasText:tag+' service'}).first().locator('button',{hasText:'Restore',exact:true}));
  await page.goto(base+'/'+service.slug,{waitUntil:'domcontentloaded'}); check('trashed service is restored',await page.locator('h1').innerText()===tag+' service');
  await go('/admin/cms'); await submit(admin.locator('form[action$="/admin/logout"] button')); await go('/admin/cms'); check('logout ends dashboard access',admin.url().endsWith('/admin/login'));
  await context.close();
}
async function cleanup() {
  if(!original) return;
  report.cleanup=php("$db=Illuminate\\Support\\Facades\\DB::connection();$db->transaction(function()use($db,$d){foreach($d['edits'] as $e){$current=$db->table('cms_sections')->where('id',$e['id'])->first();if(json_decode($current->content,true)!=json_decode($e['expected'],true)&&$current->content!==$e['original'])throw new RuntimeException('Unexpected concurrent section edit; refusing to overwrite.');$db->table('cms_sections')->where('id',$e['id'])->update(['content'=>$e['original'],'updated_at'=>$e['updated_at'],'page_links'=>$e['page_links']]);}foreach($d['pages'] as $id){$p=$db->table('cms_pages')->where('id',$id)->first();if($p&&!str_starts_with($p->slug,'cms-activation-'))throw new RuntimeException('Unexpected fixture page.');$db->table('cms_pages')->where('id',$id)->delete();}$db->table('cms_items')->whereIn('id',$d['items'])->delete();$db->table('cms_submissions')->where('name',$d['tag'])->delete();$db->table('cms_redirects')->whereIn('from',$d['slugs'])->delete();foreach($d['media'] as $m)$db->table('cms_media')->where('id',$m['id'])->where('path',$m['path'])->delete();$db->table('admins')->where('id',$d['account'])->where('email',$d['email'])->delete();});echo json_encode(['integrity'=>$db->select('PRAGMA integrity_check'),'foreign_keys'=>$db->select('PRAGMA foreign_key_check')]);",{...owned,edits,tag,account,email});
  for(const media of owned.media) { if(!/^\/storage\/cms\/[a-zA-Z0-9_.-]+$/.test(media.path)) throw new Error('Unexpected uploaded fixture path.'); const file=path.resolve('storage/app/public/cms',path.basename(media.path)); if(fs.existsSync(file))fs.unlinkSync(file); }
  const after=snapshot(); const changed=[];
  for(const table of tables) for(const row of original[table]) if(JSON.stringify(after[table].find(r=>r.id===row.id))!==JSON.stringify(row))changed.push(table+':'+row.id);
  report.cleanup.changedOriginalRows=changed; report.cleanup.counts=Object.fromEntries(tables.map(t=>[t,{before:original[t].length,after:after[t].length}]));
  check('every pre-existing CMS row preserved exactly',changed.length===0,changed);
  check('temporary fixture counts cleaned',tables.every(t=>original[t].length===after[t].length),report.cleanup.counts);
  if(rehearsalOwner)php("Illuminate\\Support\\Facades\\DB::table('admins')->where('id',$d)->where('email','admin@demo.com')->delete();",rehearsalOwner);
}
(async()=>{try{await main();}catch(e){report.fatal=e.stack||String(e);console.error(e.message);if(debugPage){report.failurePage=debugPage.url();report.failureTitle=await debugPage.title().catch(()=>null);await debugPage.screenshot({path:out+'/failure.png',fullPage:true}).catch(()=>{});}}finally{if(browser)await browser.close();try{await cleanup();}catch(e){report.cleanupError=e.stack||String(e);console.error('Cleanup:',e.message);}report.summary={checks:report.checks.length,failures:report.checks.filter(c=>!c.pass).length,jsErrors:report.jsErrors.length};fs.writeFileSync(out+'/report.json',JSON.stringify(report,null,2));console.log(JSON.stringify(report.summary));}process.exit(report.fatal||report.cleanupError||report.summary.failures||report.summary.jsErrors?1:0);})();
