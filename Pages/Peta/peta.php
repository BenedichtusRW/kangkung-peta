<?php require_once __DIR__ . '/../../config.php'; ?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Peta Kelurahan | <?= NAMA_KELURAHAN ?></title>
<meta name="description" content="Peta interaktif titik lokasi penting di <?= NAMA_KELURAHAN ?>: tugu, pemerintahan, kuliner, jasa, tempat ibadah, sekolah, dan kesehatan.">

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Sora:wght@600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../../assets/css/style.css?v=<?= time() ?>">
</head>
<body>

<!-- ===================== HEADER ===================== -->
<header class="site-header">
  <div class="container header-inner">
    <a href="../index.php" class="brand">
      <img src="../../assets/img/logo-kkn.png" alt="Logo KKN" class="brand-logo" onerror="this.style.display='none'">
      <div class="brand-text">
        <strong><?= NAMA_KELURAHAN ?></strong>
        <span>Oleh Mahasiswa KKN UIN RIL</span>
      </div>
    </a>

    <button class="nav-toggle" id="navToggle" aria-label="Buka menu">
      <span></span><span></span><span></span>
    </button>

    <nav class="main-nav" id="mainNav">
      <a href="../index.php">Beranda</a>
      <div class="nav-dropdown">
        <button class="nav-dropdown-btn">Profile <i class="chev">▾</i></button>
        <div class="nav-dropdown-menu">
          <a href="../VisiMisi/visi-misi.php">Visi &amp; Misi</a>
          <a href="../Sejarah/sejarah.php">Sejarah Kelurahan</a>
          <a href="../Data-Aparatur/aparatur.php">Data Aparatur</a>
          <a href="../Tim-KKN/tim-kkn.php">Tim KKN</a>
        </div>
      </div>
      <a href="peta.php" class="active">Peta Kelurahan</a>
      <a href="../Chatbot/chatbot.php">Chatbot AI</a>
      <a href="../Statistik/statistik.php">Statistik Kelurahan</a>
      <a href="../Berita/berita.php">Berita</a>
      <a href="../Galeri/galeri.php">Galeri</a>
    </nav>
  </div>
</header>

<!-- ===================== HERO ===================== -->
<section class="peta-hero">
  <div class="container">
    <span class="eyebrow">Peta Interaktif</span>
    <h1>Peta Kelurahan <?= explode(' ', NAMA_KELURAHAN)[1] ?? NAMA_KELURAHAN ?></h1>
    <p>Temukan tugu, kantor pemerintahan, kuliner, jasa, tempat ibadah, sekolah, dan fasilitas kesehatan di area <?= NAMA_KELURAHAN ?>.</p>
  </div>
</section>

<!-- ===================== KONTEN PETA ===================== -->
<section class="peta-section">
  <div class="container">

    <!-- Filter kategori -->
    <div class="filter-bar" id="filterBar">
      <?php foreach ($KATEGORI_PETA as $key => $kat): ?>
        <button
          class="filter-chip<?= $key === 'semua' ? ' active' : '' ?>"
          data-kategori="<?= $key ?>"
          style="--chip-color: <?= $kat['warna'] ?>">
          <i class="<?= $kat['icon'] ?>"></i> <?= htmlspecialchars($kat['label']) ?>
        </button>
      <?php endforeach; ?>
    </div>

    <!-- Toolbar: lokasi saya, sedang buka, urutkan, cari -->
    <div class="toolbar">
      <div class="toolbar-left">
        <button id="btnLokasiSaya" class="btn btn-outline"><i class="fa-solid fa-location-crosshairs"></i> Lokasi Saya</button>
        <button id="btnSedangBuka" class="btn btn-outline"><i class="fa-solid fa-clock"></i> Sedang Buka</button>
      </div>
      <div class="toolbar-right">
        <input type="search" id="searchInput" placeholder="Cari nama tempat / alamat..." class="search-input">
        <select id="sortSelect" class="sort-select">
          <option value="default">Urutkan: Default</option>
          <option value="terdekat">Urutkan: Terdekat</option>
          <option value="az">Urutkan: A - Z</option>
        </select>
      </div>
    </div>

    <!-- Map + sidebar -->
    <div class="map-layout">
      <div class="map-wrap">
        <div id="map"></div>
      </div>

      <aside class="place-list">
        <div class="place-list-head">
          <h3>Daftar Tempat</h3>
          <span id="placeCount" class="place-count">Area <?= NAMA_KELURAHAN ?></span>
        </div>
        <div id="placeListItems" class="place-list-items">
          <p class="loading-text">Memuat data...</p>
        </div>
      </aside>
    </div>
  </div>
</section>

