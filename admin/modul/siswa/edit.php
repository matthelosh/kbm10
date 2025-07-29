<?php
	$edit = mysqli_query($con, "SELECT * FROM tb_siswa WHERE id_siswa='$_GET[id]' ");
	foreach ($edit as $d) ?>
	<div class="page-inner">
		<div class="page-header">
			<h4 class="page-title">Siswa</h4>
			<ul class="breadcrumbs">
				<li class="nav-home">
					<a href="#">
						<i class="flaticon-home"></i>
					</a>
				</li>
				<li class="separator">
					<i class="flaticon-right-arrow"></i>
				</li>
				<li class="nav-item">
					<a href="#">Data Siswa</a>
				</li>
				<li class="separator">
					<i class="flaticon-right-arrow"></i>
				</li>
				<li class="nav-item">
					<a href="#">Edit Siswa</a>
				</li>
			</ul>
		</div>
		<div class="row">
			<div class="col-lg-8">
				<div class="card">
					<div class="card-header d-flex align-items-center">
						<h3 class="h4">Edit Siswa</h3>
					</div>
					<div class="card-body">
						<form action="?page=siswa&act=proses&id=<?= $d['id_siswa'] ?>" method="post" enctype="multipart/form-data">
							<input name="id" type="hidden" value="<?= $d['id_siswa'] ?>">

							<table cellpadding="3" style="font-weight: bold;">
							    <?php
							        echo $d['id_mkelas'];
							        ?>
								<tr>
									<td>Nama Peserta Didik </td>
									<td>:</td>
									<td><input type="text" class="form-control" name="nama" value="<?= $d['nama_siswa'] ?>"></td>
								</tr>
								<tr>
									<td>NIS</td>
									<td>:</td>
									<td><input name="nis" type="text" class="form-control" value="<?= $d['nis'] ?>"> </td>
								</tr>
								<tr>
									<td>Jenis Kelamin </td>
									<td>:</td>
									<td>
										<select name="jk" class="form-control">
											<option value="L">Laki-laki</option>
											<option value="P">Perempuan</option>
										</select>
									</td>
								</tr>

								<tr>
									<td>Kelas Siswa</td>
									<td>:</td>
									<td>
										<select class="form-control" name="kelas">
											<option>Pilih Kelas</option>
						
								           <?php
                                                $sqlKelas = mysqli_query($con, "SELECT * FROM tb_mkelas ORDER BY id_mkelas ASC");
                                             while ($kelas = mysqli_fetch_array($sqlKelas)) { 
                                            $selected = $d['id_mkelas'] == $kelas['id_mkelas'] ? 'selected' : '';
                                            ?>
                        
                       
                                            <option value="<?=$kelas['id_mkelas'];?>" <?=$selected;?>><?=$kelas['nama_kelas'];?></option>
                                            <?php
                                                }
                                            ?>
										</select>
									</td>
								</tr>

								<tr>
									<td>Ketua Kelas</td>
									<td>:</td>
									<td>
										<input type="hidden" name="is_ketua" value="0">
										<label class="switch switch-space mt-2 mb-2">
											<input type="checkbox" name="is_ketua" value="1" <?= ($d['is_ketua'] == '1') ? 'checked' : '' ?> >
											<span class="slider round"></span>
										</label>
									</td>
								</tr>

								<tr>
									<td>Tahun Masuk</td>
									<td>:</td>
									<td><input name="th_angkatan" type="number" class="form-control" value="<?= $d['th_angkatan'] ?>"></td>
								</tr>
								<tr>
									<td>Pas Foto</td>
									<td>:</td>
									<td><input type="file" class="form-control" name="foto"></td>
								</tr>

								<tr>
									<td>Status</td>
									<td>:</td>
									<td>
										<input type="hidden" name="status" value="0">
										<label class="switch switch-space mt-2 mb-2">
											<input type="checkbox" id="statusCheckbox" name="status" value="1" <?= ($d['status'] == '1') ? 'checked' : '' ?> onchange="updateStatusText()">
											<span class="slider round"></span>
										</label>
										<span id="statusText" class="ml-2"><?= ($d['status'] == '1') ? 'Aktif' : 'Tidak Aktif' ?></span>
									</td>
								</tr>

								<tr>
									<td colspan="3">
										<button name="editSiswa" type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Update</button>
										<a href="javascript:history.back()" class="btn btn-warning"><i class="fa fa-chevron-left"></i> Batal</a>
									</td>
								</tr>
							</table>
						</form>
					</div>
				</div>
			</div>
		</div>
	</div>

	<script>
		function updateStatusText() {
			const checkbox = document.getElementById('statusCheckbox');
			const text = document.getElementById('statusText');
			text.textContent = checkbox.checked ? 'Aktif' : 'Tidak Aktif';
		}
	</script>