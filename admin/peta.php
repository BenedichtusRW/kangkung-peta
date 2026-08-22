<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../config_db.php';
require_once __DIR__ . '/includes/functions.php';

$pdo = getDB();
$errors = [];

// Handle Form Submission (Add & Edit)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save_poi') {
    $editId = !empty($_POST['id']) ? (int)$_POST['id'] : null;
    
    // Prioritize URL over uploaded file to save space
    $uploadedImage = null;
    if (!empty($_POST['gambar_url'])) {
        $uploadedImage = trim($_POST['gambar_url']);
    } else {
        $uploadedImage = handle_image_upload('gambar_file');
    }

    $formData = build_poi_from_post($_POST, $uploadedImage, $editId ?? 0);
    $errors = validate_poi($formData);

    if (empty($errors)) {
        if ($editId) {
            $stmt = $pdo->prepare("UPDATE pois SET nama=?, kategori=?, deskripsi=?, alamat=?, kontak=?, jam_buka=?, lat=?, lng=?, gambar=? WHERE id=?");
            $stmt->execute([
                $formData['nama'], $formData['kategori'], $formData['deskripsi'], $formData['alamat'],
                $formData['kontak'], $formData['jam_buka'], $formData['lat'], $formData['lng'], $formData['gambar'], $editId
            ]);
            $flashMsg = 'Data "' . $formData['nama'] . '" berhasil diperbarui.';
        } else {
            $stmt = $pdo->prepare("INSERT INTO pois (nama, kategori, deskripsi, alamat, kontak, jam_buka, lat, lng, gambar) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $formData['nama'], $formData['kategori'], $formData['deskripsi'], $formData['alamat'],
                $formData['kontak'], $formData['jam_buka'], $formData['lat'], $formData['lng'], $formData['gambar']
            ]);
            $flashMsg = 'Data "' . $formData['nama'] . '" berhasil ditambahkan.';
        }

        $_SESSION['flash'] = ['type' => 'success', 'message' => $flashMsg];
        header('Location: peta.php');
        exit;
    }
}

$stmt = $pdo->query("SELECT * FROM pois ORDER BY id DESC");
$pois = $stmt->fetchAll();

