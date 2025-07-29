<?php
include '../../../config/db.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Rekap Jurnal Guru</title>
    <link rel="stylesheet" href="../..//assets/_login/vendor/bootstrap/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-5">
    <h3 class="text-center mb-4">Rekap Jurnal Mengajar Guru</h3>

    <!-- Form Pilihan Guru -->
    <form method="GET" action="index.php" class="form-inline mb-4 justify-content-center">
    <input type="hidden" name="page" value="rekap_jurnal">
    <select name="id_guru" class="form-control mr-2" required>
        <option value="">-- Pilih Guru --</option>
        <?php
        $guruQuery = mysqli_query($con, "SELECT id_guru, nama_guru FROM tb_guru ORDER BY nama_guru ASC");
        while ($g = mysqli_fetch_assoc($guruQuery)) {
            $selected = (isset($_GET['id_guru']) && $_GET['id_guru'] == $g['id_guru']) ? 'selected' : '';
            echo "<option value='{$g['id_guru']}' $selected>{$g['nama_guru']}</option>";
        }
        ?>
    </select>
    <button type="submit" class="btn btn-primary">Tampilkan</button>
</form>


    <?php
    if (isset($_GET['id_guru']) && $_GET['id_guru'] != '') {
        $id_guru = mysqli_real_escape_string($con, $_GET['id_guru']);

        $sql = mysqli_query($con, "
            SELECT jm.tanggal, jm.jam_ke, jm.kelas, jm.mapel, jm.uraian_kegiatan, jm.catatan_perkembangan, g.nama_guru
            FROM jurnal_mengajar jm
            INNER JOIN tb_guru g ON jm.id_guru = g.id_guru
            WHERE g.id_guru = '$id_guru'
            ORDER BY jm.tanggal DESC
        ");

        if (mysqli_num_rows($sql) > 0) {
            $namaGuru = mysqli_fetch_assoc(mysqli_query($con, "SELECT nama_guru FROM tb_guru WHERE id_guru = '$id_guru'"))['nama_guru'];
            echo "<h5 class='mb-3'>Hasil Rekap untuk: <strong>$namaGuru</strong></h5>";
            echo "<table class='table table-bordered table-striped'>";
            echo "<thead class='thead-dark'>
                    <tr>
                        <th>No</th>
                        <th>Tanggal</th>
                        <th>Jam Ke</th>
                        <th>Kelas</th>
                        <th>Mapel</th>
                        <th>Uraian Kegiatan</th>
                        <th>Catatan Perkembangan</th>
                    </tr>
                  </thead>
                  <tbody>";
            $no = 1;
            while ($row = mysqli_fetch_assoc($sql)) {
                echo "<tr>
                        <td>{$no}</td>
                        <td>{$row['tanggal']}</td>
                        <td>{$row['jam_ke']}</td>
                        <td>{$row['kelas']}</td>
                        <td>{$row['mapel']}</td>
                        <td>{$row['uraian_kegiatan']}</td>
                        <td>{$row['catatan_perkembangan']}</td>
                    </tr>";
                $no++;
            }
            echo "</tbody></table>";
        } else {
            echo "<div class='alert alert-warning text-center'>Belum ada jurnal untuk guru ini.</div>";
        }
    } else {
        echo "<div class='text-center text-muted'>Silakan pilih guru terlebih dahulu untuk melihat jurnal.</div>";
    }
    ?>
</div>
</body>
</html>
