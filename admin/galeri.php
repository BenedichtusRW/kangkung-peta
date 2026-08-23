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
    } elseif ($action === 'edit_galeri') {
        $id = (int)$_POST['id'];
        $judul = trim($_POST['judul'] ?? 'Tanpa Judul');
        $kategori = trim($_POST['kategori'] ?? 'kegiatan');
        
        $newImage = null;
        if (!empty($_FILES['galeri_foto']['tmp_name'])) {
            $newImage = handle_image_upload('galeri_foto');
        }
        
        if ($newImage) {
            $stmt = $pdo->prepare("UPDATE galeri SET judul = ?, kategori = ?, gambar = ? WHERE id = ?");
            $stmt->execute([$judul, $kategori, $newImage, $id]);
        } else {
            $stmt = $pdo->prepare("UPDATE galeri SET judul = ?, kategori = ? WHERE id = ?");
            $stmt->execute([$judul, $kategori, $id]);
        }
        
        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Info foto galeri berhasil diperbarui.'];
        header('Location: galeri.php');
        exit;
    } elseif ($action === 'update_banner_galeri') {
        $uploaded = handle_image_upload('banner_foto');
        if ($uploaded) {
            $stmt = $pdo->prepare("INSERT INTO settings (key_name, key_value) VALUES ('header_galeri', ?) ON DUPLICATE KEY UPDATE key_value = VALUES(key_value)");
            $stmt->execute([$uploaded]);
            $_SESSION['flash'] = ['type' => 'success', 'message' => 'Foto banner Galeri berhasil diperbarui.'];
        }
        header("Location: galeri.php");
        exit;
    } elseif ($action === 'reset_banner_galeri') {
        $stmt = $pdo->prepare("INSERT INTO settings (key_name, key_value) VALUES ('header_galeri', '') ON DUPLICATE KEY UPDATE key_value = VALUES(key_value)");
        $stmt->execute();
        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Banner Galeri dikembalikan ke background default.'];
        header("Location: galeri.php");
        exit;
    }
}

$stmtBanner = $pdo->query("SELECT key_value FROM settings WHERE key_name = 'header_galeri'");
$banner_galeri = $stmtBanner->fetchColumn() ?: '';

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
      <button class="btn-add-foto" onclick="document.getElementById('modalAddGaleri').classList.add('open')">
        <i class="fa-solid fa-plus"></i> TAMBAH FOTO
      </button>
    </div>

    <?php if ($flash): ?>
      <div class="alert alert-<?= $flash['type'] ?>"><?= htmlspecialchars($flash['message']) ?></div>
    <?php endif; ?>

    <!-- Banner Header -->
    <div class="section-card" style="margin-bottom: 24px;">
        <div style="display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 16px;">
            <div>
                <h2 style="font-size: 1.1rem; margin: 0 0 4px 0;"><i class="fa-solid fa-image" style="color: var(--teal-600);"></i> Banner Header Galeri</h2>
                <p style="margin: 0; font-size: 0.85rem; color: var(--ink-soft);">Ganti foto latar belakang header pada halaman Galeri publik.</p>
            </div>
        </div>

        <div style="margin-top: 16px; display: flex; flex-wrap: wrap; gap: 16px; align-items: center;">
            <div style="flex: 1; min-width: 260px; height: 130px; border-radius: 12px; overflow: hidden; background: #ecfdf5; border: 1px solid #e2e8f0; position: relative;">
                <?php if (!empty($banner_galeri)): ?>
                    <img src="../<?= htmlspecialchars($banner_galeri) ?>" style="width: 100%; height: 100%; object-fit: cover;" alt="Banner Galeri">
                <?php else: ?>
                    <div style="height: 100%; display: flex; align-items: center; justify-content: center; color: #059669; font-weight: 600; font-size: 0.85rem; border: 2px dashed #a7f3d0;"><i class="fa-solid fa-check-circle" style="margin-right: 6px;"></i> Background Default (Hijau Kangkung)</div>
                <?php endif; ?>
            </div>

            <div style="display: flex; flex-direction: column; gap: 10px;">
                <form method="POST" enctype="multipart/form-data" style="margin:0;">
                    <input type="hidden" name="action" value="update_banner_galeri">
                    <label class="btn btn-primary" style="cursor: pointer; display: inline-flex; align-items: center; gap: 8px; width: 100%; justify-content: center;">
                        <i class="fa-solid fa-camera"></i> Ganti Banner
                        <input type="file" name="banner_foto" accept=".jpg,.jpeg,.png,.webp" required onchange="this.form.submit()" style="display: none;">
                    </label>
                </form>
                <?php if (!empty($banner_galeri)): ?>
                <form method="POST" style="margin:0;">
                    <input type="hidden" name="action" value="reset_banner_galeri">
                    <button type="submit" class="btn btn-outline" style="color: #ef4444; border-color: #fca5a5; background: #fee2e2; width: 100%;">
                        <i class="fa-solid fa-rotate-left"></i> Reset ke Default
                    </button>
                </form>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="section-card">
      <div class="gallery-grid">
          <?php foreach ($gallery as $g): ?>
              <div class="gallery-item galeri-card">
                  <img src="../<?= htmlspecialchars($g['gambar']) ?>" alt="Galeri">
                  <div class="overlay-actions" style="display:flex; gap: 6px;">
                      <button type="button" class="icon-btn edit" title="Edit" data-id="<?= $g['id'] ?>" data-judul="<?= htmlspecialchars($g['judul'], ENT_QUOTES, 'UTF-8') ?>" data-kategori="<?= htmlspecialchars($g['kategori'], ENT_QUOTES, 'UTF-8') ?>">
                          <i class="fa-solid fa-pen"></i>
                      </button>
                      <form method="POST" action="galeri.php" onsubmit="return confirm('Hapus foto dari galeri?');" style="margin:0;">
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
      <button class="admin-modal-close" onclick="document.getElementById('modalAddGaleri').classList.remove('open')">&times;</button>
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
</div>