// Statistik per kategori
$statByKategori = [];
foreach ($pois as $p) {
    $statByKategori[$p['kategori']] = ($statByKategori[$p['kategori']] ?? 0) + 1;
}

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard Admin | <?= NAMA_KELURAHAN ?></title>
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
        <h1>Daftar Tempat</h1>
        <p>Kelola titik lokasi yang tampil di Peta Interaktif <?= NAMA_KELURAHAN ?>.</p>
      </div>
    </div>

    <?php if ($flash): ?>
      <div class="alert alert-<?= $flash['type'] ?>"><?= htmlspecialchars($flash['message']) ?></div>
    <?php endif; ?>
    <?php if (!empty($errors)): ?>
      <div class="alert alert-error">
        <?php foreach ($errors as $e): ?>
          <?= htmlspecialchars($e) ?><br>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <div class="stat-cards">
      <div class="stat-card">
        <div class="num"><?= count($pois) ?></div>
        <div class="label">Total Tempat</div>
      </div>
      <?php foreach ($KATEGORI_PETA as $key => $kat): if ($key === 'semua') continue; ?>
        <div class="stat-card">
          <div class="num">
            <i class="<?= htmlspecialchars($kat['icon'] ?? 'fa-solid fa-circle') ?>" style="font-size: 20px; color: <?= $kat['warna'] ?>; margin-right: 8px; vertical-align: middle;"></i>
            <?= $statByKategori[$key] ?? 0 ?>
          </div>
          <div class="label"><?= htmlspecialchars($kat['label']) ?></div>
        </div>
      <?php endforeach; ?>
    </div>

    <div class="table-card">
      <div class="table-toolbar" style="display: flex; gap: 12px; align-items: center; justify-content: space-between; flex-wrap: wrap;">
        <div style="display: flex; gap: 12px; align-items: center; flex: 1;">
          <input type="search" id="tableSearch" placeholder="Cari nama tempat..." style="flex: 1; max-width: 300px;">
          <button type="button" class="btn btn-primary" onclick="openModal()" style="padding: 9px 16px;">+ Tambah Tempat</button>
        </div>
        <span style="font-size:12.5px;color:var(--ink-soft)"><?= count($pois) ?> data</span>
      </div>
      <table id="poiTable">
        <thead>
          <tr>
            <th>Foto</th>
            <th>Nama Tempat</th>
            <th>Kategori</th>
            <th>Alamat</th>
            <th>Jam Buka</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($pois)): ?>
            <tr class="empty-row"><td colspan="6">Belum ada data tempat. Klik "+ Tambah Tempat" untuk mulai.</td></tr>
          <?php else: foreach (array_reverse($pois) as $p):
            $katInfo = $KATEGORI_PETA[$p['kategori']] ?? ['label' => $p['kategori'], 'warna' => '#999']; ?>
            <tr data-nama="<?= strtolower(htmlspecialchars($p['nama'])) ?>">
              <?php 
                $imgSrc = $p['gambar'] ?: 'assets/img/placeholder-default.jpg';
                $finalSrc = strpos($imgSrc, 'http') === 0 ? $imgSrc : '../' . $imgSrc;
              ?>
              <td><img class="thumb" src="<?= htmlspecialchars($finalSrc) ?>" referrerpolicy="no-referrer" onerror="this.src='../assets/img/placeholder-default.jpg'" alt=""></td>
              <td><strong><?= htmlspecialchars($p['nama']) ?></strong></td>
              <td><span class="badge"><i class="<?= htmlspecialchars($katInfo['icon'] ?? 'fa-solid fa-circle') ?>" style="color: <?= $katInfo['warna'] ?>;"></i> <?= htmlspecialchars($katInfo['label']) ?></span></td>
              <td><?= htmlspecialchars($p['alamat']) ?></td>
              <td><?= htmlspecialchars($p['jam_buka']) ?></td>
              <td>
                <div class="row-actions">
                  <button type="button" class="btn btn-outline btn-sm" onclick='openModal(<?= json_encode($p, JSON_HEX_TAG|JSON_HEX_QUOT|JSON_HEX_APOS) ?>)'>Edit</button>
                  <form method="POST" action="delete.php" onsubmit="return confirm('Hapus &quot;<?= htmlspecialchars(addslashes($p['nama'])) ?>&quot;? Tindakan ini tidak bisa dibatalkan.');" style="display:inline">
                    <input type="hidden" name="id" value="<?= $p['id'] ?>">
                    <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
                  </form>
                </div>
              </td>
            </tr>
          <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>
  </main>
</div>

