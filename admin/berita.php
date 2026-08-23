<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../config_db.php';
require_once __DIR__ . '/includes/functions.php';

$pdo = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'save_berita') {
        $id = !empty($_POST['id']) ? (int)$_POST['id'] : null;
        $judul = trim($_POST['judul'] ?? '');
        $ringkasan = trim($_POST['ringkasan'] ?? '');
        $konten = trim($_POST['konten'] ?? '');
        $penulis = trim($_POST['penulis'] ?? $_SESSION['admin_username']);
        $tanggal = $_POST['tanggal'] ?? date('Y-m-d');
        $status = $_POST['status'] ?? 'published';
        
        // Auto slug
        $baseSlug = preg_replace('/[^a-z0-9]+/i', '-', strtolower($judul));
        $slug = trim($baseSlug, '-') ?: 'berita';
        
        $uploaded = handle_image_upload('gambar');
        
        if ($id) {
            // Keep old slug if edit? Better to keep it to avoid broken links unless title changes drastically.
            // Let's just keep the old slug for simplicity, or re-generate if we wanted to.
            // We'll leave slug alone on edit, unless we want to change it.
            if ($uploaded) {
                $stmt = $pdo->prepare("UPDATE berita SET judul=?, ringkasan=?, konten=?, penulis=?, tanggal=?, status=?, gambar=? WHERE id=?");
                $stmt->execute([$judul, $ringkasan, $konten, $penulis, $tanggal, $status, $uploaded, $id]);
            } else {
                $stmt = $pdo->prepare("UPDATE berita SET judul=?, ringkasan=?, konten=?, penulis=?, tanggal=?, status=? WHERE id=?");
                $stmt->execute([$judul, $ringkasan, $konten, $penulis, $tanggal, $status, $id]);
            }
            $_SESSION['flash'] = ['type' => 'success', 'message' => "Berita berhasil diperbarui."];
        } else {
            // Check slug collision
            $num = 2;
            $slugTest = $slug;
            while(true) {
                $check = $pdo->prepare("SELECT id FROM berita WHERE slug = ?");
                $check->execute([$slugTest]);
                if (!$check->fetch()) break;
                $slugTest = $slug . '-' . $num++;
            }
            $slug = $slugTest;
            
            $stmt = $pdo->prepare("INSERT INTO berita (slug, judul, ringkasan, konten, gambar, penulis, tanggal, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$slug, $judul, $ringkasan, $konten, $uploaded, $penulis, $tanggal, $status]);
            $_SESSION['flash'] = ['type' => 'success', 'message' => "Berita berhasil diterbitkan."];
        }
        header("Location: berita.php");
        exit;
    } elseif ($action === 'delete_berita') {
        $id = (int)$_POST['id'];
        $stmt = $pdo->prepare("DELETE FROM berita WHERE id=?");
        $stmt->execute([$id]);
        $_SESSION['flash'] = ['type' => 'success', 'message' => "Berita berhasil dihapus."];
        header("Location: berita.php");
        exit;
    } elseif ($action === 'update_banner_berita') {
        $uploaded = handle_image_upload('banner_foto');
        if ($uploaded) {
            $stmt = $pdo->prepare("INSERT INTO settings (key_name, key_value) VALUES ('header_berita', ?) ON DUPLICATE KEY UPDATE key_value = VALUES(key_value)");
            $stmt->execute([$uploaded]);
            $_SESSION['flash'] = ['type' => 'success', 'message' => 'Foto banner Berita berhasil diperbarui.'];
        }
        header("Location: berita.php");
        exit;
    } elseif ($action === 'reset_banner_berita') {
        $stmt = $pdo->prepare("INSERT INTO settings (key_name, key_value) VALUES ('header_berita', '') ON DUPLICATE KEY UPDATE key_value = VALUES(key_value)");
        $stmt->execute();
        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Banner Berita dikembalikan ke background default.'];
        header("Location: berita.php");
        exit;
    }
}

$stmtBanner = $pdo->query("SELECT key_value FROM settings WHERE key_name = 'header_berita'");
$banner_berita = $stmtBanner->fetchColumn() ?: '';

