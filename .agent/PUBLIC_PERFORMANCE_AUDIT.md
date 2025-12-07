# 📊 Laporan Audit Performa Bagian Publik
**Tanggal Audit:** 2025-12-07
**Versi:** 1.0

---

## 📁 1. STRUKTUR FILE CSS

### 1.1 Daftar File CSS Publik

| No | File | Ukuran | Keterangan |
|----|------|--------|------------|
| 1 | `tokens.css` | ~~15,103~~ → **11,282 bytes** | Design tokens & utilities ✅ OPTIMIZED |
| 2 | `layout.css` | 22,221 bytes | Layout utama |
| 3 | `components.css` | 11,373 bytes | Komponen UI |
| 4 | `pages.css` | 12,269 bytes | Halaman spesifik |
| 5 | `navbar-dropdown.css` | 2,729 bytes | Dropdown navbar |
| 6 | `global-search.css` | 6,810 bytes | Search overlay |
| 7 | `hero-slider.css` | 1,626 bytes | Hero slider |
| 8 | `home-enhancements.css` | 8,533 bytes | Home page (conditional) |
| 9 | `home-news.css` | 4,480 bytes | News section home (conditional) |
| 10 | `home-app-links.css` | 3,871 bytes | App links (conditional) |
| 11 | `news-list.css` | 5,334 bytes | Halaman berita (conditional) |
| 12 | `gallery-lightbox.css` | 5,081 bytes | Lightbox galeri (conditional) |
| 13 | `hide-icons.css` | 374 bytes | Hide icons utility |

**Total CSS yang dimuat di semua halaman:** ~72,905 bytes (layout, tokens, components, pages, navbar-dropdown, global-search, hero-slider, hide-icons)

**CSS Kondisional (per halaman):**
- Home: +16,884 bytes (home-enhancements, home-news, home-app-links)
- Berita: +5,334 bytes (news-list)
- Galeri: +5,081 bytes (gallery-lightbox)

---

## 🎨 2. ANALISIS CSS CLASSES

### 2.1 Classes di `tokens.css` yang DUPLIKAT

| Class | Line Pertama | Line Duplikat | Status |
|-------|--------------|---------------|--------|
| `.section-head` | 302-319 | 500-517 | ⚠️ DUPLIKAT |
| `.section-head h2` | 310-314 | 508-512 | ⚠️ DUPLIKAT |
| `.section-head p` | 316-319 | 514-517 | ⚠️ DUPLIKAT |
| `.section-cta` | 321-324 | 519-522 | ⚠️ DUPLIKAT |
| `.empty-state` | 326-333 | 524-531 | ⚠️ DUPLIKAT |
| `.surface-card` | 335-344 | 533-542 | ⚠️ DUPLIKAT |
| `.surface-card h3` | 346-350 | 544-548 | ⚠️ DUPLIKAT |
| `.surface-link` | 352-364 | 550-562 | ⚠️ DUPLIKAT |
| `.minimal-grid` | 366-369 | 564-567 | ⚠️ DUPLIKAT |
| `.minimal-grid-4` | 371-373 | 569-571 | ⚠️ DUPLIKAT |
| `.minimal-grid-2` | 375-377 | 573-575 | ⚠️ DUPLIKAT |
| `.mono-badge` | 379-390 | 577-588 | ⚠️ DUPLIKAT |
| `.service-minimal` | 392-404 | 590-602 | ⚠️ DUPLIKAT |
| `.gallery-card` | 406-416 | 604-614 | ⚠️ DUPLIKAT |
| `.document-table` | 418-427 | 616-625 | ⚠️ DUPLIKAT |
| `.public-footer` | 429-486 | 627-684 | ⚠️ DUPLIKAT |
| `.news-card img` | 296-299 | 494-497 | ⚠️ DUPLIKAT |

**Estimasi bytes yang bisa dihemat:** ~5,000 bytes (setelah hapus duplikasi)

✅ **STATUS: SELESAI** - Semua duplikasi sudah dihapus pada 2025-12-07. Penghematan aktual: **~3,821 bytes (25%)**

### 2.2 Classes yang DIGUNAKAN di Views

#### Layout & Container
- `.public-section` - Digunakan di semua halaman publik
- `.public-container`, `.container` - Wrapper container
- `.public-home` - Home page wrapper
- `.public-body` - Body class

