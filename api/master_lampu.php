<?php
/**
 * api/master_lampu.php - CRUD Master Lampu (Emergency & Exit) + scan check
 * Query param: type = emergency | exit
 * Action: list | next_code | area_list | create | update | delete | scan_check
 */
require_once __DIR__ . '/config.php';
require_login();

$action = api_action('list');
$type = api_get('type', 'emergency');

$prefix_filter = ($type === 'exit') ? "ml.code LIKE 'LE%'" : "ml.code NOT LIKE 'LE%'";

if ($action === 'area_list') {
    $jenis = ($type === 'exit') ? 'lampu_exit' : 'lampu_emergency';
    $daftar = [];
    $q = mysqli_query($koneksi, "SELECT nama_line FROM area_line WHERE jenis = '$jenis' ORDER BY nama_line ASC");
    while ($r = mysqli_fetch_assoc($q)) {
        $daftar[] = $r['nama_line'];
    }
    api_response(['success' => true, 'data' => $daftar]);
}

if ($action === 'next_code') {
    $prefix = ($type === 'exit') ? 'LE' : 'LPE';
    $q = mysqli_query($koneksi, "SELECT code FROM master_lampu WHERE code LIKE '$prefix%' ORDER BY id DESC LIMIT 1");
    $next_code = $prefix . '01';
    if ($q && mysqli_num_rows($q) > 0) {
        $max_code = mysqli_fetch_assoc($q)['code'];
        $offset = ($type === 'exit') ? 2 : 3;
        $num = (int) substr($max_code, $offset);
        $next_code = $prefix . sprintf('%02d', $num + 1);
    }
    api_response(['success' => true, 'next_code' => $next_code]);
}

