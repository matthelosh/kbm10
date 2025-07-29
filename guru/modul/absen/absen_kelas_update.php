<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../../config/db.php';

if (!isset($_SESSION['guru'])) {
    echo "<script>alert('Sesi login tidak ditemukan.');window.location='../user.php';</script>";
    exit;
}

$id_guru = $_SESSION['guru'];
$pelajaran = $_GET['pelajaran'] ?? '';

if (!$pelajaran) {
    echo "<div class='alert alert-danger'>❌ Parameter pelajaran tidak ditemukan.</div>";
    exit;
}

$stmt = $con->prepare("
    SELECT mg.*, k.nama_kelas, m.mapel 
    FROM tb_mengajar mg
    INNER JOIN tb_mkelas k ON mg.id_mkelas = k.id_mkelas
    INNER JOIN tb_master_mapel m ON mg.id_mapel = m.id_mapel
    WHERE mg.id_mengajar = ?
");
$stmt->bind_param("s", $pelajaran);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "<div class='alert alert-danger'>❌ Data mengajar tidak ditemukan.</div>";
    exit;
}

$d = $result->fetch_assoc();

if ($d['id_guru'] != $id_guru) {
    echo "<div class='alert alert-danger'>❌ Anda tidak berhak mengakses data ini.</div>";
    exit;
}

$id_pelajaran = $d['id_mengajar'];
$id_mkelas = $d['id_mkelas'];

if (isset($_POST['update'])) {
    $total = (int)$_POST['total'];
    $tgl = date('Y-m-d');

    $stmtUpdate = $con->prepare("
        UPDATE _logabsensi SET ket = ? 
        WHERE id_mengajar = ? AND id_siswa = ? AND tgl_absen = ?
    ");

    for ($i = 0; $i < $total; $i++) {
        $id_siswa = $_POST["id_siswa-$i"];
        $ket = $_POST["ket-$i"];
        $stmtUpdate->bind_param("ssis", $ket, $id_pelajaran, $id_siswa, $tgl);
        $stmtUpdate->execute();
    }

    $stmtUpdate->close();

    echo "<script>
        alert('✅ Absensi berhasil diperbarui');
        window.location='?page=absen&act=update&pelajaran=$pelajaran';
    </script>";
    exit;
}
?>

<div class="page-inner">
    <div class="page-header">
        <ul class="breadcrumbs">
            <li class="nav-home"><a href="#"><i class="flaticon-home"></i></a></li>
            <li class="separator"><i class="flaticon-right-arrow"></i></li>
            <li class="nav-item"><a href="#">KELAS (<?= strtoupper(htmlspecialchars($d['nama_kelas'])) ?>)</a></li>
            <li class="separator"><i class="flaticon-right-arrow"></i></li>
            <li class="nav-item"><a href="#"><?= strtoupper(htmlspecialchars($d['mapel'])) ?></a></li>
        </ul>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="post">
                <h4><span class="badge badge-warning">Update Absensi Hari Ini: <?= date('d-m-Y') ?></span></h4>

                <?php
                $tgl_hari_ini = date('Y-m-d');
                $stmtSiswa = $con->prepare("
                    SELECT a.ket, s.id_siswa, s.nama_siswa 
                    FROM _logabsensi a 
                    INNER JOIN tb_siswa s ON a.id_siswa = s.id_siswa 
                    WHERE a.id_mengajar = ? AND a.tgl_absen = ? 
                    ORDER BY s.nama_siswa ASC
                ");
                $stmtSiswa->bind_param("ss", $id_pelajaran, $tgl_hari_ini);
                $stmtSiswa->execute();
                $resultSiswa = $stmtSiswa->get_result();

                $jumlahSiswa = $resultSiswa->num_rows;
                if ($jumlahSiswa < 1) {
                    echo "<div class='alert alert-warning'>⚠️ Tidak ada data absensi yang bisa diperbarui untuk hari ini.</div>";
                } else {
                ?>

                <div class="table-responsive mt-3">
                    <table class="table table-bordered text-center">
                        <thead class="thead-light">
                            <tr>
                                <th style="width: 30%;">Nama Siswa</th>
                                <th style="width: 70%;">Kehadiran</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $i = 0;
                            $kehadiran = ['H' => 'Hadir', 'I' => 'Izin', 'S' => 'Sakit', 'A' => 'Alpha', 'C' => 'Cuti'];
                            while ($s = $resultSiswa->fetch_assoc()) {
                            ?>
                            <tr>
                                <td style="vertical-align: middle;"><?= htmlspecialchars($s['nama_siswa']) ?>
                                    <input type="hidden" name="id_siswa-<?= $i ?>" value="<?= $s['id_siswa'] ?>">
                                </td>
                                <td>
                                    <div class="d-flex justify-content-center" style="gap: 1rem;">
                                        <?php foreach ($kehadiran as $kode => $label) { ?>
                                            <label style="min-width: 80px;">
                                                <input type="radio" name="ket-<?= $i ?>" value="<?= $kode ?>" <?= ($s['ket'] == $kode) ? 'checked' : '' ?> required>
                                                <?= $label ?>
                                            </label>
                                        <?php } ?>
                                    </div>
                                </td>
                            </tr>
                            <?php
                                $i++;
                            } ?>
                        </tbody>
                    </table>
                </div>

                <input type="hidden" name="total" value="<?= $i ?>">

                <div class="text-center mt-4">
                    <button type="submit" name="update" class="btn btn-success">
                        <i class="fas fa-check"></i> Perbarui Absensi
                    </button>
                    <a href="index.php?page=absen&pelajaran=<?= $pelajaran ?>" class="btn btn-default">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                </div>
                <?php } ?>
            </form>
        </div>
    </div>
</div>