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
        <a href="#">Tambah Siswa</a>
      </li>
    </ul>
  </div>
  <div class="row">
    <div class="col-lg-12">
      <div class="card">
        <div class="card-header d-flex align-items-center justify-content-between">
          <h3 class="h4">Form Entry Siswa</h3>
          <form method="GET" action="<?php echo $_SERVER['PHP_SELF']; ?>">
          <input type="hidden" name="page" value="siswa"> <!-- biar gak hilang page -->
          <div class="form-group mb-0">
            <label for="filter_kelas">Filter Kelas:</label>
            <select name="filter_kelas" id="filter_kelas" class="form-control" onchange="this.form.submit()">
              <option value="">-- Semua Kelas --</option>
              <?php
              $kelasList = mysqli_query($con, "SELECT * FROM tb_mkelas ORDER BY nama_kelas ASC");
              while ($kls = mysqli_fetch_array($kelasList)) {
                $selected = (isset($_GET['filter_kelas']) && $_GET['filter_kelas'] == $kls['id_mkelas']) ? 'selected' : '';
                echo "<option value='{$kls['id_mkelas']}' $selected>{$kls['nama_kelas']}</option>";
              }
              ?>
            </select>
          </div>
           </form>
        </div>
        <div class="card-body">
          <form action="?page=siswa&act=bulk_delete" method="POST" id="formSiswa">
            <table id="basic-datatables" class="display table table-striped table-hover">
              <thead>
                <tr>
                  <th>Pilih Semua <br><input type="checkbox" id="selectAll"> </th>
                  <th>#</th>
                  <th>NIS/NISN</th>
                  <th>Nama Siswa</th>
                  <th>Kelas</th>
                  <th>Tahun Masuk</th>
                  <th>Ketua kelas</th>
                  <th>Status</th>
                  <th>Foto</th>
                  <th>Opsi</th>
                </tr>
              </thead>
              <tbody>
                <?php
                $no = 1;
                $filter_kelas = isset($_GET['filter_kelas']) ? intval($_GET['filter_kelas']) : 0;
                $query = "SELECT * FROM tb_siswa 
                          INNER JOIN tb_mkelas ON tb_siswa.id_mkelas = tb_mkelas.id_mkelas";
                if ($filter_kelas > 0) {
                    $query .= " WHERE tb_siswa.id_mkelas = '$filter_kelas'";
                }
                $query .= " ORDER BY tb_siswa.nama_siswa ASC";
                $siswa = mysqli_query($con, $query);
                foreach ($siswa as $g) { ?>
                <tr>
                  <td><input type="checkbox" name="selected_siswa[]" value="<?= $g['id_siswa'] ?>"></td>
                  <td><?= $no++; ?>.</td>
                  <td><?= $g['nis']; ?></td>
                  <td><?= $g['nama_siswa']; ?></td>
                  <td><?= $g['nama_kelas']; ?></td>
                  <td><?= $g['th_angkatan']; ?></td>
                  <td>
                    <?php if ($g['is_ketua'] == 1) {
                        echo "<span class='badge badge-info'>Ketua</span>";
                    } else {
                        echo "<span class='badge badge-warning'>Bukan</span>";
                    } ?>
                  </td>
                  <td>
                    <?php if ($g['status'] == 1) {
                        echo "<span class='badge badge-success'>Aktif</span>";
                    } else {
                        echo "<span class='badge badge-danger'>Off</span>";
                    } ?>
                  </td>
                  <td><img src="/assets/img/user/<?= $g['foto'] ?>" alt="tidak ada foto" width="45" height="45"></td>
                  <td>
                    <a class="btn btn-danger btn-sm" onclick="return confirm('Yakin Hapus Data ??')" href="?page=siswa&act=del&id=<?= $g['id_siswa'] ?>"><i class="fas fa-trash"></i></a>
                    <a class="btn btn-info btn-sm" href="?page=siswa&act=edit&id=<?= $g['id_siswa'] ?>"><i class="far fa-edit"></i></a>
                  </td>
                </tr>
                <?php } ?>
              </tbody>
            </table>
            <button type="submit" class="btn btn-danger mt-3" onclick="return confirm('Yakin ingin menghapus data yang dipilih?')">Hapus yang Dipilih</button>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
// Script untuk Select All
document.addEventListener('DOMContentLoaded', function() {
    const selectAllCheckbox = document.getElementById('selectAll');
    const studentCheckboxes = document.querySelectorAll('input[name="selected_siswa[]"]');
    const formSiswa = document.getElementById('formSiswa');

    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            studentCheckboxes.forEach(function(checkbox) {
                checkbox.checked = selectAllCheckbox.checked;
            });
        });
    }

    studentCheckboxes.forEach(function(checkbox) {
        checkbox.addEventListener('change', function() {
            if (!this.checked && selectAllCheckbox) {
                selectAllCheckbox.checked = false;
            } else {
                const allChecked = Array.from(studentCheckboxes).every(cb => cb.checked);
                if (allChecked && selectAllCheckbox) {
                    selectAllCheckbox.checked = true;
                }
            }
        });
    });

    formSiswa.addEventListener('submit', function(event) {
        const checkedCount = document.querySelectorAll('input[name="selected_siswa[]"]:checked').length;
        if (checkedCount === 0) {
            alert('Silakan pilih setidaknya satu data siswa untuk dihapus.');
            event.preventDefault();
        }
    });
});
</script>
