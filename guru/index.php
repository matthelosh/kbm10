<?php

if (session_status() === PHP_SESSION_NONE) session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/config/db.php';

if (!isset($_SESSION['guru'])) {
    echo "<script>alert('Maaf! Anda belum login!'); window.location='../user.php';</script>";
    exit;
}

$id_login = $_SESSION['guru'];
$stmt = $con->prepare("SELECT * FROM tb_guru WHERE id_guru = ?");
$stmt->bind_param("s", $id_login);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();

$stmt2 = $con->prepare("
    SELECT 
        MIN(m.id_mengajar) AS id_mengajar, MIN(m.id_mkelas) AS id_mkelas, MIN(m.id_mapel) AS id_mapel, MIN(k.nama_kelas) AS nama_kelas, MIN(mp.mapel) AS nama_mapel
    FROM tb_mengajar m
    INNER JOIN tb_mkelas k ON m.id_mkelas = k.id_mkelas
    INNER JOIN tb_master_mapel mp ON m.id_mapel = mp.id_mapel
    INNER JOIN tb_thajaran t ON m.id_thajaran = t.id_thajaran
    WHERE m.id_guru = ? AND t.status = 1
    GROUP BY m.id_mkelas, m.id_mapel
");
$stmt2->bind_param("s", $id_login);
$stmt2->execute();
$kelas_guru = $stmt2->get_result()->fetch_all(MYSQLI_ASSOC);

if (isset($_GET['act'] ) && $_GET['act'] === 'hapus') {
    $id_mengajar = $_GET['id_mengajar'];   
    // Hapus logabsensi dulu
    $stmt_log_absensi = $con->prepare("DELETE FROM _logabsensi WHERE id_mengajar = ?");
    $stmt_log_absensi->bind_param("s", $id_mengajar);
    $stmt_log_absensi->execute();
    $stmt_log_absensi->close();
    // Hapus mengajar

    $stmt = $con->prepare("DELETE FROM tb_mengajar WHERE id_mengajar = ?");
    $stmt->bind_param("s", $id_mengajar);
    $stmt->execute();
    $stmt->close();
    header("Location: ?page=jadwal");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Guru | KBM</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!--===============================================================================================-->
    <link rel="icon" type="image/png" href="/assets/img/vsc.png" />
    <!--===============================================================================================-->
    <link rel="icon" href="/assets/img/icon.ico" type="image/x-icon"/>
    <link rel="stylesheet" href="/assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="/assets/css/atlantis.min.css">
    <link rel="stylesheet" href="/assets/css/fonts.min.css">
    <script src="/assets/js/plugin/webfont/webfont.min.js"></script>
    <script>
        WebFont.load({
            google: { families: ["Lato:300,400,700,900"] },
            custom: {
                families: ["Flaticon", "Font Awesome 5 Solid", "Font Awesome 5 Regular", "Font Awesome 5 Brands", "simple-line-icons"],
                urls: ['/assets/css/fonts.min.css']
            },
            active: function () { sessionStorage.fonts = true; }
        });
    </script>
</head>
<body>
<div class="wrapper">

    <div class="main-header">
        <div class="logo-header" data-background-color="blue">
            <a href="index.php" class="logo text-white font-weight-bold">Presensi Siswa</a>
            <button class="navbar-toggler sidenav-toggler ml-auto" type="button">
                <span class="navbar-toggler-icon"><i class="icon-menu"></i></span>
            </button>
            <button class="topbar-toggler more"><i class="icon-options-vertical"></i></button>
            <div class="nav-toggle">
                <button class="btn btn-toggle toggle-sidebar">
                    <i class="icon-menu"></i>
                </button>
            </div>
        </div>

        <nav class="navbar navbar-header navbar-expand-lg" data-background-color="blue2">
                <div class="container-fluid">
                    <ul class="navbar-nav topbar-nav ml-md-auto align-items-center">
                        <li class="nav-item dropdown hidden-caret">
                            <a class="dropdown-toggle profile-pic" data-toggle="dropdown" href="#" aria-expanded="false">
                                <div class="avatar-sm">
                                    <img src="/assets/img/user/<?=$data['foto'] ?>" alt="..." class="avatar-img rounded-circle">
                                </div>
                            </a>
                            <ul class="dropdown-menu dropdown-user animated fadeIn">
                                <div class="dropdown-user-scroll scrollbar-outer">
                                    <li>
                                        <div class="user-box">
                                            <div class="avatar-lg"><img src="/assets/img/user/<?=$data['foto'] ?>" alt="image profile" class="avatar-img rounded"></div>
                                            <div class="u-text">
                                                <h4><?=$data['nama_guru'] ?></h4>
                                                <p class="text-muted"><?=$data['nip'] ?></p>
                                            </div>
                                        </div>
                                    </li>
                                    <li>
                                        <div class="dropdown-divider"></div>
                                        <a class="dropdown-item" href="?page=akun">Ganti Password</a>
                                        <div class="dropdown-divider"></div>
                                        <a class="dropdown-item" href="logout.php">Logout</a>
                                    </li>
                                </div>
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
                                <li><a href="?page=akun"><span class="link-collapse">Akun Saya</span></a></li>
                                <li><a href="logout.php" onclick="return confirm('Yakin ingin logout?')"><span class="link-collapse">Logout</span></a></li>
                            </ul>
                        </div>
                    </div>
                </div>

                <ul class="nav nav-primary">
                    <li class="nav-item <?= ($_GET['page'] ?? '') == '' ? 'active' : '' ?>">
                        <a href="index.php"><i class="fas fa-home"></i><p>Dashboard</p></a>
                    </li>
                    <li class="nav-section"><h4 class="text-section">Menu Utama</h4></li>
                    <li class="nav-item <?= ($_GET['page'] ?? '') == 'jadwal' ? 'active' : '' ?>">
                        <a href="?page=jadwal"><i class="fas fa-calendar-alt"></i><p>Jadwal Mengajar</p></a>
                    </li>
                    <!-- <li class="nav-item <?= ($_GET['page'] ?? '') == 'jurnal' ? 'active' : '' ?>">
                        <a href="?page=jurnal"><i class="fas fa-book"></i><p>Jurnal Harian</p></a>
                    </li> -->
                    <li class="nav-item <?= ($_GET['page'] ?? '') == 'rekap_jurnal' ? 'active' : '' ?>">
                        <a href="?page=rekap_jurnal"><i class="fas fa-list-alt"></i><p>Rekap Jurnal</p></a>
                    </li>
                    <li class="nav-item">
                        <a data-toggle="collapse" href="#presensiMenu">
                            <i class="fas fa-clipboard-list"></i><p>Presensi</p><span class="caret"></span>
                        </a>
                        <div class="collapse" id="presensiMenu">
                            <ul class="nav nav-collapse">
                                <?php foreach ($kelas_guru as $dm): ?>
                                    <li>
                                        <a href="?page=absen&pelajaran=<?= $dm['id_mengajar'] ?>">
                                            Kelas <?= strtoupper($dm['nama_kelas']) ?> - <?= $dm['nama_mapel'] ?>
                                        </a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </li>
                    <li class="nav-item">
                        <a data-toggle="collapse" href="#rekapMenu">
                            <i class="fas fa-list"></i><p>Rekap Absen</p><span class="caret"></span>
                        </a>
                        <div class="collapse" id="rekapMenu">
                            <ul class="nav nav-collapse">
                                <?php foreach ($kelas_guru as $dm): ?>
                                    <li>
                                        <a href="?page=rekap&pelajaran=<?= $dm['id_mengajar'] ?>">
                                            Kelas <?= strtoupper($dm['nama_kelas']) ?> - <?= $dm['nama_mapel'] ?>
                                        </a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
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
            $act = $_GET['act'] ?? '';
            switch ($page) {
                case 'absen':
                    $act === 'update' ? include 'modul/absen/absen_kelas_update.php' : include 'modul/absen/absen_kelas.php';
                    break;
                case 'rekap':
                    include 'modul/rekap/rekap_absen.php';
                    break;
                case 'jadwal':
                    include 'modul/jadwal/jadwal_megajar.php';
                    break;
                case 'tambah_jadwal':
                    include 'modul/jadwal/tambah.php'; // pastikan nama file ini sesuai
                    break;
                case 'akun':
                    include 'modul/akun/akun.php';
                    break;
                case 'jurnal':
                    include 'modul/jurnal/jurnal_harian.php';
                    break;
                case 'rekap_jurnal':
                    include 'modul/rekap_jurnal/rekap_jurnal.php';
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
                        &copy; <?php echo date('Y');?> Vocsten Malang (<a href="index.php">Tim IT Kurikulum </a> | 2025)
                    </div>              
                </div>
            </footer>
    </div>
</div>

    <!--   Core JS Files   -->
    <script src="/assets/js/core/jquery.3.2.1.min.js"></script>
    <script src="/assets/js/core/popper.min.js"></script>
    <script src="/assets/js/core/bootstrap.min.js"></script>

    <!-- jQuery UI -->
    <script src="/assets/js/plugin/jquery-ui-1.12.1.custom/jquery-ui.min.js"></script>
    <script src="/assets/js/plugin/jquery-ui-touch-punch/jquery.ui.touch-punch.min.js"></script>

    <!-- jQuery Scrollbar -->
    <script src="/assets/js/plugin/jquery-scrollbar/jquery.scrollbar.min.js"></script>

    <!-- Datatables -->
    <script src="/assets/js/plugin/datatables/datatables.min.js"></script>

    <!-- Sweet Alert -->
    <script src="/assets/js/plugin/sweetalert/sweetalert.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Atlantis JS -->
    <script src="/assets/js/atlantis.min.js"></script>

    <!-- Atlantis DEMO methods, don't include it in your project! -->
    <script src="/assets/js/setting-demo.js"></script>
</body>
</html>