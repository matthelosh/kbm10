<?php
require_once __DIR__ . '/../../../config/db.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $jadwal = mysqli_fetch_array(mysqli_query($con, "SELECT * FROM tb_mengajar 
        LEFT JOIN tb_guru ON tb_mengajar.id_guru = tb_guru.id_guru
        LEFT JOIN tb_master_mapel ON tb_mengajar.id_mapel = tb_master_mapel.id_mapel
        LEFT JOIN tb_mkelas ON tb_mengajar.id_mkelas = tb_mkelas.id_mkelas
        LEFT JOIN tb_semester ON tb_mengajar.id_semester = tb_semester.id_semester
        LEFT JOIN tb_thajaran ON tb_mengajar.id_thajaran = tb_thajaran.id_thajaran
        WHERE tb_mengajar.id_mengajar = '$id'"));
}

?>

<div class="page-inner">
    <div class="page-header">
        <h4 class="page-title">Edit Jadwal Mengajar</h4>
    </div>
    <div class="card">
        <div class="card-body">
            <form method="post" onsubmit="return validateForm()">
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Kode Pelajaran</label>
                            <input name="kode" type="text" class="form-control" id="kode" readonly value="<?= $jadwal['kode_pelajaran'] ?>" placeholder="Otomatis dari mapel">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Tahun Pelajaran</label>
                            <input type="text" class="form-control" value="<?= $jadwal['tahun_ajaran'] ?>" readonly>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Semester</label>
                            <input type="text" class="form-control" value="<?= $jadwal['semester'] ?>" readonly>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Guru Mata Pelajaran</label>
                            <select name="guru" class="form-control">
                                <option value="">- Pilih -</option>
                                <?php 
                                $guru = mysqli_query($con, "SELECT * FROM tb_guru ORDER BY id_guru ASC");
                                while ($g = mysqli_fetch_assoc($guru)) {
                                    $selected = ($g['id_guru'] == $jadwal['id_guru']) ? 'selected' : '';
                                    echo "<option value='{$g['id_guru']}' $selected>{$g['nama_guru']}</option>";
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Mata Pelajaran</label>
                            <select name="mapel" id="mapel" class="form-control" onchange="updateKode()">
                                <option value="">- Pilih -</option>
                                <?php 
                                $mapel = mysqli_query($con, "SELECT * FROM tb_master_mapel ORDER BY id_mapel ASC");
                                while ($m = mysqli_fetch_assoc($mapel)) {
                                    $selected = ($m['id_mapel'] == $jadwal['id_mapel']) ? 'selected' : '';
                                    echo "<option value='{$m['id_mapel']}' data-kode='{$m['kode_mapel']}' $selected>[ {$m['kode_mapel']} ] {$m['mapel']}</option>";
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <label>Hari</label><br/>
                        <?php
                        $hariList = ['Senin', 'Selasa', 'Rabu', 'Kamis', "Jum'at", 'Sabtu'];
                        foreach ($hariList as $h) {
                            $checked = ($h == $jadwal['hari']) ? 'checked' : '';
                            echo '<label class="form-radio-label mr-3">
                                    <input class="form-radio-input" type="radio" name="hari" value="'.$h.'" '.$checked.'>
                                    <span class="form-radio-sign">'.$h.'</span>
                                  </label>';
                        }
                        ?>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Kelas</label>
                            <select name="kelas" class="form-control">
                                <option value="">- Pilih -</option>
                                <?php 
                                $kelas = mysqli_query($con, "SELECT * FROM tb_mkelas ORDER BY id_mkelas ASC");
                                while ($k = mysqli_fetch_assoc($kelas)) {
                                    $selected = ($k['id_mkelas'] == $jadwal['id_mkelas']) ? 'selected' : '';
                                    echo "<option value='{$k['id_mkelas']}' $selected>{$k['nama_kelas']}</option>";
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Waktu</label>
                            <input name="waktu" type="text" class="form-control" id="waktu" value="<?= $jadwal['jam_mengajar'] ?>" placeholder="07.00 - 08.30">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Jam Ke</label>
                            <input name="jamke" type="text" class="form-control" id="jamke" value="<?= $jadwal['jamke'] ?>" placeholder="Contoh: 1">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Ruang</label>
                            <input name="ruang" type="text" class="form-control" id="ruang" value="<?= $jadwal['ruang'] ?>" placeholder="Contoh: R.07">
                        </div>
                    </div>
                </div>

                <div class="form-group mt-4">
                    <button type="submit" name="save" class="btn btn-secondary"><i class="far fa-save"></i> Simpan</button>
                    <a href="javascript:history.back()" class="btn btn-danger"><i class="fas fa-angle-double-left"></i> Kembali</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php 
if (isset($_POST['save'])) {
    $kode = trim($_POST['kode']);
    $guru = $_POST['guru'];
    $mapel = $_POST['mapel'];
    $hari = $_POST['hari'];
    $kelas = $_POST['kelas'];
    $waktu = trim($_POST['waktu']);
    $jamke = trim($_POST['jamke']);
    $ruang = trim($_POST['ruang']);

    $query = "UPDATE tb_mengajar 
              SET kode_pelajaran = '$kode', hari = '$hari', jam_mengajar = '$waktu', 
              jamke = '$jamke', ruang = '$ruang', id_guru = '$guru', id_mapel = '$mapel', 
              id_mkelas = '$kelas' 
              WHERE id_mengajar = '$id'";

    $update = mysqli_query($con, $query);

    if ($update) {
        echo "<script>
            setTimeout(function () {
                swal('Sukses', 'Jadwal berhasil diperbarui', {
                    icon : 'success',
                    buttons: { confirm: { className : 'btn btn-success' } },
                });
            }, 10);
            setTimeout(function () {
                window.location.href = 'dashboard.php?page=jadwal';
            }, 3000);
        </script>";
    } else {
        echo "<script>alert('Gagal memperbarui jadwal!');</script>";
    }
}
?>