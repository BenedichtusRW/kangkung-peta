<footer class="site-footer">

    <div
        class="container footer-grid"
        style="
            display: grid;
            grid-template-columns: minmax(0, 1.4fr) minmax(0, 1fr) minmax(150px, 0.7fr);
            gap: 50px;
            width: 100%;
            max-width: 1180px;
            margin: 0 auto;
            box-sizing: border-box;
            padding-left: 24px;
            padding-right: 24px;
        "
    >

        <!-- =====================================================
             KOLOM 1 — IDENTITAS KELURAHAN
             ===================================================== -->

        <div style="min-width: 0;">

            <h4 style="
                margin: 0 0 12px;
                color: #ffffff;
                font-size: 16px;
                font-weight: 800;
            ">
                <?= htmlspecialchars(NAMA_KELURAHAN) ?>
            </h4>

            <p style="
                margin: 0;
                max-width: 430px;
                color: rgba(255,255,255,0.78);
                font-size: 13px;
                line-height: 1.7;
            ">
                Melayani dengan sepenuh hati untuk masyarakat
                yang lebih baik dan sejahtera.
            </p>


            <!-- Social Media -->

            <div
                class="social-links"
                style="
                    display: flex;
                    gap: 8px;
                    margin-top: 16px;
                    flex-wrap: wrap;
                "
            >

                <a href="#" title="Facebook">FB</a>
                <a href="#" title="Instagram">IG</a>
                <a href="#" title="YouTube">YT</a>

            </div>


            <!-- Logo KKN -->

            <div
                class="kkn-attribution"
                style="
                    margin-top: 24px;
                "
            >

                <span style="
                    display: block;
                    margin-bottom: 9px;
                    color: rgba(255,255,255,0.6);
                    font-size: 10px;
                    font-weight: 600;
                    letter-spacing: 0.08em;
                    text-transform: uppercase;
                ">
                    Dipersembahkan Oleh
                </span>


                <div style="
                    display: flex;
                    align-items: center;
                    gap: 10px;
                    flex-wrap: wrap;
                ">

                    <!-- Logo UIN -->

                    <img
                        src="<?= htmlspecialchars($assetPrefix) ?>img/logo-uin.png"
                        alt="Logo UIN Raden Intan Lampung"
                        style="
                            display: block;
                            width: 48px;
                            height: 48px;
                            object-fit: contain;
                            background: #ffffff;
                            border-radius: 50%;
                            padding: 4px;
                            box-sizing: border-box;
                            filter: drop-shadow(0 2px 4px rgba(0,0,0,0.2));
                        "
                        onerror="this.style.display='none'"
                    >


                    <!-- Logo KKN -->

                    <img
                        src="<?= htmlspecialchars($assetPrefix) ?>img/logo-kkn.png"
                        alt="Logo KKN"
                        style="
                            display: block;
                            width: 48px;
                            height: 48px;
                            object-fit: contain;
                            background: #ffffff;
                            border-radius: 50%;
                            padding: 3px;
                            box-sizing: border-box;
                            filter: drop-shadow(0 2px 4px rgba(0,0,0,0.2));
                        "
                        onerror="this.style.display='none'"
                    >

                </div>

            </div>

        </div>


        <!-- =====================================================
             KOLOM 2 — KONTAK
             ===================================================== -->

        <div style="min-width: 0;">

            <h5 style="
                margin: 0 0 14px;
                color: #f59e0b;
                font-size: 13px;
                font-weight: 800;
                letter-spacing: 0.04em;
                text-transform: uppercase;
            ">
                Kontak Kami
            </h5>


            <ul
                class="footer-list"
                style="
                    margin: 0;
                    padding: 0;
                    list-style: none;
                "
            >

                <li style="
                    margin-bottom: 10px;
                    color: rgba(255,255,255,0.82);
                    font-size: 12px;
                    line-height: 1.6;
                    overflow-wrap: anywhere;
                    word-break: normal;
                ">
                    <?= htmlspecialchars(ALAMAT_KANTOR) ?>
                </li>

                <li style="
                    margin-bottom: 10px;
                    color: rgba(255,255,255,0.82);
                    font-size: 12px;
                    line-height: 1.6;
                ">
                    <?= htmlspecialchars(KONTAK_TELEPON) ?>
                </li>

                <li style="
                    margin-bottom: 10px;
                    color: rgba(255,255,255,0.82);
                    font-size: 12px;
                    line-height: 1.6;
                    overflow-wrap: anywhere;
                ">
                    <?= htmlspecialchars(KONTAK_EMAIL) ?>
                </li>

                <li style="
                    color: rgba(255,255,255,0.82);
                    font-size: 12px;
                    line-height: 1.6;
                ">
                    <?= htmlspecialchars(JAM_LAYANAN) ?>
                </li>

            </ul>

        </div>


        <!-- =====================================================
             KOLOM 3 — LINK CEPAT
             ===================================================== -->

        <div style="
            min-width: 0;
        ">

            <h5 style="
                margin: 0 0 14px;
                color: #f59e0b;
                font-size: 13px;
                font-weight: 800;
                letter-spacing: 0.04em;
                text-transform: uppercase;
            ">
                Link Cepat
            </h5>


            <ul
                class="footer-list"
                style="
                    margin: 0;
                    padding: 0;
                    list-style: none;
                "
            >

                <li>
                    <a href="<?= htmlspecialchars($navPrefix) ?>index.php">
                        Beranda
                    </a>
                </li>

                <li>
                    <a href="<?= htmlspecialchars($navPrefix) ?>VisiMisi/visi-misi.php">
                        Visi &amp; Misi
                    </a>
                </li>

                <li>
                    <a href="<?= htmlspecialchars($navPrefix) ?>Peta/peta.php">
                        Peta Kelurahan
                    </a>
                </li>

                <li>
                    <a href="<?= htmlspecialchars($navPrefix) ?>Statistik/statistik.php">
                        Statistik Kelurahan
                    </a>
                </li>

                <li>
                    <a href="<?= htmlspecialchars($navPrefix) ?>Berita/berita.php">
                        Berita
                    </a>
                </li>

                <li>
                    <a href="<?= htmlspecialchars($navPrefix) ?>Galeri/galeri.php">
                        Galeri
                    </a>
                </li>

            </ul>

        </div>

    </div>


    <!-- =====================================================
         FOOTER BOTTOM
         ===================================================== -->

    <div
        class="footer-bottom"
        style="
            margin-top: 30px;
            text-align: center;
            padding-left: 20px;
            padding-right: 20px;
            box-sizing: border-box;
        "
    >

        &copy;
        <?= date('Y') ?>
        <?= htmlspecialchars(NAMA_KELURAHAN) ?>.
        All rights reserved.

    </div>