<!-- Modal Edit Galeri -->
<div id="modalEditGaleri" class="admin-modal-overlay">
  <div class="admin-modal-card" style="max-width: 400px;">
    <div class="admin-modal-header">
      <h2>Edit Foto Galeri</h2>
      <button class="admin-modal-close" onclick="document.getElementById('modalEditGaleri').classList.remove('open')">&times;</button>
    </div>
    <div class="admin-modal-body">
      <form method="POST" action="galeri.php" enctype="multipart/form-data">
        <input type="hidden" name="action" value="edit_galeri">
        <input type="hidden" name="id" id="edit_id" value="">
        
        <div class="field">
          <label>Judul / Deskripsi Singkat</label>
          <input type="text" name="judul" id="edit_judul" required placeholder="Contoh: Kegiatan Posyandu">
        </div>
        
        <div class="field">
          <label>Kategori</label>
          <select name="kategori" id="edit_kategori">
            <option value="kegiatan">Kegiatan</option>
            <option value="infrastruktur">Infrastruktur</option>
            <option value="prestasi">Prestasi</option>
            <option value="lainnya">Lainnya</option>
          </select>
        </div>
        
        <div class="field">
          <label>Ganti File Foto (Opsional)</label>
          <input type="file" name="galeri_foto" accept=".jpg,.jpeg,.png,.webp" style="width:100%; padding:10px; border:1px dashed var(--line); border-radius:6px;">
          <small style="color:var(--ink-soft); display:block; margin-top:4px;">Biarkan kosong jika tidak ingin mengganti foto saat ini.</small>
        </div>
        
        <div style="margin-top:20px; display:flex; justify-content:flex-end;">
            <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
document.querySelectorAll('.icon-btn.edit').forEach(function(btn) {
    btn.addEventListener('click', function() {
        var id = this.getAttribute('data-id');
        var judul = this.getAttribute('data-judul');
        var kategori = this.getAttribute('data-kategori');
        document.getElementById('edit_id').value = id;
        document.getElementById('edit_judul').value = judul;
        document.getElementById('edit_kategori').value = kategori;
        document.getElementById('modalEditGaleri').classList.add('open');
    });
});
</script>

</body>
</html>

