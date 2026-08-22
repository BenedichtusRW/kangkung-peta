<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../config_db.php';
require_once __DIR__ . '/includes/functions.php';

$pdo = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'save_tim') {
        $id = !empty($_POST['id']) ? (int)$_POST['id'] : null;
        $nama = trim($_POST['nama'] ?? '');
        $jabatan = trim($_POST['jabatan'] ?? '');
        
        $uploaded = handle_image_upload('foto');
        
        if ($id) {
            if ($uploaded) {
                $stmt = $pdo->prepare("UPDATE tim_kkn SET nama=?, jabatan=?, foto=? WHERE id=?");
                $stmt->execute([$nama, $jabatan, $uploaded, $id]);
            } else {
                $stmt = $pdo->prepare("UPDATE tim_kkn SET nama=?, jabatan=? WHERE id=?");
                $stmt->execute([$nama, $jabatan, $id]);
            }
            $_SESSION['flash'] = ['type' => 'success', 'message' => "Data $nama berhasil diperbarui."];
        } else {
            $stmt = $pdo->prepare("INSERT INTO tim_kkn (nama, jabatan, foto) VALUES (?, ?, ?)");
            $stmt->execute([$nama, $jabatan, $uploaded]);
            $_SESSION['flash'] = ['type' => 'success', 'message' => "Data $nama berhasil ditambahkan."];
        }
        header("Location: tim-kkn.php");
        exit;
    } elseif ($action === 'delete_tim') {
        $id = (int)$_POST['id'];
        $stmt = $pdo->prepare("DELETE FROM tim_kkn WHERE id=?");
        $stmt->execute([$id]);
        $_SESSION['flash'] = ['type' => 'success', 'message' => "Data berhasil dihapus."];
        header("Location: tim-kkn.php");
        exit;
    }
}

$tim_kkn = $pdo->query("SELECT * FROM tim_kkn ORDER BY id ASC")->fetchAll();
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Tim KKN | <?= NAMA_KELURAHAN ?></title>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Sora:wght@600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
<link rel="stylesheet" href="../assets/css/admin.css?v=<?= time() ?>">
<style>
.person-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 20px; }
.person-card { background: var(--white); border: 1px solid var(--line); border-radius: var(--radius-md); overflow: hidden; text-align: center; position: relative; }
.person-photo { width: 100%; aspect-ratio: 3/4; object-fit: cover; background: var(--paper); }
.person-info { padding: 16px; }
.person-info h3 { margin: 0 0 4px 0; font-size: 16px; color: var(--teal-900); }
.person-info p { margin: 0; font-size: 13px; color: var(--ink-soft); }
.person-actions { position: absolute; top: 8px; right: 8px; display: flex; gap: 4px; opacity: 0; transition: opacity 0.2s; }
.person-card:hover .person-actions { opacity: 1; }
.add-person { display: flex; flex-direction: column; align-items: center; justify-content: center; background: var(--teal-100); border: 2px dashed var(--teal-300); color: var(--teal-800); cursor: pointer; aspect-ratio: unset; height: 100%; min-height: 250px; }
.add-person:hover { background: var(--teal-200); }
.add-person i { font-size: 32px; margin-bottom: 8px; }
</style>
</head>
<body>
<div class="admin-shell">
  <?php include __DIR__ . '/includes/sidebar.php'; ?>

  <main class="admin-main">
    <div class="admin-topbar">
      <div>
        <h1>Tim KKN / Relawan</h1>
        <p>Kelola data mahasiswa KKN atau relawan yang sedang bertugas.</p>
      </div>
    </div>

    <?php if ($flash): ?>
      <div class="alert alert-<?= $flash['type'] ?>"><?= htmlspecialchars($flash['message']) ?></div>
    <?php endif; ?>

    <div class="section-card">
        <div class="person-grid">
            <div class="person-card add-person" onclick="openModal()">
                <i class="fa-solid fa-plus-circle"></i>
                <strong>Tambah Anggota</strong>
            </div>

            <?php foreach ($tim_kkn as $p): ?>
                <div class="person-card">
                    <img src="<?= $p['foto'] ? '../' . htmlspecialchars($p['foto']) : '../assets/img/default-avatar.jpg' ?>" class="person-photo" alt="Foto">
                    <div class="person-actions">
                        <button class="icon-btn" onclick='editPerson(<?= json_encode($p, JSON_HEX_APOS | JSON_HEX_TAG) ?>)'><i class="fa-solid fa-pen"></i></button>
                        <form method="POST" style="display:inline;" onsubmit="return confirm('Hapus data ini?');">
                            <input type="hidden" name="action" value="delete_tim">
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
      <h2 id="modalTitle">Tambah Anggota</h2>
      <button class="admin-modal-close" onclick="closeModal()">&times;</button>
    </div>
    <div class="admin-modal-body">
      <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="action" value="save_tim">
        <input type="hidden" name="id" id="formId">
        
        <div class="field">
          <label>Nama Lengkap</label>
          <input type="text" name="nama" id="formNama" required>
        </div>
        
        <div class="field">
          <label>Peran / Jabatan (Mhs KKN)</label>
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
    document.getElementById('modalTitle').innerText = 'Tambah Anggota';
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
    document.getElementById('modalTitle').innerText = 'Edit Anggota';
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

