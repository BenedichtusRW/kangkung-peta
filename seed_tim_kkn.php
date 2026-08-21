<?php
/**
 * seed_tim_kkn.php — Seed 21 anggota Tim KKN UIN RIL Kelompok 31
 * Jalankan sekali: http://localhost:8000/seed_tim_kkn.php
 */
require_once __DIR__ . '/config_db.php';
$pdo = getDB();

echo "<style>body{font-family:sans-serif;padding:20px; max-width:700px;}</style>";
echo "<h2>Seeding Tim KKN UIN RIL Kelompok 31...</h2>";

// Kosongkan dulu
$pdo->exec("TRUNCATE TABLE tim_kkn");

$anggota = [
    // Pengurus Inti
    ['nama' => 'Ridho Hasiholan',          'jabatan' => 'Ketua KKN',               'jurusan' => 'Manajemen Bisnis Syariah'],
    ['nama' => 'Aiman Dhafa Haris Putra',  'jabatan' => 'Wakil Ketua',             'jurusan' => 'Hukum Tata Negara'],
    ['nama' => 'Anggun Melsa Fitrianti',   'jabatan' => 'Sekretaris',              'jurusan' => 'Ekonomi Syariah'],
    ['nama' => 'Nesa Firnanda Sakinah',    'jabatan' => 'Sekretaris',              'jurusan' => 'Akuntansi Syariah'],
    ['nama' => 'Anisah Okta Rahmawati',    'jabatan' => 'Bendahara',               'jurusan' => 'Ekonomi Syariah'],
    ['nama' => 'Neng Tia Permata S',       'jabatan' => 'Bendahara',               'jurusan' => 'Akuntansi Syariah'],
    // Divisi Acara
    ['nama' => 'Rara Dwika Arjuna',        'jabatan' => 'Divisi Acara',            'jurusan' => 'Ilmu Perpustakaan dan Informasi Islam'],
    ['nama' => 'Annisa Wahidaturrohmah',   'jabatan' => 'Divisi Acara',            'jurusan' => 'Sosiologi Agama'],
    ['nama' => 'Yamlikha Rayna',           'jabatan' => 'Divisi Acara',            'jurusan' => 'Hukum Tata Negara'],
    // Divisi PDDM
    ['nama' => 'Hera Silvia',              'jabatan' => 'Divisi PDDM',             'jurusan' => 'Tasawuf dan Psikoterapi'],
    ['nama' => 'Abdur Rahman',             'jabatan' => 'Divisi PDDM',             'jurusan' => 'Hukum Keluarga'],
    ['nama' => 'Nayyara Alya F.M',         'jabatan' => 'Divisi PDDM',             'jurusan' => 'Bimbingan dan Konseling Islam'],
    // Divisi Konsumsi
    ['nama' => 'Hevin Indyana Bulqish',    'jabatan' => 'Divisi Konsumsi',         'jurusan' => 'Hukum Keluarga'],
    ['nama' => 'Nanda Ega Desvita',        'jabatan' => 'Divisi Konsumsi',         'jurusan' => 'Bimbingan dan Konseling Islam'],
    ['nama' => 'Sinta Marsila',            'jabatan' => 'Divisi Konsumsi',         'jurusan' => 'Manajemen Bisnis Syariah'],
    // Divisi Perlengkapan
    ['nama' => 'Elia Abigail',             'jabatan' => 'Divisi Perlengkapan',     'jurusan' => 'Ilmu Al-Qur\'an dan Tafsir'],
    ['nama' => 'Ridho Ady Prayoga',        'jabatan' => 'Divisi Perlengkapan',     'jurusan' => 'Psikologi Islam'],
    // Humas
    ['nama' => 'Nurul Yuliana Awalin',     'jabatan' => 'Divisi Humas',            'jurusan' => 'Hukum Tata Negara'],
    ['nama' => 'Rahmat Mustopa',           'jabatan' => 'Divisi Humas',            'jurusan' => 'Manajemen Bisnis Syariah'],
    ['nama' => 'Zahra Mutiara',            'jabatan' => 'Divisi Humas',            'jurusan' => 'Sistem Informasi'],
    // Dosen Pembimbing
    ['nama' => 'Lis Yulitasari',           'jabatan' => 'Dosen Pembimbing Lapangan','jurusan' => ''],
];

$stmt = $pdo->prepare("INSERT INTO tim_kkn (nama, jabatan, foto) VALUES (?, ?, ?)");
$count = 0;
foreach ($anggota as $a) {
    $jabatanFull = $a['jabatan'] . ($a['jurusan'] ? ' — ' . $a['jurusan'] : '');
    $stmt->execute([$a['nama'], $jabatanFull, '']);
    $count++;
    echo "✅ <strong>{$a['nama']}</strong> — {$a['jabatan']}<br>";
}

echo "<hr><h3>🎉 $count anggota berhasil di-seed!</h3>";
echo "<p>Foto dapat diupload satu per satu melalui halaman <a href='admin/tim-kkn.php' target='_blank'><strong>Admin → Tim KKN</strong></a> dengan tombol edit (ikon pensil) di masing-masing kartu.</p>";
echo "<p><a href='Pages/Tim-KKN/tim-kkn.php' target='_blank'>→ Lihat halaman Tim KKN publik</a></p>";
