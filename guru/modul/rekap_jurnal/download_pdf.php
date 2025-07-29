<?php
ob_start();
require_once __DIR__ . '/../../../vendor/autoload.php';
include '../../../config/db.php';

//require_once $_SERVER['DOCUMENT_ROOT'] . '/kbm/config/db.php';
//require_once $_SERVER['DOCUMENT_ROOT'] . '../../../vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

// Fungsi tanggal Indo
function bulanIndo($tanggal) {
    $bulan = [
        '01' => 'Januari', '02' => 'Februari', '03' => 'Maret',
        '04' => 'April', '05' => 'Mei', '06' => 'Juni',
        '07' => 'Juli', '08' => 'Agustus', '09' => 'September',
        '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
    ];
    $tgl = strtotime($tanggal);
    $bln = date('m', $tgl);
    $thn = date('Y', $tgl);
    return $bulan[$bln] . ' ' . $thn;
}

function tanggalIndo($tanggal) {
    $bulan = [
        '01' => 'Januari', '02' => 'Februari', '03' => 'Maret',
        '04' => 'April', '05' => 'Mei', '06' => 'Juni',
        '07' => 'Juli', '08' => 'Agustus', '09' => 'September',
        '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
    ];
    $tgl = date('d', strtotime($tanggal));
    $bln = date('m', strtotime($tanggal));
    $thn = date('Y', strtotime($tanggal));
    return $tgl . ' ' . $bulan[$bln] . ' ' . $thn;
}

// Session
session_start();
if (!isset($_SESSION['guru'])) {
    echo "<script>alert('Anda harus login terlebih dahulu'); window.location='../../user.php';</script>";
    exit;
}

$id_guru = $_SESSION['guru'];

// Filter
$tgl_awal = $_GET['tgl_awal'] ?? '';
$tgl_akhir = $_GET['tgl_akhir'] ?? '';
$id_kelas = $_GET['kelas'] ?? '';

// Query
$where = "WHERE jm.id_guru = '$id_guru'";
if (!empty($tgl_awal) && !empty($tgl_akhir)) {
    $where .= " AND jm.tanggal BETWEEN '$tgl_awal' AND '$tgl_akhir'";
}
if (!empty($id_kelas)) {
    $where .= " AND jm.id_kelas = '$id_kelas'";
}

$query = "
SELECT jm.*, mk.nama_kelas, g.nama_guru, g.nip
FROM jurnal_mengajar jm
JOIN tb_mkelas mk ON jm.id_kelas = mk.id_mkelas
JOIN tb_guru g ON jm.id_guru = g.id_guru
$where
ORDER BY jm.tanggal DESC, jm.jam_ke ASC
";

$result = $con->query($query);
if (!$result || $result->num_rows == 0) {
    echo "<script>alert('Tidak ada data jurnal untuk periode/kriteria yang dipilih'); window.history.back();</script>";
    exit;
}

// Data guru
$row1 = $result->fetch_assoc();
$nama_guru = $row1['nama_guru'];
$nip_guru = $row1['nip'];
$mapel = $row1['mapel'];
$result->data_seek(0);

// Periode
$periode_awal = $tgl_awal ?: $row1['tanggal'];
$periode_akhir = $tgl_akhir ?: $row1['tanggal'];
$judul_bulan = bulanIndo($periode_awal);

// Ambil Kepala Sekolah dinamis
$q_kepsek = $con->query("SELECT nama_kepsek, nip FROM tb_kepsek WHERE status='Y' LIMIT 1");
if ($q_kepsek && $q_kepsek->num_rows > 0) {
    $kepsek = $q_kepsek->fetch_assoc();
    $nama_kepsek = $kepsek['nama_kepsek'];
    $nip_kepsek = $kepsek['nip'];
} else {
    $nama_kepsek = "Nama Kepala Sekolah";
    $nip_kepsek = "NIP Kepala Sekolah";
}

// Logo
$logo_path = $_SERVER['DOCUMENT_ROOT'].'/assets/img/jatim.png';
$logo_data = file_exists($logo_path) ? base64_encode(file_get_contents($logo_path)) : null;

