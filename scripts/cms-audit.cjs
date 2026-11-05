const fs=require('fs'),path=require('path'),crypto=require('crypto');
const source=path.resolve('../dr.mohamed-baz/assets'),target=path.resolve('public/website/assets');
const hash=p=>crypto.createHash('sha256').update(fs.readFileSync(p)).digest('hex');
function files(dir){return fs.readdirSync(dir,{withFileTypes:true}).flatMap(e=>e.isDirectory()?files(path.join(dir,e.name)):[path.join(dir,e.name)]);}
const original=files(source);const changed=[],missing=[];let identical=0;
for(const file of original){const relative=path.relative(source,file);if(['js/home.js','js/layout.js','js/forms.js','js/service-details.js','js/article-details.js'].includes(relative.replaceAll('\\','/'))||relative.endsWith('.txt'))continue;const dest=path.join(target,relative);if(!fs.existsSync(dest))missing.push(relative);else if(hash(file)!==hash(dest))changed.push(relative);else identical++;}
const seed=JSON.parse(fs.readFileSync('database/content/website.json','utf8'));const templates=files(path.resolve('resources/views/website/generated'));const hardcoded=[];
for(const file of templates){const text=fs.readFileSync(file,'utf8').replace(/\{\{[\s\S]*?\}\}/g,'').replace(/@[^\n]+/g,'');if(/[\u0600-\u06ff]/.test(text))hardcoded.push(file);}
const report={originalAssetsUnchanged:identical,missingAssets:missing,unexpectedAssetChanges:changed,generatedTemplates:templates.length,hardcodedArabicContentTemplates:hardcoded,pageRecords:seed.pages.length,sections:seed.sections.length+2,initialItems:seed.sections.reduce((n,s)=>n+s.items.length,0)+seed.rotator.length};
fs.mkdirSync('storage/app/cms-verification',{recursive:true});fs.writeFileSync('storage/app/cms-verification/content-audit.json',JSON.stringify(report,null,2));console.log(JSON.stringify(report,null,2));
fs.mkdirSync('docs',{recursive:true});let doc='# Frontend content map\n\nSource: `C:/laragon/www/dr.mohamed-baz`. Destination: `C:/laragon/www/elbaz_laravel`.\n\nEvery row is imported as structured fields and relational collection items. Templates contain the original markup; the database supplies the content. `website:upgrade` replaces the header\'s six fixed navigation groups with one menu (Main navigation and its dropdown items).\n\n| Page | Public URL | Sections |\n|---|---|---|\n';
for(const p of seed.pages)doc+=`| ${p.title.replaceAll('|',' / ')} | /${p.slug} | ${seed.sections.filter(s=>s.page_key===p.key).length} |\n`;
doc+='\n## Sections and collections\n\n| Page / shared area | Section | Collections |\n|---|---|---|\n';for(const s of seed.sections)doc+=`| ${s.page_key||'Shared'} | ${s.label.replaceAll('|',' / ')} | ${s.groups.map(g=>g.label.replaceAll('|',' / ')+(g.parent_group?' (nested)':'')).join('; ')||'Typed content fields'} |\n`;
doc+='| Shared | Website identity and contact settings | Typed global fields and optional favicon |\n| Shared | Home article carousel | Ordered slides with image, title, description and destination |\n';
fs.writeFileSync('docs/FRONTEND_MAP.md',doc);
if(missing.length||changed.length||hardcoded.length)process.exitCode=1;
