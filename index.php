<?php
session_start();
include 'config/db.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <title>Login | KBM</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!--===============================================================================================-->
    <link rel="icon" type="image/png" href="../assets/img/vsc.png" />
    <!--===============================================================================================-->
    <link rel="stylesheet" type="text/css" href="assets/_login/vendor/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="assets/_login/fonts/font-awesome-4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" type="text/css" href="assets/_login/fonts/iconic/css/material-design-iconic-font.min.css">
    <link rel="stylesheet" type="text/css" href="assets/_login/vendor/animate/animate.css">
    <link rel="stylesheet" type="text/css" href="assets/_login/vendor/css-hamburgers/hamburgers.min.css">
    <link rel="stylesheet" type="text/css" href="assets/_login/vendor/animsition/css/animsition.min.css">
    <link rel="stylesheet" type="text/css" href="assets/_login/vendor/select2/select2.min.css">
    <link rel="stylesheet" type="text/css" href="assets/_login/vendor/daterangepicker/daterangepicker.css">
    <link rel="stylesheet" type="text/css" href="assets/_login/css/util.css">
    <link rel="stylesheet" type="text/css" href="assets/_login/css/main.css">
    <!--===============================================================================================-->
</head>

<body>

    <div class="limiter">
        <div class="container-login100" style="height: 100vh; overflow: hidden; background-image: url('./assets/img/bgdarkk.jpg'); background-size: cover;">
            <div class="wrap-login100">
                <form method="post" action="" class="login100-form validate-form">
                    <span class="login100-form-title p-b-48">
                        <img src="./assets/img/image.png" width="100" alt="Logo">
                    </span>
                    <span class="login100-form-title p-b-26">
                        KBM<br>
                       SMKN 10 Malang
                    </span>

                    <div class="wrap-input100 validate-input" data-validate="Username is required">
                        <input class="input100" type="text" name="username" placeholder="Username" required>
                        <span class="focus-input100"></span>
                    </div>

                    <div class="wrap-input100 validate-input" data-validate="Password is required">
                        <span class="btn-show-pass">
                            <i class="zmdi zmdi-eye"></i>
                        </span>
                        <input class="input100" type="password" name="password" placeholder="Password" required>
                        <span class="focus-input100"></span>
                    </div>

                    <div class="form-group mb-3">
                        <select class="form-control" name="level" required>
                            <option value="" disabled selected>Level</option>
                            <option value="1">Guru</option>
                            <option value="2">Siswa</option>
                            <option value="3">Kepala Sekolah</option>
                            <option value="4">Wali Kelas</option>
                        </select>
                    </div>
                    <br>

                    <div class="container-login100-form-btn">
                        <div class="wrap-login100-form-btn">
                            <div class="login100-form-bgbtn"></div>
                            <button type="submit" class="login100-form-btn">
                                Login
                            </button>
                        </div>
                    </div>

                    <div class="container-login100-form-btn" style="margin-top: 20px; text-align: center;">
                        by <a href="#" target="_blank" style="margin-left: 5px;">Tim IT Kurikulum</a>
                    </div>
                </form>

                <?php
                if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                    $level = $_POST['level'];
                    $username = $_POST['username'];
                    $password = $_POST['password'];

                    if ($level == '1') {
                        // Guru login: NIP = Username & Password
                        $sql = mysqli_query($con, "SELECT * FROM tb_guru WHERE nip='$username' AND status='Y'");
                        if (mysqli_num_rows($sql) == 1) {
                            $d = mysqli_fetch_assoc($sql);
                            if (password_verify($password, $d['password'])) {
                                
                                $_SESSION['guru'] = $d['id_guru'];
                                $_SESSION['username'] = $d['nip'];
                                echo "
                                <script type='text/javascript'>
                                setTimeout(function () {
                                    swal('{$d['nama_guru']}', 'Login berhasil sebagai Guru', {
                                        icon : 'success',
                                        buttons: { confirm: { className : 'btn btn-success' } },
                                    });
                                }, 10);
                                window.setTimeout(function(){
                                    window.location.replace('./guru/');
                                }, 3000);
                                </script>";
                            } else {
                                echo "
                                <script type='text/javascript'>
                                setTimeout(function () {
                                    swal('Sorry!', 'Password Salah', {
                                        icon : 'error',
                                        buttons: { confirm: { className : 'btn btn-danger' } },
                                    });
                                }, 10);
                                </script>";
                            }
                        } else {
                            echo "
                            <script type='text/javascript'>
                            setTimeout(function () {
                                swal('Sorry!', 'Username / Password Salah', {
                                    icon : 'error',
                                    buttons: { confirm: { className : 'btn btn-danger' } },
                                });
                            }, 10);
                            </script>";
                        }
                    } elseif ($level == '4') {
                        // Wali Kelas login: NIP = Username & Password
                        $sql = mysqli_query($con, "SELECT g.*, w.id_walikelas FROM tb_guru g JOIN tb_walikelas w ON g.id_guru = w.id_guru WHERE g.nip='$username' AND g.status='Y'");
                        if (mysqli_num_rows($sql) == 1) {
                            $d = mysqli_fetch_assoc($sql);
                            if (password_verify($password, $d['password'])) {
                            $_SESSION['walikelas'] = $d['id_walikelas'];
                            $_SESSION['guru'] = $d['id_guru'];
                            $_SESSION['username'] = $d['nip'];
                            echo "
                            <script type='text/javascript'>
                            setTimeout(function () {
                                swal('{$d['nama_guru']}', 'Login berhasil sebagai Wali Kelas', {
                                    icon : 'success',
                                    buttons: { confirm: { className : 'btn btn-success' } },
                                });
                            }, 10);
                            window.setTimeout(function(){
                                window.location.replace('./walikelas/');
                            }, 3000);
                            </script>";
                            } else {
                                echo "
                                <script type='text/javascript'>
                                setTimeout(function () {
                                    swal('Sorry!', 'Password Salah', {
                                        icon : 'error',
                                        buttons: { confirm: { className : 'btn btn-danger' } },
                                    });
                                }, 10);
                                </script>";
                            }
                        } else {
                            echo "
                            <script type='text/javascript'>
                            setTimeout(function () {
                                swal('Sorry!', 'Username / Password Salah atau bukan Wali Kelas', {
                                    icon : 'error',
                                    buttons: { confirm: { className : 'btn btn-danger' } },
                                });
                            }, 10);
                            </script>";
                        }
                    } elseif ($level == '2') {
                        // Siswa login: NIS = Username & Password
                        $sql = mysqli_query($con, "SELECT * FROM tb_siswa WHERE nis='$username' AND status='1'");
                        if (mysqli_num_rows($sql) > 0) {
                            $d = mysqli_fetch_assoc($sql);
                            if (password_verify($password, $d['password'])) {
                            $_SESSION['siswa'] = $d['id_siswa'];
                            $_SESSION['username'] = $d['nis'];
                            echo "
                            <script type='text/javascript'>
                            setTimeout(function () {
                                swal('{$d['nama_siswa']}', 'Login berhasil sebagai Siswa', {
                                    icon : 'success',
                                    buttons: { confirm: { className : 'btn btn-success' } },
                                });
                            }, 10);
                            window.setTimeout(function(){
                                window.location.replace('./siswa/');
                            }, 3000);
                            </script>";
                            } else {
                                echo "
                                <script type='text/javascript'>
                                setTimeout(function () {
                                    swal('Sorry!', 'Password Salah', {
                                        icon : 'error',
                                        buttons: { confirm: { className : 'btn btn-danger' } },
                                    });
                                }, 10);
                                </script>";
                            }
                        } else {
                            echo "
                            <script type='text/javascript'>
                            setTimeout(function () {
                                swal('Sorry!', 'Username / Password Salah', {
                                    icon : 'error',
                                    buttons: { confirm: { className : 'btn btn-danger' } },
                                });
                            }, 10);
                            </script>";
                        }
                    } elseif ($level == '3') {
                        // Kepala Sekolah login
                        $passHash = sha1($password);
                        $sql = mysqli_query($con, "SELECT * FROM tb_kepsek WHERE nip='$username' AND password='$passHash' AND status='Y'");
                        if (mysqli_num_rows($sql) > 0) {
                            $d = mysqli_fetch_assoc($sql);
                            $_SESSION['kepsek'] = $d['id_kepsek'];
                            $_SESSION['username'] = $d['nip'];
                            echo "
                            <script type='text/javascript'>
                            setTimeout(function () {
                                swal('{$d['nama_kepsek']}', 'Login berhasil sebagai Kepala Sekolah', {
                                    icon : 'success',
                                    buttons: { confirm: { className : 'btn btn-success' } },
                                });
                            }, 10);
                            window.setTimeout(function(){
                                window.location.replace('./kepsek/');
                            }, 3000);
                            </script>";
                        } else {
                            echo "
                            <script type='text/javascript'>
                            setTimeout(function () {
                                swal('Sorry!', 'Username / Password Salah', {
                                    icon : 'error',
                                    buttons: { confirm: { className : 'btn btn-danger' } },
                                });
                            }, 10);
                            </script>";
                        }
                    } else {
                        echo "<div style='text-align:center; margin-top:20px; color:red;'>Level tidak valid atau belum dipilih</div>";
                    }
                }
                ?>
            </div>
        </div>
    </div>

    <div id="dropDownSelect1"></div>

    <!--===============================================================================================-->
    <script src="assets/_login/vendor/jquery/jquery-3.2.1.min.js"></script>
    <script src="assets/_login/vendor/animsition/js/animsition.min.js"></script>
    <script src="assets/_login/vendor/bootstrap/js/popper.js"></script>
    <script src="assets/_login/vendor/bootstrap/js/bootstrap.min.js"></script>
    <script src="assets/_login/vendor/select2/select2.min.js"></script>
    <script src="assets/_login/vendor/daterangepicker/moment.min.js"></script>
    <script src="assets/_login/vendor/daterangepicker/daterangepicker.js"></script>
    <script src="assets/_login/vendor/countdowntime/countdowntime.js"></script>
    <script src="assets/js/plugin/sweetalert/sweetalert.min.js"></script>
    <script src="assets/_login/js/main.js"></script>

</body>

</html>
