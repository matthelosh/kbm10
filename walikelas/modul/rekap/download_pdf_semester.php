<?php
ob_start();
require_once '../../../vendor/autoload.php';
include "../../../config/db.php";

use Dompdf\Dompdf;
use Dompdf\Options;

function tgl_indo($tanggal) {
    $bulan = [
        1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];
    $tgl = date('d', strtotime($tanggal));
    $bln = $bulan[(int)date('m', strtotime($tanggal))];
    $thn = date('Y', strtotime($tanggal));
    return "$tgl $bln $thn";
}

// ✅ Ambil parameter
$id_mengajar = intval($_GET['id_mengajar'] ?? 0);
$id_mkelas   = intval($_GET['id_mkelas'] ?? 0);
$semester_id = intval($_GET['semester_id'] ?? 0);

if (!$id_mengajar || !$id_mkelas || !$semester_id) {
    die("Parameter tidak lengkap.");
}

$kehadiran = ['H', 'I', 'S', 'A']; // Hadir, Izin, Sakit, Alpha

// ✅ Ambil info mengajar & kelas
$d = mysqli_fetch_assoc(mysqli_query($con, "
    SELECT 
        g.nama_guru, g.nip, 
        k.nama_kelas, 
        m.mapel, 
        s.semester, 
        t.tahun_ajaran
    FROM tb_mengajar mg
    JOIN tb_guru g ON mg.id_guru = g.id_guru
    JOIN tb_master_mapel m ON mg.id_mapel = m.id_mapel
    JOIN tb_mkelas k ON mg.id_mkelas = k.id_mkelas
    JOIN tb_semester s ON mg.id_semester = s.id_semester
    JOIN tb_thajaran t ON mg.id_thajaran = t.id_thajaran
    WHERE mg.id_mengajar = '$id_mengajar'
")) or die("Data mengajar tidak ditemukan.");

// ✅ Ambil wali kelas
$walas = mysqli_fetch_assoc(mysqli_query($con, "
    SELECT g.nama_guru
    FROM tb_walikelas w
    JOIN tb_guru g ON w.id_guru = g.id_guru
    WHERE w.id_mkelas = '$id_mkelas'
")) ?: ['nama_guru' => '-'];

// ✅ Ambil kepala sekolah
$kepsek = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM tb_kepsek WHERE status='Y' LIMIT 1")) ?: [];
$nama_kepsek = $kepsek['nama_kepsek'] ?? 'Kepala Sekolah';
$nip_kepsek  = $kepsek['nip'] ?? '-';
$nip_guru    = $d['nip'] ?? '-';

// ✅ Ambil periode semester dari tb_semester & tb_thajaran
$infoSemester = mysqli_fetch_assoc(mysqli_query($con, "
    SELECT s.semester, t.tahun_ajaran
    FROM tb_semester s
    JOIN tb_thajaran t ON s.id_thajaran = t.id_thajaran
    WHERE s.id_semester = '$semester_id'
")) ?: ['semester'=>'Ganjil', 'tahun_ajaran'=>date('Y').'/'.(date('Y')+1)];
$tahun = explode('/', $infoSemester['tahun_ajaran']);
$tahunAwal = intval($tahun[0] ?? date('Y'));
$tahunAkhir = intval($tahun[1] ?? (date('Y')+1));

$startDate = strtolower($infoSemester['semester']) === 'ganjil'
    ? "$tahunAwal-07-01" : "$tahunAkhir-01-01";
$endDate = strtolower($infoSemester['semester']) === 'ganjil'
    ? "$tahunAwal-12-31" : "$tahunAkhir-06-30";

// ✅ Logo base64
$logo_path = $_SERVER['DOCUMENT_ROOT'] . '/kbm/assets/img/image.png';
$logo_data = file_exists($logo_path) ? base64_encode(file_get_contents($logo_path)) : '';

$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('isPhpEnabled', true);
$dompdf = new Dompdf($options);

// ✅ HTML dimulai
$html = '
<style>
    body { font-family: timesnewroman, serif; font-size: 10pt; }
    .header { text-align: center; }
    .header h3 { margin: 0; font-size: 14pt; }
    .header p { margin: 0; font-size: 10pt; }
    .info td { padding: 3px 5px; vertical-align: top; }
    .absensi { border-collapse: collapse; width: 100%; margin-top: 10px; }
    .absensi th, .absensi td { border: 1px solid #000; text-align: center; padding: 3px; }
    .absensi th { background-color: #f2f2f2; font-weight: bold; }
</style>

<div class="header">
    <table style="width:100%; border-bottom: 3px solid black; padding-bottom: 5px; margin-bottom: 10px;">
        <tr>
            <td style="width:80px; vertical-align: middle;">
                <img src="data:image/png;base64,'.$logo_data.'" style="width: 60px;">
            </td>
            <td style="text-align:center;">
                <p style="margin:0;">PEMERINTAH PROVINSI JAWA TIMUR</p>
                <p style="margin:0;">DINAS PENDIDIKAN</p>
                <h2 style="margin:0;">SMK NEGERI 10 MALANG</h2>
                <p style="margin:0;">Jl. Raya Tlogowaru, Kedungkandang, Malang, Jawa Timur 65133</p>
                <p style="margin:0;">Telp. (0341) 754086 | Email: smkn10_malang@yahoo.co.id</p>
            </td>
        </tr>
    </table>
</div>

<h4 style="text-align:center;">REKAP ABSENSI SEMESTER '.strtoupper($infoSemester['semester']).' ('.$infoSemester['tahun_ajaran'].')</h4>

<table class="info">
    <tr><td><b>Kelas</b></td><td>: '.$d['nama_kelas'].'</td></tr>
    <tr><td><b>Tahun Ajaran</b></td><td>: '.$infoSemester['tahun_ajaran'].'</td></tr>
    <tr><td><b>Guru Mapel</b></td><td>: '.$d['nama_guru'].'</td></tr>
    <tr><td><b>Mapel</b></td><td>: '.$d['mapel'].'</td></tr>
    <tr><td><b>Wali Kelas</b></td><td>: '.$walas['nama_guru'].'</td></tr>
</table>

<table class="absensi">
    <thead>
        <tr>
            <th>No</th>
            <th>NIS</th>
            <th>Nama Siswa</th>
            <th>L/P</th>
            <th>H</th>
            <th>I</th>
            <th>S</th>
            <th>A</th>
        </tr>
    </thead>
    <tbody>';

$no = 1;
$siswa = mysqli_query($con, "
    SELECT id_siswa, nis, nama_siswa, jk
    FROM tb_siswa
    WHERE id_mkelas='$id_mkelas'
    ORDER BY nama_siswa ASC
");
while ($s = mysqli_fetch_assoc($siswa)) {
    $html .= '<tr>
        <td>'.$no++.'</td>
        <td>'.$s['nis'].'</td>
        <td style="text-align:left;">'.$s['nama_siswa'].'</td>
        <td>'.$s['jk'].'</td>';

    foreach ($kehadiran as $kode) {
        $q = mysqli_query($con, "
            SELECT COUNT(*) AS jml
            FROM _logabsensi a
            JOIN tb_mengajar m ON a.id_mengajar = m.id_mengajar
            WHERE a.id_siswa='{$s['id_siswa']}'
            AND m.id_mkelas = '$id_mkelas'
            AND m.id_semester = '$semester_id'
            AND a.ket='$kode'
        ");
        $jml = mysqli_fetch_assoc($q)['jml'] ?? 0;
        $html .= "<td>$jml</td>";
    }

    $html .= '</tr>';
}

$html .= '</tbody></table>

<br><br>
<table width="100%">
<tr>
    <td width="50%" style="text-align:left;">
        <p>Kepala Sekolah</p><br><br><br>
        <p><u>' . $nama_kepsek . '</u><br>NIP. ' . $nip_kepsek . '</p>
    </td>
    <td width="50%" style="text-align:right;">
        <p>Malang, ' . tgl_indo(date('Y-m-d')) . '</p>
        <p>Guru Pengampu</p><br><br><br>
        <p><u>' . $d['nama_guru'] . '</u><br>NIP. ' . $nip_guru . '</p>
    </td>
</tr>
</table>';

ob_end_clean();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'landscape');
$dompdf->render();
$filename = "Rekap_Absensi_Semester_{$d['nama_kelas']}_{$infoSemester['semester']}.pdf";
$dompdf->stream($filename, ['Attachment' => 0]);
exit;
?>