<!-- ===================== MODAL DETAIL ===================== -->
<div class="detail-overlay" id="detailOverlay">
  <div class="detail-card">
    <button class="detail-close" id="detailClose">&times;</button>
    <div class="detail-img-wrap">
      <img id="detailImg" src="" alt="Detail lokasi">
      <span id="detailKategori" class="detail-kategori-badge"></span>
    </div>
    <div class="detail-body">
      <h2 id="detailNama">Nama Tempat</h2>
      <p id="detailDeskripsi" class="detail-desc">Deskripsi...</p>
      <div class="detail-row" id="detailJarakRow" style="display:none;">
        <i class="fa-solid fa-location-arrow detail-icon" style="color:var(--teal-600);"></i>
        <div class="detail-text">
          <div class="detail-label">Jarak Estimasi</div>
          <div id="detailJarak" class="detail-value" style="color:var(--teal-700); font-weight:700;">-</div>
        </div>
      </div>
      <div class="detail-row">
        <i class="fa-solid fa-location-dot detail-icon"></i>
        <div class="detail-text">
          <div class="detail-label">Alamat</div>
          <div id="detailAlamat" class="detail-value">-</div>
        </div>
      </div>
      <div class="detail-row">
        <i class="fa-solid fa-phone detail-icon"></i>
        <div class="detail-text">
          <div class="detail-label">Kontak</div>
          <div id="detailKontak" class="detail-value">-</div>
        </div>
      </div>
      <div class="detail-row">
        <i class="fa-solid fa-clock detail-icon"></i>
        <div class="detail-text">
          <div class="detail-label">Jam Buka</div>
          <div id="detailJamBuka" class="detail-value">-</div>
        </div>
      </div>

      <div class="detail-actions">
        <button id="btnWhatsapp" class="btn btn-outline" style="flex:1"><i class="fa-brands fa-whatsapp" style="color:#25D366; font-size:16px;"></i> Chat</button>
        <button id="btnRute" class="btn btn-primary" style="flex:2"><i class="fa-solid fa-route"></i> Rute Lokasi</button>
      </div>
    </div>
  </div>
</div>

<!-- ===================== FOOTER ===================== -->
<footer class="site-footer">
  <div class="container footer-grid">
    <div>
      <h4><?= NAMA_KELURAHAN ?></h4>
      <p>Melayani dengan sepenuh hati untuk masyarakat yang lebih baik dan sejahtera.</p>
      <div class="social-links">
        <a href="#" title="Facebook">FB</a>
        <a href="#" title="Instagram">IG</a>
        <a href="#" title="YouTube">YT</a>
      </div>
      
      <div class="kkn-attribution" style="margin-top: 24px;">
        <span style="font-size: 11px; color: rgba(255,255,255,0.6); text-transform: uppercase; letter-spacing: 0.05em; display: block; margin-bottom: 8px;">Dipersembahkan Oleh</span>
        <div style="display: flex; gap: 12px; align-items: center;">
          <img src="../../assets/img/logo-uin.png" alt="Logo UIN" style="height: 48px; width: 48px; object-fit: contain; background: #fff; border-radius: 50%; padding: 4px; filter: drop-shadow(0 2px 4px rgba(0,0,0,0.2));" onerror="this.style.display='none'">
          <img src="../../assets/img/logo-kkn.png" alt="Logo KKN 31" style="height: 48px; width: 48px; object-fit: contain; background: #fff; border-radius: 50%; padding: 2px; filter: drop-shadow(0 2px 4px rgba(0,0,0,0.2));" onerror="this.style.display='none'">
        </div>
      </div>
    </div>

    <div>
      <h5>Kontak Kami</h5>
      <ul class="footer-list">
        <li><?= ALAMAT_KANTOR ?></li>
        <li><?= KONTAK_TELEPON ?></li>
        <li><?= KONTAK_EMAIL ?></li>
        <li><?= JAM_LAYANAN ?></li>
      </ul>
    </div>

    <div>
      <h5>Link Cepat</h5>
      <ul class="footer-list">
        <li><a href="../index.php">Beranda</a></li>
        <li><a href="../VisiMisi/visi-misi.php">Visi &amp; Misi</a></li>
        <li><a href="../Statistik/statistik.php">Statistik Kelurahan</a></li>
        <li><a href="../Galeri/galeri.php">Galeri</a></li>
        <li><a href="../Chatbot/chatbot.php">Chatbot AI</a></li>
      </ul>
    </div>
  </div>
  <div class="footer-bottom">
    &copy; <?= date('Y') ?> <?= NAMA_KELURAHAN ?>. All rights reserved.
  </div>
</footer>

<script>
  (() => {
    const toggle = document.getElementById('navToggle');
    const nav = document.getElementById('mainNav');
    const siteHeader = document.querySelector('.site-header');

    if (siteHeader) {
      const handleScroll = () => {
        if (window.scrollY > 20) {
          siteHeader.classList.add('scrolled');
        } else {
          siteHeader.classList.remove('scrolled');
        }
      };
      window.addEventListener('scroll', handleScroll, {passive: true});
      handleScroll();
    }

    if (toggle && nav) {
      toggle.addEventListener('click', () => nav.classList.toggle('open'));
    }
    document.querySelectorAll('.nav-dropdown-btn').forEach((button) => {
      button.addEventListener('click', () => {
        if (window.matchMedia('(max-width: 1100px)').matches) {
          button.closest('.nav-dropdown').classList.toggle('open');
        }
      });
    });
  })();
</script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
  window.PETA_CONFIG = {
    lat: <?= PETA_LAT ?>,
    lng: <?= PETA_LNG ?>,
    zoom: <?= PETA_ZOOM ?>,
    apiUrl: '../../api/get_pois.php',
    kategoriWarna: <?= json_encode(array_combine(array_keys($KATEGORI_PETA), array_column($KATEGORI_PETA, 'warna'))) ?>,
    kategoriLabel: <?= json_encode(array_combine(array_keys($KATEGORI_PETA), array_column($KATEGORI_PETA, 'label'))) ?>
  };
</script>
<script src="../../assets/js/peta.js"></script>
</body>
</html>
