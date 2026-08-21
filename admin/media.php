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
        $uploaded = handle_image_upload('header_foto');
        if ($uploaded) {
            $settings['header_image'] = $uploaded;
            updateSetting($pdo, 'header_image', $settings['header_image']);
            $_SESSION['flash'] = ['type' => 'success', 'message' => 'Foto header berhasil diperbarui.'];
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
<link rel="stylesheet" href="../assets/css/admin.css">
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
        <h2><i class="fa-solid fa-panorama"></i> Gambar Header Utama (Hero Section)</h2>
        <p>Gambar ukuran besar yang pertama kali dilihat pengunjung di halaman utama.</p>
        
        <form method="POST" action="media.php" enctype="multipart/form-data" class="media-form">
          <input type="hidden" name="action" value="update_header">
          
          <div class="header-preview-container">
            <?php if (!empty($settings['header_image'])): ?>
                <img src="../<?= htmlspecialchars($settings['header_image']) ?>" class="header-preview-img" alt="Header">
            <?php else: ?>
                <div class="header-preview-empty">Belum ada gambar header.</div>
            <?php endif; ?>
            
            <div class="upload-overlay">
                <label for="header_foto_input" class="upload-btn">
                  <i class="fa-solid fa-camera"></i> Ganti Gambar Header
                </label>
                <input type="file" id="header_foto_input" name="header_foto" accept=".jpg,.jpeg,.png,.webp" required onchange="this.form.submit()">
            </div>
          </div>
          <p class="help-text"><i class="fa-solid fa-circle-info"></i> Rasio terbaik: 21:9 atau 16:9 (Lebar min. 1200px). Gambar akan otomatis terganti saat Anda memilih file baru.</p>
        </form>
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
