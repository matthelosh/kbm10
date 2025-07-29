<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

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
$pelajaran = intval($_GET['pelajaran'] ?? 0);
$kelas = intval($_GET['kelas'] ?? 0);
$bulanParam = $_GET['bulan'] ?? date('Y-m');
if (!preg_match('/^\d{4}-\d{2}$/', $bulanParam)) die("Format bulan salah.");

list($tahun, $bulan) = explode('-', $bulanParam);
$tglBulan = "$tahun-$bulan-01";
$tglTerakhir = date('t', strtotime($tglBulan));

// Ambil data mengajar
$sql = "SELECT m.*, g.nama_guru, g.nip as nip_guru, k.nama_kelas, s.semester, t.tahun_ajaran, mp.mapel
        FROM tb_mengajar m
        JOIN tb_guru g ON m.id_guru = g.id_guru
        JOIN tb_mkelas k ON m.id_mkelas = k.id_mkelas
        JOIN tb_semester s ON m.id_semester = s.id_semester
        JOIN tb_thajaran t ON m.id_thajaran = t.id_thajaran
        JOIN tb_master_mapel mp ON m.id_mapel = mp.id_mapel
        WHERE m.id_mengajar = $pelajaran AND m.id_mkelas = $kelas AND s.status=1 AND t.status=1";
$d = mysqli_fetch_assoc(mysqli_query($con, $sql));
if (!$d) die("Data mengajar tidak ditemukan.");

// Ambil wali kelas dan kepsek
$walas = mysqli_fetch_assoc(mysqli_query($con, "SELECT g.nama_guru FROM tb_walikelas w JOIN tb_guru g ON w.id_guru = g.id_guru WHERE w.id_mkelas = $kelas"));
$kepsek = mysqli_fetch_assoc(mysqli_query($con, "SELECT nama_kepsek, nip FROM tb_kepsek WHERE status = 'Y' LIMIT 1"));
$nama_kepsek = $kepsek['nama_kepsek'] ?? 'Kepala Sekolah';
$nip_kepsek = $kepsek['nip'] ?? '-';

// Logo base64
$logoPath = '../..//assets/img/jatim.png';
$logoBase64 = '';
if (file_exists($logoPath)) {
    $type = pathinfo($logoPath, PATHINFO_EXTENSION);
    $data = file_get_contents($logoPath);
    $logoBase64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
}

