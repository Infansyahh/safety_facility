<?php
/**
 * api/data_inspeksi.php - Laporan data inspeksi per jenis
 * Param: type = p3k | lampu_exit | lampu_emergency | eyewash
 *        bulan (nama bulan Indonesia), tahun, cari
 * Action: list | delete
 */
require_once __DIR__ . '/config.php';
require_login();

$action = api_action('list');
$type = api_get('type', 'p3k');

$nama_bulan_map = [
    'Januari' => 1, 'Februari' => 2, 'Maret' => 3, 'April' => 4, 'Mei' => 5, 'Juni' => 6,
    'Juli' => 7, 'Agustus' => 8, 'September' => 9, 'Oktober' => 10, 'November' => 11, 'Desember' => 12,
];

$bulan = api_get('bulan', date('F'));
$tahun = (int) api_get('tahun', date('Y'));
$cari = trim(api_get('cari', ''));
$bulan_angka = $nama_bulan_map[$bulan] ?? (int) date('n');
$nama_operator_login = $_SESSION['nama_operator_popup'] ?? '';

$bulan_indo_map = [
    'Januari' => 'Januari', 'Februari' => 'Februari', 'Maret' => 'Maret', 'April' => 'April',
    'Mei' => 'Mei', 'Juni' => 'Juni', 'Juli' => 'Juli', 'Agustus' => 'Agustus',
    'September' => 'September', 'Oktober' => 'Oktober', 'November' => 'November', 'Desember' => 'Desember',
];
$bulan_indo = $bulan_indo_map[$bulan] ?? $bulan;

if ($action === 'delete') {
    $id = (int) api_get('id', 0);
    $table = [
        'p3k' => 'inspeksi_p3k',
        'lampu_exit' => 'inspeksi_lampu_exit',
        'lampu_emergency' => 'inspeksi_lampu',
        'eyewash' => 'inspeksi_eyewash',
    ];
    $tbl = $table[$type] ?? 'inspeksi_p3k';
    $stmt = mysqli_prepare($koneksi, "DELETE FROM `$tbl` WHERE `id_inspeksi` = ?");
    mysqli_stmt_bind_param($stmt, 'i', $id);
    $ok = mysqli_stmt_execute($stmt);
    if ($ok) {
        api_response(['success' => true, 'message' => 'Data inspeksi berhasil dihapus.']);
    }
    api_response(['success' => false, 'message' => 'Gagal menghapus data inspeksi.'], 500);
}

