/* =========================================================
   HOME PAGE INTERACTIONS / تفاعلات الصفحة الرئيسية
========================================================= */
document.addEventListener('DOMContentLoaded',()=>{

/* =========================
   BEFORE / AFTER SLIDER
========================= */

const compare = document.querySelector('[data-compare]');
const range = document.querySelector('[data-range]');

if (compare && range) {

    let autoDemo = true;
    let frameId = null;

    /* مدة الحركة من طرف للطرف */
const travelDuration = 12000;

    /* الوقفة عند كل طرف */
const pauseDuration = 1500;


    const draw = (value) => {

        range.value = value;

        compare.style.setProperty(
            '--compare-pos',
            value + '%'
        );

    };


    /* حركة ناعمة */
    const easeInOut = (t) => {
        return 0.5 - Math.cos(Math.PI * t) / 2;
    };


    const stopAutoDemo = () => {

        autoDemo = false;

        if (frameId) {
            cancelAnimationFrame(frameId);
        }

        compare.classList.add('compare--used');
    };


    const startAutoDemo = () => {

        const totalDuration =
            pauseDuration +
            travelDuration +
            pauseDuration +
            travelDuration;

        let startTime = null;


        const animate = (time) => {

            if (!autoDemo) return;

            if (!startTime) {
                startTime = time;
            }


            const elapsed =
                (time - startTime) % totalDuration;


            let value = 0;


            /* 1. وقفة عند البداية 0% */
            if (elapsed < pauseDuration) {

                value = 0;

            }

            /* 2. الحركة من 0 إلى 100 */
            else if (
                elapsed <
                pauseDuration + travelDuration
            ) {

                const progress =
                    (
                        elapsed -
                        pauseDuration
                    ) / travelDuration;

                value =
                    easeInOut(progress) * 100;

            }

            /* 3. وقفة عند النهاية 100% */
            else if (
                elapsed <
                pauseDuration +
                travelDuration +
                pauseDuration
            ) {

                value = 100;

            }

            /* 4. الرجوع من 100 إلى 0 */
            else {

                const progress =
                    (
                        elapsed -
                        pauseDuration -
                        travelDuration -
                        pauseDuration
                    ) / travelDuration;

                value =
                    100 -
                    easeInOut(progress) * 100;

            }


            draw(value);

            frameId =
                requestAnimationFrame(animate);
        };


        /* يبدأ من أول الصورة */
        draw(0);

        frameId =
            requestAnimationFrame(animate);

    };


    /* أول ما العميل يلمس السلايدر
       تتوقف الحركة التلقائية
    */

    range.addEventListener(
        'pointerdown',
        stopAutoDemo
    );


    range.addEventListener(
        'input',
        () => {

            stopAutoDemo();

            draw(range.value);

        }
    );


    startAutoDemo();

}
  /* ========================= AUTO ARTICLES / تغيير المقالات تلقائيًا ========================= */
  const articleBox=document.querySelector('[data-home-article]');
  if(articleBox && window.websiteRotator?.length){
    const image=articleBox.querySelector('[data-home-article-image]');
    const title=articleBox.querySelector('[data-home-article-title]');
    const copy=articleBox.querySelector('[data-home-article-copy]');
    const articles=window.websiteRotator || [];
    let i=0;
    setInterval(()=>{
      i=(i+1)%articles.length;
      image.style.opacity='0';
      setTimeout(()=>{
        image.src=articles[i].image;
        title.textContent=articles[i].title;
        copy.textContent=articles[i].copy;
        articleBox.href=articles[i].link;
        image.style.opacity='1';
      },180);
    },4500);
  }


});