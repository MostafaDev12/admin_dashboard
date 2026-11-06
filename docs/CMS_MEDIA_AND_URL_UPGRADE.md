# Media Manager and clean URL upgrade

Verification date: 6 November 2026. This report describes local application code and isolated SQLite clones. **The working SQLite database has not been migrated or rewritten. Owner approval is required before activation.** No environment file, MySQL configuration, original asset, deployment, commit or remote push was changed.

## Media Manager

The existing Velzon library now has thumbnail cards, filename, MIME format, size, image dimensions, alt text, reference counts, View / Edit alt, Replace, Where used, Copy URL and protected deletion. The detail screen shows a large preview and searchable content locations with editor and public-page links. Shared header/footer references expand to their published pages. Draft, inherited, retained navigation and restorable-trash references are distinguished.

Click **Upload new image**, select files or drop them onto the upload form, and upload up to ten files. Progress, previews and validation errors are shown. Uploading registers immutable media without assigning it to content. The picker displays uploads immediately. The library returns to its first page so new uploads appear immediately.

Section, collection item, carousel, shared setting, listing-card, create-page, missing-image and Open Graph image fields use the same searchable Bootstrap / Velzon modal picker. Choose an image or upload inside the modal, inspect the field preview, and save the enclosing content form. Paths are read-only; manual path entry is unnecessary. Remove selection clears optional image fields. Existing image-description fields remain editable; library alt editing supplies the existing global media-alt behavior.

### Replace in one location

Open a media card → Replace. Choose/upload a replacement, select **Only one selected content field**, choose its page/section/item field, and click **Review replacement**. Confirm the listed field. Only that image JSON leaf changes. Listing-placement image overrides are recorded where needed so later page synchronization preserves an explicit field replacement. Selecting only a page's listing image preserves existing cards as overrides; a global replacement updates matching listing/card references together and retains normal inheritance. The other image, text, link and structural values remain unchanged.

A shared setting can affect multiple displayed locations. The one-location workflow therefore offers a dependent content field; changing the shared setting itself requires the everywhere workflow when it has dependents.

### Replace everywhere

Select **Replace everywhere**, then review the exact affected stored references. A separate checked confirmation is required to save. The signed review includes source, target, selected locations, content fingerprints and the complete source inventory. A stale or altered review is rejected. Original files are never overwritten. The Content health missing-image action now opens this review instead of immediately rewriting content.

`CmsMediaUsage` discovers actual section/item JSON leaves, page listing and SEO images, inherited shared settings, nested values and restorable trash. Only schema-declared image fields and supported page image properties can be replaced. Other matching text/structural values are reported/protected, never rewritten as images. Restore trash before changing its image references. Historical non-restorable menu archives retain the previous preservation policy; they are not active content and do not independently block deleting an otherwise-unused upload. Original imported files always remain protected.

Uploads retain the JPG/JPEG, PNG, WebP, GIF, MP4 and PDF allowlist and 20 MB limit. Validation checks file contents using Fileinfo, validates image decoding, and generates random immutable server-side filenames under `storage/app/public/cms`. Duplicate client filenames produce distinct stored files. Public paths use the existing Laravel storage link. Metadata is escaped by Blade and inserted into picker DOM using `textContent`. Delete requires a safe managed filename, an uploaded asset, and zero protected references.

## URL map

Mappings use stable imported page keys, never numeric database IDs or mutable titles. All nine article topics were checked against their existing records.

