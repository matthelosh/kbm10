<?php
if (session_status() === PHP_SESSION_NONE) session_start();
//require_once __DIR__ . '/../../../vendor/autoload.php';
include '../../../config/db.php';
//require_once $_SERVER['DOCUMENT_ROOT'] . '/kbm/config/db.php';

if (!isset($_SESSION['guru'])) {
    echo "<script>
        alert('🚫 Anda harus login terlebih dahulu');
        window.location='../user.php';
    </script>";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['jurnal_ids'])) {
    $jurnal_ids = $_POST['jurnal_ids'];

    if (!empty($jurnal_ids)) {
        $placeholders = implode(',', array_fill(0, count($jurnal_ids), '?'));
        $types = str_repeat('i', count($jurnal_ids));

        $stmt = $con->prepare("DELETE FROM jurnal_mengajar WHERE id IN ($placeholders)");
        if ($stmt) {
            $stmt->bind_param($types, ...$jurnal_ids);
            if ($stmt->execute()) {
                echo "<script>
                    alert('✅ Jurnal terpilih berhasil dihapus.');
                    window.location='/guru/index.php?page=rekap_jurnal';
                </script>";
            } else {
                echo "<script>
                    alert('❌ Gagal menghapus jurnal. Silakan coba lagi.');
                    window.location='/guru/index.php?page=rekap_jurnal';
                </script>";
            }
        } else {
            echo "<script>
                alert('❌ Terjadi kesalahan pada query.');
                window.location='/guru/index.php?page=rekap_jurnal';
            </script>";
        }
    } else {
        echo "<script>
            alert('⚠️ Tidak ada jurnal yang dipilih untuk dihapus.');
            window.location='/guru/index.php?page=rekap_jurnal';
        </script>";
    }
} else {
    echo "<script>
        alert('⚠️ Akses tidak valid!');
        window.location='/guru/index.php?page=rekap_jurnal';
    </script>";
}
?>
