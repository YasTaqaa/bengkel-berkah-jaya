<?php
require_once __DIR__ . "/../config.php";

if (!isset($_GET['id'])) die("ID tidak ditemukan");
$id = (int)$_GET['id'];

$q = mysqli_query($conn, "SELECT p.*, l.nama AS layanan_nama, g.judul AS nama_model
                           FROM proyek p
                           LEFT JOIN layanan l ON p.layanan_id = l.id
                           LEFT JOIN galeri g ON p.galeri_id = g.id
                           WHERE p.id=$id");
$data = mysqli_fetch_assoc($q);
if (!$data) die("Pesanan tidak ditemukan");

// Link di catatan tetap bisa diklik langsung (nota dibuka standalone/print, tidak pakai modal JS).
function linkify(string $text): string {
    $text = htmlspecialchars($text);
    $text = preg_replace_callback('/https?:\/\/\S+/i', function ($m) {
        return '<a href="' . $m[0] . '" target="_blank" rel="noopener" style="color:#2563eb; text-decoration:underline; word-break:break-all;">' . $m[0] . '</a>';
    }, $text);
    return $text;
}

// Hitung total harga dari ukuran (format "3 x 4") dikali harga per m².
// Kalau ukuran tidak berformat panjang x tinggi, anggap harga tersimpan sudah berupa total.
function hitungTotalHarga(string $ukuran, float $hargaPerM2): array {
    $ukuran = strtolower(trim($ukuran));
    $ukuran = str_replace(',', '.', $ukuran);

    if (preg_match('/^([\d.]+)\s*[x×*]\s*([\d.]+)/', $ukuran, $m)) {
        $panjang = (float)$m[1];
        $tinggi  = (float)$m[2];
        $luas    = $panjang * $tinggi;
        return ['total' => $luas * (float)$hargaPerM2, 'luas' => $luas];
    }

    return ['total' => (float)$hargaPerM2, 'luas' => null];
}

$hasilHarga = hitungTotalHarga($data['ukuran'], $data['harga']);
$totalHarga = $hasilHarga['total'];
$luasM2     = $hasilHarga['luas'];

$badgeMap = [
    'baru'    => ['#fef3c7', '#b45309'],
    'proses'  => ['#ede9fe', '#6d28d9'],
    'selesai' => ['#dcfce7', '#15803d'],
    'batal'   => ['#fee2e2', '#b91c1c'],
];
$status = strtolower($data['status']);
$badgeColor = $badgeMap[$status] ?? ['#f1f5f9', '#64748b'];

$waNumber = preg_replace('/[^0-9]/', '', $data['hp']);
$notaUrl  = "https://domainmu.com/nota.php?id=" . $data['id'];
$waText   = urlencode("Berikut nota pesanan Anda: " . $notaUrl);
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Nota Pesanan #<?php echo $data['id']; ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <style>
    body {
        background: #f1f5f9;
        font-family: system-ui, sans-serif;
        padding: 30px 15px;
    }

    .nota-box {
        max-width: 720px;
        margin: 0 auto;
        background: #fff;
        border-radius: 16px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        padding: 32px;
    }

    .nota-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        border-bottom: 2px solid #f1f5f9;
        padding-bottom: 18px;
        margin-bottom: 22px;
        flex-wrap: wrap;
        gap: 12px;
    }

    .nota-header h4 {
        font-weight: 800;
        color: #0f172a;
        margin-bottom: 4px;
    }

    .nota-header small {
        color: #64748b;
    }

    .nota-no {
        text-align: right;
    }

    .nota-no h5 {
        font-weight: 700;
        color: #0f172a;
        margin-bottom: 4px;
    }

    .section-title {
        font-size: 0.8rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: #94a3b8;
        margin-bottom: 10px;
        margin-top: 24px;
    }

    .info-table {
        width: 100%;
        border-collapse: collapse;
    }

    .info-table tr {
        border-bottom: 1px solid #f1f5f9;
    }

    .info-table tr:last-child {
        border-bottom: none;
    }

    .info-table th {
        text-align: left;
        width: 150px;
        padding: 10px 0;
        color: #64748b;
        font-weight: 600;
        font-size: 0.87rem;
        vertical-align: top;
    }

    .info-table td {
        padding: 10px 0;
        color: #0f172a;
        font-size: 0.9rem;
        word-break: break-word;
    }

    .badge-status {
        display: inline-block;
        padding: 4px 14px;
        border-radius: 50px;
        font-size: 0.78rem;
        font-weight: 700;
        text-transform: capitalize;
        background: <?php echo $badgeColor[0];
        ?>;
        color: <?php echo $badgeColor[1];
        ?>;
    }

    .harga-highlight {
        font-size: 1.15rem;
        font-weight: 800;
        color: #2563eb;
    }

    .harga-rincian {
        font-size: 0.8rem;
        color: #64748b;
        margin-top: 3px;
    }

    .nota-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-top: 2px solid #f1f5f9;
        margin-top: 26px;
        padding-top: 16px;
        font-size: 0.82rem;
        color: #64748b;
        flex-wrap: wrap;
        gap: 8px;
    }

    .btn-area {
        text-align: center;
        margin-top: 20px;
        display: flex;
        justify-content: center;
        gap: 10px;
        flex-wrap: wrap;
    }

    .btn-area .btn {
        border-radius: 8px;
        font-weight: 600;
        font-size: 0.85rem;
        padding: 8px 18px;
    }
    </style>
