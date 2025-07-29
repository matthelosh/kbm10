<?php
include 'koneksi.php';

// Ambil ID dari URL
$id = $_GET['id'];

// Query untuk mendapatkan data jurnal berdasarkan ID
$query = "SELECT * FROM jurnal_mengajar WHERE id = $id";
$result = mysqli_query($conn, $query);
$row = mysqli_fetch_assoc($result);

// Jika data tidak ditemukan
if (!$row) {
    echo "Data tidak ditemukan!";
    exit;
}

// Proses untuk menyimpan perubahan
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ambil data dari form
    $tanggal = $_POST['tanggal'];
    $jam_ke = $_POST['jam_ke'];
    $kelas = $_POST['kelas'];
    $mapel = $_POST['mapel'];
    $uraian_kegiatan = $_POST['uraian_kegiatan'];
    $catatan_perkembangan = $_POST['catatan_perkembangan'];

    // Query untuk update data jurnal
    $update_query = "UPDATE jurnal_mengajar SET 
                    tanggal = '$tanggal',
                    jam_ke = '$jam_ke',
                    kelas = '$kelas',
                    mapel = '$mapel',
                    uraian_kegiatan = '$uraian_kegiatan',
                    catatan_perkembangan = '$catatan_perkembangan'
                    WHERE id = $id";

    if (mysqli_query($conn, $update_query)) {
        // Redirect setelah berhasil
        header("Location: jurnal.php?pesan=sukses");
    } else {
        echo "Terjadi kesalahan saat menyimpan perubahan: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Jurnal Mengajar</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-4">
    <h3 class="mb-3">Edit Jurnal Mengajar</h3>

    <!-- Form untuk mengedit jurnal -->
    <form method="POST">
        <div class="mb-3">
            <label for="tanggal" class="form-label">Tanggal</label>
            <input type="date" id="tanggal" name="tanggal" class="form-control" value="<?= htmlspecialchars($row['tanggal']) ?>" required>
        </div>

        <div class="mb-3">
            <label for="jam_ke" class="form-label">Jam Ke</label>
            <input type="number" id="jam_ke" name="jam_ke" class="form-control" value="<?= htmlspecialchars($row['jam_ke']) ?>" required>
        </div>

        <div class="mb-3">
            <label for="kelas" class="form-label">Kelas</label>
            <input type="text" id="kelas" name="kelas" class="form-control" value="<?= htmlspecialchars($row['kelas']) ?>" required>
        </div>

        <div class="mb-3">
            <label for="mapel" class="form-label">Mapel</label>
            <input type="text" id="mapel" name="mapel" class="form-control" value="<?= htmlspecialchars($row['mapel']) ?>" required>
        </div>

        <div class="mb-3">
            <label for="uraian_kegiatan" class="form-label">Uraian Kegiatan</label>
            <textarea id="uraian_kegiatan" name="uraian_kegiatan" class="form-control" rows="4" required><?= htmlspecialchars($row['uraian_kegiatan']) ?></textarea>
        </div>

        <div class="mb-3">
            <label for="catatan_perkembangan" class="form-label">Catatan Perkembangan</label>
            <textarea id="catatan_perkembangan" name="catatan_perkembangan" class="form-control" rows="4" required><?= htmlspecialchars($row['catatan_perkembangan']) ?></textarea>
        </div>

        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
        <a href="jurnal.php" class="btn btn-secondary">Kembali</a>
    </form>
</div>
</body>
</html>
