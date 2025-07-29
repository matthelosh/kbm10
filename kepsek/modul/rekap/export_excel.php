<?php
require_once '../../../vendor/autoload.php';
include "../../../config/db.php";

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

$pelajaran = intval($_GET['pelajaran']);
$kelas = intval($_GET['kelas']);
$bulan = intval($_GET['bulan']);

$d = mysqli_fetch_array(mysqli_query($con, "SELECT * FROM tb_mengajar 
	JOIN tb_guru ON tb_mengajar.id_guru=tb_guru.id_guru
	JOIN tb_master_mapel ON tb_mengajar.id_mapel=tb_master_mapel.id_mapel
	JOIN tb_mkelas ON tb_mengajar.id_mkelas=tb_mkelas.id_mkelas
	JOIN tb_semester ON tb_mengajar.id_semester=tb_semester.id_semester
	JOIN tb_thajaran ON tb_mengajar.id_thajaran=tb_thajaran.id_thajaran
	WHERE tb_mengajar.id_mengajar='$pelajaran' AND tb_mengajar.id_mkelas='$kelas' AND tb_thajaran.status=1 AND tb_semester.status=1"));

$walas = mysqli_fetch_array(mysqli_query($con, "SELECT * FROM tb_walikelas 
	JOIN tb_guru ON tb_walikelas.id_guru=tb_guru.id_guru 
	WHERE tb_walikelas.id_mkelas='$kelas' "));

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

$tglBulan = date("Y-$bulan-01");
$tglTerakhir = date('t', strtotime($tglBulan));

// === KOP & LOGO ===
$sheet->getRowDimension(1)->setRowHeight(40);
$sheet->getRowDimension(2)->setRowHeight(25);
$sheet->getRowDimension(3)->setRowHeight(25);
$sheet->mergeCells('A1:A3');

$logo = new Drawing();
$logo->setName('Logo Jatim');
$logo->setDescription('Logo Jatim');
$logo->setPath('../..//assets/img/jatim.png');
$logo->setCoordinates('A1');
$logo->setHeight(70);
$logo->setOffsetX(10);
$logo->setOffsetY(5);
$logo->setWorksheet($sheet);

$sheet->mergeCells('B1:M1')->setCellValue('B1', 'PEMERINTAH PROVINSI JAWA TIMUR - SMK NEGERI 10 MALANG');
$sheet->mergeCells('B2:M2')->setCellValue('B2', 'Jalan Raya Tlogowaru, Kedungkandang, Malang, Jawa Timur 65133');
$sheet->mergeCells('B3:M3')->setCellValue('B3', 'Telp. (0341) 712500 | Website: www.smkn10malang.sch.id | Email: info@smkn10malang.sch.id');

$sheet->getStyle('B1')->getFont()->setBold(true)->setSize(14);
$sheet->getStyle('B2:B3')->getFont()->setSize(12);
$sheet->getStyle('B1:B3')->getAlignment()->setHorizontal('center');
$sheet->getStyle('B1:B3')->getAlignment()->setVertical('center');

$sheet->mergeCells('A4:H4');
$sheet->getStyle('A4')->getBorders()->getBottom()->setBorderStyle(Border::BORDER_THIN);

// === INFO KELAS ===
$sheet->setCellValue('A6', 'Kelas')->setCellValue('B6', $d['nama_kelas']);
$sheet->setCellValue('A7', 'Semester')->setCellValue('B7', $d['semester']);
$sheet->setCellValue('A8', 'Tahun Ajaran')->setCellValue('B8', $d['tahun_ajaran']);
$sheet->setCellValue('A9', 'Guru Mapel')->setCellValue('B9', $d['nama_guru']);
$sheet->setCellValue('A10', 'Mapel')->setCellValue('B10', $d['mapel']);
$sheet->setCellValue('A11', 'Wali Kelas')->setCellValue('B11', $walas['nama_guru']);

$sheet->getStyle('A6:A11')->getFont()->setBold(true);
$sheet->getStyle('A6:B11')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
$sheet->getStyle('A6:A11')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
$sheet->getStyle('B6:B11')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

// === HEADER TABEL ===
$sheet->setCellValue('A13', 'NO');
$sheet->setCellValue('B13', 'NIS');
$sheet->setCellValue('C13', 'NAMA');
$sheet->setCellValue('D13', 'L/P');

$col = 'E';
for ($i = 1; $i <= $tglTerakhir; $i++) {
    $sheet->setCellValue($col . '13', $i);
    $col++;
}

$sheet->setCellValue($col++ . '13', 'S');
$sheet->setCellValue($col++ . '13', 'I');
$sheet->setCellValue($col++ . '13', 'A');

// === DATA SISWA ===
$qrySiswa = mysqli_query($con, "SELECT * FROM _logabsensi 
	JOIN tb_siswa ON _logabsensi.id_siswa = tb_siswa.id_siswa 
	WHERE MONTH(tgl_absen) = '$bulan' 
	AND _logabsensi.id_mengajar = '$pelajaran'
	GROUP BY _logabsensi.id_siswa 
	ORDER BY tb_siswa.nama_siswa ASC");

$row = 14;
$no = 1;
while ($s = mysqli_fetch_array($qrySiswa)) {
    $sheet->setCellValue('A' . $row, $no++);
    $sheet->setCellValue('B' . $row, $s['nis']);
    $sheet->setCellValue('C' . $row, $s['nama_siswa']);
    $sheet->setCellValue('D' . $row, $s['jk']);

    $col = 'E';
    for ($i = 1; $i <= $tglTerakhir; $i++) {
        $ket = mysqli_fetch_array(mysqli_query($con, "SELECT ket FROM _logabsensi 
            WHERE id_siswa='{$s['id_siswa']}' 
            AND id_mengajar='$pelajaran'
            AND MONTH(tgl_absen)='$bulan'
            AND DAY(tgl_absen)='$i' 
            LIMIT 1"));
        $sheet->setCellValue($col . $row, $ket['ket'] ?? '');
        $col++;
    }

    // Sakit
    $sakit = mysqli_fetch_array(mysqli_query($con, "SELECT COUNT(*) as jml FROM _logabsensi 
        WHERE id_siswa = '{$s['id_siswa']}'
        AND id_mengajar = '$pelajaran'
        AND MONTH(tgl_absen) = '$bulan'
        AND ket = 'S'"));
    $sheet->setCellValue($col++ . $row, $sakit['jml'] ?: '-');

    // Izin
    $izin = mysqli_fetch_array(mysqli_query($con, "SELECT COUNT(*) as jml FROM _logabsensi 
        WHERE id_siswa = '{$s['id_siswa']}'
        AND id_mengajar = '$pelajaran'
        AND MONTH(tgl_absen) = '$bulan'
        AND ket = 'I'"));
    $sheet->setCellValue($col++ . $row, $izin['jml'] ?: '-');

    // Alpha/Terlambat/Cabut
    $alpha = mysqli_fetch_array(mysqli_query($con, "SELECT COUNT(*) as jml FROM _logabsensi 
        WHERE id_siswa = '{$s['id_siswa']}'
        AND id_mengajar = '$pelajaran'
        AND MONTH(tgl_absen) = '$bulan'
        AND (ket = 'A' OR ket = 'T' OR ket = 'C')"));
    $sheet->setCellValue($col++ . $row, $alpha['jml'] ?: '-');

    $row++;
}

// === FORMAT TABEL ===
$lastCol = $sheet->getHighestDataColumn();
$lastRow = $sheet->getHighestRow();

$sheet->getStyle("A13:{$lastCol}{$lastRow}")->applyFromArray([
    'borders' => [
        'allBorders' => ['borderStyle' => Border::BORDER_THIN],
    ],
    'alignment' => [
        'horizontal' => Alignment::HORIZONTAL_CENTER,
        'vertical' => Alignment::VERTICAL_CENTER,
        'wrapText' => true,
    ],
]);

$sheet->getStyle("A13:{$lastCol}13")->applyFromArray([
    'font' => ['bold' => true],
    'fill' => [
        'fillType' => Fill::FILL_SOLID,
        'startColor' => ['rgb' => 'D9E1F2'],
    ],
]);

// Autosize
foreach (range('A', $lastCol) as $col) {
    $sheet->getColumnDimension($col)->setAutoSize(true);
}

// === OUTPUT FILE ===
$filename = "Rekap_Absensi_Kelas_{$d['nama_kelas']}_Bulan_" . date('F', strtotime($tglBulan)) . ".xlsx";
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header("Content-Disposition: attachment;filename=\"$filename\"");
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
?>