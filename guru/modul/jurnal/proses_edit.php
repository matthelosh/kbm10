<?php
include 'koneksi.php'; // Sesuaikan path koneksi.php

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['id'];
    $tanggal = $_POST['tanggal'];
    $jam_ke = $_POST['jam_ke'];
    $kelas = $_POST['kelas'];
    $mapel = $_POST['mapel'];
    $uraian_kegiatan = $_POST['uraian_kegiatan'];
    $catatan_perkembangan = $_POST['catatan_perkembangan'];

    // Gunakan prepared statement untuk keamanan
    $query = "UPDATE jurnal_mengajar SET 
                tanggal = ?, 
                jam_ke = ?, 
                kelas = ?, 
                mapel = ?, 
                uraian_kegiatan = ?, 
                catatan_perkembangan = ? 
              WHERE id = ?";

    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "sissssi", 
        $tanggal, 
        $jam_ke, 
        $kelas, 
        $mapel, 
        $uraian_kegiatan, 
        $catatan_perkembangan, 
        $id
    );

    if (mysqli_stmt_execute($stmt)) {
        // Redirect ke halaman utama setelah edit berhasil
        header("Location: http://localhost/absensiswa/guru/?page=jurnalisi&pelajaran=10");
        exit(); // Pastikan tidak ada output lain setelah redirect
    } else {
        echo "Terjadi kesalahan: " . mysqli_error($conn);
    }
}
?>
