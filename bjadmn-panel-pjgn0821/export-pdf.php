<?php
// Tangkap output apapun (whitespace/BOM/warning) yang mungkin nyelip dari file
// lain (misal config.php), supaya tidak merusak isi PDF yang dikirim ke browser.
ob_start();

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

if (!isset($_SESSION['admin_login']) || $_SESSION['admin_login'] !== true) {
    header('Location: login.php');
    exit;
}

function hitungTotalHargaExport(string $ukuran, float|int|string $hargaPerM2): float
{
    $ukuran = strtolower(trim($ukuran));
    $ukuran = str_replace(',', '.', $ukuran);

    if (preg_match('/([\d.]+)\s*x\s*([\d.]+)/', $ukuran, $m)) {
        return (float) $m[1] * (float) $m[2] * (float) $hargaPerM2;
    }

    return (float) $hargaPerM2;
}

$dari = isset($_GET['dari']) ? trim($_GET['dari']) : '';
$sampai = isset($_GET['sampai']) ? trim($_GET['sampai']) : '';
$status = isset($_GET['status']) ? trim($_GET['status']) : '';

$where = '1=1';
if ($dari !== '') {
    $where .= " AND DATE(p.tgl_pesan) >= '" . mysqli_real_escape_string($conn, $dari) . "'";
}
if ($sampai !== '') {
    $where .= " AND DATE(p.tgl_pesan) <= '" . mysqli_real_escape_string($conn, $sampai) . "'";
}
if ($status !== '') {
    $where .= " AND p.status = '" . mysqli_real_escape_string($conn, $status) . "'";
}

