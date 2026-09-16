<?php
require_once __DIR__ . "/../config.php";
require_once __DIR__ . "/../vendor/autoload.php";

use Dompdf\Dompdf;
use Dompdf\Options;

if (!isset($_SESSION['admin_login']) || $_SESSION['admin_login'] !== true) {
    header("Location: login.php");
    exit;
}

function hitungTotalHargaExport(string $ukuran, float|int|string $hargaPerM2): float {
    $ukuran = strtolower(trim($ukuran));
    $ukuran = str_replace(',', '.', $ukuran);

    if (preg_match('/^([\d.]+)\s*[x×*]\s*([\d.]+)/', $ukuran, $m)) {
        return (float)$m[1] * (float)$m[2] * (float)$hargaPerM2;
    }

    return (float)$hargaPerM2;
}

$dari   = isset($_GET['dari']) ? trim($_GET['dari']) : '';
$sampai = isset($_GET['sampai']) ? trim($_GET['sampai']) : '';

$where = "1=1";
if ($dari !== '') {
    $where .= " AND DATE(p.tgl_pesan) >= '" . mysqli_real_escape_string($conn, $dari) . "'";
}
if ($sampai !== '') {
    $where .= " AND DATE(p.tgl_pesan) <= '" . mysqli_real_escape_string($conn, $sampai) . "'";
}

$list = mysqli_query($conn, "SELECT p.*, l.nama AS nama_layanan, g.judul AS nama_model
                              FROM proyek p
                              LEFT JOIN layanan l ON p.layanan_id = l.id
                              LEFT JOIN galeri g ON p.galeri_id = g.id
                              WHERE $where
                              ORDER BY p.tgl_pesan DESC, p.id DESC");

$rows = [];
while ($p = mysqli_fetch_assoc($list)) {
    $p['harga_total'] = hitungTotalHargaExport($p['ukuran'] ?? '', $p['harga'] ?? 0);
    $rows[] = $p;
}

$periode = 'Semua Tanggal';
if ($dari !== '' && $sampai !== '') $periode = 'Periode ' . $dari . ' s/d ' . $sampai;
elseif ($dari !== '') $periode = 'Dari ' . $dari;
elseif ($sampai !== '') $periode = 'Sampai ' . $sampai;

$esc = function (string $value): string {
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
};

$html = '<!DOCTYPE html><html lang="id"><head><meta charset="UTF-8"><style>
body{font-family:DejaVu Sans,sans-serif;font-size:9px;color:#1f2937}
h1{text-align:center;color:#1b4332;font-size:17px;margin:0 0 5px}
.sub{text-align:center;color:#475569;font-size:10px;margin-bottom:3px}
table{width:100%;border-collapse:collapse;margin-top:16px}
th{background:#2e7d32;color:#fff;padding:7px 4px;border:1px solid #fff;text-align:center;font-size:8px}
td{padding:6px 4px;border:1px solid #e2e8f0;vertical-align:top;word-wrap:break-word}
tr:nth-child(even) td{background:#f8fafc}
.harga{text-align:right;white-space:nowrap}
.footer{margin-top:12px;text-align:right;color:#64748b;font-size:8px}
</style></head><body>
<h1>LAPORAN RIWAYAT PESANAN</h1>
<div class="sub">' . $esc($periode) . '</div>
<div class="sub">Total Data: ' . count($rows) . '</div>
<table><thead><tr>
<th>No</th><th>Nama</th><th>No HP</th><th>Layanan</th><th>Model</th><th>Lokasi</th><th>Catatan</th><th>Ukuran</th><th>Harga Total</th><th>Status</th><th>Tgl Pesan</th><th>Tgl Selesai</th>
</tr></thead><tbody>';

$no = 1;
foreach ($rows as $p) {
    $status = ucfirst($p['status'] ?? '-');
    $catatan = trim((string)($p['catatan'] ?? ''));
    $html .= '<tr>';
    $html .= '<td style="text-align:center">' . $no++ . '</td>';
    $html .= '<td>' . $esc((string)($p['nama'] ?? '-')) . '</td>';
    $html .= '<td>' . $esc((string)($p['hp'] ?? '-')) . '</td>';
    $html .= '<td>' . $esc((string)($p['nama_layanan'] ?? '-')) . '</td>';
    $html .= '<td>' . $esc((string)($p['nama_model'] ?? '-')) . '</td>';
    $html .= '<td>' . nl2br($esc((string)($p['lokasi'] ?? '-'))) . '</td>';
    $html .= '<td>' . ($catatan !== '' ? nl2br($esc($catatan)) : '-') . '</td>';
    $html .= '<td>' . $esc((string)($p['ukuran'] ?? '-')) . '</td>';
    $html .= '<td class="harga">Rp ' . number_format($p['harga_total'], 0, ',', '.') . '</td>';
    $html .= '<td>' . $esc($status) . '</td>';
    $html .= '<td>' . $esc((string)($p['tgl_pesan'] ?? '-')) . '</td>';
    $html .= '<td>' . $esc((string)($p['tgl_selesai'] ?? '-')) . '</td>';
    $html .= '</tr>';
}

$html .= '</tbody></table><div class="footer">Bengkel Las Berkah Jaya</div></body></html>';

$options = new Options();
$options->set('isRemoteEnabled', true);
$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'landscape');
$dompdf->render();
$dompdf->stream('riwayat-pesanan-' . date('YmdHis') . '.pdf', ['Attachment' => true]);
