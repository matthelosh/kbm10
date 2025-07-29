<?php
session_start();

if (!isset($_SESSION['admin'])) {
    echo json_encode(['success' => false, 'message' => 'Session expired, please login again.']);
    exit;
}

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../../../config/db.php';
require_once __DIR__ . '/../../../vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;

header('Content-Type: application/json');

// Fungsi untuk mencari kolom dengan kata kunci terdekat
function cariKolomTerdekat($headers, $targetKeywords) {
    $terdekat = null;
    $skorTertinggi = 0;
    foreach ($headers as $col => $headerName) {
        foreach ($targetKeywords as $keyword) {
            similar_text(strtolower($headerName), strtolower($keyword), $percent);
            if ($percent > $skorTertinggi) {
                $skorTertinggi = $percent;
                $terdekat = $col;
            }
        }
    }
    return ($skorTertinggi >= 50) ? $terdekat : null; // Mengembalikan kolom jika skor >= 50
}

// Fungsi untuk mencari id_mkelas berdasarkan nama kelas
function cari_kelas_id($nama_kelas, $con) {
    $query = mysqli_query($con, "SELECT id_mkelas FROM tb_mkelas WHERE nama_kelas = '$nama_kelas'");
    if ($result = mysqli_fetch_array($query)) {
        return $result['id_mkelas'];
    }
    return null; // Kembalikan null jika tidak ditemukan
}

if ($_FILES['excel_file']['error'] != 0) {
    echo json_encode([ 
        'success' => false, 
        'message' => 'File gagal diupload, error: ' . $_FILES['excel_file']['error'] 
    ]);
    exit;
}

if ($_FILES['excel_file']['size'] > 10000000) {  // Maksimum 10MB
    echo json_encode([ 
        'success' => false, 
        'message' => 'File terlalu besar. Maksimal 10MB.' 
    ]);
    exit;
}

try {
    $file = $_FILES['excel_file']['tmp_name'];
    $spreadsheet = IOFactory::load($file);  // Memuat file Excel
    $sheet = $spreadsheet->getActiveSheet();
    $rows = $sheet->toArray(null, true, true, true);
    $headers = $rows[1] ?? [];

    // Menentukan kolom yang ada dalam file
    $colNama = cariKolomTerdekat($headers, ['nama', 'nama lengkap', 'nama siswa']);
    $colNis = cariKolomTerdekat($headers, ['nis', 'nisn']);
    $colJk = cariKolomTerdekat($headers, ['jk', 'kelamin', 'jenis kelamin', 'gender']);
    $colKelas = cariKolomTerdekat($headers, ['kelas', 'nama kelas']);
    $colKetua = cariKolomTerdekat($headers, ['ketua']);
    $colThn = cariKolomTerdekat($headers, ['tahun masuk', 'angkatan']);
    $colFoto = cariKolomTerdekat($headers, ['foto', 'pas foto']);

    $berhasil = 0;
    $gagal = 0;
    $duplikat = [];
    $error_details = [];

    // Proses setiap baris dalam file
    for ($i = 2; $i <= count($rows); $i++) {
        $row = $rows[$i];

        // Ambil data dari Excel
        $nama = trim($row[$colNama] ?? '');
        $nis = trim($row[$colNis] ?? '');
        $jk = strtoupper(trim($row[$colJk] ?? ''));
        $kelas = trim($row[$colKelas] ?? '');
        $ketua = ($colKetua !== null) ? (trim($row[$colKetua]) == '1' ? 1 : 0) : 0;
        $thn = ($colThn !== null) ? trim($row[$colThn]) : '';
        $foto = ($colFoto !== null) ? trim($row[$colFoto]) : '';

        // Cek kelas ID
        $kelas_id = cari_kelas_id($kelas, $con);
        if ($kelas_id === null) {
            $gagal++;
            $error_details[] = "Kelas '$kelas' tidak ditemukan di database.";
            continue;
        }

        // Validasi data yang hilang
        if (empty($nama) || empty($nis) || $kelas_id === null || empty($thn)) {
            $gagal++;
            $error_details[] = "Data gagal: Nama: $nama, NIS: $nis, Kelas: $kelas (Data tidak lengkap)";
            continue;
        }

        // Cek duplikat NIS
        $cekNIS = mysqli_query($con, "SELECT COUNT(*) AS total FROM tb_siswa WHERE nis = '$nis'");
        $cekResult = mysqli_fetch_assoc($cekNIS);
        if ($cekResult['total'] > 0) {
            $gagal++;
            $duplikat[] = "$nis ($nama)";
            $error_details[] = "Duplikat: $nis ($nama)";
            continue;
        }

        // Query untuk menyimpan data ke dalam database
        $query = mysqli_query($con, "INSERT INTO tb_siswa 
            (nama_siswa, nis, password, jk, id_mkelas, is_ketua, th_angkatan, foto, status)
            VALUES (
                '$nama',
                '$nis',
                '$nis',  -- Password = NIS
                '$jk',
                '$kelas_id',
                '$ketua',
                '$thn',
                '$foto',
                '1' -- Status aktif
            )");

        if ($query) {
            $berhasil++;
        } else {
            $gagal++;
            $error_details[] = "Error inserting data: " . mysqli_error($con);
        }
    }

    // Menyusun pesan hasil
    $pesan = "Import selesai. Berhasil: $berhasil | Gagal: $gagal";
    if ($gagal > 0 && !empty($duplikat)) {
        $pesan .= "\\nDuplikat:\n- " . implode("\n- ", $duplikat);
    }

    // Tampilkan detail kesalahan jika ada
    if (!empty($error_details)) {
        $pesan .= "<br>Detail Kesalahan:\n- " . implode("\n- ", $error_details);
    }

    $response = [
        'success' => true,
        'message' => $pesan
    ];

    echo json_encode($response);
    exit;

} catch (Exception $e) {
    $response = [
        'success' => false,
        'message' => $e->getMessage()
    ];

    echo json_encode($response);
    exit;
}
?>