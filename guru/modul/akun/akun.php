<div class="page-header">
    <ul class="breadcrumbs">
        <li class="nav-home">
            <a href="#">
                <i class="flaticon-home"></i>
            </a>
        </li>
        <li class="separator">
            <i class="flaticon-right-arrow"></i>
        </li>
        <li class="nav-item">
            <a href="#">Akun Saya</a>
        </li>
    </ul>
</div>

<div class="col-md-6">
    <div class="card">
        <div class="card-header">
            <h4 class="card-title">Pengaturan Akun</h4>
        </div>
        <div class="card-body">
            <form action="" method="post" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="nama_guru">Nama Lengkap</label>
                    <input name="nama_guru" type="text" class="form-control" id="nama_guru" value="<?=$data['nama_guru']?>" required>
                </div>

                <div class="form-group">
                    <label>Foto Profile</label>
                    <p>
                        <center>
                            <img src="/assets/img/user/<?=$data['foto'] ?>" class="img-thumbnail" style="height: 90px;width: 90px;">
                        </center>
                    </p>
                    <input type="file" name="foto"> 
                </div>

                <div class="form-group">
                    <label for="pass">Password Lama</label>
                    <input name="pass" type="password" class="form-control" id="pass" placeholder="Password Lama" required>
                </div>

                <div class="form-group">
                    <label for="pass1">Password Baru</label>
                    <input name="pass1" type="password" class="form-control" id="pass1" placeholder="Password Baru" required>
                </div>

                <div class="form-row">
                    <div class="col">
                        <button name="updateProfile" type="submit" class="btn btn-primary btn-block">Simpan Perubahan</button>
                    </div>
                    <!--<div class="col">
                        <a href="<?php echo ($_SERVER['SERVER_NAME'] == 'localhost') ? 'http://localhost/kbm/guru/index.php' : 'https://' . $_SERVER['HTTP_HOST'] . '/kbm/guru/index.php'; ?>" class="btn btn-default btn-block">Kembali</a>
                    </div> -->
                    <a href="javascript:history.back()" class="btn btn-outline-secondary btn-block mt-3"><i class="fas fa-arrow-left"></i> Kembali </a>
                </div>
            </form>

            <?php 
            if (isset($_POST['updateProfile'])) {
                // Update Nama Guru dan Foto
                $namaGuru = $_POST['nama_guru'];
                $foto = @$_FILES['foto']['name'];
                
                if (!empty($foto)) {
                    move_uploaded_file($_FILES['foto']['tmp_name'], "/assets/img/user/$foto");
                    $updateFoto = ", foto='$foto'";
                } else {
                    $updateFoto = "";
                }

                // Update Password
                $passLama = $data['password'];
                $pass = sha1($_POST['pass']);
                $newPass = sha1($_POST['pass1']);

                if ($passLama == $pass && !empty($newPass)) {
                    $setPass = ", password='$newPass'";
                } else {
                    $setPass = "";
                }

                // Proses update data
                $updateQuery = "UPDATE tb_guru SET nama_guru='$namaGuru' $updateFoto $setPass WHERE id_guru='$data[id_guru]'";
                $updateResult = mysqli_query($con, $updateQuery);

                if ($updateResult) {
                    echo "
                    <script type='text/javascript'>
                        setTimeout(function () { 
                            swal('Berhasil', 'Profil Berhasil Diubah', {
                                icon : 'success',
                                buttons: {        			
                                    confirm: {
                                        className : 'btn btn-success'
                                    }
                                },
                            });    
                        },10);  
                        window.setTimeout(function(){ 
                            window.location.replace('?page=akun');
                        } ,3000);   
                    </script>";
                } else {
                    echo "
                    <script type='text/javascript'>
                        setTimeout(function () { 
                            swal('Gagal', 'Ada kesalahan dalam proses pengubahan data', {
                                icon : 'error',
                                buttons: {        			
                                    confirm: {
                                        className : 'btn btn-danger'
                                    }
                                },
                            });    
                        },10);  
                        window.setTimeout(function(){ 
                            window.location.replace('?page=akun');
                        } ,3000);   
                    </script>";
                }
            }
            ?>
        </div>
    </div>
</div>

<script>
    // Script tambahan jika diperlukan
</script>