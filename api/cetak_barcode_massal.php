<?php
/**
 * api/cetak_barcode_massal.php - Unduh barcode massal ke dokumen Word (.docx)
 * Param: type = lampu | p3k | eyewash, ids = "1,2,3" (maks 30)
 */
ini_set('display_errors', 0);
error_reporting(E_ALL);
ob_start();
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/../phpqrcode/qrlib.php';
require_once __DIR__ . '/../vendor/autoload.php';

use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;

if (!is_logged_in()) {
    die('Akses ditolak.');
}

if (!isset($_GET['ids']) || trim($_GET['ids']) === '') {
    die('Tidak ada data dipilih.');
}

@ini_set('memory_limit', '512M');
@set_time_limit(300);

$type = isset($_GET['type']) ? $_GET['type'] : 'lampu';
$ids = array_filter(array_map('intval', explode(',', $_GET['ids'])));

if (empty($ids)) {
    die('ID tidak valid.');
}

$MAX_CETAK_SEKALIGUS = 30;
if (count($ids) > $MAX_CETAK_SEKALIGUS) {
    die('Terlalu banyak data dipilih (' . count($ids) . '). Maksimal ' . $MAX_CETAK_SEKALIGUS . ' barcode sekali cetak. Silakan pilih lebih sedikit, atau cetak bertahap per halaman.');
}

switch ($type) {
    case 'p3k':
        $table = 'master_p3k';
        $templatePath = __DIR__ . '/../foto/template_barcode_p3k.png';
        break;
    case 'eyewash':
        $table = 'master_eyewash';
        $templatePath = __DIR__ . '/../foto/template_barcode_eyewash.png';
        break;
    default:
        $type = 'lampu';
        $table = 'master_lampu';
        $templatePath = __DIR__ . '/../foto/template_barcode.png';
        break;
}

if (!file_exists($templatePath)) {
    die('Error: File template tidak ditemukan.');
}

$tempDir = __DIR__ . '/../proses/temp_qr/';
if (!file_exists($tempDir)) {
    mkdir($tempDir, 0755, true);
}

$fontPath = __DIR__ . '/../fonts/Poppins-Bold.ttf';

function renderLabelBarcode($data, $templatePath, $tempDir, $fontPath, $type)
{
    $qrContent = $data['code'];
    $qrFile = $tempDir . 'qr_' . $type . '_' . $data['id'] . '_' . uniqid() . '.png';
    QRcode::png($qrContent, $qrFile, QR_ECLEVEL_Q, 20, 1);

    $sourceImg = imagecreatefrompng($templatePath);
    $qrImg = imagecreatefrompng($qrFile);

    $newQrWidth = 520;
    $newQrHeight = 520;
    $qrX = 1060;
    $qrY = 460;
    imagecopyresampled($sourceImg, $qrImg, $qrX, $qrY, 0, 0, $newQrWidth, $newQrHeight, imagesx($qrImg), imagesy($qrImg));

    $textColor = imagecolorallocate($sourceImg, 0, 0, 0);

    if (file_exists($fontPath)) {
        imagettftext($sourceImg, 35, 0, 460, 558, $textColor, $fontPath, $data['code']);

        $textLokasi = $data['lokasi'] ?? '';
        $fontSize = 25;
        $startX = 460;
        $startY = 700;
        $lineHeight = 50;
        $maxWidth = 300;

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

$generatedFiles = [];
foreach ($ids as $id) {
    $q = mysqli_query($koneksi, "SELECT * FROM $table WHERE id = $id");
    $data = $q ? mysqli_fetch_assoc($q) : null;
    if (!$data) {
        continue;
    }
    $generatedFiles[] = renderLabelBarcode($data, $templatePath, $tempDir, $fontPath, $type);
}

if (empty($generatedFiles)) {
    die('Data tidak ditemukan untuk ID yang dipilih.');
}

$phpWord = new PhpWord();
$section = $phpWord->addSection([
    'marginLeft' => 600,
    'marginRight' => 600,
    'marginTop' => 600,
    'marginBottom' => 600,
]);

$imgStyle = ['width' => 230, 'height' => 240, 'alignment' => 'center'];
$cellStyle = ['valign' => 'center'];
$cellWidth = 4500;
$perRow = 2;

$table = $section->addTable(['borderSize' => 0]);
$col = 0;

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
if ($col !== 0) {
    while ($col < $perRow) {
        $table->addCell($cellWidth, $cellStyle);
        $col++;
    }
}

$filename = 'barcode_' . $type . '_' . date('Ymd_His') . '.docx';

while (ob_get_level() > 0) {
    ob_end_clean();
}

header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: max-age=0');

$objWriter = IOFactory::createWriter($phpWord, 'Word2007');
$objWriter->save('php://output');

foreach ($generatedFiles as $f) {
    if (file_exists($f)) {
        unlink($f);
    }
}
exit;