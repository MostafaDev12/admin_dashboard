(() => {
    'use strict';
    const modal = document.getElementById('cms-media-picker');
    const results = document.querySelector('[data-picker-results]');
    let selected = null, next = null;
    const assign = (id, path) => {
        const input = document.getElementById(id); if (!input) return;
        input.value = path; input.dispatchEvent(new Event('change',{bubbles:true}));
        const preview = document.getElementById('preview-'+id); if (preview) { preview.src=path; preview.hidden=!path; }
    };
    async function load(url, append=false) {
        try {
            const response = await fetch(url,{headers:{Accept:'application/json'}}); if (!response.ok) throw Error('Cannot load images.');
            const data = await response.json(); if (!append) results.replaceChildren();
            for (const file of data.data) {
                const col=document.createElement('div'); col.className='col-6 col-md-3';
                const button=document.createElement('button'); button.type='button'; button.className='btn btn-light border w-100 h-100';
                const img=document.createElement('img'); img.src=file.path; img.alt=file.alt||''; img.className='img-fluid'; img.style.height='110px'; img.style.objectFit='contain';
                const label=document.createElement('div'); label.textContent=file.name; label.className='small text-break mt-2';
                button.append(img,label); button.addEventListener('click',()=>{assign(selected,file.path); bootstrap.Modal.getOrCreateInstance(modal).hide();}); col.append(button); results.append(col);
            }
            next=data.next_page_url; document.querySelector('[data-picker-more]').hidden=!next;
        } catch(error) { results.textContent=error.message; }
    }
    document.querySelectorAll('[data-media-target]').forEach(button=>button.addEventListener('click',()=>{
        selected=button.dataset.mediaTarget; bootstrap.Modal.getOrCreateInstance(modal).show(); load(results.dataset.url);
        if (button.hasAttribute('data-open-upload')) modal.querySelector('input[type=file]').focus();
    }));
    document.querySelectorAll('[data-media-remove]').forEach(button=>button.addEventListener('click',()=>assign(button.dataset.mediaRemove,'')));
    document.querySelector('[data-picker-search]')?.addEventListener('submit',e=>{e.preventDefault();load(results.dataset.url+'?q='+encodeURIComponent(e.target.q.value));});
    document.querySelector('[data-picker-more]')?.addEventListener('click',()=>next&&load(next,true));
    document.querySelectorAll('[data-copy]').forEach(button=>button.addEventListener('click',async()=>{try {await navigator.clipboard.writeText(button.dataset.copy);button.textContent='Copied';}catch {button.textContent='Copy unavailable';}}));
    document.querySelector('[data-usage-search]')?.addEventListener('input',e=>document.querySelectorAll('[data-usage-row]').forEach(row=>{row.hidden=!row.textContent.toLowerCase().includes(e.target.value.toLowerCase());}));
    document.querySelectorAll('[data-media-upload]').forEach(form=>{
        const input=form.querySelector('input[type=file]'),status=form.querySelector('[data-upload-status]'),previews=form.querySelector('[data-upload-previews]');
        input.addEventListener('change',()=>{previews.replaceChildren();for(const file of input.files){if(file.type.startsWith('image/')){const img=document.createElement('img');img.src=URL.createObjectURL(file);img.style.height='70px';img.onload=()=>URL.revokeObjectURL(img.src);previews.append(img);}}});
        form.addEventListener('dragover',e=>{e.preventDefault();form.classList.add('border-primary');});
        form.addEventListener('dragleave',()=>form.classList.remove('border-primary'));
        form.addEventListener('drop',e=>{e.preventDefault();form.classList.remove('border-primary');input.files=e.dataTransfer.files;input.dispatchEvent(new Event('change'));});
        form.addEventListener('submit',e=>{
            e.preventDefault(); const files=Array.from(input.files);
            if (!files.length||files.length>10||files.some(f=>f.size>20*1024*1024||! /\.(jpe?g|png|webp|gif|mp4|pdf)$/i.test(f.name))) {status.textContent='Choose up to 10 supported files, each no larger than 20 MB.';return;}
            const xhr=new XMLHttpRequest(),progress=form.querySelector('progress'),button=form.querySelector('button');button.disabled=true;progress.hidden=false;status.textContent='Uploading…';
            xhr.open('POST',form.action);xhr.setRequestHeader('Accept','application/json');
            xhr.upload.onprogress=e=>{if(e.lengthComputable)progress.value=e.loaded/e.total*100;};
            xhr.onload=()=>{button.disabled=false;let data;try{data=JSON.parse(xhr.responseText);}catch{data={message:'Upload failed. Check the server upload limits.'};}if(xhr.status<200||xhr.status>=300){status.textContent=Object.values(data.errors||{}).flat().join(' ')||data.message;return;}status.textContent=data.message;input.value='';previews.replaceChildren();if(form.hasAttribute('data-picker-upload')){load(results.dataset.url);}else{location.assign(location.pathname);}};
            xhr.onerror=()=>{button.disabled=false;status.textContent='Network error. Please retry.';};xhr.send(new FormData(form));
        });
    });
    const slug=document.querySelector('input[name=slug]'),prefix=document.querySelector('[data-slug-prefix]');
    if(slug&&prefix)slug.addEventListener('input',()=>{const link=document.querySelector('[data-url-preview]');link.href=prefix.dataset.slugPrefix+slug.value;link.textContent=link.href;});
    document.querySelectorAll('form').forEach(form=>{const title=form.querySelector('input[name=title]'),field=form.querySelector('input[name=slug]');if(!title||!field||prefix)return;let edited=!!field.value;field.addEventListener('input',()=>edited=true);title.addEventListener('input',()=>{if(!edited&&/^[\x00-\x7F]+$/.test(title.value))field.value=title.value.toLowerCase().replace(/[^a-z0-9]+/g,'-').replace(/^-|-$/g,'');});});
})();
