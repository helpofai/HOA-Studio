# 🚀 HelpOfAi Studio — Production Deployment & Operations Guide

This guide provides end-to-end instructions for deploying **HelpOfAi Studio** to **Shared Hosting (cPanel / DirectAdmin / LiteSpeed)**, **Cloud VPS (Ubuntu / Nginx / Apache)**, and **Enterprise Server Environments**.

---

## 📑 Table of Contents
1. [Server Requirements & Prerequisites](#1-server-requirements--prerequisites)
2. [Shared Hosting & cPanel Deployment](#2-shared-hosting--cpanel-deployment)
3. [VPS & Dedicated Cloud Deployment (Nginx + PHP-FPM)](#3-vps--dedicated-cloud-deployment-nginx--php-fpm)
4. [Production Environment Settings (`.env`)](#4-production-environment-settings-env)
5. [Performance Optimization & Caching Commands](#5-performance-optimization--caching-commands)
6. [Automated Production Diagnostic Tool](#6-automated-production-diagnostic-tool)
7. [Scheduled Cron Jobs & Background Workers](#7-scheduled-cron-jobs--background-workers)
8. [SSL / HTTPS & Security Hardening](#8-ssl--https--security-hardening)
9. [Troubleshooting & FAQ](#9-troubleshooting--faq)

---

## 1. Server Requirements & Prerequisites

| Component | Minimum Requirement | Recommended Production |
| :--- | :--- | :--- |
| **PHP Version** | PHP 8.2+ | **PHP 8.5+** |
| **Database** | MySQL 8.0+ / MariaDB 10.5+ | MySQL 8.4+ / PostgreSQL 16+ |
| **Web Server** | Apache 2.4+ / LiteSpeed / Nginx | Nginx 1.26+ or LiteSpeed Web Server |
| **Memory (RAM)** | 1 GB | 2 GB - 4 GB |
| **PHP Extensions** | `curl`, `mbstring`, `openssl`, `pdo_mysql`, `tokenizer`, `xml`, `ctype`, `fileinfo`, `zip`, `intl` | All enabled with `opcache.enable=1` |

---

## 2. Shared Hosting & cPanel Deployment

HelpOfAi Studio comes pre-configured with **Root & Public `.htaccess`** rules, making shared hosting deployment completely seamless even if your document root cannot be changed from `public_html`.

### Step 1: Upload Files
1. Compress your project into a `.zip` archive (exclude `/vendor`, `/node_modules`, and `.git`).
2. Upload and extract the archive into your cPanel `public_html/` or home folder.
3. Ensure directory structure:
   ```
   public_html/
   ├── .htaccess             # Root protection & public forwarder
   ├── app/
   ├── bootstrap/
   ├── config/
   ├── database/
   ├── public/
   │   ├── .htaccess         # Compression & asset caching
   │   ├── index.php
   │   └── build/            # Compiled Vite assets
   ├── resources/
   ├── routes/
   ├── storage/
   └── vendor/
   ```

### Step 2: Configure Database
1. Create a MySQL Database and User in cPanel **MySQL Databases**.
2. Assign the user full permissions.
3. Update `.env` with database credentials.

### Step 3: Run Migrations & Dependencies via SSH or Terminal
```bash
composer install --no-dev --optimize-autoloader
php artisan migrate --force --seed
php artisan storage:link
```

---

---

## 3. VPS & Dedicated Cloud Deployment (Nginx + PHP-FPM)

### Recommended Nginx Configuration (`/etc/nginx/sites-available/hoa-studio.conf`):

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name studio.helpofai.com;
    return 301 https://$host$request_uri;
}

server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name studio.helpofai.com;

    root /var/www/hoa-studio/public;
    index index.php index.html;

    # SSL Certificates (Let's Encrypt / Certbot)
    ssl_certificate /etc/letsencrypt/live/studio.helpofai.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/studio.helpofai.com/privkey.pem;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers HIGH:!aNULL:!MD5;

    # Security Headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header Referrer-Policy "strict-origin-when-cross-origin" always;

    # Gzip Compression
    gzip on;
    gzip_types text/plain text/css application/json application/javascript text/xml application/xml application/xml+rss text/javascript font/woff2 font/woff;

    # Static Assets Caching
    location ~* \.(jpg|jpeg|png|gif|ico|css|js|woff2|woff|ttf|svg)$ {
        expires 365d;
        add_header Cache-Control "public, no-transform";
        access_log off;
    }

    # Pass all requests to Laravel Front Controller
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    # PHP-FPM FastCGI Handler
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.5-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_buffers 16 16k;
        fastcgi_buffer_size 32k;
        fastcgi_read_timeout 300;
    }

    # Real-Time SSE AI Streaming (Disable Buffering)
    location ~ ^/api/v1/wordpress/stream {
        fastcgi_pass unix:/var/run/php/php8.5-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root/index.php;
        include fastcgi_params;
        fastcgi_buffering off;
        proxy_buffering off;
        gzip off;
    }

    # Deny access to hidden files and system configs
    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

---

## 4. 📂 Sub-Directory Domain Setup (`helpofai.com/studio`)

HelpOfAi Studio natively supports running inside subdirectories on **Linux**, **Apache**, **Nginx**, and **LiteSpeed / OpenLiteSpeed / cPanel**.

### A. Environment Configuration (`.env`):
Set the app URL to your subdirectory. **Important:** Leave `ASSET_URL` empty unless you are using a dedicated CDN, as setting it to the base URL can break Livewire assets in subdirectories.

```env
APP_URL=https://helpofai.com/studio
ASSET_URL=
SESSION_PATH=/studio
```

### B. Apache & LiteSpeed / cPanel Subdirectory Setup:
Create a subdirectory folder named `studio` inside `public_html/` (e.g. `public_html/studio/`), upload HOA Studio files, and make sure both root and public `.htaccess` are present. 
The dynamic `.htaccess` rules automatically route `/studio/*` requests to `/studio/public/index.php`.

For LiteSpeed servers, buffer flushing is enabled out-of-the-box in `public/.htaccess` via `CacheLookup off`.

### C. Nginx Subdirectory Block (`/etc/nginx/sites-available/helpofai.com`):
When hosting WordPress on the root domain (`helpofai.com`) and HOA-Studio in `/studio`:
```nginx
server {
    server_name helpofai.com;
    root /var/www/helpofai.com/public_html;

    # Primary Root Site (e.g. WordPress)
    location / {
        try_files $uri $uri/ /index.php?$args;
    }

    # HOA Studio Subdirectory Location Block
    location ^~ /studio {
        alias /var/www/hoa-studio/public;
        try_files $uri $uri/ @studio;

        location ~ \.php$ {
            include fastcgi_params;
            fastcgi_param SCRIPT_FILENAME /var/www/hoa-studio/public/index.php;
            fastcgi_pass unix:/var/run/php/php8.5-fpm.sock;
            fastcgi_read_timeout 300;
        }
    }

    location @studio {
        rewrite /studio/(.*)$ /studio/index.php?/$1 last;
    }
}
```

---

## 5. Production Environment Settings (`.env`)

Ensure the following critical values are configured for production:

```ini
APP_NAME="HelpOfAi Studio"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://studio.helpofai.com

# Database Connection
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=hoa_studio_prod
DB_USERNAME=hoa_db_user
DB_PASSWORD=YOUR_STRONG_DATABASE_PASSWORD

# Drivers
CACHE_STORE=file
SESSION_DRIVER=file
QUEUE_CONNECTION=database

# OmniRoute AI Gateway
OMNIROUTE_BASE_URL=http://127.0.0.1:20128
OMNIROUTE_API_KEY=YOUR_OMNIRoute_PRODUCTION_KEY
OMNIROUTE_DEFAULT_MODEL=deepseek/deepseek-chat
OMNIROUTE_FALLBACK_MODEL=glm/glm-4-flash
```

---

## 5. Performance Optimization & Caching Commands

Run these commands after every production release:

```bash
# 1. Clear existing caches
php artisan optimize:clear

# 2. Cache configuration files
php artisan config:cache

# 3. Cache application routes
php artisan route:cache

# 4. Compile and cache Blade views
php artisan view:cache

# 5. Cache application event listeners
php artisan event:cache

# 6. Verify production build assets
npm run build
```

---

## 6. Automated Production Diagnostic Tool

HelpOfAi Studio includes a built-in production validation command. Run it on your live server to verify all operational parameters:

```bash
php artisan hoa:verify-production
```

### Expected Output:
```text
===========================================================
 HelpOfAi Studio — Production Readiness Diagnostics 
===========================================================

1. PHP Environment & Extensions:
 • PHP Version: 8.5.0
 ✓ Extension 'curl' is enabled.
 ✓ Extension 'mbstring' is enabled.
 ✓ Extension 'openssl' is enabled.
 ✓ Extension 'pdo' is enabled.
 ✓ Extension 'tokenizer' is enabled.
 ✓ Extension 'xml' is enabled.
 ✓ Extension 'ctype' is enabled.
 ✓ Extension 'fileinfo' is enabled.

2. Database Connectivity & Migrations:
 ✓ Database connection established successfully.
 ✓ Database seeded with AI providers and models.

3. Directory Permissions & Writable Paths:
 ✓ Directory writable: storage/framework/views
 ✓ Directory writable: storage/framework/cache
 ✓ Directory writable: storage/framework/sessions
 ✓ Directory writable: storage/logs
 ✓ Directory writable: bootstrap/cache

4. Production Frontend Build Assets:
 ✓ Vite build manifest found: public/build/manifest.json

5. Shared Hosting .htaccess Redirection Rules:
 ✓ Root .htaccess redirection configured.
 ✓ Public .htaccess compression and routing configured.

===========================================================
 SUCCESS: HelpOfAi Studio is 100% PRODUCTION READY! 🚀
===========================================================
```

---

## 7. Scheduled Cron Jobs & Background Workers

### Cron Job Setup
Add this single entry to your server crontab (`crontab -e` or cPanel **Cron Jobs**):

```bash
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

### Supervisor Queue Worker (`/etc/supervisor/conf.d/hoa-worker.conf`):
```ini
[program:hoa-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/hoa-studio/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/hoa-studio/storage/logs/worker.log
```

---

## 8. SSL / HTTPS & Security Hardening

1. **Free Let's Encrypt SSL via Certbot**:
   ```bash
   sudo certbot --nginx -d studio.helpofai.com
   ```
2. **Directory Permissions**:
   ```bash
   sudo chown -R www-data:www-data /var/www/hoa-studio
   sudo find /var/www/hoa-studio -type f -exec chmod 644 {} \;
   sudo find /var/www/hoa-studio -type d -exec chmod 755 {} \;
   sudo chmod -R 775 /var/www/hoa-studio/storage /var/www/hoa-studio/bootstrap/cache
   ```

---

## 9. Troubleshooting & FAQ

### Q: Why do I see a 500 error on shared hosting?
**A**: Ensure directory permissions on `storage/` and `bootstrap/cache/` are writable (`chmod -R 775`). Also verify that PHP extension `fileinfo` and `pdo_mysql` are enabled in your cPanel PHP selector.

### Q: How do I test the AI Gateway connection?
**A**: Log in to the Admin Control Panel at `/admin/ai-settings` and click the **"⚡ Ping"** button next to any model to test real-time latency and connectivity.