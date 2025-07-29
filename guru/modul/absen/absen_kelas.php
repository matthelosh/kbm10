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

$id_mapel = $d['id_mapel'];
$id_mkelas = $d['id_mkelas'];
$id_pelajaran = $d['id_mengajar'];

$tahunAktif = $con->query("SELECT id_thajaran FROM tb_thajaran WHERE status = 1")->fetch_assoc();
if (!$tahunAktif) {
    echo "<div class='alert alert-danger'>❌ Tidak ada tahun ajaran aktif.</div>";
    exit;
}
$id_thajaran = $tahunAktif['id_thajaran'];

$stmtLast = $con->prepare("SELECT MAX(pertemuan_ke) AS terakhir FROM _logabsensi WHERE id_mengajar = ?");
$stmtLast->bind_param("s", $id_pelajaran);
$stmtLast->execute();
$lastData = $stmtLast->get_result()->fetch_assoc();
$pertemuan = ($lastData['terakhir']) ? $lastData['terakhir'] + 1 : 1;

if (isset($_POST['absen'])) {
    $total = (int)$_POST['total'];
    $tgl = $_POST['tgl'];
    $pertemuanPost = $_POST['pertemuan'];

    $stmtCek = $con->prepare("
        SELECT id_siswa FROM _logabsensi 
        WHERE id_mengajar = ? AND tgl_absen = ?
    ");
    $stmtCek->bind_param("ss", $id_pelajaran, $tgl);
    $stmtCek->execute();
    $resultCek = $stmtCek->get_result();
    $sudahAda = [];
    while ($row = $resultCek->fetch_assoc()) {
        $sudahAda[] = $row['id_siswa'];
    }

    $stmtInsert = $con->prepare("
        INSERT INTO _logabsensi 
        (id_presensi, id_mengajar, id_siswa, tgl_absen, ket, pertemuan_ke)
        VALUES (NULL, ?, ?, ?, ?, ?)
    ");
    $stmtUpdate = $con->prepare("
        UPDATE _logabsensi SET ket = ? 
        WHERE id_mengajar = ? AND id_siswa = ? AND tgl_absen = ?
    ");

    for ($i = 0; $i < $total; $i++) {
        $id_siswa = $_POST["id_siswa-$i"];
        $ket = $_POST["ket-$i"];

        if (in_array($id_siswa, $sudahAda)) {
            $stmtUpdate->bind_param("siss", $ket, $id_pelajaran, $id_siswa, $tgl);
            $stmtUpdate->execute();
        } else {
            $stmtInsert->bind_param("sissi", $id_pelajaran, $id_siswa, $tgl, $ket, $pertemuanPost);
            $stmtInsert->execute();
        }
    }

    $stmtInsert->close();
    $stmtUpdate->close();

    echo "<script>alert('✅ Absensi berhasil diproses');window.location='/guru/modul/jurnal/tambah_jurnal.php?id_mengajar=$pelajaran';</script>";
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
                <div class="mb-4">
                    <h4><span class="badge badge-info">Pertemuan Ke: <?= $pertemuan ?></span></h4>
                    <input type="hidden" name="pertemuan" value="<?= $pertemuan ?>">
                    <input type="date" name="tgl" class="form-control mt-2" value="<?= date('Y-m-d') ?>" required>
                </div>

                <?php
                $stmtSiswa = $con->prepare("SELECT * FROM tb_siswa WHERE id_mkelas = ? ORDER BY nama_siswa ASC");
                $stmtSiswa->bind_param("s", $id_mkelas);
                $stmtSiswa->execute();
                $resultSiswa = $stmtSiswa->get_result();
                $jumlahSiswa = $resultSiswa->num_rows;

                if ($jumlahSiswa < 1) {
                    echo "<div class='alert alert-warning'>⚠️ Tidak ada siswa ditemukan di kelas ini.</div>";
                } else {
                ?>
                <div class="table-responsive">
                    <table class="table table-bordered text-left">
                        <thead class="thead-light">
                            <tr>
                                <th style="width: 30%;">Nama Siswa</th>
                                <th style="width: 70%;">Kehadiran</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php
                        $i = 0;
                        $kehadiran = ['H' => 'Hadir', 'I' => 'Izin', 'S' => 'Sakit', 'A' => 'Alpha'];
                        while ($s = $resultSiswa->fetch_assoc()) {
                        ?>
                            <tr>
                                <td style="vertical-align: middle;"><?= htmlspecialchars($s['nama_siswa']) ?>
                                    <input type="hidden" name="id_siswa-<?= $i ?>" value="<?= $s['id_siswa'] ?>">
                                </td>
                                <td>
                                    <div class="d-flex justify-content-center" style="gap: 2rem;">
                                        <?php foreach ($kehadiran as $kode => $label) { ?>
                                            <label style="min-width: 90px; white-space: nowrap;">
                                                <input type="radio" name="ket-<?= $i ?>" value="<?= $kode ?>" required> <?= $label ?>
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
                <input type="hidden" name="pelajaran" value="<?= htmlspecialchars($pelajaran) ?>">

                <div class="text-center mt-4">
                    <button type="submit" name="absen" class="btn btn-success">
                        <i class="fas fa-save"></i> Simpan Absensi
                    </button>
                    <a href="index.php?page=absen&act=update&pelajaran=<?= $pelajaran ?>" class="btn btn-warning">
                        <i class="fas fa-edit"></i> Update Absensi
                    </a>
                    <button type="button" class="btn btn-primary" onclick="centangSemuaHadir()">
                        <a>Hadir Semua</a>
                    </button>
                </div>
                <?php } ?>
            </form>
        </div>
    </div>
</div>

<script>
function centangSemuaHadir() {
    const radios = document.querySelectorAll('input[type="radio"][value="H"]');
    radios.forEach(radio => {
        radio.checked = true;
    });
}
</script>