<?php
// modul/home.php
?>
<div class="panel-header bg-primary-gradient">
    <div class="page-inner py-5">
        <div class="d-flex align-items-left align-items-md-center flex-column flex-md-row">
            <div>
                <h2 class="text-white pb-2 fw-bold">Aplikasi Presensi Siswa</h2>
                <h5 class="text-white op-7 mb-2">Selamat Datang, <b class="text-warning"><?= htmlspecialchars($data['nama_siswa']); ?></b></h5>
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
                    <ul class="nav nav-pills nav-secondary nav-pills-no-bd nav-pills-icons justify-content-center" id="pills-tab-with-icon" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link" href="?page=kehadiran">
                                <i class="fas fa-clipboard-list"></i>
                                Absensi
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
                        <div class="tab-pane fade" id="pills-contact-icon" role="tabpanel" aria-labelledby="pills-contact-icon-tab">
                            <hr>
                            <p>
                                Aplikasi Absensi siswa ini dibuat untuk mendokumentasikan kehadiran Siswa
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>  
</div>