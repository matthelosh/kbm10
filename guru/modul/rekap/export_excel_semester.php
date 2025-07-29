<?php
require_once '../../../vendor/autoload.php';
include "../../../config/db.php";

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Font;

// Params
$kelas = intval($_GET['kelas'] ?? 0);
$pelajaran = intval($_GET['pelajaran'] ?? 0);
$semester_id = intval($_GET['semester_id'] ?? 0);
if ($kelas == 0 || $pelajaran == 0) exit('Parameter kelas atau pelajaran tidak lengkap.');

// ✅ Ambil semester aktif jika tidak ada parameter semester_id
if ($semester_id === 0) {
    $aktif = mysqli_fetch_assoc(mysqli_query($con, "
        SELECT s.id_semester, s.semester, t.tahun_ajaran
        FROM tb_semester s
        INNER JOIN tb_thajaran t ON s.id_thajaran = t.id_thajaran
        WHERE s.status=1 AND t.status=1
        ORDER BY t.tahun_ajaran DESC, s.semester DESC
        LIMIT 1
    "));
    if (!$aktif) {
        // Fallback ke semester terbaru jika tidak ada yang aktif
        $aktif = mysqli_fetch_assoc(mysqli_query($con, "
            SELECT s.id_semester, s.semester, t.tahun_ajaran
            FROM tb_semester s
            INNER JOIN tb_thajaran t ON s.id_thajaran = t.id_thajaran
            ORDER BY t.tahun_ajaran DESC, s.semester DESC
            LIMIT 1
        "));
    }
    $semester_id = intval($aktif['id_semester'] ?? 0);
}

if ($semester_id === 0) exit('Semester tidak ditemukan.');

// ✅ Ambil info semester terpilih
$infoSemester = mysqli_fetch_assoc(mysqli_query($con, "
    SELECT s.semester, t.tahun_ajaran
    FROM tb_semester s
    JOIN tb_thajaran t ON s.id_thajaran = t.id_thajaran
    WHERE s.id_semester = '$semester_id'
")) ?: ['semester'=>'Ganjil', 'tahun_ajaran'=>date('Y').'/'.(date('Y')+1)];
$namaSemester = strtoupper($infoSemester['semester']); // Ganjil / Genap

// ✅ Ambil nama kelas
$qKelas = mysqli_fetch_assoc(mysqli_query($con, "
    SELECT nama_kelas FROM tb_mkelas WHERE id_mkelas = '$kelas'
"));
$namaKelas = $qKelas['nama_kelas'] ?? 'Kelas Tidak Diketahui';

// ✅ Periode semester
$tahunAjaran = explode('/', $infoSemester['tahun_ajaran']);
$tahunAwal = intval($tahunAjaran[0] ?? date('Y'));
$tahunAkhir = intval($tahunAjaran[1] ?? (date('Y')+1));

if (strtolower($infoSemester['semester']) === 'ganjil') {
    $startDate = "$tahunAwal-07-01";
    $endDate = "$tahunAwal-12-31";
} else { // Genap
    $startDate = "$tahunAkhir-01-01";
    $endDate = "$tahunAkhir-06-30";
}

$walas = mysqli_fetch_array(mysqli_query($con, "SELECT * FROM tb_walikelas 
	JOIN tb_guru ON tb_walikelas.id_guru=tb_guru.id_guru 
	WHERE tb_walikelas.id_mkelas='$kelas'"));
	
	
// Spreadsheet
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$spreadsheet->getDefaultStyle()->getFont()->setName('Times New Roman')->setSize(12);

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

// === INFO KELAS ===
$sheet->setCellValue('A6', 'Kelas')->setCellValue('B6', $namaKelas);
$sheet->setCellValue('A7', 'Semester')->setCellValue('B7', $infoSemester['semester']);
$sheet->setCellValue('A8', 'Tahun Ajaran')->setCellValue('B8', $infoSemester['tahun_ajaran']);
$sheet->setCellValue('A9', 'Wali Kelas')->setCellValue('B9', $walas['nama_guru']);
$sheet->getStyle('A6:A9')->getFont()->setBold(true);

// === JUDUL REKAP ===
$sheet->mergeCells('A10:H10')->setCellValue('A10', "REKAP ABSENSI SEMESTER $namaSemester - $infoSemester[tahun_ajaran]");
$sheet->getStyle('A10')->getFont()->setBold(true)->setSize(12);
$sheet->getStyle('A10')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

// === HEADER TABEL ===
$headers = ['NO', 'NIS', 'NAMA SISWA', 'L/P', 'Hadir', 'Izin', 'Sakit', 'Alpha'];
$sheet->fromArray($headers, NULL, 'A12');
$sheet->getStyle('A12:H12')->getFont()->setBold(true);
$sheet->getStyle('A12:H12')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

// === ISI DATA SISWA ===
$kehadiran = ['H', 'I', 'S', 'A'];
$row = 13;
$no = 1;
$qrySiswa = mysqli_query($con, "
    SELECT id_siswa, nis, nama_siswa, jk
    FROM tb_siswa
    WHERE id_mkelas='$kelas'
    ORDER BY nama_siswa ASC
");
while ($s = mysqli_fetch_assoc($qrySiswa)) {
    $data = [$no++, $s['nis'], $s['nama_siswa'], $s['jk']];
    foreach ($kehadiran as $kode) {
        $jml = mysqli_fetch_assoc(mysqli_query($con, "
            SELECT COUNT(*) AS jml
            FROM _logabsensi
            WHERE id_siswa='{$s['id_siswa']}'
            AND id_mengajar='$pelajaran'
            AND tgl_absen BETWEEN '$startDate' AND '$endDate'
            AND ket='$kode'
        "))['jml'] ?? 0;
        $data[] = intval($jml);
    }
    $sheet->fromArray($data, NULL, "A$row");
    $row++;
}

// === FORMAT BORDER & AUTOFIT ===
$lastRow = $sheet->getHighestRow();
$sheet->getStyle("A12:H$lastRow")->applyFromArray([
    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
    'alignment' => [
        'horizontal' => Alignment::HORIZONTAL_CENTER,
        'vertical' => Alignment::VERTICAL_CENTER
    ],
]);

foreach (range('A', 'H') as $col) {
    $sheet->getColumnDimension($col)->setAutoSize(true);
}

// === OUTPUT FILE ===
$filename = "Rekap_Absensi_Semester_{$namaSemester}_KELAS_{$namaKelas}.xlsx";
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header("Content-Disposition: attachment;filename=\"$filename\"");
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
?>