<?php
global $koneksi;
include '../koneksi.php';
require '../vendor/autoload.php';
require '../export_excel_helper.php';

$headers = ['No', 'Inspektor', 'Kode', 'Departemen', 'Lokasi', 'Catatan', 'Lampu Exit'];
$data = [];
$no = 1;

$sql = mysqli_query($koneksi, "SELECT ml.*, il.username, il.tanggal_inspeksi
    FROM master_lampu ml
    LEFT JOIN (
        SELECT code_lampu, username, tanggal_inspeksi
        FROM inspeksi_lampu
        WHERE id_inspeksi IN (SELECT MAX(id_inspeksi) FROM inspeksi_lampu GROUP BY code_lampu)
    ) il ON ml.code = il.code_lampu
    WHERE ml.code LIKE 'LE%'");

while ($d = mysqli_fetch_assoc($sql)) {
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
        $statusLampu
    ];
}

export_excel_xlsx($headers, $data, 'Data_Lampu_Exit');
exit();