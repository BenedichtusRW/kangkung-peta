<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../config_db.php';
require_once __DIR__ . '/includes/functions.php'; // For handle_image_upload

$pdo = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'save_sejarah') {
        $sejarah = trim($_POST['sejarah'] ?? '');
        $stmt = $pdo->prepare("INSERT INTO konten (key_name, key_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE key_value = VALUES(key_value)");
        $stmt->execute(['sejarah_teks', $sejarah]);
        
        $_SESSION['flash'] = ['type' => 'success', 'message' => "Data Sejarah Kelurahan berhasil diperbarui."];
        header("Location: sejarah.php");
        exit;
    } elseif ($action === 'update_banner_sejarah') {
        $uploaded = handle_image_upload('banner_foto');
        if ($uploaded) {
            $stmt = $pdo->prepare("INSERT INTO settings (key_name, key_value) VALUES ('header_sejarah', ?) ON DUPLICATE KEY UPDATE key_value = VALUES(key_value)");
            $stmt->execute([$uploaded]);
            $_SESSION['flash'] = ['type' => 'success', 'message' => 'Foto banner Sejarah berhasil diperbarui.'];
        }
        header("Location: sejarah.php");
        exit;
    } elseif ($action === 'reset_banner_sejarah') {
        $stmt = $pdo->prepare("INSERT INTO settings (key_name, key_value) VALUES ('header_sejarah', '') ON DUPLICATE KEY UPDATE key_value = VALUES(key_value)");
        $stmt->execute();
        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Banner Sejarah dikembalikan ke background default.'];
        header("Location: sejarah.php");
        exit;
    }
}

// Ambil data yang ada
$stmt = $pdo->query("SELECT * FROM konten WHERE key_name = 'sejarah_teks'");
$data = [];
while ($row = $stmt->fetch()) {
    $data[$row['key_name']] = $row['key_value'];
}
$sejarah = $data['sejarah_teks'] ?? '';

// Ambil banner
$stmtBanner = $pdo->query("SELECT key_value FROM settings WHERE key_name = 'header_sejarah'");
$banner_sejarah = $stmtBanner->fetchColumn() ?: '';

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Sejarah Kelurahan | Admin <?= NAMA_KELURAHAN ?></title>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Sora:wght@600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
<link rel="stylesheet" href="../assets/css/admin.css?v=<?= time() ?>">
<style>
.form-group textarea {
    width: 100%; padding: 12px; border: 1px solid var(--line); border-radius: var(--radius-sm); font-family: inherit; font-size: 14.5px; line-height: 1.6; resize: vertical;
}
.help-text { font-size: 12px; color: var(--ink-soft); margin-top: 6px; display: block; }
</style>
<link rel="icon" type="image/png" href="../assets/img/favicon.png?v=2">
</head>
<body>
<div class="admin-shell">
  <?php include __DIR__ . '/includes/sidebar.php'; ?>

  <main class="admin-main">
    <div class="admin-topbar">
      <div>
        <h1>Sejarah Kelurahan</h1>
        <p>Atur informasi sejarah dan foto banner halaman sejarah.</p>
      </div>
    </div>

    <?php if ($flash): ?>
      <div class="alert alert-<?= $flash['type'] ?>"><?= $flash['message'] ?></div>
    <?php endif; ?>

    <!-- Banner Header -->
    <div class="section-card" style="margin-bottom: 24px;">
        <div style="display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 16px;">
            <div>
                <h2 style="font-size: 1.1rem; margin: 0 0 4px 0;"><i class="fa-solid fa-image" style="color: var(--teal-600);"></i> Banner Header Sejarah</h2>
                <p style="margin: 0; font-size: 0.85rem; color: var(--ink-soft);">Ganti foto latar belakang header pada halaman Sejarah publik.</p>
            </div>
        </div>

        <div style="margin-top: 16px; display: flex; flex-wrap: wrap; gap: 16px; align-items: stretch;">
            <div style="flex: 1; min-width: 260px; aspect-ratio: 4 / 1; border-radius: 12px; overflow: hidden; background: #ecfdf5; border: 1px solid #e2e8f0; position: relative;">
                <?php if (!empty($banner_sejarah)): ?>
                    <img src="../<?= htmlspecialchars($banner_sejarah) ?>" style="width: 100%; height: 100%; object-fit: cover;" alt="Banner Sejarah">
                <?php else: ?>
                    <div style="height: 100%; display: flex; align-items: center; justify-content: center; color: #059669; font-weight: 600; font-size: 0.85rem; border: 2px dashed #a7f3d0;"><i class="fa-solid fa-check-circle" style="margin-right: 6px;"></i> Background Default (Hijau Kangkung)</div>
                <?php endif; ?>
            </div>

            <div style="display: flex; flex-direction: column; gap: 10px;">
                <form method="POST" enctype="multipart/form-data" style="margin:0;">
                    <input type="hidden" name="action" value="update_banner_sejarah">
                    <label class="btn btn-primary" style="cursor: pointer; display: inline-flex; align-items: center; gap: 8px; width: 100%; justify-content: center;">
                        <i class="fa-solid fa-camera"></i> Ganti Banner
                        <input type="file" name="banner_foto" accept=".jpg,.jpeg,.png,.webp" required class="cropper-upload-input" data-aspect-ratio="4/1" style="display: none;">
                    </label>
                </form>
                <?php if (!empty($banner_sejarah)): ?>
                <form method="POST" style="margin:0;">
                    <input type="hidden" name="action" value="reset_banner_sejarah">
                    <button type="submit" class="btn btn-outline" style="color: #ef4444; border-color: #fca5a5; background: #fee2e2; width: 100%;">
                        <i class="fa-solid fa-rotate-left"></i> Reset ke Default
                    </button>
                </form>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Teks Sejarah -->
    <div class="admin-card">
        <form method="POST" action="">
            <input type="hidden" name="action" value="save_sejarah">
            
            <div class="form-group">
                <label>Sejarah Kelurahan</label>
                <textarea name="sejarah" rows="15" placeholder="Teks panjang sejarah kelurahan..."><?= htmlspecialchars($sejarah) ?></textarea>
                <span class="help-text">Anda bisa menggunakan tag HTML dasar seperti &lt;p&gt; atau &lt;br&gt; jika diperlukan.</span>
            </div>

            <div style="margin-top: 24px;">
                <button type="submit" class="btn btn-primary" style="padding: 12px 24px;"><i class="fas fa-save"></i> Simpan Teks Sejarah</button>
            </div>
        </form>
    </div>

  </main>
</div>
<?php include __DIR__ . '/includes/cropper_modal.php'; ?>
</body>
</html>
