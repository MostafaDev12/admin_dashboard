# CMS completion report

Date: **5 November 2026 (Africa/Cairo)**. Application: `C:/laragon/www/elbaz_laravel`. Visual source: `C:/laragon/www/dr.mohamed-baz` (not modified).

This report continues from the [independent final audit](CMS_FINAL_AUDIT.md). How to run and use the CMS is in the [implementation guide](CMS_IMPLEMENTATION.md).

## Verdict

**All nine phases are complete as far as they can safely go on this machine, and verified on local copies. The website is not production-ready, and the new dashboard features are not active on the running local site yet.** Three things stand in the way, and all three need you:

1. **The working database has not been upgraded.** I made no writes to `storage/app/website-v1.sqlite`; every check ran against a disposable copy. The public site keeps working on the new code exactly as before, but the new menu, relationships, messages, and trash need the steps in [section 13A](#a-activate-the-new-features-on-the-local-site). That database also has no administrator accounts, so nobody can sign in until one is created.
2. **MySQL is switched off on purpose, and this application's database is not on this machine.** The server binary has been renamed to `mysqld.exe.DISABLED-by-owner`, its last start attempts crashed on InnoDB corruption, and no `velzon_admin` database exists in any local data directory. I left all of this untouched. Nothing here has ever run on MySQL. See [section 11](#11-mysql-connectivity-status).
3. **Owner-supplied content is still missing:** the clinic image, 10 placeholder links, and 18 pieces of provisional text, including all patient reviews. The dashboard now lists each one with a link to its editor. I invented nothing.

Verified results:

- **160 tests passed, 9,511 assertions.** The previous baseline was 30 tests.
- **114 visual comparisons against the original site: 0 with changed pixels.** A further 68 confirm the running site on its un-upgraded database is also unchanged.
- **116 real-browser checks passed, 0 failed, 0 JavaScript errors.** These drive the real dashboard forms in Chrome.
- **139 registered routes: 0 GET mutations, 0 routes pointing to missing methods.** The audit found 9 and 3.
- `.env`, `.env.cms`, and `website-v1.sqlite` have the same SHA-256 hashes as when this work started.

## 1. Completed fixes

| Audit finding | Result |
|---|---|
| Nine state-changing GET routes | All gone. Cache clearing and six delete routes now require POST or DELETE with a CSRF token. Logout now uses the existing POST route. One obsolete route was retired. See [section 8](#8-security-results). |
| Three routes to missing methods | All retired after confirming nothing called them. |
| Legacy uploads and mass assignment reviewed | Confirmed defects fixed, including unrestricted uploads and a way for staff managers to become owners. See [section 8](#8-security-results). |
| Services and articles were disconnected from their cards | Pages and placements are now linked by an explicit relationship. See [section 5](#5-service-and-article-relationship-behavior). |
| Unpublishing or deleting a page left links behind | Cards and menu links hide themselves. Fixed buttons send visitors to the nearest live page. Deleted pages redirect and can be restored. |
| Form feedback was hardcoded | All 19 form, validation, and error-page messages are editable. No customer-facing Arabic text remains hardcoded in the public scripts (their Arabic is developer comments only), the public controller, or the public templates; a scan of all 156 templates found only a viewport `<meta>` tag. |
| No way to add or remove sections | Add, duplicate, reorder, hide, remove, and restore are available, limited to compatible trusted designs. See [section 7](#7-section-management-capabilities). |
| Navigation split into fixed groups | One menu for desktop and mobile. See [section 6](#6-menu-management-capabilities). |
| Active state tied to page templates | Calculated from the destination. A page copied from About no longer lights up the About link. |
| Rerunning the import restored deleted records | Removals are recorded, so imports respect them. |
| Passwords changed without ending other sessions | Changing a password now ends the account's other sessions and invalidates remember-me cookies. |
| Missing clinic image and placeholder links unresolved | The Content health screen names every location, and one action replaces a missing image everywhere from the media library. No image was generated. |
| Map block had no destination | Optional map link added. Left empty, the block's markup is unchanged. |
| Unused copied assets | `service-details.js`, `article-details.js`, and a stray "put the clinic image here" note were removed after confirming that no seed, template, script, or database record used them. No image was removed: every image file is referenced by the seeded content, and the media library still lists each one. |

Problems this work found beyond the audit, also fixed:

- Three more non-working legacy screens: `/admin/password` and `/admin/general-settings/home_video` pointed to views that do not exist, and the Staff add, edit, and view modals depended on a layout file that was missing.
- 49 fixed links inside 39 sections (buttons, breadcrumbs, and the header's booking button) could lead to an unpublished page. They now fall back as described above. This was counted on an upgraded copy of the working database, not estimated.
- Dashboard labels such as `href · #` and `services-grid · article` now read "Link — #" and "Service cards".

## 2. Source files changed

Nothing from this work is committed. The earlier audit's work was committed separately as `aa92a14d` while this work was in progress, and none of the edits listed here are in that commit. Everything below is uncommitted in the working tree on top of it. Nothing was pushed.

| Area | Files |
|---|---|
| New services | `app/Services/CmsPageLinks.php`, `CmsPlacements.php`, `CmsMenu.php`, `CmsTrash.php`, `CmsSectionCatalog.php`, `CmsMessages.php`, `CmsContentHealth.php`, `CmsUpgrade.php` |
| New support code and config | `app/Support/SafeUpload.php`, `app/Support/CmsLabels.php`, `config/cms.php`, `app/Models/CmsTrashEntry.php`, `app/Console/Commands/WebsiteUpgrade.php` |
| New dashboard controllers | `app/Http/Controllers/Admin/CmsStructureController.php`, `CmsMenuController.php`, `CmsTrashController.php`, `CmsHealthController.php` |
| New dashboard views | `resources/views/cms/menu.blade.php`, `menu-row.blade.php`, `menu-fields.blade.php`, `menu-edit.blade.php`, `health.blade.php`, `trash.blade.php` |
| New public views | `resources/views/website/header.blade.php`, `resources/views/errors/404`, `419`, `429`, `500`, `503.blade.php`, `resources/views/layouts/load.blade.php` |
| Changed CMS code | `CmsController.php`, `CmsUserController.php`, `WebsiteController.php`, `CmsContentRequest.php`, `WebsiteContactRequest.php`, `CmsItem.php`, `CmsPage.php`, `CmsSection.php`, `CmsMediaReferences.php`, `WebsiteContent.php`, `WebsiteImporter.php`, `WebsiteAdmin.php`, `AppServiceProvider.php`, `routes/cms.php` |
| Changed dashboard views | `resources/views/cms/dashboard`, `fields`, `item`, `layout`, `page`, `pages`, `section`, `sections` |
| Changed public files | `resources/views/website/page.blade.php`, `resources/views/website/generated/contact-s3.blade.php`, `public/website/assets/js/forms.js`, `layout.js`, `resources/js/website-forms.js` |
| Legacy security | `routes/admin.php`, `DashboardController.php`, `GeneralSettingController.php`, `PageSettingController.php`, `SliderController.php`, `PartnerController.php`, `ServiceController.php`, `ModelController.php`, `StaffController.php`, `RoleController.php`, `Api/FrontController.php`, `Auth/RegisterController.php`, `HomeController.php`, `RedirectIfNotAdmin.php`, `Admin.php`, `Generalsetting.php`, `Pagesetting.php`, `layouts/sidebar`, `topbar`, `head-css`, `public/build/js/myscript.js` |
| Database | `database/migrations/2026_11_05_000002_add_cms_content_relationships.php` (see section 3) |
| Removed | `app/Http/Controllers/Admin/LoginController.php`, `resources/views/admin/generalsetting/loader2.blade.php`, `public/website/assets/js/service-details.js`, `article-details.js`, the note in `public/website/assets/images/about/` |
| Tests | New: `tests/Feature/CmsFeatureTestCase.php`, `LegacySecurityTest.php`, `ContentRelationshipTest.php`, `SectionManagementTest.php`, `MenuManagementTest.php`, `MenuMigrationTest.php`, `ManagedMessagesTest.php`, `ContentHealthTest.php`, `SiteRegressionTest.php`. Changed: the `setUp` of `FinalCmsAuditTest.php` and `WebsiteCmsTest.php` now also runs the new migration. No existing assertion was changed. |
| Scripts | New: `scripts/cms-completion-browser.cjs`, `cms-completion-prepare.php`, `cms-mysql-ddl.php`. Changed: `cms-audit.cjs`, `import-frontend.cjs`. |
| Docs | `docs/CMS_COMPLETION_REPORT.md` (new), `CMS_FINAL_AUDIT.md`, `CMS_IMPLEMENTATION.md`, `FRONTEND_MAP.md` |

## 3. Database migrations added

One migration: `database/migrations/2026_11_05_000002_add_cms_content_relationships.php`. It only adds to the schema and changes no existing rows. It has **not** been applied to any real database.

| Table | Addition | Purpose |
|---|---|---|
| `cms_pages` | `listing`, `auto_listing` | Title, summary, image, and category shared by the page's cards, and whether it appears in its listing automatically |
| `cms_sections` | `page_links` | Which fixed link fields lead to which page |
| `cms_items` | `target_page_id`, `is_managed`, `overrides`, `detached_from` | The page an item points to, whether it was created automatically, which values it customises, and the title of a removed page |
| `cms_redirects` | `status` | 301 for renames, 302 while a removed page can still be restored |
| `cms_trash` (new) | | Exact snapshots of removed content |
| `cms_tombstones` (new) | | Original records removed on purpose, which imports respect |

Existing data is linked by a separate, repeatable command, `website:upgrade`, which supports `--dry-run`. On the copy of your working database it reported:

```
{
    "menu_migrated": true,
    "menu_items": 13,
    "linked_placements": 90,
    "page_listings": 17,
    "map_fields": 1,
    "pruned_media_records": 1
}
```

Running it a second time changed nothing. Afterwards the copy held 22 pages, 80 sections (the 79 originals plus the messages record), 261 items (272 originals, minus 12 mobile menu records merged into their desktop twins, plus the new Services dropdown record), and 18 media records (19 minus the stray note). 90 items and 39 sections are now linked to pages, and 17 pages have listing values.

To undo this on SQLite, restore the backup from section 13A. `migrate:rollback` removes the new columns but does not undo the menu consolidation or return the trash contents.

The SQL this migration would run on MySQL is in [the appendix](#appendix-sql-for-review). It was compiled offline and has never been executed.

## 4. CMS features completed

| Feature | Where in the dashboard |
|---|---|
| Create a service or article with its card in one step | Services, Articles |
| See and edit everywhere a page appears | Page editor → "Where this page appears" |
| Listing title, summary, image, and category | Page editor → "Listing card" (service and article pages) |
| Add, duplicate, reorder, hide, remove, and restore sections | Page editor → "Page sections" |
| Reorder and hide collection items directly | Section editor |
| Link any card or link to a page | Item editor → "Linked page" |
| One menu for desktop and mobile | Navigation menu |
| Form, validation, and error-page wording | Form & system messages |
| Missing images, broken links, placeholders, provisional text | Content health, with a summary on the dashboard |
| Replace a missing image in every location | Content health |
| Map destination | Contact page's location section → "Map destination (optional)" |
| Recover removed content | Trash (permanent deletion is owner-only) |

Everything uses the existing Velzon components. No new CSS framework or public styling was added.

## 5. Service and article relationship behavior

Each card, carousel slide, sidebar link, and menu link stores the page it points to. This is a real foreign key, not text matching at render time. Text is only parsed once, to establish the link during the upgrade or when an editor saves a link.

| When you… | What happens |
|---|---|
| Create a service or article | A draft page and its listing card are created together. New articles also get a filter option for a new category. A service can also join the Services menu. Nothing is public yet. |
| Publish it | The card, its link, and any menu entry appear. |
| Edit its listing title, summary, or image | Every placement that has not been customised follows. |
| Edit a card's text directly | That value is kept for that card and survives later page edits. "Use the page's values" reverts it. |
| Change its URL | Linked cards, menu links, and fixed buttons update, keeping `#fragments`. The old URL redirects with a 301. |
| Unpublish it | Cards, slides, sidebar links, and menu links disappear. Fixed buttons lead to the listing page, or home for plain pages. All content stays stored, and republishing restores it. |
| Hide just its card | The page stays public but leaves the listing, and can be reordered like any other card. |
| Move it to the trash | The page and its sections are removed. Placements elsewhere are hidden with their text intact and marked with the page they belonged to. The old URL redirects to the listing with a 302, or a 301 after permanent deletion. |
| Restore it | The page, sections, placement links, previous visibility, and URL all return. |
| Delete its automatic card | The card stays deleted. "Add to listing" brings it back. |
| Run the upgrade or sync again | No duplicate cards are created. Tests repeat these operations and compare the results. |

Existing cards were preserved exactly. Each page's listing values were taken from its main card, so that card follows the page with no visible change. Placements with different wording, such as the home card "تصحيح الإبصار" for the page titled "تصحيح الإبصار بالليزر", were recorded as customised and kept.

**What stays manual, on purpose:** the home page's service cards and each article's sidebar are curated selections, so a new page does not join them automatically. Add one with "Add similar" and pick the page.

**What to review before publishing:** a new page copies its body text from the design you chose. Its heading, summary, and image are set from the form, but the remaining medical copy still belongs to the source page and must be replaced. It stays a draft until you publish it.

## 6. Menu management capabilities

- Add items linked to a page, or with a custom link. Unsafe links such as `javascript:` are rejected.
- Edit labels and destinations. An optional separate mobile label is available.
- Nest items one level, which is what the original design supports. Adding a child to any top-level item creates a working dropdown on both desktop and mobile. Deeper nesting is refused.
- Reorder with up and down controls, and move items into or out of a dropdown.
- Hide an item, or limit it to desktop or mobile.
- Remove to the trash and restore, including nested items.
- The active item comes from the destination: the page's own item and its parent, otherwise the listing item for its type.
- Links to unpublished pages are never shown. A dropdown survives if only its parent's page is unpublished; if no child remains either, the entry disappears. A dropdown whose children are all hidden on one device becomes a plain link there.

**Migration:** desktop and mobile records for the same destination became one record. Different labels, one-device items, each device's order, and hidden states were all preserved, and a test confirms this using deliberately edited data. The previous structure is kept as a read-only archive in the trash. The header's visual output is pixel-identical to the original.

**Not included:** the footer's link groups remain ordinary collections, and the top social and contact links remain header collections. Both are fully editable; they are just not part of the main menu.

## 7. Section management capabilities

- **Add:** only the original trusted templates are offered, and only on pages that already load their stylesheet and scripts. A section built for the home page's styles is not offered on the About page, because it would render unstyled. Identical layouts, such as the eight service page templates, appear once.
- **Starts hidden:** added and duplicated sections are hidden until shown, because their starter text comes from another page.
- **Duplicate:** copies the section and its items next to the original, with unique IDs.
- **Reorder, hide, show, edit, and manage items:** available from the page and section editors.
- **Remove and restore:** the section goes to the trash and comes back with the same ID, position, and items. Imports will not recreate a removed original section.
- **Not repeatable, by design:** four designs have fixed page anchors (`contact-s2`, `contact-s3`, `index-s5`, `index-s7`), and three drive scripts that only bind one instance (`index-s4`, `index-s5`, `articles-s2`). They cannot be duplicated, and can only be added if the page has no copy. Shared header and footer sections cannot be removed or moved; they can be edited or hidden.
- **Editors cannot supply code:** templates, keys, and schemas cannot be changed from a form, and all content is escaped on output. Tests attempt each of these.

Every compatible design was added through the real dashboard on seven pages, then checked in Chrome at 1440, 768, and 390 pixels for horizontal overflow and duplicate IDs.

## 8. Security results

### The routes from the audit

| Route | Method it called | Callers found | Outcome |
|---|---|---|---|
| `GET admin/logout` | `LoginController@logout` | Legacy topbar link | Removed. The topbar now posts to the existing `POST admin/logout`. |
| `GET admin/cache/clear` | Closure | Legacy sidebar link | `POST`, form with CSRF token, `permissions:super` kept |
| `GET admin/role/delete/{id}` | `RoleController@destroy` | Datatable delete modal | `DELETE`, `permissions:super` kept |
| `GET admin/staff/delete/{id}` | `StaffController@destroy` | Datatable delete modal | `DELETE`, `permissions:manage_staffs` kept, owner protection added |
| `GET admin/slider/delete/{id}` | `SliderController@destroy` | Datatable delete modal | `DELETE`, `permissions:slider` kept |
| `GET admin/models/delete/{id}` | `ModelController@destroy` | Datatable delete modal | `DELETE`, `permissions:models` kept |
| `GET admin/partners/delete/{id}` | `PartnerController@destroy` | Datatable delete modal | `DELETE`, `permissions:partners` kept |
| `GET admin/services/delete/{id}` | `ServiceController@destroy` | Datatable delete modal | `DELETE`, `permissions:services` kept |
| `GET admin/general-settings/admin/loader/{status}` | `GeneralSettingController@isadminloader` | Only `loader2.blade.php`, which could never render. Its setting is read nowhere. | Retired with that view |
| `GET admin/general-settings/Adminloader` | `load2` (missing) | None | Retired |
| `POST admin/update-profile/{id}` | `updateProfile` (missing) | None | Retired. `POST admin/profile/update` remains. |
| `POST admin/update-password/{id}` | `updatePassword` (missing) | None | Retired. `POST admin/password/update` remains. |

The shared delete modal in `public/build/js/myscript.js` now sends `DELETE` with the CSRF token, and legacy layouts now include the token. Also removed: the legacy login and forgot-password routes, which `routes/cms.php` already overrode, along with `LoginController`. Its public forgot-password method passed raw request input to the admin model, so it would have become a privilege escalation if those routes were ever reordered.

### Confirmed defects fixed

| Severity | Defect | Fix |
|---|---|---|
| Critical | Slider, partner, service, model, general settings, and page settings uploads had no validation and kept the client's filename inside `public/`. Anyone with those permissions could upload a `.php` file. | Allow-listed types, generated names, and extensions taken from the file's content. SVG is no longer accepted. |
| Critical | A staff manager could create an owner, promote someone to owner, grant a role with more access than their own, or reset or delete an owner. Omitting `role_id` created an owner by default. | Non-owners cannot create or change owners or grant permissions they lack. `role_id` is required, passwords must have at least 12 characters, and only named fields are accepted. |
| High | General settings accepted the favicon and admin loader as plain text, with no validation and no URL accessor on those columns. The next replacement then passed that text to `unlink()`, so a user with the general-settings permission could delete any file the web server can write. | These fields can only come from an upload, and every deletion is limited to the upload folder. A test stores a traversal value and confirms the target file survives. |
| Medium | The public registration form checked the avatar's content but stored it with the client-supplied extension. | The name is generated and the extension comes from the content. |
| Medium | A changed password left the account's other sessions signed in. | Those sessions now end, and remember-me cookies are invalidated. |
| Low | `/index/{locale}` accepted any value as the locale and stored it in the session. | Allow-listed |
| Low | Line breaks in the `name` field of `/api/contact-submit` reached the email subject and body. | Rejected by validation |

Slider, partner, service, and model records also accepted a text value for their photo, which could only break their own images. That now fails validation. Role input takes only named fields too, but the model's fillable list already limited what could be stored, so that is hardening, not a fix. Replacing a slider, partner, service, or model image still leaves the old file on disk, exactly as before: the old cleanup never worked, because those models' accessors return URLs rather than file names. I did not change that.

Editors cannot gain owner privileges through any path tested: the profile form, the staff screens, the CMS user screens, or direct requests. Real-browser checks confirmed `405` on the old GET routes, `404` on the retired ones, and `419` on requests without a CSRF token.

### Found but left for you to decide

- **Public registration** at `/register` still works. It grants no admin access and nothing in the site uses it. I recommend retiring it.
- **`GET /api/visitors`** can record a visitor row and calls an external IP-lookup service. It is an anonymous analytics beacon that the current site does not call, so it was not treated as one of the admin GET mutations. I recommend retiring it along with the rest of the old public API, which also returns errors on the SQLite site because its tables do not exist there. While checking this claim I called it once on the running site, which I should not have done given that it can write. No database write happened: the file's hash is unchanged and the table does not exist there. The endpoint's code issues one lookup request for `127.0.0.1` to geoplugin.net, which I did not check was actually sent.
- **Dead code with unsafe patterns, unreachable because no route points to it** (checked against the registered routes): `LanguageController`, `TenantController`, the web `FrontController`, 69 `GeneralSettingController` methods, and the admin email screens `config`, `edit`, `group`, and `index`, which use route names that do not exist. `admin/email/mailbody` is still used by the mailer. Removing the dead code is safe but was left for you to approve.
- **Historical secrets:** `velzon_admin.sql` is committed and contains an `admin@demo.com` account with a password hash, plus SMTP columns. Rotate anything that was ever real, and consider purging it from the repository's history.
- **Production settings** are unverified: `.env` has `APP_DEBUG=true`, and HTTPS-only cookies have not been tested.

## 9. Tests actually executed

```powershell
& 'C:/laragon/bin/php/php-8.1.10-Win32-vs16-x64/php.exe' -d extension=pdo_sqlite vendor/phpunit/phpunit/phpunit
node scripts/cms-completion-browser.cjs
$env:CMS_COMPLETION_TARGET='live'; node scripts/cms-completion-browser.cjs
```

**PHPUnit: 160 tests, 9,511 assertions, 0 failures.** All tests use in-memory SQLite. Legacy module tests create that module's tables from its own migrations.

| Test class | Tests | Assertions | Covers |
|---|---:|---:|---|
| `WebsiteCmsTest` | 12 | 243 | Baseline: CMS core |
| `FinalCmsAuditTest` | 16 | 1,621 | Baseline: independent audit, including CRUD on every collection group and an edit of every section text field |
| `LegacySecurityTest` | 26 | 3,328 | Routes, CSRF, uploads, privilege escalation, sessions, and every legacy screen |
| `ContentRelationshipTest` | 23 | 436 | Service and article lifecycles, placements, overrides, URLs, removal and restore |
| `SectionManagementTest` | 17 | 298 | Add, duplicate, reorder, hide, remove, restore, and compatibility |
| `MenuManagementTest` | 15 | 212 | Menu editing, nesting, moving, devices, active state, and unpublished pages |
| `MenuMigrationTest` | 10 | 150 | Migration fidelity, repeat runs, dry run, rollback, and un-upgraded databases |
| `ManagedMessagesTest` | 12 | 139 | Editable messages, escaping, fallbacks, and error pages |
| `ContentHealthTest` | 16 | 200 | Missing media, placeholders, provisional text, map link, media protection, and removed assets |
| `SiteRegressionTest` | 11 | 2,881 | SEO, internal links, duplicate IDs, sitemap, all dashboard screens, and defined routes |
| `ExampleTest` (2) | 2 | 3 | Framework examples |

**How this compares with the baseline of 30 tests and 1,916 assertions.** All 30 still pass. Their combined assertion count is now 1,867 rather than 1,916. They loop over whatever groups and fields exist, and six fixed menu groups became two (76 groups became 72) while five header fields moved onto menu items, so the count moves without any assertion being removed or changed. The only edit to those two files is one line each, adding the new migration to their setup.

**Real-browser verification: 116 checks, 0 failed, 0 JavaScript errors.** Chrome drove the actual forms against a backup copy of the working database, with the new migration and upgrade applied only to that copy. It covered:

- All 16 dashboard screens, and 25 unique internal destinations including their fragments, with 0 broken.
- Mobile navigation and nested menus, the desktop dropdown, the before/after slider, the carousel, article search and filtering.
- The full service and article lifecycles, including filtering by a new category.
- Menu additions, a second dropdown working independently on mobile, removal, and restore.
- Every compatible section design on seven pages, plus duplicate, restore, reorder, and hide.
- Edited success, validation, connection, rate-limit, and server-error messages shown in a real form, plus a real expired-session response.
- Password change keeping the current session and ending a second one.
- Replacing the missing image through the media library. A flat grey test image was used in the disposable copy and deleted afterwards.

The only failed resources in the crawl were the known `clinic.jpg` on About and Contact.

**What this does not cover:** MySQL, real SMTP or external integrations, production web server behavior, and browsers other than Chrome. Legacy modules were tested at the HTTP level with their real controllers and views, but their jQuery modals were not clicked through in a browser, because those tables do not exist in the working SQLite database.

## 10. Visual comparison results

Every comparison decodes both screenshots and counts changed RGBA pixels, with no tolerance.

| Comparison | Count | Changed pixels |
|---|---|---|
| All 22 pages at 1440, 768, and 390 pixels | 66 | 0 |
| Expanded mobile navigation and filtered articles | 2 | 0 |
| After saving forms with no changes: five pages at 1440 and 390 | 10 | 0 |
| Hero section edited in the dashboard, against the same edit on the original | 1 | 0 |
| Menu item renamed and another added, against the same edit on the original: 1440, and 390 with the menu open | 2 | 0 |
| Services page while a new service is unpublished | 3 | 0 |
| Articles page after removing a new article | 3 | 0 |
| Home after removing new menu items | 3 | 0 |
| Services page after reverting section changes | 3 | 0 |
| Seven representative pages at three widths, after all dashboard flows | 21 | 0 |
| **Total** | **114** | **0** |

Comparing the running site on its un-upgraded database gave the same result: 68 comparisons, 0 with changed pixels.

Three things worth knowing:

- **The gate caught a real regression during this work.** The first capture of the new header showed about 2,790 changed pixels on every desktop page, because I had trimmed a space before the dropdown chevron. It was fixed, and this comparison is why it did not ship.
- **Two checks failed because of how I wrote the comparison, not because of the application.** Both are fixed in the harness, and neither needed any application change:
  - *Mobile navigation.* This state sometimes differed by 12 pixels, all anti-aliased edge pixels where the two floating buttons' shadows overlap. The markup there is identical on both sites, the original site differs from itself in the same way, and after repainting that one layer on both sites, 32 of 32 comparisons were exact. That points to paint history rather than content. The likely trigger is that the original injects its layout with `fetch` while Laravel renders it on the server, but I did not prove that.
  - *Hero edit.* My first version swapped the new hero image into the original page by script but let Laravel load it normally. It reported 132,877 changed pixels, none more than 5 levels out of 255, with no shift. A controlled experiment showed that when both sites load the image normally they are exactly identical (6 of 6 runs), and that the original differs from itself by up to 21 pixels depending on whether an image is loaded or swapped in. The check now loads the image the same way on both sides, through an in-memory rewrite in the harness's own static server. The original source files are never written.

New content was checked for consistency rather than equality, because the original has no equivalent. New cards, dropdowns, and sections showed no horizontal overflow or duplicate IDs at any width. As a stricter test, a renamed and an added menu item were compared against the same edit applied to the original page's DOM, on desktop and mobile. The hero-section edit from the earlier audit was repeated, with the new image loaded the same way on both sides. All matched exactly.

Screenshots are in `storage/app/cms-verification/completion/`.

## 11. MySQL connectivity status

**Not reachable, and the reason is deliberate. No MySQL server was started, no configuration or data was changed, and no write was attempted.** Everything below came from reading files and listing processes and ports.

| Check | Finding |
|---|---|
| Port 3306, 3309, and test port 3311 | Connection refused. Nothing is listening. |
| Windows service | No MySQL or MariaDB service is installed. |
| Process | No `mysqld` is running. Laragon and Apache are. |
| **Executable** | **`C:/laragon/bin/mysql/mysql-8.0.30-winx64/bin/mysqld.exe` does not exist. It is `mysqld.exe.DISABLED-by-owner`.** The `bin` folder was last changed on 12 October 2026 at 13:21. |
| Port consistency | `.env` says **3306**. Laragon and `my.ini` say **3309**. |
| Data directory | `my.ini` points to `C:/laragon/data/mysql-8`. |
| **Last start attempts** | 11 and 12 October 2026: InnoDB reported page log sequence numbers "in the future" and "Your database may be corrupt or you may have copied the InnoDB tablespace but not the InnoDB redo log files", then `Data Dictionary initialization failed`, then an assertion failure at `trx0trx.cc:595` and a crash. |
| Recovery folders | `_recovery_backup_20261011`, `_rebuild_mysql_20261011`, `_scratch_mysql_20261011`, `_validate_mysql_20261011`, `_prestart_backup_20261026`, and `_test_mysql_3311` exist beside it. |
| **This application's database** | **`velzon_admin` is not in `mysql-8` or any of those folders.** |
| Other projects | `mysql-8` holds 59 other databases, from other projects. |

**What this means:** the audit recorded a connection refusal. The actual situation is that the server was disabled by renaming its executable, its last logged start attempts failed, and it does not hold this application's data. I cannot see who disabled it or why. Restoring connectivity would not make this application work. Renaming the binary back risks further damage to the other projects' data, so I did not do it, and it should not be done until you decide.

**Schema compatibility.** A live inspection was impossible. The only evidence is `velzon_admin.sql`, a phpMyAdmin dump from 29 June 2024 (MySQL 8.2.0), which predates the sliders, partners, services, models, pagesettings, and contacts tables. It shows:

- `admins` has the columns the code needs, including `remember_token`. `role_id` defaults to 0, which means owner in this application.
- `users` and `migrations` are **MyISAM**, and `roles` is **latin1**. A `--single-transaction` dump does not protect MyISAM tables, and foreign keys need InnoDB, so the default engine must be confirmed before migrating.
- There are no `cms_*` tables, so no name conflicts in that snapshot.

This is a historical snapshot, not proof of a current database's state.

**Prepared for MySQL without running it.** Reading the SQL the code generates turned up one problem that SQLite hides: the existing "add item" action asked for a maximum from an ordered query, which MySQL's default strict mode (`ONLY_FULL_GROUP_BY`) very likely rejects. That action, and the same pattern in the new code, now clear the ordering first. Comparisons no longer depend on JSON key order, which MySQL normalises. Generated labels stay under the 191-character column limit. None of this has been run on MySQL, so treat it as reviewed, not verified.

The backup, restore-testing, and staging plan is in [section 13B and C](#b-decide-what-to-do-about-mysql).

## 12. Remaining blockers

| Blocker | Needs |
|---|---|
| Working database not upgraded, and it has no administrators | Your approval for section 13A |
| MySQL disabled, and the data directory appears corrupt | Your decision. This may be a wider recovery matter affecting other projects. |
| The real `velzon_admin` database has not been located | You: where does the production data live? |
| No MySQL execution of any CMS migration | A staging MySQL instance |
| Clinic image, social, video, directions, and map destinations | Content from you. Enter them in the dashboard. |
| Testimonials and medical, about, and clinic copy are provisional | Approved text from you. The original source's placeholder wording is still live, including visible text such as "هذا النص Placeholder فقط". |
| Production debug, HTTPS, cookie, and session settings | Configuration you control |
| Historical credentials in `velzon_admin.sql` and git history | Rotation and possibly history cleanup, by you |
| Video playback and live map embeds, email or SMS notifications, calendar booking | These are product decisions and were not requested as integrations. The site links to destinations and stores appointment requests. |

## 13. Exact steps requiring owner approval

Nothing below has been run.

### A. Activate the new features on the local site

Stop the server on port 8787 first, so the backup is consistent.

```powershell
Copy-Item storage/app/website-v1.sqlite storage/app/website-v1.before-upgrade.sqlite
./scripts/cms.ps1 migrate --path=database/migrations/2026_11_05_000002_add_cms_content_relationships.php
./scripts/cms.ps1 website:upgrade --dry-run
./scripts/cms.ps1 website:upgrade
./scripts/cms.ps1 website:admin your-email@example.com
./scripts/cms.ps1 serve
```

The same sequence ran on a copy of this database, with the result in [section 3](#3-database-migrations-added). To undo it, stop the server and copy the backup back over `website-v1.sqlite`.

### B. Decide what to do about MySQL

1. **Tell me where the real `velzon_admin` database lives** (a hosting provider, another machine, or a backup), and whether anything outside this project still depends on the `mysql-8` data directory.
2. **Do not rename the binary back or start the server on `C:/laragon/data/mysql-8`** until you have decided how to handle its apparent corruption. If those other projects matter, first take a cold file copy of the whole directory.
3. Choose where staging will run. I recommend a new, empty data directory on its own port, leaving `mysql-8` alone. Staging would need a working `mysqld`, which you control.

### C. Back up, restore-test, and stage

Each step needs separate approval. Replace the bracketed values. Run these against the real source only once it has been located.

1. **Inspect, read-only.** Confirm the host, port, version, `sql_mode`, default engine, collation, table list, and migration rows. Compare the `admins` and `roles` definitions with the code.
2. **Back up:**
   ```powershell
   mysqldump --host=[host] --port=[port] --user=[user] -p --single-transaction --routines --triggers --events --result-file=[backup.sql] velzon_admin
   Get-FileHash [backup.sql] -Algorithm SHA256
   ```
   Tables such as `users` and `migrations` may be MyISAM, so use a maintenance window or lock them if the data can change during the dump.
3. **Restore-test** into a separate, disposable database on the staging instance. Compare row counts, unique constraints, and representative content. Keep the original backup unchanged.
4. **Review the SQL** in [the appendix](#appendix-sql-for-review), or regenerate it with `php scripts/cms-mysql-ddl.php`. It needs no database.
5. **Migrate staging only**, with the three CMS migrations by explicit path:
   ```powershell
   php artisan migrate --path=database/migrations/2026_11_05_000000_ensure_cms_admin_auth_tables.php --path=database/migrations/2026_11_05_000001_create_website_cms_tables.php --path=database/migrations/2026_11_05_000002_add_cms_content_relationships.php
   ```
   Confirm with `SHOW CREATE TABLE` that the `cms_*` tables are InnoDB and that the foreign keys exist.
6. **Load content on staging.** Run `php artisan website:import` on an empty CMS, or `php artisan website:upgrade --dry-run` and then `website:upgrade` on existing CMS content.
7. **Test on staging:** run the browser checks, legacy module screens, and a real sign-in. Fix anything MySQL-specific before going further.
8. **Production** needs its own approval, a fresh verified backup, a maintenance window, and a rollback decision. Rollback means restoring that backup, because `migrate:rollback` on `000001` would drop the CMS tables and their content.

### D. Production configuration

Set `APP_DEBUG=false`, HTTPS, `SESSION_SECURE_COOKIE=true`, and the real `APP_URL`, and rotate any credential that has ever appeared in the dump or in git. `.env` was deliberately left untouched.

### E. Content only you can provide

Sign in and open **Content health**. Upload the approved clinic image and use "Replace" to apply it everywhere. Then work through the placeholder links and provisional text; each row has an Edit button.

## Appendix: SQL for review

Compiled by `scripts/cms-mysql-ddl.php` in Laravel's pretend mode, against a closed port so that no connection was possible. **Never executed.** Includes `2026_11_05_000002` only.

```sql
alter table `cms_pages` add `listing` json null, add `auto_listing` tinyint(1) not null default '0';
alter table `cms_sections` add `page_links` json null;
alter table `cms_items` add `target_page_id` bigint unsigned null, add `is_managed` tinyint(1) not null default '0', add `overrides` json null, add `detached_from` varchar(191) null;
alter table `cms_items` add constraint `cms_items_target_page_id_foreign` foreign key (`target_page_id`) references `cms_pages` (`id`) on delete set null;
alter table `cms_redirects` add `status` smallint unsigned not null default '301';
create table `cms_trash` (`id` bigint unsigned not null auto_increment primary key, `type` varchar(191) not null, `label` varchar(191) not null, `payload` longtext not null, `deleted_by` bigint unsigned null, `created_at` timestamp null, `updated_at` timestamp null) default character set utf8mb4 collate 'utf8mb4_unicode_ci';
alter table `cms_trash` add index `cms_trash_type_index`(`type`);
create table `cms_tombstones` (`id` bigint unsigned not null auto_increment primary key, `kind` varchar(191) not null, `key` varchar(191) not null, `created_at` timestamp null) default character set utf8mb4 collate 'utf8mb4_unicode_ci';
alter table `cms_tombstones` add unique `cms_tombstones_kind_key_unique`(`kind`, `key`);
```
