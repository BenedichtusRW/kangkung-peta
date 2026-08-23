<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../config_db.php';
require_once __DIR__ . '/includes/functions.php'; // For handle_image_upload

$pdo = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'save_visi_misi') {
        $visi = trim($_POST['visi'] ?? '');
        
        $misiLines = explode("\n", $_POST['misi'] ?? '');
        $misiArray = [];
        foreach ($misiLines as $line) {
            $l = trim($line);
            if ($l !== '') {
                $l = preg_replace('/^\d+\.\s*/', '', $l);
                $misiArray[] = $l;
            }
        }
        
        $stmt = $pdo->prepare("INSERT INTO konten (key_name, key_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE key_value = VALUES(key_value)");
        
        $stmt->execute(['visi_teks', $visi]);
        $stmt->execute(['misi_teks', json_encode($misiArray, JSON_UNESCAPED_UNICODE)]);
        
        $_SESSION['flash'] = ['type' => 'success', 'message' => "Data Visi dan Misi Kelurahan berhasil diperbarui."];
        header("Location: visi-misi.php");
        exit;
    } elseif ($action === 'update_banner_visi_misi') {
        $uploaded = handle_image_upload('banner_foto');
        if ($uploaded) {
            $stmt = $pdo->prepare("INSERT INTO settings (key_name, key_value) VALUES ('header_visi_misi', ?) ON DUPLICATE KEY UPDATE key_value = VALUES(key_value)");
            $stmt->execute([$uploaded]);
            $_SESSION['flash'] = ['type' => 'success', 'message' => 'Foto banner Visi Misi berhasil diperbarui.'];
        }
        header("Location: visi-misi.php");
        exit;
    } elseif ($action === 'reset_banner_visi_misi') {
        $stmt = $pdo->prepare("INSERT INTO settings (key_name, key_value) VALUES ('header_visi_misi', '') ON DUPLICATE KEY UPDATE key_value = VALUES(key_value)");
        $stmt->execute();
        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Banner Visi Misi dikembalikan ke background default.'];
        header("Location: visi-misi.php");
        exit;
    }
}

// Ambil data yang ada
$stmt = $pdo->query("SELECT * FROM konten WHERE key_name IN ('visi_teks', 'misi_teks')");
$data = [];
while ($row = $stmt->fetch()) {
    $data[$row['key_name']] = $row['key_value'];
}
$visi = $data['visi_teks'] ?? '';
$misiArray = json_decode($data['misi_teks'] ?? '[]', true) ?: [];
$misiText = implode("\n", $misiArray);

// Ambil banner
$stmtBanner = $pdo->query("SELECT key_value FROM settings WHERE key_name = 'header_visi_misi'");
$banner_visi_misi = $stmtBanner->fetchColumn() ?: '';

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Visi Misi Kelurahan | Admin <?= NAMA_KELURAHAN ?></title>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Sora:wght@600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
<link rel="stylesheet" href="../assets/css/admin.css?v=<?= time() ?>">
<style>
.form-group textarea {
    width: 100%; padding: 12px; border: 1px solid var(--line); border-radius: var(--radius-sm); font-family: inherit; font-size: 14.5px; line-height: 1.6; resize: vertical;
}
.form-group input {
    width: 100%; padding: 12px; border: 1px solid var(--line); border-radius: var(--radius-sm); font-family: inherit; font-size: 14.5px;
}
.help-text { font-size: 12px; color: var(--ink-soft); margin-top: 6px; display: block; }
</style>
</head>
<body>
<div class="admin-shell">
  <?php include __DIR__ . '/includes/sidebar.php'; ?>

  <main class="admin-main">
    <div class="admin-topbar">
      <div>
        <h1>Visi Misi Kelurahan</h1>
        <p>Atur informasi visi misi dan foto banner halaman visi misi.</p>
      </div>
    </div>

    <?php if ($flash): ?>
      <div class="alert alert-<?= $flash['type'] ?>"><?= $flash['message'] ?></div>
    <?php endif; ?>

    <!-- Banner Header -->
    <div class="section-card" style="margin-bottom: 24px; max-width: 800px;">
        <div style="display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 16px;">
            <div>
                <h2 style="font-size: 1.1rem; margin: 0 0 4px 0;"><i class="fa-solid fa-image" style="color: var(--teal-600);"></i> Banner Header Visi Misi</h2>
                <p style="margin: 0; font-size: 0.85rem; color: var(--ink-soft);">Ganti foto latar belakang header pada halaman Visi Misi publik.</p>
            </div>
        </div>

        <div style="margin-top: 16px; display: flex; flex-wrap: wrap; gap: 16px; align-items: center;">
            <div style="flex: 1; min-width: 260px; height: 130px; border-radius: 12px; overflow: hidden; background: #ecfdf5; border: 1px solid #e2e8f0; position: relative;">
                <?php if (!empty($banner_visi_misi)): ?>
                    <img src="../<?= htmlspecialchars($banner_visi_misi) ?>" style="width: 100%; height: 100%; object-fit: cover;" alt="Banner Visi Misi">
                <?php else: ?>
                    <div style="height: 100%; display: flex; align-items: center; justify-content: center; color: #059669; font-weight: 600; font-size: 0.85rem; border: 2px dashed #a7f3d0;"><i class="fa-solid fa-check-circle" style="margin-right: 6px;"></i> Background Default (Hijau Kangkung)</div>
                <?php endif; ?>
            </div>

            <div style="display: flex; flex-direction: column; gap: 10px;">
                <form method="POST" enctype="multipart/form-data" style="margin:0;">
                    <input type="hidden" name="action" value="update_banner_visi_misi">
                    <label class="btn btn-primary" style="cursor: pointer; display: inline-flex; align-items: center; gap: 8px; width: 100%; justify-content: center;">
                        <i class="fa-solid fa-camera"></i> Ganti Banner
                        <input type="file" name="banner_foto" accept=".jpg,.jpeg,.png,.webp" required onchange="this.form.submit()" style="display: none;">
                    </label>
                </form>
                <?php if (!empty($banner_visi_misi)): ?>
                <form method="POST" style="margin:0;">
                    <input type="hidden" name="action" value="reset_banner_visi_misi">
                    <button type="submit" class="btn btn-outline" style="color: #ef4444; border-color: #fca5a5; background: #fee2e2; width: 100%;">
                        <i class="fa-solid fa-rotate-left"></i> Reset ke Default
                    </button>
                </form>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Teks Visi Misi -->
    <div class="admin-card" style="max-width: 800px;">
        <form method="POST" action="">
            <input type="hidden" name="action" value="save_visi_misi">
            
            <div class="form-group">
                <label>Visi Kelurahan</label>
                <input type="text" name="visi" value="<?= htmlspecialchars($visi) ?>" placeholder="Teks visi kelurahan..." required>
            </div>

            <div class="form-group" style="margin-top: 16px;">
                <label>Misi Kelurahan</label>
                <textarea name="misi" rows="6" placeholder="Tuliskan setiap misi di baris baru..." required><?= htmlspecialchars($misiText) ?></textarea>
                <span class="help-text">Tuliskan setiap misi pada baris yang berbeda (tekan Enter untuk misi baru). Nomor urut tidak perlu ditulis secara manual.</span>
            </div>

            <div style="margin-top: 24px;">
                <button type="submit" class="btn btn-primary" style="padding: 12px 24px;"><i class="fas fa-save"></i> Simpan Visi Misi</button>
            </div>
        </form>
    </div>

  </main>
</div>
</body>
</html>
