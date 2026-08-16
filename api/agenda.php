<?php
/**
 * api/agenda.php - CRUD Agenda Inspeksi
 * Action: list | create | update | status | delete | options
 */
require_once __DIR__ . '/config.php';
require_login();

$action = api_action('list');

mysqli_query($koneksi, "
    CREATE TABLE IF NOT EXISTS `agenda_inspeksi` (
        `id_agenda` INT(11) NOT NULL AUTO_INCREMENT,
        `jenis_inspeksi` VARCHAR(50) NOT NULL,
        `line_area` VARCHAR(50) NOT NULL,
        `id_lampu` VARCHAR(20) DEFAULT NULL,
        `id_user` INT(11) DEFAULT NULL,
        `tanggal_jadwal` DATE NOT NULL,
        `jam_jadwal` TIME DEFAULT NULL,
        `status` ENUM('Terjadwal','Berlangsung','Selesai','Terlewat') NOT NULL DEFAULT 'Terjadwal',
        `catatan` TEXT DEFAULT NULL,
        `created_at` TIMESTAMP NOT NULL DEFAULT current_timestamp(),
        PRIMARY KEY (`id_agenda`),
        KEY `id_lampu` (`id_lampu`),
        KEY `id_user` (`id_user`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
");

mysqli_query($koneksi, "UPDATE agenda_inspeksi
    SET status = 'Terlewat'
    WHERE status = 'Terjadwal' AND tanggal_jadwal < CURDATE()");

if ($action === 'options') {
    $list_lampu = [];
    $q = mysqli_query($koneksi, "SELECT code, lokasi FROM master_lampu ORDER BY lokasi ASC");
    while ($r = mysqli_fetch_assoc($q)) {
        $list_lampu[] = $r;
    }
    $list_line = [];
    $q = mysqli_query($koneksi, "SELECT id_line, nama_line FROM area_line ORDER BY nama_line ASC");
    while ($r = mysqli_fetch_assoc($q)) {
        $list_line[] = $r;
    }
    $list_user = [];
    $q = mysqli_query($koneksi, "SELECT id_user, nama_lengkap FROM users ORDER BY nama_lengkap ASC");
    while ($r = mysqli_fetch_assoc($q)) {
        $list_user[] = $r;
    }
    api_response([
        'success' => true,
        'list_lampu' => $list_lampu,
        'list_line' => $list_line,
        'list_user' => $list_user,
    ]);
}

if ($action === 'list') {
    $filter_status = api_get('filter_status', '');
    $filter_bulan = api_get('filter_bulan', date('Y-m'));
    $cari = trim(api_get('cari', ''));

    $where = [];
    $params = [];
    $types = '';

    if ($filter_status !== '') {
        $where[] = 'a.status = ?';
        $params[] = $filter_status;
        $types .= 's';
    }
    if ($filter_bulan !== '') {
        $where[] = "DATE_FORMAT(a.tanggal_jadwal, '%Y-%m') = ?";
        $params[] = $filter_bulan;
        $types .= 's';
    }
    if ($cari !== '') {
        $where[] = '(a.line_area LIKE ? OR a.id_lampu LIKE ? OR a.jenis_inspeksi LIKE ?)';
        $like = "%$cari%";
        $params[] = $like;
        $params[] = $like;
        $params[] = $like;
        $types .= 'sss';
    }

    $sql = "SELECT a.*, u.nama_lengkap
            FROM agenda_inspeksi a
            LEFT JOIN users u ON a.id_user = u.id_user";
    if (count($where) > 0) {
        $sql .= ' WHERE ' . implode(' AND ', $where);
    }
    $sql .= ' ORDER BY a.tanggal_jadwal ASC, a.jam_jadwal ASC';

    $rows = [];
    $stmt = mysqli_prepare($koneksi, $sql);
    if (count($params) > 0) {
        mysqli_stmt_bind_param($stmt, $types, ...$params);
    }
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    while ($r = mysqli_fetch_assoc($result)) {
        $rows[] = $r;
    }

    $stat = ['Terjadwal' => 0, 'Berlangsung' => 0, 'Selesai' => 0, 'Terlewat' => 0];
    $q_stat = mysqli_query($koneksi, "SELECT status, COUNT(*) as total FROM agenda_inspeksi GROUP BY status");
    if ($q_stat) {
        while ($r = mysqli_fetch_assoc($q_stat)) {
            if (isset($stat[$r['status']])) {
                $stat[$r['status']] = (int) $r['total'];
            }
        }
    }

    api_response([
        'success' => true,
        'data' => $rows,
        'stat' => $stat,
        'filter_status' => $filter_status,
        'filter_bulan' => $filter_bulan,
        'cari' => $cari,
    ]);
}

if ($action === 'create') {
    $data = api_input();
    $jenis = isset($data['jenis_inspeksi']) ? $data['jenis_inspeksi'] : '';
    $line = isset($data['line_area']) ? $data['line_area'] : '';
    $lampu = !empty($data['id_lampu']) ? $data['id_lampu'] : null;
    $petugas = !empty($data['id_user']) ? (int) $data['id_user'] : null;
    $tanggal = isset($data['tanggal_jadwal']) ? $data['tanggal_jadwal'] : '';
    $jam = !empty($data['jam_jadwal']) ? $data['jam_jadwal'] : null;
    $catatan = isset($data['catatan']) ? trim($data['catatan']) : '';

    $stmt = mysqli_prepare($koneksi, "INSERT INTO agenda_inspeksi
        (jenis_inspeksi, line_area, id_lampu, id_user, tanggal_jadwal, jam_jadwal, catatan)
        VALUES (?, ?, ?, ?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, 'sssisss', $jenis, $line, $lampu, $petugas, $tanggal, $jam, $catatan);
    $ok = mysqli_stmt_execute($stmt);
    if ($ok) {
        api_response(['success' => true, 'message' => 'Agenda inspeksi baru berhasil ditambahkan.']);
    }
    api_response(['success' => false, 'message' => 'Gagal menambahkan agenda: ' . mysqli_error($koneksi)], 500);
}

if ($action === 'update') {
    $data = api_input();
    $id = (int) ($data['id_agenda'] ?? 0);
    $jenis = isset($data['jenis_inspeksi']) ? $data['jenis_inspeksi'] : '';
    $line = isset($data['line_area']) ? $data['line_area'] : '';
    $lampu = !empty($data['id_lampu']) ? $data['id_lampu'] : null;
    $petugas = !empty($data['id_user']) ? (int) $data['id_user'] : null;
    $tanggal = isset($data['tanggal_jadwal']) ? $data['tanggal_jadwal'] : '';
    $jam = !empty($data['jam_jadwal']) ? $data['jam_jadwal'] : null;
    $status = isset($data['status']) ? $data['status'] : 'Terjadwal';
    $catatan = isset($data['catatan']) ? trim($data['catatan']) : '';

    $stmt = mysqli_prepare($koneksi, "UPDATE agenda_inspeksi SET
        jenis_inspeksi = ?, line_area = ?, id_lampu = ?, id_user = ?,
        tanggal_jadwal = ?, jam_jadwal = ?, status = ?, catatan = ?
        WHERE id_agenda = ?");
    mysqli_stmt_bind_param($stmt, 'sssissssi', $jenis, $line, $lampu, $petugas, $tanggal, $jam, $status, $catatan, $id);
    $ok = mysqli_stmt_execute($stmt);
    if ($ok) {
        api_response(['success' => true, 'message' => 'Agenda inspeksi berhasil diperbarui.']);
    }
    api_response(['success' => false, 'message' => 'Gagal memperbarui agenda: ' . mysqli_error($koneksi)], 500);
}

if ($action === 'status') {
    $id = (int) api_get('id', 0);
    $status = api_get('status', '');
    $allowed = ['Terjadwal', 'Berlangsung', 'Selesai', 'Terlewat'];
    if (!in_array($status, $allowed) || $id < 1) {
        api_response(['success' => false, 'message' => 'Status tidak valid.'], 400);
    }
    $stmt = mysqli_prepare($koneksi, "UPDATE agenda_inspeksi SET status = ? WHERE id_agenda = ?");
    mysqli_stmt_bind_param($stmt, 'si', $status, $id);
    $ok = mysqli_stmt_execute($stmt);
    if ($ok) {
        api_response(['success' => true, 'message' => 'Status agenda berhasil diperbarui.']);
    }
    api_response(['success' => false, 'message' => 'Gagal memperbarui status.'], 500);
}

if ($action === 'delete') {
    $id = (int) api_get('id', 0);
    $stmt = mysqli_prepare($koneksi, "DELETE FROM agenda_inspeksi WHERE id_agenda = ?");
    mysqli_stmt_bind_param($stmt, 'i', $id);
    $ok = mysqli_stmt_execute($stmt);
    if ($ok) {
        api_response(['success' => true, 'message' => 'Agenda inspeksi berhasil dihapus.']);
    }
    api_response(['success' => false, 'message' => 'Gagal menghapus agenda.'], 500);
}

api_response(['success' => false, 'message' => 'Action tidak dikenal.'], 404);