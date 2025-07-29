<?php
$kehadiran = ['H', 'I', 'S', 'A']; // ✅ Hapus 'T'
$no = 1;
?>
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h4 class="card-title">REKAP ABSEN HARIAN</h4>
        <input type="date" id="tglFilter" value="<?= $hariIni ?>" class="form-control" style="max-width: 200px;">
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
                        <th>Alpha</th> <!-- ✅ Hapus Terlambat -->
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $siswa = mysqli_query($con, "SELECT * FROM tb_siswa WHERE id_mkelas='$id_mkelas'");
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