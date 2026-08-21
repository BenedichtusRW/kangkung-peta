<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../config_db.php';

$assetPrefix = '../../assets/';
$navPrefix = '../';
$activeNav = 'profile';
$pageTitle = 'Tim KKN UIN RIL';

$pdo = getDB();
$stmt = $pdo->query("SELECT * FROM tim_kkn ORDER BY id ASC");
$tim = $stmt->fetchAll();

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

<section class="page-hero">
  <div class="container">
    <div class="breadcrumb"><a href="<?= $navPrefix ?>index.php">Beranda</a> / Profile / Tim KKN</div>
    <h1>Tim KKN UIN RIL</h1>
    <p>Mahasiswa KKN UIN Raden Intan Lampung Kelompok 31 yang bertugas membangun portal digital dan program kerja di <?= NAMA_KELURAHAN ?>.</p>
  </div>
</section>

<section class="page-section">
  <div class="container">

    <?php if ($dpl): ?>
    <!-- Dosen Pembimbing Lapangan -->
    <div class="section-head" style="text-align:center; margin-bottom:32px;">
      <span class="eyebrow">Pembimbing</span>
      <h2>Dosen Pembimbing Lapangan</h2>
    </div>
    <div style="display:flex; justify-content:center; margin-bottom:48px;">
      <div class="people-card" style="max-width:220px; text-align:center;">
        <img class="photo" src="<?= $dpl['foto'] ? '../../' . htmlspecialchars($dpl['foto']) : $assetPrefix . 'img/placeholder-default.jpg' ?>" onerror="this.src='<?= $assetPrefix ?>img/placeholder-default.jpg'" alt="<?= htmlspecialchars($dpl['nama']) ?>">
        <div class="info">
          <div class="name"><?= htmlspecialchars($dpl['nama']) ?></div>
          <div class="role">Dosen Pembimbing Lapangan</div>
        </div>
      </div>
    </div>
    <?php endif; ?>

    <?php if (!empty($mahasiswa)): ?>
    <div class="section-head" style="text-align:center; margin-bottom:32px;">
      <span class="eyebrow">Anggota Kelompok 31</span>
      <h2>Mahasiswa KKN</h2>
    </div>
    <div class="people-grid">
      <?php foreach ($mahasiswa as $t):
        // Pisahkan jabatan dan jurusan
        $parts = explode(' — ', $t['jabatan'], 2);
        $jabatan = $parts[0];
        $jurusan = $parts[1] ?? '';
      ?>
        <div class="people-card">
          <img class="photo" src="<?= $t['foto'] ? '../../' . htmlspecialchars($t['foto']) : $assetPrefix . 'img/placeholder-default.jpg' ?>" onerror="this.src='<?= $assetPrefix ?>img/placeholder-default.jpg'" alt="<?= htmlspecialchars($t['nama']) ?>">
          <div class="info">
            <div class="name"><?= htmlspecialchars($t['nama']) ?></div>
            <div class="role"><?= htmlspecialchars($jabatan) ?></div>
            <?php if ($jurusan): ?>
              <div style="font-size:11px; color:var(--ink-soft, #888); margin-top:4px; padding: 0 8px;"><?= htmlspecialchars($jurusan) ?></div>
            <?php endif; ?>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <?php if (empty($tim)): ?>
      <p style="color:var(--ink-soft); text-align:center;">Belum ada data anggota tim.</p>
    <?php endif; ?>
  </div>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>
