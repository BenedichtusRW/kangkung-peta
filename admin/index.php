<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../config_db.php';

$pdo = getDB();

// Gather stats for the dashboard
$stats = [
    'pois' => $pdo->query("SELECT COUNT(*) FROM pois")->fetchColumn(),
    'berita' => $pdo->query("SELECT COUNT(*) FROM berita")->fetchColumn(),
    'aparatur' => $pdo->query("SELECT COUNT(*) FROM aparatur")->fetchColumn(),
    'galeri' => $pdo->query("SELECT COUNT(*) FROM galeri")->fetchColumn(),
];

?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Pusat Kontrol | Admin <?= NAMA_KELURAHAN ?></title>
<link rel="icon" type="image/png" href="../assets/img/lambang-kota.png">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Sora:wght@600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
<link rel="stylesheet" href="../assets/css/admin.css?v=<?= time() ?>">
<link rel="icon" type="image/png" href="../assets/img/lambang-kota.png">
</head>
<body>
<div class="admin-shell">

  <?php include __DIR__ . '/includes/sidebar.php'; ?>

  <main class="admin-main">
    <div class="admin-topbar" style="margin-bottom: 24px;">
      <div>
        <h1>Pusat Kontrol</h1>
        <p>Kelola seluruh konten publik <?= NAMA_KELURAHAN ?> dari satu tempat.</p>
      </div>
    </div>

    <!-- Hero Banner -->
    <div class="dashboard-hero">
      <h2>Selamat Melayani, Pengurus!</h2>
      <p>"Melayani dengan sepenuh hati untuk masyarakat yang lebih baik dan sejahtera."</p>
      <div class="dashboard-hero-actions">
        <a href="berita.php" class="btn" style="background: white; color: var(--teal-900); width: auto;"><i class="fa-solid fa-plus"></i> TULIS BERITA BARU</a>
        <a href="peta.php" class="btn" style="background: transparent; border: 1.5px solid rgba(255,255,255,0.4); color: white; width: auto;"><i class="fa-solid fa-map-location-dot"></i> TAMBAH TEMPAT</a>
      </div>
    </div>

    <!-- Quick Stats -->
    <div class="dashboard-stats">
      <div class="d-stat-card">
        <div class="d-stat-icon"><i class="fa-solid fa-map-location-dot"></i></div>
        <div class="d-stat-info">
          <span class="d-stat-label">TOTAL TEMPAT</span>
          <span class="d-stat-value"><?= $stats['pois'] ?></span>
        </div>
      </div>
      <div class="d-stat-card">
        <div class="d-stat-icon"><i class="fa-solid fa-newspaper"></i></div>
        <div class="d-stat-info">
          <span class="d-stat-label">BERITA AKTIF</span>
          <span class="d-stat-value"><?= $stats['berita'] ?></span>
        </div>
      </div>
      <div class="d-stat-card">
        <div class="d-stat-icon"><i class="fa-solid fa-images"></i></div>
        <div class="d-stat-info">
          <span class="d-stat-label">KOLEKSI GALERI</span>
          <span class="d-stat-value"><?= $stats['galeri'] ?></span>
        </div>
      </div>
      <div class="d-stat-card">
        <div class="d-stat-icon"><i class="fa-solid fa-users"></i></div>
        <div class="d-stat-info">
          <span class="d-stat-label">JUMLAH APARATUR</span>
          <span class="d-stat-value"><?= $stats['aparatur'] ?></span>
        </div>
      </div>
    </div>

  </main>
</div>
</body>
</html>
