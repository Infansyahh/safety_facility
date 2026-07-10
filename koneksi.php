<?php
$host     = "localhost";
$user     = "root";
$pass     = "";
$db_name  = "safety_facility";
$koneksi = mysqli_connect($host, $user, $pass, $db_name);

if (!$koneksi) {
    die("Koneksi ke database 'safety_facility' gagal: " . mysqli_connect_error());
}
