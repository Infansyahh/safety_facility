<?php
/**
 * api/master_p3k.php - CRUD Master Kotak P3K + scan check
 * Action: list | next_code | area_list | create | update | delete | scan_check
 */
require_once __DIR__ . '/config.php';
require_login();

$action = api_action('list');

if ($action === 'area_list') {
    $daftar = [];
    $q = mysqli_query($koneksi, "SELECT nama_line FROM area_line WHERE jenis = 'p3k' ORDER BY nama_line ASC");
    while ($r = mysqli_fetch_assoc($q)) {
        $daftar[] = $r['nama_line'];
    }
    api_response(['success' => true, 'data' => $daftar]);
}

if ($action === 'next_code') {
    $q = mysqli_query($koneksi, "SELECT code FROM master_p3k WHERE code LIKE 'P3K%' ORDER BY id DESC LIMIT 1");
    $next_code = 'P3K01';
    if ($q && mysqli_num_rows($q) > 0) {
        $max_code = mysqli_fetch_assoc($q)['code'];
        $num = (int) substr($max_code, 3);
        $next_code = 'P3K' . sprintf('%02d', $num + 1);
    }
    api_response(['success' => true, 'next_code' => $next_code]);
}

if ($action === 'scan_check') {
    $scan_id = mysqli_real_escape_string($koneksi, api_get('scan_id', ''));
    $q = mysqli_query($koneksi, "SELECT * FROM master_p3k WHERE id = '$scan_id' OR code = '$scan_id'");
    if ($q && mysqli_num_rows($q) > 0) {
        $scan_data = mysqli_fetch_assoc($q);
        $code_p3k = $scan_data['code'];
        $q_cek = mysqli_query($koneksi, "SELECT tanggal_inspeksi FROM inspeksi_p3k
            WHERE code_p3k = '$code_p3k'
            AND tanggal_inspeksi >= DATE_SUB(NOW(), INTERVAL 1 MONTH)
            ORDER BY tanggal_inspeksi DESC LIMIT 1");
        if ($q_cek && mysqli_num_rows($q_cek) > 0) {
            $tanggal_terakhir = mysqli_fetch_assoc($q_cek)['tanggal_inspeksi'];
            $tanggal_bisa_kembali = date('d F Y', strtotime($tanggal_terakhir . ' + 1 month'));
            $tanggal_terakhir_indo = date('d F Y', strtotime($tanggal_terakhir));
            api_response([
                'success' => true,
                'blocked' => true,
                'message' => "Maaf, Alat dengan kode '$code_p3k' sudah diinspeksi pada $tanggal_terakhir_indo. Berdasarkan aturan 1 bulan sekali, alat ini baru bisa diinspeksi kembali pada tanggal $tanggal_bisa_kembali!",
            ]);
        }
        api_response(['success' => true, 'blocked' => false, 'data' => $scan_data]);
    }
    api_response([
        'success' => true,
        'blocked' => true,
        'message' => "Data Kotak P3K dengan kode '" . htmlspecialchars($scan_id) . "' tidak ditemukan atau bukan bagian dari sistem data master Kotak P3K.",
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
        $search_sql = " WHERE (mp.code LIKE '%$s%' OR mp.line_area LIKE '%$s%' OR mp.lokasi LIKE '%$s%' OR mp.catatan LIKE '%$s%' OR ip.username LIKE '%$s%')";
    }

    $join_sql = "FROM master_p3k mp
        LEFT JOIN (
            SELECT code_p3k, username, tanggal_inspeksi
            FROM inspeksi_p3k
            WHERE id_inspeksi IN (SELECT MAX(id_inspeksi) FROM inspeksi_p3k GROUP BY code_p3k)
        ) ip ON mp.code = ip.code_p3k" . $search_sql;

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
    $query = mysqli_query($koneksi, "SELECT mp.*, ip.username, ip.tanggal_inspeksi $join_sql ORDER BY mp.code ASC LIMIT $limit OFFSET $offset");
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

if ($action === 'create') {
    $data = api_input();
    $code = isset($data['code']) ? trim($data['code']) : '';
    $line_area = isset($data['line_area']) ? $data['line_area'] : '';
    $lokasi = isset($data['lokasi']) ? $data['lokasi'] : '';
    $kondisi_kotak = isset($data['kondisi_kotak']) ? $data['kondisi_kotak'] : 'Baik';
    $kelengkapan_isi = isset($data['kelengkapan_isi']) ? $data['kelengkapan_isi'] : 'Lengkap';
    $expired_obat = isset($data['expired_obat']) ? $data['expired_obat'] : 'Lengkap';
    $catatan = isset($data['catatan']) ? $data['catatan'] : '';

    if ($code === '' || $lokasi === '') {
        api_response(['success' => false, 'message' => 'Kode dan lokasi wajib diisi.'], 400);
    }

    $kondisi = (strtolower($kondisi_kotak) === 'tidak' || strtolower($kelengkapan_isi) === 'tidak' || strtolower($expired_obat) === 'tidak') ? 'rusak' : 'baik';

    $stmt = mysqli_prepare($koneksi, "INSERT INTO master_p3k
        (code, line_area, lokasi, kondisi_kotak, kelengkapan_isi, expired_obat, catatan, kondisi)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, 'ssssssss', $code, $line_area, $lokasi, $kondisi_kotak, $kelengkapan_isi, $expired_obat, $catatan, $kondisi);
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
    $kondisi_kotak = isset($data['kondisi_kotak']) ? $data['kondisi_kotak'] : 'Baik';
    $kelengkapan_isi = isset($data['kelengkapan_isi']) ? $data['kelengkapan_isi'] : 'Lengkap';
    $expired_obat = isset($data['expired_obat']) ? $data['expired_obat'] : 'Lengkap';
    $catatan = isset($data['catatan']) ? $data['catatan'] : '';

    if ($id < 1) {
        api_response(['success' => false, 'message' => 'ID tidak valid.'], 400);
    }

    $kondisi = (strtolower($kondisi_kotak) === 'tidak' || strtolower($kelengkapan_isi) === 'tidak' || strtolower($expired_obat) === 'tidak') ? 'rusak' : 'baik';

    $operator = api_operator_name();
    $id_user = api_user_id();
    $keterangan = $catatan;

    $upd = mysqli_prepare($koneksi, "UPDATE master_p3k SET
        code=?, line_area=?, lokasi=?, kondisi_kotak=?, kelengkapan_isi=?, expired_obat=?, catatan=?, kondisi=?
        WHERE id=?");
    mysqli_stmt_bind_param($upd, 'ssssssssi', $code, $line_area, $lokasi, $kondisi_kotak, $kelengkapan_isi, $expired_obat, $catatan, $kondisi, $id);
    $ok = mysqli_stmt_execute($upd);

    if (!$ok) {
        api_response(['success' => false, 'message' => 'Gagal memperbarui data: ' . mysqli_error($koneksi)], 500);
    }

    $ins = mysqli_prepare($koneksi, "INSERT INTO inspeksi_p3k
        (code_p3k, id_user, line_area, kondisi_kotak, kelengkapan_isi, expired_obat, keterangan, tanggal_inspeksi, kondisi, catatan, username)
        VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), ?, ?, ?)");
    mysqli_stmt_bind_param($ins, 'sissssssss', $code, $id_user, $line_area, $kondisi_kotak, $kelengkapan_isi, $expired_obat, $keterangan, $kondisi, $catatan, $operator);
    mysqli_stmt_execute($ins);

    api_response(['success' => true, 'message' => 'Data berhasil diperbarui.']);
}

if ($action === 'delete') {
    $id = (int) api_get('id', 0);
    $del = mysqli_query($koneksi, "DELETE FROM master_p3k WHERE id = $id");
    if ($del) {
        api_response(['success' => true, 'message' => 'Data berhasil dihapus.']);
    }
    api_response(['success' => false, 'message' => 'Gagal menghapus data: ' . mysqli_error($koneksi)], 500);
}

api_response(['success' => false, 'message' => 'Action tidak dikenal.'], 404);