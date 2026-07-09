<?php

global $koneksi;

// Header ini memberi tahu Excel bahwa ini adalah file HTML yang bisa dibuka sebagai spreadsheet
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=Data_Kotak_P3K.xls");
header("Pragma: no-cache");
header("Expires: 0");

include '../koneksi.php';
?>

<table border="1">
    <thead>
        <tr>
            <th style="background-color: #0044aa; color: white;">No</th>
            <th style="background-color: #0044aa; color: white;">Kode P3K</th>
            <th style="background-color: #0044aa; color: white;">Line Area</th>
            <th style="background-color: #0044aa; color: white;">Lokasi</th>
            <th style="background-color: #0044aa; color: white;">Kondisi Kotak</th>
            <th style="background-color: #0044aa; color: white;">Isi Kotak</th>
            <th style="background-color: #0044aa; color: white;">Obat-obatan</th>
            <th style="background-color: #0044aa; color: white;">Kondisi</th>
            <th style="background-color: #0044aa; color: white;">Catatan</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $no = 1;
        $sql = mysqli_query($koneksi, "SELECT * FROM master_p3k ORDER BY code ASC");
        while($d = mysqli_fetch_array($sql)){
        ?>
        <tr>
            <td><?php echo $no++; ?></td>
            <td><?php echo $d['code']; ?></td>
            <td><?php echo $d['line_area']; ?></td>
            <td><?php echo $d['lokasi']; ?></td>
            <td><?php echo $d['kondisi_kotak']; ?></td>
            <td><?php echo $d['kelengkapan_isi']; ?></td>
            <td><?php echo $d['expired_obat']; ?></td>
            <td><?php echo $d['kondisi']; ?></td>
            <td><?php echo $d['catatan']; ?></td>
        </tr>
        <?php } ?>
    </tbody>
</table>
