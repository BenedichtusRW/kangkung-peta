<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../config_db.php';

$assetPrefix = '../../assets/';
$navPrefix = '../';
$activeNav = 'berita';
$pageTitle = 'Berita';

$pdo = getDB();
$stmt = $pdo->query("SELECT * FROM berita WHERE status = 'published' ORDER BY tanggal DESC, id DESC");
$berita = $stmt->fetchAll();

include __DIR__ . '/../includes/header.php';
?>

<!-- Library Animasi CSS (bisa dipindah ke header.php) -->
<link rel="stylesheet" href="https://unpkg.com/aos@next/dist/aos.css" />

<section class="page-hero">
  <div class="container" data-aos="fade-down" data-aos-duration="800">
    <div class="breadcrumb"><a href="<?= $navPrefix ?>index.php">Beranda</a> / Berita</div>
    <h1>Berita &amp; Kegiatan</h1>
    <p>Informasi terbaru seputar kegiatan dan program di <?= NAMA_KELURAHAN ?>.</p>
  </div>
</section>

<section class="submission-cta">
  <div class="container">
    <div>
      <span class="eyebrow">Partisipasi warga</span>
      <h2>Punya kabar atau kegiatan di lingkungan Anda?</h2>
      <p>Kirim berita dan foto kegiatan untuk ditinjau oleh admin kelurahan.</p>
    </div>
    <a href="ajukan.php" class="btn btn-primary">Ajukan berita <span aria-hidden="true">→</span></a>
  </div>
</section>

<section class="page-section">
  <div class="container">
    <?php if (empty($berita)): ?>
      <p style="color:var(--ink-soft); text-align: center; padding: 40px 0;" data-aos="fade-up">Belum ada berita yang diterbitkan.</p>
    <?php else: ?>
      <div class="news-grid">
        <?php 
        $delay = 0; 
        foreach ($berita as $b): 
          $delay += 100;
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
              <span class="read-more">Baca selengkapnya &rarr;</span>
            </div>
          </a>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</section>

<!-- Library Script Animasi (bisa dipindah ke footer.php) -->
<script src="https://unpkg.com/aos@next/dist/aos.js"></script>
<script>
  AOS.init({
    once: true,
    offset: 120
  });
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
