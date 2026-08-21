<?php
session_start();
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../includes/berita_submission.php';

$assetPrefix = '../../assets/';
$navPrefix = '../';
$activeNav = 'berita';
$pageTitle = 'Ajukan Berita';
$errors = [];
$success = false;
$form = ['nama' => '', 'kontak' => '', 'judul' => '', 'ringkasan' => '', 'konten' => ''];

if (empty($_SESSION['berita_csrf'])) {
    $_SESSION['berita_csrf'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($form as $key => $_) $form[$key] = trim((string) ($_POST[$key] ?? ''));
    if (!hash_equals($_SESSION['berita_csrf'], (string) ($_POST['csrf_token'] ?? ''))) $errors[] = 'Sesi formulir tidak valid. Muat ulang halaman lalu coba lagi.';
    if (mb_strlen($form['nama']) < 3 || mb_strlen($form['nama']) > 80) $errors[] = 'Nama pengaju harus terdiri dari 3–80 karakter.';
    if (mb_strlen($form['kontak']) < 6 || mb_strlen($form['kontak']) > 100) $errors[] = 'Nomor WhatsApp atau email wajib diisi.';
    if (mb_strlen($form['judul']) < 10 || mb_strlen($form['judul']) > 140) $errors[] = 'Judul berita harus terdiri dari 10–140 karakter.';
    if (mb_strlen($form['ringkasan']) < 20 || mb_strlen($form['ringkasan']) > 300) $errors[] = 'Ringkasan harus terdiri dari 20–300 karakter.';
    if (mb_strlen($form['konten']) < 80 || mb_strlen($form['konten']) > 8000) $errors[] = 'Isi berita harus terdiri dari 80–8.000 karakter.';

    $uploadError = null;
    $gambar = empty($errors) ? berita_upload_foto('gambar', $uploadError) : null;
    if ($uploadError) $errors[] = $uploadError;

    if (!$errors) {
        require_once __DIR__ . '/../../config_db.php';
        $pdo = getDB();
        
        $stmt = $pdo->prepare("INSERT INTO berita (slug, judul, ringkasan, konten, gambar, penulis, tanggal, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'pending')");
        $slug = 'pengajuan-' . time() . '-' . rand(100, 999);
        $res = $stmt->execute([
            $slug, $form['judul'], $form['ringkasan'], $form['konten'], $gambar, $form['nama'] . ' (' . $form['kontak'] . ')', date('Y-m-d')
        ]);
        
        if ($res) {
            $success = true;
            $form = ['nama' => '', 'kontak' => '', 'judul' => '', 'ringkasan' => '', 'konten' => ''];
            $_SESSION['berita_csrf'] = bin2hex(random_bytes(32));
        } else {
            $errors[] = 'Pengajuan belum dapat disimpan. Silakan coba lagi nanti.';
        }
    }
}

include __DIR__ . '/../includes/header.php';
?>

<section class="page-hero">
  <div class="container">
    <div class="breadcrumb"><a href="<?= $navPrefix ?>index.php">Beranda</a> / <a href="berita.php">Berita</a> / Ajukan Berita</div>
    <h1>Bagikan kabar baik dari lingkungan Anda</h1>
    <p>Kirim kegiatan, pengumuman, atau cerita warga. Semua pengajuan akan ditinjau admin sebelum diterbitkan.</p>
  </div>
</section>

<section class="page-section">
  <div class="container">
    <div class="submission-layout">
      <aside class="submission-note">
        <span class="eyebrow">Panduan pengajuan</span>
        <h2>Berita yang bermanfaat untuk warga</h2>
        <ul>
          <li>Gunakan judul yang jelas dan sesuai kegiatan.</li>
          <li>Tulis informasi faktual, waktu, dan lokasi kegiatan.</li>
          <li>Unggah satu foto kegiatan berformat JPG, PNG, atau WebP.</li>
          <li>Admin dapat menyunting atau menolak konten yang tidak sesuai.</li>
        </ul>
      </aside>

      <div class="submission-card">
        <?php if ($success): ?>
          <div class="submission-success"><strong>Pengajuan berhasil dikirim.</strong><p>Terima kasih. Admin akan meninjau berita dan foto Anda sebelum dipublikasikan.</p><a href="berita.php" class="btn btn-primary">Kembali ke Berita</a></div>
        <?php else: ?>
          <h2>Form pengajuan berita</h2>
          <p class="form-intro">Kolom bertanda * wajib diisi.</p>
          <?php if ($errors): ?><div class="form-alert"><?php foreach ($errors as $error): ?><div><?= htmlspecialchars($error) ?></div><?php endforeach; ?></div><?php endif; ?>
          <form method="post" enctype="multipart/form-data" class="submission-form">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['berita_csrf']) ?>">
            <div class="form-two-col">
              <label>Nama pengaju *<input name="nama" required maxlength="80" value="<?= htmlspecialchars($form['nama']) ?>" placeholder="Nama lengkap"></label>
              <label>WhatsApp / email *<input name="kontak" required maxlength="100" value="<?= htmlspecialchars($form['kontak']) ?>" placeholder="Contoh: 0812xxxx / nama@email.com"></label>
            </div>
            <label>Judul berita *<input name="judul" required maxlength="140" value="<?= htmlspecialchars($form['judul']) ?>" placeholder="Contoh: Kerja bakti warga di Lingkungan 2"></label>
            <label>Ringkasan *<textarea name="ringkasan" required maxlength="300" rows="3" placeholder="Ringkasan singkat yang akan tampil di kartu berita."><?= htmlspecialchars($form['ringkasan']) ?></textarea></label>
            <label>Isi berita *<textarea name="konten" required maxlength="8000" rows="8" placeholder="Ceritakan kegiatan secara lengkap: waktu, lokasi, pihak yang terlibat, dan hasil kegiatan."><?= htmlspecialchars($form['konten']) ?></textarea></label>
            <label>Foto kegiatan *<input name="gambar" type="file" required accept="image/jpeg,image/png,image/webp"><small>JPEG, PNG, atau WebP. Maksimal 5 MB.</small></label>
            <button class="btn btn-primary" type="submit">Kirim untuk ditinjau <span aria-hidden="true">→</span></button>
          </form>
        <?php endif; ?>
      </div>
    </div>
  </div>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>
