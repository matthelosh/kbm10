<?php
session_start(); // kalau pakai session
include "../../../config/db.php"; // koneksi ke database

// === Proses Tambah Kepsek ===
if (isset($_POST['saveKepsek'])) {
    $nip = htmlspecialchars($_POST['nip']);
    $nama = htmlspecialchars($_POST['nama']);
    $email = htmlspecialchars($_POST['email']);
    $pass = sha1($nip);

    $sumber = @$_FILES['foto']['tmp_name'];
    $target = '/assets/img/user/';
    $nama_gambar = @$_FILES['foto']['name'];
    $pindah = false;

    if (!empty($nama_gambar)) {
        $pindah = move_uploaded_file($sumber, $target . $nama_gambar);
    }

    if ($pindah) {
        $save = mysqli_query($con, "INSERT INTO tb_kepsek VALUES (
            NULL, '$nip', '$nama', '$email', '$pass', '$nama_gambar', 'Y'
        )");

        if ($save) {
            echo "
            <script type='text/javascript'>
            setTimeout(function () {
                swal('$nama', 'Berhasil disimpan', {
                    icon : 'success',
                    buttons: {
                        confirm: { className : 'btn btn-success' }
                    },
                });
            }, 10);
            window.setTimeout(function(){
                window.location.replace('?page=kepsek');
            }, 3000);
            </script>";
        }
    } else {
        echo "
        <script type='text/javascript'>
        swal('Gagal', 'Foto tidak diupload atau gagal upload', 'error');
        </script>";
    }
}

// === Proses Edit Kepsek ===
elseif (isset($_POST['editKepsek'])) {
    $id = intval($_POST['id']);
    $nama = htmlspecialchars($_POST['nama']);
    $email = htmlspecialchars($_POST['email']);
    $gambar = @$_FILES['foto']['name'];

    if (!empty($gambar)) {
        move_uploaded_file($_FILES['foto']['tmp_name'], "/assets/img/user/$gambar");
        mysqli_query($con, "UPDATE tb_kepsek SET foto='$gambar' WHERE id_kepsek='$id'");
    }

    $editKepsek = mysqli_query($con, "UPDATE tb_kepsek SET 
        nama_kepsek='$nama', 
        email='$email' 
        WHERE id_kepsek='$id'");

    if ($editKepsek) {
        echo "
        <script type='text/javascript'>
        setTimeout(function () {
            swal('$nama', 'Berhasil diubah', {
                icon : 'success',
                buttons: {
                    confirm: { className : 'btn btn-success' }
                },
            });
        }, 10);
        window.setTimeout(function(){
            window.location.replace('?page=kepsek');
        }, 3000);
        </script>";
    }
}

// === Proses Aktifkan Kepsek ===
elseif (isset($_GET['aktifkan'])) {
    $id = intval($_GET['id']);
    mysqli_query($con, "UPDATE tb_kepsek SET status='N'"); // Nonaktifkan semua
    mysqli_query($con, "UPDATE tb_kepsek SET status='Y' WHERE id_kepsek='$id'"); // Aktifkan yang dipilih
    header("Location: ../../../admin/dashboard.php?page=kepsek");
    exit;
}

// === Proses Nonaktifkan Kepsek ===
elseif (isset($_GET['nonaktifkan'])) {
    $id = intval($_GET['id']);
    mysqli_query($con, "UPDATE tb_kepsek SET status='N' WHERE id_kepsek='$id'");
    header("Location: ../../../admin/dashboard.php?page=kepsek");
    exit;
}
?>