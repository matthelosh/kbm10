<?php 
// Pastikan session sudah dimulai
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../../config/db.php';

// Cek apakah parameter ID ada di URL
if (isset($_GET['id'])) {
    $id_siswa = $_GET['id']; // Ambil ID dari URL
    
    // Pastikan ID adalah angka (menghindari SQL Injection)
    if (is_numeric($id_siswa)) {
        // Jalankan query untuk menghapus data siswa berdasarkan ID
        $del = mysqli_query($con, "DELETE FROM tb_siswa WHERE id_siswa = $id_siswa");

        // Cek jika query berhasil
        if ($del) {
            echo "<script>
                    alert('Data telah dihapus!');
                    window.location='?page=siswa'; // Redirect kembali ke halaman siswa
                  </script>";
        } else {
            echo "<script>
                    alert('Gagal menghapus data!');
                    window.location='?page=siswa'; // Redirect kembali jika gagal
                  </script>";
        }
    } else {
        // Jika ID tidak valid
        echo "<script>
                alert('ID tidak valid!');
                window.location='?page=siswa';
              </script>";
    }
} else {
    // Jika tidak ada parameter ID di URL
    echo "<script>
            alert('Tidak ada ID yang dipilih!');
            window.location='?page=siswa';
          </script>";
}
?>