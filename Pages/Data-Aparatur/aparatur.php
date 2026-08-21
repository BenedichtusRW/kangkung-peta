<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../config_db.php';

$assetPrefix = '../../assets/';
$navPrefix = '../';
$activeNav = 'profile';
$pageTitle = 'Data Aparatur';

$pdo = getDB();
$stmt = $pdo->query("SELECT * FROM aparatur ORDER BY id ASC");
$aparatur = $stmt->fetchAll();

include __DIR__ . '/../includes/header.php';
?>

<section class="page-hero">
  <div class="container">
    <div class="breadcrumb"><a href="<?= $navPrefix ?>index.php">Beranda</a> / Profile / Data Aparatur</div>
    <h1>Data Aparatur Kelurahan</h1>
    <p>Struktur perangkat <?= NAMA_KELURAHAN ?> yang melayani masyarakat.</p>
  </div>
</section>

<section class="page-section">
  <div class="container">
    <?php if (empty($aparatur)): ?>
      <p style="color:var(--ink-soft)">Belum ada data aparatur.</p>
    <?php else: ?>
      <div class="people-grid">
        <?php foreach ($aparatur as $a): ?>
          <div class="people-card">
            <img class="photo" src="../../<?= htmlspecialchars($a['foto']) ?>" onerror="this.src='<?= $assetPrefix ?>img/placeholder-default.jpg'" alt="<?= htmlspecialchars($a['nama']) ?>">
            <div class="info">
              <div class="name"><?= htmlspecialchars($a['nama']) ?></div>
              <div class="role"><?= htmlspecialchars($a['jabatan']) ?></div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>
