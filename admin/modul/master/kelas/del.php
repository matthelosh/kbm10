<?php
$id = intval($_GET['id']);

// Cek apakah ada siswa di kelas ini
$cekSiswa = mysqli_query($con, "SELECT COUNT(*) AS jumlah FROM tb_siswa WHERE id_mkelas = $id");
$dataSiswa = mysqli_fetch_assoc($cekSiswa);

if ($dataSiswa['jumlah'] > 0) {
    // Ada siswa → tampilkan alert dan batalkan hapus
    echo "<script>
        alert('Kelas tidak bisa dihapus karena masih ada siswa yang terdaftar.');
        window.location='?page=master&act=kelas';
    </script>";
} else {
    // Tidak ada siswa → aman untuk hapus kelas
    $del = mysqli_query($con, "DELETE FROM tb_mkelas WHERE id_mkelas = $id");
    if ($del) {
        echo "<script>
            alert('Data kelas berhasil dihapus!');
            window.location='?page=master&act=kelas';
        </script>";
    } else {
        echo "<script>
            alert('Gagal menghapus kelas. Silakan coba lagi.');
            window.location='?page=master&act=kelas';
        </script>";
    }
}
?>
