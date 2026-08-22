<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../config_db.php';

$assetPrefix = '../../assets/';
$navPrefix   = '../';
$activeNav   = 'galeri';
$pageTitle   = 'Galeri Kegiatan';

$pdo = getDB();
$stmt = $pdo->query("SELECT * FROM galeri ORDER BY created_at DESC, id DESC");
$galeri = $stmt->fetchAll();
$kategoriList = array_values(array_unique(array_filter(array_column($galeri, 'kategori'))));

include __DIR__ . '/../includes/header.php';
?>

<style>
  /* Style Hero Khusus Presisi Center */
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
    margin-bottom: 12px;
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

  /* Filter Chips Bar */
  .gallery-filter {
    display: flex;
    justify-content: center;
    flex-wrap: wrap;
    gap: 10px;
    margin-bottom: 36px;
  }

  .filter-chip {
    background: #f1f5f9;
    color: #334155;
    border: 1px solid #cbd5e1;
    padding: 8px 18px;
    border-radius: 20px;
    font-weight: 600;
    font-size: 0.9rem;
    cursor: pointer;
    transition: all 0.2s ease;
  }

  .filter-chip:hover {
    background: #e2e8f0;
  }

  .filter-chip.active {
    background: #059669;
    color: #ffffff;
    border-color: #059669;
    box-shadow: 0 4px 10px rgba(5, 150, 105, 0.2);
  }

  /* Gallery Grid & Items */
  .gallery-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 24px;
  }

  .gallery-item {
    position: relative;
    border-radius: 12px;
    overflow: hidden;
    background: #f1f5f9;
    aspect-ratio: 4 / 3;
    cursor: pointer;
    box-shadow: 0 4px 12px rgba(0,0,0,0.04);
  }

  .gallery-item img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s ease;
  }

  .gallery-item:hover img {
    transform: scale(1.08);
  }

  .gallery-item .caption {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    padding: 16px;
    background: linear-gradient(to top, rgba(0,0,0,0.85), transparent);
    color: #ffffff;
    font-size: 0.95rem;
    font-weight: 600;
    opacity: 0;
    transition: opacity 0.3s ease;
  }

  .gallery-item:hover .caption {
    opacity: 1;
  }

  /* Lightbox Overlay */
  .lightbox-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(15, 23, 42, 0.9);
    z-index: 9999;
    display: none;
    align-items: center;
    justify-content: center;
    padding: 20px;
    backdrop-filter: blur(4px);
  }

  .lightbox-overlay.open {
    display: flex;
  }

  .lightbox-content {
    max-width: 900px;
    width: 100%;
    text-align: center;
    position: relative;
  }

  .lightbox-content img {
    max-width: 100%;
    max-height: 75vh;
    border-radius: 8px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.5);
  }

  .lightbox-caption {
    color: #ffffff;
    margin-top: 16px;
    font-size: 1.1rem;
    font-weight: 600;
  }

  .lightbox-close {
    position: absolute;
    top: -40px;
    right: 0;
    background: transparent;
    border: none;
    color: #ffffff;
    font-size: 2.5rem;
    cursor: pointer;
    line-height: 1;
  }
</style>

<!-- Hero Section -->
<section class="page-hero">
  <div class="container">
    <div class="breadcrumb">
      <a href="<?= $navPrefix ?>index.php">Beranda</a> <span>/</span> <strong style="color: #ffffff;">Galeri</strong>
    </div>
    <h1>Galeri Kegiatan</h1>
    <p>Dokumentasi berbagai agenda kegiatan, pembangunan, dan fasilitas di <?= htmlspecialchars(defined('NAMA_KELURAHAN') ? NAMA_KELURAHAN : 'Kelurahan') ?>.</p>
  </div>
</section>

<!-- Main Gallery Section -->
<section class="page-section" style="padding: 48px 0 80px;">
  <div class="container">
    
    <!-- Category Filters -->
    <div class="gallery-filter" id="galleryFilter">
      <button class="filter-chip active" data-kategori="semua">Semua</button>
      <?php foreach ($kategoriList as $k): ?>
        <button class="filter-chip" data-kategori="<?= htmlspecialchars($k) ?>"><?= htmlspecialchars(ucfirst($k)) ?></button>
      <?php endforeach; ?>
    </div>

    <!-- Gallery Grid -->
    <?php if (empty($galeri)): ?>
      <div style="text-align: center; padding: 60px 0;">
        <p style="color: #64748b; font-size: 1.1rem;">Belum ada foto kegiatan di dalam galeri.</p>
      </div>
    <?php else: ?>
      <div class="gallery-grid" id="galleryGrid">
        <?php foreach ($galeri as $g): ?>
          <div class="gallery-item" 
               data-kategori="<?= htmlspecialchars($g['kategori']) ?>"
               data-img="../../<?= htmlspecialchars($g['gambar']) ?>" 
               data-caption="<?= htmlspecialchars($g['judul']) ?>">
            <img src="../../<?= htmlspecialchars($g['gambar']) ?>" alt="<?= htmlspecialchars($g['judul']) ?>" onerror="this.src='<?= $assetPrefix ?>img/placeholder-default.jpg'">
            <div class="caption"><?= htmlspecialchars($g['judul']) ?></div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

  </div>
</section>

<!-- Lightbox Modal Overlay -->
<div class="lightbox-overlay" id="lightboxOverlay">
  <div class="lightbox-content">
    <button class="lightbox-close" id="lightboxClose" aria-label="Tutup Modal">&times;</button>
    <img id="lightboxImg" src="" alt="">
    <div class="lightbox-caption" id="lightboxCaption"></div>
  </div>
</div>

<script>
  // Filter Functionality
  document.getElementById('galleryFilter').addEventListener('click', (e) => {
    const chip = e.target.closest('.filter-chip');
    if (!chip) return;
    
    document.querySelectorAll('#galleryFilter .filter-chip').forEach(c => c.classList.remove('active'));
    chip.classList.add('active');
    
    const kat = chip.dataset.kategori;
    document.querySelectorAll('#galleryGrid .gallery-item').forEach(item => {
      item.style.display = (kat === 'semua' || item.dataset.kategori === kat) ? 'block' : 'none';
    });
  });

  // Lightbox Functionality
  const overlay = document.getElementById('lightboxOverlay');
  const imgEl   = document.getElementById('lightboxImg');
  const capEl   = document.getElementById('lightboxCaption');

  document.querySelectorAll('.gallery-item').forEach(item => {
    item.addEventListener('click', () => {
      imgEl.src = item.dataset.img;
      capEl.textContent = item.dataset.caption;
      overlay.classList.add('open');
    });
  });

  document.getElementById('lightboxClose').addEventListener('click', () => overlay.classList.remove('open'));
  
  overlay.addEventListener('click', (e) => { 
    if (e.target === overlay) overlay.classList.remove('open'); 
  });
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>