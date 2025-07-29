<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../../../config/db.php';

if (!isset($_SESSION['guru'])) {
    echo "<script>alert('Anda harus login terlebih dahulu'); window.location='../../user.php';</script>";
    exit;
}

$id_guru = $_SESSION['guru'];

$tgl_awal = $_GET['tgl_awal'] ?? '';
$tgl_akhir = $_GET['tgl_akhir'] ?? '';
$id_kelas = $_GET['kelas'] ?? '';

$where = "WHERE jm.id_guru = ?";
$params = [$id_guru];
$types = "s";

if (!empty($tgl_awal) && !empty($tgl_akhir)) {
    $where .= " AND jm.tanggal BETWEEN ? AND ?";
    $params[] = $tgl_awal;
    $params[] = $tgl_akhir;
    $types .= "ss";
}

if (!empty($id_kelas)) {
    $where .= " AND jm.id_kelas = ?";
    $params[] = $id_kelas;
    $types .= "s";
}

$query = "
    SELECT jm.*, mk.nama_kelas 
    FROM jurnal_mengajar jm
    JOIN tb_mkelas mk ON jm.id_kelas = mk.id_mkelas
    $where
    ORDER BY jm.tanggal DESC, jm.jam_ke ASC
";

$stmt = $con->prepare($query);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();
?>

<div class="page-inner">
    <div class="page-header">
        <h4 class="page-title">Rekap Jurnal Mengajar</h4>
        <ul class="breadcrumbs">
            <li class="nav-home"><a href="#"><i class="flaticon-home"></i></a></li>
            <li class="separator"><i class="flaticon-right-arrow"></i></li>
            <li class="nav-item"><a href="#">Jurnal</a></li>
            <li class="separator"><i class="flaticon-right-arrow"></i></li>
            <li class="nav-item"><a href="#">Rekap</a></li>
        </ul>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="GET" class="form-inline mb-4">
                <input type="hidden" name="page" value="rekap_jurnal">

                <label class="mr-2">Tanggal Awal:</label>
                <input type="date" name="tgl_awal" class="form-control mr-3" value="<?= htmlspecialchars($tgl_awal) ?>">

                <label class="mr-2">Tanggal Akhir:</label>
                <input type="date" name="tgl_akhir" class="form-control mr-3" value="<?= htmlspecialchars($tgl_akhir) ?>">

                <label class="mr-2">Kelas:</label>
                <select name="kelas" class="form-control mr-3">
                    <option value="">-- Semua Kelas --</option>
                    <?php
                    $kelas_stmt = $con->prepare("
                        SELECT mk.id_mkelas, mk.nama_kelas 
                        FROM tb_mengajar m
                        JOIN tb_mkelas mk ON m.id_mkelas = mk.id_mkelas
                        WHERE m.id_guru = ?
                        GROUP BY mk.id_mkelas
                    ");
                    $kelas_stmt->bind_param("s", $id_guru);
                    $kelas_stmt->execute();
                    $kelas_result = $kelas_stmt->get_result();
                    while ($kls = $kelas_result->fetch_assoc()): ?>
                        <option value="<?= $kls['id_mkelas'] ?>" <?= $id_kelas == $kls['id_mkelas'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($kls['nama_kelas']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>

                <button type="submit" class="btn btn-primary">Tampilkan</button>
                
                <?php if ($result->num_rows > 0): ?>
                    <a href="modul/rekap_jurnal/download_pdf.php?tgl_awal=<?= urlencode($tgl_awal) ?>&tgl_akhir=<?= urlencode($tgl_akhir) ?>&kelas=<?= urlencode($id_kelas) ?>"
                        class="btn btn-danger ml-2" target="_blank">
                        <i class="fas fa-file-pdf"></i> Download PDF
                    </a>
                <?php endif; ?>
            </form>

            <form method="POST" action="modul/rekap_jurnal/del.php">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered">
                        <thead class="bg-secondary text-white">
                            <tr>
                                <th><input type="checkbox" id="select_all"></th>
                                <th>No</th>
                                <th>Tanggal</th>
                                <th>Jam Ke</th>
                                <th>Kelas</th>
                                <th>Mata Pelajaran</th>
                                <th>Uraian Kegiatan</th>
                                <th>Catatan Perkembangan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result->num_rows > 0): 
                                $no = 1;
                                while ($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><input type="checkbox" name="jurnal_ids[]" value="<?= $row['id'] ?>"></td>
                                    <td><strong><?= $no++ ?>.</strong></td>
                                    <td><?= date('d/m/Y', strtotime($row['tanggal'])) ?></td>
                                    <td><?= htmlspecialchars($row['jam_ke']) ?></td>
                                    <td><?= htmlspecialchars($row['nama_kelas']) ?></td>
                                    <td><?= htmlspecialchars($row['mapel']) ?></td>
                                    <td><?= htmlspecialchars($row['uraian_kegiatan']) ?></td>
                                    <td><?= htmlspecialchars($row['catatan_perkembangan']) ?></td>
                                </tr>
                            <?php endwhile; else: ?>
                                <tr>
                                    <td colspan="8" class="text-center">Tidak ada data jurnal ditemukan.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <?php if ($result->num_rows > 0): ?>
                    <button type="submit" name="hapus_terpilih" class="btn btn-danger mt-2"
                            onclick="return confirm('Hapus semua jurnal terpilih?')">
                        🗑 Hapus Terpilih
                    </button>
                <?php endif; ?>
            </form>
        </div>
    </div>
</div>

<script>
    document.getElementById('select_all').onclick = function() {
        const checkboxes = document.querySelectorAll('input[name="jurnal_ids[]"]');
        checkboxes.forEach(cb => cb.checked = this.checked);
    }
</script>