$list = mysqli_query($conn, "
    SELECT p.*, l.nama AS nama_layanan, g.judul AS nama_model
    FROM proyek p
    LEFT JOIN layanan l ON p.layanan_id = l.id
    LEFT JOIN galeri g ON p.galeri_id = g.id
    WHERE $where
    ORDER BY p.tgl_pesan DESC, p.id DESC
");

$rows = [];
$grandTotal = 0;
while ($p = mysqli_fetch_assoc($list)) {
    $p['harga_total'] = hitungTotalHargaExport($p['ukuran'] ?? '', $p['harga'] ?? 0);
    $grandTotal += $p['harga_total'];
    $rows[] = $p;
}

$periode = 'Semua Tanggal';
if ($dari !== '' && $sampai !== '') {
    $periode = 'Periode ' . $dari . ' s/d ' . $sampai;
} elseif ($dari !== '') {
    $periode = 'Dari ' . $dari;
} elseif ($sampai !== '') {
    $periode = 'Sampai ' . $sampai;
}

$namaAdmin = $_SESSION['admin_nama'] ?? ($_SESSION['admin_username'] ?? 'Admin');
$tglCetak = date('d-m-Y H:i');

$esc = function (?string $value): string {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
};

$html = '<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<style>
    body { font-family: "DejaVu Sans", sans-serif; font-size: 9px; color: #1f2937; }

    .kop { width: 100%; border-bottom: 3px solid #1b4332; padding-bottom: 10px; margin-bottom: 14px; }
    .kop table { width: 100%; border: none; }
    .kop td { border: none; padding: 0; vertical-align: middle; }
    .kop .nama-toko { font-size: 18px; font-weight: bold; color: #1b4332; margin: 0; }
    .kop .alamat { font-size: 9px; color: #475569; margin: 2px 0 0 0; }
    .kop .kontak { font-size: 9px; color: #475569; margin: 2px 0 0 0; }
    .kop .kanan { text-align: right; font-size: 9px; color: #64748b; }

    h1.judul { text-align: center; color: #1b4332; font-size: 15px; margin: 4px 0 2px 0; letter-spacing: .5px; }
    .sub { text-align: center; color: #475569; font-size: 10px; margin-bottom: 3px; }

    table.data { width: 100%; border-collapse: collapse; margin-top: 14px; }
    table.data th { background: #2e7d32; color: #fff; padding: 7px 4px; border: 1px solid #fff; text-align: center; font-size: 8px; }
    table.data td { padding: 6px 4px; border: 1px solid #e2e8f0; vertical-align: top; word-wrap: break-word; }
    table.data tr:nth-child(even) td { background: #f8fafc; }
    .harga { text-align: right; white-space: nowrap; }

    .total-row td { background: #eef7ee !important; font-weight: bold; border-top: 2px solid #2e7d32; }

    .footer-info { margin-top: 10px; text-align: right; color: #64748b; font-size: 8px; }

    .ttd-wrap { width: 100%; margin-top: 40px; }
    .ttd-wrap table { width: 100%; border: none; }
    .ttd-wrap td { border: none; text-align: center; font-size: 9px; vertical-align: top; }
    .ttd-box { height: 60px; }
    .ttd-nama { margin-top: 4px; font-weight: bold; text-decoration: underline; }
    .ttd-jabatan { color: #64748b; font-size: 8px; }

    .footer-doc { margin-top: 20px; text-align: center; color: #94a3b8; font-size: 7px; border-top: 1px solid #e2e8f0; padding-top: 6px; }
</style>
</head>
<body>

<div class="kop">
    <table>
        <tr>
            <td style="width:70%;">
                <p class="nama-toko">Bengkel Las Berkah Jaya</p>
                <p class="alamat">Pejagoan, Kec. Pejagoan, Kabupaten Kebumen, Jawa Tengah</p>
                <p class="kontak">No. WA bengkel: +62 812-3456-7890</p>
            </td>
            <td class="kanan" style="width:30%;">
                Dicetak: ' . $esc($tglCetak) . '<br>
                Oleh: ' . $esc($namaAdmin) . '
            </td>
        </tr>
    </table>
</div>

<h1 class="judul">LAPORAN RIWAYAT PESANAN</h1>
<div class="sub">' . $esc($periode) . '</div>
<div class="sub">Total Data: ' . count($rows) . '</div>

<table class="data">
<thead>
<tr>
    <th>No</th><th>Nama</th><th>No HP</th><th>Layanan</th><th>Model</th>
    <th>Lokasi</th><th>Catatan</th><th>Ukuran</th><th>Harga Total</th>
    <th>Status</th><th>Tgl Pesan</th><th>Tgl Selesai</th>
</tr>
</thead>
<tbody>';

$no = 1;
foreach ($rows as $p) {
    $statusLabel = ucfirst($p['status'] ?? '-');
    $catatan = trim((string) ($p['catatan'] ?? ''));

    $html .= '<tr>
        <td style="text-align:center;">' . $no++ . '</td>
        <td>' . $esc($p['nama'] ?? '-') . '</td>
        <td>' . $esc($p['hp'] ?? '-') . '</td>
        <td>' . $esc($p['nama_layanan'] ?? '-') . '</td>
        <td>' . $esc($p['nama_model'] ?? '-') . '</td>
        <td>' . nl2br($esc($p['lokasi'] ?? '-')) . '</td>
        <td>' . ($catatan !== '' ? nl2br($esc($catatan)) : '-') . '</td>
        <td>' . $esc($p['ukuran'] ?? '-') . '</td>
        <td class="harga">Rp ' . number_format($p['harga_total'], 0, ',', '.') . '</td>
        <td>' . $esc($statusLabel) . '</td>
        <td>' . $esc($p['tgl_pesan'] ?? '-') . '</td>
        <td>' . $esc($p['tgl_selesai'] ?? '-') . '</td>
    </tr>';
}

$html .= '<tr class="total-row">
    <td colspan="8" style="text-align:right;">GRAND TOTAL</td>
    <td class="harga">Rp ' . number_format($grandTotal, 0, ',', '.') . '</td>
    <td colspan="3"></td>
</tr>';

$html .= '</tbody>
</table>

<div class="ttd-wrap">
    <table>
        <tr>
            <td style="width:60%;"></td>
            <td style="width:40%;">
                Kebumen, ' . $esc($tglCetak) . '<br>
                Admin Bengkel Las Berkah Jaya
                <div class="ttd-box"></div>
                <div class="ttd-nama">' . $esc($namaAdmin) . '</div>
                <div class="ttd-jabatan">Admin Berkah Jaya</div>
            </td>
        </tr>
    </table>
</div>

<div class="footer-doc">
    Dokumen ini dicetak otomatis oleh sistem Bengkel Las Berkah Jaya dan sah tanpa cap basah selama terdapat tanda tangan admin.
</div>

</body>
</html>';

$options = new Options();
$options->set('isRemoteEnabled', true);

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'landscape');
$dompdf->render();

// Buang semua output yang sempat tertampung di buffer (whitespace/warning dari
// file lain) sebelum PDF dikirim, supaya file PDF tidak korup dan bisa
// dirender langsung (preview) di iframe.
if (ob_get_length()) {
    ob_end_clean();
}

// Preview dulu di tab browser. Attachment di-set false supaya tidak langsung terdownload.
$dompdf->stream('riwayat-pesanan-' . date('Ymd_His') . '.pdf', ['Attachment' => false]);