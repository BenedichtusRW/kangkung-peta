<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../admin/includes/functions.php';

// Helper function untuk sanitasi string output HTML (XSS Protection)
if (!function_exists('e')) {
    function e($string) {
        return htmlspecialchars((string)$string, ENT_QUOTES, 'UTF-8');
    }
}

$assetPrefix = '../assets/';
$navPrefix   = '';
$activeNav   = 'beranda';
$pageTitle   = 'Beranda';

require_once __DIR__ . '/../config_db.php';
$pdo = getDB();

$stmt = $pdo->query("SELECT * FROM statistik");
$statistik = [];
while ($row = $stmt->fetch()) {
    $val = json_decode($row['key_value'], true);
    $statistik[$row['key_name']] = (json_last_error() == JSON_ERROR_NONE && !is_null($val)) ? $val : $row['key_value'];
}

$stmt = $pdo->query("SELECT * FROM berita WHERE status = 'published' ORDER BY tanggal DESC, id DESC LIMIT 3");
$beritaTerbaru = $stmt->fetchAll();

$stmt = $pdo->query("SELECT * FROM galeri ORDER BY created_at DESC, id DESC LIMIT 5");
$slides = $stmt->fetchAll();

$stmt = $pdo->query("SELECT * FROM pois");
$pois = $stmt->fetchAll();

include __DIR__ . '/includes/header.php';
?>

<section class="hero-home">
  <div class="container hero-home-grid">
    <div class="hero-copy" data-reveal>
      <span class="eyebrow">Portal Digital Warga</span>
      <h1>Informasi kelurahan, lebih dekat dengan warga.</h1>
      <p class="lead">Akses layanan, data wilayah, peta fasilitas, dan kabar terbaru dari <?= e(defined('NAMA_KELURAHAN') ? NAMA_KELURAHAN : 'Kelurahan') ?> dalam satu tempat.</p>
      <div class="hero-actions">
        <a href="Peta/peta.php" class="btn btn-primary">Jelajahi Peta</a>
        <a href="Chatbot/chatbot.php" class="btn btn-on-dark">Tanya Asisten</a>
      </div>
    </div>

    <div class="home-slider" id="homeSlider" data-reveal aria-label="Sorotan kegiatan kelurahan">
      <?php if (empty($slides)): ?>
        <div class="home-slide is-active">
          <img src="<?= e($assetPrefix) ?>img/hero-kangkung.jpg" alt="Suasana <?= e(defined('NAMA_KELURAHAN') ? NAMA_KELURAHAN : 'Kelurahan') ?>">
          <div class="home-slide-caption">
            <span><?= e(defined('NAMA_KELURAHAN') ? 'Kelurahan ' . NAMA_KELURAHAN : 'Kelurahan') ?></span>
            <strong>Informasi untuk setiap warga</strong>
          </div>
        </div>
      <?php else: ?>
        <?php foreach ($slides as $index => $slide): 
          // Cek path gambar agar tidak dobel prefix
          $imgPath = $slide['gambar'] ?? '';
          if (!empty($imgPath) && strpos($imgPath, 'http') !== 0 && strpos($imgPath, '/') !== 0 && strpos($imgPath, '../') !== 0) {
              $imgPath = '../' . $imgPath;
          }
        ?>
          <div class="home-slide<?= $index === 0 ? ' is-active' : '' ?>">
            <img src="<?= e($imgPath) ?>" 
                 alt="<?= e($slide['judul'] ?? 'Dokumentasi') ?>" 
                 onerror="this.onerror=null; this.src='<?= e($assetPrefix) ?>img/placeholder-default.jpg';">
            <div class="home-slide-caption">
              <span>Dokumentasi Kegiatan</span>
              <strong><?= e($slide['judul'] ?? '') ?></strong>
            </div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>

      <div class="slider-controls">
        <button class="slider-arrow" type="button" data-slider-prev aria-label="Foto sebelumnya">&larr;</button>
        <div class="slider-dots" aria-label="Pilih foto"></div>
        <button class="slider-arrow" type="button" data-slider-next aria-label="Foto berikutnya">&rarr;</button>
      </div>
    </div>
  </div>
</section>

