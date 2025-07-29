<?php
ob_start(); // Mulai buffering output supaya gak ada output aneh sebelum PDF

require_once __DIR__ . '/../../../vendor/autoload.php';
include __DIR__ . '/../../../config/db.php';

use Dompdf\Dompdf;

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

// Cek parameter GET
if (!isset($_GET['id_mengajar']) || !isset($_GET['tanggal'])) {
    die("Parameter tidak lengkap.");
}

$id_mengajar = mysqli_real_escape_string($con, $_GET['id_mengajar']);
$tanggal = mysqli_real_escape_string($con, $_GET['tanggal']);

// Ambil data mengajar
$q = mysqli_query($con, "SELECT m.id_mkelas, m.id_guru, k.nama_kelas, g.nama_guru, mp.mapel 
    FROM tb_mengajar m
    LEFT JOIN tb_mkelas k ON m.id_mkelas = k.id_mkelas
    LEFT JOIN tb_guru g ON m.id_guru = g.id_guru
    LEFT JOIN tb_master_mapel mp ON m.id_mapel = mp.id_mapel
    WHERE m.id_mengajar = '$id_mengajar'");
$d = mysqli_fetch_assoc($q);
if (!$d) die("Data mengajar tidak ditemukan.");

// Ambil nip guru
$guru = mysqli_query($con, "SELECT nip FROM tb_guru WHERE id_guru = '{$d['id_guru']}' LIMIT 1");
$g = mysqli_fetch_assoc($guru);
$nip_guru = $g['nip'] ?? '-';

// Ambil data siswa
$siswa = mysqli_query($con, "SELECT * FROM tb_siswa WHERE id_mkelas = '{$d['id_mkelas']}'");

// Kepala sekolah
$kepsek = mysqli_query($con, "SELECT * FROM tb_kepsek WHERE status='Y' LIMIT 1");
$k = mysqli_fetch_assoc($kepsek);
$nama_kepsek = $k['nama_kepsek'] ?? 'Kepala Sekolah';
$nip_kepsek = $k['nip'] ?? '-';

// Load gambar logo sebagai base64 supaya bisa tampil di PDF Dompdf
$logo_path = __DIR__ . '/../..//assets/img/jatim.png';
$logo_base64 = '';
if (file_exists($logo_path)) {
    $logo_base64 = base64_encode(file_get_contents($logo_path));
} else {
    $logo_base64 = ''; // Kalau gak ada gambarnya
}

$html = '
<style>
    body { font-family: "Times New Roman", serif; font-size: 12px; }
    .kop table {
        width: 100%;
        border-bottom: 3px solid black;
        margin-bottom: 10px;
        border-collapse: collapse;
    }
    .kop td.logo {
        width: 50px;
        padding-right: 10px;
        vertical-align: middle;
    }
    .kop img {
        width: 50px;
        height: auto;
    }
    .kop td.center {
        text-align: center;
        vertical-align: middle;
    }
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
            <td class="logo">';
if ($logo_base64 !== '') {
    $html .= '<img src="data:image/png;base64,' . $logo_base64 . '" alt="Logo Jatim">';
}
$html .= '</td>
            <td class="center">
                <p>PEMERINTAH PROVINSI JAWA TIMUR</p>
                <p>DINAS PENDIDIKAN</p>
                <h2>SMK NEGERI 10 MALANG</h2>
                <p>Jalan Raya Tlogowaru, Kedungkandang, Malang, Jawa Timur 65133</p>
                <p>Telp. (0341) 754086 E-mail: <a href="mailto:smkn10_malang@yahoo.co.id" style="color: blue;">smkn10_malang@yahoo.co.id</a></p>
            </td>
        </tr>
    </table>
</div>

<h4 style="text-align:center; margin-top: 30px;">REKAP ABSENSI HARIAN - ' . tgl_indo($tanggal) . '</h4>
<table style="margin-top: 10px; font-size: 12px;">
    <tr>
        <td style="font-weight: bold; width: 120px;">Guru Pengampu</td>
        <td>: ' . $d['nama_guru'] . '</td>
    </tr>
    <tr>
        <td style="font-weight: bold;">Kelas</td>
        <td>: ' . $d['nama_kelas'] . '</td>
    </tr>
    <tr>
        <td style="font-weight: bold;">Mata Pelajaran</td>
        <td>: ' . $d['mapel'] . '</td>
    </tr>
</table>

<table class="data">
<thead>
<tr>
<th>No</th>
<th>NIS</th>
<th>Nama Siswa</th>
<th class="nowrap" width="15%">Hadir</th>
<th class="nowrap" width="15%">Izin</th>
<th class="nowrap" width="15%">Sakit</th>
<th class="nowrap" width="15%">Terlambat</th>
</tr>
</thead>
<tbody>';

$no = 1;
$jumlah = ['H' => 0, 'I' => 0, 'S' => 0, 'T' => 0];
while ($s = mysqli_fetch_array($siswa)) {
    $absen = mysqli_fetch_assoc(mysqli_query($con, "SELECT ket FROM _logabsensi WHERE id_siswa='{$s['id_siswa']}' AND id_mengajar='$id_mengajar' AND tgl_absen='$tanggal'"));
    $rekap = ['H' => '', 'I' => '', 'S' => '', 'T' => ''];
    if ($absen) {
        $rekap[$absen['ket']] = 'V';
        $jumlah[$absen['ket']]++;
    }
    $html .= "<tr>
        <td>$no</td>
        <td>{$s['nis']}</td>
        <td style='text-align:left;'>{$s['nama_siswa']}</td>
        <td>{$rekap['H']}</td>
        <td>{$rekap['I']}</td>
        <td>{$rekap['S']}</td>
        <td>{$rekap['T']}</td>
    </tr>";
    $no++;
}

$html .= "<tr style='font-weight:bold; background:#f0f0f0;'>
    <td colspan='3' style='text-align:right;'><b>Total</b></td>
    <td>{$jumlah['H']}</td>
    <td>{$jumlah['I']}</td>
    <td>{$jumlah['S']}</td>
    <td>{$jumlah['T']}</td>
</tr>";

$html .= '</tbody></table>';

$html .= '
<table style="width:100%; margin-top:60px;">
<tr>
<td style="width:50%; text-align:left; vertical-align:top;">

<p>Kepala Sekolah</p>
<br><br><br><br>
<p><u>' . $nama_kepsek . '</u><br>NIP. ' . $nip_kepsek . '</p>
</td>

<td style="width:50%; text-align:right; vertical-align:top;">
<p>Malang, ' . tgl_indo(date('Y-m-d')) . '</p>
<p>Guru Pengampu</p>
<br><br><br><br>
<p><u>' . $d['nama_guru'] . '</u><br>NIP. ' . $nip_guru . '</p>
</td>

</tr>
</table>';

// Inisialisasi Dompdf dan render PDF
$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

ob_end_clean(); // Bersihkan buffer supaya PDF bersih

// Stream output PDF ke browser tanpa download (inline)
$dompdf->stream("Rekap_Absen_Harian_{$d['nama_kelas']}_{$tanggal}.pdf", ["Attachment" => 0]);

exit;
