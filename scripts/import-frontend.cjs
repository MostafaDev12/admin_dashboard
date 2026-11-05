// Compile trusted source markup into fixed Blade templates and typed CMS seed data.
// Content editors never submit markup or template names.
const fs=require('fs'),path=require('path'),vm=require('vm');
const cheerio=require('../storage/cms-tools/node_modules/cheerio');
const source=path.resolve(process.argv[2]||'../dr.mohamed-baz');
const dest=path.resolve('resources/views/website/generated');fs.mkdirSync(dest,{recursive:true});
const generatedFiles=new Set();
function writeTemplate(file,markup){generatedFiles.add(file);fs.writeFileSync(path.join(dest,file),markup);}
const read=p=>fs.readFileSync(path.join(source,p),'utf8');
const dataFrom=(file,name)=>vm.runInNewContext(read(file).split('document.addEventListener')[0]+`;JSON.stringify(${name})`);
const services=JSON.parse(dataFrom('assets/js/service-details.js','SERVICE_DATA'));
const articles=JSON.parse(dataFrom('assets/js/article-details.js','ARTICLES'));
const appointment=read('components/appointment-form.js').match(/const appointmentFormHTML = `([\s\S]*?)`;/)[1];
const layout=cheerio.load(read('layout/layout.html'),{decodeEntities:false},false);
const seed={pages:[],sections:[],media:[],rotator:[]};
let counter=0;
function buildSection(html,key,label,pageKey,position){
 const $=cheerio.load(html,{decodeEntities:false},false);const section={key,label,page_key:pageKey,position,template:'website.generated.'+key,fields:{},schema:{},groups:[],items:[]};
 let n=0,g=0,itemN=0;
 function field(value,type,ctx,label){const k='f'+(ctx.fieldCount=(ctx.fieldCount||0)+1);ctx.fields[k]=value;ctx.schema[k]={type,label:label||value.trim().slice(0,70)||type};return `{{ $${ctx.variable}->content['${k}'] ?? '' }}`;}
 function walk(node,ctx){
  if(node.type==='comment')return '';
  if(node.type==='text'){return node.data.trim()?field(node.data,'text',ctx):node.data;}
  if(node.type!=='tag')return '';
  let tag=node.name,attrs='',fixedAttributes={};
  const attributeKey='attributes_'+(ctx.attrCount=(ctx.attrCount||0)+1);
  for(let [k,v] of Object.entries(node.attribs||{})){
   if(['href','src','alt','title','placeholder','aria-label','data-category'].includes(k)){
    const type=k==='src'?'image':k==='href'?'link':'text';if(k==='src'&&v.startsWith('assets/'))v='/website/'+v;
    let expression=field(v,type,ctx,k+' · '+(v.slice(0,60)||tag));
    if(k==='src')node.cmsSourceField='f'+ctx.fieldCount;
    if(k==='alt'&&node.cmsSourceField)expression=expression.replace(`$${ctx.variable}->content['f${ctx.fieldCount}'] ?? ''`,`$mediaAlts[$${ctx.variable}->content['${node.cmsSourceField}'] ?? ''] ?? $${ctx.variable}->content['f${ctx.fieldCount}'] ?? ''`);
    attrs+=` ${k}="${expression}"`;
   }else if(k==='class'&&tag==='i'){attrs+=` class="${field(v,'icon',ctx,'Icon')}"`;}
   else if(k==='style'&&/url\(/.test(v)){v=v.replace(/url\(['"]?([^)'";]+)['"]?\)/g,(_,url)=>`url('${field(url.startsWith('assets/')?'/website/'+url:url,'image',ctx,'Background image')}')`);attrs+=` style="${v}"`;}
   else if(ctx.variable!=='section' && ['class','id','data-nav','name','type','min','max','rows'].includes(k)) attrs+=` ${k}="${field(v,'fixed',ctx,k)}"`;
   else if(ctx.variable!=='section')fixedAttributes[k]=v;
   else attrs+=` ${k}="${v.replace(/"/g,'&quot;')}"`;
  }
  if(ctx.variable!=='section'){ctx.fields[attributeKey]=fixedAttributes;ctx.schema[attributeKey]={type:'fixed',label:'Original element attributes'};attrs+=` {{ new \\Illuminate\\View\\ComponentAttributeBag($${ctx.variable}->content['${attributeKey}'] ?? []) }}`;}
  if(tag==='form')attrs+=' method="POST" action="{{ route(\'website.contact\') }}"';
  let inner=tag==='form'?'@csrf':'';
  const children=node.children||[];
  const signature=x=>x.type==='tag'?x.name+'|'+(x.name==='i'?'':(x.attribs.class||'').replace(/\bactive\b/g,'').trim())+'['+(x.children||[]).filter(c=>c.type==='tag').map(signature).join(',')+']':'';
  for(let i=0;i<children.length;){
   const child=children[i];if(child.type!=='tag'){inner+=walk(child,ctx);i++;continue;}
   let batch=[child],end=i+1;while(end<children.length){const next=children[end];if(next.type==='text'&&!next.data.trim()||next.type==='comment'){end++;continue;}if(signature(next)===signature(child)){batch.push(next);end++;}else break;}
   if(batch.length>=2&&!['br','i','input','select','textarea'].includes(child.name)&&!/(?:^|\s)(?:booking-field|field)(?:\s|$)/.test(child.attribs.class||'')){
    const groupKey=(ctx.group||'g')+'-'+(ctx.groupCount=(ctx.groupCount||0)+1);const varName='item'+((ctx.depth||0)+1);
    let group=section.groups.find(x=>x.key===groupKey);if(!group){group={key:groupKey,label:(node.attribs?.class||tag)+' · '+child.name,parent_group:ctx.group||null,template:null,schema:null};section.groups.push(group);}
    const parentId=ctx.importId||null;
    batch.forEach((el,index)=>{const item={import_id:++itemN,parent_import_id:parentId,group_key:groupKey,position:index,fields:{},schema:{},variable:varName,group:groupKey,depth:(ctx.depth||0)+1};item.importId=item.import_id;const view=walk(el,item);item.label=$(el).text().trim().replace(/\s+/g,' ').slice(0,75)||group.label;section.items.push(item);if(index===0&&!group.template){group.template='website.generated.'+key+'-'+groupKey;group.schema=item.schema;group.defaults=item.fields;writeTemplate(key+'-'+groupKey+'.blade.php',view);}});
    // All repeated siblings use the same shape; values remain separate records.
    inner+=`@foreach($section->items->where('group_key', '${groupKey}')->where('parent_id', ${ctx.variable==='section'?'null':'$'+ctx.variable+'->id'})->where('is_visible', true)->sortBy('position') as $${varName})\n@include('${group.template}')\n@endforeach\n`;
    i=end;
   }else{inner+=walk(child,ctx);i++;}
  }
  if(['img','input','br','hr','meta','link','source'].includes(tag))return `<${tag}${attrs}>`;
  return `<${tag}${attrs}>${inner}</${tag}>`;
 }
 section.variable='section';const view=$.root().contents().toArray().map(x=>walk(x,section)).join('');writeTemplate(key+'.blade.php',view);seed.sections.push(section);return key;
}
for(const name of ['header','footer','floating'])buildSection(layout('#'+name+'-template').html(),'global-'+name,name,null,0);
const variants=[['index','page',null],['about','page',null],['services','page',null],['articles','page',null],['contact','page',null],...Object.keys(services).map(k=>['service-details','service',k]),...Object.keys(articles).map(k=>['article-details','article',k])];
for(const [file,type,id] of variants){
 const $=cheerio.load(read(file+'.html'),{decodeEntities:false});const key=type==='page'?file:type+'-'+id;
 $('[data-appointment-form]').html(appointment);
 $('form').each((_,el)=>{let unnamed=0;$(el).find('input,select,textarea').each((_,f)=>{if(!f.attribs.name)$(f).attr('name',['name','phone','service','preferred_time','details'][unnamed++]);});});
 if(type==='service'){const s=services[id];for(const [attr,val] of Object.entries({title:s.title,intro:s.intro,breadcrumb:s.title,heading:s.title,description:s.description}))$('[data-service-'+attr+']').text(val);$('[data-service-image]').attr({src:s.image,alt:s.title});$('[data-service-points]').html(s.points.map(p=>`<div><i class="fa-solid fa-circle-check"></i><span>${p}</span></div>`).join(''));$('title').text(s.title+' | د. محمد باز');}
 if(type==='article'){const a=articles[id];$('[data-article-category]').text(a.category);$('[data-article-title]').text(a.title);$('[data-article-image]').attr({src:a.image,alt:a.title});const js=read('assets/js/article-details.js');let body=js.match(/innerHTML=`([\s\S]*?)`;/)[1].replace('${a.intro}',a.intro);$('[data-article-body]').html(body);$('title').text(a.title+' | د. محمد باز');}
 const page={key,type,slug:type==='page'?(file==='index'?'':file+'.html'):`${file}.html?${type}=${id}`,title:$('title').text(),description:$('meta[name="description"]').attr('content'),body_page:$('body').attr('data-page'),styles:[],scripts:[]};
 $('link[rel="stylesheet"]').each((_,el)=>page.styles.push(el.attribs.href.startsWith('assets/')?'/website/'+el.attribs.href:el.attribs.href));
 page.scripts=['/website/assets/js/layout.js'];if(file==='index')page.scripts.push('/website/assets/js/home.js?v=3');if(file==='articles')page.scripts.push('/website/assets/js/articles.js');
 page.scripts.push('/website/assets/js/forms.js');
 $('main').children().each((i,el)=>buildSection($.html(el),key+'-s'+(i+1),$(el).find('h1,h2,h3').first().text()||el.attribs.class||'Section',key,i));seed.pages.push(page);
}
// Preserve the home carousel's distinct original copy as its own manageable collection.
const home=read('assets/js/home.js');const arr=home.match(/const articles=(\[[\s\S]*?\n    \]);/);if(arr)seed.rotator=JSON.parse(vm.runInNewContext('JSON.stringify('+arr[1]+')'));
fs.mkdirSync('database/content',{recursive:true});fs.writeFileSync('database/content/website.json',JSON.stringify(seed,null,2));
fs.mkdirSync('public/website',{recursive:true});fs.cpSync(path.join(source,'assets'),'public/website/assets',{recursive:true});
// Page data now lives in the database; these source-only files are not published.
for(const unused of ['js/service-details.js','js/article-details.js'])fs.rmSync('public/website/assets/'+unused,{force:true});
for(const note of fs.readdirSync('public/website/assets/images/about'))if(note.endsWith('.txt'))fs.rmSync('public/website/assets/images/about/'+note);
fs.copyFileSync('resources/js/website-forms.js','public/website/assets/js/forms.js');
// Optional managed map link. Empty by default, which keeps the original markup.
const mapTemplate='resources/views/website/generated/contact-s3.blade.php';fs.writeFileSync(mapTemplate,fs.readFileSync(mapTemplate,'utf8').replace("<small>{{ $section->content['f5'] ?? '' }}</small></div>","<small>{{ $section->content['f5'] ?? '' }}</small>@if(!empty($section->content['map_url']))<a href=\"{{ $section->content['map_url'] }}\" target=\"_blank\" rel=\"noopener\" aria-label=\"{{ $section->content['f4'] ?? '' }}\" style=\"position:absolute;inset:0\"></a>@endif</div>"));
let js=read('assets/js/layout.js');js=js.slice(js.indexOf('function initLayout()'));js=js.replace(/const mobileServices=document\.querySelector\('\[data-mobile-services\]'\);[\s\S]*?\n  \}\);\n\}/,"document.querySelectorAll('[data-mobile-services]').forEach(mobileServices=>{const mobileServicesTrigger=mobileServices.querySelector('[data-mobile-services-trigger]');const mobileServicesMenu=mobileServices.querySelector('[data-mobile-services-menu]');mobileServicesTrigger?.addEventListener('click',()=>{const opened=mobileServices.classList.toggle('is-open');mobileServicesTrigger.setAttribute('aria-expanded',opened?'true':'false');if(mobileServicesMenu)mobileServicesMenu.setAttribute('aria-hidden',opened?'false':'true');});});\n}");fs.writeFileSync('public/website/assets/js/layout.js',js+'\ndocument.addEventListener("DOMContentLoaded",initLayout);');
let h=home.replace(/const articles=\[[\s\S]*?\n    \];/,'const articles=window.websiteRotator || [];').replace("if(articleBox){","if(articleBox && window.websiteRotator?.length){").replace('articleBox.href=`article-details.html?article=${i+1}`;','articleBox.href=articles[i].link;');h=h.slice(0,h.indexOf('  /* ========================= DEMO FORMS'))+'\n});';fs.writeFileSync('public/website/assets/js/home.js',h);
console.log(`Compiled ${seed.pages.length} pages, ${seed.sections.length} sections, ${seed.sections.reduce((s,x)=>s+x.items.length,0)} repeatable items.`);
// Remove only obsolete compiler output inside the dedicated generated directory.
for(const file of fs.readdirSync(dest)){if(/^[a-z0-9-]+\.blade\.php$/.test(file)&&!generatedFiles.has(file))fs.unlinkSync(path.join(dest,file));}
