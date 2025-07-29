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

// Ambil parameter
$pelajaran = intval($_GET['pelajaran'] ?? $_GET['id_mengajar'] ?? 0);
$kelas = intval($_GET['kelas'] ?? 0);
$bulanParam = $_GET['bulan'] ?? date('Y-m');
list($tahun, $bulan) = explode('-', $bulanParam);
$tglBulan = "$tahun-$bulan-01";
$tglTerakhir = date('t', strtotime($tglBulan));

// Ambil data guru/mengajar
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

$kepsek = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM tb_kepsek WHERE status='Y' LIMIT 1"));
$nama_kepsek = $kepsek['nama_kepsek'] ?? 'Kepala Sekolah';
$nip_kepsek = $kepsek['nip'] ?? '-';
$nip_guru = $d['nip'] ?? '-';

// Logo ke base64
$logo_path = $_SERVER['DOCUMENT_ROOT'] . '/assets/img/image.png';
$logo_data = file_exists($logo_path) ? base64_encode(file_get_contents($logo_path)) : '';

$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('isPhpEnabled', true);
$dompdf = new Dompdf($options);

// Mulai HTML isi PDF
$html = '
<style>
    body { font-family: Times, serif; font-size: 10pt; }
    .header { text-align: center; }
    .info td { padding: 3px 5px; vertical-align: top; }
    .absensi { border-collapse: collapse; width: 100%; margin-top: 10px; }
    .absensi th, .absensi td { border: 1px solid #000; text-align: center; padding: 3px; }
    .absensi th { font-weight: bold; background: #f0f0f0; }
</style>

<div class="header">
    <table style="width:100%; border-bottom: 3px solid black; margin-bottom: 10px;">
        <tr>
            <td style="width:80px;"><img src="data:image/png;base64,' . $logo_data . '" style="width:60px;"></td>
            <td style="text-align:center;">
                <p>PEMERINTAH PROVINSI JAWA TIMUR</p>
                <p>DINAS PENDIDIKAN</p>
                <h2>SMK NEGERI 10 MALANG</h2>
                <p>Jl. Raya Tlogowaru, Kedungkandang, Malang</p>
                <p>Telp. (0341) 754086 | Email: smkn10_malang@yahoo.co.id</p>
            </td>
        </tr>
    </table>
</div>

<h4 style="text-align:center;">REKAP ABSENSI BULAN ' . strtoupper(date('F', strtotime($tglBulan))) . ' ' . $tahun . '</h4>

<table class="info">
    <tr><td><b>Kelas</b></td><td>: ' . $d['nama_kelas'] . '</td></tr>
    <tr><td><b>Semester</b></td><td>: ' . $d['semester'] . '</td></tr>
    <tr><td><b>Tahun Ajaran</b></td><td>: ' . $d['tahun_ajaran'] . '</td></tr>
    <tr><td><b>Guru Mapel</b></td><td>: ' . $d['nama_guru'] . '</td></tr>
    <tr><td><b>Mapel</b></td><td>: ' . $d['mapel'] . '</td></tr>
    <tr><td><b>Wali Kelas</b></td><td>: ' . $walas['nama_guru'] . '</td></tr>
</table>

<table class="absensi">
    <thead>
        <tr>
            <th>No</th>
            <th>NIS</th>
            <th>Nama</th>
            <th>L/P</th>';

for ($i = 1; $i <= $tglTerakhir; $i++) {
    $html .= '<th>' . $i . '</th>';
}

// Total H S I A (T dihapus)
$html .= '
            <th>H</th>
            <th>S</th>
            <th>I</th>
            <th>A</th>
        </tr>
    </thead>
    <tbody>';

$no = 1;
$siswa = mysqli_query($con, "SELECT * FROM tb_siswa WHERE id_mkelas = '$kelas' ORDER BY nama_siswa ASC");
while ($s = mysqli_fetch_array($siswa)) {
    $html .= '<tr>
        <td>' . $no++ . '</td>
        <td>' . $s['nis'] . '</td>
        <td style="text-align:left;">' . $s['nama_siswa'] . '</td>
        <td>' . $s['jk'] . '</td>';

    for ($i = 1; $i <= $tglTerakhir; $i++) {
        $tgl = "$tahun-$bulan-" . str_pad($i, 2, '0', STR_PAD_LEFT);
        $ket = mysqli_fetch_array(mysqli_query($con, "SELECT ket FROM _logabsensi 
            WHERE id_siswa='{$s['id_siswa']}' AND id_mengajar='$pelajaran' AND tgl_absen='$tgl' LIMIT 1"));
        $html .= '<td>' . ($ket['ket'] ?? '') . '</td>';
    }

    foreach (['H', 'S', 'I', 'A'] as $kode) {
        $jml = mysqli_fetch_array(mysqli_query($con, "SELECT COUNT(*) as jml FROM _logabsensi 
            WHERE id_siswa = '{$s['id_siswa']}' AND id_mengajar = '$pelajaran' 
            AND MONTH(tgl_absen) = '$bulan' AND YEAR(tgl_absen) = '$tahun' AND ket = '$kode'"));
        $html .= '<td>' . $jml['jml'] . '</td>';
    }

    $html .= '</tr>';
}

$html .= '</tbody></table>

<table style="width:100%; margin-top:20px;">
<tr>
    <td style="width:50%; text-align:left;">
        <p>Kepala Sekolah</p><br><br><br>
        <p><u>' . $nama_kepsek . '</u><br>NIP. ' . $nip_kepsek . '</p>
    </td>
    <td style="width:50%; text-align:right;">
        <p>Malang, ' . tgl_indo(date('Y-m-d')) . '</p>
        <p>Guru Pengampu</p><br><br><br>
        <p><u>' . $d['nama_guru'] . '</u><br>NIP. ' . $nip_guru . '</p>
    </td>
</tr>
</table>';

ob_end_clean(); // Penting agar tidak ada output lain sebelum PDF
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'landscape');
$dompdf->render();
$filename = "Rekap_Absensi_Kelas_{$d['nama_kelas']}_Bulan_" . date('F', strtotime($tglBulan)) . ".pdf";
$dompdf->stream($filename, ["Attachment" => 0]);
exit;
?>