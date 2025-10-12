# Panduan Logo & Branding

Gunakan panduan ini saat mengunggah atau memperbarui logo OPD melalui panel admin.

## Dimensi & Format Ideal

- Rasio disarankan: persegi (1:1).  
- Ukuran minimum: 96 × 96 piksel.  
- Ukuran maksimum: 512 piksel sisi terpanjang (sistem otomatis akan menyesuaikan).  
- Format file yang diterima: `PNG`, `WEBP`, `JPG/JPEG`, dan `GIF`.  
- Untuk hasil terbaik, gunakan gambar berlatar transparan (PNG/WEBP) agar pas di tema terang maupun gelap.

## Alur Pengunggahan

1. Buka `Admin → Profil` lalu pilih tab **Umum**.  
2. Klik tombol unggah pada bagian *Logo*.  
3. Setelah memilih berkas, modal cropping akan muncul (Cropper.js):  
   - Seret gambar untuk mengatur posisi.  
   - Gunakan scroll atau pinch untuk zoom.  
   - Tekan **Gunakan Logo** untuk menyimpan hasil crop.  
4. Pratinjau akan berubah mengikuti hasil crop. Bila ingin membatalkan, pilih **Hapus logo** lalu simpan.  
5. Klik **Simpan** untuk menerapkan perubahan. Logo yang sama dipakai di situs publik dan di dashboard admin.

## Catatan Teknis

- Hasil crop otomatis disimpan sebagai berkas baru dengan kualitas ±90 dan dibatasi pada dimensi maksimum.  
- Metadata sederhana (dimensi akhir, waktu simpan) ikut diserialisasi untuk mempermudah debugging.  
- Semua logo tersimpan di `public/uploads/profile`; pastikan folder tersebut writable pada lingkungan produksi.  
- Jika logo tidak tersedia, sistem menampilkan fallback (inisial atau ikon lingkaran).

## Tips QA

- Uji tampilan di layar kecil (≤ 576 px) agar logo tidak keluar dari navbar.  
- Coba unggah logo berukuran besar (> 1 MB) untuk memastikan proses crop + optimasi tetap lancar.  
- Matikan JavaScript sementara untuk memastikan mode fallback upload tetap dapat digunakan.  
- Setelah deploy, lakukan smoke test: unggah logo baru, simpan, lalu pastikan tampil di situs publik dan admin.
