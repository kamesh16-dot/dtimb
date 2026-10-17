# TIMB CMS — Changelog

All notable changes to the TIMB website and CMS are documented here.  
Format: [Keep a Changelog](https://keepachangelog.com/en/1.0.0/).  
Versioning: `<major>.<phase>.<patch>`.

---

## [1.19.0] — 2026-10-17 — Dashboard rebuilt, and a 500 on the login page fixed

The dashboard showed four counts, six links and a line confirming the database
was connected. Everything on it was already visible in the sidebar. Now that
every module works and the audit log is actually written, there is real
material to show.

### Added
- **Needs attention** — the actionable items, most pressing first: unread
  enquiries and applications, unpublished machines, draft articles, pages whose
  title or description needs a look, locked accounts, email not configured, and
  a `robots.txt` shadowing the generated one. Each links straight to the screen
  that fixes it, and when there is nothing outstanding it says so
- **At a glance** — the four counts now carry a second line each: how many
  messages in total, how many machines are unpublished, how many articles are
  drafts, how many images are unused. Each tile is a link
- **Latest messages** — the five newest enquiries and applications together,
  newest first, unread ones marked
- **Recent changes** — the last eight entries from the audit log, phrased in
  plain words ("Machine updated", "Media uploaded") with how long ago
- **Quick actions** now point at the actions themselves — write an article, add
  a machine — rather than at list screens already one click away in the sidebar

### Fixed
- **Visiting `/admin/login` while already signed in returned a 500** —
  `Auth::login()` was declared as returning `string` but returns a redirect in
  that case. Anyone with the login page bookmarked, or pressing back after
  signing in, hit an error page. Found while checking the dashboard in a
  browser, not by reading the code
- **Recent changes said the same thing twice** on sign-in rows: "Signed in"
  above "Logged in"

### Notes
- Every panel is gathered defensively: one unreadable table leaves its own
  panel empty rather than taking the screen down
- The dashboard is view and controller code, which the test suite does not
  cover; it was verified in a browser with real data, including an enquiry
  submitted through the public form

---

## [1.18.1] — 2026-10-17 — Media Library: upload opens a dialog

### Changed
- **The Upload button opens a dialog** rather than jumping to a form at the
  bottom of the page, so the fields appear over the library instead of past 64
  thumbnails. Closes on Cancel, the X, Escape or a click on the backdrop
- **A rejected upload reopens the dialog with the reason inside it**, where the
  fields are, rather than leaving the message behind the closed dialog
- Without scripting the dialog is shown inline, so the form stays usable

---

## [1.18.0] — 2026-10-16 — Settings: wired to the site, and to the right keys

The Settings screen stored its values under eight key names it invented —
`site_name`, `contact_email`, `social_twitter` and so on — while the table
already held seven rows under different names. Saving wrote a second, parallel
set that nothing ever read, and the form showed blanks because it was looking
for keys that were never there. Nothing on the site read either set.

### Added
- **`app/Models/SiteSettingModel.php`** — the six values the layout actually
  renders, each tied to its field on `Config\Site`, which supplies the default
  and stays the fallback when the table cannot be read
- **Settings now reach the site** — `BaseController` applies them to the shared
  `Config\Site` instance, so the name, tagline, contact email, LinkedIn link
  and footer line change on every page
- **Validation** — required fields, a real email address, and full web
  addresses for the links. The old screen accepted anything
- **Outgoing email status**, read-only: whether SMTP is configured, and where
  to set it. Credentials belong in `.env`, not in the database and not editable
  through a browser, so the screen reports rather than edits
- **Pointers to where the rest lives** — addresses and map pins in the Lists
  Hub, titles and descriptions in SEO, the Products menu in Machines
- **Tests** — `Unit/SiteSettingTest`, 10 cases. One asserts every key names a
  real property on `Config\Site`, which is the exact failure the old screen had.
  Suite: 132 tests, 1850 assertions

### Fixed
- **The form read and wrote the wrong keys**, so it displayed blanks and saved
  into rows nothing consumed
- **Editing settings changed nothing on the site** — `Config\Site` was still
  hard-coded
- **The public address was asked for twice** — SEO had its own field for it.
  SEO now falls back to the address set here, and its own field is an override.
  As a result canonical links and the sitemap resolve to the real domain rather
  than to whatever host the page was requested on

### Notes
- Two stored rows are deliberately not on the screen. `address` is rendered
  nowhere — the addresses visitors see are locations in the Lists Hub — and
  `product_menu` is left over from before the menu was built from the machines
  table. Both are left in place rather than surfaced as fields that do nothing
- `contact_phone` and `social_twitter` existed only in the old form's markup;
  the site has no such values, and the phone numbers shown are per-location

---

## [1.17.0] — 2026-10-16 — Users: the screen that never existed

`/admin/users` had routes and views but no controller. The only `Users` class
was one of seven crammed into `app/Controllers/Admin/Other.php`, a file PSR-4
can never load, so the page 404'd. The other six classes in it duplicated
controllers that already had their own files — dead code that would have been
a fatal redeclaration had anything ever loaded it.

### Added
- **`app/Controllers/Admin/Users.php`** — the controller that was missing
- **Editing an account** — name, email and role. Previously create and delete
  only, so a typo in an email address meant deleting and starting again
- **Setting a password for someone** — for a locked-out or forgetful user. The
  holder is asked to choose their own at next sign-in
- **Unlocking** an account locked by repeated failed sign-ins. The lockout has
  worked since the CMS was built, but nothing could clear it short of waiting
- **Switching an account off** without removing it
- **Restoring a removed account** — removal is a soft delete, and removed
  accounts are listed separately with their email addresses still reserved
- **Two rules that prevent lock-out**: nobody can remove or disable their own
  account, and the last active admin cannot be removed, disabled or demoted.
  The form disables those controls and explains why rather than failing on save
- **Tests** — `Unit/UserPolicyTest`, 9 cases over the password policy, the role
  list and lockout detection. Suite: 122 tests, 1782 assertions

### Fixed
- **`/admin/users` returned 404** for every admin, in every install
- **Passwords were only checked on the user's own change screen** — an admin
  creating an account could set any password at all, including one the holder
  would then be unable to replace with anything weaker. The policy now lives in
  `UserModel` and both paths use it
- **"You" never appeared beside your own row** — the view compared a string id
  against an integer with `!==`, which is always true

### Removed
- **`app/Controllers/Admin/Other.php`** — 186 lines declaring seven classes,
  six of them duplicates of real controllers. Verified afterwards that all
  twelve admin screens still load

### Notes
- Role separation was tested end to end: a new editor is forced to set a
  password at first sign-in, can reach the dashboard and content screens, and
  is refused Users, Settings and admin-only actions such as deleting a machine

---

## [1.16.0] — 2026-10-16 — Redirects: now actually redirect

The Redirects screen wrote `from_url` and `to_url`; the table has `from_path`
and `to_path`. The list silently showed nothing and adding one failed. More to
the point, nothing in the application ever read the table, so even a correctly
stored redirect did nothing at all.

### Added
- **`app/Controllers/NotFound.php`** — set as the 404 handler, it looks for a
  redirect before showing the error page. Hooking here rather than on every
  request means the table is read only when a page genuinely was not found, so
  a normal page view costs no extra query
- **`app/Models/RedirectModel.php`** — path normalising, matching, hit
  counting and the checks below
- **Matching is forgiving** — a leading slash is added, a trailing slash
  ignored, and case does not matter, so `/Old-Products/` finds `/old-products`
- **Query strings survive** — `?utm_source=linkedin` is carried to the target
- **External targets** — a redirect may point at another site
- **Hit counting** — each redirect shows how many visits have followed it,
  using the `hits` column that existed but was never written
- **Editing** — previously add and delete only
- **Guards on saving**: a duplicate source, a redirect to itself, a full web
  address as the source, the site root as the source, and loops. The loop check
  follows the chain, so adding `/c → /a` is refused when `/a → /b → /c` exists
- **A "two hops" warning** where a target is itself redirected: it works, but
  wastes a round trip
- **Tests** — `Unit/RedirectPathTest`, 10 cases over path normalising.
  Suite: 113 tests, 1764 assertions

### Fixed
- **Redirects admin used non-existent columns** — `from_url` and `to_url`
  replaced with the real `from_path` and `to_path`
- **The front controller broke path matching** — the requested path arrives as
  `/index.php/old-products` depending on the server's rewrite rules, so no
  stored path ever matched. Added `timb_path()` to `site_helper`, which also
  replaces the copy of this logic written for the canonical links last release

### Changed
- **An unknown address now returns a 404 response rather than throwing
  `PageNotFoundException`**, because the override handles it. Two existing
  tests asserted the exception and were updated; a third was added covering the
  case where the redirects table cannot be read at all

### Notes
- A redirect cannot override an address the site still serves — the page wins.
  That is the trade-off for not querying on every request, and the screen says so

---

## [1.15.1] — 2026-10-16 — SEO: readable sitemap, fewer false alarms

### Changed
- **The sitemap is now shown as a table on the SEO screen** — every address
  with the date it last changed. The raw `sitemap.xml` and `robots.txt` are
  still one click away, relabelled "Raw", since both are written for search
  engines and read as a wall of text in a browser

### Fixed
- **The audit cried wolf on short titles** — "About | TIMB" is 12 characters
  and perfectly fine, but the minimum was set at 15. Lowered to 10, which still
  catches a near-empty title. Pages flagged as needing attention dropped from 8
  to 6, and all six remaining are genuine: four descriptions running past the
  160-character cut-off and two that are very short

---

## [1.15.0] — 2026-10-16 — SEO: a working audit, a sitemap and a real robots.txt

The SEO screen read the same three columns the old Pages screen did —
`slug`, `meta_title`, `meta_description` — none of which exist. It rendered ten
rows of dashes with Edit links that 404'd, and had no save route at all.

### Added
- **`app/Libraries/SeoAudit.php`** — checks all 22 public addresses (10 pages
  and 12 machines) and reports the title, the description, their lengths, and
  anything worth fixing: missing, too long for a search result, very short, or
  duplicated across pages. 14 are clean; 8 have something worth a look
- **`app/Models/SeoSettingModel.php`** — site-wide settings in the `settings`
  table under a new `seo` group, each with a default so nothing depends on a
  row existing
- **`/sitemap.xml`** — generated from what is actually published, with a last
  changed date per address. The news page's date follows its newest article
- **`/robots.txt`** — generated, pointing at the sitemap and keeping crawlers
  out of `/admin/`. Extra rules can be added from the screen
- **Canonical links and sharing previews** — `app/Views/partials/seo_head.php`
  adds a canonical link plus Open Graph and Twitter tags, so a link shared on
  LinkedIn or WhatsApp shows a title, description and image. Both groups can be
  turned off
- **A staging switch** — turning indexing off adds `noindex, nofollow` to every
  page and makes robots.txt disallow everything
- **Tests** — `Unit/SeoAuditTest`, 7 cases pinning the settings contract and
  the defaults. Suite: 100 tests, 1738 assertions

### Fixed
- **The SEO screen listed nothing** and its Edit links pointed at an empty page
  key. It now reads the real columns and links to the right editor
- **`public/robots.txt` shadowed the CMS** — a static two-line file the web
  server served in place of the generated one, which `php spark cms:status` has
  been warning about. Removed; `cms:status` now reports it clean. The screen
  warns if it reappears
- **Canonical URLs included the front controller** — `/index.php/about` rather
  than `/about`, which would have advertised two addresses for one page.
  Found during testing, before release

### Notes
- This release changes rendered HTML for the first time: ten tags are added to
  every page's `<head>`. Nothing is removed and nothing on the page itself
  changes, verified across all 12 public addresses
- Per-page titles and descriptions are still edited in Pages and Machines; this
  screen reports on them and links across

---

## [1.14.0] — 2026-10-16 — Inbox: messages now actually arrive

The inbox could never show anything. `App\Controllers\Forms` wrote every
contact enquiry and career application to a JSONL file and tried to email it,
and never inserted a row. Both tables had sat empty since the CMS was built.
Marking a message read wrote to a `read_at` column that does not exist, so the
one action the screen offered returned a 500.

### Added
- **Submissions are saved to the database**, where they appear in the inbox. If
  the insert fails the old JSONL file is still written, so a visitor's message
  is never lost to a database problem
- **`app/Models/InboxModel.php`** with `EnquiryModel` and `ApplicationModel` —
  shared status handling over the two tables
- **A message detail view** — the full message, the sender's details, and a
  reply-by-email link. Opening a new message marks it read
- **New / Read / Archived workflow** with counts on each filter tab, replacing
  a single "Mark read" button. Messages can be marked unread again
- **Internal notes** on enquiries, using the `notes` column that existed but
  had no interface
- **CV download** — career applications store a CV that was previously
  unreachable from the admin at all. CVs stay outside the web root and are
  streamed through an authenticated route under a readable file name
- **Tests** — `Unit/InboxStatusTest`, 9 cases covering the status contract and
  the CV path guard. Suite: 93 tests, 1678 assertions

### Fixed
- **Mark as read returned a 500** — it wrote `read_at`, which the table does
  not have. Status changes now go through the real `status` column
- **`cv_path` was never populated** — the careers form moved the uploaded file
  into place and then recorded it under a key nothing read
- **An unresolved route filter** — writing the message type as a regex
  alternation left the route's `adminauth` and `csrf` filters unresolved,
  which would have left those URLs reachable without a session. The type is a
  plain segment now, validated in the controller

### Notes
- CVs are personal data. They are stored under `writable/uploads/careers/`,
  are not fetchable by URL, and the download route refuses any stored path
  that resolves outside that directory
- No submissions existed in the database or on disk, so there was nothing to
  migrate

---

## [1.13.0] — 2026-10-16 — Media Library: working browser and uploader

The Media Library listed its 64 images through three column names the table
does not have, so every tile was a broken thumbnail pointing at `/uploads/`
with no caption. Uploading wrote into a directory that does not exist and then
failed on the insert. Deleting tried to unlink a file by a column that is
always undefined, and removed the row anyway.

### Added
- **`app/Models/MediaModel.php`** — the library's source of truth: variants,
  folders, disk sizes, and a usage lookup
- **Usage tracking** — each image shows where it is referenced across machines,
  hero slides, news heroes and news galleries, built in four queries rather
  than one per image
- **Deletion is refused while an image is in use**, and names what is using it.
  Only unused images offer a Delete button
- **Uploading actually works** — it generates the same -1600 and -640 webp
  variants `tools/optimize_images.py` produces, at the same quality, never
  upscaling, then indexes the result. A failure part-way removes any variant
  already written rather than leaving half an image set behind
- **Uploads are usable immediately** — `timb_manifest()` now merges the media
  rows over `app/Data/ImageManifest.php`, so a newly uploaded image can be used
  by the Machines and News modules without regenerating the file
- **Renaming** — an image's display name is editable and feeds the home page
  gallery's description text
- **Tests** — `Unit/MediaManifestTest`, 7 cases over the manifest contract.
  Suite: 84 tests, 1643 assertions

### Fixed
- **Media admin used non-existent columns** — `filename`, `original` and
  `mime_type` replaced with the real `path`, `original_name` and `mime`. Every
  thumbnail was broken and every caption blank
- **Upload wrote outside the asset tree** — it moved files to `public/uploads/`,
  which does not exist, and stored no usable variants. Uploads now land beside
  the existing optimised set
- **Delete removed the row but never the files**, because it looked for them by
  a column that does not exist

### Notes
- Uploads accept JPG, PNG and WebP up to 12 MB, and are converted to WebP
- The library covers the machine image set. The handful of news images under
  `public/assets/img/news/` follow a different convention and are not indexed

---

## [1.12.0] — 2026-10-16 — Lists Hub: built, and wired to the live site

The Lists Hub was ten cards reading "Coming soon", every link pointing at `#`.
Behind them sat fifteen populated tables with no interface at all, and the
public site read all of that content from `Config\Site` and `App\Data\Content`
instead of the database.

### Added
- **`app/Config/Lists.php`** — a registry describing each list: its table,
  label, where it appears on the site, and its fields with types. One
  controller, one model and one pair of views now serve all fifteen
- **`app/Models/ListItemModel.php`** — generic CRUD over any registered list,
  including the five tables that store their fields positionally in a single
  `payload` column
- **`app/Libraries/SiteLists.php`** — turns the tables into the exact arrays
  the public views already expect. Each list falls back to the hard-coded copy
  on its own, so one empty table cannot blank a section
- **All fifteen lists are now editable**: Stats, Hero Slides, Pillars, Process
  Steps, Line Stages, Home Industries, Industries, Projects, Case Studies,
  Client Work, Client Names, FAQ Items, Career Areas, Network Nodes and
  Disclaimer Points. Five were not even listed on the old hub
- **Add, edit, reorder, hide and delete** on every list, with per-field
  validation. Reordering rewrites the whole list's order, so pre-existing gaps
  or duplicate sort values cannot make a move do nothing
- **Hide as the reversible alternative to delete** — these tables have no
  `deleted_at` column, so deletion is permanent and the form says so
- **Field types** covering what the tables actually hold: plain text, long
  text, numbers, decimals, fixed choices, JSON string lists edited one per
  line, and JSON number pairs edited comma-separated
- **Tests** — `Unit/ListRegistryTest`, 9 cases over the registry's shape,
  checking every field has a known type, payload positions name real fields,
  and JSON columns are never edited as plain text.
  Suite: 77 tests, 547 assertions

### Fixed
- **The hub was a hard-coded array of labels** with every link disabled, and it
  listed a Clients entry while omitting Hero Slides, Line Stages, Process
  Steps, Case Studies, Client Names and Projects
- **Public site ignored all fifteen lists** — `BaseController` now overlays
  them onto `Config\Site`, and `Pages` onto `App\Data\Content`

### Notes
- Verified by comparing all fifteen tables against their source arrays field by
  field, then confirming every public page renders byte-identically

---

## [1.11.0] — 2026-10-16 — Pages module: wired to the live site

The Pages admin was the worst of the three. The list was a hard-coded array of
eleven labels matched against a `slug` column that does not exist, so every
meta title showed as "—" and clicking Edit returned a 500. The table held rows
for only two of the site's ten pages, and every page's title and description
lived in `App\Controllers\Pages`.

### Added
- **`app/Models/PageModel.php`** — the pages source of truth: validation, SEO
  lookup, and content blocks with the intro merged back in
- **A row per public page** (migration `2026-10-17-000002`) — ten pages seeded
  with exactly the titles and descriptions the controllers already produced, so
  nothing visible changed. The admin list is now data, not a hard-coded array
- **The `intro` column is now used** — each page's lead paragraph was buried
  inside the `body_blocks` JSON, where it could not be edited without editing
  raw JSON. The migration moves it out; the model puts it back when rendering
- **Content block editing** — guarded JSON editing for the two pages that have
  blocks, with the block names listed. Invalid JSON is rejected before saving,
  so a mistake cannot take a page down
- **SEO is editable and live** — browser title and search description now come
  from the CMS on all ten pages, including the home page, which previously had
  no CMS-controlled title at all. A character counter is shown
- **Tests** — `Unit/PageBlocksTest`, 11 cases over the block conversion.
  Suite: 68 tests, 273 assertions

### Fixed
- **Pages admin used non-existent columns** — `slug`, `meta_title`,
  `meta_description` and `content` replaced with the real `key`, `title`,
  `seo_title`, `seo_description`, `intro` and `body_blocks`. Opening any page
  for editing returned a 500
- **Saving a page wiped its content blocks** — the blocks field is only shown
  for pages that have blocks, and an absent field was being treated as an empty
  value rather than as "unchanged". Found during testing, before release
- **Public pages ignored the CMS** — `Pages` and `Home` now read title,
  description and blocks from the database, falling back to the hard-coded
  values and `App\Data\Content` when it is unreachable. Blocks merge over the
  file copy rather than replacing it, so a page view can never reach for a
  block that is not stored yet

### Notes
- No create or delete here: every page is a fixed route with its own view, so
  the CMS cannot add or remove one. The publish toggle was left out for the
  same reason — it could not actually take a page offline
- A "Machines" entry appeared in the old hard-coded list; there is no such
  route, so it is not seeded

---

## [1.10.0] — 2026-10-16 — News module: wired to the live site

The News admin screens had the same defect as Machines: they were written
against column names `news_articles` does not have, and /news read its
articles from `App\Data\Content` rather than the database.

### Added
- **`app/Models/NewsModel.php`** — the news source of truth: validation, the
  `Content`-shaped listing the news view expects, and gallery handling
- **Photo gallery editing** — `news_gallery` had no interface at all. The form
  now has an add/remove repeating list of image paths and descriptions, saved
  in order in one transaction
- **Paragraph body editing** — `body` is stored as a JSON array; the form edits
  it as plain text with blank lines between paragraphs and converts both ways
- **Restore for a deleted article** — delete is a soft delete and keeps both the
  slug and the gallery rows, so `POST /admin/news/restore/{id}` undoes it
- **Tests** — `Unit/NewsBodyTest`, 13 cases over the body conversion.
  Suite: 57 tests, 254 assertions

### Fixed
- **News admin used non-existent columns** — `published_at` and `excerpt`
  replaced with the real `published_on` and `summary`. The list silently showed
  nothing and the form saved nothing
- **Seven fields had no interface** — `tag`, `hero_path`, `hero_alt`, `intro`,
  `quote`, `sort_order` and the gallery were unreachable from the CMS, so most
  of a published article could not be edited
- **Editing an article would have silently done nothing** — the unique-slug
  rule's `{id}` placeholder had no value, the same defect found in Machines
- **Public news page ignored the CMS** — `Pages::news` now reads articles from
  the database, falling back to `App\Data\Content` when it is unreachable

### Notes
- A CSRF failure in production silently redirects back with no message
  (`Config\Security::$redirect`). An editor whose token has expired loses the
  form and is told nothing. Not changed here: it affects every admin form

---

## [1.9.0] — 2026-10-16 — Machines module: wired to the live site

The Machines admin screens were written against column names the `machines`
table does not have, and the public site read its machine content from
`Config\Site` rather than the database. Both are fixed; the Machines module is
now the live source for what visitors see.

### Added
- **`app/Models/MachineModel.php`** — the machines source of truth: validation,
  the `Config\Site`-shaped listings the public views expect, the header menu,
  and the product-page payload
- **`machines.nav_order`** (migration `2026-10-17-000001`) — the header Products
  menu is ordered differently from the product listings, and the original import
  kept only `menu_order`. Backfilled from `Config\Site::$productMenu`, so the
  live menu is unchanged
- **Restore for a deleted machine** — delete is a soft delete and keeps the slug,
  so `POST /admin/machines/restore/{id}` undoes it
- **Details skeleton** — a machine added through the CMS has no stored
  `details_json`; its page now renders from its own fields instead of 404ing
- **Tests** — `Unit/MachineDetailsTest` (payload completeness) and two
  `img_tag` cases. Suite: 44 tests, 238 assertions

### Fixed
- **Machines admin used non-existent columns** — `sort_order`, `category`,
  `description` and `is_featured` replaced with the real `menu_order`,
  `group_name`, `kind`, `series_label`, `metric`, `visual_key`, `image_key`,
  `spec_sheet`, `show_in_menu` and `is_published`. The list silently showed
  nothing and the form saved nothing
- **Editing a machine silently did nothing** — the unique-slug rule's `{id}`
  placeholder had no value, so every update failed validation against itself
- **Public site ignored the CMS** — `BaseController` now hydrates the shared
  `Config\Site` instance from the database, so every existing view renders
  edited content unchanged. The config arrays remain the fallback when the
  database is unreachable
- **`audit_log` writes always failed** — `audit()` wrote `user_email`, `detail`
  and `target_id`; the table has `entity`, `entity_id` and `summary`. Affected
  every admin module, silently
- **`img_tag('')` logged an error** — an item with no photo is a normal state

### Notes
- `details_json` (the full product-page content: specs, features, galleries) is
  preserved on save but is not yet editable through the form
- A fresh database still cannot be built with `spark migrate`: the original
  `CreateCmsSchema` and `AddLoginAttempts` migration files are missing from the
  repository while the `migrations` table records them as run

---

## [1.8.0] — 2026-10-16 — Phase 8: Hardening, Tooling & Docs

### Added
- **`php spark cms:status`** — colour-coded health table: PHP version, DB, pending migrations, writable permissions, `.htaccess` guard, `robots.txt` conflict, encryption key, SMTP, environment (`app/Commands/CmsStatus.php`)
- **`php spark cms:clear-cache`** — clears page cache, file cache, debugbar files; `--all` also clears sessions (`app/Commands/CmsClearCache.php`)
- **PHPUnit test suite** (`tests/`) covering:
  - `Feature/PublicRoutesTest` — all 25 public routes (home, 9 pages, 12 machines, 404, redirect)
  - `Feature/FormsTest` — honeypot bypass, CSRF rejection, validation failure, valid submission
  - `Unit/SiteHelperTest` — `img_url`, `img_tag`, `india_xy`, `timb_manifest`, `asset_url`
  - `Unit/SiteConfigTest` — products/solutions structure, slug uniqueness, productMenu integrity, hero slides, network bounds
- **`docs/CMS-SETUP.md`** — requirements, `.env` keys, migrations, seeders, Apache/Nginx config, permissions, backup cron
- **`docs/CMS-USER-GUIDE.md`** — plain-language guide, one section per module
- **`docs/CHANGELOG.md`** — this file
- PHP 8.1.x compatibility: `spark` and `public/index.php` minimum version lowered to `8.1`; `composer.json` pinned to `codeigniter4/framework 4.5.*`

### Changed
- `phpunit.dist.xml` — removed deprecated attributes (`failOnRisky`, `failOnWarning`, `beStrictAboutOutputDuringTests`); added SQLite `:memory:` test DB env vars
- `composer.json` — `php ^8.1`, `codeigniter4/framework 4.5.*`, `phpunit/phpunit ^10.5`

---

## [1.6.0] — 2026-10-14 — Phase 6: Forms & Inbox (Checkpoint 3)

### Added
- **`enquiries` table** — all contact-form submissions stored in DB with `ip`, `user_agent`, `status=new`
- **`career_applications` table** — all career applications stored; CVs saved to `writable/uploads/careers/<random-name>` with original filename preserved
- **`Forms.php`** updated — honeypot check → validate → DB insert + JSONL backup + email (silent on failure)
- **Admin → Inbox** — enquiries and applications in tabs; 25 per page; unread bold; search + date range
- Status workflow: New → Read (on open, enquiries) / Reviewing (on open, applications) → Closed
- Status + internal notes update; bulk status change; tick-all
- **CSV export** — UTF-8 BOM, formula-safe values, follows current search/filter/date
- **CV download** — authenticated; path-traversal check (`../../.env` rejected)
- Rate limiting — 5 submissions / 10 min / IP on all public forms
- **Editor role** — 403 on delete action; can read and download CVs
- `Config\Exceptions::$ignoreCodes` — added `403` so blocked-editor requests are not logged as CRITICAL

### Fixed
- JSONL backup files now created with `LOCK_EX` to prevent corruption under concurrent requests

---

## [1.5.0] — 2026-10-12 — Phase 5: Content Modules (Checkpoint 2)

### Added
- **Admin → Settings** — site name, tagline, email, LinkedIn, footer text; favicon generator
- **Admin → Mail** — SMTP config with encrypted password; test-email send
- **Admin → Home hub** — hero slides (image, copy, links); photo-reel order
- **Admin → Home lists** — stats, pillars, process, line stages, industries, network (7 lists)
- **Admin → Machines** — full CRUD: sections, repeaters, preview, slug→301 + link updates, duplicate, soft delete/restore, product/solution ordering, mega-menu ordering
- **Admin → News** — gallery ≤ 6 images, rich text, preview (closed/opened), bin
- **Admin → Pages** — copy, About/Infrastructure blocks, SEO, publish/preview, page copy
- **Admin → Lists hub** — projects, case studies, clients, FAQ, career areas, disclaimer, industries
- **Admin → SEO** — global defaults screen
- **Admin → Redirects** — manual + automatic 301s

### Changed
- Public controllers switched to read from `ContentRepository` with fallback to static arrays
- Public site HTML regression: 24 byte-identical + 1 DOM-identical routes (no visual change)

### Fixed
- `<script>` / `onerror` / `javascript:` stripped from rich-text fields by `HtmlSanitizer`
- Unpublished machine → 404 publicly; preview link with token still works

---

## [1.4.0] — 2026-10-10 — Phase 4: Media Library

### Added
- Multi-file upload (drag-and-drop, up to 20 at once)
- GD re-encode → WebP at 640 px and 1600 px
- SVG sanitiser — strips `onload`, `<script>`, `javascript:` href
- PDF MIME check
- Replace keeps same URL/key; "Used in" tracking
- Soft delete / restore; Bin screen
- Picker JSON API for inline image selection from content editors
- `img_url()` reads from `media` table (falls back to manifest path)
- `ImageProcessor::makeFavicons()` — generates `.ico` / `favicon-32.png` / `apple-touch-icon.png`

### Security
- PHP-in-JPEG and `x.php.jpg` uploads rejected (MIME + extension double-check)
- `writable/uploads/.htaccess` created automatically on first upload

---

## [1.3.0] — 2026-10-08 — Phase 3: Auth & Admin Shell

### Added
- Login / logout; forced password change on first login
- Idle timeout (30 min)
- 5-fail account lock + IP lock (15 min)
- `RoleGuard` filter — admin and editor roles
- Audit log table + admin screen
- Dashboard with system-status checks
- Users module (CRUD)
- Admin layout, CSS (no Tailwind — strict CSP), JS
- `SecurityHeaders` filter (CSP, X-Frame-Options, HSTS, Referrer-Policy, Permissions-Policy)
- CSRF enforced on all admin POST routes

---

## [1.2.0] — 2026-10-06 — Phase 2: Database Layer

### Added
- 8 migrations (all 19 tables); tested on MariaDB 10.11 and SQLite
- 26 models (CI4 Model subclasses; soft-delete, timestamps where appropriate)
- `ContentRepository` — cached read layer with fallback to static PHP arrays
- `LegacyContent` — wraps the static array files for the fallback path
- `ImportExistingContentSeeder` — idempotent; 2nd run adds 0 rows for all 19 tables
- Public controllers and views switched to `ContentRepository`

### Tested
- `php spark migrate` from empty DB; `migrate:rollback`; `migrate` again — pass on MariaDB 10.11 and SQLite
- HTML regression: 24 byte-identical + 1 DOM-identical (home `'` now `&#039;`); 0 differences with DB, 0 differences with DB stopped

---

## [1.1.0] — 2026-10-04 — Phase 1: Audit & Plan

### Added
- `docs/CONTENT-MAP.md` — maps every content field to DB table + column
- `docs/CMS-DECISIONS.md` — architecture decisions log (schema-driven forms, ContentRepository pattern, role model, media strategy)

---

## [1.0.0] — 2026-10-01 — Original Public Website

### Added
- CodeIgniter 4 public site launched (static-array content)
- 25 routes: home, 9 pages, 12 machine detail pages, contact, careers, redirect
- Contact and career forms with JSONL file backup
- Honeypot spam protection on all forms
- CSRF on all POST routes
- `ImageManifest.php` generated by `tools/optimize_images.py`
- `site_helper.php` — `img_url`, `img_tag`, `asset_url`, `india_xy`, `timb_manifest`
- Auto base URL detection for deploy-anywhere capability (`App::__construct`)
- Hero carousel, machines grid, India map with SVG pins, photo reel marquee
