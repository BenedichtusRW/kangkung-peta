<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../config_db.php';
require_once __DIR__ . '/includes/functions.php';

$pdo = getDB();

// Pastikan tabel tim_kkn ada di database
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS tim_kkn (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nama VARCHAR(255) NOT NULL,
        jabatan VARCHAR(255) NOT NULL,
        foto VARCHAR(255) DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    // Auto-seed data lengkap jika data di database kurang dari 21
    $stmtCheck = $pdo->query("SELECT COUNT(*) FROM tim_kkn");
    if ($stmtCheck->fetchColumn() < 21) {
        $dataFile = __DIR__ . '/../data/tim-kkn.json';
        if (is_file($dataFile)) {
            $jsonTim = json_decode(file_get_contents($dataFile), true);
            if (is_array($jsonTim) && count($jsonTim) >= 21) {
                $pdo->exec("TRUNCATE TABLE tim_kkn");
                $stmtInsert = $pdo->prepare("INSERT INTO tim_kkn (id, nama, jabatan, foto) VALUES (?, ?, ?, ?)");
                foreach ($jsonTim as $t) {
                    $stmtInsert->execute([$t['id'], $t['nama'], $t['jabatan'], $t['foto'] ?? '']);
                }
            }
        }
    }
} catch (Exception $e) {}

