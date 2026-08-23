<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../config_db.php';
require_once __DIR__ . '/includes/functions.php';

$pdo = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'update_banner_chatbot') {
        $uploaded = handle_image_upload('banner_foto');
        if ($uploaded) {
            $stmt = $pdo->prepare("INSERT INTO settings (key_name, key_value) VALUES ('header_chatbot', ?) ON DUPLICATE KEY UPDATE key_value = VALUES(key_value)");
            $stmt->execute([$uploaded]);
            $_SESSION['flash'] = ['type' => 'success', 'message' => 'Foto banner Chatbot berhasil diperbarui.'];
        }
        header("Location: chatbot.php");
        exit;
    } elseif ($action === 'reset_banner_chatbot') {
        $stmt = $pdo->prepare("INSERT INTO settings (key_name, key_value) VALUES ('header_chatbot', '') ON DUPLICATE KEY UPDATE key_value = VALUES(key_value)");
        $stmt->execute();
        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Banner Chatbot dikembalikan ke background default.'];
        header("Location: chatbot.php");
        exit;
    } elseif ($action === 'save_chatbot_settings') {
        $welcome_msg = trim($_POST['welcome_message'] ?? '');
        $stmt = $pdo->prepare("INSERT INTO settings (key_name, key_value) VALUES ('chatbot_welcome_message', ?) ON DUPLICATE KEY UPDATE key_value = VALUES(key_value)");
        $stmt->execute([$welcome_msg]);
        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Pengaturan Chatbot berhasil disimpan.'];
        header("Location: chatbot.php");
        exit;
    }
}

// Ambil data pengaturan chatbot
$stmtBanner = $pdo->query("SELECT key_value FROM settings WHERE key_name = 'header_chatbot'");
$banner_chatbot = $stmtBanner->fetchColumn() ?: '';

$stmtWelcome = $pdo->query("SELECT key_value FROM settings WHERE key_name = 'chatbot_welcome_message'");
$welcome_message = $stmtWelcome->fetchColumn();
if (!$welcome_message) {
    $welcome_message = "Tabik Pun! Selamat datang di Pusat Informasi Kelurahan " . (defined('NAMA_KELURAHAN') ? NAMA_KELURAHAN : 'Kangkung') . ". Saya adalah Asisten Cerdas yang siap membantu Bapak/Ibu. Ingin tahu soal surat pengantar, data penduduk, atau nama aparatur kami? 👇";
}

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Pengaturan Chatbot | <?= NAMA_KELURAHAN ?></title>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Sora:wght@600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
<link rel="stylesheet" href="../assets/css/admin.css?v=<?= time() ?>">
<style>
textarea { width: 100%; padding: 10px; border: 1px solid var(--line); border-radius: 6px; font-family: inherit; font-size: 14px; }
</style>
</head>
<body>
<div class="admin-shell">
  <?php include __DIR__ . '/includes/sidebar.php'; ?>

  <main class="admin-main">
    <div class="admin-topbar">
      <div>
        <h1>Pengaturan Chatbot</h1>
        <p>Atur tampilan banner dan pesan pembuka (welcome message) untuk halaman Chatbot AI.</p>
      </div>
    </div>

    <?php if ($flash): ?>
      <div class="alert alert-<?= $flash['type'] ?>"><?= htmlspecialchars($flash['message']) ?></div>
    <?php endif; ?>

    <!-- Banner Header -->
    <div class="section-card" style="margin-bottom: 24px;">
        <div style="display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 16px;">
            <div>
                <h2 style="font-size: 1.1rem; margin: 0 0 4px 0;"><i class="fa-solid fa-image" style="color: var(--teal-600);"></i> Banner Header Chatbot</h2>
                <p style="margin: 0; font-size: 0.85rem; color: var(--ink-soft);">Ganti foto latar belakang header pada halaman Chatbot publik.</p>
            </div>
        </div>

        <div style="margin-top: 16px; display: flex; flex-wrap: wrap; gap: 16px; align-items: stretch;">
            <div style="flex: 1; min-width: 260px; aspect-ratio: 4 / 1; border-radius: 12px; overflow: hidden; background: #ecfdf5; border: 1px solid #e2e8f0; position: relative;">
                <?php if (!empty($banner_chatbot)): ?>
                    <img src="../<?= htmlspecialchars($banner_chatbot) ?>" style="width: 100%; height: 100%; object-fit: cover;" alt="Banner Chatbot">
                <?php else: ?>
                    <div style="height: 100%; display: flex; align-items: center; justify-content: center; color: #059669; font-weight: 600; font-size: 0.85rem; border: 2px dashed #a7f3d0;"><i class="fa-solid fa-check-circle" style="margin-right: 6px;"></i> Background Default (Hijau Kangkung)</div>
                <?php endif; ?>
            </div>

            <div style="display: flex; flex-direction: column; gap: 10px;">
                <form method="POST" enctype="multipart/form-data" style="margin:0;">
                    <input type="hidden" name="action" value="update_banner_chatbot">
                    <label class="btn btn-primary" style="cursor: pointer; display: inline-flex; align-items: center; gap: 8px; width: 100%; justify-content: center;">
                        <i class="fa-solid fa-camera"></i> Ganti Banner
                        <input type="file" name="banner_foto" accept=".jpg,.jpeg,.png,.webp" required class="cropper-upload-input" data-aspect-ratio="4/1" style="display: none;">
                    </label>
                </form>
                <?php if (!empty($banner_chatbot)): ?>
                <form method="POST" style="margin:0;">
                    <input type="hidden" name="action" value="reset_banner_chatbot">
                    <button type="submit" class="btn btn-outline" style="color: #ef4444; border-color: #fca5a5; background: #fee2e2; width: 100%;">
                        <i class="fa-solid fa-rotate-left"></i> Reset ke Default
                    </button>
                </form>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Teks Welcome -->
    <div class="admin-card">
        <form method="POST" action="">
            <input type="hidden" name="action" value="save_chatbot_settings">
            
            <div class="form-group" style="margin-bottom: 24px;">
                <label style="font-weight: 600; display: block; margin-bottom: 8px;"><i class="fa-regular fa-comment-dots"></i> Pesan Pembuka (Welcome Message)</label>
                <textarea name="welcome_message" rows="4" placeholder="Tulis pesan pembuka bot di sini..."><?= htmlspecialchars($welcome_message) ?></textarea>
                <span class="help-text">Pesan ini akan muncul pertama kali saat warga membuka halaman Chatbot.</span>
            </div>

            <div style="margin-top: 24px;">
                <button type="submit" class="btn btn-primary" style="padding: 12px 24px;"><i class="fas fa-save"></i> Simpan Pengaturan</button>
            </div>
        </form>
    </div>

  </main>
</div>
<?php include __DIR__ . '/includes/cropper_modal.php'; ?>
</body>
</html>
