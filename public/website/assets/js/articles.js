/* =========================================================
   ARTICLES FILTER / البحث والتصفية في صفحة المقالات
========================================================= */
document.addEventListener('DOMContentLoaded',()=>{
  const search=document.querySelector('[data-article-search]');
  const filter=document.querySelector('[data-article-filter]');
  const cards=[...document.querySelectorAll('.article-card')];
  function apply(){
    const q=(search?.value||'').trim().toLowerCase();
    const f=filter?.value||'all';
    cards.forEach(card=>{
      const okText=!q||card.innerText.toLowerCase().includes(q);
      const okCat=f==='all'||card.dataset.category===f;
      card.style.display=okText&&okCat?'':'none';
    });
  }
  search?.addEventListener('input',apply);
  filter?.addEventListener('change',apply);
});
