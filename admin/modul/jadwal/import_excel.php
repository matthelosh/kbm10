<?php

$via_ajax = isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false;
if ($via_ajax) header('Content-Type: application/json');

require_once __DIR__ . '/../../../config/db.php';
require_once __DIR__ . '/../../../vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;

function cari_terdekat($input, $list) {
    $maxSim = 0; $hasil = null;
    foreach ($list as $id => $nama) {
        similar_text(strtolower($input), strtolower($nama), $persen);
        if ($persen > $maxSim) {
            $maxSim = $persen;
            $hasil = $id;
        }
    }
    return ($maxSim >= 50) ? $hasil : null;
}

try {
    if (!isset($_FILES['excel_file']['tmp_name']) || empty($_FILES['excel_file']['tmp_name'])) {
        throw new Exception("File Excel tidak ditemukan.");
    }

    $file = $_FILES['excel_file']['tmp_name'];
    $spreadsheet = IOFactory::load($file);
    $rows = $spreadsheet->getActiveSheet()->toArray();

    if (count($rows) < 2) throw new Exception("File Excel kosong atau tidak valid.");

    // Pemetaan kolom dari header
    $headers = array_map('strtolower', $rows[0]);
    $map = [];

    foreach ($headers as $i => $header) {
        $key = trim(strtolower($header));
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

    // Ambil referensi data
    $guruList = []; $q = mysqli_query($con, "SELECT id_guru, nama_guru FROM tb_guru");
    while ($d = mysqli_fetch_assoc($q)) $guruList[$d['id_guru']] = $d['nama_guru'];

    $mapelList = []; $q = mysqli_query($con, "SELECT id_mapel, mapel FROM tb_master_mapel");
    while ($d = mysqli_fetch_assoc($q)) $mapelList[$d['id_mapel']] = $d['mapel'];

    $kelasList = []; $q = mysqli_query($con, "SELECT id_mkelas, nama_kelas FROM tb_mkelas");
    while ($d = mysqli_fetch_assoc($q)) $kelasList[$d['id_mkelas']] = $d['nama_kelas'];

    $log = [];

    // Loop data mulai dari baris kedua
    for ($i = 1; $i < count($rows); $i++) {
        $row = $rows[$i];
        $guruText  = trim($row[$map['guru']] ?? '');
        $mapelText = trim($row[$map['mapel']] ?? '');
        $kelasText = trim($row[$map['kelas']] ?? '');
        $hari      = trim($row[$map['hari']] ?? '');
        $jamke     = trim($row[$map['jamke']] ?? '');
        $jam       = trim($row[$map['waktu']] ?? '');
        $ruang     = isset($map['ruang']) ? trim($row[$map['ruang']] ?? '') : '';

        if (!$guruText || !$mapelText || !$kelasText) {
            $log[] = "❌ Baris $i kosong atau data tidak lengkap (Guru/Mapel/Kelas)";
            continue;
        }

        $id_mapel = cari_terdekat($mapelText, $mapelList);
        $id_guru = cari_terdekat($guruText, $guruList);
        $id_kelas = cari_terdekat($kelasText, $kelasList);

        if (!$id_mapel || !$id_guru || !$id_kelas) {
    $log[] = "❌ Gagal cocok:";
    if (!$id_mapel) {
        $log[] = "- Mapel '$mapelText' tidak cocok. Kandidat: " . implode(", ", array_slice(array_values($mapelList), 0, 5));
    }
    if (!$id_guru) {
        $log[] = "- Guru '$guruText' tidak cocok. Kandidat: " . implode(", ", array_slice(array_values($guruList), 0, 5));
    }
    if (!$id_kelas) {
        $log[] = "- Kelas '$kelasText' tidak cocok. Kandidat: " . implode(", ", array_slice(array_values($kelasList), 0, 5));
    }
    continue;
}


        // Cek duplikasi
        $cek = mysqli_query($con, "SELECT * FROM tb_mengajar 
            WHERE hari='$hari' AND jamke='$jamke' AND id_mapel='$id_mapel' AND id_guru='$id_guru' 
            AND id_mkelas='$id_kelas' AND id_semester='$id_semester' AND id_thajaran='$id_thajaran'");
        if (mysqli_num_rows($cek) > 0) {
            $log[] = "⚠️ Duplikat: $mapelText - $kelasText ($hari jam $jamke)";
            continue;
        }

        $kode = 'MPL-' . uniqid();
        $stmt = $con->prepare("INSERT INTO tb_mengajar 
            (kode_pelajaran, hari, jam_mengajar, jamke, ruang, id_guru, id_mapel, id_mkelas, id_semester, id_thajaran) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssiiiii", $kode, $hari, $jam, $jamke, $ruang, $id_guru, $id_mapel, $id_kelas, $id_semester, $id_thajaran);

        if ($stmt->execute()) {
            $log[] = "✅ Berhasil: $mapelText - $kelasText ($hari jam $jamke)";
        } else {
            $log[] = "❌ Gagal insert: " . $stmt->error;
        }
    }

        if ($via_ajax) {
        echo json_encode(['status' => 'success', 'message' => 'Proses selesai.', 'log' => $log]);
    } else {
        $message = implode("\\n", $log);
echo "<script>
    alert('$message');
    window.location.href = '../../dashboard.php?page=jadwal';
</script>";
    }
// if ($via_ajax) {
//     echo json_encode(['status' => 'success', 'message' => 'Proses selesai.', 'log' => $log]);
// } else {
//     $message = implode("\\n", $log);
//     echo "<script>
//         alert(`$message`);
//         window.location.href = '../../dashboard.php?page=jadwal';
//     </script>";
// }


} catch (Exception $e) {
    if ($via_ajax) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    } else {
        echo "<script>alert('❌ Error: " . $e->getMessage() . "');</script>";
    }
}
