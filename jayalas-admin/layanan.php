<?php
require_once __DIR__ . "/../config.php";
if (!isset($_SESSION['admin_login']) || $_SESSION['admin_login'] !== true) {
    header("Location: login.php");
    exit;
}

// Hapus
if (isset($_GET['hapus'])) {
    $id = (int)$_GET['hapus'];
    $q  = mysqli_query($conn, "SELECT foto FROM layanan WHERE id=$id");
    if ($d = mysqli_fetch_assoc($q)) {
        $file = "../assets/img/layanan/".$d['foto'];
        if ($d['foto'] && file_exists($file)) {
            unlink($file);
        }
    }
    mysqli_query($conn, "DELETE FROM layanan WHERE id=$id");
    header("Location: layanan.php");
    exit;
}

$list = mysqli_query($conn, "SELECT * FROM layanan ORDER BY urutan ASC, id ASC");
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Master Layanan - Admin</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>

    <?php include "navbar-menu.php"; ?>

    <div class="container mt-3">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3 class="mb-0">Data Layanan</h3>
            <a href="proses/tambah-layanan.php" class="btn btn-success btn-sm">
                + Tambah Layanan
            </a>
        </div>


        <div class="table-responsive">
            <table class="table table-bordered table-striped table-sm">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Foto</th>
                        <th>Nama</th>
                        <th>Harga</th>
                        <th>Deskripsi</th>
                        <th>Urutan</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $no = 1; 
                    while ($l = mysqli_fetch_assoc($list)) { ?>
                    <tr>
                        <td><?php echo $no++; ?></td>
                        <td>
                            <?php if ($l['foto']) { ?>
                            <img src="../assets/img/layanan/<?php echo htmlspecialchars($l['foto']); ?>" width="80">
                            <?php } ?>
                        </td>
                        <td><?php echo htmlspecialchars($l['nama']); ?></td>
                        <td>Rp <?php echo number_format($l['harga'],0,',','.'); ?></td>
                        <td><?php echo nl2br(htmlspecialchars($l['deskripsi'])); ?></td>
                        <td><?php echo $l['urutan']; ?></td>
                        <td>
                            <a href="proses/edit-layanan.php?id=<?php echo $l['id']; ?>"
                                class="btn btn-sm btn-warning">Edit</a>

                            <a href="layanan.php?hapus=<?php echo $l['id']; ?>" class="btn btn-sm btn-danger"
                                onclick="return confirm('Hapus data ini?');">Hapus</a>
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