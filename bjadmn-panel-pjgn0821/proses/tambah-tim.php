<?php
session_start();
require_once __DIR__ . "/../../config.php";

if (!isset($_SESSION['admin_login']) || $_SESSION['admin_login'] !== true) {
    header("Location: ../login.php");
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama       = input_filter($conn, $_POST['nama']);
    $jabatan    = input_filter($conn, $_POST['jabatan']);
    $pengalaman = (int)$_POST['pengalaman'];
    $urutan     = $_POST['urutan'] !== '' ? (int)$_POST['urutan'] : 0;

    require_once __DIR__ . "/upload-helper.php";
    $result = upload_foto($_FILES['foto'], __DIR__ . '/../../assets/img/tim/');

    if ($result['error']) {
        $error = $result['error'];
    } else {
        $foto = $result['file'];

        $stmt = $conn->prepare("INSERT INTO karyawan (nama, jabatan, pengalaman, urutan, foto) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("ssiss", $nama, $jabatan, $pengalaman, $urutan, $foto);
        $stmt->execute();

        header("Location: ../tim.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Tambah Anggota Tim - Admin</title>
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
                <i class="bi bi-plus-circle me-2 text-primary"></i>Tambah Anggota Tim
            </div>

            <form method="post" enctype="multipart/form-data">

                <div class="mb-3">
                    <label class="form-label">Nama</label>
                    <input type="text" name="nama" class="form-control"
                        value="<?php echo htmlspecialchars($_POST['nama'] ?? ''); ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Jabatan</label>
                    <select name="jabatan" class="form-select" required>
                        <option value="">-- Pilih Jabatan --</option>
                        <?php foreach (['Owner', 'Karyawan'] as $j): ?>
                        <option value="<?php echo $j; ?>"
                            <?php echo (($_POST['jabatan'] ?? '') === $j) ? 'selected' : ''; ?>>
                            <?php echo $j; ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Pengalaman (tahun)</label>
                    <input type="number" name="pengalaman" class="form-control" min="0"
                        value="<?php echo htmlspecialchars($_POST['pengalaman'] ?? '0'); ?>">
                </div>

                <div class="mb-3">
                    <label class="form-label">Urutan</label>
                    <input type="number" name="urutan" class="form-control" min="0"
                        value="<?php echo htmlspecialchars($_POST['urutan'] ?? '0'); ?>">
                    <small class="text-muted">Angka kecil tampil lebih dulu.</small>
                </div>

                <div class="mb-4">
                    <label class="form-label">Foto <span class="text-muted fw-normal">(opsional)</span></label>
                    <input type="file" name="foto" class="form-control" accept="image/jpeg,image/png,image/webp">
                    <small class="text-muted">JPG, PNG, atau WebP. Maksimal 3MB.</small>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn-simpan">
                        <i class="bi bi-floppy me-1"></i> Simpan
                    </button>
                    <a href="../tim.php" class="btn-batal">Batal</a>
                </div>

            </form>
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>