| Stable key | Previous URL | Canonical URL |
| --- | --- | --- |
| index | /index.html (and /) | / |
| about | /about.html | /about |
| services | /services.html | /services |
| articles | /articles.html | /articles |
| contact | /contact.html | /contact |
| service-cataract | /service-details.html?service=cataract | /services/cataract |
| service-vision-correction | /service-details.html?service=vision-correction | /services/vision-correction |
| service-retina | /service-details.html?service=retina | /services/retina |
| service-lenses | /service-details.html?service=lenses | /services/lens-implantation |
| service-eyelid | /service-details.html?service=eyelid | /services/eyelid-surgery |
| service-exam | /service-details.html?service=exam | /services/eye-examination |
| service-cornea | /service-details.html?service=cornea | /services/cornea |
| service-children | /service-details.html?service=children | /services/children-eye-care |
| article-1 | /article-details.html?article=1 | /articles/dry-eye |
| article-2 | /article-details.html?article=2 | /articles/vision-correction |
| article-3 | /article-details.html?article=3 | /articles/cataract-symptoms |
| article-4 | /article-details.html?article=4 | /articles/femto-custom-lasik |
| article-5 | /article-details.html?article=5 | /articles/lasik-vs-femto-lasik |
| article-6 | /article-details.html?article=6 | /articles/lens-implantation-candidates |
| article-7 | /article-details.html?article=7 | /articles/prk-assessment |
| article-8 | /article-details.html?article=8 | /articles/modern-vision-correction |
| article-9 | /article-details.html?article=9 | /articles/implantable-lens-faq |

## Architecture and editor behavior

The additive migration `2026_11_06_000003_add_cms_public_slugs.php` adds nullable `cms_pages.public_slug`. This is the editable leaf slug; existing `slug` stores the canonical relative path and retains its existing database UNIQUE constraint. `key` remains stable identity; type, publication, SEO and page relationships remain intact. A non-null homepage public slug explicitly marks activation. Until the schema and reviewed URL upgrade are applied, the working installation keeps its legacy routing and editor behavior.

`CmsUrls` prepares a stable-identity mapping, detects duplicate destinations and redirect conflicts, blocks unreviewed unknown legacy identities, and refuses migration while a restorable page is in Trash. Restore it first and re-plan. This avoids silently rewriting restorable snapshots. The upgrade applies within a transaction and is repeatable. It converts only schema-defined links and collection defaults, preserving fragments, legitimate tracking parameters, external/mail/tel/WhatsApp links, static assets and other text. Runtime relationship-aware link normalization also covers new placements and copied content.

The Page & SEO editor shows page type, publication, leaf slug, live canonical preview, copy/open actions and a redirect notice. Existing slug changes are explicit; title editing preserves the slug. Clean-mode canonical URLs are generated, not editorial overrides. English titles offer an editable suggested slug. A blank slug submitted with an English title receives a unique suggestion; an Arabic title requires an explicit validated English slug. Slugs use lowercase words separated by hyphens, with reserved routes, existing URLs, aliases and trashed URLs excluded.

Routes retain `front.index` for homepage compatibility. GET named routes are `website.about`, `website.services.index`, `website.services.show` (`leaf`), `website.articles.index`, `website.articles.show` (`leaf`), `website.contact.show` and `website.page`. Existing `website.contact` remains the POST appointment endpoint. Public routes exclude admin, API, storage, build, assets and website asset prefixes. Sitemap and robots retain explicit precedence. `/admin/cms` and its authorization remain unchanged.

Legacy URLs redirect directly with 301 to confirmed published pages. Missing selectors retain the original known default destinations (exam / article 1); invalid and unknown selectors return 404. Tracking parameters survive redirecting; selectors do not become canonical query strings. Slug edits flatten existing aliases. Unpublished canonical URLs and their aliases return 404. Trash retains temporary fallback redirects; restore recovers clean URLs and direct permanent aliases. Canonical, `og:url`, sitemap and noindex behavior share the current canonical page URL.

## Database readiness, backup and migration

Read-only inspection confirmed the effective CMS connection is `storage/app/website-v1.sqlite`; `.env` still describes the original MySQL installation. MySQL is unavailable: there are no TCP listeners on 3306, 3309 or 3311, and `C:/laragon/bin/mysql/mysql-8.0.30-winx64/bin/mysqld.exe.DISABLED-by-owner` remains present. MySQL tables, migration history and engine-specific conflicts cannot be inspected through a running connection. No start, repair, datadir access, configuration change or MySQL write was attempted. MySQL execution compatibility remains unverified; SQLite SQL dry-run and actual clone migration were executed. The working SQLite migration repository still contains only its original three CMS migrations.

