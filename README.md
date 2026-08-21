# Website Kelurahan Kangkung (PHP Native)

## Struktur folder
```
kangkung-peta/
├── index.php                     # auto-redirect ke Pages/index.php
├── config.php                    # nama kelurahan, kontak, koordinat, kategori, akun admin
├── Pages/
│   ├── index.php                  # Beranda
│   ├── includes/
│   │   ├── header.php              # navbar (dipakai semua halaman publik)
│   │   └── footer.php              # footer (dipakai semua halaman publik)
│   ├── VisiMisi/visi-misi.php
│   ├── Sejarah/sejarah.php
│   ├── Data-Aparatur/aparatur.php
│   ├── Tim-KKN/tim-kkn.php
│   ├── Peta/peta.php               # peta interaktif (Leaflet.js)
│   ├── Chatbot/chatbot.php         # chatbot FAQ (rule-based, tanpa API key)
│   ├── Statistik/statistik.php
│   ├── Berita/berita.php + detail.php
│   └── Galeri/galeri.php
├── admin/                        # panel admin kelola data peta
│   ├── login.php / logout.php
│   ├── index.php                  # dashboard
│   ├── form.php                   # tambah & edit tempat
│   ├── delete.php
│   └── includes/ (auth.php, functions.php, sidebar.php)
├── api/
│   └── get_pois.php               # endpoint PHP -> baca data/pois.json
├── data/
│   ├── pois.json                  # titik lokasi di peta
│   ├── aparatur.json              # data aparatur kelurahan
│   ├── tim-kkn.json               # data anggota tim KKN
│   ├── statistik.json             # data kependudukan
│   ├── berita.json                # artikel berita
│   └── galeri.json                # foto galeri
└── assets/
    ├── css/style.css (situs publik) + admin.css (panel admin)
    ├── js/peta.js
    └── img/                       # gambar & placeholder per section
```

## Cara jalanin di lokal
```bash
php -S localhost:8000
```
Buka: `http://localhost:8000/` (otomatis redirect ke Beranda) atau `http://localhost:8000/Pages/index.php`

## Semua halaman navbar sudah jadi (gak ada lagi 404)
- **Beranda** — hero, ringkasan statistik, quick links ke semua section, 3 berita terbaru
- **Profile ▾**
  - Visi & Misi
  - Sejarah Kelurahan (timeline)
  - Data Aparatur (kartu foto + jabatan, dari `data/aparatur.json`)
  - Tim KKN (kartu foto + jabatan, dari `data/tim-kkn.json`)
- **Peta Kelurahan** — peta interaktif + panel admin buat kelola datanya
- **Chatbot AI** — FAQ otomatis: jawab soal jam layanan, kontak, cara buat surat, arahin ke Peta/Statistik/Berita/Galeri sesuai pertanyaan. Rule-based di JavaScript, **tidak butuh API key** apa pun.
- **Statistik Kelurahan** — kartu jumlah penduduk/KK/wiraswasta, tabel RT per lingkungan, grafik gender
- **Berita** — grid berita + halaman detail per artikel (`berita.php?slug=...`)
- **Galeri** — grid foto dengan filter kategori + lightbox (klik foto buat perbesar)

⚠️ **PENTING:** semua konten teks/foto di halaman-halaman baru ini masih **data contoh**, ditandai jelas dengan tulisan "Contoh data" di kode maupun tampilan. Ganti sesuai data asli KKN lu:
- Visi Misi & Sejarah → edit langsung teks di `Pages/VisiMisi/visi-misi.php` dan `Pages/Sejarah/sejarah.php`
- Aparatur, Tim KKN, Statistik, Berita, Galeri → edit file JSON di folder `data/` (format & contoh field ada di masing-masing file)
- Foto → ganti file di `assets/img/aparatur/`, `assets/img/tim-kkn/`, `assets/img/berita/`, `assets/img/galeri/`

## Panel Admin (untuk kelola data Peta)
Saat ini panel admin baru meng-cover **data titik lokasi peta** (UMKM, fasilitas, dll). Section lain (Berita/Galeri/Aparatur/Statistik) masih diedit manual lewat file JSON di `data/` — kalau lu mau ada panel admin buat itu juga, tinggal bilang, gw tambahin (arsitekturnya sama persis kayak admin peta, tinggal digandain).

**Akses:** `http://localhost:8000/admin/login.php`
**Login default:**
- Username: `admin`
- Password: `admin123`

⚠️ **WAJIB ganti password default sebelum di-deploy ke hosting.** Caranya:
```bash
php -r "echo password_hash('password_baru_lu', PASSWORD_DEFAULT);"
```
Copy hasil hash-nya, tempel ke `config.php` di baris `ADMIN_PASSWORD_HASH`. Kalau mau ganti username juga, tinggal edit `ADMIN_USERNAME`.

**Fitur admin peta:**
- Login/logout pakai session PHP (halaman lain otomatis redirect ke login kalau belum login — `admin/includes/auth.php`)
- Dashboard: tabel semua tempat + statistik jumlah per kategori + search cepat
- Tambah/Edit tempat: form lengkap (nama, kategori, deskripsi, alamat, kontak, jam buka, lat/lng, upload foto)
- Hapus tempat: konfirmasi dulu sebelum kehapus
- Semua perubahan langsung ke `data/pois.json` → otomatis muncul di peta publik tanpa langkah tambahan

## Yang perlu lu edit sebelum dipakai beneran
1. **`config.php`** — ganti kontak, alamat, jam layanan, dan `ADMIN_PASSWORD_HASH`.
2. **`data/*.json`** — semua isinya masih dummy, ganti satu-satu sesuai data lapangan.
3. **`assets/img/`** — ganti semua placeholder dengan foto asli.
4. **Logo** — taruh file `logo-kelurahan.png` di `assets/img/` biar muncul di navbar (sekarang disembunyikan otomatis kalau filenya belum ada).

## Ganti ke database (opsional, kalau butuh nanti)
Struktur sekarang pakai file JSON per section (`data/*.json`) supaya gampang diedit tanpa setup database. Kalau nanti mau pindah ke MySQL:
- Untuk peta: ganti isi `pois_load()` / `pois_save()` di `admin/includes/functions.php` dan `api/get_pois.php` dengan query PDO/MySQLi.
- Untuk section lain: pola sama — file publik (`Pages/.../*.php`) baca lewat `json_decode(file_get_contents(...))`, tinggal ganti jadi query database, struktur array hasilnya disamain biar HTML-nya gak perlu diubah.

```php
$pdo = new PDO('mysql:host=localhost;dbname=kangkung;charset=utf8mb4', 'user', 'pass');
$stmt = $pdo->query('SELECT * FROM pois');
$pois = $stmt->fetchAll(PDO::FETCH_ASSOC);
```
