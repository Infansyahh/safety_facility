<?php
session_start();
include '../koneksi.php';

global $koneksi;

if (isset($_POST['submit'])) {
    $code = mysqli_real_escape_string($koneksi, $_POST['code']);
    $line_area = mysqli_real_escape_string($koneksi, $_POST['line_area']);
    $lokasi = mysqli_real_escape_string($koneksi, $_POST['lokasi']);
    
    $kondisi_kotak = mysqli_real_escape_string($koneksi, $_POST['kondisi_kotak'] ?? 'Baik');
    $kelengkapan_isi = mysqli_real_escape_string($koneksi, $_POST['kelengkapan_isi'] ?? 'Lengkap');
    $expired_obat = mysqli_real_escape_string($koneksi, $_POST['expired_obat'] ?? 'Lengkap');
    
    $catatan = mysqli_real_escape_string($koneksi, $_POST['catatan'] ?? '');

    // Logika kondisi: jika salah satu "Tidak", maka "rusak"
    if (strtolower($kondisi_kotak) === 'tidak' || strtolower($kelengkapan_isi) === 'tidak' || strtolower($expired_obat) === 'tidak') {
        $kondisi = 'rusak';
    } else {
        $kondisi = 'baik';
    }

    $query = "INSERT INTO master_p3k (code, line_area, lokasi, kondisi_kotak, kelengkapan_isi, expired_obat, catatan, kondisi) 
              VALUES ('$code', '$line_area', '$lokasi', '$kondisi_kotak', '$kelengkapan_isi', '$expired_obat', '$catatan', '$kondisi')";

    if (mysqli_query($koneksi, $query)) {
        echo "<script>alert('Data P3K berhasil ditambah!'); window.location='../admin/master_p3k.php';</script>";
    } else {
        echo "<script>alert('Error: " . mysqli_error($koneksi) . "'); window.history.back();</script>";
    }
}
?>
