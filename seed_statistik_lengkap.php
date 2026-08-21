<?php
/**
 * seed_statistik_lengkap.php — Seed data statistik per RT lengkap
 */
require_once __DIR__ . '/config_db.php';
$pdo = getDB();

echo "<style>body{font-family:sans-serif;padding:20px;max-width:700px;}</style>";
echo "<h2>Seed Statistik Lengkap Per RT...</h2>";

// Pastikan tabel konten ada
$pdo->exec("CREATE TABLE IF NOT EXISTS konten (key_name VARCHAR(100) PRIMARY KEY, key_value LONGTEXT)");

$stmt = $pdo->prepare("INSERT INTO konten (key_name, key_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE key_value=VALUES(key_value)");
$stmtStat = $pdo->prepare("INSERT INTO statistik (key_name, key_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE key_value=VALUES(key_value)");

// ---- STATISTIK AGREGAT ----
$stmtStat->execute(['jumlah_penduduk', '2562']);
$stmtStat->execute(['jumlah_kk', '209']);
$stmtStat->execute(['jumlah_wiraswasta', '70']);
$stmtStat->execute(['terakhir_diperbarui', date('Y-m-d')]);
$stmtStat->execute(['penduduk_per_jenis_kelamin', json_encode(['laki_laki'=>1145,'perempuan'=>1417], JSON_UNESCAPED_UNICODE)]);
$stmtStat->execute(['lingkungan', json_encode([
    ['nama'=>'Lingkungan I',   'jumlah_rt'=>9],
    ['nama'=>'Lingkungan II',  'jumlah_rt'=>9],
    ['nama'=>'Lingkungan III', 'jumlah_rt'=>9],
], JSON_UNESCAPED_UNICODE)]);

// ---- DATA PER RT ----
$dataRT = [
    ['rt'=>'02', 'lk'=>'I',   'laki'=>48,  'perempuan'=>57,  'total'=>105, 'pekerjaan'=>[
        ['nama'=>'Buruh','jumlah'=>9],
        ['nama'=>'Wiraswasta','jumlah'=>6],
        ['nama'=>'Karyawan Swasta','jumlah'=>5],
        ['nama'=>'Serabutan','jumlah'=>10],
    ]],
    ['rt'=>'06', 'lk'=>'II',  'laki'=>257, 'perempuan'=>364, 'total'=>621, 'pekerjaan'=>[
        ['nama'=>'Buruh','jumlah'=>112],
        ['nama'=>'Wiraswasta','jumlah'=>21],
        ['nama'=>'PNS','jumlah'=>3],
        ['nama'=>'Serabutan','jumlah'=>13],
    ]],
    ['rt'=>'17', 'lk'=>'III', 'laki'=>145, 'perempuan'=>166, 'total'=>311, 'pekerjaan'=>[
        ['nama'=>'Buruh Nelayan','jumlah'=>72],
        ['nama'=>'Buruh Harian','jumlah'=>50],
        ['nama'=>'Wiraswasta','jumlah'=>23],
    ]],
    ['rt'=>'18', 'lk'=>'III', 'laki'=>82,  'perempuan'=>143, 'total'=>225, 'pekerjaan'=>[
        ['nama'=>'Buruh Nelayan','jumlah'=>21],
        ['nama'=>'Buruh Harian Lepas','jumlah'=>34],
        ['nama'=>'Wiraswasta','jumlah'=>4],
        ['nama'=>'Pengangguran/Lainnya','jumlah'=>166],
    ]],
    ['rt'=>'25', 'lk'=>'III', 'laki'=>403, 'perempuan'=>375, 'total'=>778, 'pekerjaan'=>[
        ['nama'=>'Nelayan','jumlah'=>61],
        ['nama'=>'Pedagang','jumlah'=>55],
        ['nama'=>'Buruh','jumlah'=>82],
        ['nama'=>'Wiraswasta','jumlah'=>4],
    ]],
    ['rt'=>'28', 'lk'=>'III', 'laki'=>210, 'perempuan'=>312, 'total'=>522, 'pekerjaan'=>[
        ['nama'=>'Buruh','jumlah'=>90],
        ['nama'=>'Wiraswasta','jumlah'=>12],
        ['nama'=>'PNS','jumlah'=>0],
        ['nama'=>'Serabutan','jumlah'=>20],
    ]],
];

$stmt->execute(['data_per_rt', json_encode($dataRT, JSON_UNESCAPED_UNICODE)]);
echo "✅ Data per RT (<strong>" . count($dataRT) . " RT</strong>) berhasil disimpan.<br>";

// ---- JENIS PEKERJAAN AGREGAT ----
$pekerjaan = [
    ['nama'=>'Buruh/Nelayan', 'jumlah'=>480],
    ['nama'=>'Wiraswasta',    'jumlah'=>70],
    ['nama'=>'Pedagang',      'jumlah'=>55],
    ['nama'=>'PNS',           'jumlah'=>6],
    ['nama'=>'Karyawan Swasta','jumlah'=>5],
    ['nama'=>'Serabutan/Lainnya','jumlah'=>43],
];
$stmt->execute(['jenis_pekerjaan', json_encode($pekerjaan, JSON_UNESCAPED_UNICODE)]);
echo "✅ Jenis Pekerjaan agregat berhasil disimpan.<br>";

echo "<hr><h3>🎉 Selesai! <a href='Pages/Statistik/statistik.php' target='_blank'>→ Lihat Halaman Statistik</a></h3>";
