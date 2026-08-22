<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../config_db.php';
require_once __DIR__ . '/includes/functions.php';

$pdo = getDB();

// ==========================================
// 1. Ambil Data Galeri
// ==========================================
$stmtGaleri = $pdo->query("SELECT * FROM galeri ORDER BY created_at DESC, id DESC");
$gallery = $stmtGaleri->fetchAll();

// ==========================================
// 2. Tangani Request POST (Upload/Hapus)
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    // --- Galeri ---
    if ($action === 'upload_galeri') {
        $uploaded = handle_image_upload('galeri_foto');
        if ($uploaded) {
            $stmt = $pdo->prepare("INSERT INTO galeri (judul, kategori, gambar) VALUES (?, ?, ?)");
            $stmt->execute([trim($_POST['judul'] ?? 'Tanpa Judul'), trim($_POST['kategori'] ?? 'kegiatan'), $uploaded]);
            $_SESSION['flash'] = ['type' => 'success', 'message' => 'Foto berhasil ditambahkan ke galeri publik.'];
        } else {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Gagal mengupload foto galeri.'];
        }
        header('Location: galeri.php');
        exit;
    } elseif ($action === 'delete_galeri') {
        $id = (int)$_POST['id'];
        $stmt = $pdo->prepare("DELETE FROM galeri WHERE id = ?");
        $stmt->execute([$id]);
        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Foto berhasil dihapus dari galeri publik.'];
        header('Location: galeri.php');
        exit;
    }
}

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Galeri Publik | <?= NAMA_KELURAHAN ?></title>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Sora:wght@600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
<link rel="stylesheet" href="../assets/css/admin.css?v=<?= time() ?>">
<style>
.galeri-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:24px; }
.btn-add-foto {
  background: var(--teal-900); color: white; padding: 12px 24px; border-radius: 99px;
  font-weight: 700; font-size: 13.5px; border: none; cursor: pointer;
  display: flex; align-items: center; gap: 8px; box-shadow: 0 4px 12px rgba(15, 61, 54, 0.2);
  transition: 0.2s;
}
.btn-add-foto:hover { transform: translateY(-2px); box-shadow: 0 6px 16px rgba(15, 61, 54, 0.3); }
</style>
</head>
<body>
<div class="admin-shell">
  <?php include __DIR__ . '/includes/sidebar.php'; ?>

  <main class="admin-main">
    <div class="galeri-header">
      <div>
        <h1>Galeri Publik</h1>
        <p style="color:var(--ink-soft); margin-top:4px; font-size:13.5px;">Koleksi foto kegiatan dan infrastruktur kelurahan di halaman Galeri.</p>
      </div>
      <button class="btn-add-foto" onclick="document.getElementById('modalAddGaleri').classList.add('show')">
        <i class="fa-solid fa-plus"></i> TAMBAH FOTO
      </button>
    </div>

    <?php if ($flash): ?>
      <div class="alert alert-<?= $flash['type'] ?>"><?= htmlspecialchars($flash['message']) ?></div>
    <?php endif; ?>

    <div class="section-card">
      <div class="gallery-grid">
          <?php foreach ($gallery as $g): ?>
              <div class="gallery-item galeri-card">
                  <img src="../<?= htmlspecialchars($g['gambar']) ?>" alt="Galeri">
                  <div class="overlay-actions">
                      <form method="POST" action="galeri.php" onsubmit="return confirm('Hapus foto dari galeri?');">
                          <input type="hidden" name="action" value="delete_galeri">
                          <input type="hidden" name="id" value="<?= $g['id'] ?>">
                          <button type="submit" class="icon-btn delete" title="Hapus"><i class="fa-solid fa-trash-can"></i></button>
                      </form>
                  </div>
                  <div class="galeri-info">
                      <strong><?= htmlspecialchars($g['judul']) ?></strong>
                      <span class="badge-sm"><?= htmlspecialchars($g['kategori']) ?></span>
                  </div>
              </div>
          <?php endforeach; ?>
      </div>
    </div>
  </main>
</div>

<!-- Modal Tambah Galeri -->
<div id="modalAddGaleri" class="admin-modal-overlay">
  <div class="admin-modal-card" style="max-width: 400px;">
    <div class="admin-modal-header">
      <h2>Tambah Foto Galeri</h2>
      <button class="admin-modal-close" onclick="document.getElementById('modalAddGaleri').classList.remove('show')">&times;</button>
    </div>
    <div class="admin-modal-body">
      <form method="POST" action="galeri.php" enctype="multipart/form-data">
        <input type="hidden" name="action" value="upload_galeri">
        
        <div class="field">
          <label>Judul / Deskripsi Singkat</label>
          <input type="text" name="judul" required placeholder="Contoh: Kegiatan Posyandu">
        </div>
        
        <div class="field">
          <label>Kategori</label>
          <select name="kategori">
            <option value="kegiatan">Kegiatan</option>
            <option value="infrastruktur">Infrastruktur</option>
            <option value="prestasi">Prestasi</option>
            <option value="lainnya">Lainnya</option>
          </select>
        </div>
        
        <div class="field">
          <label>File Foto</label>
          <input type="file" name="galeri_foto" accept=".jpg,.jpeg,.png,.webp" required style="width:100%; padding:10px; border:1px dashed var(--line); border-radius:6px;">
        </div>
        
        <div style="margin-top:20px; display:flex; justify-content:flex-end;">
            <button type="submit" class="btn btn-primary">Upload Foto</button>
        </div>
      </form>
    </div>
  </div>
</div>

</body>
</html>