$stmt = $pdo->query("SELECT * FROM berita ORDER BY tanggal DESC, id DESC");
$berita = $stmt->fetchAll();

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Manajemen Berita | <?= NAMA_KELURAHAN ?></title>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Sora:wght@600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
<link rel="stylesheet" href="../assets/css/admin.css?v=<?= time() ?>">
<style>
.berita-list { display: flex; flex-direction: column; gap: 16px; }
.berita-item { display: flex; gap: 20px; background: var(--white); padding: 16px; border-radius: var(--radius-md); border: 1px solid var(--line); align-items:center;}
.berita-thumb { width: 120px; height: 80px; border-radius: 8px; object-fit: cover; background:var(--paper); }
.berita-content { flex: 1; }
.berita-content h3 { margin: 0 0 4px 0; font-size: 16px; color: var(--teal-900); }
.berita-meta { font-size: 13px; color: var(--ink-soft); margin-bottom: 8px; }
.berita-meta span { margin-right: 12px; }
.badge { padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: bold; text-transform:uppercase;}
.badge-published { background: #dcfce7; color: #166534; }
.badge-pending { background: #fef08a; color: #854d0e; }
.badge-rejected { background: #fee2e2; color: #991b1b; }
.berita-actions { display: flex; gap: 8px; }

/* Modal Wide */
.admin-modal-card.wide { max-width: 700px; }
textarea { width: 100%; padding: 10px; border: 1px solid var(--line); border-radius: 6px; font-family: inherit; font-size: 14px; }
</style>
<link rel="icon" type="image/png" href="../assets/img/favicon.png?v=2">
</head>
<body>
<div class="admin-shell">
  <?php include __DIR__ . '/includes/sidebar.php'; ?>

  <main class="admin-main">
    <div class="admin-topbar">
      <div>
        <h1>Manajemen Berita</h1>
        <p>Kelola artikel, pengumuman, dan berita kelurahan.</p>
      </div>
      <button class="btn btn-primary" onclick="openModal()"><i class="fa-solid fa-plus"></i> Tulis Berita Baru</button>
    </div>

    <?php if ($flash): ?>
      <div class="alert alert-<?= $flash['type'] ?>"><?= htmlspecialchars($flash['message']) ?></div>
    <?php endif; ?>

    <!-- Banner Header -->
    <div class="section-card" style="margin-bottom: 24px;">
        <div style="display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 16px;">
            <div>
                <h2 style="font-size: 1.1rem; margin: 0 0 4px 0;"><i class="fa-solid fa-image" style="color: var(--teal-600);"></i> Banner Header Berita</h2>
                <p style="margin: 0; font-size: 0.85rem; color: var(--ink-soft);">Ganti foto latar belakang header pada halaman Berita publik.</p>
            </div>
        </div>

        <div style="margin-top: 16px; display: flex; flex-wrap: wrap; gap: 16px; align-items: stretch;">
            <div style="flex: 1; min-width: 260px; aspect-ratio: 4 / 1; border-radius: 12px; overflow: hidden; background: #ecfdf5; border: 1px solid #e2e8f0; position: relative;">
                <?php if (!empty($banner_berita)): ?>
                    <img src="../<?= htmlspecialchars($banner_berita) ?>" style="width: 100%; height: 100%; object-fit: cover;" alt="Banner Berita">
                <?php else: ?>
                    <div style="height: 100%; display: flex; align-items: center; justify-content: center; color: #059669; font-weight: 600; font-size: 0.85rem; border: 2px dashed #a7f3d0;"><i class="fa-solid fa-check-circle" style="margin-right: 6px;"></i> Background Default (Hijau Kangkung)</div>
                <?php endif; ?>
            </div>

            <div style="display: flex; flex-direction: column; gap: 10px;">
                <form method="POST" enctype="multipart/form-data" style="margin:0;">
                    <input type="hidden" name="action" value="update_banner_berita">
                    <label class="btn btn-primary" style="cursor: pointer; display: inline-flex; align-items: center; gap: 8px; width: 100%; justify-content: center;">
                        <i class="fa-solid fa-camera"></i> Ganti Banner
                        <input type="file" name="banner_foto" accept=".jpg,.jpeg,.png,.webp" required class="cropper-upload-input" data-aspect-ratio="4/1" style="display: none;">
                    </label>
                </form>
                <?php if (!empty($banner_berita)): ?>
                <form method="POST" style="margin:0;">
                    <input type="hidden" name="action" value="reset_banner_berita">
                    <button type="submit" class="btn btn-outline" style="color: #ef4444; border-color: #fca5a5; background: #fee2e2; width: 100%;">
                        <i class="fa-solid fa-rotate-left"></i> Reset ke Default
                    </button>
                </form>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="section-card">
      <div class="berita-list">
        <?php if (empty($berita)): ?>
            <p style="color:var(--ink-soft); text-align:center; padding: 40px 0;">Belum ada berita.</p>
        <?php else: ?>
            <?php foreach ($berita as $b): ?>
                <div class="berita-item">
                    <?php if ($b['gambar']): ?>
                        <img src="../<?= htmlspecialchars($b['gambar']) ?>" class="berita-thumb" alt="Thumbnail">
                    <?php else: ?>
                        <div class="berita-thumb" style="display:flex; align-items:center; justify-content:center; color:#ccc;"><i class="fa-solid fa-image fa-2x"></i></div>
                    <?php endif; ?>
                    
                    <div class="berita-content">
                        <h3><?= htmlspecialchars($b['judul']) ?></h3>
                        <div class="berita-meta">
                            <span><i class="fa-regular fa-calendar"></i> <?= htmlspecialchars($b['tanggal']) ?></span>
                            <span><i class="fa-regular fa-user"></i> <?= htmlspecialchars($b['penulis']) ?></span>
                            <span class="badge badge-<?= $b['status'] ?>"><?= $b['status'] ?></span>
                        </div>
                        <p style="margin:0; font-size:14px; color:var(--ink-soft); display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden;">
                            <?= htmlspecialchars($b['ringkasan']) ?>
                        </p>
                    </div>
                    
                    <div class="berita-actions">
                        <button class="btn" style="padding:8px 12px;" onclick='editBerita(<?= json_encode($b, JSON_HEX_APOS | JSON_HEX_TAG) ?>)'><i class="fa-solid fa-pen"></i> Edit</button>
                        <form method="POST" onsubmit="return confirm('Hapus berita ini?');">
                            <input type="hidden" name="action" value="delete_berita">
                            <input type="hidden" name="id" value="<?= $b['id'] ?>">
                            <button type="submit" class="btn" style="padding:8px 12px; color:red; border-color:red; background:white;"><i class="fa-solid fa-trash"></i></button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>
  </main>
</div>

<!-- Modal Form Berita -->
<div id="modalForm" class="admin-modal-overlay">
  <div class="admin-modal-card wide">
    <div class="admin-modal-header">
      <h2 id="modalTitle">Tulis Berita Baru</h2>
      <button class="admin-modal-close" onclick="closeModal()">&times;</button>
    </div>
    <div class="admin-modal-body" style="max-height: 70vh; overflow-y: auto;">
      <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="action" value="save_berita">
        <input type="hidden" name="id" id="formId">
        
        <div class="field">
          <label>Judul Berita</label>
          <input type="text" name="judul" id="formJudul" required>
        </div>
        
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
            <div class="field">
                <label>Tanggal Terbit</label>
                <input type="date" name="tanggal" id="formTanggal" required value="<?= date('Y-m-d') ?>">
            </div>
            <div class="field">
                <label>Status</label>
                <select name="status" id="formStatus" style="width:100%; padding:10px; border:1px solid var(--line); border-radius:6px; font-family:inherit;">
                    <option value="published">Published</option>
                    <option value="pending">Pending / Draft</option>
                </select>
            </div>
        </div>

        <div class="field">
          <label>Penulis</label>
          <input type="text" name="penulis" id="formPenulis" value="<?= htmlspecialchars($_SESSION['admin_username']) ?>" required>
        </div>
        
        <div class="field">
          <label>Gambar Cover (Opsional)</label>
          <input type="file" name="gambar" id="formGambar" accept="image/*" style="width:100%; padding:10px; border:1px dashed var(--line); border-radius:6px;">
        </div>

        <div class="field">
          <label>Ringkasan (Singkat)</label>
          <textarea name="ringkasan" id="formRingkasan" rows="3" required></textarea>
        </div>
        
        <div class="field">
          <label>Konten Lengkap (Gunakan HTML dasar diperbolehkan)</label>
          <textarea name="konten" id="formKonten" rows="10" required></textarea>
        </div>
        
        <div style="margin-top:20px; display:flex; justify-content:flex-end;">
            <button type="submit" class="btn btn-primary"><i class="fa-solid fa-paper-plane"></i> Simpan Berita</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
function openModal() {
    document.getElementById('modalTitle').innerText = 'Tulis Berita Baru';
    document.getElementById('formId').value = '';
    document.getElementById('formJudul').value = '';
    document.getElementById('formTanggal').value = '<?= date('Y-m-d') ?>';
    document.getElementById('formStatus').value = 'published';
    document.getElementById('formPenulis').value = '<?= htmlspecialchars($_SESSION['admin_username']) ?>';
    document.getElementById('formRingkasan').value = '';
    document.getElementById('formKonten').value = '';
    document.getElementById('modalForm').classList.add('show');
    document.getElementById('modalForm').style.display = 'flex';
}
function closeModal() {
    document.getElementById('modalForm').style.display = 'none';
}
function editBerita(data) {
    document.getElementById('modalTitle').innerText = 'Edit Berita';
    document.getElementById('formId').value = data.id;
    document.getElementById('formJudul').value = data.judul;
    document.getElementById('formTanggal').value = data.tanggal;
    document.getElementById('formStatus').value = data.status;
    document.getElementById('formPenulis').value = data.penulis;
    document.getElementById('formRingkasan').value = data.ringkasan;
    document.getElementById('formKonten').value = data.konten;
    document.getElementById('modalForm').classList.add('show');
    document.getElementById('modalForm').style.display = 'flex';
}
</script>
<?php include __DIR__ . '/includes/cropper_modal.php'; ?>
</body>
</html>
