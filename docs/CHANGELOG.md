# TIMB CMS — Changelog

All notable changes to the TIMB website and CMS are documented here.  
Format: [Keep a Changelog](https://keepachangelog.com/en/1.0.0/).  
Versioning: `<major>.<phase>.<patch>`.

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
