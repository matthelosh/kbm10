<div class="panel-header bg-primary-gradient">
    <div class="page-inner py-5">
        <div class="d-flex align-items-left align-items-md-center flex-column flex-md-row justify-content-between">
            <div>
                <h2 class="text-white pb-2 fw-bold">Aplikasi Jurnal Guru dan Presensi Siswa</h2>
                <h5 class="text-white op-7 mb-2">
                    Selamat Datang, <b class="text-warning"><?= htmlspecialchars($data['nama_kepsek'] ?? ''); ?></b>
                </h5>
            </div>
        </div>
    </div>
</div>

<div class="page-inner mt--5">
    <div class="row mt--2">
        <!-- Kartu Statistik -->
        <div class="col-md-4">
            <div class="card card-stats card-round">
                <div class="card-body d-flex align-items-center">
                    <div class="col-icon">
                        <div class="icon-big text-center icon-info bubble-shadow-small">
                            <i class="fas fa-users"></i>
                        </div>
                    </div>
                    <div class="col ml-3">
                        <p class="card-category mb-0">Jumlah Siswa</p>
                        <h4 class="card-title">
                            <?php
                            $siswa = mysqli_num_rows(mysqli_query($con, "SELECT * FROM tb_siswa"));
                            echo $siswa;
                            ?>
                        </h4>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card card-stats card-round">
                <div class="card-body d-flex align-items-center">
                    <div class="col-icon">
                        <div class="icon-big text-center icon-success bubble-shadow-small">
                            <i class="fas fa-chalkboard-teacher"></i>
                        </div>
                    </div>
                    <div class="col ml-3">
                        <p class="card-category mb-0">Jumlah Guru</p>
                        <h4 class="card-title">
                            <?php
                            $guru = mysqli_num_rows(mysqli_query($con, "SELECT * FROM tb_guru"));
                            echo $guru;
                            ?>
                        </h4>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card card-stats card-round">
                <div class="card-body d-flex align-items-center">
                    <div class="col-icon">
                        <div class="icon-big text-center icon-warning bubble-shadow-small">
                            <i class="fas fa-school"></i>
                        </div>
                    </div>
                    <div class="col ml-3">
                        <p class="card-category mb-0">Jumlah Kelas</p>
                        <h4 class="card-title">
                            <?php
                            $kelas = mysqli_num_rows(mysqli_query($con, "SELECT * FROM tb_mkelas"));
                            echo $kelas;
                            ?>
                        </h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Informasi Sekolah -->
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card full-height">
                <div class="card-body text-center">
                    <img src="/assets/img/jatim.png" width="100" alt="Logo Jatim" class="mb-3">
                    
                    <p class="mt-2 mb-1">
                        PEMERINTAH PROVINSI JAWA TIMUR<br>
                        DINAS PENDIDIKAN<br>
                        <h3><b>SMK NEGERI 10 MALANG</b></h3>
                        Jl. Raya Tlogowaru, Tlogowaru, Kedungkandang, Malang, Jawa Timur 65133<br>
                        Website: <a href="https://www.smkn10-mlg.sch.id" target="_blank">www.smkn10-mlg.sch.id</a><br>
                        Email: <a href="mailto:smkn10_malang@yahoo.co.id">smkn10_malang@yahoo.co.id</a>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Tentang Aplikasi -->
    <div class="row mt-3">
        <div class="col-md-12">
            <div class="card full-height">
                <div class="card-body">
                    <h4 class="card-title"><i class="fas fa-info-circle text-primary mr-2"></i>Tentang Website</h4>
                    <p class="mt-2">
                        Website ini merupakan sistem informasi KBM berbasis web yang dikembangkan untuk memudahkan pengelolaan data absensi dan jurnal oleh guru, wali kelas, dan kepala sekolah. 
                        <br>
                        Kepala sekolah dapat melihat data rekap absen siswa dan rekap jurnal guru dan informasi sekolah secara real-time melalui dashboard ini. Sistem ini juga mendukung integrasi dengan laporan kegiatan harian guru dan rekapitulasi kehadiran kelas.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
