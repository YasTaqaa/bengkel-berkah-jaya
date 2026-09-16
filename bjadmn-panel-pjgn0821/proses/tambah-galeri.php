<?php
session_start();
require_once __DIR__ . "/../../config.php";
if (!isset($_SESSION['admin_login']) || $_SESSION['admin_login'] !== true) {
    header("Location: ../login.php");
    exit;
}

$qLayanan = mysqli_query($conn, "SELECT id, nama FROM layanan ORDER BY urutan ASC");
$error = '';
$MAX_FOTO = 5;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $judul       = input_filter($conn, $_POST['judul']);
    $layanan_id  = ($_POST['layanan_id'] != '') ? (int)$_POST['layanan_id'] : null;
    $keterangan  = input_filter($conn, $_POST['keterangan']);
    $tgl_selesai = input_filter($conn, $_POST['tgl_selesai']);
    $harga       = !empty($_POST['harga']) ? (int)$_POST['harga'] : null;

    // Susun ulang array $_FILES['foto'] (multiple) jadi list per-file, buang slot kosong
    $fileList = [];
    if (!empty($_FILES['foto']['name'][0])) {
        $totalFile = count($_FILES['foto']['name']);
        for ($i = 0; $i < $totalFile; $i++) {
            if ($_FILES['foto']['error'][$i] === UPLOAD_ERR_NO_FILE) continue;
            $fileList[] = [
                'name'     => $_FILES['foto']['name'][$i],
                'type'     => $_FILES['foto']['type'][$i],
                'tmp_name' => $_FILES['foto']['tmp_name'][$i],
                'error'    => $_FILES['foto']['error'][$i],
                'size'     => $_FILES['foto']['size'][$i],
            ];
        }
    }

    if (empty($fileList)) {
        $error = "Minimal 1 foto wajib diupload.";
    } elseif (count($fileList) > $MAX_FOTO) {
        $error = "Maksimal $MAX_FOTO foto per proyek.";
    } else {
        require_once __DIR__ . "/upload-helper.php";
        $uploadedFiles = [];

        foreach ($fileList as $file) {
            $result = upload_foto($file, __DIR__ . '/../../assets/img/galeri/');
            if ($result['error']) {
                $error = $result['error'];
                break;
            }
            $uploadedFiles[] = $result['file'];
        }

        if (empty($error) && !empty($uploadedFiles)) {
            $fotoUtama = $uploadedFiles[0];

            $stmt = $conn->prepare("INSERT INTO galeri (judul, layanan_id, keterangan, tgl_selesai, foto, harga) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sisssi", $judul, $layanan_id, $keterangan, $tgl_selesai, $fotoUtama, $harga);
            $stmt->execute();
            $galeri_id = $stmt->insert_id;

            $stmtFoto = $conn->prepare("INSERT INTO galeri_foto (galeri_id, foto, urutan) VALUES (?, ?, ?)");
            foreach ($uploadedFiles as $urutan => $fotoFile) {
                $stmtFoto->bind_param("isi", $galeri_id, $fotoFile, $urutan);
                $stmtFoto->execute();
            }

            header("Location: ../galeri.php");
            exit;
        }
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
        font-size: .9rem;
    }

    .form-card {
        background: #fff;
        border-radius: 14px;
        box-shadow: 0 2px 12px rgba(0, 0, 0, .06);
        padding: 28px;
        width: 100%;
        max-width: 560px;
    }

    .form-card-title {
        font-weight: 700;
        font-size: .95rem;
        color: #0f172a;
        margin-bottom: 20px;
        padding-bottom: 14px;
        border-bottom: 1px solid #f1f5f9;
    }

    .form-label {
        font-weight: 600;
        font-size: .82rem;
        color: #475569;
        margin-bottom: 5px;
    }

    .form-control,
    .form-select {
        border-radius: 8px;
        border: 1.5px solid #e2e8f0;
        font-size: .88rem;
    }

    .form-control:focus,
    .form-select:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, .1);
    }

    .foto-preview-grid {
        display: grid;
        grid-template-columns: repeat(5, 1fr);
        gap: 8px;
        margin-top: 10px;
    }

    .foto-preview-item {
        position: relative;
        border-radius: 8px;
        overflow: hidden;
        border: 1.5px solid #e2e8f0;
        aspect-ratio: 1;
        background: #f8fafc;
    }

    .foto-preview-item img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }

    .foto-preview-item .badge-utama {
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        background: rgba(37, 99, 235, .9);
        color: #fff;
        font-size: .62rem;
        font-weight: 700;
        text-align: center;
        padding: 2px 0;
    }

    .harga-hint {
        background: #f0f9ff;
        border: 1px solid #bae6fd;
        border-radius: 8px;
        padding: 8px 12px;
        font-size: .76rem;
        color: #0369a1;
        margin-top: 6px;
    }

    .btn-simpan {
        background: #2563eb;
        color: #fff;
        border: none;
        border-radius: 8px;
        padding: 9px 22px;
        font-weight: 600;
        font-size: .88rem;
        cursor: pointer;
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
        font-size: .88rem;
        text-decoration: none;
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
        <?php if ($error) { ?>
        <div class="alert alert-danger d-flex align-items-center gap-2 mb-3"
            style="max-width:560px; width:100%; font-size:0.85rem; border-radius:8px">
            <i class="bi bi-exclamation-circle-fill"></i> <?php echo htmlspecialchars($error); ?>
        </div>
        <?php } ?>

        <div class="form-card">
            <div class="form-card-title"><i class="bi bi-plus-circle me-2 text-primary"></i>Tambah Galeri</div>
            <form method="post" enctype="multipart/form-data" id="formGaleri">
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
                            echo "<option value='" . $l['id'] . "' " . $sel . ">" . htmlspecialchars($l['nama']) . "</option>";
                        }
                        ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Harga Khusus untuk Model Ini (Rp / m&sup2;)</label>
                    <input type="number" name="harga" class="form-control" min="0"
                        value="<?php echo htmlspecialchars($_POST['harga'] ?? ''); ?>">
                    <div class="harga-hint"><i class="bi bi-lightbulb me-1"></i>Opsional. Isi angka saja, contoh
                        <code>450000</code>.</div>
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
                    <label class="form-label">Foto Proyek <span class="text-danger">*</span></label>
                    <input type="file" name="foto[]" id="inputFoto" class="form-control"
                        accept="image/jpeg,image/png,image/webp" multiple required>
                    <small class="text-muted">Pilih 1 sampai 5 foto sekaligus (tahan Ctrl saat memilih). Foto pertama
                        jadi
                        thumbnail utama. JPG, PNG, atau WebP, maksimal 3MB per foto.</small>
                    <div class="foto-preview-grid" id="previewGrid"></div>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn-simpan"><i class="bi bi-floppy me-1"></i> Simpan</button>
                    <a href="../galeri.php" class="btn-batal">Batal</a>
                </div>
            </form>
        </div>
    </div>

    <script>
    const inputFoto = document.getElementById('inputFoto');
    const previewGrid = document.getElementById('previewGrid');
    const MAX_FOTO = <?php echo $MAX_FOTO; ?>;

    inputFoto.addEventListener('change', function() {
        previewGrid.innerHTML = '';
        const files = Array.from(inputFoto.files).slice(0, MAX_FOTO);

        if (inputFoto.files.length > MAX_FOTO) {
            alert('Maksimal ' + MAX_FOTO + ' foto. Hanya ' + MAX_FOTO + ' foto pertama yang akan dipakai.');
        }

        files.forEach(function(file, i) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const item = document.createElement('div');
                item.className = 'foto-preview-item';
                item.innerHTML = '<img src="' + e.target.result + '" alt="Preview ' + (i + 1) +
                    '">' +
                    (i === 0 ? '<div class="badge-utama">Utama</div>' : '');
                previewGrid.appendChild(item);
            };
            reader.readAsDataURL(file);
        });
    });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>