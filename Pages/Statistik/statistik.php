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

<style>
  .stat-dashboard {
    background: #f8fafc;
    padding: 48px 0 80px;
  }

  .stat-grid-counter {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
    gap: 20px;
    margin-bottom: 48px;
  }

  .stat-grid-two {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
    gap: 30px;
    margin-bottom: 48px;
  }

  .stat-card-rt {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
    gap: 24px;
  }

  .stat-counter-card,
  .stat-panel,
  .rt-card {
    box-sizing: border-box;
  }

  @media (max-width: 900px) {
    .stat-grid-two {
      grid-template-columns: 1fr;
    }

    .stat-dashboard {
      padding-top: 40px;
      padding-bottom: 56px;
    }
  }

  @media (max-width: 560px) {
    .stat-grid-counter {
      grid-template-columns: repeat(2, minmax(0, 1fr));
      gap: 14px;
    }

    .stat-counter-card {
      padding: 16px 12px !important;
    }

    .counter-val {
      font-size: 22px !important;
    }

    .stat-panel {
      padding: 20px !important;
    }

    .stat-card-rt {
      grid-template-columns: 1fr;
      gap: 16px;
    }

    .rt-card {
      overflow: hidden;
    }

    .rt-card .rt-header {
      padding: 16px !important;
      flex-direction: column;
      align-items: flex-start;
      gap: 8px;
    }

    .rt-card .rt-body,
    .rt-card .rt-footer {
      padding: 16px !important;
    }
  }

  @media (max-width: 420px) {
    .stat-grid-counter {
      grid-template-columns: 1fr;
    }

    .page-hero .breadcrumb {
      font-size: 0.75rem;
    }
  }
</style>

<?php
$stmt = $pdo->prepare("SELECT key_value FROM settings WHERE key_name = 'header_statistik'");
$stmt->execute();
$header_statistik = $stmt->fetchColumn();
$bgStyle = '';
if (!empty($header_statistik)) {
    $header_statistik = htmlspecialchars((string)$header_statistik, ENT_QUOTES, 'UTF-8');
    $bgStyle = 'background: linear-gradient(rgba(0,0,0,0.3), rgba(0,0,0,0.5)), url(\'../../' . $header_statistik . '\') center/cover; color: #ffffff;';
}
?>

<section class="page-hero" style="<?= $bgStyle ?>">
  <div class="container">
    <div class="breadcrumb">
      <a href="<?= $navPrefix ?>index.php">Beranda</a> <span>/</span> <strong style="color: #ffffff;">Statistik Kelurahan</strong>
    </div>
    <h1>Potret <?= htmlspecialchars(defined('NAMA_KELURAHAN') ? NAMA_KELURAHAN : 'Kelurahan') ?></h1>
    <p>
      Visualisasi data kependudukan dan wilayah yang informatif. Terakhir diperbarui <?= isset($stat['terakhir_diperbarui']) ? date('d M Y', strtotime($stat['terakhir_diperbarui'])) : '-' ?>.
    </p>
  </div>
</section>

