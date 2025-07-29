<?php
include 'koneksi.php';
if (session_status() === PHP_SESSION_NONE) session_start();

// Cek login guru
if (!isset($_SESSION['guru'])) {
    echo "<script>alert('Akses ditolak. Guru belum login.'); window.location='../user.php';</script>";
    exit;
}

$id_login = $_SESSION['guru'];

// Ambil kelas & mapel berdasarkan jadwal aktif
$query = "
SELECT DISTINCT m.id_mengajar, k.id_mkelas, k.nama_kelas, mp.mapel AS nama_mapel
FROM tb_mengajar m
JOIN tb_mkelas k ON m.id_mkelas = k.id_mkelas
JOIN tb_master_mapel mp ON m.id_mapel = mp.id_mapel
JOIN tb_thajaran t ON m.id_thajaran = t.id_thajaran
WHERE m.id_guru = '$id_login' AND t.status = 1
ORDER BY k.nama_kelas ASC
";

$result = mysqli_query($conn, $query);
if (!$result) {
    die("Query error: " . mysqli_error($conn));
}
?>

<div class="page-inner">
    <div class="page-header">
        <h4 class="page-title">Jurnal Harian</h4>
        <ul class="breadcrumbs">
            <li class="nav-home"><a href="#"><i class="flaticon-home"></i></a></li>
            <li class="separator"><i class="flaticon-right-arrow"></i></li>
            <li class="nav-item"><a href="#">Daftar Kelas</a></li>
        </ul>
    </div>

    <!-- Notifikasi sukses edit -->
    <?php if (isset($_GET['pesan']) && $_GET['pesan'] === 'edit_sukses'): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert" id="pesanSukses">
            ✅ <strong>Berhasil!</strong> Kelas berhasil diperbarui.
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    <?php endif; ?>

    <div class="row">
        <?php if (mysqli_num_rows($result) > 0): ?>
            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                <div class="col-md-6 col-xs-12">
                    <div class="alert alert-info alert-dismissible" role="alert">
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close"></button>
                        <h3><strong><?= htmlspecialchars($row['nama_kelas']) ?></strong></h3>
                        <hr>
                        <ul>
                            <li><strong>Mata Pelajaran:</strong> <strong><?= htmlspecialchars($row['nama_mapel']) ?></strong></li>
                        </ul>
                        <hr>
                        <a href="modul/jurnal/tambah_jurnal.php?id_mengajar=<?= $row['id_mengajar'] ?>" 
                           class="btn btn-primary btn-block text-left">
                            <i class="fas fa-book"></i> Tambah Jurnal
                        </a>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="col-12">
                <div class="alert alert-warning">Belum ada kelas yang tersedia.</div>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
// Hilangkan notifikasi sukses setelah 3 detik
setTimeout(function () {
    const alert = document.getElementById('pesanSukses');
    if (alert) {
        alert.classList.remove('show');
        alert.classList.add('fade');
    }
}, 3000);
</script>
