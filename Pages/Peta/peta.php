<?php 
require_once __DIR__ . '/../../config.php';

$assetPrefix = '../../assets/';
$navPrefix   = '../';
$activeNav   = 'peta';
$pageTitle   = 'Peta Kelurahan';

include __DIR__ . '/../includes/header.php';
?>

<!-- Include CSS Leaflet khusus halaman peta -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

<style>
/* Style Hero khusus agar Rata Tengah Presisi */
.peta-hero {
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

.peta-hero .breadcrumb {
  display: flex;
  justify-content: center !important;
  align-items: center;
  gap: 8px;
  margin-bottom: 12px;
  font-size: 0.9rem;
  color: #a7f3d0;
  text-align: center !important;
}

.peta-hero .breadcrumb a {
  color: #a7f3d0;
  text-decoration: none;
}

.peta-hero .breadcrumb a:hover {
  text-decoration: underline;
}

.peta-hero .eyebrow {
  display: inline-block;
  color: #f59e0b;
  font-weight: 700;
  font-size: 0.85rem;
  letter-spacing: 1.5px;
  text-transform: uppercase;
  margin-bottom: 8px;
}

.peta-hero h1 {
  font-size: 2.5rem;
  font-weight: 800;
  margin: 0 auto 12px auto;
  color: #ffffff;
  text-align: center !important;
}

.peta-hero p {
  max-width: 680px;
  margin: 0 auto;
  font-size: 1rem;
  line-height: 1.6;
  color: #e2e8f0;
  text-align: center !important;
}

/* Modal Detail Adjustments */
.detail-overlay {
  z-index: 1000;
}
</style>

<!-- ===================== HERO ===================== -->
<section class="peta-hero">
  <div class="container">
    <div class="breadcrumb">
      <a href="<?= $navPrefix ?>index.php">Beranda</a> <span>/</span> <strong style="color: #ffffff;">Peta Kelurahan</strong>
    </div>
    <span class="eyebrow">PETA INTERAKTIF</span>
    <h1>Peta Kelurahan <?= htmlspecialchars(explode(' ', NAMA_KELURAHAN)[1] ?? NAMA_KELURAHAN) ?></h1>
    <p>
      Temukan tugu, kantor pemerintahan, kuliner, jasa, tempat ibadah, sekolah, dan fasilitas kesehatan di area <?= htmlspecialchars(NAMA_KELURAHAN) ?>.
    </p>
  </div>
</section>

<!-- ===================== KONTEN PETA ===================== -->
<section class="peta-section" style="padding: 32px 0 60px 0;">
  <div class="container">

    <!-- Filter Kategori -->
    <div class="filter-bar" id="filterBar">
      <?php foreach ($KATEGORI_PETA as $key => $kat): ?>
        <button
          class="filter-chip<?= $key === 'semua' ? ' active' : '' ?>"
          data-kategori="<?= htmlspecialchars($key) ?>"
          style="--chip-color: <?= htmlspecialchars($kat['warna']) ?>">
          <i class="<?= htmlspecialchars($kat['icon']) ?>"></i> <?= htmlspecialchars($kat['label']) ?>
        </button>
      <?php endforeach; ?>
    </div>

    <!-- Toolbar Interaktif -->
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

    <!-- Map & Sidebar -->
    <div class="map-layout">
      <div class="map-wrap">
        <div id="map"></div>
      </div>

      <aside class="place-list">
        <div class="place-list-head">
          <h3>Daftar Tempat</h3>
          <span id="placeCount" class="place-count">Area <?= htmlspecialchars(NAMA_KELURAHAN) ?></span>
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

<!-- Scripts Khusus Leaflet & Peta -->
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

<?php include __DIR__ . '/../includes/footer.php'; ?>