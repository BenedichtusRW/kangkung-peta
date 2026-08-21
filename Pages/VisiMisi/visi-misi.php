<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../config_db.php';

$assetPrefix = '../../assets/';
$navPrefix = '../';
$activeNav = 'profile';
$pageTitle = 'Visi & Misi';

$pdo = getDB();

// Fetch Visi & Misi from database
$stmt = $pdo->query("SELECT * FROM konten WHERE key_name IN ('visi_teks', 'misi_teks')");
$data = [];
while ($row = $stmt->fetch()) {
    $data[$row['key_name']] = $row['key_value'];
}

$visi = $data['visi_teks'] ?? 'Visi kelurahan belum diatur.';
$misiRaw = $data['misi_teks'] ?? '[]';
$misiArray = json_decode($misiRaw, true) ?: [];

include __DIR__ . '/../includes/header.php';
?>

<section class="page-hero">
  <div class="container">
    <div class="breadcrumb"><a href="<?= $navPrefix ?>index.php">Beranda</a> / Profile / Visi &amp; Misi</div>
    <h1>Visi &amp; Misi</h1>
    <p>Arah dan komitmen pembangunan <?= NAMA_KELURAHAN ?> untuk masyarakat yang lebih sejahtera.</p>
  </div>
</section>

<section class="page-section">
  <div class="container">
    <div class="section-head">
      <span class="eyebrow">Visi</span>
      <h2 style="line-height:1.4; max-width:900px; font-weight:700;">"<?= htmlspecialchars($visi) ?>"</h2>
    </div>
  </div>
</section>

<section class="page-section alt">
  <div class="container">
    <div class="section-head">
      <span class="eyebrow" style="color:var(--gold-500)">Misi</span>
      <h2>Langkah Mewujudkan Visi</h2>
      <p>Misi resmi <?= NAMA_KELURAHAN ?>.</p>
    </div>
    <div class="cards-grid">
      <?php if(empty($misiArray)): ?>
        <p>Belum ada data misi.</p>
      <?php else: ?>
        <?php foreach($misiArray as $index => $ms): ?>
        <div class="info-card">
          <div class="icon-badge"><?= $index + 1 ?></div>
          <p style="margin-top: 15px; font-weight:500; font-size:16px; color:var(--teal-900);"><?= htmlspecialchars($ms) ?></p>
        </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>
