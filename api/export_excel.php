<?php
/**
 * api/export_excel.php - Unduh laporan data inspeksi sebagai Excel (.xlsx)
 * Param: type = p3k | lampu_exit | lampu_emergency | eyewash
 *        bulan (nama bulan Indonesia), tahun, cari
 */
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../export_excel_helper.php';

if (!is_logged_in()) {
    api_response(['success' => false, 'message' => 'Akses ditolak.'], 401);
}

$nama_bulan_map = [
    'Januari' => 1, 'Februari' => 2, 'Maret' => 3, 'April' => 4, 'Mei' => 5, 'Juni' => 6,
    'Juli' => 7, 'Agustus' => 8, 'September' => 9, 'Oktober' => 10, 'November' => 11, 'Desember' => 12,
];

$type = api_get('type', 'p3k');
$bulan = api_get('bulan', date('F'));
$tahun = (int) api_get('tahun', date('Y'));
$cari = trim(api_get('cari', ''));
$bulan_angka = $nama_bulan_map[$bulan] ?? (int) date('n');
$nama_operator_login = $_SESSION['nama_operator_popup'] ?? '';

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
    $headers = ['No', 'Nama Operator', 'Tanggal Inspeksi', 'Kode P3K', 'Lokasi', 'Line / Area', 'Kondisi Kotak', 'Kelengkapan Isi', 'Expired Obat', 'Keterangan'];
    $filename = 'Laporan_Kotak_P3K_' . $bulan . '_' . $tahun;
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
    $headers = ['No', 'Nama Inspektor', 'Tanggal Inspeksi', 'ID Lampu', 'Kondisi Fisik', 'Kondisi Lampu', 'Kondisi Tulisan', 'Keterangan'];
    $filename = 'Laporan_Lampu_Exit_' . $bulan . '_' . $tahun;
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
    $headers = ['No', 'Nama Operator', 'Tanggal Inspeksi', 'Code Lampu', 'Lokasi', 'Indikator', 'Lampu Mati', 'Nyala Otomatis', 'Catatan'];
    $filename = 'Laporan_Lampu_Emergency_' . $bulan . '_' . $tahun;
} else {
    $type = 'eyewash';
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
    $headers = ['No', 'Nama Inspektor', 'Tanggal Inspeksi', 'Kode Eye Wash', 'Lokasi', 'Aliran Air', 'Kondisi Air', 'Kondisi Kotak', 'Catatan'];
    $filename = 'Laporan_Eye_Wash_' . $bulan . '_' . $tahun;
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

$data = [];
$no = 1;
foreach ($rows as $r) {
    if ($type === 'p3k' || $type === 'lampu_exit') {
        $vals = array_values($r);
        array_shift($vals);
        array_unshift($vals, $no++);
        if ($type === 'p3k') {
            $vals[2] = !empty($vals[2]) ? date('d-m-Y H:i', strtotime($vals[2])) : '-';
        }
        $data[] = $vals;
    } elseif ($type === 'lampu_emergency') {
        $data[] = [
            $no++,
            $r['nama_operator'] ?? '-',
            $r['tanggal_inspeksi'],
            $r['code_lampu'] ?? '-',
            $r['lokasi'] ?? '-',
            $r['indikator_mati_menyala'] ?? '-',
            $r['lampu_mati'] ?? '-',
            $r['nyala_otomatis'] ?? '-',
            $r['catatan'] ?? '-',
        ];
    } else { // eyewash
        $val_air = '-';
        $val_kondisi_air = '-';
        $val_kotak = '-';
        if (!empty($r['catatan'])) {
            $parts = explode(', ', $r['catatan']);
            if (count($parts) == 3) {
                $val_air = $parts[0];
                $val_kondisi_air = $parts[1];
                $val_kotak = $parts[2];
            } else {
                $val_air = $r['catatan'];
            }
        }
        $data[] = [
            $no++,
            $r['nama_lengkap'] ?? '-',
            $r['tanggal_inspeksi'],
            $r['code_eyewash'],
            $r['lokasi'] ?? '-',
            $val_air,
            $val_kondisi_air,
            $val_kotak,
            $r['catatan'],
        ];
    }
}

export_excel_xlsx($headers, $data, $filename);
exit;