if ($action === 'scan_check') {
    $scan_id = mysqli_real_escape_string($koneksi, api_get('scan_id', ''));
    $filter = ($type === 'exit') ? "AND code LIKE 'LE%'" : "AND code NOT LIKE 'LE%'";
    $q = mysqli_query($koneksi, "SELECT * FROM master_lampu WHERE (id = '$scan_id' OR code = '$scan_id') $filter");
    if ($q && mysqli_num_rows($q) > 0) {
        $scan_data = mysqli_fetch_assoc($q);
        $code_lampu = $scan_data['code'];
        $q_cek = mysqli_query($koneksi, "SELECT tanggal_inspeksi FROM inspeksi_lampu
            WHERE code_lampu = '$code_lampu'
            AND tanggal_inspeksi >= DATE_SUB(NOW(), INTERVAL 1 MONTH)
            ORDER BY tanggal_inspeksi DESC LIMIT 1");
        if ($q_cek && mysqli_num_rows($q_cek) > 0) {
            $tanggal_terakhir = mysqli_fetch_assoc($q_cek)['tanggal_inspeksi'];
            $tanggal_bisa_kembali = date('d F Y', strtotime($tanggal_terakhir . ' + 1 month'));
            $tanggal_terakhir_indo = date('d F Y', strtotime($tanggal_terakhir));
            api_response([
                'success' => true,
                'blocked' => true,
                'message' => "Maaf, Alat dengan kode '$code_lampu' sudah diinspeksi pada $tanggal_terakhir_indo. Berdasarkan aturan 1 month sekali, alat ini baru bisa diinspeksi kembali pada tanggal $tanggal_bisa_kembali!",
            ]);
        }
        api_response(['success' => true, 'blocked' => false, 'data' => $scan_data]);
    }
    api_response([
        'success' => true,
        'blocked' => true,
        'message' => "Data Lampu dengan kode '" . htmlspecialchars($scan_id) . "' tidak ditemukan atau bukan bagian dari sistem data master Lampu.",
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
        $search_sql = " AND (ml.code LIKE '%$s%' OR ml.merek LIKE '%$s%' OR ml.line_area LIKE '%$s%' OR ml.lokasi LIKE '%$s%' OR ml.catatan LIKE '%$s%' OR il.username LIKE '%$s%')";
    }

    $join_sql = "FROM master_lampu ml
        LEFT JOIN (
            SELECT code_lampu, username, tanggal_inspeksi
            FROM inspeksi_lampu
            WHERE id_inspeksi IN (SELECT MAX(id_inspeksi) FROM inspeksi_lampu GROUP BY code_lampu)
        ) il ON ml.code = il.code_lampu
        WHERE $prefix_filter" . $search_sql;

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
    $query = mysqli_query($koneksi, "SELECT ml.*, il.username, il.tanggal_inspeksi $join_sql ORDER BY ml.id ASC LIMIT $limit OFFSET $offset");
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
    $merek = isset($data['merek']) ? $data['merek'] : '';
    $line_area = isset($data['line_area']) ? $data['line_area'] : '';
    $lokasi = isset($data['lokasi']) ? $data['lokasi'] : '';
    $catatan = isset($data['catatan']) ? $data['catatan'] : '';
    $indikator = isset($data['indikator_mati_menyala']) ? $data['indikator_mati_menyala'] : 'Nyala';
    $lampu_mati = isset($data['lampu_mati']) ? $data['lampu_mati'] : 'Tidak';
    $nyala_otomatis = isset($data['nyala_otomatis']) ? $data['nyala_otomatis'] : 'Tidak';

    if ($code === '' || $lokasi === '') {
        api_response(['success' => false, 'message' => 'Kode dan lokasi wajib diisi.'], 400);
    }

    if (stripos($code, 'LE') === 0) {
        $lampu_mati = (strtolower($indikator) === 'mati') ? 'Ya' : 'Tidak';
        $nyala_otomatis = 'Tidak';
        $kondisi = (strtolower($lampu_mati) === 'ya') ? 'rusak' : 'baik';
    } else {
        $kondisi = (strtolower($lampu_mati) === 'ya') ? 'rusak' : 'baik';
    }

    $stmt = mysqli_prepare($koneksi, "INSERT INTO master_lampu
        (code, merek, line_area, lokasi, indikator_mati_menyala, lampu_mati, nyala_otomatis, catatan, kondisi)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, 'sssssssss', $code, $merek, $line_area, $lokasi, $indikator, $lampu_mati, $nyala_otomatis, $catatan, $kondisi);
    $ok = mysqli_stmt_execute($stmt);

    if ($ok) {
        api_response(['success' => true, 'message' => 'Data berhasil disimpan.', 'redirect' => (stripos($code, 'LE') === 0) ? 'exit' : 'lampu']);
    }
    api_response(['success' => false, 'message' => 'Gagal menyimpan data: ' . mysqli_error($koneksi)], 500);
}

if ($action === 'update') {
    $data = api_input();
    $id = (int) ($data['id'] ?? 0);
    $code = isset($data['code']) ? trim($data['code']) : '';
    $merek = isset($data['merek']) ? $data['merek'] : '';
    $line_area = isset($data['line_area']) ? $data['line_area'] : '';
    $lokasi = isset($data['lokasi']) ? $data['lokasi'] : '';
    $catatan = isset($data['catatan']) ? $data['catatan'] : '';
    $indikator = isset($data['indikator_mati_menyala']) ? $data['indikator_mati_menyala'] : 'Nyala';
    $lampu_mati = isset($data['lampu_mati']) ? $data['lampu_mati'] : 'Tidak';
    $nyala_otomatis = isset($data['nyala_otomatis']) ? $data['nyala_otomatis'] : 'Tidak';

    if ($id < 1) {
        api_response(['success' => false, 'message' => 'ID tidak valid.'], 400);
    }

    if (stripos($code, 'LE') === 0) {
        $lampu_mati = (strtolower($indikator) === 'mati') ? 'Ya' : 'Tidak';
        $nyala_otomatis = 'Tidak';
        $kondisi = (strtolower($lampu_mati) === 'ya') ? 'rusak' : 'baik';
    } else {
        $kondisi = (strtolower($lampu_mati) === 'ya') ? 'rusak' : 'baik';
    }

    $operator = api_operator_name();
    $id_user = api_user_id();

    $upd = mysqli_prepare($koneksi, "UPDATE master_lampu SET
        code=?, merek=?, line_area=?, lokasi=?, indikator_mati_menyala=?, lampu_mati=?, nyala_otomatis=?, catatan=?, kondisi=?
        WHERE id=?");
    mysqli_stmt_bind_param($upd, 'sssssssssi', $code, $merek, $line_area, $lokasi, $indikator, $lampu_mati, $nyala_otomatis, $catatan, $kondisi, $id);
    $ok = mysqli_stmt_execute($upd);

    if (!$ok) {
        api_response(['success' => false, 'message' => 'Gagal memperbarui data: ' . mysqli_error($koneksi)], 500);
    }

    // Catat riwayat inspeksi (replikasi proses_edit_lampu.php)
    if (stripos($code, 'LE') === 0) {
        $kondisi_fisik = 'Baik';
        $kondisi_tulisan = 'Baik';
        $kondisi_lampu = (strtolower($lampu_mati) === 'ya') ? 'Tidak' : 'Baik';
        $keterangan = $catatan;

        if (!$id_user) {
            $q_user = mysqli_query($koneksi, "SELECT id_user FROM users WHERE nama_lengkap = '$operator' OR username = '$operator' LIMIT 1");
            if ($q_user && mysqli_num_rows($q_user) > 0) {
                $id_user = (int) mysqli_fetch_assoc($q_user)['id_user'];
            }
        }

        $ins = mysqli_prepare($koneksi, "INSERT INTO inspeksi_lampu_exit
            (id_lampu, id_user, nama_operator, tanggal_cek, kondisi_fisik, kondisi_lampu, kondisi_tulisan, keterangan)
            VALUES (?, ?, ?, NOW(), ?, ?, ?, ?)");
        mysqli_stmt_bind_param($ins, 'sisssss', $code, $id_user, $operator, $kondisi_fisik, $kondisi_lampu, $kondisi_tulisan, $keterangan);
        mysqli_stmt_execute($ins);
    } else {
        $ins = mysqli_prepare($koneksi, "INSERT INTO inspeksi_lampu (code_lampu, username, tanggal_inspeksi, kondisi, catatan)
            VALUES (?, ?, NOW(), ?, ?)");
        mysqli_stmt_bind_param($ins, 'ssss', $code, $operator, $kondisi, $catatan);
        mysqli_stmt_execute($ins);
    }

    api_response([
        'success' => true,
        'message' => 'Data berhasil diperbarui.',
        'redirect' => (stripos($code, 'LE') === 0) ? 'exit' : 'lampu',
    ]);
}

if ($action === 'delete') {
    $id = (int) api_get('id', 0);
    $q = mysqli_query($koneksi, "SELECT code FROM master_lampu WHERE id = $id");
    $redirect = 'lampu';
    if ($q && mysqli_num_rows($q) > 0) {
        $code = mysqli_fetch_assoc($q)['code'];
        if (stripos($code, 'LE') === 0) {
            $redirect = 'exit';
        }
    }
    $del = mysqli_query($koneksi, "DELETE FROM master_lampu WHERE id = $id");
    if ($del) {
        api_response(['success' => true, 'message' => 'Data berhasil dihapus.', 'redirect' => $redirect]);
    }
    api_response(['success' => false, 'message' => 'Gagal menghapus data: ' . mysqli_error($koneksi)], 500);
}

api_response(['success' => false, 'message' => 'Action tidak dikenal.'], 404);