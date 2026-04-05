<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../vendor/autoload.php';

use Dompdf\Dompdf;

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

$html = '
<html>
<head>
  <meta charset="UTF-8">
  <style>
    body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
    .box { border:1px solid #ccc; padding:15px; }
    .header { border-bottom:1px solid #ccc; margin-bottom:10px; padding-bottom:8px; }
    table { width:100%; border-collapse: collapse; }
    th, td { padding:4px; text-align:left; vertical-align:top; }
  </style>
</head>
<body>
<div class="box">
  <div class="header">
    <table>
      <tr>
        <td>
          <strong>Bengkel Las Berkah Jaya</strong><br>
          <small>Alamat bengkel, No. HP bengkel</small>
        </td>
        <td style="text-align:right;">
          <strong>Nota Pesanan</strong><br>
          <small>No: #'. $data['id'] .'</small><br>
          <small>Tgl Pesan: '. $data['tgl_pesan'] .'</small>
        </td>
      </tr>
    </table>
  </div>

  <h4>Data Pelanggan</h4>
  <table>
    <tr><th style="width:120px;">Nama</th><td>'. htmlspecialchars($data['nama']) .'</td></tr>
    <tr><th>HP / WA</th><td>'. htmlspecialchars($data['hp']) .'</td></tr>
    <tr><th>Lokasi</th><td>'. nl2br(htmlspecialchars($data['lokasi'])) .'</td></tr>
  </table>

  <h4 style="margin-top:10px;">Detail Pesanan</h4>
  <table>
    <tr><th style="width:120px;">Layanan</th><td>'. htmlspecialchars($data['layanan_nama']) .'</td></tr>
    <tr><th>Ukuran</th><td>'. htmlspecialchars($data['ukuran']) .'</td></tr>
    <tr><th>Harga</th><td>Rp '. number_format((float)$data['harga'],0,',','.') .'</td></tr>
    <tr><th>Status</th><td>'. htmlspecialchars($data['status']) .'</td></tr>';

if (!empty($data['catatan'])) {
    $html .= '<tr><th>Catatan</th><td>'. nl2br(htmlspecialchars($data['catatan'])) .'</td></tr>';
}

$html .= '
  </table>

  <p style="margin-top:15px; font-size:11px;">
    Terima kasih sudah mempercayakan pekerjaan kepada kami.
  </p>
</div>
</body>
</html>
';

$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

// nama file
$filename = 'nota-pesanan-'. $data['id'] .'.pdf';
$dompdf->stream($filename, ['Attachment' => true]);