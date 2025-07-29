<?php
ob_start(); // ✅ Bersihkan output buffer di awal

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

function nama_bulan($bulan) {
    $bulanIndo = [
        1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];
    return $bulanIndo[intval($bulan)] ?? 'Bulan Tidak Valid';
}

$pelajaran = intval($_GET['pelajaran']);
$kelas = intval($_GET['kelas']);
$bulan = intval($_GET['bulan']);

// Ambil data mengajar
$d = mysqli_fetch_array(mysqli_query($con, "SELECT * FROM tb_mengajar 
    JOIN tb_guru ON tb_mengajar.id_guru=tb_guru.id_guru
    JOIN tb_master_mapel ON tb_mengajar.id_mapel=tb_master_mapel.id_mapel
    JOIN tb_mkelas ON tb_mengajar.id_mkelas=tb_mkelas.id_mkelas
    JOIN tb_semester ON tb_mengajar.id_semester=tb_semester.id_semester
    JOIN tb_thajaran ON tb_mengajar.id_thajaran=tb_thajaran.id_thajaran
    WHERE tb_mengajar.id_mengajar='$pelajaran' 
    AND tb_mengajar.id_mkelas='$kelas' 
    AND tb_thajaran.status=1 AND tb_semester.status=1"));

// Ambil wali kelas
$walas = mysqli_fetch_array(mysqli_query($con, "SELECT * FROM tb_walikelas 
    JOIN tb_guru ON tb_walikelas.id_guru=tb_guru.id_guru 
    WHERE tb_walikelas.id_mkelas='$kelas'"));

// Ambil semua Kepala Sekolah aktif
$qKepsek = mysqli_query($con, "SELECT nama_kepsek, nip FROM tb_kepsek WHERE status='Y' ORDER BY id_kepsek ASC");
$tanda_tangan_kepsek = '';
if (mysqli_num_rows($qKepsek) > 0) {
    while ($k = mysqli_fetch_array($qKepsek)) {
        $tanda_tangan_kepsek .= '<u>'.htmlspecialchars($k['nama_kepsek']).'</u><br>NIP. '.htmlspecialchars($k['nip']).'<br><br>';
    }
} else {
    $tanda_tangan_kepsek = '<i>Kepala Sekolah Belum Ditentukan</i>';
}

$tglBulan = date("Y-$bulan-01");
$tglTerakhir = date('t', strtotime($tglBulan));
$namaBulan = strtoupper(nama_bulan($bulan));

// Base64 encode logo image
$logo_path = realpath(__DIR__ . '/../..//assets/img/image.png');
$logo_data = '';
if ($logo_path && file_exists($logo_path)) {
    $logo_data = base64_encode(file_get_contents($logo_path));
}

// Set up Dompdf options
$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('isPhpEnabled', true);
$options->set('isRemoteEnabled', true);
$options->set('defaultFont', 'Times');
$options->set('dpi', 96);
$dompdf = new Dompdf($options);

// HTML content
$html = '
<style>
    body { font-family: "Times New Roman", serif; font-size: 11px; margin: 10px; }
    .header { text-align: center; }
    .header h3 { margin: 0; font-size: 14pt; }
    .header p { margin: 0; font-size: 9pt; }
    .info td { padding: 2px 4px; vertical-align: top; font-size: 9pt; }
    .absensi {
        border-collapse: collapse; width: 100%; margin-top: 10px; font-size: 9pt;
    }
    .absensi th, .absensi td {
        border: 1px solid #000; text-align: center; padding: 2px;
    }
    .absensi th { background-color: #f2f2f2; font-weight: bold; }
</style>

<div class="header">
    <table style="width:100%; border-bottom: 3px solid black; margin-bottom: 10px;">
        <tr>
            <td style="width:80px;">
                <img src="data:image/png;base64,'.$logo_data.'" style="width: 60px;">
            </td>
            <td style="text-align:center;">
                <p>PEMERINTAH PROVINSI JAWA TIMUR</p>
                <p>DINAS PENDIDIKAN</p>
                <h2>SMK NEGERI 10 MALANG</h2>
                <p>Jalan Raya Tlogowaru, Kedungkandang, Malang, Jawa Timur 65133</p>
                <p>Telp. (0341) 754086 E-mail: <a href="mailto:smkn10_malang@yahoo.co.id">smkn10_malang@yahoo.co.id</a></p>
            </td>
        </tr>
    </table>
</div>
<h4 style="text-align:center;">REKAP ABSENSI BULAN '.$namaBulan.'</h4>
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
            <th>NO</th>
            <th>NIS</th>
            <th>NAMA</th>
            <th>L/P</th>';
for ($i = 1; $i <= $tglTerakhir; $i++) {
    $html .= '<th>'.$i.'</th>';
}
$html .= '<th>H</th><th>S</th><th>I</th><th>A</th></tr></thead><tbody>';

$no = 1;
$qrySiswa = mysqli_query($con, "SELECT * FROM tb_siswa WHERE id_mkelas='$kelas' ORDER BY nama_siswa ASC");
while ($s = mysqli_fetch_array($qrySiswa)) {
    $html .= '<tr>
        <td>'.$no++.'</td>
        <td>'.$s['nis'].'</td>
        <td style="text-align:left;">'.$s['nama_siswa'].'</td>
        <td>'.$s['jk'].'</td>';
    for ($i = 1; $i <= $tglTerakhir; $i++) {
        $ket = mysqli_fetch_array(mysqli_query($con, "SELECT ket FROM _logabsensi 
            WHERE id_siswa='{$s['id_siswa']}' AND id_mengajar='$pelajaran' 
            AND MONTH(tgl_absen)='$bulan' AND DAY(tgl_absen)='$i' LIMIT 1"));
        $nilai = !empty($ket['ket']) ? $ket['ket'] : '-';
        $html .= '<td>'.$nilai.'</td>';
    }
    foreach (['H', 'S', 'I', 'A'] as $status) {
        $count = mysqli_fetch_array(mysqli_query($con, "SELECT COUNT(*) as jml FROM _logabsensi 
            WHERE id_siswa='{$s['id_siswa']}' AND id_mengajar='$pelajaran' 
            AND MONTH(tgl_absen)='$bulan' AND ket='$status'"));
        $html .= '<td>'.$count['jml'].'</td>';
    }
    $html .= '</tr>';
}
$html .= '</tbody></table>

<br><br>
<table style="width:100%; margin-top:40px;">
    <tr>
        <td style="width:50%; text-align:left;">
            <p>Kepala Sekolah</p><br><br><br><br>
            '.$tanda_tangan_kepsek.'
        </td>
        <td style="width:50%; text-align:right;">
            <p>Malang, '.tgl_indo(date('Y-m-d')).'</p>
            <p>Wali Kelas</p><br><br><br><br>
            <u>'.htmlspecialchars($walas['nama_guru']).'</u><br>
            NIP. '.htmlspecialchars($walas['nip']).'
        </td>
    </tr>
</table>';

$filename = "Rekap_Absensi_Kelas_{$d['nama_kelas']}_Bulan_{$namaBulan}.pdf";

$dompdf->loadHtml($html);
$dompdf->setPaper(array(0, 0, 612, 1008), 'landscape'); // ✅ Legal/Folio size
$dompdf->render();

ob_end_clean(); // ✅ Bersihkan buffer
$dompdf->stream($filename, array('Attachment' => 0));
exit;
?>