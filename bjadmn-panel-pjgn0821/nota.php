<?php
require_once __DIR__ . "/../config.php";

if (!isset($_GET['id'])) {
    die('ID tidak ditemukan');
}

$id = (int)$_GET['id'];

$q  = mysqli_query($conn, "
    SELECT p.*, l.nama AS layanan_nama
    FROM proyek p
    LEFT JOIN layanan l ON p.layanan_id = l.id
    WHERE p.id = $id
");
$data = mysqli_fetch_assoc($q);

if (!$data) {
    die('Pesanan tidak ditemukan');
}

$waNumber = preg_replace('/[^0-9]/', '', $data['hp']);
$notaUrl  = 'https://domainmu.com/nota.php?id=' . $data['id'];
$waText   = urlencode("Berikut nota pesanan Anda:\n" . $notaUrl);
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Nota Pesanan #<?= $data['id']; ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <style>
    body {
        padding: 20px;
    }

    .nota-box {
        max-width: 700px;
        margin: 0 auto;
        border: 1px solid #ccc;
        padding: 20px;
    }

    .nota-header {
        border-bottom: 1px solid #ccc;
        margin-bottom: 15px;
        padding-bottom: 10px;
    }

    .nota-footer {
        border-top: 1px solid #ccc;
        margin-top: 15px;
        padding-top: 10px;
        font-size: 12px;
    }
    </style>
</head>

<body>

    <div class="nota-box">
        <div class="nota-header d-flex justify-content-between">
            <div>
                <h4>Bengkel Las Berkah Jaya</h4>
                <small>Pejagoan, Kec. Pejagoan, Kabupaten Kebumen, Jawa Tengah</small><br>
                <small>No. HP bengkel</small>
            </div>
            <div class="text-end">
                <h5>Nota Pesanan</h5>
                <small>No: #<?= $data['id']; ?></small><br>
                <small>Tgl Pesan: <?= $data['tgl_pesan']; ?></small>
            </div>
        </div>

        <div class="mb-3">
            <h6>Data Pelanggan</h6>
            <table class="table table-sm">
                <tr>
                    <th style="width:150px;">Nama</th>
                    <td><?= htmlspecialchars($data['nama']); ?></td>
                </tr>
                <tr>
                    <th>HP / WA</th>
                    <td><?= htmlspecialchars($data['hp']); ?></td>
                </tr>
                <tr>
                    <th>Lokasi</th>
                    <td><?= nl2br(htmlspecialchars($data['lokasi'])); ?></td>
                </tr>
            </table>
        </div>

        <div class="mb-3">
            <h6>Detail Pesanan</h6>
            <table class="table table-sm">
                <tr>
                    <th style="width:150px;">Layanan</th>
                    <td><?= htmlspecialchars($data['layanan_nama']); ?></td>
                </tr>
                <tr>
                    <th>Ukuran</th>
                    <td><?= htmlspecialchars($data['ukuran']); ?></td>
                </tr>
                <tr>
                    <th>Harga</th>
                    <td>Rp <?= number_format((float)$data['harga'], 0, ',', '.'); ?></td>
                </tr>
                <tr>
                    <th>Status</th>
                    <td><?= htmlspecialchars($data['status']); ?></td>
                </tr>
                <?php if (!empty($data['catatan'])): ?>
                <tr>
                    <th>Catatan</th>
                    <td><?= nl2br(htmlspecialchars($data['catatan'])); ?></td>
                </tr>
                <?php endif; ?>
            </table>
        </div>

        <div class="nota-footer d-flex justify-content-between">
            <div>
                <small>Terima kasih sudah mempercayakan pekerjaan kepada kami.</small>
            </div>
            <div class="text-end">
                <small>Admin, Bengkel Las Berkah Jaya</small>
            </div>
        </div>
    </div>

    <div class="text-center mt-3">
        <button class="btn btn-sm btn-primary" onclick="window.print()">Print</button>
        <a href="nota-pdf.php?id=<?= $data['id']; ?>" class="btn btn-sm btn-secondary">
            Download PDF
        </a>
        <a href="https://wa.me/<?= $waNumber; ?>?text=<?= $waText; ?>" class="btn btn-sm btn-success" target="_blank">
            Kirim via WhatsApp
        </a>
    </div>

</body>

</html>