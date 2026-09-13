<?php
session_start();
require_once __DIR__ . "/../../config.php";
if (!isset($_SESSION['admin_login']) || $_SESSION['admin_login'] !== true) {
    header("Location: ../login.php");
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama      = input_filter($conn, $_POST['nama']);
    $harga     = (int)$_POST['harga'];
    $deskripsi = input_filter($conn, $_POST['deskripsi']);
    $urutan    = !empty($_POST['urutan']) ? (int)$_POST['urutan'] : 0;

    require_once __DIR__ . "/upload-helper.php";
    $result = upload_foto($_FILES['foto'], __DIR__ . '/../../assets/img/layanan/');

    if ($result['error']) {
        $error = $result['error'];
    } elseif (empty($result['file'])) {
        $error = "Foto layanan wajib diupload.";
    } else {
        $foto = $result['file'];

        $stmt = $conn->prepare("INSERT INTO layanan (nama, harga, deskripsi, urutan, foto) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sisss", $nama, $harga, $deskripsi, $urutan, $foto);
        $stmt->execute();
        $layanan_id = $stmt->insert_id;

        // Simpan baris estimasi harga (hanya angka nominal, format ditambahkan saat ditampilkan)
        if (!empty($_POST['ukuran_model']) && is_array($_POST['ukuran_model'])) {
            $stmtEst = $conn->prepare("INSERT INTO layanan_estimasi (layanan_id, ukuran_model, estimasi_harga, urutan) VALUES (?, ?, ?, ?)");

            foreach ($_POST['ukuran_model'] as $i => $ukuran) {
                $ukuran = trim($ukuran);
                $nominal = trim($_POST['estimasi_harga'][$i] ?? '');

                if ($ukuran === '' || $nominal === '') continue;

                $ukuranClean = input_filter($conn, $ukuran);
                $nominalClean = (int)$nominal;
                $urutanEst = $i;

                $stmtEst->bind_param("isii", $layanan_id, $ukuranClean, $nominalClean, $urutanEst);
                $stmtEst->execute();
            }
        }

        header("Location: ../layanan.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Tambah Layanan - Admin</title>
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
        max-width: 680px;
    }

    .form-card-title {
        font-weight: 700;
        font-size: 0.95rem;
        color: #0f172a;
        margin-bottom: 20px;
        padding-bottom: 14px;
        border-bottom: 1px solid #f1f5f9;
    }

    .section-title-sm {
        font-weight: 700;
        font-size: 0.85rem;
        color: #0f172a;
        margin-bottom: 4px;
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

    .format-hint {
        background: #f0f9ff;
        border: 1px solid #bae6fd;
        border-radius: 8px;
        padding: 10px 12px;
        font-size: 0.78rem;
        color: #0369a1;
        margin-top: 6px;
        line-height: 1.6;
    }

    .format-hint code {
        background: #e0f2fe;
        padding: 1px 5px;
        border-radius: 4px;
        color: #0c4a6e;
    }

    .estimasi-box {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 16px;
        margin-bottom: 20px;
    }

    .estimasi-row {
        display: grid;
        grid-template-columns: 1.3fr 1fr auto;
        gap: 8px;
        margin-bottom: 8px;
        align-items: center;
    }

    .estimasi-prefix {
        position: relative;
    }

    .estimasi-prefix .prefix-text {
        position: absolute;
        left: 10px;
        top: 50%;
        transform: translateY(-50%);
        font-size: 0.82rem;
        color: #64748b;
        pointer-events: none;
    }

    .estimasi-prefix input {
        padding-left: 32px;
    }

    .estimasi-suffix {
        position: relative;
    }

    .estimasi-suffix .suffix-text {
        position: absolute;
        right: 10px;
        top: 50%;
        transform: translateY(-50%);
        font-size: 0.78rem;
        color: #64748b;
        pointer-events: none;
    }

    .btn-hapus-baris {
        background: #fee2e2;
        color: #dc2626;
        border: none;
        border-radius: 7px;
        width: 34px;
        height: 34px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .btn-hapus-baris:hover {
        background: #fecaca;
    }

    .btn-tambah-baris {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: #eff6ff;
        color: #2563eb;
        border: 1px dashed #93c5fd;
        border-radius: 8px;
        padding: 7px 14px;
        font-size: 0.82rem;
        font-weight: 600;
        cursor: pointer;
        transition: background 0.2s;
    }

    .btn-tambah-baris:hover {
        background: #dbeafe;
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

    @media (max-width: 575.98px) {
        .estimasi-row {
            grid-template-columns: 1fr;
        }

        .btn-hapus-baris {
            width: 100%;
        }
    }
    </style>
</head>

<body>
    <?php include "../navbar-menu.php"; ?>
    <div class="container-fluid px-3 px-md-4 py-4 d-flex flex-column align-items-center">
        <?php if ($error) { ?>
        <div class="alert alert-danger d-flex align-items-center gap-2 mb-3"
            style="max-width:680px; width:100%; font-size:0.85rem; border-radius:8px">
            <i class="bi bi-exclamation-circle-fill"></i> <?php echo htmlspecialchars($error); ?>
        </div>
        <?php } ?>

        <div class="form-card">
            <div class="form-card-title"><i class="bi bi-plus-circle me-2 text-primary"></i>Tambah Layanan</div>
            <form method="post" enctype="multipart/form-data">
                <div class="mb-3">
                    <label class="form-label">Nama Layanan</label>
                    <input type="text" name="nama" class="form-control"
                        value="<?php echo htmlspecialchars($_POST['nama'] ?? ''); ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Harga Mulai (Rp / m&sup2;)</label>
                    <input type="number" name="harga" class="form-control" min="0"
                        value="<?php echo htmlspecialchars($_POST['harga'] ?? ''); ?>" required>
                    <small class="text-muted">Cukup isi angka, contoh <code>400000</code>. Nanti otomatis tampil
                        "Mulai Rp 400.000 / m&sup2;".</small>
                </div>

                <div class="mb-3">
                    <label class="form-label">Deskripsi</label>
                    <textarea name="deskripsi" class="form-control"
                        rows="6"><?php echo htmlspecialchars($_POST['deskripsi'] ?? ''); ?></textarea>
                    <div class="format-hint">
                        <i class="bi bi-lightbulb me-1"></i>
                        <strong>Cara menulis deskripsi:</strong> baris pertama jadi ringkasan, baris yang diawali
                        <code>-</code> otomatis jadi daftar spesifikasi bercentang.
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Urutan</label>
                    <input type="number" name="urutan" class="form-control" min="0"
                        value="<?php echo htmlspecialchars($_POST['urutan'] ?? '0'); ?>">
                    <small class="text-muted">Angka kecil tampil lebih dulu.</small>
                </div>

                <div class="mb-4">
                    <label class="form-label">Foto <span class="text-danger">*</span></label>
                    <input type="file" name="foto" class="form-control" accept="image/jpeg,image/png,image/webp"
                        required>
                    <small class="text-muted">JPG, PNG, atau WebP. Maksimal 3MB.</small>
                </div>

                <hr class="my-4">

                <div class="mb-2">
                    <div class="section-title-sm"><i class="bi bi-cash-coin me-1 text-primary"></i>Estimasi Harga per
                        Ukuran/Model</div>
                    <small class="text-muted">Isi angka nominalnya saja, format "Rp ... / m&sup2;" otomatis
                        ditambahkan saat tampil di halaman depan.</small>
                </div>

                <div class="estimasi-box" id="estimasiBox">
                    <div id="estimasiRows"></div>
                    <button type="button" class="btn-tambah-baris" id="btnTambahBaris">
                        <i class="bi bi-plus-lg"></i> Tambah Baris Estimasi
                    </button>
                </div>

                <div class="d-flex gap-2 mt-4">
                    <button type="submit" class="btn-simpan"><i class="bi bi-floppy me-1"></i> Simpan</button>
                    <a href="../layanan.php" class="btn-batal">Batal</a>
                </div>
            </form>
        </div>
    </div>

    <script>
    const estimasiRows = document.getElementById('estimasiRows');
    const btnTambahBaris = document.getElementById('btnTambahBaris');

    function tambahBarisEstimasi(ukuran, nominal) {
        ukuran = ukuran || '';
        nominal = nominal || '';

        const row = document.createElement('div');
        row.className = 'estimasi-row';
        row.innerHTML =
            '<input type="text" name="ukuran_model[]" class="form-control form-control-sm" ' +
            'placeholder="Contoh: Model standar, ukuran kecil" value="' + ukuran.replace(/"/g, '&quot;') + '">' +
            '<div class="estimasi-prefix estimasi-suffix">' +
            '<span class="prefix-text">Rp</span>' +
            '<input type="number" name="estimasi_harga[]" class="form-control form-control-sm" min="0" ' +
            'placeholder="400000" value="' + nominal.replace(/"/g, '&quot;') + '">' +
            '<span class="suffix-text">/ m2</span>' +
            '</div>' +
            '<button type="button" class="btn-hapus-baris" title="Hapus baris">' +
            '<i class="bi bi-trash"></i>' +
            '</button>';

        row.querySelector('.btn-hapus-baris').addEventListener('click', function() {
            row.remove();
        });

        estimasiRows.appendChild(row);
    }

    btnTambahBaris.addEventListener('click', function() {
        tambahBarisEstimasi();
    });

    tambahBarisEstimasi();
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>