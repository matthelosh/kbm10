<?php
// echo "Halo <br />";
// echo tanggalIndo('2025-07-23');

ob_start();

// require_once __DIR__ . '../../../../../vendor/autoload.php';
// include '../../../../config/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/config/db.php';

if(!isset($con)) {
    die("Variabel $con tidak ada");
}
// // //require_once $_SERVER['DOCUMENT_ROOT'] . '/kbm/config/db.php';
// // //require_once realpath(__DIR__ . '/../../../../vendor/autoload.php');

use Dompdf\Dompdf;
use Dompdf\Options;

// // Fungsi ubah nama bulan ke format Indonesia (bulan saja)
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



// Fungsi ubah full tanggal ke format Indonesia
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

// printf($con);
// Ambil data filter dari GET
// echo $_GET['tgl_awal'];
$tgl_awal = $con->real_escape_string(isset($_GET['tgl_awal']) ? $_GET['tgl_awal'] : date('Y-m-d'));
$tgl_akhir = $con->real_escape_string($_GET['tgl_akhir'] ?? '');
$id_kelas = $con->real_escape_string($_GET['kelas'] ?? '');
$id_guru = $con->real_escape_string($_GET['guru'] ?? '');

// WHERE dinamis
$where = "WHERE 1";
if (!empty($tgl_awal) && !empty($tgl_akhir)) {
    $where .= " AND tanggal BETWEEN '$tgl_awal' AND '$tgl_akhir'";
}
if (!empty($id_kelas)) {
    $where .= " AND id_kelas = '$id_kelas'";
}
if (!empty($id_guru)) {
    $where .= " AND jm.id_guru = '$id_guru'";
}

// Query utama
$query = "SELECT jm.*, mk.nama_kelas, g.nama_guru, g.nip, jm.mapel
          FROM jurnal_mengajar jm 
          JOIN tb_mkelas mk ON jm.id_kelas = mk.id_mkelas 
          LEFT JOIN tb_guru g ON jm.id_guru = g.id_guru
          $where 
          ORDER BY tanggal DESC";

$result = $con->query($query);
if (!$result) {
    die('Query error: ' . $con->error);
}

// Ambil data
$rows = [];
$nama_gurus = [];
$mapels = [];
$nip_guru = '';
while ($row = $result->fetch_assoc()) {
    $rows[] = $row;
    if (!empty($row['nama_guru'])) $nama_gurus[] = $row['nama_guru'];
    if (!empty($row['mapel'])) $mapels[] = $row['mapel'];
    if (!empty($row['nip'])) $nip_guru = $row['nip'];
}

// Periode otomatis
$periode_awal = (!empty($tgl_awal)) ? $tgl_awal : (isset($rows[count($rows)-1]['tanggal']) ? $rows[count($rows)-1]['tanggal'] : '');
$periode_akhir = (!empty($tgl_akhir)) ? $tgl_akhir : (isset($rows[0]['tanggal']) ? $rows[0]['tanggal'] : '');

// Judul bulan
$judul_bulan = (!empty($periode_awal)) ? bulanIndo($periode_awal) : '-';

// Unikkan nama guru dan mapel
$nama_gurus = array_unique($nama_gurus);
$mapels = array_unique($mapels);

$nama_guru = count($nama_gurus) === 1 ? $nama_gurus[0] : (count($nama_gurus) > 1 ? 'Beberapa Guru' : 'Tidak diketahui');
$mapel = count($mapels) === 1 ? $mapels[0] : (count($mapels) > 1 ? 'Beberapa Mapel' : 'Tidak diketahui');

// Ambil Kepala Sekolah aktif
$qKepsek = $con->query("SELECT nama_kepsek, nip FROM tb_kepsek WHERE status = 'Y'");
$kepsek_list = [];
while ($rowKepsek = $qKepsek->fetch_assoc()) {
    $kepsek_list[] = $rowKepsek;
}

// Isi tanda tangan Kepsek
$tanda_tangan_kepsek = '';
if (count($kepsek_list) > 0) {
    foreach ($kepsek_list as $ks) {
        $tanda_tangan_kepsek .= '<u>'.htmlspecialchars($ks['nama_kepsek']).'</u><br>NIP. '.htmlspecialchars($ks['nip']).'<br><br>';
    }
} else {
    $tanda_tangan_kepsek = '<i>Kepala Sekolah Belum Ditentukan</i>';
}

// Logo
$logo_path = $_SERVER['DOCUMENT_ROOT'].'/assets/img/jatim.png';
$logo_data = file_exists($logo_path) ? base64_encode(file_get_contents($logo_path)) : '';

// HTML PDF
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
$html .= $logo_data ? '<img src="data:image/png;base64,'.$logo_data.'" alt="Logo Jatim">' : '<p>Logo tidak ditemukan</p>';
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
    <strong>Periode:</strong> '.(!empty($periode_awal) ? tanggalIndo($periode_awal) : '-').' s.d. '.(!empty($periode_akhir) ? tanggalIndo($periode_akhir) : '-').'
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

if (!empty($rows)) {
    $no = 1;
    foreach ($rows as $row) {
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
} else {
    $html .= '<tr><td colspan="7" style="text-align:center;">Tidak ada data</td></tr>';
}

$html .= '</tbody></table>

<br><br>
<table style="width:100%; margin-top:60px;">
    <tr>
        <td style="width:50%; text-align:left; vertical-align:top;">
            <p>Kepala Sekolah</p><br><br><br>
            '.$tanda_tangan_kepsek.'
        </td>
        <td style="width:50%; text-align:right; vertical-align:top;">
            <p>Malang, '.tanggalIndo(date('Y-m-d')).'</p>
            <p>Guru Pengampu</p><br><br><br>
            <p><u>'.htmlspecialchars($nama_guru).'</u><br>
            NIP. '.htmlspecialchars($nip_guru).'</p>
        </td>
    </tr>
</table>';

// Generate PDF
$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'landscape');
$dompdf->render();

// Bersihkan buffer
ob_end_clean();

// Output PDF
$dompdf->stream('rekap_jurnal.pdf', ['Attachment' => false]);
exit;
?>