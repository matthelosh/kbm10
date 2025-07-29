<?php
include 'koneksi.php';

// Ambil data kelas dari database
$query = "SELECT * FROM tb_mkelas ORDER BY nama_kelas ASC";
$result = mysqli_query($conn, $query);

if (!$result) {
    die("Query error: " . mysqli_error($conn));
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jurnal Harian</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        .kelas-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border-radius: 10px;
            overflow: hidden;
        }
        .kelas-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.15);
        }
        .kelas-card .card-body {
            padding: 15px;
        }
        .btn-sm {
            font-size: 14px;
        }
    </style>
</head>
<body>
<div class="container mt-4">
    <h3 class="mb-3">Daftar Kelas</h3>
    
    <div class="row">
        <?php if (mysqli_num_rows($result) > 0): 
            while ($row = mysqli_fetch_assoc($result)): ?>
            <div class="col-md-4 col-sm-6 mb-4">
                <div class="card kelas-card shadow-sm">
                    <div class="card-body">
                        <h5 
                            class="card-title text-primary" 
                            title="<?= htmlspecialchars($row['nama_kelas']) ?>">
                            <?= htmlspecialchars($row['nama_kelas']) ?>
                        </h5>
                        <p 
                            class="card-text" 
                            title="<?= !empty($row['deskripsi']) ? htmlspecialchars($row['deskripsi']) : 'Kejuruan/Produktif' ?>">
                            <?= !empty($row['deskripsi']) ? htmlspecialchars($row['deskripsi']) : "Kejuruan/Produktif" ?>
                        </p>
                        <a 
                            href="modul/jurnal/tambah_jurnal.php?id_mkelas=<?= $row['id_mkelas'] ?>" 
                            class="btn btn-primary btn-sm">
                            Tambah Jurnal
                        </a>
                    </div>
                </div>
            </div>
        <?php endwhile; else: ?>
            <p class="text-muted">Belum ada kelas yang tersedia.</p>
        <?php endif; ?>
    </div>

    <a href="modul/jurnal/tambah_kelas.php" class="btn btn-success mt-3">➕ Tambah Kelas</a>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
