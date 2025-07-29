<?php
$filter_bulan = $_GET['filter_bulan'] ?? date('Y-m');
$filter_guru = $_GET['filter_guru'] ?? '';

// Ambil daftar semua guru untuk dropdown filter
$guruList = mysqli_query($con, "SELECT id_guru, nama_guru FROM tb_guru ORDER BY nama_guru ASC");

// Query utama rekap absensi guru
$sql = "SELECT lag.id_absguru id_lag, g.id_guru, g.nip, g.nama_guru, mp.mapel, k.nama_kelas, lag.tanggal, lag.ket, lag.foto
        FROM _logabsenguru lag
        INNER JOIN tb_guru g ON lag.id_guru = g.id_guru
        LEFT JOIN tb_mengajar m ON lag.id_mengajar = m.id_mengajar
        LEFT JOIN tb_master_mapel mp ON m.id_mapel = mp.id_mapel
        LEFT JOIN tb_mkelas k ON m.id_mkelas = k.id_mkelas
        WHERE DATE_FORMAT(lag.tanggal, '%Y-%m') = '" . mysqli_real_escape_string($con, $filter_bulan) . "'
        AND lag.ket != ''";

if (!empty($filter_guru)) {
    $sql .= " AND g.id_guru = '" . mysqli_real_escape_string($con, $filter_guru) . "'";
}

$sql .= " ORDER BY g.id_guru ASC, lag.tanggal ASC";

$queryRekapSemuaGuru = mysqli_query($con, $sql);
?>

<div class="page-inner">
    <div class="page-header">
        <h4 class="page-title">Guru</h4>
        <ul class="breadcrumbs">
            <li class="nav-home"><a href="#"><i class="flaticon-home"></i></a></li>
            <li class="separator"><i class="flaticon-right-arrow"></i></li>
            <li class="nav-item"><a href="#">Data Guru</a></li>
            <li class="separator"><i class="flaticon-right-arrow"></i></li>
            <li class="nav-item"><a href="#">Rekap Absensi Guru</a></li>
        </ul>
    </div>

    <div class="row">
        <div class="col-md-12 col-xs-12">
            <div class="card">
                <div class="card-body">

                    <!-- Form Filter Bulan dan Guru -->
                    <form method="GET" action="" class="mb-3 form-inline">
                        <input type="hidden" name="page" value="guru">
                        <input type="hidden" name="act" value="rekap">

                        <label for="filter_bulan" class="mr-2">Pilih Bulan:</label>
                        <input type="month" id="filter_bulan" name="filter_bulan" class="form-control mr-3"
                            value="<?= htmlspecialchars($filter_bulan) ?>">

                        <label for="filter_guru" class="mr-2">Pilih Guru:</label>
                        <select name="filter_guru" id="filter_guru" class="form-control mr-3">
                            <option value="">Semua Guru</option>
                            <?php while ($guru = mysqli_fetch_assoc($guruList)) : ?>
                                <option value="<?= $guru['id_guru'] ?>" <?= ($filter_guru == $guru['id_guru']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($guru['nama_guru']) ?>
                                </option>
                            <?php endwhile; ?>
                        </select>

                        <button type="submit" class="btn btn-primary btn-sm">Filter</button>
                    </form>

                    <h4 class="page-title">Rekap Absensi Semua Guru - Bulan <?= date('F Y', strtotime($filter_bulan . '-01')) ?></h4>
                    <form id="formRekapLag" action="/admin/dashboard.php?page=guru&act=delete_lag" method="POST">
                    <table class="table table-bordered table-striped table-sm">
                        <thead class="text-center">
                            <tr>
                                <th>
                                    <label for="selectAll">Pilih Semua
                                    <input type="checkbox" name="selectAll"
                                    </label>
                                </th>
                                <th>No</th>
                                <th>NIP/NITK</th>
                                <th>Nama Guru</th>
                                <th>Mengajar</th>
                                <th>Tanggal</th>
                                <th>Keterangan</th>
                                <th>Foto</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $no = 1;
                            while ($data = mysqli_fetch_assoc($queryRekapSemuaGuru)) :
                                $status = match ($data['ket']) {
                                    'H' => 'Hadir',
                                    'I' => 'Izin',
                                    'S' => 'Sakit',
                                    'C' => 'Cuti',
                                    'D' => 'Dinas Luar',
                                    'P' => 'Penugasan',
                                    'K' => 'Kosong',
                                    default => 'Tidak Diketahui'
                                };
                            ?>
                                <tr>
                                    <td class="text-center">
                                        <input type="checkbox" name="selectedAbsen[]" value="<?=$data['id_lag'];?>" />
                                    </td>
                                    <td class="text-center"><?= $no++ ?></td>
                                    <td><?= htmlspecialchars($data['nip']) ?></td>
                                    <td><?= htmlspecialchars($data['nama_guru']) ?></td>
                                    <td><?= htmlspecialchars($data['mapel'] ?? '-') ?> - <?= htmlspecialchars($data['nama_kelas'] ?? '-') ?></td>
                                    <td class="text-center"><?= date('d-m-Y', strtotime($data['tanggal'])) ?></td>
                                    <td class="text-center"><?= $status ?></td>
                                    <td class="text-center">
                                        <?php if (!empty($data['foto'])) : ?>
                                            <a href="//assets/foto_presensi_guru/<?= htmlspecialchars($data['foto']) ?>" target="_blank" title="Foto Presensi">
                                                <img src="//assets/foto_presensi_guru/<?= $data['foto'] ?>" alt="Foto" width="50" height="50" style="object-fit:cover; border-radius:4px;">
                                            </a>
                                        <?php else : ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                            <?php if (mysqli_num_rows($queryRekapSemuaGuru) == 0) : ?>
                                <tr>
                                    <td colspan="7" class="text-center alert alert-warning">Tidak ada data absensi ditemukan bulan ini.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>

                    <!-- Tombol Download -->
                    <div class="mt-3 d-flex justify-between">
                        <button class="btn btn-danger mr-2" id="btn_hapus">Hapus terpilih</button>
                        <a href="/admin/modul/guru/export_excel_rekap.php?bulan=<?= urlencode($filter_bulan) ?>&guru=<?= urlencode($filter_guru) ?>" 
                           class="btn btn-success ">
                            <i class="fa fa-file-excel-o"></i> Download Data Guru Excel
                        </a>
                    </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
   const checkAll = document.querySelector("input[name='selectAll']");
   const checkItems = document.querySelectorAll("input[name='selectedAbsen[]'")
   
   const btnHapus = document.querySelector("#btn_hapus")
   
   checkAll.addEventListener("change", function() {
       checkItems.forEach((checkbox) => checkbox.checked = checkAll.checked)
   })
   
   checkItems.forEach(check => {
       check.addEventListener("change", function() {
           if (!check.checked && checkAll.checked) {
               checkAll.checked = false
           } else {
               const allChecked = Array.from(checkItems).every(cb => cb.checked)
               if (allChecked && checkAll) {
                   checkAll.checked = true
               }
           }
       })
   })
   
//   btnHapus.addEventListener("click", function() {
//       const selectedAbsen = []
//       checkItems.forEach(check => {
//           if (check.checked) selectedAbsen.push(check.value)
//       })
       
//       console.log(selectedAbsen)
//       const formData = new FormData()
//       formData.append("id_lags", selectedAbsen)
//       fetch("?act=delete_lag", {
//           method: "POST",
//           body: formData
//       })
//       .then(response => response.json())
//       .then(data => {console.log('Success', data)})
//       .catch(error => console.log('Error:', error));
       
//   })
   
   
   
});
</script>
