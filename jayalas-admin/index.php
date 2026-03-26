<?php
require_once __DIR__ . "/../config.php";
if (!isset($_SESSION['admin_login']) || $_SESSION['admin_login'] !== true) {
    header("Location: login.php");
    exit;
}

// Statistik
$tot       = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS jml FROM proyek"));
$baru      = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS jml FROM proyek WHERE status='baru'"));
$selesai   = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS jml FROM proyek WHERE status='selesai'"));
$pend      = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COALESCE(SUM(harga),0) AS total FROM proyek WHERE status='selesai'"));

// 10 riwayat terakhir
$riwayat = mysqli_query($conn, "SELECT p.*, l.nama AS nama_layanan
                                FROM proyek p
                                LEFT JOIN layanan l ON p.layanan_id=l.id
                                ORDER BY p.id DESC LIMIT 10");
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Dashboard Admin - Bengkel Las Berkah Jaya</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>

    <?php include "navbar-menu.php"; ?>

    <div class="container-fluid mt-3">
        <div class="row">

            <div class="col-md-3 mb-3">
                <div class="card text-bg-primary">
                    <div class="card-body">
                        <h5>Total Pesanan</h5>
                        <h3><?php echo (int)$tot['jml']; ?></h3>
                    </div>
                </div>
            </div>

            <div class="col-md-3 mb-3">
                <div class="card text-bg-warning">
                    <div class="card-body">
                        <h5>Pesanan Baru</h5>
                        <h3><?php echo (int)$baru['jml']; ?></h3>
                    </div>
                </div>
            </div>

            <div class="col-md-3 mb-3">
                <div class="card text-bg-success">
                    <div class="card-body">
                        <h5>Pesanan Selesai</h5>
                        <h3><?php echo (int)$selesai['jml']; ?></h3>
                    </div>
                </div>
            </div>

            <div class="col-md-3 mb-3">
                <div class="card text-bg-dark">
                    <div class="card-body">
                        <h5>Pendapatan (Selesai)</h5>
                        <h3>Rp <?php echo number_format($pend['total'],0,',','.'); ?></h3>
                    </div>
                </div>
            </div>

        </div>

        <!-- Tabel riwayat 10 terakhir -->
        <div class="row">
            <div class="col">
                <div class="card">
                    <div class="card-header">
                        Riwayat Pesanan Terbaru
                    </div>
                    <div class="card-body table-responsive">
                        <table class="table table-striped table-bordered table-sm">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nama</th>
                                    <th>HP</th>
                                    <th>Layanan</th>
                                    <th>Ukuran</th>
                                    <th>Harga</th>
                                    <th>Status</th>
                                    <th>Tgl Pesan</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $no = 1;
                                while ($p = mysqli_fetch_assoc($riwayat)) { ?>
                                <tr>
                                    <td><?php echo $no++; ?></td>
                                    <td><?php echo htmlspecialchars($p['nama']); ?></td>
                                    <td><?php echo htmlspecialchars($p['hp']); ?></td>
                                    <td><?php echo htmlspecialchars($p['nama_layanan']); ?></td>
                                    <td><?php echo htmlspecialchars($p['ukuran']); ?></td>
                                    <td>Rp <?php echo number_format($p['harga'],0,',','.'); ?></td>
                                    <td><?php echo htmlspecialchars($p['status']); ?></td>
                                    <td><?php echo $p['tgl_pesan']; ?></td>
                                </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>