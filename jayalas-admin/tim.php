<?php
require_once __DIR__ . "/../config.php";
if (!isset($_SESSION['admin_login']) || $_SESSION['admin_login'] !== true) {
    header("Location: login.php");
    exit;
}

// Hapus
if (isset($_GET['hapus'])) {
    $id = (int)$_GET['hapus'];
    $q  = mysqli_query($conn, "SELECT foto FROM karyawan WHERE id=$id");
    if ($d = mysqli_fetch_assoc($q)) {
        $file = "../assets/img/tim/".$d['foto'];
        if ($d['foto'] && file_exists($file)) {
            unlink($file);
        }
    }
    mysqli_query($conn, "DELETE FROM karyawan WHERE id=$id");
    header("Location: tim.php");
    exit;
}

$list = mysqli_query($conn, "SELECT * FROM karyawan ORDER BY urutan ASC, id ASC");
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Tim Bengkel - Admin</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>

    <?php include "navbar-menu.php"; ?>

    <div class="container mt-3">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3>Data Tim / Karyawan</h3>
            <a href="proses/tambah-tim.php" class="btn btn-success btn-sm">+ Tambah Anggota</a>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered table-striped table-sm">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Foto</th>
                        <th>Nama</th>
                        <th>Jabatan</th>
                        <th>Pengalaman</th>
                        <th>Urutan</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($t = mysqli_fetch_assoc($list)) { ?>
                    <tr>
                        <td><?php echo $t['id']; ?></td>
                        <td>
                            <?php if ($t['foto']) { ?>
                            <img src="../assets/img/tim/<?php echo htmlspecialchars($t['foto']); ?>" width="80">
                            <?php } ?>
                        </td>
                        <td><?php echo htmlspecialchars($t['nama']); ?></td>
                        <td><?php echo htmlspecialchars($t['jabatan']); ?></td>
                        <td><?php echo (int)$t['pengalaman']; ?> th</td>
                        <td><?php echo $t['urutan']; ?></td>
                        <td>
                            <a href="proses/edit-tim.php?id=<?php echo $t['id']; ?>"
                                class="btn btn-sm btn-warning">Edit</a>
                            <a href="tim.php?hapus=<?php echo $t['id']; ?>" class="btn btn-sm btn-danger"
                                onclick="return confirm('Hapus data ini?');">Hapus</a>
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>