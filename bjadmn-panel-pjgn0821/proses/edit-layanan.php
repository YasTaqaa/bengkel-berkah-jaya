<?php
session_start();
require_once __DIR__ . "/../../config.php";

if (!isset($_SESSION['admin_login']) || $_SESSION['admin_login'] !== true) {
    header("Location: ../login.php");
    exit;
}

$id    = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$error = '';

// Ambil data
$stmt = $conn->prepare("SELECT * FROM layanan WHERE id = ? LIMIT 1");
$stmt->bind_param("i", $id);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();

if (!$data) {
    $error = "Data layanan tidak ditemukan.";
}

// Proses update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($error)) {
    $nama      = input_filter($conn, $_POST['nama']);
    $harga     = (float)$_POST['harga'];
    $deskripsi = input_filter($conn, $_POST['deskripsi']);
    $urutan    = (int)$_POST['urutan'];
    $nama_file = $data['foto'];

    // Upload foto baru jika ada
    if (!empty($_FILES['foto']['name'])) {
        require_once __DIR__ . "/upload-helper.php";
        $result = upload_foto($_FILES['foto'], __DIR__ . '/../../assets/img/layanan/');
        if ($result['error']) {
            $error = $result['error'];
        } else {
            // Hapus foto lama
            if ($data['foto'] && file_exists(__DIR__ . '/../../assets/img/layanan/' . $data['foto'])) {
                unlink(__DIR__ . '/../../assets/img/layanan/' . $data['foto']);
            }
            $nama_file = $result['file'];
        }
    }

    if (empty($error)) {
        $stmt = $conn->prepare("UPDATE layanan SET nama=?, harga=?, deskripsi=?, urutan=?, foto=? WHERE id=?");
        $stmt->bind_param("sdsssi", $nama, $harga, $deskripsi, $urutan, $nama_file, $id);
        $stmt->execute();

        header("Location: ../layanan.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Edit Layanan - Admin</title>
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

    .foto-preview {
        width: 140px;
        height: 90px;
        object-fit: cover;
        border-radius: 8px;
        border: 1px solid #e2e8f0;
        margin-bottom: 10px;
        display: block;
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

        <?php if (!empty($data)): ?>
        <div class="form-card">
            <div class="form-card-title">
                <i class="bi bi-pencil-square me-2 text-primary"></i>Edit Layanan
            </div>

            <form method="post" action="edit-layanan.php?id=<?php echo $data['id']; ?>" enctype="multipart/form-data">

                <div class="mb-3">
                    <label class="form-label">Nama Layanan</label>
                    <input type="text" name="nama" class="form-control"
                        value="<?php echo htmlspecialchars($data['nama']); ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Harga (Rp)</label>
                    <input type="number" name="harga" class="form-control" min="0" value="<?php echo $data['harga']; ?>"
                        required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Deskripsi</label>
                    <textarea name="deskripsi" class="form-control"
                        rows="3"><?php echo htmlspecialchars($data['deskripsi']); ?></textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label">Urutan</label>
                    <input type="number" name="urutan" class="form-control" min="0"
                        value="<?php echo $data['urutan']; ?>">
                    <small class="text-muted">Angka kecil tampil lebih dulu.</small>
                </div>

                <div class="mb-4">
                    <label class="form-label">Foto</label>
                    <?php if ($data['foto']): ?>
                    <img src="<?php echo '../../assets/img/layanan/' . htmlspecialchars($data['foto']); ?>"
                        class="foto-preview" alt="Foto saat ini">
                    <?php endif; ?>
                    <input type="file" name="foto" class="form-control" accept="image/jpeg,image/png,image/webp">
                    <small class="text-muted">Kosongkan jika tidak mengganti. JPG, PNG, WebP. Maks 3MB.</small>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn-simpan">
                        <i class="bi bi-floppy me-1"></i> Update
                    </button>
                    <a href="../layanan.php" class="btn-batal">Batal</a>
                </div>

            </form>
        </div>
        <?php endif; ?>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>