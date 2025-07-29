<?php
$jumlahHari = date('t', strtotime("$bulanIni-01"));
?>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h4 class="card-title">REKAP ABSEN BULANAN PER TANGGAL</h4>
        <input type="month" id="bulanFilter" value="<?= $bulanIni ?>" class="form-control" style="max-width: 200px;">
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered text-center" style="font-size: 13px;">
                <thead class="thead-light align-middle">
                    <tr>
                        <th rowspan="2">NO</th>
                        <th rowspan="2">NIS</th>
                        <th rowspan="2">NAMA SISWA</th>
                        <th rowspan="2">L/P</th>
                        <th colspan="<?= $jumlahHari ?>">BULAN : <?= strtoupper(date('F Y', strtotime($bulanIni))) ?></th>
                        <th colspan="3">JUMLAH</th> <!-- ✅ Hapus T -->
                    </tr>
                    <tr>
                        <?php for ($i = 1; $i <= $jumlahHari; $i++): ?>
                            <th style="min-width: 25px; padding: 3px;"><?= $i ?></th>
                        <?php endfor; ?>
                        <th style="background:yellow; font-weight: bold;">S</th>
                        <th style="background:lightgreen; font-weight: bold;">I</th>
                        <th style="background:red; color:white; font-weight: bold;">A</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $no = 1;
                    $siswa = mysqli_query($con,"SELECT * FROM tb_siswa WHERE id_mkelas='$id_mkelas'");
                    while($s = mysqli_fetch_array($siswa)):
                        $rekap = [];
                        $jml = ['S' => 0, 'I' => 0, 'A' => 0]; // ✅ Hapus T

                        for ($i = 1; $i <= $jumlahHari; $i++) {
                            $tgl = sprintf('%s-%02d', $bulanIni, $i);
                            $q = mysqli_query($con, "SELECT ket FROM _logabsensi 
                                WHERE id_siswa='{$s['id_siswa']}' 
                                AND id_mengajar='$id_mengajar' 
                                AND tgl_absen='$tgl' LIMIT 1");
                            $d = mysqli_fetch_array($q);
                            $simbol = $d['ket'] ?? '';
                            $rekap[$i] = $simbol;

                            if (isset($jml[$simbol])) {
                                $jml[$simbol]++;
                            }
                        }
                    ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td><?= htmlspecialchars($s['nis']) ?></td>
                        <td class="text-left"><?= htmlspecialchars($s['nama_siswa']) ?></td>
                        <td><?= htmlspecialchars($s['jk']) ?></td>
                        <?php for ($i = 1; $i <= $jumlahHari; $i++):
                            $symbol = $rekap[$i] ?? '';
                            $color = '';
                            if ($symbol == 'H') $color = 'style="color:blue; font-weight:bold"';
                            elseif ($symbol == 'I') $color = 'style="color:green; font-weight:bold"';
                            elseif ($symbol == 'S') $color = 'style="color:orange; font-weight:bold"';
                            elseif ($symbol == 'A') $color = 'style="color:red; font-weight:bold"';
                            echo "<td $color>$symbol</td>";
                        endfor; ?>
                        <td><?= $jml['S'] ?: '-' ?></td>
                        <td><?= $jml['I'] ?: '-' ?></td>
                        <td><?= $jml['A'] ?: '-' ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    document.getElementById('bulanFilter').addEventListener('change', function() {
        const bulan = this.value;
        const urlParams = new URLSearchParams(window.location.search);
        urlParams.set('bulan', bulan);
        window.location.search = urlParams.toString();
    });
</script>