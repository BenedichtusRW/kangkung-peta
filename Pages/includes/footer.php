<footer class="site-footer">
  <div class="container footer-grid">
    <div>
      <h4><?= NAMA_KELURAHAN ?></h4>
      <p>Melayani dengan sepenuh hati untuk masyarakat yang lebih baik dan sejahtera.</p>
      <div class="social-links">
        <a href="#" title="Facebook">FB</a>
        <a href="#" title="Instagram">IG</a>
        <a href="#" title="YouTube">YT</a>
      </div>
      
      <div class="kkn-attribution" style="margin-top: 24px;">
        <span style="font-size: 11px; color: rgba(255,255,255,0.6); text-transform: uppercase; letter-spacing: 0.05em; display: block; margin-bottom: 8px;">Dipersembahkan Oleh</span>
        <div style="display: flex; gap: 12px; align-items: center;">
          <img src="<?= $assetPrefix ?>img/logo-uin.png" alt="Logo UIN" style="height: 48px; width: 48px; object-fit: contain; background: #fff; border-radius: 50%; padding: 4px; filter: drop-shadow(0 2px 4px rgba(0,0,0,0.2));" onerror="this.style.display='none'">
          <img src="<?= $assetPrefix ?>img/logo-kkn.png" alt="Logo KKN 31" style="height: 48px; width: 48px; object-fit: contain; background: #fff; border-radius: 50%; padding: 2px; filter: drop-shadow(0 2px 4px rgba(0,0,0,0.2));" onerror="this.style.display='none'">
        </div>
      </div>
    </div>

    <div>
      <h5>Kontak Kami</h5>
      <ul class="footer-list">
        <li><?= ALAMAT_KANTOR ?></li>
        <li><?= KONTAK_TELEPON ?></li>
        <li><?= KONTAK_EMAIL ?></li>
        <li><?= JAM_LAYANAN ?></li>
      </ul>
    </div>

    <div>
      <h5>Link Cepat</h5>
      <ul class="footer-list">
        <li><a href="<?= $navPrefix ?>index.php">Beranda</a></li>
        <li><a href="<?= $navPrefix ?>VisiMisi/visi-misi.php">Visi &amp; Misi</a></li>
        <li><a href="<?= $navPrefix ?>Peta/peta.php">Peta Kelurahan</a></li>
        <li><a href="<?= $navPrefix ?>Statistik/statistik.php">Statistik Kelurahan</a></li>
        <li><a href="<?= $navPrefix ?>Berita/berita.php">Berita</a></li>
        <li><a href="<?= $navPrefix ?>Galeri/galeri.php">Galeri</a></li>
      </ul>
    </div>
  </div>
  <div class="footer-bottom">
    &copy; <?= date('Y') ?> <?= NAMA_KELURAHAN ?>. All rights reserved.
  </div>
</footer>

<?php include __DIR__ . '/chatbot.php'; ?>

<script>
  const navToggle = document.getElementById('navToggle');
  const mainNav = document.getElementById('mainNav');
  const siteHeader = document.querySelector('.site-header');

  if (siteHeader) {
    const handleScroll = () => {
      if (window.scrollY > 20) {
        siteHeader.classList.add('scrolled');
      } else {
        siteHeader.classList.remove('scrolled');
      }
    };
    window.addEventListener('scroll', handleScroll, {passive: true});
    handleScroll();
  }

  if (navToggle && mainNav) {
    navToggle.addEventListener('click', () => {
      const isOpen = mainNav.classList.toggle('open');
      navToggle.setAttribute('aria-expanded', String(isOpen));
    });
  }
  document.querySelectorAll('.nav-dropdown-btn').forEach((button) => {
    button.addEventListener('click', () => {
      const dropdown = button.closest('.nav-dropdown');
      if (window.matchMedia('(max-width: 1100px)').matches) {
        dropdown.classList.toggle('open');
        button.setAttribute('aria-expanded', String(dropdown.classList.contains('open')));
      }
    });
  });
</script>
</body>
</html>
