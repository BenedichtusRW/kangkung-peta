<?php

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../config_db.php';

/*
|--------------------------------------------------------------------------
| Konfigurasi Halaman
|--------------------------------------------------------------------------
*/

$assetPrefix = '../../assets/';
$navPrefix   = '../';
$activeNav   = 'profile';
$pageTitle   = 'Sejarah Kelurahan';

/*
|--------------------------------------------------------------------------
| Data Sejarah Default
|--------------------------------------------------------------------------
*/

$defaultSejarah = <<<EOD
Nama Kangkung berasal dari tanaman kangkung. Menurut sumber sejarah masyarakat setempat, dahulu wilayah Kangkung terdiri atas daratan dan rawa kecil. Di kawasan rawa tersebut banyak tumbuh tanaman kangkung, sehingga masyarakat kemudian menyebut daerah tersebut sebagai Kampung Kangkung.

Pada masa awal, wilayah Kangkung dihuni oleh masyarakat Lampung Pesisir sebagai penduduk asli. Karena letaknya berada di kawasan pesisir Teluk Lampung, kehidupan masyarakat sejak dahulu banyak berkaitan dengan aktivitas laut dan perikanan.

Sekitar tahun 1952, datang rombongan menggunakan perahu besar dari Jawa Barat/Cirebon. Mereka datang ke kawasan pesisir Lampung untuk menangkap ikan dan kemudian menetap. Kehadiran masyarakat pendatang tersebut turut membentuk perkembangan masyarakat pesisir Kangkung yang sampai sekarang dikenal memiliki kehidupan yang erat dengan aktivitas nelayan.

Kawasan Kangkung juga dikenal dengan nama Ujung Bom. Nama tersebut berkaitan dengan kawasan dermaga di pesisir yang pada masa kolonial Belanda digunakan sebagai tempat pendaratan kapal. Karena sejarah kawasan ini, Ujung Bom kemudian menjadi salah satu bagian penting dari identitas kawasan pesisir Kangkung.

Dalam perkembangan pemerintahannya, Kangkung pada awalnya merupakan perkampungan, kemudian pada sekitar tahun 1960-an pemerintahan dipimpin oleh seorang Kepala Kampung. Selanjutnya sistem pemerintahan berubah menjadi pemerintahan kelurahan yang dipimpin oleh lurah.

Secara administratif, Kangkung dahulu termasuk wilayah Kecamatan Teluk Betung Selatan. Setelah dilakukan penataan wilayah Kota Bandar Lampung melalui Peraturan Daerah Kota Bandar Lampung Nomor 04 Tahun 2012, terbentuk Kecamatan Bumi Waras. Kangkung kemudian menjadi salah satu kelurahan yang berada di Kecamatan Bumi Waras.

Saat ini, berdasarkan portal resmi Pemerintah Kota Bandar Lampung, Kelurahan Kangkung merupakan bagian dari Kecamatan Bumi Waras dan terdiri atas 3 Lingkungan serta 27 RT.
EOD;

/*
|--------------------------------------------------------------------------
| Ambil Data Sejarah dari Database
|--------------------------------------------------------------------------
*/

$sejarahTeks = $defaultSejarah;

try {
    $pdo = getDB();

    $stmt = $pdo->prepare("
        SELECT key_value
        FROM konten
        WHERE key_name = :key_name
        LIMIT 1
    ");

    $stmt->execute([
        'key_name' => 'sejarah'
    ]);

    $row = $stmt->fetch();

    if ($row && !empty(trim($row['key_value']))) {
        $sejarahTeks = $row['key_value'];
    }
} catch (Exception $e) {
    // Jika database bermasalah, gunakan data sejarah default.
    $sejarahTeks = $defaultSejarah;
}

/*
|--------------------------------------------------------------------------
| Header
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("SELECT key_value FROM settings WHERE key_name = 'header_sejarah'");
$stmt->execute();
$header_sejarah = $stmt->fetchColumn();
$bgStyle = '';
if (!empty($header_sejarah)) {
    $header_sejarah = htmlspecialchars((string)$header_sejarah, ENT_QUOTES, 'UTF-8');
    $bgStyle = 'background: linear-gradient(rgba(0,0,0,0.3), rgba(0,0,0,0.5)), url(\'../../' . $header_sejarah . '\') center/cover; color: #ffffff;';
}

include __DIR__ . '/../includes/header.php';

?>

<!-- HERO SECTION -->
<section class="page-hero" style="<?= $bgStyle ?>">
  <div class="container">
    <div class="breadcrumb">
      <a href="<?= htmlspecialchars($navPrefix) ?>index.php">Beranda</a>
      <span>/</span>
      <span>Profile</span>
      <span>/</span>
      <strong style="color: #ffffff;">Sejarah Kelurahan</strong>
    </div>
    <h1>Sejarah Kelurahan Kangkung</h1>
    <p>Perjalanan terbentuknya <?= htmlspecialchars(defined('NAMA_KELURAHAN') ? NAMA_KELURAHAN : 'Kelurahan Kangkung') ?> hingga menjadi bagian penting dari kawasan pesisir Kota Bandar Lampung.</p>
  </div>
</section>

<!-- CONTENT SECTION -->
<section class="page-section" style="padding: 40px 0;">
    <div class="container container-md">
        <?php if (!empty($sejarahTeks)): ?>

            <div class="section-head section-head-full">
                <span class="eyebrow" style="color: #d97706; font-weight: 600;">LATAR BELAKANG</span>
                <h2 style="color: #064e3b; font-size: 2rem; font-weight: 700; margin-top: 6px;">Asal Usul Nama &amp; Wilayah</h2>
                <p class="section-desc">
                    Mengenal perjalanan sejarah, perkembangan masyarakat, serta perubahan administratif Kelurahan Kangkung.
                </p>
            </div>

            <article class="prose-text history-card">
                <?php
                $paragraphs = array_filter(
                    array_map(
                        'trim',
                        preg_split("/\R{2,}/", $sejarahTeks)
                    )
                );

                foreach ($paragraphs as $paragraph):
                ?>
                    <p><?= nl2br(htmlspecialchars($paragraph)) ?></p>
                <?php endforeach; ?>
            </article>

        <?php else: ?>

            <div class="empty-state">
                <strong>Data sejarah belum tersedia.</strong>
                <span>Silakan tambahkan informasi sejarah melalui halaman pengelolaan konten.</span>
            </div>

        <?php endif; ?>
    </div>
</section>

<?php
/*
|--------------------------------------------------------------------------
| Footer
|--------------------------------------------------------------------------
*/

include __DIR__ . '/../includes/footer.php';
?>