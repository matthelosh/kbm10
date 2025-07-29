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

// Logo
$logo = new Drawing();
$logo->setName('Logo');
$logo->setPath('../..//assets/img/jatim.png');
$logo->setHeight(100);
$logo->setCoordinates('A1');
$logo->setOffsetX(12);
$logo->setOffsetY(10);
$logo->setWorksheet($sheet);

// Kop
$sheet->mergeCells('B1:I1')->setCellValue('B1', 'PEMERINTAH PROVINSI JAWA TIMUR');
$sheet->mergeCells('B2:I2')->setCellValue('B2', 'DINAS PENDIDIKAN');
$sheet->mergeCells('B3:I3')->setCellValue('B3', 'SMK NEGERI 10 MALANG');
$sheet->mergeCells('B4:I4')->setCellValue('B4', 'Jalan Raya Tlogowaru, Kedungkandang, Malang, Jawa Timur 65133');
$sheet->mergeCells('B5:I5')->setCellValue('B5', 'Telp. (0341) 754086 E-mail: smkn10_malang@yahoo.co.id');
$sheet->getCell('B5')->getHyperlink()->setUrl('mailto:smkn10_malang@yahoo.co.id');

$sheet->getStyle('B1:B5')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$sheet->getStyle('B3')->getFont()->setBold(true)->setSize(14);

//$sheet->mergeCells('A4:H4');
//$sheet->getStyle('A4')->getBorders()->getBottom()->setBorderStyle(Border::BORDER_THIN);

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

// Tambahkan header total H, S, I, A
$headerH = $col++;
$headerS = $col++;
$headerI = $col++;
$headerA = $col++;

$sheet->setCellValue($headerH . '13', 'H');
$sheet->setCellValue($headerS . '13', 'S');
$sheet->setCellValue($headerI . '13', 'I');
$sheet->setCellValue($headerA . '13', 'A');

// === DATA SISWA ===
$qrySiswa = mysqli_query($con, "SELECT * FROM tb_siswa WHERE id_mkelas='$kelas' ORDER BY nama_siswa ASC");
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
        $nilai = $ket['ket'] ?? '-';
        if ($nilai === 'T' || $nilai === 'C') $nilai = '-';
        $sheet->setCellValue($col . $row, $nilai);
        $col++;
    }

    // Hitung total H, S, I, A
    $totalH = mysqli_fetch_array(mysqli_query($con, "SELECT COUNT(*) as jml FROM _logabsensi 
        WHERE id_siswa='{$s['id_siswa']}'
        AND id_mengajar='$pelajaran'
        AND MONTH(tgl_absen)='$bulan'
        AND ket='H'"));
    $sheet->setCellValue($headerH . $row, $totalH['jml'] ?: '0');

    $totalS = mysqli_fetch_array(mysqli_query($con, "SELECT COUNT(*) as jml FROM _logabsensi 
        WHERE id_siswa='{$s['id_siswa']}'
        AND id_mengajar='$pelajaran'
        AND MONTH(tgl_absen)='$bulan'
        AND ket='S'"));
    $sheet->setCellValue($headerS . $row, $totalS['jml'] ?: '0');

    $totalI = mysqli_fetch_array(mysqli_query($con, "SELECT COUNT(*) as jml FROM _logabsensi 
        WHERE id_siswa='{$s['id_siswa']}'
        AND id_mengajar='$pelajaran'
        AND MONTH(tgl_absen)='$bulan'
        AND ket='I'"));
    $sheet->setCellValue($headerI . $row, $totalI['jml'] ?: '0');

    $totalA = mysqli_fetch_array(mysqli_query($con, "SELECT COUNT(*) as jml FROM _logabsensi 
        WHERE id_siswa='{$s['id_siswa']}'
        AND id_mengajar='$pelajaran'
        AND MONTH(tgl_absen)='$bulan'
        AND ket='A'"));
    $sheet->setCellValue($headerA . $row, $totalA['jml'] ?: '0');

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

// === WARNA HEADER TOTAL H S I A ===
$sheet->getStyle("{$headerH}13")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('C6EFCE'); // H Hijau
$sheet->getStyle("{$headerS}13")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('FFF2CC'); // S Kuning
$sheet->getStyle("{$headerI}13")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('DDEBF7'); // I Biru
$sheet->getStyle("{$headerA}13")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F4CCCC'); // A Merah

// === WARNA KOLOM TOTAL H S I A ===
$sheet->getStyle("{$headerH}14:{$headerH}{$lastRow}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('E2F0D9'); // Hijau muda
$sheet->getStyle("{$headerS}14:{$headerS}{$lastRow}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('FFF9DB'); // Kuning muda
$sheet->getStyle("{$headerI}14:{$headerI}{$lastRow}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('EBF1DE'); // Biru muda
$sheet->getStyle("{$headerA}14:{$headerA}{$lastRow}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('FDE9D9'); // Merah muda

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