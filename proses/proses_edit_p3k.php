<?php
session_start();
include '../koneksi.php';

global $koneksi;

if (isset($_POST['submit'])) {
    $id = $_POST['id'];
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

    // Mengambil nama operator dari popup Dashboard
    $username = $_SESSION['nama_operator_popup'] ?? $_SESSION['nama_lengkap'] ?? $_SESSION['username'] ?? 'Admin';
    $id_user = $_SESSION['id_user'] ?? NULL;

    $query = "UPDATE master_p3k SET 
                code = ?, 
                line_area = ?, 
                lokasi = ?, 
                kondisi_kotak = ?, 
                kelengkapan_isi = ?, 
                expired_obat = ?, 
                catatan = ?, 
                kondisi = ? 
              WHERE id = ?";

    $stmt = mysqli_prepare($koneksi, $query);
    
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "ssssssssi", $code, $line_area, $lokasi, $kondisi_kotak, $kelengkapan_isi, $expired_obat, $catatan, $kondisi, $id);
        
        if (mysqli_stmt_execute($stmt)) {
            // Insert ke tabel inspeksi_p3k
            $query_inspeksi = "INSERT INTO inspeksi_p3k (code_p3k, id_user, line_area, kondisi_kotak, kelengkapan_isi, expired_obat, keterangan, tanggal_inspeksi, kondisi, catatan, username) 
                               VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), ?, ?, ?)";
            $stmt_inspeksi = mysqli_prepare($koneksi, $query_inspeksi);
            if ($stmt_inspeksi) {
                mysqli_stmt_bind_param($stmt_inspeksi, "sissssssss", $code, $id_user, $line_area, $kondisi_kotak, $kelengkapan_isi, $expired_obat, $catatan, $kondisi, $catatan, $username);
                mysqli_stmt_execute($stmt_inspeksi);
            }

            echo "<script>
                    alert('Data P3K berhasil diperbarui!');
                    window.location='../admin/master_p3k.php';
                  </script>";
        } else {
            echo "<script>
                    alert('Error Update: " . mysqli_stmt_error($stmt) . "');
                    window.history.back();
                  </script>";
        }
    } else {
        echo "<script>
                alert('Error Prepare: " . mysqli_error($koneksi) . "');
                window.history.back();
              </script>";
    }
}
?>
