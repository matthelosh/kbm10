<?php
// Handler untuk set aktif / nonaktif semester
if (isset($_GET['act']) && $_GET['act'] == 'set_semester' && isset($_GET['id']) && isset($_GET['status'])) {
    $idSemester = intval($_GET['id']);
    $statusBaru = intval($_GET['status']);

    // Jika ingin mengaktifkan semester, nonaktifkan semua semester lain dulu
    if ($statusBaru == 1) {
        mysqli_query($con, "UPDATE tb_semester SET status=0");
    }

    // Update status semester yang dipilih
    $update = mysqli_query($con, "UPDATE tb_semester SET status='$statusBaru' WHERE id_semester='$idSemester'");

    if ($update) {
        echo "<script>
            alert('Status semester berhasil diubah!');
            window.location='?page=master&act=semester';
        </script>";
    } else {
        echo "<script>
            alert('Gagal mengubah status semester!');
            window.location='?page=master&act=semester';
        </script>";
    }
}
?>

<div class="page-inner">
    <div class="page-header">
        <h4 class="page-title">Semester</h4>
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
                <a href="#">Data Umum</a>
            </li>
            <li class="separator">
                <i class="flaticon-right-arrow"></i>
            </li>
            <li class="nav-item">
                <a href="#">Daftar Semester</a>
            </li>
        </ul>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="card-title">
                        <a href="" class="btn btn-primary btn-sm text-white" data-toggle="modal" data-target="#addSemester">
                            <i class="fa fa-plus"></i> Tambah
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-sm">
                            <thead>
                                <tr>
                                    <th>No.</th>
                                    <th>Semester</th>
                                    <th>Status</th>
                                    <th>Opsi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $no = 1;
                                $semester = mysqli_query($con, "SELECT * FROM tb_semester");
                                foreach ($semester as $k) { ?>
                                <tr>
                                    <td><?= $no++; ?>.</td>
                                    <td><?= htmlspecialchars($k['semester']); ?></td>
                                    <td>
                                        <?php 
                                        if ($k['status'] == 0) {
                                            echo "<span class='badge badge-danger'>Tidak Aktif</span>";
                                        } else {
                                            echo "<span class='badge badge-success'>Aktif</span>";
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <?php if ($k['status'] == 0) { ?>
                                            <a onclick="return confirm('Yakin Aktifkan Semester?')" 
                                               href="?page=master&act=set_semester&id=<?= $k['id_semester'] ?>&status=1" 
                                               class="btn btn-success btn-sm">
                                                <i class="fas fa-check-circle"></i> Aktifkan
                                            </a>
                                        <?php } else { ?>
                                            <a onclick="return confirm('Yakin Nonaktifkan Semester?')" 
                                               href="?page=master&act=set_semester&id=<?= $k['id_semester'] ?>&status=0" 
                                               class="btn btn-danger btn-sm">
                                                <i class="far fa-times-circle"></i> Nonaktif
                                            </a>
                                        <?php } ?>

                                        <a href="" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#edit<?= $k['id_semester'] ?>">
                                            <i class="far fa-edit"></i> Edit
                                        </a>
                                        <a class="btn btn-danger btn-sm" onclick="return confirm('Yakin Hapus Data?')" 
                                           href="?page=master&act=delsemester&id=<?= $k['id_semester'] ?>">
                                            <i class="fas fa-trash"></i> Del
                                        </a>

                                        <!-- Modal Edit Semester -->
                                        <div aria-hidden="true" aria-labelledby="myModalLabel" role="dialog" tabindex="-1" id="edit<?= $k['id_semester'] ?>" class="modal fade" style="display: none;">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h4 class="modal-title">Edit Semester</h4>
                                                        <button type="button" data-dismiss="modal" aria-label="Close" class="close">
                                                            <span aria-hidden="true">×</span>
                                                        </button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <form action="" method="post">
                                                            <input name="id" type="hidden" value="<?= $k['id_semester'] ?>">
                                                            <div class="form-group">
                                                                <label>Semester</label>
                                                                <input name="semester" type="text" value="<?= htmlspecialchars($k['semester']) ?>" class="form-control">
                                                            </div>
                                                            <div class="form-group">
                                                                <button name="edit" class="btn btn-primary" type="submit">Edit</button>
                                                            </div>
                                                        </form>
                                                        <?php 
                                                        if (isset($_POST['edit'])) {
                                                            $save = mysqli_query($con, "UPDATE tb_semester SET semester='$_POST[semester]' WHERE id_semester='$_POST[id]'");
                                                            if ($save) {
                                                                echo "<script>
                                                                    alert('Data diubah!');
                                                                    window.location='?page=master&act=semester';
                                                                </script>";                        
                                                            }
                                                        }
                                                        ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- /.modal -->
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

<!-- Modal Tambah Semester -->
<div aria-hidden="true" aria-labelledby="myModalLabel" role="dialog" tabindex="-1" id="addSemester" class="modal fade" style="display: none;">
    <div class="modal-dialog">
        <div class="modal-content">           
            <div class="modal-header">
                <h4 class="modal-title">Tambah Semester</h4>
                <button type="button" data-dismiss="modal" aria-label="Close" class="close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <form action="" method="post" class="form-horizontal">
                    <div class="form-group">
                        <label>Semester</label>
                        <input name="semester" type="text" placeholder="Nama semester .." class="form-control">
                    </div>
                    <div class="form-group">
                        <button name="save" class="btn btn-primary" type="submit">Save</button>
                    </div>
                </form>
                <?php 
                if (isset($_POST['save'])) {
                    $save = mysqli_query($con, "INSERT INTO tb_semester VALUES(NULL,'$_POST[semester]','1')");
                    if ($save) {
                        echo "<script>
                            alert('Data tersimpan!');
                            window.location='?page=master&act=semester';
                        </script>";                        
                    }
                }
                ?>
            </div>
        </div>
    </div>
</div>
<!-- /.modal -->