The consistent SQLite backup is `C:/laragon/backups/elbaz-cms/media-url-20261106/before.sqlite`, created with SQLite's backup API from a read-only source. Backup SHA-256: `abb0f1e210ee2cd1c33e6b5be1cc1db6f68d0388fd678adc4546effd15bc6d15`. Backup and initial clone both passed integrity and foreign-key checks with matching row hashes.

The pristine upgraded release clone is `storage/app/cms-media-url-release.sqlite`. The read-only preservation report is `storage/app/cms-verification/media-url-preservation.json`. It verifies all original IDs and rows survive: 22 pages, 80 sections, 285 items, 19 media, one owner administrator and one preserved archive. No unexpected column/content change was found; schema-defined links, collection defaults, canonical paths, the new leaf column and affected update timestamps are the intended changes. Media references and owner credentials remain intact. Foreign keys and integrity checks pass.

Working database SHA-256 remains `42a21851264fdeb72dcbf8fe498b73353c2f7d568f1751e39105b5a88a708d79`. `.env` remains `1cab22d86b42408becd36cf15f5ede252f540f9f1d0bae80550d820fd4a6c036`; `.env.cms` remains `2150bab8b12d76c5309c1c3ff8048fe048ec1dad3d2bf479f8e5d1b2db29a8c1`.

Commands below are **for a clone**, run from the project directory. PHP 8.1 with `pdo_sqlite` is required. Do not run against the working database until separately authorized.

```powershell
$php = 'C:/laragon/bin/php/php-8.1.10-Win32-vs16-x64/php.exe'
$env:APP_ENV = 'cms'
$env:DB_CONNECTION = 'sqlite'
$env:DB_DATABASE = 'C:/laragon/www/elbaz_laravel/storage/app/YOUR_UNUSED_CLONE.sqlite'
# First create the clone with the existing read-only backup helper.
& $php -d extension=sqlite3 scripts/cms-activation-preflight.php C:/laragon/backups/elbaz-cms/media-url-20261106/before.sqlite $env:DB_DATABASE
& $php -d extension=pdo_sqlite artisan website:urls --dry-run
& $php -d extension=pdo_sqlite artisan migrate --path=database/migrations/2026_11_06_000003_add_cms_public_slugs.php --pretend
& $php -d extension=pdo_sqlite artisan migrate --path=database/migrations/2026_11_06_000003_add_cms_public_slugs.php --force
# Review mapping/conflicts, then use the exact fresh hash printed by dry-run:
& $php -d extension=pdo_sqlite artisan website:urls --apply --plan-hash=REVIEWED_HASH
& $php -d extension=pdo_sqlite -d extension=sqlite3 scripts/cms-media-url-verify.php $env:DB_DATABASE C:/laragon/backups/elbaz-cms/media-url-20261106/before.sqlite
```

The baseline plan hash is `280259adb39a2a5c15f3f1e5a6390513f29e0bc35b1526dbf5e991e12f4f8a94`; obtain a new plan if content changes. Never blindly reuse a historical hash. Working activation requires a fresh backup, the single additive migration, reviewed plan application, and post-upgrade verification. Keep the site quiescent during these steps. A failed content upgrade rolls back its transaction; the nullable schema column alone keeps legacy mode. For a complete rollback after a successful activation, stop the local application, preserve the failed database separately, restore a verified pre-upgrade snapshot via SQLite backup API, and restart. Restoring discards subsequent editorial changes, so archive them first. No rollback has been executed on the working database.

The local `scripts/cms.ps1 serve` command now supplies process-local 20 MB upload / 205 MB POST limits for up to ten uploads. The already-running server is not restarted. Restart it with this command when activating to make the advertised upload limit effective; no php.ini or environment-file edit is required.

## Verification results

Evidence files are under `storage/app/cms-verification/`; they contain local test artifacts, not production deployment output.

