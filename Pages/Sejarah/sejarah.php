<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../config_db.php';

$assetPrefix = '../../assets/';
$navPrefix = '../';
$activeNav = 'profile';
$pageTitle = 'Sejarah Kelurahan';

// Teks Sejarah default (Fallback jika database belum terisi)
$defaultSejarah = <<<EOD
Nama Kangkung berasal dari tanaman kangkung. Menurut sumber sejarah masyarakat setempat, dahulu wilayah Kangkung terdiri atas daratan dan rawa kecil. Di kawasan rawa tersebut banyak tumbuh tanaman kangkung, sehingga masyarakat kemudian menyebut daerah tersebut sebagai Kampung Kangkung.

Pada masa awal, wilayah Kangkung dihuni oleh masyarakat Lampung Pesisir sebagai penduduk asli. Karena letaknya berada di kawasan pesisir Teluk Lampung, kehidupan masyarakat sejak dahulu banyak berkaitan dengan aktivitas laut dan perikanan.

Sekitar tahun 1952, datang rombongan menggunakan perahu besar dari Jawa Barat/Cirebon. Mereka datang ke kawasan pesisir Lampung untuk menangkap ikan dan kemudian menetap. Kehadiran masyarakat pendatang tersebut turut membentuk perkembangan masyarakat pesisir Kangkung yang sampai sekarang dikenal memiliki kehidupan yang erat dengan aktivitas nelayan.

Kawasan Kangkung juga dikenal dengan nama Ujung Bom. Nama tersebut berkaitan dengan kawasan dermaga di pesisir yang pada masa kolonial Belanda digunakan sebagai tempat pendaratan kapal. Karena sejarah kawasan ini, Ujung Bom kemudian menjadi salah satu bagian penting dari identitas kawasan pesisir Kangkung.

Dalam perkembangan pemerintahannya, Kangkung pada awalnya merupakan perkampungan, kemudian pada sekitar tahun 1960-an pemerintahan dipimpin oleh seorang Kepala Kampung. Selanjutnya sistem pemerintahan berubah menjadi pemerintahan kelurahan yang dipimpin oleh lurah.

Secara administratif, Kangkung dahulu termasuk wilayah Kecamatan Teluk Betung Selatan. Setelah dilakukan penataan wilayah Kota Bandar Lampung melalui Peraturan Daerah Kota Bandar Lampung Nomor 04 Tahun 2012, terbentuk Kecamatan Bumi Waras. Kangkung kemudian menjadi salah satu kelurahan yang berada di Kecamatan Bumi Waras.

Saat ini, berdasarkan portal resmi Pemerintah Kota Bandar Lampung, Kelurahan Kangkung merupakan bagian dari Kecamatan Bumi Waras dan terdiri atas 3 Lingkungan serta 27 RT.
EOD;

// Ambil dari database, jika kosong / error pakai defaultSejarah
$pdo = getDB();
try {
    $row = $pdo->query("SELECT key_value FROM konten WHERE key_name = 'sejarah'")->fetch();
    $sejarahTeks = ($row && !empty($row['key_value'])) ? $row['key_value'] : $defaultSejarah;
} catch(Exception $e) {
    $sejarahTeks = $defaultSejarah;
}

include __DIR__ . '/../includes/header.php';
?>

<section class="page-hero">
  <div class="container">
    <div class="breadcrumb"><a href="<?= $navPrefix ?>index.php">Beranda</a> / Profile / Sejarah Kelurahan</div>
    <h1>Sejarah Kelurahan Kangkung</h1>
    <p>Perjalanan terbentuknya <?= defined('NAMA_KELURAHAN') ? NAMA_KELURAHAN : 'Kelurahan Kangkung' ?> hingga menjadi seperti sekarang.</p>
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
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>