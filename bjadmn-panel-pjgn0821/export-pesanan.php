<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../vendor/autoload.php';

if (!isset($_SESSION['admin_login']) || $_SESSION['admin_login'] !== true) {
    header("Location: login.php");
    exit;
}

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Cell\DataType;

$dari   = isset($_GET['dari']) ? trim($_GET['dari']) : '';
$sampai = isset($_GET['sampai']) ? trim($_GET['sampai']) : '';

$where = "1=1";
if ($dari) {
    $where .= " AND DATE(p.tgl_pesan) >= '" . mysqli_real_escape_string($conn, $dari) . "'";
}
if ($sampai) {
    $where .= " AND DATE(p.tgl_pesan) <= '" . mysqli_real_escape_string($conn, $sampai) . "'";
}

$list = mysqli_query($conn, "
    SELECT 
        p.*, 
        l.nama AS nama_layanan,
        g.judul AS nama_model
    FROM proyek p
    LEFT JOIN layanan l ON p.layanan_id = l.id
    LEFT JOIN galeri g ON p.galeri_id = g.id
    WHERE $where
    ORDER BY p.tgl_pesan DESC, p.id DESC
");

$rows = [];
while ($p = mysqli_fetch_assoc($list)) {
    $rows[] = $p;
}

function rapikanCatatanExcel(?string $catatan): string {
    $catatan = trim((string)$catatan);

    if ($catatan === '') {
        return '-';
    }

    $catatan = str_replace(["\r\n", "\r"], "\n", $catatan);
    $catatan = preg_replace("/[ \t]+/", " ", $catatan);
    $catatan = preg_replace("/\n{3,}/", "\n\n", $catatan);

    return trim($catatan);
}

function hitungTinggiBaris(?string $lokasi, ?string $catatan): float {
    $lokasi = (string)$lokasi;
    $catatan = (string)$catatan;

    $panjangLokasi = mb_strlen($lokasi);
    $panjangCatatan = mb_strlen($catatan);

    $barisLokasi = max(1, substr_count($lokasi, "\n") + (int)ceil($panjangLokasi / 28));
    $barisCatatan = max(1, substr_count($catatan, "\n") + (int)ceil($panjangCatatan / 38));

    $barisTerbanyak = max($barisLokasi, $barisCatatan);

    return max(24, min(140, 18 * $barisTerbanyak));
}

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Riwayat Pesanan');

/* =========================
   JUDUL LAPORAN
========================= */
$sheet->mergeCells('A1:L1');
$sheet->setCellValue('A1', 'LAPORAN RIWAYAT PESANAN');

$periodeText = 'Periode: Semua Tanggal';
if ($dari && $sampai) {
    $periodeText = 'Periode: ' . $dari . ' s/d ' . $sampai;
} elseif ($dari) {
    $periodeText = 'Periode: Dari ' . $dari;
} elseif ($sampai) {
    $periodeText = 'Periode: Sampai ' . $sampai;
}

$sheet->mergeCells('A2:L2');
$sheet->setCellValue('A2', $periodeText);

$sheet->mergeCells('A3:L3');
$sheet->setCellValue('A3', 'Total Data: ' . count($rows));

$sheet->getStyle('A1:L1')->applyFromArray([
    'font' => [
        'bold' => true,
        'size' => 16,
        'color' => ['rgb' => '1B4332'],
    ],
    'alignment' => [
        'horizontal' => Alignment::HORIZONTAL_CENTER,
        'vertical' => Alignment::VERTICAL_CENTER,
    ],
]);

$sheet->getStyle('A2:L3')->applyFromArray([
    'font' => [
        'size' => 10,
        'color' => ['rgb' => '475569'],
    ],
    'alignment' => [
        'horizontal' => Alignment::HORIZONTAL_CENTER,
        'vertical' => Alignment::VERTICAL_CENTER,
    ],
]);

$sheet->getRowDimension(1)->setRowHeight(28);
$sheet->getRowDimension(2)->setRowHeight(20);
$sheet->getRowDimension(3)->setRowHeight(20);

/* =========================
   HEADER TABEL
========================= */
$headerRow = 5;

$headers = [
    'A' => 'No',
    'B' => 'Nama',
    'C' => 'No HP',
    'D' => 'Layanan',
    'E' => 'Model',
    'F' => 'Lokasi',
    'G' => 'Catatan',
    'H' => 'Ukuran',
    'I' => 'Harga (Rp)',
    'J' => 'Status',
    'K' => 'Tgl Pesan',
    'L' => 'Tgl Selesai',
];

foreach ($headers as $col => $label) {
    $sheet->setCellValue($col . $headerRow, $label);
}

$sheet->getStyle('A5:L5')->applyFromArray([
    'font' => [
        'bold' => true,
        'color' => ['rgb' => 'FFFFFF'],
        'size' => 11,
    ],
    'fill' => [
        'fillType' => Fill::FILL_SOLID,
        'startColor' => ['rgb' => '2E7D32'],
    ],
    'alignment' => [
        'horizontal' => Alignment::HORIZONTAL_CENTER,
        'vertical' => Alignment::VERTICAL_CENTER,
    ],
    'borders' => [
        'allBorders' => [
            'borderStyle' => Border::BORDER_THIN,
            'color' => ['rgb' => 'FFFFFF'],
        ],
    ],
]);

$sheet->getRowDimension($headerRow)->setRowHeight(24);

/* =========================
   ISI DATA
========================= */
$row = $headerRow + 1;
$no  = 1;

foreach ($rows as $p) {
    $catatanExcel = rapikanCatatanExcel($p['catatan'] ?? '');
    $lokasiExcel  = trim((string)($p['lokasi'] ?? '-'));

    $sheet->setCellValue('A' . $row, $no++);
    $sheet->setCellValue('B' . $row, $p['nama'] ?? '-');
    $sheet->setCellValueExplicit('C' . $row, $p['hp'] ?? '-', DataType::TYPE_STRING);
    $sheet->setCellValue('D' . $row, $p['nama_layanan'] ?? '-');
    $sheet->setCellValue('E' . $row, $p['nama_model'] ?? '-');
    $sheet->setCellValue('F' . $row, $lokasiExcel ?: '-');
    $sheet->setCellValue('G' . $row, $catatanExcel);
    $sheet->setCellValue('H' . $row, !empty($p['ukuran']) ? $p['ukuran'] : '-');
    $sheet->setCellValue('I' . $row, (float)($p['harga'] ?? 0));
    $sheet->setCellValue('J' . $row, ucfirst($p['status'] ?? '-'));
    $sheet->setCellValue('K' . $row, !empty($p['tgl_pesan']) ? $p['tgl_pesan'] : '-');
    $sheet->setCellValue('L' . $row, !empty($p['tgl_selesai']) ? $p['tgl_selesai'] : '-');

    $bgColor = ($row % 2 === 0) ? 'F8FAFC' : 'FFFFFF';

    $sheet->getStyle('A' . $row . ':L' . $row)->applyFromArray([
        'fill' => [
            'fillType' => Fill::FILL_SOLID,
            'startColor' => ['rgb' => $bgColor],
        ],
        'borders' => [
            'allBorders' => [
                'borderStyle' => Border::BORDER_THIN,
                'color' => ['rgb' => 'E2E8F0'],
            ],
        ],
        'alignment' => [
            'vertical' => Alignment::VERTICAL_TOP,
        ],
    ]);

    $statusCell = 'J' . $row;
    $status = strtolower(trim($p['status'] ?? ''));

    $statusColor = match ($status) {
        'baru'    => 'FEF3C7',
        'proses'  => 'EDE9FE',
        'selesai' => 'DCFCE7',
        'batal'   => 'FEE2E2',
        default   => 'F1F5F9',
    };

    $statusFont = match ($status) {
        'baru'    => 'B45309',
        'proses'  => '6D28D9',
        'selesai' => '15803D',
        'batal'   => 'B91C1C',
        default   => '64748B',
    };

    $sheet->getStyle($statusCell)->applyFromArray([
        'font' => [
            'bold' => true,
            'color' => ['rgb' => $statusFont],
        ],
        'fill' => [
            'fillType' => Fill::FILL_SOLID,
            'startColor' => ['rgb' => $statusColor],
        ],
        'alignment' => [
            'horizontal' => Alignment::HORIZONTAL_CENTER,
            'vertical' => Alignment::VERTICAL_CENTER,
        ],
    ]);

    $sheet->getStyle('F' . $row . ':G' . $row)->getAlignment()->setWrapText(true);
    $sheet->getStyle('K' . $row . ':L' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

    $tinggiBaris = hitungTinggiBaris($lokasiExcel, $catatanExcel);
    $sheet->getRowDimension($row)->setRowHeight($tinggiBaris);

    $row++;
}

/* =========================
   FORMAT KOLOM
========================= */
if ($row > $headerRow + 1) {
    $lastRow = $row - 1;

    $sheet->getStyle('I' . ($headerRow + 1) . ':I' . $lastRow)
        ->getNumberFormat()
        ->setFormatCode('"Rp" #,##0');

    $sheet->getStyle('A' . $headerRow . ':L' . $lastRow)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
    $sheet->getStyle('A' . ($headerRow + 1) . ':A' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
}

/* =========================
   LEBAR KOLOM
========================= */
$sheet->getColumnDimension('A')->setWidth(6);
$sheet->getColumnDimension('B')->setWidth(22);
$sheet->getColumnDimension('C')->setWidth(18);
$sheet->getColumnDimension('D')->setWidth(18);
$sheet->getColumnDimension('E')->setWidth(20);
$sheet->getColumnDimension('F')->setWidth(30);
$sheet->getColumnDimension('G')->setWidth(45);
$sheet->getColumnDimension('H')->setWidth(14);
$sheet->getColumnDimension('I')->setWidth(16);
$sheet->getColumnDimension('J')->setWidth(14);
$sheet->getColumnDimension('K')->setWidth(22);
$sheet->getColumnDimension('L')->setWidth(22);

/* =========================
   FREEZE, FILTER, FONT
========================= */
$sheet->freezePane('A6');
$sheet->setAutoFilter('A5:L5');
$sheet->getStyle('A5:L' . max($row - 1, 5))->getFont()->setName('Arial')->setSize(10);

/* =========================
   OUTPUT FILE
========================= */
$filename = 'riwayat_pesanan_' . date('Ymd_His') . '.xlsx';

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;