#### Hero Section
- `.hero-section`, `.hero-shell`, `.hero-soft` - Hero wrapper
- `.hero-slider`, `.hero-slides`, `.hero-slide` - Slider
- `.hero-slide-cover`, `.hero-cover-media`, `.hero-cover-overlay` - Cover style
- `.hero-cover-copy`, `.hero-cover-title`, `.hero-cover-actions` - Copy
- `.hero-eyebrow`, `.hero-eyebrow-light` - Eyebrow badge
- `.hero-placeholder` - Placeholder
- `.hero-fallback-wrap`, `.hero-grid`, `.hero-copy` - Fallback
- `.hero-title`, `.hero-lead`, `.hero-actions`, `.hero-link` - Copy

#### Section Headers
- `.section-head` - Section header wrapper
- `.section-title` - H2 title
- `.section-lead` - Paragraph lead
- `.section-cta` - CTA wrapper

#### Section Variants
- `.section-warm` - Warm background
- `.section-cool` - Cool background
- `.section-neutral` - Neutral background

#### Quick Actions
- `.quick-actions` - Grid wrapper
- `.quick-action` - Individual action
- `.quick-action__icon` - Icon
- `.quick-action__label` - Label

#### Welcome/Sambutan
- `.welcome-section` - Wrapper
- `.welcome-photo`, `.welcome-photo--placeholder` - Photo
- `.welcome-content` - Content
- `.welcome-greeting` - Text
- `.welcome-author`, `.welcome-author__name`, `.welcome-author__title` - Author

#### Services
- `.service-card`, `.surface-card` - Card wrapper
- `.service-card__icon`, `.service-card__body` - Parts
- `.service-card__title`, `.service-card__desc` - Text
- `.service-minimal` - Minimal style
- `.service-icon` - Icon badge
- `.mono-badge` - Monogram badge

#### News
- `.news-home-grid` - Home news grid
- `.news-home-featured`, `.news-home-featured__media`, `.news-home-featured__body` - Featured
- `.news-home-list`, `.news-home-item` - List items
- `.news-home-item__media`, `.news-home-item__body` - Item parts
- `.news-badge-category`, `.news-date`, `.news-excerpt` - Meta
- `.news-placeholder` - Placeholder
- `.news-card` - Card style
- `.news-list-item`, `.news-list-item__thumb`, `.news-list-item__thumb-placeholder` - List
- `.news-list-item__body`, `.news-list-item__title`, `.news-list-item__meta` - Body

#### App Links
- `.app-links-slider`, `.app-links-track` - Slider
- `.app-links-static` - Static grid
- `.app-link-item` - Item
- `.app-link-logo`, `.app-link-logo-placeholder` - Logo
- `.app-link-name` - Name

#### Contact
- `.contact-grid` - Grid
- `.contact-info-list`, `.contact-info-item` - List
- `.contact-info-item__icon`, `.contact-info-item__content` - Parts
- `.contact-info-item__label`, `.contact-info-item__value` - Text
- `.contact-map`, `.contact-map-placeholder` - Map

#### Cards & Surfaces
- `.surface-card`, `.card-base` - Base card
- `.profile-card` - Profile specific
- `.stat-card`, `.gallery-card`, `.contact-card`, `.documents-card` - Types

#### Footer
- `.public-footer` - Footer wrapper
- `.footer-heading`, `.footer-description` - Text
- `.footer-links`, `.footer-link` - Links
- `.footer-contact-list`, `.footer-contact-label` - Contact
- `.footer-map`, `.footer-map-placeholder`, `.footer-map-link` - Map
- `.footer-bottom` - Bottom section

#### Navbar
- `.public-navbar`, `.public-navbar-top`, `.public-navbar-bottom` - Structure
- `.public-navbar-brand`, `.public-navbar-brand--compact` - Brand
- `.public-navbar-brand-copy`, `.public-navbar-brand-name` - Copy
- `.public-navbar-brand-name-main`, `.public-navbar-brand-name-region` - Name parts
- `.public-navbar-brand-tagline` - Tagline
- `.public-navbar-links`, `.public-navbar-meta` - Links & meta
- `.public-search-trigger` - Search button
- `.navbar-brand-logo` - Logo image

#### Search Overlay
- `.search-overlay`, `.search-overlay__backdrop` - Overlay
- `.search-overlay__container`, `.search-overlay__header` - Structure
- `.search-overlay__body`, `.search-overlay__form` - Body
- `.search-overlay__input-wrap`, `.search-overlay__input` - Input
- `.search-overlay__input-icon`, `.search-overlay__hint` - Input parts
- `.search-overlay__results`, `.search-overlay__empty` - Results
- `.search-overlay__close` - Close button
- `.search-overlay__group`, `.search-overlay__group-title` - Groups
- `.search-overlay__item`, `.search-overlay__item-icon` - Items
- `.search-overlay__item-content`, `.search-overlay__item-title`, `.search-overlay__item-snippet` - Item parts
- `.search-overlay__loading`, `.search-overlay__no-results` - States

