<?php
require_once __DIR__ . '/config_db.php';
// Need config.php for initial ADMIN_USERNAME and ADMIN_PASSWORD_HASH if defined
@include_once __DIR__ . '/config.php';

try {
    // 1. Connect without database selected to create it
    $dsn = "mysql:host=" . DB_HOST . ";charset=utf8mb4";
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE `" . DB_NAME . "`");
    echo "✅ Database `" . DB_NAME . "` berhasil dipastikan ada.<br>";

    // 2. Create tables
    $tables = [
        "CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) NOT NULL UNIQUE,
            password_hash VARCHAR(255) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )",
        "CREATE TABLE IF NOT EXISTS pois (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nama VARCHAR(255) NOT NULL,
            kategori VARCHAR(50) NOT NULL,
            deskripsi TEXT,
            alamat TEXT,
            kontak VARCHAR(50),
            jam_buka VARCHAR(50),
            lat DOUBLE NOT NULL,
            lng DOUBLE NOT NULL,
            gambar TEXT
        )",
        "CREATE TABLE IF NOT EXISTS aparatur (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nama VARCHAR(100) NOT NULL,
            jabatan VARCHAR(100) NOT NULL,
            foto TEXT
        )",
        "CREATE TABLE IF NOT EXISTS tim_kkn (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nama VARCHAR(100) NOT NULL,
            jabatan VARCHAR(100) NOT NULL,
            foto TEXT
        )",
        "CREATE TABLE IF NOT EXISTS galeri (
            id INT AUTO_INCREMENT PRIMARY KEY,
            judul VARCHAR(255) NOT NULL,
            kategori VARCHAR(50),
            gambar TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )",
        "CREATE TABLE IF NOT EXISTS berita (
            id INT AUTO_INCREMENT PRIMARY KEY,
            slug VARCHAR(255) NOT NULL UNIQUE,
            judul VARCHAR(255) NOT NULL,
            ringkasan TEXT,
            konten TEXT,
            gambar TEXT,
            penulis VARCHAR(100),
            tanggal DATE,
            status ENUM('published', 'pending', 'rejected') DEFAULT 'published',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )",
        "CREATE TABLE IF NOT EXISTS statistik (
            key_name VARCHAR(100) PRIMARY KEY,
            key_value TEXT
        )",
        "CREATE TABLE IF NOT EXISTS konten (
            key_name VARCHAR(100) PRIMARY KEY,
            key_value TEXT
        )",
        "CREATE TABLE IF NOT EXISTS settings (
            key_name VARCHAR(100) PRIMARY KEY,
            key_value TEXT
        )",
        "CREATE TABLE IF NOT EXISTS chatbot_qa (
            id INT AUTO_INCREMENT PRIMARY KEY,
            kata_kunci TEXT NOT NULL,
            jawaban TEXT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )"
    ];

    foreach ($tables as $sql) {
        $pdo->exec($sql);
    }
    echo "✅ Tabel-tabel berhasil dibuat.<br>";

    // 3. Migrate Default Admin
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
    $adminUsername = defined('ADMIN_USERNAME') ? ADMIN_USERNAME : 'admin';
    $adminPasswordHash = defined('ADMIN_PASSWORD_HASH') ? ADMIN_PASSWORD_HASH : password_hash('admin123', PASSWORD_DEFAULT);
    
    $stmt->execute([$adminUsername]);
    if ($stmt->fetchColumn() == 0) {
        $pdo->prepare("INSERT INTO users (username, password_hash) VALUES (?, ?)")
            ->execute([$adminUsername, $adminPasswordHash]);
        echo "✅ Admin user ($adminUsername) berhasil di-migrate.<br>";
    }

    // Fungsi bantu baca JSON
    function read_json($file) {
        if (!file_exists($file)) return [];
        $data = json_decode(file_get_contents($file), true);
        return is_array($data) ? $data : [];
    }

    // 4. Migrate POIs
    $pois = read_json(__DIR__ . '/data/pois.json');
    if (!empty($pois)) {
        $pdo->exec("TRUNCATE TABLE pois");
        $stmt = $pdo->prepare("INSERT INTO pois (id, nama, kategori, deskripsi, alamat, kontak, jam_buka, lat, lng, gambar) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        foreach ($pois as $p) {
            $stmt->execute([
                $p['id'], $p['nama'], $p['kategori'], $p['deskripsi'] ?? '', $p['alamat'] ?? '', 
                $p['kontak'] ?? '', $p['jam_buka'] ?? '', $p['lat'], $p['lng'], $p['gambar'] ?? ''
            ]);
        }
        echo "✅ " . count($pois) . " POIs berhasil di-migrate.<br>";
    }

    // 5. Migrate Aparatur
    $aparatur = read_json(__DIR__ . '/data/aparatur.json');
    if (!empty($aparatur)) {
        $pdo->exec("TRUNCATE TABLE aparatur");
        $stmt = $pdo->prepare("INSERT INTO aparatur (id, nama, jabatan, foto) VALUES (?, ?, ?, ?)");
        foreach ($aparatur as $a) {
            $stmt->execute([$a['id'], $a['nama'], $a['jabatan'], $a['foto'] ?? '']);
        }
        echo "✅ " . count($aparatur) . " Aparatur berhasil di-migrate.<br>";
    }

    // 6. Migrate Tim KKN
    $tim_kkn = read_json(__DIR__ . '/data/tim-kkn.json');
    if (!empty($tim_kkn)) {
        $pdo->exec("TRUNCATE TABLE tim_kkn");
        $stmt = $pdo->prepare("INSERT INTO tim_kkn (id, nama, jabatan, foto) VALUES (?, ?, ?, ?)");
        foreach ($tim_kkn as $t) {
            $stmt->execute([$t['id'], $t['nama'], $t['jabatan'], $t['foto'] ?? '']);
        }
        echo "✅ " . count($tim_kkn) . " Tim KKN berhasil di-migrate.<br>";
    }

    // 6.5 Migrate Chatbot QA
    $chatbot_qa = read_json(__DIR__ . '/data/chatbot_qa.json');
    if (!empty($chatbot_qa)) {
        $pdo->exec("TRUNCATE TABLE chatbot_qa");
        $stmt = $pdo->prepare("INSERT INTO chatbot_qa (kata_kunci, jawaban) VALUES (?, ?)");
        foreach ($chatbot_qa as $qa) {
            $stmt->execute([$qa['kata_kunci'], $qa['jawaban']]);
        }
        echo "✅ " . count($chatbot_qa) . " Chatbot Q&A berhasil di-migrate.<br>";
    }

    // 7. Migrate Galeri
    $galeri = read_json(__DIR__ . '/data/galeri.json');
    if (!empty($galeri)) {
        $pdo->exec("TRUNCATE TABLE galeri");
        $stmt = $pdo->prepare("INSERT INTO galeri (id, judul, kategori, gambar) VALUES (?, ?, ?, ?)");
        foreach ($galeri as $g) {
            $stmt->execute([$g['id'], $g['judul'], $g['kategori'] ?? '', $g['gambar'] ?? '']);
        }
        echo "✅ " . count($galeri) . " Galeri berhasil di-migrate.<br>";
    }

    // 8. Migrate Berita
    $berita = read_json(__DIR__ . '/data/berita.json');
    $pdo->exec("TRUNCATE TABLE berita");
    $stmt = $pdo->prepare("INSERT INTO berita (id, slug, judul, ringkasan, konten, gambar, penulis, tanggal, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'published')");
    foreach ($berita as $b) {
        $stmt->execute([
            $b['id'], $b['slug'], $b['judul'], $b['ringkasan'] ?? '', $b['konten'] ?? '',
            $b['gambar'] ?? '', $b['penulis'] ?? '', $b['tanggal'] ?? date('Y-m-d')
        ]);
    }
    
    // Migrate Pengajuan Berita (Pending)
    $pengajuan = read_json(__DIR__ . '/data/pengajuan_berita.json');
    if (!empty($pengajuan)) {
        $stmt = $pdo->prepare("INSERT INTO berita (slug, judul, ringkasan, konten, gambar, penulis, tanggal, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'pending')");
        foreach ($pengajuan as $p) {
            $stmt->execute([
                $p['slug'] ?? uniqid('berita-'), $p['judul'], $p['ringkasan'] ?? '', $p['konten'] ?? '',
                $p['gambar'] ?? '', $p['penulis'] ?? '', $p['tanggal'] ?? date('Y-m-d')
            ]);
        }
    }
    echo "✅ " . (count($berita) + count($pengajuan)) . " Berita berhasil di-migrate.<br>";

    // 9. Migrate Statistik
    $statistik = json_decode(@file_get_contents(__DIR__ . '/data/statistik.json'), true);
    if (!empty($statistik)) {
        $pdo->exec("TRUNCATE TABLE statistik");
        $stmt = $pdo->prepare("INSERT INTO statistik (key_name, key_value) VALUES (?, ?)");
        foreach ($statistik as $key => $value) {
            $valStr = is_array($value) ? json_encode($value) : (string)$value;
            $stmt->execute([$key, $valStr]);
        }
        echo "✅ Statistik berhasil di-migrate.<br>";
    }
    
    // 10. Migrate Settings
    $settings = json_decode(@file_get_contents(__DIR__ . '/data/settings.json'), true);
    if (!empty($settings)) {
        $pdo->exec("TRUNCATE TABLE settings");
        $stmt = $pdo->prepare("INSERT INTO settings (key_name, key_value) VALUES (?, ?)");
        foreach ($settings as $key => $value) {
            $valStr = is_array($value) ? json_encode($value) : (string)$value;
            $stmt->execute([$key, $valStr]);
        }
        echo "✅ Settings berhasil di-migrate.<br>";
    }

    echo "<h3>🎉 Migrasi ke MySQL Selesai!</h3>";
    echo "Sekarang Anda dapat melanjutkan pengeditan file PHP untuk membaca dari database.";

} catch (PDOException $e) {
    die("❌ Error Setup Database: " . $e->getMessage());
}