// HTML
$html = '
<style>
    body { font-family: "Times New Roman", serif; font-size: 12px; }
    .kop table { width: 100%; border-bottom: 3px solid black; padding-bottom: 5px; margin-bottom: 10px; border-collapse: collapse; }
    .kop td.logo { width: 50px; padding-right: 10px; vertical-align: middle; }
    .kop img { width: 50px; height: auto; display: block; }
    .kop td.center { text-align: center; vertical-align: middle; }
    .kop h2, .kop h3, .kop p { margin: 0; padding: 0; line-height: 1.1; }
    table.data { border-collapse: collapse; width: 100%; margin-top: 20px; }
    table.data th, table.data td { border: 1px solid #000; padding: 6px; text-align: center; }
</style>

<div class="kop">
    <table>
        <tr>
            <td class="logo">';
// Logo dinamis
$html .= $logo_data ? '<img src="data:image/png;base64,'.$logo_data.'" alt="Logo Jatim">' : $logo_path;
$html .= '</td>
            <td class="center">
                <p>PEMERINTAH PROVINSI JAWA TIMUR</p>
                <p>DINAS PENDIDIKAN</p>
                <h2>SMK NEGERI 10 MALANG</h2>
                <p>Jalan Raya Tlogowaru, Kedungkandang, Malang, Jawa Timur 65133</p>
                <p>Telp. (0341) 754086 E-mail : <a href="mailto:smkn10_malang@yahoo.co.id" style="color: blue;">smkn10_malang@yahoo.co.id</a></p>
            </td>
        </tr>
    </table>
</div>

<h4 style="text-align:center; margin-top: 30px;">REKAP JURNAL BULAN '.htmlspecialchars($judul_bulan).'</h4>

<p>
    <strong>Nama Guru:</strong> '.htmlspecialchars($nama_guru).'<br>
    <strong>Mata Pelajaran:</strong> '.htmlspecialchars($mapel).'<br>
    <strong>Periode:</strong> '.tanggalIndo($periode_awal).' s.d. '.tanggalIndo($periode_akhir).'
</p>

<table class="data">
    <thead>
        <tr>
            <th>No</th>
            <th>Tanggal</th>
            <th>Jam Ke</th>
            <th>Kelas</th>
            <th>Mapel</th>
            <th>Uraian</th>
            <th>Catatan</th>
        </tr>
    </thead>
    <tbody>';
$no = 1;
while ($row = $result->fetch_assoc()) {
    $html .= '<tr>
        <td>'.$no++.'</td>
        <td>'.date('d/m/Y', strtotime($row['tanggal'])).'</td>
        <td>'.htmlspecialchars($row['jam_ke']).'</td>
        <td>'.htmlspecialchars($row['nama_kelas']).'</td>
        <td>'.htmlspecialchars($row['mapel']).'</td>
        <td>'.htmlspecialchars($row['uraian_kegiatan']).'</td>
        <td>'.htmlspecialchars($row['catatan_perkembangan']).'</td>
    </tr>';
}
$html .= '</tbody></table>

<br><br>
<table style="width:100%; margin-top:60px;">
    <tr>
        <td style="width:50%; text-align:left; vertical-align:top;">
            <p>Kepala Sekolah</p><br><br><br>
            <u>'.htmlspecialchars($nama_kepsek).'</u><br>
            NIP. '.htmlspecialchars($nip_kepsek).'
        </td>
        <td style="width:50%; text-align:right; vertical-align:top;">
            <p>Malang, '.tanggalIndo(date('Y-m-d')).'</p>
            <p>Guru Pengampu</p><br><br><br>
            <p><u>'.htmlspecialchars($nama_guru).'</u><br>
            NIP. '.htmlspecialchars($nip_guru).'</p>
        </td>
    </tr>
</table>';

$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'landscape');
$dompdf->render();
ob_end_clean();
$dompdf->stream("Rekap_Jurnal_Mengajar.pdf", ["Attachment" => false]);
exit;
?>
