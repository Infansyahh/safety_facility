<?php
session_start();
include '../koneksi.php';

global $koneksi;

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: ../login.php");
    exit();
}

if (isset($_GET['scan_id'])) {
    $scan_id = trim($_GET['scan_id']);
    $scan_id = preg_replace('/[\r\n\t]+/', '', $scan_id);
    $scan_id = mysqli_real_escape_string($koneksi, $scan_id);

    // Check master_lampu
    $query_lampu = mysqli_query($koneksi, "SELECT code FROM master_lampu WHERE id = '$scan_id' OR code = '$scan_id'");
    if ($query_lampu && mysqli_num_rows($query_lampu) > 0) {
        $row = mysqli_fetch_assoc($query_lampu);
        $code = $row['code'];
        if (stripos($code, 'LE') === 0) {
            header("Location: ../admin/lampu_exit.php?scan_id=" . urlencode($scan_id) . "&action=scan_popup");
            exit();
        } else {
            header("Location: ../admin/master_lampu.php?scan_id=" . urlencode($scan_id) . "&action=scan_popup");
            exit();
        }
    }

    // Check master_p3k
    $query_p3k = mysqli_query($koneksi, "SELECT code FROM master_p3k WHERE id = '$scan_id' OR code = '$scan_id'");
    if ($query_p3k && mysqli_num_rows($query_p3k) > 0) {
        header("Location: ../admin/master_p3k.php?scan_id=" . urlencode($scan_id) . "&action=scan_popup");
        exit();
    }

    // Check master_eyewash
    $query_eyewash = mysqli_query($koneksi, "SELECT code FROM master_eyewash WHERE id = '$scan_id' OR code = '$scan_id'");
    if ($query_eyewash && mysqli_num_rows($query_eyewash) > 0) {
        header("Location: ../admin/master_eyewash.php?scan_id=" . urlencode($scan_id) . "&action=scan_popup");
        exit();
    }

    // Default: redirect to master_lampu.php which will show the "not found" message
    header("Location: ../admin/master_lampu.php?scan_id=" . urlencode($scan_id) . "&action=scan_popup");
    exit();
} else {
    header("Location: ../admin/scan.php");
    exit();
}
?>