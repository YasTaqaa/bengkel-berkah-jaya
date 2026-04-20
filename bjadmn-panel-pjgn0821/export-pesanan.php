<?php
require_once __DIR__ . '/../config.php';

// Cek login admin
if (!isset($_SESSION['admin_login']) || $_SESSION['admin_login'] !== true) {
    header("Location: login.php");
    exit;
}

// Autoload PhpSpreadsheet
require_once __DIR__ . '/../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

// ── Query ──────────────────────────────────────────────────────────────────
$where = "1=1";
$dari   = $_GET['dari']   ?? '';
$sampai = $_GET['sampai'] ?? '';

if ($dari)   $where .= " AND DATE(p.tgl_pesan) >= '" . mysqli_real_escape_string($conn, $dari)   . "'";
if ($sampai) $where .= " AND DATE(p.tgl_pesan) <= '" . mysqli_real_escape_string($conn, $sampai) . "'";

$list = mysqli_query($conn, "
    SELECT p.*, l.nama AS nama_layanan
    FROM proyek p
    LEFT JOIN layanan l ON p.layanan_id = l.id
    WHERE $where
    ORDER BY p.tgl_pesan DESC
");

$rows = [];
while ($p = mysqli_fetch_assoc($list)) {
    $rows[] = $p;
}

// ── Buat Spreadsheet ───────────────────────────────────────────────────────
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Pesanan');

// ── Header Kolom ───────────────────────────────────────────────────────────
$headers = [
    'A' => 'No',
    'B' => 'Nama',
    'C' => 'No HP',
    'D' => 'Layanan',
    'E' => 'Lokasi',
    'F' => 'Catatan',
    'G' => 'Ukuran',
    'H' => 'Harga (Rp)',
    'I' => 'Status',
    'J' => 'Tgl Pesan',
    'K' => 'Tgl Selesai',
];

foreach ($headers as $col => $label) {
    $sheet->setCellValue($col . '1', $label);
}

// ── Styling Header ─────────────────────────────────────────────────────────
$sheet->getStyle('A1:K1')->applyFromArray([
    'font' => [
        'bold'  => true,
        'color' => ['rgb' => 'FFFFFF'],
        'size'  => 11,
    ],
    'fill' => [
        'fillType'   => Fill::FILL_SOLID,
        'startColor' => ['rgb' => '2E7D32'],
    ],
    'alignment' => [
        'horizontal' => Alignment::HORIZONTAL_CENTER,
        'vertical'   => Alignment::VERTICAL_CENTER,
    ],
    'borders' => [
        'allBorders' => [
            'borderStyle' => Border::BORDER_THIN,
            'color'       => ['rgb' => 'FFFFFF'],
        ],
    ],
]);
$sheet->getRowDimension(1)->setRowHeight(22);

// ── Isi Data ───────────────────────────────────────────────────────────────
$no  = 1;
$row = 2;

foreach ($rows as $p) {
    $sheet->setCellValue('A' . $row, $no++);
    $sheet->setCellValue('B' . $row, $p['nama']);
    $sheet->setCellValueExplicit('C' . $row, $p['hp'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
    $sheet->setCellValue('D' . $row, $p['nama_layanan'] ?? '');
    $sheet->setCellValue('E' . $row, $p['lokasi']);
    $sheet->setCellValue('F' . $row, $p['catatan']);
    $sheet->setCellValue('G' . $row, $p['ukuran']);
    $sheet->setCellValue('H' . $row, (float)$p['harga']);
    $sheet->setCellValue('I' . $row, $p['status']);
    $sheet->setCellValue('J' . $row, $p['tgl_pesan']   ?? '');
    $sheet->setCellValue('K' . $row, $p['tgl_selesai'] ?? '');

    // Zebra stripe
    $bgColor = ($row % 2 === 0) ? 'F5F5F5' : 'FFFFFF';
    $sheet->getStyle('A' . $row . ':K' . $row)->applyFromArray([
        'fill' => [
            'fillType'   => Fill::FILL_SOLID,
            'startColor' => ['rgb' => $bgColor],
        ],
        'borders' => [
            'allBorders' => [
                'borderStyle' => Border::BORDER_THIN,
                'color'       => ['rgb' => 'DDDDDD'],
            ],
        ],
        'alignment' => [
            'vertical' => Alignment::VERTICAL_CENTER,
        ],
    ]);

    $row++;
}

// ── Format Harga & Tanggal ─────────────────────────────────────────────────
if ($row > 2) {
    $sheet->getStyle('H2:H' . ($row - 1))
          ->getNumberFormat()
          ->setFormatCode('#,##0');

    $sheet->getStyle('J2:J' . ($row - 1))
          ->getNumberFormat()
          ->setFormatCode('DD/MM/YYYY HH:MM');

    $sheet->getStyle('K2:K' . ($row - 1))
          ->getNumberFormat()
          ->setFormatCode('DD/MM/YYYY HH:MM');
}

// ── Auto-size semua kolom ──────────────────────────────────────────────────
foreach (array_keys($headers) as $col) {
    $sheet->getColumnDimension($col)->setAutoSize(true);
}

// ── Freeze baris header & Auto-filter ─────────────────────────────────────
$sheet->freezePane('A2');
$sheet->setAutoFilter('A1:K1');

// ── Output ke browser ─────────────────────────────────────────────────────
$filename = 'pesanan_' . date('Ymd_His') . '.xlsx';

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;