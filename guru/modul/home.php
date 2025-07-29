<div class="panel-header bg-primary-gradient">
    <div class="page-inner py-5">
        <div class="d-flex align-items-left align-items-md-center flex-column flex-md-row">
            <div>
                <h2 class="text-white pb-2 fw-bold">Aplikasi Jurnal Guru dan Presensi Siswa</h2>
                <h5 class="text-white op-7 mb-2">Selamat Datang, <b class="text-warning"><?=$data['nama_guru']; ?></b></h5>
            </div>
        </div>
    </div>
</div>

<div class="page-inner mt--5">
    <div class="row mt--2">
        <div class="col-md-6">
            <div class="card full-height">
                <div class="card-body">
                    <div class="card-title">
                        <center>
                            <img src="/assets/img/jatim.png" width="100">
                        </center>
                    </div>
                    <div class="card-category">
                        <center>
                            PEMERINTAH PROVINSI JAWA TIMUR
                            <br>DINAS PENDIDIKAN
                            <br><b>SMK NEGERI 10 MALANG </b>
                            <br>Jl. Raya Tlogowaru Kec. Kedungkandang Kota Malang
                            <br>Telp. (0341) 754086 E-mail : <a href="mailto:smkn10_malang@yahoo.co.id" style="color: blue;">smkn10_malang@yahoo.co.id</a>
                        </center>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <ul class="nav nav-pills nav-secondary nav-pills-no-bd nav-pills-icons justify-content-center" id="pills-tab-with-icon" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link" id="pills-home-tab-icon" data-toggle="pill" href="#pills-home-icon" role="tab" aria-controls="pills-home-icon" aria-selected="true">
                                <i class="fas fa-clipboard-list"></i>
                                Absen
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="pills-profile-tab-icon" data-toggle="pill" href="#pills-profile-icon" role="tab" aria-controls="pills-profile-icon" aria-selected="false">
                                <i class="fas fa-list-alt"></i>
                                Rekap
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="pills-contact-tab-icon" data-toggle="pill" href="#pills-contact-icon" role="tab" aria-controls="pills-contact-icon" aria-selected="false">
                                <i class="fas fa-user-astronaut"></i>
                                About
                            </a>
                        </li>
                    </ul>
                    <div class="tab-content mt-2 mb-3" id="pills-with-icon-tabContent">
                        <!-- Absen Tab -->
                        <div class="tab-pane fade" id="pills-home-icon" role="tabpanel" aria-labelledby="pills-home-tab-icon">
                            <p>
                                <ul class="list-group">
                                    <?php foreach ($kelas_guru as $dm) { ?>
                                        <li class="list-group-item">
                                            <a class="btn btn-primary btn-block text-left" href="?page=absen&pelajaran=<?=$dm['id_mengajar']?>">
                                                <i class="fas fa-chevron-circle-right"></i>
                                                <span class="sub-item"><?= strtoupper($dm['mapel']); ?> (<?= strtoupper($dm['nama_kelas']); ?>)</span>
                                            </a>
                                        </li>
                                    <?php } ?>
                                </ul>
                            </p>
                        </div>

                        <!-- Rekap Tab -->
                        <div class="tab-pane fade" id="pills-profile-icon" role="tabpanel" aria-labelledby="pills-profile-tab-icon">
                            <p>
                                <ul class="list-group">
                                    <?php foreach ($kelas_guru as $dm) { ?>
                                        <li class="list-group-item">
                                            <a class="btn btn-secondary btn-block text-left" href="?page=rekap&pelajaran=<?=$dm['id_mengajar']?>">
                                                <i class="fas fa-chevron-circle-right"></i>
                                                <span class="sub-item"><?= strtoupper($dm['mapel']); ?> (<?= strtoupper($dm['nama_kelas']); ?>)</span>
                                            </a>
                                        </li>
                                    <?php } ?>
                                </ul>
                            </p>
                        </div>

                        <!-- About Tab -->
                        <div class="tab-pane fade" id="pills-contact-icon" role="tabpanel" aria-labelledby="pills-contact-tab-icon">
                            <p>
                                <hr>
                                Aplikasi Jurnal Guru dan Absensi Siswa ini dibuat untuk mendokumentasikan KBM serta kehadiran siswa.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>