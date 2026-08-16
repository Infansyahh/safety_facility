<?php
/**
 * api/master_eyewash.php - CRUD Master Eye Wash + scan check
 * Action: list | next_code | area_list | create | update | delete | scan_check
 */
require_once __DIR__ . '/config.php';
require_login();

$action = api_action('list');

if ($action === 'area_list') {
    $daftar = [];
    $q = mysqli_query($koneksi, "SELECT nama_line FROM area_line WHERE jenis = 'eyewash' ORDER BY nama_line ASC");
    while ($r = mysqli_fetch_assoc($q)) {
        $daftar[] = $r['nama_line'];
    }
    api_response(['success' => true, 'data' => $daftar]);
}

if ($action === 'next_code') {
    $q = mysqli_query($koneksi, "SELECT code FROM master_eyewash WHERE code LIKE 'EYE%' ORDER BY id DESC LIMIT 1");
    $next_code = 'EYE01';
    if ($q && mysqli_num_rows($q) > 0) {
        $max_code = mysqli_fetch_assoc($q)['code'];
        $num = (int) substr($max_code, 3);
        $next_code = 'EYE' . sprintf('%02d', $num + 1);
    }
    api_response(['success' => true, 'next_code' => $next_code]);
}

if ($action === 'scan_check') {
    $scan_id = mysqli_real_escape_string($koneksi, api_get('scan_id', ''));
    $q = mysqli_query($koneksi, "SELECT * FROM master_eyewash WHERE id = '$scan_id' OR code = '$scan_id'");
    if ($q && mysqli_num_rows($q) > 0) {
        $scan_data = mysqli_fetch_assoc($q);
        $code_eyewash = $scan_data['code'];
        $q_cek = mysqli_query($koneksi, "SELECT tanggal_inspeksi FROM inspeksi_eyewash
            WHERE code_eyewash = '$code_eyewash'
            AND tanggal_inspeksi >= DATE_SUB(NOW(), INTERVAL 1 MONTH)
            ORDER BY tanggal_inspeksi DESC LIMIT 1");
        if ($q_cek && mysqli_num_rows($q_cek) > 0) {
            $tanggal_terakhir = mysqli_fetch_assoc($q_cek)['tanggal_inspeksi'];
            $tanggal_bisa_kembali = date('d F Y', strtotime($tanggal_terakhir . ' + 1 month'));
            $tanggal_terakhir_indo = date('d F Y', strtotime($tanggal_terakhir));
            api_response([
                'success' => true,
                'blocked' => true,
                'message' => "Maaf, Alat dengan kode '$code_eyewash' sudah diinspeksi pada $tanggal_terakhir_indo. Berdasarkan aturan 1 bulan sekali, alat ini baru bisa diinspeksi kembali pada tanggal $tanggal_bisa_kembali!",
            ]);
        }
        api_response(['success' => true, 'blocked' => false, 'data' => $scan_data]);
    }
    api_response([
        'success' => true,
        'blocked' => true,
        'message' => "Data Eye Wash dengan kode '" . htmlspecialchars($scan_id) . "' tidak ditemukan atau bukan bagian dari sistem data master Eye Wash.",
    ]);
}

if ($action === 'list') {
    $search = trim(api_get('search', ''));
    $limit = (int) api_get('limit', 10);
    $limit_options = [10, 25, 50, 100];
    if (!in_array($limit, $limit_options)) {
        $limit = 10;
    }
    $page = (int) api_get('page', 1);
    if ($page < 1) {
        $page = 1;
    }
    $offset = ($page - 1) * $limit;

    $search_sql = '';
    if ($search !== '') {
        $s = mysqli_real_escape_string($koneksi, $search);
        $search_sql = " WHERE (me.code LIKE '%$s%' OR me.line_area LIKE '%$s%' OR me.lokasi LIKE '%$s%' OR me.catatan LIKE '%$s%' OR ie.username LIKE '%$s%')";
    }

    $join_sql = "FROM master_eyewash me
        LEFT JOIN (
            SELECT code_eyewash, username, tanggal_inspeksi
            FROM inspeksi_eyewash
            WHERE id_inspeksi IN (SELECT MAX(id_inspeksi) FROM inspeksi_eyewash GROUP BY code_eyewash)
        ) ie ON me.code = ie.code_eyewash" . $search_sql;

    $query_total = mysqli_query($koneksi, "SELECT COUNT(*) AS total $join_sql");
    $total_rows = 0;
    if ($query_total) {
        $total_rows = (int) mysqli_fetch_assoc($query_total)['total'];
    }
    $total_pages = $total_rows > 0 ? (int) ceil($total_rows / $limit) : 1;
    if ($page > $total_pages) {
        $page = $total_pages;
        $offset = ($page - 1) * $limit;
    }

    $rows = [];
    $query = mysqli_query($koneksi, "SELECT me.*, ie.username, ie.tanggal_inspeksi $join_sql ORDER BY me.code ASC LIMIT $limit OFFSET $offset");
    if ($query) {
        while ($r = mysqli_fetch_assoc($query)) {
            $rows[] = $r;
        }
    }

    api_response([
        'success' => true,
        'data' => $rows,
        'total_rows' => $total_rows,
        'total_pages' => $total_pages,
        'page' => $page,
        'limit' => $limit,
        'limit_options' => $limit_options,
        'search' => $search,
        'offset' => $offset,
    ]);
}

function eyewash_build_catatan($data)
{
    $catatan = isset($data['catatan']) ? $data['catatan'] : '';
    if ($catatan === '') {
        $air = isset($data['cek_air']) ? $data['cek_air'] : 'Aliran Lancar';
        $kondisiAir = isset($data['cek_kondisi_air']) ? $data['cek_kondisi_air'] : 'Air Bersih';
        $kotak = isset($data['cek_kotak']) ? $data['cek_kotak'] : 'Kotak Bagus';
        $catatan = $air . ', ' . $kondisiAir . ', ' . $kotak;
    }
    return $catatan;
}

function eyewash_auto_kondisi($catatan)
{
    $c = strtolower($catatan);
    return (strpos($c, 'tidak lancar') !== false || strpos($c, 'kotor') !== false || strpos($c, 'tidak bagus') !== false) ? 'rusak' : 'baik';
}

if ($action === 'create') {
    $data = api_input();
    $code = isset($data['code']) ? trim($data['code']) : '';
    $line_area = isset($data['line_area']) ? $data['line_area'] : '';
    $lokasi = isset($data['lokasi']) ? $data['lokasi'] : '';
    $kondisi = isset($data['kondisi']) ? $data['kondisi'] : 'baik';
    $catatan = eyewash_build_catatan($data);
    if ($kondisi === '') {
        $kondisi = eyewash_auto_kondisi($catatan);
    }

    if ($code === '' || $lokasi === '') {
        api_response(['success' => false, 'message' => 'Kode dan lokasi wajib diisi.'], 400);
    }

    $stmt = mysqli_prepare($koneksi, "INSERT INTO master_eyewash (code, line_area, lokasi, kondisi, catatan) VALUES (?, ?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, 'sssss', $code, $line_area, $lokasi, $kondisi, $catatan);
    $ok = mysqli_stmt_execute($stmt);

    if ($ok) {
        api_response(['success' => true, 'message' => 'Data berhasil disimpan.']);
    }
    api_response(['success' => false, 'message' => 'Gagal menyimpan data: ' . mysqli_error($koneksi)], 500);
}

if ($action === 'update') {
    $data = api_input();
    $id = (int) ($data['id'] ?? 0);
    $code = isset($data['code']) ? trim($data['code']) : '';
    $line_area = isset($data['line_area']) ? $data['line_area'] : '';
    $lokasi = isset($data['lokasi']) ? $data['lokasi'] : '';
    $kondisi = isset($data['kondisi']) ? $data['kondisi'] : 'baik';
    $catatan = eyewash_build_catatan($data);
    if ($kondisi === '') {
        $kondisi = eyewash_auto_kondisi($catatan);
    }

    if ($id < 1) {
        api_response(['success' => false, 'message' => 'ID tidak valid.'], 400);
    }

    $operator = api_operator_name();
    $tanggal_hari_ini = date('Y-m-d');

    $upd = mysqli_prepare($koneksi, "UPDATE master_eyewash SET code = ?, line_area = ?, lokasi = ?, kondisi = ?, catatan = ? WHERE id = ?");
    mysqli_stmt_bind_param($upd, 'sssssi', $code, $line_area, $lokasi, $kondisi, $catatan, $id);
    $ok = mysqli_stmt_execute($upd);

    if (!$ok) {
        api_response(['success' => false, 'message' => 'Gagal memperbarui data: ' . mysqli_error($koneksi)], 500);
    }

    $ins = mysqli_prepare($koneksi, "INSERT INTO inspeksi_eyewash (code_eyewash, tanggal_inspeksi, kondisi, catatan, username) VALUES (?, ?, ?, ?, ?)");
    mysqli_stmt_bind_param($ins, 'sssss', $code, $tanggal_hari_ini, $kondisi, $catatan, $operator);
    $ins_ok = mysqli_stmt_execute($ins);

    if (!$ins_ok) {
        api_response(['success' => false, 'message' => 'Data master berhasil diperbarui, tapi gagal mencatat riwayat inspeksi.'], 500);
    }

    api_response(['success' => true, 'message' => 'Data berhasil diperbarui.']);
}

if ($action === 'delete') {
    $id = (int) api_get('id', 0);
    $del = mysqli_query($koneksi, "DELETE FROM master_eyewash WHERE id = $id");
    if ($del) {
        api_response(['success' => true, 'message' => 'Data berhasil dihapus.']);
    }
    api_response(['success' => false, 'message' => 'Gagal menghapus data: ' . mysqli_error($koneksi)], 500);
}

api_response(['success' => false, 'message' => 'Action tidak dikenal.'], 404);