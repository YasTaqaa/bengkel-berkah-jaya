<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../vendor/autoload.php';

if (!isset($_SESSION['admin_login']) || $_SESSION['admin_login'] !== true) {
    header('Location: login.php');
    exit;
}

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Cell\DataType;

$dari = isset($_GET['dari']) ? trim($_GET['dari']) : '';
$sampai = isset($_GET['sampai']) ? trim($_GET['sampai']) : '';

/*
|--------------------------------------------------------------------------
| Filter tanggal
|--------------------------------------------------------------------------
*/
$where = '1=1';

if ($dari !== '') {
    $dariAman = mysqli_real_escape_string($conn, $dari);
    $where .= " AND DATE(p.tgl_pesan) >= '{$dariAman}'";
}

if ($sampai !== '') {
    $sampaiAman = mysqli_real_escape_string($conn, $sampai);
    $where .= " AND DATE(p.tgl_pesan) <= '{$sampaiAman}'";
}

/*
|--------------------------------------------------------------------------
| Ambil data pesanan
|--------------------------------------------------------------------------
| proyek.harga      = harga per m² yang telah disimpan admin.
| galeri.harga      = harga per m² cadangan dari model yang dipilih.
*/
$query = "
    SELECT
        p.*,
        l.nama AS nama_layanan,
        g.judul AS nama_model,
        g.harga AS harga_galeri
    FROM proyek p
    LEFT JOIN layanan l ON p.layanan_id = l.id
    LEFT JOIN galeri g ON p.galeri_id = g.id
    WHERE {$where}
    ORDER BY p.tgl_pesan DESC, p.id DESC
";

$list = mysqli_query($conn, $query);

if (!$list) {
    die('Gagal mengambil data pesanan: ' . mysqli_error($conn));
}

$rows = [];

while ($pesanan = mysqli_fetch_assoc($list)) {
    $rows[] = $pesanan;
}

/*
|--------------------------------------------------------------------------
| Fungsi pendukung
|--------------------------------------------------------------------------
*/
function rapikanCatatanExcel(?string $catatan): string
{
    $catatan = trim((string) $catatan);

    if ($catatan === '') {
        return '-';
    }

    $catatan = str_replace(["\r\n", "\r"], "\n", $catatan);
    $catatan = preg_replace('/[ \t]+/', ' ', $catatan);
    $catatan = preg_replace("/\n{3,}/", "\n\n", $catatan);

    return trim($catatan);
}

function hitungTinggiBaris(?string $lokasi, ?string $catatan): float
{
    $lokasi = (string) $lokasi;
    $catatan = (string) $catatan;

    $panjangLokasi = mb_strlen($lokasi);
    $panjangCatatan = mb_strlen($catatan);

    $barisLokasi = max(
        1,
        substr_count($lokasi, "\n") + (int) ceil($panjangLokasi / 28)
    );

    $barisCatatan = max(
        1,
        substr_count($catatan, "\n") + (int) ceil($panjangCatatan / 38)
    );

    $barisTerbanyak = max($barisLokasi, $barisCatatan);

    return max(24, min(140, 18 * $barisTerbanyak));
}

/*
|--------------------------------------------------------------------------
| Mengambil luas dari ukuran
|--------------------------------------------------------------------------
| Contoh format yang didukung:
| 3 x 1.5
| 3x1.5
| 3 X 1,5 m
| 2.5 × 4
*/
function ambilLuasUkuran(?string $ukuran): float
{
    $ukuran = strtolower(trim((string) $ukuran));

    if ($ukuran === '') {
        return 0;
    }

    $ukuran = str_replace(',', '.', $ukuran);

    if (
        preg_match(
            '/(\d+(?:\.\d+)?)\s*[x×]\s*(\d+(?:\.\d+)?)/i',
            $ukuran,
            $match
        )
    ) {
        $panjang = (float) $match[1];
        $lebar = (float) $match[2];

        return $panjang * $lebar;
    }

    return 0;
}

