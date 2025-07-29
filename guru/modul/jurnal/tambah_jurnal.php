<?php
include 'koneksi.php';
if (session_status() === PHP_SESSION_NONE) session_start();

// Cek login guru
if (!isset($_SESSION['guru'])) {
    echo "<script>alert('Akses ditolak. Guru belum login!'); window.location='../../user.php';</script>";
    exit;
}

$id_guru = $_SESSION['guru'];
$id_mengajar = $_GET['id_mengajar'] ?? null;

if (!$id_mengajar) {
    die("Parameter id_mengajar tidak valid.");
}

// Ambil detail mengajar
$query = "SELECT m.*, k.nama_kelas, mp.mapel 
          FROM tb_mengajar m
          JOIN tb_mkelas k ON m.id_mkelas = k.id_mkelas
          JOIN tb_master_mapel mp ON m.id_mapel = mp.id_mapel
          JOIN tb_thajaran t ON m.id_thajaran = t.id_thajaran
          WHERE m.id_mengajar = '$id_mengajar' AND m.id_guru = '$id_guru' AND t.status = 1";

$result = mysqli_query($conn, $query);
$data = mysqli_fetch_assoc($result);

if (!$data) {
    die("Data mengajar tidak ditemukan atau bukan milik Anda.");
}

// Proses simpan
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tanggal = $_POST['tanggal'];
    $jam_ke = $data['jamke'];
    $mapel = $data['mapel'];
    $uraian = $_POST['uraian_kegiatan'];
    $catatan = $_POST['catatan_perkembangan'];
    $kelas = $data['nama_kelas'];
    $id_kelas = $data['id_mkelas'];

    $sql = "INSERT INTO jurnal_mengajar (tanggal, jam_ke, kelas, mapel, uraian_kegiatan, catatan_perkembangan, id_kelas, id_guru)
            VALUES ('$tanggal', '$jam_ke', '$kelas', '$mapel', '$uraian', '$catatan', '$id_kelas', '$id_guru')";

    if (mysqli_query($conn, $sql)) {
        header("Location: ../../index.php?page=rekap_jurnal&pesan=sukses");
        exit;
    } else {
        $error = "Gagal menyimpan jurnal: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tambah Jurnal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f1f3f5;
        }
        .card {
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
            padding: 25px;
        }
        .form-label {
            font-weight: 500;
        }
        h3 {
            font-weight: bold;
            text-align: center;
            margin-bottom: 25px;
        }
    </style>
</head>
<body>
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="bg-primary text-white p-2 rounded text-center fw-semibold fs-5">
                    Tambah Jurnal
                </div>

                <?php if (isset($error)): ?>
                    <div class="alert alert-danger"><?= $error ?></div>
                <?php endif; ?>

                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">Tanggal</label>
                        <input type="date" name="tanggal" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Jam Ke</label>
                        <input type="text" class="form-control" value="<?= htmlspecialchars($data['jamke']) ?>" readonly>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Mata Pelajaran</label>
                        <input type="text" class="form-control" value="<?= htmlspecialchars($data['mapel']) ?>" readonly>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Kelas</label>
                        <input type="text" class="form-control" value="<?= htmlspecialchars($data['nama_kelas']) ?>" readonly>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Uraian Kegiatan</label>
                        <textarea name="uraian_kegiatan" class="form-control" rows="4" required></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Catatan Perkembangan</label>
                        <textarea name="catatan_perkembangan" class="form-control" rows="4" required></textarea>
                    </div>

                    <div class="d-flex justify-content-between">
                        <button type="submit" class="btn btn-success">Simpan Jurnal</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
</body>
</html>
