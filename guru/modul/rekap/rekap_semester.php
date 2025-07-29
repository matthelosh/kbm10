<?php
$kehadiran = ['H', 'I', 'S', 'A']; // Hadir, Izin, Sakit, Alpha
$no = 1;

// Ambil semester aktif jika tidak ada di GET
$semester_id = isset($_GET['semester_id']) ? intval($_GET['semester_id']) : 0;

if ($semester_id === 0) {
    $aktif = mysqli_fetch_assoc(mysqli_query($con, "
        SELECT s.id_semester, s.semester, t.tahun_ajaran
        FROM tb_semester s
        INNER JOIN tb_thajaran t ON s.id_thajaran = t.id_thajaran
        WHERE s.status=1 AND t.status=1
        ORDER BY t.tahun_ajaran DESC, s.semester DESC
        LIMIT 1
    "));
    if (!$aktif) {
        // Fallback kalau tidak ada semester aktif + tahun ajaran aktif
        $aktif = mysqli_fetch_assoc(mysqli_query($con, "
            SELECT s.id_semester, s.semester, t.tahun_ajaran
            FROM tb_semester s
            INNER JOIN tb_thajaran t ON s.id_thajaran = t.id_thajaran
            ORDER BY t.tahun_ajaran DESC, s.semester DESC
            LIMIT 1
        "));
    }
    $semester_id = intval($aktif['id_semester'] ?? 0);
}

if ($semester_id === 0) {
    echo "<div class='alert alert-warning'>⚠️ Semester & Tahun Ajaran tidak ditemukan!</div>";
    return;
}

// Ambil detail periode semester aktif
$infoSemester = mysqli_fetch_assoc(mysqli_query($con, "
    SELECT s.semester, t.tahun_ajaran
    FROM tb_semester s
    INNER JOIN tb_thajaran t ON s.id_thajaran = t.id_thajaran
    WHERE s.id_semester='$semester_id'
")) ?: ['semester' => 'Ganjil', 'tahun_ajaran' => date('Y') . '/' . (date('Y') + 1)];

$tahunAjaran = explode('/', $infoSemester['tahun_ajaran']);
$tahunAwal = intval($tahunAjaran[0] ?? date('Y'));
$tahunAkhir = intval($tahunAjaran[1] ?? (date('Y') + 1));

// Hitung periode semester
if (strtolower($infoSemester['semester']) === 'ganjil') {
    $startDate = "$tahunAwal-07-01";
    $endDate = "$tahunAwal-12-31";
} else { // Genap
    $startDate = "$tahunAkhir-01-01";
    $endDate = "$tahunAkhir-06-30";
}
?>

<form method="get" class="mb-3 d-flex align-items-center gap-2">
    <input type="hidden" name="page" value="rekap">
    <input type="hidden" name="id_mengajar" value="<?= $id_mengajar ?>">
    <input type="hidden" name="jenis" value="semester">
    <label for="semesterSelect" class="mr-2">Pilih Semester:</label>
    <select name="semester_id" id="semesterSelect" class="form-control" onchange="this.form.submit()" style="max-width: 300px;">
        <?php
        // ✅ Hilangkan filter t.status=1 supaya semua tahun ajaran & semester tampil
        $semesters = mysqli_query($con, "
            SELECT s.id_semester, s.semester, t.tahun_ajaran
            FROM tb_semester s
            INNER JOIN tb_thajaran t ON s.id_thajaran = t.id_thajaran
            ORDER BY t.tahun_ajaran DESC, s.semester ASC
        ");
        while ($s = mysqli_fetch_assoc($semesters)) {
            $selected = ($s['id_semester'] == $semester_id) ? 'selected' : '';
            echo "<option value='{$s['id_semester']}' $selected>{$s['semester']} ({$s['tahun_ajaran']})</option>";
        }
        ?>
    </select>
</form>

<div class="card">
    <div class="card-header">
        <h4 class="card-title">REKAP ABSEN SEMESTER</h4>
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
                    $qrySiswa = mysqli_query($con, "
                        SELECT id_siswa, nis, nama_siswa, jk
                        FROM tb_siswa
                        WHERE id_mkelas='$id_mkelas'
                        ORDER BY nama_siswa ASC
                    ");
                    while ($s = mysqli_fetch_assoc($qrySiswa)) {
                        echo "<tr>
                            <td>{$no}</td>
                            <td>" . htmlspecialchars($s['nis']) . "</td>
                            <td class='text-left'>" . htmlspecialchars($s['nama_siswa']) . "</td>
                            <td>" . htmlspecialchars($s['jk']) . "</td>";

                        foreach ($kehadiran as $kode) {
                            $jml = mysqli_fetch_assoc(mysqli_query($con, "
                                SELECT COUNT(*) AS jml
                                FROM _logabsensi
                                WHERE id_siswa='{$s['id_siswa']}'
                                AND id_mengajar='$id_mengajar'
                                AND DATE(tgl_absen) BETWEEN '$startDate' AND '$endDate'
                                AND ket='$kode'
                            "))['jml'] ?? 0;
                            echo "<td>{$jml}</td>";
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