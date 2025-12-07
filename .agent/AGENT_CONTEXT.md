# 🤖 Agent Context - OPD Profile Website

**Last Updated:** 2025-12-07
**Project:** Website Profil OPD (Organisasi Perangkat Daerah)

---

## 📋 Project Overview

Website profil resmi untuk instansi pemerintah daerah (OPD) dengan fitur:
- **Public Site**: Beranda, Berita, Layanan, Galeri, Dokumen, Kontak, Struktur Organisasi
- **Admin Panel**: Kelola konten, berita, layanan, galeri, dokumen, pengguna

---

## 🛠️ Tech Stack

| Component | Technology |
|-----------|------------|
| Framework | CodeIgniter 4 |
| PHP Version | 8.x |
| Database | MySQL |
| CSS | Vanilla CSS (tokens.css, layout.css, components.css, pages.css) |
| JS | Vanilla JS (public-min.js) |
| Icons | Boxicons (CDN) |
| UI Framework | Bootstrap 5.3 |
| Server | XAMPP (Development) |

---

## 📁 Key Directory Structure

```
opd-profile/
├── app/
│   ├── Controllers/
│   │   ├── Admin/           # Admin controllers
│   │   ├── Home.php         # Public home
│   │   └── Pages.php        # Public pages
│   ├── Views/
│   │   ├── admin/           # Admin views
│   │   ├── public/          # Public views
│   │   │   ├── home.php
│   │   │   ├── news/
│   │   │   ├── services.php
│   │   │   ├── gallery.php
│   │   │   ├── contact.php
│   │   │   ├── documents.php
│   │   │   └── organization/
│   │   └── layouts/
│   │       ├── public.php   # Main public layout
│   │       └── admin.php    # Admin layout (Sneat template)
│   ├── Models/
│   ├── Services/
│   │   ├── NewsMediaService.php
│   │   ├── PublicContentService.php
│   │   └── ProfileLogoService.php
│   └── Helpers/
│       ├── image_helper.php # Responsive images helper
│       ├── news_helper.php
│       └── activity_helper.php
├── public/
│   ├── assets/
│   │   ├── css/
│   │   │   └── public/      # Public CSS files
│   │   ├── js/
│   │   │   └── public-min.js
│   │   └── vendor/
│   │       └── swiper/
│   └── uploads/             # User uploads
│       ├── news/
│       ├── galleries/
│       ├── services/
│       └── profile/
└── .agent/
    ├── AGENT_CONTEXT.md     # This file
    └── PUBLIC_PERFORMANCE_AUDIT.md
```

---

## ✅ Completed Tasks (Performance Optimization)

### Quick Wins
- [x] **Hapus duplikasi CSS** di `tokens.css` (-3.8KB)
- [x] **Width/Height attributes** pada semua `<img>` (CLS prevention)
- [x] **Preload Boxicons CSS** (non-blocking)
- [x] **Fetchpriority** untuk LCP images
- [x] **CSS Bundle** via `bundle.css` (-5 HTTP requests)

### Major Features
- [x] **Responsive Images (srcset)** - Full implementation:
  - `image_helper.php` with `responsive_image()`, `responsive_srcset()`, `generate_image_variants()`, `delete_image_variants()`
  - Backend variant generation (400w, 800w, 1200w) in:
    - `NewsMediaService.php`
    - `Galleries.php` controller
    - `Services.php` controller
  - Frontend srcset in all public views
- [x] **Self-hosted Fonts** - Full implementation:
  - **Public Sans** (Google Fonts) self-hosted in `assets/vendor/fonts/public-sans/`
  - **Boxicons Subset** (~83 icons) in `assets/vendor/fonts/boxicons/`
  - Benefits: -90KB CSS, -2 DNS lookups, no external dependencies

### Bug Fixes
- [x] **Admin News Form** - Fixed `$val` undefined error in `form.php`

---

## 🎯 Remaining Tasks (Optional)

| Task | Priority | Effort | Impact |
|------|----------|--------|--------|
| WebP conversion pipeline | Low | 8+ jam | -25-35% image size |
| Code splitting JS | Low | 4+ jam | Smaller initial bundle |

**Not doing**: Lazy load Google Maps (user needs it always visible)

---

## 🔧 Code Conventions

### PHP
- PSR-4 autoloading
- Type hints where possible
- Helper functions loaded via `helper('name')`
- Services in `app/Services/`

### CSS
- BEM-like naming: `.component`, `.component__element`, `.component--modifier`
- Design tokens in `tokens.css`
- Page-specific styles in `pages.css`

### JavaScript
- Vanilla JS, no jQuery
- `defer` attribute on non-critical scripts
- Functions prefixed with `init` for initialization

### Images
- Path format: `uploads/category/filename.ext`
- Variants: `filename-400.ext`, `filename-800.ext`, `filename-1200.ext`
- Use `responsive_srcset()` helper in views
- Backend uses `generate_image_variants()` on upload

---

## 📝 Important Notes

### Data Flow for Images
1. **Controller** (`Home.php`) calls `resolveMediaUrl()` which wraps path with `base_url()`
2. **View** receives full URL in `$slide['thumbnail']`, `$featuredNews['thumbnail']`, etc.
3. **Helper** `responsive_srcset()` can handle both path and full URL (auto-detects)

### Admin Panel
- Uses **Sneat Bootstrap Template** (different from public site)
- Admin routes: `/admin/*`
- Authentication via sessions

### Public Site
- Custom CSS design system (not Sneat)
- Hero slider uses Swiper
- Search overlay with AJAX

---

## 🚨 Known Issues / Gotchas

1. **Image paths**: Some controllers return full URL, some return relative path. `responsive_srcset()` handles both.
2. **FCPATH**: Points to `public/` folder. Uploads are in `public/uploads/`.
3. **Windows paths**: Helper normalizes `\` to `/` for URLs.

---

## 📚 Reference Files

| Purpose | File |
|---------|------|
| Performance Audit | `.agent/PUBLIC_PERFORMANCE_AUDIT.md` |
| Public Layout | `app/Views/layouts/public.php` |
| Image Helper | `app/Helpers/image_helper.php` |
| Home Controller | `app/Controllers/Home.php` |
| News Service | `app/Services/NewsMediaService.php` |

---

## 💡 Tips for AI Agent

1. **Before editing views**: Check if data comes as URL or path (see `resolveMediaUrl()` in controller)
2. **For image uploads**: Use `generate_image_variants()` after saving to create responsive sizes
3. **CSS changes**: Edit specific file in `public/assets/css/public/`, not the bundle
4. **Syntax check**: Run `php -l filename.php` after PHP edits
5. **Git commits**: Group related changes, use descriptive messages

---

*This file helps new AI sessions understand the project context and continue work seamlessly.*
