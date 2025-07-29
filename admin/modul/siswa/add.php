<?php
session_start();

// Mengecek apakah admin sudah login
if (!isset($_SESSION['admin'])) {
    echo "<script>alert('Session expired, please login again.'); window.location.href = 'login.php';</script>";
    exit;
}

require_once __DIR__ . '/../../../config/db.php';

?>

<div class="page-inner">
  <?php if (isset($_SESSION['import_message'])): ?>
  <script>alert("<?= addslashes($_SESSION['import_message']) ?>");</script>
  <?php unset($_SESSION['import_message']); ?>
  <?php endif; ?>

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
        <a href="#">Tambah Siswa</a>
      </li>
    </ul>
  </div>

  <div class="row">
    <div class="col-lg-12">
      <div class="card">
        <div class="card-header d-flex align-items-center">
          <h3 class="h4">Form Entry Siswa</h3>
        </div>
        <div class="card-body">
          <form action="?page=siswa&act=proses" method="post" enctype="multipart/form-data">
            <table cellpadding="3" style="font-weight: bold;">
              <tr>
                <td>Nama Peserta Didik </td>
                <td>:</td>
                <td><input type="text" class="form-control" name="nama" placeholder="Nama lengkap"></td>
              </tr>
              <tr>
                <td>NIS/NISN</td>
                <td>:</td>
                <td><input name="nis" type="text" class="form-control" placeholder="NIS/NISN"> </td>
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
                      echo "<option value='$kelas[id_mkelas]'>$kelas[nama_kelas]</option>";
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
                    <input type="checkbox" name="is_ketua" value="1">
                    <span class="slider round"></span>
                  </label>
                </td>
              </tr>

              <tr>
                <td>Tahun Masuk</td>
                <td>:</td>
                <td><input name="th_angkatan" type="number" class="form-control" placeholder="2019"></td>
              </tr>
              <tr>
                <td>Pas Foto</td>
                <td>:</td>
                <td><input type="file" class="form-control" name="foto"></td>
              </tr>
              <tr>
                <td colspan="3">
                  <button name="saveSiswa" type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Simpan</button>
                  <!-- Tombol Upload Excel -->
                  <button type="button" class="btn btn-success" data-toggle="modal" data-target="#uploadExcelModal">
                    <i class="fa fa-file-excel-o"></i> Upload Excel
                  </button>
                  <a href="modul/siswa/data_siswa.xlsx" class="btn btn-info" download>
                    <i class="fa fa-download"></i> Contoh File
                  </a>
                  <a href="javascript:history.back()" class="btn btn-warning"><i class="fa fa-chevron-left"></i> Batal</a>
                </td>
              </tr>
            </table>
          </form>

          <!-- Modal Upload Excel -->
          <div class="modal fade" id="uploadExcelModal" tabindex="-1" role="dialog" aria-labelledby="uploadExcelModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
              <form id="formUploadExcel" enctype="multipart/form-data">
                <div class="modal-content">
                  <div class="modal-header">
                    <h5 class="modal-title" id="uploadExcelModalLabel">Upload Data Siswa via Excel</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Tutup">
                      <span aria-hidden="true">&times;</span>
                    </button>
                  </div>
                  <div class="modal-body">
                    <div class="form-group">
                      <label for="excelFile">Pilih File Excel (.xls, .xlsx)</label>
                      <input type="file" name="excel_file" class="form-control" accept=".xls,.xlsx" required>
                    </div>
                  </div>
                  <div class="modal-footer">
                    <button type="submit" class="btn btn-success"><i class="fa fa-upload"></i> Upload</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                  </div>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- jQuery (wajib) -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- SweetAlert -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$('#formUploadExcel').on('submit', function(e) {
  e.preventDefault(); // Mencegah form submit secara default
  var formData = new FormData(this); // Menyertakan data form dalam request

  $.ajax({
    url: 'modul/siswa/upload_excel_siswa.php', // Pastikan URL sudah benar
    type: 'POST',
    data: formData,
    contentType: false,
    processData: false,
    success: function(res) {
      try {
        const json = (typeof res === 'object') ? res : JSON.parse(res); // Parsing respons JSON
        
        Swal.fire({
          icon: json.success ? 'success' : 'error',
          title: 'Hasil Import',
          html: `<pre style="text-align:left;font-size:14px;">${json.message}</pre>`
        }).then(() => {
          if (json.success) {
            window.location.href = '?page=siswa'; // Arahkan kembali ke halaman siswa
          }
        });
      } catch (e) {
        Swal.fire('Gagal', 'Respon tidak valid:\n' + e.message, 'error');
      }
    },
    error: function(xhr, status, error) {
      Swal.fire('Gagal', 'Upload gagal karena kesalahan jaringan atau server. Error: ' + error, 'error');
    }
  });
});
</script>