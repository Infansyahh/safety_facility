<?php
session_start();
include '../koneksi.php';

global $koneksi;

// Proteksi halaman proses
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: ../login.php");
    exit();
}

if (isset($_POST['submit'])) {
    $code      = mysqli_real_escape_string($koneksi, $_POST['code']);
    $line_area = mysqli_real_escape_string($koneksi, $_POST['line_area']);
    $lokasi    = mysqli_real_escape_string($koneksi, $_POST['lokasi']);
    $kondisi   = mysqli_real_escape_string($koneksi, $_POST['kondisi']);
    $catatan = mysqli_real_escape_string($koneksi, $_POST['catatan']);

    // Fallback jika input catatan kosong (diambil langsung dari gabungan radio button)
    if (empty($catatan)) {
        $air        = $_POST['cek_air_tambah'];
        $kondisiAir = $_POST['cek_kondisi_air_tambah'];
        $kotak      = $_POST['cek_kotak_tambah'];
        $catatan = $air . ", " . $kondisiAir . ", " . $kotak;
        $catatan = mysqli_real_escape_string($koneksi, $catatan);
    }

    // Query simpan ke tabel master_eyewash
    $query = "INSERT INTO master_eyewash (code, line_area, lokasi, kondisi, catatan) VALUES ('$code', '$line_area', '$lokasi', '$kondisi', '$catatan')";
    $simpan = mysqli_query($koneksi, $query);

    if ($simpan) {
        echo "<script>
                alert('Data Master Eye Wash berhasil ditambahkan!');
                window.location.href = '../admin/master_eyewash.php';
              </script>";
    } else {
        echo "<script>
                alert('Gagal menambahkan data: " . mysqli_error($koneksi) . "');
                window.location.href = '../admin/master_eyewash.php';
              </script>";
    }
} else {
    header("Location: ../admin/master_eyewash.php");
    exit();
}
?>