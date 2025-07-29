<?php
// File: tambah_kelas.php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['guru'])) {
    die("Akses ditolak. Silakan login sebagai guru.");
}

$id_guru = $_SESSION['guru'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nama_kelas = mysqli_real_escape_string($conn, $_POST['nama_kelas']);
    $deskripsi = mysqli_real_escape_string($conn, $_POST['deskripsi']);

    if (empty($nama_kelas) || empty($deskripsi)) {
        $error = "Nama kelas dan deskripsi harus diisi!";
    } else {
        $query = "INSERT INTO tb_mkelas (nama_kelas, deskripsi, id_guru) VALUES ('$nama_kelas', '$deskripsi', '$id_guru')";
        if (mysqli_query($conn, $query)) {
            header("Location: /kbm/guru/index.php?page=jurnal&status=sukses");
            exit();
        } else {
            $error = "Gagal menambahkan kelas: " . mysqli_error($conn);
        }
    }
}
?>

<!-- HTML untuk tambah kelas -->
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Kelas</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-5">
    <div class="card shadow-lg">
        <div class="card-header bg-primary text-white">
            <h4>Tambah Kelas Baru</h4>
        </div>
        <div class="card-body">
            <?php if (isset($error)) { ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php } ?>
            <form method="POST">
                <div class="mb-3">
                    <label class="form-label">Nama Kelas</label>
                    <input type="text" name="nama_kelas" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Deskripsi</label>
                    <textarea name="deskripsi" class="form-control" rows="3" required></textarea>
                </div>
                <div class="d-flex justify-content-between">
                    <a href="/kbm/guru/index.php?page=jurnal" class="btn btn-secondary">Kembali</a>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>
</body>
</html>
