<?php
if (!isset($_SESSION)) session_start();
require_once __DIR__ . '/../../../config/db.php';

if (!isset($_SESSION['guru'])) {
    echo "<script>alert('Maaf! Anda belum login!');window.location='../../user.php';</script>";
    exit;
}

$id_login = $_SESSION['guru'];

$stmt = $con->prepare("
    SELECT mg.id_mengajar, mg.id_mapel, m.mapel, mg.hari, mg.jamke, mg.jam_mengajar, mg.ruang, k.nama_kelas, mg.id_mkelas
    FROM tb_mengajar mg
    INNER JOIN tb_master_mapel m ON mg.id_mapel = m.id_mapel
    INNER JOIN tb_mkelas k ON mg.id_mkelas = k.id_mkelas
    INNER JOIN tb_thajaran t ON mg.id_thajaran = t.id_thajaran
    WHERE mg.id_guru = ? AND t.status = 1
    ORDER BY FIELD(mg.hari, 'Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'), mg.jamke
");
$stmt->bind_param("s", $id_login);
$stmt->execute();
$jadwal = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<div class="page-inner">

    <div class="page-header">
        <h4 class="page-title">Jadwal</h4> 
        <ul class="breadcrumbs">
            <li class="nav-home"><a href="#"><i class="flaticon-home"></i></a></li>
            <li class="separator"><i class="flaticon-right-arrow"></i></li>
            <li class="nav-item"><a href="#">Jadwal Mengajar</a></li>
            <li class="separator"><i class="flaticon-right-arrow"></i></li>
        </ul>
    </div>

    <div class="mb-4">
        <a href="modul/jadwal/contoh_jadwal.xlsx" class="btn btn-default" download>
            <i class="fas fa-download"></i> Download Contoh File Excel
        </a>
        <button class="btn btn-primary" onclick="document.getElementById('excelInput').click()">
            <i class="fas fa-file-upload"></i> Insert Excel
        </button>
        <a href="index.php?page=tambah_jadwal" class="btn btn-success">
            <i class="fas fa-plus-circle"></i> Tambah Manual
        </a>
        <form id="excelForm" action="modul/jadwal/import_excel.php" method="post" enctype="multipart/form-data" style="display:none;">
            <input type="file" name="excel_file" id="excelInput" accept=".xlsx,.xls" onchange="document.getElementById('excelForm').submit()">
        </form>
    </div>

    <div class="row">
        <?php if (!empty($jadwal)): ?>
            <?php foreach ($jadwal as $jd): ?>
                <div class="col-md-6 col-xs-12">
                    <div class="alert alert-info alert-dismissible" role="alert">
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close"></button>
                        <strong><h3><?= htmlspecialchars($jd['mapel']); ?></h3></strong>
                        <hr>
                        <ul>
                            <li>Hari : <?= htmlspecialchars($jd['hari']); ?></li>
                            <li>Jam Ke : <?= htmlspecialchars($jd['jamke']); ?></li>
                            <li>Waktu : <?= htmlspecialchars($jd['jam_mengajar']); ?></li>
                            <li>Kelas : <?= htmlspecialchars($jd['nama_kelas']); ?></li>
                            <li>Ruang : <?= htmlspecialchars($jd['ruang']); ?></li>
                        </ul>
                        <hr>
                        <a href="index.php?page=absen&pelajaran=<?= urlencode($jd['id_mengajar']); ?>" 
                           class="btn btn-default btn-block text-left">
                            <i class="fas fa-clipboard-check"></i> Isi Absen
                        </a>
                        <a href="index.php?page=rekap&id_mengajar=<?= urlencode($jd['id_mengajar']); ?>&jenis=hari" class="btn btn-secondary btn-block text-left">
                            <i class="fas fa-list-alt"></i> Rekap Absen
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12">
                <div class="alert alert-warning">Tidak ada jadwal mengajar ditemukan.</div>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
// Supaya klik tombol insert excel buka file dialog upload
document.getElementById('excelInput').style.display = 'none';
</script>