<section class="page-section">
  <div class="container">
    <div class="section-head" data-reveal>
      <span class="eyebrow">Jelajahi Layanan</span>
      <h2>Semua kebutuhan warga dalam satu portal</h2>
      <p>Pilih halaman yang ingin Anda kunjungi untuk memperoleh informasi dengan cepat.</p>
    </div>
    
    <div class="quicklinks" data-reveal>
      <a href="VisiMisi/visi-misi.php" class="quicklink-card"><span class="ico">01</span><span class="label">Visi &amp; Misi</span></a>
      <a href="Sejarah/sejarah.php" class="quicklink-card"><span class="ico">02</span><span class="label">Sejarah</span></a>
      <a href="Data-Aparatur/aparatur.php" class="quicklink-card"><span class="ico">03</span><span class="label">Aparatur</span></a>
      <a href="Tim-KKN/tim-kkn.php" class="quicklink-card"><span class="ico">04</span><span class="label">Tim KKN</span></a>
      <a href="Peta/peta.php" class="quicklink-card"><span class="ico">05</span><span class="label">Peta Wilayah</span></a>
      <a href="Chatbot/chatbot.php" class="quicklink-card"><span class="ico">06</span><span class="label">Asisten AI</span></a>
      <a href="Statistik/statistik.php" class="quicklink-card"><span class="ico">07</span><span class="label">Statistik</span></a>
      <a href="Berita/berita.php" class="quicklink-card"><span class="ico">08</span><span class="label">Berita</span></a>
      <a href="Galeri/galeri.php" class="quicklink-card"><span class="ico">09</span><span class="label">Galeri</span></a>
    </div>
  </div>
</section>

<section class="page-section alt">
  <div class="container">
    <div class="section-head section-head-row" data-reveal>
      <div>
        <span class="eyebrow">Kabar Terkini</span>
        <h2>Berita &amp; Kegiatan Terbaru</h2>
      </div>
      <a href="Berita/berita.php" class="btn btn-outline">Semua berita <span aria-hidden="true">&rarr;</span></a>
    </div>

    <?php if (empty($beritaTerbaru)): ?>
      <p class="empty-state">Belum ada berita yang diterbitkan.</p>
    <?php else: ?>
      <div class="news-grid" data-reveal>
        <?php foreach ($beritaTerbaru as $b): 
          $newsImgPath = $b['gambar'] ?? '';
          if (!empty($newsImgPath) && strpos($newsImgPath, 'http') !== 0 && strpos($newsImgPath, '/') !== 0 && strpos($newsImgPath, '../') !== 0) {
              $newsImgPath = '../' . $newsImgPath;
          }
        ?>
          <a href="Berita/detail.php?slug=<?= urlencode($b['slug'] ?? '') ?>" class="news-card">
            <div class="thumb-wrap">
              <img src="<?= e($newsImgPath) ?>" 
                   alt="<?= e($b['judul'] ?? 'Berita') ?>" 
                   onerror="this.onerror=null; this.src='<?= e($assetPrefix) ?>img/placeholder-default.jpg';">
            </div>
            <div class="body">
              <span class="date"><?= !empty($b['tanggal']) ? date('d M Y', strtotime($b['tanggal'])) : '-' ?></span>
              <h3><?= e($b['judul'] ?? '') ?></h3>
              <p><?= e($b['ringkasan'] ?? '') ?></p>
              <span class="read-more">Baca selengkapnya <span aria-hidden="true">&rarr;</span></span>
            </div>
          </a>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const slider = document.getElementById('homeSlider');
  if (!slider) return;

  const slides = [...slider.querySelectorAll('.home-slide')];
  const dotsContainer = slider.querySelector('.slider-dots');
  const prevBtn = slider.querySelector('[data-slider-prev]');
  const nextBtn = slider.querySelector('[data-slider-next]');

  if (slides.length < 2) return;

  let active = 0;
  let timer = null;

  const goTo = (next) => {
    active = (next + slides.length) % slides.length;
    slides.forEach((slide, index) => slide.classList.toggle('is-active', index === active));
    if (dotsContainer) {
      [...dotsContainer.children].forEach((dot, index) => dot.classList.toggle('is-active', index === active));
    }
  };

  if (dotsContainer) {
    dotsContainer.innerHTML = '';
    slides.forEach((_, index) => {
      const dot = document.createElement('button');
      dot.type = 'button';
      dot.className = index === 0 ? 'is-active' : '';
      dot.setAttribute('aria-label', `Tampilkan foto ${index + 1}`);
      dot.addEventListener('click', () => goTo(index));
      dotsContainer.appendChild(dot);
    });
  }

  if (prevBtn) prevBtn.addEventListener('click', () => goTo(active - 1));
  if (nextBtn) nextBtn.addEventListener('click', () => goTo(active + 1));

  const play = () => { 
    clearInterval(timer); 
    timer = setInterval(() => goTo(active + 1), 5000); 
  };

  slider.addEventListener('mouseenter', () => clearInterval(timer));
  slider.addEventListener('mouseleave', play);
  
  play();
});
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>