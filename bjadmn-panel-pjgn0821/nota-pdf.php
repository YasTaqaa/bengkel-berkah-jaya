<?php
require_once __DIR__ . "/../config.php";
require_once __DIR__ . "/../vendor/autoload.php";

use Dompdf\Dompdf;
use Dompdf\Options;

if (!isset($_GET['id'])) die("ID tidak ditemukan");
$id = (int)$_GET['id'];

$q = mysqli_query($conn, "SELECT p.*, l.nama AS layanan_nama, g.judul AS nama_model
                           FROM proyek p
                           LEFT JOIN layanan l ON p.layanan_id = l.id
                           LEFT JOIN galeri g ON p.galeri_id = g.id
                           WHERE p.id=$id");
$data = mysqli_fetch_assoc($q);
if (!$data) die("Pesanan tidak ditemukan");

function linkifyPdf(string $text): string {
    $text = htmlspecialchars($text);
    return preg_replace_callback('/https?:\/\/\S+/i', function ($m) {
        return '<a href="' . $m[0] . '" style="color:#2563eb; word-break:break-all;">' . $m[0] . '</a>';
    }, $text);
}

// Hitung total: ukuran panjang x tinggi dikalikan harga per m².
function hitungTotalHargaPdf(string $ukuran, float $hargaPerM2): array {
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

$hasilHarga = hitungTotalHargaPdf($data['ukuran'], $data['harga']);
$totalHarga = $hasilHarga['total'];
$luasM2 = $hasilHarga['luas'];

$badgeMap = [
    'baru'    => ['#fef3c7', '#b45309'],
    'proses'  => ['#ede9fe', '#6d28d9'],
    'selesai' => ['#dcfce7', '#15803d'],
    'batal'   => ['#fee2e2', '#b91c1c'],
];
$status = strtolower($data['status']);
$badge = $badgeMap[$status] ?? ['#f1f5f9', '#64748b'];

$html = '<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<style>
body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #1f2937; }
.box { border: 1px solid #e2e8f0; border-radius: 8px; padding: 18px; }
.header { border-bottom: 2px solid #f1f5f9; margin-bottom: 14px; padding-bottom: 10px; }
h4 { margin: 14px 0 6px 0; font-size: 12px; text-transform: uppercase; letter-spacing: .5px; color: #64748b; }
table { width: 100%; border-collapse: collapse; }
th, td { padding: 7px 4px; text-align: left; vertical-align: top; border-bottom: 1px solid #f1f5f9; }
th { width: 120px; color: #475569; }
.badge { display:inline-block; padding:2px 10px; border-radius:10px; font-size:11px; font-weight:bold; color:' . $badge[1] . '; background:' . $badge[0] . '; }
.harga { font-size:14px; font-weight:bold; color:#2563eb; }
.rincian { font-size:10px; color:#64748b; margin-top:3px; }
.footer { margin-top:16px; font-size:10px; border-top:1px solid #e2e8f0; padding-top:8px; }
</style>
</head>
<body>
<div class="box">
<div class="header">
<table><tr>
<td><strong>Bengkel Las Berkah Jaya</strong><br><small>Pejagoan, Kec. Pejagoan, Kabupaten Kebumen, Jawa Tengah</small><br><small>No. WA bengkel: +62 812-3456-7890</small></td>
<td style="text-align:right"><strong>Nota Pesanan</strong><br><small>No: #' . $data['id'] . '</small><br><small>Tgl Pesan: ' . htmlspecialchars($data['tgl_pesan']) . '</small></td>
</tr></table>
</div>

<h4>Data Pelanggan</h4>
<table>
<tr><th>Nama</th><td>' . htmlspecialchars($data['nama']) . '</td></tr>
<tr><th>WA</th><td>' . htmlspecialchars($data['hp']) . '</td></tr>
<tr><th>Lokasi</th><td>' . nl2br(htmlspecialchars($data['lokasi'])) . '</td></tr>
</table>

<h4>Detail Pesanan</h4>
<table>
<tr><th>Layanan</th><td>' . htmlspecialchars($data['layanan_nama']) . '</td></tr>
<tr><th>Model</th><td>' . ($data['nama_model'] ? htmlspecialchars($data['nama_model']) : '-') . '</td></tr>
<tr><th>Ukuran</th><td>' . ($data['ukuran'] ? htmlspecialchars($data['ukuran']) : '-') . '</td></tr>
<tr><th>Harga</th><td><span class="harga">Rp ' . number_format($totalHarga, 0, ',', '.') . '</span>';

if ($luasM2 !== null && (float)$data['harga'] > 0) {
    $html .= '<div class="rincian">' . str_replace('.', ',', $luasM2) . ' m² × Rp ' . number_format((float)$data['harga'], 0, ',', '.') . ' /m²</div>';
}

$html .= '</td></tr>
<tr><th>Status</th><td><span class="badge">' . htmlspecialchars($data['status']) . '</span></td></tr>';

if (!empty($data['catatan'])) {
    $html .= '<tr><th>Catatan</th><td>' . nl2br(linkifyPdf($data['catatan'])) . '</td></tr>';
}

$html .= '</table>
<div class="footer">Terima kasih sudah mempercayakan pekerjaan kepada kami.<span style="float:right"><strong>Admin, Bengkel Las Berkah Jaya</strong></span></div>
</div>
</body>
</html>';

$options = new Options();
$options->set('isRemoteEnabled', true);
$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$dompdf->stream('nota-pesanan-' . $data['id'] . '.pdf', ['Attachment' => true]);