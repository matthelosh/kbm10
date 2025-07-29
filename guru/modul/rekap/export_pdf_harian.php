<?php
ob_start(); // ✅ penting untuk cegah output awal
require_once __DIR__ . '/../../../vendor/autoload.php';
include '../../../config/db.php';

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

if (!isset($_GET['id_mengajar']) || !isset($_GET['tanggal'])) {
    die("Parameter tidak lengkap.");
}

$id_mengajar = intval($_GET['id_mengajar']);
$tanggal = $_GET['tanggal'];

$q = mysqli_query($con, "SELECT m.id_mkelas, m.id_guru, k.nama_kelas, g.nama_guru, g.nip, mp.mapel 
    FROM tb_mengajar m
    JOIN tb_mkelas k ON m.id_mkelas = k.id_mkelas
    JOIN tb_guru g ON m.id_guru = g.id_guru
    JOIN tb_master_mapel mp ON m.id_mapel = mp.id_mapel
    WHERE m.id_mengajar = '$id_mengajar'");

$d = mysqli_fetch_assoc($q);
if (!$d) die("Data mengajar tidak ditemukan.");

$id_mkelas = $d['id_mkelas'];
$siswa = mysqli_query($con, "SELECT * FROM tb_siswa WHERE id_mkelas = '$id_mkelas'");

// Kepala sekolah
$kepsek = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM tb_kepsek WHERE status='Y' LIMIT 1"));

// ✅ Fix path logo
$logo_path = $_SERVER['DOCUMENT_ROOT'] . '/assets/img/jatim.png';
$logo_data = file_exists($logo_path) ? base64_encode(file_get_contents($logo_path)) : $logo_path;

$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('isPhpEnabled', true);
$options->set('defaultFont', 'Times New Roman'); // optional
$dompdf = new Dompdf($options);

$html = '
<style>
    body { font-family: "Times New Roman", serif; font-size: 12px; }
    .kop table {
        width: 100%;
        border-bottom: 3px solid black;
        margin-bottom: 10px;
    }
    .kop img { width: 60px; height: auto; }
    .kop td { text-align: center; vertical-align: middle; }
    table.data {
        border-collapse: collapse;
        width: 100%;
        margin-top: 20px;
    }
    table.data th, table.data td {
        border: 1px solid #000;
        padding: 6px;
        text-align: center;
    }
</style>

<div class="kop">
    <table>
        <tr>
            <td style="width:80px;">
                <img src="data:image/png;base64,' . $logo_data . '">
            </td>
            <td>
                <p>PEMERINTAH PROVINSI JAWA TIMUR</p>
                <p>DINAS PENDIDIKAN</p>
                <h2>SMK NEGERI 10 MALANG</h2>
                <p>Jl. Raya Tlogowaru, Kedungkandang, Malang</p>
                <p>Telp. (0341) 754086 | Email: smkn10_malang@yahoo.co.id</p>
            </td>
        </tr>
    </table>
</div>


<h4 style="text-align:center;">REKAP ABSENSI HARIAN</h4>
<table>
    <tr><td><b>Guru Mapel</b></td><td>: ' . $d['nama_guru'] . '</td></tr>
    <tr><td><b>Kelas</b></td><td>: ' . $d['nama_kelas'] . '</td></tr>
    <tr><td><b>Mapel</b></td><td>: ' . $d['mapel'] . '</td></tr>
    <tr><td><b>Tanggal</b></td><td>: ' . tgl_indo($tanggal) . '</td></tr>
</table>

<table class="data">
    <thead>
        <tr>
            <th>No</th>
            <th>NIS</th>
            <th>Nama Siswa</th>
            <th>Hadir</th>
            <th>Izin</th>
            <th>Sakit</th>
            <th>Alpha</th> <!-- ✅ Hapus Terlambat -->
        </tr>
    </thead>
    <tbody>';

$no = 1;
$total = ['H' => 0, 'I' => 0, 'S' => 0, 'A' => 0]; // ✅ Hapus T
while ($s = mysqli_fetch_array($siswa)) {
    $rekap = ['H' => '', 'I' => '', 'S' => '', 'A' => '']; // ✅ Hapus T
    $absen = mysqli_fetch_assoc(mysqli_query($con, "SELECT ket FROM _logabsensi WHERE id_siswa='{$s['id_siswa']}' AND id_mengajar='$id_mengajar' AND tgl_absen='$tanggal'"));
    if ($absen) {
        $kode = $absen['ket'];
        $rekap[$kode] = 'V';
        if (isset($total[$kode])) $total[$kode]++;
    }

    $html .= "<tr>
        <td>{$no}</td>
        <td>{$s['nis']}</td>
        <td style='text-align:left;'>{$s['nama_siswa']}</td>
        <td>{$rekap['H']}</td>
        <td>{$rekap['I']}</td>
        <td>{$rekap['S']}</td>
        <td>{$rekap['A']}</td>
    </tr>";
    $no++;
}

$html .= "<tr style='font-weight:bold; background:#f0f0f0;'>
    <td colspan='3'>Total</td>
    <td>{$total['H']}</td>
    <td>{$total['I']}</td>
    <td>{$total['S']}</td>
    <td>{$total['A']}</td>
</tr>";

$html .= '</tbody></table>
<br><br>
<table width="100%">
<tr>
    <td style="width:50%; text-align:left;">
        <p>Kepala Sekolah</p><br><br><br>
        <p><u>' . ($kepsek['nama_kepsek'] ?? 'Kepala Sekolah') . '</u><br>NIP. ' . ($kepsek['nip'] ?? '-') . '</p>
    </td>
    <td style="width:50%; text-align:right;">
        <p>Malang, ' . tgl_indo(date('Y-m-d')) . '</p>
        <p>Guru Pengampu</p><br><br><br>
        <p><u>' . $d['nama_guru'] . '</u><br>NIP. ' . $d['nip'] . '</p>
    </td>
</tr>
</table>';

$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

ob_end_clean(); // ✅ cegah konflik output dari luar dompdf
$dompdf->stream("Rekap_Absen_Harian.pdf", ["Attachment" => 0]);