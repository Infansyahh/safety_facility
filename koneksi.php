<?php
$host     = "sql308.infinityfree.com";
$user     = "if0_42292966";
$pass     = "corinthianbgr26";
$db_name  = "if0_42292966_safety_facility";
$koneksi = mysqli_connect($host, $user, $pass, $db_name);

if (!$koneksi) {
    die("Koneksi ke database 'safety_facility' gagal: " . mysqli_connect_error());
}
