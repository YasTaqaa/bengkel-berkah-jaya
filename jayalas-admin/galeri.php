<?php
require_once __DIR__ . "/../config.php";
if (!isset($_SESSION['admin_login']) || $_SESSION['admin_login'] !== true) {
    header("Location: login.php");
    exit;
}

// Hapus
if (isset($_GET['hapus'])) {
    $id = (int)$_GET['hapus'];
    $q  = mysqli_query($conn, "SELECT foto FROM galeri WHERE id=$id");
    if ($d = mysqli_fetch_assoc($q)) {
        $file = "../assets/img/galeri/".$d['foto'];
        if ($d['foto'] && file_exists($file)) {
            unlink($file);
        }
    }
    mysqli_query($conn, "DELETE FROM galeri WHERE id=$id");
    header("Location: galeri.php");
    exit;
}

$list      = mysqli_query($conn, "SELECT g.*, l.nama AS nama_layanan
                                  FROM galeri g
                                  LEFT JOIN layanan l ON g.layanan_id=l.id
                                  ORDER BY g.tgl_selesai DESC, g.id DESC");
$layananDD = mysqli_query($conn, "SELECT * FROM layanan ORDER BY urutan ASC");
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Galeri Proyek - Admin</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>

    <?php include "navbar-menu.php"; ?>

    <div class="container mt-3">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3 class="mb-0">Galeri Proyek</h3>
            <a href="proses/tambah-galeri.php" class="btn btn-success btn-sm">
                + Tambah Galeri
            </a>
        </div>


        <div class="table-responsive">
            <table class="table table-bordered table-striped table-sm">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Foto</th>
                        <th>Judul</th>
                        <th>Layanan</th>
                        <th>Keterangan</th>
                        <th>Tgl Selesai</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>

                    <?php
                    $no = 1; 
                    while ($g = mysqli_fetch_assoc($list)) { ?>
                    <tr>
                        <td><?php echo $no++; ?></td>
                        <td>
                            <?php if ($g['foto']) { ?>
                            <img src="../assets/img/galeri/<?php echo htmlspecialchars($g['foto']); ?>" width="80">
                            <?php } ?>
                        </td>
                        <td><?php echo htmlspecialchars($g['judul']); ?></td>
                        <td><?php echo htmlspecialchars($g['nama_layanan']); ?></td>
                        <td><?php echo nl2br(htmlspecialchars($g['keterangan'])); ?></td>
                        <td><?php echo htmlspecialchars($g['tgl_selesai']); ?></td>
                        <td>
                            <a href="proses/edit-galeri.php?id=<?php echo $g['id']; ?>"
                                class="btn btn-sm btn-warning">Edit</a>
                            <a href="galeri.php?hapus=<?php echo $g['id']; ?>" class="btn btn-sm btn-danger"
                                onclick="return confirm('Hapus data ini?');">Hapus</a>
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Tambah Galeri -->
    <div class="modal fade" id="modalTambah" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <form method="post" action="proses/simpan-galeri.php" enctype="multipart/form-data" class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Galeri</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Judul</label>
                        <input type="text" name="judul" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Layanan Terkait</label>
                        <select name="layanan_id" class="form-select">
                            <option value="">-- Pilih Layanan (opsional) --</option>
                            <?php
            mysqli_data_seek($layananDD, 0);
            while ($l = mysqli_fetch_assoc($layananDD)) {
              echo '<option value="'.$l['id'].'">'.htmlspecialchars($l['nama']).'</option>';
            }
            ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Keterangan</label>
                        <textarea name="keterangan" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tanggal Selesai</label>
                        <input type="date" name="tgl_selesai" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Foto</label>
                        <input type="file" name="foto" class="form-control" accept="image/*" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Simpan</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>