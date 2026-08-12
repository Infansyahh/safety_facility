<?php
/**
 * proses_unduh_barcode_word.php
 * Terima ids[] + type dari unduh_barcode.php, generate gambar barcode
 * per item (pakai barcode_helper.php), susun ke satu file .docx
 * (grid 5 kolom, A4 landscape, ukuran gambar ngikut barcode_contoh.docx
 * yang dikirim), lalu langsung dikirim sebagai download.
 *
 * Butuh: composer require phpoffice/phpword
 */

ini_set('display_errors', 1);
error_reporting(E_ALL);
if (ob_get_length()) ob_end_clean();
session_start();

require '../vendor/autoload.php';
include '../koneksi.php';
include 'barcode_helper.php';

global $koneksi;

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    die("Akses ditolak.");
}

if (empty($_POST['ids']) || !is_array($_POST['ids'])) {
    die("Tidak ada barcode yang dipilih.");
}

$type = isset($_POST['type']) ? $_POST['type'] : 'lampu';
$allowedTypes = ['lampu', 'lampu_exit', 'eyewash', 'p3k'];
if (!in_array($type, $allowedTypes)) $type = 'lampu';

// type 'lampu' & 'lampu_exit' sama-sama dari master_lampu & pakai template lampu
$tableMap  = [
    'lampu'      => 'master_lampu',
    'lampu_exit' => 'master_lampu',
    'eyewash'    => 'master_eyewash',
    'p3k'        => 'master_p3k',
];
$templateTypeMap = [
    'lampu'      => 'lampu',
    'lampu_exit' => 'lampu',
    'eyewash'    => 'eyewash',
    'p3k'        => 'p3k',
];

$table = $tableMap[$type];
$templateType = $templateTypeMap[$type];

$ids = array_filter(array_map('intval', $_POST['ids']));
if (empty($ids)) die("ID tidak valid.");
$idsIn = implode(',', $ids);

$query = mysqli_query($koneksi, "SELECT * FROM $table WHERE id IN ($idsIn) ORDER BY code ASC");
if (!$query || mysqli_num_rows($query) === 0) {
    die("Data tidak ditemukan.");
}

// ==== Susun Word (layout ngikut barcode_contoh.docx: A4 landscape, margin 1", 5 gambar per baris) ====
$phpWord = new \PhpOffice\PhpWord\PhpWord();

$section = $phpWord->addSection([
    'orientation'  => 'landscape',
    'marginTop'    => 1440,
    'marginBottom' => 1440,
    'marginLeft'   => 1440,
    'marginRight'  => 1440,
]);

// 1.9676in x 1.3957in (sama kaya barcode_contoh.docx terbaru) -> dikonversi ke point (1in = 72pt)
$imgWidthPt  = 150.66;
$imgHeightPt = 109.49;
$perRow = 4;

$tempFiles = [];
$count = 0;
$textRun = null;

try {
    while ($data = mysqli_fetch_assoc($query)) {
        $imgPath = generateBarcodeImage($templateType, $data);
        $tempFiles[] = $imgPath;

        if ($count % $perRow === 0) {
            $textRun = $section->addTextRun();
        }
        $textRun->addImage($imgPath, [
            'width'  => $imgWidthPt,
            'height' => $imgHeightPt,
        ]);
        $count++;
    }

    if ($count === 0) {
        throw new Exception("Tidak ada gambar barcode yang berhasil dibuat.");
    }

    $tempDir = "../proses/temp_qr/";
    if (!file_exists($tempDir)) mkdir($tempDir, 0755, true);
    $tempDocx = $tempDir . 'barcode_export_' . uniqid() . '.docx';

    $writer = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
    $writer->save($tempDocx);

    // Bersihkan gambar sementara
    foreach ($tempFiles as $f) {
        if (file_exists($f)) unlink($f);
    }

    $fileName = 'Barcode_' . strtoupper($type) . '_' . date('Ymd_His') . '.docx';

    header('Content-Description: File Transfer');
    header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
    header('Content-Disposition: attachment; filename="' . $fileName . '"');
    header('Content-Transfer-Encoding: binary');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Content-Length: ' . filesize($tempDocx));

    readfile($tempDocx);
    unlink($tempDocx);
    exit;

} catch (Exception $e) {
    foreach ($tempFiles as $f) {
        if (file_exists($f)) unlink($f);
    }
    die("Gagal membuat file Word: " . $e->getMessage());
}