<?php 
$idKelas = $_GET['kelas'] ?? null;
$idMengajar = $_GET['pelajaran'] ?? null;

// Ambil data mengajar untuk kelas ini
$kelasMengajar = mysqli_query($con,"SELECT * FROM tb_mengajar 
	LEFT JOIN tb_master_mapel ON tb_mengajar.id_mapel = tb_master_mapel.id_mapel
	LEFT JOIN tb_mkelas ON tb_mengajar.id_mkelas = tb_mkelas.id_mkelas
	LEFT JOIN tb_guru ON tb_mengajar.id_guru = tb_guru.id_guru
	LEFT JOIN tb_semester ON tb_mengajar.id_semester = tb_semester.id_semester
	LEFT JOIN tb_thajaran ON tb_mengajar.id_thajaran = tb_thajaran.id_thajaran
	WHERE tb_mengajar.id_mkelas = '$idKelas' 
	AND tb_thajaran.status = 1 
	-- AND tb_semester.id_semester = 1
	-- AND tb_semester.status = 1
");

$d = mysqli_fetch_array(mysqli_query($con, "SELECT * FROM tb_mkelas WHERE id_mkelas = '$idKelas'"));
?>

<div class="page-inner">
	<div class="page-header">
		<h4 class="page-title">Rekap Absen</h4> 
		<ul class="breadcrumbs">
			<li class="nav-home">
				<a href="#"><i class="flaticon-home"></i></a>
			</li>
			<li class="separator"><i class="flaticon-right-arrow"></i></li>
			<li class="nav-item">
				<a href="#" class="font-weight-bold">KELAS <?=strtoupper($d['nama_kelas']) ?></a>
			</li>
		</ul>
	</div>

	<div class="row">
		<div class="col-md-12 col-xs-12">	
			<div class="card">
				<div class="card-body">
					<table class="table table-head-bg-danger table-xs">
						<thead>
							<tr>
								<th>No.</th>
								<th>Kode Pelajaran</th>
								<th>Mata Pelajaran</th>
								<th>Jam Pelajaran</th>
								<th>Rekap</th>
							</tr>
						</thead>
						<tbody>
							<?php if($kelasMengajar&& mysqli_num_rows($kelasMengajar) > 0):?>
							<?php 
							$no=1;
							foreach ($kelasMengajar as $mp) { ?>
							<tr>
								<td><?= $no++; ?>.</td>
								<td><?= $mp['kode_pelajaran']; ?></td>
								
								<td>
									<b><?= $mp['mapel']; ?></b><br>
									<code><?= $mp['nama_guru']; ?></code>
								</td>
								<td><?= $mp['hari']; ?> ( <?= $mp['jam_mengajar']; ?>)</td>
								<td>
									<a href="?page=rekap&act=rekap-perbulan&kelas=<?= $idKelas ?>&pelajaran=<?= $mp['id_mengajar'] ?>" class="btn btn-sm btn-primary">
										<i class="fas fa-eye"></i> Lihat Absen
									</a>
								</td>
							</tr>
							<?php } ?>
							<?php else: ?>
							<tr>
								<td colspan="7" class="text-center">
									<div class="mt-3 alert alert-danger" role="alert">
										Data Mata Pelajaran tidak ada
									</div>
								</td>
							</tr>
							<?php endif;?>
						</tbody>
					</table>
				</div>	
			</div>
		</div>
	</div>

	<?php if($idMengajar): ?>
	<?php 
		// Ambil data siswa dan absensi
		$lsiswa = mysqli_query($con, "SELECT s.id_siswa, s.nis, s.nama_siswa, 
			(SELECT COUNT(*) FROM _logabsensi WHERE id_siswa=s.id_siswa AND ket='H' AND id_mengajar='$idMengajar') AS hadir,
			(SELECT COUNT(*) FROM _logabsensi WHERE id_siswa=s.id_siswa AND ket='I' AND id_mengajar='$idMengajar') AS izin,
			(SELECT COUNT(*) FROM _logabsensi WHERE id_siswa=s.id_siswa AND ket='S' AND id_mengajar='$idMengajar') AS sakit,
			(SELECT COUNT(*) FROM _logabsensi WHERE id_siswa=s.id_siswa AND ket='A' AND id_mengajar='$idMengajar') AS alpa
			FROM tb_siswa s 
			WHERE s.id_mkelas = '$idKelas'
			AND s.status=1
			ORDER BY s.nama_siswa ASC"
		);
		
		if (!$lsiswa) {
			die('Query Error : '.mysqli_errno($con).' - '.mysqli_error($con));
		}
	?>

	<div class="row mt-4">
		<div class="col-md-12">
			<div class="card">
				<div class="card-header bg-info text-white">
					<?php 
						$qMapel = mysqli_query($con, "SELECT tb_master_mapel.mapel 
							FROM tb_mengajar 
							JOIN tb_master_mapel ON tb_mengajar.id_mapel = tb_master_mapel.id_mapel 
							WHERE tb_mengajar.id_mengajar = '$idMengajar' 
						");

						$rowMapel = mysqli_fetch_assoc($qMapel);
						$namaMapel = $rowMapel['mapel'] ?? 'Tidak Diketahui';
					?>
					<h4 class="mb-0">Rekap Absensi Siswa - Mata Pelajaran <?= htmlspecialchars($namaMapel) ?></h4>
				</div>
				<div class="card-body">
					<table class="table table-bordered table-striped table-sm">
						<thead>
							<tr class="text-center">
								<th>No</th>
								<th>NIS</th>
								<th>Nama Siswa</th>
								<th>Hadir</th>
								<th>Sakit</th>
								<th>Izin</th>
								<th>Alpa</th>
							</tr>
						</thead>
						<tbody>
							<?php
								if ($lsiswa && mysqli_num_rows($lsiswa) > 0):
							?>
							<?php 
							$no=1;
							while($s = mysqli_fetch_assoc($lsiswa)): ?>
							<tr>
								<td class="text-center"><?= $no++ ?>.</td>
								<td><?= $s['nis'] ?></td>
								<td><?= $s['nama_siswa'] ?></td>
								<td class="text-center text-success"><?= $s['hadir'] ?></td>
								<td class="text-center text-warning"><?= $s['sakit'] ?></td>
								<td class="text-center text-info"><?= $s['izin'] ?></td>
								<td class="text-center text-danger"><?= $s['alpa'] ?></td>
							</tr>
							<?php endwhile; ?>
							<?php else: ?>
							<tr>
								<td colspan="7" class="text-center">
									<div class="mt-3 alert alert-danger" role="alert">
										Data Siswa tidak ada
									</div>
								</td>
							</tr>
							<?php endif; ?>
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
	<?php endif; ?>
</div>