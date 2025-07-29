<?php 
require_once __DIR__ . '/../../../config/db.php';

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);

    // Cek apakah jadwal memiliki data absensi
    $cekAbsensi = mysqli_num_rows(mysqli_query($con, "SELECT * FROM _logabsensi WHERE id_mengajar = '$id'"));

    if ($cekAbsensi > 0) {
        // Ada data absensi, tampilkan peringatan
        echo "<script>
            alert('Tidak bisa menghapus data yang telah memiliki daftar absensi siswa.');
            window.location.href = 'dashboard.php?page=jadwal';
        </script>";
    } else {
        // Aman untuk hapus
        $hapus = mysqli_query($con, "DELETE FROM tb_mengajar WHERE id_mengajar = '$id'");

        if ($hapus) {
            echo "<script>
                alert('Jadwal berhasil dihapus.');
                window.location.href = 'dashboard.php?page=jadwal';
            </script>";
        } else {
            echo "<script>
                alert('Gagal menghapus jadwal.');
                window.location.href = 'dashboard.php?page=jadwal';
            </script>";
        }
    }
}
?>