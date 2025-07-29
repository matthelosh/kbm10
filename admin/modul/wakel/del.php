<?php 
require_once __DIR__ . '/../../../config/db.php';
if (isset($_GET['id'])) {
    $id = intval($_GET['id']);

	echo "ID yang akan dihapus: $id<br>";

    $del = mysqli_query($con, "DELETE FROM tb_walikelas WHERE id_walikelas = $id");

    if ($del) {
        echo "<script>
            alert('Data berhasil dihapus!');
            window.location='?page=walas';
        </script>";
    } else {
        echo "<script>
            alert('Gagal menghapus data: " . mysqli_error($con) . "');
            window.location='?page=walas';
        </script>";
    }
} else {
    echo "<script>
        alert('ID tidak ditemukan!');
        window.location='?page=walas';
    </script>";
}

?>