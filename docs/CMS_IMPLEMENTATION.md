# Website CMS implementation

The later [independent final audit](CMS_FINAL_AUDIT.md) supersedes the completion/readiness claims below and records verified coverage, safe fixes, and remaining gaps.

The application destination is `C:/laragon/www/elbaz_laravel`. The original static source is `C:/laragon/www/dr.mohamed-baz`; it was not modified. Laravel 10.48.14 and the existing Velzon assets are used. No application dependency upgrades were required.

## Run the verified local installation

The original `.env` points to an unavailable MySQL server. It has been preserved. The working local CMS uses the separate, ignored `.env.cms` and `storage/app/website-v1.sqlite`. Only the two new additive migrations were applied there. Earlier local verification databases were retained, not reset or deleted.

```powershell
# Start the application at http://127.0.0.1:8787
./scripts/cms.ps1 serve

# Create an owner account, or reset an existing account's password interactively.
# Supply your own email; the password is entered at a hidden prompt.
./scripts/cms.ps1 website:admin your-email@example.com
```

Sign in at `/admin/login`; the dashboard is `/admin/cms`. No persistent administrator or public password was seeded. Browser verification created a temporary account with a random password and removed it afterward.

To prepare this isolated environment on another checkout:

```powershell
node scripts/prepare-cms-local.cjs
./scripts/cms.ps1 migrate:status
./scripts/cms.ps1 migrate --path=database/migrations/2026_11_05_000000_ensure_cms_admin_auth_tables.php --path=database/migrations/2026_11_05_000001_create_website_cms_tables.php
./scripts/cms.ps1 website:import
./scripts/cms.ps1 storage:link
```

The helper selects the installed Laragon PHP 8.1 executable and loads its SQLite extension for this process. A normal configured PHP installation can use `php artisan` directly; for the isolated environment add `--env=cms`. For deployment to the intended MySQL database, restore connectivity, inspect `migrate:status` and the two new migrations with `--pretend`, then apply those migrations and run `website:import`. The existing MySQL schema and data have not been verified or modified. Point the web server at `public/`, configure the deployment URL, and use the deployment environment rather than `.env.cms`.

## Public pages

- `/` and `/index.html`
- `/about.html`
- `/services.html`
- `/articles.html`
- `/contact.html`
- `/service-details.html?service=exam`, plus cataract, vision-correction, retina, lenses, eyelid, cornea, and children
- `/article-details.html?article=1` through `9`
- Additional pages created from any existing design template
- `/sitemap.xml` and `/robots.txt`

There are 22 initial page records, 79 sections including shared settings and the carousel, and 272 collection items. The complete page/section/collection mapping is in [FRONTEND_MAP.md](FRONTEND_MAP.md).

## Editing workflows

The CMS reuses the existing Velzon CSS, icons, layout structure, cards, tables, buttons, form controls, notifications, responsive sidebar, and JavaScript. Public pages load their original CSS and separate public JavaScript.

- **Pages & SEO:** search and filter pages, update publication status and URLs, manage canonical/OG/noindex metadata, and create draft pages by copying an existing design. Intentional URL changes create 301 redirects.
- **Sections:** edit typed text, links, images, icons, order, and visibility. The editor label identifies a record internally; the actual public text is edited in its content fields.
- **Collections:** add similar items, create items in empty collections, edit fields, order, hide, and delete with confirmation. Nested collections support parent selection. Repeated markup is rendered with Blade loops.
- **Services and articles:** dedicated filtered page lists expose all eight service pages and nine article pages. Their content uses the same section editor. Listing cards, featured cards, sidebars, and carousel copy have separate presentation records because the original text differs between placements.
- **Menus, footer & settings:** edit shared desktop/mobile navigation, service submenus, footer groups, social links, floating links, home carousel, identity, logo/favicon, and contact settings. Shared contact changes resolve throughout the website. A placement whose content has been independently customized retains its override.
- **Media:** original asset registration, upload/browse/search/pagination, image previews and selection, editable alternative text, and deletion of unused uploads. Original assets are protected. Referenced uploads cannot be deleted. Images, MP4 and PDF are supported; uploads are limited to 20 MB and SVG/executable files are rejected.
- **Appointment requests:** all three original form designs submit to a real validated endpoint, save requests, display success/errors, and expose searchable requests and workflow status changes to authorized editors.
- **Admin users:** owners can create owners/editors, edit account details, reset passwords, and delete eligible accounts. Editors cannot manage users.

To add a new service/article to an existing listing, create its page from the corresponding design, publish it, then add a card in the listing collection and select the new URL. Menus and promotional placements are managed separately. This requires no code changes.

## Storage and relationships

| Table | Purpose and relationships |
|---|---|
| `cms_pages` | Page type, public URL, assets, publication, and SEO; has many sections |
| `cms_sections` | Stable section key, trusted Blade template, typed content/schema, group definitions, ordering and visibility; belongs to a page or is shared |
| `cms_items` | Repeatable content; belongs to a section, optionally belongs to another item, and has many children |
| `cms_media` | Original and uploaded media metadata and reusable public paths |
| `cms_submissions` | Contact/appointment fields and workflow status |
| `cms_redirects` | Old URL to current URL mapping |
| `admins`, `roles` | Existing administrator authentication architecture; created only if absent |

