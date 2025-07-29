<?php
session_start();
include '../../../config/db.php';

if (!isset($_SESSION['siswa'])) {
    echo "<script>window.location='../../../login.php';</script>";
    exit;
}

$id_siswa = $_SESSION['siswa'];
$notif = null;

// Proses saat form disubmit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pass_lama = $_POST['pass1'];
    $pass_baru = $_POST['pass2'];
    $konfirmasi = $_POST['pass3'];

    $q = mysqli_query($con, "SELECT password FROM tb_siswa WHERE id_siswa='$id_siswa'");
    $data_pass = mysqli_fetch_assoc($q);

    if ($data_pass['password'] !== $pass_lama) {
        $notif = ['type' => 'error', 'msg' => 'Password lama tidak sesuai'];
    } elseif ($pass_baru !== $konfirmasi) {
        $notif = ['type' => 'error', 'msg' => 'Konfirmasi password tidak cocok'];
    } else {
        mysqli_query($con, "UPDATE tb_siswa SET password='$pass_baru' WHERE id_siswa='$id_siswa'");
        $notif = ['type' => 'success', 'msg' => 'Password berhasil diubah'];
    }
}

// Ambil data siswa
$data = mysqli_fetch_assoc(mysqli_query($con, "
    SELECT s.*, k.nama_kelas FROM tb_siswa s 
    JOIN tb_mkelas k ON s.id_mkelas = k.id_mkelas 
    WHERE s.id_siswa = '$id_siswa'
"));
?>

<!-- HTML TAMPILAN PROFIL DAN GANTI PASSWORD -->
<div class="col-md-4 mt-3">
    <div class="card card-profile">
        <div class="card-header" style="background-image: url('/assets/img/bgdarkk.jpg')">
            <div class="profile-picture">
                <div class="avatar avatar-xl">
                    <img src="/assets/img/user/<?= $data['foto']; ?>" alt="..." class="avatar-img rounded-circle">
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="user-profile text-center">
                <div class="name"><?= $data['nama_siswa'] ?></div>
                <div class="job"><?= $data['nis'] ?></div>
                <div class="desc">Kelas (<?= $data['nama_kelas'] ?>)</div>

                <form action="" method="post">
                    <div class="form-group">
                        <input type="password" name="pass1" class="form-control" placeholder="Password Lama" required>
                    </div>
                    <div class="form-group">
                        <input type="password" name="pass2" class="form-control" placeholder="Password Baru" required>
                    </div>
                    <div class="form-group">
                        <input type="password" name="pass3" class="form-control" placeholder="Konfirmasi Password" required>
                    </div>

                    <div class="view-profile mt-3">
                        <button type="submit" class="btn btn-secondary btn-block">Ganti Password</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- SweetAlert Script -->
<?php if ($notif): ?>
<script src="../..//assets/js/plugin/sweetalert/sweetalert.min.js"></script>
<script>
setTimeout(function () {
    swal({
        title: "<?= $notif['type'] === 'success' ? 'Berhasil!' : 'Gagal!' ?>",
        text: "<?= $notif['msg'] ?>",
        icon: "<?= $notif['type'] ?>",
        buttons: {
            confirm: {
                className: "btn btn-<?= $notif['type'] === 'success' ? 'success' : 'danger' ?>"
            }
        }
    });
}, 100);
</script>
<?php endif; ?>