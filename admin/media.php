<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../config_db.php';
require_once __DIR__ . '/includes/functions.php';

$pdo = getDB();

// ==========================================
// 1. Ambil Data Settings (Header & Slider)
// ==========================================
$stmt = $pdo->query("SELECT * FROM settings");
$settings = [];
while ($row = $stmt->fetch()) {
    $val = json_decode($row['key_value'], true);
    $settings[$row['key_name']] = (json_last_error() == JSON_ERROR_NONE && !is_null($val)) ? $val : $row['key_value'];
}
if (!isset($settings['header_image'])) $settings['header_image'] = '';
if (!isset($settings['slider_images'])) $settings['slider_images'] = [];

function updateSetting($pdo, $key, $value) {
    $valStr = is_array($value) ? json_encode($value) : (string)$value;
    $stmt = $pdo->prepare("INSERT INTO settings (key_name, key_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE key_value = VALUES(key_value)");
    $stmt->execute([$key, $valStr]);
}

// ==========================================
// 3. Tangani Request POST (Upload/Hapus)
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    // --- Header ---
    if ($action === 'update_header') {
        $page = $_POST['page'] ?? '';
        $allowed = ['header_beranda', 'header_peta', 'header_chatbot', 'header_statistik', 'header_berita', 'header_galeri', 'header_tim_kkn', 'header_visi_misi', 'header_sejarah', 'header_aparatur'];
        if (in_array($page, $allowed)) {
            $uploaded = handle_image_upload('header_foto');
            if ($uploaded) {
                $settings[$page] = $uploaded;
                updateSetting($pdo, $page, $settings[$page]);
                
                // Buat label ramah manusia
                $labels = [
                    'header_beranda' => 'Beranda',
                    'header_peta' => 'Peta Kelurahan',
                    'header_chatbot' => 'Chatbot AI',
                    'header_statistik' => 'Statistik Kelurahan',
                    'header_berita' => 'Berita',
                    'header_galeri' => 'Galeri',
                    'header_tim_kkn' => 'Tim KKN',
                    'header_visi_misi' => 'Visi & Misi',
                    'header_sejarah' => 'Sejarah',
                    'header_aparatur' => 'Data Aparatur',
                ];
                $labelName = $labels[$page] ?? 'Banner';
                
                $_SESSION['flash'] = ['type' => 'success', 'message' => "Foto banner $labelName berhasil diperbarui."];
            }
        }
        header('Location: media.php?tab=header');
        exit;
    } elseif ($action === 'reset_header') {
        $page = $_POST['page'] ?? '';
        $allowed = ['header_beranda', 'header_peta', 'header_chatbot', 'header_statistik', 'header_berita', 'header_galeri', 'header_tim_kkn', 'header_visi_misi', 'header_sejarah', 'header_aparatur'];
        if (in_array($page, $allowed)) {
            $settings[$page] = '';
            updateSetting($pdo, $page, '');
            $_SESSION['flash'] = ['type' => 'success', 'message' => "Banner dikembalikan ke warna default (Hijau)."];
        }
        header('Location: media.php?tab=header');
        exit;
    } elseif ($action === 'update_pengumuman') {
        $teks = trim($_POST['pengumuman_teks'] ?? '');
        updateSetting($pdo, 'pengumuman_teks', $teks);
        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Teks pengumuman berjalan berhasil diperbarui.'];
        header('Location: media.php?tab=header');
        exit;
    }
    }

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

