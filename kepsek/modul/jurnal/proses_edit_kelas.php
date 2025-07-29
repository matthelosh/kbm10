<?php
include 'koneksi.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['id'];
    $nama_kelas = mysqli_real_escape_string($conn, $_POST['nama_kelas']);
    $deskripsi = mysqli_real_escape_string($conn, $_POST['deskripsi']);

    $query = "UPDATE tb_mkelas SET nama_kelas='$nama_kelas', deskripsi='$deskripsi' WHERE id='$id'";

    if (mysqli_query($conn, $query)) {
        echo "<script>
                alert('Kelas berhasil diperbarui!');
                window.location.href = 'jurnal_harian.php';
              </script>";
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
?>
