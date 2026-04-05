<?php
session_start();
require_once __DIR__ . "/../../config.php";

if (!isset($_SESSION['admin_login']) || $_SESSION['admin_login'] !== true) {
    header("Location: ../login.php");
    exit;
}

$qLayanan = mysqli_query($conn, "SELECT * FROM layanan ORDER BY urutan ASC");
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama       = input_filter($conn, $_POST['nama']);
    $hp         = input_filter($conn, $_POST['hp']);
    $layanan_id = (int)$_POST['layanan_id'];
    $lokasi     = input_filter($conn, $_POST['lokasi']);
    $catatan    = input_filter($conn, $_POST['catatan']);

    if (empty($nama) || empty($hp) || empty($layanan_id) || empty($lokasi)) {
        $error = 'Semua field wajib diisi.';
    } else {
        $stmt = $conn->prepare("INSERT INTO proyek (nama, hp, layanan_id, lokasi, ukuran, harga, catatan, status, tgl_pesan)
                                VALUES (?, ?, ?, ?, '', 0, ?, 'baru', NOW())");
        $stmt->bind_param("ssiss", $nama, $hp, $layanan_id, $lokasi, $catatan);
        $stmt->execute();

        header("Location: ../proyek.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Tambah Pesanan Manual - Admin</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <style>
    body {
        background: #f1f5f9;
        font-family: system-ui, sans-serif;
        font-size: 0.9rem;
    }

    .form-card {
        background: #fff;
        border-radius: 14px;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.06);
        padding: 28px;
        width: 100%;
        max-width: 560px;
    }

    .form-card-title {
        font-weight: 700;
        font-size: 0.95rem;
        color: #0f172a;
        margin-bottom: 20px;
        padding-bottom: 14px;
        border-bottom: 1px solid #f1f5f9;
    }

    .form-label {
        font-weight: 600;
        font-size: 0.82rem;
        color: #475569;
        margin-bottom: 5px;
    }

    .form-control,
    .form-select {
        border-radius: 8px;
        border: 1.5px solid #e2e8f0;
        font-size: 0.88rem;
        transition: border-color 0.2s, box-shadow 0.2s;
    }

    .form-control:focus,
    .form-select:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
    }

    .btn-simpan {
        background: #2563eb;
        color: #fff;
        border: none;
        border-radius: 8px;
        padding: 9px 22px;
        font-weight: 600;
        font-size: 0.88rem;
        cursor: pointer;
        transition: background 0.2s;
    }

    .btn-simpan:hover {
        background: #1d4ed8;
    }

    .btn-batal {
        background: #f1f5f9;
        color: #475569;
        border: none;
        border-radius: 8px;
        padding: 9px 22px;
        font-weight: 600;
        font-size: 0.88rem;
        text-decoration: none;
        transition: background 0.2s;
    }

    .btn-batal:hover {
        background: #e2e8f0;
        color: #1e293b;
    }
    </style>
</head>

<body>
    <?php include "../navbar-menu.php"; ?>

    <div class="container-fluid px-3 px-md-4 py-4 d-flex flex-column align-items-center">

        <?php if ($error): ?>
        <div class="alert alert-danger d-flex align-items-center gap-2 mb-3"
            style="max-width:560px; width:100%; font-size:0.85rem; border-radius:8px;">
            <i class="bi bi-exclamation-circle-fill"></i>
            <?php echo htmlspecialchars($error); ?>
        </div>
        <?php endif; ?>

        <div class="form-card">
            <div class="form-card-title">
                <i class="bi bi-plus-circle me-2 text-primary"></i>Tambah Pesanan Manual
            </div>

            <form method="post" action="">

                <div class="mb-3">
                    <label class="form-label">Nama Lengkap</label>
                    <input type="text" name="nama" class="form-control"
                        value="<?php echo htmlspecialchars($_POST['nama'] ?? ''); ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">No. HP / WhatsApp</label>
                    <input type="text" name="hp" class="form-control"
                        value="<?php echo htmlspecialchars($_POST['hp'] ?? ''); ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Layanan</label>
                    <select name="layanan_id" class="form-select" required>
                        <option value="">-- Pilih Layanan --</option>
                        <?php
                        mysqli_data_seek($qLayanan, 0);
                        while ($lay = mysqli_fetch_assoc($qLayanan)) {
                            $sel = (isset($_POST['layanan_id']) && $_POST['layanan_id'] == $lay['id']) ? 'selected' : '';
                            echo '<option value="'.$lay['id'].'" '.$sel.'>'.htmlspecialchars($lay['nama']).'</option>';
                        }
                        ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Lokasi / Alamat Lengkap</label>
                    <textarea name="lokasi" class="form-control" rows="2"
                        required><?php echo htmlspecialchars($_POST['lokasi'] ?? ''); ?></textarea>
                </div>

                <div class="mb-4">
                    <label class="form-label">Catatan Tambahan <span
                            class="text-muted fw-normal">(opsional)</span></label>
                    <textarea name="catatan" class="form-control"
                        rows="3"><?php echo htmlspecialchars($_POST['catatan'] ?? ''); ?></textarea>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn-simpan">
                        <i class="bi bi-floppy me-1"></i> Simpan Pesanan
                    </button>
                    <a href="../proyek.php" class="btn-batal">Batal</a>
                </div>

            </form>
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>