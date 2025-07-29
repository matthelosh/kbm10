<?php
// mengambil id siswa
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
$id_siswa = $data['id_siswa'];

$filter_bulan = $_GET['bulan'] ?? date('Y-m');
$filter_tanggal = $_GET['tanggal'] ?? date('Y-m-d');
$where = "a.id_siswa='$id_siswa' AND DATE_FORMAT(tgl_absen, '%Y-%m') ='$filter_bulan'";
$nama_siswa = $data['nama_siswa'];
$namaKelas = $data['nama_kelas'];
$idKelas = $data['id_mkelas'];

$id_guru = $data['id_guru'];  // Ganti dengan data yang sesuai
$id_mengajar = $data['id_mengajar'];

?>
<div class="card">
    <div class="card-body">
        <h4 class="card-title">Ketua Kelas <strong><?= $namaKelas ?> | <b style="text-transform: uppercase;"><code> <?= $nama_siswa ?> </code></b></h4>
        <hr>
        <!-- Menampilkan data absensi sesuai bulan & tahun -->
        <div class="col-xl-12">
            <div class="card text-left">
                <div class="card-body">
                    <div>
                        <h3>
                            <b>
                                Presensi Guru
                            </b>
                        </h3>
                    </div>
                    <form method="POST" action="?page=presensi-guru&act=simpan" enctype="multipart/form-data">
                        <div class="form-group">
                            <label for="tanggal">Tanggal</label>
                            <input style="max-width: 400px;" type="date" class="form-control" name="tanggal" value="<?= htmlspecialchars($filter_tanggal) ?>">
                        </div>

                        <div class="form-group">
                            <label for="id_mengajar">Mata Pelajaran & Guru</label>
                            <select style="max-width: 400px;" class="form-control" name="id_mengajar" required>
                                <option value="">- Pilih -</option>
                                <?php
                                // var_dump($data['id_mkelas']);
                                $q = mysqli_query($con, "SELECT m.id_mengajar,m.hari, m.jam_mengajar, mp.mapel, g.nama_guru 
                                    FROM tb_mengajar m 
                                    JOIN tb_master_mapel mp ON m.id_mapel=mp.id_mapel
                                    JOIN tb_guru g ON m.id_guru=g.id_guru
                                    WHERE m.id_mkelas='$idKelas'    
                                    ORDER BY mp.mapel ASC");
                                if ($q) {
                                    while ($row = mysqli_fetch_assoc($q)) {
                                        $selected = ($filter_mapel == $row['id_mengajar']) ? 'selected' : '';
                                        var_dump($row);
                                        echo "<option value='{$row['id_mengajar']}' $selected>{$row['mapel']} - {$row['hari']}({$row['jam_mengajar']}) - {$row['nama_guru']}</option>";
                                    }
                                } else {
                                    echo "<option value='' disabled>Data tidak ditemukan</option>";
                                }
                                ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Keterangan</label>
                            <div>
                                <label style="margin-right: 10px;">
                                    <input type="radio" name="keterangan" value="H" required> Hadir
                                </label>
                                <label style="margin-right: 10px;">
                                    <input type="radio" name="keterangan" value="S" required> Sakit
                                </label>
                                <label style="margin-right: 10px;">
                                    <input type="radio" name="keterangan" value="I" required> Izin
                                </label>
                                <label style="margin-right: 10px;">
                                    <input type="radio" name="keterangan" value="C" required> Cuti
                                </label>
                                <label style="margin-right: 10px;">
                                    <input type="radio" name="keterangan" value="D" required> Dinas Luar
                                </label>
                                <label style="margin-right: 10px;">
                                    <input type="radio" name="keterangan" value="P" required> Penugasan
                                </label>
                                <label style="margin-right: 10px;">
                                    <input type="radio" name="keterangan" value="K" required> Kosong
                                </label>
                        </div>
                        </div>

                        <div class="form-group">
                            <label>Upload Foto</label>
                            <input
                                type="file"
                                name="fileUpload"
                                accept="image/*"
                                capture="environment"
                                class="form-control-file"
                                required>
                        </div>
                        <div class="d-flex gap-5">
                            <button type="submit" class="btn btn-primary mt-5 mr-2">Simpan</button>
                            <a href="javascript:history.back()" class="btn btn-secondary mt-5 mr-2">Kembali</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>