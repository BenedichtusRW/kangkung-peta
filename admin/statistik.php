<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../config_db.php';

$pdo = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save_statistik') {
    
    // 1. Save Basic Stats to `statistik`
    $jumlah_penduduk = trim($_POST['jumlah_penduduk'] ?? '');
    $jumlah_kk = trim($_POST['jumlah_kk'] ?? '');
    $laki = trim($_POST['laki_laki'] ?? '');
    $perempuan = trim($_POST['perempuan'] ?? '');
    
    $stmtStat = $pdo->prepare("INSERT INTO statistik (key_name, key_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE key_value = VALUES(key_value)");
    
    $stmtStat->execute(['jumlah_penduduk', $jumlah_penduduk]);
    $stmtStat->execute(['jumlah_kk', $jumlah_kk]);
    $stmtStat->execute(['terakhir_diperbarui', date('Y-m-d H:i:s')]);
    
    $gender = json_encode(['laki_laki' => (int)$laki, 'perempuan' => (int)$perempuan]);
    $stmtStat->execute(['penduduk_per_jenis_kelamin', $gender]);

    // 2. Save Lingkungan to `statistik`
    $lingkungan_nama = $_POST['lingkungan_nama'] ?? [];
    $lingkungan_rt = $_POST['lingkungan_rt'] ?? [];
    $lingkungan_data = [];
    for($i = 0; $i < count($lingkungan_nama); $i++) {
        if(trim($lingkungan_nama[$i]) !== '') {
            $lingkungan_data[] = [
                'nama' => trim($lingkungan_nama[$i]),
                'jumlah_rt' => (int)$lingkungan_rt[$i]
            ];
        }
    }
    $stmtStat->execute(['lingkungan', json_encode($lingkungan_data)]);

    // 3. Save Jenis Pekerjaan to `konten`
    $stmtKonten = $pdo->prepare("INSERT INTO konten (key_name, key_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE key_value = VALUES(key_value)");
    
    $pekerjaan_nama = $_POST['pekerjaan_nama'] ?? [];
    $pekerjaan_jumlah = $_POST['pekerjaan_jumlah'] ?? [];
    $pekerjaan_data = [];
    for($i = 0; $i < count($pekerjaan_nama); $i++) {
        if(trim($pekerjaan_nama[$i]) !== '') {
            $pekerjaan_data[] = [
                'nama' => trim($pekerjaan_nama[$i]),
                'jumlah' => (int)$pekerjaan_jumlah[$i]
            ];
        }
    }
    $stmtKonten->execute(['jenis_pekerjaan', json_encode($pekerjaan_data)]);

    // 4. Save Data Per RT to `konten` (From Raw JSON string)
    $data_per_rt_json = $_POST['data_per_rt'] ?? '[]';
    if(json_decode($data_per_rt_json) !== null) {
        $stmtKonten->execute(['data_per_rt', $data_per_rt_json]);
    }

    $_SESSION['flash'] = ['type' => 'success', 'message' => "Data Statistik berhasil diperbarui secara menyeluruh."];
    header("Location: statistik.php");
    exit;
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    require_once __DIR__ . '/includes/functions.php';
    if ($_POST['action'] === 'update_banner_statistik') {
        $uploaded = handle_image_upload('banner_foto');
        if ($uploaded) {
            $stmt = $pdo->prepare("INSERT INTO settings (key_name, key_value) VALUES ('header_statistik', ?) ON DUPLICATE KEY UPDATE key_value = VALUES(key_value)");
            $stmt->execute([$uploaded]);
            $_SESSION['flash'] = ['type' => 'success', 'message' => 'Foto banner Statistik berhasil diperbarui.'];
        }
        header("Location: statistik.php");
        exit;
    } elseif ($_POST['action'] === 'reset_banner_statistik') {
        $stmt = $pdo->prepare("INSERT INTO settings (key_name, key_value) VALUES ('header_statistik', '') ON DUPLICATE KEY UPDATE key_value = VALUES(key_value)");
        $stmt->execute();
        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Banner Statistik dikembalikan ke background default.'];
        header("Location: statistik.php");
        exit;
    }
}

