<div class="page-inner">
    <div class="page-header">
        <h4 class="page-title">Kelas</h4>
        <ul class="breadcrumbs">
            <li class="nav-home"><a href="#"><i class="flaticon-home"></i></a></li>
            <li class="separator"><i class="flaticon-right-arrow"></i></li>
            <li class="nav-item"><a href="#">Data Umum</a></li>
            <li class="separator"><i class="flaticon-right-arrow"></i></li>
            <li class="nav-item"><a href="#">Daftar Kelas</a></li>
        </ul>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <a href="#" class="btn btn-primary btn-sm text-white" data-toggle="modal" data-target="#addKelas">
                        <i class="fa fa-plus"></i> Tambah
                    </a>
                </div>

                <div class="card-body">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Kode Kelas</th>
                                <th>Nama Kelas</th>
                                <th>Opsi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $no = 1;
                            // Urutkan berdasarkan tingkatan (X, XI, XII), lalu jurusan (abjad), lalu nomor
                            $kelas = mysqli_query($con, "
                                SELECT * FROM tb_mkelas
                                ORDER BY 
                                    CASE
                                        WHEN nama_kelas LIKE 'X %' THEN 1
                                        WHEN nama_kelas LIKE 'XI %' THEN 2
                                        WHEN nama_kelas LIKE 'XII %' THEN 3
                                        ELSE 4
                                    END,
                                    nama_kelas ASC
                            ");
                            foreach ($kelas as $k): ?>
                                <tr>
                                    <td><b><?= $no++; ?>.</b></td>
                                    <td><?= htmlspecialchars($k['kd_kelas']); ?></td>
                                    <td><?= htmlspecialchars($k['nama_kelas']); ?></td>
                                    <td>
                                        <a href="#" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#edit<?= $k['id_mkelas'] ?>">
                                            <i class="far fa-edit"></i> Edit
                                        </a>
                                        <a href="?page=master&act=delkelas&id=<?= $k['id_mkelas'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Yakin Hapus Data?')">
                                            <i class="fas fa-trash"></i> Del
                                        </a>

                                        <!-- Modal Edit -->
                                        <div class="modal fade" id="edit<?= $k['id_mkelas'] ?>" tabindex="-1" role="dialog" aria-labelledby="modalEdit" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h4 class="modal-title">Edit Kelas</h4>
                                                        <button type="button" class="close" data-dismiss="modal"><span>×</span></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <form action="" method="post">
                                                            <input type="hidden" name="id" value="<?= $k['id_mkelas'] ?>">
                                                            <div class="form-group">
                                                                <label>Kode Kelas</label>
                                                                <input type="text" name="kode" class="form-control" value="<?= htmlspecialchars($k['kd_kelas']) ?>" required>
                                                            </div>
                                                            <div class="form-group">
                                                                <label>Nama Kelas</label>
                                                                <input type="text" name="kelas" class="form-control" value="<?= htmlspecialchars($k['nama_kelas']) ?>" required>
                                                            </div>
                                                            <button type="submit" name="edit" class="btn btn-primary">Simpan Perubahan</button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- End Modal Edit -->
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Modal Tambah -->
            <div class="modal fade" id="addKelas" tabindex="-1" role="dialog" aria-labelledby="modalTambah" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title">Tambah Kelas</h4>
                            <button type="button" class="close" data-dismiss="modal"><span>×</span></button>
                        </div>
                        <div class="modal-body">
                            <form action="" method="post">
                                <div class="form-group">
                                    <label>Kode Kelas</label>
                                    <input type="text" name="kode" class="form-control" placeholder="Contoh: KL-01" required>
                                </div>
                                <div class="form-group">
                                    <label>Nama Kelas</label>
                                    <input type="text" name="kelas" class="form-control" placeholder="Contoh: X TKJ 1" required>
                                </div>
                                <button type="submit" name="save" class="btn btn-primary">Simpan</button>
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <!-- End Modal Tambah -->

            <?php
            // Simpan data baru
            if (isset($_POST['save'])) {
                $kode = mysqli_real_escape_string($con, $_POST['kode']);
                $kelas = mysqli_real_escape_string($con, $_POST['kelas']);

                $cek = mysqli_query($con, "SELECT * FROM tb_mkelas WHERE kd_kelas='$kode' OR nama_kelas='$kelas'");
                if (mysqli_num_rows($cek) > 0) {
                    echo "<script>alert('Kode kelas atau nama kelas sudah ada!'); window.location='?page=master&act=kelas';</script>";
                } else {
                    $save = mysqli_query($con, "INSERT INTO tb_mkelas (kd_kelas, nama_kelas) VALUES('$kode', '$kelas')");
                    if ($save) {
                        echo "<script>alert('Data kelas berhasil ditambahkan!'); window.location='?page=master&act=kelas';</script>";
                    } else {
                        echo "<script>alert('Gagal menambahkan data kelas!'); window.location='?page=master&act=kelas';</script>";
                    }
                }
            }

            // Edit data
            if (isset($_POST['edit'])) {
                $id = intval($_POST['id']);
                $kode = mysqli_real_escape_string($con, $_POST['kode']);
                $kelas = mysqli_real_escape_string($con, $_POST['kelas']);

                $cek = mysqli_query($con, "SELECT * FROM tb_mkelas WHERE (kd_kelas='$kode' OR nama_kelas='$kelas') AND id_mkelas != '$id'");
                if (mysqli_num_rows($cek) > 0) {
                    echo "<script>alert('Kode kelas atau nama kelas sudah digunakan!'); window.location='?page=master&act=kelas';</script>";
                } else {
                    $update = mysqli_query($con, "UPDATE tb_mkelas SET kd_kelas='$kode', nama_kelas='$kelas' WHERE id_mkelas='$id'");
                    if ($update) {
                        echo "<script>alert('Data kelas berhasil diubah!'); window.location='?page=master&act=kelas';</script>";
                    } else {
                        echo "<script>alert('Gagal mengubah data kelas!'); window.location='?page=master&act=kelas';</script>";
                    }
                }
            }
            ?>
        </div>
    </div>
</div>
