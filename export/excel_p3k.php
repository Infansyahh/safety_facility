<?php
global $koneksi;
include '../koneksi.php';
require '../vendor/autoload.php';
require '../export_excel_helper.php';

$headers = ['No', 'Kode P3K', 'Line Area', 'Lokasi', 'Kondisi Kotak', 'Isi Kotak', 'Obat-obatan', 'Kondisi', 'Catatan'];
$data = [];
$no = 1;
$sql = mysqli_query($koneksi, "SELECT * FROM master_p3k ORDER BY code ASC");
while ($d = mysqli_fetch_assoc($sql)) {
    $data[] = [
        $no++,
        $d['code'],
        $d['line_area'],
        $d['lokasi'],
        $d['kondisi_kotak'],
        $d['kelengkapan_isi'],
        $d['expired_obat'],
        $d['kondisi'],
        $d['catatan']
    ];
}

export_excel_xlsx($headers, $data, 'Data_Kotak_P3K');
exit();