<?php
session_start();
require_once __DIR__ . "/../../config.php";

if (!isset($_SESSION['admin_login']) || $_SESSION['admin_login'] !== true) {
    header("Location: ../login.php");
    exit;
}

// Simpan
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama        = input_filter($conn, $_POST['nama']);
    $jabatan     = input_filter($conn, $_POST['jabatan']);
    $pengalaman  = (int)$_POST['pengalaman'];
    $urutan      = $_POST['urutan'] !== '' ? (int)$_POST['urutan'] : 0;

    $foto = '';
    if (!empty($_FILES['foto']['name'])) {
        $tmp  = $_FILES['foto']['tmp_name'];
        $name = time() . '-' . basename($_FILES['foto']['name']);
        $dest = "../../assets/img/tim/" . $name;
        if (move_uploaded_file($tmp, $dest)) {
            $foto = $name;
        }
    }

    $sql = "INSERT INTO karyawan (nama, jabatan, pengalaman, urutan, foto)
            VALUES ('$nama', '$jabatan', $pengalaman, $urutan, '$foto')";
    mysqli_query($conn, $sql);

    header("Location: ../tim.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Tambah Anggota Tim - Admin</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>

    <section class="py-4">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h3 class="mb-0">Tambah Anggota Tim</h3>
                <a href="../tim.php" class="btn btn-secondary btn-sm">
                    &laquo; Kembali
                </a>
            </div>

            <div class="card">
                <div class="card-body">
                    <form method="post" enctype="multipart/form-data">

                        <div class="mb-3">
                            <label class="form-label">Nama</label>
                            <input type="text" name="nama" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Jabatan</label>
                            <select name="jabatan" class="form-select" required>
                                <option value="">-- Pilih Jabatan --</option>
                                <option value="Owner">Owner</option>
                                <option value="Karyawan">Karyawan</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Pengalaman (tahun)</label>
                            <input type="number" name="pengalaman" class="form-control" value="0" min="0">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Urutan</label>
                            <input type="number" name="urutan" class="form-control" value="0" min="0">
                            <small class="text-muted">Angka kecil tampil lebih dulu.</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Foto</label>
                            <input type="file" name="foto" class="form-control" accept="image/*">
                            <small class="text-muted">Upload foto anggota (jpg/png).</small>
                        </div>

                        <button type="submit" class="btn btn-primary">Simpan</button>
                        <a href="../tim.php" class="btn btn-secondary">Batal</a>

                    </form>
                </div>
            </div>
        </div>
    </section>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>