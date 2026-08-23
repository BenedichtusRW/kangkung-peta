<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../config_db.php';

$assetPrefix = '../../assets/';
$navPrefix = '../';
$activeNav = 'profile';
$pageTitle = 'Tim KKN UIN RIL';

$dataFile = __DIR__ . '/../../data/tim-kkn.json';
$tim = [];

if (is_file($dataFile)) {
    $data = json_decode(file_get_contents($dataFile), true);
    if (is_array($data)) {
        $tim = $data;
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

</style>

<!-- Section Hero -->
<section class="page-hero">
  <div class="container">
    <div class="breadcrumb">
      <a href="<?= $navPrefix ?>index.php">Beranda</a> <span>/</span> <span>Profile</span> <span>/</span> <strong style="color: #fff;">Tim KKN</strong>
    </div>
    <h1>Tim KKN UIN RIL</h1>
    <p>Mahasiswa KKN UIN Raden Intan Lampung Kelompok 31 yang bertugas membangun portal digital dan program kerja di <?= defined('NAMA_KELURAHAN') ? NAMA_KELURAHAN : 'Kelurahan Kangkung' ?>.</p>
  </div>
</section>
<!-- Section Anggota Tim -->
<section class="page-section" style="padding: 40px 0;">
  <div class="container">

    <?php if ($dpl): ?>
    <!-- Section Dosen Pembimbing Lapangan -->
    <div class="center-section">
      <span class="eyebrow" style="color: #d97706;">PEMBIMBING</span>
      <h2 style="color: #064e3b;">Dosen Pembimbing Lapangan</h2>
    </div>
    
    <div style="display: flex; justify-content: center; margin-bottom: 56px;">
      <div class="people-card" style="width: 240px;">
        <img class="photo" src="<?= $dpl['foto'] ? '../../' . htmlspecialchars($dpl['foto']) : $assetPrefix . 'img/placeholder-default.jpg' ?>" onerror="this.src='<?= $assetPrefix ?>img/placeholder-default.jpg'" alt="<?= htmlspecialchars($dpl['nama']) ?>">
        <div class="info">
          <div class="name"><?= htmlspecialchars($dpl['nama']) ?></div>
          <div class="role">Dosen Pembimbing Lapangan</div>
        </div>
      </div>
    </div>
    <?php endif; ?>

    <?php if (!empty($mahasiswa)): ?>
    <!-- Section Mahasiswa KKN -->
    <div class="center-section">
      <span class="eyebrow" style="color: #d97706;">ANGGOTA KELOMPOK 31</span>
      <h2 style="color: #064e3b;">Mahasiswa KKN</h2>
    </div>

    <div class="people-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 24px; padding: 0 10px;">
      <?php foreach ($mahasiswa as $t):
        $parts = preg_split('/\\s+—\\s+/', $t['jabatan'], 2);
        $jabatan = $parts[0];
        $jurusan = $parts[1] ?? '';
      ?>
        <div class="people-card">
          <img class="photo" src="<?= $t['foto'] ? '../../' . htmlspecialchars($t['foto']) : $assetPrefix . 'img/placeholder-default.jpg' ?>" onerror="this.src='<?= $assetPrefix ?>img/placeholder-default.jpg'" alt="<?= htmlspecialchars($t['nama']) ?>">
          <div class="info">
            <div class="name"><?= htmlspecialchars($t['nama']) ?></div>
            <div class="role"><?= htmlspecialchars($jabatan) ?></div>
            <?php if ($jurusan): ?>
              <div class="dept"><?= htmlspecialchars($jurusan) ?></div>
            <?php endif; ?>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <?php if (empty($tim)): ?>
      <p style="color: #6b7280; text-align: center;">Belum ada data anggota tim.</p>
    <?php endif; ?>
  </div>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>