<?php

global $koneksi;

// Header ini memberi tahu Excel bahwa ini adalah file HTML yang bisa dibuka sebagai spreadsheet
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=Data_Lampu_Emergency.xls");
header("Pragma: no-cache");
header("Expires: 0");

include '../koneksi.php';
?>

<table border="1">
    <thead>
        <tr>
            <th style="background-color: #0044aa;">No</th>
            <th style="background-color: #0044aa;">Kode Lampu</th>
            <th style="background-color: #0044aa;">Line Area</th>
            <th style="background-color: #0044aa;">Lokasi</th>
            <th style="background-color: #0044aa;">Indikator</th>
            <th style="background-color: #0044aa;">Lampu Mati</th>
            <th style="background-color: #0044aa;">Nyala Otomatis</th>
            <th style="background-color: #0044aa;">Kondisi</th>
            <th style="background-color: #0044aa;">Catatan</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $no = 1;
        // Ganti bagian query SQL Anda di file export_lampu.php menjadi seperti ini:
$sql = mysqli_query($koneksi, "SELECT * FROM master_lampu WHERE code LIKE 'LPE%'");
        while($d = mysqli_fetch_array($sql)){
        ?>
        <tr>
            <td><?php echo $no++; ?></td>
            <td><?php echo $d['code']; ?></td>
            <td><?php echo $d['line_area']; ?></td>
            <td><?php echo $d['lokasi']; ?></td>
            <td><?php echo $d['indikator_mati_menyala']; ?></td>
            <td><?php echo $d['lampu_mati']; ?></td>
            <td><?php echo $d['nyala_otomatis']; ?></td>
            <td><?php echo $d['kondisi']; ?></td>
            <td><?php echo $d['catatan']; ?></td>
        </tr>
        <?php } ?>
    </tbody>
</table>