<?php
// Ambil variabel GET dengan aman
$idMengajar = $_GET['pelajaran'] ?? null;
$idKelas = $_GET['kelas'] ?? null;

if (!$idMengajar || !$idKelas) {
    die("Parameter pelajaran atau kelas belum lengkap.");
}

// Query untuk mengambil data mengajar, semester, tahun ajaran, dll
$query = mysqli_query($con, "
    SELECT m.*, k.nama_kelas, mp.mapel, g.nama_guru, s.semester, s.id_semester, t.tahun_ajaran
    FROM tb_mengajar m
    JOIN tb_mkelas k ON m.id_mkelas = k.id_mkelas
    JOIN tb_master_mapel mp ON m.id_mapel = mp.id_mapel
    JOIN tb_guru g ON m.id_guru = g.id_guru
    JOIN tb_semester s ON m.id_semester = s.id_semester
    JOIN tb_thajaran t ON m.id_thajaran = t.id_thajaran
    WHERE m.id_mengajar = '$idMengajar' AND m.id_mkelas = '$idKelas' 
    AND t.status = 1 AND s.status = 1
    LIMIT 1
");

$d = mysqli_fetch_assoc($query);

if (!$d) {
    die("Data mengajar tidak ditemukan.");
}

// Ambil wali kelas
$walikelasQuery = mysqli_query($con, "SELECT g.nama_guru FROM tb_walikelas w JOIN tb_guru g ON w.id_guru=g.id_guru WHERE w.id_mkelas='$idKelas' LIMIT 1");
$walas = mysqli_fetch_assoc($walikelasQuery);
?>

<div class="page-inner">
    <div class="page-header">
        <h4 class="page-title"><?= strtoupper($d['mapel']) ?> </h4>
        <ul class="breadcrumbs">
            <li class="nav-home">
                <a href="#">
                    <i class="flaticon-home"></i>
                </a>
            </li>
            <li class="separator">
                <i class="flaticon-right-arrow"></i>
            </li>
            <li class="nav-item">
                <a href="#">KELAS (<?= strtoupper($d['nama_kelas']) ?> )</a>
            </li>
        </ul>
    </div>

    <div class="row">
        <div class="col-md-12 col-xs-12">
            <div class="card">
                <div class="card-header">
                    <table width="100%">
                        <tr>
                            <td><img src="/assets/img/vsc.png" width="130"></td>
                            <td>
                                <h1>
                                    ABSENSI SISWA <br>
                                    <small> SMK NEGERI 10 MALANG</small>
                                </h1>
                            </td>
                            <td>
                                <table width="100%">
                                    <tr>
                                        <td colspan="2"><b style="border: 2px solid;padding: 7px;">
                                                KELAS ( <?= strtoupper($d['nama_kelas']) ?> )
                                            </b></td>
                                        <td>
                                            <b style="border: 2px solid;padding: 7px;">
                                                <?= $d['semester'] ?> |
                                                <?= $d['tahun_ajaran'] ?>
                                            </b>
                                        </td>
                                        <td rowspan="5">
                                            <p class="text-info"> H = Hadir</p>
                                            <p class="text-success"> I = Izin</p>
                                            <p class="text-warning"> S = Sakit</p>
                                            <p class="text-danger"> A = Alpha</p>
                                        </td>
                                    </tr>
                                    <tr><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>
                                    <tr>
                                        <td>Nama Guru </td>
                                        <td>:</td>
                                        <td><?= $d['nama_guru'] ?></td>
                                    </tr>
                                    <tr>
                                        <td>Bidang Studi </td>
                                        <td>:</td>
                                        <td><?= $d['mapel'] ?></td>
                                    </tr>
                                    <tr>
                                        <td>Wali Kelas </td>
                                        <td>:</td>
                                        <td><?= $walas['nama_guru'] ?? '-' ?></td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>
                </div>

                <div class="card-body">
                    <!-- Tombol rekap semester -->
                    <a target="_blank" href="modul/rekap/rekap_semester.php?id_mengajar=<?= urlencode($idMengajar) ?>&id_mkelas=<?= urlencode($idKelas) ?>&semester_id=<?= urlencode($d['id_semester']) ?>" class="btn btn-default">
                        <span class="btn-label">
                            <i class="fas fa-print"></i>
                        </span>
                        REKAP (<?= strtoupper($d['semester']); ?> - <?= strtoupper($d['tahun_ajaran']); ?>)
                    </a>

                    <?php
                    // Query untuk rekap absensi per bulan
                    $qry = mysqli_query($con, "
                        SELECT * FROM _logabsensi
                        WHERE id_mengajar = '$idMengajar'
                        GROUP BY MONTH(tgl_absen) 
                        ORDER BY MONTH(tgl_absen) DESC
                    ");

                    foreach ($qry as $bulan) :
                        $tglBulan = $bulan['tgl_absen'];
                        $bulanAngka = date('m', strtotime($tglBulan));
                        $tglTerakhir = date('t', strtotime($tglBulan));
                    ?>
                        <div class="alert alert-warning alert-dismissible mt-2" role="alert">
                            <b class="text-warning" style="text-transform: uppercase;">BULAN <?= namaBulan($bulanAngka); ?> <?= date('Y') ?></b>
                            <hr>
                            <p>
                                <a target="_blank" href="modul/rekap/export_excel.php?pelajaran=<?= $idMengajar ?>&kelas=<?= $idKelas ?>&bulan=<?= $bulanAngka ?>" class="btn btn-success">
                                    <span class="btn-label"><i class="far fa-file-excel"></i></span>
                                    Export Excel
                                </a>

                                <a target="_blank" href="modul/rekap/download_pdf.php?pelajaran=<?= $idMengajar ?>&bulan=<?= $bulanAngka ?>&kelas=<?= $idKelas ?>" class="btn btn-default">
                                    <span class="btn-label"><i class="fas fa-print"></i></span>
                                    CETAK BULAN (<?= strtoupper(namaBulan($bulanAngka)); ?>)
                                </a>
                            </p>

                            <table width="100%" border="1" cellpadding="2" style="border-collapse: collapse;">
                                <tr>
                                    <td rowspan="2" bgcolor="#EFEBE9" align="center">NO</td>
                                    <td rowspan="2" bgcolor="#EFEBE9" align="center">NIS</td>
                                    <td rowspan="2" bgcolor="#EFEBE9">NAMA SISWA</td>
                                    <td rowspan="2" bgcolor="#EFEBE9" align="center">L/P</td>
                                    <td colspan="<?= $tglTerakhir; ?>" style="padding: 8px;">PERTEMUAN KE- DAN BULAN : <b><?= namaBulan($bulanAngka); ?> <?= date('Y', strtotime($tglBulan)); ?></b></td>
                                    <td colspan="3" align="center" bgcolor="#EFEBE9">JUMLAH</td>
                                </tr>
                                <tr>
                                    <?php for ($i = 1; $i <= $tglTerakhir; $i++) : ?>
                                        <td bgcolor='#EFEBE9' align='center'><?= $i ?></td>
                                    <?php endfor; ?>
                                    <td bgcolor="#FFC107" align="center">S</td>
                                    <td bgcolor="#4CAF50" align="center">I</td>
                                    <td bgcolor="#D50000" align="center">A</td>
                                </tr>
                                <?php
                                $no = 1;
                                $qryAbsen = mysqli_query($con, "
                                    SELECT * FROM tb_siswa 
                                    WHERE id_mkelas = '$idKelas'
                                    ORDER BY nama_siswa ASC
                                ");

                                foreach ($qryAbsen as $s) :
                                    $warna = ($no % 2 == 1) ? "#ffffff" : "#f0f0f0";
                                ?>
                                    <tr bgcolor="<?= $warna; ?>">
                                        <td align="center"><?= $no++; ?></td>
                                        <td><?= $s['nis']; ?></td>
                                        <td><?= $s['nama_siswa']; ?></td>
                                        <td align="center"><?= $s['jk']; ?></td>

                                        <?php
                                        for ($i = 1; $i <= $tglTerakhir; $i++) :
                                            $ketQry = mysqli_query($con, "
                                                SELECT ket FROM _logabsensi 
                                                WHERE DAY(tgl_absen) = '$i' 
                                                AND id_siswa = '{$s['id_siswa']}'
                                                AND id_mengajar = '$idMengajar'
                                                AND MONTH(tgl_absen) = '$bulanAngka'
                                                LIMIT 1
                                            ");
                                            $dataKet = mysqli_fetch_array($ketQry);
                                            $nilaiKet = $dataKet['ket'] ?? '';
                                        ?>
                                            <td align="center">
                                                <?php
                                                switch ($nilaiKet) {
                                                    case 'H': echo "<b style='color:#2196F3;'>H</b>"; break;
                                                    case 'I': echo "<b style='color:#4CAF50;'>I</b>"; break;
                                                    case 'S': echo "<b style='color:#FFC107;'>S</b>"; break;
                                                    case 'A': echo "<b style='color:#D50000;'>A</b>"; break;
                                                    default: echo "-";
                                                }
                                                ?>
                                            </td>
                                        <?php endfor; ?>

                                        <td align="center" style="font-weight: bold;">
                                            <?= mysqli_fetch_array(mysqli_query($con, "
                                                SELECT COUNT(*) as jml FROM _logabsensi 
                                                WHERE id_siswa = '{$s['id_siswa']}'
                                                AND id_mengajar = '$idMengajar'
                                                AND MONTH(tgl_absen) = '$bulanAngka'
                                                AND ket = 'S'
                                            "))['jml'] ?? '-' ?>
                                        </td>
                                        <td align="center" style="font-weight: bold;">
                                            <?= mysqli_fetch_array(mysqli_query($con, "
                                                SELECT COUNT(*) as jml FROM _logabsensi 
                                                WHERE id_siswa = '{$s['id_siswa']}'
                                                AND id_mengajar = '$idMengajar'
                                                AND MONTH(tgl_absen) = '$bulanAngka'
                                                AND ket = 'I'
                                            "))['jml'] ?? '-' ?>
                                        </td>
                                        <td align="center" style="font-weight: bold;">
                                            <?= mysqli_fetch_array(mysqli_query($con, "
                                                SELECT COUNT(*) as jml FROM _logabsensi 
                                                WHERE id_siswa = '{$s['id_siswa']}'
                                                AND id_mengajar = '$idMengajar'
                                                AND MONTH(tgl_absen) = '$bulanAngka'
                                                AND ket = 'A'
                                            "))['jml'] ?? '-' ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </table>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>