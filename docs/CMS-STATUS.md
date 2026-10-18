# TIMB CMS — Status

**Last updated:** 2026-10-19
**Stack:** CodeIgniter 4.5.8 · PHP 8.1 · MariaDB (SQLite for tests)

## At a glance

| Area | State |
|---|---|
| Public site | All pages served from the CMS, with file fallbacks if the database is unreachable |
| Admin | Dashboard, Machines, News, Pages, Lists Hub, Media Library, Inbox, SEO, Redirects, Settings, Users |
| Fresh install | `php spark migrate` + `php spark db:seed DatabaseSeeder` rebuilds the site — verified against the live database (identical tables and content) |
| Tests | 183 tests, ~2,000 assertions; admin screens tested against a real database built from the migrations |
| Security | See below |

## Security posture

Done:
- Content Security Policy and security headers on every response; HSTS on https
- Links are built from `app.baseURL`, never from an untrusted Host header
- https enforced and cookies marked secure when the site address is https
- Session renewed at sign-in; every admin request re-checks the account (deactivated or demoted users lose access at once); sign-out is a POST
- Sign-in limited per connection; the error never reveals which emails have accounts
- Public forms: honeypot, signed "form opened at" stamp, per-connection limits
- CMS-edited text that allows formatting is escaped, with only `<br>`, `<strong>`, `<b>`, `<em>` let through
- Job applications and CVs are admin-only; messages and CVs can be deleted, and old ones are removed by `cms:housekeeping`

Open:
- **Framework advisories.** `composer audit` reports advisories against CodeIgniter 4.5.8 that are fixed in 4.7.4+, which needs PHP 8.2. PHP 8.1 no longer receives security fixes. Upgrading PHP and the framework is the remaining item — see `docs/CMS-SETUP.md` §2b for what currently mitigates the affected features.

## Operations

| Task | How |
|---|---|
| Health check | `php spark cms:status` |
| Nightly backup + tidy-up | cron, see `docs/CMS-SETUP.md` §7 |
| Clear caches | `php spark cms:clear-cache` (the content cache also clears on every admin save) |

## Known limits

- Product pages are edited as JSON (Machines → Advanced) — powerful but technical
- Product-page photos cannot be moved between folders from the Media Library, because the built-in fallback copy in `app/Data` refers to them by name
- Content exists in three places by design: `Config\Site` and `app/Data` are the fallbacks used if the database cannot be read; the database is the source of truth
