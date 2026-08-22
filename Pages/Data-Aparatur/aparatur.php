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

<style>
  /* Style Hero khusus agar Rata Tengah Presisi */
  .page-hero {
    padding-top: 120px !important;
    padding-bottom: 40px !important;
    text-align: center !important;
    background-color: #064e3b; /* Hijau Kangkung */
    color: #ffffff;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
  }

  .page-hero .breadcrumb {
    display: flex;
    justify-content: center !important;
    align-items: center;
    gap: 8px;
    margin-bottom: 16px;
    font-size: 0.9rem;
    color: #a7f3d0;
    text-align: center !important;
  }

  .page-hero .breadcrumb a {
    color: #a7f3d0;
    text-decoration: none;
  }

  .page-hero .breadcrumb a:hover {
    text-decoration: underline;
  }

  .page-hero h1 {
    font-size: 2.5rem;
    font-weight: 800;
    margin: 0 auto 12px auto;
    color: #ffffff;
    text-align: center !important;
  }

  .page-hero p {
    max-width: 650px;
    margin: 0 auto;
    font-size: 1rem;
    line-height: 1.6;
    color: #e2e8f0;
    text-align: center !important;
  }

  /* Layout & Card Styling */
  .people-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
    gap: 24px;
    padding: 10px 0;
  }

  .people-card {
    background: #ffffff;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
    border: 1px solid #e2e8f0;
    text-align: center;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
  }

  .people-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08);
  }

  .people-card .photo {
    width: 100%;
    height: 250px;
    object-fit: cover;
    object-position: top;
    background-color: #f8fafc;
  }

  .people-card .info {
    padding: 16px;
  }

  .people-card .name {
    font-weight: 700;
    font-size: 1.05rem;
    color: #0f172a;
    margin-bottom: 4px;
  }

  .people-card .role {
    font-size: 0.875rem;
    color: #059669;
    font-weight: 600;
  }
</style>

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