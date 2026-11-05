// Real-browser verification of the completed CMS against the original static site.
// Works on a backup copy of the working database, on its own port. Nothing here touches
// storage/app/website-v1.sqlite, .env, .env.cms, MySQL, or the original source.
// Usage: node scripts/cms-completion-browser.cjs [stage,stage,...]   (default: all stages)
const fs = require('fs'), path = require('path'), http = require('http'), crypto = require('crypto');
const { spawn, spawnSync } = require('child_process');
const { chromium } = require('../storage/cms-tools/node_modules/playwright');
const { PNG } = require('../storage/cms-tools/node_modules/pngjs');
const cheerio = require('../storage/cms-tools/node_modules/cheerio');

// CMS_COMPLETION_TARGET=live compares the running 8787 site with GET requests only: no copy, no upgrade.
const live = process.env.CMS_COMPLETION_TARGET === 'live';
const out = path.resolve(live ? 'storage/app/cms-verification/completion-unupgraded' : 'storage/app/cms-verification/completion');
const reportFile = 'storage/app/cms-verification/' + (live ? 'completion-report-unupgraded.json' : 'completion-report.json');
fs.mkdirSync(out, { recursive: true });
const php = 'C:/laragon/bin/php/php-8.1.10-Win32-vs16-x64/php.exe';
const chrome = 'C:/Program Files/Google/Chrome/Application/chrome.exe';
const copy = path.resolve('storage/app/cms-completion.sqlite').replace(/\\/g, '/');
const source = path.resolve('../dr.mohamed-baz');
const base = live ? 'http://127.0.0.1:8787' : 'http://127.0.0.1:8793', original = 'http://127.0.0.1:8792';
const env = { ...process.env, APP_ENV: 'cms', APP_URL: base, DB_CONNECTION: 'sqlite', DB_DATABASE: copy };
const seed = JSON.parse(fs.readFileSync('database/content/website.json', 'utf8'));
const stages = (process.argv[2] || (live ? 'visual,states,live,crawl' : 'visual,states,live,crawl,screens,noop,edit,service,article,menu,sections,messages,security,final,health')).split(',');
const readOnlyStages = ['visual', 'states', 'live', 'crawl'];
if (live && stages.some((stage) => !readOnlyStages.includes(stage))) throw new Error('The live site is only compared with read-only stages.');
const uploadsDir = path.resolve('storage/app/public/cms');
const uploadsBefore = new Set(fs.existsSync(uploadsDir) ? fs.readdirSync(uploadsDir) : []);
const email = 'completion-' + crypto.randomBytes(5).toString('hex') + '@example.test';
const password = crypto.randomBytes(24).toString('base64url');
const widths = [1440, 768, 390];
const report = { stages, prepare: null, upgrade: null, visuals: [], checks: [], jsErrors: [], resources: [], links: { checked: 0, broken: [], placeholders: 0 }, adminScreens: [] };

