<?php
session_start();
include '../koneksi.php';

global $koneksi;

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: ../login.php");
    exit();
}

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];

    $query = mysqli_query($koneksi, "DELETE FROM master_p3k WHERE id = $id");

    if ($query) {
        echo "<script>
                alert('Data P3K berhasil dihapus!');
                window.location.href = '../admin/master_p3k.php'; 
              </script>";
    } else {
        echo "<script>
                alert('Gagal menghapus data: " . mysqli_error($koneksi) . "');
                window.location.href = '../admin/master_p3k.php';
              </script>";
    }
} else {
    header("Location: ../admin/master_p3k.php");
    exit();
}
?>
