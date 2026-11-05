# Frontend content map

Source: `C:/laragon/www/dr.mohamed-baz`. Destination: `C:/laragon/www/elbaz_laravel`.

Every row is imported as structured fields and relational collection items. Templates contain the original markup; the database supplies the content.

| Page | Public URL | Sections |
|---|---|---|
| الرئيسية  /  د. محمد باز | / | 7 |
| عن الدكتور  /  د. محمد باز | /about.html | 3 |
| الخدمات  /  د. محمد باز | /services.html | 6 |
| المقالات  /  د. محمد باز | /articles.html | 4 |
| اتصل بنا  /  د. محمد باز | /contact.html | 4 |
| جراحة المياه البيضاء  /  د. محمد باز | /service-details.html?service=cataract | 4 |
| تصحيح الإبصار بالليزر  /  د. محمد باز | /service-details.html?service=vision-correction | 4 |
| علاج أمراض الشبكية  /  د. محمد باز | /service-details.html?service=retina | 4 |
| زراعة العدسات  /  د. محمد باز | /service-details.html?service=lenses | 4 |
| جراحة الجفون  /  د. محمد باز | /service-details.html?service=eyelid | 4 |
| فحص ومتابعة العين  /  د. محمد باز | /service-details.html?service=exam | 4 |
| علاج أمراض القرنية  /  د. محمد باز | /service-details.html?service=cornea | 4 |
| طب عيون الأطفال  /  د. محمد باز | /service-details.html?service=children | 4 |
| جفاف العين: الأسباب وطرق التخفيف  /  د. محمد باز | /article-details.html?article=1 | 2 |
| تصحيح الإبصار: متى يكون الخيار مناسبًا؟  /  د. محمد باز | /article-details.html?article=2 | 2 |
| المياه البيضاء: الأعراض وطرق العلاج  /  د. محمد باز | /article-details.html?article=3 | 2 |
| فيمتو كاستم ليزك: ما الذي يميزه؟  /  د. محمد باز | /article-details.html?article=4 | 2 |
| الفرق بين الليزك والفيمتو ليزك  /  د. محمد باز | /article-details.html?article=5 | 2 |
| زراعة العدسات: لمن تناسب؟  /  د. محمد باز | /article-details.html?article=6 | 2 |
| عملية PRK: كيف يتم تقييم الحالة؟  /  د. محمد باز | /article-details.html?article=7 | 2 |
| تقنيات تصحيح الإبصار الحديثة  /  د. محمد باز | /article-details.html?article=8 | 2 |
| أسئلة شائعة عن العدسات المزروعة  /  د. محمد باز | /article-details.html?article=9 | 2 |

## Sections and collections

