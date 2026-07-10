<?php
global $koneksi;
include '../koneksi.php';
require '../vendor/autoload.php';
require '../export_excel_helper.php';

$headers = ['No', 'Inspektor', 'Kode Eye Wash', 'Area / Line (Lokasi)', 'Catatan', 'Aliran Air (15 Menit)', 'Kondisi Air', 'Kondisi Kotak', 'Kondisi Akhir'];
$data = [];
$no = 1;

$sql = mysqli_query($koneksi, "SELECT me.*, ie.username, ie.tanggal_inspeksi FROM master_eyewash me
    LEFT JOIN (
        SELECT code_eyewash, username, tanggal_inspeksi
        FROM inspeksi_eyewash
        WHERE id_inspeksi IN (SELECT MAX(id_inspeksi) FROM inspeksi_eyewash GROUP BY code_eyewash)
    ) ie ON me.code = ie.code_eyewash");

while ($d = mysqli_fetch_assoc($sql)) {
    // Catatan disimpan gabung "Aliran Air, Kondisi Air, Kondisi Kotak" dipisah koma
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
        $d['kondisi']
    ];
}

export_excel_xlsx($headers, $data, 'Data_Eye_Wash');
exit();