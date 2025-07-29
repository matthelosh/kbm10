<?php
session_start();
// echo '<pre>';
// print_r($_POST);
// echo '</pre>';
// var_dump($id_mengajar);
// exit; 

$id_siswa = $data['id_siswa'];
$id_mengajar = $_POST['id_mengajar'];
$keterangan = $_POST['keterangan'] ?? ''; // Menambahkan pengecekan jika keterangan tidak ada
$tanggal = $_POST['tanggal'] ?? date('d-m-Y');

// Ambil id_guru berdasarkan id_mengajar
$getGuru = mysqli_query($con, "SELECT id_guru FROM tb_mengajar WHERE id_mengajar='$id_mengajar'");
$dataGuru = mysqli_fetch_assoc($getGuru);
$id_guru = $dataGuru['id_guru'];

// Proses upload foto
$foto = '';
if (isset($_FILES['fileUpload']) && $_FILES['fileUpload']['error'] === UPLOAD_ERR_OK) {
    $ext = pathinfo($_FILES['fileUpload']['name'], PATHINFO_EXTENSION);
    $fotoName = 'foto_' . time() . '.' . strtolower($ext);
    $uploadDir = __DIR__ . '/../..//assets/foto_presensi_guru/';

    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $uploadPath = $uploadDir . $fotoName;

    if (move_uploaded_file($_FILES['fileUpload']['tmp_name'], $uploadPath)) {
        $foto = $fotoName;
    } else {
        echo "Upload foto gagal.";
        exit;
    }
}

// Simpan data ke database
$query = mysqli_query($con, "INSERT INTO _logabsenguru 
    (id_guru, id_mengajar, tanggal, ket, input_by, foto) 
    VALUES 
    ('$id_guru', '$id_mengajar', '$tanggal', '$keterangan', '$id_siswa', '$foto')");

if ($query) {
    echo "
    <html>
    <head><script src='https://unpkg.com/sweetalert/dist/sweetalert.min.js'></script></head>
    <body>
    <script>
        swal({
            title: 'Berhasil!',
            text: 'Presensi berhasil diupload.',
            icon: 'success',
            button: {
                text: 'OK',
                className: 'btn btn-success'
            }
        }).then(() => {
            window.location = '../../../siswa/index.php';
        });
    </script>
    </body>
    </html>";
} else {
    echo "
    <html>
    <head><script src='https://unpkg.com/sweetalert/dist/sweetalert.min.js'></script></head>
    <body>
    <script>
        swal({
            title: 'Gagal!',
            text: 'Presensi gagal diupload.',
            icon: 'error',
            button: {
                text: 'Coba Lagi',
                className: 'btn btn-danger'
            }
        }).then(() => {
            window.location = '../../index.php?page=presensi-guru';
        });
    </script>
    </body>
    </html>";
}
?>