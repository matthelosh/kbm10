<?php
$autoloadPath = __DIR__ . '/../../../vendor/autoload.php';
if (!file_exists($autoloadPath)) {
    die("File autoload.php tidak ditemukan di $autoloadPath");
}
require_once $autoloadPath;

if (!class_exists('PhpOffice\\PhpSpreadsheet\\Spreadsheet')) {
    die("Class Spreadsheet tidak ditemukan setelah include autoload!");
}
include '../../../config/db.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

// Validasi parameter
if (!isset($_GET['id_mengajar']) || !isset($_GET['tanggal'])) {
    die("❌ Parameter tidak lengkap.");
}

$id_mengajar = $_GET['id_mengajar'];
$tanggal = $_GET['tanggal'];

// Ambil info mengajar
$info = mysqli_fetch_array(mysqli_query($con, "
    SELECT m.id_mkelas, k.nama_kelas, g.nama_guru, mp.mapel 
    FROM tb_mengajar m 
    LEFT JOIN tb_mkelas k ON m.id_mkelas = k.id_mkelas 
    LEFT JOIN tb_guru g ON m.id_guru = g.id_guru 
    LEFT JOIN tb_master_mapel mp ON m.id_mapel = mp.id_mapel 
    WHERE m.id_mengajar='$id_mengajar'
"));

if (!$info) die("❌ Data tidak ditemukan.");

$id_mkelas = $info['id_mkelas'];
$siswa = mysqli_query($con, "SELECT * FROM tb_siswa WHERE id_mkelas='$id_mkelas' ORDER BY nama_siswa ASC");

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

$spreadsheet->getDefaultStyle()->getFont()->setName('Times New Roman')->setSize(12);

// Logo
$drawing = new Drawing();
$drawing->setPath('../../assets/img/jatim.png');
$drawing->setHeight(105);
$drawing->setCoordinates('A1');
$drawing->setOffsetX(20);
$drawing->setOffsetY(10);
$drawing->setWorksheet($sheet);

// Header Sekolah
$sheet->mergeCells('B1:H1')->setCellValue('B1', 'PEMERINTAH PROVINSI JAWA TIMUR');
$sheet->mergeCells('B2:H2')->setCellValue('B2', 'DINAS PENDIDIKAN');
$sheet->mergeCells('B3:H3')->setCellValue('B3', 'SMK NEGERI 10 MALANG');
$sheet->mergeCells('B4:H4')->setCellValue('B4', 'Jalan Raya Tlogowaru, Kedungkandang, Malang, Jawa Timur 65133');
$sheet->mergeCells('B5:H5')->setCellValue('B5', 'Telp. (0341) 754086 E-mail: smkn10_malang@yahoo.co.id');
$sheet->getCell('B5')->getHyperlink()->setUrl('mailto:smkn10_malang@yahoo.co.id');
$sheet->getStyle('B1:B5')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$sheet->getStyle('B3')->getFont()->setBold(true)->setSize(14);

// Judul Rekap
$sheet->mergeCells('B6:H6')->setCellValue('B6', 'REKAP ABSENSI HARIAN - ' . date('d M Y', strtotime($tanggal)));
$sheet->getStyle('B6')->getFont()->setBold(true)->setSize(16);
$sheet->getStyle('B6')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

// Informasi kelas
$sheet->setCellValue('A7', 'Nama Guru:');
$sheet->setCellValue('B7', $info['nama_guru']);
$sheet->setCellValue('A8', 'Kelas:');
$sheet->setCellValue('B8', $info['nama_kelas']);
$sheet->setCellValue('A9', 'Mata Pelajaran:');
$sheet->setCellValue('B9', $info['mapel']);
$sheet->setCellValue('A10', 'Tanggal:');
$sheet->setCellValue('B10', date('d M Y', strtotime($tanggal)));

$startRow = 12;
$header = ['No', 'NIS', 'Nama Siswa', 'Hadir', 'Izin', 'Sakit', 'Alpha'];
$colLetters = range('A', 'G');
foreach ($header as $i => $text) {
    $cell = $colLetters[$i] . $startRow;
    $sheet->setCellValue($cell, $text);
}

$sheet->getStyle("A$startRow:G$startRow")->applyFromArray([
    'font' => ['bold' => true],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
    'fill' => [
        'fillType' => Fill::FILL_SOLID,
        'startColor' => ['rgb' => 'D9D9D9']
    ],
    'borders' => [
        'allBorders' => ['borderStyle' => Border::BORDER_THIN]
    ]
]);

$kehadiran = ['H', 'I', 'S', 'A'];
$rekapTotal = array_fill_keys($kehadiran, 0);
$rowNum = $startRow + 1;
$no = 1;

while ($s = mysqli_fetch_array($siswa)) {
    $sheet->setCellValue('A' . $rowNum, $no++);
    $sheet->setCellValue('B' . $rowNum, $s['nis']);
    $sheet->setCellValue('C' . $rowNum, $s['nama_siswa']);

    $absen = mysqli_fetch_array(mysqli_query($con, "
        SELECT ket FROM _logabsensi 
        WHERE id_siswa='{$s['id_siswa']}' AND id_mengajar='$id_mengajar' AND tgl_absen='$tanggal'
    "));

    foreach ($kehadiran as $j => $kode) {
        $col = $colLetters[$j + 3];
        if ($absen && $absen['ket'] == $kode) {
            $sheet->setCellValue($col . $rowNum, '✓');
            $rekapTotal[$kode]++;
        }
    }
    $rowNum++;
}

$sheet->setCellValue('A' . $rowNum, 'Total');
$sheet->mergeCells("A$rowNum:C$rowNum");
$sheet->getStyle("A$rowNum:G$rowNum")->applyFromArray([
    'font' => ['bold' => true],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
    'fill' => [
        'fillType' => Fill::FILL_SOLID,
        'startColor' => ['rgb' => 'F2F2F2']
    ],
    'borders' => [
        'allBorders' => ['borderStyle' => Border::BORDER_THIN]
    ]
]);

foreach ($kehadiran as $j => $kode) {
    $col = $colLetters[$j + 3];
    $sheet->setCellValue($col . $rowNum, $rekapTotal[$kode]);
}

$sheet->getStyle("A" . ($startRow + 1) . ":G" . ($rowNum - 1))->applyFromArray([
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
    'borders' => [
        'allBorders' => ['borderStyle' => Border::BORDER_THIN]
    ]
]);

foreach ($colLetters as $col) {
    $sheet->getColumnDimension($col)->setAutoSize(true);
}

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="Rekap_Absen_' . $tanggal . '.xlsx"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;