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

<?php
$stmt = $pdo->prepare("SELECT key_value FROM settings WHERE key_name = 'header_berita'");
$stmt->execute();
$header_berita = $stmt->fetchColumn();
$bgStyle = '';
if (!empty($header_berita)) {
    $header_berita = htmlspecialchars((string)$header_berita, ENT_QUOTES, 'UTF-8');
    $bgStyle = 'background: linear-gradient(rgba(0,0,0,0.3), rgba(0,0,0,0.5)), url(\'../../' . $header_berita . '\') center/cover; color: #ffffff;';
}
?>
<style>
.page-hero h1, .page-hero p, .page-hero .eyebrow, .page-hero .breadcrumb {
    text-shadow: 0 4px 25px rgba(0,0,0,0.9), 0 1px 4px rgba(0,0,0,0.6);
}
</style>
<section class="page-hero" style="<?= $bgStyle ?>">
  <div class="container" data-aos="fade-down" data-aos-duration="800">
    <div class="breadcrumb">
      <a href="<?= $navPrefix ?>index.php">Beranda</a> <span>/</span> <strong style="color: #ffffff;">Berita</strong>
    </div>
    <h1>Berita &amp; Kegiatan</h1>
    <p>Kabar terbaru, pengumuman, dan liputan kegiatan dari kelurahan kami.</p>
  </div>
</section>

<section class="submission-cta" style="background: var(--surface-muted); border-top: 1px solid var(--line); border-bottom: 1px solid var(--line); margin-top: 40px; margin-bottom: 20px;">
  <div class="container">
    <div style="display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 24px; padding-bottom: 24px; margin-bottom: 24px; border-bottom: 1px solid var(--line);">
      <div style="flex: 1; min-width: 280px;">
        <span class="eyebrow" style="color: var(--teal-700);">Dokumentasi Visual</span>
        <h2 style="color: var(--teal-900); margin-bottom: 8px;">Ingin melihat foto & video kegiatan?</h2>
        <p style="color: var(--ink-soft); margin: 0;">Kunjungi halaman Galeri untuk melihat kumpulan dokumentasi momen-momen penting kelurahan.</p>
      </div>
      <a href="../Galeri/galeri.php" class="btn btn-outline" style="border-color: var(--teal-700); color: var(--teal-700); white-space: nowrap;">Jelajahi Galeri &rarr;</a>
    </div>

    <div style="display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 24px;">
      <div style="flex: 1; min-width: 280px;">
        <span class="eyebrow" style="color: var(--teal-700);">Partisipasi warga</span>
        <h2 style="color: var(--teal-900); margin-bottom: 8px;">Punya kabar atau kegiatan di lingkungan Anda?</h2>
        <p style="color: var(--ink-soft); margin: 0;">Kirim berita dan foto kegiatan untuk ditinjau oleh admin kelurahan.</p>
      </div>
      <a href="ajukan.php" class="btn" style="background: var(--teal-700); color: #ffffff; white-space: nowrap;">Ajukan berita &rarr;</a>
    </div>
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
