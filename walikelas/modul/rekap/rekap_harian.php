<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../../../config/db.php';

// Cek login wali kelas
if (!isset($_SESSION['walikelas'])) {
    echo "Anda harus login sebagai wali kelas.";
    exit;
}

$walikelas = $_SESSION['walikelas'];

// Ambil kelas yang diasuh wali kelas
$qKelas = mysqli_query($con, "SELECT id_mkelas FROM tb_walikelas WHERE id_walikelas = '$walikelas'");
if (mysqli_num_rows($qKelas) == 0) {
    echo "Kelas wali kelas tidak ditemukan.";
    exit;
}
$dataKelas = mysqli_fetch_assoc($qKelas);
$id_mkelas = $dataKelas['id_mkelas'];

$qNamaKelas = mysqli_query($con, "SELECT nama_kelas FROM tb_mkelas WHERE id_mkelas = '$id_mkelas' LIMIT 1");
$dataNamaKelas = mysqli_fetch_assoc($qNamaKelas);
$nama_kelas = $dataNamaKelas['nama_kelas'] ?? '-';

// Ambil tanggal filter dari parameter GET atau default hari ini
$hariIni = $_GET['tanggal'] ?? date('Y-m-d');

// Ambil id_mengajar untuk kelas ini
$qMengajar = mysqli_query($con, "SELECT id_mengajar FROM tb_mengajar WHERE id_mkelas = '$id_mkelas' LIMIT 1");
if (mysqli_num_rows($qMengajar) == 0) {
    echo "Data mengajar untuk kelas ini tidak ditemukan.";
    exit;
}
$dataMengajar = mysqli_fetch_assoc($qMengajar);
$id_mengajar = $dataMengajar['id_mengajar'];

$kehadiran = ['H', 'I', 'S', 'A']; // Tanpa 'T'
$no = 1;
?>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h4 class="card-title">REKAP ABSEN HARIAN KELAS <?= htmlspecialchars($nama_kelas) ?></h4>
        <input type="date" id="tglFilter" value="<?= htmlspecialchars($hariIni) ?>" class="form-control" style="max-width: 200px;">
    </div>
    <div class="card-body">
        <div class="mb-3">
            <input type="text" class="form-control" id="searchInput" placeholder="Cari siswa...">
        </div>
        <div class="table-responsive">
            <table class="table table-bordered" id="rekapTable">
                <thead class="thead-light">
                    <tr>
                        <th>No</th>
                        <th>Nama Siswa</th>
                        <th>Hadir</th>
                        <th>Izin</th>
                        <th>Sakit</th>
                        <th>Alpha</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $siswa = mysqli_query($con, "SELECT * FROM tb_siswa WHERE id_mkelas='$id_mkelas' ORDER BY nama_siswa ASC");
                    while($s = mysqli_fetch_array($siswa)) {
                        $absen = mysqli_query($con, "SELECT ket FROM _logabsensi WHERE id_siswa='{$s['id_siswa']}' AND id_mengajar='$id_mengajar' AND tgl_absen='$hariIni'");
                        $row = mysqli_fetch_array($absen);
                        echo "<tr><td>{$no}</td><td class='nama'>{$s['nama_siswa']}</td>";
                        foreach ($kehadiran as $kode) {
                            $checked = ($row && $row['ket'] == $kode) ? '<span style="color: green; font-weight: bold;">✔</span>' : '-';
                            echo "<td>{$checked}</td>";
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

<script>
    document.getElementById('tglFilter').addEventListener('change', function() {
        const tanggal = this.value;
        const urlParams = new URLSearchParams(window.location.search);
        urlParams.set('tanggal', tanggal);
        window.location.search = urlParams.toString();
    });

    document.getElementById('searchInput').addEventListener('keyup', function () {
        const filter = this.value.toLowerCase();
        const rows = document.querySelectorAll('#rekapTable tbody tr');
        rows.forEach(row => {
            const nama = row.querySelector('.nama').textContent.toLowerCase();
            row.style.display = nama.includes(filter) ? '' : 'none';
        });
    });
</script>