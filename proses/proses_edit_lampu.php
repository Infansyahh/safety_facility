<?php
session_start();
include '../koneksi.php';

global $koneksi;

if (isset($_POST['submit'])) {

    $id = $_POST['id'];
    $code = $_POST['code'];
    $merek = $_POST['merek'];
    $line_area = $_POST['line_area'];
    $lokasi = $_POST['lokasi'];
    $catatan = $_POST['catatan'];

    // Jika Lampu Exit (diawali LE)
    if (stripos($code, 'LE') === 0) {
        $indikator = $_POST['indikator_mati_menyala'] ?? 'Nyala';
        $lampu_mati = (strtolower($indikator) === 'mati') ? 'Ya' : 'Tidak';
        $otomatis = 'Tidak';
        $kondisi = (strtolower($indikator) === 'mati') ? 'rusak' : 'baik';
    } else {
        $indikator = $_POST['indikator_mati_menyala'] ?? 'Nyala';
        $lampu_mati = $_POST['lampu_mati'] ?? 'Tidak';
        $otomatis = $_POST['nyala_otomatis'] ?? 'Tidak';
        $kondisi = (strtolower($lampu_mati) == 'ya') ? 'rusak' : 'baik';
    }

    // Mengambil nama operator dari popup Dashboard
    $user = $_SESSION['nama_operator_popup'] ?? $_SESSION['nama_lengkap'] ?? 'Admin';

    // 1. Pastikan nama kolom di bawah ini (seperti indikator_mati_menyala) SUDAH PASTI SAMA dengan di phpMyAdmin
    $query = "UPDATE master_lampu SET 
                code = ?, 
                merek = ?, 
                line_area = ?, 
                lokasi = ?, 
                indikator_mati_menyala = ?, 
                lampu_mati = ?, 
                nyala_otomatis = ?, 
                catatan = ?, 
                kondisi = ? 
              WHERE id = ?";

    $stmt = mysqli_prepare($koneksi, $query);
    
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "sssssssssi", $code, $merek, $line_area, $lokasi, $indikator, $lampu_mati, $otomatis, $catatan, $kondisi, $id);
        
        if (mysqli_stmt_execute($stmt)) {

            if (stripos($code, 'LE') === 0) {
                // Lampu Exit -> masuk ke tabel inspeksi_lampu_exit (dipakai laporan_lampu_exit.php)
                $id_user = $_SESSION['id_user'] ?? null;
                if (!$id_user) {
                    // fallback cari id_user dari username jika session id_user belum ada
                    $q_user = mysqli_prepare($koneksi, "SELECT id_user FROM users WHERE nama_lengkap = ? OR username = ? LIMIT 1");
                    mysqli_stmt_bind_param($q_user, "ss", $user, $user);
                    mysqli_stmt_execute($q_user);
                    $r_user = mysqli_stmt_get_result($q_user);
                    if ($row_user = mysqli_fetch_assoc($r_user)) {
                        $id_user = $row_user['id_user'];
                    }
                }

                $kondisi_lampu = (strtolower($indikator) === 'mati') ? 'Tidak' : 'Baik';
                $kondisi_fisik = 'Baik';
                $kondisi_tulisan = 'Baik';
                $nama_operator = $_SESSION['nama_operator_popup'] ?? $user;

                $query_inspeksi = "INSERT INTO inspeksi_lampu_exit (id_lampu, id_user, nama_operator, tanggal_cek, kondisi_fisik, kondisi_lampu, kondisi_tulisan, keterangan) VALUES (?, ?, ?, NOW(), ?, ?, ?, ?)";
                $stmt_inspeksi = mysqli_prepare($koneksi, $query_inspeksi);
                mysqli_stmt_bind_param($stmt_inspeksi, "sisssss", $code, $id_user, $nama_operator, $kondisi_fisik, $kondisi_lampu, $kondisi_tulisan, $catatan);
                mysqli_stmt_execute($stmt_inspeksi);
            } else {
                // Selain Lampu Exit -> tetap ke tabel inspeksi_lampu lama
                $query_inspeksi = "INSERT INTO inspeksi_lampu (code_lampu, username, tanggal_inspeksi, kondisi, catatan) VALUES (?, ?, NOW(), ?, ?)";
                $stmt_inspeksi = mysqli_prepare($koneksi, $query_inspeksi);
                mysqli_stmt_bind_param($stmt_inspeksi, "ssss", $code, $user, $kondisi, $catatan);
                mysqli_stmt_execute($stmt_inspeksi);
            }

            $redirect_url = (stripos($code, 'LE') === 0) ? '../admin/lampu_exit.php' : '../admin/master_lampu.php';
            echo "<script>
                    alert('Data berhasil diperbarui!');
                    window.location='$redirect_url';
                  </script>";
        } else {
            echo "<script>
                    alert('Error : " . mysqli_stmt_error($stmt) . "');
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