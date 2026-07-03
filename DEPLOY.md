# Deployment guide — Camping Uzbekistan

A plain PHP + MySQL app. Works on standard shared hosting (Apache + mod_php),
a VPS (Apache or Nginx + PHP-FPM), or anything that serves PHP 8.

## 1. Requirements

- **PHP 8.0+** with extensions: `pdo_mysql`, `mbstring`, `fileinfo`, `curl`
  (and `intl` recommended — improves slug transliteration for non-Latin titles).
- **MySQL 8** or **MariaDB 10.4+**.
- Apache with `mod_rewrite`/`mod_headers` **or** Nginx.

## 2. Upload the files

Put the contents of `camping-uz/` so that **the web root points at this folder**
(it contains `index.php`, `admin/`, `assets/`, `uploads/`, `app/`, …).

- Served at a domain root (`https://example.com`) → leave `BASE_PATH` empty.
- Served in a subfolder (`https://example.com/site`) → set `BASE_PATH` to `/site`.

> Don't deploy `_ref_urbix/`, `.git`, or `node_modules` — they're reference/build only.

## 3. Database + config

1. Create a database and a user with rights on it.
2. Copy `app/config.sample.php` → `app/config.local.php` and fill in:
   ```php
   define('DB_HOST', '127.0.0.1');
   define('DB_NAME', 'camping_uz');
   define('DB_USER', '...');
   define('DB_PASS', '...');
   define('BASE_PATH', '');     // or '/subfolder'
   define('APP_DEBUG', false);  // IMPORTANT on production
   ```
3. Visit **`/admin/setup.php` once** — it creates the tables and seeds the
   default admin (`admin` / `password`), settings and the About page.
4. Sign in at `/admin/login.php` and **change the password** in *Settings*.
5. **Delete `admin/setup.php`** (or it stays admin-only locked).

## 4. PHP upload limits (posters / hero video)

Posters cap at 8 MB and hero video at 60 MB in the app, so raise PHP's limits:

**Apache + mod_php** — add to a `.htaccess` (or php.ini):
```
php_value upload_max_filesize 64M
php_value post_max_size 80M
php_value memory_limit 128M
```
**PHP-FPM / CGI** — create `.user.ini` in the web root:
```
upload_max_filesize = 64M
post_max_size = 80M
memory_limit = 128M
```
(or set them in the server's `php.ini`).

## 5. Apache

The app ships protective `.htaccess` files. Ensure the vhost allows them:
```apache
<Directory /var/www/camping-uz>
    AllowOverride All
    Require all granted
</Directory>
```
What they do:
- root `.htaccess` — no directory listing, security headers, blocks dotfiles/`.sql`.
- `app/.htaccess`, `db/.htaccess` — deny all web access (config, DB schema).
- `uploads/.htaccess` — serves files but **never executes PHP** there.

## 6. Nginx (if not using Apache)

`.htaccess` is ignored by Nginx — add the equivalents to your server block:
```nginx
root /var/www/camping-uz;
index index.php;

# Block internal dirs
location ~ ^/(app|db)/ { deny all; return 404; }

# Never execute PHP inside uploads
location ^~ /uploads/ {
    location ~ \.php$ { deny all; return 404; }
}

# Hide dotfiles and raw data
location ~ /\.            { deny all; }
location ~* \.(sql|ini|log|md|sh|bak)$ { deny all; }

# URL Rewriting
rewrite ^/tour/([^/]+)$ /tour.php?slug=$1 last;
rewrite ^/register/([^/]+)$ /register.php?tour=$1 last;
rewrite ^/admin/tour-edit/([0-9]+)$ /admin/tour-edit.php?id=$1 last;
rewrite ^/admin/guide-edit/([0-9]+)$ /admin/guide-edit.php?id=$1 last;
rewrite ^/admin/testimonial-edit/([0-9]+)$ /admin/testimonial-edit.php?id=$1 last;

location / { 
    try_files $uri $uri/ $uri.php$is_args$args =404; 
}
location ~ \.php$ {
    include fastcgi_params;
    fastcgi_pass unix:/run/php/php8.2-fpm.sock;
    fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
}
```

## 7. Permissions

- `uploads/` (and its subfolders) must be **writable by the web server**
  (e.g. `chown -R www-data uploads && chmod -R 775 uploads`).
- `app/config.local.php` should **not** be world-readable (`chmod 640`).

## 8. Go-live checklist

- [ ] `APP_DEBUG` is `false`
- [ ] Site served over **HTTPS** (the session cookie auto-flags `Secure`)
- [ ] Admin password changed; `admin/setup.php` deleted
- [ ] `uploads/` writable; a test poster uploads OK
- [ ] **Telegram**: token + chat id set, *Send test message* succeeds
- [ ] **Yandex Maps API key** set (route map + admin point picker)
- [ ] Real hero image/video + agency name/moto set in *Settings*
- [ ] Database backups scheduled

## 9. Moving the admin to its own domain (later)

The admin is isolated under `/admin` with its own session and assets. To split it
onto `admin.example.com`, point that host at the `admin/` folder, share the same
`app/` + database (or expose a small API), and set cookie/domain scope accordingly.
