document.addEventListener('DOMContentLoaded',()=>{
 const messages=window.websiteMessages||{};
 document.querySelectorAll('form').forEach(form=>form.addEventListener('submit',async event=>{
  event.preventDefault();const button=form.querySelector('[type="submit"]');if(button)button.disabled=true;
  let status=form.querySelector('[role="status"]');if(!status){status=document.createElement('p');status.setAttribute('role','status');form.append(status);}
  try{const response=await fetch(form.action,{method:'POST',body:new FormData(form),headers:{Accept:'application/json','X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content}});const data=await response.json().catch(()=>({}));if(!response.ok){status.textContent=response.status===419?messages.session_expired:response.status===429?messages.rate_limited:Object.values(data.errors||{}).flat().join(' ')||messages.submit_error;return;}status.textContent=data.message;form.reset();}catch{status.textContent=messages.network_error;}finally{if(button)button.disabled=false;}
 }));
});
