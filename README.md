# TIMB website and CMS

The website for TI Machine Building, with a content management system (CMS) at
`/admin` for editing machines, news, pages, lists, photos and SEO, and for
reading and replying to enquiries and job applications.

Built on CodeIgniter 4.5 (PHP 8.1) with MariaDB. Plain CSS and JavaScript, no
build step.

## Run it locally

```bash
composer install
cp .env.example .env        # then set the database details; see the comments in the file
php spark migrate
php spark db:seed DatabaseSeeder   # loads the content and prints the first admin's password
php spark serve             # http://localhost:8080 — the admin is at /admin
```

For local use, set `CI_ENVIRONMENT = development` in `.env` and leave
`app.baseURL` out: the site then works on whatever address it is opened on.

## Where things are

| What | Where |
|---|---|
| Routes | `app/Config/Routes.php` |
| Public pages | `app/Controllers/{Home,Pages,Machines,Forms}.php`, views in `app/Views/` |
| Admin screens | `app/Controllers/Admin/`, views in `app/Views/admin/` |
| Content in the database | models in `app/Models/`; the Lists Hub is defined in `app/Config/Lists.php` |
| Fallback content (used if the database cannot be read) | `app/Config/Site.php`, `app/Data/` |
| Emails (alerts and replies) | `app/Libraries/Notifier.php`, templates in `app/Views/emails/` |
| Photos | `public/assets/img/machines/` (managed in Admin → Media Library) |
| Styles and scripts | `public/assets/` (site), `public/admin-assets/` (admin) |
| Database tables and starting content | `app/Database/Migrations/`, `app/Database/Seeds/` |

## Documentation

- [Setup guide](docs/CMS-SETUP.md) — installing on a server, `.env`, email, cron jobs
- [User guide](docs/CMS-USER-GUIDE.md) — using the CMS, screen by screen
- [Status](docs/CMS-STATUS.md) — what is done, the security position, what is open
- [Changelog](docs/CHANGELOG.md)

## Useful commands

```bash
php spark cms:status           # health check: database, email, site address, permissions
php spark cms:housekeeping     # nightly tidy-up of old messages, CVs and logs (--dry-run to preview)
php spark cms:clear-cache      # clear caches
vendor/bin/phpunit             # run the tests
```

Production's web root must be the `public/` folder. If a host only allows the
project folder itself, rename `htaccess-2` to `.htaccess` in the project root.
