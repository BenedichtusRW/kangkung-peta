<?php
// ================= ENVIRONMENT SETUP =================
// Ubah ke 'development' jika sedang masa perbaikan, 'production' jika sudah live
define('ENVIRONMENT', 'production'); 

if (ENVIRONMENT === 'production') {
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
    error_reporting(E_ALL);
    ini_set('log_errors', 1); // Error akan disimpan di file log, tidak ditampilkan ke pengunjung
} else {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
}

/**
 * config.php
 * Pengaturan umum situs Kelurahan Kangkung. Diikutkan (require) di setiap
 * halaman supaya nama kelurahan, kontak, dan titik koordinat cukup diubah
 * di satu tempat saja.
 */

// Info umum kelurahan — GANTI sesuai data resmi
define('NAMA_KELURAHAN', 'Kelurahan Kangkung');
define('NAMA_KECAMATAN', 'Kec. Bumi Waras');
define('NAMA_KOTA', 'Kota Bandar Lampung');
define('ALAMAT_KANTOR', 'Jl. Way Kanan No. 4, Kelurahan Kangkung, Kec. Bumi Waras, Kota Bandar Lampung');
define('KONTAK_TELEPON', '0812-3456-7890');
define('KONTAK_EMAIL', 'kelurahan.kangkung@bandarlampung.go.id');
define('JAM_LAYANAN', 'Senin - Jumat, 08:00 - 15:00 WIB');

// AI Settings (Google Gemini)
define('GEMINI_API_KEY', 'YOUR_API_KEY_HERE');

// Titik pusat peta (koordinat Kangkung, Kec. Bumi Waras, Bandar Lampung)
define('PETA_LAT', -5.4496361);
define('PETA_LNG', 105.2684878);
define('PETA_ZOOM', 16);

// ================= AKUN ADMIN =================
// GANTI username & password default ini sebelum dipakai beneran!
// Password di-hash pakai password_hash(). Untuk generate hash baru:
//   php -r "echo password_hash('password_baru', PASSWORD_DEFAULT);"
define('ADMIN_USERNAME', 'admin');
define('ADMIN_PASSWORD_HASH', '$2y$10$WVVocIM79w9H8WsTpcyMW.swfEiI3Y7jvJYPQGNMj5edfaOKhp7Za'); // default: admin123

// Kategori titik lokasi di peta — urutannya mengikuti catatan struktur situs
$KATEGORI_PETA = [
    'semua'         => ['label' => 'Semua',          'warna' => '#374151', 'icon' => 'fa-solid fa-layer-group'],
    'tugu'          => ['label' => 'Tugu / Landmark', 'warna' => '#6B7280', 'icon' => 'fa-solid fa-monument'],
    'pemerintahan'  => ['label' => 'Pemerintahan',    'warna' => '#2563EB', 'icon' => 'fa-solid fa-building-columns'],
    'kuliner'       => ['label' => 'Kuliner',         'warna' => '#DC2626', 'icon' => 'fa-solid fa-utensils'],
    'jasa'          => ['label' => 'Jasa',            'warna' => '#F59E0B', 'icon' => 'fa-solid fa-briefcase'],
    'ibadah'        => ['label' => 'Tempat Ibadah',   'warna' => '#8B5CF6', 'icon' => 'fa-solid fa-mosque'],
    'sekolah'       => ['label' => 'Sekolah',         'warna' => '#CA8A04', 'icon' => 'fa-solid fa-graduation-cap'],
    'kesehatan'     => ['label' => 'Kesehatan',       'warna' => '#059669', 'icon' => 'fa-solid fa-hospital'],
];
