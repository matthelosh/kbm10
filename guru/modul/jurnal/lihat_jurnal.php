<?php
include 'koneksi.php'; // Sesuaikan path koneksi.php

// Pastikan ada ID yang dikirim via GET
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("ID jurnal tidak ditemukan!");
}

$id = $_GET['id'];
$query = "SELECT * FROM jurnal_mengajar WHERE id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
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
    <title>Lihat Jurnal Mengajar</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">

    <style>
@media print {
    .btn {
        display: none;
    }

    .card-header {
        display: block !important;
    }

    body {
        margin: 1cm;
        font-size: 14px;
        overflow: hidden;
    }

    .kop {
        margin-bottom: 20px;
        text-align: center;
        page-break-before: always;
        width: 100%;
    }

    .kop img {
        width: 100px;
        height: auto;
        margin-bottom: 10px;
    }

    .kop .info {
        text-align: center;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .kop .info h5 {
        font-size: 16px;
        white-space: nowrap;
        margin-top: 0;
    }

    .kop .info p {
        margin: 0;
    }

    .card-body {
        page-break-inside: avoid;
        margin-top: 20px;
    }

    hr {
        border: 1px solid #000;
        margin-top: 20px;
    }

    .card {
        width: 100%;
    }

    .card-header {
        text-align: center;
        font-weight: bold;
    }

    table {
        width: 100%;
        border-collapse: collapse; /* Garis tabel tidak terputus */
    }

    table th, table td {
        padding: 8px;
        border: 1px solid #000;
        font-weight: normal; /* Biar konsisten */
    }
}

.kop {
    display: flex;
    align-items: center;
    margin-bottom: 20px;
    justify-content: center;
}

.kop img {
    width: 100px;
    height: auto;
}

.kop .info {
    text-align: center;
    margin-left: 20px;
}

hr {
    border: 1px solid #000;
}
</style>

</head>
<body>
<div class="container mt-4">
    <!-- KOP SURAT -->
    <div class="kop">
        <img src="/absensiswa/assets/img/vsc.png" alt="Logo Instansi">
        <div class="info">
            <p class="mb-0">PEMERINTAH PROVINSI JAWA TIMUR</p>
            <p class="mb-0">DINAS PENDIDIKAN</p>
            <h6 class="mb-0">SEKOLAH MENENGAH KEJURUAN NEGERI 10 MALANG</h6>
            <p class="mb-0">Jl. Raya Tlogowaru Telp. (0341) 754086, Fax. (0341) 754087</p>
            <p class="mb-0">E-mail: smkn10_malang@yahoo.co.id</p>
        </div>
    </div>
    <hr>

    <div class="card shadow-lg mt-4">
        <div class="card-header bg-info text-white text-center">
            <h4 class="mb-0">Jurnal Mengajar</h4>
        </div>
        <div class="card-body">
            <!-- Tampilkan data jurnal dalam bentuk tabel -->
            <table class="table table-bordered">
                <tr>
                    <th>Tanggal</th>
                    <td><?= htmlspecialchars($row['tanggal']) ?></td>
                </tr>
                <tr>
                    <th>Jam Ke</th>
                    <td><?= htmlspecialchars($row['jam_ke']) ?></td>
                </tr>
                <tr>
                    <th>Kelas</th>
                    <td><?= htmlspecialchars($row['kelas']) ?></td>
                </tr>
                <tr>
                    <th>Mata Pelajaran</th>
                    <td><?= htmlspecialchars($row['mapel']) ?></td>
                </tr>
                <tr>
                    <th>Uraian Kegiatan</th>
                    <td><?= nl2br(htmlspecialchars($row['uraian_kegiatan'])) ?></td>
                </tr>
                <tr>
                    <th>Catatan Perkembangan</th>
                    <td><?= nl2br(htmlspecialchars($row['catatan_perkembangan'])) ?></td>
                </tr>
            </table>

            <!-- Tanda Tangan -->
<div class="row mt-5">
    <div class="col-6 text-center">
        <p class="mb-5">Kepala Sekolah</p>
        <p><strong>( __________________________ )</strong></p>
    </div>
    <div class="col-6 text-center">
        <p class="mb-5">Wali Kelas</p>
        <p><strong>( __________________________ )</strong></p>
    </div>
</div>
            <!-- Tombol Kembali & Print -->
            <div class="d-flex justify-content-between mt-4">
                <a href="jurnal.php" class="btn btn-secondary">🔙 Kembali</a>
                <button class="btn btn-success" onclick="window.print()">🖨️ Cetak Jurnal</button>
            </div>
        </div>
    </div>
</div>

</body>
</html>
