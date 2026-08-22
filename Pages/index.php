<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../admin/includes/functions.php';

// Helper function untuk sanitasi string output HTML
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

<style>
/* CSS Modern Quicklinks / Services */
.quicklinks-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: 20px;
  margin-top: 24px;
}

.quicklink-card-modern {
  background: #ffffff;
  border: 1px solid #e5e7eb;
  border-radius: 16px;
  padding: 24px;
  display: flex;
  align-items: flex-start;
  gap: 16px;
  text-decoration: none;
  color: inherit;
  transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.02);
  position: relative;
  overflow: hidden;
}

.quicklink-card-modern::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 4px;
  background: linear-gradient(90deg, #059669, #10b981);
  opacity: 0;
  transition: opacity 0.25s ease;
}

.quicklink-card-modern:hover {
  transform: translateY(-4px);
  box-shadow: 0 12px 24px -8px rgba(5, 150, 105, 0.15);
  border-color: #a7f3d0;
}

.quicklink-card-modern:hover::before {
  opacity: 1;
}

.icon-box {
  width: 48px;
  height: 48px;
  border-radius: 12px;
  background: #ecfdf5;
  color: #059669;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  transition: all 0.25s ease;
}

.quicklink-card-modern:hover .icon-box {
  background: #059669;
  color: #ffffff;
}

.icon-box svg {
  width: 24px;
  height: 24px;
  stroke-width: 2;
}

.card-content h3 {
  margin: 0 0 4px 0;
  font-size: 1.1rem;
  font-weight: 700;
  color: #111827;
}

.card-content p {
  margin: 0;
  font-size: 0.825rem;
  color: #6b7280;
  line-height: 1.4;
}
</style>

<?php
$stmt = $pdo->prepare("SELECT key_value FROM settings WHERE key_name = 'header_beranda'");
$stmt->execute();
$header_beranda = $stmt->fetchColumn();

$bgStyle = '';
if (!empty($header_beranda)) {
    $bgStyle = 'background: linear-gradient(rgba(0, 0, 0, 0.2), rgba(0, 0, 0, 0.4)), url(\'../' . e($header_beranda) . '\') center/cover; color: #ffffff;';
}
?>
<style>
.hero-copy.glass-box {
    background: rgba(0, 0, 0, 0.4);
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
    padding: 32px;
    border-radius: 24px;
    border: 1px solid rgba(255, 255, 255, 0.15);
    box-shadow: 0 10px 30px rgba(0,0,0,0.3);
}
.hero-copy h1, .hero-copy p, .hero-copy .eyebrow {
    text-shadow: 0 2px 8px rgba(0,0,0,0.5);
}
</style>
<section class="hero-home" style="padding-top: 100px; <?= $bgStyle ?>">
  <div class="container hero-home-grid">
    <div class="hero-copy <?= !empty($header_beranda) ? 'glass-box' : '' ?>" data-reveal>
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

<!-- Section Jelajahi Layanan -->
<section class="page-section" style="background-color: #f9fafb; padding: 60px 0;">
  <div class="container">
    <div class="section-head" data-reveal style="margin-bottom: 32px;">
      <span class="eyebrow" style="color: #d97706; font-weight: 600;">JELAJAHI LAYANAN</span>
      <h2 style="font-size: 2.2rem; font-weight: 700; color: #064e3b; margin-top: 4px;">Semua kebutuhan warga dalam satu portal</h2>
      <p style="color: #6b7280;">Pilih halaman yang ingin Anda kunjungi untuk memperoleh informasi dengan cepat.</p>
    </div>
    
    <div class="quicklinks-grid" data-reveal>
      
      <a href="VisiMisi/visi-misi.php" class="quicklink-card-modern">
        <div class="icon-box">
          <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
        </div>
        <div class="card-content">
          <h3>Visi &amp; Misi</h3>
          <p>Arah kebijakan dan cita-cita pembangunan wilayah.</p>
        </div>
      </a>

      <a href="Sejarah/sejarah.php" class="quicklink-card-modern">
        <div class="icon-box">
          <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>
        <div class="card-content">
          <h3>Sejarah</h3>
          <p>Asal-usul dan rekam jejak perkembangan wilayah.</p>
        </div>
      </a>

      <a href="Data-Aparatur/aparatur.php" class="quicklink-card-modern">
        <div class="icon-box">
          <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
        </div>
        <div class="card-content">
          <h3>Aparatur</h3>
          <p>Struktur organisasi dan pengurus kelurahan.</p>
        </div>
      </a>

      <a href="Tim-KKN/tim-kkn.php" class="quicklink-card-modern">
        <div class="icon-box">
          <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 14l9-5-9-5-9 5 9 5z"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0112 20.055a11.952 11.952 0 01-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/></svg>
        </div>
        <div class="card-content">
          <h3>Tim KKN</h3>
          <p>Profil mahasiswa pengabdi Kelompok 31 UIN RIL.</p>
        </div>
      </a>

      <a href="Peta/peta.php" class="quicklink-card-modern">
        <div class="icon-box">
          <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>
        </div>
        <div class="card-content">
          <h3>Peta Wilayah</h3>
          <p>Peta interaktif titik fasilitas dan potensi daerah.</p>
        </div>
      </a>

      <a href="Chatbot/chatbot.php" class="quicklink-card-modern">
        <div class="icon-box">
          <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
        </div>
        <div class="card-content">
          <h3>Asisten AI</h3>
          <p>Tanya jawab cepat terkait pelayanan publik.</p>
        </div>
      </a>

      <a href="Statistik/statistik.php" class="quicklink-card-modern">
        <div class="icon-box">
          <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 012 2h2a2 2 0 012-2z"/></svg>
        </div>
        <div class="card-content">
          <h3>Statistik</h3>
          <p>Data demografi kependudukan dan statistik wilayah.</p>
        </div>
      </a>

      <a href="Berita/berita.php" class="quicklink-card-modern">
        <div class="icon-box">
          <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/></svg>
        </div>
        <div class="card-content">
          <h3>Berita</h3>
          <p>Informasi terbaru seputar kegiatan kelurahan.</p>
        </div>
      </a>

      <a href="Galeri/galeri.php" class="quicklink-card-modern">
        <div class="icon-box">
          <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
        </div>
        <div class="card-content">
          <h3>Galeri</h3>
          <p>Kumpulan foto dokumentasi momen kelurahan.</p>
        </div>
      </a>

    </div>
  </div>
</section>

<!-- Section Kabar Terkini -->
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