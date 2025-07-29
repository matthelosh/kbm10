<?php
require '../../../vendor/autoload.php';
include '../../../guru/modul/jurnal/koneksi.php';
include '../../../config/db.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Style\Border;

// Ambil filter dari URL
$filter_bulan = $_GET['bulan'] ?? date('Y-m');
$filter_guru = $_GET['guru'] ?? '';

// Setup spreadsheet
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$spreadsheet->getDefaultStyle()->getFont()->setName('Times New Roman')->setSize(12);

// Logo
$drawing = new Drawing();
$drawing->setPath('../..//assets/img/jatim.png');
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

// Judul laporan
$judul = 'DATA ABSENSI SEMUA GURU - BULAN ' . strtoupper(date('F Y', strtotime($filter_bulan . '-01')));
$sheet->mergeCells('A7:G7')->setCellValue('A7', $judul);
$sheet->getStyle('A7')->getFont()->setBold(true)->setSize(14);
$sheet->getStyle('A7')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

// Header tabel
$header = ['No', 'NIP', 'Nama', 'Mapel - Kelas', 'Tanggal', 'Status', 'Foto'];
$sheet->fromArray($header, null, 'A8');
$sheet->getStyle('A8:G8')->getFont()->setBold(true);
$sheet->getStyle('A8:G8')->getAlignment()->setHorizontal('center');
$sheet->getStyle('A8:G8')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

// Query data berdasarkan filter
$sql = "
    SELECT lag.*, g.nip, g.nama_guru, mp.mapel, k.nama_kelas
    FROM _logabsenguru lag
    INNER JOIN tb_guru g ON lag.id_guru = g.id_guru
    LEFT JOIN tb_mengajar m ON lag.id_mengajar = m.id_mengajar
    LEFT JOIN tb_master_mapel mp ON m.id_mapel = mp.id_mapel
    LEFT JOIN tb_mkelas k ON m.id_mkelas = k.id_mkelas
    WHERE DATE_FORMAT(lag.tanggal, '%Y-%m') = '" . mysqli_real_escape_string($conn, $filter_bulan) . "'
      AND lag.ket != ''";

if (!empty($filter_guru)) {
    $sql .= " AND g.id_guru = '" . mysqli_real_escape_string($conn, $filter_guru) . "'";
}

$sql .= " ORDER BY g.id_guru ASC, lag.tanggal ASC";

$query = mysqli_query($conn, $sql);

// Tulis data ke Excel
$row = 9;
$no = 1;
while ($data = mysqli_fetch_assoc($query)) {
    $status = match ($data['ket']) {
        'H' => 'Hadir',
        'I' => 'Izin',
        'S' => 'Sakit',
        'C' => 'Cuti',
        'D' => 'Dinas Luar',
        'P' => 'Penugasan',
        'K' => 'Kosong',
        default => 'Tidak Diketahui',
    };

    $sheet->setCellValue('A' . $row, $no++);
    $sheet->setCellValue('B' . $row, $data['nip'] ?? '-');
    $sheet->setCellValue('C' . $row, $data['nama_guru'] ?? '-');
    $sheet->setCellValue('D' . $row, ($data['mapel'] ?? '-') . ' - ' . ($data['nama_kelas'] ?? '-'));
    $sheet->setCellValue('E' . $row, isset($data['tanggal']) ? date('d-m-Y', strtotime($data['tanggal'])) : '-');
    $sheet->setCellValue('F' . $row, $status);
    $sheet->setCellValue('G' . $row, $data['foto'] ?? '-');
    $row++;
}

// Auto width
foreach (range('A', 'G') as $col) {
    $sheet->getColumnDimension($col)->setAutoSize(true);
}

// Export file
$filename = 'Rekap_Absensi_Guru_' . date('Ymd_His') . '.xlsx';
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header("Content-Disposition: attachment;filename=\"$filename\"");
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
