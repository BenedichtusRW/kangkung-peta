<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../config_db.php';
require_once __DIR__ . '/includes/functions.php';

$pdo = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'save_aparatur') {
        $id = !empty($_POST['id']) ? (int)$_POST['id'] : null;
        $nama = trim($_POST['nama'] ?? '');
        $jabatan = trim($_POST['jabatan'] ?? '');
        
        $uploaded = handle_image_upload('foto');
        
        if ($id) {
            if ($uploaded) {
                $stmt = $pdo->prepare("UPDATE aparatur SET nama=?, jabatan=?, foto=? WHERE id=?");
                $stmt->execute([$nama, $jabatan, $uploaded, $id]);
            } else {
                $stmt = $pdo->prepare("UPDATE aparatur SET nama=?, jabatan=? WHERE id=?");
                $stmt->execute([$nama, $jabatan, $id]);
            }
            $_SESSION['flash'] = ['type' => 'success', 'message' => "Data $nama berhasil diperbarui."];
        } else {
            $stmt = $pdo->prepare("INSERT INTO aparatur (nama, jabatan, foto) VALUES (?, ?, ?)");
            $stmt->execute([$nama, $jabatan, $uploaded]);
            $_SESSION['flash'] = ['type' => 'success', 'message' => "Data $nama berhasil ditambahkan."];
        }
        header("Location: aparatur.php");
        exit;
    } elseif ($action === 'delete_aparatur') {
        $id = (int)$_POST['id'];
        $stmt = $pdo->prepare("DELETE FROM aparatur WHERE id=?");
        $stmt->execute([$id]);
        $_SESSION['flash'] = ['type' => 'success', 'message' => "Data berhasil dihapus."];
        header("Location: aparatur.php");
        exit;
    } elseif ($action === 'update_banner_aparatur') {
        $uploaded = handle_image_upload('banner_foto');
        if ($uploaded) {
            $stmt = $pdo->prepare("INSERT INTO settings (key_name, key_value) VALUES ('header_aparatur', ?) ON DUPLICATE KEY UPDATE key_value = VALUES(key_value)");
            $stmt->execute([$uploaded]);
            $_SESSION['flash'] = ['type' => 'success', 'message' => 'Foto banner Data Aparatur berhasil diperbarui.'];
        }
        header("Location: aparatur.php");
        exit;
    } elseif ($action === 'reset_banner_aparatur') {
        $stmt = $pdo->prepare("INSERT INTO settings (key_name, key_value) VALUES ('header_aparatur', '') ON DUPLICATE KEY UPDATE key_value = VALUES(key_value)");
        $stmt->execute();
        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Banner Data Aparatur dikembalikan ke background default.'];
        header("Location: aparatur.php");
        exit;
    }
}

$aparatur = $pdo->query("SELECT * FROM aparatur ORDER BY id ASC")->fetchAll();

$stmtBanner = $pdo->query("SELECT key_value FROM settings WHERE key_name = 'header_aparatur'");
$banner_aparatur = $stmtBanner->fetchColumn() ?: '';

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Data Aparatur | <?= NAMA_KELURAHAN ?></title>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Sora:wght@600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
<link rel="stylesheet" href="../assets/css/admin.css?v=<?= time() ?>">
<style>
.person-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 20px; }
.person-card { background: var(--white); border: 1px solid var(--line); border-radius: var(--radius-md); overflow: hidden; text-align: center; position: relative; }
.person-photo { width: 100%; height: 220px; object-fit: cover; }
.person-info { padding: 16px; }
.person-info h3 { font-size: 1.1rem; color: var(--ink); margin: 0 0 4px 0; }
.person-info p { font-size: 0.9rem; color: var(--ink-soft); margin: 0; }
.person-actions { position: absolute; top: 12px; right: 12px; display: flex; gap: 8px; opacity: 0; transition: opacity 0.2s; }
.person-card:hover .person-actions { opacity: 1; }

