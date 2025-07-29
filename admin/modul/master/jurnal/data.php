<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include '../../../config/db.php';
//require_once $_SERVER['DOCUMENT_ROOT'] . '../config/db.php';

if (!isset($_SESSION['admin'])) {
    echo "<script>alert('Anda harus login sebagai admin terlebih dahulu'); window.location='../../user.php';</script>";
    exit;
}

// Ambil filter
$tgl_awal = $_GET['tgl_awal'] ?? '';
$tgl_akhir = $_GET['tgl_akhir'] ?? '';
$id_kelas = $_GET['kelas'] ?? '';
$id_guru = $_GET['guru'] ?? '';

// Ambil daftar kelas dan guru
$kelasList = $con->query("SELECT * FROM tb_mkelas ORDER BY nama_kelas ASC")->fetch_all(MYSQLI_ASSOC);
$guruList = $con->query("SELECT * FROM tb_guru ORDER BY nama_guru ASC")->fetch_all(MYSQLI_ASSOC);

// Filter query
$where = "WHERE 1";
$params = [];
$types = "";

if (!empty($tgl_awal) && !empty($tgl_akhir)) {
    $where .= " AND jm.tanggal BETWEEN ? AND ?";
    $params[] = $tgl_awal;
    $params[] = $tgl_akhir;
    $types .= "ss";
}
if (!empty($id_kelas)) {
    $where .= " AND jm.id_kelas = ?";
    $params[] = $id_kelas;
    $types .= "i";
}
if (!empty($id_guru)) {
    $where .= " AND jm.id_guru = ?";
    $params[] = $id_guru;
    $types .= "s";
}

$sql = "
    SELECT jm.*, mk.nama_kelas
    FROM jurnal_mengajar jm
    JOIN tb_mkelas mk ON jm.id_kelas = mk.id_mkelas
    $where
    ORDER BY jm.tanggal DESC, jm.jam_ke ASC
";

$stmt = $con->prepare($sql);
if (!$stmt) {
    die("Query error: " . $con->error);
}
if ($types) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();


?>

<div class="page-inner">
    <div class="page-header">
        <h4 class="page-title">Rekap Jurnal Mengajar</h4>
        <ul class="breadcrumbs">
            <li class="nav-home"><a href="#"><i class="flaticon-home"></i></a></li>
            <li class="separator"><i class="flaticon-right-arrow"></i></li>
            <li class="nav-item"><a href="#">Rekap</a></li>
            <li class="separator"><i class="flaticon-right-arrow"></i></li>
            <li class="nav-item"><a href="#">Jurnal Mengajar</a></li>
        </ul>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <form method="GET" action="dashboard.php" class="row">
                        <input type="hidden" name="page" value="jurnal">
                        <div class="col-md-3">
                            <label>Tanggal Awal:</label>
                            <input type="date" name="tgl_awal" class="form-control" value="<?= htmlspecialchars($tgl_awal) ?>">
                        </div>
                        <div class="col-md-3">
                            <label>Tanggal Akhir:</label>
                            <input type="date" name="tgl_akhir" class="form-control" value="<?= htmlspecialchars($tgl_akhir) ?>">
                        </div>
                        <div class="col-md-2">
                            <label>Kelas:</label>
                            <select name="kelas" class="form-control">
                                <option value="">-- Semua Kelas --</option>
                                <?php foreach ($kelasList as $kelas): ?>
                                    <option value="<?= $kelas['id_mkelas'] ?>" <?= ($id_kelas == $kelas['id_mkelas']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($kelas['nama_kelas']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label>Guru:</label>
                            <select name="guru" class="form-control">
                                <option value="">-- Semua Guru --</option>
                                <?php foreach ($guruList as $guru): ?>
                                    <option value="<?= $guru['id_guru'] ?>" <?= ($id_guru == $guru['id_guru']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($guru['nama_guru']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100">Filter</button>
                        </div>
                    </form>
                </div>

                <div class="card-body">
                <form method="POST" action="?page=jurnal&act=bulk_delete_jurnal" id="form-jurnal">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr class="table-light">
                                    <th>
                                        Pilih Semua <br><input type="checkbox" id="selectAll">
                                    </th>
                                    <th>No</th>
                                    <th>Tanggal</th>
                                    <th>Jam Ke</th>
                                    <th>Kelas</th>
                                    <th>Mapel</th>
                                    <th>Uraian Kegiatan</th>
                                    <th>Catatan Perkembangan</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($result->num_rows > 0): ?>
                                    <?php $no = 1; ?>
                                    <?php while ($row = $result->fetch_assoc()): ?>
                                        <tr>
                                            <td>
                                                <input type="checkbox" name="selected_jurnal[]" value="<?= $row['id'] ?>">
                                            </td>
                                            <td><?= $no++ ?></td>
                                            <td><?= date('d/m/Y', strtotime($row['tanggal'])) ?></td>
                                            <td><?= htmlspecialchars($row['jam_ke']) ?></td>
                                            <td><?= htmlspecialchars($row['nama_kelas']) ?></td>
                                            <td><?= htmlspecialchars($row['mapel']) ?></td>
                                            <td><?= htmlspecialchars($row['uraian_kegiatan']) ?></td>
                                            <td><?= htmlspecialchars($row['catatan_perkembangan']) ?></td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="7" class="text-center">Data jurnal tidak ditemukan</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="text-end mt-3">
                        <a href="modul/master/jurnal/ekspor.php?tgl_awal=<?= $tgl_awal ?>&tgl_akhir=<?= $tgl_akhir ?>&kelas=<?= $id_kelas ?>&guru=<?= $id_guru ?>" class="btn btn-danger" target="_blank">
                            Export PDF
                        </a>
                        
                        <button class="btn btn-danger" id="btnHapusJurnal" type="submit">Hapus jurnal terpilih</button>    
                    </div>
                </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const selectAll = document.querySelector("#selectAll")
    const itemCheckBoxes = document.querySelectorAll('input[name="selected_jurnal[]"]')
    const btnHapus = document.querySelector("#btnHapusJurnal");
    const selectedJurnals = []
    
    
    selectAll.addEventListener('change', function() {
        itemCheckBoxes.forEach(checkbox => {
            checkbox.checked = selectAll.checked;
        });
    });
    
    itemCheckBoxes.forEach((checkbox) => {
        checkbox.addEventListener('change', function(e) {
            
            if(!checkbox.checked && selectAll.checked) {
                selectAll.checked = false
            } else {
                const allChecked = Array.from(itemCheckBoxes).every(cb => cb.checked);
                if (allChecked && selectAll) {
                    selectAll.checked = true;
                }
            }    
        })
        
    });
    
    // btnHapus.addEventListener("click", function() {
    //   itemCheckBoxes.forEach((cbox) => {
    //       if(cbox.checked) {
    //           console.log(cbox.value);
    //       } 
    //   });
    // });
    
    
});

</script>