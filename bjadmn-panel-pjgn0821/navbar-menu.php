<?php
require_once __DIR__ . "/../config.php";
if (!isset($_SESSION['admin_login']) || $_SESSION['admin_login'] !== true) {
    header("Location: login.php");
    exit;
}
$halaman_aktif = basename($_SERVER['PHP_SELF']);

// Hitung pesanan baru untuk badge notifikasi
$notif = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS jml FROM proyek WHERE status='baru'"));
$jml_notif = (int)$notif['jml'];
?>
<style>
#adminNav {
    background: #0f172a;
    border-bottom: 1px solid rgba(255, 255, 255, 0.07);
    position: sticky;
    top: 0;
    z-index: 1000;
    padding: 0;
}

#adminNav .container-fluid {
    padding: 0 16px;
}

.admin-brand-icon {
    width: 32px;
    height: 32px;
    background: linear-gradient(135deg, #2563eb, #1d4ed8);
    border-radius: 8px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-size: 0.9rem;
    margin-right: 8px;
    vertical-align: middle;
    flex-shrink: 0;
}

#adminNav .navbar-brand {
    display: flex;
    align-items: center;
    font-size: 0.92rem;
    font-weight: 700;
    color: #f8fafc;
    padding: 12px 0;
    text-decoration: none;
}

#adminNav .navbar-brand:hover {
    color: #fff;
}

#adminNav .nav-link {
    font-size: 0.85rem;
    font-weight: 500;
    color: rgba(255, 255, 255, 0.55);
    padding: 8px 12px;
    border-radius: 7px;
    transition: all 0.18s;
    position: relative;
}

#adminNav .nav-link:hover {
    color: #fff;
    background: rgba(255, 255, 255, 0.08);
}

#adminNav .nav-link.active {
    color: #fff;
    font-weight: 600;
    background: rgba(37, 99, 235, 0.3);
}

/* Badge notifikasi */
.notif-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: #ef4444;
    color: #fff;
    font-size: 0.62rem;
    font-weight: 700;
    min-width: 17px;
    height: 17px;
    border-radius: 50px;
    padding: 0 4px;
    margin-left: 5px;
    line-height: 1;
    vertical-align: middle;
    animation: pulse-badge 2s infinite;
}

@keyframes pulse-badge {

    0%,
    100% {
        opacity: 1;
    }

    50% {
        opacity: 0.7;
    }
}

.btn-logout {
    font-size: 0.82rem;
    font-weight: 600;
    color: #f87171;
    background: rgba(248, 113, 113, 0.1);
    border: 1px solid rgba(248, 113, 113, 0.25);
    padding: 6px 14px;
    border-radius: 7px;
    text-decoration: none;
    transition: all 0.2s;
}

.btn-logout:hover {
    background: rgba(248, 113, 113, 0.2);
    color: #fca5a5;
}

#adminNav .navbar-toggler {
    border: 1px solid rgba(255, 255, 255, 0.18);
    color: rgba(255, 255, 255, 0.7);
    padding: 4px 9px;
    border-radius: 7px;
    font-size: 1.1rem;
    box-shadow: none;
    outline: none;
    background: transparent;
}

@media (max-width: 991.98px) {
    #adminNav .navbar-collapse {
        background: #1e2d45;
        margin: 6px -16px 0;
        padding: 10px 12px 14px;
        border-top: 1px solid rgba(255, 255, 255, 0.07);
    }

    #adminNav .nav-link {
        padding: 9px 12px;
    }

    .btn-logout {
        margin-top: 8px;
        display: block;
        text-align: center;
    }
}
</style>

<nav class="navbar navbar-expand-lg" id="adminNav">
    <div class="container-fluid">

        <a class="navbar-brand" href="index.php">
            <span class="admin-brand-icon">
                <i class="bi bi-tools"></i>
            </span>
            Berkah Jaya — Admin
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#adminNavbar">
            <i class="bi bi-list"></i>
        </button>

        <div class="collapse navbar-collapse" id="adminNavbar">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0 gap-lg-1">
                <li class="nav-item">
                    <a class="nav-link <?= $halaman_aktif === 'index.php' ? 'active' : '' ?>"
                        href="index.php">Dashboard</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $halaman_aktif === 'layanan.php' ? 'active' : '' ?>"
                        href="layanan.php">Layanan</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $halaman_aktif === 'galeri.php' ? 'active' : '' ?>"
                        href="galeri.php">Galeri</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $halaman_aktif === 'tim.php' ? 'active' : '' ?>" href="tim.php">Tim</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $halaman_aktif === 'proyek.php' ? 'active' : '' ?>" href="proyek.php">
                        Pesanan
                        <?php if ($jml_notif > 0): ?>
                        <span class="notif-badge"><?= $jml_notif ?></span>
                        <?php endif; ?>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $halaman_aktif === 'riwayat-pesanan.php' ? 'active' : '' ?>"
                        href="riwayat-pesanan.php">Riwayat</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $halaman_aktif === 'ganti-password.php' ? 'active' : '' ?>"
                        href="ganti-password.php">Ganti Password</a>
                </li>
            </ul>
            <a href="logout.php" class="btn-logout">Logout</a>
        </div>

    </div>
</nav>