<?php
global $koneksi;
include '../koneksi.php';
require '../vendor/autoload.php';
require '../export_excel_helper.php';

$headers = ['No', 'Kode Lampu', 'Merek', 'Line Area', 'Lokasi', 'Indikator', 'Lampu Mati', 'Nyala Otomatis', 'Kondisi', 'Catatan'];
$data = [];
$no = 1;
$sql = mysqli_query($koneksi, "SELECT * FROM master_lampu WHERE code LIKE 'LPE%'");
while ($d = mysqli_fetch_assoc($sql)) {
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
        $d['catatan']
    ];
}

export_excel_xlsx($headers, $data, 'Data_Lampu_Emergency');
exit();