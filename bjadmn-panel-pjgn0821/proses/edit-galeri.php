<?php
session_start();
require_once __DIR__ . "/../../config.php";
if (!isset($_SESSION['admin_login']) || $_SESSION['admin_login'] !== true) {
    header("Location: ../login.php");
    exit;
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$data = null;
$error = '';
$MAX_FOTO = 5;

if ($id > 0) {
    $stmt = $conn->prepare("SELECT * FROM galeri WHERE id = ? LIMIT 1");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $data = $stmt->get_result()->fetch_assoc();
    if (!$data) $error = "Data galeri tidak ditemukan.";
} else {
    $error = "ID galeri tidak valid.";
}

$qLayanan = mysqli_query($conn, "SELECT id, nama FROM layanan ORDER BY urutan ASC");

// Ambil semua foto tambahan proyek ini, urut sesuai kolom urutan
$fotoList = [];
if ($data) {
    $qFoto = $conn->prepare("SELECT * FROM galeri_foto WHERE galeri_id = ? ORDER BY urutan ASC, id ASC");
    $qFoto->bind_param("i", $id);
    $qFoto->execute();
    $resFoto = $qFoto->get_result();
    while ($row = $resFoto->fetch_assoc()) $fotoList[] = $row;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($error)) {
    $judul       = input_filter($conn, $_POST['judul']);
    $layanan_id  = ($_POST['layanan_id'] != '') ? (int)$_POST['layanan_id'] : null;
    $keterangan  = input_filter($conn, $_POST['keterangan']);
    $tgl_selesai = input_filter($conn, $_POST['tgl_selesai']);
    $harga = input_filter($conn, $_POST['harga'] ?? '');
    $hapusFotoIds = $_POST['hapus_foto'] ?? [];

    require_once __DIR__ . "/upload-helper.php";

    // 1. Hapus foto yang dicentang admin untuk dihapus
    $jumlahSaatIni = count($fotoList);
    foreach ($hapusFotoIds as $fotoId) {
        $fotoId = (int)$fotoId;
        $qCari = $conn->prepare("SELECT foto FROM galeri_foto WHERE id=? AND galeri_id=?");
        $qCari->bind_param("ii", $fotoId, $id);
        $qCari->execute();
        $fotoRow = $qCari->get_result()->fetch_assoc();

        if ($fotoRow) {
            $path = __DIR__ . '/../../assets/img/galeri/' . $fotoRow['foto'];
            if (file_exists($path)) unlink($path);

            $qHapus = $conn->prepare("DELETE FROM galeri_foto WHERE id=? AND galeri_id=?");
            $qHapus->bind_param("ii", $fotoId, $id);
            $qHapus->execute();
            $jumlahSaatIni--;
        }
    }

    // 2. Upload foto baru (jika ada), sepanjang total tidak melebihi MAX_FOTO
    $fileList = [];
    if (!empty($_FILES['foto_baru']['name'][0])) {
        $totalFile = count($_FILES['foto_baru']['name']);
        for ($i = 0; $i < $totalFile; $i++) {
            if ($_FILES['foto_baru']['error'][$i] === UPLOAD_ERR_NO_FILE) continue;
            $fileList[] = [
                'name'     => $_FILES['foto_baru']['name'][$i],
                'type'     => $_FILES['foto_baru']['type'][$i],
                'tmp_name' => $_FILES['foto_baru']['tmp_name'][$i],
                'error'    => $_FILES['foto_baru']['error'][$i],
                'size'     => $_FILES['foto_baru']['size'][$i],
            ];
        }
    }

    if (($jumlahSaatIni + count($fileList)) > $MAX_FOTO) {
        $error = "Total foto tidak boleh lebih dari $MAX_FOTO. Hapus beberapa foto lama dulu, atau upload lebih sedikit.";
    } else {
        foreach ($fileList as $file) {
            $result = upload_foto($file, __DIR__ . '/../../assets/img/galeri/');
            if ($result['error']) {
                $error = $result['error'];
                break;
            }
            $urutanBaru = $jumlahSaatIni;
            $stmtFoto = $conn->prepare("INSERT INTO galeri_foto (galeri_id, foto, urutan) VALUES (?, ?, ?)");
            $stmtFoto->bind_param("isi", $id, $result['file'], $urutanBaru);
            $stmtFoto->execute();
            $jumlahSaatIni++;
        }
    }

    if (empty($error) && $jumlahSaatIni < 1) {
    $error = "Minimal harus ada satu foto proyek. Upload foto baru jika semua foto lama dihapus.";
}

if (empty($error)) {
        // 3. Perbarui thumbnail utama (galeri.foto) supaya selalu ikut foto pertama yang masih ada
        $qUtama = $conn->prepare("SELECT foto FROM galeri_foto WHERE galeri_id=? ORDER BY urutan ASC, id ASC LIMIT 1");
        $qUtama->bind_param("i", $id);
        $qUtama->execute();
        $utamaRow = $qUtama->get_result()->fetch_assoc();
        $fotoUtama = $utamaRow ? $utamaRow['foto'] : $data['foto'];

        $stmt = $conn->prepare("
    UPDATE galeri 
    SET judul = ?, layanan_id = ?, keterangan = ?, tgl_selesai = ?, foto = ?, harga = ?
    WHERE id = ?
");

$stmt->bind_param(
    "sissssi",
    $judul,
    $layanan_id,
    $keterangan,
    $tgl_selesai,
    $fotoUtama,
    $harga,
    $id
);

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
    <title>Edit Galeri - Admin</title>
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

    .foto-existing-grid {
        display: grid;
        grid-template-columns: repeat(5, 1fr);
        gap: 8px;
        margin-bottom: 14px;
    }

    .foto-existing-item {
        position: relative;
        border-radius: 8px;
        overflow: hidden;
        border: 1.5px solid #e2e8f0;
        aspect-ratio: 1;
        background: #f8fafc;
    }

    .foto-existing-item img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }

    .foto-existing-item .badge-utama {
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        background: rgba(37, 99, 235, .9);
        color: #fff;
        font-size: .6rem;
        font-weight: 700;
        text-align: center;
        padding: 2px 0;
    }

    .foto-existing-item .chk-hapus {
        position: absolute;
        top: 4px;
        right: 4px;
        width: 20px;
        height: 20px;
        cursor: pointer;
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

    .sisa-slot {
        font-size: .76rem;
        color: #64748b;
        margin-top: 4px;
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

        <?php if ($data) { ?>
        <div class="form-card">
            <div class="form-card-title"><i class="bi bi-pencil-square me-2 text-primary"></i>Edit Galeri</div>
            <form method="post" enctype="multipart/form-data">
                <div class="mb-3">
                    <label class="form-label">Judul</label>
                    <input type="text" name="judul" class="form-control"
                        value="<?php echo htmlspecialchars($data['judul']); ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Layanan Terkait</label>
                    <select name="layanan_id" class="form-select">
                        <option value="">-- Pilih Layanan (opsional) --</option>
                        <?php
                        mysqli_data_seek($qLayanan, 0);
                        while ($l = mysqli_fetch_assoc($qLayanan)) {
                            $sel = ($data['layanan_id'] == $l['id']) ? 'selected' : '';
                            echo "<option value='" . $l['id'] . "' " . $sel . ">" . htmlspecialchars($l['nama']) . "</option>";
                        }
                        ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Keterangan Harga Model</label>

                    <input type="text" name="harga" class="form-control" placeholder="Contoh: Rp 4.000.000 per paket"
                        value="<?php echo htmlspecialchars($data['harga'] ?? ''); ?>">

                    <div class="harga-hint">
                        <i class="bi bi-pencil-square me-1"></i>
                        Opsional. Tulis harga sesuai model, misalnya “Rp 4.000.000 per paket”,
                        “Rp 750.000 per unit”, “Mulai Rp 2.500.000”, atau “Hubungi kami untuk harga”.
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Keterangan</label>
                    <textarea name="keterangan" class="form-control"
                        rows="3"><?php echo htmlspecialchars($data['keterangan']); ?></textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label">Tanggal Selesai</label>
                    <input type="date" name="tgl_selesai" class="form-control"
                        value="<?php echo htmlspecialchars($data['tgl_selesai']); ?>">
                </div>

                <div class="mb-3">
                    <label class="form-label">Foto Saat Ini
                        (<?php echo count($fotoList); ?>/<?php echo $MAX_FOTO; ?>)</label>
                    <?php if (!empty($fotoList)) { ?>
                    <div class="foto-existing-grid">
                        <?php foreach ($fotoList as $i => $f) { ?>
                        <div class="foto-existing-item">
                            <img src="../../assets/img/galeri/<?php echo htmlspecialchars($f['foto']); ?>"
                                alt="Foto <?php echo $i + 1; ?>">
                            <?php if ($i === 0) { ?><div class="badge-utama">Utama</div><?php } ?>
                            <input type="checkbox" name="hapus_foto[]" value="<?php echo $f['id']; ?>" class="chk-hapus"
                                title="Hapus foto ini">
                        </div>
                        <?php } ?>
                    </div>
                    <small class="text-muted">Centang ikon di pojok foto untuk menghapusnya saat disimpan.</small>
                    <?php } else { ?>
                    <p class="text-muted small">Belum ada foto.</p>
                    <?php } ?>
                </div>

                <div class="mb-4">
                    <label class="form-label">Tambah Foto Baru</label>
                    <input type="file" name="foto_baru[]" id="inputFotoBaru" class="form-control"
                        accept="image/jpeg,image/png,image/webp" multiple>
                    <div class="sisa-slot" id="sisaSlot">Total maksimal <?php echo $MAX_FOTO; ?> foto per proyek.</div>
                    <div class="foto-preview-grid" id="previewGrid"></div>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn-simpan"><i class="bi bi-floppy me-1"></i> Update</button>
                    <a href="../galeri.php" class="btn-batal">Batal</a>
                </div>
            </form>
        </div>
        <?php } ?>
    </div>

    <script>
    const inputFotoBaru = document.getElementById('inputFotoBaru');
    const previewGrid = document.getElementById('previewGrid');
    const MAX_FOTO = <?php echo $MAX_FOTO; ?>;
    const jumlahFotoAda = <?php echo count($fotoList); ?>;

    inputFotoBaru.addEventListener('change', function() {
        previewGrid.innerHTML = '';
        const sisaSlot = MAX_FOTO - jumlahFotoAda;
        const files = Array.from(inputFotoBaru.files).slice(0, Math.max(sisaSlot, 0));

        if (inputFotoBaru.files.length > sisaSlot) {
            alert('Sisa slot foto hanya ' + sisaSlot + '. Kelebihan file tidak akan diupload.');
        }

        files.forEach(function(file) {
            const reader = new window.FileReader();
            reader.onload = function(e) {
                const item = document.createElement('div');
                item.className = 'foto-preview-item';
                item.innerHTML = '<img src="' + e.target.result + '" alt="Preview baru">';
                previewGrid.appendChild(item);
            };
            reader.readAsDataURL(file);
        });
    });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>