// Fetch current stats
$stmt = $pdo->query("SELECT * FROM statistik");
$stats = [];
while ($row = $stmt->fetch()) {
    $stats[$row['key_name']] = $row['key_value'];
}

$gender = json_decode($stats['penduduk_per_jenis_kelamin'] ?? '{}', true);
$laki = $gender['laki_laki'] ?? 0;
$perempuan = $gender['perempuan'] ?? 0;
$lingkungan = json_decode($stats['lingkungan'] ?? '[]', true);

// Fetch Konten (Jenis Pekerjaan & Data Per RT)
$stmtKonten = $pdo->query("SELECT * FROM konten WHERE key_name IN ('jenis_pekerjaan', 'data_per_rt')");
$konten = [];
while ($row = $stmtKonten->fetch()) {
    $konten[$row['key_name']] = $row['key_value'];
}

$jenis_pekerjaan = json_decode($konten['jenis_pekerjaan'] ?? '[]', true);
$data_per_rt_json = $konten['data_per_rt'] ?? '[]';
if(empty($data_per_rt_json)) $data_per_rt_json = '[]';
$data_per_rt_pretty = json_encode(json_decode($data_per_rt_json), JSON_PRETTY_PRINT);

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Statistik Kelurahan | <?= NAMA_KELURAHAN ?></title>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Sora:wght@600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
<link rel="stylesheet" href="../assets/css/admin.css?v=<?= time() ?>">
<!-- SheetJS for Excel Parsing -->
<script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>
<style>
.stats-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px; }
.stats-card { background: var(--paper); border: 1px solid var(--line); border-radius: var(--radius-md); padding: 24px; display:flex; align-items:center; gap:16px; }
.stats-icon { width: 50px; height: 50px; border-radius: 50%; background: var(--teal-100); color: var(--teal-700); display: flex; align-items: center; justify-content: center; font-size: 24px; flex-shrink:0; }
.stats-info { flex: 1; }
.stats-info label { display: block; font-size: 13px; color: var(--ink-soft); margin-bottom: 4px; font-weight:600;}
.stats-info input { width: 100%; padding: 8px 12px; font-size: 16px; border: 1px solid var(--line); border-radius: 6px; font-family:inherit;}

/* Section Headers */
.section-title { font-size: 18px; font-weight: 700; color: var(--ink); margin: 32px 0 16px; padding-bottom: 12px; border-bottom: 1px solid var(--line); display:flex; justify-content:space-between; align-items:center;}
.section-title:first-of-type { margin-top: 0; }

