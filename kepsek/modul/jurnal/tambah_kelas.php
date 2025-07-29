<?php
include 'koneksi.php';

// Cek apakah form telah disubmit
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nama_kelas = mysqli_real_escape_string($conn, $_POST['nama_kelas']);
    $deskripsi = mysqli_real_escape_string($conn, $_POST['deskripsi']);

    // Validasi input tidak boleh kosong
    if (empty($nama_kelas) || empty($deskripsi)) {
        $error = "Nama kelas dan deskripsi harus diisi!";
    } else {
        // Insert data ke database
        $query = "INSERT INTO tb_mkelas (nama_kelas, deskripsi) VALUES ('$nama_kelas', '$deskripsi')";
        if (mysqli_query($conn, $query)) {
            // Redirect ke halaman utama dengan parameter ?page=jurnal
            header("Location: /absensiswa/guru/?page=jurnal&status=sukses");
            exit();
        } else {
            $error = "Gagal menambahkan kelas: " . mysqli_error($conn);
        }
    }
}
?>

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
        <div class="card-header bg-success text-white">
            <h4>➕ Tambah Kelas Baru</h4>
        </div>
        <div class="card-body">
            <?php if (isset($error)) { ?>
                <div class="alert alert-danger"><?= $error ?></div>
            <?php } ?>
            <form method="POST">
                <div class="mb-3">
                    <label class="form-label">📌 Nama Kelas</label>
                    <input type="text" name="nama_kelas" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">📖 Deskripsi Kelas</label>
                    <textarea name="deskripsi" class="form-control" rows="3" required></textarea>
                </div>
                <button type="submit" class="btn btn-success">✔️ Simpan</button>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
