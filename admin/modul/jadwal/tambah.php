<?php 
require_once __DIR__ . '/../../../config/db.php';

$taAktif = mysqli_fetch_array(mysqli_query($con, "SELECT * FROM tb_thajaran WHERE status=1"));
$semAktif = mysqli_fetch_array(mysqli_query($con, "SELECT * FROM tb_semester WHERE status=1"));
?>

<script>
function updateKode() {
    const selectMapel = document.getElementById("mapel");
    const selected = selectMapel.options[selectMapel.selectedIndex];
    const kodeMapel = selected.getAttribute("data-kode");
    document.getElementById("kode").value = kodeMapel || "";
}

function validateForm() {
    const kode = document.getElementById('kode').value.trim();
    const guru = document.querySelector('select[name="guru"]').value;
    const mapel = document.getElementById('mapel').value;
    const hari = document.querySelector('input[name="hari"]:checked');
    const kelas = document.querySelector('select[name="kelas"]').value;
    const waktu = document.getElementById('waktu').value.trim();
    const jamke = document.getElementById('jamke').value.trim();
    const ruang = document.getElementById('ruang').value.trim();

    if (!kode) { alert('Kode Pelajaran harus diisi!'); return false; }
    if (!guru) { alert('Pilih Guru Mata Pelajaran!'); return false; }
    if (!mapel) { alert('Pilih Mata Pelajaran!'); return false; }
    if (!hari) { alert('Pilih Hari!'); return false; }
    if (!kelas) { alert('Pilih Kelas!'); return false; }
    if (!waktu) { alert('Waktu harus diisi!'); return false; }
    if (!jamke) { alert('Jam Ke harus diisi!'); return false; }
    if (!ruang) { alert('Ruang harus diisi!'); return false; }

    return true;
}
</script>

<div class="page-inner">
    <div class="page-header">
        <h4 class="page-title">Jadwal</h4>
        <ul class="breadcrumbs">
            <li class="nav-home"><a href="#"><i class="flaticon-home"></i></a></li>
            <li class="separator"><i class="flaticon-right-arrow"></i></li>
            <li class="nav-item"><a href="#">Jadwal Mengajar</a></li>
            <li class="separator"><i class="flaticon-right-arrow"></i></li>
            <li class="nav-item"><a href="#">Tambah Jadwal</a></li>
        </ul>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <div class="card-title">Tambah Jadwal Mengajar</div>
                </div>
                <div class="card-body">
                    <form method="post" action="modul/jadwal/proses.php" onsubmit="return validateForm()">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Kode Pelajaran</label>
                                    <input name="kode" type="text" class="form-control" id="kode" readonly placeholder="Otomatis dari mapel">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Tahun Pelajaran</label>
                                    <input type="hidden" name="ta" value="<?= $taAktif['id_thajaran'] ?>">
                                    <input type="hidden" name="semester" value="<?= $semAktif['id_semester'] ?>">
                                    <input type="text" class="form-control" value="<?= $taAktif['tahun_ajaran'] ?>" readonly>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Semester</label>
                                    <input type="text" class="form-control" value="<?= $semAktif['semester'] ?>" readonly>
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
                                            echo "<option value='{$g['id_guru']}'>{$g['nama_guru']}</option>";
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
                                            echo "<option value='{$m['id_mapel']}' data-kode='{$m['kode_mapel']}'>[ {$m['kode_mapel']} ] {$m['mapel']}</option>";
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
                                    echo '<label class="form-radio-label mr-3">
                                            <input class="form-radio-input" type="radio" name="hari" value="'.$h.'">
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
                                            echo "<option value='{$k['id_mkelas']}'>{$k['nama_kelas']}</option>";
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
                                    <input name="waktu" type="text" class="form-control" id="waktu" placeholder="07.00 - 08.30">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Jam Ke</label>
                                    <input name="jamke" type="text" class="form-control" id="jamke" placeholder="Contoh: 1">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Ruang</label>
                                    <input name="ruang" type="text" class="form-control" id="ruang" placeholder="Contoh: R.07">
                                </div>
                            </div>
                        </div>

                        <div class="form-group mt-4">
                            <button type="submit" name="save" class="btn btn-secondary"><i class="far fa-save"></i> Simpan</button>
                            <a href="modul/jadwal/contoh_jadwal.xlsx" class="btn btn-info" download><i class="fas fa-download"></i> Download Contoh Excel</a>
                            <button type="button" class="btn btn-success" data-toggle="modal" data-target="#uploadExcelModal">
                                <i class="fas fa-file-excel"></i> Insert Excel
                            </button>
                            <a href="javascript:history.back()" class="btn btn-danger"><i class="fas fa-angle-double-left"></i> Kembali</a>
                        </div>
                    </form>
                    <?php include 'upload_excel_modal.php'; ?>
                </div>
            </div>
        </div>
    </div>
</div>