/*
|--------------------------------------------------------------------------
| Hitung total pesanan
|--------------------------------------------------------------------------
| Total = luas (m²) × harga per m².
|
| Prioritas harga:
| 1. proyek.harga, jika sudah diisi admin.
| 2. galeri.harga, jika harga proyek masih 0.
*/
function hitungTotalHargaPesanan(array $pesanan): float
{
    $hargaPerM2 = (float) ($pesanan['harga'] ?? 0);

    if ($hargaPerM2 <= 0) {
        $hargaPerM2 = (float) ($pesanan['harga_galeri'] ?? 0);
    }

    $luas = ambilLuasUkuran($pesanan['ukuran'] ?? '');

    /*
     * Bila ukuran kosong atau tidak sesuai format,
     * gunakan nilai harga/m² agar tidak menjadi nol.
     */
    if ($luas <= 0) {
        return $hargaPerM2;
    }

    return $luas * $hargaPerM2;
}

/*
|--------------------------------------------------------------------------
| Membuat file Excel
|--------------------------------------------------------------------------
*/
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Riwayat Pesanan');

/*
|--------------------------------------------------------------------------
| Judul laporan
|--------------------------------------------------------------------------
*/
$sheet->mergeCells('A1:L1');
$sheet->setCellValue('A1', 'LAPORAN RIWAYAT PESANAN');

$periodeText = 'Periode: Semua Tanggal';

if ($dari !== '' && $sampai !== '') {
    $periodeText = 'Periode: ' . $dari . ' s/d ' . $sampai;
} elseif ($dari !== '') {
    $periodeText = 'Periode: Dari ' . $dari;
} elseif ($sampai !== '') {
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

/*
|--------------------------------------------------------------------------
| Header tabel
|--------------------------------------------------------------------------
*/
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
    'I' => 'Total Harga (Rp)',
    'J' => 'Status',
    'K' => 'Tgl Pesan',
    'L' => 'Tgl Selesai',
];

foreach ($headers as $kolom => $label) {
    $sheet->setCellValue($kolom . $headerRow, $label);
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

/*
|--------------------------------------------------------------------------
| Isi data pesanan
|--------------------------------------------------------------------------
*/
$row = $headerRow + 1;
$dataAwalRow = $row;
$no = 1;
$totalSemuaPesanan = 0;

foreach ($rows as $p) {
    $catatanExcel = rapikanCatatanExcel($p['catatan'] ?? '');
    $lokasiExcel = trim((string) ($p['lokasi'] ?? '-'));

    $totalHarga = hitungTotalHargaPesanan($p);
    $totalSemuaPesanan += $totalHarga;

    $sheet->setCellValue('A' . $row, $no++);
    $sheet->setCellValue('B' . $row, $p['nama'] ?? '-');

    /*
     * Nomor HP sebagai teks agar angka nol di depan tetap ada.
     */
    $sheet->setCellValueExplicit(
        'C' . $row,
        $p['hp'] ?? '-',
        DataType::TYPE_STRING
    );

    $sheet->setCellValue('D' . $row, $p['nama_layanan'] ?? '-');
    $sheet->setCellValue('E' . $row, $p['nama_model'] ?? '-');
    $sheet->setCellValue('F' . $row, $lokasiExcel !== '' ? $lokasiExcel : '-');
    $sheet->setCellValue('G' . $row, $catatanExcel);
    $sheet->setCellValue('H' . $row, !empty($p['ukuran']) ? $p['ukuran'] : '-');
    $sheet->setCellValue('I' . $row, $totalHarga);
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
            'vertical' => Alignment::VERTICAL_CENTER,
        ],
    ]);

    $statusCell = 'J' . $row;
    $status = strtolower(trim((string) ($p['status'] ?? '')));

    $statusColor = match ($status) {
        'baru' => 'FEF3C7',
        'proses' => 'EDE9FE',
        'selesai' => 'DCFCE7',
        'batal' => 'FEE2E2',
        default => 'F1F5F9',
    };

    $statusFont = match ($status) {
        'baru' => 'B45309',
        'proses' => '6D28D9',
        'selesai' => '15803D',
        'batal' => 'B91C1C',
        default => '64748B',
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

    $sheet->getStyle('F' . $row . ':G' . $row)
        ->getAlignment()
        ->setWrapText(true);

    $sheet->getStyle('K' . $row . ':L' . $row)
        ->getAlignment()
        ->setHorizontal(Alignment::HORIZONTAL_CENTER);

    $tinggiBaris = hitungTinggiBaris($lokasiExcel, $catatanExcel);
    $sheet->getRowDimension($row)->setRowHeight($tinggiBaris);

    $row++;
}

