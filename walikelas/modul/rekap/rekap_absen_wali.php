<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../../../config/db.php';
require_once __DIR__ . '/../../../vendor/autoload.php';

function safe_upper($val) {
    return isset($val) ? strtoupper($val) : '-';
}

$jenis = $_GET['jenis'] ?? 'hari';
$tanggal = $_GET['tanggal'] ?? date('Y-m-d');
$bulanIni = $_GET['bulan'] ?? date('Y-m');

$id_guru = null;

if (isset($_SESSION['guru'])) {
    $id_guru = $_SESSION['guru'];
} elseif (isset($_SESSION['walikelas'])) {
    $id_wali = $_SESSION['walikelas'];
    $q = mysqli_query($con, "SELECT id_guru FROM tb_walikelas WHERE id_walikelas = '$id_wali'");
    if ($d = mysqli_fetch_assoc($q)) {
        $id_guru = $d['id_guru'];
    }
}

if (!$id_guru) {
    echo "<div class='alert alert-danger'>Session tidak ditemukan. Silakan login ulang.</div>";
    exit;
}

// Ambil semua kelas yang diampu sebagai wali kelas
$qKelas = mysqli_query($con, "SELECT w.id_mkelas, k.nama_kelas 
    FROM tb_walikelas w 
    INNER JOIN tb_mkelas k ON w.id_mkelas = k.id_mkelas 
    WHERE w.id_guru = '$id_guru'");

$kelasDiampu = [];
while ($row = mysqli_fetch_assoc($qKelas)) {
    $kelasDiampu[$row['id_mkelas']] = $row['nama_kelas'];
}

if (empty($kelasDiampu)) {
    echo "<div class='alert alert-danger'>Anda belum terdaftar sebagai wali kelas.</div>";
    exit;
}

// Ambil ID kelas dari URL jika tersedia, kalau tidak gunakan yang pertama dari daftar
$id_mkelas = $_GET['kelas'] ?? array_key_first($kelasDiampu);

if (!isset($kelasDiampu[$id_mkelas])) {
    echo "<div class='alert alert-danger'>Anda bukan wali kelas dari kelas ini.</div>";
    exit;
}

$nama_kelas = $kelasDiampu[$id_mkelas];

// Ambil info mengajar untuk kelas tersebut
$qMengajar = mysqli_query($con, "SELECT mg.*, m.mapel, s.semester, t.tahun_ajaran 
    FROM tb_mengajar mg
    LEFT JOIN tb_master_mapel m ON mg.id_mapel = m.id_mapel
    LEFT JOIN tb_semester s ON mg.id_semester = s.id_semester
    LEFT JOIN tb_thajaran t ON mg.id_thajaran = t.id_thajaran
    WHERE mg.id_mkelas = '$id_mkelas' LIMIT 1");

if (mysqli_num_rows($qMengajar) === 0) {
    echo "<div class='alert alert-danger'>Data mengajar untuk kelas ini belum tersedia.</div>";
    exit;
}

$d = mysqli_fetch_assoc($qMengajar);
$id_mengajar = $d['id_mengajar'];
$semester_id = $d['id_semester'];

$paramLink = "jenis=$jenis";
?>

<div class="page-inner">
    <div class="page-header">
        <h4 class="page-title">Rekap Absen</h4>
        <ul class="breadcrumbs">
            <li class="nav-home"><a href="#"><i class="flaticon-home"></i></a></li>
            <li class="separator"><i class="flaticon-right-arrow"></i></li>
            <li class="nav-item"><a href="#">KELAS (<?= safe_upper($nama_kelas) ?>)</a></li>
        </ul>
    </div>

    <div class="row mb-4 text-center">
        <!-- <div class="col-md-4">
            <a href="?page=rekap&jenis=hari&kelas=<?= $id_mkelas ?>" class="btn btn-outline-warning btn-lg btn-block shadow-sm">
                <i class="fas fa-calendar-day"></i> Rekap Per Hari
            </a> -->
        </div>
        <div class="col-md-4">
            <a href="?page=rekap&jenis=bulan&kelas=<?= $id_mkelas ?>" class="btn btn-outline-primary btn-lg btn-block shadow-sm">
                <i class="fas fa-calendar-alt"></i> Rekap Per Bulan
            </a>
        </div>
        <div class="col-md-4">
            <a href="?page=rekap&jenis=semester&kelas=<?= $id_mkelas ?>" class="btn btn-outline-success btn-lg btn-block shadow-sm">
                <i class="fas fa-book"></i> Rekap Per Semester
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <?php 
            if ($jenis == 'bulan') {
                include 'rekap_bulanan.php';
            } elseif ($jenis == 'semester') {
                include 'rekap_semester.php';
            } 
            ?>

            <div class="text-center mt-4">
                 <a href="javascript:history.back()" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Kembali</a>

                <?php if ($jenis == 'hari') { ?>
                    
                    <!-- <a href="modul/rekap/export_pdf_harian.php?id_mengajar=<?= $id_mengajar ?>&tanggal=<?= $tanggal ?>" target="_blank" class="btn btn-danger ml-2">
                        <i class="fas fa-file-pdf"></i> Download PDF
                    </a> -->
                <?php } elseif ($jenis == 'bulan') { ?>
                    
                    <a href="modul/rekap/download_pdf.php?pelajaran=<?= $id_mengajar ?>&kelas=<?= $id_mkelas ?>&bulan=<?= $bulanIni ?>" target="_blank" class="btn btn-danger ml-2">
                        <i class="fas fa-file-pdf"></i> Download PDF
                    </a>
                <?php } elseif ($jenis == 'semester') { ?>
                    
                    <a href="modul/rekap/download_pdf_semester.php?id_mengajar=<?= $id_mengajar ?>&id_mkelas=<?= $id_mkelas ?>&semester_id=<?= $semester_id ?>" target="_blank" class="btn btn-danger ml-2">
                        <i class="fas fa-file-pdf"></i> Download PDF
                    </a>
                <?php } ?>
            </div>
        </div>
    </div>
</div>