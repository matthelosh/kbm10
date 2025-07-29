<?php
require_once '../../../vendor/autoload.php';
include "../../../config/db.php";

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Font;

$pelajaran = intval($_GET['pelajaran']);
$kelas = intval($_GET['kelas']);
$bulanParam = $_GET['bulan'] ?? date('Y-m');
list($tahun, $bulan) = explode('-', $bulanParam);
$tglBulan = "$tahun-$bulan-01";
$tglTerakhir = date('t', strtotime($tglBulan));

// Ambil data kelas dan guru
$d = mysqli_fetch_array(mysqli_query($con, "SELECT * FROM tb_mengajar 
	JOIN tb_guru ON tb_mengajar.id_guru=tb_guru.id_guru
	JOIN tb_master_mapel ON tb_mengajar.id_mapel=tb_master_mapel.id_mapel
	JOIN tb_mkelas ON tb_mengajar.id_mkelas=tb_mkelas.id_mkelas
	JOIN tb_semester ON tb_mengajar.id_semester=tb_semester.id_semester
	JOIN tb_thajaran ON tb_mengajar.id_thajaran=tb_thajaran.id_thajaran
	WHERE tb_mengajar.id_mengajar='$pelajaran' AND tb_mengajar.id_mkelas='$kelas' AND tb_thajaran.status=1 AND tb_semester.status=1"));

$walas = mysqli_fetch_array(mysqli_query($con, "SELECT * FROM tb_walikelas 
	JOIN tb_guru ON tb_walikelas.id_guru=tb_guru.id_guru 
	WHERE tb_walikelas.id_mkelas='$kelas'"));

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$spreadsheet->getDefaultStyle()->getFont()->setName('Times New Roman')->setSize(11);

// === KOP & LOGO ===
$sheet->getRowDimension(1)->setRowHeight(15);
$sheet->mergeCells('A1:A3');

$logo = new Drawing();
$logo->setName('Logo Jatim');
$logo->setPath('../..//assets/img/jatim.png');
$logo->setHeight(100);
$logo->setCoordinates('A1');
$logo->setOffsetX(10);
$logo->setOffsetY(5);
$logo->setWorksheet($sheet);

$sheet->mergeCells('B1:AM1')->setCellValue('B1', 'PEMERINTAH PROVINSI JAWA TIMUR');
$sheet->mergeCells('B2:AM2')->setCellValue('B2', 'DINAS PENDIDIKAN');
$sheet->mergeCells('B3:AM3')->setCellValue('B3', 'SMK NEGERI 10 MALANG');
$sheet->mergeCells('B4:AM4')->setCellValue('B4', 'Jalan Raya Tlogowaru, Kedungkandang, Malang, Jawa Timur 65133');
$sheet->mergeCells('B5:AM5')->setCellValue('B5', 'Telp. (0341) 754086 E-mail: smkn10_malang@yahoo.co.id');
$sheet->getCell('B5')->getHyperlink()->setUrl('mailto:smkn10_malang@yahoo.co.id');

$sheet->getStyle('B3')->getFont()->setBold(true)->setSize(14);
$sheet->getStyle('B1:B5')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

// === INFO KELAS ===
$sheet->setCellValue('A6', 'Kelas')->setCellValue('B6', $d['nama_kelas']);
$sheet->setCellValue('A7', 'Semester')->setCellValue('B7', $d['semester']);
$sheet->setCellValue('A8', 'Tahun Ajaran')->setCellValue('B8', $d['tahun_ajaran']);
$sheet->setCellValue('A9', 'Guru Mapel')->setCellValue('B9', $d['nama_guru']);
$sheet->setCellValue('A10', 'Mapel')->setCellValue('B10', $d['mapel']);
$sheet->setCellValue('A11', 'Wali Kelas')->setCellValue('B11', $walas['nama_guru']);
$sheet->getStyle('A6:A11')->getFont()->setBold(true);

// === HEADER TABEL ===
$sheet->setCellValue('A13', 'NO');
$sheet->setCellValue('B13', 'NIS');
$sheet->setCellValue('C13', 'NAMA');
$sheet->setCellValue('D13', 'L/P');

$col = 'E';
for ($i = 1; $i <= $tglTerakhir; $i++) {
    $sheet->setCellValue($col . '13', $i);
    $sheet->getStyle($col . '13')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('D9E1F2');
    $col++;
}

// Warna khusus untuk H, S, I, A
$rekapHeaders = [
    'H' => 'C6EFCE', // Hadir (Hijau Pastel)
    'S' => 'FFE699', // Sakit (Kuning Pastel)
    'I' => 'BDD7EE', // Izin (Biru Pastel)
    'A' => 'F4B084'  // Alpha (Merah Pastel)
];
foreach ($rekapHeaders as $kode => $color) {
    $sheet->setCellValue($col . '13', $kode);
    $sheet->getStyle($col . '13')->applyFromArray([
        'font' => ['bold' => true],
        'alignment' => [
            'horizontal' => Alignment::HORIZONTAL_CENTER,
            'vertical' => Alignment::VERTICAL_CENTER
        ],
        'fill' => [
            'fillType' => Fill::FILL_SOLID,
            'startColor' => ['rgb' => $color]
        ],
    ]);
    $col++;
}

// === ISI DATA SISWA ===
$qrySiswa = mysqli_query($con, "SELECT * FROM tb_siswa WHERE id_mkelas='$kelas' ORDER BY nama_siswa ASC");
$row = 14;
$no = 1;
while ($s = mysqli_fetch_array($qrySiswa)) {
    $sheet->setCellValue("A$row", $no++);
    $sheet->setCellValue("B$row", $s['nis']);
    $sheet->setCellValue("C$row", $s['nama_siswa']);
    $sheet->setCellValue("D$row", $s['jk']);

    $col = 'E';
    for ($i = 1; $i <= $tglTerakhir; $i++) {
        $tgl = "$tahun-$bulan-" . str_pad($i, 2, '0', STR_PAD_LEFT);
        $ket = mysqli_fetch_array(mysqli_query($con, "SELECT ket FROM _logabsensi 
            WHERE id_siswa='{$s['id_siswa']}' 
            AND id_mengajar='$pelajaran'
            AND tgl_absen='$tgl' LIMIT 1"));
        $sheet->setCellValue($col . $row, $ket['ket'] ?? '');
        $col++;
    }

    foreach (array_keys($rekapHeaders) as $kode) {
        $jml = mysqli_fetch_array(mysqli_query($con, "SELECT COUNT(*) as jml FROM _logabsensi 
            WHERE id_siswa = '{$s['id_siswa']}'
            AND id_mengajar = '$pelajaran'
            AND MONTH(tgl_absen) = '$bulan'
            AND YEAR(tgl_absen) = '$tahun'
            AND ket = '$kode'"));
        $sheet->setCellValue($col++ . $row, $jml['jml'] ?: '-');
    }

    $row++;
}

// === FORMAT AKHIR ===
$lastCol = $sheet->getHighestDataColumn();
$lastRow = $sheet->getHighestRow();

$sheet->getStyle("A13:{$lastCol}{$lastRow}")->applyFromArray([
    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
    'alignment' => [
        'horizontal' => Alignment::HORIZONTAL_CENTER,
        'vertical' => Alignment::VERTICAL_CENTER,
        'wrapText' => true,
    ],
]);

$sheet->getStyle("A13:D13")->getFont()->setBold(true);
$sheet->getStyle("A13:D13")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('D9E1F2');

foreach (range('A', $lastCol) as $colLetter) {
    $sheet->getColumnDimension($colLetter)->setAutoSize(true);
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