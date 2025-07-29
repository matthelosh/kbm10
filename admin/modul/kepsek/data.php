<div class="page-inner">
    <div class="page-header">
        <h4 class="page-title">Kepala Sekolah</h4>
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
                <a href="#">Data Kepala Sekolah</a>
            </li>
            <li class="separator">
                <i class="flaticon-right-arrow"></i>
            </li>
            <li class="nav-item">
                <a href="#">Daftar Kepala Sekolah</a>
            </li>
        </ul>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="card-title">
                        <a href="?page=kepsek&act=add" class="btn btn-primary btn-sm text-white">
                            <i class="fa fa-plus"></i> Tambah
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="basic-datatables" class="display table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>NIP</th>
                                    <th>Nama Kepala Sekolah</th>
                                    <th>Email</th>
                                    <th>Status</th>
                                    <th>Foto</th>
                                    <th>Opsi</th>
                                </tr>
                            </thead>
                            <tfoot>
                                <tr>
                                    <th>#</th>
                                    <th>NIP</th>
                                    <th>Nama Kepala Sekolah</th>
                                    <th>Email</th>
                                    <th>Status</th>
                                    <th>Foto</th>
                                    <th>Opsi</th>
                                </tr>
                            </tfoot>
                            <tbody>
                                <?php
                                $no = 1;
                                $kepsek = mysqli_query($con, "SELECT * FROM tb_kepsek");
                                foreach ($kepsek as $k) { ?>
                                    <tr>
                                        <td><?= $no++; ?>.</td>
                                        <td><?= htmlspecialchars($k['nip']); ?></td>
                                        <td><?= htmlspecialchars($k['nama_kepsek']); ?></td>
                                        <td><?= htmlspecialchars($k['email']); ?></td>
                                        <td>
                                            <?php
                                            if ($k['status'] == 'Y') {
                                                echo "<span class='badge badge-success'>Aktif</span>";
                                            } else {
                                                echo "<span class='badge badge-danger'>Off</span>";
                                            }
                                            ?>
                                        </td>
                                        <td>
                                            <img src="/assets/img/user/<?= htmlspecialchars($k['foto']) ?>" width="45" height="45" class="rounded-circle">
                                        </td>
                                        <td>
                                            <a class="btn btn-info btn-sm" href="?page=kepsek&act=edit&id=<?= $k['id_kepsek'] ?>">
                                                <i class="far fa-edit"></i>
                                            </a>
                                            <a class="btn btn-danger btn-sm" onclick="return confirm('Yakin Hapus Data ??')" href="?page=kepsek&act=del&id=<?= $k['id_kepsek'] ?>">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                            <?php if ($k['status'] == 'Y') { ?>
                                                <a class="btn btn-warning btn-sm" href="modul/kepsek/proses.php?nonaktifkan&id=<?= $k['id_kepsek'] ?>" onclick="return confirm('Nonaktifkan Kepala Sekolah ini?')">
                                                    OFF
                                                </a>
                                            <?php } else { ?>
                                                <a class="btn btn-success btn-sm" href="modul/kepsek/proses.php?aktifkan&id=<?= $k['id_kepsek'] ?>" onclick="return confirm('Aktifkan Kepala Sekolah ini?')">
                                                    ON
                                                </a>
                                            <?php } ?>
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