- `media-url-tests.xml`: **179 tests, 11,256 assertions; zero failures, errors or skipped tests**. Final full run completed in 2 minutes 52 seconds. The final focused upgrade run also passed (13 tests, 1,330 assertions), followed by the complete suite after the final display-label refinement.
- `media-url/report.json`: 22 original pages × 1440/768/390 viewports = **66 strict decoded-RGBA PNG comparisons, zero changed pixels**. Three restored-home comparisons plus the open nested mobile menu and filtered article state add **five more exact comparisons**. Total: **71 comparisons, zero visual differences**. Timers and the before/after slider are fixed only in the test context; both sites use identical real external font/icon responses. The floating-contact layer is repainted on both sides to normalize browser raster history. No visual regions or pixel differences are masked.
- The same browser report contains **92 passing checks** and verifies all 22 clean canonical pages, 24 unique internal URLs (including fragments), owner login on a clone, multi-file upload, immediate library display, modal selection, a successful public-storage HTTP response and native browser image decoding, one-location replacement, confirmed global replacement and reversal, usage search, alt editing, safe unused-file deletion and working Copy URL. Velzon library layouts fit tablet/mobile widths, and the mobile picker opens and remains within the viewport. Browser JavaScript errors: **zero**.
- `media-url/admin-url-report.json`: **39 passing browser assertions** for both service and article creation, English slug suggestions, leaf-only editing, draft visibility, publication, SEO/noindex/canonical output, automatic listing cards, title stability, live URL preview, direct old-slug redirects, unpublication, trash fallback, restoration and alias integrity. These flows ran on a separate clone from media workflows.
- Browser resource requests across all 22 clean pages found exactly the two existing 404 uses of `/website/assets/images/about/clinic.jpg` (About and Contact). No new local missing resource or JavaScript error was found.
- The new PHPUnit feature coverage includes every original mapping, direct legacy redirects, bad selectors, tracking parameters/fragments, rendered canonical links, unique suggestions and Arabic validation, slug edits and redirect flattening, publication, trash/restore identity, reserved routes, repeatability/stale plans, media uploads/selection, MIME spoofing rejection, immutable filenames, alt escaping, original/used-file protection, signed confirmations, stale content, unrelated text preservation, shared settings and listing inheritance. Existing security, authorization, CRUD, menus, managed messages and schema regression suites also ran.
- PHP Pint completed for changed PHP files; JavaScript syntax checks and `git diff --check` passed. Existing unrelated working-tree edits were preserved.

Reproduce the complete suite with:

```powershell
& C:/laragon/bin/php/php-8.1.10-Win32-vs16-x64/php.exe -d extension=pdo_sqlite vendor/phpunit/phpunit/phpunit --no-progress
$env:CMS_UPGRADE_CLONE = 'C:/laragon/www/elbaz_laravel/storage/app/YOUR_UPGRADED_MEDIA_CLONE.sqlite'
node scripts/cms-media-url-browser.cjs
node scripts/cms-media-url-browser.cjs --after-edits
$env:CMS_UPGRADE_CLONE = 'C:/laragon/www/elbaz_laravel/storage/app/YOUR_UPGRADED_URL_CLONE.sqlite'
node scripts/cms-media-url-admin.cjs
```

The browser harness refuses the working database path. It creates a temporary clone administrator with a random secret and removes that account afterwards. Use separate clones for simultaneous media and URL workflows. Full browser runs capture new visual baselines against the original static source; `--workflows` reuses already-recorded visual evidence while repeating dashboard flows. Interrupted runs were corrected for test selectors/navigation timing; final successful reports contain no failed check. Only precisely identified test uploads were removed during cleanup. The pristine release clone is separate from workflow clones and contains no browser fixture accounts or pages.

## Remaining activation and editorial blockers

Working-database activation awaits explicit owner approval. MySQL remains intentionally unavailable and is not a target of this upgrade. The pre-existing missing clinic image on About/Contact, provisional medical/editorial text and placeholder social/video/directions destinations still require approved owner content. They have not been invented or silently replaced. No full working-site clean-URL completion is claimed before activation and post-activation verification.