.add-person { display: flex; flex-direction: column; align-items: center; justify-content: center; min-height: 280px; border: 2px dashed var(--line); cursor: pointer; transition: all 0.2s; color: var(--teal-600); }
.add-person:hover { border-color: var(--teal-600); background: #f0fdf4; }
.add-person i { font-size: 2.5rem; margin-bottom: 12px; }

/* Modal */
.modal { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 100; align-items: center; justify-content: center; }
.modal.active { display: flex; }
.modal-content { background: var(--white); padding: 32px; border-radius: var(--radius-lg); width: 100%; max-width: 500px; box-shadow: 0 10px 25px rgba(0,0,0,0.1); }
.modal-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; }
.modal-header h2 { margin: 0; font-size: 1.3rem; }
.close-modal { background: none; border: none; font-size: 1.5rem; cursor: pointer; color: var(--ink-soft); }
</style>
</head>
<body>
<div class="admin-shell">
  <?php include __DIR__ . '/includes/sidebar.php'; ?>

  <main class="admin-main">
    <div class="admin-topbar">
      <div>
        <h1>Data Aparatur</h1>
        <p>Kelola data aparatur dan perangkat desa kelurahan.</p>
      </div>
    </div>

    <?php if ($flash): ?>
      <div class="alert alert-<?= $flash['type'] ?>"><?= htmlspecialchars($flash['message']) ?></div>
    <?php endif; ?>

    <!-- Banner Header -->
    <div class="section-card" style="margin-bottom: 24px;">
        <div style="display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 16px;">
            <div>
                <h2 style="font-size: 1.1rem; margin: 0 0 4px 0;"><i class="fa-solid fa-image" style="color: var(--teal-600);"></i> Banner Header Data Aparatur</h2>
                <p style="margin: 0; font-size: 0.85rem; color: var(--ink-soft);">Ganti foto latar belakang header pada halaman Data Aparatur publik.</p>
            </div>
        </div>

        <div style="margin-top: 16px; display: flex; flex-wrap: wrap; gap: 16px; align-items: center;">
            <div style="flex: 1; min-width: 260px; height: 130px; border-radius: 12px; overflow: hidden; background: #ecfdf5; border: 1px solid #e2e8f0; position: relative;">
                <?php if (!empty($banner_aparatur)): ?>
                    <img src="../<?= htmlspecialchars($banner_aparatur) ?>" style="width: 100%; height: 100%; object-fit: cover;" alt="Banner Aparatur">
                <?php else: ?>
                    <div style="height: 100%; display: flex; align-items: center; justify-content: center; color: #059669; font-weight: 600; font-size: 0.85rem; border: 2px dashed #a7f3d0;"><i class="fa-solid fa-check-circle" style="margin-right: 6px;"></i> Background Default (Hijau Kangkung)</div>
                <?php endif; ?>
            </div>

            <div style="display: flex; flex-direction: column; gap: 10px;">
                <form method="POST" enctype="multipart/form-data" style="margin:0;">
                    <input type="hidden" name="action" value="update_banner_aparatur">
                    <label class="btn btn-primary" style="cursor: pointer; display: inline-flex; align-items: center; gap: 8px; width: 100%; justify-content: center;">
                        <i class="fa-solid fa-camera"></i> Ganti Banner
                        <input type="file" name="banner_foto" accept=".jpg,.jpeg,.png,.webp" required onchange="this.form.submit()" style="display: none;">
                    </label>
                </form>
                <?php if (!empty($banner_aparatur)): ?>
                <form method="POST" style="margin:0;">
                    <input type="hidden" name="action" value="reset_banner_aparatur">
                    <button type="submit" class="btn btn-outline" style="color: #ef4444; border-color: #fca5a5; background: #fee2e2; width: 100%;">
                        <i class="fa-solid fa-rotate-left"></i> Reset ke Default
                    </button>
                </form>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="section-card">
        <div class="person-grid">
            <div class="person-card add-person" onclick="openModal()">
                <i class="fa-solid fa-plus-circle"></i>
                <strong>Tambah Aparatur</strong>
            </div>

            <?php foreach ($aparatur as $p): ?>
                <div class="person-card">
                    <img src="<?= $p['foto'] ? '../' . htmlspecialchars($p['foto']) : '../assets/img/default-avatar.jpg' ?>" class="person-photo" alt="Foto">
                    <div class="person-actions">
                        <button class="icon-btn edit" onclick='editPerson(<?= json_encode($p, JSON_HEX_APOS | JSON_HEX_TAG) ?>)'><i class="fa-solid fa-pen"></i></button>
                        <form method="POST" style="display:inline;" onsubmit="return confirm('Hapus data ini?');">
                            <input type="hidden" name="action" value="delete_aparatur">
                            <input type="hidden" name="id" value="<?= $p['id'] ?>">
                            <button type="submit" class="icon-btn delete"><i class="fa-solid fa-trash"></i></button>
                        </form>
                    </div>
                    <div class="person-info">
                        <h3><?= htmlspecialchars($p['nama']) ?></h3>
                        <p><?= htmlspecialchars($p['jabatan']) ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
  </main>
</div>

<!-- Modal Form -->
<div id="modalForm" class="admin-modal-overlay">
  <div class="admin-modal-card" style="max-width: 400px;">
    <div class="admin-modal-header">
      <h2 id="modalTitle">Tambah Aparatur</h2>
      <button class="admin-modal-close" onclick="closeModal()">&times;</button>
    </div>
    <div class="admin-modal-body">
      <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="action" value="save_aparatur">
        <input type="hidden" name="id" id="formId">
        
        <div class="field">
          <label>Nama Lengkap</label>
          <input type="text" name="nama" id="formNama" required>
        </div>
        
        <div class="field">
          <label>Jabatan</label>
          <input type="text" name="jabatan" id="formJabatan" required>
        </div>
        
        <div class="field">
          <label>Foto Profil (Opsional saat edit)</label>
          <input type="file" name="foto" id="formFoto" accept="image/*" style="width:100%; padding:10px; border:1px dashed var(--line); border-radius:6px;">
        </div>
        
        <div style="margin-top:20px; display:flex; justify-content:flex-end;">
            <button type="submit" class="btn btn-primary">Simpan Data</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
function openModal() {
    document.getElementById('modalTitle').innerText = 'Tambah Aparatur';
    document.getElementById('formId').value = '';
    document.getElementById('formNama').value = '';
    document.getElementById('formJabatan').value = '';
    document.getElementById('formFoto').required = true;
    document.getElementById('modalForm').classList.add('show');
    document.getElementById('modalForm').style.display = 'flex';
}
function closeModal() {
    document.getElementById('modalForm').style.display = 'none';
}
function editPerson(data) {
    document.getElementById('modalTitle').innerText = 'Edit Aparatur';
    document.getElementById('formId').value = data.id;
    document.getElementById('formNama').value = data.nama;
    document.getElementById('formJabatan').value = data.jabatan;
    document.getElementById('formFoto').required = false;
    document.getElementById('modalForm').classList.add('show');
    document.getElementById('modalForm').style.display = 'flex';
}
</script>
</body>
</html>

