# TIMB CMS — Setup Guide

> **Audience:** server administrators and developers setting up the TIMB CMS for the first time.  
> **Stack:** PHP 8.1+ · CodeIgniter 4.5 · MariaDB 10.6+ (or SQLite for local dev) · Apache 2.4 / Nginx

---

## 1. Requirements

| Requirement | Minimum | Recommended |
|---|---|---|
| PHP | 8.1.0 | 8.1.x latest |
| PHP extensions | `pdo`, `pdo_mysql` (or `pdo_sqlite`), `gd`, `mbstring`, `intl`, `curl`, `json`, `xml`, `zip` | all of the above |
| Composer | 2.x | 2.7+ |
| MariaDB | 10.6 | 10.11 LTS |
| Disk (writable/) | 500 MB | 2 GB |

> **Note:** PHP 8.1.8 is the minimum tested version for this build. CI4 4.5.x does not support PHP 7.x.

---

## 2. Installation

### 2a. Extract files

```bash
cd /var/www
unzip timb-cms-checkpoint3.zip -d dtimb
cd dtimb
```

### 2b. Install PHP dependencies

```bash
composer install --no-dev --no-security-blocking
```

> **Known security advisories.** This build runs CodeIgniter 4.5.8 on PHP 8.1, and
> `composer audit` reports advisories against it that are fixed only in
> CodeIgniter 4.7.4+, which needs **PHP 8.2**. `--no-security-blocking` lets the
> install proceed. Until that upgrade: the affected upload checks are backed by
> the site's own checks (files are re-encoded, stored outside the web root under
> random names, and the career form is rate-limited); `deleteBatch()` and the
> ImageMagick handler are not used. Plan the PHP 8.2 / CodeIgniter 4.7 upgrade —
> PHP 8.1 no longer receives security fixes.
>
> Remove `--no-dev` if you need the test suite.

### 2c. Configure the environment

```bash
cp .env.example .env
```

Edit `.env`. The template explains every value; the ones that matter most:

| Setting | Why |
|---|---|
| `app.baseURL = 'https://www.timbindia.com/'` | **Required.** Every link is built from it. With `https` the site also forces https, sends HSTS and marks cookies secure. If it is missing, links fall back to the request's host name, which a visitor controls — so set it. |
| `database.default.*` | A dedicated database user, never root. |
| `encryption.key` | Filled in by the next step. |
| `email.*` | Without it nobody is emailed when an enquiry arrives (it is still saved in the Inbox). |

### Email alerts (Gmail)

Every enquiry and job application is emailed (HTML, with the CV attached for
applications; *Reply* goes straight to the visitor). The `email.*` block in
`.env` is the only place it is configured — currently the test mailbox
`smtptest897@gmail.com`. **Before going live**, change that address in the three
places and the password, then use **Admin → Settings → Send a test email**.

With a Gmail account, `email.SMTPPass` must be an **App Password**, not the
normal password:

1. Sign in to the Gmail account → **Google Account → Security**.
2. Turn on **2-Step Verification** (App Passwords need it).
3. **2-Step Verification → App passwords** → create one named "TIMB website".
4. Copy the 16 characters into `email.SMTPPass = '…'` (spaces are fine).

A company mail server works the same way: set `email.SMTPHost`, `SMTPPort` and
`SMTPCrypto` to its values (`587` + `tls`, or `465` + `ssl`).
`email.recipients` can list several people, separated by commas.

### 2d. Generate the encryption key

```bash
php spark key:generate
```

### 2e. Create the database tables

```bash
php spark migrate
```

Builds every table (CreateCmsSchema and the migrations after it). Safe to run on
an existing database: tables that exist are left alone.

### 2f. Load the content and create the first admin

```bash
CMS_ADMIN_EMAIL=you@timbindia.com php spark db:seed DatabaseSeeder
```

- **Content** comes from `app/Database/Seeds/data/content.json`: machines,
  pages, news, photos, lists and settings. It only fills an empty database and
  refuses to run on one that already has machines.
- **Admin account** — the email above (default `admin@timbindia.com`) and a random
  password printed **once**. You must choose a new password at the first sign-in.
  Nothing is created if any account already exists.

Run the two parts on their own with `db:seed ImportExistingContentSeeder` and
`db:seed AdminUserSeeder`.

To refresh the content file from a live site, export the content tables (see the
list in the file) to JSON in the same shape — never include users, enquiries or
applications.

### 2g. Schedule the nightly jobs