/* Dynamic Rows */
.dynamic-row { display: flex; gap: 12px; margin-bottom: 12px; align-items: center; }
.dynamic-row input { flex: 1; padding: 10px 12px; border: 1px solid var(--line); border-radius: 6px; font-family:inherit; }
.btn-remove { background: #fee2e2; color: #b91c1c; border: none; padding: 10px 16px; border-radius: 6px; cursor: pointer; font-weight: 600; transition:0.2s;}
.btn-remove:hover { background: #fca5a5; }
/* RT Builder */
.rt-card { background: #fff; border: 1px solid var(--line); border-radius: 8px; padding: 16px; margin-bottom: 16px; position: relative; }
.rt-card-header { display: flex; gap: 12px; margin-bottom: 16px; align-items: flex-end; }
.rt-card-header > div { flex: 1; }
.rt-card-header label { font-size: 12px; color: var(--ink-soft); font-weight: 600; display: block; margin-bottom: 4px; }
.rt-card-header input { width: 100%; padding: 8px 12px; border: 1px solid var(--line); border-radius: 6px; font-family: inherit; }
.pek-row { display: flex; gap: 8px; margin-bottom: 8px; }
.pek-row input { flex: 1; padding: 6px 10px; border: 1px solid var(--line); border-radius: 4px; font-size: 13px; }
.btn-sm-remove { background: #fee2e2; color: #b91c1c; border: none; padding: 6px 10px; border-radius: 4px; cursor: pointer; }
.btn-rt-remove { position: absolute; top: 16px; right: 16px; background: #fee2e2; color: #b91c1c; border: none; padding: 8px 12px; border-radius: 6px; cursor: pointer; font-size: 12px; font-weight: 600; }
</style>
<link rel="icon" type="image/png" href="../assets/img/lambang-kota.png">
</head>
<body>
<div class="admin-shell">
  <?php include __DIR__ . '/includes/sidebar.php'; ?>

  <main class="admin-main">
    <div class="admin-topbar">
      <div>
        <h1>Statistik Kelurahan</h1>
        <p>Perbarui angka statistik agregat demografi, pekerjaan, lingkungan, dan per-RT.</p>
      </div>
    </div>

    <?php if ($flash): ?>
      <div class="alert alert-<?= $flash['type'] ?>"><?= $flash['message'] ?></div>
    <?php endif; ?>

    <!-- Banner Header -->
    <div class="section-card" style="margin-bottom: 24px; padding: 24px; background: white; border-radius: 12px; border: 1px solid var(--line);">
        <div style="display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 16px;">
            <div>
                <h2 style="font-size: 1.1rem; margin: 0 0 4px 0;"><i class="fa-solid fa-image" style="color: var(--teal-600);"></i> Banner Header Statistik</h2>
                <p style="margin: 0; font-size: 0.85rem; color: var(--ink-soft);">Ganti foto latar belakang header pada halaman Statistik Kelurahan publik.</p>
            </div>
        </div>

        <div style="margin-top: 16px; display: flex; flex-wrap: wrap; gap: 16px; align-items: stretch;">
            <div style="flex: 1; min-width: 260px; aspect-ratio: 4 / 1; border-radius: 12px; overflow: hidden; background: #ecfdf5; border: 1px solid #e2e8f0; position: relative;">
                <?php if (!empty($banner_statistik)): ?>
                    <img src="../<?= htmlspecialchars($banner_statistik) ?>" style="width: 100%; height: 100%; object-fit: cover;" alt="Banner Statistik">
                <?php else: ?>
                    <div style="height: 100%; display: flex; align-items: center; justify-content: center; color: #059669; font-weight: 600; font-size: 0.85rem; border: 2px dashed #a7f3d0;"><i class="fa-solid fa-check-circle" style="margin-right: 6px;"></i> Background Default (Hijau Kangkung)</div>
                <?php endif; ?>
            </div>

            <div style="display: flex; flex-direction: column; gap: 10px;">
                <form method="POST" enctype="multipart/form-data" style="margin:0;">
                    <input type="hidden" name="action" value="update_banner_statistik">
                    <label class="btn btn-primary" style="cursor: pointer; display: inline-flex; align-items: center; gap: 8px; width: 100%; justify-content: center;">
                        <i class="fa-solid fa-camera"></i> Ganti Banner
                        <input type="file" name="banner_foto" accept=".jpg,.jpeg,.png,.webp" required class="cropper-upload-input" data-aspect-ratio="4/1" style="display: none;">
                    </label>
                </form>
                <?php if (!empty($banner_statistik)): ?>
                <form method="POST" style="margin:0;">
                    <input type="hidden" name="action" value="reset_banner_statistik">
                    <button type="submit" class="btn btn-outline" style="color: #ef4444; border-color: #fca5a5; background: #fee2e2; width: 100%;">
                        <i class="fa-solid fa-rotate-left"></i> Reset ke Default
                    </button>
                </form>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="admin-card">
      <form method="POST" action="" id="statistikForm">
        <input type="hidden" name="action" value="save_statistik">
        
        <!-- SECTION 1: STATISTIK UTAMA -->
        <div class="section-title">1. Statistik Utama & Gender</div>
        <div class="stats-grid">
          <div class="stats-card">
            <div class="stats-icon"><i class="fa-solid fa-users"></i></div>
            <div class="stats-info">
              <label>Total Penduduk</label>
              <input type="number" name="jumlah_penduduk" value="<?= htmlspecialchars($stats['jumlah_penduduk'] ?? '') ?>" required>
            </div>
          </div>
          <div class="stats-card">
            <div class="stats-icon"><i class="fa-solid fa-home"></i></div>
            <div class="stats-info">
              <label>Jumlah KK</label>
              <input type="number" name="jumlah_kk" value="<?= htmlspecialchars($stats['jumlah_kk'] ?? '') ?>" required>
            </div>
          </div>
          <div class="stats-card">
            <div class="stats-icon"><i class="fa-solid fa-mars" style="color: #A0522D;"></i></div>
            <div class="stats-info">
              <label>Laki-Laki</label>
              <input type="number" name="laki_laki" value="<?= htmlspecialchars($laki) ?>" required>
            </div>
          </div>
          <div class="stats-card">
            <div class="stats-icon"><i class="fa-solid fa-venus" style="color: #DB2777;"></i></div>
            <div class="stats-info">
              <label>Perempuan</label>
              <input type="number" name="perempuan" value="<?= htmlspecialchars($perempuan) ?>" required>
            </div>
          </div>
        </div>

        <!-- SECTION 2: LINGKUNGAN -->
        <div class="section-title">2. Data Lingkungan</div>
        <div id="lingkunganContainer">
          <?php if (empty($lingkungan)): ?>
            <div class="dynamic-row">
              <input type="text" name="lingkungan_nama[]" placeholder="Nama Lingkungan (Misal: I, II, dll)" required>
              <input type="number" name="lingkungan_rt[]" placeholder="Jumlah RT" required>
              <button type="button" class="btn-remove" onclick="this.parentElement.remove()"><i class="fa-solid fa-trash"></i></button>
            </div>
          <?php else: ?>
            <?php foreach ($lingkungan as $l): ?>
            <div class="dynamic-row">
              <input type="text" name="lingkungan_nama[]" placeholder="Nama Lingkungan" value="<?= htmlspecialchars($l['nama'] ?? '') ?>" required>
              <input type="number" name="lingkungan_rt[]" placeholder="Jumlah RT" value="<?= htmlspecialchars($l['jumlah_rt'] ?? '') ?>" required>
              <button type="button" class="btn-remove" onclick="this.parentElement.remove()"><i class="fa-solid fa-trash"></i></button>
            </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
        <button type="button" class="btn-add" onclick="addLingkungan()"><i class="fa-solid fa-plus"></i> Tambah Lingkungan</button>

        <!-- SECTION 3: PEKERJAAN -->
        <div class="section-title">3. Sebaran Jenis Pekerjaan</div>
        <div id="pekerjaanContainer">
          <?php if (empty($jenis_pekerjaan)): ?>
            <div class="dynamic-row">
              <input type="text" name="pekerjaan_nama[]" placeholder="Jenis Pekerjaan" required>
              <input type="number" name="pekerjaan_jumlah[]" placeholder="Jumlah Orang" required>
              <button type="button" class="btn-remove" onclick="this.parentElement.remove()"><i class="fa-solid fa-trash"></i></button>
            </div>
          <?php else: ?>
            <?php foreach ($jenis_pekerjaan as $p): ?>
            <div class="dynamic-row">
              <input type="text" name="pekerjaan_nama[]" placeholder="Jenis Pekerjaan" value="<?= htmlspecialchars($p['nama'] ?? '') ?>" required>
              <input type="number" name="pekerjaan_jumlah[]" placeholder="Jumlah Orang" value="<?= htmlspecialchars($p['jumlah'] ?? '') ?>" required>
              <button type="button" class="btn-remove" onclick="this.parentElement.remove()"><i class="fa-solid fa-trash"></i></button>
            </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
        <button type="button" class="btn-add" onclick="addPekerjaan()"><i class="fa-solid fa-plus"></i> Tambah Pekerjaan</button>

        <!-- SECTION 4: DATA PER RT (DYNAMIC BUILDER) -->
        <div class="section-title">
          4. Data Penduduk Per RT (Formulir Dinamis)
        </div>
        <p style="font-size:13.5px; color:var(--ink-soft); margin-bottom:16px;">Kelola rincian data per RT beserta jenis pekerjaan warganya secara langsung tanpa perlu menyentuh kode JSON.</p>
        
        <div id="rtCardsContainer" style="background:#f8fafc; padding:20px; border-radius:12px; border:1px solid var(--line);">
            <!-- Diisi oleh Javascript -->
        </div>
        <button type="button" class="btn-add" onclick="addRtCard()" style="margin-top:16px;"><i class="fa-solid fa-plus"></i> Tambah Data RT Baru</button>

        <input type="hidden" name="data_per_rt" id="data_per_rt" value="<?= htmlspecialchars($data_per_rt_json) ?>">


        <div style="margin-top: 40px; padding-top:20px; border-top: 1px solid var(--line); display:flex; gap:12px; align-items:center;">
          <button type="submit" class="btn btn-primary" style="padding: 14px 28px; font-size:15px;"><i class="fa-solid fa-save"></i> Simpan Semua Perubahan</button>
        </div>
      </form>
    </div>

  </main>
</div>

<script>
// Logic to add dynamic rows
function addLingkungan() {
  const container = document.getElementById('lingkunganContainer');
  const div = document.createElement('div');
  div.className = 'dynamic-row';
  div.innerHTML = `
    <input type="text" name="lingkungan_nama[]" placeholder="Nama Lingkungan (Misal: I, II, dll)" required>
    <input type="number" name="lingkungan_rt[]" placeholder="Jumlah RT" required>
    <button type="button" class="btn-remove" onclick="this.parentElement.remove()"><i class="fa-solid fa-trash"></i></button>
  `;
  container.appendChild(div);
}

function addPekerjaan() {
  const container = document.getElementById('pekerjaanContainer');
  const div = document.createElement('div');
  div.className = 'dynamic-row';
  div.innerHTML = `
    <input type="text" name="pekerjaan_nama[]" placeholder="Jenis Pekerjaan" required>
    <input type="number" name="pekerjaan_jumlah[]" placeholder="Jumlah Orang" required>
    <button type="button" class="btn-remove" onclick="this.parentElement.remove()"><i class="fa-solid fa-trash"></i></button>
  `;
  container.appendChild(div);
}

// JSON Validation before submit
</script>

<script>
// RT Builder Logic
let rtData = [];
try {
    rtData = JSON.parse(document.getElementById('data_per_rt').value || '[]');
} catch(e) {}

const rtContainer = document.getElementById('rtCardsContainer');
const dataRtInput = document.getElementById('data_per_rt');

function saveRtState() {
    dataRtInput.value = JSON.stringify(rtData);
}

function renderRtCards() {
    rtContainer.innerHTML = '';
    if(rtData.length === 0) {
        rtContainer.innerHTML = '<div style="color:var(--ink-soft); font-size:13px; text-align:center;">Belum ada data RT. Klik Tambah Data RT Baru.</div>';
        return;
    }

    rtData.forEach((rt, idx) => {
        const card = document.createElement('div');
        card.className = 'rt-card';
        
        // Pekerjaan HTML
        let pekHtml = '';
        if(rt.pekerjaan && rt.pekerjaan.length > 0) {
            rt.pekerjaan.forEach((p, pIdx) => {
                pekHtml += `
                <div class="pek-row">
                    <input type="text" placeholder="Nama Pekerjaan" value="${p.nama || ''}" onchange="updatePek(${idx}, ${pIdx}, 'nama', this.value)">
                    <input type="number" placeholder="Jumlah" value="${p.jumlah || 0}" onchange="updatePek(${idx}, ${pIdx}, 'jumlah', this.value)">
                    <button type="button" class="btn-sm-remove" onclick="removePek(${idx}, ${pIdx})"><i class="fa-solid fa-xmark"></i></button>
                </div>
                `;
            });
        } else {
            pekHtml = '<div style="font-size:12px; color:#94a3b8; margin-bottom:8px;">Belum ada rincian pekerjaan.</div>';
        }

        card.innerHTML = `
            <button type="button" class="btn-rt-remove" onclick="removeRt(${idx})"><i class="fa-solid fa-trash"></i> Hapus RT</button>
            <div style="font-size:15px; font-weight:700; color:var(--ink); margin-bottom:12px;">RT ${rt.rt || '-'} (Lingkungan ${rt.lk || '-'})</div>
            
            <div class="rt-card-header" style="padding-right: 120px;">
                <div>
                    <label>Lingkungan (LK)</label>
                    <input type="text" value="${rt.lk || ''}" onchange="updateRt(${idx}, 'lk', this.value)">
                </div>
                <div>
                    <label>Nomor RT</label>
                    <input type="text" value="${rt.rt || ''}" onchange="updateRt(${idx}, 'rt', this.value)">
                </div>
                <div>
                    <label>Laki-laki</label>
                    <input type="number" value="${rt.laki || 0}" onchange="updateRt(${idx}, 'laki', this.value)">
                </div>
                <div>
                    <label>Perempuan</label>
                    <input type="number" value="${rt.perempuan || 0}" onchange="updateRt(${idx}, 'perempuan', this.value)">
                </div>
                <div>
                    <label>Total Jiwa</label>
                    <input type="number" value="${rt.total || 0}" onchange="updateRt(${idx}, 'total', this.value)">
                </div>
            </div>

            <div style="background:#f1f5f9; padding:12px; border-radius:6px; border:1px solid #e2e8f0;">
                <div style="font-size:13px; font-weight:600; margin-bottom:8px; color:#475569;">Rincian Pekerjaan di RT ini:</div>
                <div id="pek_list_${idx}">${pekHtml}</div>
                <button type="button" class="btn-add" style="padding:6px 12px; font-size:12px; margin-top:4px;" onclick="addPek(${idx})"><i class="fa-solid fa-plus"></i> Tambah Pekerjaan</button>
            </div>
        `;
        rtContainer.appendChild(card);
    });
}

// RT Actions
window.addRtCard = function() {
    rtData.push({ lk: '', rt: '', laki: 0, perempuan: 0, total: 0, pekerjaan: [] });
    saveRtState();
    renderRtCards();
};
window.removeRt = function(idx) {
    if(confirm('Yakin ingin menghapus data RT ini?')) {
        rtData.splice(idx, 1);
        saveRtState();
        renderRtCards();
    }
};
window.updateRt = function(idx, field, val) {
    if(['laki', 'perempuan', 'total'].includes(field)) val = parseInt(val) || 0;
    rtData[idx][field] = val;
    // Auto calculate total if laki/perempuan changes
    if(field === 'laki' || field === 'perempuan') {
        rtData[idx].total = (parseInt(rtData[idx].laki)||0) + (parseInt(rtData[idx].perempuan)||0);
    }
    saveRtState();
    renderRtCards(); // Re-render to reflect calculated total
};

// Pekerjaan Actions
window.addPek = function(rtIdx) {
    if(!rtData[rtIdx].pekerjaan) rtData[rtIdx].pekerjaan = [];
    rtData[rtIdx].pekerjaan.push({ nama: '', jumlah: 0 });
    saveRtState();
    renderRtCards();
};
window.removePek = function(rtIdx, pekIdx) {
    rtData[rtIdx].pekerjaan.splice(pekIdx, 1);
    saveRtState();
    renderRtCards();
};
window.updatePek = function(rtIdx, pekIdx, field, val) {
    if(field === 'jumlah') val = parseInt(val) || 0;
    rtData[rtIdx].pekerjaan[pekIdx][field] = val;
    saveRtState();
};

// Initial Render
renderRtCards();
</div>
<?php include __DIR__ . '/includes/cropper_modal.php'; ?>
</body>
</html>
