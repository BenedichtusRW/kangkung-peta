<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../config_db.php';

$assetPrefix = '../../assets/';
$navPrefix = '../';
$activeNav = 'profile';
$pageTitle = 'Sejarah Kelurahan';

// Ambil dari database, fallback ke teks hardcode
$pdo = getDB();
try {
    $row = $pdo->query("SELECT key_value FROM konten WHERE key_name = 'sejarah'")->fetch();
    $sejarahTeks = $row ? $row['key_value'] : '';
} catch(Exception $e) {
    $sejarahTeks = '';
}

include __DIR__ . '/../includes/header.php';
?>

<section class="page-hero">
  <div class="container">
    <div class="breadcrumb"><a href="<?= $navPrefix ?>index.php">Beranda</a> / Profile / Sejarah Kelurahan</div>
    <h1>Sejarah Kelurahan Kangkung</h1>
    <p>Perjalanan terbentuknya <?= NAMA_KELURAHAN ?> hingga menjadi seperti sekarang.</p>
  </div>
</section>

<section class="page-section">
  <div class="container" style="max-width:820px;">

    <?php if ($sejarahTeks): ?>
      <div class="section-head" style="max-width:100%;">
        <span class="eyebrow">Latar Belakang</span>
        <h2>Asal Usul Nama &amp; Wilayah</h2>
      </div>
      <div class="prose-text" style="line-height:1.9; color:#374151; font-size:15.5px;">
        <?php
          $paragraphs = array_filter(array_map('trim', explode("\n\n", $sejarahTeks)));
          foreach ($paragraphs as $p) {
              echo '<p>' . nl2br(htmlspecialchars($p)) . '</p>';
          }
        ?>
      </div>
    <?php endif; ?>

    <div class="timeline" style="margin-top: 48px;">
      <div class="timeline-item">
        <div class="year">Awal</div>
        <h4>Kampung Kangkung — Asal Mula Nama</h4>
        <p>Wilayah ini terdiri atas daratan dan rawa kecil yang dipenuhi tanaman kangkung. Masyarakat Lampung Pesisir sebagai penduduk asli menghuni wilayah pesisir Teluk Lampung dan menjalani kehidupan nelayan.</p>
      </div>
      <div class="timeline-item">
        <div class="year">1952</div>
        <h4>Kedatangan Perantau dari Cirebon</h4>
        <p>Rombongan nelayan dari Jawa Barat/Cirebon datang menggunakan perahu besar dan menetap di wilayah pesisir Kangkung, turut membentuk karakter masyarakat nelayan yang kuat hingga saat ini.</p>
      </div>
      <div class="timeline-item">
        <div class="year">1960-an</div>
        <h4>Berdirinya Pemerintahan Kampung</h4>
        <p>Kangkung mulai memiliki pemerintahan resmi dengan dipimpin oleh seorang Kepala Kampung, yang kemudian berkembang menjadi sistem pemerintahan kelurahan yang dipimpin oleh Lurah.</p>
      </div>
      <div class="timeline-item">
        <div class="year">2012</div>
        <h4>Masuk Kecamatan Bumi Waras</h4>
        <p>Melalui Peraturan Daerah Kota Bandar Lampung Nomor 04 Tahun 2012, dibentuk Kecamatan Bumi Waras. Kelurahan Kangkung resmi menjadi bagian dari kecamatan baru ini, terdiri atas 3 Lingkungan dan 27 RT.</p>
      </div>
      <div class="timeline-item">
        <div class="year">2026</div>
        <h4>Digitalisasi Layanan Kelurahan</h4>
        <p>Kelurahan Kangkung mulai memiliki portal informasi digital, dibangun oleh mahasiswa KKN UIN RIL Kelompok 31, untuk mempermudah akses layanan dan informasi bagi seluruh warga Kangkung.</p>
      </div>
    </div>
  </div>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>
