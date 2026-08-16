<?php
/**
 * api/scan.php - Pencarian barcode/QR (router scan)
 * Param: scan_id
 * Mengembalikan JSON: found, table, target, code, scan_id, action
 */
require_once __DIR__ . '/config.php';
require_login();

$scan_id = api_get('scan_id', '');
if ($scan_id === '') {
    api_response([
        'success' => false,
        'message' => 'scan_id kosong.',
        'target' => 'scan',
    ], 400);
}

$scan_id = trim($scan_id);
$scan_id = preg_replace('/[\r\n\t]+/', '', $scan_id);

$lookup = function ($table) use ($koneksi, $scan_id) {
    $stmt = mysqli_prepare($koneksi, "SELECT code FROM `$table` WHERE id = ? OR code = ?");
    mysqli_stmt_bind_param($stmt, 'ss', $scan_id, $scan_id);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    return $res ? mysqli_fetch_assoc($res) : null;
};

$found = false;
$table = '';
$target = 'master_lampu';
$code = '';

$r_lampu = $lookup('master_lampu');
if ($r_lampu) {
    $found = true;
    $table = 'lampu';
    $code = $r_lampu['code'];
    $target = (stripos($code, 'LE') === 0) ? 'lampu_exit' : 'master_lampu';
} else {
    $r_p3k = $lookup('master_p3k');
    if ($r_p3k) {
        $found = true;
        $table = 'p3k';
        $target = 'master_p3k';
        $code = $r_p3k['code'];
    } else {
        $r_eyewash = $lookup('master_eyewash');
        if ($r_eyewash) {
            $found = true;
            $table = 'eyewash';
            $target = 'master_eyewash';
            $code = $r_eyewash['code'];
        }
    }
}

api_response([
    'success' => true,
    'found' => $found,
    'table' => $table,
    'target' => $target,
    'code' => $code,
    'scan_id' => $scan_id,
    'action' => 'scan_popup',
]);