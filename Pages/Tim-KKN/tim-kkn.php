<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../config_db.php';

$assetPrefix = '../../assets/';
$navPrefix = '../';
$activeNav = 'profile';
$pageTitle = 'Tim KKN UIN RIL';

$pdo = getDB();

// Ambil header banner Tim KKN dari database settings
$stmt = $pdo->prepare("SELECT key_value FROM settings WHERE key_name = 'header_tim_kkn'");
$stmt->execute();
$header_tim_kkn = $stmt->fetchColumn();
$bgStyle = '';
if (!empty($header_tim_kkn)) {
    $header_tim_kkn = htmlspecialchars((string)$header_tim_kkn, ENT_QUOTES, 'UTF-8');
    $bgStyle = 'background: linear-gradient(rgba(0,0,0,0.3), rgba(0,0,0,0.5)), url(\'../../' . $header_tim_kkn . '\') center/cover; color: #ffffff;';
}

function get_public_photo_url(?string $path): string {
    if (empty($path)) {
        return '../../assets/img/placeholder-default.jpg';
    }
    if (strpos($path, 'http://') === 0 || strpos($path, 'https://') === 0) {
        return $path;
    }
    $clean = preg_replace('/^(\.\.\/)+/', '', $path);
    return '../../' . ltrim($clean, '/');
}

$tim = [];
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS tim_kkn (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nama VARCHAR(255) NOT NULL,
        jabatan VARCHAR(255) NOT NULL,
        foto VARCHAR(255) DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $stmtCheck = $pdo->query("SELECT COUNT(*) FROM tim_kkn");
    if ($stmtCheck->fetchColumn() < 21) {
        $dataFile = __DIR__ . '/../../data/tim-kkn.json';
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

    $stmt = $pdo->query("SELECT * FROM tim_kkn ORDER BY id ASC");
    $tim = $stmt->fetchAll();
} catch (Exception $e) {}

if (empty($tim) || count($tim) < 21) {
    $dataFile = __DIR__ . '/../../data/tim-kkn.json';
    if (is_file($dataFile)) {
        $data = json_decode(file_get_contents($dataFile), true);
        if (is_array($data)) {
            $tim = $data;
        }
    }
}

// Pisahkan DPL dari anggota mahasiswa
$dpl = null;
$mahasiswa = [];
foreach ($tim as $t) {
    if (stripos($t['jabatan'], 'Dosen Pembimbing') !== false) {
        $dpl = $t;
    } else {
        $mahasiswa[] = $t;
    }
}

include __DIR__ . '/../includes/header.php';
?>

<!-- Section Hero -->
<section class="page-hero" style="<?= $bgStyle ?>">
  <div class="container">
    <div class="breadcrumb">
      <a href="<?= $navPrefix ?>index.php">Beranda</a> <span>/</span> <span>Profile</span> <span>/</span> <strong style="color: #fff;">Tim KKN</strong>
    </div>
    <h1>Tim KKN UIN RIL</h1>
    <p>Mahasiswa KKN UIN Raden Intan Lampung Kelompok 31 yang bertugas membangun portal digital dan program kerja di <?= defined('NAMA_KELURAHAN') ? NAMA_KELURAHAN : 'Kelurahan Kangkung' ?>.</p>
  </div>
</section>

<!-- Section Anggota Tim -->
<section class="page-section" style="padding: 48px 0 80px;">
  <div class="container">

    <?php if ($dpl): ?>
    <!-- Section Dosen Pembimbing Lapangan -->
    <div style="text-align: center; margin-bottom: 32px;">
      <span class="eyebrow" style="display: block; margin-bottom: 6px; text-align: center;">PEMBIMBING</span>
      <h2 style="color: var(--teal-900); text-align: center;">Dosen Pembimbing Lapangan</h2>
    </div>
    
    <div style="display: flex; justify-content: center; margin-bottom: 56px;">
      <div class="people-card" style="width: 250px; text-align: center;">
        <img class="photo" src="<?= get_public_photo_url($dpl['foto'] ?? '') ?>" onerror="this.src='<?= $assetPrefix ?>img/placeholder-default.jpg'" alt="<?= htmlspecialchars($dpl['nama']) ?>">
        <div class="info" style="text-align: center;">
          <div class="name" style="text-align: center;"><?= htmlspecialchars($dpl['nama']) ?></div>
          <div class="role" style="text-align: center;">Dosen Pembimbing Lapangan</div>
        </div>
      </div>
    </div>
    <?php endif; ?>

    <?php if (!empty($mahasiswa)): ?>
    <!-- Section Mahasiswa KKN -->
    <div style="text-align: center; margin-bottom: 32px;">
      <span class="eyebrow" style="display: block; margin-bottom: 6px; text-align: center;">ANGGOTA KELOMPOK 31</span>
      <h2 style="color: var(--teal-900); text-align: center;">Mahasiswa KKN</h2>
    </div>

    <div class="people-grid">
      <?php foreach ($mahasiswa as $t):
        $parts = preg_split('/\\s+—\\s+/', $t['jabatan'], 2);
        $jabatan = $parts[0];
        $jurusan = $parts[1] ?? '';
      ?>
        <div class="people-card" style="text-align: center;">
          <img class="photo" src="<?= get_public_photo_url($t['foto'] ?? '') ?>" onerror="this.src='<?= $assetPrefix ?>img/placeholder-default.jpg'" alt="<?= htmlspecialchars($t['nama']) ?>">
          <div class="info" style="text-align: center;">
            <div class="name" style="text-align: center;"><?= htmlspecialchars($t['nama']) ?></div>
            <div class="role" style="text-align: center;"><?= htmlspecialchars($jabatan) ?></div>
            <?php if ($jurusan): ?>
              <div class="dept" style="text-align: center;"><?= htmlspecialchars($jurusan) ?></div>
            <?php endif; ?>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <?php if (empty($tim)): ?>
      <p style="color: var(--ink-soft); text-align: center;">Belum ada data anggota tim.</p>
    <?php endif; ?>
  </div>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>