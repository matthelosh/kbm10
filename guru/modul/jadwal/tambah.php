<?php 
require_once __DIR__ . '/../../../config/db.php';

if (!isset($_SESSION)) session_start();
if (!isset($_SESSION['guru'])) {
    echo "<script>alert('Maaf! Anda belum login!');window.location='../../user.php';</script>";
    exit;
}

$id_guru = $_SESSION['guru'];
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
    const mapel = document.getElementById('mapel').value;
    const hari = document.querySelector('input[name="hari"]:checked');
    const kelas = document.querySelector('select[name="kelas"]').value;
    const waktu = document.getElementById('waktu').value.trim();
    const jamke = document.getElementById('jamke').value.trim();
    const ruang = document.getElementById('ruang').value.trim();

    if (!kode) { alert('Kode Pelajaran harus diisi!'); return false; }
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
                    <form method="post" onsubmit="return validateForm()">
                        <input type="hidden" name="ta" value="<?= $taAktif['id_thajaran'] ?>">
                        <input type="hidden" name="semester" value="<?= $semAktif['id_semester'] ?>">

                        <div class="form-group">
                            <label>Kode Pelajaran</label>
                            <input name="kode" type="text" class="form-control" id="kode" readonly placeholder="Otomatis dari mapel">
                        </div>

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

                        <div class="form-group">
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

                        <div class="form-group">
                            <label>Waktu</label>
                            <input name="waktu" type="text" class="form-control" id="waktu" placeholder="07.00 - 08.30">
                        </div>

                        <div class="form-group">
                            <label>Jam Ke</label>
                            <input name="jamke" type="text" class="form-control" id="jamke" placeholder="Contoh: 1 - 2">
                        </div>

                        <div class="form-group">
                            <label>Ruang</label>
                            <input name="ruang" type="text" class="form-control" id="ruang" placeholder="Contoh: R.1 / Lab">
                        </div>

                        <button type="submit" name="save" class="btn btn-primary"><i class="far fa-save"></i> Simpan</button>
                        <a href="index.php?page=jadwal" class="btn btn-danger"><i class="fas fa-angle-double-left"></i> Kembali</a>
                    </form>

                    <?php 
                    if (isset($_POST['save'])) {
                        $kode = trim($_POST['kode']);
                        $ta = $_POST['ta'];
                        $semester = $_POST['semester'];
                        $mapel = $_POST['mapel'];
                        $hari = $_POST['hari'];
                        $kelas = $_POST['kelas'];
                        $waktu = trim($_POST['waktu']);
                        $jamke = trim($_POST['jamke']);
                        $ruang = trim($_POST['ruang']);

                        $insert = mysqli_query($con, "INSERT INTO tb_mengajar 
                            (kode_pelajaran, hari, jam_mengajar, jamke, ruang, id_guru, id_mapel, id_mkelas, id_semester, id_thajaran) VALUES (
                            '$kode', '$hari', '$waktu', '$jamke', '$ruang', '$id_guru', '$mapel', '$kelas', '$semester', '$ta'
                        )");

                        if ($insert) {
                            echo "<script>
                                alert('Jadwal berhasil ditambahkan!');
                                window.location.href = 'index.php?page=jadwal';
                            </script>";
                        } else {
                            echo "<script>alert('Gagal menyimpan jadwal!');</script>";
                        }
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
</div>