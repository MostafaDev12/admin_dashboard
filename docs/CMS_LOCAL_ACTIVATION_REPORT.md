# CMS local activation report

Date: 6 November 2026, Africa/Cairo. Scope: the existing local SQLite CMS only.

## Status

**The existing local SQLite CMS is activated and its new features have been exercised successfully on the running site at `http://127.0.0.1:8787`.** The working migration and non-destructive content upgrade were applied only after backup, restore-test, staging and automated verification passed. All 27 working-dashboard checks passed, with zero JavaScript errors and exact restoration of every pre-existing CMS row. The owner account is verified in the database; the owner confirmed on 6 November 2026 that their credentials successfully open the CMS dashboard. Production readiness is not claimed.

## Authorized database and backup

- Effective environment: `cms`, selected by `scripts/cms.ps1`; configuration is not cached.
- Effective connection: SQLite.
- Working file: `C:/laragon/www/elbaz_laravel/storage/app/website-v1.sqlite`.
- Consistent backup: `C:/laragon/backups/elbaz-cms/activation-20261106-01/before.sqlite`.
- SHA-256: `dde6a53397023cc09084cbb052bab5c30eee4ec6df99179431d51387332f7e1d`.
- Created using `SQLite3::backup()` with the working source opened read-only, not a raw active-file copy. The backup is outside the application tree. Both the backup and restored staging clone passed `PRAGMA integrity_check` and `PRAGMA foreign_key_check`; all table row hashes matched. Existing backup/clone paths are never overwritten by the activation helpers.
- Evidence: `manifest-before.json`, `clone-manifest.json`, `fixed-clone-manifest.json`, and `stage-preserving-final-check.json` in that backup directory. Manifests contain sanitized metadata and hashes, not account passwords or patient records.

The backup used immediately before working activation **includes the newly created owner account**:

- File: `C:/laragon/backups/elbaz-cms/activation-20261106-01/approved-working-before.sqlite`.
- SHA-256: `3890f74db0725ac739fa373b93a99c4289e15657fecee5abc1d760bde831cd0c`.
- Restore-tested through the backup API into `approved-working-restore.sqlite`. That clone received the final migration/upgrade and passed the complete original-data comparison, including the owner account (`approved-staging-verified.json`).
- Post-cleanup activated backup: `C:/laragon/backups/elbaz-cms/activation-20261106-01/activated-clean.sqlite`.
- Post-cleanup backup SHA-256: `e417f599a8be1ab9f5d47b039324fa35ba04686c3b9cde90274f7f98ebe1022d`.
- Final working evidence: `working-pre-migration-comparison.json`, `working-activated-verification.json`, `working-final-verification.json`, `working-final-snapshot.json`, and the dry-run/actual upgrade logs in the same directory.

Database counts, confirmed again after all working fixtures were removed:

| Table | Initial preflight | Activated working database |
|---|---:|---:|
| `cms_pages` | 22 | 22 |
| `cms_sections` | 79 | 80 |
| `cms_items` | 272 | 285 |
| `cms_media` | 19 | 19 |
| `cms_submissions` | 0 | 0 |
| `cms_redirects` | 0 | 0 |
| `admins` | 0 | 1 owner; already present in the pre-migration backup |
| `roles` | 0 | 0 |
| `cms_trash` | absent | 1 original-menu archive |
| `cms_tombstones` | absent | 0 |
| migration rows | 2 | 3 |

Original migration rows are `2026_11_05_000000_ensure_cms_admin_auth_tables` and `2026_11_05_000001_create_website_cms_tables`, batch 1. **Applied to the working database:** `2026_11_05_000002_add_cms_content_relationships`, batch 2, followed by `website:upgrade --dry-run` and `website:upgrade` through the existing CMS environment. No import was run. Legacy unrelated migrations were deliberately excluded.

## Independent findings and code fixes

The previous completion report was not accepted as proof. Source inspection, a migration pretend run, actual isolated migration, row-by-row comparison, and a transactionally rolled-back content upgrade found:

