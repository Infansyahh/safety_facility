<?php
session_start();
include '../koneksi.php';

global $koneksi;

// Proteksi halaman proses
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: ../login.php");
    exit();
}

if (isset($_GET['id'])) {
    $id = mysqli_real_escape_string($koneksi, $_GET['id']);

    // Query hapus data dari master_eyewash berdasarkan ID
    $query = "DELETE FROM master_eyewash WHERE id = '$id'";
    $hapus = mysqli_query($koneksi, $query);

    if ($hapus) {
        echo "<script>
                alert('Data Master Eye Wash berhasil dihapus!');
                window.location.href = '../admin/master_eyewash.php';
              </script>";
    } else {
        echo "<script>
                alert('Gagal menghapus data: " . mysqli_error($koneksi) . "');
                window.location.href = '../admin/master_eyewash.php';
              </script>";
    }
} else {
    header("Location: ../admin/master_eyewash.php");
    exit();
}
?>