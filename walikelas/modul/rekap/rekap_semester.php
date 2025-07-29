<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../../../config/db.php';

// ✅ Cek login wali kelas
$walikelas = intval($_SESSION['walikelas'] ?? 0);
if ($walikelas === 0) {
    echo "Anda harus login sebagai wali kelas.";
    exit;
}

// ✅ Ambil kelas wali kelas + nama kelas
$qKelas = mysqli_query($con, "
    SELECT k.id_mkelas, k.nama_kelas
    FROM tb_walikelas w
    JOIN tb_mkelas k ON w.id_mkelas = k.id_mkelas
    WHERE w.id_walikelas = '$walikelas'
    LIMIT 1
");
if (mysqli_num_rows($qKelas) === 0) {
    echo "Kelas wali kelas tidak ditemukan.";
    exit;
}
$dataKelas = mysqli_fetch_assoc($qKelas);
$id_mkelas = intval($dataKelas['id_mkelas']);
$nama_kelas = htmlspecialchars($dataKelas['nama_kelas']);

// ✅ Ambil semester_id dari GET atau default ke semester terbaru
$semester_id = intval($_GET['semester_id'] ?? 0);

if ($semester_id === 0) {
    $getTerbaru = mysqli_query($con, "
        SELECT s.id_semester
        FROM tb_semester s
        JOIN tb_thajaran t ON s.id_thajaran = t.id_thajaran
        ORDER BY t.tahun_ajaran DESC, s.semester DESC
        LIMIT 1
    ");
    $terbaru = mysqli_fetch_assoc($getTerbaru);
    if ($terbaru) {
        $semester_id = intval($terbaru['id_semester']);
    } else {
        echo "Tidak ada data semester.";
        exit;
    }
}

$kehadiran = ['H', 'I', 'S', 'A'];
$no = 1;
?>

<form method="get" class="mb-3 d-flex align-items-center gap-2">
    <input type="hidden" name="page" value="rekap">
    <input type="hidden" name="jenis" value="semester">
    <label for="semesterSelect" class="mr-2">Pilih Semester:</label>
    <select name="semester_id" id="semesterSelect" class="form-control" onchange="this.form.submit()" style="max-width: 300px;">
        <?php
        // ✅ Ambil SEMUA semester & tahun ajaran (tanpa filter status)
        $semesters = mysqli_query($con, "
            SELECT s.id_semester, s.semester, t.tahun_ajaran
            FROM tb_semester s
            JOIN tb_thajaran t ON s.id_thajaran = t.id_thajaran
            ORDER BY t.tahun_ajaran DESC, s.semester ASC
        ");

        while ($s = mysqli_fetch_assoc($semesters)) {
            $selected = ($s['id_semester'] == $semester_id) ? 'selected' : '';
            $label = htmlspecialchars($s['semester']) . " (" . htmlspecialchars($s['tahun_ajaran']) . ")";
            echo "<option value='" . intval($s['id_semester']) . "' $selected>$label</option>";
        }
        ?>
    </select>
</form>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h4 class="card-title">REKAP ABSEN SEMESTER KELAS <?= $nama_kelas ?></h4>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered text-center">
                <thead class="thead-light">
                    <tr>
                        <th>No</th>
                        <th>NIS</th>
                        <th>Nama Siswa</th>
                        <th>L/P</th>
                        <th>Hadir</th>
                        <th>Izin</th>
                        <th>Sakit</th>
                        <th>Alpha</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $siswa = mysqli_query($con, "
                        SELECT s.id_siswa, s.nis, s.nama_siswa, s.jk
                        FROM tb_siswa s
                        WHERE s.id_mkelas = '$id_mkelas'
                        ORDER BY s.nama_siswa ASC
                    ");

                    while ($s = mysqli_fetch_assoc($siswa)) {
                        echo "<tr>
                            <td>{$no}</td>
                            <td>" . htmlspecialchars($s['nis']) . "</td>
                            <td class='text-left'>" . htmlspecialchars($s['nama_siswa']) . "</td>
                            <td>" . htmlspecialchars($s['jk']) . "</td>";

                        foreach ($kehadiran as $kode) {
                            $q = mysqli_query($con, "
                                SELECT COUNT(*) AS jml
                                FROM _logabsensi a
                                JOIN tb_mengajar m ON a.id_mengajar = m.id_mengajar
                                WHERE a.id_siswa='{$s['id_siswa']}'
                                AND m.id_mkelas = '$id_mkelas'
                                AND m.id_semester = '$semester_id'
                                AND a.ket = '$kode'
                            ");
                            $jml = mysqli_fetch_assoc($q);
                            echo "<td>" . intval($jml['jml'] ?? 0) . "</td>";
                        }

                        echo "</tr>";
                        $no++;
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>