// Start buffering HTML
ob_start();
?>
<style>
    body { font-family: DejaVu Sans, sans-serif; font-size: 10pt; }
    .header { text-align: center; }
    .header h3 { margin: 0; font-size: 14pt; }
    .header p { margin: 0; font-size: 10pt; }
    .info td { padding: 3px 5px; vertical-align: top; }
    .absensi { border-collapse: collapse; width: 100%; margin-top: 10px; }
    .absensi th, .absensi td { border: 1px solid #000; text-align: center; padding: 3px; }
    .absensi th { font-weight: bold; }
    .nowrap { white-space: nowrap; }
    .th-h { background-color: #C6EFCE; }
    .th-s { background-color: #FFE699; }
    .th-i { background-color: #BDD7EE; }
    .th-a { background-color: #F4B084; }
    .th-hari { background-color: #D9E1F2; }
</style>

<div class="header">
    <table width="100%">
        <tr>
            <td width="80">
                <?php if($logoBase64): ?>
                    <img src="<?= $logoBase64 ?>" height="60" alt="Logo Jatim">
                <?php else: ?>
                    <strong>LOGO</strong>
                <?php endif; ?>
            </td>
            <td style="text-align:center;">
                <h3>PEMERINTAH PROVINSI JAWA TIMUR<br>SMK NEGERI 10 MALANG</h3>
                <p>Jalan Raya Tlogowaru, Kedungkandang, Malang, Jawa Timur 65133<br>
                Telp. (0341) 754086 E-mail: <a href="mailto:smkn10_malang@yahoo.co.id" style="color: blue;">smkn10_malang@yahoo.co.id</a></p>
            </td>
        </tr>
    </table>
    <hr>
</div>

<table class="info">
    <tr><td><b>Kelas</b></td><td>: <?= htmlspecialchars($d['nama_kelas']) ?></td></tr>
    <tr><td><b>Semester</b></td><td>: <?= htmlspecialchars($d['semester']) ?></td></tr>
    <tr><td><b>Tahun Ajaran</b></td><td>: <?= htmlspecialchars($d['tahun_ajaran']) ?></td></tr>
    <tr><td><b>Guru Mapel</b></td><td>: <?= htmlspecialchars($d['nama_guru']) ?></td></tr>
    <tr><td><b>Mapel</b></td><td>: <?= htmlspecialchars($d['mapel']) ?></td></tr>
    <tr><td><b>Wali Kelas</b></td><td>: <?= htmlspecialchars($walas['nama_guru'] ?? '-') ?></td></tr>
</table>

<br><br>
<h4 style="text-align:center;">REKAP ABSENSI BULAN <?= strtoupper(date('F', strtotime($tglBulan))) ?> <?= $tahun ?></h4>

<table class="absensi">
    <thead>
        <tr>
            <th class="nowrap">NO</th>
            <th class="nowrap">NIS</th>
            <th>NAMA</th>
            <th class="nowrap">L/P</th>
            <?php for ($i = 1; $i <= $tglTerakhir; $i++): ?>
                <th class="th-hari"><?= $i ?></th>
            <?php endfor; ?>
            <th class="th-h">H</th>
            <th class="th-s">S</th>
            <th class="th-i">I</th>
            <th class="th-a">A</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $no = 1;
        $qrySiswa = mysqli_query($con, "SELECT * FROM tb_siswa WHERE id_mkelas = $kelas ORDER BY nama_siswa ASC");
        while ($s = mysqli_fetch_assoc($qrySiswa)):
        ?>
        <tr>
            <td><?= $no++ ?></td>
            <td><?= htmlspecialchars($s['nis']) ?></td>
            <td style="text-align:left;"><?= htmlspecialchars($s['nama_siswa']) ?></td>
            <td><?= htmlspecialchars($s['jk']) ?></td>
            <?php
            for ($i = 1; $i <= $tglTerakhir; $i++) {
                $tgl = "$tahun-$bulan-" . str_pad($i, 2, '0', STR_PAD_LEFT);
                $absenQ = mysqli_query($con, "SELECT ket FROM _logabsensi WHERE id_siswa='{$s['id_siswa']}' AND id_mengajar='$pelajaran' AND tgl_absen='$tgl' LIMIT 1");
                $absen = mysqli_fetch_assoc($absenQ);
                echo '<td>' . ($absen['ket'] ?? '') . '</td>';
            }
            foreach (['H','S','I','A'] as $kode) {
                $jmlQ = mysqli_query($con, "SELECT COUNT(*) AS jml FROM _logabsensi WHERE id_siswa='{$s['id_siswa']}' AND id_mengajar='$pelajaran' AND YEAR(tgl_absen)='$tahun' AND MONTH(tgl_absen)='$bulan' AND ket='$kode'");
                $jml = mysqli_fetch_assoc($jmlQ);
                echo '<td>' . ($jml['jml'] ?? 0) . '</td>';
            }
            ?>
        </tr>
        <?php endwhile; ?>
    </tbody>
</table>

<table style="width:100%; margin-top:60px;">
<tr>
    <td style="width:50%; text-align:left;">
        <p>Kepala Sekolah</p><br><br><br><br>
        <p><u><?= htmlspecialchars($nama_kepsek) ?></u><br>NIP. <?= htmlspecialchars($nip_kepsek) ?></p>
    </td>
    <td style="width:50%; text-align:right;">
        <p>Malang, <?= tgl_indo(date('Y-m-d')) ?></p>
        <p>Guru Pengampu</p><br><br><br><br>
        <p><u><?= htmlspecialchars($d['nama_guru']) ?></u><br>NIP. <?= htmlspecialchars($d['nip_guru']) ?></p>
    </td>
</tr>
</table>
<?php
$html = ob_get_clean();

// Konfigurasi DomPDF
$options = new Options();
$options->set('isRemoteEnabled', true);
$options->set('defaultFont', 'DejaVu Sans');

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'landscape');
$dompdf->render();

if (ob_get_length()) ob_end_clean();

// Output PDF
$dompdf->stream("Rekap_Absensi_{$d['nama_kelas']}_{$bulanParam}.pdf", ["Attachment" => false]);
exit;