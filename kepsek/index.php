<?php
session_start();
include '../config/db.php';

// Pastikan pengguna sudah login sebagai kepala sekolah
if (!isset($_SESSION['kepsek'])) {
    echo "<script>
        alert('Maaf! Anda belum login!');
        window.location='../index.php';
    </script>";
    exit;
}

// Ambil data kepala sekolah dari session dan database
$id_login = $_SESSION['kepsek'];
$sql = mysqli_query($con, "SELECT * FROM tb_kepsek WHERE id_kepsek = '$id_login'") or die(mysqli_error($con));
$data = mysqli_fetch_array($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <title>Kepala Sekolah | Aplikasi KBM</title>
    <meta content='width=device-width, initial-scale=1.0, shrink-to-fit=no' name='viewport' />
    <link rel="icon" href="/assets/img/icon.ico" type="image/x-icon"/>
    <!--===============================================================================================-->
    <link rel="icon" type="image/png" href="/assets/img/vsc.png" />
    <!--===============================================================================================-->
    <!-- Fonts and icons -->
    <script src="/assets/js/plugin/webfont/webfont.min.js"></script>
    <script>
        WebFont.load({
            google: {"families":["Lato:300,400,700,900"]},
            custom: {"families":["Flaticon", "Font Awesome 5 Solid", "Font Awesome 5 Regular", "Font Awesome 5 Brands", "simple-line-icons"], urls: ['/assets/css/fonts.min.css']},
            active: function() {
                sessionStorage.fonts = true;
            }
        });
    </script>

    <!-- CSS Files -->
    <link rel="stylesheet" href="/assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="/assets/css/atlantis.min.css">
    <link rel="stylesheet" href="/assets/css/demo.css">
</head>
<body>
<div class="wrapper">
    <div class="main-header">
        <!-- Logo Header -->
        <div class="logo-header" data-background-color="blue">
            <a href="index.php" class="logo">
                <b class="text-white">Presensi Siswa</b>
            </a>
            <button class="navbar-toggler sidenav-toggler ml-auto">
                <span class="navbar-toggler-icon"><i class="icon-menu"></i></span>
            </button>
            <button class="topbar-toggler more"><i class="icon-options-vertical"></i></button>
            <div class="nav-toggle">
                <button class="btn btn-toggle toggle-sidebar">
                    <i class="icon-menu"></i>
                </button>
            </div>
        </div>

        <!-- Navbar Header -->
        <nav class="navbar navbar-header navbar-expand-lg" data-background-color="blue2">
            <div class="container-fluid">
                <ul class="navbar-nav topbar-nav ml-md-auto align-items-center">
                    <li class="nav-item dropdown hidden-caret">
                        <a class="dropdown-toggle profile-pic" data-toggle="dropdown" href="#" aria-expanded="false">
                            <div class="avatar-sm">
                                <img src="/assets/img/user/<?= $data['foto'] ?>" alt="..." class="avatar-img rounded-circle">
                            </div>
                        </a>
                        <ul class="dropdown-menu dropdown-user animated fadeIn">
                            <div class="dropdown-user-scroll scrollbar-outer">
                                <li>
                                    <div class="user-box">
                                        <div class="avatar-lg">
                                            <img src="/assets/img/user/<?= $data['foto'] ?>" alt="image profile" class="avatar-img rounded">
                                        </div>
                                        <div class="u-text">
                                            <h4><?= $data['nama_kepsek'] ?></h4>
                                            <p class="text-muted"><?= $data['email'] ?></p>
                                        </div>
                                    </div>
                                </li>
                                <li>
                                    <div class="dropdown-divider"></div>
                                    <a class="dropdown-item" href="?page=akun">Akun Saya</a>
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

    <!-- Sidebar -->
    <div class="sidebar sidebar-style-2">
        <div class="sidebar-wrapper scrollbar scrollbar-inner">
            <div class="sidebar-content">
                <div class="user">
                    <div class="avatar-sm float-left mr-2">
                        <img src="/assets/img/user/<?= $data['foto'] ?>" alt="..." class="avatar-img rounded-circle">
                    </div>
                    <div class="info">
                        <a data-toggle="collapse" href="#collapseExample" aria-expanded="true">
                            <span>
                                <?= $data['nama_kepsek'] ?>
                                <span class="user-level"><?= $data['nip'] ?></span>
                                <span class="caret"></span>
                            </span>
                        </a>
                        <div class="clearfix"></div>
                        <div class="collapse in" id="collapseExample">
                            <ul class="nav">
                                <li><a href="?page=akun"><span class="link-collapse">Akun Saya</span></a></li>
                            </ul>
                        </div>
                    </div>
                </div>
                <ul class="nav nav-primary">
                    <li class="nav-item active">
                        <a href="index.php">
                            <i class="fas fa-home"></i>
                            <p>Dashboard</p>
                        </a>
                    </li>
                    <li class="nav-section">
                        <span class="sidebar-mini-icon"><i class="fa fa-ellipsis-h"></i></span>
                        <h4 class="text-section">Main Utama</h4>
                    </li>
                    <li class="nav-item">
                        <a data-toggle="collapse" href="#rekapAbsen">
                            <i class="fas fa-list-alt"></i>
                            <p>Rekap Absen</p>
                            <span class="caret"></span>
                        </a>
                        <div class="collapse" id="rekapAbsen">
                            <ul class="nav nav-collapse">
                                <?php
                                $kelas = mysqli_query($con, "SELECT * FROM tb_mkelas ORDER BY id_mkelas ASC");
                                foreach ($kelas as $k) { ?>
                                    <li><a href="?page=rekap&kelas=<?= $k['id_mkelas'] ?>"><span class="sub-item">KELAS <?= strtoupper($k['nama_kelas']) ?></span></a></li>
                                <?php } ?>
                            </ul>
                        </div>
                    </li>
                    <li class="nav-item">
                        <a href="?page=rekap_jurnal">
                            <i class="fas fa-book"></i>
                            <p>Rekap Jurnal</p>
                        </a>
                    </li>
                    <li class="nav-item mt-3">
                        <a href="logout.php">
                            <i class="fas fa-arrow-alt-circle-left"></i>
                            <p>Logout</p>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Main Panel -->
    <div class="main-panel">
        <div class="content">
            <?php
            $page = $_GET['page'] ?? '';
            $act = $_GET['act'] ?? '';

            if ($page == 'absen') {
                if ($act == '') {
                    include 'modul/absen/absen_kelas.php';
                } elseif ($act == 'update') {
                    include 'modul/absen/absen_kelas_update.php';
                }
            } elseif ($page == 'akun') {
                include 'modul/akun/akun.php';
            } elseif ($page == 'rekap') {
                if ($act == '') {
                    include 'modul/rekap/rekap_absen.php';
                } elseif ($act == 'rekap-perbulan') {
                    include 'modul/rekap/rekap_perbulan.php';
                }
            } elseif ($page == 'rekap_jurnal') {
                include 'modul/rekap_jurnal/rekap_jurnal.php';
            } elseif ($page == '') {
                include 'modul/home.php';
            } else {
                echo "<b>Halaman tidak ditemukan.</b>";
            }
            ?>
        </div>

        <footer class="footer">
            <div class="container">
                <div class="copyright ml-auto">
                    &copy; <?= date('Y'); ?> Vocsten Malang (Tim IT Kurikulum)
                </div>
            </div>
        </footer>
    </div>
</div>

<!-- JS Files -->
<script src="/assets/js/core/jquery.3.2.1.min.js"></script>
<script src="/assets/js/core/popper.min.js"></script>
<script src="/assets/js/core/bootstrap.min.js"></script>
<script src="/assets/js/plugin/jquery-scrollbar/jquery.scrollbar.min.js"></script>
<script src="/assets/js/plugin/datatables/datatables.min.js"></script>
<script src="/assets/js/plugin/sweetalert/sweetalert.min.js"></script>
<script src="/assets/js/atlantis.min.js"></script>
</body>
</html>