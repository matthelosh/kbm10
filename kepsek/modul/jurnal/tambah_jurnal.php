<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include 'koneksi.php';
session_start();

// Ambil ID guru dari session
$id_guru = $_SESSION['guru'] ?? null;
$nama_guru = $_SESSION['nama_guru'] ?? '';

// Jika tidak ada ID guru, set default ke NULL agar tidak menyebabkan error SQL
if (!$id_guru) {
    $id_guru = 'NULL';
}

// Ambil ID kelas dari URL
$id_mkelas = $_GET['id_mkelas'] ?? 0;

// Ambil data kelas
$query = "SELECT * FROM tb_mkelas WHERE id_mkelas = '$id_mkelas'";
$result = mysqli_query($conn, $query);
if (!$result) {
    die("Query Error: " . mysqli_error($conn));
}
$kelas = mysqli_fetch_assoc($result);
if (!$kelas) {
    die("Kelas tidak ditemukan!");
}

// Ambil data mapel dari database
$query_mapel = "SELECT * FROM tb_master_mapel ORDER BY mapel ASC";
$result_mapel = mysqli_query($conn, $query_mapel);
$daftar_mapel = [];
if ($result_mapel) {
    while ($row_mapel = mysqli_fetch_assoc($result_mapel)) {
        $daftar_mapel[] = $row_mapel;
    }
}

// Proses simpan jika disubmit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tanggal = $_POST['tanggal'];
    $jam_ke = $_POST['jam_ke'];
    $mapel = $_POST['mapel'];
    $uraian = $_POST['uraian_kegiatan'];
    $catatan = $_POST['catatan_perkembangan'];

    $sql = "INSERT INTO jurnal_mengajar (tanggal, jam_ke, kelas, mapel, uraian_kegiatan, catatan_perkembangan, id_kelas, id_guru)
            VALUES ('$tanggal', '$jam_ke', '{$kelas['nama_kelas']}', '$mapel', '$uraian', '$catatan', '$id_mkelas', '$id_guru')";

    if (mysqli_query($conn, $sql)) {
        header("Location:../../index.php?page=rekap_jurnal&pesan=sukses");
        exit;
    } else {
        $error = "❌ Gagal menyimpan jurnal: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Jurnal</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-4">
    <h3 class="mb-3">📖 Tambah Jurnal - <?= htmlspecialchars($kelas['nama_kelas']) ?></h3>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>

    <!-- Form tambah jurnal -->
    <form action="" method="POST">
        <div class="mb-3">
            <label class="form-label">Tanggal</label>
            <input type="date" name="tanggal" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Jam Ke</label>
            <input type="text" name="jam_ke" class="form-control" required placeholder="Misal: 1-3">
        </div>

        <div class="mb-3">
            <label class="form-label">Mata Pelajaran</label>
            <select name="mapel" class="form-select" required>
                <option value="" disabled selected>-- Pilih Mata Pelajaran --</option>
                <?php foreach ($daftar_mapel as $mapel): ?>
                    <option value="<?= htmlspecialchars($mapel['mapel']) ?>">
                        <?= htmlspecialchars($mapel['mapel']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Uraian Kegiatan</label>
            <textarea name="uraian_kegiatan" class="form-control" required></textarea>
        </div>

        <div class="mb-3">
            <label class="form-label">Catatan Perkembangan</label>
            <textarea name="catatan_perkembangan" class="form-control" required></textarea>
        </div>

        <input type="hidden" name="id_kelas" value="<?= htmlspecialchars($id_mkelas) ?>">

        <button type="submit" class="btn btn-success">✅ Simpan Jurnal</button>
        <a href="jurnal.php" class="btn btn-secondary">🔙 Kembali</a>
    </form>
</div>
</body>
</html>
