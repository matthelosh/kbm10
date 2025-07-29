<?php
if (isset($_POST['editSiswa'])) {
    require_once __DIR__ . '/../../../config/db.php';

    $id       = $_POST['id'];
    $nama     = $_POST['nama'];
    $nis      = $_POST['nis'];
    $tempat   = $_POST['tempat'];
    $tgl      = $_POST['tgl'];
    $jk       = $_POST['jk'];
    $alamat   = $_POST['alamat'];
    $kelas    = $_POST['kelas'];
    $th_masuk = $_POST['th_masuk'];
    $is_ketua = $_POST['is_ketua'] ?? 0;
    $status   = $_POST['status'] ?? 0;

    // Ambil nama file lama (jika ada)
    $q_old = mysqli_query($con, "SELECT foto FROM tb_siswa WHERE id_siswa = '$id'");
    $oldData = mysqli_fetch_assoc($q_old);
    $oldFoto = $oldData['foto'];

    // Upload foto jika ada
    $fotoUpdate = "";
    if (!empty($_FILES['foto']['name'])) {
        $namaFile = time() . '_' . $_FILES['foto']['name'];
        $tmp      = $_FILES['foto']['tmp_name'];
        $folder   = '/assets/img/user/' . $namaFile;

        // Pindahkan dan update nama file
        if (move_uploaded_file($tmp, $folder)) {
            $fotoUpdate = ", foto='$namaFile'";

            // Optional: hapus file lama jika berbeda
            if (!empty($oldFoto) && file_exists("/assets/img/user/$oldFoto")) {
                unlink("/assets/img/user/$oldFoto");
            }
        }
    }

    // Update data
    $update = mysqli_query($con, "UPDATE tb_siswa SET 
    nama_siswa   = '$nama',
    tempat_lahir = '$tempat',
    tgl_lahir    = '$tgl',
    jk           = '$jk',
    alamat       = '$alamat',
    id_mkelas    = '$kelas',
    th_angkatan  = '$th_masuk',
    is_ketua     = '$is_ketua',
    status       = '$status'
    $fotoUpdate
    WHERE id_siswa = '$id'
    ");


    // Ambil id_mkelas untuk redirect
    $getKelas = mysqli_query($con, "SELECT id_mkelas FROM tb_siswa WHERE id_siswa = '$id'");
    $dataKelas = mysqli_fetch_assoc($getKelas);
    $id_mkelas = $dataKelas['id_mkelas'];

    if ($update) {
        echo "<script>alert('Data siswa berhasil diperbarui!'); window.location='index.php?page=daftar&id_mkelas=$id_mkelas';</script>";
    } else {
        echo "<script>alert('Gagal mengupdate data.'); window.history.back();</script>";
    }
} else {
    echo "<script>alert('Akses tidak valid!'); window.location='index.php';</script>";
}
?>