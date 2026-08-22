<?php
$currentPage = basename($_SERVER['PHP_SELF']);
// Determine if a submenu should be open
$isProfileOpen = in_array($currentPage, ['profile.php', 'aparatur.php', 'tim-kkn.php']);
?>
<aside class="admin-sidebar">
  <div class="admin-brand">
    <div class="brand-avatar">
      <img src="../assets/img/logo-kkn.png" onerror="this.style.display='none'">
    </div>
    <div class="brand-text">
      <small>ADMIN KONTEN</small>
      <strong><?= strtoupper(NAMA_KELURAHAN) ?></strong>
    </div>
  </div>

  <nav class="admin-nav">
    <!-- Main Dashboard -->
    <a href="index.php" class="nav-item <?= $currentPage === 'index.php' ? 'active' : '' ?>">
      <i class="fa-solid fa-border-all"></i> Pusat Kontrol
    </a>

    <div class="nav-divider">EDITING KONTEN PUBLIK</div>

    <!-- Media (Beranda) -->
    <a href="media.php" class="nav-item <?= $currentPage === 'media.php' ? 'active' : '' ?>">
      <i class="fa-solid fa-desktop"></i> Beranda
    </a>

    <!-- Profil (Accordion) -->
    <div class="nav-accordion <?= $isProfileOpen ? 'open' : '' ?>">
      <div class="nav-accordion-btn">
        <div style="display:flex; align-items:center; gap: 12px;">
          <i class="fa-regular fa-clock"></i> Profil
        </div>
        <i class="fa-solid fa-chevron-down arrow" style="margin-left: auto;"></i>
      </div>
      <div class="nav-accordion-content" style="<?= $isProfileOpen ? 'display:block;' : 'display:none;' ?>">
        <a href="profile.php" class="sub-nav-item <?= $currentPage === 'profile.php' ? 'active' : '' ?>">Sejarah & Visi Misi</a>
        <a href="aparatur.php" class="sub-nav-item <?= $currentPage === 'aparatur.php' ? 'active' : '' ?>">Pengurus Organisasi</a>
        <a href="tim-kkn.php" class="sub-nav-item <?= $currentPage === 'tim-kkn.php' ? 'active' : '' ?>">Tim KKN</a>
      </div>
    </div>

    <!-- Peta -->
    <a href="peta.php" class="nav-item <?= $currentPage === 'peta.php' ? 'active' : '' ?>">
      <i class="fa-solid fa-map-location-dot"></i> Peta Kelurahan
    </a>

    <!-- Statistik -->
    <a href="statistik.php" class="nav-item <?= $currentPage === 'statistik.php' ? 'active' : '' ?>">
      <i class="fa-solid fa-chart-pie"></i> Statistik Kelurahan
    </a>

    <?php
    $pdoSidebar = getDB();
    $pendingCount = 0;
    try {
        $pendingCount = $pdoSidebar->query("SELECT COUNT(*) FROM berita WHERE status = 'pending'")->fetchColumn();
    } catch(Exception $e) {}
    $isBeritaOpen = in_array($currentPage, ['berita.php', 'pengajuan-berita.php']);
    ?>
    <div class="nav-accordion <?= $isBeritaOpen ? 'open' : '' ?>">
      <div class="nav-accordion-btn">
        <div style="display:flex; align-items:center; gap: 12px; width:100%;">
          <i class="fa-regular fa-newspaper"></i> Berita
          <?php if($pendingCount > 0): ?>
            <span style="background:var(--primary); color:white; font-size:10px; padding:2px 6px; border-radius:10px; font-weight:bold; margin-left:auto; margin-right:8px;"><?= $pendingCount ?></span>
          <?php endif; ?>
        </div>
        <i class="fa-solid fa-chevron-down arrow"></i>
      </div>
      <div class="nav-accordion-content" style="<?= $isBeritaOpen ? 'display:block;' : 'display:none;' ?>">
        <a href="berita.php" class="sub-nav-item <?= $currentPage === 'berita.php' ? 'active' : '' ?>">Semua Berita</a>
        <a href="pengajuan-berita.php" class="sub-nav-item <?= $currentPage === 'pengajuan-berita.php' ? 'active' : '' ?>" style="display:flex; justify-content:space-between; align-items:center;">
            Tinjau Pengajuan
            <?php if($pendingCount > 0): ?>
                <span style="background:var(--primary); color:white; font-size:10px; padding:2px 6px; border-radius:10px; font-weight:bold;"><?= $pendingCount ?> Baru</span>
            <?php endif; ?>
        </a>
      </div>
    </div>

    <!-- Galeri -->
    <a href="galeri.php" class="nav-item <?= $currentPage === 'galeri.php' ? 'active' : '' ?>">
      <i class="fa-solid fa-camera-retro"></i> Galeri
    </a>

  </nav>

  <div class="admin-sidebar-footer">
    <a href="../Pages/Peta/peta.php" target="_blank" class="btn btn-outline" style="width:100%; margin-bottom:12px; font-size:12px;"><i class="fa-solid fa-arrow-up-right-from-square"></i> Lihat Situs</a>
    <a href="logout.php" class="logout-link"><i class="fa-solid fa-arrow-right-from-bracket"></i> KELUAR SISTEM</a>
  </div>
</aside>

<script>
// Accordion Logic
document.addEventListener('DOMContentLoaded', () => {
  const accordionBtns = document.querySelectorAll('.nav-accordion-btn');
  accordionBtns.forEach(btn => {
    btn.addEventListener('click', () => {
      const parent = btn.parentElement;
      const content = parent.querySelector('.nav-accordion-content');
      if (parent.classList.contains('open')) {
        parent.classList.remove('open');
        content.style.display = 'none';
      } else {
        parent.classList.add('open');
        content.style.display = 'block';
      }
    });
  });
});
</script>