1. **The original content upgrade exceeded the owner's scope.** It deleted menu IDs 23–34 and media ID 11. Its menu archive could not be restored from the dashboard. The owner explicitly chose to revise the upgrade to preserve every existing row. It now creates 13 new consolidated menu rows, retains all 272 original items and their content, retains all 19 media records, and keeps the original header content and archive. Retired navigation groups do not render or appear as ordinary editable collections. Their old edit URLs lead to the active menu; direct mutation routes reject them. Backfill and content-health scans skip these retired rows. The current dry-run removes zero item/media IDs and changes zero original item-content values.
2. **SQLite did not enforce the declared placement foreign key.** Laravel's SQLite ALTER TABLE compilation silently omitted the table-level constraint. A controlled invalid reference succeeded on the initial clone. The pending migration now uses an additive inline nullable SQLite reference, with `ON DELETE SET NULL`; MySQL keeps its original constrained-column path. A fresh clone rejects the invalid reference. Original data columns and row counts remain exact after the schema migration. This correction preceded the migration's first working-database application here; any other installation that already recorded the old version needs a separately reviewed forward repair, not an automatic rerun.
3. **Administrator creation could replace an account without confirmation.** `website:admin` now asks before replacing an existing password, name, or role; declining leaves all account attributes unchanged. New accounts still require a hidden password of at least 12 characters.
4. Verification helpers now refuse to overwrite old clones; browser evidence can use distinct run names and database paths. The asset audit's `--read-only` option preserves the existing frontend mapping document.
5. Final login-view inspection found two inactive controls: the admin forgot-password link targeted the public user reset route, and the remember checkbox submitted no value. The link now targets the existing owner-guided `/admin/forgot` flow, and the checkbox submits `remember=1`. The stock design is unchanged. A focused regression verifies both controls and the recovery redirect.

The staged content upgrade reports 90 linked active placements, 17 page listings, one optional map field, and zero pruned media records. It adds the managed-message section and the original-menu archive. Existing settings, appointments, media, pages, sections, users, and roles are retained. No import or database reset was used.

Schema inspection includes existing primary/unique/order indexes, the unique tombstone `(kind,key)` constraint, and the trash type index. Original page/section/item cascade foreign keys remain; the newly tested placement reference uses `SET NULL`. The compatibility auth schema still lacks a role foreign key and a database-enforced unique admin-email constraint; these need review before multi-user production use. The current owner email has one account. No existing user/role table was replaced.

## Administrator and dashboard

Requested owner email: `admin@demo.com`. No default owner password was generated or seeded. The original `website:admin` helper targets `--env=cms`. A verbal success indication was initially checked and found not to match the database; the waiting command was stopped and the owner ran the command directly. A subsequent read-only SQLite query **verified the owner account exists, ID 6, role 0, with a bcrypt password hash**. No password/hash value was emitted. The owner's password and account attributes match the pre-migration backup after activation and test cleanup. On 6 November 2026, the owner confirmed that signing in at `/admin/login` with their own credentials successfully loads `/admin/cms`.

Headless verification successfully logged in through the actual working `/admin/login`, loaded `/admin/cms`, performed real dashboard operations and logged out, after which dashboard access redirected to login. It used a uniquely marked temporary owner test account and removed it; the owner's account was never reset or substituted. PHPUnit verifies editor/owner authorization separately in memory; the staging browser verifies CSRF, anonymous access and concurrent-session revocation. Browser fixtures never seed a persistent public credential.

## Tests and visual evidence

