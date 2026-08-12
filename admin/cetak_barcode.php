<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
if (ob_get_length()) ob_end_clean();
session_start();
include '../koneksi.php';
include '../phpqrcode/qrlib.php'; 

global $koneksi;

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    die("Akses ditolak.");
}

if (!isset($_GET['id'])) {
    die("ID Alat tidak ditemukan.");
}

$id = (int)$_GET['id'];
$type = isset($_GET['type']) ? $_GET['type'] : 'lampu';

if ($type === 'p3k') {
    $query = mysqli_query($koneksi, "SELECT * FROM master_p3k WHERE id = $id");
    $data = mysqli_fetch_assoc($query);
    if (!$data) die("Data Kotak P3K tidak ditemukan.");
} else {
    $query = mysqli_query($koneksi, "SELECT * FROM master_lampu WHERE id = $id");
    $data = mysqli_fetch_assoc($query);
    if (!$data) die("Data lampu tidak ditemukan.");
}

if ($type === 'p3k') {
    $templatePath = '../foto/template_barcode_p3k.png';
    $newQrWidth = 520; $newQrHeight = 520; $qrX = 1060; $qrY = 460;
    $codeX = 460; $codeY = 558; $codeFontSize = 35;
    $lokasiX = 460; $lokasiY = 700; $lokasiFontSize = 25; $lokasiMaxWidth = 300;
} else {
    $templatePath = '../foto/template_barcode_exit.png';
    $newQrWidth = 524; $newQrHeight = 524; $qrX = 960; $qrY = 360;
    $codeX = 459; $codeY = 577; $codeFontSize = 31;
    $lokasiX = 459; $lokasiY = 686; $lokasiFontSize = 27; $lokasiMaxWidth = 382;
}
if (!file_exists($templatePath)) die("Error: File template tidak ditemukan.");

$tempDir = "../proses/temp_qr/";
if (!file_exists($tempDir)) mkdir($tempDir, 0755, true);

$qrContent = $data['code']; 
$qrFile = $tempDir . 'qr_' . $id . '.png';

QRcode::png($qrContent, $qrFile, QR_ECLEVEL_Q, 20, 1);

$sourceImg = imagecreatefrompng($templatePath);
$qrImg = imagecreatefrompng($qrFile);

imagecopyresampled($sourceImg, $qrImg, $qrX, $qrY, 0, 0, $newQrWidth, $newQrHeight, imagesx($qrImg), imagesy($qrImg));

$textColor = imagecolorallocate($sourceImg, 0, 0, 0);
$fontPath = '../fonts/Poppins-Bold.ttf';

if (file_exists($fontPath)) {
    imagettftext($sourceImg, $codeFontSize, 0, $codeX, $codeY, $textColor, $fontPath, $data['code']);
    
    $textLokasi = $data['lokasi'];
    $fontSize = $lokasiFontSize;
    $startX = $lokasiX;
    $startY = $lokasiY;
    $lineHeight = 50;
    $maxWidth = $lokasiMaxWidth; 

    $words = explode(' ', $textLokasi);
    $currentLine = '';

    foreach ($words as $word) {
        $testLine = $currentLine . ($currentLine === '' ? '' : ' ') . $word;
        $box = imagettfbbox($fontSize, 0, $fontPath, $testLine);
        $textWidth = abs($box[2] - $box[0]);

        if ($textWidth > $maxWidth && $currentLine !== '') {
            imagettftext($sourceImg, $fontSize, 0, $startX, $startY, $textColor, $fontPath, $currentLine);
            $currentLine = $word;
            $startY += $lineHeight;
        } else {
            $currentLine = $testLine;
        }
    }
    if ($currentLine !== '') imagettftext($sourceImg, $fontSize, 0, $startX, $startY, $textColor, $fontPath, $currentLine);
} else {
    imagestring($sourceImg, 5, $codeX, $codeY - 20, $data['code'], $textColor);
    imagestring($sourceImg, 5, $lokasiX, $lokasiY - 20, $data['lokasi'], $textColor);
}

header('Content-Type: image/png');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

imagepng($sourceImg, NULL, 0);
imagedestroy($sourceImg);
imagedestroy($qrImg);
if (file_exists($qrFile)) unlink($qrFile);
exit;
?>