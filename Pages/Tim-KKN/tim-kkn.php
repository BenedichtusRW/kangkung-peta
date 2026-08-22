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

<style>
  /* Perbaikan Spasi Hero agar tidak menabrak Navbar Fixed */
  .page-hero {
    padding-top: 120px !important;
    padding-bottom: 40px !important;
    text-align: center;
    background-color: #064e3b; /* Hijau Kangkung */
    color: #ffffff;
  }
  
  .page-hero .breadcrumb {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 8px;
    margin-bottom: 16px;
    font-size: 0.9rem;
    color: #a7f3d0;
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
    margin: 0 0 12px 0;
    color: #ffffff;
  }

  .page-hero p {
    max-width: 650px;
    margin: 0 auto;
    font-size: 1rem;
    line-height: 1.6;
    color: #e2e8f0;
  }

  .center-section {
    text-align: center !important;
    margin-bottom: 32px;
  }
  .center-section .eyebrow {
    display: inline-block;
    letter-spacing: 1px;
    font-weight: 600;
    margin-bottom: 8px;
  }
  .center-section h2 {
    margin: 0 auto;
    font-size: 2rem;
    font-weight: 700;
  }
  
  .people-card {
    text-align: center;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 12px rgba(0,0,0,0.06);
    background: #fff;
    transition: transform 0.2s ease;
  }
  .people-card:hover {
    transform: translateY(-4px);
  }
  .people-card .photo {
    width: 100%;
    height: 240px;
    object-fit: cover;
    object-position: top;
  }
  .people-card .info {
    padding: 16px;
    text-align: center;
  }
  .people-card .name {
    font-weight: 700;
    font-size: 1.05rem;
    color: #111827;
    margin-bottom: 4px;
  }
  .people-card .role {
    font-size: 0.875rem;
    color: #059669;
    font-weight: 600;
  }
  .people-card .dept {
    font-size: 0.75rem;
    color: #6b7280;
    margin-top: 4px;
  }
</style>

<!-- Section Hero (Rata Tengah Presisi) -->
<section class="page-hero" style="text-align: center !important; display: flex; flex-direction: column; align-items: center; justify-content: center;">
  <div class="container" style="text-align: center !important; width: 100%;">
    
    <div class="breadcrumb" style="justify-content: center !important; text-align: center !important; margin: 0 auto 12px auto;">
      <a href="<?= $navPrefix ?>index.php">Beranda</a> <span>/</span> <span>Profile</span> <span>/</span> <strong style="color: #fff;">Tim KKN</strong>
    </div>

    <h1 style="text-align: center !important; width: 100%; margin: 0 auto 12px auto;">Tim KKN UIN RIL</h1>

    <p style="text-align: center !important; max-width: 650px; margin: 0 auto;">
      Mahasiswa KKN UIN Raden Intan Lampung Kelompok 31 yang bertugas membangun portal digital dan program kerja di <?= defined('NAMA_KELURAHAN') ? NAMA_KELURAHAN : 'Kelurahan Kangkung' ?>.
    </p>

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