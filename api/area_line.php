<?php
/**
 * api/area_line.php - CRUD Area Line
 * Param jenis: lampu_emergency | lampu_exit | p3k | eyewash
 * Action: list | create | update | delete
 */
require_once __DIR__ . '/config.php';
require_login();

$action = api_action('list');
$jenis = api_get('jenis', 'lampu_emergency');

$allowed_jenis = ['lampu_emergency', 'lampu_exit', 'p3k', 'eyewash'];
if (!in_array($jenis, $allowed_jenis)) {
    $jenis = 'lampu_emergency';
}

$item_config = [
    'lampu_emergency' => ['table' => 'master_lampu', 'filter' => " AND code NOT LIKE 'LE%'"],
    'lampu_exit' => ['table' => 'master_lampu', 'filter' => " AND code LIKE 'LE%'"],
    'p3k' => ['table' => 'master_p3k', 'filter' => ''],
    'eyewash' => ['table' => 'master_eyewash', 'filter' => ''],
];

if ($action === 'list') {
    $cari = trim(api_get('cari', ''));
    $filter = '';
    if ($cari !== '') {
        $s = mysqli_real_escape_string($koneksi, $cari);
        $filter = " AND a.nama_line LIKE '%$s%'";
    }

    $cfg = $item_config[$jenis];
    $table = $cfg['table'];
    $filter_item = $cfg['filter'];
    $sub = "SELECT COUNT(*) FROM `$table` m WHERE m.line_area = a.nama_line" . $filter_item;

    $rows = [];
    $q = mysqli_query($koneksi, "SELECT a.*, ($sub) as total_item
        FROM area_line a WHERE a.jenis = '$jenis'$filter
        ORDER BY a.id_line ASC");
    if ($q) {
        while ($r = mysqli_fetch_assoc($q)) {
            $rows[] = $r;
        }
    }
    api_response(['success' => true, 'data' => $rows, 'jenis' => $jenis, 'cari' => $cari]);
}

if ($action === 'create') {
    $data = api_input();
    $nama_line = isset($data['nama_line']) ? trim($data['nama_line']) : '';
    if ($nama_line === '') {
        api_response(['success' => false, 'message' => 'Nama area / line wajib diisi.'], 400);
    }
    $stmt = mysqli_prepare($koneksi, "INSERT INTO area_line (nama_line, jenis) VALUES (?, ?)");
    mysqli_stmt_bind_param($stmt, 'ss', $nama_line, $jenis);
    $ok = mysqli_stmt_execute($stmt);
    if ($ok) {
        api_response(['success' => true, 'message' => 'Area / line berhasil ditambahkan.']);
    }
    api_response(['success' => false, 'message' => 'Gagal menyimpan: ' . mysqli_error($koneksi)], 500);
}

if ($action === 'update') {
    $data = api_input();
    $id = (int) ($data['id_line'] ?? 0);
    $nama_line = isset($data['nama_line']) ? trim($data['nama_line']) : '';
    if ($id < 1 || $nama_line === '') {
        api_response(['success' => false, 'message' => 'Data tidak valid.'], 400);
    }
    $stmt = mysqli_prepare($koneksi, "UPDATE area_line SET nama_line = ? WHERE id_line = ? AND jenis = ?");
    mysqli_stmt_bind_param($stmt, 'sis', $nama_line, $id, $jenis);
    $ok = mysqli_stmt_execute($stmt);
    if ($ok) {
        api_response(['success' => true, 'message' => 'Area / line berhasil diperbarui.']);
    }
    api_response(['success' => false, 'message' => 'Gagal memperbarui: ' . mysqli_error($koneksi)], 500);
}

if ($action === 'delete') {
    $id = (int) api_get('id_line', 0);
    $cfg = $item_config[$jenis];
    $table = $cfg['table'];
    $filter_item = $cfg['filter'];

    $q = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM `$table` m
        INNER JOIN area_line a ON a.nama_line = m.line_area
        WHERE a.id_line = $id" . $filter_item);
    $terpakai = 0;
    if ($q) {
        $terpakai = (int) mysqli_fetch_assoc($q)['total'];
    }

    if ($terpakai > 0) {
        api_response([
            'success' => false,
            'message' => 'Area / line ini masih memiliki item terdaftar. Hapus atau pindahkan item terlebih dahulu.',
        ], 400);
    }

    $stmt = mysqli_prepare($koneksi, "DELETE FROM area_line WHERE id_line = ? AND jenis = ?");
    mysqli_stmt_bind_param($stmt, 'is', $id, $jenis);
    $ok = mysqli_stmt_execute($stmt);
    if ($ok) {
        api_response(['success' => true, 'message' => 'Area / line berhasil dihapus.']);
    }
    api_response(['success' => false, 'message' => 'Gagal menghapus: ' . mysqli_error($koneksi)], 500);
}

api_response(['success' => false, 'message' => 'Action tidak dikenal.'], 404);