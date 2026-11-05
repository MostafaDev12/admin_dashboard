# Website CMS implementation

The [completion report](CMS_COMPLETION_REPORT.md) is the current status, including what still needs owner approval. The [independent final audit](CMS_FINAL_AUDIT.md) records the findings it addresses.

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
./scripts/cms.ps1 migrate --path=database/migrations/2026_11_05_000000_ensure_cms_admin_auth_tables.php --path=database/migrations/2026_11_05_000001_create_website_cms_tables.php --path=database/migrations/2026_11_05_000002_add_cms_content_relationships.php
./scripts/cms.ps1 website:import
./scripts/cms.ps1 storage:link
```

### Upgrading an existing database

`website:import` applies the upgrade on a fresh database. A database that already holds content needs the third migration and `website:upgrade`, which links placements to pages, consolidates the menu, adds the managed messages, and keeps what the site displays. Back up first; the exact commands are in the [completion report](CMS_COMPLETION_REPORT.md#13-exact-steps-requiring-owner-approval). `website:upgrade --dry-run` reports what would change and saves nothing. Until a database is upgraded its public pages keep rendering through the original header template, but dashboard features that use the new tables are unavailable.

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
- **Services and articles:** the Services and Articles screens create a draft detail page and its listing card together. Publishing the page controls where it appears. Cards, carousel slides, sidebars, and menu links are explicitly linked to their page: they follow its URL and listing values, keep any copy written specifically for that placement, and disappear while the page is unpublished. See [the relationship rules](CMS_COMPLETION_REPORT.md#5-service-and-article-relationship-behavior).
- **Sections:** add a compatible section design to a page, duplicate it where that is safe, reorder, hide, remove to the trash, and restore. New sections start hidden so their starter copy can be replaced first. Only the original trusted templates are offered, and only where their stylesheet and scripts are already loaded.
- **Navigation menu:** one menu drives desktop and mobile. Add, edit, nest one level, reorder, move between positions, hide, or remove items; choose a page or a custom link; set a separate mobile label or limit an item to one device. The active item is calculated from the destination.
- **Footer & shared settings:** edit footer groups, social links, floating links, home carousel, identity, logo/favicon, and contact settings. Shared contact changes resolve throughout the website. A placement whose content has been independently customized retains its override.
- **Form & system messages:** edit form success and failure text, validation wording, and error-page text. Validation rules themselves stay in code. Everything is plain text and is escaped on output.
- **Content health:** lists missing images, broken and unpublished links, placeholder links, and provisional text with a direct link to each editor, and can replace a missing image in every location at once. It reports only; it never changes content or suggests replacements.
- **Trash:** removed pages, sections, and items can be restored exactly, including their position and links. Permanent deletion is owner-only. Removed original records stay removed when imports are rerun.
- **Media:** original asset registration, upload/browse/search/pagination, image previews and selection, editable alternative text, and deletion of unused uploads. Original assets are protected. Referenced uploads cannot be deleted. Images, MP4 and PDF are supported; uploads are limited to 20 MB and SVG/executable files are rejected.
- **Appointment requests:** all three original form designs submit to a real validated endpoint, save requests, display success/errors, and expose searchable requests and workflow status changes to authorized editors.
- **Admin users:** owners can create owners/editors, edit account details, reset passwords, and delete eligible accounts. Editors cannot manage users.

To add a service or article, open Services or Articles, fill in the form, review the copied body text in the new page's sections, then publish it. The home page's service cards and article sidebars are curated by hand: add one with "Add similar" and choose the page in its "Linked page" field.

## Storage and relationships

| Table | Purpose and relationships |
|---|---|
| `cms_pages` | Page type, public URL, assets, publication, SEO, and the listing values shared by its placements; has many sections |
| `cms_sections` | Stable section key, trusted Blade template, typed content/schema, group definitions, ordering and visibility; fixed link fields mapped to pages in `page_links`; belongs to a page or is shared |
| `cms_items` | Repeatable content, including the menu; belongs to a section, optionally belongs to another item, has many children, and optionally targets a page (`target_page_id`) with its customised roles in `overrides` |
| `cms_media` | Original and uploaded media metadata and reusable public paths |
| `cms_submissions` | Contact/appointment fields and workflow status |
| `cms_redirects` | Old URL to current URL mapping: 301 for renames, 302 while a removed page can still be restored |
| `cms_trash` | Exact snapshots of removed pages, sections, and items, plus the archive made by the menu upgrade |
| `cms_tombstones` | Original records removed on purpose, so imports do not bring them back |
| `admins`, `roles` | Existing administrator authentication architecture; created only if absent |

Content values are structured JSON within section/item records. Editors cannot change templates, element attributes, structural classes, or arbitrary HTML. Dedicated fixed Blade templates preserve the original DOM. Public text is escaped, including user-supplied content. Related records use foreign keys and indexed ordering/group fields. Public controllers eager-load sections and items.

## Original content import and source maintenance

`database/content/website.json` contains the exact HTML and JavaScript content extracted into typed fields and relational items. `database/content/globals.json` contains the original shared identity/contact defaults. `website:import` and `WebsiteSeeder` import these records with stable unique keys, retain existing edited content, and do not create duplicates on reruns. Original records that are missing because of an interrupted import are filled in; those removed through the dashboard stay removed.

`scripts/import-frontend.cjs` is a development-time compiler for the trusted original source. It produces the content manifest and fixed Blade section/item templates, copies assets unchanged, and applies the three necessary JavaScript integrations: server-rendered layout initialization, database-backed carousel data, and real form submissions. The application does not parse HTML at runtime. Editors do not need Node or the compiler.

The public structure is `resources/views/website/page.blade.php`, the consolidated `resources/views/website/header.blade.php`, fixed reusable templates under `resources/views/website/generated/`, and original assets under `public/website/assets/`. Field roles for placements live in `config/cms.php`. CMS views are under `resources/views/cms/`. The retained legacy modules (staff, roles, sliders, partners, services, models, settings) were hardened and tested against in-memory copies of their tables; they have not been exercised against the real MySQL database.

For developer-only screenshot/source tools, install:

```powershell
npm install --prefix storage/cms-tools --no-audit --no-fund playwright cheerio pngjs pixelmatch
```

Do not recompile a modified source site directly against edited production content without reviewing schema/template changes. Normal editorial updates happen entirely in the CMS.

## Authentication and security

The existing `admin` guard and `Admin`/`Role` models are reused. CMS routes require administrator authentication and the `manage-cms` Gate. Existing owners retain access; content-editor roles receive only the `cms` permission. User administration is owner-only. Login regenerates sessions, logout invalidates them, and login/appointment endpoints are rate limited. Passwords are hashed and never placed in seed data or the login form. Changing a password ends the account's other sessions. Forms include CSRF protection; inputs and uploads are validated. No state-changing route uses GET, and legacy uploads get generated names with extensions taken from their content. The previously empty general authentication middleware now delegates to Laravel's normal authentication checks.

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

The completion verification drives real forms in Chrome against a disposable copy of the working database, and compares against the original site at 1440, 768, and 390 pixels. Its report is `storage/app/cms-verification/completion-report.json`:

```powershell
node scripts/cms-completion-browser.cjs
$env:CMS_COMPLETION_TARGET='live'; node scripts/cms-completion-browser.cjs   # read-only check of the running 8787 site
```

Evidence is saved under `storage/app/cms-verification/`: PHP test output/JUnit XML, browser checks and admin screenshots, original/integrated screenshots, pixel diffs, visual comparison JSON, and content/asset audit JSON. The screenshot matrix uses 1440, 768, and 390 pixel viewports, all seven original HTML pages, expanded mobile navigation, and filtered articles. Representative detail variants are visually compared; all 17 detail URLs are covered by PHP rendering tests.

Current results are in the [completion report](CMS_COMPLETION_REPORT.md#9-tests-actually-executed). The asset audit (`scripts/cms-audit.cjs`) checks that copied source assets are unchanged, apart from the two integrated scripts and the three unused source files removed on purpose, and that the generated templates hold no hardcoded Arabic content. The missing clinic image described below is a pre-existing source reference, not a lost copied file. `git diff --check` passed. The new PHP files were formatted with Laravel Pint.

## Remaining source/configuration limitations

- The source references `assets/images/about/clinic.jpg`, which does not exist. Its original missing-image/fallback behavior is preserved on About and Contact, rather than substituting an invented image. Upload the approved clinic image through the media library, then use Content health to replace it everywhere.
- The original source contains provisional medical/about/testimonial copy, placeholder social/video links, and a placeholder map. That exact source content is preserved and now editable. Approved content and real video/social/map destinations must be supplied by the site owner.
- Appointment requests persist locally and are manageable in the CMS. Email/SMS delivery, booking-calendar synchronization, and third-party map/video embeds were not present in the source and are not claimed as implemented. External notification/calendar services require a separately configured integration.
- The original MySQL environment has not been exercised. Verification was performed using isolated SQLite copies. No database reset commands were used. See [MySQL connectivity status](CMS_COMPLETION_REPORT.md#11-mysql-connectivity-status).
- Remote Google Fonts and Font Awesome are retained exactly as in the original; rendering still depends on access to those CDNs.

