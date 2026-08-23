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

$stmt = $pdo->prepare("SELECT key_value FROM settings WHERE key_name = 'header_visi_misi'");
$stmt->execute();
$header_visi_misi = $stmt->fetchColumn();
$bgStyle = '';
if (!empty($header_visi_misi)) {
    $header_visi_misi = htmlspecialchars((string)$header_visi_misi, ENT_QUOTES, 'UTF-8');
    $bgStyle = 'background: linear-gradient(rgba(0,0,0,0.3), rgba(0,0,0,0.5)), url(\'../../' . $header_visi_misi . '\') center/cover; color: #ffffff;';
}

$forceSolidHeader = true;
include __DIR__ . '/../includes/header.php';
?>

<section class="page-hero" style="<?= $bgStyle ?>">
  <div class="container">
    <div class="breadcrumb">
      <a href="<?= $navPrefix ?>index.php">Beranda</a> <span>/</span> <span>Profile</span> <span>/</span> <strong style="color: #ffffff;">Visi &amp; Misi</strong>
    </div>
    <h1>Visi &amp; Misi Kelurahan</h1>
    <p>Arah kebijakan dan komitmen pelayanan <?= htmlspecialchars(defined('NAMA_KELURAHAN') ? NAMA_KELURAHAN : 'Kelurahan') ?>.</p>
  </div>
</section>

<section class="page-section" style="padding: 48px 0 80px;">
  <div class="container">
    <!-- Section VISI -->
    <div style="background: linear-gradient(135deg, var(--teal-950) 0%, var(--teal-700) 100%); border-radius: 20px; padding: clamp(24px, 4vw, 44px); text-align: center; color: #ffffff; box-shadow: var(--shadow-soft); margin-bottom: 48px; position: relative;">
      <span class="eyebrow" style="background: var(--gold-500); color: var(--teal-950); padding: 6px 16px; border-radius: 50px; margin-bottom: 16px;">
        VISI UTAMA
      </span>
      <h2 style="font-size: clamp(1.2rem, 2.5vw, 1.6rem); font-weight: 700; line-height: 1.6; max-width: 850px; margin: 0 auto; color: #ffffff;">
        "<?= htmlspecialchars($visi) ?>"
      </h2>
    </div>

    <!-- Section MISI -->
    <div style="text-align: center; margin-bottom: 32px;">
      <span class="eyebrow">LANGKAH STRATEGIS</span>
      <h2 style="font-size: clamp(1.6rem, 3vw, 2.2rem); font-weight: 800; color: var(--teal-900); margin: 4px 0 0 0;">Misi Kelurahan</h2>
    </div>

    <?php if(empty($misiArray)): ?>
      <p style="text-align: center; color: var(--ink-soft);">Belum ada data misi.</p>
    <?php else: ?>
      <!-- Grid Misi -->
      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(min(100%, 280px), 1fr)); gap: 20px;">
        <?php foreach($misiArray as $index => $ms): ?>
          <div style="background: var(--white); border: 1px solid var(--line); border-radius: 16px; padding: 24px; display: flex; align-items: flex-start; gap: 16px; box-shadow: var(--shadow-card);">
            
            <!-- Badge Nomor -->
            <div style="width: 42px; height: 42px; background-color: var(--teal-100); color: var(--teal-700); border: 1px solid var(--teal-100); border-radius: 12px; font-weight: 800; font-size: 1.1rem; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
              <?= sprintf('%02d', $index + 1) ?>
            </div>
            
            <!-- Teks Misi -->
            <div style="font-size: 0.95rem; color: var(--ink); line-height: 1.6; font-weight: 500; margin-top: 2px;">
              <?= htmlspecialchars($ms) ?>
            </div>

          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

  </div>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>