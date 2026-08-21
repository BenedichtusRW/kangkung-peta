<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/../config_db.php';
require_once __DIR__ . '/includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['id'])) {
    header('Location: peta.php');
    exit;
}

$id = (int) $_POST['id'];
$pdo = getDB();

$stmt = $pdo->prepare("SELECT * FROM pois WHERE id = ?");
$stmt->execute([$id]);
$target = $stmt->fetch();

if (!$target) {
    $_SESSION['flash'] = ['type' => 'error', 'message' => 'Data tidak ditemukan.'];
    header('Location: peta.php');
    exit;
}

$stmt = $pdo->prepare("DELETE FROM pois WHERE id = ?");
if ($stmt->execute([$id])) {
    if (!empty($target['gambar']) && strpos($target['gambar'], 'http') !== 0) {
        $imgPath = __DIR__ . '/../../' . $target['gambar'];
        if (file_exists($imgPath)) {
            @unlink($imgPath);
        }
    }
    $_SESSION['flash'] = ['type' => 'success', 'message' => 'Data berhasil dihapus.'];
} else {
    $_SESSION['flash'] = ['type' => 'error', 'message' => 'Gagal menghapus data dari database.'];
}

header('Location: peta.php');
exit;
