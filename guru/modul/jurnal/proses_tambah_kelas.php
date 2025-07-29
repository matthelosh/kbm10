<?php
include 'koneksi.php'; // Pastikan path koneksi benar

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nama_kelas = mysqli_real_escape_string($conn, $_POST['nama_kelas']);
    $deskripsi = mysqli_real_escape_string($conn, $_POST['deskripsi']);

    $query = "INSERT INTO tb_mkelas (nama_kelas, deskripsi) VALUES ('$nama_kelas', '$deskripsi')";

    if (mysqli_query($conn, $query)) {
        echo "<script>
                alert('Kelas berhasil ditambahkan!');
                window.location.href = 'jurnal_harian.php';
              </script>";
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
?>
