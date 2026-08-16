<?php
/**
 * api/export_master.php - Unduh data master ke Excel (.xlsx)
 * Param: type = lampu | exit | p3k | eyewash
 */
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../export_excel_helper.php';

if (!is_logged_in()) {
    api_response(['success' => false, 'message' => 'Akses ditolak.'], 401);
}

$type = api_get('type', 'lampu');
$data = [];

if ($type === 'exit') {
    $join_sql = "FROM master_lampu ml
        LEFT JOIN (
            SELECT code_lampu, username, tanggal_inspeksi
            FROM inspeksi_lampu
            WHERE id_inspeksi IN (SELECT MAX(id_inspeksi) FROM inspeksi_lampu GROUP BY code_lampu)
        ) il ON ml.code = il.code_lampu
        WHERE ml.code LIKE 'LE%'";
    $query = mysqli_query($koneksi, "SELECT ml.*, il.username, il.tanggal_inspeksi $join_sql ORDER BY ml.id ASC");
    $headers = ['No', 'Inspektor', 'Kode', 'Departemen', 'Lokasi', 'Catatan', 'Lampu Exit'];
    $no = 1;
    while ($d = mysqli_fetch_assoc($query)) {
        $indikator = $d['indikator_mati_menyala'] ?? '';
        $kondisi = $d['kondisi'] ?? '';
        if (!empty($indikator)) {
            $statusLampu = (strtolower($indikator) == 'nyala') ? 'Nyala' : 'Mati';
        } else {
            $statusLampu = (strtolower($kondisi) == 'baik') ? 'Nyala' : 'Mati';
        }
        $data[] = [
            $no++,
            !empty($d['username']) ? $d['username'] : 'Belum Diinspeksi',
            $d['code'],
            $d['line_area'] ?? '',
            $d['lokasi'],
            !empty($d['catatan']) ? $d['catatan'] : '-',
            $statusLampu,
        ];
    }
    export_excel_xlsx($headers, $data, 'Data_Lampu_Exit');
    exit;
} elseif ($type === 'p3k') {
    $query = mysqli_query($koneksi, "SELECT * FROM master_p3k ORDER BY code ASC");
    $headers = ['No', 'Kode P3K', 'Line Area', 'Lokasi', 'Kondisi Kotak', 'Isi Kotak', 'Obat-obatan', 'Kondisi', 'Catatan'];
    $no = 1;
    while ($d = mysqli_fetch_assoc($query)) {
        $data[] = [
            $no++,
            $d['code'],
            $d['line_area'],
            $d['lokasi'],
            $d['kondisi_kotak'],
            $d['kelengkapan_isi'],
            $d['expired_obat'],
            $d['kondisi'],
            $d['catatan'],
        ];
    }
    export_excel_xlsx($headers, $data, 'Data_Kotak_P3K');
    exit;
} elseif ($type === 'eyewash') {
    $join_sql = "FROM master_eyewash me
        LEFT JOIN (
            SELECT code_eyewash, username, tanggal_inspeksi
            FROM inspeksi_eyewash
            WHERE id_inspeksi IN (SELECT MAX(id_inspeksi) FROM inspeksi_eyewash GROUP BY code_eyewash)
        ) ie ON me.code = ie.code_eyewash";
    $query = mysqli_query($koneksi, "SELECT me.*, ie.username, ie.tanggal_inspeksi $join_sql ORDER BY me.id ASC");
    $headers = ['No', 'Inspektor', 'Kode Eye Wash', 'Area / Line (Lokasi)', 'Catatan', 'Aliran Air (15 Menit)', 'Kondisi Air', 'Kondisi Kotak', 'Kondisi Akhir'];
    $no = 1;
    while ($d = mysqli_fetch_assoc($query)) {
        $val_air = '-';
        $val_kondisi_air = '-';
        $val_kotak = '-';
        if (!empty($d['catatan'])) {
            $parts = explode(', ', $d['catatan']);
            if (count($parts) == 3) {
                $val_air = $parts[0];
                $val_kondisi_air = $parts[1];
                $val_kotak = $parts[2];
            } else {
                $val_air = $d['catatan'];
            }
        }
        $data[] = [
            $no++,
            !empty($d['username']) ? $d['username'] : 'Belum Diinspeksi',
            $d['code'],
            $d['lokasi'],
            !empty($d['catatan']) ? $d['catatan'] : '-',
            $val_air,
            $val_kondisi_air,
            $val_kotak,
            $d['kondisi'],
        ];
    }
    export_excel_xlsx($headers, $data, 'Data_Eye_Wash');
    exit;
}

// default: lampu emergency
$query = mysqli_query($koneksi, "SELECT * FROM master_lampu WHERE code LIKE 'LPE%' ORDER BY id ASC");
$headers = ['No', 'Kode Lampu', 'Merek', 'Line Area', 'Lokasi', 'Indikator', 'Lampu Mati', 'Nyala Otomatis', 'Kondisi', 'Catatan'];
$no = 1;
while ($d = mysqli_fetch_assoc($query)) {
    $data[] = [
        $no++,
        $d['code'],
        $d['merek'],
        $d['line_area'],
        $d['lokasi'],
        $d['indikator_mati_menyala'],
        $d['lampu_mati'],
        $d['nyala_otomatis'],
        $d['kondisi'],
        $d['catatan'],
    ];
}
export_excel_xlsx($headers, $data, 'Data_Lampu_Emergency');
exit;