<?php
session_start();
require_once __DIR__ . "/../../config.php";

if (!isset($_SESSION['admin_login']) || $_SESSION['admin_login'] !== true) {
    header("Location: ../login.php");
    exit;
}

$qLayanan = mysqli_query($conn, "SELECT id, nama FROM layanan ORDER BY urutan ASC");

// simpan galeri
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $judul       = input_filter($conn, $_POST['judul']);
    $layanan_id  = $_POST['layanan_id'] !== '' ? (int)$_POST['layanan_id'] : null;
    $keterangan  = input_filter($conn, $_POST['keterangan']);
    $tgl_selesai = input_filter($conn, $_POST['tgl_selesai']);

    $foto = '';
    if (!empty($_FILES['foto']['name'])) {
        $tmp  = $_FILES['foto']['tmp_name'];
        $name = time() . '-' . basename($_FILES['foto']['name']);
        $dest = "../../assets/img/galeri/" . $name;
        if (move_uploaded_file($tmp, $dest)) {
            $foto = $name;
        }
    }

    $layanan_sql = is_null($layanan_id) ? "NULL" : $layanan_id;

    $sql = "INSERT INTO galeri (judul, layanan_id, keterangan, tgl_selesai, foto)
            VALUES ('$judul', $layanan_sql, '$keterangan', '$tgl_selesai', '$foto')";
    mysqli_query($conn, $sql);

    header("Location: ../galeri.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Tambah Galeri - Admin</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>

    <section class="py-4">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h3 class="mb-0">Tambah Galeri</h3>
                <a href="../galeri.php" class="btn btn-outline-secondary btn-sm">Kembali</a>
            </div>

            <div class="card">
                <div class="card-body">
                    <form method="post" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label class="form-label">Judul</label>
                            <input type="text" name="judul" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Layanan Terkait</label>
                            <select name="layanan_id" class="form-select">
                                <option value="">-- Pilih Layanan (opsional) --</option>
                                <?php
              mysqli_data_seek($qLayanan, 0);
              while ($l = mysqli_fetch_assoc($qLayanan)) {
                  echo '<option value="'.$l['id'].'">'
                       .htmlspecialchars($l['nama']).'</option>';
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
                            <input type="date" name="tgl_selesai" class="form-control">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Foto</label>
                            <input type="file" name="foto" class="form-control" accept="image/*" required>
                            <small class="text-muted">Upload foto hasil pekerjaan (jpg/png).</small>
                        </div>

                        <button type="submit" class="btn btn-primary">Simpan</button>
                        <a href="../galeri.php" class="btn btn-secondary">Batal</a>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>