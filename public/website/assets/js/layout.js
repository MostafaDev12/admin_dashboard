function initLayout(){
  const page=document.body.dataset.page;
  document.querySelector(`[data-nav="${page}"]`)?.classList.add('active');

  // Main mobile menu / القائمة الرئيسية في الموبايل
  const toggle=document.querySelector('[data-menu-toggle]');
  const menu=document.querySelector('[data-mobile-menu]');
  toggle?.addEventListener('click',()=>{
    const opened=menu?.classList.toggle('is-open');
    toggle.setAttribute('aria-expanded',opened?'true':'false');
  });

  // Desktop services dropdown / دروب داون الخدمات على الديسكتوب
  document.querySelectorAll('[data-dropdown]').forEach(d=>{
    const trigger=d.querySelector('.nav-dropdown__trigger');
    trigger?.addEventListener('click',()=>{
      const opened=d.classList.toggle('is-open');
      trigger.setAttribute('aria-expanded',opened?'true':'false');
    });
  });

  // Mobile services dropdown / دروب داون الخدمات على الموبايل
  const mobileServices=document.querySelector('[data-mobile-services]');
  const mobileServicesTrigger=document.querySelector('[data-mobile-services-trigger]');
  const mobileServicesMenu=document.querySelector('[data-mobile-services-menu]');
  mobileServicesTrigger?.addEventListener('click',()=>{
    const opened=mobileServices?.classList.toggle('is-open');
    mobileServicesTrigger.setAttribute('aria-expanded',opened?'true':'false');
    if(mobileServicesMenu) mobileServicesMenu.setAttribute('aria-hidden',opened?'false':'true');
  });
}

document.addEventListener("DOMContentLoaded",initLayout);