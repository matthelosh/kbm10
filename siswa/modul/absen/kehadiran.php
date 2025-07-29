<?php
// mengambil id siswa
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$id_siswa = $data['id_siswa'];

$filter_bulan = $_GET['bulan'] ?? date('Y-m');
$filter_tanggal = $_GET['tanggal'] ?? date('Y-m-d');
$filter_mapel = $_GET['id_mengajar'] ?? '';
$where = "a.id_siswa='$id_siswa' AND DATE_FORMAT(tgl_absen, '%Y-%m') ='$filter_bulan'";
$nama_siswa = $data['nama_siswa'];

$query = mysqli_query($con, "SELECT 
		SUM(CASE WHEN ket='H' THEN 1 ELSE 0 END) AS hadir,
		SUM(CASE WHEN ket='I' THEN 1 ELSE 0 END) AS izin,
		SUM(CASE WHEN ket='S' THEN 1 ELSE 0 END) AS sakit,
		SUM(CASE WHEN ket='T' THEN 1 ELSE 0 END) AS terlambat,
		SUM(CASE WHEN ket='A' THEN 1 ELSE 0 END) AS alfa,
		SUM(CASE WHEN ket='C' THEN 1 ELSE 0 END) AS cabut
	FROM _logabsensi 
	WHERE id_siswa='$id_siswa' AND DATE_FORMAT(tgl_absen, '%Y-%m') = '$filter_bulan'
");
$data = mysqli_fetch_assoc($query);


?>

<div class="card">
	<div class="card-body">
		<h4 class="card-title">Kehadiran | <b style="text-transform: uppercase;"><code> <?= $nama_siswa?> </code></b></h4>
		<hr>
		<!-- Menampilkan data absensi sesuai bulan & tahun -->
		<div class="col-xl-12">
			<div class="card text-left">
				<div class="card-body">
					<div>
						<h5>
							<b>
								Rekap Presensi Perbulan
							</b>
						</h5>
					</div>
					<input type="month" id="bulanFilter" value="<?= $filter_bulan ?>" class="form-control" style="max-width: 200px;">
					<table cellpadding="5" width="100%">
						<tr>
							<td>Hadir</td>
							<td>:</td>
							<td>
								<?php
								echo $data['hadir'];
								?>
							</td>
						</tr>
						<tr>
							<td>Izin</td>
							<td>:</td>
							<td>
								<?php
								echo $data['izin'];
								?>
							</td>
						</tr>
						<tr>
							<td>Sakit</td>
							<td>:</td>
							<td>
								<?php
								echo $data['sakit'];
								?>
							</td>
						</tr>
						<tr>
							<td>Terlambat</td>
							<td>:</td>
							<td>
								<?php
								echo $data['terlambat'];
								?>
							</td>
						</tr>
						<tr>
							<td>Absen</td>
							<td>:</td>
							<td>
								<?php
								echo $data['alfa'];
								?>
							</td>
						</tr>
						<tr>
							<td>Cabut</td>
							<td>:</td>
							<td>
								<?php
								echo $data['cabut'];
								?>
							</td>
						</tr>
					</table>
				</div>
			</div>
		</div>
	</div>

	<div class="card-body">
		<div class="col-xl-12">
			<div class="card text-left">
				<div class="card-body">
					<div>
						<h5><b>Presensi Tiap Mapel</b></h5>
					</div>

					<input style="max-width: 200px;" type="date" id="tanggalFilter" name="tanggal" class="form-control mr-3" value="<?= htmlspecialchars($filter_tanggal) ?>" required>
					<!-- <select id="mapelFilter" name="id_mengajar" class="form-control mr-3">
						<option value="">-- Mata Pelajaran --</option> -->
					<?php
					// $mapelQuery = mysqli_query($con, "SELECT DISTINCT m.id_mengajar, mp.mapel 
					// 			FROM tb_mengajar m
					// 			JOIN tb_master_mapel mp ON m.id_mapel = mp.id_mapel
					// 			JOIN _logabsensi a ON a.id_mengajar = m.id_mengajar
					// 			WHERE a.id_siswa='$id_siswa'
					// 		");

					// while ($row = mysqli_fetch_array($mapelQuery)) {
					// 	$selected = ($filter_mapel == $row['id_mengajar']) ? 'selected' : '';
					// 	echo "<option value='{$row['id_mengajar']}' $selected>{$row['mapel']}</option>";
					// }
					?>
					</select>

					<table id="tabelPresensiMapel" class="table table-bordered mt-3">
						<thead class="thead-light">
							<tr>
								<th>Nama Siswa</th>
								<th>Mata Pelajaran</th>
								<th>Keterangan</th>
								<th>Tanggal</th>
							</tr>
						</thead>
						<tbody id="bodyPresensiMapel">
							<!-- AJAX content here -->
							<?php
							$tanggal = $_GET['tanggal'] ?? date('Y-m-d');
							$where = "a.id_siswa='$id_siswa' AND a.tgl_absen='$tanggal'";

							$query = mysqli_query($con, "SELECT s.nama_siswa, mp.mapel, a.ket, a.tgl_absen, g.id_guru, g.nama_guru
								FROM _logabsensi a
								JOIN tb_siswa s ON a.id_siswa = s.id_siswa
								JOIN tb_mengajar m ON a.id_mengajar = m.id_mengajar
								JOIN tb_master_mapel mp ON m.id_mapel = mp.id_mapel
								JOIN tb_guru g ON m.id_guru = g.id_guru
								WHERE $where
								ORDER BY a.tgl_absen ASC");

							$html = '';
							while ($row = mysqli_fetch_array($query)) {
								switch ($row['ket']) {
									case 'H':
										$keterangan = 'Hadir';
										break;
									case 'I':
										$keterangan = 'Izin';
										break;
									case 'S':
										$keterangan = 'Sakit';
										break;
									case 'T':
										$keterangan = 'Terlambat';
										break;
									case 'SA':
										$keterangan = 'Alpha';
										break;
									case 'C':
										$keterangan = 'Cabut';
										break;
									default:
										$keterangan = $row['ket'];
								}

								$html .= "<tr>
									<td>{$row['nama_siswa']}</td>
									<td><b>{$row['mapel']}</b> <br> <a>{$row['nama_guru']}</a></td>
									<td>{$keterangan}</td>
									<td>" . date('d-m-Y', strtotime($row['tgl_absen'])) . "</td>
								</tr>";
							}

							echo $html ?: '<tr><td colspan="4" class="text-center">Tidak ada data pada tanggal ini.</td></tr>';
							?>
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
</div>
</div>



<div class="mt-3">
	<a href="javascript:history.back()" class="btn btn-default btn-block"><i class="fas fa-arrow-circle-left"></i> Kembali</a>
</div>

<script>
	document.getElementById('bulanFilter').addEventListener('change', function() {
		const bulan = this.value;
		const urlParams = new URLSearchParams(window.location.search);
		urlParams.set('bulan', bulan);
		window.location.search = urlParams.toString();
	});
</script>
<script>
	document.getElementById('tanggalFilter').addEventListener('change', function() {
		const tanggal = this.value;
		const urlParams = new URLSearchParams(window.location.search);
		urlParams.set('tanggal', tanggal);
		window.location.search = urlParams.toString();
	});
</script>