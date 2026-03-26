<?php
require_once __DIR__ . "/../../config.php";
if (!isset($_SESSION['admin_login']) || $_SESSION['admin_login'] !== true) {
    header("Location: ../login.php");
    exit;
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// PROSES UPDATE KETIKA SUBMIT
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama       = input_filter($conn, $_POST['nama']);
    $jabatan    = input_filter($conn, $_POST['jabatan']);
    $pengalaman = (int)$_POST['pengalaman'];
    $urutan     = (int)$_POST['urutan'];

    // ambil data lama untuk foto
    $qData = mysqli_query($conn, "SELECT foto FROM karyawan WHERE id=$id");
    $lama  = mysqli_fetch_assoc($qData);
    if ($lama) {
        $fotoName = $lama['foto'];
        if (!empty($_FILES['foto']['name'])) {
            if ($fotoName && file_exists("../../assets/img/tim/".$fotoName)) {
                unlink("../../assets/img/tim/".$fotoName);
            }
            $ext = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
            $fotoName = "tim_".time().".".$ext;
            move_uploaded_file($_FILES['foto']['tmp_name'], "../../assets/img/tim/".$fotoName);
        }

        $sql = "UPDATE karyawan SET
                nama='$nama',
                jabatan='$jabatan',
                pengalaman='$pengalaman',
                foto='$fotoName',
                urutan='$urutan'
                WHERE id=$id";
        mysqli_query($conn, $sql);

        header("Location: ../tim.php");
        exit;
    } else {
        $error = "Data tidak ditemukan.";
    }
}

// AMBIL DATA UNTUK DITAMPILKAN DI FORM
$q  = mysqli_query($conn, "SELECT * FROM karyawan WHERE id=$id");
$data = mysqli_fetch_assoc($q);
if (!$data) {
    $error = "Data tidak ditemukan.";
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Edit Tim - Admin</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>

    <?php include "../navbar-menu.php"; ?>

    <div class="container mt-3">
        <h3>Edit Anggota Tim</h3>

        <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <?php if (!empty($data)): ?>
        <form method="post" action="edit-tim.php?id=<?php echo $data['id']; ?>" enctype="multipart/form-data">
            <div class="mb-3">
                <label class="form-label">Nama</label>
                <input type="text" name="nama" class="form-control" required
                    value="<?php echo htmlspecialchars($data['nama']); ?>">
            </div>
            <div class="mb-3">
                <label class="form-label">Jabatan</label>
                <select name="jabatan" class="form-select" required>
                    <option value="Owner" <?php if ($data['jabatan']=='Owner') echo 'selected'; ?>>Owner</option>
                    <option value="Karyawan" <?php if ($data['jabatan']=='Karyawan') echo 'selected'; ?>>Karyawan
                    </option>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Pengalaman (tahun)</label>
                <input type="number" name="pengalaman" class="form-control"
                    value="<?php echo (int)$data['pengalaman']; ?>">
            </div>
            <div class="mb-3">
                <label class="form-label">Urutan</label>
                <input type="number" name="urutan" class="form-control" value="<?php echo (int)$data['urutan']; ?>">
            </div>
            <div class="mb-3">
                <label class="form-label">Foto</label><br>
                <?php if ($data['foto']) { ?>
                <img src="../../assets/img/tim/<?php echo htmlspecialchars($data['foto']); ?>" width="120"
                    class="mb-2"><br>
                <?php } ?>
                <input type="file" name="foto" class="form-control" accept="image/*">
                <small class="text-muted">Kosongkan jika tidak mengganti.</small>
            </div>
            <button type="submit" class="btn btn-primary">Update</button>
            <a href="../tim.php" class="btn btn-secondary">Kembali</a>
        </form>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>