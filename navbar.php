<?php
require_once __DIR__ . "/config.php";
$halaman_aktif = basename($_SERVER['PHP_SELF']);
?>

<nav class="navbar navbar-expand-lg" id="mainNav">
    <div class="container">

        <!-- Brand -->
        <a class="navbar-brand" href="index.php">
            <span class="nav-brand-icon">
                <i class="bi bi-tools"></i>
            </span>
            <div class="nav-brand-text">
                <span class="nav-brand-name">Berkah Jaya</span>
                <span class="nav-brand-sub">Bengkel Las </span>
            </div>
        </a>

        <!-- Toggler Mobile -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar"
            aria-controls="mainNavbar" aria-expanded="false" aria-label="Toggle navigation">
            <i class="bi bi-list"></i>
        </button>

        <!-- Menu -->
        <div class="collapse navbar-collapse" id="mainNavbar">
            <ul class="navbar-nav ms-auto mb-2 mb-lg-0 align-items-lg-center gap-lg-1">
                <li class="nav-item">
                    <a class="nav-link <?= $halaman_aktif === 'index.php'   ? 'active' : '' ?>"
                        href="index.php">Beranda</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $halaman_aktif === 'layanan.php' ? 'active' : '' ?>"
                        href="layanan.php">Layanan</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $halaman_aktif === 'galeri.php'  ? 'active' : '' ?>"
                        href="galeri.php">Galeri</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $halaman_aktif === 'tim.php'     ? 'active' : '' ?>" href="tim.php">Tim</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $halaman_aktif === 'pesan.php'   ? 'active' : '' ?>"
                        href="pesan.php">Pesan</a>
                </li>
            </ul>

            <!-- Divider + CTA WA -->
            <span class="nav-divider d-none d-lg-flex ms-2"></span>
            <a href="https://wa.me/6281234567890" target="_blank" class="btn-nav-wa ms-lg-2">
                <i class="bi bi-whatsapp"></i>
                <span>Hubungi WA</span>
            </a>
        </div>

    </div>
</nav>