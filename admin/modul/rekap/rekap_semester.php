<?php
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

$id_mengajar = intval($_GET['id_mengajar'] ?? 0);
$id_mkelas = intval($_GET['id_mkelas'] ?? 0);
$semester_id = intval($_GET['semester_id'] ?? 0);

if (!$id_mengajar || !$id_mkelas || !$semester_id) {
    die("Parameter tidak lengkap.");
}

// Hanya ambil kehadiran yang diinginkan (H, I, S, A)
$kehadiran = ['H', 'I', 'S', 'A'];

$d = mysqli_fetch_array(mysqli_query($con, "SELECT * FROM tb_mengajar 
    JOIN tb_guru ON tb_mengajar.id_guru=tb_guru.id_guru
    JOIN tb_master_mapel ON tb_mengajar.id_mapel=tb_master_mapel.id_mapel
    JOIN tb_mkelas ON tb_mengajar.id_mkelas=tb_mkelas.id_mkelas
    JOIN tb_semester ON tb_mengajar.id_semester=tb_semester.id_semester
    JOIN tb_thajaran ON tb_mengajar.id_thajaran=tb_thajaran.id_thajaran
    WHERE tb_mengajar.id_mengajar='$id_mengajar' AND tb_mengajar.id_mkelas='$id_mkelas'"));

$walas = mysqli_fetch_array(mysqli_query($con, "SELECT * FROM tb_walikelas 
    JOIN tb_guru ON tb_walikelas.id_guru=tb_guru.id_guru 
    WHERE tb_walikelas.id_mkelas='$id_mkelas'"));

$kepsek = mysqli_fetch_array(mysqli_query($con, "SELECT * FROM tb_kepsek WHERE status='Y' LIMIT 1"));

// Base64 encode logo
$logo_path = $_SERVER['DOCUMENT_ROOT'] . '/assets/img/jatim.png';
$logo_data = '';
if (file_exists($logo_path)) {
    $logo_data = base64_encode(file_get_contents($logo_path));
}

// Set up Dompdf options
$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('isPhpEnabled', true);
$dompdf = new Dompdf($options);

$html = '
<style>
    body { font-family: "Times New Roman", serif; font-size: 12px; }
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
                <p style="margin:0;">Telp. (0341) 754086 E-mail: <a href="mailto:smkn10_malang@yahoo.co.id" style="color: blue;">smkn10_malang@yahoo.co.id</a></p>
            </td>
        </tr>
    </table>
</div>
<h4 style="text-align:center;">REKAP ABSENSI SEMESTER '.strtoupper($d['semester']).'</h4>
<table class="info">
    <tr><td><b>Kelas</b></td><td>: '.$d['nama_kelas'].'</td></tr>
    <tr><td><b>Semester</b></td><td>: '.$d['semester'].'</td></tr>
    <tr><td><b>Tahun Ajaran</b></td><td>: '.$d['tahun_ajaran'].'</td></tr>
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
$siswa = mysqli_query($con, "SELECT * FROM tb_siswa WHERE id_mkelas='$id_mkelas' ORDER BY nama_siswa ASC");
while($s = mysqli_fetch_array($siswa)) {
    $html .= '<tr>
        <td>'.$no++.'</td>
        <td>'.$s['nis'].'</td>
        <td style="text-align:left;">'.$s['nama_siswa'].'</td>
        <td>'.$s['jk'].'</td>';

    foreach ($kehadiran as $kode) {
        $q = mysqli_query($con, "SELECT COUNT(*) as jml FROM _logabsensi a
            JOIN tb_mengajar m ON a.id_mengajar = m.id_mengajar
            WHERE a.id_siswa='{$s['id_siswa']}'
            AND m.id_mkelas = '$id_mkelas'
            AND m.id_semester = '$semester_id'
            AND a.ket = '$kode'");
        $jml = mysqli_fetch_array($q);
        $html .= "<td>{$jml['jml']}</td>";
    }

    $html .= '</tr>';
}

$html .= '</tbody></table>
<br><br><table width="100%">
<tr>
<td width="50%" style="text-align:left;">
    <p>Kepala Sekolah</p><br><br><br><br><br>
    <p><u>'.$kepsek['nama_kepsek'].'</u><br>NIP. '.$kepsek['nip'].'</p>
</td>
<td width="50%" style="text-align:right;">
    <p>Malang, '.tgl_indo(date('Y-m-d')).'</p>
    <p>Guru Pengampu</p><br><br><br><br><br>
    <p><u>'.$d['nama_guru'].'</u><br>NIP. '.$d['nip'].'</p>
</td>
</tr>
</table>';

$filename = "Rekap_Absensi_Semester_{$d['nama_kelas']}_{$d['semester']}.pdf";
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'landscape');
$dompdf->render();
$dompdf->stream($filename, array('Attachment' => 0));