#### Gallery
- `.gallery-grid`, `.gallery-card` - Grid & card
- `.gallery-card__image`, `.gallery-card__overlay` - Parts

#### Documents
- `.document-table` - Table style

#### Utilities
- `.prose`, `.rich-content` - Rich text
- `.empty-state` - Empty state
- `.back-to-top` - Back to top button
- `.skip-link` - Skip link accessibility

---

## 🔤 3. BOXICONS YANG DIGUNAKAN

### 3.1 Regular Icons (bx-)

| Icon | Penggunaan |
|------|------------|
| `bx-home-alt` | Breadcrumb beranda |
| `bx-briefcase` | Layanan default |
| `bx-briefcase-alt-2` | Empty state layanan |
| `bx-message-square-detail` | Sambutan, Form pengaduan |
| `bx-target-lock` | Visi misi |
| `bx-sitemap` | Struktur organisasi |
| `bx-time-five` | Waktu update |
| `bx-download` | Unduh dokumen |
| `bx-info-circle` | Info |
| `bx-news` | Berita |
| `bx-star` | Featured |
| `bx-calendar` | Tanggal |
| `bx-image` | Placeholder gambar |
| `bx-chevron-right` | Arrow right |
| `bx-chevron-down` | Dropdown arrow |
| `bx-chevron-up` | Back to top |
| `bx-list-check` | Layanan publik |
| `bx-phone-call` | Hubungi |
| `bx-user` | User placeholder |
| `bx-link-external` | External link |
| `bx-map` | Alamat |
| `bx-phone` | Telepon |
| `bx-envelope` | Email |
| `bx-time` | Jam operasional |
| `bx-map-alt` | Peta placeholder |
| `bx-map-pin` | Map pin |
| `bx-search` | Search |
| `bx-search-alt` | Search empty |
| `bx-search-alt-2` | Search overlay empty |
| `bx-x` | Close |
| `bx-loader-alt` | Loading |
| `bx-error-circle` | Error |
| `bx-folder` | Folder (fallback) |

### 3.2 Logo Icons (bxl-)

| Icon | Penggunaan |
|------|------------|
| `bxl-facebook-circle` | Social link |
| `bxl-instagram` | Social link |
| `bxl-twitter` | Social link |
| `bxl-youtube` | Social link |
| `bxl-tiktok` | Social link |

### 3.3 Modifier Classes

| Class | Penggunaan |
|-------|------------|
| `bx-spin` | Loading animation |
| `bx-lg` | Large size |

**Total icons yang digunakan:** ~40 icons
**Boxicons library full:** ~1500+ icons
**Potensi penghematan:** ~95% jika di-subset

---

## 📦 4. EXTERNAL DEPENDENCIES

### 4.1 CDN Resources

| Resource | URL | Tipe | Blocking | Size (Gzip) |
|----------|-----|------|----------|-------------|
| Bootstrap CSS | cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css | CSS | ✅ Yes | ~25KB |
| Bootstrap JS | cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js | JS | ❌ defer | ~25KB |
| Google Fonts | fonts.googleapis.com/css2?family=Public+Sans | CSS | ✅ Yes | ~12KB |
| Boxicons | unpkg.com/boxicons@2.1.4/css/boxicons.min.css | CSS | ✅ **Preload** (optimized) | ~100KB+ |

### 4.2 Local Vendor Resources

| Resource | Path | Tipe | Blocking | Size |
|----------|------|------|----------|------|
| Swiper CSS | assets/vendor/swiper/swiper-bundle.min.css | CSS | ❌ Conditional | ~15KB |
| Swiper JS | assets/vendor/swiper/swiper-bundle.min.js | JS | ❌ defer | ~45KB |
| Headroom JS | assets/vendor/js/headroom.min.js | JS | ❌ defer | ~3KB |

### 4.3 Local JavaScript

| File | Size | Loaded |
|------|------|--------|
| `public-min.js` | 14,883 bytes | All pages, defer |
| `gallery-lightbox.js` | 8,116 bytes | Gallery page only |

---

## 🖼️ 5. IMAGE HANDLING

### 5.1 Current Implementation

```html
<!-- Typical image tag used -->
<img src="<?= esc($image) ?>" alt="..." loading="lazy">
```

### 5.2 Issues Identified

