# Project Setup & Rebuild Guide

This guide ensures a clean, repeatable setup for this CodeIgniter 4 project with admin template assets bundled under `public/assets`.

## Quick Start

1) Clone and install PHP deps

```
git clone <repo-url>
cd opd-profile
composer install
```

2) Environment config

Copy `env` to `.env` then update at least:

```
app.baseURL = 'http://localhost/opd-profile/public/'
app.forceGlobalSecureRequests = false   ; enable true only in HTTPS env

database.default.hostname = 127.0.0.1
database.default.database = opd_profile
database.default.username = root
database.default.password = ''
database.default.DBDriver = MySQLi
```

3) Database

```
php spark migrate --all
php spark db:seed CoreSeeder   ; optional if seeder available
```

4) Writable directories

Ensure the web server can write to `writable/cache`, `writable/logs`, and `writable/uploads`.

5) Run

```
php spark serve
```

Navigate to the URL in `app.baseURL`. Make sure your web server document root points to the `public/` folder.

## Assets & Theming

- All assets live under a single source: `public/assets`.
- Vendor bundles (admin template, libs) are under `public/assets/vendor/` and should not be modified.
- Put all customizations here:
  - `public/assets/css/custom.css` (admin overrides)
  - `public/assets/js/helpers-override.js` (JS overrides to keep vendor untouched)
  - `public/assets/css/public.css` (public site theme)

The admin sidebar collapse is provided by `helpers-override.js` and is loaded after vendor JS in the admin layout.

## Best Practices

- Do not edit files in `public/assets/vendor/`. Keep vendor clean for easy upgrades.
- Only add or edit code in `custom.css`, `helpers-override.js`, and your own JS/CSS files.
- Commit the `public/assets/vendor/` bundle so a fresh clone works without extra build steps.
- If you add new third-party libraries, place them under `public/assets/vendor/`.

## Common Issues

CSS/JS not loading (page looks like plain HTML)
- Most likely `app.baseURL` is incorrect. Set it to your actual site root ending with `/public/` when using PHP's built-in server, or to the host that points to `public/`.

HTTP redirects to HTTPS on local
- `app.forceGlobalSecureRequests` is enforced by a filter in production. Disable it in `.env` for local HTTP.

Uploads fail or cannot delete files
- Ensure `writable/uploads` exists and is writeable. News/Galleries/Documents store files under `public/uploads/...` and guard deletions to this folder only.

DataTables not loading
- Some tables use DataTables via CDN. Make sure internet access is available. If you need offline support, host the DataTables assets locally and update the views to use local files.

## Useful Commands

```
php spark cache:clear      # Clear framework caches
php spark migrate:status   # Check migration status
php spark routes           # Inspect routes
```

## Structure Reference

- Admin layout: `app/Views/layouts/admin.php`
- Public layout: `app/Views/layouts/public.php`
- Admin styles: `public/assets/css/custom.css`
- Public styles: `public/assets/css/public.css`
- Admin JS: `public/assets/js/main.js`
- JS override: `public/assets/js/helpers-override.js`
- Vendor libs: `public/assets/vendor/**`

## Kontak & Anti-Spam

- Jalankan migrasi terbaru untuk kolom `phone`, `ip_address`, `user_agent`, dan `responded_at`:
  ```bash
  php spark migrate --all
  ```
- Setelah migrasi, gunakan command berikut untuk membersihkan pesan lama:
  ```bash
  php spark contacts:purge 90
  php spark contacts:purge 60 --anonymize
  ```
- Atur variabel `CONTACT_*` di `.env` (lihat `docs/ENV_SETUP.md`) untuk blacklist, limit harian, dan notifikasi email/telegram.

## Menjalankan Pengujian

- PHPUnit membutuhkan ekstensi `sqlite3` aktif karena koneksi `tests` memakai database `:memory:`.
- Jalankan tes aplikasi:
  ```bash
  vendor/bin/phpunit --testsuite App
  ```