Content values are structured JSON within section/item records. Editors cannot change templates, element attributes, structural classes, or arbitrary HTML. Dedicated fixed Blade templates preserve the original DOM. Public text is escaped, including user-supplied content. Related records use foreign keys and indexed ordering/group fields. Public controllers eager-load sections and items.

## Original content import and source maintenance

`database/content/website.json` contains the exact HTML and JavaScript content extracted into typed fields and relational items. `database/content/globals.json` contains the original shared identity/contact defaults. `website:import` and `WebsiteSeeder` import these records with stable unique keys, retain existing edited content, and do not create duplicates on reruns. Missing original records are filled in when import is rerun.

`scripts/import-frontend.cjs` is a development-time compiler for the trusted original source. It produces the content manifest and fixed Blade section/item templates, copies assets unchanged, and applies the three necessary JavaScript integrations: server-rendered layout initialization, database-backed carousel data, and real form submissions. The application does not parse HTML at runtime. Editors do not need Node or the compiler.

The public structure is `resources/views/website/page.blade.php`, fixed reusable templates under `resources/views/website/generated/`, and original assets under `public/website/assets/`. CMS views are under `resources/views/cms/`. The original legacy controllers and modules remain in place; their behavior against the offline original database is outside the verified CMS installation.

For developer-only screenshot/source tools, install:

```powershell
npm install --prefix storage/cms-tools --no-audit --no-fund playwright cheerio pngjs pixelmatch
```

Do not recompile a modified source site directly against edited production content without reviewing schema/template changes. Normal editorial updates happen entirely in the CMS.

## Authentication and security

The existing `admin` guard and `Admin`/`Role` models are reused. CMS routes require administrator authentication and the `manage-cms` Gate. Existing owners retain access; content-editor roles receive only the `cms` permission. User administration is owner-only. Login regenerates sessions, logout invalidates them, and login/appointment endpoints are rate limited. Passwords are hashed and never placed in seed data or the login form. Forms include CSRF protection; inputs and uploads are validated. The previously empty general authentication middleware now delegates to Laravel's normal authentication checks.

The stock Velzon login form's unused AJAX handler and unrelated dashboard scripts were removed from its auth layout. An optional notification-modal listener in the original Velzon script was guarded so the functional CMS does not require dummy notification UI.

## Verification

Run PHP tests with the installed SQLite extension:

```powershell
& 'C:/laragon/bin/php/php-8.1.10-Win32-vs16-x64/php.exe' -d extension=pdo_sqlite vendor/phpunit/phpunit/phpunit
```

Tests use an isolated in-memory database and only the new migrations. The suite covers every initial page, authentication/authorization, section edits and escaping, collection creation/editing/deletion, nested cloning and empty collections, visibility/order values, menus/global settings, safe uploads, referenced media protection, template page creation, SEO/redirects, appointment validation and persistence, and every section editor.

With the server running:

```powershell
node scripts/cms-browser-check.cjs
node scripts/cms-visual.cjs baseline
node scripts/cms-visual.cjs integrated
node scripts/cms-compare.cjs
node scripts/cms-audit.cjs
```

Evidence is saved under `storage/app/cms-verification/`: PHP test output/JUnit XML, browser checks and admin screenshots, original/integrated screenshots, pixel diffs, visual comparison JSON, and content/asset audit JSON. The screenshot matrix uses 1440, 768, and 390 pixel viewports, all seven original HTML pages, expanded mobile navigation, and filtered articles. Representative detail variants are visually compared; all 17 detail URLs are covered by PHP rendering tests.

Final results: **14 PHP tests passed with 245 assertions**; all **23 screenshot comparisons matched exactly (zero changed pixels)**; nine administrator screens and the public interactive checks passed with no JavaScript errors. The asset audit confirmed 30 unchanged source assets, no unexpectedly changed/missing copied files, 152 generated templates, and no hardcoded Arabic content in those templates. The missing clinic image described below is a pre-existing source reference, not a lost copied file. `git diff --check` passed. The new PHP files were formatted with Laravel Pint.

## Remaining source/configuration limitations

- The source references `assets/images/about/clinic.jpg`, which does not exist. Its original missing-image/fallback behavior is preserved on About and Contact, rather than substituting an invented image. Upload the approved clinic image through the media library and select it in those sections.
- The original source contains provisional medical/about/testimonial copy, placeholder social/video links, and a placeholder map. That exact source content is preserved and now editable. Approved content and real video/social/map destinations must be supplied by the site owner.
- Appointment requests persist locally and are manageable in the CMS. Email/SMS delivery, booking-calendar synchronization, and third-party map/video embeds were not present in the source and are not claimed as implemented. External notification/calendar services require a separately configured integration.
- The offline original MySQL environment and legacy modules were not exercised. Verification was performed using the isolated SQLite environment. No database reset commands were used.
- Remote Google Fonts and Font Awesome are retained exactly as in the original; rendering still depends on access to those CDNs.