$activeTab = $_GET['tab'] ?? 'header';
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Media & Tampilan | <?= NAMA_KELURAHAN ?></title>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Sora:wght@600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css" />
<link rel="stylesheet" href="../assets/css/admin.css?v=<?= time() ?>">
<style>
/* Modal Cropper Styles */
.cropper-modal-overlay {
  position: fixed; inset: 0; background: rgba(0,0,0,0.8); z-index: 9999;
  display: none; align-items: center; justify-content: center; opacity: 0; transition: opacity 0.3s ease;
}
.cropper-modal-overlay.active { display: flex; opacity: 1; }
.cropper-modal-content {
  background: white; padding: 20px; border-radius: 12px; max-width: 90vw; width: 800px;
  display: flex; flex-direction: column; gap: 16px; box-shadow: 0 10px 25px rgba(0,0,0,0.5);
}
.cropper-img-container {
  width: 100%; height: 60vh; max-height: 500px; background: #eee; border-radius: 8px; overflow: hidden;
}
</style>
<link rel="icon" type="image/png" href="../assets/img/lambang-kota.png">
</head>
<body>
<div class="admin-shell">
  <?php include __DIR__ . '/includes/sidebar.php'; ?>

  <main class="admin-main">
    <div class="admin-topbar">
      <div>
        <h1>Media & Tampilan</h1>
        <p>Kelola gambar Banner Utama dan Pengumuman Berjalan.</p>
      </div>
    </div>

    <?php if ($flash): ?>
      <div class="alert alert-<?= $flash['type'] ?>"><?= htmlspecialchars($flash['message']) ?></div>
    <?php endif; ?>

    <div style="margin-top: 24px;">
      <?php $img = $settings['header_beranda'] ?? ''; ?>
      <!-- Banner Header -->
      <div class="section-card" style="margin-bottom: 24px;">
          <div style="display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 16px;">
              <div>
                  <h2 style="font-size: 1.1rem; margin: 0 0 4px 0;"><i class="fa-solid fa-image" style="color: var(--teal-600);"></i> Banner Header Beranda</h2>
                  <p style="margin: 0; font-size: 0.85rem; color: var(--ink-soft);">Ganti foto latar belakang khusus (banner/hero) untuk halaman Utama Beranda publik.</p>
              </div>
          </div>

          <div style="margin-top: 16px; display: flex; flex-wrap: wrap; gap: 16px; align-items: stretch;">
              <div style="flex: 1; min-width: 260px; aspect-ratio: 21 / 9; border-radius: 12px; overflow: hidden; background: #ecfdf5; border: 1px solid #e2e8f0; position: relative;">
                  <?php if (!empty($img)): ?>
                      <img src="../<?= htmlspecialchars($img) ?>" style="width: 100%; height: 100%; object-fit: cover;" alt="Banner Beranda">
                  <?php else: ?>
                      <div style="height: 100%; display: flex; align-items: center; justify-content: center; color: #059669; font-weight: 600; font-size: 0.85rem; border: 2px dashed #a7f3d0;"><i class="fa-solid fa-check-circle" style="margin-right: 6px;"></i> Background Default (Hijau Kangkung)</div>
                  <?php endif; ?>
              </div>

              <div style="display: flex; flex-direction: column; gap: 10px;">
                  <form method="POST" action="media.php" enctype="multipart/form-data" style="margin:0;">
                      <input type="hidden" name="action" value="update_header">
                      <input type="hidden" name="page" value="header_beranda">
                      <label class="btn btn-primary" style="cursor: pointer; display: inline-flex; align-items: center; gap: 8px; width: 100%; justify-content: center;">
                          <i class="fa-solid fa-camera"></i> Ganti Banner
                          <input type="file" name="header_foto" id="heroImageInput" accept=".jpg,.jpeg,.png,.webp" class="cropper-upload-input" data-aspect-ratio="21/9" style="display: none;">
                      </label>
                  </form>
                  <?php if (!empty($img)): ?>
                  <form method="POST" action="media.php" style="margin:0;">
                      <input type="hidden" name="action" value="reset_header">
                      <input type="hidden" name="page" value="header_beranda">
                      <button type="submit" class="btn btn-outline" style="color: #ef4444; border-color: #fca5a5; background: #fee2e2; width: 100%;">
                          <i class="fa-solid fa-rotate-left"></i> Reset ke Default
                      </button>
                  </form>
                  <?php endif; ?>
              </div>
          </div>
      </div>

      <!-- Card Teks Pengumuman Berjalan -->
      <div class="section-card" style="margin-top: 24px;">
        <h2><i class="fa-solid fa-bullhorn" style="color: var(--teal-600);"></i> Teks Pengumuman Berjalan (Ticker Beranda)</h2>
        <p>Teks pengumuman running text yang berjalan otomatis di bagian atas beranda publik (seperti situs web resmi pemerintah).</p>

        <form method="POST" action="media.php" style="margin-top: 16px;">
          <input type="hidden" name="action" value="update_pengumuman">
          <div class="field" style="margin-bottom: 16px;">
            <label style="font-weight: 700; font-size: 0.9rem; margin-bottom: 6px; display: block; color: var(--teal-900);">Isi Teks Pengumuman</label>
            <?php 
              $defaultPengumuman = "Selamat Datang di Portal Resmi " . (defined('NAMA_KELURAHAN') ? NAMA_KELURAHAN : 'Kelurahan Kangkung') . " — Dapatkan kemudahan akses informasi layanan publik, peta wilayah interaktif, data statistik, dan informasi kegiatan warga secara digital.";
              $teks = !empty($settings['pengumuman_teks']) ? $settings['pengumuman_teks'] : $defaultPengumuman;
            ?>
            <textarea name="pengumuman_teks" rows="3" style="width: 100%; border: 1.5px solid #cbd5e1; border-radius: 8px; padding: 12px; font-family: inherit; font-size: 0.95rem;" placeholder="Contoh: Selamat Datang di Portal Resmi Kelurahan Kangkung..."><?= htmlspecialchars($teks) ?></textarea>
          </div>
          <button type="submit" class="btn btn-primary" style="display: inline-flex; align-items: center; gap: 8px; width: auto;">
            <i class="fa-solid fa-floppy-disk"></i> Simpan Teks Pengumuman
          </button>
        </form>
      </div>
    </div>

  </main>
</div>

<?php include __DIR__ . '/includes/cropper_modal.php'; ?>
</body>
</html>

