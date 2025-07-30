<?php
// include 'koneksi.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/config/db.php';

$query = "SELECT * FROM jurnal_mengajar ORDER BY tanggal DESC";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jurnal Mengajar</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        .aksi-col a {
            margin-bottom: 5px;
        }
    </style>
</head>
<body>
<div class="container mt-4">
    <h3 class="mb-3">Jurnal Mengajar</h3>

    <!-- Tampilkan pesan sukses jika ada -->
    <?php if (isset($_GET['pesan']) && $_GET['pesan'] === 'sukses'): ?>
        <div class="alert alert-success">✅ Jurnal berhasil disimpan!</div>
    <?php endif; ?>

    <div class="table-responsive">
        <table class="table table-bordered table-striped">
            <thead class="table-dark">
                <tr>
                    <th>Tanggal</th>
                    <th>Jam Ke</th>
                    <th>Kelas</th>
                    <th>Mapel</th>
                    <th>Uraian Kegiatan</th>
                    <th>Catatan Perkembangan</th>
                    <th class="aksi-col">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_assoc($result)) { ?>
                    <tr>
                        <td><?= htmlspecialchars($row['tanggal']) ?></td>
                        <td><?= htmlspecialchars($row['jam_ke']) ?></td>
                        <td><?= htmlspecialchars($row['kelas']) ?></td>
                        <td><?= htmlspecialchars($row['mapel']) ?></td>
                        <td><?= htmlspecialchars($row['uraian_kegiatan']) ?></td>
                        <td><?= htmlspecialchars($row['catatan_perkembangan']) ?></td>
                        <td class="aksi-col">
                            <div class="d-flex flex-column">
                                <a href="edit_jurnal.php?id=<?= $row['id'] ?>" class="btn btn-warning mb-2">Edit Jurnal</a>
                                <a href="hapus.php?id=<?= $row['id'] ?>" class="btn btn-danger btn-sm mb-2" onclick="return confirm('Yakin ingin menghapus?');">Hapus</a>
                                <a href="lihat_jurnal.php?id=<?= $row['id'] ?>" class="btn btn-info btn-sm">Lihat</a>
                            </div>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>
