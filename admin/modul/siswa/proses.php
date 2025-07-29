<?php
//require_once __DIR__ . '../../../../vendor/autoload.php';
//require_once __DIR__ . '../../../../config/db.php';
include '../../../db.php';

// =====================
// PROSES SIMPAN SISWA
// =====================
if (isset($_POST['saveSiswa'])) {
    // Pastikan semua input valid
    $nama     = mysqli_real_escape_string($con, $_POST['nama']);
    $nis      = mysqli_real_escape_string($con, $_POST['nis']);
    $jk       = mysqli_real_escape_string($con, $_POST['jk']);
    $kelas    = mysqli_real_escape_string($con, $_POST['kelas']);
    $th_angkatan = mysqli_real_escape_string($con, $_POST['th_angkatan']);
    $is_ketua = isset($_POST['is_ketua']) ? $_POST['is_ketua'] : 0;
    $foto     = '';

    // Password default = NIS (di-hash)
    $pass = password_hash($nis, PASSWORD_DEFAULT);

    // Upload foto
    if ($_FILES['foto']['name'] != '') {
        $tmp          = $_FILES['foto']['tmp_name'];
        $nama_gambar  = uniqid() . '_' . $_FILES['foto']['name'];
        move_uploaded_file($tmp, "/assets/img/user/" . $nama_gambar);
        $foto = $nama_gambar;
    }

    // Cek jika sudah ada ketua kelas lain
    if ($is_ketua == 1) {
        $cek = mysqli_query($con, "SELECT * FROM tb_siswa WHERE id_mkelas = '$kelas' AND is_ketua = 1");
        if (mysqli_num_rows($cek) > 0) {
            echo "<script>alert('Ketua kelas sudah ada di kelas ini!');window.location='?page=siswa';</script>";
            exit;
        }
    }

    // Query untuk memasukkan data
    $query = mysqli_query($con, "INSERT INTO tb_siswa 
        (nis, nama_siswa, jk, foto, password, status, th_angkatan, id_mkelas, is_ketua) 
        VALUES 
        ('$nis', '$nama', '$jk', '$foto', '$pass', '1', '$th_angkatan', '$kelas', '$is_ketua')");

    if ($query) {
        echo "<script>alert('Data siswa berhasil disimpan');window.location='?page=siswa';</script>";
    } else {
        // Debugging error
        echo "<script>alert('Gagal menyimpan data siswa: " . mysqli_error($con) . "');window.location='?page=siswa';</script>";
    }
}

// =====================
// PROSES EDIT / UPDATE SISWA
// =====================
if (isset($_POST['editSiswa'])) {
    // Pastikan id ada di URL
    if (!isset($_GET['id']) || empty($_GET['id'])) {
        die("ID siswa tidak ditemukan.");
    }

    $id = $_POST['id'];
    $nama = mysqli_real_escape_string($con, $_POST['nama']);
    $jk = mysqli_real_escape_string($con, $_POST['jk']);
    $kelas = mysqli_real_escape_string($con, $_POST['kelas']);
    $is_ketua = isset($_POST['is_ketua']) ? $_POST['is_ketua'] : 0;
    $th_angkatan = mysqli_real_escape_string($con, $_POST['th_angkatan']);
    $status = isset($_POST['status']) ? $_POST['status'] : 0;
    $updateFoto = '';

    // Upload foto baru jika diinput
    if ($_FILES['foto']['name'] != '') {
        $tmp = $_FILES['foto']['tmp_name'];
        $nama_gambar = uniqid() . '_' . $_FILES['foto']['name'];
        move_uploaded_file($tmp, "/assets/img/user/" . $nama_gambar);
        $updateFoto = ", foto = '$nama_gambar'";
    }

    // Cek ketua kelas hanya boleh 1 per kelas
    if ($is_ketua == 1) {
        $cek = mysqli_query($con, "SELECT * FROM tb_siswa WHERE id_mkelas = '$kelas' AND is_ketua = 1 AND id_siswa != '$id'");
        if (mysqli_num_rows($cek) > 0) {
            echo "<script>alert('Ketua kelas sudah ada di kelas ini!');window.location='?page=siswa';</script>";
            exit;
        }
    }

    // Query untuk update data siswa
    $query = mysqli_query($con, "UPDATE tb_siswa SET 
        nama_siswa = '$nama',
        jk = '$jk',
        id_mkelas = '$kelas',
        is_ketua = '$is_ketua',
        th_angkatan = '$th_angkatan',
        status = '$status'
        $updateFoto
        WHERE id_siswa = '$id'");

    if ($query) {
        echo "<script>alert('Data siswa berhasil diupdate');window.location='?page=siswa';</script>";
    } else {
        // Tampilkan pesan error
        echo "<script>alert('Gagal update data siswa: " . mysqli_error($con) . "');window.history.back();</script>";
    }
}
?>