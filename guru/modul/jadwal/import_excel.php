<?php
$via_ajax = isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false;
if ($via_ajax) header('Content-Type: application/json');

require_once __DIR__ . '/../../../config/db.php';
require_once __DIR__ . '/../../../vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;

session_start();
if (!isset($_SESSION['guru'])) {
    throw new Exception("Akses ditolak. Silakan login sebagai guru.");
}
$id_guru_login = $_SESSION['guru'];
$dataGuruLogin = mysqli_fetch_assoc(mysqli_query($con, "SELECT nama_guru FROM tb_guru WHERE id_guru='$id_guru_login'"));
if (!$dataGuruLogin) throw new Exception("Data guru tidak ditemukan.");
$namaGuruLogin = strtolower(trim($dataGuruLogin['nama_guru']));

function cari_terdekat($input, $list) {
    $maxSim = 0; $hasil = null;
    foreach ($list as $id => $nama) {
        similar_text(strtolower($input), strtolower($nama), $persen);
        if ($persen > $maxSim) {
            $maxSim = $persen;
            $hasil = $id;
        }
    }
    return ($maxSim >= 70) ? $hasil : null;
}

try {
    if (!isset($_FILES['excel_file']['tmp_name']) || empty($_FILES['excel_file']['tmp_name'])) {
        throw new Exception("File Excel tidak ditemukan.");
    }

    $file = $_FILES['excel_file']['tmp_name'];
    $spreadsheet = IOFactory::load($file);
    $rows = $spreadsheet->getActiveSheet()->toArray();

    if (count($rows) < 2) throw new Exception("File Excel kosong atau tidak valid.");

    $headers = array_map('strtolower', $rows[0]);
    $map = [];

    foreach ($headers as $i => $header) {
        $key = trim($header);
        if (in_array($key, ['mapel', 'mata pelajaran', 'nama mapel'])) $map['mapel'] = $i;
        elseif (in_array($key, ['guru', 'nama guru', 'pengajar'])) $map['guru'] = $i;
        elseif (in_array($key, ['kelas', 'nama kelas'])) $map['kelas'] = $i;
        elseif ($key === 'hari') $map['hari'] = $i;
        elseif (in_array($key, ['jam', 'jam ke'])) $map['jamke'] = $i;
        elseif (in_array($key, ['waktu', 'jam mengajar'])) $map['waktu'] = $i;
        elseif ($key === 'ruang') $map['ruang'] = $i;
    }

    foreach (['mapel', 'guru', 'kelas', 'hari', 'jamke', 'waktu'] as $k) {
        if (!isset($map[$k])) throw new Exception("Kolom '$k' tidak ditemukan di file Excel.");
    }

    $id_thajaran = $_POST['id_thajaran'] ?? null;
    $id_semester = $_POST['id_semester'] ?? null;

    if (!$id_thajaran || !$id_semester) {
        $thajaran = mysqli_fetch_assoc(mysqli_query($con, "SELECT id_thajaran FROM tb_thajaran WHERE status=1"));
        $semester = mysqli_fetch_assoc(mysqli_query($con, "SELECT id_semester FROM tb_semester WHERE status=1"));
        if (!$thajaran || !$semester) throw new Exception("Tahun ajaran / semester aktif tidak ditemukan.");
        $id_thajaran = $thajaran['id_thajaran'];
        $id_semester = $semester['id_semester'];
    }

    $guruList = []; $q = mysqli_query($con, "SELECT id_guru, nama_guru FROM tb_guru");
    while ($d = mysqli_fetch_assoc($q)) $guruList[$d['id_guru']] = $d['nama_guru'];

    $mapelList = []; $q = mysqli_query($con, "SELECT id_mapel, mapel FROM tb_master_mapel");
    while ($d = mysqli_fetch_assoc($q)) $mapelList[$d['id_mapel']] = $d['mapel'];

    $kelasList = []; $q = mysqli_query($con, "SELECT id_mkelas, nama_kelas FROM tb_mkelas");
    while ($d = mysqli_fetch_assoc($q)) $kelasList[$d['id_mkelas']] = $d['nama_kelas'];

    $log = [];

    for ($i = 1; $i < count($rows); $i++) {
        $row = $rows[$i];
        $mapelText = trim($row[$map['mapel']]);
        $guruText  = trim($row[$map['guru']]);
        $kelasText = trim($row[$map['kelas']]);
        $hari      = trim($row[$map['hari']]);
        $jamke     = trim($row[$map['jamke']]);
        $jam       = trim($row[$map['waktu']]);
        $ruang     = isset($map['ruang']) ? trim($row[$map['ruang']]) : '';

        if (!$mapelText || !$guruText || !$kelasText) {
            $log[] = "❌ Baris $i tidak lengkap.";
            continue;
        }

        // Pencocokan nama guru login dengan Excel menggunakan similar_text
        $persen_kecocokan = 0;
        similar_text(strtolower($guruText), $namaGuruLogin, $persen_kecocokan);
        if ($persen_kecocokan < 50) {
            $log[] = "⏩ Lewat baris $i: '$guruText' bukan Anda (cocok hanya $persen_kecocokan%).";
            continue;
        }

        $id_mapel = cari_terdekat($mapelText, $mapelList);
        $id_kelas = cari_terdekat($kelasText, $kelasList);
        $id_guru  = $id_guru_login;

        if (!$id_mapel || !$id_kelas) {
            $log[] = "❌ Gagal cocok baris $i: $mapelText - $kelasText";
            continue;
        }

        $cek = mysqli_query($con, "SELECT * FROM tb_mengajar 
            WHERE hari='$hari' AND jamke='$jamke' AND id_mapel='$id_mapel' AND id_guru='$id_guru' 
            AND id_mkelas='$id_kelas' AND id_semester='$id_semester' AND id_thajaran='$id_thajaran'");
        if (mysqli_num_rows($cek) > 0) {
            $log[] = "⚠️ Duplikat baris $i: $mapelText - $kelasText ($hari jam $jamke)";
            continue;
        }

        $kode = 'MPL-' . uniqid();
        $stmt = $con->prepare("INSERT INTO tb_mengajar 
            (kode_pelajaran, hari, jam_mengajar, jamke, ruang, id_guru, id_mapel, id_mkelas, id_semester, id_thajaran) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssiiiii", $kode, $hari, $jam, $jamke, $ruang, $id_guru, $id_mapel, $id_kelas, $id_semester, $id_thajaran);

        if ($stmt->execute()) {
            $log[] = "✅ Berhasil baris $i: $mapelText - $kelasText ($hari jam $jamke)";
        } else {
            $log[] = "❌ Gagal insert baris $i: " . $stmt->error;
        }
    }

    if ($via_ajax) {
        echo json_encode(['status' => 'success', 'message' => 'Proses selesai.', 'log' => $log]);
    } else {
        $message = implode("\\n", array_map(fn($l) => str_replace("'", "\\'", $l), $log));
echo "<script>
    alert('$message');
    window.location.href = '../../../guru/?page=jadwal';
</script>";

        // foreach ($log as $line) echo $line . "<br>";
        // echo "<hr><a href='../../../guru/?page=jadwal' class='btn btn-primary'>🔙 Kembali ke Jadwal</a>";
    }

} catch (Exception $e) {
    if ($via_ajax) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    } else {
        echo "<span style='color:red'>❌ Error:</span> " . $e->getMessage();
    }
}