- Baseline rerun: **160 tests, 9,511 assertions passed** (`activation-tests.xml` and `.txt`).
- New account-preservation tests: **2 tests, 9 assertions passed**. The first test attempt compared a newly created model's partially loaded attributes with a fully loaded row; the test was corrected to compare two fully loaded snapshots.
- New SQLite foreign-key regression: **1 test, 5 assertions passed**.
- Final full post-change suite: **165 tests, 9,858 assertions, zero failures**, recorded in `storage/app/cms-verification/activation-release-tests.xml` and `.txt`. Earlier post-change failures exposed tests selecting retired menu records and not expecting the new confirmation prompt; they were corrected to verify the intended behavior, retaining their frontend assertions. The added tests cover non-overwriting account setup, actual SQLite FK enforcement, original-row preservation/read-only navigation, and stale media retention.
- After the final login-view correction, the affected account/login class passed **3 tests, 17 assertions** (`activation-login-tests.xml` and `.txt`), including the newly added control/recovery scenario. The full-suite result above precedes this final view-only correction; the affected class was rerun afterward.
- **Staging browser: 116 checks passed, 114 visual comparisons with zero changed pixels, zero JavaScript errors, all 16 dashboard screens loaded, 25 internal destinations/fragments checked with none broken.** Real dashboard forms cover all original public variants, desktop/tablet/mobile, no-op saves, equivalent-content edits, service/article lifecycle, menu nesting/devices, section compatibility, managed feedback, authentication/CSRF/session controls, trash/restore and media replacement. Screenshot comparisons decode RGBA pixels with no tolerance. Evidence: `activation-staging-final-report.json` and `activation-staging-final/`.
- **Working dashboard: 27 checks passed, zero JavaScript errors**, following a successful 27-check rehearsal on a separate clone. Evidence: `activation-working/report.json` and its screenshots. The temporary service/article, collection item, supported section, menu entry, media upload, request and test account were removed; heading, paragraph, CTA, image and message values, section link metadata and timestamps were restored. Every original CMS row was compared exactly and all table counts returned to their activated baseline. The original owner account was separately compared with the pre-migration backup.
- **Additional direct working-site visual comparison: 23 comparisons, zero changed pixels, zero JavaScript errors.** This includes 21 page/viewport comparisons and expanded-navigation/filtered-article states. Pages: home, About, Services, Articles, Contact, service detail and article detail, each at 1440, 768 and 390 pixels. This run was read-only and performed no upload removal or database setup. Evidence: `activation-working-visual-report.json` and `activation-working-visual/`.
- Asset/source audit: **27 original assets unchanged, zero missing copied assets, zero unexpected asset changes, 152 generated templates, zero hardcoded Arabic-content templates**. Expected integration scripts and intentionally retired source artifacts are excluded explicitly. This is distinct from the two already missing `clinic.jpg` references in rendered pages.
- Registered route inspection: **139 routes, 45 CMS routes, zero missing controller methods, zero legacy admin GET mutation routes**. The separate anonymous visitor GET remains a mutation risk, described below; the route helper's admin-only count does not cover it.
- Pint is limited to files deliberately changed during activation. No project-wide formatting, frontend redesign, Velzon replacement, commit, push, or deployment was performed.

Browser runs aborted while investigating font/capture progress are not counted as successful full verifications. The final named run is the authoritative evidence. Original source files were never edited for screenshot comparisons; equivalent edits are applied in memory by the harness.

The working rehearsal also caught test-harness issues before activation: a publication selector matched hidden inputs, a global link count also counted the service menu entry, and message-save metadata normalized `page_links` from null to an empty array. Selectors were scoped to the actual select/listing, and cleanup now restores the original link metadata along with values and timestamps. The corrected rehearsal and real working run both passed exact original-row comparisons. No original editorial record was deleted.

SQLite autoincrement high-water marks and ordinary temporary browser-session files are not reset during fixture cleanup; resetting them is unnecessary and would exceed the cleanup's purpose.

## Working-feature coverage

| Workflow | Actual verification |
|---|---|
| Heading, paragraph, button label/destination, uploaded image selection | Working dashboard edits appeared on the public home page; original values restored. |
| Collections | Working add/edit, visible order change, hide/show and frontend output; full in-memory CRUD matrix covers all 72 defined active groups, including nested collections. |
| Services | Working draft creation, publication, automatic listing, unpublish, republish, detail and SEO; temporary page trashed/restored and then cleaned. |
| Articles | Working publication, listing/category creation, linked desktop/mobile menu, unpublish/republish; staging also verifies category filtering and placement lifecycle. |
| Trusted sections | Working add, show and reorder on a temporary page; staging checks every compatible design across seven page types at three widths. |
| Menus | Different working desktop/mobile labels and publication-controlled destinations; staging verifies independent dropdowns, nested entries, ordering, remove and restore. |
| Forms/messages | Working edited success message and real form submission, then restoration/removal of the test request; staging covers validation/network/session/rate-limit/server-error wording. |
| Content health/media | Working health screen and media upload/select; staging additionally replaces the missing image in both locations and verifies rendered images. The approved working clinic image remains owner-supplied. |
| Trash | Actual working service-page trash/restore, including its child content; suite/staging additionally cover section/item/nested recovery and owner-only purge. |
| SEO | Working title, description and noindex output; full suite covers canonical, Open Graph, redirects, sitemap and escaping. |
| Authentication/authorization | Working real-form temporary-owner login, dashboard and logout; suite verifies owner/editor restrictions and CLI account preservation; staging verifies CSRF, redirects and session revocation. The owner also confirmed successful sign-in with their own credentials on 6 November 2026. |

