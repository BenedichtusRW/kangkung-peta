<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../config_db.php';

$assetPrefix = '../../assets/';
$navPrefix = '../';
$activeNav = 'berita';

$slug = $_GET['slug'] ?? '';
$pdo = getDB();
$stmt = $pdo->prepare("SELECT * FROM berita WHERE slug = ? AND status = 'published' LIMIT 1");
$stmt->execute([$slug]);
$artikel = $stmt->fetch();

if (!$artikel) {
    header('Location: berita.php');
    exit;
}

$pageTitle = $artikel['judul'];

include __DIR__ . '/../includes/header.php';
?>

<section class="page-hero">
  <div class="container">
    <div class="breadcrumb"><a href="<?= $navPrefix ?>index.php">Beranda</a> / <a href="berita.php">Berita</a> / <?= htmlspecialchars($artikel['judul']) ?></div>
    <h1><?= htmlspecialchars($artikel['judul']) ?></h1>
  </div>
</section>

<section class="page-section">
  <div class="container">
    <div class="article-body">
      <p class="article-meta">Oleh <?= htmlspecialchars($artikel['penulis']) ?> • <?= date('d M Y', strtotime($artikel['tanggal'])) ?></p>
      <img class="article-img" src="../../<?= htmlspecialchars($artikel['gambar']) ?>" alt="" onerror="this.src='<?= $assetPrefix ?>img/placeholder-default.jpg'">
      <div class="content">
        <?php foreach (explode("\n\n", $artikel['konten']) as $para): if (trim($para) === '') continue; ?>
          <p><?= nl2br(htmlspecialchars($para)) ?></p>
        <?php endforeach; ?>
      </div>
      <a href="berita.php" class="btn btn-outline" style="margin-top:20px;">← Kembali ke Berita</a>
    </div>
  </div>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>
