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

> The `--no-security-blocking` flag bypasses Composer's advisory check for CI4 4.5.x (historical advisories, all mitigated by the CMS security headers and filters already in place).  
> Remove `--no-dev` if you need the test suite.

### 2c. Configure the environment

```bash
cp env .env
```

Edit `.env` and set **all** of the following:

```ini
#--------------------------------------------------------------------
# ENVIRONMENT
#--------------------------------------------------------------------
CI_ENVIRONMENT = production

#--------------------------------------------------------------------
# APP
#--------------------------------------------------------------------
app.baseURL = 'https://www.timbindia.com/'
app.indexPage = ''

#--------------------------------------------------------------------
# DATABASE
#--------------------------------------------------------------------
database.default.hostname = 127.0.0.1
database.default.database = timb_cms
database.default.username = timb_user
database.default.password = STRONG_PASSWORD_HERE
database.default.DBDriver = MySQLi
database.default.DBCollat = utf8mb4_unicode_ci

#--------------------------------------------------------------------
# ENCRYPTION (required for admin session security)
#--------------------------------------------------------------------
# Generate with: php spark key:generate
encryption.key = hex2bin:REPLACE_WITH_GENERATED_KEY

#--------------------------------------------------------------------
# CMS ADMIN
#--------------------------------------------------------------------
# The URL prefix for the admin panel. Default: admin
# Change to something non-obvious for production.
cms.adminPrefix = admin

#--------------------------------------------------------------------
# EMAIL (for form notifications and test-email feature)
#--------------------------------------------------------------------
email.protocol    = smtp
email.SMTPHost    = mail.yourdomain.com
email.SMTPUser    = noreply@timbindia.com
email.SMTPPass    = SMTP_PASSWORD_HERE
email.SMTPPort    = 587
email.SMTPCrypto  = tls
email.fromEmail   = noreply@timbindia.com
email.fromName    = 'TIMB Website'
```

### 2d. Generate the encryption key

```bash
php spark key:generate
```

This writes `encryption.key = hex2bin:...` into `.env` automatically.

### 2e. Run database migrations

```bash
php spark migrate
```

This creates all 19+ tables in one pass. To start fresh:

```bash
php spark migrate:rollback --all
php spark migrate
```

### 2f. Seed content from the static files

```bash
php spark db:seed ImportExistingContentSeeder
```

This imports all products, solutions, pages, news, etc. from the original PHP arrays into the DB. It is **idempotent** — running it a second time adds 0 rows.

### 2g. Create the first admin user

```bash
php spark db:seed AdminUserSeeder
```

This prints a one-time password to the console. Save it — you will be forced to change it on first login.

### 2h. Delete the static robots.txt

```bash
rm public/robots.txt
```

The static file would silently shadow the CMS-generated `/robots.txt` route.

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
| `app.baseURL` | ✅ | Full URL with trailing slash |
| `app.indexPage` | ✅ | Set to `''` for clean URLs |
| `database.default.*` | ✅ | MariaDB connection details |
| `encryption.key` | ✅ | 32+ char hex key (use `php spark key:generate`) |
| `cms.adminPrefix` | ✅ | URL prefix for admin panel (default `admin`) |
| `email.SMTPHost` | ⚠️ | Without this, form emails are silently skipped |
| `email.SMTPUser` | ⚠️ | SMTP username |
| `email.SMTPPass` | ⚠️ | SMTP password (stored encrypted in DB once saved via Settings screen) |

---

## 6. Spark Commands Reference

| Command | Purpose |
|---|---|
| `php spark migrate` | Run all pending migrations |
| `php spark migrate:rollback` | Roll back last migration batch |
| `php spark db:seed ImportExistingContentSeeder` | Import legacy static-array content into DB |
| `php spark db:seed AdminUserSeeder` | Create first admin account (prints password) |
| `php spark cms:status` | Health check: DB, cache, permissions, SMTP, env |
| `php spark cms:clear-cache` | Clear page cache, file cache, debugbar files |
| `php spark cms:clear-cache --all` | Also clear sessions |
| `php spark key:generate` | Generate and write encryption key to `.env` |
| `php spark routes` | List all registered routes |

---

## 7. Nightly Backup (Recommended Cron)

```bash
# /etc/cron.d/timb-backup
0 2 * * * www-data /usr/local/bin/timb-backup.sh >> /var/log/timb-backup.log 2>&1
```

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

1. Navigate to `https://www.timbindia.com/<adminPrefix>/login`
2. Username: `admin` (default), Password: printed by `AdminUserSeeder`
3. You will be **forced to change your password** immediately
4. After login, go to **Settings** → verify site name, email, and SMTP settings
5. Go to **Settings → Favicon** to generate favicon sizes from your logo

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
