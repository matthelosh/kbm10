<?php

if (isset($_POST['saveGuru'])) {

    // Simpan password secara langsung (plaintext)
    $pass = $_POST['nip'];  // Menggunakan NIP sebagai password

    // Menangani upload foto
    $sumber = @$_FILES['foto']['tmp_name'];
    $target = '/assets/img/user/';
    $nama_gambar = @$_FILES['foto']['name'];
    $pindah = move_uploaded_file($sumber, $target . $nama_gambar);

    if ($pindah) {
        // Menyimpan data guru baru ke dalam database
        $save = mysqli_query($con, "INSERT INTO tb_guru VALUES(NULL, '$_POST[nip]', '$_POST[nama]', '$_POST[email]', '$pass', '$nama_gambar', 'Y')");
        if ($save) {
            echo "
            <script type='text/javascript'>
            setTimeout(function () { 
                swal('($_POST[nama]) ', 'Berhasil disimpan', {
                    icon : 'success',
                    buttons: {        			
                        confirm: {
                            className : 'btn btn-success'
                        }
                    },
                });    
            }, 10);  
            window.setTimeout(function(){ 
                window.location.replace('?page=guru');
            }, 3000);   
            </script>";
        }
    }

} elseif (isset($_POST['editGuru'])) {

    // Variabel untuk memperbarui password jika password baru diisi
    $updatePassword = "";

    // Cek apakah password baru dimasukkan
    if (!empty($_POST['passBaru'])) {
        // Simpan password baru secara langsung (plaintext)
        $newPass = $_POST['passBaru'];
        // Jika ada password baru, tambahkan ke query untuk update
        $updatePassword = ", password='$newPass'";
    }

    // Menangani upload foto jika ada perubahan foto
    $gambar = @$_FILES['foto']['name'];
    if (!empty($gambar)) {
        move_uploaded_file($_FILES['foto']['tmp_name'], "/assets/img/user/$gambar");
        // Update foto jika ada perubahan
        $ganti = mysqli_query($con, "UPDATE tb_guru SET foto='$gambar' WHERE id_guru='$_POST[id]'");
    }

    // Proses update data guru (nama, email, dan password jika ada)
    $editGuru = mysqli_query($con, "UPDATE tb_guru SET nama_guru='$_POST[nama]', email='$_POST[email]' $updatePassword WHERE id_guru='$_POST[id]'");

    // Proses sukses atau gagal
    if ($editGuru) {
        echo "
        <script type='text/javascript'>
        setTimeout(function () { 
            swal('($_POST[nama]) ', 'Berhasil diubah', {
                icon : 'success',
                buttons: {        			
                    confirm: {
                        className : 'btn btn-success'
                    }
                },
            });    
        }, 10);  
        window.setTimeout(function(){ 
            window.location.replace('?page=guru');
        }, 3000);   
        </script>";
    }
}
?>