| Issue | Status | Impact |
|-------|--------|--------|
| No `width`/`height` attributes | ✅ **FIXED** | Causes CLS (Layout Shift) |
| No `srcset` responsive images | ✅ **FIXED** | Larger files on mobile |
| No WebP/AVIF conversion | ❌ | Larger file sizes |
| No blur placeholder | ❌ | No LQIP |
| `decoding="async"` missing | ✅ **FIXED** | Minor |

### 5.3 Images Using `loading="lazy"`

| Location | File | Count |
|----------|------|-------|
| Home | home.php | 5+ |
| Services | services.php | Per item |
| News List | news/index.php | Per article |
| News Detail | news/show.php | Per media |
| Gallery | gallery.php | Per item |
| Footer | public_footer.php | Map iframe |
| Contact | contact.php | Map iframe |

### 5.4 Images Using `loading="eager"` (Above fold)

| Location | File | Usage |
|----------|------|-------|
| News Featured | news/index.php | Line 119 - Featured image |

### 5.5 Responsive Images Implementation (srcset)

| Komponen | Status | Keterangan |
|----------|--------|------------|
| `image_helper.php` | ✅ | Helper dengan `responsive_image()`, `responsive_srcset()`, `generate_image_variants()`, `delete_image_variants()` |
| `NewsMediaService.php` | ✅ | Generate variants 400w, 800w, 1200w saat upload |
| `Galleries.php` | ✅ | Generate variants saat upload galeri |
| `Services.php` | ✅ | Generate variants saat upload layanan |
| `home.php` | ✅ | Hero slider, featured news, other news |
| `news/index.php` | ✅ | Featured news, list news |
| `news/show.php` | ✅ | Carousel, single media, thumbnail |
| `services.php` | ✅ | Service thumbnails |
| `gallery.php` | ✅ | Gallery items |
| `organization/index.php` | ✅ | Org structure image |

---

## 🔧 6. JAVASCRIPT ANALYSIS

### 6.1 public-min.js Functions

| Function | Purpose | Dependencies |
|----------|---------|--------------| 
| `debounce()` | Utility | None |
| `prefersReducedMotion()` | Accessibility | None |
| `initNavbar()` | Navbar behavior | DOM |
| `initHeroSwiper()` | Hero slider | Swiper library |
| `initSearchOverlay()` | Search functionality | Fetch API |
| `initContactForm()` | Form validation | DOM |
| `initDropdownHover()` | Dropdown behavior | Bootstrap |
| `initBackToTop()` | Back to top button | DOM |

### 6.2 Potential Issues

| Issue | Impact | Recommendation |
|-------|--------|----------------|
| Swiper retry loop (40x120ms) | CPU usage if Swiper fails | Use load event instead |
| No code splitting | Larger initial bundle | Consider lazy loading |
| Global search fetches on every keystroke (debounced) | Network usage | OK - debounced 350ms |

---

## 📉 7. PERFORMANCE METRICS ESTIMATE

### 7.1 Total Resource Size (Uncompressed)

#### All Pages (Base)
| Type | Size |
|------|------|
| CSS (7 core files) | ~72KB |
| JS (public-min.js) | ~15KB |
| External CSS (Bootstrap + Boxicons + Fonts) | ~137KB+ |
| External JS (Bootstrap) | ~25KB |
| **Subtotal** | **~249KB** |

#### Home Page Additional
| Type | Size |
|------|------|
| CSS (3 home files) | ~17KB |
| Swiper (CSS + JS) | ~60KB |
| **Home Total** | **~326KB** |

#### News List Additional
| Type | Size |
|------|------|
| CSS (news-list.css) | ~5KB |
| **News Total** | **~254KB** |

#### Gallery Additional
| Type | Size |
|------|------|
| CSS (gallery-lightbox.css) | ~5KB |
| JS (gallery-lightbox.js) | ~8KB |
| **Gallery Total** | **~262KB** |

### 7.2 HTTP Requests (Per Page)

| Page | CSS Requests | JS Requests | Total |
|------|--------------|-------------|-------|
| Home | 12 | 4 | 16 |
| News List | 9 | 3 | 12 |
| Gallery | 9 | 4 | 13 |
| Other | 8 | 3 | 11 |

---

## ✅ 8. REKOMENDASI REFAKTOR

### 8.1 Prioritas Tinggi (Quick Wins)

1. ✅ **~~Hapus duplikasi di `tokens.css`~~** - SELESAI
   - Impact: -3.8KB, cleaner code
   - Risk: Low
   - Effort: 30 menit