<section class="stat-dashboard">
  <div class="container">
    <div class="stat-grid-counter">
      <?php
        $counters = [
          ['val'=>$jumlahPenduduk, 'label'=>'Total Penduduk', 'icon'=>'fa-users', 'color'=>'#047857'],
          ['val'=>$lakiLaki,        'label'=>'Laki-laki', 'icon'=>'fa-mars', 'color'=>'#0284c7'],
          ['val'=>$perempuan,      'label'=>'Perempuan', 'icon'=>'fa-venus', 'color'=>'#db2777'],
          ['val'=>$jumlahKK,        'label'=>'Kepala Keluarga', 'icon'=>'fa-home', 'color'=>'#047857'],
          ['val'=>count($lingkungan),'label'=>'Lingkungan', 'icon'=>'fa-layer-group', 'color'=>'#047857'],
          ['val'=>count($dataRT) > 0 ? count($dataRT) : 27, 'label'=>'Jumlah RT', 'icon'=>'fa-map-pin', 'color'=>'#047857'],
        ];
        foreach ($counters as $c):
      ?>
      <div class="stat-counter-card" style="background:#fff; padding:20px; border-radius:12px; text-align:center; box-shadow:0 4px 12px rgba(0,0,0,0.03); border: 1px solid #e2e8f0;">
        <div style="font-size:24px; color:<?= $c['color'] ?>; margin-bottom:10px;"><i class="fa-solid <?= $c['icon'] ?>"></i></div>
        <div class="counter-val" data-target="<?= $c['val'] ?>" style="font-size:26px; font-weight:800; color:#0f172a; margin-bottom:4px; font-family:'Sora', sans-serif;">
            0
        </div>
        <div style="font-size:11px; font-weight:700; color:#64748b; text-transform:uppercase; letter-spacing:0.05em;">
            <?= $c['label'] ?>
        </div>
      </div>
      <?php endforeach; ?>
    </div>

    <div class="stat-grid-two">
        <div class="stat-panel" style="background:#fff; padding:32px; border-radius:16px; box-shadow:0 4px 12px rgba(0,0,0,0.03); border:1px solid #e2e8f0;">
            <div class="section-head compact" style="margin-bottom: 24px;">
                <span style="font-size: 0.85rem; font-weight: 700; color: #059669; text-transform: uppercase; letter-spacing: 1px;">Komposisi Penduduk</span>
                <h3 style="font-size:20px; font-weight:800; color:#0f172a; margin-top:4px;">Sebaran Jenis Kelamin</h3>
            </div>
            
            <div style="margin-bottom:24px;">
                <div style="display:flex; justify-content:space-between; margin-bottom:8px; font-weight:600; gap:12px;">
                    <span style="color:#0f172a;">Laki-laki</span>
                    <span style="color:#0284c7; font-weight:700; white-space:nowrap;"><?= number_format($lakiLaki,0,',','.') ?> (<?= $pctLaki ?>%)</span>
                </div>
                <div style="background:#f1f5f9; border-radius:8px; height:12px; overflow:hidden;">
                    <div style="width:<?= $pctLaki ?>%; background:#0284c7; height:100%; border-radius:8px;"></div>
                </div>
            </div>

            <div>
                <div style="display:flex; justify-content:space-between; margin-bottom:8px; font-weight:600; gap:12px;">
                    <span style="color:#0f172a;">Perempuan</span>
                    <span style="color:#db2777; font-weight:700; white-space:nowrap;"><?= number_format($perempuan,0,',','.') ?> (<?= $pctPerempuan ?>%)</span>
                </div>
                <div style="background:#f1f5f9; border-radius:8px; height:12px; overflow:hidden;">
                    <div style="width:<?= $pctPerempuan ?>%; background:#db2777; height:100%; border-radius:8px;"></div>
                </div>
            </div>
        </div>

        <div class="stat-panel" style="background:#fff; padding:32px; border-radius:16px; box-shadow:0 4px 12px rgba(0,0,0,0.03); border:1px solid #e2e8f0;">
            <div class="section-head compact" style="margin-bottom: 24px;">
                <span style="font-size: 0.85rem; font-weight: 700; color: #059669; text-transform: uppercase; letter-spacing: 1px;">Pekerjaan</span>
                <h3 style="font-size:20px; font-weight:800; color:#0f172a; margin-top:4px;">Jenis Pekerjaan</h3>
            </div>
            <?php
                $maxPekerjaan = 1;
                foreach ($jenisPekerjaan as $pek) {
                    $maxPekerjaan = max($maxPekerjaan, (int)$pek['jumlah']);
                }
                if ($maxPekerjaan < 1) $maxPekerjaan = 1;
            ?>
            <?php foreach ($jenisPekerjaan as $pek):
                $barW = round($pek['jumlah'] / $maxPekerjaan * 100);
            ?>
            <div style="margin-bottom:16px;">
                <div style="display:flex; justify-content:space-between; margin-bottom:6px; font-weight:600; font-size:14px; gap:12px;">
                    <span style="color:#334155; word-break:break-word;"><?= htmlspecialchars($pek['nama']) ?></span>
                    <span style="color:#059669; font-weight:700; white-space:nowrap;"><?= number_format($pek['jumlah'],0,',','.') ?></span>
                </div>
                <div style="background:#f1f5f9; border-radius:6px; height:8px; overflow:hidden;">
                    <div style="width:<?= $barW ?>%; background:linear-gradient(90deg,#059669,#10b981); height:100%;"></div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <?php if (!empty($dataRT)): ?>
    <div style="margin-top: 56px;">
        <div style="margin-bottom:32px; text-align:center;">
            <span style="font-size: 0.85rem; font-weight: 700; color: #059669; text-transform: uppercase; letter-spacing: 1px;">Sebaran Wilayah</span>
            <h2 style="font-size: clamp(1.6rem, 3vw, 2rem); font-weight: 800; color: #0f172a; margin: 4px 0;">Data Penduduk Per RT</h2>
            <p style="color: #64748b; margin: 0 auto; max-width: 620px;">Rincian data kependudukan berdasarkan Lingkungan dan RT.</p>
        </div>

        <div class="stat-card-rt">
        <?php foreach ($dataRT as $idx => $rt):
            $maxJumlahPek = max(array_column($rt['pekerjaan'] ?: [['jumlah'=>1]], 'jumlah')) ?: 1;
            $pctL = round($rt['laki'] / max($rt['total'],1) * 100);
            $pctP = round($rt['perempuan'] / max($rt['total'],1) * 100);
        ?>
        <div class="rt-card" style="background:#fff; border-radius:16px; overflow:hidden; box-shadow:0 4px 12px rgba(0,0,0,0.03); border:1px solid #e2e8f0;">
            <div class="rt-header" style="background:#064e3b; padding:20px; color:#fff; display:flex; justify-content:space-between; align-items:center; gap:12px;">
                <div>
                    <div style="font-size:11px; font-weight:700; color:#a7f3d0; text-transform:uppercase;">LK <?= htmlspecialchars($rt['lk']) ?></div>
                    <div style="font-size:22px; font-weight:800; font-family:'Sora', sans-serif;">RT <?= htmlspecialchars($rt['rt']) ?></div>
                </div>
                <div style="text-align:right;">
                    <div style="font-size:11px; color:#a7f3d0;">Total Jiwa</div>
                    <div style="font-size:22px; font-weight:800;"><?= number_format($rt['total'],0,',','.') ?></div>
                </div>
            </div>

            <div class="rt-body" style="padding:20px; border-bottom:1px solid #f1f5f9;">
                <div style="display:flex; gap:8px; margin-bottom:12px;">
                    <div style="flex:1; background:#f1f5f9; border-radius:4px; height:8px; overflow:hidden;">
                        <div style="width:<?= $pctL ?>%; background:#0284c7; height:100%;"></div>
                    </div>
                    <div style="flex:1; background:#f1f5f9; border-radius:4px; height:8px; overflow:hidden;">
                        <div style="width:<?= $pctP ?>%; background:#db2777; height:100%;"></div>
                    </div>
                </div>
                <div style="display:flex; justify-content:space-between; font-size:13px; font-weight:600; gap:12px;">
                    <span style="color:#0284c7; white-space:nowrap;">L: <?= number_format($rt['laki'],0,',','.') ?></span>
                    <span style="color:#db2777; white-space:nowrap;">P: <?= number_format($rt['perempuan'],0,',','.') ?></span>
                </div>
            </div>

            <div class="rt-footer" style="padding:20px;">
                <div style="font-size:11px; font-weight:700; text-transform:uppercase; color:#64748b; margin-bottom:14px;">Top Pekerjaan</div>
                <?php foreach ($rt['pekerjaan'] as $pek):
                    $barW = round($pek['jumlah'] / $maxJumlahPek * 100);
                ?>
                <div style="margin-bottom:10px;">
                    <div style="display:flex; justify-content:space-between; font-size:12px; font-weight:600; margin-bottom:4px; gap:12px;">
                        <span style="color:#334155; word-break:break-word;"><?= htmlspecialchars($pek['nama']) ?></span>
                        <span style="color:#059669; white-space:nowrap;"><?= number_format($pek['jumlah'],0,',','.') ?></span>
                    </div>
                    <div style="background:#f1f5f9; border-radius:4px; height:6px; overflow:hidden;">
                        <div style="width:<?= $barW ?>%; background:linear-gradient(90deg,#059669,#10b981); height:100%;"></div>
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
        if (isNaN(target)) return;
        const duration = 1600;
        const step = target / (duration / 16);
        let current = 0;
        const timer = setInterval(() => {
          current += step;
          if (current >= target) {
            current = target;
            clearInterval(timer);
          }
          el.textContent = Math.floor(current).toLocaleString('id-ID');
        }, 16);
        numObserver.unobserve(el);
      });
    }, { threshold: 0.2 });

    counters.forEach(c => numObserver.observe(c));
  });
</script>
<?php include __DIR__ . '/../includes/footer.php'; ?>