<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../config_db.php';

$assetPrefix = '../../assets/';
$navPrefix   = '../';
$activeNav   = 'berita';
$pageTitle   = 'Berita & Kegiatan';

$pdo = getDB();
$stmt = $pdo->query("SELECT * FROM berita WHERE status = 'published' ORDER BY tanggal DESC, id DESC");
$berita = $stmt->fetchAll();

$stmt = $pdo->prepare("SELECT key_value FROM settings WHERE key_name = 'header_berita'");
$stmt->execute();
$header_berita = $stmt->fetchColumn();
$bgStyle = '';
if (!empty($header_berita)) {
    $header_berita = htmlspecialchars((string) $header_berita, ENT_QUOTES, 'UTF-8');
    $bgStyle = 'background: linear-gradient(rgba(0,0,0,0.3), rgba(0,0,0,0.5)), url(\'../../' . $header_berita . '\') center/cover; color: #ffffff;';
}

include __DIR__ . '/../includes/header.php';
?>

<section class="page-hero" style="<?= $bgStyle ?>">
  <div class="container" data-aos="fade-down" data-aos-duration="800">
    <div class="breadcrumb">
      <a href="<?= $navPrefix ?>index.php">Beranda</a>
      <span>/</span>
      <strong style="color: #ffffff;">Berita</strong>
    </div>
    <h1>Berita &amp; Kegiatan</h1>
    <p>Informasi terbaru seputar kegiatan, pengumuman, dan program kerja di <?= htmlspecialchars(defined('NAMA_KELURAHAN') ? NAMA_KELURAHAN : 'Kelurahan') ?>.</p>
  </div>
</section>

<section class="submission-cta">
  <div class="container">
    <div class="cta-stack">
      <div class="cta-item">
        <div>
          <span class="eyebrow">Dokumentasi Visual</span>
          <h2>Ingin melihat foto &amp; video kegiatan?</h2>
          <p>Kunjungi galeri untuk melihat momen penting dari kegiatan kelurahan.</p>
        </div>
        <a href="../Galeri/galeri.php" class="btn-secondary">Jelajahi Galeri <span aria-hidden="true">→</span></a>
      </div>

      <div class="cta-item">
        <div>
          <span class="eyebrow">Partisipasi Warga</span>
          <h2>Punya kabar atau kegiatan di lingkungan Anda?</h2>
          <p>Kirim berita dan foto kegiatan untuk ditinjau oleh admin kelurahan.</p>
        </div>
        <a href="ajukan.php" class="btn-cta">Ajukan Berita <span aria-hidden="true">→</span></a>
      </div>
    </div>
  </div>
</section>

<section class="page-section" style="padding: 56px 0 80px;">
  <div class="container">
    <?php if (empty($berita)): ?>
      <div style="text-align: center; padding: 60px 0;" data-aos="fade-up">
        <p style="color: #64748b; font-size: 1.1rem; margin: 0;">Belum ada berita yang diterbitkan saat ini.</p>
      </div>
    <?php else: ?>
      <div class="news-grid">
        <?php
        $delay = 0;
        foreach ($berita as $b):
          $delay = ($delay >= 300) ? 100 : $delay + 100;
        ?>
          <a href="detail.php?slug=<?= urlencode($b['slug']) ?>"
             class="news-card"
             data-aos="fade-up"
             data-aos-delay="<?= $delay ?>"
             data-aos-duration="600">
            <div class="thumb-wrap">
              <img src="../../<?= htmlspecialchars($b['gambar']) ?>" alt="<?= htmlspecialchars($b['judul']) ?>" onerror="this.src='<?= $assetPrefix ?>img/placeholder-default.jpg'">
            </div>
            <div class="body">
              <span class="date">📅 <?= date('d M Y', strtotime($b['tanggal'])) ?></span>
              <h3><?= htmlspecialchars($b['judul']) ?></h3>
              <p><?= htmlspecialchars($b['ringkasan']) ?></p>
              <span class="read-more">Baca selengkapnya →</span>
            </div>
          </a>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</section>

<script src="https://unpkg.com/aos@next/dist/aos.js"></script>
<script>
  AOS.init({
    once: true,
    offset: 120
  });
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
