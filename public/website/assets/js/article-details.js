/* =========================================================
   ARTICLE DETAILS DATA / بيانات صفحات تفاصيل المقالات
   عدّل نص أي مقال من هنا بدون تغيير تصميم الصفحة
========================================================= */
const ARTICLES={
  1:{category:'جفاف العين',title:'جفاف العين: الأسباب وطرق التخفيف',image:'assets/images/Blog/blog-1.jpg',intro:'جفاف العين من الأعراض الشائعة وقد يرتبط بعوامل متعددة تختلف من شخص لآخر.'},
  2:{category:'تصحيح الإبصار',title:'تصحيح الإبصار: متى يكون الخيار مناسبًا؟',image:'assets/images/Blog/blog-2.jpg',intro:'اختيار إجراء تصحيح الإبصار يبدأ بقياسات وفحوص دقيقة وليس بدرجة النظر وحدها.'},
  3:{category:'المياه البيضاء',title:'المياه البيضاء: الأعراض وطرق العلاج',image:'assets/images/Blog/blog-3.jpg',intro:'عتامة عدسة العين قد تؤثر تدريجيًا على وضوح الرؤية وتحتاج إلى تقييم عند تأثيرها على النشاط اليومي.'},
  4:{category:'الليزك',title:'فيمتو كاستم ليزك: ما الذي يميزه؟',image:'assets/images/Blog/blog-4.jpg',intro:'تقنيات تصحيح الإبصار الحديثة تعتمد على دقة القياسات واختيار الإجراء المناسب للحالة.'},
  5:{category:'الليزك',title:'الفرق بين الليزك والفيمتو ليزك',image:'assets/images/Blog/blog-5.jpg',intro:'تختلف تقنيات تصحيح الإبصار في خطواتها ومتطلباتها، ويحدد الفحص الأنسب لكل مريض.'},
  6:{category:'زراعة العدسات',title:'زراعة العدسات: لمن تناسب؟',image:'assets/images/Blog/blog-6.jpg',intro:'زراعة العدسات خيار يتم تقييمه بعد فحص شامل وقياسات دقيقة للعين.'},
  7:{category:'تصحيح الإبصار',title:'عملية PRK: كيف يتم تقييم الحالة؟',image:'assets/images/Blog/blog-7.jpg',intro:'قبل اختيار PRK يتم تقييم القرنية ودرجة الإبصار وصحة سطح العين بعناية.'},
  8:{category:'الليزك',title:'تقنيات تصحيح الإبصار الحديثة',image:'assets/images/Blog/blog-8.jpg',intro:'توجد تقنيات متعددة لتصحيح الإبصار، والاختيار يعتمد على خصائص العين واحتياجات المريض.'},
  9:{category:'زراعة العدسات',title:'أسئلة شائعة عن العدسات المزروعة',image:'assets/images/Blog/blog-9.jpg',intro:'التقييم الطبي يساعد على فهم فوائد وحدود خيارات العدسات ومناقشة ما يناسب الحالة.'}
};

/* ========================= ARTICLE RENDER / عرض المقال ========================= */
document.addEventListener('DOMContentLoaded',()=>{
  const id=Number(new URLSearchParams(location.search).get('article')||1);
  const a=ARTICLES[id]||ARTICLES[1];
  document.title=`${a.title} | د. محمد باز`;
  document.querySelector('[data-article-category]').textContent=a.category;
  document.querySelector('[data-article-title]').textContent=a.title;
  const image=document.querySelector('[data-article-image]');image.src=a.image;image.alt=a.title;
  document.querySelector('[data-article-body]').innerHTML=`
    <p>${a.intro}</p>
    <h2>لماذا يبدأ القرار بالفحص؟</h2>
    <p>الأعراض المتشابهة قد يكون لها أكثر من سبب، لذلك يساعد الفحص المباشر والقياسات المناسبة على فهم الحالة ووضع الخيارات المتاحة بصورة أوضح.</p>
    <h2>ما الذي يناقشه الطبيب مع المريض؟</h2>
    <p>يتم شرح نتائج الفحص، والبدائل المتاحة، وخطة المتابعة المناسبة. ويمكن إضافة المحتوى الطبي النهائي المعتمد لهذا المقال داخل ملف .</p>
    <h2>متى تحتاج إلى استشارة؟</h2>
    <p>إذا كان لديك تغير مستمر في الرؤية أو أعراض جديدة، فالتقييم الطبي المباشر هو الوسيلة المناسبة لتحديد السبب والخطوة التالية.</p>`;
});
