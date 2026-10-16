# TIMB CMS — Build Status

**Last updated:** 2026-10-16  
**Stack:** CodeIgniter 4.5.8 · PHP 8.1.34 · MariaDB

---

## Phase Summary

| Phase | Description | Status |
|---|---|---|
| 1 | Audit & plan | ✅ Done |
| 2 | Database layer | ✅ Done |
| 3 | Auth & admin shell | ✅ Done |
| 4 | Media library | ✅ Done |
| 5 | Content modules | ✅ Done |
| 6 | Forms & inbox | ✅ Done |
| 7 | Hardening | ✅ Done |
| **8** | **Tooling, tests, docs** | ✅ **Done** |

---

## Phase 8 — Completed Items

### Spark Commands
| Command | File | Status |
|---|---|---|
| `php spark cms:status` | `app/Commands/CmsStatus.php` | ✅ Verified |
| `php spark cms:clear-cache` | `app/Commands/CmsClearCache.php` | ✅ Verified |

**`cms:status` output (this server):**
- PHP 8.1.34 ✅
- CI4 4.5.8 ✅
- Database: MySQLi connected ✅
- Writable dirs: all writable ✅
- Warnings: encryption key not set, SMTP not configured, uploads `.htaccess` missing, `public/robots.txt` exists (expected on dev)

### PHPUnit Test Suite
```
35 tests · 194 assertions · 0 failures · 0 errors
Runtime: PHP 8.1.34 · 0.125 s · 14 MB
```

| Suite | Tests | Result |
|---|---|---|
| `Feature/FormsTest` | 5 | ✅ All pass |
| `Feature/PublicRoutesTest` | 8 | ✅ All pass |
| `Unit/SiteConfigTest` | 12 | ✅ All pass |
| `Unit/SiteHelperTest` | 10 | ✅ All pass |

**Run with:**
```bash
php vendor/bin/phpunit --testdox
```

### Documentation
| File | Description |
|---|---|
| `docs/CMS-SETUP.md` | Requirements, .env keys, Apache/Nginx config, permissions, spark commands, backup cron, first-login steps |
| `docs/CMS-USER-GUIDE.md` | Plain-language guide, one section per module (Settings, Mail, Home, Machines, News, Pages, Lists, Media, SEO, Redirects, Inbox, Users) |
| `docs/CHANGELOG.md` | Full changelog from v1.0.0 → v1.8.0, Keep a Changelog format |

### PHP Version Downgrade
- Downgraded from PHP 8.2 → **PHP 8.1** (CI4 4.7.x → CI4 4.5.8)
- `spark` and `public/index.php` minimum version check updated to `8.1`
- `composer.json`: `php ^8.1`, `codeigniter4/framework 4.5.*`
- `app/Config/Kint.php`: added missing `$richSort`, `$richExpand`, `$aliases`, `$textAliases`, `$cliAliases` properties required by CI4 4.5 `Autoloader::configureKint()`
- Install: `composer install --no-security-blocking`

---

## Known Warnings (expected on dev, fix before production)

| Warning | Fix |
|---|---|
| Encryption key not set | `php spark key:generate` |
| `writable/uploads/.htaccess` missing | Create with `deny all` |
| `public/robots.txt` exists | `rm public/robots.txt` |
| SMTP not configured | Set `email.SMTPHost` etc. in `.env` |

---

## New/Changed Files — Phase 8

```
app/Commands/CmsStatus.php          ← new
app/Commands/CmsClearCache.php      ← new
app/Config/Kint.php                 ← changed (added 5 missing properties)
spark                               ← changed ($minPhpVersion 8.2 → 8.1)
public/index.php                    ← changed ($minPhpVersion 8.2 → 8.1)
composer.json                       ← changed (php ^8.1, framework 4.5.*)
phpunit.dist.xml                    ← changed (SQLite :memory: test DB)
tests/Feature/PublicRoutesTest.php  ← new
tests/Feature/FormsTest.php         ← new
tests/Unit/SiteHelperTest.php       ← new
tests/Unit/SiteConfigTest.php       ← new
docs/CMS-SETUP.md                   ← new
docs/CMS-USER-GUIDE.md              ← new
docs/CHANGELOG.md                   ← new
```

---

## Install Commands (fresh deploy)

```bash
composer install --no-security-blocking
php spark key:generate
php spark migrate
php spark db:seed ImportExistingContentSeeder
php spark db:seed AdminUserSeeder
rm public/robots.txt
php spark cms:status   # verify all OK
```

## First Login
1. Go to `/admin/login`  
2. Use credentials printed by `AdminUserSeeder`  
3. Change password on first login (forced)  
4. Settings → Favicon → upload logo PNG → Generate favicons  
5. Mail → configure SMTP → Send test email  