## Legacy security decisions

| Route | Actual functionality and dependencies | Decision |
|---|---|---|
| `/register` | Laravel `RegistersUsers`, guest/web guard, CSRF-protected POST; name/email/password/avatar validation, unique `users.email`, hashed password, generated image name, `users` table and writable `public/images`. It creates a public web user, not an administrator. The current CMS SQLite schema has no `users` table, so a functioning registration POST is not established here. No frontend caller was found in current public templates/scripts. No registration write was submitted. | Recommend retiring or disabling after the owner confirms external usage. If retained, provision its real dependencies, add registration abuse controls and a business-approved verification policy. Leave routes untouched until usage is verified. |
| `GET /api/visitors` | Anonymous API route, normal API throttling but no authentication. Reads client/proxy IP headers, calls `http://www.geoplugin.net/json.gp`, and conditionally `Visitor::firstOrCreate()` in `visitors`. That table is absent locally. Header trust, unencrypted external IP disclosure, network blocking, and GET-side database writes remain concerns. No caller was found in the public website. | Recommend retiring after owner usage confirmation. If required, replace the beacon with an explicit POST, trusted-proxy/IP handling, bounded HTTPS lookup, privacy controls, rate limiting and appropriate authorization. **It was not invoked.** |

`velzon_admin.sql` still contains historical administrator/password-hash data and SMTP fields. Inspection emitted only booleans, no secret values. Validity cannot be established without the real systems. Owner-controlled rotation and possibly repository-history cleanup remain separate tasks; no SQL/history files or credentials were altered.

## MySQL and production limits

Read-only inspection found no listener at ports 3306, 3309, or 3311 and no running MySQL server. The binary remains `C:/laragon/bin/mysql/mysql-8.0.30-winx64/bin/mysqld.exe.DISABLED-by-owner`. No server restart, recovery, datadir changes, MySQL connection writes, migrations, imports, or replacement were attempted. The historical report's corruption/backup findings are not a successful recovery or live-schema verification.

Environment file SHA-256 values remain:

- `.env`: `1cab22d86b42408becd36cf15f5ede252f540f9f1d0bae80550d820fd4a6c036`
- `.env.cms`: `2150bab8b12d76c5309c1c3ff8048fe048ec1dad3d2bf479f8e5d1b2db29a8c1`

Production readiness is not claimed. MySQL schema/data/account state and a restore-tested current MySQL backup remain unknown. Debug/TLS/secure-cookie settings, mail/SMS/calendar integrations, real map/video/social destinations, the clinic image, provisional medical/about/testimonial copy, historical credentials, and non-Chrome/production-server behavior still need owner work or separate verification. Per-page SEO is managed; a global SEO defaults editor and a general-purpose page builder are not implemented.

## Recovery and remaining work

The consistent backup was restored into a separate SQLite clone and checked by integrity, foreign keys, counts and complete original-row hashes. Never use `migrate:rollback` as content recovery: it removes new columns/tables and does not reverse content consolidation. If working verification fails, stop further writes, preserve the failed database/evidence and the unchanged backup, stop the local server, and obtain the owner's recovery decision before replacing the working file. A restore must use the verified backup with the application stopped and must be checked again; no working-file restore was performed.

Completed local gates: original and owner-inclusive consistent backups, restore tests, migration SQL review and actual staging upgrades, passing full suite and browser checks, additive working migration, non-destructive working content upgrade, original-data/owner preservation, FK/integrity checks, and passing working dashboard fixtures with cleanup.

Remaining tasks:

The owner confirmed successful dashboard login on 6 November 2026, completing the final local access check.

1. Supply the approved clinic image, replace the ten unique placeholder destinations and eighteen provisional text entries through the dashboard. The two clinic-image references remain missing by design until approved content is supplied. Stale media record #11 was retained as requested.
2. Decide the legacy registration/visitor routes after confirming external usage. Retained legacy modules also need their own tables: the SQLite CMS is not a complete restored copy of the old MySQL application, so their production functionality is not claimed.
3. Locate the real MySQL data, obtain an independently restore-tested backup, inspect actual schema/engines/auth constraints and approve separate staging work. Do not restart the disabled server or use the old possibly corrupt datadir as an activation shortcut.
4. Complete the owner-controlled production configuration, credential rotation and approved-content work. Configure notification/calendar/map/video integrations separately if desired.