| Page / shared area | Section | Collections |
|---|---|---|
| Shared | header | top-social · a; top-contact · a; main-nav · a; nav-dropdown__menu · a; main-nav · a; menu-toggle · span; container mobile-menu__inner · a; mobile-services__menu · a; container mobile-menu__inner · a |
| Shared | footer | footer-social · a; container footer-grid · div; footer-col · a (nested); footer-col footer-contact · a |
| Shared | floating | Typed content fields |
| index | رؤية أوضح تبدأ من تشخيص أدق | hero-badges · span |
| index | trust-strip | container trust-grid · div |
| index | section section--white services-preview | expand-services · a |
| index | لا تدع ضعف الرؤية يؤثر على تفاصيل حياتك | Typed content fields |
| index | معلومة موثوقة قبل قرارك | Typed content fields |
| index | ثقتكم هي دافعنا للاستمرار | reviews-grid · article |
| index | ابدأ بخطوة بسيطة | contact-stack · a |
| about | د. محمد باز | about-points · span |
| about | بيئة متكاملة لرعاية عينيك | clinic-features · div |
| about | نحن هنا لمساعدتك | contact-cards · a; select · option; select · option |
| services | خدمات العيون | breadcrumbs · span |
| services | مركز متكامل لصحة عينيك | services-grid · article |
| services | تصحيح الإبصار بالليزر | Typed content fields |
| services | لماذا تختار عيادة د. محمد باز؟ | why-grid · div |
| services | خطوات بسيطة نحو رؤية أفضل | journey-grid · div |
| services | ابدأ الآن واحجز موعدك | Typed content fields |
| articles | المقالات | breadcrumbs · span |
| articles | جفاف العين: الأسباب وطرق التخفيف | select · option; articles-grid · article |
| articles | كيف تختار عملية تصحيح الإبصار المناسبة؟ | Typed content fields |
| articles | هل لديك سؤال عن حالتك؟ | Typed content fields |
| contact | اتصل بنا | breadcrumbs · span |
| contact | نحن هنا لمساعدتك | contact-list · a |
| contact | عيادة د. محمد باز | location-features · div |
| contact | هل لديك استفسار سريع؟ | Typed content fields |
| service-cataract | جراحة المياه البيضاء | breadcrumbs · span |
| service-cataract | جراحة المياه البيضاء | details-points · div |
| service-cataract | التقييم أولًا | container details-info-grid · div |
| service-cataract | هل تريد معرفة إذا كانت الخدمة مناسبة لحالتك؟ | Typed content fields |
| service-vision-correction | تصحيح الإبصار بالليزر | breadcrumbs · span |
| service-vision-correction | تصحيح الإبصار بالليزر | details-points · div |
| service-vision-correction | التقييم أولًا | container details-info-grid · div |
| service-vision-correction | هل تريد معرفة إذا كانت الخدمة مناسبة لحالتك؟ | Typed content fields |
| service-retina | علاج أمراض الشبكية | breadcrumbs · span |
| service-retina | علاج أمراض الشبكية | details-points · div |
| service-retina | التقييم أولًا | container details-info-grid · div |
| service-retina | هل تريد معرفة إذا كانت الخدمة مناسبة لحالتك؟ | Typed content fields |
| service-lenses | زراعة العدسات | breadcrumbs · span |
| service-lenses | زراعة العدسات | details-points · div |
| service-lenses | التقييم أولًا | container details-info-grid · div |
| service-lenses | هل تريد معرفة إذا كانت الخدمة مناسبة لحالتك؟ | Typed content fields |
| service-eyelid | جراحة الجفون | breadcrumbs · span |
| service-eyelid | جراحة الجفون | details-points · div |
| service-eyelid | التقييم أولًا | container details-info-grid · div |
| service-eyelid | هل تريد معرفة إذا كانت الخدمة مناسبة لحالتك؟ | Typed content fields |
| service-exam | فحص ومتابعة العين | breadcrumbs · span |
| service-exam | فحص ومتابعة العين | details-points · div |
| service-exam | التقييم أولًا | container details-info-grid · div |
| service-exam | هل تريد معرفة إذا كانت الخدمة مناسبة لحالتك؟ | Typed content fields |
| service-cornea | علاج أمراض القرنية | breadcrumbs · span |
| service-cornea | علاج أمراض القرنية | details-points · div |
| service-cornea | التقييم أولًا | container details-info-grid · div |
| service-cornea | هل تريد معرفة إذا كانت الخدمة مناسبة لحالتك؟ | Typed content fields |
| service-children | طب عيون الأطفال | breadcrumbs · span |
| service-children | طب عيون الأطفال | details-points · div |
| service-children | التقييم أولًا | container details-info-grid · div |
| service-children | هل تريد معرفة إذا كانت الخدمة مناسبة لحالتك؟ | Typed content fields |
| article-1 | جفاف العين: الأسباب وطرق التخفيف | article-main__meta · span; article-sidebar · a |
| article-1 | الفحص هو الخطوة الأولى لفهم حالتك بدقة | Typed content fields |
| article-2 | تصحيح الإبصار: متى يكون الخيار مناسبًا؟ | article-main__meta · span; article-sidebar · a |
| article-2 | الفحص هو الخطوة الأولى لفهم حالتك بدقة | Typed content fields |
| article-3 | المياه البيضاء: الأعراض وطرق العلاج | article-main__meta · span; article-sidebar · a |
| article-3 | الفحص هو الخطوة الأولى لفهم حالتك بدقة | Typed content fields |
| article-4 | فيمتو كاستم ليزك: ما الذي يميزه؟ | article-main__meta · span; article-sidebar · a |
| article-4 | الفحص هو الخطوة الأولى لفهم حالتك بدقة | Typed content fields |
| article-5 | الفرق بين الليزك والفيمتو ليزك | article-main__meta · span; article-sidebar · a |
| article-5 | الفحص هو الخطوة الأولى لفهم حالتك بدقة | Typed content fields |
| article-6 | زراعة العدسات: لمن تناسب؟ | article-main__meta · span; article-sidebar · a |
| article-6 | الفحص هو الخطوة الأولى لفهم حالتك بدقة | Typed content fields |
| article-7 | عملية PRK: كيف يتم تقييم الحالة؟ | article-main__meta · span; article-sidebar · a |
| article-7 | الفحص هو الخطوة الأولى لفهم حالتك بدقة | Typed content fields |
| article-8 | تقنيات تصحيح الإبصار الحديثة | article-main__meta · span; article-sidebar · a |
| article-8 | الفحص هو الخطوة الأولى لفهم حالتك بدقة | Typed content fields |
| article-9 | أسئلة شائعة عن العدسات المزروعة | article-main__meta · span; article-sidebar · a |
| article-9 | الفحص هو الخطوة الأولى لفهم حالتك بدقة | Typed content fields |
| Shared | Website identity and contact settings | Typed global fields and optional favicon |
| Shared | Home article carousel | Ordered slides with image, title, description and destination |
