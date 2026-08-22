<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../config_db.php';

$assetPrefix = '../../assets/';
$navPrefix = '../';
$activeNav = 'profile';
$pageTitle = 'Visi & Misi';

// Default Visi & Misi
$defaultVisi = "Terwujudnya kelurahan di wilayah pesisir yang Mandiri, Sejahtera, Bersih, dan Berdaya Saing Berbasis Ekonomi Biru yang Berkelanjutan.";

$defaultMisi = [
    "Mengembangkan sistem pengelolaan ekonomi warga pesisir yang terintegrasi dari hulu ke hilir melalui penguatan peran koperasi.",
    "Membangun infrastruktur pendukung desa nelayan yang tertata rapi, sehat, dan nyaman bagi kehidupan warga.",
    "Meningkatkan kapasitas sumber daya manusia masyarakat pesisir melalui pelatihan keterampilan serta pengolahan hasil laut.",
    "Meningkatkan produktivitas masyarakat pesisir khususnya nelayan melalui penggunaan sarana dan teknologi ramah lingkungan."
];

$pdo = getDB();

try {
    $stmt = $pdo->query("SELECT * FROM konten WHERE key_name IN ('visi_teks', 'misi_teks')");
    $data = [];
    while ($row = $stmt->fetch()) {
        $data[$row['key_name']] = $row['key_value'];
    }

    $visi = !empty($data['visi_teks']) ? $data['visi_teks'] : $defaultVisi;
    
    if (!empty($data['misi_teks'])) {
        $misiDecoded = json_decode($data['misi_teks'], true);
        $misiArray = is_array($misiDecoded) && !empty($misiDecoded) ? $misiDecoded : $defaultMisi;
    } else {
        $misiArray = $defaultMisi;
    }
} catch(Exception $e) {
    $visi = $defaultVisi;
    $misiArray = $defaultMisi;
}

include __DIR__ . '/../includes/header.php';
?>

<!-- Container Utama dengan Jarak Atas Aman dari Navbar -->
<div style="padding-top: 120px; padding-bottom: 60px; background-color: #f8fafc; min-height: 100vh; font-family: sans-serif;">
  <div style="max-width: 1100px; margin: 0 auto; padding: 0 20px;">
    
    <!-- Breadcrumb & Header Title -->
    <div style="text-align: center; margin-bottom: 40px;">
      <div style="font-size: 0.9rem; color: #64748b; margin-bottom: 8px;">
        <a href="<?= $navPrefix ?>index.php" style="color: #059669; text-decoration: none; font-weight: 600;">Beranda</a> / Profile / Visi &amp; Misi
      </div>
      <h1 style="font-size: 2.5rem; font-weight: 800; color: #0f172a; margin: 0 0 10px 0;">Visi &amp; Misi</h1>
      <p style="color: #475569; font-size: 1rem; max-width: 600px; margin: 0 auto;">

    </div>

    <!-- Section VISI (Banner Hijau Gelap dengan Teks Putih Kontras) -->
    <div style="background: linear-gradient(135deg, #064e3b 0%, #047857 100%); border-radius: 20px; padding: 40px 30px; text-align: center; color: #ffffff; box-shadow: 0 10px 25px rgba(6, 78, 59, 0.2); margin-bottom: 48px; position: relative;">
      <span style="display: inline-block; background: #fef08a; color: #854d0e; font-size: 0.8rem; font-weight: 700; padding: 6px 16px; border-radius: 50px; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 20px;">
        VISI UTAMA
      </span>
      <h2 style="font-size: 1.5rem; font-weight: 700; line-height: 1.6; max-width: 850px; margin: 0 auto; color: #ffffff;">
        "<?= htmlspecialchars($visi) ?>"
      </h2>
    </div>

    <!-- Section MISI -->
    <div style="text-align: center; margin-bottom: 32px;">
      <span style="color: #d97706; font-weight: 700; font-size: 0.85rem; letter-spacing: 1.5px; text-transform: uppercase;">LANGKAH STRATEGIS</span>
      <h2 style="font-size: 2rem; font-weight: 800; color: #0f172a; margin: 6px 0 0 0;">Misi Kelurahan</h2>
    </div>

    <?php if(empty($misiArray)): ?>
      <p style="text-align: center; color: #64748b;">Belum ada data misi.</p>
    <?php else: ?>
      <!-- Grid Misi -->
      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px;">
        <?php foreach($misiArray as $index => $ms): ?>
          <div style="background: 064e3b; border: 1px solid #e2e8f0; border-radius: 16px; padding: 24px; display: flex; align-items: flex-start; gap: 16px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);">
            
            <!-- Badge Nomor -->
            <div style="width: 42px; height: 42px; background-color: #ecfdf5; color: #059669; border: 1px solid #a7f3d0; border-radius: 12px; font-weight: 800; font-size: 1.1rem; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
              <?= sprintf('%02d', $index + 1) ?>
            </div>
            
            <!-- Teks Misi -->
            <div style="font-size: 0.95rem; color: #334155; line-height: 1.6; font-weight: 500; margin-top: 2px;">
              <?= htmlspecialchars($ms) ?>
            </div>

          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>