function artisan(...args) {
  const p = spawnSync(php, ['-d', 'extension=pdo_sqlite', 'artisan', ...args, '--no-ansi'], { env, encoding: 'utf8' });
  if (p.status) throw new Error('artisan ' + args.join(' ') + ': ' + (p.stderr || p.stdout));
  return p.stdout;
}
function evalPhp(code) {
  const boot = "require 'vendor/autoload.php';$app=require 'bootstrap/app.php';$app->make(Illuminate\\Contracts\\Console\\Kernel::class)->bootstrap();";
  const p = spawnSync(php, ['-d', 'extension=pdo_sqlite', '-r', boot + code], { env, encoding: 'utf8' });
  if (p.status) throw new Error(p.stderr || p.stdout);
  return p.stdout;
}
function check(name, pass, detail) {
  report.checks.push({ name, pass: !!pass, detail: detail === undefined ? null : detail });
  console.log((pass ? 'PASS ' : 'FAIL ') + name + (pass || detail === undefined ? '' : ' :: ' + JSON.stringify(detail)));
}
function safe(file) { return file.replace(/[?=&/]/g, '-') || 'index'; }
function pixels(a, b) {
  const p = PNG.sync.read(a), q = PNG.sync.read(b);
  if (p.width !== q.width || p.height !== q.height) return { size: [[p.width, p.height], [q.width, q.height]], changedPixels: null };
  let count = 0;
  for (let i = 0; i < p.data.length; i += 4) if (p.data[i] !== q.data[i] || p.data[i + 1] !== q.data[i + 1] || p.data[i + 2] !== q.data[i + 2] || p.data[i + 3] !== q.data[i + 3]) count++;
  return { size: [p.width, p.height], changedPixels: count };
}
const staticServer = http.createServer((req, res) => {
  const url = new URL(req.url, 'http://static.invalid');
  const file = path.resolve(source, decodeURIComponent(url.pathname).replace(/^\//, '') || 'index.html');
  if (!file.startsWith(source + path.sep)) { res.writeHead(403); return res.end(); }
  fs.readFile(file, (err, data) => {
    const types = { '.html': 'text/html;charset=utf-8', '.js': 'application/javascript', '.css': 'text/css', '.png': 'image/png', '.jpg': 'image/jpeg', '.webp': 'image/webp' };
    res.writeHead(err ? 404 : 200, { 'Content-Type': types[path.extname(file)] || 'application/octet-stream' });
    if (err) return res.end('Missing');
    // ?hero=assets/images/... serves the home page with that image as the hero's *initial* image, in memory only.
    // A script-swapped image is painted through a different browser path than an initially loaded one (the
    // original differs from itself by up to 21 pixels between the two), so the edit must be loaded the same way on both sides.
    const hero = url.searchParams.get('hero');
    if (hero && /^assets\/images\/[\w\/.-]+$/.test(hero) && path.basename(file) === 'index.html') data = Buffer.from(data.toString('utf8').replace('src="assets/images/home/dr.baz.webp"', 'src="' + hero + '"'));
    res.end(data);
  });
});
async function settle(page) {
  await page.evaluate(() => document.fonts.ready);
  await page.locator('[data-range]').evaluateAll((xs) => xs.forEach((x) => { x.dispatchEvent(new Event('pointerdown')); x.value = 50; x.dispatchEvent(new Event('input')); }));
}
// The original injects its floating buttons with fetch(); Laravel renders them server-side. The content
// is identical, but Chrome's raster history differs, which can change 12 anti-aliased pixels where the
// two buttons' shadows overlap, and the original also differs from itself the same way. Repainting
// that one layer on both sites before capture removes this: 32 of 32 comparisons were then exact.
async function repaintFloatingLayer(page) {
  await page.evaluate(async () => {
    const layer = document.querySelector('.floating-contact');
    const frame = () => new Promise((resolve) => requestAnimationFrame(resolve));
    layer.style.display = 'none';
    layer.getBoundingClientRect();
    await frame(); await frame();
    layer.style.display = '';
    layer.getBoundingClientRect();
    await frame(); await frame();
  });
}
async function layout(page) {
  return page.evaluate(() => {
    const root = document.documentElement, ids = [...document.querySelectorAll('[id]')].map((e) => e.id);
    return { scrollWidth: root.scrollWidth, clientWidth: root.clientWidth, overflow: root.scrollWidth > root.clientWidth, duplicateIds: ids.length - new Set(ids).size };
  });
}

(async () => {
  if (!live) {
  report.prepare = JSON.parse(spawnSync(php, ['-d', 'extension=sqlite3', 'scripts/cms-completion-prepare.php', copy], { encoding: 'utf8' }).stdout);
  check('source database unchanged by the copy', report.prepare.source_sha256_before === report.prepare.source_sha256_after);
  check('copy passes SQLite integrity check', report.prepare.integrity === 'ok');
  const migration = 'database/migrations/2026_11_05_000002_add_cms_content_relationships.php';
  artisan('migrate', '--path=' + migration, '--force');
  report.dryRun = artisan('website:upgrade', '--dry-run');
  report.upgrade = artisan('website:upgrade');
  report.upgradeRepeat = artisan('website:upgrade');
  console.log('Upgrade:', report.upgrade.replace(/\s+/g, ' '));
  const repeat = JSON.parse(report.upgradeRepeat);
  check('repeating the upgrade changes nothing', repeat.menu_migrated === false && repeat.linked_placements === 0 && repeat.page_listings === 0 && repeat.map_fields === 0 && repeat.pruned_media_records === 0, repeat);
  report.afterUpgrade = JSON.parse(evalPhp("echo json_encode(['pages'=>App\\Models\\CmsPage::count(),'sections'=>App\\Models\\CmsSection::count(),'items'=>App\\Models\\CmsItem::count(),'media'=>App\\Models\\CmsMedia::count(),'menu_top'=>App\\Models\\CmsItem::where('group_key','menu')->count(),'menu_children'=>App\\Models\\CmsItem::where('group_key','menu-child')->count(),'linked_items'=>App\\Models\\CmsItem::whereNotNull('target_page_id')->count(),'sections_with_page_links'=>App\\Models\\CmsSection::whereNotNull('page_links')->get()->filter(fn(\$s)=>\$s->page_links)->count(),'pages_with_listing'=>App\\Models\\CmsPage::whereNotNull('listing')->count(),'collection_groups'=>App\\Models\\CmsSection::all()->sum(fn(\$s)=>count(\$s->groups)),'archives'=>App\\Models\\CmsTrashEntry::where('type','archive')->count(),'tombstones'=>Illuminate\\Support\\Facades\\DB::table('cms_tombstones')->count()]);"));
  console.log('After upgrade:', JSON.stringify(report.afterUpgrade));
  report.healthAfterUpgrade = JSON.parse(evalPhp("echo json_encode(collect(app(App\\Services\\CmsContentHealth::class)->report())->map(fn($rows)=>count($rows)));"));
  console.log('Content health:', JSON.stringify(report.healthAfterUpgrade));
  const created = spawnSync(php, ['-d', 'extension=pdo_sqlite', '-r', "require 'vendor/autoload.php';$app=require 'bootstrap/app.php';$app->make(Illuminate\\Contracts\\Console\\Kernel::class)->bootstrap();App\\Models\\Admin::create(['name'=>'Completion check','email'=>'" + email + "','password'=>Illuminate\\Support\\Facades\\Hash::make('" + password + "'),'role_id'=>0]);"], { env, encoding: 'utf8' });
  if (created.status) throw new Error(created.stderr || created.stdout);
  }

  await new Promise((resolve) => staticServer.listen(8792, '127.0.0.1', resolve));
  const app = live ? null : spawn(php, ['-d', 'extension=pdo_sqlite', '-S', '127.0.0.1:8793', '-t', 'public', 'server.php'], { env, stdio: 'ignore' });
  await new Promise((resolve) => setTimeout(resolve, 1500));
  const browser = await chromium.launch({ executablePath: chrome, headless: true });
  // External fonts and icons load from CDNs, so 'networkidle' can stall; allow it time instead of failing a check.
  const makeContext = browser.newContext.bind(browser);
  browser.newContext = async (...args) => { const context = await makeContext(...args); context.setDefaultNavigationTimeout(90000); context.setDefaultTimeout(60000); return context; };
  const watch = (page, label) => {
    page.on('pageerror', (e) => report.jsErrors.push({ at: label, url: page.url(), error: e.message }));
    page.on('dialog', (d) => d.accept());
  };
  try {
    // Timers are paused only in this context, so carousels show deterministic content.
    const still = await browser.newContext({ viewport: { width: 1440, height: 900 }, deviceScaleFactor: 1 });
    await still.addInitScript(() => { if (!location.pathname.startsWith('/admin')) window.setInterval = () => 0; });
    const page = await still.newPage(), reference = await still.newPage();
    watch(page, 'still');

    async function compareToOriginal(slug, width, label) {
      await page.setViewportSize({ width, height: 900 });
      await reference.setViewportSize({ width, height: 900 });
      await page.goto(base + '/' + slug, { waitUntil: 'networkidle' }); await settle(page);
      await reference.goto(original + '/' + slug, { waitUntil: 'networkidle' }); await settle(reference);
      await reference.bringToFront();
      const a = await reference.screenshot({ path: path.join(out, 'original-' + width + '-' + safe(slug) + '.png'), fullPage: true });
      await page.bringToFront();
      const b = await page.screenshot({ path: path.join(out, label + '-' + width + '-' + safe(slug) + '.png'), fullPage: true });
      const result = { label, slug, width, ...pixels(a, b) };
      report.visuals.push(result);
      if (result.changedPixels !== 0) console.log('VISUAL DIFF', JSON.stringify(result));
      return result;
    }

    if (stages.includes('visual')) {
      for (const width of widths) {
        for (const record of seed.pages) await compareToOriginal(record.slug || 'index.html', width, 'laravel');
        console.log('Captured all pages at', width);
      }
      const set = report.visuals.filter((v) => v.label === 'laravel');
      check('66 page and viewport comparisons have zero changed pixels', set.length === 66 && set.every((v) => v.changedPixels === 0), set.filter((v) => v.changedPixels !== 0));
    }

    if (stages.includes('states')) {
      const captureState = async (state, width) => {
        const shots = [];
        for (const [name, root] of [['original', original], ['laravel', base]]) {
          const tab = await still.newPage({ viewport: { width, height: 900 } });
          await tab.setViewportSize({ width, height: 900 });
          tab.on('pageerror', (e) => report.jsErrors.push({ at: state, error: e.message }));
          await tab.goto(root + (state === 'mobile-navigation' ? '/index.html' : '/articles.html'), { waitUntil: 'networkidle' });
          await tab.evaluate(() => document.fonts.ready);
          if (state === 'mobile-navigation') {
            await tab.locator('[data-range]').evaluate((e) => { e.dispatchEvent(new Event('pointerdown')); e.value = 50; e.dispatchEvent(new Event('input')); });
            await tab.locator('[data-menu-toggle]').evaluate((e) => e.click());
            await tab.locator('[data-mobile-services-trigger]').evaluate((e) => e.click());
            await tab.waitForTimeout(500);
            await tab.evaluate(() => window.scrollTo(0, 0));
            await repaintFloatingLayer(tab);
          } else await tab.locator('[data-article-search]').fill('جفاف');
          shots.push(await tab.screenshot({ path: path.join(out, name + '-' + state + '.png'), fullPage: state !== 'mobile-navigation' }));
          await tab.close();
        }
        return pixels(shots[0], shots[1]);
      };
      for (const state of ['mobile-navigation', 'filtered-articles']) {
        const width = state === 'mobile-navigation' ? 390 : 1440;
        const outcome = await captureState(state, width);
        const result = { label: 'state', slug: state, width, ...outcome };
        report.visuals.push(result);
        check('interactive state matches the original: ' + state, result.changedPixels === 0, result);
      }
    }

    if (stages.includes('live')) {
      const context = await browser.newContext({ viewport: { width: 390, height: 900 } });
      const tab = await context.newPage(); watch(tab, 'live');
      await tab.goto(base + '/', { waitUntil: 'networkidle' });
      await tab.locator('[data-menu-toggle]').click();
      check('mobile menu opens', await tab.locator('[data-mobile-menu]').evaluate((e) => e.classList.contains('is-open')));
      await tab.locator('[data-mobile-services-trigger]').click();
      check('mobile nested menu opens', (await tab.locator('[data-mobile-services-trigger]').getAttribute('aria-expanded')) === 'true');
      await tab.locator('[data-mobile-services-menu] a').first().waitFor({ state: 'visible' });
      check('mobile nested links are visible', await tab.locator('[data-mobile-services-menu] a').first().isVisible());
      await tab.locator('[data-mobile-services-trigger]').click();
      check('mobile nested menu closes', (await tab.locator('[data-mobile-services-trigger]').getAttribute('aria-expanded')) === 'false');
      await tab.locator('[data-range]').fill('70');
      await tab.locator('[data-range]').dispatchEvent('input');
      check('before/after slider responds', await tab.locator('[data-compare]').evaluate((e) => e.style.getPropertyValue('--compare-pos') === '70%'));
      const first = await tab.locator('[data-home-article-title]').textContent();
      await tab.waitForTimeout(4900);
      check('home carousel advances', (await tab.locator('[data-home-article-title]').textContent()) !== first);
      await tab.setViewportSize({ width: 1440, height: 900 });
      await tab.goto(base + '/', { waitUntil: 'networkidle' });
      await tab.locator('.nav-dropdown__trigger').hover();
      await tab.waitForTimeout(400);
      check('desktop dropdown opens on hover', await tab.locator('.nav-dropdown__menu a').first().isVisible());
      check('desktop active item comes from the destination', (await tab.locator('.main-nav a.active').count()) === 1 && (await tab.locator('.main-nav a.active').getAttribute('href')) === 'index.html');
      await tab.goto(base + '/service-details.html?service=retina', { waitUntil: 'networkidle' });
      check('service detail highlights the services trigger', await tab.locator('.nav-dropdown__trigger').evaluate((e) => e.classList.contains('active')));
      await tab.goto(base + '/articles.html', { waitUntil: 'networkidle' });
      const total = await tab.locator('.article-card:visible').count();
      await tab.locator('[data-article-search]').fill('جفاف');
      check('article search filters', (await tab.locator('.article-card:visible').count()) < total);
      await tab.locator('[data-article-search]').fill('');
      const category = await tab.locator('[data-article-filter] option').nth(1).getAttribute('value');
      await tab.locator('[data-article-filter]').selectOption(category);
      check('article category filter works', (await tab.locator('.article-card:visible').count()) < total);
      await context.close();
    }

    if (stages.includes('crawl')) {
      const context = await browser.newContext();
      const tab = await context.newPage(); watch(tab, 'crawl');
      let current = '';
      tab.on('response', (r) => { if (r.status() >= 400) report.resources.push({ page: current, url: r.url(), status: r.status(), type: r.request().resourceType() }); });
      tab.on('requestfailed', (r) => report.resources.push({ page: current, url: r.url(), error: r.failure()?.errorText, type: r.resourceType() }));
      const links = new Map();
      for (const record of seed.pages) {
        current = record.slug || 'index.html';
        await tab.goto(base + '/' + current, { waitUntil: 'networkidle' });
        await tab.evaluate(() => document.fonts.ready);
        const $ = cheerio.load(await tab.content());
        $('a[href]').each((_, el) => {
          const href = $(el).attr('href');
          if (href === '#') { report.links.placeholders++; return; }
          let target; try { target = new URL(href, tab.url()); } catch { report.links.broken.push({ page: current, href, error: 'invalid' }); return; }
          if (target.origin === base) links.set(target.href, current);
        });
      }
      for (const [target, from] of links) {
        const res = await context.request.get(target);
        report.links.checked++;
        let anchor = true;
        const hash = new URL(target).hash;
        if (hash) { const $ = cheerio.load(await res.text()); anchor = $('[id]').toArray().some((el) => $(el).attr('id') === decodeURIComponent(hash.slice(1))); }
        if (res.status() !== 200 || !anchor) report.links.broken.push({ page: from, href: target, status: res.status(), anchor });
      }
      check('no broken internal links or fragments', report.links.broken.length === 0, report.links.broken);
      const failed = report.resources.filter((r) => !r.url.endsWith('/website/assets/images/about/clinic.jpg'));
      check('only the known clinic image fails to load', failed.length === 0 && report.resources.length === 2, report.resources);
      await context.close();
    }

    // Everything below uses the dashboard the way an editor would.
    if (stages.some((stage) => !readOnlyStages.includes(stage))) {
    const adminContext = await browser.newContext({ viewport: { width: 1440, height: 900 } });
    const admin = await adminContext.newPage(); watch(admin, 'admin');
    await admin.goto(base + '/admin/login');
    await admin.locator('[name=email]').fill(email);
    await admin.locator('[name=password]').fill(password);
    await Promise.all([admin.waitForURL('**/admin/cms'), admin.locator('form button[type=submit]').first().click()]);
    async function submit(locator) {
      await Promise.all([admin.waitForNavigation({ waitUntil: 'networkidle' }), locator.click()]);
      if (await admin.locator('.alert-danger').count()) throw new Error('Form error at ' + admin.url() + ': ' + await admin.locator('.alert-danger').innerText());
      return admin.url();
    }
    const pageId = (key) => Number(evalPhp("echo App\\Models\\CmsPage::where('key','" + key + "')->value('id');"));
    const sectionId = (key) => Number(evalPhp("echo App\\Models\\CmsSection::where('key','" + key + "')->value('id');"));

    if (stages.includes('screens')) {
      const ids = JSON.parse(evalPhp("echo json_encode(['page'=>App\\Models\\CmsPage::where('key','services')->value('id'),'section'=>App\\Models\\CmsSection::where('key','services-s2')->value('id'),'item'=>App\\Models\\CmsSection::where('key','services-s2')->first()->items()->value('id'),'menu'=>App\\Models\\CmsItem::where('group_key','menu')->value('id')]);"));
      for (const route of ['', '/pages', '/pages?type=service', '/pages?type=article', '/menu', '/menu/' + ids.menu, '/globals', '/messages', '/media', '/submissions', '/users', '/health', '/trash', '/pages/' + ids.page, '/sections/' + ids.section, '/items/' + ids.item]) {
        const res = await admin.goto(base + '/admin/cms' + route, { waitUntil: 'networkidle' });
        report.adminScreens.push({ route: route || '/', status: res.status() });
        await admin.screenshot({ path: path.join(out, 'admin' + (route || '-dashboard').replace(/[/?=]/g, '-') + '.png'), fullPage: true });
      }
      check('all dashboard screens load', report.adminScreens.every((s) => s.status === 200), report.adminScreens.filter((s) => s.status !== 200));
    }

    if (stages.includes('noop')) {
      await admin.goto(base + '/admin/cms/sections/' + sectionId('index-s1'));
      await submit(admin.locator('button', { hasText: 'Save section' }));
      const menuId = evalPhp("echo App\\Models\\CmsItem::where('group_key','menu')->orderBy('position')->value('id');");
      await admin.goto(base + '/admin/cms/menu/' + menuId);
      await submit(admin.locator('button', { hasText: 'Save menu item' }));
      const servicesId = evalPhp("echo App\\Models\\CmsItem::where('group_key','menu')->whereNotNull('target_page_id')->where('target_page_id',App\\Models\\CmsPage::where('key','services')->value('id'))->value('id');");
      await admin.goto(base + '/admin/cms/menu/' + servicesId);
      await submit(admin.locator('button', { hasText: 'Save menu item' }));
      const pageIdServices = pageId('services');
      await admin.goto(base + '/admin/cms/pages/' + pageIdServices);
      await submit(admin.locator('button', { hasText: 'Save page & SEO' }));
      await admin.goto(base + '/admin/cms/pages/' + pageId('service-exam'));
      await submit(admin.locator('button', { hasText: 'Save page & SEO' }));
      await admin.goto(base + '/admin/cms/pages/' + pageId('article-1'));
      await submit(admin.locator('button', { hasText: 'Save page & SEO' }));
      const results = [];
      for (const width of [1440, 390]) results.push(await compareToOriginal('index.html', width, 'noop'), await compareToOriginal('services.html', width, 'noop'), await compareToOriginal('articles.html', width, 'noop'), await compareToOriginal('service-details.html?service=exam', width, 'noop'), await compareToOriginal('article-details.html?article=1', width, 'noop'));
      check('saving unchanged forms changes no pixels', results.every((r) => r.changedPixels === 0), results);
    }

    // The same change made in the dashboard and directly in the original page must look identical.
    async function compareToEditedOriginal(slug, width, label, mutate, arg, openMobileMenu, referenceQuery) {
      await page.setViewportSize({ width, height: 900 });
      await reference.setViewportSize({ width, height: 900 });
      await page.goto(base + '/' + slug, { waitUntil: 'networkidle' }); await settle(page);
      await reference.goto(original + '/' + slug + (referenceQuery ? '?' + referenceQuery : ''), { waitUntil: 'networkidle' }); await settle(reference);
      await reference.evaluate(mutate, arg);
      if (openMobileMenu) {
        for (const tab of [page, reference]) {
          await tab.locator('[data-menu-toggle]').evaluate((e) => e.click());
          await tab.waitForTimeout(500);
          await tab.evaluate(() => window.scrollTo(0, 0));
          await repaintFloatingLayer(tab);
        }
      }
      await reference.bringToFront();
      const a = await reference.screenshot({ path: path.join(out, label + '-original-' + width + '.png'), fullPage: !openMobileMenu });
      await page.bringToFront();
      const b = await page.screenshot({ path: path.join(out, label + '-laravel-' + width + '.png'), fullPage: !openMobileMenu });
      const result = { label, slug, width, ...pixels(a, b) };
      report.visuals.push(result);
      return result;
    }

    if (stages.includes('edit')) {
      const heroId = sectionId('index-s1');
      await admin.goto(base + '/admin/cms/sections/' + heroId);
      const edits = { f3: 'رؤية أوضح بعد تحرير المحتوى ', f5: 'نص فقرة تمت مراجعته من خلال لوحة إدارة المحتوى.', f6: '/contact.html#appointment', f7: 'احجز الآن ', f11: '/website/assets/images/Blog/blog-2.jpg' };
      const heroOriginals = {};
      for (const [key, value] of Object.entries(edits)) { heroOriginals[key] = await admin.locator('#field-' + key).inputValue(); await admin.locator('#field-' + key).fill(value); }
      await submit(admin.locator('button', { hasText: 'Save section' }));
      const hero = await compareToEditedOriginal('index.html', 1440, 'edit-hero', (e) => {
        document.querySelector('.home-hero h1').firstChild.textContent = e.f3;
        document.querySelector('.home-hero__content > p').textContent = e.f5;
        const button = document.querySelector('.hero-actions a');
        button.firstChild.textContent = e.f7;
        button.setAttribute('href', e.f6);
      }, edits, false, 'hero=' + edits.f11.replace('/website/', ''));
      check('hero edit matches the same edit on the original', hero.changedPixels === 0, hero);
      await admin.goto(base + '/admin/cms/sections/' + heroId);
      for (const [key, value] of Object.entries(heroOriginals)) await admin.locator('#field-' + key).fill(value);
      await submit(admin.locator('button', { hasText: 'Save section' }));

      const aboutId = evalPhp("echo App\\Models\\CmsItem::where('group_key','menu')->where('target_page_id',App\\Models\\CmsPage::where('key','about')->value('id'))->value('id');");
      await admin.goto(base + '/admin/cms/menu/' + aboutId);
      const aboutLabel = await admin.locator('[name=label]').inputValue();
      const renamed = 'عن د. محمد باز', added = 'العيادة', addedUrl = 'https://clinic.example.test/';
      await admin.locator('[name=label]').fill(renamed);
      await submit(admin.locator('button', { hasText: 'Save menu item' }));
      await admin.goto(base + '/admin/cms/menu');
      await admin.locator('[name=label]').fill(added);
      await admin.locator('[name=url]').fill(addedUrl);
      await submit(admin.locator('button', { hasText: 'Add menu item' }));
      const menuEdit = { renamed, added, addedUrl };
      const desktop = await compareToEditedOriginal('index.html', 1440, 'edit-menu', (e) => {
        document.querySelector('.main-nav a[data-nav="about"]').textContent = e.renamed;
        const link = document.createElement('a');
        link.className = 'nav-link'; link.href = e.addedUrl; link.textContent = e.added;
        document.querySelector('.main-nav').append(link);
      }, menuEdit);
      check('edited and added menu items match the original design on desktop', desktop.changedPixels === 0, desktop);
      const mobile = await compareToEditedOriginal('index.html', 390, 'edit-menu', (e) => {
        document.querySelector('.mobile-menu__inner a[href="about.html"]').textContent = e.renamed;
        const link = document.createElement('a');
        link.href = e.addedUrl; link.textContent = e.added;
        document.querySelector('.mobile-menu__inner').append(link);
      }, menuEdit, true);
      check('edited and added menu items match the original design on mobile', mobile.changedPixels === 0, mobile);
      await admin.goto(base + '/admin/cms/menu');
      await submit(admin.locator('tr', { hasText: added }).first().locator('button', { hasText: 'Remove' }));
      await admin.goto(base + '/admin/cms/menu/' + aboutId);
      await admin.locator('[name=label]').fill(aboutLabel);
      await submit(admin.locator('button', { hasText: 'Save menu item' }));
    }

    if (stages.includes('service')) {
      await admin.goto(base + '/admin/cms/pages?type=service');
      await admin.locator('[name=title]').fill('خدمة تجريبية للتحقق');
      await admin.locator('[name=slug]').fill('completion-service');
      await admin.locator('[name=summary]').fill('ملخص خدمة تجريبية يظهر في بطاقة الخدمة.');
      await submit(admin.locator('button', { hasText: 'Create service draft' }));
      const editor = admin.url();
      check('new service redirects to its editor', /\/admin\/cms\/pages\/\d+$/.test(editor), editor);
      const draftResponse = await page.goto(base + '/completion-service');
      check('draft service is not public', draftResponse.status() === 404, draftResponse.status());
      await page.goto(base + '/services.html', { waitUntil: 'networkidle' });
      check('draft service has no public card', (await page.locator('a[href="/completion-service"]').count()) === 0);
      await admin.selectOption('[name=is_visible]', '1');
      await submit(admin.locator('button', { hasText: 'Save page & SEO' }));
      for (const width of widths) {
        await page.setViewportSize({ width, height: 900 });
        await page.goto(base + '/services.html', { waitUntil: 'networkidle' }); await settle(page);
        const state = await layout(page);
        check('published service card fits at ' + width, !state.overflow && state.duplicateIds === 0, state);
        await page.screenshot({ path: path.join(out, 'service-card-' + width + '.png'), fullPage: true });
      }
      check('published service shows one card', (await page.locator('.services-grid a[href="/completion-service"]').count()) === 1);
      await page.setViewportSize({ width: 1440, height: 900 });
      const detail = await page.goto(base + '/completion-service', { waitUntil: 'networkidle' });
      check('published service detail opens', detail.status() === 200);
      check('service detail shows its own heading', (await page.locator('h1').first().innerText()).includes('خدمة تجريبية للتحقق'));
      check('new service highlights services in the menu', await page.locator('.nav-dropdown__trigger').evaluate((e) => e.classList.contains('active')));
      await page.locator('.services-grid a').first().count();
      await page.goto(base + '/services.html', { waitUntil: 'networkidle' });
      await Promise.all([page.waitForNavigation(), page.locator('.services-grid a[href="/completion-service"]').click()]);
      check('service card links to its page', page.url() === base + '/completion-service', page.url());
      await admin.goto(editor);
      await admin.locator('[name="listing[title]"]').fill('عنوان بطاقة معدل');
      await submit(admin.locator('button', { hasText: 'Save page & SEO' }));
      await page.goto(base + '/services.html', { waitUntil: 'networkidle' });
      check('page listing edits reach the card', (await page.locator('.services-grid').innerText()).includes('عنوان بطاقة معدل'));
      await admin.selectOption('[name=is_visible]', '0');
      await submit(admin.locator('button', { hasText: 'Save page & SEO' }));
      await page.goto(base + '/services.html', { waitUntil: 'networkidle' });
      check('unpublishing removes the card', (await page.locator('a[href="/completion-service"]').count()) === 0);
      const hidden = [];
      for (const width of widths) hidden.push(await compareToOriginal('services.html', width, 'service-hidden'));
      check('services page is pixel-identical while the new service is unpublished', hidden.every((r) => r.changedPixels === 0), hidden);
      await admin.goto(editor);
      await submit(admin.locator('button', { hasText: 'Move page to trash' }));
      const redirected = await page.goto(base + '/completion-service');
      check('removed service redirects to the listing', page.url() === base + '/services.html' && redirected.status() === 200, page.url());
      await admin.goto(base + '/admin/cms/trash');
      check('removed service is in the trash', (await admin.locator('tr', { hasText: 'خدمة تجريبية للتحقق' }).count()) >= 1);
    }

    if (stages.includes('article')) {
      await admin.goto(base + '/admin/cms/pages?type=article');
      await admin.locator('[name=title]').fill('مقال تجريبي للتحقق');
      await admin.locator('[name=slug]').fill('completion-article');
      await admin.locator('[name=summary]').fill('ملخص مقال تجريبي يظهر في بطاقة المقال.');
      await admin.locator('[name=category]').fill('تصنيف تجريبي');
      await submit(admin.locator('button', { hasText: 'Create article draft' }));
      const editor = admin.url();
      await admin.selectOption('[name=is_visible]', '1');
      await submit(admin.locator('button', { hasText: 'Save page & SEO' }));
      for (const width of widths) {
        await page.setViewportSize({ width, height: 900 });
        await page.goto(base + '/articles.html', { waitUntil: 'networkidle' });
        const state = await layout(page);
        check('published article card fits at ' + width, !state.overflow && state.duplicateIds === 0, state);
        await page.screenshot({ path: path.join(out, 'article-card-' + width + '.png'), fullPage: true });
      }
      await page.setViewportSize({ width: 1440, height: 900 });
      const live = await browser.newContext({ viewport: { width: 1440, height: 900 } });
      const tab = await live.newPage(); watch(tab, 'article-live');
      await tab.goto(base + '/articles.html', { waitUntil: 'networkidle' });
      await tab.locator('[data-article-filter]').selectOption('تصنيف تجريبي');
      const visible = tab.locator('.article-card:visible');
      check('new category filters to the new article', (await visible.count()) === 1 && (await visible.first().locator('a').first().getAttribute('href')) === '/completion-article');
      await live.close();
      await admin.goto(editor);
      await admin.selectOption('[name=is_visible]', '0');
      await submit(admin.locator('button', { hasText: 'Save page & SEO' }));
      await page.goto(base + '/articles.html', { waitUntil: 'networkidle' });
      check('unpublished article leaves the listing', (await page.locator('a[href="/completion-article"]').count()) === 0);
      await admin.goto(editor);
      await submit(admin.locator('button', { hasText: 'Move page to trash' }));
      // The filter option created for the new category is an ordinary item the editor can remove.
      await admin.goto(base + '/admin/cms/sections/' + sectionId('articles-s2'));
      await submit(admin.locator('tr', { hasText: 'تصنيف تجريبي' }).first().locator('button', { hasText: 'Remove' }));
      // The orphaned, hidden card stays until an editor deletes it.
      await submit(admin.locator('tr', { hasText: 'مقال تجريبي للتحقق' }).first().locator('button', { hasText: 'Remove' }));
      const hidden = [];
      for (const width of widths) hidden.push(await compareToOriginal('articles.html', width, 'article-removed'));
      check('articles page is pixel-identical after the article is removed', hidden.every((r) => r.changedPixels === 0), hidden);
    }

    if (stages.includes('menu')) {
      await admin.goto(base + '/admin/cms/menu');
      await admin.locator('[name=label]').fill('قائمة تجريبية');
      await admin.locator('[name=url]').fill('#');
      await submit(admin.locator('button', { hasText: 'Add menu item' }));
      await admin.locator('[name=label]').fill('رابط فرعي');
      await admin.selectOption('[name=target_page_id]', String(pageId('about')));
      await admin.locator('[name=parent_id]').selectOption({ label: 'Inside: قائمة تجريبية' });
      await submit(admin.locator('button', { hasText: 'Add menu item' }));
      const live = await browser.newContext({ viewport: { width: 1440, height: 900 } });
      const tab = await live.newPage(); watch(tab, 'menu-live');
      for (const width of widths) {
        await tab.setViewportSize({ width, height: 900 });
        await tab.goto(base + '/', { waitUntil: 'networkidle' });
        const state = await layout(tab);
        check('menu with a new dropdown fits at ' + width, !state.overflow && state.duplicateIds === 0, state);
        await tab.screenshot({ path: path.join(out, 'menu-new-dropdown-' + width + '.png') });
      }
      await tab.setViewportSize({ width: 1440, height: 900 });
      await tab.goto(base + '/', { waitUntil: 'networkidle' });
      const newTrigger = tab.locator('.nav-dropdown__trigger', { hasText: 'قائمة تجريبية' });
      await newTrigger.hover();
      await tab.waitForTimeout(400);
      check('new desktop dropdown opens', await tab.locator('.nav-dropdown', { hasText: 'قائمة تجريبية' }).locator('.nav-dropdown__menu a').first().isVisible());
      await tab.setViewportSize({ width: 390, height: 900 });
      await tab.goto(base + '/', { waitUntil: 'networkidle' });
      await tab.locator('[data-menu-toggle]').click();
      const triggers = tab.locator('[data-mobile-services-trigger]');
      check('both mobile dropdowns render', (await triggers.count()) === 2);
      await triggers.nth(1).click();
      check('second mobile dropdown opens by itself', (await triggers.nth(1).getAttribute('aria-expanded')) === 'true' && (await triggers.nth(0).getAttribute('aria-expanded')) === 'false');
      await tab.locator('[data-mobile-services]').nth(1).locator('a').first().waitFor({ state: 'visible' });
      check('second mobile dropdown shows its link', await tab.locator('[data-mobile-services]').nth(1).locator('a').first().isVisible());
      await triggers.nth(0).click();
      check('first mobile dropdown still opens', (await triggers.nth(0).getAttribute('aria-expanded')) === 'true');
      await live.close();
      // Remove the additions, then prove the menu is back to the original.
      await admin.goto(base + '/admin/cms/menu');
      await submit(admin.locator('tr', { hasText: 'قائمة تجريبية' }).first().locator('button', { hasText: 'Remove' }));
      const restored = [];
      for (const width of widths) restored.push(await compareToOriginal('index.html', width, 'menu-removed'));
      check('home is pixel-identical after removing the new menu items', restored.every((r) => r.changedPixels === 0), restored);
      await admin.goto(base + '/admin/cms/trash');
      await submit(admin.locator('tr', { hasText: 'قائمة تجريبية' }).first().locator('button', { hasText: 'Restore' }));
      await page.setViewportSize({ width: 1440, height: 900 });
      await page.goto(base + '/', { waitUntil: 'networkidle' });
      check('restored menu item returns with its nested link', (await page.locator('.nav-dropdown', { hasText: 'قائمة تجريبية' }).locator('a').count()) === 2);
      await admin.goto(base + '/admin/cms/menu');
      await submit(admin.locator('tr', { hasText: 'قائمة تجريبية' }).first().locator('button', { hasText: 'Remove' }));
    }

    if (stages.includes('sections')) {
      for (const key of ['index', 'about', 'services', 'articles', 'contact', 'service-exam', 'article-1']) {
        const id = pageId(key);
        const slug = seed.pages.find((p) => p.key === key).slug || 'index.html';
        await admin.goto(base + '/admin/cms/pages/' + id);
        const rows = admin.locator('form[action*="/sections/"][action$="/move"]');
        const originalRows = (await rows.count()) / 2;
        const options = await admin.locator('[name=type] option').evaluateAll((list) => list.map((o) => o.value).filter(Boolean));
        for (const type of options) {
          await admin.goto(base + '/admin/cms/pages/' + id);
          await admin.selectOption('[name=type]', type);
          await submit(admin.locator('button', { hasText: 'Add section' }));
          await submit(admin.locator('button', { hasText: 'Show section' }));
        }
        for (const width of widths) {
          await page.setViewportSize({ width, height: 900 });
          await page.goto(base + '/' + slug, { waitUntil: 'networkidle' }); await settle(page);
          const state = await layout(page);
          check(key + ' with ' + options.length + ' added section(s) fits at ' + width, !state.overflow && state.duplicateIds === 0, state);
          await page.screenshot({ path: path.join(out, 'sections-added-' + key + '-' + width + '.png'), fullPage: true });
        }
        // Remove newest first through the same screen the editor uses.
        for (let i = 0; i < options.length; i++) {
          await admin.goto(base + '/admin/cms/pages/' + id);
          const row = admin.locator('table tbody tr', { has: admin.locator('form[action$="/move"]') }).nth(originalRows);
          await submit(row.locator('button', { hasText: 'Remove' }));
        }
      }
      // Duplicate, reorder, hide, remove, and restore on one page.
      const id = pageId('services');
      await admin.goto(base + '/admin/cms/pages/' + id);
      const row = (label) => admin.locator('table tbody tr', { hasText: label }).first();
      const grid = seed.sections.find((s) => s.key === 'services-s2').label.trim();
      await submit(row(grid).locator('button', { hasText: 'Duplicate' }));
      await submit(admin.locator('button', { hasText: 'Show section' }));
      for (const width of widths) {
        await page.setViewportSize({ width, height: 900 });
        await page.goto(base + '/services.html', { waitUntil: 'networkidle' });
        const state = await layout(page);
        check('duplicated section fits at ' + width + ' with unique ids', !state.overflow && state.duplicateIds === 0, state);
        await page.screenshot({ path: path.join(out, 'section-duplicate-' + width + '.png'), fullPage: true });
      }
      await admin.goto(base + '/admin/cms/pages/' + id);
      await submit(row(grid + ' (copy)').locator('button', { hasText: 'Remove' }));
      await admin.goto(base + '/admin/cms/trash');
      await submit(admin.locator('tr', { hasText: grid + ' (copy)' }).first().locator('button', { hasText: 'Restore' }));
      await admin.goto(base + '/admin/cms/pages/' + id);
      check('restored section returns to the page', (await row(grid + ' (copy)').count()) === 1);
      await submit(row(grid + ' (copy)').locator('button', { hasText: 'Remove' }));

      const gridIndex = async () => { await page.goto(base + '/services.html', { waitUntil: 'networkidle' }); return page.locator('main > section').evaluateAll((list) => list.findIndex((s) => s.querySelector('.services-grid'))); };
      await page.setViewportSize({ width: 1440, height: 900 });
      const before = await gridIndex();
      await admin.goto(base + '/admin/cms/pages/' + id);
      await submit(row(grid).locator('button[aria-label="Move down"]'));
      const after = await gridIndex();
      check('moved section follows the new order', after === before + 1, { before, after });
      await submit(row(grid).locator('button[aria-label="Move up"]'));
      await submit(row(grid).locator('button', { hasText: 'Hide' }));
      await page.goto(base + '/services.html', { waitUntil: 'networkidle' });
      check('hidden section leaves the page', (await page.locator('.services-grid').count()) === 0);
      await submit(row(grid).locator('button', { hasText: 'Show' }));
      const restored = [];
      for (const width of widths) restored.push(await compareToOriginal('services.html', width, 'sections-reverted'));
      check('services page is pixel-identical after reverting section changes', restored.every((r) => r.changedPixels === 0), restored);
    }

    if (stages.includes('messages')) {
      await admin.goto(base + '/admin/cms/messages', { waitUntil: 'networkidle' });
      const keys = { appointment_success: 'رسالة نجاح معدلة', network_error: 'رسالة شبكة معدلة', submit_error: 'رسالة فشل معدلة', session_expired: 'رسالة جلسة معدلة', rate_limited: 'رسالة محاولات معدلة', validation_phone_invalid: 'رسالة هاتف معدلة' };
      const originals = {};
      for (const [key, value] of Object.entries(keys)) { originals[key] = await admin.locator('#field-' + key).inputValue(); await admin.locator('#field-' + key).fill(value); }
      await submit(admin.locator('button', { hasText: 'Save section' }));
      const context = await browser.newContext({ viewport: { width: 1440, height: 900 } });
      const tab = await context.newPage(); watch(tab, 'messages');
      const form = tab.locator('form').first();
      const fill = async (phone) => { await tab.goto(base + '/contact.html', { waitUntil: 'networkidle' }); await form.locator('[name=name]').fill('فحص تجريبي'); await form.locator('[name=phone]').fill(phone); };
      const statusText = async (expected) => { await form.locator('[role=status]').filter({ hasText: expected }).waitFor({ timeout: 8000 }); return form.locator('[role=status]').innerText(); };
      await fill('+201001234567');
      await form.locator('[type=submit]').click();
      check('edited success message appears', (await statusText(keys.appointment_success)).includes(keys.appointment_success));
      await fill('not-a-phone');
      await form.locator('[type=submit]').click();
      check('edited validation wording appears', (await statusText(keys.validation_phone_invalid)).includes(keys.validation_phone_invalid));
      await fill('+201001234567');
      await tab.route('**/appointment', (r) => r.abort());
      await form.locator('[type=submit]').click();
      check('edited connection message appears', (await statusText(keys.network_error)).includes(keys.network_error));
      await tab.unroute('**/appointment');
      await fill('+201001234567');
      await tab.route('**/appointment', (r) => r.fulfill({ status: 429, contentType: 'application/json', body: '{"message":"Too Many Attempts."}' }));
      await form.locator('[type=submit]').click();
      check('edited rate limit message appears', (await statusText(keys.rate_limited)).includes(keys.rate_limited));
      await tab.unroute('**/appointment');
      await fill('+201001234567');
      await tab.route('**/appointment', (r) => r.fulfill({ status: 500, contentType: 'text/html', body: '<h1>Server Error</h1>' }));
      await form.locator('[type=submit]').click();
      check('edited generic failure appears for non-JSON errors', (await statusText(keys.submit_error)).includes(keys.submit_error));
      await tab.unroute('**/appointment');
      await fill('+201001234567');
      await tab.evaluate(() => { document.querySelector('meta[name="csrf-token"]').content = 'expired'; document.querySelectorAll('input[name="_token"]').forEach((i) => { i.value = 'expired'; }); });
      await form.locator('[type=submit]').click();
      check('real expired session shows the edited message', (await statusText(keys.session_expired)).includes(keys.session_expired));
      await context.close();
      await admin.goto(base + '/admin/cms/messages', { waitUntil: 'networkidle' });
      for (const [key, value] of Object.entries(originals)) await admin.locator('#field-' + key).fill(value);
      await submit(admin.locator('button', { hasText: 'Save section' }));
    }

    if (stages.includes('security')) {
      const anonymous = await browser.newContext();
      const http2 = anonymous.request;
      const status = async (method, url) => (await http2.fetch(base + url, { method, maxRedirects: 0 })).status();
      for (const url of ['/admin/logout', '/admin/cache/clear', '/admin/role/delete/1', '/admin/staff/delete/1', '/admin/slider/delete/1', '/admin/models/delete/1', '/admin/partners/delete/1', '/admin/services/delete/1']) check('GET ' + url + ' is not allowed', (await status('GET', url)) === 405);
      for (const url of ['/admin/general-settings/admin/loader/1', '/admin/general-settings/Adminloader']) check('GET ' + url + ' is retired', (await status('GET', url)) === 404);
      for (const url of ['/admin/update-profile/1', '/admin/update-password/1']) check('POST ' + url + ' is retired', (await status('POST', url)) === 404);
      check('real browser request without CSRF token gets 419 (DELETE section)', (await status('DELETE', '/admin/cms/sections/' + sectionId('services-s4'))) === 419);
      check('real browser request without CSRF token gets 419 (logout)', (await status('POST', '/admin/logout')) === 419);
      check('real browser request without CSRF token gets 419 (legacy delete)', (await status('DELETE', '/admin/slider/delete/1')) === 419);
      check('anonymous dashboard access redirects to login', (await status('GET', '/admin/cms')) === 302);
      check('anonymous health access redirects to login', (await status('GET', '/admin/cms/health')) === 302);
      const missing = await http2.get(base + '/this-page-does-not-exist');
      check('404 uses managed copy', missing.status() === 404 && (await missing.text()).includes('الصفحة غير موجودة'));
      await anonymous.close();
      const secondSession = await browser.newContext();
      const second = await secondSession.newPage();
      await second.goto(base + '/admin/login');
      await second.locator('[name=email]').fill(email);
      await second.locator('[name=password]').fill(password);
      await Promise.all([second.waitForURL('**/admin/cms'), second.locator('form button[type=submit]').first().click()]);
      const replacement = crypto.randomBytes(24).toString('base64url');
      const adminId = evalPhp("echo App\\Models\\Admin::where('email','" + email + "')->value('id');");
      await admin.goto(base + '/admin/cms/users', { waitUntil: 'networkidle' });
      const usersForm = admin.locator('form[action$="/admin/cms/users/' + adminId + '"]').filter({ has: admin.locator('input[name=_method][value=PUT]') });
      await usersForm.locator('[name=password]').fill(replacement);
      await submit(usersForm.locator('button').first());
      const adminStill = await admin.goto(base + '/admin/cms', { waitUntil: 'networkidle' });
      check('password change keeps the current session', adminStill.status() === 200 && admin.url().endsWith('/admin/cms'), admin.url());
      await second.goto(base + '/admin/cms', { waitUntil: 'networkidle' });
      check('password change ends the other session', second.url().endsWith('/admin/login'), second.url());
      await secondSession.close();
    }

    if (stages.includes('final')) {
      const finals = [];
      for (const record of seed.pages) if (['index', 'about', 'services', 'articles', 'contact', 'service-exam', 'article-1'].includes(record.key)) for (const width of widths) finals.push(await compareToOriginal(record.slug || 'index.html', width, 'final'));
      check('representative pages stay pixel-identical after every dashboard flow', finals.every((r) => r.changedPixels === 0), finals.filter((r) => r.changedPixels !== 0));
    }

    if (stages.includes('health')) {
      await admin.goto(base + '/admin/cms/health', { waitUntil: 'networkidle' });
      await admin.screenshot({ path: path.join(out, 'admin-health-before.png'), fullPage: true });
      check('health screen names the missing clinic image', (await admin.locator('body').innerText()).includes('/website/assets/images/about/clinic.jpg'));
      await admin.goto(base + '/admin/cms/media', { waitUntil: 'networkidle' });
      const png = new PNG({ width: 640, height: 430 });
      png.data.fill(200);
      await admin.locator('[name=file]').setInputFiles({ name: 'verification-only.png', mimeType: 'image/png', buffer: PNG.sync.write(png) });
      await submit(admin.locator('form').filter({ has: admin.locator('[name=file]') }).locator('button'));
      await admin.goto(base + '/admin/cms/health', { waitUntil: 'networkidle' });
      const replace = admin.locator('form[action$="/health/replace-image"]');
      await replace.locator('[name=to]').selectOption({ label: 'verification-only.png' });
      await submit(replace.locator('button', { hasText: 'Replace' }));
      check('replacement reports both locations', (await admin.locator('.alert-success').innerText()).includes('2 location'));
      const failures = [];
      const live = await browser.newContext({ viewport: { width: 1440, height: 900 } });
      const tab = await live.newPage(); watch(tab, 'health');
      tab.on('response', (r) => { if (r.status() >= 400) failures.push(r.url()); });
      await tab.goto(base + '/about.html', { waitUntil: 'networkidle' });
      check('clinic photo renders after replacement', await tab.locator('.clinic-photo img').evaluate((i) => i.naturalWidth > 0 && !i.closest('.clinic-photo').classList.contains('is-empty')));
      await tab.goto(base + '/contact.html', { waitUntil: 'networkidle' });
      check('location photo renders after replacement', await tab.locator('.location-media img').evaluate((i) => i.naturalWidth > 0));
      check('no failed resources remain on About and Contact', failures.length === 0, failures);
      await live.close();
    }
    await adminContext.close();
    }
  } finally {
    await browser.close();
    staticServer.close();
    if (app) app.kill();
    // Only files this run uploaded; the copy database is disposable.
    if (fs.existsSync(uploadsDir)) for (const file of fs.readdirSync(uploadsDir)) if (!uploadsBefore.has(file)) { fs.unlinkSync(path.join(uploadsDir, file)); }
  }
  report.summary = {
    comparisons: report.visuals.length,
    visualMismatches: report.visuals.filter((v) => v.changedPixels !== 0).length,
    checks: report.checks.length,
    failedChecks: report.checks.filter((c) => !c.pass).length,
    jsErrors: report.jsErrors.length,
    linksChecked: report.links.checked,
    placeholderHrefs: report.links.placeholders,
    failedResources: report.resources.length,
    adminScreens: report.adminScreens.length,
  };
  fs.writeFileSync(reportFile, JSON.stringify(report, null, 2));
  console.log(JSON.stringify(report.summary, null, 2));
  // The copy was edited by the flows above and its uploads are gone, so it is not worth keeping.
  if (!live && stages.includes('health')) { await new Promise((resolve) => setTimeout(resolve, 500)); try { fs.unlinkSync(copy); } catch {} }
  if (report.jsErrors.length) console.log(JSON.stringify(report.jsErrors.slice(0, 10), null, 2));
  process.exit(report.summary.failedChecks || report.summary.visualMismatches || report.summary.jsErrors ? 1 : 0);
})().catch((e) => {
  fs.writeFileSync(reportFile, JSON.stringify({ ...report, fatal: e.stack || String(e) }, null, 2));
  console.error(e);
  process.exit(1);
});
