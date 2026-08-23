<?php
/**
 * Pages/includes/header.php
 * Dipakai di semua halaman publik. Sebelum include ini, halaman pemanggil
 * WAJIB set variabel:
 *   $assetPrefix -> path relatif ke folder assets/  (mis. '../../assets/' atau '../assets/')
 *   $navPrefix   -> path relatif ke folder Pages/    (mis. '../' atau '' untuk index.php)
 *   $activeNav   -> salah satu: beranda, profile, peta, chatbot, statistik, berita, galeri
 *   $pageTitle   -> judul tab browser
 * dan sudah require config.php sebelumnya.
 */
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($pageTitle) ?> | <?= NAMA_KELURAHAN ?></title>
<link rel="icon" type="image/png" href="<?= $assetPrefix ?>img/favicon.png">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Sora:wght@600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="<?= $assetPrefix ?>css/style.css?v=<?= time() ?>">
<link rel="stylesheet" href="<?= $assetPrefix ?>css/chatbot.css?v=<?= time() ?>">
</head>
<body>

<header class="site-header <?= (isset($forceSolidHeader) && $forceSolidHeader) ? 'force-solid' : '' ?>">
  <div class="container header-inner">
    <a href="<?= $navPrefix ?>index.php" class="brand">
      <img src="<?= $assetPrefix ?>img/logo-kkn.png" alt="Logo KKN" class="brand-logo" onerror="this.style.display='none'">
      <div class="brand-text">
        <strong><?= NAMA_KELURAHAN ?></strong>
        <span>Oleh Mahasiswa KKN UIN RIL</span>
      </div>
    </a>

    <button class="nav-toggle" id="navToggle" aria-label="Buka menu" aria-expanded="false" aria-controls="mainNav">
      <span></span><span></span><span></span>
    </button>

    <nav class="main-nav" id="mainNav">
      <a href="<?= $navPrefix ?>index.php" class="<?= $activeNav === 'beranda' ? 'active' : '' ?>">Beranda</a>
      <div class="nav-dropdown">
        <button class="nav-dropdown-btn">Profile <i class="chev">▾</i></button>
        <div class="nav-dropdown-menu">
          <a href="<?= $navPrefix ?>VisiMisi/visi-misi.php">Visi &amp; Misi</a>
          <a href="<?= $navPrefix ?>Sejarah/sejarah.php">Sejarah Kelurahan</a>
          <a href="<?= $navPrefix ?>Data-Aparatur/aparatur.php">Data Aparatur</a>
          <a href="<?= $navPrefix ?>Tim-KKN/tim-kkn.php">Tim KKN</a>
        </div>
      </div>
      <a href="<?= $navPrefix ?>Peta/peta.php" class="<?= $activeNav === 'peta' ? 'active' : '' ?>">Peta Kelurahan</a>
      <a href="<?= $navPrefix ?>Chatbot/chatbot.php" class="<?= $activeNav === 'chatbot' ? 'active' : '' ?>">Chatbot AI</a>
      <a href="<?= $navPrefix ?>Statistik/statistik.php" class="<?= $activeNav === 'statistik' ? 'active' : '' ?>">Statistik Kelurahan</a>
      <a href="<?= $navPrefix ?>Berita/berita.php" class="<?= $activeNav === 'berita' ? 'active' : '' ?>">Berita</a>
      <a href="<?= $navPrefix ?>Galeri/galeri.php" class="<?= $activeNav === 'galeri' ? 'active' : '' ?>">Galeri</a>
    </nav>
  </div>
</header>
