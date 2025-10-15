# Audit Komponen UI (Pra Migrasi Tailwind)

Dokumen ini mencatat seluruh fitur dan komponen yang harus tetap tersedia setelah redesain dengan Tailwind + DaisyUI. Gunakan sebagai referensi saat menyusun ulang tampilan publik dan admin agar tidak ada modul yang terlewat.

## 1. Area Publik

### Layout & Navigasi
- `layouts/public.php`: struktur dasar, skip link, header, footer, pemanggilan asset JS/CSS.
- `layouts/public_nav.php`: navbar, dropdown layanan (jika ada), logo OPD.
- `layouts/public_footer.php`: kontak ringkas, peta (opsional via Leaflet), sosial media.

### Halaman & Komponen
- `public/home.php`: hero slider/fallback, ringkasan profil, statistik layanan, daftar layanan, berita, galeri, dokumen, kontak cepat.
- `public/profile.php`: detail profil OPD, visi misi, struktur organisasi (jika memanfaatkan data).
- `public/services.php`: daftar layanan/produk layanan, CTA untuk tiap layanan.
- `public/news/index.php` & `news/show.php`: daftar berita, pagination, detail berita, meta tanggal.
- `public/documents.php`: tabel/daftar dokumen unduhan (judul, kategori, tahun, link).
- `public/gallery.php`: grid galeri, modal pratinjau (jika ada).
- `public/contact.php`: informasi kontak, form pengaduan, peta lokasi.

### Utilitas
- `public/assets/js/public.js`: carousel, interaksi statistik, utilitas aksesibilitas.
- `public/assets/js/footer-map.js`: inisialisasi Leaflet di footer.
- `public/assets/css/public/*`: tokens, layout, komponen, pages (referensi gaya lama sebelum dibersihkan).

## 2. Area Admin

### Layout & Navigasi
- `layouts/admin.php`: shell Sneat (sidebar, topbar, breadcrumb, avatar).
- Menu, breadcrumb, notifikasi session (flash message), dropdown akun.

### Halaman Dashboard & Modul
- `admin/dashboard.php`: kartu ringkasan (statistik berita, layanan, dsb).
- `admin/profile/edit.php`: form profil OPD (tab umum/visi-misi/kontak), upload logo dengan Cropper, peta Leaflet pengaturan lokasi.
- `admin/news/index.php` & `form.php`: tabel berita, action CRUD, editor konten.
- `admin/documents/index.php` & `form.php`: daftar dokumen, upload file.
- `admin/galleries/index.php` & `form.php`: list galeri, upload gambar.
- `admin/users/index.php` & `form.php`: manajemen pengguna/admin.
- `admin/contacts/index.php` & `show.php`: daftar pesan/aduan, detail pesan.
- `admin/activity_logs/index.php`: tabel log aktivitas.
- `admin/account/settings.php`: pengaturan akun pribadi (password, profil).

### Autentikasi
- `auth/login.php`: form login admin (flash error/success).

### Utilitas & Skrip
- `public/assets/js/admin/profile-logos.js`: logika CropperJS.
- `public/assets/js/admin/profile-map.js`: interaksi Leaflet admin.
- `public/assets/css/admin/*` & `public/assets/css/custom.css`: referensi gaya lama (Snack/Sneat).
- Vendor JS/CSS (CropperJS, Leaflet) yang harus tetap terintegrasi.

## 3. Catatan Tambahan

- Pastikan setiap komponen memiliki padanan di desain baru; tambahkan ke daftar ini jika modul baru ditemukan.
- Setiap file/fitur yang dihapus harus memiliki pengganti fungsional atau alasan bisnis yang terdokumentasi.
- Gunakan inventaris ini untuk membuat partial komponen Tailwind (navbar, footer, kartu, tabel, modal, form tabs) sebelum memulai refactor besar.

## 4. Evaluasi Font & Ikon Legacy

- **Font saat ini**
  - Public: `Inter` (Google Fonts) dimuat di `layouts/public.php`.
  - Admin: `Public Sans` (Google Fonts) dimuat di `layouts/admin.php`.
- **Ikon saat ini**
  - Admin menggunakan kelas `bx` (Boxicons) melalui paket Sneat.
  - Beberapa ikon tambahan tersedia lewat `assets/vendor/fonts/iconify-icons.css`.

### Rencana Migrasi
- Gunakan tema bawaan DaisyUI dengan font sistem terlebih dahulu; jika diperlukan, reintroduksi font publik (mis. `Inter`) via konfigurasi Tailwind.
- Adopsi ikon yang umum di ekosistem Tailwind:
  - Heroicons (SVG) untuk navigasi dan tombol utama.
  - Tabler Icons atau Lucide sebagai tambahan jika varian ikon lebih banyak dibutuhkan.
- Hentikan penggunaan Boxicons/Iconify yang terkait template Sneat; dokumentasikan ikon baru saat membangun komponen Tailwind.

## 5. Komponen Partial Tailwind

- `components/public/section_header.php`: digunakan di halaman publik (home, profil, layanan, dokumen, kontak) untuk menjaga konsistensi struktur judul/eyebrow/deskripsi. Parameter: `id`, `eyebrow`, `title`, `description`, `align`, `maxWidth`.
- Navbar & footer sudah dibangun ulang berbasis DaisyUI utilitas (`layouts/public_nav.php`, `layouts/public_footer.php`) dan menjadi referensi untuk partial komponen navigasi & informasi kontak.
- Kartu layanan/berita/dokumen memakai pola utilitas serupa (rounded-3xl, border, shadow-sm); gunakan pola ini saat menambah modul baru agar tampilan tetap seragam.

## 6. Validasi Responsif & Aksesibilitas

- **Responsif**: diuji pada viewport 320px, 768px, 1280px menggunakan DevTools; navbar mobile toggle, grid layanan/galeri, dan layout berita tetap terbaca tanpa overflow.
- **Aksesibilitas**: skip-link diuji dengan keyboard (Tab) menuju `#konten-utama`; komponen slider mematuhi `aria` roles; form pencarian memiliki label tersembunyi dan state aria-expanded.
- **Kontras**: kombinasi warna DaisyUI default + utilitas netral memenuhi kontras AA untuk teks utama; periksa ulang ketika menambahkan warna kustom.
