<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../config_db.php';

$assetPrefix = '../../assets/';
$navPrefix   = '../';
$activeNav   = 'profile';
$pageTitle   = 'Data Aparatur';

$pdo = getDB();
$stmt = $pdo->query("SELECT * FROM aparatur ORDER BY id ASC");
$aparatur = $stmt->fetchAll();

include __DIR__ . '/../includes/header.php';
?>

<!-- Hero Section -->
<section class="page-hero">
  <div class="container">
    <div class="breadcrumb">
      <a href="<?= $navPrefix ?>index.php">Beranda</a> <span>/</span> <span>Profile</span> <span>/</span> <strong style="color: #ffffff;">Data Aparatur</strong>
    </div>
    <h1>Data Aparatur Kelurahan</h1>
    <p>
      Struktur perangkat <?= htmlspecialchars(defined('NAMA_KELURAHAN') ? NAMA_KELURAHAN : 'Kelurahan Kangkung') ?> yang melayani masyarakat.
    </p>
  </div>
</section>

<!-- Section Grid Aparatur -->
<section class="page-section" style="padding: 40px 0;">
  <div class="container">
    <?php if (empty($aparatur)): ?>
      <div style="text-align: center; padding: 40px; background: #fff; border-radius: 12px; border: 1px solid #e2e8f0; color: #64748b;">
        <p style="margin: 0;">Belum ada data aparatur yang ditambahkan.</p>
      </div>
    <?php else: ?>
      <div class="people-grid">
        <?php foreach ($aparatur as $a): ?>
          <div class="people-card">
            <img class="photo" 
                 src="<?= $a['foto'] ? '../../' . htmlspecialchars($a['foto']) : $assetPrefix . 'img/placeholder-default.jpg' ?>" 
                 onerror="this.src='<?= $assetPrefix ?>img/placeholder-default.jpg'" 
                 alt="<?= htmlspecialchars($a['nama']) ?>">
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