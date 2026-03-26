<?php
session_start();
require_once __DIR__ . "/../../config.php";

if (!isset($_SESSION['admin_login']) || $_SESSION['admin_login'] !== true) {
    header("Location: ../login.php");
    exit;
}

// ambil layanan untuk dropdown
$qLayanan = mysqli_query($conn, "SELECT * FROM layanan ORDER BY urutan ASC");

// kalau POST -> simpan
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama       = input_filter($conn, $_POST['nama']);
    $hp         = input_filter($conn, $_POST['hp']);
    $layanan_id = (int)$_POST['layanan_id'];
    $lokasi     = input_filter($conn, $_POST['lokasi']);
    $catatan    = input_filter($conn, $_POST['catatan']);

    mysqli_query(
        $conn,
        "INSERT INTO proyek (nama,hp,layanan_id,lokasi,ukuran,harga,catatan,status,tgl_pesan)
         VALUES ('$nama','$hp','$layanan_id','$lokasi','',0,'$catatan','baru',NOW())"
    );

    header("Location: ../proyek.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Tambah Pesanan Manual</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>

    <section class="py-4">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h3 class="mb-0">Tambah Pesanan Manual</h3>
            </div>

            <div class="card">
                <div class="card-body">
                    <form method="post" action="">
                        <div class="mb-3">
                            <label class="form-label">Nama Lengkap</label>
                            <input type="text" name="nama" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">No. HP / WhatsApp</label>
                            <input type="text" name="hp" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Layanan</label>
                            <select name="layanan_id" class="form-select" required>
                                <option value="">-- Pilih Layanan --</option>
                                <?php
              mysqli_data_seek($qLayanan, 0);
              while ($lay = mysqli_fetch_assoc($qLayanan)) {
                  echo '<option value="'.$lay['id'].'">'
                       .htmlspecialchars($lay['nama']).'</option>';
              }
              ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Lokasi / Alamat Lengkap</label>
                            <textarea name="lokasi" class="form-control" rows="2" required></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Catatan Tambahan</label>
                            <textarea name="catatan" class="form-control" rows="3"></textarea>
                        </div>

                        <button type="submit" class="btn btn-primary">Simpan Pesanan</button>
                        <a href="../proyek.php" class="btn btn-secondary">Batal</a>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>