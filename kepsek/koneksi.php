<?php
$host = 'localhost';
$user = 'user_kbm';
$pass = 'pass_kbmqweQWE!@#';
$db   = 'kbm_db';

$con = mysqli_connect($host, $user, $pass, $db);

if (mysqli_connect_errno()) {
    echo "Koneksi database gagal: " . mysqli_connect_error();
    exit();
}
?>
