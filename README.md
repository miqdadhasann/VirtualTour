# DOKUMENTASI SISTEM VIRTUAL TOUR

**Status:** Development Stage  
**Teknologi:** PHP Native, MySQL, Pannellum JS

---

## 1. PERSIAPAN DATABASE (WAJIB)
Sistem ini membutuhkan database MySQL agar bisa berjalan.

### Langkah 1: Download Database
File master database (`.sql`) tersimpan di Google Drive. Silakan unduh melalui link berikut:  
📂 **[Download Database SQL Disini]([https://drive.google.com/drive/folders/1eUzx537Maoq8C_595g74DG49b7iantue?usp=drive_link](https://drive.google.com/file/d/1oa_6ii4eyaY9gy-Nd7uSjDlk_At_YWD5/view?usp=sharing))**

### Langkah 2: Import ke MySQL
1. Buka **phpMyAdmin** (atau tools database lain).
2. Buat database baru dengan nama: `virtual_tour`.
3. Import file `.sql` yang sudah diunduh ke dalam database tersebut.

### Langkah 3: Cek Koneksi
Pastikan konfigurasi di file `koneksi.php` dan `includes/logic_loader.php` sesuai dengan settingan server lokal Anda:
```php
$host = 'localhost';
$user = 'root';
$pass = ''; 
$db   = 'virtual_tour';
```

### Langkah 4: Sisanya Tinggal Input Gambar sama koordinat Aj

