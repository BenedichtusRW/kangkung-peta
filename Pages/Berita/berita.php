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

include __DIR__ . '/../includes/header.php';
?>

<!-- Library Animasi CSS AOS -->
<link rel="stylesheet" href="https://unpkg.com/aos@next/dist/aos.css" />

<<<<<<< HEAD
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

  /* Submission CTA Banner */
  .submission-cta {
    background: #ecfdf5;
    border-bottom: 1px solid #a7f3d0;
    padding: 32px 0;
  }

  .submission-cta .cta-box {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 20px;
    flex-wrap: wrap;
  }

  .submission-cta .eyebrow {
    font-size: 0.8rem;
    font-weight: 700;
    color: #059669;
    text-transform: uppercase;
    letter-spacing: 1px;
    display: block;
    margin-bottom: 4px;
  }

  .submission-cta h2 {
    font-size: 1.4rem;
    font-weight: 800;
    color: #064e3b;
    margin: 0 0 4px 0;
  }

  .submission-cta p {
    margin: 0;
    color: #047857;
    font-size: 0.95rem;
  }

  .submission-cta .btn-cta {
    background: #059669;
    color: #ffffff;
    padding: 12px 24px;
    border-radius: 8px;
    font-weight: 700;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: background 0.2s ease, transform 0.2s ease;
  }

  .submission-cta .btn-cta:hover {
    background: #047857;
    transform: translateY(-2px);
  }

  /* Grid Berita & Card */
  .news-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    gap: 30px;
  }

  .news-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    overflow: hidden;
    text-decoration: none;
    color: inherit;
    display: flex;
    flex-direction: column;
    box-shadow: 0 4px 12px rgba(0,0,0,0.03);
    transition: transform 0.2s ease, box-shadow 0.2s ease;
  }

  .news-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 24px rgba(0,0,0,0.08);
  }

  .news-card .thumb-wrap {
    width: 100%;
    height: 200px;
    overflow: hidden;
    background: #f1f5f9;
  }

  .news-card .thumb-wrap img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s ease;
  }

  .news-card:hover .thumb-wrap img {
    transform: scale(1.05);
  }

  .news-card .body {
    padding: 24px;
    display: flex;
    flex-direction: column;
    flex-grow: 1;
  }

  .news-card .date {
    font-size: 0.85rem;
    color: #64748b;
    font-weight: 600;
    margin-bottom: 8px;
    display: block;
  }

  .news-card h3 {
    font-size: 1.2rem;
    font-weight: 700;
    color: #0f172a;
    line-height: 1.4;
    margin: 0 0 10px 0;
  }

  .news-card p {
    font-size: 0.95rem;
    color: #475569;
    line-height: 1.5;
    margin: 0 0 20px 0;
    flex-grow: 1;
  }

  .news-card .read-more {
    font-size: 0.9rem;
    font-weight: 700;
    color: #059669;
    display: inline-flex;
    align-items: center;
    gap: 4px;
    transition: color 0.2s ease;
  }

  .news-card:hover .read-more {
    color: #047857;
    text-decoration: underline;
  }
</style>

<!-- Hero Section -->
<section class="page-hero">
=======
<?php
$stmt = $pdo->prepare("SELECT key_value FROM settings WHERE key_name = 'header_berita'");
$stmt->execute();
$header_berita = $stmt->fetchColumn();
$bgStyle = '';
if (!empty($header_berita)) {
    $header_berita = htmlspecialchars((string)$header_berita, ENT_QUOTES, 'UTF-8');
    $bgStyle = 'background: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.6)), url(\'../../' . $header_berita . '\') center/cover; color: #ffffff;';
}
?>
<section class="page-hero" style="<?= $bgStyle ?>">
>>>>>>> e8b5ffee368934b8f0a1fe53437987be8017a4f8
  <div class="container" data-aos="fade-down" data-aos-duration="800">
    <div class="breadcrumb">
      <a href="<?= $navPrefix ?>index.php">Beranda</a> <span>/</span> <strong style="color: #ffffff;">Berita</strong>
    </div>
    <h1>Berita &amp; Kegiatan</h1>
    <p>Informasi terbaru seputar kegiatan, pengumuman, dan program kerja di <?= htmlspecialchars(defined('NAMA_KELURAHAN') ? NAMA_KELURAHAN : 'Kelurahan') ?>.</p>
  </div>
</section>

<!-- Call to Action Submission -->
<section class="submission-cta">
  <div class="container">
    <div class="cta-box">
      <div>
        <span class="eyebrow">Partisipasi Warga</span>
        <h2>Punya kabar atau kegiatan di lingkungan Anda?</h2>
        <p>Kirim berita dan foto kegiatan wilayah Anda untuk ditinjau dan diterbitkan oleh admin kelurahan.</p>
      </div>
      <a href="ajukan.php" class="btn-cta">
        Ajukan Berita <span aria-hidden="true">→</span>
      </a>
    </div>
  </div>
</section>

<!-- List Berita Section -->
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
          $delay = ($delay >= 300) ? 100 : $delay + 100; // Reset delay maks 300ms agar animasi tetap cepat di grid
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

<!-- Library Script Animasi AOS -->
<script src="https://unpkg.com/aos@next/dist/aos.js"></script>
<script>
  AOS.init({
    once: true,
    offset: 120
  });
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>