See [7. Nightly jobs](#7-nightly-jobs-cron): the backup and `cms:housekeeping`.

### 2h. Check the install

```bash
php spark cms:status
```

Every row should say OK. `Site address` warns until `app.baseURL` is an https address.

---

## 3. Web Server Configuration

### Apache

Point `DocumentRoot` to the **`public/`** folder. The `public/.htaccess` handles URL rewriting.

```apache
<VirtualHost *:443>
    ServerName www.timbindia.com
    DocumentRoot /var/www/dtimb/public

    <Directory /var/www/dtimb/public>
        AllowOverride All
        Require all granted
    </Directory>

    # Deny script execution in uploads
    <Directory /var/www/dtimb/public/uploads>
        Options -ExecCGI
        AddHandler default-handler .php .php5 .phtml
        php_flag engine off
    </Directory>

    SSLEngine on
    SSLCertificateFile    /etc/ssl/certs/timbindia.pem
    SSLCertificateKeyFile /etc/ssl/private/timbindia.key
</VirtualHost>
```

### Nginx

```nginx
server {
    listen 443 ssl;
    server_name www.timbindia.com;
    root /var/www/dtimb/public;
    index index.php;

    # Deny PHP execution in uploads
    location ~* /uploads/.*\.php$ {
        deny all;
    }

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/run/php/php8.1-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    ssl_certificate     /etc/ssl/certs/timbindia.pem;
    ssl_certificate_key /etc/ssl/private/timbindia.key;
}
```

---

## 4. File & Folder Permissions

```bash
# Application needs write access to writable/
chmod -R 775 writable/
chown -R www-data:www-data writable/

# Uploads guard — deny direct script execution
cat > writable/uploads/.htaccess <<'EOF'
Options -Indexes -ExecCGI
AddHandler default-handler .php .php5 .phtml .pl .py .cgi
php_flag engine off
EOF
```

---

## 5. Environment Variables Reference

| Key | Required | Description |
|---|---|---|
| `CI_ENVIRONMENT` | ✅ | `production` hides errors from visitors |
| `app.baseURL` | ✅ | Full https URL with trailing slash. Links, redirects, HSTS and secure cookies all follow from it |
| `app.allowedHostnames.0` | — | Other host names allowed to build links, only when `app.baseURL` is unset |
| `app.indexPage` | — | Already `''` (clean URLs). Set `'index.php'` only on a server without URL rewriting |
| `database.default.*` | ✅ | MariaDB connection details |
| `encryption.key` | ✅ | 32+ char hex key (use `php spark key:generate`) |
| `email.SMTPHost` | ⚠️ | Mail server, e.g. `smtp.gmail.com`. Without it no alerts are sent (messages are still saved) |
| `email.SMTPUser` | ⚠️ | Mail account, e.g. `smtptest897@gmail.com` |
| `email.recipients` | — | Who receives the alerts, comma-separated; empty = the contact email in Settings |
| `email.fromEmail` | — | Address the alerts come from (Gmail: the account itself) |
| `email.SMTPPass` | ⚠️ | SMTP password — kept in `.env` only, never in the database |

---

## 6. Spark Commands Reference

| Command | Purpose |
|---|---|
| `php spark migrate` | Run all pending migrations |
| `php spark migrate:rollback` | Roll back last migration batch |
| `php spark db:seed DatabaseSeeder` | New install: load the content and create the first admin |
| `php spark db:seed ImportExistingContentSeeder` | Load the content into an empty database only |
| `php spark db:seed AdminUserSeeder` | Create the first admin account (prints the password once) |
| `php spark cms:housekeeping` | Delete inbox messages and CVs older than 24 months, orphaned CVs and logs older than 30 days (`--inbox-months`, `--log-days`, `--dry-run`) |
| `php spark cms:status` | Health check: DB, cache, permissions, SMTP, env |
| `php spark cms:clear-cache` | Clear page cache, file cache, debugbar files |
| `php spark cms:clear-cache --all` | Also clear sessions |
| `php spark key:generate` | Generate and write encryption key to `.env` |
| `php spark routes` | List all registered routes |

---

## 7. Nightly jobs (cron)

```bash
# /etc/cron.d/timb
# Backup first, then tidy up.
0 2 * * *  www-data /usr/local/bin/timb-backup.sh >> /var/log/timb-backup.log 2>&1
15 3 * * * www-data php /var/www/dtimb/spark cms:housekeeping >> /var/log/timb-housekeeping.log 2>&1
```

`cms:housekeeping` keeps personal data and logs from piling up: inbox messages
and their CVs go after 24 months (change with `--inbox-months`, or `0` to keep),
logs after 30 days. Try it first with `--dry-run`.

### Backup script

**`/usr/local/bin/timb-backup.sh`:**

```bash
#!/bin/bash
set -euo pipefail

PROJECT=/var/www/dtimb
BACKUP=/var/backups/timb
DATE=$(date +%Y-%m-%d)

mkdir -p "$BACKUP"

# 1. Database dump
mysqldump --single-transaction -u timb_user -p"$TIMB_DB_PASS" timb_cms \
  | gzip > "$BACKUP/db-$DATE.sql.gz"

# 2. Media library
tar -czf "$BACKUP/public-uploads-$DATE.tar.gz" "$PROJECT/public/uploads" 2>/dev/null || true

# 3. CV files
tar -czf "$BACKUP/career-cvs-$DATE.tar.gz" "$PROJECT/writable/uploads/careers" 2>/dev/null || true

# 4. Keep last 30 days
find "$BACKUP" -name "*.gz" -mtime +30 -delete
```

---

## 8. First Login

1. Go to `https://www.timbindia.com/admin/login`
2. Sign in with the email and password printed by `db:seed DatabaseSeeder`
3. You will be **asked to choose a new password** straight away
4. Go to **Settings** and check the site name, contact email and links
5. Add the other people who need access in **Users** — *editor* for content only, *admin* for everything including job applications and CVs

---

## 9. Verify the Install

```bash
php spark cms:status
```

All rows should show **OK** (green). Fix any **FAIL** or **WARN** before going live.

---

## 10. Upgrading

When new migrations are added in a future checkpoint:

```bash
git pull  # or unzip new checkpoint over the project
composer install --no-dev --no-security-blocking
php spark migrate
php spark cms:clear-cache
```

No `db:seed` needed — migrations handle schema changes; seeders only run once for initial data.
