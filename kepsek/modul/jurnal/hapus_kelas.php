<?php
include 'koneksi.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $query = "DELETE FROM tb_mkelas WHERE id = '$id'";

    if (mysqli_query($conn, $query)) {
        echo "<script>
                alert('Kelas berhasil dihapus!');
                window.location.href = 'jurnal_harian.php';
              </script>";
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
?>
