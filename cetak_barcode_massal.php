<?php
// Error TETAP dicatat ke log, tapi JANGAN ditampilkan ke output
// (kalau ditampilkan, teks error nyampur ke file .docx -> file jadi corrupt/gak kebuka)
ini_set('display_errors', 0);
error_reporting(E_ALL);
ob_start(); // tangkap semua output nyasar (warning/notice) biar bisa dibuang sebelum file docx dikirim
session_start();
include '../koneksi.php';
include '../phpqrcode/qrlib.php';
require '../vendor/autoload.php';

use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;

global $koneksi;

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    die("Akses ditolak.");
}

if (!isset($_GET['ids']) || trim($_GET['ids']) === '') {
    die("Tidak ada data dipilih.");
}

// Naikin limit resource kalau hosting izinin (proses gambar banyak = berat)
@ini_set('memory_limit', '512M');
@set_time_limit(300);

$type = isset($_GET['type']) ? $_GET['type'] : 'lampu';
$ids  = array_filter(array_map('intval', explode(',', $_GET['ids'])));

if (empty($ids)) {
    die("ID tidak valid.");
}

// Batasi jumlah cetak sekaligus biar server gak overload/crash (ERR_INVALID_RESPONSE)
$MAX_CETAK_SEKALIGUS = 30;
if (count($ids) > $MAX_CETAK_SEKALIGUS) {
    die("Terlalu banyak data dipilih (" . count($ids) . "). Maksimal $MAX_CETAK_SEKALIGUS barcode sekali cetak. Silakan pilih lebih sedikit, atau cetak bertahap per halaman.");
}

// Konfigurasi per tipe (tabel + template gambar)
switch ($type) {
    case 'p3k':
        $table        = 'master_p3k';
        $templatePath = '../foto/template_barcode_p3k.png';
        break;
    case 'eyewash':
        $table        = 'master_eyewash';
        $templatePath = '../foto/template_barcode_eyewash.png';
        break;
    default:
        $type         = 'lampu';
        $table        = 'master_lampu';
        $templatePath = '../foto/template_barcode.png';
        break;
}

if (!file_exists($templatePath)) {
    die("Error: File template tidak ditemukan.");
}

$tempDir = "../proses/temp_qr/";
if (!file_exists($tempDir)) {
    mkdir($tempDir, 0755, true);
}

$fontPath = '../fonts/Poppins-Bold.ttf';

/**
 * Render 1 label barcode (template + QR + teks) ke file PNG sementara.
 * Logikanya sama persis dengan cetak_barcode.php / cetak_barcode_eyewash.php.
 */
function renderLabelBarcode($data, $templatePath, $tempDir, $fontPath, $type)
{
    $qrContent = $data['code'];
    $qrFile    = $tempDir . 'qr_' . $type . '_' . $data['id'] . '.png';
    QRcode::png($qrContent, $qrFile, QR_ECLEVEL_Q, 20, 1);

    $sourceImg = imagecreatefrompng($templatePath);
    $qrImg     = imagecreatefrompng($qrFile);

    $newQrWidth  = 520;
    $newQrHeight = 520;
    $qrX = 1060;
    $qrY = 460;
    imagecopyresampled($sourceImg, $qrImg, $qrX, $qrY, 0, 0, $newQrWidth, $newQrHeight, imagesx($qrImg), imagesy($qrImg));

    $textColor = imagecolorallocate($sourceImg, 0, 0, 0);

    if (file_exists($fontPath)) {
        imagettftext($sourceImg, 35, 0, 460, 558, $textColor, $fontPath, $data['code']);

        $textLokasi = $data['lokasi'] ?? '';
        $fontSize   = 25;
        $startX     = 460;
        $startY     = 700;
        $lineHeight = 50;
        $maxWidth   = 300;

        $words       = explode(' ', $textLokasi);
        $currentLine = '';

        foreach ($words as $word) {
            $testLine  = $currentLine . ($currentLine === '' ? '' : ' ') . $word;
            $box       = imagettfbbox($fontSize, 0, $fontPath, $testLine);
            $textWidth = abs($box[2] - $box[0]);

            if ($textWidth > $maxWidth && $currentLine !== '') {
                imagettftext($sourceImg, $fontSize, 0, $startX, $startY, $textColor, $fontPath, $currentLine);
                $currentLine = $word;
                $startY += $lineHeight;
            } else {
                $currentLine = $testLine;
            }
        }
        if ($currentLine !== '') {
            imagettftext($sourceImg, $fontSize, 0, $startX, $startY, $textColor, $fontPath, $currentLine);
        }
    } else {
        imagestring($sourceImg, 5, 560, 550, $data['code'], $textColor);
        imagestring($sourceImg, 5, 560, 730, $data['lokasi'] ?? '', $textColor);
    }

    $outFile = $tempDir . 'label_' . $type . '_' . $data['id'] . '_' . uniqid() . '.png';
    imagepng($sourceImg, $outFile);
    imagedestroy($sourceImg);
    imagedestroy($qrImg);
    if (file_exists($qrFile)) {
        unlink($qrFile);
    }

    return $outFile;
}

// Generate label untuk tiap id terpilih
$generatedFiles = [];
foreach ($ids as $id) {
    $q    = mysqli_query($koneksi, "SELECT * FROM $table WHERE id = $id");
    $data = $q ? mysqli_fetch_assoc($q) : null;
    if (!$data) {
        continue;
    }
    $generatedFiles[] = renderLabelBarcode($data, $templatePath, $tempDir, $fontPath, $type);
}

if (empty($generatedFiles)) {
    die("Data tidak ditemukan untuk ID yang dipilih.");
}

// Susun semua label ke 1 dokumen Word, grid 2 kolom per baris
$phpWord = new PhpWord();
$section = $phpWord->addSection([
    'marginLeft'   => 600,
    'marginRight'  => 600,
    'marginTop'    => 600,
    'marginBottom' => 600,
]);

$imgStyle  = ['width' => 230, 'height' => 240, 'alignment' => 'center'];
$cellStyle = ['valign' => 'center'];
$cellWidth = 4500; // twips (~3.1in)
$perRow    = 2;

$table = $section->addTable(['borderSize' => 0]);
$col   = 0;

foreach ($generatedFiles as $imgPath) {
    if ($col === 0) {
        $table->addRow();
    }
    $cell = $table->addCell($cellWidth, $cellStyle);
    $cell->addImage($imgPath, $imgStyle);
    $col++;
    if ($col >= $perRow) {
        $col = 0;
    }
}
// lengkapi sel kosong di baris terakhir biar rapi
if ($col !== 0) {
    while ($col < $perRow) {
        $table->addCell($cellWidth, $cellStyle);
        $col++;
    }
}

$filename = 'barcode_' . $type . '_' . date('Ymd_His') . '.docx';

// buang semua output/warning yang kejebak di buffer sebelum kirim file
while (ob_get_level() > 0) {
    ob_end_clean();
}

header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: max-age=0');

$objWriter = IOFactory::createWriter($phpWord, 'Word2007');
$objWriter->save('php://output');

// bersihkan file sementara
foreach ($generatedFiles as $f) {
    if (file_exists($f)) {
        unlink($f);
    }
}
exit;