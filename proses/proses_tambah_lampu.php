<?php
session_start();
include '../koneksi.php';

global $koneksi;

if (isset($_POST['submit'])) {
    $code = mysqli_real_escape_string($koneksi, $_POST['code']);
    $line_area = mysqli_real_escape_string($koneksi, $_POST['line_area']);
    $lokasi = mysqli_real_escape_string($koneksi, $_POST['lokasi']);
    $catatan = mysqli_real_escape_string($koneksi, $_POST['catatan']);

    // Jika Lampu Exit (diawali LE)
    if (stripos($code, 'LE') === 0) {
        $indikator = mysqli_real_escape_string($koneksi, $_POST['indikator_mati_menyala'] ?? 'Nyala');
        $lampu_mati = (strtolower($indikator) === 'mati') ? 'Ya' : 'Tidak';
        $otomatis = 'Tidak';
        $kondisi = (strtolower($indikator) === 'mati') ? 'rusak' : 'baik';
    } else {
        $indikator = mysqli_real_escape_string($koneksi, $_POST['indikator_mati_menyala'] ?? 'Nyala');
        $lampu_mati = mysqli_real_escape_string($koneksi, $_POST['lampu_mati'] ?? 'Tidak');
        $otomatis = mysqli_real_escape_string($koneksi, $_POST['nyala_otomatis'] ?? 'Tidak');
        $kondisi = (strtolower($lampu_mati) == 'ya') ? 'rusak' : 'baik';
    }

    $query = "INSERT INTO master_lampu (code, line_area, lokasi, indikator_mati_menyala, lampu_mati, nyala_otomatis, catatan, kondisi) 
              VALUES ('$code', '$line_area', '$lokasi', '$indikator', '$lampu_mati', '$otomatis', '$catatan', '$kondisi')";

    $redirect_url = (stripos($code, 'LE') === 0) ? '../admin/lampu_exit.php' : '../admin/master_lampu.php';

    if (mysqli_query($koneksi, $query)) {
        echo "<script>alert('Data berhasil ditambah!'); window.location='$redirect_url';</script>";
    } else {
        echo "<script>alert('Error: " . mysqli_error($koneksi) . "'); window.history.back();</script>";
    }
}
?>