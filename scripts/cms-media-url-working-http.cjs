// Read-only activation probe using native HTTP. No sessions or CMS content are changed.
const fs=require('fs'),http=require('http');
const cheerio=require('../storage/cms-tools/node_modules/cheerio');
const state=JSON.parse(fs.readFileSync('storage/app/cms-verification/media-url-activation-state.json'));
const plan=JSON.parse(fs.readFileSync(state.backup_directory+'/applied-plan.json'));
const base='http://127.0.0.1:8787',report={checks:[],links:[],pages:[]};
function get(url){return new Promise((resolve,reject)=>{http.get(url,{agent:false},r=>{const chunks=[];r.on('data',d=>chunks.push(d));r.on('end',()=>resolve({status:r.statusCode,headers:r.headers,body:Buffer.concat(chunks).toString('utf8')}));r.on('error',reject);}).on('error',reject);});}
function check(name,pass,detail){report.checks.push({name,pass:!!pass,detail});if(!pass)throw Error(name+': '+JSON.stringify(detail));}
(async()=>{try{
 const links=new Set();
 for(const row of plan.mapping){
  const response=await get(base+'/'+row.to);check('canonical HTTP '+row.key,response.status===200,response.status);
  const $=cheerio.load(response.body);check('canonical tag '+row.key,new URL($('link[rel=canonical]').attr('href')).href===new URL(base+'/'+row.to).href);
  check('Open Graph URL '+row.key,new URL($('meta[property="og:url"]').attr('content')).href===new URL(base+'/'+row.to).href);
  const old=row.key==='index'?'index.html':row.from,redirect=await get(base+'/'+old);
  check('legacy 301 '+row.key,redirect.status===301&&new URL(redirect.headers.location).href===new URL(base+'/'+row.to).href,{status:redirect.status,location:redirect.headers.location});
  report.pages.push({key:row.key,url:base+'/'+row.to,old:base+'/'+old});
  for(const a of $('a[href]').toArray()){const href=$(a).attr('href');if(!href||href.startsWith('#'))continue;const url=new URL(href,base+'/'+row.to);if(url.origin===base)links.add(url.href);}
 }
 for(const href of links){const r=await get(href);check('internal link '+href,r.status===200&&!new URL(href).pathname.endsWith('.html'),r.status);report.links.push({url:href,status:r.status});}
 for(const href of ['/service-details.html?service=unknown','/article-details.html?article=999','/article-details.html?article[]=1','/no-such-clean-page'])check('unknown returns 404 '+href,(await get(base+href)).status===404);
 const tracked=await get(base+'/service-details.html?service=eyelid&utm_source=verification');check('tracking preserved',tracked.status===301&&tracked.headers.location===base+'/services/eyelid-surgery?utm_source=verification');
 const sitemap=(await get(base+'/sitemap.xml')).body;check('sitemap has 22 clean entries',(sitemap.match(/<loc>/g)||[]).length===22&&!sitemap.includes('.html'));
 check('robots retains admin exclusion',(await get(base+'/robots.txt')).body.includes('Disallow: /admin'));
 check('admin login available',(await get(base+'/admin/login')).status===200);check('admin remains protected',(await get(base+'/admin/cms')).status===302);
 console.log(JSON.stringify({checks:report.checks.length,failed:report.checks.filter(c=>!c.pass),pages:report.pages.length,links:report.links.length}));
}catch(e){report.error=e.stack;console.error(e.stack);process.exitCode=1;}finally{fs.writeFileSync('storage/app/cms-verification/media-url-working-http.json',JSON.stringify(report,null,2));}})();
