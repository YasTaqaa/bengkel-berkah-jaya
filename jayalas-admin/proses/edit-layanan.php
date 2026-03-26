<?php
require_once __DIR__ . "/../../config.php";
if (!isset($_SESSION['admin_login']) || $_SESSION['admin_login'] !== true) {
    header("Location: ../login.php");
    exit;
}


$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// PROSES UPDATE
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama      = input_filter($conn, $_POST['nama']);
    $harga     = (float)$_POST['harga'];
    $deskripsi = input_filter($conn, $_POST['deskripsi']);
    $urutan    = (int)$_POST['urutan'];

    $qData = mysqli_query($conn, "SELECT foto FROM layanan WHERE id=$id");
    $lama  = mysqli_fetch_assoc($qData);
    if ($lama) {
        $fotoName = $lama['foto'];
        if (!empty($_FILES['foto']['name'])) {
            if ($fotoName && file_exists("../../assets/img/layanan/".$fotoName)) {
                unlink("../../assets/img/layanan/".$fotoName);
            }
            $ext = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
            $fotoName = "layanan_".time().".".$ext;
            move_uploaded_file($_FILES['foto']['tmp_name'], "../../assets/img/layanan/".$fotoName);
        }

        $sql = "UPDATE layanan SET
                nama='$nama',
                harga='$harga',
                deskripsi='$deskripsi',
                foto='$fotoName',
                urutan='$urutan'
                WHERE id=$id";
        mysqli_query($conn, $sql);

        header("Location: ../layanan.php");
        exit;
    } else {
        $error = "Data tidak ditemukan.";
    }
}

// TAMPIL FORM
$q  = mysqli_query($conn, "SELECT * FROM layanan WHERE id=$id");
$data = mysqli_fetch_assoc($q);
if (!$data) {
    $error = "Data tidak ditemukan.";
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Edit Layanan - Admin</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css"><!-- kalau ada -->
</head>

<body>

    <section class="py-4">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h3 class="mb-0">Edit Layanan</h3>
            </div>

            <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>

            <?php if (!empty($data)): ?>
            <div class="card">
                <div class="card-body">
                    <form method="post" action="edit-layanan.php?id=<?php echo $data['id']; ?>"
                        enctype="multipart/form-data">

                        <div class="mb-3">
                            <label class="form-label">Nama Layanan</label>
                            <input type="text" name="nama" class="form-control" required
                                value="<?php echo htmlspecialchars($data['nama']); ?>">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Harga</label>
                            <input type="number" name="harga" class="form-control" required
                                value="<?php echo $data['harga']; ?>">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Deskripsi</label>
                            <textarea name="deskripsi" class="form-control" rows="3"><?php
                echo htmlspecialchars($data['deskripsi']);
              ?></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Urutan</label>
                            <input type="number" name="urutan" class="form-control"
                                value="<?php echo $data['urutan']; ?>">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Foto</label><br>
                            <?php if ($data['foto']) { ?>
                            <img src="../../assets/img/layanan/<?php echo htmlspecialchars($data['foto']); ?>"
                                width="120" class="mb-2 d-block">
                            <?php } ?>
                            <input type="file" name="foto" class="form-control" accept="image/*">
                            <small class="text-muted">Kosongkan jika tidak mengganti.</small>
                        </div>

                        <button type="submit" class="btn btn-primary">Update</button>
                        <a href="../layanan.php" class="btn btn-secondary">Batal</a>
                    </form>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </section>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>