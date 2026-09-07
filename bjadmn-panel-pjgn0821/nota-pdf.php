<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../vendor/autoload.php';
use Dompdf\Dompdf;
use Dompdf\Options;

if (!isset($_GET['id'])) die("ID tidak ditemukan");
$id = (int)$_GET['id'];

$q = mysqli_query($conn, "SELECT p.*, l.nama AS layanan_nama, g.judul AS nama_model
FROM proyek p
LEFT JOIN layanan l ON p.layanan_id=l.id
LEFT JOIN galeri g ON p.galeri_id=g.id
WHERE p.id=$id");
$data = mysqli_fetch_assoc($q);
if (!$data) die("Pesanan tidak ditemukan");

function linkify_pdf(string $text): string {
    $text = htmlspecialchars($text);
    $text = preg_replace_callback('/(https?:\/\/\S+)/i', function($m) {
        return '<a href="'.$m[1].'" style="color:#2563eb; word-break:break-all;">'.$m[1].'</a>';
    }, $text);
    return $text;
}

$badge_map = [
    'baru' => 'background:#fef3c7;color:#b45309;',
    'proses' => 'background:#ede9fe;color:#6d28d9;',
    'selesai' => 'background:#dcfce7;color:#15803d;',
    'batal' => 'background:#fee2e2;color:#b91c1c;',
];
$status = strtolower($data['status']);
$badge_style = $badge_map[$status] ?? 'background:#f1f5f9;color:#64748b;';

$html = '
<html>
<head>
<meta charset="UTF-8">
<style>
body { font-family: "DejaVu Sans", sans-serif; font-size: 12px; color: #1f2937; }
.box { border: 1px solid #e2e8f0; border-radius: 8px; padding: 18px; }
.header { border-bottom: 2px solid #f1f5f9; margin-bottom: 14px; padding-bottom: 10px; }
h4 { margin: 14px 0 6px 0; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px; color: #64748b; }
table { width: 100%; border-collapse: collapse; }
th, td { padding: 5px 4px; text-align: left; vertical-align: top; }
th { width: 120px; color: #475569; }
.badge { display: inline-block; padding: 2px 10px; border-radius: 10px; font-size: 11px; font-weight: bold; text-transform: capitalize; '.$badge_style.' }
.harga { font-size: 14px; font-weight: bold; color: #2563eb; }
.footer { margin-top: 16px; font-size: 10px; border-top: 1px solid #e2e8f0; padding-top: 8px; }
</style>
</head>
<body>
<div class="box">
  <div class="header">
    <table>
      <tr>
        <td>
          <strong>Bengkel Las Berkah Jaya</strong><br>
          <small>Pejagoan, Kec. Pejagoan, Kabupaten Kebumen, Jawa Tengah</small><br>
          <small>No. WA bengkel : +62 812‑3456‑7890</small>
        </td>
        <td style="text-align:right">
          <strong>Nota Pesanan</strong><br>
          <small>No: #'. $data['id'] .'</small><br>
          <small>Tgl Pesan: '. $data['tgl_pesan'] .'</small>
        </td>
      </tr>
    </table>
  </div>

  <h4>Data Pelanggan</h4>
  <table>
    <tr><th>Nama</th><td>'. htmlspecialchars($data['nama']) .'</td></tr>
    <tr><th>WA</th><td>'. htmlspecialchars($data['hp']) .'</td></tr>
    <tr><th>Lokasi</th><td>'. nl2br(htmlspecialchars($data['lokasi'])) .'</td></tr>
  </table>

  <h4>Detail Pesanan</h4>
  <table>
    <tr><th>Layanan</th><td>'. htmlspecialchars($data['layanan_nama']) .'</td></tr>
    <tr><th>Model</th><td>'. ($data['nama_model'] ? htmlspecialchars($data['nama_model']) : '-') .'</td></tr>
    <tr><th>Ukuran</th><td>'. htmlspecialchars($data['ukuran'] ?: '-') .'</td></tr>
    <tr><th>Harga</th><td><span class="harga">Rp '. number_format((float)$data['harga'],0,',','.') .'</span></td></tr>
    <tr><th>Status</th><td><span class="badge">'. htmlspecialchars($data['status']) .'</span></td></tr>';

if (!empty($data['catatan'])) {
    $html .= '<tr><th>Catatan</th><td>'. nl2br(linkify_pdf($data['catatan'])) .'</td></tr>';
}

$html .= '
  </table>

  <p class="footer">
    Terima kasih sudah mempercayakan pekerjaan kepada kami.<br>
    <span style="float:right">Admin, Bengkel Las Berkah Jaya</span>
  </p>
</div>
</body>
</html>
';

$options = new Options();
$options->set('isRemoteEnabled', true);
$dompdf = new Dompdf($options);
$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

$filename = "nota-pesanan-" . $data['id'] . ".pdf";
$dompdf->stream($filename, ["Attachment" => true]);