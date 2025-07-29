<?php
require_once __DIR__ . '/../../../config/db.php';

if (isset($_POST['save'])) {
    $kode     = mysqli_real_escape_string($con, trim($_POST['kode']));
    $ta       = mysqli_real_escape_string($con, $_POST['ta']);
    $semester = mysqli_real_escape_string($con, $_POST['semester']);
    $guru     = mysqli_real_escape_string($con, $_POST['guru']);
    $mapel    = mysqli_real_escape_string($con, $_POST['mapel']);
    $hari     = mysqli_real_escape_string($con, $_POST['hari']);
    $kelas    = mysqli_real_escape_string($con, $_POST['kelas']);
    $waktu    = mysqli_real_escape_string($con, trim($_POST['waktu']));
    $jamke    = mysqli_real_escape_string($con, trim($_POST['jamke']));
    $ruang    = mysqli_real_escape_string($con, trim($_POST['ruang']));

    $query = "INSERT INTO tb_mengajar 
              (kode_pelajaran, hari, jam_mengajar, jamke, ruang, id_guru, id_mapel, id_mkelas, id_semester, id_thajaran) 
              VALUES ('$kode', '$hari', '$waktu', '$jamke', '$ruang', '$guru', '$mapel', '$kelas', '$semester', '$ta')";

    if (mysqli_query($con, $query)) {
        header("Location: ../../dashboard.php?page=jadwal");
        exit();
    } else {
        $err = mysqli_error($con);
        echo "<script>alert('Gagal menyimpan: $err');window.history.back();</script>";
    }
} else {
    header("Location: ../../dashboard.php?page=jadwal");
    exit();
}
?>
