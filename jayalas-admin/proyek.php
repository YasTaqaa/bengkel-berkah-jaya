<?php
require_once __DIR__ . "/../config.php";
if (!isset($_SESSION['admin_login']) || $_SESSION['admin_login'] !== true) {
    header("Location: login.php");
    exit;
}

$list = mysqli_query($conn, "SELECT p.*, l.nama AS nama_layanan
                             FROM proyek p
                             LEFT JOIN layanan l ON p.layanan_id=l.id
                             ORDER BY p.status='baru' DESC, p.id DESC");
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Data Pesanan - Admin</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>

    <?php include "navbar-menu.php"; ?>

    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3 class="mb-0">Data Pesanan</h3>
            <a href="proses/tambah-proyek.php" class="btn btn-success btn-sm">
                + Tambah Pesanan
            </a>
        </div>

        <div class="table-responsive mt-3">
            <table class="table table-bordered table-striped align-middle">
                <thead class="table-light">
                    <tr>
                        <th>No</th>
                        <th>Nama</th>
                        <th>HP</th>
                        <th>Layanan</th>
                        <th>Lokasi / Alamat</th>
                        <th>Catatan</th>
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
            while ($p = mysqli_fetch_assoc($list)) {
            ?>
                    <tr>
                        <form method="post" action="proses/update-proyek.php">
                            <td><?php echo $no++; ?></td>
                            <td><?php echo htmlspecialchars($p['nama']); ?></td>
                            <td><?php echo htmlspecialchars($p['hp']); ?></td>
                            <td><?php echo htmlspecialchars($p['nama_layanan']); ?></td>
                            <td><?php echo htmlspecialchars($p['lokasi']); ?></td>
                            <td style="white-space:pre-line;"><?php echo htmlspecialchars($p['catatan']); ?></td>

                            <td>
                                <input type="text" name="ukuran" value="<?php echo htmlspecialchars($p['ukuran']); ?>"
                                    class="form-control form-control-sm">
                            </td>
                            <td>
                                <input type="number" name="harga" value="<?php echo (float)$p['harga']; ?>"
                                    class="form-control form-control-sm">
                            </td>

                            <td><?php echo htmlspecialchars($p['status']); ?></td>
                            <td><?php echo $p['tgl_pesan']; ?></td>
                            <td><?php echo $p['tgl_selesai']; ?></td>

                            <td>
                                <div class="d-flex flex-wrap gap-1">
                                    <input type="hidden" name="id" value="<?php echo $p['id']; ?>">
                                    <button type="submit" class="btn btn-sm btn-primary">
                                        Simpan
                                    </button>

                                    <a href="proses/update-proyek.php?aksi=baru&id=<?php echo $p['id']; ?>"
                                        class="btn btn-secondary btn-sm">
                                        Baru
                                    </a>

                                    <a href="proses/update-proyek.php?aksi=proses&id=<?php echo $p['id']; ?>"
                                        class="btn btn-warning btn-sm">
                                        Proses
                                    </a>

                                    <a href="proses/update-proyek.php?aksi=selesai&id=<?php echo $p['id']; ?>"
                                        class="btn btn-success btn-sm"
                                        onclick="return confirm('Yakin pesanan ini sudah selesai?');">
                                        Selesai
                                    </a>

                                    <a href="proses/hapus-proyek.php?id=<?php echo $p['id']; ?>"
                                        class="btn btn-danger btn-sm"
                                        onclick="return confirm('Yakin hapus pesanan ini?');">
                                        Hapus
                                    </a>
                                </div>
                            </td>

                        </form>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>