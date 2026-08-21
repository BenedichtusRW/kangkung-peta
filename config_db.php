<?php
// Konfigurasi Database Utama
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', ''); // Sesuaikan jika menggunakan password (contoh MAMP: 'root')
define('DB_NAME', 'db_kangkung_peta');

// Fungsi koneksi PDO agar bisa di-reuse
function getDB() {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = "mysql:host=" . DB_HOST . ";charset=utf8mb4";
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
            
            // Pilih database setelah koneksi (mengizinkan koneksi awal tanpa DB untuk setup)
            $pdo->exec("USE `" . DB_NAME . "`");
        } catch (PDOException $e) {
            // Jika DB belum ada (biasanya saat pertama kali setup), tidak apa-apa jika belum di-USE.
            // Setup script akan menghandlenya.
        }
    }
    return $pdo;
}
