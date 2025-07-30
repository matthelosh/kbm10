<?php
require_once __DIR__ . '/../../../vendor/autoload.php';
require_once __DIR__ . '/../../../config/db.php';

function safe_upper($val) {
    return isset($val) ? strtoupper($val) : '-';
}

$jenis = $_GET['jenis'] ?? 'hari';
$id_mengajar = $_GET['id_mengajar'] ?? ($_GET['pelajaran'] ?? '');
$id_mkelas = $_GET['kelas'] ?? '';

if (!$id_mengajar && !$id_mkelas) {
    echo "<div class='alert alert-danger'>Parameter id_mengajar atau kelas wajib diisi.</div>";
    exit;
}

$hariIni = $_GET['tanggal'] ?? date('Y-m-d');
$bulanIni = $_GET['bulan'] ?? date('Y-m');

if ($id_mengajar) {
    $sql = "SELECT mg.*, k.nama_kelas, m.mapel, s.semester, t.tahun_ajaran 
        FROM tb_mengajar mg
        LEFT JOIN tb_mkelas k ON mg.id_mkelas = k.id_mkelas
        LEFT JOIN tb_master_mapel m ON mg.id_mapel = m.id_mapel
        LEFT JOIN tb_semester s ON mg.id_semester = s.id_semester
        LEFT JOIN tb_thajaran t ON mg.id_thajaran = t.id_thajaran
        WHERE mg.id_mengajar = ?";
    $stmt = $con->prepare($sql);
    $stmt->bind_param('s', $id_mengajar);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 0) {
        echo "<div class='alert alert-danger'>Data mengajar tidak ditemukan.</div>";
        exit;
    }
    $d = $result->fetch_assoc();
    $id_mkelas = $d['id_mkelas'];
} else {
    $sql = "SELECT mg.*, k.nama_kelas, m.mapel, s.semester, t.tahun_ajaran 
        FROM tb_mengajar mg
        LEFT JOIN tb_mkelas k ON mg.id_mkelas = k.id_mkelas
        LEFT JOIN tb_master_mapel m ON mg.id_mapel = m.id_mapel
        LEFT JOIN tb_semester s ON mg.id_semester = s.id_semester
        LEFT JOIN tb_thajaran t ON mg.id_thajaran = t.id_thajaran
        WHERE mg.id_mkelas = ?
        LIMIT 1";
    $stmt = $con->prepare($sql);
    $stmt->bind_param('s', $id_mkelas);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 0) {
        echo "<div class='alert alert-danger'>Data mengajar tidak ditemukan.</div>";
        exit;
    }
    $d = $result->fetch_assoc();
    $id_mengajar = $d['id_mengajar'];
}

$paramLink = $id_mengajar ? "id_mengajar=$id_mengajar" : "kelas=$id_mkelas";
?>

<div class="page-inner">
    <div class="page-header">
        <h4 class="page-title">Rekap Absen</h4>
        <ul class="breadcrumbs">
            <li class="nav-home"><a href="#"><i class="flaticon-home"></i></a></li>
            <li class="separator"><i class="flaticon-right-arrow"></i></li>
            <li class="nav-item"><a href="#">KELAS (<?= safe_upper($d['nama_kelas']) ?>)</a></li>
            <li class="separator"><i class="flaticon-right-arrow"></i></li>
            <li class="nav-item"><a href="#"><?= safe_upper($d['mapel']) ?></a></li>
        </ul>
    </div>

    <div class="row mb-4 text-center">
        <div class="col-md-4">
            <a href="?page=rekap&<?= $paramLink ?>&jenis=hari" class="btn btn-outline-warning btn-lg btn-block shadow-sm">
                <i class="fas fa-calendar-day"></i> Rekap Per Hari
            </a>
        </div>
        <div class="col-md-4">
            <a href="?page=rekap&<?= $paramLink ?>&jenis=bulan" class="btn btn-outline-primary btn-lg btn-block shadow-sm">
                <i class="fas fa-calendar-alt"></i> Rekap Per Bulan
            </a>
        </div>
        <div class="col-md-4">
            <a href="?page=rekap&<?= $paramLink ?>&jenis=semester" class="btn btn-outline-success btn-lg btn-block shadow-sm">
                <i class="fas fa-book"></i> Rekap Per Semester
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <?php 
            if ($jenis == 'hari') {
                include 'rekap_harian.php';
            } elseif ($jenis == 'bulan') {
                include 'rekap_bulanan.php';
            } elseif ($jenis == 'semester') {
                include 'rekap_semester.php';
            }
            ?>

            <div class="text-center mt-4">
                <a href="javascript:history.back()" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Kembali</a>
                <?php if ($jenis == 'hari') { ?>
                    
                    <a href="modul/rekap/export_pdf_harian.php?<?= $paramLink ?>&tanggal=<?= $hariIni ?>" target="_blank" class="btn btn-danger ml-2">
                        <i class="fas fa-file-pdf"></i> Download PDF
                    </a>

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