# Camping Uzbekistan

Bilingual (EN/RU) travel-agency website + admin panel.

- **Public site:** PHP, reusing the *Gowilds* visual language.
- **Admin (`/admin`):** PHP, styled with the *Urbix* admin theme (Bootstrap 5.3 + Inter + Remix icons), compiled into `admin/assets`.
- **DB:** MySQL 8 / MariaDB.

## Local setup

1. **Create a database** (e.g. `camping_uz`) in MySQL/MariaDB.
2. **Configure credentials:** copy `app/config.sample.php` → `app/config.local.php` and fill in DB host/name/user/pass.
3. **Run the installer once:** start a server and open `/admin/setup.php`. It creates the tables and seeds the default admin, settings and About page.
   ```bash
   php -S localhost:8000   # from the camping-uz/ folder
   # then visit http://localhost:8000/admin/setup.php
   ```
4. **Sign in:** `/admin/login.php` with `admin` / `password` — change it in **Settings** (M1).
5. **Delete or block `admin/setup.php`** on production.

## Project layout

```
app/        config, DB, auth, csrf, i18n, settings, helpers   (web-blocked)
admin/      admin panel pages + partials + compiled assets
lang/       en.php, ru.php  (UI strings)
db/         schema.sql       (web-blocked)
uploads/    posters, avatars, guides, hero, media  (no PHP execution)
assets/     public-site assets (Gowilds) — M2
```

## Status

- **M0 (done):** foundation — schema, config/DB/auth/CSRF/i18n, Urbix admin shell (header, sidebar, login, dashboard), installer.
- **M1 (done):** admin CRUD — Settings (hero media, identity, socials, Telegram/Yandex keys, change login), Guides (+ one-by-one socials), Testimonials, About, Tours (poster, bilingual Quill, dates, Yandex route picker, guide multi-select), plus Registrations & Messages management views. Includes a safe upload helper, an XSS-sanitizing Quill HTML pipeline (with in-editor image upload + size/position), and reusable form widgets.
- **M2 (done):** public site reusing the Gowilds look — 100vh hero (image/video from settings), scroll-reactive transparent→white header with scaling wordmark, EN/RU switch, upcoming-tours carousel + full tours grid, About (rich text), testimonials carousel, working contact form (stores + honeypot), and the trip-details page (poster, dates, rich description, Yandex route map, guide avatars + centered modal). Shared partials in `partials/`, custom CSS/JS in `assets/css/site.css` + `assets/js/site.js`.
- **M3 (done):** public registration page `register.php` (solo + "add another person" group sign-ups, optional `?tour=` context) feeding the Registrations inbox grouped; "Register for this tour" CTA on trip pages; Telegram notifications (`app/telegram.php`) for new registrations + contact messages, with a **"Send test message"** button in Settings that surfaces the exact API error.
- **M4 (done):** security hardening (baseline security headers, `X-Powered-By` removed, `setup.php` locked after install, root `.htaccess` with no-listing + dotfile/`.sql` blocking + asset caching), a11y/robustness (aria-labels on public forms, visible focus styles, `prefers-reduced-motion` support, `<noscript>` fallback so JS-gated content still shows), Open Graph tags, and a full deployment guide → **[DEPLOY.md](DEPLOY.md)**.

**The build is feature-complete (M0–M4).** Remaining is yours: real content/photos, a working Telegram chat id, and deploying per DEPLOY.md.

### Telegram setup
Set the bot token + chat id in **Settings → Integrations**, then click **Send test message**. "chat not found" means the bot can't reach that chat — DM the bot first (personal id, positive number) or add the bot to your group/channel (id starts with `-100…`). Get the id from [@userinfobot](https://t.me/userinfobot).

## Rebuilding the admin theme CSS

The Urbix theme is compiled from SCSS into `admin/assets/css/{bootstrap,app}.min.css`.
Source + build live under `../_ref_urbix` (reference only, not deployed).
