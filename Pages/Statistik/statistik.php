<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../config_db.php';

$assetPrefix = '../../assets/';
$navPrefix   = '../';
$activeNav   = 'statistik';
$pageTitle   = 'Statistik Kelurahan';

$pdo = getDB();

// --- Ambil statistik agregat ---
$rows = $pdo->query("SELECT * FROM statistik")->fetchAll();
$stat = [];
foreach ($rows as $r) {
    $val = json_decode($r['key_value'], true);
    $stat[$r['key_name']] = (json_last_error() === JSON_ERROR_NONE && !is_null($val)) ? $val : $r['key_value'];
}

// --- Ambil data per RT dan jenis pekerjaan dari tabel konten ---
try {
    $rowRT = $pdo->query("SELECT key_value FROM konten WHERE key_name='data_per_rt'")->fetch();
    $dataRT = $rowRT ? json_decode($rowRT['key_value'], true) : [];

    $rowPek = $pdo->query("SELECT key_value FROM konten WHERE key_name='jenis_pekerjaan'")->fetch();
    $jenisPekerjaan = $rowPek ? json_decode($rowPek['key_value'], true) : [];
} catch(Exception $e) {
    $dataRT = [];
    $jenisPekerjaan = [];
}

$jumlahPenduduk = (int)($stat['jumlah_penduduk'] ?? 0);
$jumlahKK       = (int)($stat['jumlah_kk'] ?? 0);
$lakiLaki       = (int)($stat['penduduk_per_jenis_kelamin']['laki_laki'] ?? 0);
$perempuan      = (int)($stat['penduduk_per_jenis_kelamin']['perempuan'] ?? 0);
$lingkungan     = $stat['lingkungan'] ?? [];

$totalGender    = max($lakiLaki + $perempuan, 1);
$pctLaki        = round($lakiLaki / $totalGender * 100, 1);
$pctPerempuan   = round($perempuan / $totalGender * 100, 1);

include __DIR__ . '/../includes/header.php';
?>

<!-- =================== HERO =================== -->
<section class="page-hero" style="padding-bottom: 80px; text-align: center;">
  <div class="container">
    <div class="breadcrumb" style="justify-content: center;"><a href="<?= $navPrefix ?>index.php">Beranda</a> / Statistik Kelurahan</div>
    <h1>Potret <?= NAMA_KELURAHAN ?></h1>
    <p>Visualisasi data kependudukan dan wilayah yang informatif. Terakhir diperbarui <?= isset($stat['terakhir_diperbarui']) ? date('d M Y', strtotime($stat['terakhir_diperbarui'])) : '-' ?>.</p>
    
    <!-- 1. COUNTER CARDS (Moved up to Hero) -->
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:20px;margin-top:40px;">
      <?php
        $counters = [
          ['val'=>$jumlahPenduduk, 'label'=>'Total Penduduk', 'icon'=>'fa-users', 'color'=>'#0F3D36'],
          ['val'=>$lakiLaki,        'label'=>'Laki-laki', 'icon'=>'fa-mars', 'color'=>'#A0522D'],
          ['val'=>$perempuan,       'label'=>'Perempuan', 'icon'=>'fa-venus', 'color'=>'#DB2777'],
          ['val'=>$jumlahKK,        'label'=>'Kepala Keluarga', 'icon'=>'fa-home', 'color'=>'#0F3D36'],
          ['val'=>count($lingkungan),'label'=>'Lingkungan', 'icon'=>'fa-layer-group', 'color'=>'#0F3D36'],
          ['val'=>count($dataRT) > 0 ? count($dataRT) : 27, 'label'=>'Jumlah RT', 'icon'=>'fa-map-pin', 'color'=>'#0F3D36'],
        ];
        foreach ($counters as $c):
      ?>
      <div style="background:#fff; padding:20px; border-radius:12px; text-align:center; box-shadow:0 8px 24px rgba(0,0,0,0.1);">
        <div style="font-size:24px; color:<?= $c['color'] ?>; margin-bottom:12px;"><i class="fa-solid <?= $c['icon'] ?>"></i></div>
        <div class="counter-val" data-target="<?= $c['val'] ?>" style="font-size:28px; font-weight:800; color:#1C2622; margin-bottom:4px; font-family:'Sora', sans-serif;">
            0
        </div>
        <div style="font-size:12px; font-weight:700; color:#5B6B65; text-transform:uppercase; letter-spacing:0.05em;">
            <?= $c['label'] ?>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- =================== DASHBOARD =================== -->
