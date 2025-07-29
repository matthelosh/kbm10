<?php 
require_once __DIR__ . '/../../../config/db.php';

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = intval($_GET['id']); // pastikan id integer
    
    $del = mysqli_query($con, "DELETE FROM tb_siswa WHERE id_siswa=$id");
    
    if ($del) {
        echo "<script>
            alert('Data telah dihapus!');
            window.location='../../index.php?page=daftar';
        </script>";
    } else {
        echo "<script>
            alert('Gagal menghapus data!');
            window.location='../../index.php?page=daftar';
        </script>";
    }
} else {
    echo "<script>
        alert('ID tidak valid atau tidak ditemukan!');
        window.location='../../index.php?page=daftar';
    </script>";
}
?>
