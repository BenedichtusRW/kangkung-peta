<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../config_db.php';

$assetPrefix = '../../assets/';
$navPrefix = '../';
$activeNav = 'galeri';
$pageTitle = 'Galeri';

$pdo = getDB();
$stmt = $pdo->query("SELECT * FROM galeri ORDER BY created_at DESC, id DESC");
$galeri = $stmt->fetchAll();
$kategoriList = array_values(array_unique(array_column($galeri, 'kategori')));

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
<style>
.page-hero h1, .page-hero p, .page-hero .eyebrow, .page-hero .breadcrumb {
    text-shadow: 0 4px 25px rgba(0,0,0,0.9), 0 1px 4px rgba(0,0,0,0.6);
}
</style>
<section class="page-hero" style="<?= $bgStyle ?>">
  <div class="container">
    <div class="breadcrumb">
      <a href="<?= $navPrefix ?>index.php">Beranda</a> <span>/</span> <strong style="color: #ffffff;">Galeri</strong>
    </div>
    <h1>Galeri Kegiatan</h1>
    <p>Dokumentasi foto dan video dari berbagai acara serta pembangunan di kelurahan kami.</p>
  </div>
</section>

<section class="page-section">
  <div class="container">
    <div class="gallery-filter" id="galleryFilter">
      <button class="filter-chip active" data-kategori="semua" style="--chip-color:#14574B">Semua</button>
      <?php foreach ($kategoriList as $k): ?>
        <button class="filter-chip" data-kategori="<?= htmlspecialchars($k) ?>" style="--chip-color:#D6A24C"><?= htmlspecialchars(ucfirst($k)) ?></button>
      <?php endforeach; ?>
    </div>

    <?php if (empty($galeri)): ?>
      <p style="color:var(--ink-soft)">Belum ada foto di galeri.</p>
    <?php else: ?>
      <div class="gallery-grid" id="galleryGrid">
        <?php foreach ($galeri as $g): ?>
          <div class="gallery-item" data-kategori="<?= htmlspecialchars($g['kategori']) ?>"
               data-img="../../<?= htmlspecialchars($g['gambar']) ?>" data-caption="<?= htmlspecialchars($g['judul']) ?>">
            <img src="../../<?= htmlspecialchars($g['gambar']) ?>" alt="<?= htmlspecialchars($g['judul']) ?>" onerror="this.src='<?= $assetPrefix ?>img/placeholder-default.jpg'">
            <div class="caption"><?= htmlspecialchars($g['judul']) ?></div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</section>

<section class="submission-cta" style="background: var(--surface-muted); border-top: 1px solid var(--line); border-bottom: 1px solid var(--line); margin-bottom: 40px; margin-top: 40px;">
  <div class="container" style="display: flex; flex-direction: column; gap: 24px;">
    <div style="display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 24px;">
      <div>
        <span class="eyebrow" style="color: var(--teal-700);">Kabar & Informasi</span>
        <h2 style="color: var(--teal-900); margin-bottom: 8px;">Ingin tahu cerita di balik dokumentasi ini?</h2>
        <p style="color: var(--ink-soft); margin: 0;">Baca rincian acara, pengumuman, dan kabar terbaru lainnya di halaman Berita & Kegiatan.</p>
      </div>
      <a href="../Berita/berita.php" class="btn" style="background: var(--teal-700); color: #ffffff;">Baca Berita &rarr;</a>
    </div>
  </div>
</section>

<div class="lightbox-overlay" id="lightboxOverlay">
  <button class="lightbox-close" id="lightboxClose">&times;</button>
  <div>
    <img id="lightboxImg" src="" alt="">
    <div class="lightbox-caption" id="lightboxCaption"></div>
  </div>
</div>

<script>
  document.getElementById('galleryFilter').addEventListener('click', (e) => {
    const chip = e.target.closest('.filter-chip');
    if (!chip) return;
    document.querySelectorAll('#galleryFilter .filter-chip').forEach(c => c.classList.remove('active'));
    chip.classList.add('active');
    const kat = chip.dataset.kategori;
    document.querySelectorAll('#galleryGrid .gallery-item').forEach(item => {
      item.style.display = (kat === 'semua' || item.dataset.kategori === kat) ? '' : 'none';
    });
  });

  const overlay = document.getElementById('lightboxOverlay');
  const imgEl = document.getElementById('lightboxImg');
  const capEl = document.getElementById('lightboxCaption');

  document.querySelectorAll('.gallery-item').forEach(item => {
    item.addEventListener('click', () => {
      imgEl.src = item.dataset.img;
      capEl.textContent = item.dataset.caption;
      overlay.classList.add('open');
    });
  });
  document.getElementById('lightboxClose').addEventListener('click', () => overlay.classList.remove('open'));
  overlay.addEventListener('click', (e) => { if (e.target === overlay) overlay.classList.remove('open'); });
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
