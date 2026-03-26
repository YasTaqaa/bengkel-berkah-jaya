<?php
require_once __DIR__ . "/../config.php";
if (!isset($_SESSION['admin_login']) || $_SESSION['admin_login'] !== true) {
    header("Location: login.php");
    exit;
}

?>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="index.php">Admin Berkah Jaya</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#adminNavbar">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="adminNavbar">
            <ul class="navbar-nav me-auto">
                <li class="nav-item"><a class="nav-link" href="index.php">Dashboard</a></li>
                <li class="nav-item"><a class="nav-link" href="layanan.php">Layanan</a></li>
                <li class="nav-item"><a class="nav-link" href="galeri.php">Galeri</a></li>
                <li class="nav-item"><a class="nav-link" href="tim.php">Tim</a></li>
                <li class="nav-item"><a class="nav-link" href="proyek.php">Pesanan</a></li>
                <li class="nav-item"><a class="nav-link" href="riwayat-pesanan.php">Riwayat</a></li>
                <li class="nav-item"><a class="nav-link" href="ganti-password.php">Ganti Password</a></li>
            </ul>
            <span class="navbar-text me-3">
                Login sebagai: <?php echo htmlspecialchars($_SESSION['admin_user'] ?? 'admin'); ?>
            </span>
            <a href="logout.php" class="btn btn-outline-light btn-sm">Logout</a>
        </div>
    </div>
</nav>