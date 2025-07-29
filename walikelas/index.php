<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once '../config/db.php';

if (!isset($_SESSION['walikelas'])) {
    echo "<script>alert('Maaf! Anda belum login sebagai Wali Kelas!'); window.location='../user.php';</script>";
    exit;
}

$id_wali = $_SESSION['walikelas'];
$stmt = $con->prepare("
    SELECT g.*, w.id_mkelas, k.nama_kelas 
    FROM tb_walikelas w 
    JOIN tb_guru g ON w.id_guru = g.id_guru 
    JOIN tb_mkelas k ON w.id_mkelas = k.id_mkelas 
    WHERE w.id_walikelas = ?
");
$stmt->bind_param("s", $id_wali);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();

$id_mkelas = $data['id_mkelas'];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Wali Kelas | Aplikasi Presensi </title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!--===============================================================================================-->
    <link rel="icon" type="image/png" href="/assets/img/vsc.png" />
    <!--===============================================================================================-->
    <link rel="icon" href="/assets/img/icon.ico" type="image/x-icon"/>
    <link rel="stylesheet" href="/assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="/assets/css/atlantis.min.css">
    <link rel="stylesheet" href="/assets/css/fonts.min.css">
</head>
<body>
<div class="wrapper">
    <div class="main-header">
        <div class="logo-header" data-background-color="blue">
            <a href="index.php" class="logo text-white font-weight-bold"><?= htmlspecialchars($data['nama_kelas']) ?></a>
            <button class="navbar-toggler sidenav-toggler ml-auto" type="button">
                <span class="navbar-toggler-icon"><i class="icon-menu"></i></span>
            </button>
        </div>
        <nav class="navbar navbar-header navbar-expand-lg" data-background-color="blue2">
            <div class="container-fluid">
                <ul class="navbar-nav topbar-nav ml-md-auto align-items-center">
                    <li class="nav-item dropdown hidden-caret">
                        <a class="dropdown-toggle profile-pic" data-toggle="dropdown" href="#" aria-expanded="false">
                            <div class="avatar-sm">
                                <img src="/assets/img/user/<?= htmlspecialchars($data['foto']) ?>" class="avatar-img rounded-circle">
                            </div>
                        </a>
                        <ul class="dropdown-menu dropdown-user animated fadeIn">
                            <li>
                                <div class="user-box text-center">
                                    <img src="/assets/img/user/<?= htmlspecialchars($data['foto']) ?>" class="avatar-img rounded" width="60">
                                    <h5><?= htmlspecialchars($data['nama_guru']) ?></h5>
                                    <p class="text-muted"><?= htmlspecialchars($data['email']) ?></p>
                                </div>
                            </li>
                            <li>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item text-center" href="logout.php" onclick="return confirm('Yakin ingin logout?')">Logout</a>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
        </nav>
    </div>

    <div class="sidebar sidebar-style-2">
        <div class="sidebar-wrapper scrollbar scrollbar-inner">
            <div class="sidebar-content">
                <div class="user">
                    <div class="avatar-sm float-left mr-2">
                        <img src="/assets/img/user/<?= htmlspecialchars($data['foto']) ?>" class="avatar-img rounded-circle">
                    </div>
                    <div class="info">
                        <a data-toggle="collapse" href="#userMenu" aria-expanded="true">
                            <span>
                                <?= htmlspecialchars($data['nama_guru']) ?>
                                <span class="user-level"><?= htmlspecialchars($data['nip']) ?></span>
                                <span class="caret"></span>
                            </span>
                        </a>
                        <div class="clearfix"></div>
                        <div class="collapse in" id="userMenu">
                            <ul class="nav">
                                <li><a href="logout.php" onclick="return confirm('Yakin ingin logout?')"><span class="link-collapse">Logout</span></a></li>
                            </ul>
                        </div>
                    </div>
                </div>

                <ul class="nav nav-primary">
                    <li class="nav-item <?= ($_GET['page'] ?? '') == '' ? 'active' : '' ?>">
                        <a href="index.php"><i class="fas fa-home"></i><p>Dashboard</p></a>
                    </li>
                    <li class="nav-section"><h4 class="text-section">Menu Wali Kelas</h4></li>
                    <li class="nav-item <?= ($_GET['page'] ?? '') == 'daftar' ? 'active' : '' ?>">
                        <a href="?page=daftar&id_mkelas=<?= $id_mkelas ?>">
                            <i class="fas fa-users"></i><p>Daftar Nama</p>
                        </a>
                    </li>
                    <li class="nav-item <?= ($_GET['page'] ?? '') == 'rekap' ? 'active' : '' ?>">
                        <a href="?page=rekap&id_mkelas=<?= $id_mkelas ?>">
                            <i class="fas fa-list-alt"></i><p>Rekap Absensi</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="logout.php" onclick="return confirm('Yakin ingin logout?')">
                            <i class="fas fa-sign-out-alt"></i><p>Logout</p>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <div class="main-panel">
        <div class="content">
            <?php
            $page = $_GET['page'] ?? '';

            switch ($page) {
                case 'daftar':
                    include 'modul/rekap/daftar_siswa.php';
                    break;
                case 'edit':
                    include 'modul/rekap/edit.php';
                    break;
                case 'upload':
                    include 'modul/rekap/proses.php';
                    break;
                case 'delete':
                    include 'modul/rekap/del.php';
                    break;
                case 'rekap':
                    include 'modul/rekap/rekap_absen_wali.php';
                    break;
                case 'siswa':
                    $act = $_GET['act'] ?? '';
                    if ($act == 'proses') {
                        include 'modul/rekap/proses.php';
                    } else {
                        echo "<div class='alert alert-warning'>Sub-halaman siswa tidak ditemukan!</div>";
                    }
                    break;
                case '':
                    include 'modul/home.php';
                    break;
                default:
                    echo "<div class='alert alert-danger'>Halaman tidak ditemukan!</div>";
                    break;
            }
            ?>
        </div>

        <footer class="footer">
            <div class="container">
                <div class="copyright ml-auto">
                    &copy; <?= date('Y') ?> Vocsten Malang (<a href="#">Tim IT Kurikulum</a> | 2025)
                </div>
            </div>
        </footer>
    </div>
</div>

<!-- JS -->
<script src="/assets/js/core/jquery.3.2.1.min.js"></script>
<script src="/assets/js/core/popper.min.js"></script>
<script src="/assets/js/core/bootstrap.min.js"></script>
<script src="/assets/js/plugin/jquery-scrollbar/jquery.scrollbar.min.js"></script>
<script src="/assets/js/plugin/datatables/datatables.min.js"></script>
<script src="/assets/js/plugin/sweetalert/sweetalert.min.js"></script>
<script src="/assets/js/atlantis.min.js"></script>
<script>
    $(document).ready(function () {
        $('#basic-datatables').DataTable();
    });
</script>
</body>
</html>