function sync_tim_kkn_json($pdo) {
    try {
        $stmt = $pdo->query("SELECT id, nama, jabatan, foto FROM tim_kkn ORDER BY (CASE WHEN jabatan LIKE '%Dosen%' OR jabatan LIKE '%DPL%' THEN 1 WHEN jabatan LIKE '%Kordes%' OR jabatan LIKE '%Ketua%' THEN 2 WHEN jabatan LIKE '%Sekretaris%' THEN 3 WHEN jabatan LIKE '%Bendahara%' THEN 4 ELSE 5 END) ASC, id ASC");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $dataFile = __DIR__ . '/../data/tim-kkn.json';
        file_put_contents($dataFile, json_encode($rows, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    } catch (Exception $e) {}
}

function get_admin_photo_url(?string $path): string {
    if (empty($path)) {
        return '../assets/img/placeholder-default.jpg';
    }
    if (strpos($path, 'http://') === 0 || strpos($path, 'https://') === 0) {
        return $path;
    }
    $clean = preg_replace('/^(\.\.\/)+/', '', $path);
    return '../' . ltrim($clean, '/');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'update_banner_kkn') {
        $uploaded = handle_image_upload('banner_foto');
        if ($uploaded) {
            $stmt = $pdo->prepare("INSERT INTO settings (key_name, key_value) VALUES ('header_tim_kkn', ?) ON DUPLICATE KEY UPDATE key_value = VALUES(key_value)");
            $stmt->execute([$uploaded]);
            $_SESSION['flash'] = ['type' => 'success', 'message' => 'Foto banner Tim KKN berhasil diperbarui.'];
        }
        header("Location: tim-kkn.php");
        exit;
    } elseif ($action === 'reset_banner_kkn') {
        $stmt = $pdo->prepare("INSERT INTO settings (key_name, key_value) VALUES ('header_tim_kkn', '') ON DUPLICATE KEY UPDATE key_value = VALUES(key_value)");
        $stmt->execute();
        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Banner Tim KKN dikembalikan ke background default.'];
        header("Location: tim-kkn.php");
        exit;
    } elseif ($action === 'save_tim') {
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
        sync_tim_kkn_json($pdo);
        header("Location: tim-kkn.php");
        exit;
    } elseif ($action === 'delete_tim') {
        $id = (int)$_POST['id'];
        $stmt = $pdo->prepare("DELETE FROM tim_kkn WHERE id=?");
        $stmt->execute([$id]);
        sync_tim_kkn_json($pdo);
        $_SESSION['flash'] = ['type' => 'success', 'message' => "Data berhasil dihapus."];
        header("Location: tim-kkn.php");
        exit;
    }
}

$stmtBanner = $pdo->prepare("SELECT key_value FROM settings WHERE key_name = 'header_tim_kkn'");
$stmtBanner->execute();
$banner_tim_kkn = $stmtBanner->fetchColumn();

$tim_kkn = [];
try {
    $tim_kkn = $pdo->query("SELECT * FROM tim_kkn ORDER BY (CASE WHEN jabatan LIKE '%Dosen%' OR jabatan LIKE '%DPL%' THEN 1 WHEN jabatan LIKE '%Kordes%' OR jabatan LIKE '%Ketua%' THEN 2 WHEN jabatan LIKE '%Sekretaris%' THEN 3 WHEN jabatan LIKE '%Bendahara%' THEN 4 ELSE 5 END) ASC, id ASC")->fetchAll();
} catch (Exception $e) {}

if (empty($tim_kkn)) {
    $dataFile = __DIR__ . '/../data/tim-kkn.json';
    if (is_file($dataFile)) {
        $jsonTim = json_decode(file_get_contents($dataFile), true);
        if (is_array($jsonTim)) {
            $tim_kkn = $jsonTim;
        }
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
<link rel="icon" type="image/png" href="../assets/img/lambang-kota.png">
</head>
<body>
<div class="admin-shell">
  <?php include __DIR__ . '/includes/sidebar.php'; ?>

  <main class="admin-main">
    <div class="admin-topbar">
      <div>
        <h1>Tim KKN / Relawan</h1>
        <p>Kelola data mahasiswa KKN atau relawan serta foto banner halaman Tim KKN.</p>
      </div>
    </div>

    <?php if ($flash): ?>
      <div class="alert alert-<?= $flash['type'] ?>"><?= htmlspecialchars($flash['message']) ?></div>
    <?php endif; ?>

    <!-- Card Kelola Banner Tim KKN -->
    <div class="section-card" style="margin-bottom: 24px;">
        <div style="display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 16px;">
            <div>
                <h2 style="font-size: 1.1rem; margin: 0 0 4px 0;"><i class="fa-solid fa-image" style="color: var(--teal-600);"></i> Banner Header Tim KKN</h2>
                <p style="margin: 0; font-size: 0.85rem; color: var(--ink-soft);">Ganti foto latar belakang header pada halaman Tim KKN publik.</p>
            </div>
        </div>

        <div style="margin-top: 16px; display: flex; flex-wrap: wrap; gap: 16px; align-items: stretch;">
            <div style="flex: 1; min-width: 260px; aspect-ratio: 4 / 1; border-radius: 12px; overflow: hidden; background: #ecfdf5; border: 1px solid #e2e8f0; position: relative;">
                <?php if (!empty($banner_tim_kkn)): ?>
                    <img src="../<?= htmlspecialchars($banner_tim_kkn) ?>" style="width: 100%; height: 100%; object-fit: cover;" alt="Banner Tim KKN">
                <?php else: ?>
                    <div style="height: 100%; display: flex; align-items: center; justify-content: center; color: #059669; font-weight: 600; font-size: 0.85rem; border: 2px dashed #a7f3d0;"><i class="fa-solid fa-check-circle" style="margin-right: 6px;"></i> Background Default (Hijau Kangkung)</div>
                <?php endif; ?>
            </div>

            <div style="display: flex; flex-direction: column; gap: 10px;">
                <form method="POST" enctype="multipart/form-data" style="margin:0;">
                    <input type="hidden" name="action" value="update_banner_kkn">
                    <label class="btn btn-primary" style="cursor: pointer; display: inline-flex; align-items: center; gap: 8px; width: 100%; justify-content: center;">
                        <i class="fa-solid fa-camera"></i> Ganti Banner
                        <input type="file" name="banner_foto" accept=".jpg,.jpeg,.png,.webp" required class="cropper-upload-input" data-aspect-ratio="4/1" style="display: none;">
                    </label>
                </form>
                <?php if (!empty($banner_tim_kkn)): ?>
                <form method="POST" style="margin:0;">
                    <input type="hidden" name="action" value="reset_banner_kkn">
                    <button type="submit" class="btn btn-outline" style="color: #ef4444; border-color: #fca5a5; background: #fee2e2; width: 100%;">
                        <i class="fa-solid fa-rotate-left"></i> Reset ke Default
                    </button>
                </form>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Section Data Anggota -->
    <div class="section-card">
        <h2 style="font-size: 1.1rem; margin: 0 0 16px 0;"><i class="fa-solid fa-users" style="color: var(--teal-600);"></i> Data Anggota Tim</h2>
        <div class="person-grid">
            <div class="person-card add-person" onclick="openModal()">
                <i class="fa-solid fa-plus-circle"></i>
                <strong>Tambah Anggota</strong>
            </div>

            <?php foreach ($tim_kkn as $p): ?>
                <div class="person-card">
                    <img src="<?= get_admin_photo_url($p['foto'] ?? '') ?>" class="person-photo" alt="<?= htmlspecialchars($p['nama']) ?>" onerror="this.src='../assets/img/placeholder-default.jpg'">
                    <div class="person-actions">
                        <button class="icon-btn edit" onclick='editPerson(<?= json_encode($p, JSON_HEX_APOS | JSON_HEX_TAG) ?>)'><i class="fa-solid fa-pen"></i></button>
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
        
        <div style="margin-top:20px;">
            <button type="submit" class="btn btn-primary" style="width: 100%;">Simpan Data</button>
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
<?php include __DIR__ . '/includes/cropper_modal.php'; ?>
</body>
</html>
