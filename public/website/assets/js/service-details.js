/* =========================================================
   SERVICE DETAILS DATA / بيانات صفحة تفاصيل الخدمة
   عدّل النصوص من هنا لكل خدمة بدون تعديل تصميم الصفحة
========================================================= */
const SERVICE_DATA={
  cataract:{
    title:'جراحة المياه البيضاء',
    intro:'تقييم عتامة عدسة العين واختيار الخطة المناسبة بناءً على الفحص واحتياجات المريض.',
    image:'assets/images/servies/service-eye3.jpg',
    description:'المياه البيضاء هي عتامة في عدسة العين قد تؤثر على وضوح الرؤية. يبدأ التعامل معها بتقييم شامل للعين وتحديد مدى تأثيرها على النشاط اليومي، ثم مناقشة الخيارات المناسبة للحالة.',
    points:['فحص شامل للعين قبل تحديد الخطة','مناقشة خيارات العدسات حسب احتياج الحالة','توضيح خطوات الإجراء والمتابعة بعده']
  },
  'vision-correction':{
    title:'تصحيح الإبصار بالليزر',
    intro:'تقييم شامل للقرنية ودرجة الإبصار لتحديد مدى ملاءمة تقنيات تصحيح الإبصار.',
    image:'assets/images/servies/service-eye2.jpg',
    description:'اختيار تقنية تصحيح الإبصار لا يعتمد على درجة النظر فقط، بل على قياسات القرنية وصحة العين ونمط حياة المريض. لذلك يبدأ القرار بفحوص دقيقة قبل مناقشة الخيارات المتاحة.',
    points:['قياسات دقيقة للقرنية ودرجة النظر','مقارنة الخيارات المناسبة لكل حالة','متابعة منظمة بعد الإجراء']
  },
  retina:{
    title:'علاج أمراض الشبكية',
    intro:'تشخيص ومتابعة مشكلات الشبكية باستخدام الفحوصات المناسبة لكل حالة.',
    image:'assets/images/servies/service-eye4.jpg',
    description:'الشبكية جزء أساسي من عملية الإبصار، لذلك تعتمد خطة المتابعة على التشخيص الدقيق وتحديد نوع المشكلة ومدى تأثيرها على الرؤية قبل اختيار الإجراء المناسب.',
    points:['فحص قاع العين حسب الحالة','متابعة التغيرات بصورة منظمة','تحديد خطة العلاج وفق نتائج الفحوص']
  },
  lenses:{
    title:'زراعة العدسات',
    intro:'تقييم الحالات التي قد تستفيد من زراعة العدسات بعد فحص العين وقياساتها بدقة.',
    image:'assets/images/servies/service-eye1.jpg',
    description:'زراعة العدسات خيار يتم تقييمه وفق حالة العين ودرجة الإبصار والقياسات المطلوبة. صفحة الخدمة جاهزة لإضافة التفاصيل الطبية النهائية المعتمدة الخاصة بالدكتور.',
    points:['تقييم ملاءمة الحالة لزراعة العدسات','إجراء القياسات اللازمة قبل الاختيار','شرح المتابعة المطلوبة بعد الإجراء']
  },
  eyelid:{
    title:'جراحة الجفون',
    intro:'تقييم مشكلات الجفون الوظيفية والتجميلية واختيار الحل المناسب لكل حالة.',
    image:'assets/images/servies/service-eye5.jpg',
    description:'يبدأ تقييم الجفون بتحديد طبيعة المشكلة ومدى تأثيرها على وظيفة العين أو الشكل، ثم مناقشة الخيارات المناسبة بعد الفحص.',
    points:['تقييم وظيفة الجفن وشكل العين','تحديد الإجراء وفق احتياج الحالة','متابعة النتائج بعد الإجراء']
  },
  exam:{
    title:'فحص ومتابعة العين',
    intro:'فحص شامل يساعد على تقييم الرؤية وصحة العين ووضع خطة متابعة واضحة.',
    image:'assets/images/servies/service-eye1.jpg',
    description:'الفحص الدوري خطوة مهمة لاكتشاف التغيرات مبكرًا ومتابعة صحة العين، ويتم اختيار الفحوص الإضافية حسب شكوى المريض وتقييم الطبيب.',
    points:['قياس النظر وتقييم الأعراض','فحص صحة العين بصورة متكاملة','تحديد مواعيد المتابعة عند الحاجة']
  },
  cornea:{
    title:'علاج أمراض القرنية',
    intro:'تشخيص ومتابعة أمراض القرنية بناءً على الفحص والقياسات المتخصصة.',
    image:'assets/images/servies/service-eye2.jpg',
    description:'تختلف مشكلات القرنية من حالة لأخرى، ولذلك يعتمد التعامل معها على تحديد التشخيص ونتائج القياسات ثم اختيار خطة المتابعة أو العلاج المناسبة.',
    points:['فحص سطح وشفافية القرنية','إجراء القياسات اللازمة حسب الحالة','تحديد خطة علاج ومتابعة واضحة']
  },
  children:{
    title:'طب عيون الأطفال',
    intro:'تقييم النظر والحول ومشكلات الإبصار لدى الأطفال بطريقة مناسبة لأعمارهم.',
    image:'assets/images/servies/service-eye3.jpg',
    description:'فحص نظر الأطفال يساعد على اكتشاف مشكلات الإبصار والحول ومتابعتها مبكرًا، وتختلف طريقة الفحص وخطة المتابعة حسب عمر الطفل وطبيعة الحالة.',
    points:['فحص النظر بما يناسب عمر الطفل','تقييم الحول ومشكلات الإبصار','خطة متابعة واضحة للأسرة']
  }
};

/* ========================= RENDER SERVICE / عرض بيانات الخدمة ========================= */
document.addEventListener('DOMContentLoaded',()=>{
  const params=new URLSearchParams(location.search);
  const key=params.get('service')||'exam';
  const data=SERVICE_DATA[key]||SERVICE_DATA.exam;
  document.title=`${data.title} | د. محمد باز`;
  document.querySelector('[data-service-title]').textContent=data.title;
  document.querySelector('[data-service-intro]').textContent=data.intro;
  document.querySelector('[data-service-breadcrumb]').textContent=data.title;
  document.querySelector('[data-service-heading]').textContent=data.title;
  document.querySelector('[data-service-description]').textContent=data.description;
  const image=document.querySelector('[data-service-image]');
  image.src=data.image;image.alt=data.title;
  document.querySelector('[data-service-points]').innerHTML=data.points.map(p=>`<div><i class="fa-solid fa-circle-check"></i><span>${p}</span></div>`).join('');
});
