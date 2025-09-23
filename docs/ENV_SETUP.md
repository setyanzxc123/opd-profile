# Panduan Konfigurasi `.env`

Panduan ini membantu menyiapkan file `.env` untuk aplikasi OPD Profile berbasis CodeIgniter 4.

## 1. Menyalin Template

1. Salin berkas contoh:
   ```bash
   copy .env.example .env
   ```
2. Buka `.env` baru tersebut dan aktifkan variabel yang dibutuhkan dengan menghapus karakter `#` di depannya.

## 2. Mode Lingkungan & URL Dasar

```ini
CI_ENVIRONMENT = development
app.baseURL = 'http://localhost:8080/'
```
- Gunakan `production` saat deployment.
- Sesuaikan `baseURL` dengan domain/port server.

## 3. Database

Sesuaikan kredensial database MariaDB/MySQL:

```ini
database.default.hostname = localhost
database.default.database = opd_profile
database.default.username = opd_user
database.default.password = ganti_passwordmu
database.default.DBDriver  = MySQLi
database.default.port      = 3306
```

> Pastikan database dan user sudah dibuat di server.

## 4. Kunci Enkripsi (opsional tapi disarankan)

```ini
# encryption.key = base64:GENERATE_YOUR_KEY
```
- Untuk membuat key baru: `php spark key:generate` lalu salin hasilnya.

## 5. Email / SMTP untuk Notifikasi

Jika ingin mengaktifkan pengiriman email (misal notifikasi Pesan Kontak), tambahkan konfigurasi berikut:

```ini
email.protocol = smtp
email.SMTPHost = smtp.mailtrap.io
email.SMTPUser = user_smtp
email.SMTPPass = password_smtp
email.SMTPPort = 587
email.mailType = html
email.SMTPTimeout = 10
email.fromEmail = noreply@opd.go.id
email.fromName  = OPD Profile
```

> Ganti nilai sesuai layanan email Anda. Untuk pengujian bisa memakai Mailtrap atau SMTP lokal.

## 6. Pengaturan Upload

Pastikan direktori upload dapat ditulis:

```ini
app.uploadPath = 'public/uploads'
```

Saat deployment, berikan permission yang tepat, contoh di Linux:

```bash
chmod -R 775 public/uploads writable/
chown -R www-data:www-data public/uploads writable/
```

## 7. Menjalankan Migrasi & Seeder

Setelah `.env` siap:

```bash
php spark migrate
php spark db:seed DatabaseSeeder
```

Perintah tersebut membuat seluruh tabel (users, opd_profile, services, news, galleries, documents, contact_messages, activity_logs) dan mengisi data contoh beserta akun admin default (`admin / Admin123!`).

## 8. Pengujian Lokal

1. Jalankan server pengembangan:
   ```bash
   php spark serve
   ```
2. Akses `http://localhost:8080` dan login ke `/admin` menggunakan kredensial admin.

## 9. Catatan Tambahan

- Kolom `services`, `contact_messages`, dan fitur anti-spam akan diimplementasikan setelah frontend publik rampung. Konfigurasi SMTP tetap disiapkan untuk mengantisipasi fitur tersebut.
- Apabila mengaktifkan caching atau fitur lainnya, tambahkan variabel yang relevan (misal Redis) di `.env` sesuai dokumentasi CodeIgniter 4.
