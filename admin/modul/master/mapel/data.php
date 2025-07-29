<div class="page-inner">
  <div class="page-header">
    <h4 class="page-title">Mata Pelajaran</h4>
    <ul class="breadcrumbs">
      <li class="nav-home">
        <a href="#"><i class="flaticon-home"></i></a>
      </li>
      <li class="separator"><i class="flaticon-right-arrow"></i></li>
      <li class="nav-item"><a href="#">Data Umum</a></li>
      <li class="separator"><i class="flaticon-right-arrow"></i></li>
      <li class="nav-item"><a href="#">Daftar Mata Pelajaran</a></li>
    </ul>
  </div>
  <div class="row">
    <div class="col-md-12">
      <div class="card">
        <div class="card-header">
          <div class="card-title">
            <a href="" class="btn btn-primary btn-sm text-white" data-toggle="modal" data-target="#myModal">
              <i class="fa fa-plus text-white"></i> Tambah
            </a>
          </div>
        </div>
        <div class="card-body">
          <div class="table-responsive">
            <table class="table table-hover table-striped">
              <thead>
                <tr>
                  <th>#</th>
                  <th>Kode</th>
                  <th>Nama Mapel</th>
                  <th>Opsi</th>
                </tr>
              </thead>
              <tbody>
                <?php 
                $no = 1;
                $mapel = mysqli_query($con,"SELECT * FROM tb_master_mapel");
                foreach ($mapel as $k) { ?>
                <tr>
                  <td><?= $no++; ?>.</td>
                  <td><?= $k['kode_mapel']; ?></td>
                  <td><?= $k['mapel']; ?></td>
                  <td>
                    <a href="" class="btn btn-info btn-sm" data-toggle="modal" data-target="#edit<?= $k['id_mapel'] ?>">
                      <i class="far fa-edit"></i> Edit
                    </a>
                    <a class="btn btn-danger btn-sm" onclick="return confirm('Yakin Hapus Data ??')" href="?page=master&act=delmapel&id=<?= $k['id_mapel'] ?>">
                      <i class="fas fa-trash"></i> Del
                    </a>

                    <!-- Modal Edit -->
                    <div class="modal fade" id="edit<?= $k['id_mapel'] ?>" tabindex="-1" role="dialog">
                      <div class="modal-dialog">
                        <div class="modal-content">
                          <div class="modal-header">
                            <h4 class="modal-title">Edit Mapel</h4>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                          </div>
                          <div class="modal-body">
                            <form action="" method="post">
                              <input type="hidden" name="id" value="<?= $k['id_mapel'] ?>">
                              <div class="form-group">
                                <label>Kode Mapel</label>
                                <input name="kode" type="text" value="<?= $k['kode_mapel'] ?>" class="form-control">
                              </div>
                              <div class="form-group">
                                <label>Nama Mapel</label>
                                <input name="mapel" type="text" value="<?= $k['mapel'] ?>" class="form-control">
                              </div>
                              <div class="form-group">
                                <button name="edit" class="btn btn-primary" type="submit">Edit</button>
                              </div>
                            </form>
                            <?php 
                            if (isset($_POST['edit']) && $_POST['id'] == $k['id_mapel']) {
                              $id = $_POST['id'];
                              $kode = $_POST['kode'];
                              $mapel = $_POST['mapel'];
                              $cek = mysqli_query($con, "SELECT * FROM tb_master_mapel WHERE (kode_mapel='$kode' OR mapel='$mapel') AND id_mapel != '$id'");
                              if (mysqli_num_rows($cek) > 0) {
                                echo "<script>alert('Kode atau Nama Mapel sudah ada!');</script>";
                              } else {
                                $save = mysqli_query($con, "UPDATE tb_master_mapel SET kode_mapel='$kode', mapel='$mapel' WHERE id_mapel='$id'");
                                if ($save) {
                                  echo "<script>
                                      alert('Data diubah!');
                                      window.location='?page=master&act=mapel';
                                  </script>";
                                }
                              }
                            }
                            ?>
                          </div>
                        </div>
                      </div>
                    </div>
                    <!-- /.modal edit -->
                  </td>
                </tr>
                <?php } ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Modal Tambah -->
<div class="modal fade" id="myModal" tabindex="-1" role="dialog">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">Tambah Mapel</h4>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
      </div>
      <div class="modal-body">
        <form action="" method="post" class="form-horizontal">
          <div class="form-group">
            <label>Kode Mapel</label>
            <input name="kode" type="text" value="MP-<?= time() ?>" class="form-control">
          </div>
          <div class="form-group">
            <label>Nama Mapel</label>
            <input name="mapel" type="text" placeholder="Nama mapel .." class="form-control">
          </div>
          <div class="form-group">
            <button name="save" class="btn btn-primary" type="submit">Save</button>
          </div>
        </form>
        <?php 
        if (isset($_POST['save'])) {
          $kode = $_POST['kode'];
          $mapel = $_POST['mapel'];
          $cek = mysqli_query($con, "SELECT * FROM tb_master_mapel WHERE kode_mapel='$kode' OR mapel='$mapel'");
          if (mysqli_num_rows($cek) > 0) {
            echo "<script>alert('Kode atau Nama Mapel sudah ada!');</script>";
          } else {
            $save = mysqli_query($con, "INSERT INTO tb_master_mapel VALUES(NULL,'$kode','$mapel')");
            if ($save) {
              echo "<script>
                  alert('Data tersimpan!');
                  window.location='?page=master&act=mapel';
              </script>";
            }
          }
        }
        ?>
      </div>
    </div>
  </div>
</div>
<!-- /.modal tambah -->