</head>

<body>
    <div class="nota-box">
        <div class="nota-header">
            <div>
                <h4>Bengkel Las Berkah Jaya</h4>
                <small>Pejagoan, Kec. Pejagoan, Kabupaten Kebumen, Jawa Tengah</small><br>
                <small><i class="bi bi-whatsapp me-1"></i>No. WA bengkel: +62 812-3456-7890</small>
            </div>
            <div class="nota-no">
                <h5>Nota Pesanan</h5>
                <small>No: #<?php echo $data['id']; ?></small><br>
                <small>Tgl Pesan: <?php echo $data['tgl_pesan']; ?></small>
            </div>
        </div>

        <div class="section-title">Data Pelanggan</div>
        <table class="info-table">
            <tr>
                <th>Nama</th>
                <td><?php echo htmlspecialchars($data['nama']); ?></td>
            </tr>
            <tr>
                <th>WA</th>
                <td><?php echo htmlspecialchars($data['hp']); ?></td>
            </tr>
            <tr>
                <th>Lokasi</th>
                <td><?php echo nl2br(htmlspecialchars($data['lokasi'])); ?></td>
            </tr>
        </table>

        <div class="section-title">Detail Pesanan</div>
        <table class="info-table">
            <tr>
                <th>Layanan</th>
                <td><?php echo htmlspecialchars($data['layanan_nama']); ?></td>
            </tr>
            <tr>
                <th>Model</th>
                <td><?php echo $data['nama_model'] ? htmlspecialchars($data['nama_model']) : '-'; ?></td>
            </tr>
            <tr>
                <th>Ukuran</th>
                <td><?php echo $data['ukuran'] ? htmlspecialchars($data['ukuran']) : '-'; ?></td>
            </tr>
            <tr>
                <th>Harga</th>
                <td>
                    <span class="harga-highlight">Rp <?php echo number_format($totalHarga, 0, ',', '.'); ?></span>
                    <?php if ($luasM2 !== null && (float)$data['harga'] > 0) { ?>
                    <div class="harga-rincian">
                        <?php echo str_replace('.', ',', $luasM2) . ' m² &times; Rp ' . number_format((float)$data['harga'], 0, ',', '.') . ' /m²'; ?>
                    </div>
                    <?php } ?>
                </td>
            </tr>
            <tr>
                <th>Status</th>
                <td><span class="badge-status"><?php echo htmlspecialchars($data['status']); ?></span></td>
            </tr>
            <?php if (!empty($data['catatan'])) { ?>
            <tr>
                <th>Catatan</th>
                <td><?php echo nl2br(linkify($data['catatan'])); ?></td>
            </tr>
            <?php } ?>
        </table>

        <div class="nota-footer">
            <span>Terima kasih sudah mempercayakan pekerjaan kepada kami.</span>
            <strong>Admin, Bengkel Las Berkah Jaya</strong>
        </div>
    </div>

    <div class="btn-area">
        <a href="nota-pdf.php?id=<?php echo $data['id']; ?>" class="btn btn-secondary">
            <i class="bi bi-file-earmark-pdf me-1"></i>Generate PDF
        </a>
        <a href="https://wa.me/<?php echo $waNumber; ?>?text=<?php echo $waText; ?>" class="btn btn-success"
            target="_blank">
            <i class="bi bi-whatsapp me-1"></i>Kirim via WhatsApp
        </a>
    </div>
</body>

</html>