/*
|--------------------------------------------------------------------------
| Catat batas data sebelum membuat baris total
|--------------------------------------------------------------------------
*/
$lastDataRow = $row - 1;

/*
|--------------------------------------------------------------------------
| Baris total nilai pesanan
|--------------------------------------------------------------------------
*/
if ($lastDataRow >= $dataAwalRow) {
    $sheet->mergeCells('A' . $row . ':H' . $row);
    $sheet->setCellValue('A' . $row, 'TOTAL NILAI PESANAN');

    $sheet->setCellValue('I' . $row, $totalSemuaPesanan);

    $sheet->mergeCells('J' . $row . ':L' . $row);
    $sheet->setCellValue('J' . $row, 'Jumlah Pesanan: ' . count($rows));

    $sheet->getStyle('A' . $row . ':L' . $row)->applyFromArray([
        'font' => [
            'bold' => true,
            'color' => ['rgb' => '1B4332'],
        ],
        'fill' => [
            'fillType' => Fill::FILL_SOLID,
            'startColor' => ['rgb' => 'DCFCE7'],
        ],
        'borders' => [
            'allBorders' => [
                'borderStyle' => Border::BORDER_THIN,
                'color' => ['rgb' => '86EFAC'],
            ],
        ],
        'alignment' => [
            'vertical' => Alignment::VERTICAL_CENTER,
        ],
    ]);

    $sheet->getStyle('I' . $row)
        ->getNumberFormat()
        ->setFormatCode('"Rp" #,##0');

    $sheet->getStyle('I' . $row)
        ->getAlignment()
        ->setHorizontal(Alignment::HORIZONTAL_RIGHT);

    $sheet->getRowDimension($row)->setRowHeight(24);

    $row++;
}

/*
|--------------------------------------------------------------------------
| Format angka dan alignment tabel
|--------------------------------------------------------------------------
*/
if ($lastDataRow >= $dataAwalRow) {
    $sheet->getStyle('I' . $dataAwalRow . ':I' . $lastDataRow)
        ->getNumberFormat()
        ->setFormatCode('"Rp" #,##0');

    $sheet->getStyle('A' . $dataAwalRow . ':A' . $lastDataRow)
        ->getAlignment()
        ->setHorizontal(Alignment::HORIZONTAL_CENTER);

    $sheet->getStyle('I' . $dataAwalRow . ':I' . $lastDataRow)
        ->getAlignment()
        ->setHorizontal(Alignment::HORIZONTAL_RIGHT);
}

/*
|--------------------------------------------------------------------------
| Lebar kolom
|--------------------------------------------------------------------------
*/
$sheet->getColumnDimension('A')->setWidth(6);
$sheet->getColumnDimension('B')->setWidth(22);
$sheet->getColumnDimension('C')->setWidth(18);
$sheet->getColumnDimension('D')->setWidth(18);
$sheet->getColumnDimension('E')->setWidth(20);
$sheet->getColumnDimension('F')->setWidth(30);
$sheet->getColumnDimension('G')->setWidth(45);
$sheet->getColumnDimension('H')->setWidth(14);
$sheet->getColumnDimension('I')->setWidth(18);
$sheet->getColumnDimension('J')->setWidth(14);
$sheet->getColumnDimension('K')->setWidth(22);
$sheet->getColumnDimension('L')->setWidth(22);

/*
|--------------------------------------------------------------------------
| Freeze, filter, dan font
|--------------------------------------------------------------------------
*/
$sheet->freezePane('A6');

if ($lastDataRow >= $dataAwalRow) {
    $sheet->setAutoFilter('A5:L' . $lastDataRow);
}

$sheet->getStyle('A5:L' . max($row - 1, $headerRow))
    ->getFont()
    ->setName('Arial')
    ->setSize(10);

/*
|--------------------------------------------------------------------------
| Output file Excel
|--------------------------------------------------------------------------
*/
$filename = 'riwayat_pesanan_' . date('Ymd_His') . '.xlsx';

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');

exit;