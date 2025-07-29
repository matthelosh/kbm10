<?php 
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id_kepsek = $_GET['id'];

    // Menggunakan prepared statement untuk mencegah SQL Injection
    $stmt = $con->prepare("DELETE FROM tb_kepsek WHERE id_kepsek = ?");
    $stmt->bind_param("i", $id_kepsek);

    if ($stmt->execute()) {
        echo "<script>
            alert('Data telah dihapus!');
            window.location='?page=kepsek';
        </script>";
    } else {
        echo "<script>
            alert('Gagal menghapus data');
            window.location='?page=kepsek';
        </script>";
    }

    $stmt->close();
} else {
    echo "<script>
        alert('ID tidak valid');
        window.location='?page=kepsek';
    </script>";
}

if ($del) {
		echo " <script>
		alert('Data telah dihapus !');
		window.location='?page=kepsek';
		</script>";	
}

 ?>