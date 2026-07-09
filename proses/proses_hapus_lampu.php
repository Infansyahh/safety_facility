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

    // Ambil code untuk menentukan redirect url
    $code = '';
    $query_code = mysqli_query($koneksi, "SELECT code FROM master_lampu WHERE id = $id");
    if ($query_code && mysqli_num_rows($query_code) > 0) {
        $row_code = mysqli_fetch_assoc($query_code);
        $code = $row_code['code'];
    }

    $redirect_url = (stripos($code, 'LE') === 0) ? '../admin/lampu_exit.php' : '../admin/master_lampu.php';

    $query = mysqli_query($koneksi, "DELETE FROM master_lampu WHERE id = $id");

    if ($query) {
        echo "<script>
                alert('Data berhasil dihapus!');
                window.location.href = '$redirect_url'; 
              </script>";
    } else {
        echo "<script>
                alert('Gagal menghapus data: " . mysqli_error($koneksi) . "');
                window.location.href = '$redirect_url';
              </script>";
    }
} else {
    header("Location: ../admin/master_lampu.php");
    exit();
}
?>
