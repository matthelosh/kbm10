<?php
$host = "localhost";  // Sesuaikan dengan server MySQL
$user = "user_kbm";       // Username MySQL (default: root)
$pass = "pass_kbmqweQWE!@#";           // Password MySQL (kosong jika default)
$db   = "kbm_db";  // Nama database

$conn = mysqli_connect($host, $user, $pass, $db);

// Cek koneksi
if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}
?>