<section style="background: #F4F6F5; padding:48px 0 80px;">
  <div class="container">

    <!-- 2. GENDER & PEKERJAAN BARS -->
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(350px,1fr));gap:30px;margin-bottom:60px;">
        
        <!-- Gender -->
        <div style="background:#fff; padding:32px; border-radius:16px; box-shadow:0 4px 12px rgba(0,0,0,0.03);">
            <div class="section-head compact" style="margin-bottom: 24px;">
                <span class="eyebrow">Komposisi Penduduk</span>
                <h3 style="font-size:20px;font-weight:700;">Sebaran Jenis Kelamin</h3>
            </div>
            
            <div style="margin-bottom:24px;">
                <div style="display:flex; justify-content:space-between; margin-bottom:8px; font-weight:600;">
                    <span style="color:#1C2622;">Laki-laki</span>
                    <span style="color:#A0522D;"><?= number_format($lakiLaki,0,',','.') ?> (<?= $pctLaki ?>%)</span>
                </div>
                <div style="background:#F4F6F5; border-radius:8px; height:12px; overflow:hidden;">
                    <div style="width:<?= $pctLaki ?>%; background:#A0522D; height:100%;"></div>
                </div>
            </div>

            <div>
                <div style="display:flex; justify-content:space-between; margin-bottom:8px; font-weight:600;">
                    <span style="color:#1C2622;">Perempuan</span>
                    <span style="color:#DB2777;"><?= number_format($perempuan,0,',','.') ?> (<?= $pctPerempuan ?>%)</span>
                </div>
                <div style="background:#F4F6F5; border-radius:8px; height:12px; overflow:hidden;">
                    <div style="width:<?= $pctPerempuan ?>%; background:#DB2777; height:100%;"></div>
                </div>
            </div>
        </div>

        <!-- Pekerjaan -->
        <div style="background:#fff; padding:32px; border-radius:16px; box-shadow:0 4px 12px rgba(0,0,0,0.03);">
            <div class="section-head compact" style="margin-bottom: 24px;">
                <span class="eyebrow">Mata Pencaharian</span>
                <h3 style="font-size:20px;font-weight:700;">Jenis Pekerjaan Warga</h3>
            </div>
            
            <?php 
            $maxPekerjaan = max(array_column($jenisPekerjaan ?: [['jumlah'=>1]], 'jumlah')) ?: 1;
            foreach ($jenisPekerjaan as $pek): 
                $barW = round($pek['jumlah'] / $maxPekerjaan * 100);
            ?>
            <div style="margin-bottom:16px;">
                <div style="display:flex; justify-content:space-between; margin-bottom:6px; font-weight:600; font-size:14px;">
                    <span style="color:#1C2622;"><?= htmlspecialchars($pek['nama']) ?></span>
                    <span style="color:#0F3D36;"><?= number_format($pek['jumlah'],0,',','.') ?></span>
                </div>
                <div style="background:#F4F6F5; border-radius:6px; height:8px; overflow:hidden;">
                    <div style="width:<?= $barW ?>%; background:linear-gradient(90deg,#059669,#065F46); height:100%;"></div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

    </div>

    <!-- 3. DATA PER RT -->
    <?php if (!empty($dataRT)): ?>
    <div style="margin-bottom:48px;">
        <div class="section-head" style="margin-bottom:32px;">
            <span class="eyebrow">Sebaran Wilayah</span>
            <h2>Data Penduduk Per RT</h2>
            <p>Rincian data kependudukan berdasarkan Lingkungan dan RT.</p>
        </div>

        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(320px,1fr));gap:24px;">
        <?php foreach ($dataRT as $idx => $rt):
            $maxJumlahPek = max(array_column($rt['pekerjaan'] ?: [['jumlah'=>1]], 'jumlah')) ?: 1;
            $pctL = round($rt['laki'] / max($rt['total'],1) * 100);
            $pctP = round($rt['perempuan'] / max($rt['total'],1) * 100);
        ?>
        <div style="background:#fff; border-radius:16px; overflow:hidden; box-shadow:0 4px 12px rgba(0,0,0,0.03);">
            
            <!-- RT Header -->
            <div style="background:#0F3D36; padding:20px; color:#fff; display:flex; justify-content:space-between; align-items:center;">
                <div>
                    <div style="font-size:12px; font-weight:700; color:rgba(255,255,255,0.8); text-transform:uppercase;">LK <?= htmlspecialchars($rt['lk']) ?></div>
                    <div style="font-size:24px; font-weight:800; font-family:'Sora', sans-serif;">RT <?= htmlspecialchars($rt['rt']) ?></div>
                </div>
                <div style="text-align:right;">
                    <div style="font-size:12px; color:rgba(255,255,255,0.8);">Total Jiwa</div>
                    <div style="font-size:24px; font-weight:800;"><?= number_format($rt['total'],0,',','.') ?></div>
                </div>
            </div>

            <!-- Gender mini bar -->
            <div style="padding:20px; border-bottom:1px solid #F4F6F5;">
                <div style="display:flex; gap:8px; margin-bottom:12px;">
                    <div style="flex:1; background:#F4F6F5; border-radius:4px; height:8px; overflow:hidden;">
                        <div style="width:<?= $pctL ?>%; background:#A0522D; height:100%;"></div>
                    </div>
                    <div style="flex:1; background:#F4F6F5; border-radius:4px; height:8px; overflow:hidden;">
                        <div style="width:<?= $pctP ?>%; background:#DB2777; height:100%;"></div>
                    </div>
                </div>
                <div style="display:flex; justify-content:space-between; font-size:13px; font-weight:600;">
                    <span style="color:#A0522D;">L: <?= number_format($rt['laki'],0,',','.') ?></span>
                    <span style="color:#DB2777;">P: <?= number_format($rt['perempuan'],0,',','.') ?></span>
                </div>
            </div>

            <!-- Pekerjaan -->
            <div style="padding:20px;">
                <div style="font-size:12px; font-weight:700; text-transform:uppercase; color:#5B6B65; margin-bottom:16px;">Top Pekerjaan</div>
                <?php foreach ($rt['pekerjaan'] as $pek):
                    $barW = round($pek['jumlah'] / $maxJumlahPek * 100);
                ?>
                <div style="margin-bottom:12px;">
                    <div style="display:flex; justify-content:space-between; font-size:13px; font-weight:600; margin-bottom:6px;">
                        <span style="color:#1C2622;"><?= htmlspecialchars($pek['nama']) ?></span>
                        <span style="color:#0F3D36;"><?= number_format($pek['jumlah'],0,',','.') ?></span>
                    </div>
                    <div style="background:#F4F6F5; border-radius:4px; height:6px; overflow:hidden;">
                        <div style="width:<?= $barW ?>%; background:linear-gradient(90deg,#059669,#065F46); height:100%;"></div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
        </div>
        <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

  </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const counters = document.querySelectorAll('.counter-val[data-target]');
    const numObserver = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (!entry.isIntersecting) return;
        const el = entry.target;
        const target = parseInt(el.dataset.target);
        if(isNaN(target)) return;
        const duration = 1800;
        const step = target / (duration / 16);
        let current = 0;
        const timer = setInterval(() => {
          current += step;
          if (current >= target) { current = target; clearInterval(timer); }
          el.textContent = Math.floor(current).toLocaleString('id-ID');
        }, 16);
        numObserver.unobserve(el);
      });
    }, { threshold: 0.3 });
    counters.forEach(c => numObserver.observe(c));
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