</footer>


<?php include __DIR__ . '/chatbot.php'; ?>


<!-- =========================================================
     RESPONSIVE FOOTER
     ========================================================= -->

<style>

    /* Tablet */
    @media (max-width: 900px) {

        .site-footer .footer-grid {
            grid-template-columns: 1fr 1fr !important;
            gap: 35px !important;
        }

        .site-footer .footer-grid > div:first-child {
            grid-column: 1 / -1;
        }

    }


    /* Mobile */
    @media (max-width: 600px) {

        .site-footer .footer-grid {
            grid-template-columns: 1fr !important;
            gap: 28px !important;
            padding-left: 20px !important;
            padding-right: 20px !important;
        }

        .site-footer .footer-grid > div:first-child {
            grid-column: auto;
        }

        .site-footer h4 {
            font-size: 17px !important;
        }

        .site-footer .kkn-attribution {
            margin-top: 20px !important;
        }

        .site-footer .footer-bottom {
            font-size: 11px;
            line-height: 1.6;
        }

    }

</style>


<!-- =========================================================
     JAVASCRIPT NAVBAR
     ========================================================= -->

<script>

    const navToggle = document.getElementById('navToggle');
    const mainNav = document.getElementById('mainNav');
    const siteHeader = document.querySelector('.site-header');


    /*
    |--------------------------------------------------------------------------
    | Header Scroll
    |--------------------------------------------------------------------------
    */

    if (siteHeader) {

        const handleScroll = () => {

            if (window.scrollY > 20) {
                siteHeader.classList.add('scrolled');
            } else {
                siteHeader.classList.remove('scrolled');
            }

        };

        window.addEventListener(
            'scroll',
            handleScroll,
            { passive: true }
        );

        handleScroll();

    }


    /*
    |--------------------------------------------------------------------------
    | Mobile Navigation
    |--------------------------------------------------------------------------
    */

    if (navToggle && mainNav) {

        navToggle.addEventListener('click', () => {

            const isOpen =
                mainNav.classList.toggle('open');

            navToggle.setAttribute(
                'aria-expanded',
                String(isOpen)
            );

        });

    }


    /*
    |--------------------------------------------------------------------------
    | Dropdown Navigation
    |--------------------------------------------------------------------------
    */

    document
        .querySelectorAll('.nav-dropdown-btn')
        .forEach((button) => {

            button.addEventListener('click', () => {

                const dropdown =
                    button.closest('.nav-dropdown');

                if (
                    window.matchMedia(
                        '(max-width: 1100px)'
                    ).matches
                ) {

                    dropdown.classList.toggle('open');

                    button.setAttribute(
                        'aria-expanded',
                        String(
                            dropdown.classList.contains('open')
                        )
                    );

                }

            });

        });
</script>

</body>
</html> 