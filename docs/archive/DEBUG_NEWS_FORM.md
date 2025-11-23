# Debugging Form News - Langkah-langkah

## Masalah
Form `/admin/news` tidak merespon saat tombol "Simpan Berita" diklik.

## Langkah Debugging

### 1. Cek JavaScript Console (PENTING!)

1. Buka halaman `/admin/news/create` di browser
2. Tekan **F12** untuk membuka Developer Tools
3. Klik tab **Console**
4. Isi form dengan data dummy:
   - Judul: "Test Berita"
   - Isi: Tulis sesuatu di editor
5. Klik tombol **"Simpan Berita"**
6. Lihat output di console

**Yang Harus Muncul di Console:**
```
[News Form] Form element: <form>...</form>
[News Form] Submit button: <button>...</button>
[News Form] Submit event listener attached
[News Form] Submit event triggered
[News Form] isSubmitting: false
[News Form] TinyMCE editor: Object {...}
[News Form] Editor content saved
[News Form] Title value: Test Berita
[News Form] Content length: xxx
[News Form] Setting isSubmitting to true
[News Form] Button updated to loading state
[News Form] Form will now submit
```

**Jika Ada Error Merah:**
- Catat error message lengkapnya
- Kemungkinan ada masalah dengan TinyMCE atau JavaScript lainnya

### 2. Cek Network Tab

1. Masih di Developer Tools (F12)
2. Klik tab **Network**
3. Isi form dan klik "Simpan Berita"
4. Lihat apakah ada request ke `/admin/news` (method POST)

**Yang Harus Terjadi:**
- Ada request POST ke `http://localhost/opd-profile/admin/news`
- Status Code: 302 (redirect) ATAU 200 (success) ATAU 422/400 (validation error)

**Jika TIDAK ADA REQUEST SAMA SEKALI:**
- Berarti JavaScript memblock form submission
- Lihat console untuk error

**Jika Ada Request tapi Status 500:**
- Ada error di server/PHP
- Lanjut ke step 3

### 3. Cek PHP Error Log

Lokasi log file: `c:\xampp\htdocs\opd-profile\writable\logs\`

1. Buka folder tersebut
2. Cari file dengan nama format: `log-YYYY-MM-DD.log` (tanggal hari ini)
3. Buka dengan text editor
4. Cari baris yang mengandung `[News::store]`

**Yang Harus Muncul:**
```
DEBUG - [News::store] Method called
DEBUG - [News::store] POST data: {...}
DEBUG - [News::store] Validation failed: {...} ATAU Validation passed
```

**Jika TIDAK ADA LOG SAMA SEKALI:**
- Request tidak sampai ke controller
- Kemungkinan masalah routing atau middleware

### 4. Cek CSRF Token

Di halaman form, buka **Developer Tools > Elements/Inspector**

Cari elemen ini:
```html
<input type="hidden" name="csrf_test_name" value="...">
```

**Pastikan:**
- Input tersebut ada
- Value tidak kosong

### 5. Test Sederhana

Coba disable validasi JavaScript untuk test:

1. Comment out bagian validasi di console:
```javascript
// Paste di console browser:
document.querySelector('form[action*="admin/news"]').addEventListener('submit', function(e) {
    console.log('Form submitted!');
}, true);
```

2. Atau sementara edit file form.php, comment bagian ini:
```javascript
if (!titleValue || !contentValue) {
  // ... code ini
}
```

## Hasil yang Diharapkan

Tolong berikan informasi berikut:
1. ✅/❌ Apakah console log muncul?
2. ✅/❌ Apakah ada error di console?
3. ✅/❌ Apakah ada request di Network tab?
4. ✅/❌ Apakah ada log di file PHP (writable/logs/)?
5. Screenshot console dan network tab jika memungkinkan

Dengan informasi ini saya bisa tahu persis di mana masalahnya.
