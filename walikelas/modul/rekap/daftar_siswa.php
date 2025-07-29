<?php
if (session_status() === PHP_SESSION_NONE) session_start();

// Pastikan $id_mkelas sudah tersedia dari halaman utama (sudah ada di file index.php wali kelas)
require_once __DIR__ . '/../../../config/db.php';

if (!isset($_SESSION['walikelas'])) {
    echo "<script>alert('Anda belum login sebagai Wali Kelas!'); window.location='../../../user.php';</script>";
    exit;
}

$id_wali = $_SESSION['walikelas'];

// Ambil id_mkelas sesuai wali kelas
$stmt = $con->prepare("
    SELECT w.id_mkelas, k.nama_kelas 
    FROM tb_walikelas w 
    JOIN tb_mkelas k ON w.id_mkelas = k.id_mkelas 
    WHERE w.id_walikelas = ?
");
$stmt->bind_param("s", $id_wali);
$stmt->execute();
$data_kelas = $stmt->get_result()->fetch_assoc();
$id_mkelas = $data_kelas['id_mkelas'];
?>

<div class="page-inner">
    <div class="page-header">
        <h4 class="page-title">Daftar Siswa</h4>
    </div>
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header d-flex align-items-center">
                    <h3 class="h4">Kelas: <?= htmlspecialchars($data_kelas['nama_kelas']) ?></h3>
                </div>
                <div class="card-body">
                    <table id="basic-datatables" class="display table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>NIS/NISN</th>
                                <th>Nama Siswa</th>
                                <th>Kelas</th>
                                <th>Tahun Masuk</th>
                                <th>Ketua Kelas</th>
                                <th>Status</th>
                                <th>Foto</th>
                                <th>Opsi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $no = 1;
                            $query = $con->prepare("
                                SELECT s.*, k.nama_kelas 
                                FROM tb_siswa s
                                JOIN tb_mkelas k ON s.id_mkelas = k.id_mkelas
                                WHERE s.id_mkelas = ?
                                ORDER BY s.id_siswa ASC
                            ");
                            $query->bind_param("s", $id_mkelas);
                            $query->execute();
                            $result = $query->get_result();

                            while ($g = $result->fetch_assoc()) {
                            ?>
                                <tr>
                                    <td><?= $no++; ?>.</td>
                                    <td><?= htmlspecialchars($g['nis']); ?></td>
                                    <td><?= htmlspecialchars($g['nama_siswa']); ?></td>
                                    <td><?= htmlspecialchars($g['nama_kelas']); ?></td>
                                    <td><?= htmlspecialchars($g['th_angkatan']); ?></td>
                                    <td>
                                        <?php if ($g['is_ketua'] == 1) {
                                            echo "<span class='badge badge-info'>Ketua</span>";
                                        } else {
                                            echo "<span class='badge badge-warning'>Bukan</span>";
                                        } ?>
                                    </td>
                                    <td>
                                        <?php if ($g['status'] == 1) {
                                            echo "<span class='badge badge-success'>Aktif</span>";
                                        } else {
                                            echo "<span class='badge badge-danger'>Off</span>";
                                        } ?>
                                    </td>
                                    <td><img src="/assets/img/user/<?=$g['foto'] ?>" width="45" height="45"></td>
                                    <td>
                                        <!-- <a class="btn btn-danger btn-sm" onclick="return confirm('Yakin Hapus Data?')" href="modul/rekap/del.php?id=<?= $g['id_siswa'] ?>"><i class="fas fa-trash"></i></a> -->
                                        <a class="btn btn-info btn-sm" href="index.php?page=edit&id=<?= $g['id_siswa'] ?>"><i class="far fa-edit"></i></a>
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