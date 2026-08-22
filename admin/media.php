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
        $allowed = ['header_beranda', 'header_peta', 'header_chatbot', 'header_statistik', 'header_berita', 'header_galeri'];
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
                    'header_galeri' => 'Galeri'
                ];
                $labelName = $labels[$page] ?? 'Banner';
                
                $_SESSION['flash'] = ['type' => 'success', 'message' => "Foto banner $labelName berhasil diperbarui."];
            }
        }
        header('Location: media.php?tab=header');
        exit;
    } elseif ($action === 'reset_header') {
        $page = $_POST['page'] ?? '';
        $allowed = ['header_beranda', 'header_peta', 'header_chatbot', 'header_statistik', 'header_berita', 'header_galeri'];
        if (in_array($page, $allowed)) {
            $settings[$page] = '';
            updateSetting($pdo, $page, '');
            $_SESSION['flash'] = ['type' => 'success', 'message' => "Banner dikembalikan ke warna default (Hijau)."];
        }
        header('Location: media.php?tab=header');
        exit;
    }
    // --- Slider ---
    elseif ($action === 'upload_slider') {
        $uploaded = handle_image_upload('slider_foto');
        if ($uploaded) {
            if (!isset($settings['slider_images'])) $settings['slider_images'] = [];
            $settings['slider_images'][] = [
                'id' => time() . rand(100, 999),
                'url' => $uploaded
            ];
            updateSetting($pdo, 'slider_images', $settings['slider_images']);
            $_SESSION['flash'] = ['type' => 'success', 'message' => 'Foto slider berhasil ditambahkan.'];
        }
        header('Location: media.php?tab=slider');
        exit;
    } elseif ($action === 'delete_slider' && !empty($_POST['id'])) {
        $id = (int)$_POST['id'];
        $settings['slider_images'] = array_values(array_filter($settings['slider_images'], fn($s) => (int)$s['id'] !== $id));
        updateSetting($pdo, 'slider_images', $settings['slider_images']);
        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Foto slider berhasil dihapus.'];
        header('Location: media.php?tab=slider');
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
<link rel="stylesheet" href="../assets/css/admin.css?v=<?= time() ?>">
</head>
<body>
<div class="admin-shell">
  <?php include __DIR__ . '/includes/sidebar.php'; ?>

  <main class="admin-main">
    <div class="admin-topbar">
      <div>
        <h1>Media & Tampilan</h1>
        <p>Kelola gambar Banner Utama dan Slider Beranda.</p>
      </div>
    </div>

    <?php if ($flash): ?>
      <div class="alert alert-<?= $flash['type'] ?>"><?= htmlspecialchars($flash['message']) ?></div>
    <?php endif; ?>

    <!-- TABS NAVIGATION -->
    <div class="tabs-nav">
      <button class="tab-btn <?= $activeTab === 'header' ? 'active' : '' ?>" onclick="switchTab('header')"><i class="fa-solid fa-panorama"></i> Header Utama</button>
      <button class="tab-btn <?= $activeTab === 'slider' ? 'active' : '' ?>" onclick="switchTab('slider')"><i class="fa-solid fa-images"></i> Slider Beranda</button>
    </div>

    <!-- TAB 1: HEADER -->
    <div id="tab-header" class="tab-content <?= $activeTab === 'header' ? 'active' : '' ?>">
      <div class="section-card">
        <h2><i class="fa-solid fa-panorama"></i> Gambar Banner Khusus Halaman</h2>
        <p>Kelola gambar latar belakang khusus (banner/hero) untuk masing-masing halaman utama di situs publik. Rasio terbaik: 21:9 atau 16:9 (Lebar min. 1200px).</p>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 24px; margin-top: 24px;">
        <?php
        $banners = [
            'header_beranda' => 'Banner Beranda',
            'header_peta' => 'Banner Peta Kelurahan',
            'header_chatbot' => 'Banner Chatbot AI',
            'header_statistik' => 'Banner Statistik',
            'header_berita' => 'Banner Berita',
            'header_galeri' => 'Banner Galeri',
        ];
        foreach ($banners as $key => $label): 
            $img = $settings[$key] ?? '';
        ?>
        <div class="banner-card" style="border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden; background: #fff;">
          <div style="padding: 14px 16px; background: #f8fafc; border-bottom: 1px solid #e2e8f0; font-weight: 600; font-size: 0.95rem; display: flex; align-items: center; gap: 8px;">
            <i class="fa-solid fa-image" style="color: var(--teal-600);"></i> <?= $label ?>
          </div>
          <form method="POST" action="media.php" enctype="multipart/form-data" class="media-form" style="margin: 0; padding: 16px;">
            <input type="hidden" name="action" value="update_header">
            <input type="hidden" name="page" value="<?= $key ?>">
            
            <div class="header-preview-container" style="height: 160px; margin-bottom: 0;">
              <?php if (!empty($img)): ?>
                  <img src="../<?= htmlspecialchars($img) ?>" class="header-preview-img" alt="<?= $label ?>" style="width: 100%; height: 100%; object-fit: cover; border-radius: 8px;">
              <?php else: ?>
                  <div class="header-preview-empty" style="height: 100%; display: flex; align-items: center; justify-content: center; background: #ecfdf5; border-radius: 8px; color: #059669; font-size: 0.85rem; border: 2px dashed #a7f3d0;"><i class="fa-solid fa-check-circle" style="margin-right: 6px;"></i> Background Default (Hijau)</div>
              <?php endif; ?>
              
              <div class="upload-overlay" style="border-radius: 8px;">
                  <label for="<?= $key ?>_input" class="upload-btn" style="padding: 8px 16px; font-size: 0.85rem;">
                    <i class="fa-solid fa-camera"></i> Ganti
                  </label>
                  <input type="file" id="<?= $key ?>_input" name="header_foto" accept=".jpg,.jpeg,.png,.webp" required onchange="this.form.submit()">
              </div>
            </div>
          </form>
          <?php if (!empty($img)): ?>
          <form method="POST" action="media.php" style="padding: 0 16px 16px; margin: 0;">
            <input type="hidden" name="action" value="reset_header">
            <input type="hidden" name="page" value="<?= $key ?>">
            <button type="submit" style="width: 100%; padding: 8px; background: #fee2e2; color: #ef4444; border: 1px solid #fca5a5; border-radius: 6px; cursor: pointer; font-size: 0.85rem; font-weight: 600; display: flex; align-items: center; justify-content: center; gap: 6px; transition: all 0.2s;" onmouseover="this.style.background='#fecaca'" onmouseout="this.style.background='#fee2e2'">
                <i class="fa-solid fa-rotate-left"></i> Gunakan Background Default
            </button>
          </form>
          <?php endif; ?>
        </div>
        <?php endforeach; ?>
        </div>
      </div>
    </div>

    <!-- TAB 2: SLIDER -->
    <div id="tab-slider" class="tab-content <?= $activeTab === 'slider' ? 'active' : '' ?>">
      <div class="section-card">
        <h2><i class="fa-solid fa-images"></i> Slider Beranda</h2>
        <p>Kumpulan gambar berjalan otomatis di bawah halaman utama.</p>
        
        <div class="gallery-grid">
            <!-- Add New Slider Card -->
            <div class="gallery-item add-new">
              <form method="POST" action="media.php" enctype="multipart/form-data">
                  <input type="hidden" name="action" value="upload_slider">
                  <label for="slider_foto_input" class="add-new-label">
                      <i class="fa-solid fa-plus-circle"></i>
                      <span>Tambah Slider</span>
                  </label>
                  <input type="file" id="slider_foto_input" name="slider_foto" accept=".jpg,.jpeg,.png,.webp" required onchange="this.form.submit()">
              </form>
            </div>

            <?php foreach (array_reverse($settings['slider_images']) as $item): ?>
                <div class="gallery-item">
                    <img src="../<?= htmlspecialchars($item['url']) ?>" alt="Slider">
                    <div class="overlay-actions">
                        <form method="POST" action="media.php" onsubmit="return confirm('Hapus slider ini?');">
                            <input type="hidden" name="action" value="delete_slider">
                            <input type="hidden" name="id" value="<?= $item['id'] ?>">
                            <button type="submit" class="icon-btn delete" title="Hapus"><i class="fa-solid fa-trash-can"></i></button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
      </div>
    </div>

  </main>
</div>

<script>
function switchTab(tabId) {
    document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
    document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));
    
    document.querySelector(`.tab-btn[onclick="switchTab('${tabId}')"]`).classList.add('active');
    document.getElementById(`tab-${tabId}`).classList.add('active');
    
    // Update URL without reload
    const url = new URL(window.location);
    url.searchParams.set('tab', tabId);
    window.history.pushState({}, '', url);
}
</script>
</body>
</html>

