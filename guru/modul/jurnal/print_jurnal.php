<?php
include 'koneksi.php'; // Hubungkan ke database

// Cek apakah ada ID jurnal
if (!isset($_GET['id'])) {
    die("ID jurnal tidak ditemukan!");
}

$id = $_GET['id'];
$query = "SELECT * FROM jurnal_mengajar WHERE id = $id";
$result = mysqli_query($conn, $query);
$row = mysqli_fetch_assoc($result);

// Jika data tidak ditemukan
if (!$row) {
    die("Data jurnal tidak ditemukan!");
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Jurnal Mengajar</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">

    <style>
        @media print {
            .print-btn { display: none; } /* Sembunyikan tombol saat dicetak */
        }

        .container {
            max-width: 800px;
            margin: auto;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .header img {
            height: 80px;
        }

        .school-info {
            display: block;
            line-height: 1.2; /* Menghilangkan jarak besar antar teks */
        }

        .school-name {
            font-size: 24px; /* Ukuran lebih besar */
            font-weight: bold;
        }

        .school-address {
            font-size: 16px; /* Ukuran lebih kecil */
            font-weight: bold;
        }

        .jurnal-title {
            font-size: 28px;
            font-weight: bold;
            color: #007BFF; /* Warna biru elegan */
            text-transform: uppercase;
            margin-top: 10px;
        }

        .title-divider {
            border: 2px solid #007BFF; /* Garis bawah berwarna biru */
            width: 50%;
            margin: 10px auto;
        }

        .table th {
            width: 200px;
            background-color: #f8f9fa;
        }

        .text-end {
            margin-top: 20px;
        }
    </style>
</head>
<body>

<div class="container mt-4">
    <div class="header">
        <img src="../..//assets/img/vsc.png" alt="Logo Sekolah"> <!-- Ganti dengan logo sekolah -->
        <span class="school-info school-name">SMKN 10 Malang</span>
        <span class="school-info school-address">Jl. Tlogowaru, Kedungkandang, Kota Malang</span>
        <hr class="title-divider">
        <h1 class="jurnal-title">📖 Jurnal Mengajar Harian</h1>
    </div>

    <table class="table table-bordered">
        <tr>
            <th>📅 Tanggal</th>
            <td><?= htmlspecialchars($row['tanggal']) ?></td>
        </tr>
        <tr>
            <th>⏰ Jam Ke</th>
            <td><?= htmlspecialchars($row['jam_ke']) ?></td>
        </tr>
        <tr>
            <th>🏫 Kelas</th>
            <td><?= htmlspecialchars($row['kelas']) ?></td>
        </tr>
        <tr>
            <th>📚 Mata Pelajaran</th>
            <td><?= htmlspecialchars($row['mapel']) ?></td>
        </tr>
        <tr>
            <th>📝 Uraian Kegiatan</th>
            <td><?= htmlspecialchars($row['uraian_kegiatan']) ?></td>
        </tr>
        <tr>
            <th>📌 Catatan Perkembangan</th>
            <td><?= htmlspecialchars($row['catatan_perkembangan']) ?></td>
        </tr>
    </table>

    <div class="text-end">
        <button onclick="window.print()" class="btn btn-success print-btn">🖨️ Cetak</button>
        <a href="javascript:history.back()" class="btn btn-secondary print-btn">🔙 Kembali</a>
    </div>
</div>

</body>
</html>
