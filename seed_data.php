<?php
/**
 * seed_data.php — Seed data nyata Kelurahan Kangkung ke database
 * Jalankan sekali: http://localhost:8000/seed_data.php
 */
require_once __DIR__ . '/config_db.php';
$pdo = getDB();

echo "<style>body{font-family:sans-serif;padding:20px;}</style>";
echo "<h2>Seeding Data Kelurahan Kangkung...</h2>";

// ============================================================
// 1. TABEL KONTEN — buat jika belum ada
// ============================================================
$pdo->exec("CREATE TABLE IF NOT EXISTS konten (
    key_name VARCHAR(100) PRIMARY KEY,
    key_value LONGTEXT
)");
echo "✅ Tabel <code>konten</code> siap.<br>";

// ============================================================
// 2. SEED SEJARAH
// ============================================================
$sejarah = "Nama Kangkung berasal dari tanaman kangkung. Menurut sumber sejarah masyarakat setempat, dahulu wilayah Kangkung terdiri atas daratan dan rawa kecil. Di kawasan rawa tersebut banyak tumbuh tanaman kangkung, sehingga masyarakat kemudian menyebut daerah tersebut sebagai Kampung Kangkung.

Pada masa awal, wilayah Kangkung dihuni oleh masyarakat Lampung Pesisir sebagai penduduk asli. Karena letaknya berada di kawasan pesisir Teluk Lampung, kehidupan masyarakat sejak dahulu banyak berkaitan dengan aktivitas laut dan perikanan.

Sekitar tahun 1952, datang rombongan menggunakan perahu besar dari Jawa Barat/Cirebon. Mereka datang ke kawasan pesisir Lampung untuk menangkap ikan dan kemudian menetap. Kehadiran masyarakat pendatang tersebut turut membentuk perkembangan masyarakat pesisir Kangkung yang sampai sekarang dikenal memiliki kehidupan yang erat dengan aktivitas nelayan.

Kawasan Kangkung juga dikenal dengan nama Ujung Bom. Nama tersebut berkaitan dengan kawasan dermaga di pesisir yang pada masa kolonial Belanda digunakan sebagai tempat pendaratan kapal. Karena sejarah kawasan ini, Ujung Bom kemudian menjadi salah satu bagian penting dari identitas kawasan pesisir Kangkung.

Dalam perkembangan pemerintahannya, Kangkung pada awalnya merupakan perkampungan, kemudian pada sekitar tahun 1960-an pemerintahan dipimpin oleh seorang Kepala Kampung. Selanjutnya sistem pemerintahan berubah menjadi pemerintahan kelurahan yang dipimpin oleh lurah.

Secara administratif, Kangkung dahulu termasuk wilayah Kecamatan Teluk Betung Selatan. Setelah dilakukan penataan wilayah Kota Bandar Lampung melalui Peraturan Daerah Kota Bandar Lampung Nomor 04 Tahun 2012, terbentuk Kecamatan Bumi Waras. Kangkung kemudian menjadi salah satu kelurahan yang berada di Kecamatan Bumi Waras.

Saat ini, berdasarkan portal resmi Pemerintah Kota Bandar Lampung, Kelurahan Kangkung merupakan bagian dari Kecamatan Bumi Waras dan terdiri atas 3 Lingkungan serta 27 RT.";

$stmt = $pdo->prepare("INSERT INTO konten (key_name, key_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE key_value = VALUES(key_value)");
$stmt->execute(['sejarah', $sejarah]);
echo "✅ Sejarah Kelurahan berhasil disimpan.<br>";

// ============================================================
// 3. SEED VISI MISI
// ============================================================
$visi = 'Terwujudnya kelurahan di wilayah pesisir yang Mandiri, Sejahtera, Bersih, dan Berdaya Saing Berbasis Ekonomi Biru yang Berkelanjutan.';
$stmt->execute(['visi', $visi]);

$misi = json_encode([
    'Mengembangkan sistem pengelolaan ekonomi warga pesisir yang terintegrasi dari hulu ke hilir melalui penguatan peran koperasi.',
    'Membangun infrastruktur pendukung desa nelayan yang tertata rapi, sehat, dan nyaman bagi kehidupan warga.',
    'Meningkatkan kapasitas sumber daya manusia masyarakat pesisir melalui pelatihan keterampilan serta pengolahan hasil laut.',
    'Meningkatkan produktivitas masyarakat pesisir khususnya nelayan melalui penggunaan sarana dan teknologi ramah lingkungan.',
], JSON_UNESCAPED_UNICODE);
$stmt->execute(['misi', $misi]);
echo "✅ Visi & Misi berhasil disimpan.<br>";

// ============================================================
// 4. SEED STATISTIK — sesuai format yang dibaca halaman publik
// ============================================================
$statData = [
    'jumlah_penduduk' => '2562',
    'jumlah_kk'       => '209',
    'jumlah_wiraswasta' => '70',  // sum dari semua RT: 23+21+12+4+4+6=70
    'terakhir_diperbarui' => date('Y-m-d'),
    'penduduk_per_jenis_kelamin' => json_encode([
        'laki_laki'  => 1145,   // 145+257+210+82+403+48
        'perempuan'  => 1417,   // 166+364+312+143+375+57
    ], JSON_UNESCAPED_UNICODE),
    'lingkungan' => json_encode([
        ['nama' => 'Lingkungan I',   'jumlah_rt' => 9],
        ['nama' => 'Lingkungan II',  'jumlah_rt' => 9],
        ['nama' => 'Lingkungan III', 'jumlah_rt' => 9],
    ], JSON_UNESCAPED_UNICODE),
];

$stmtStat = $pdo->prepare("INSERT INTO statistik (key_name, key_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE key_value = VALUES(key_value)");
foreach ($statData as $key => $val) {
    $stmtStat->execute([$key, $val]);
}
echo "✅ Data Statistik berhasil disimpan.<br>";
echo "<ul>
  <li>Total Penduduk: <strong>2.562</strong></li>
  <li>Laki-laki: <strong>1.145</strong></li>
  <li>Perempuan: <strong>1.417</strong></li>
  <li>Jumlah KK: <strong>209</strong></li>
  <li>Jumlah RT: <strong>27</strong></li>
  <li>Lingkungan: <strong>3</strong></li>
</ul>";

echo "<hr><h3>🎉 Seeding Selesai! <a href='Pages/index.php'>Lihat Halaman Publik</a></h3>";
