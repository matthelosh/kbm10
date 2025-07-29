<?php
if (session_status() === PHP_SESSION_NONE) session_start();
include_once '../config/db.php';

// Pastikan session username tersedia
$nip = $_SESSION['username'] ?? '';

$nama_kelas = '';
$data = []; 

if ($nip != '') {
    // Ambil id_guru dari tb_guru berdasarkan NIP
    $queryGuru = mysqli_query($con, "SELECT id_guru, nama_guru FROM tb_guru WHERE nip='$nip'");
    $dataGuru = mysqli_fetch_assoc($queryGuru);
    $id_guru = $dataGuru['id_guru'] ?? '';
    $data['nama_guru'] = $dataGuru['nama_guru'] ?? '';

    if ($id_guru != '') {
        // Ambil id_mkelas dari tb_walikelas berdasarkan id_guru
        $queryWali = mysqli_query($con, "SELECT id_mkelas FROM tb_walikelas WHERE id_guru='$id_guru'");
        $dataWali = mysqli_fetch_assoc($queryWali);
        $id_mkelas = $dataWali['id_mkelas'] ?? '';

    if ($id_mkelas != '') {
            // Ambil nama kelas dari tb_mkelas
            $queryKelas = mysqli_query($con, "SELECT nama_kelas FROM tb_mkelas WHERE id_mkelas='$id_mkelas'");
            $dataKelas = mysqli_fetch_assoc($queryKelas);
            $nama_kelas = $dataKelas['nama_kelas'] ?? '';

            // Hitung jumlah siswa total
            $queryJumlahSiswa = mysqli_query($con, "SELECT COUNT(*) as total FROM tb_siswa WHERE id_mkelas='$id_mkelas'");
            $dataJumlahSiswa = mysqli_fetch_assoc($queryJumlahSiswa);
            $jumlahSiswa = $dataJumlahSiswa['total'] ?? 0;

            // Hitung jumlah siswa laki-laki
            $queryJumlahLaki = mysqli_query($con, "SELECT COUNT(*) as total FROM tb_siswa WHERE id_mkelas='$id_mkelas' AND jk='L'");
            $dataJumlahLaki = mysqli_fetch_assoc($queryJumlahLaki);
            $jumlahLaki = $dataJumlahLaki['total'] ?? 0;

            // Hitung jumlah siswa perempuan
            $queryJumlahPerempuan = mysqli_query($con, "SELECT COUNT(*) as total FROM tb_siswa WHERE id_mkelas='$id_mkelas' AND jk='P'");
            $dataJumlahPerempuan = mysqli_fetch_assoc($queryJumlahPerempuan);
            $jumlahPerempuan = $dataJumlahPerempuan['total'] ?? 0;
        } else {
            // Jika tidak ada kelas wali kelas
            $jumlahSiswa = $jumlahLaki = $jumlahPerempuan = 0;
        }
    } else {
        // Jika guru tidak ditemukan
        $jumlahSiswa = $jumlahLaki = $jumlahPerempuan = 0;
    }
} else {
    // Jika session kosong
    $jumlahSiswa = $jumlahLaki = $jumlahPerempuan = 0;
}

?>
<div class="panel-header bg-primary-gradient">
    <div class="page-inner py-5">
        <div class="d-flex align-items-left align-items-md-center flex-column flex-md-row">
            <div>
                <h2 class="text-white pb-2 fw-bold">Aplikasi Presensi <?= $nama_kelas ? $nama_kelas : '' ?></h2>
                <h5 class="text-white op-7 mb-2">Selamat Datang, <b class="text-warning"><?= htmlspecialchars($data['nama_guru'] ?? ''); ?></b></h5>
            </div>
        </div>
    </div>
</div>
<div class="page-inner mt--5">
    <div class="row mt--2">
        <div class="col-md-6">
            <div class="card full-height">
                <div class="card-body">
                    <div class="card-title text-center">
                        <img src="/assets/img/jatim.png" width="100" alt="VOCSTEN MALANG">
                    </div>
                    <div class="card-category text-center">
                        PEMERINTAH PROVINSI JAWA TIMUR
                        <br>DINAS PENDIDIKAN
                        <br><b>SMK NEGERI 10 MALANG </b>
                        <br>Jl. Raya Tlogowaru Kec. Kedungkandang Kota Malang
                        <br>Telp. (0341) 754086 E-mail : <a href="mailto:smkn10_malang@yahoo.co.id" style="color: blue;">smkn10_malang@yahoo.co.id</a>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    
                    <!-- Kotak Besar: Total Siswa -->
                    <div class="row mb-3">
                        <div class="col-12">
                            <div class="card card-stats card-secondary card-round">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-5">
                                            <div class="icon-big text-center">
                                                <i class="flaticon-users"></i>
                                            </div>
                                        </div>
                                        <div class="col-7 col-stats">
                                            <div class="numbers">
                                                <p class="card-category">Total Siswa</p>
                                                <h4 class="card-title"><?php echo $jumlahSiswa; ?></h4>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Dua Kotak Kecil: Laki-laki dan Perempuan -->
                    <div class="row">
                        <div class="col-sm-6">
                            <div class="card card-stats card-primary card-round">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-5">
                                            <div class="icon-big text-center">
                                                <i class="fas fa-male"></i>
                                            </div>
                                        </div>
                                        <div class="col-7 col-stats">
                                            <div class="numbers">
                                                <p class="card-category">Laki-laki</p>
                                                <h4 class="card-title"><?php echo $jumlahLaki; ?></h4>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-sm-6">
                            <div class="card card-stats card-pink card-round">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-5">
                                            <div class="icon-big text-center">
                                                <i class="fas fa-female"></i>
                                            </div>
                                        </div>
                                        <div class="col-7 col-stats">
                                            <div class="numbers">
                                                <p class="card-category">Perempuan</p>
                                                <h4 class="card-title"><?php echo $jumlahPerempuan; ?></h4>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>  
</div>