<!-- Modal Tambah/Edit Tempat -->
<div id="poiModal" class="admin-modal-overlay">
  <div class="admin-modal-card">
    <div class="admin-modal-header">
      <h2 id="modalTitle">Tambah Tempat Baru</h2>
      <button class="admin-modal-close" onclick="closeModal()">&times;</button>
    </div>
    <div class="admin-modal-body">
      <form id="poiForm" method="POST" action="peta.php" enctype="multipart/form-data">
        <input type="hidden" name="action" value="save_poi">
        <input type="hidden" id="form_id" name="id" value="">
        <input type="hidden" id="form_gambar_lama" name="gambar_lama" value="">

        <div class="form-grid">
          <div class="field full">
            <label for="nama">Nama Tempat *</label>
            <input type="text" id="form_nama" name="nama" required placeholder="Contoh: Warung Makan Bu Sari">
          </div>

          <div class="field">
            <label for="kategori">Kategori *</label>
            <select id="form_kategori" name="kategori" required>
              <option value="">-- Pilih Kategori --</option>
              <?php foreach ($KATEGORI_PETA as $key => $kat): if ($key === 'semua') continue; ?>
                <option value="<?= $key ?>"><?= htmlspecialchars($kat['label']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="field">
            <label for="jam_buka">Jam Buka</label>
            <input type="text" id="form_jam_buka" name="jam_buka" placeholder="08:00 - 17:00 atau 24 Jam">
          </div>

          <div class="field full">
            <label for="deskripsi">Deskripsi</label>
            <textarea id="form_deskripsi" name="deskripsi" rows="3" placeholder="Deskripsi singkat tempat ini..."></textarea>
          </div>

          <div class="field full">
            <label for="alamat">Alamat *</label>
            <input type="text" id="form_alamat" name="alamat" required placeholder="Jl. Ikan Kakap, Kangkung, Bandar Lampung">
          </div>

          <div class="field">
            <label for="kontak">Kontak / No. WhatsApp</label>
            <input type="text" id="form_kontak" name="kontak" placeholder="0812xxxxxxx">
            <p class="help-text">Diisi nomor HP diawali 08xx agar tombol WhatsApp di peta berfungsi otomatis.</p>
          </div>

          <div class="field"></div>

          <div class="field">
            <label for="lat">Latitude *</label>
            <input type="text" id="form_lat" name="lat" required placeholder="-5.4496361">
          </div>
          <div class="field">
            <label for="lng">Longitude *</label>
            <input type="text" id="form_lng" name="lng" required placeholder="105.2684878">
          </div>
          <p class="help-text full" style="grid-column:1/-1;margin-top:-8px;">
            Tips: buka lokasi di Google Maps → klik kanan titiknya → koordinat langsung tersalin (format: lat, lng).
          </p>

          <div class="field full">
            <label for="gambar_url">Atau URL Gambar (Dari Google Maps / Internet)</label>
            <input type="url" id="gambar_url" name="gambar_url" placeholder="https://contoh.com/gambar.jpg">
            <p class="help-text">Jika diisi, sistem tidak akan menyimpan file baru ke server (menghemat memori).</p>
          </div>
          <div class="field full" style="margin-top:-8px;">
            <label for="gambar_file">Upload File Manual</label>
            <input type="file" id="gambar_file" name="gambar_file" accept=".jpg,.jpeg,.png,.webp">
            <p class="help-text">Format JPG/PNG/WEBP. Kosongkan kalau tidak ingin mengganti foto atau sudah mengisi URL di atas.</p>
          </div>
        </div>

        <div class="form-actions" style="margin-top: 24px;">
          <button type="submit" class="btn btn-primary" id="btnSubmitForm">Tambah Tempat</button>
          <button type="button" class="btn btn-outline" onclick="closeModal()">Batal</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
  document.getElementById('tableSearch').addEventListener('input', function (e) {
    const q = e.target.value.toLowerCase();
    document.querySelectorAll('#poiTable tbody tr[data-nama]').forEach(function (row) {
      row.style.display = row.dataset.nama.includes(q) ? '' : 'none';
    });
  });

  const modal = document.getElementById('poiModal');
  
  function openModal(poi = null) {
    document.body.style.overflow = 'hidden';
    modal.classList.add('open');
    if (poi) {
      document.getElementById('modalTitle').textContent = 'Edit Tempat';
      document.getElementById('btnSubmitForm').textContent = 'Simpan Perubahan';
      
      document.getElementById('form_id').value = poi.id;
      document.getElementById('form_nama').value = poi.nama || '';
      document.getElementById('form_kategori').value = poi.kategori || '';
      document.getElementById('form_jam_buka').value = poi.jam_buka || '';
      document.getElementById('form_deskripsi').value = poi.deskripsi || '';
      document.getElementById('form_alamat').value = poi.alamat || '';
      document.getElementById('form_kontak').value = poi.kontak || '';
      document.getElementById('form_lat').value = poi.lat || '';
      document.getElementById('form_lng').value = poi.lng || '';
      document.getElementById('form_gambar_lama').value = poi.gambar || '';
      
      const g = poi.gambar || '';
      document.getElementById('gambar_url').value = (g.startsWith('http')) ? g : '';
    } else {
      document.getElementById('modalTitle').textContent = 'Tambah Tempat Baru';
      document.getElementById('btnSubmitForm').textContent = 'Tambah Tempat';
      document.getElementById('poiForm').reset();
      document.getElementById('form_id').value = '';
      document.getElementById('form_gambar_lama').value = '';
      document.getElementById('gambar_url').value = '';
    }
  }

  function closeModal() {
    document.body.style.overflow = '';
    modal.classList.remove('open');
  }

  // Close modal when clicking outside
  modal.addEventListener('click', (e) => {
    if (e.target === modal) closeModal();
  });
</script>
</body>
</html>

