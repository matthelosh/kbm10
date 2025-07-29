<?php
if ($_GET['act'] == 'detail') {
  $id = $_GET['id'];
  $jadwal = mysqli_fetch_array(mysqli_query($con,"  
    SELECT * FROM tb_mengajar 
    LEFT JOIN tb_guru ON tb_mengajar.id_guru=tb_guru.id_guru
    LEFT JOIN tb_master_mapel ON tb_mengajar.id_mapel=tb_master_mapel.id_mapel
    LEFT JOIN tb_mkelas ON tb_mengajar.id_mkelas=tb_mkelas.id_mkelas
    LEFT JOIN tb_semester ON tb_mengajar.id_semester=tb_semester.id_semester
    LEFT JOIN tb_thajaran ON tb_mengajar.id_thajaran=tb_thajaran.id_thajaran
    WHERE tb_mengajar.id_mengajar='$id'
  "));
?>

<div class="page-inner">
  <div class="page-header">
    <h4 class="page-title">Detail Jadwal Mengajar</h4>
  </div>
  <div class="card">
    <div class="card-body">
      <table class="table table-bordered">
        <tr><th>Kode Pelajaran</th><td><?= $jadwal['kode_mapel'] ?></td></tr>
        <tr><th>Nama Guru</th><td><?= $jadwal['nama_guru'] ?></td></tr>
        <tr><th>Mata Pelajaran</th><td><?= $jadwal['mapel'] ?></td></tr>
        <tr><th>Kelas</th><td><?= $jadwal['nama_kelas'] ?></td></tr>
        <tr><th>Tahun Pelajaran</th><td><?= $jadwal['tahun_ajaran'] ?></td></tr>
        <tr><th>Semester</th><td><?= $jadwal['semester'] ?></td></tr>
        <tr><th>Hari</th><td><?= $jadwal['hari'] ?></td></tr>
        <tr><th>Waktu</th><td><?= $jadwal['jam_mengajar'] ?></td></tr>
        <tr><th>Jam Ke</th><td><?= $jadwal['jamke'] ?></td></tr>
        <tr><th>Ruang</th><td><?= $jadwal['ruang'] ?></td></tr> <!-- Tambahan -->
      </table>
      <a href="?page=jadwal" class="btn btn-secondary btn-sm"><i class="fa fa-arrow-left"></i> Kembali</a>
    </div>
  </div>
</div>

<?php } ?>