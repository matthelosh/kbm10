<?php
session_start();

// Menampilkan konfirmasi logout menggunakan JavaScript
if (isset($_SESSION['guru'])) {
    echo "
    <script>
        var result = confirm('Apakah Anda yakin ingin logout?');
        if (result) {
            // Jika user klik 'OK', logout dan redirect
            window.location = '../'; // Redirect ke halaman login
        } else {
            // Jika user klik 'Cancel', kembali ke halaman sebelumnya
            window.location = 'javascript:history.back()';
        }
    </script>
    ";
} else {
    // Jika tidak ada session aktif, langsung redirect
    echo "<script>window.location = '../';</script>";
}
?>