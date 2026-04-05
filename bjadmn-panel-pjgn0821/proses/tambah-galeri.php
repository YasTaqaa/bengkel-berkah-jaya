<?php
session_start();
require_once __DIR__ . "/../../config.php";

if (!isset($_SESSION['admin_login']) || $_SESSION['admin_login'] !== true) {
    header("Location: ../login.php");
    exit;
}

$qLayanan = mysqli_query($conn, "SELECT id, nama FROM layanan ORDER BY urutan ASC");
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $judul       = input_filter($conn, $_POST['judul']);
    $layanan_id  = $_POST['layanan_id'] !== '' ? (int)$_POST['layanan_id'] : null;
    $keterangan  = input_filter($conn, $_POST['keterangan']);
    $tgl_selesai = input_filter($conn, $_POST['tgl_selesai']);

    // Upload foto
    require_once __DIR__ . "/upload-helper.php";
    $result = upload_foto($_FILES['foto'], __DIR__ . '/../../assets/img/galeri/');

    if ($result['error']) {
        $error = $result['error'];
    } elseif (empty($result['file'])) {
        $error = 'Foto wajib diupload.';
    } else {
        $foto = $result['file'];

        $stmt = $conn->prepare("INSERT INTO galeri (judul, layanan_id, keterangan, tgl_selesai, foto) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sisss", $judul, $layanan_id, $keterangan, $tgl_selesai, $foto);
        $stmt->execute();

        header("Location: ../galeri.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Tambah Galeri - Admin</title>
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
                <i class="bi bi-plus-circle me-2 text-primary"></i>Tambah Galeri
            </div>

            <form method="post" enctype="multipart/form-data">

                <div class="mb-3">
                    <label class="form-label">Judul</label>
                    <input type="text" name="judul" class="form-control"
                        value="<?php echo htmlspecialchars($_POST['judul'] ?? ''); ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Layanan Terkait</label>
                    <select name="layanan_id" class="form-select">
                        <option value="">-- Pilih Layanan (opsional) --</option>
                        <?php
                        mysqli_data_seek($qLayanan, 0);
                        while ($l = mysqli_fetch_assoc($qLayanan)) {
                            $sel = (isset($_POST['layanan_id']) && $_POST['layanan_id'] == $l['id']) ? 'selected' : '';
                            echo '<option value="'.$l['id'].'" '.$sel.'>'.htmlspecialchars($l['nama']).'</option>';
                        }
                        ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Keterangan</label>
                    <textarea name="keterangan" class="form-control"
                        rows="3"><?php echo htmlspecialchars($_POST['keterangan'] ?? ''); ?></textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label">Tanggal Selesai</label>
                    <input type="date" name="tgl_selesai" class="form-control"
                        value="<?php echo htmlspecialchars($_POST['tgl_selesai'] ?? ''); ?>">
                </div>

                <div class="mb-4">
                    <label class="form-label">Foto <span class="text-danger">*</span></label>
                    <input type="file" name="foto" class="form-control" accept="image/jpeg,image/png,image/webp"
                        required>
                    <small class="text-muted">JPG, PNG, atau WebP. Maksimal 3MB.</small>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn-simpan">
                        <i class="bi bi-floppy me-1"></i> Simpan
                    </button>
                    <a href="../galeri.php" class="btn-batal">Batal</a>
                </div>

            </form>
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>