<?php
session_start();
require_once __DIR__ . "/../../config.php";
if (!isset($_SESSION['admin_login']) || $_SESSION['admin_login'] !== true) {
    header("Location: ../login.php");
    exit;
}

$error = '';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$stmt = $conn->prepare("SELECT * FROM layanan WHERE id = ? LIMIT 1");
$stmt->bind_param("i", $id);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();

if (!$data) {
    $error = "Data layanan tidak ditemukan.";
}

// Ambil baris estimasi harga yang sudah ada untuk layanan ini
$estimasiList = [];
if ($data) {
    $qEst = $conn->prepare("SELECT * FROM layanan_estimasi WHERE layanan_id = ? ORDER BY urutan ASC, id ASC");
    $qEst->bind_param("i", $id);
    $qEst->execute();
    $resEst = $qEst->get_result();
    while ($row = $resEst->fetch_assoc()) {
        $estimasiList[] = $row;
    }
}

// ============================
// Proses update (saat form disubmit)
// ============================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($error)) {
    $nama      = input_filter($conn, $_POST['nama']);
    $harga     = (float)$_POST['harga'];
    $deskripsi = input_filter($conn, $_POST['deskripsi']);
    $urutan    = (int)$_POST['urutan'];
    $nama_file = $data['foto'];

    // Upload foto baru jika admin memilih file baru
    if (!empty($_FILES['foto']['name'])) {
        require_once __DIR__ . "/upload-helper.php";
        $result = upload_foto($_FILES['foto'], __DIR__ . '/../../assets/img/layanan/');

        if ($result['error']) {
            $error = $result['error'];
        } else {
            // Hapus foto lama supaya tidak menumpuk file sampah di server
            if ($data['foto'] && file_exists(__DIR__ . '/../../assets/img/layanan/' . $data['foto'])) {
                unlink(__DIR__ . '/../../assets/img/layanan/' . $data['foto']);
            }
            $nama_file = $result['file'];
        }
    }

    if (empty($error)) {
        // 1. Update data utama layanan
        $stmt = $conn->prepare("UPDATE layanan SET nama=?, harga=?, deskripsi=?, urutan=?, foto=? WHERE id=?");
        $stmt->bind_param("sdsssi", $nama, $harga, $deskripsi, $urutan, $nama_file, $id);
        $stmt->execute();

        // 2. Update baris estimasi: hapus semua baris lama, lalu insert ulang yang baru
        $del = $conn->prepare("DELETE FROM layanan_estimasi WHERE layanan_id = ?");
        $del->bind_param("i", $id);
        $del->execute();

        if (!empty($_POST['ukuran_model']) && is_array($_POST['ukuran_model'])) {
            $stmtEst = $conn->prepare("INSERT INTO layanan_estimasi (layanan_id, ukuran_model, estimasi_harga, urutan) VALUES (?, ?, ?, ?)");

            foreach ($_POST['ukuran_model'] as $i => $ukuran) {
                $ukuran = trim($ukuran);
                $hargaEst = trim($_POST['estimasi_harga'][$i] ?? '');

                // Lewati baris yang dikosongkan admin
                if ($ukuran === '' || $hargaEst === '') continue;

                $ukuranClean = input_filter($conn, $ukuran);
                $hargaEstClean = input_filter($conn, $hargaEst);
                $urutanEst = $i;

                $stmtEst->bind_param("issi", $id, $ukuranClean, $hargaEstClean, $urutanEst);
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

    .foto-lama {
        width: 100px;
        height: 70px;
        object-fit: cover;
        border-radius: 8px;
        border: 1px solid #e2e8f0;
        margin-bottom: 8px;
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
        grid-template-columns: 1fr 1fr auto;
        gap: 8px;
        margin-bottom: 8px;
        align-items: center;
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

        <?php if ($data) { ?>
        <div class="form-card">
            <div class="form-card-title"><i class="bi bi-pencil-square me-2 text-primary"></i>Edit Layanan</div>
            <form method="post" enctype="multipart/form-data" id="formLayanan">
                <div class="mb-3">
                    <label class="form-label">Nama Layanan</label>
                    <input type="text" name="nama" class="form-control"
                        value="<?php echo htmlspecialchars($_POST['nama'] ?? $data['nama']); ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Harga Mulai (Rp / m&sup2;)</label>
                    <input type="number" name="harga" class="form-control" min="0"
                        value="<?php echo htmlspecialchars($_POST['harga'] ?? $data['harga']); ?>" required>
                    <small class="text-muted">Ini yang tampil sebagai "Mulai Rp ... /m&sup2;" di halaman detail
                        layanan.</small>
                </div>

                <div class="mb-3">
                    <label class="form-label">Deskripsi</label>
                    <textarea name="deskripsi" class="form-control"
                        rows="6"><?php echo htmlspecialchars($_POST['deskripsi'] ?? $data['deskripsi']); ?></textarea>
                    <div class="format-hint">
                        <i class="bi bi-lightbulb me-1"></i>
                        <strong>Cara menulis deskripsi:</strong> baris pertama jadi ringkasan, baris yang diawali
                        <code>-</code> otomatis jadi daftar spesifikasi bercentang.
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Urutan</label>
                    <input type="number" name="urutan" class="form-control" min="0"
                        value="<?php echo htmlspecialchars($_POST['urutan'] ?? $data['urutan']); ?>">
                    <small class="text-muted">Angka kecil tampil lebih dulu.</small>
                </div>

                <div class="mb-4">
                    <label class="form-label">Foto Saat Ini</label><br>
                    <?php if ($data['foto']) { ?>
                    <img src="../../assets/img/layanan/<?php echo htmlspecialchars($data['foto']); ?>" class="foto-lama"
                        alt="Foto saat ini">
                    <?php } ?>
                    <input type="file" name="foto" class="form-control" accept="image/jpeg,image/png,image/webp">
                    <small class="text-muted">Kosongkan jika tidak ingin mengganti foto. JPG, PNG, atau WebP,
                        maksimal 3MB.</small>
                </div>

                <hr class="my-4">

                <div class="mb-2">
                    <div class="section-title-sm"><i class="bi bi-cash-coin me-1 text-primary"></i>Estimasi Harga per
                        Ukuran/Model</div>
                    <small class="text-muted">Ubah, hapus, atau tambah baris sesuai kebutuhan.</small>
                </div>

                <div class="estimasi-box" id="estimasiBox">
                    <div id="estimasiRows"></div>
                    <button type="button" class="btn-tambah-baris" id="btnTambahBaris">
                        <i class="bi bi-plus-lg"></i> Tambah Baris Estimasi
                    </button>
                </div>

                <div class="d-flex gap-2 mt-4">
                    <button type="submit" class="btn-simpan"><i class="bi bi-floppy me-1"></i> Simpan
                        Perubahan</button>
                    <a href="../layanan.php" class="btn-batal">Batal</a>
                </div>
            </form>
        </div>
        <?php } ?>
    </div>

    <script>
    const estimasiRows = document.getElementById('estimasiRows');
    const btnTambahBaris = document.getElementById('btnTambahBaris');

    function tambahBarisEstimasi(ukuran, harga) {
        ukuran = ukuran || '';
        harga = harga || '';

        const row = document.createElement('div');
        row.className = 'estimasi-row';
        row.innerHTML =
            '<input type="text" name="ukuran_model[]" class="form-control form-control-sm" ' +
            'placeholder="Contoh: Model standar, ukuran kecil" value="' + ukuran.replace(/"/g, '&quot;') + '">' +
            '<input type="text" name="estimasi_harga[]" class="form-control form-control-sm" ' +
            'placeholder="Contoh: Rp 400.000 / m2" value="' + harga.replace(/"/g, '&quot;') + '">' +
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

    // Muat baris estimasi yang sudah ada di database
    const dataEstimasiLama = <?php echo json_encode($estimasiList); ?>;

    if (dataEstimasiLama.length > 0) {
        dataEstimasiLama.forEach(function(e) {
            tambahBarisEstimasi(e.ukuran_model, e.estimasi_harga);
        });
    } else {
        tambahBarisEstimasi();
    }
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>