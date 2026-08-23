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

$forceSolidHeader = true;
include __DIR__ . '/../includes/header.php';
?>

<?php
$stmt = $pdo->prepare("SELECT key_value FROM settings WHERE key_name = 'header_galeri'");
$stmt->execute();
$header_galeri = $stmt->fetchColumn();
$bgStyle = '';
if (!empty($header_galeri)) {
    $header_galeri = htmlspecialchars((string)$header_galeri, ENT_QUOTES, 'UTF-8');
    $bgStyle = 'background: linear-gradient(rgba(0,0,0,0.3), rgba(0,0,0,0.5)), url(\'../../' . $header_galeri . '\') center/cover; color: #ffffff;';
}
?>

<!-- Hero Section -->
<section class="page-hero" style="<?= $bgStyle ?>">
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

<section class="submission-cta" style="background: var(--surface-muted); border-top: 1px solid var(--line); border-bottom: 1px solid var(--line); margin-bottom: 40px; margin-top: 20px;">
  <div class="container" style="flex-direction: column; align-items: stretch; gap: 0;">
    <div style="display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 24px;">
      <div style="flex: 1; min-width: 280px;">
        <span class="eyebrow" style="color: var(--teal-700);">Kabar & Informasi</span>
        <h2 style="color: var(--teal-900); margin-bottom: 8px;">Ingin tahu cerita di balik dokumentasi ini?</h2>
        <p style="color: var(--ink-soft); margin: 0;">Baca rincian acara, pengumuman, dan kabar terbaru lainnya di halaman Berita & Kegiatan.</p>
      </div>
      <a href="../Berita/berita.php" class="btn btn-primary" style="white-space: nowrap;">Baca Berita &rarr;</a>
    </div>
  </div>
</section>
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