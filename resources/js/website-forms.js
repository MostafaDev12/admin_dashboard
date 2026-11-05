document.addEventListener('DOMContentLoaded',()=>{
 document.querySelectorAll('form').forEach(form=>form.addEventListener('submit',async event=>{
  event.preventDefault();const button=form.querySelector('[type="submit"]');if(button)button.disabled=true;
  let status=form.querySelector('[role="status"]');if(!status){status=document.createElement('p');status.setAttribute('role','status');form.append(status);}
  try{const response=await fetch(form.action,{method:'POST',body:new FormData(form),headers:{Accept:'application/json','X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content}});const data=await response.json();if(!response.ok){status.textContent=Object.values(data.errors||{}).flat().join(' ')||data.message||'تعذر إرسال الطلب.';return;}status.textContent=data.message;form.reset();}catch{status.textContent='تعذر الاتصال. يرجى المحاولة مرة أخرى.';}finally{if(button)button.disabled=false;}
 }));
});
