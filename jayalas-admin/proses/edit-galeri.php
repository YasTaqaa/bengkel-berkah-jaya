<?php
session_start();
require_once __DIR__ . "/../../config.php";

if (!isset($_SESSION['admin_login']) || $_SESSION['admin_login'] !== true) {
    header("Location: login.php");
    exit;
}

// ambil data galeri yang mau diedit
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$data = [];
$error = '';

if ($id > 0) {
    $q = mysqli_query($conn, "SELECT * FROM galeri WHERE id = $id LIMIT 1");
    if ($q && mysqli_num_rows($q) === 1) {
        $data = mysqli_fetch_assoc($q);
    } else {
        $error = "Data galeri tidak ditemukan.";
    }
} else {
    $error = "ID galeri tidak valid.";
}

// ambil layanan utk dropdown (optional)
$qLayanan = mysqli_query($conn, "SELECT id, nama FROM layanan ORDER BY urutan ASC");

// proses update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($error)) {
    $judul        = input_filter($conn, $_POST['judul']);
    $layanan_id   = $_POST['layanan_id'] !== '' ? (int)$_POST['layanan_id'] : null;
    $keterangan   = input_filter($conn, $_POST['keterangan']);
    $tgl_selesai  = input_filter($conn, $_POST['tgl_selesai']);

    $nama_file = $data['foto'];

    // jika upload foto baru
    if (!empty($_FILES['foto']['name'])) {
        $tmp  = $_FILES['foto']['tmp_name'];
        $name = time() . '-' . basename($_FILES['foto']['name']);
        $dest = "../assets/img/galeri/" . $name;

        if (move_uploaded_file($tmp, $dest)) {
            // hapus foto lama (optional)
            if ($data['foto'] && file_exists("../assets/img/galeri/" . $data['foto'])) {
                unlink("../assets/img/galeri/" . $data['foto']);
            }
            $nama_file = $name;
        }
    }

    $layanan_sql = is_null($layanan_id) ? "NULL" : $layanan_id;

    $sql = "UPDATE galeri SET 
                judul       = '$judul',
                layanan_id  = $layanan_sql,
                keterangan  = '$keterangan',
                tgl_selesai = '$tgl_selesai',
                foto        = '$nama_file'
            WHERE id = $id";

    mysqli_query($conn, $sql);

    header("Location: ../galeri.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Edit Galeri - Admin</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>

    <section class="py-4">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h3 class="mb-0">Edit Galeri</h3>
            </div>

            <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>

            <?php if (!empty($data)): ?>
            <div class="card">
                <div class="card-body">
                    <form method="post" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label class="form-label">Judul</label>
                            <input type="text" name="judul" class="form-control" required
                                value="<?php echo htmlspecialchars($data['judul']); ?>">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Layanan Terkait</label>
                            <select name="layanan_id" class="form-select">
                                <option value="">-- Pilih Layanan (opsional) --</option>
                                <?php
                mysqli_data_seek($qLayanan, 0);
                while ($l = mysqli_fetch_assoc($qLayanan)) {
                    $sel = ($data['layanan_id'] == $l['id']) ? 'selected' : '';
                    echo '<option value="'.$l['id'].'" '.$sel.'>'
                         .htmlspecialchars($l['nama']).'</option>';
                }
                ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Keterangan</label>
                            <textarea name="keterangan" class="form-control" rows="3"><?php
                echo htmlspecialchars($data['keterangan']);
              ?></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Tanggal Selesai</label>
                            <input type="date" name="tgl_selesai" class="form-control"
                                value="<?php echo htmlspecialchars($data['tgl_selesai']); ?>">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Foto</label><br>
                            <?php if ($data['foto']) { ?>
                            <img src="../assets/img/galeri/<?php echo htmlspecialchars($data['foto']); ?>" width="150"
                                class="mb-2 d-block">
                            <?php } ?>
                            <input type="file" name="foto" class="form-control" accept="image/*">
                            <small class="text-muted">Kosongkan jika tidak mengganti foto.</small>
                        </div>

                        <button type="submit" class="btn btn-primary">Update</button>
                        <a href="../galeri.php" class="btn btn-secondary">Batal</a>
                    </form>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </section>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>