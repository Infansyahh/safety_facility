<?php
/**
 * api/dashboard.php - Statistik dashboard admin
 */
require_once __DIR__ . '/config.php';
require_login();

$action = api_action('');

if ($action === 'set_operator') {
    $data = api_input();
    $nama = isset($data['nama_operator']) ? trim($data['nama_operator']) : '';
    if ($nama === '') {
        api_response(['success' => false, 'message' => 'Nama operator wajib diisi.'], 400);
    }
    $_SESSION['nama_operator_popup'] = $nama;
    api_response(['success' => true, 'message' => 'Nama operator tersimpan.', 'nama_operator' => $nama]);
}

if ($action === 'check_operator') {
    api_response([
        'success' => true,
        'nama_operator' => $_SESSION['nama_operator_popup'] ?? '',
        'has_operator' => isset($_SESSION['nama_operator_popup']) && !empty($_SESSION['nama_operator_popup']),
    ]);
}

$total_pengguna = 0;
$total_fasilitas = 0;
$total_rusak = 0;
$detail_notifikasi = [];

if ($koneksi) {
    $q_user = mysqli_query($koneksi, 'SELECT COUNT(*) as total FROM users');
    if ($q_user) {
        $res = mysqli_fetch_assoc($q_user);
        $total_pengguna = (int) $res['total'];
        if (isset($_SESSION['nama_operator_popup']) && !empty($_SESSION['nama_operator_popup'])) {
            $total_pengguna++;
        }
    }

    $c_lampu = (int) (mysqli_fetch_assoc(mysqli_query($koneksi, 'SELECT COUNT(*) as total FROM master_lampu'))['total'] ?? 0);
    $c_p3k = (int) (mysqli_fetch_assoc(mysqli_query($koneksi, 'SELECT COUNT(*) as total FROM master_p3k'))['total'] ?? 0);
    $c_eyewash = (int) (mysqli_fetch_assoc(mysqli_query($koneksi, 'SELECT COUNT(*) as total FROM master_eyewash'))['total'] ?? 0);
    $total_fasilitas = $c_lampu + $c_p3k + $c_eyewash;

    $current_month = date('m');
    $current_year = date('Y');

    $r_lampu = (int) (mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as total FROM inspeksi_lampu WHERE kondisi = 'rusak'"))['total'] ?? 0);
    if ($r_lampu > 0) {
        $detail_notifikasi[] = [
            'icon' => 'fa-lightbulb',
            'text' => "$r_lampu Lampu Emergency dalam kondisi rusak.",
            'color' => '#dc3545',
            'url' => 'master_lampu',
        ];
    }

    $r_p3k = (int) (mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(DISTINCT code_p3k) as total FROM inspeksi_p3k
        WHERE MONTH(tanggal_inspeksi) = '$current_month' AND YEAR(tanggal_inspeksi) = '$current_year'
        AND kondisi = 'rusak'"))['total'] ?? 0);
    if ($r_p3k > 0) {
        $detail_notifikasi[] = [
            'icon' => 'fa-kit-medical',
            'text' => "$r_p3k Kotak P3K dalam kondisi rusak bulan ini.",
            'color' => '#ffc107',
            'url' => 'master_p3k',
        ];
    }

    $r_eyewash = (int) (mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(DISTINCT code_eyewash) as total FROM inspeksi_eyewash
        WHERE MONTH(tanggal_inspeksi) = '$current_month' AND YEAR(tanggal_inspeksi) = '$current_year'
        AND kondisi = 'rusak'"))['total'] ?? 0);
    if ($r_eyewash > 0) {
        $detail_notifikasi[] = [
            'icon' => 'fa-eye-dropper',
            'text' => "$r_eyewash Eye Wash berstatus rusak bulan ini.",
            'color' => '#fd7e14',
            'url' => 'master_eyewash',
        ];
    }

    $total_rusak = $r_lampu + $r_p3k + $r_eyewash;
}

api_response([
    'success' => true,
    'total_pengguna' => $total_pengguna,
    'total_fasilitas' => $total_fasilitas,
    'total_rusak' => $total_rusak,
    'detail_notifikasi' => $detail_notifikasi,
    'nama_lengkap' => $_SESSION['nama_lengkap'] ?? 'Admin',
    'tanggal_format' => indo_tanggal(),
]);

function indo_tanggal()
{
    $hari_ini = date('l');
    $daftar_hari = [
        'Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa', 'Wednesday' => 'Rabu',
        'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu',
    ];
    $bulan_ini = date('F');
    $daftar_bulan = [
        'January' => 'Januari', 'February' => 'Februari', 'March' => 'Maret', 'April' => 'April',
        'May' => 'Mei', 'June' => 'Juni', 'July' => 'Juli', 'August' => 'Agustus',
        'September' => 'September', 'October' => 'Oktober', 'November' => 'November', 'December' => 'Desember',
    ];
    return ($daftar_hari[$hari_ini] ?? $hari_ini) . ", " . date('d') . " " . ($daftar_bulan[$bulan_ini] ?? $bulan_ini) . " " . date('Y');
}