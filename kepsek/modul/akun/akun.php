<div class="page-header">
  <ul class="breadcrumbs">
    <li class="nav-home">
      <a href="#"><i class="flaticon-home"></i></a>
    </li>
    <li class="separator"><i class="flaticon-right-arrow"></i></li>
    <li class="nav-item"><a href="#">Akun Saya</a></li>
  </ul>
</div>

<div class="col-md-6">
  <div class="card shadow rounded">
    <div class="card-header bg-primary text-white">
      <h4 class="card-title"><i class="fas fa-user-cog mr-2"></i> Pengaturan Akun</h4>
    </div>
    <div class="card-body">
      <ul class="nav nav-pills nav-secondary nav-pills-no-bd" id="pills-tab-without-border" role="tablist">
        <li class="nav-item">
          <a class="nav-link active" id="pills-home-tab-nobd" data-toggle="pill" href="#pills-home-nobd" role="tab" aria-controls="pills-home-nobd" aria-selected="true">
            <i class="fas fa-key mr-1"></i> Ganti Password
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" id="pills-profile-tab-nobd" data-toggle="pill" href="#pills-profile-nobd" role="tab" aria-controls="pills-profile-nobd" aria-selected="false">
            <i class="fas fa-image mr-1"></i> Ganti Foto
          </a>
        </li>
      </ul>

      <div class="tab-content mt-3 mb-3" id="pills-without-border-tabContent">
        <!-- Ganti Password -->
        <div class="tab-pane fade show active" id="pills-home-nobd" role="tabpanel">
          <form method="post">
            <div class="form-group">
              <input name="pass" type="password" class="form-control" placeholder="Password Lama" required>
            </div>
            <div class="form-group">
              <input name="pass1" type="password" class="form-control" placeholder="Password Baru" required>
            </div>
            <div class="form-group">
              <button name="changePassword" type="submit" class="btn btn-success btn-block">
                <i class="fas fa-sync-alt mr-1"></i> Ganti Password
              </button>
            </div>
          </form>

          <?php 
          if (isset($_POST['changePassword'])) {
            $passLama = $data['password'];
            $pass = sha1($_POST['pass']);
            $newPass = sha1($_POST['pass1']);

            if ($passLama == $pass) {
              $set = mysqli_query($con,"UPDATE tb_kepsek SET password='$newPass' WHERE id_kepsek='$data[id_kepsek]' ");
              echo "<script>
                setTimeout(function () {
                  swal('Berhasil', 'Password Berhasil Diubah', {
                    icon: 'success',
                    buttons: {
                      confirm: { className : 'btn btn-success' }
                    },
                  });
                },10);
                window.setTimeout(function(){
                  window.location.replace('?page=akun');
                },3000);
              </script>";
            } else {
              echo "<script>
                setTimeout(function () {
                  swal('Gagal', 'Password Lama Tidak cocok', {
                    icon: 'error',
                    buttons: {
                      confirm: { className : 'btn btn-danger' }
                    },
                  });
                },10);
                window.setTimeout(function(){
                  window.location.replace('?page=akun');
                },3000);
              </script>";
            }
          }
          ?>
        </div>

        <!-- Ganti Foto -->
        <div class="tab-pane fade" id="pills-profile-nobd" role="tabpanel">
          <form method="post" enctype="multipart/form-data">
            <div class="form-group text-center">
              <label><strong>Foto Profil Saat Ini</strong></label><br>
              <img src="/assets/img/user/<?=$data['foto'] ?>" class="img-thumbnail rounded-circle shadow" style="height: 100px; width: 100px;">
            </div>
            <div class="form-group">
              <input type="file" name="foto" class="form-control-file" accept="image/*" required>
              <input type="hidden" name="id" value="<?=$data['id_kepsek'] ?>">
            </div>
            <div class="form-group">
              <button name="updateProfile" type="submit" class="btn btn-primary btn-block">
                <i class="fas fa-upload mr-1"></i> Simpan
              </button>
            </div>
          </form>

          <?php 
          if (isset($_POST['updateProfile'])) {
            $gambar = @$_FILES['foto']['name'];
            if (!empty($gambar)) {
              move_uploaded_file($_FILES['foto']['tmp_name'], "/assets/img/user/$gambar");
              $ganti = mysqli_query($con, "UPDATE tb_kepsek SET foto='$gambar' WHERE id_kepsek='$_POST[id]'");
              if ($ganti) {
                echo "<script>
                  setTimeout(function () {
                    swal('Berhasil', 'Foto Berhasil Diubah', {
                      icon: 'success',
                      buttons: {
                        confirm: { className : 'btn btn-success' }
                      },
                    });
                  },10);
                  window.setTimeout(function(){
                    window.location.replace('?page=akun');
                  },3000);
                </script>";
              }
            }
          }
          ?>
        </div>
      </div>
    </div>
  </div>
</div>

<a href="javascript:history.back()" class="btn btn-outline-secondary btn-block mt-3">
  <i class="fas fa-arrow-left"></i> Kembali
</a>