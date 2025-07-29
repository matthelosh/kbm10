<?php
session_start();
include 'koneksi.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    $delete_query = "DELETE FROM jurnal_mengajar WHERE id = $id";
    if (mysqli_query($conn, $delete_query)) {
        $_SESSION['flash_message'] = 'Jurnal berhasil dihapus!';
        header("Location: jurnal.php"); // balik ke file yang sejajar
        exit;
    } else {
        echo "Error: " . mysqli_error($conn);
    }
} else {
    echo "ID tidak ditemukan!";
}
?>
