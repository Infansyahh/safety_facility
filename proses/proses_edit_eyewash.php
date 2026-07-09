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
    $id      = mysqli_real_escape_string($koneksi, $_POST['id']);
    $code    = mysqli_real_escape_string($koneksi, $_POST['code']);
    $lokasi  = mysqli_real_escape_string($koneksi, $_POST['lokasi']);
    $kondisi = mysqli_real_escape_string($koneksi, $_POST['kondisi']);
    $catatan = mysqli_real_escape_string($koneksi, $_POST['catatan']);

    // Fallback jika input catatan kosong (diambil langsung dari gabungan radio button)
    if (empty($catatan)) {
        $air    = $_POST['cek_air_edit'];
        $fisik  = $_POST['cek_fisik_edit'];
        $nozzle = $_POST['cek_nozzle_edit'];
        $pedal  = $_POST['cek_pedal_edit'];
        $catatan = $air . ", " . $fisik . ", " . $nozzle . ", " . $pedal;
        $catatan = mysqli_real_escape_string($koneksi, $catatan);
    }

    // Nama operator dari popup dashboard, sama pola kayak proses_edit_p3k.php
    $operator = $_SESSION['nama_operator_popup'] ?? $_SESSION['nama_lengkap'] ?? $_SESSION['username'] ?? 'Admin';
    $operator = mysqli_real_escape_string($koneksi, $operator);

    // Query update ke tabel master_eyewash berdasarkan ID
    $query = "UPDATE master_eyewash SET code = '$code', lokasi = '$lokasi', kondisi = '$kondisi', catatan = '$catatan' WHERE id = '$id'";
    $update = mysqli_query($koneksi, $query);

    if ($update) {
        // Catat riwayat inspeksi supaya kolom "Dicek Oleh" & tanggal terisi
        $tanggal_hari_ini = date('Y-m-d');
        $query_insert = "INSERT INTO inspeksi_eyewash (code_eyewash, tanggal_inspeksi, kondisi, catatan, username)
                          VALUES ('$code', '$tanggal_hari_ini', '$kondisi', '$catatan', '$operator')";
        $insert = mysqli_query($koneksi, $query_insert);

        if (!$insert) {
            echo "<script>
                    alert('Data master berhasil diperbarui, tapi gagal mencatat riwayat inspeksi: " . mysqli_error($koneksi) . "');
                    window.location.href = '../admin/master_eyewash.php';
                  </script>";
            exit();
        }

        echo "<script>
                alert('Data Master Eye Wash berhasil diperbarui!');
                window.location.href = '../admin/master_eyewash.php';
              </script>";
    } else {
        echo "<script>
                alert('Gagal memperbarui data: " . mysqli_error($koneksi) . "');
                window.location.href = '../admin/master_eyewash.php';
              </script>";
    }
} else {
    header("Location: ../admin/master_eyewash.php");
    exit();
}
?>