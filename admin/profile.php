<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../config_db.php';

$pdo = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save_profile') {
    $visi = trim($_POST['visi'] ?? '');
    
    // Misi dikirim sebagai array dari input form dinamis (atau textarea multi-line)
    // Untuk mempermudah, kita gunakan textarea multi-line dimana tiap baris adalah satu misi.
    $misiLines = explode("\n", $_POST['misi'] ?? '');
    $misiArray = [];
    foreach ($misiLines as $line) {
        $l = trim($line);
        if ($l !== '') {
            // Hapus nomor di awal baris jika admin mengetiknya secara manual (contoh: "1. Misi abc")
            $l = preg_replace('/^\d+\.\s*/', '', $l);
            $misiArray[] = $l;
        }
    }
    
    $sejarah = trim($_POST['sejarah'] ?? '');

    $stmt = $pdo->prepare("INSERT INTO konten (key_name, key_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE key_value = VALUES(key_value)");
    
    $stmt->execute(['visi_teks', $visi]);
    $stmt->execute(['misi_teks', json_encode($misiArray, JSON_UNESCAPED_UNICODE)]);
    $stmt->execute(['sejarah_teks', $sejarah]);
    
    $_SESSION['flash'] = ['type' => 'success', 'message' => "Data Profile Kelurahan berhasil diperbarui."];
    header("Location: profile.php");
    exit;
}

// Ambil data yang ada
$stmt = $pdo->query("SELECT * FROM konten WHERE key_name IN ('visi_teks', 'misi_teks', 'sejarah_teks')");
$data = [];
while ($row = $stmt->fetch()) {
    $data[$row['key_name']] = $row['key_value'];
}

$visi = $data['visi_teks'] ?? '';
$misiArray = json_decode($data['misi_teks'] ?? '[]', true) ?: [];
$misiText = implode("\n", $misiArray);
$sejarah = $data['sejarah_teks'] ?? '';

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Profile Kelurahan | Admin <?= NAMA_KELURAHAN ?></title>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Sora:wght@600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
<link rel="stylesheet" href="../assets/css/admin.css">
<style>
.form-group textarea {
    width: 100%;
    padding: 12px;
    border: 1px solid var(--line);
    border-radius: var(--radius-sm);
    font-family: inherit;
    font-size: 14.5px;
    line-height: 1.6;
    resize: vertical;
}
.form-group input {
    width: 100%;
    padding: 12px;
    border: 1px solid var(--line);
    border-radius: var(--radius-sm);
    font-family: inherit;
    font-size: 14.5px;
}
.help-text {
    font-size: 12px;
    color: var(--ink-soft);
    margin-top: 6px;
    display: block;
}
</style>
</head>
<body>
<div class="admin-shell">
  <?php include __DIR__ . '/includes/sidebar.php'; ?>

  <main class="admin-main">
    <div class="admin-topbar">
      <div>
        <h1>Profile Kelurahan</h1>
        <p>Atur informasi Sejarah, Visi, dan Misi kelurahan untuk ditampilkan di situs publik.</p>
      </div>
    </div>

    <?php if ($flash): ?>
      <div class="alert alert-<?= $flash['type'] ?>"><?= $flash['message'] ?></div>
    <?php endif; ?>

    <div class="admin-card" style="max-width: 800px;">
        <form method="POST" action="">
            <input type="hidden" name="action" value="save_profile">
            
            <div class="form-group">
                <label>Visi Kelurahan</label>
                <input type="text" name="visi" value="<?= htmlspecialchars($visi) ?>" placeholder="Teks visi kelurahan..." required>
            </div>

            <div class="form-group">
                <label>Misi Kelurahan</label>
                <textarea name="misi" rows="6" placeholder="Tuliskan setiap misi di baris baru..." required><?= htmlspecialchars($misiText) ?></textarea>
                <span class="help-text">Tuliskan setiap misi pada baris yang berbeda (tekan Enter untuk misi baru). Nomor urut tidak perlu ditulis secara manual.</span>
            </div>

            <div class="form-group">
                <label>Sejarah Kelurahan</label>
                <textarea name="sejarah" rows="10" placeholder="Teks panjang sejarah kelurahan..."><?= htmlspecialchars($sejarah) ?></textarea>
                <span class="help-text">Anda bisa menggunakan tag HTML dasar seperti &lt;p&gt; atau &lt;br&gt; jika diperlukan.</span>
            </div>

            <div style="margin-top: 24px;">
                <button type="submit" class="btn btn-primary" style="padding: 12px 24px;"><i class="fas fa-save"></i> Simpan Profile</button>
            </div>
        </form>
    </div>

  </main>
</div>
</body>
</html>
