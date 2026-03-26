<?php
session_start();
require_once __DIR__ . "/../../config.php";

if (!isset($_SESSION['admin_login']) || $_SESSION['admin_login'] !== true) {
    header("Location: ../login.php");
    exit;
}

// simpan
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama      = input_filter($conn, $_POST['nama']);
    $harga     = (int)$_POST['harga'];
    $deskripsi = input_filter($conn, $_POST['deskripsi']);
    $urutan    = $_POST['urutan'] !== '' ? (int)$_POST['urutan'] : 0;

    $foto = '';
    if (!empty($_FILES['foto']['name'])) {
        $tmp  = $_FILES['foto']['tmp_name'];
        $name = time() . '-' . basename($_FILES['foto']['name']);
        $dest = "../../assets/img/layanan/" . $name;
        if (move_uploaded_file($tmp, $dest)) {
            $foto = $name;
        }
    }

    $sql = "INSERT INTO layanan (nama,harga,deskripsi,urutan,foto)
            VALUES ('$nama',$harga,'$deskripsi',$urutan,'$foto')";
    mysqli_query($conn, $sql);

    header("Location: ../layanan.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Tambah Layanan - Admin</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>

    <section class="py-4">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h3 class="mb-0">Tambah Layanan</h3>
            </div>

            <div class="card">
                <div class="card-body">
                    <form method="post" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label class="form-label">Nama Layanan</label>
                            <input type="text" name="nama" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Harga</label>
                            <input type="number" name="harga" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Deskripsi</label>
                            <textarea name="deskripsi" class="form-control" rows="3"></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Urutan</label>
                            <input type="number" name="urutan" class="form-control" value="0">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Foto</label>
                            <input type="file" name="foto" class="form-control" accept="image/*">
                            <small class="text-muted">Upload gambar layanan (jpg/png).</small>
                        </div>

                        <button type="submit" class="btn btn-primary">Simpan</button>
                        <a href="../layanan.php" class="btn btn-secondary">Batal</a>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>