2. ✅ **~~Tambahkan `width`/`height` ke semua `<img>`~~** - SELESAI
   - Impact: Better CLS score
   - Risk: Low
   - Effort: 2 jam
   - Files: home.php, news/index.php, news/show.php, services.php, gallery.php

3. **Subset Boxicons**
   - Impact: -90KB+ CSS
   - Risk: Medium (perlu audit icon usage)
   - Effort: 2-3 jam

### 8.2 Prioritas Menengah

4. ✅ **~~Gabungkan CSS files~~** - SELESAI
   - Impact: -5 HTTP requests
   - Risk: Medium
   - Effort: 4 jam
   - Notes: Implementasi bundle.css dengan @import

5. **Self-host Google Fonts**
   - Impact: Faster TTFB, offline support
   - Risk: Low
   - Effort: 1 jam

6. ✅ **~~Preload Boxicons CSS~~** - SELESAI
   - Impact: Non-blocking font loading
   - Risk: Low
   - Effort: 15 menit

### 8.3 Prioritas Rendah (Major Refactor)

7. ✅ **~~Implement responsive images (srcset)~~** - SELESAI
   - Impact: 30-50% smaller images on mobile
   - Risk: High (needs backend changes)
   - Effort: 8+ jam
   - Notes: Helper created, backend variant generation implemented

8. **WebP conversion pipeline**
   - Impact: 25-35% smaller images
   - Risk: High
   - Effort: 8+ jam

9. **Lazy load Google Maps iframe on click**
   - Impact: -300KB if map not needed
   - Risk: Low
   - Effort: 1 jam

---

## 📋 9. CHECKLIST SEBELUM REFAKTOR

- [x] Backup semua file CSS
- [x] Document semua class yang digunakan (DONE - section 2)
- [x] Document semua icons yang digunakan (DONE - section 3)
- [ ] Test di browser: Chrome, Firefox, Safari, Edge
- [ ] Test di mobile viewport
- [ ] Run Lighthouse audit sebagai baseline
- [x] Setup git branch untuk refaktor

---

## 📝 10. CATATAN TAMBAHAN

### 10.1 File yang Tidak Lagi Dipakai
- `tokens.css.backup` - Backup file, tidak dimuat

### 10.2 Dependencies yang Bisa Dioptimasi
- Boxicons bisa diganti dengan icon font subset atau SVG sprites
- Google Fonts bisa di-self-host dengan subset karakter

### 10.3 Layout Shift Issues
- ~~All images tanpa `width`/`height`~~ ✅ FIXED
- Font loading tanpa `font-display` (Google Fonts sudah punya display=swap)

---

## 📊 11. PROGRESS LOG

| Tanggal | Perbaikan | Hasil |
|---------|-----------|-------|
| 2025-12-07 | Hapus duplikasi tokens.css | -3,821 bytes (25%) |
| 2025-12-07 | Tambah width/height/decoding ke images | Improved CLS |
| 2025-12-07 | Preload Boxicons CSS | Non-blocking |
| 2025-12-07 | Tambah fetchpriority untuk LCP images | Faster LCP |
| 2025-12-07 | CSS Bundle (bundle.css) | -5 HTTP requests |
| 2025-12-07 | Responsive Images (srcset) | Backend variant generation + frontend srcset |
| 2025-12-07 | Fix Admin News Form ($val undefined) | Error resolved |
| 2025-12-07 | **Self-host Google Fonts** | -2 DNS lookups, ~130KB local fonts |
| 2025-12-07 | **Subset Boxicons** | -90KB CSS (7KB subset vs ~100KB full) |

---

## 🎯 12. REMAINING TASKS

### Belum Dikerjakan (Optional)

| Task | Priority | Effort | Impact |
|------|----------|--------|--------|
| ~~Subset Boxicons~~ | ~~Medium~~ | ~~2-3 jam~~ | ~~-90KB+ CSS~~ ✅ SELESAI |
| ~~Self-host Google Fonts~~ | ~~Low~~ | ~~1 jam~~ | ~~Faster TTFB~~ ✅ SELESAI |
| WebP conversion pipeline | Low | 8+ jam | -25-35% image size |
| Code splitting JS | Low | 4+ jam | Smaller initial bundle |

### Tidak Dikerjakan (By Design)
| Task | Alasan |
|------|--------|
| Lazy load Google Maps | User membutuhkan maps selalu tampil |

---

*Laporan ini dibuat sebagai referensi sebelum melakukan refaktor untuk meminimalisir bug pasca-refaktor.*