if ($type === 'p3k') {
    $sql = "SELECT i.id_inspeksi, i.username AS nama_operator, i.tanggal_inspeksi,
                   m.code AS code_p3k, m.lokasi, i.line_area,
                   i.kondisi_kotak, i.kelengkapan_isi, i.expired_obat, i.keterangan
            FROM master_p3k m
            LEFT JOIN inspeksi_p3k i ON m.code = i.code_p3k
                 AND MONTH(i.tanggal_inspeksi) = ?
                 AND YEAR(i.tanggal_inspeksi) = ?
                 AND i.username = ?";
    $param_types = 'iis';
    $param_values = [$bulan_angka, $tahun, $nama_operator_login];
    if ($cari !== '') {
        $like = "%$cari%";
        $sql .= " WHERE (i.username LIKE ? OR m.code LIKE ? OR m.lokasi LIKE ? OR i.line_area LIKE ?)";
        $param_types .= 'ssss';
        $param_values[] = $like;
        $param_values[] = $like;
        $param_values[] = $like;
        $param_values[] = $like;
    }
    $sql .= " ORDER BY m.code ASC, i.tanggal_inspeksi DESC";
} elseif ($type === 'lampu_exit') {
    $sql = "SELECT i.id_inspeksi, i.nama_operator, i.tanggal_cek,
                   i.id_lampu, i.kondisi_fisik, i.kondisi_lampu, i.kondisi_tulisan, i.keterangan
            FROM inspeksi_lampu_exit i
            WHERE MONTH(i.tanggal_cek) = ? AND YEAR(i.tanggal_cek) = ? AND i.nama_operator = ?";
    $param_types = 'iis';
    $param_values = [$bulan_angka, $tahun, $nama_operator_login];
    if ($cari !== '') {
        $like = "%$cari%";
        $sql .= " AND (i.nama_operator LIKE ? OR i.id_lampu LIKE ?)";
        $param_types .= 'ss';
        $param_values[] = $like;
        $param_values[] = $like;
    }
    $sql .= " ORDER BY i.tanggal_cek DESC";
} elseif ($type === 'lampu_emergency') {
    $sql = "SELECT i.id_inspeksi,
                   COALESCE(u.nama_lengkap, i.username) AS nama_operator,
                   i.tanggal_inspeksi,
                   i.code_lampu,
                   m.lokasi,
                   i.kondisi,
                   i.catatan,
                   i.indikator_mati_menyala,
                   i.lampu_mati,
                   i.nyala_otomatis
            FROM inspeksi_lampu i
            LEFT JOIN users u ON i.username = u.username
            LEFT JOIN master_lampu m ON i.code_lampu = m.code
            WHERE MONTH(i.tanggal_inspeksi) = ? AND YEAR(i.tanggal_inspeksi) = ? AND i.username = ?";
    $param_types = 'iis';
    $param_values = [$bulan_angka, $tahun, $nama_operator_login];
    if ($cari !== '') {
        $like = "%$cari%";
        $sql .= " AND (u.nama_lengkap LIKE ? OR i.username LIKE ? OR i.code_lampu LIKE ? OR m.lokasi LIKE ?)";
        $param_types .= 'ssss';
        $param_values[] = $like;
        $param_values[] = $like;
        $param_values[] = $like;
        $param_values[] = $like;
    }
    $sql .= " ORDER BY i.tanggal_inspeksi DESC";
} else { // eyewash
    $sql = "SELECT i.id_inspeksi, i.username AS nama_lengkap, i.tanggal_inspeksi,
                   i.code_eyewash, m.lokasi, i.kondisi, i.catatan
            FROM inspeksi_eyewash i
            LEFT JOIN master_eyewash m ON i.code_eyewash = m.code
            WHERE MONTH(i.tanggal_inspeksi) = ? AND YEAR(i.tanggal_inspeksi) = ? AND i.username = ?";
    $param_types = 'iis';
    $param_values = [$bulan_angka, $tahun, $nama_operator_login];
    if ($cari !== '') {
        $like = "%$cari%";
        $sql .= " AND (i.username LIKE ? OR i.code_eyewash LIKE ? OR m.lokasi LIKE ?)";
        $param_types .= 'sss';
        $param_values[] = $like;
        $param_values[] = $like;
        $param_values[] = $like;
    }
    $sql .= " ORDER BY i.tanggal_inspeksi DESC";
}

$rows = [];
$stmt = mysqli_prepare($koneksi, $sql);
if (count($param_values) > 0) {
    mysqli_stmt_bind_param($stmt, $param_types, ...$param_values);
}
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
while ($r = mysqli_fetch_assoc($result)) {
    $rows[] = $r;
}

// Statistik kondisi (baik/tidak)
$baik_words = ['Baik', 'Layak', 'Lengkap', 'Belum Expired', 'Mengalir', 'Bersih'];
$tidak_words = ['Tidak', 'Tidak Layak', 'Tidak Lengkap', 'Ada yang Expired', 'Tidak Mengalir', 'Kotor'];
$baik_count = 0;
$tidak_count = 0;

foreach ($rows as $r) {
    if ($type === 'lampu_emergency' || $type === 'eyewash') {
        if (strtolower($r['kondisi'] ?? '') === 'baik') {
            $baik_count++;
        } else {
            $tidak_count++;
        }
    } else {
        $vals = array_values($r);
        $cv = $vals[4] ?? '';
        if (in_array($cv, $baik_words)) {
            $baik_count++;
        } elseif (in_array($cv, $tidak_words)) {
            $tidak_count++;
        }
    }
}

api_response([
    'success' => true,
    'type' => $type,
    'data' => $rows,
    'total' => count($rows),
    'bulan' => $bulan,
    'bulan_indo' => $bulan_indo,
    'tahun' => $tahun,
    'cari' => $cari,
    'baik_count' => $baik_count,
    'tidak_count' => $tidak_count,
]);