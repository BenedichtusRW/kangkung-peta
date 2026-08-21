<?php
/**
 * api/get_pois.php
 * Endpoint API sederhana (native PHP) untuk data titik lokasi Peta Kelurahan Kangkung.
 *
 * Saat ini data diambil dari file JSON (data/pois.json) supaya mudah diedit
 * tanpa database. Kalau nanti mau pindah ke MySQL, tinggal ganti bagian
 * "AMBIL DATA" di bawah dengan query PDO/MySQLi lalu tetap return array
 * asosiatif dengan struktur yang sama.
 *
 * Query string yang didukung:
 *   ?kategori=kuliner   -> filter berdasarkan kategori (kosong/"semua" = semua)
 *   ?q=warung           -> cari berdasarkan nama/alamat/deskripsi
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../config_db.php';

try {
    $pdo = getDB();
    $kategori = isset($_GET['kategori']) ? strtolower(trim($_GET['kategori'])) : '';
    $q        = isset($_GET['q']) ? trim($_GET['q']) : '';

    $sql = "SELECT * FROM pois WHERE 1=1";
    $params = [];

    if ($kategori !== '' && $kategori !== 'semua') {
        $sql .= " AND LOWER(kategori) = ?";
        $params[] = $kategori;
    }

    if ($q !== '') {
        $sql .= " AND (LOWER(nama) LIKE ? OR LOWER(alamat) LIKE ? OR LOWER(deskripsi) LIKE ?)";
        $q_param = '%' . strtolower($q) . '%';
        $params[] = $q_param;
        $params[] = $q_param;
        $params[] = $q_param;
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $pois = $stmt->fetchAll();
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
    exit;
}

// ================= OUTPUT =================
echo json_encode([
    'success' => true,
    'total'   => count($pois),
    'data'    => $pois,
]);
