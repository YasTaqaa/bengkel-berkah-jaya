<?php
require_once __DIR__ . "/../config.php";
if (!isset($_SESSION['admin_login']) || $_SESSION['admin_login'] !== true) {
    header("Location: login.php");
    exit;
}

$list = mysqli_query($conn, "SELECT p.*, l.nama AS nama_layanan
                             FROM proyek p
                             LEFT JOIN layanan l ON p.layanan_id=l.id
                             ORDER BY p.id DESC");
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Riwayat Pesanan - Admin</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>

    <?php include "navbar-menu.php"; ?>

    <div class="container mt-3">
        <h3>Riwayat Pesanan</h3>
        <div class="table-responsive">
            <table class="table table-bordered table-striped table-sm">
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
                        <th>Tgl Selesai</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $no = 1; 
                    while ($p = mysqli_fetch_assoc($list)) { ?>
                    <tr>
                        <td><?php echo $no++; ?></td>
                        <td><?php echo htmlspecialchars($p['nama']); ?></td>
                        <td><?php echo htmlspecialchars($p['hp']); ?></td>
                        <td><?php echo htmlspecialchars($p['nama_layanan']); ?></td>
                        <td><?php echo htmlspecialchars($p['ukuran']); ?></td>
                        <td>Rp <?php echo number_format($p['harga'],0,',','.'); ?></td>
                        <td><?php echo htmlspecialchars($p['status']); ?></td>
                        <td><?php echo $p['tgl_pesan']; ?></td>
                        <td><?php echo $p['tgl_selesai']; ?></td>
                        <td>
                            <a href="nota.php?id=<?= $p['id']; ?>" class="btn btn-sm btn-outline-secondary"
                                target="_blank">
                                Nota
                            </a>
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>