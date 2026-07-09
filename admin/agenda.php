<?php
session_start();
include '../koneksi.php';

global $koneksi;

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: ../login.php");
    exit();
}

/* =========================================================
   AUTO-CREATE TABLE (agar tidak perlu import SQL manual)
========================================================= */
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

/* =========================================================
   AKSI: TAMBAH AGENDA
========================================================= */
if (isset($_POST['tambah_agenda'])) {
    $jenis   = $_POST['jenis_inspeksi'];
    $line    = $_POST['line_area'];
    $lampu   = !empty($_POST['id_lampu']) ? $_POST['id_lampu'] : null;
    $petugas = !empty($_POST['id_user']) ? (int) $_POST['id_user'] : null;
    $tanggal = $_POST['tanggal_jadwal'];
    $jam     = !empty($_POST['jam_jadwal']) ? $_POST['jam_jadwal'] : null;
    $catatan = trim($_POST['catatan']);

    $stmt = mysqli_prepare($koneksi, "INSERT INTO agenda_inspeksi
        (jenis_inspeksi, line_area, id_lampu, id_user, tanggal_jadwal, jam_jadwal, catatan)
        VALUES (?, ?, ?, ?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "sssisss", $jenis, $line, $lampu, $petugas, $tanggal, $jam, $catatan);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    header("Location: agenda.php?msg=tambah");
    exit();
}

/* =========================================================
   AKSI: EDIT AGENDA
========================================================= */
if (isset($_POST['edit_agenda'])) {
    $id      = (int) $_POST['id_agenda'];
    $jenis   = $_POST['jenis_inspeksi'];
    $line    = $_POST['line_area'];
    $lampu   = !empty($_POST['id_lampu']) ? $_POST['id_lampu'] : null;
    $petugas = !empty($_POST['id_user']) ? (int) $_POST['id_user'] : null;
    $tanggal = $_POST['tanggal_jadwal'];
    $jam     = !empty($_POST['jam_jadwal']) ? $_POST['jam_jadwal'] : null;
    $status  = $_POST['status'];
    $catatan = trim($_POST['catatan']);

    $stmt = mysqli_prepare($koneksi, "UPDATE agenda_inspeksi SET
        jenis_inspeksi = ?, line_area = ?, id_lampu = ?, id_user = ?,
        tanggal_jadwal = ?, jam_jadwal = ?, status = ?, catatan = ?
        WHERE id_agenda = ?");
    mysqli_stmt_bind_param($stmt, "sssissssi", $jenis, $line, $lampu, $petugas, $tanggal, $jam, $status, $catatan, $id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    header("Location: agenda.php?msg=edit");
    exit();
}

/* =========================================================
   AKSI: UBAH STATUS CEPAT (Selesai / Berlangsung)
========================================================= */
if (isset($_GET['ubah_status']) && isset($_GET['id'])) {
    $id     = (int) $_GET['id'];
    $status = $_GET['ubah_status'];
    $allowed = ['Terjadwal', 'Berlangsung', 'Selesai', 'Terlewat'];
    if (in_array($status, $allowed)) {
        $stmt = mysqli_prepare($koneksi, "UPDATE agenda_inspeksi SET status = ? WHERE id_agenda = ?");
        mysqli_stmt_bind_param($stmt, "si", $status, $id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }
    header("Location: agenda.php?msg=status");
    exit();
}

/* =========================================================
   AKSI: HAPUS AGENDA
========================================================= */
if (isset($_GET['hapus'])) {
    $id = (int) $_GET['hapus'];
    $stmt = mysqli_prepare($koneksi, "DELETE FROM agenda_inspeksi WHERE id_agenda = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    header("Location: agenda.php?msg=hapus");
    exit();
}

/* =========================================================
   AUTO UPDATE: TANDAI "Terlewat" JIKA SUDAH LEWAT TANGGAL
   DAN MASIH BERSTATUS "Terjadwal"
========================================================= */
mysqli_query($koneksi, "UPDATE agenda_inspeksi
    SET status = 'Terlewat'
    WHERE status = 'Terjadwal' AND tanggal_jadwal < CURDATE()");

/* =========================================================
   FILTER & PENCARIAN
========================================================= */
$filter_status = isset($_GET['filter_status']) ? $_GET['filter_status'] : '';
$filter_bulan  = isset($_GET['filter_bulan']) ? $_GET['filter_bulan'] : date('Y-m');
$cari          = isset($_GET['cari']) ? trim($_GET['cari']) : '';

$where = [];
$params = [];
$types = '';

if ($filter_status !== '') {
    $where[] = "a.status = ?";
    $params[] = $filter_status;
    $types .= 's';
}
if ($filter_bulan !== '') {
    $where[] = "DATE_FORMAT(a.tanggal_jadwal, '%Y-%m') = ?";
    $params[] = $filter_bulan;
    $types .= 's';
}
if ($cari !== '') {
    $where[] = "(a.line_area LIKE ? OR a.id_lampu LIKE ? OR a.jenis_inspeksi LIKE ?)";
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
    $sql .= " WHERE " . implode(" AND ", $where);
}
$sql .= " ORDER BY a.tanggal_jadwal ASC, a.jam_jadwal ASC";

$stmt = mysqli_prepare($koneksi, $sql);
if (count($params) > 0) {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}
mysqli_stmt_execute($stmt);
$result_agenda = mysqli_stmt_get_result($stmt);

/* Statistik ringkas untuk kartu atas */
$stat = ['Terjadwal' => 0, 'Berlangsung' => 0, 'Selesai' => 0, 'Terlewat' => 0];
$q_stat = mysqli_query($koneksi, "SELECT status, COUNT(*) as total FROM agenda_inspeksi GROUP BY status");
if ($q_stat) {
    while ($r = mysqli_fetch_assoc($q_stat)) {
        $stat[$r['status']] = $r['total'];
    }
}

/* Data master untuk dropdown form */
$list_lampu  = mysqli_query($koneksi, "SELECT code, lokasi FROM master_lampu ORDER BY lokasi ASC");
$list_line  = mysqli_query($koneksi, "SELECT id_departemen AS id_line, nama_departemen AS nama_line FROM departemen ORDER BY nama_departemen ASC");
$list_user  = mysqli_query($koneksi, "SELECT id_user, nama_lengkap FROM users ORDER BY nama_lengkap ASC");

/* Tanggal hari ini, format Indonesia (sama seperti index.php) */
$hari_ini = date('l');
$daftar_hari = ['Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa', 'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu'];
$bulan_ini = date('F');
$daftar_bulan = ['January' => 'Januari', 'February' => 'Februari', 'March' => 'Maret', 'April' => 'April', 'May' => 'Mei', 'June' => 'Juni', 'July' => 'Juli', 'August' => 'Agustus', 'September' => 'September', 'October' => 'Oktober', 'November' => 'November', 'December' => 'Desember'];
$tanggal_format = $daftar_hari[$hari_ini] . ", " . date('d') . " " . $daftar_bulan[$bulan_ini] . " " . date('Y');

function badge_status($status)
{
    $map = [
        'Terjadwal'   => 'badge-blue',
        'Berlangsung' => 'badge-orange',
        'Selesai'     => 'badge-green',
        'Terlewat'    => 'badge-red',
    ];
    $class = $map[$status] ?? 'badge-blue';
    return "<span class=\"badge {$class}\">{$status}</span>";
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agenda Inspeksi - Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    <style>
        .page-toolbar {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
        }

        .grid-stats-mini {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 16px;
            margin-bottom: 24px;
        }

        .mini-card {
            background: #fff;
            border-radius: 10px;
            padding: 16px 18px;
            border-left: 5px solid #2b75cc;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
        }

        .mini-card.c-orange {
            border-left-color: #f0a500;
        }

        .mini-card.c-green {
            border-left-color: #2bb673;
        }

        .mini-card.c-red {
            border-left-color: #e15554;
        }

        .mini-card h3 {
            font-size: 13px;
            color: #64748b;
            font-weight: 500;
            margin-bottom: 6px;
        }

        .mini-card p {
            font-size: 24px;
            font-weight: 700;
            color: #1e293b;
        }

        .filter-bar {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            align-items: center;
        }

        .filter-bar select,
        .filter-bar input[type="month"],
        .filter-bar input[type="text"] {
            padding: 9px 12px;
            border: 1px solid #d8dee9;
            border-radius: 6px;
            font-size: 13px;
            font-family: inherit;
            outline: none;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 9px 16px;
            border-radius: 6px;
            border: none;
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
            text-decoration: none;
            white-space: nowrap;
        }

        .btn-primary {
            background: #000766;
            color: #fff;
        }

        .btn-primary:hover {
            background: #1a2a8c;
        }

        .btn-outline {
            background: #fff;
            color: #000766;
            border: 1px solid #000766;
        }

        .btn-sm {
            padding: 6px 10px;
            font-size: 12px;
        }

        .table-card {
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
            overflow-x: auto;
        }

        table.agenda-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 950px;
        }

        table.agenda-table th {
            background: #f4f6f9;
            text-align: left;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: .5px;
            color: #64748b;
            padding: 14px 16px;
            border-bottom: 1px solid #e2e8f0;
        }

        table.agenda-table td {
            padding: 13px 16px;
            font-size: 13.5px;
            color: #334155;
            border-bottom: 1px solid #eef1f5;
        }

        table.agenda-table tr:hover td {
            background: #fafbfd;
        }

        .badge {
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 11.5px;
            font-weight: 600;
            white-space: nowrap;
        }

        .badge-blue {
            background: #e3edfb;
            color: #2b75cc;
        }

        .badge-orange {
            background: #fdf0d8;
            color: #b8790a;
        }

        .badge-green {
            background: #ddf3e8;
            color: #1e9e63;
        }

        .badge-red {
            background: #fbe2e1;
            color: #d33a39;
        }

        .action-icons a {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 30px;
            height: 30px;
            border-radius: 6px;
            margin-right: 4px;
            color: #fff;
            text-decoration: none;
            font-size: 12px;
        }

        .ic-edit {
            background: #2b75cc;
        }

        .ic-done {
            background: #2bb673;
        }

        .ic-delete {
            background: #e15554;
        }

        .empty-row td {
            text-align: center;
            padding: 40px;
            color: #94a3b8;
        }

        /* Modal Form */
        .modal-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.5);
            z-index: 999;
            align-items: center;
            justify-content: center;
        }

        .modal-overlay.show {
            display: flex;
        }

        .modal-box {
            background: #fff;
            width: 100%;
            max-width: 520px;
            border-radius: 12px;
            padding: 26px;
            max-height: 90vh;
            overflow-y: auto;
        }

        .modal-box h3 {
            margin-bottom: 18px;
            color: #000766;
        }

        .form-group {
            margin-bottom: 14px;
        }

        .form-group label {
            display: block;
            font-size: 13px;
            font-weight: 500;
            margin-bottom: 6px;
            color: #334155;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #d8dee9;
            border-radius: 6px;
            font-size: 13.5px;
            font-family: inherit;
            outline: none;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }

        .modal-footer {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 18px;
        }

        .alert-success {
            background: #ddf3e8;
            color: #1e9e63;
            padding: 12px 18px;
            border-radius: 8px;
            margin-bottom: 18px;
            font-size: 13.5px;
        }
    </style>
</head>

<body>

    <aside class="sidebar" id="sidebar">
        <div class="sidebar-brand">
            <img src="../foto/logo.png" alt="Safety Facility Logo" class="sidebar-logo">
        </div>
        <ul class="sidebar-menu">
            <li><a href="index.php"><i class="fa-solid fa-gauge"></i> <span>Dashboard</span></a></li>
            <li class="menu-header">Menu</li>
            <li><a href="scan.php"><i class="fa-solid fa-qrcode"></i> <span>Scan Code</span></a></li>
            <li><a href="../login.php"><i class="fa-solid fa-users"></i> <span>Data Pengguna</span></a></li>

            <li class="has-submenu">
                <a href="javascript:void(0)" onclick="toggleSubmenu(this)">
                    <i class="fa-solid fa-boxes-stacked"></i>
                    <span>Data Master</span>
                    <i class="fa-solid fa-chevron-down submenu-icon"></i>
                </a>
                <ul class="submenu" style="display: none;">
                    <li><a href="master_lampu.php">Lampu Emergency</a></li>
                     <li><a href="lampu_exit.php"> • Lampu Exit</a></li>
                    <li><a href="master_p3k.php">Kotak P3K</a></li>
                    <li><a href="master_eyewash.php">Eye Wash</a></li>
                </ul>
            </li>

            <li class="has-submenu">
                <a href="javascript:void(0)" onclick="toggleSubmenu(this)">
                    <i class="fa-solid fa-location-dot"></i>
                    <span>Area Line</span>
                    <i class="fa-solid fa-chevron-down submenu-icon"></i>
                </a>
                <ul class="submenu" style="display: none;">
                    <li><a href="area_line_lampu_emergency.php"> • Lampu Emergency</a></li>
                    <li><a href="lampu_exit.php"> • Lampu Exit</a></li>
                    <li><a href="area_line_p3k.php"> • Kotak P3K</a></li>
                    <li><a href="#"> • Eye Wash</a></li>
                </ul>
            </li>
            
            <li><a href="aktivitas.php"><i class="fa-solid fa-clock-rotate-left"></i> <span>Aktivitas Pengguna</span></a></li>
            <li class="active"><a href="agenda.php"><i class="fa-solid fa-calendar-check"></i> <span>Agenda Inspeksi</span></a></li>

            <li class="has-submenu">
                <a href="javascript:void(0)" onclick="toggleSubmenu(this)">
                    <i class="fa-solid fa-file-invoice"></i>
                    <span>Laporan Inspeksi</span>
                    <i class="fa-solid fa-chevron-down submenu-icon"></i>
                </a>
                <ul class="submenu" style="display: none;">
                    <li><a href="laporan_lampu_emergency.php">Lampu Emergency</a></li>
                    <li><a href="laporan_lampu_exit.php"> • Lampu Exit</a></li>
                    <li><a href="laporan_p3k.php">Kotak P3K</a></li>
                    <li><a href="laporan_eyewash.php">Eye Wash</a></li>
                </ul>
            </li>
            <li style="margin-top: 20px;"><a href="../logout.php"><i class="fa-solid fa-right-from-bracket"></i> <span>Log out</span></a></li>
        </ul>
    </aside>

    <main class="main-content">
        <header class="topbar">
            <div class="topbar-left">
                <button class="toggle-sidebar-btn" onclick="toggleSidebar()">
                    <i class="fa-solid fa-bars"></i>
                </button>
                <div class="topbar-date"><?= $tanggal_format; ?></div>
            </div>
            <div class="topbar-right">
                <div class="user-profile">
                    <span>Hi, <strong><?= htmlspecialchars($_SESSION['nama_lengkap']); ?></strong></span>
                    <div class="user-avatar">
                        <img src="https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?q=80&w=100" alt="Avatar">
                    </div>
                </div>
            </div>
        </header>

        <section class="content-body">
            <h2 class="page-title">Agenda Inspeksi</h2>

            <?php if (isset($_GET['msg'])): ?>
                <div class="alert-success">
                    <?php
                    $pesan = [
                        'tambah' => 'Agenda inspeksi baru berhasil ditambahkan.',
                        'edit'   => 'Agenda inspeksi berhasil diperbarui.',
                        'hapus'  => 'Agenda inspeksi berhasil dihapus.',
                        'status' => 'Status agenda berhasil diperbarui.',
                    ];
                    echo $pesan[$_GET['msg']] ?? 'Berhasil.';
                    ?>
                </div>
            <?php endif; ?>

            <div class="grid-stats-mini">
                <div class="mini-card">
                    <h3>Terjadwal</h3>
                    <p><?= $stat['Terjadwal']; ?></p>
                </div>
                <div class="mini-card c-orange">
                    <h3>Berlangsung</h3>
                    <p><?= $stat['Berlangsung']; ?></p>
                </div>
                <div class="mini-card c-green">
                    <h3>Selesai</h3>
                    <p><?= $stat['Selesai']; ?></p>
                </div>
                <div class="mini-card c-red">
                    <h3>Terlewat</h3>
                    <p><?= $stat['Terlewat']; ?></p>
                </div>
            </div>

            <div class="page-toolbar">
                <form method="GET" class="filter-bar">
                    <input type="text" name="cari" placeholder="Cari area / id lampu / jenis..." value="<?= htmlspecialchars($cari); ?>">
                    <select name="filter_status">
                        <option value="">Semua Status</option>
                        <?php foreach (['Terjadwal', 'Berlangsung', 'Selesai', 'Terlewat'] as $s): ?>
                            <option value="<?= $s; ?>" <?= $filter_status === $s ? 'selected' : ''; ?>><?= $s; ?></option>
                        <?php endforeach; ?>
                    </select>
                    <input type="month" name="filter_bulan" value="<?= htmlspecialchars($filter_bulan); ?>">
                    <button type="submit" class="btn btn-outline"><i class="fa-solid fa-filter"></i> Filter</button>
                    <a href="agenda.php" class="btn btn-outline"><i class="fa-solid fa-rotate-left"></i> Reset</a>
                </form>

                <button class="btn btn-primary" onclick="openModal('modalTambah')">
                    <i class="fa-solid fa-plus"></i> Tambah Agenda
                </button>
            </div>

            <div class="table-card">
                <table class="agenda-table">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Jam</th>
                            <th>Jenis Inspeksi</th>
                            <th>Area / Line</th>
                            <th>Item</th>
                            <th>Petugas</th>
                            <th>Status</th>
                            <th>Catatan</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($result_agenda) > 0): ?>
                            <?php while ($row = mysqli_fetch_assoc($result_agenda)): ?>
                                <tr>
                                    <td><?= date('d-m-Y', strtotime($row['tanggal_jadwal'])); ?></td>
                                    <td><?= $row['jam_jadwal'] ? substr($row['jam_jadwal'], 0, 5) : '-'; ?></td>
                                    <td><?= htmlspecialchars($row['jenis_inspeksi']); ?></td>
                                    <td><?= htmlspecialchars($row['line_area']); ?></td>
                                    <td><?= htmlspecialchars($row['id_lampu'] ?? '-'); ?></td>
                                    <td><?= htmlspecialchars($row['nama_lengkap'] ?? 'Belum ditentukan'); ?></td>
                                    <td><?= badge_status($row['status']); ?></td>
                                    <td><?= htmlspecialchars($row['catatan'] ?: '-'); ?></td>
                                    <td class="action-icons">
                                        <a href="javascript:void(0)" class="ic-edit" title="Edit"
                                            onclick='bukaEdit(<?= json_encode($row); ?>)'>
                                            <i class="fa-solid fa-pen"></i>
                                        </a>
                                        <?php if ($row['status'] !== 'Selesai'): ?>
                                            <a href="agenda.php?ubah_status=Selesai&id=<?= $row['id_agenda']; ?>" class="ic-done" title="Tandai Selesai"
                                                onclick="return confirm('Tandai agenda ini sebagai Selesai?')">
                                                <i class="fa-solid fa-check"></i>
                                            </a>
                                        <?php endif; ?>
                                        <a href="agenda.php?hapus=<?= $row['id_agenda']; ?>" class="ic-delete" title="Hapus"
                                            onclick="return confirm('Yakin ingin menghapus agenda ini?')">
                                            <i class="fa-solid fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr class="empty-row">
                                <td colspan="9">Belum ada agenda inspeksi untuk filter ini.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>

    <!-- MODAL TAMBAH -->
    <div class="modal-overlay" id="modalTambah">
        <div class="modal-box">
            <h3><i class="fa-solid fa-calendar-plus"></i> Tambah Agenda Inspeksi</h3>
            <form method="POST">
                <div class="form-group">
                    <label>Jenis Inspeksi</label>
                    <select name="jenis_inspeksi" required>
                        <option value="Lampu Emergency">Lampu Emergency</option>
                        <option value="Lampu Fire Emergency">Lampu Fire Emergency</option>
                        <option value="Kotak P3K">Kotak P3K</option>
                        <option value="Eye Wash">Eye Wash</option>
                    </select>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Tanggal Jadwal</label>
                        <input type="date" name="tanggal_jadwal" required>
                    </div>
                    <div class="form-group">
                        <label>Jam</label>
                        <input type="time" name="jam_jadwal">
                    </div>
                </div>
                <div class="form-group">
                    <label>Area / Line</label>
                    <select name="line_area" required>
                        <option value="">-- Pilih Area / Line --</option>
                        <?php
                        mysqli_data_seek($list_line, 0);
                        while ($l = mysqli_fetch_assoc($list_line)):
                        ?>
                            <option value="<?= htmlspecialchars($l['nama_line']); ?>"><?= htmlspecialchars($l['nama_line']); ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Item</label>
                    <select name="id_lampu">
                        <option value="">-- Tidak spesifik --</option>
                        <?php
                        mysqli_data_seek($list_lampu, 0);
                        while ($lp = mysqli_fetch_assoc($list_lampu)):
                        ?>
                            <option value="<?= htmlspecialchars($lp['code']); ?>">
                                <?= htmlspecialchars($lp['code'] . ' - ' . $lp['lokasi']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Catatan (opsional)</label>
                    <textarea name="catatan" rows="3" placeholder="Contoh: bawa unit pengganti baterai"></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" onclick="closeModal('modalTambah')">Batal</button>
                    <button type="submit" name="tambah_agenda" class="btn btn-primary">Simpan Agenda</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL EDIT -->
    <div class="modal-overlay" id="modalEdit">
        <div class="modal-box">
            <h3><i class="fa-solid fa-pen"></i> Edit Agenda Inspeksi</h3>
            <form method="POST" id="formEdit">
                <input type="hidden" name="id_agenda" id="edit_id_agenda">
                <div class="form-group">
                    <label>Jenis Inspeksi</label>
                    <select name="jenis_inspeksi" id="edit_jenis_inspeksi" required>
                        <option value="Lampu Emergency">Lampu Emergency</option>
                        <option value="Lampu Fire Emergency">Lampu Fire Emergency</option>
                        <option value="Kotak P3K">Kotak P3K</option>
                        <option value="Eye Wash">Eye Wash</option>
                    </select>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Tanggal Jadwal</label>
                        <input type="date" name="tanggal_jadwal" id="edit_tanggal_jadwal" required>
                    </div>
                    <div class="form-group">
                        <label>Jam (opsional)</label>
                        <input type="time" name="jam_jadwal" id="edit_jam_jadwal">
                    </div>
                </div>
                <div class="form-group">
                    <label>Area / Line</label>
                    <input type="text" name="line_area" id="edit_line_area" required>
                </div>
                <div class="form-group">
                    <label>Item / ID Lampu (opsional)</label>
                    <input type="text" name="id_lampu" id="edit_id_lampu" placeholder="Kosongkan jika tidak spesifik">
                </div>
                <div class="form-group">
                    <label>Petugas (id_user, opsional)</label>
                    <select name="id_user" id="edit_id_user">
                        <option value="">-- Belum ditentukan --</option>
                        <?php
                        mysqli_data_seek($list_user, 0);
                        while ($u = mysqli_fetch_assoc($list_user)):
                        ?>
                            <option value="<?= $u['id_user']; ?>"><?= htmlspecialchars($u['nama_lengkap']); ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Status</label>
                    <select name="status" id="edit_status" required>
                        <option value="Terjadwal">Terjadwal</option>
                        <option value="Berlangsung">Berlangsung</option>
                        <option value="Selesai">Selesai</option>
                        <option value="Terlewat">Terlewat</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Catatan (opsional)</label>
                    <textarea name="catatan" id="edit_catatan" rows="3"></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" onclick="closeModal('modalEdit')">Batal</button>
                    <button type="submit" name="edit_agenda" class="btn btn-primary">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('minimized');
            const mainContent = document.querySelector('.main-content');
            if (sidebar.classList.contains('minimized')) {
                mainContent.style.marginLeft = '70px';
            } else {
                mainContent.style.marginLeft = '230px';
            }
        }

        function toggleSubmenu(element) {
            const submenu = element.nextElementSibling;
            const icon = element.querySelector('.submenu-icon');
            if (submenu.style.display === "none" || submenu.style.display === "") {
                submenu.style.display = "block";
                icon.style.transform = "rotate(180deg)";
            } else {
                submenu.style.display = "none";
                icon.style.transform = "rotate(0deg)";
            }
        }

        function openModal(id) {
            document.getElementById(id).classList.add('show');
        }

        function closeModal(id) {
            document.getElementById(id).classList.remove('show');
        }

        function bukaEdit(data) {
            document.getElementById('edit_id_agenda').value = data.id_agenda;
            document.getElementById('edit_jenis_inspeksi').value = data.jenis_inspeksi;
            document.getElementById('edit_tanggal_jadwal').value = data.tanggal_jadwal;
            document.getElementById('edit_jam_jadwal').value = data.jam_jadwal ? data.jam_jadwal.substring(0, 5) : '';
            document.getElementById('edit_line_area').value = data.line_area;
            document.getElementById('edit_id_lampu').value = data.id_lampu || '';
            document.getElementById('edit_id_user').value = data.id_user || '';
            document.getElementById('edit_status').value = data.status;
            document.getElementById('edit_catatan').value = data.catatan || '';
            openModal('modalEdit');
        }

        // Tutup modal jika klik di luar area modal-box
        document.querySelectorAll('.modal-overlay').forEach(function(overlay) {
            overlay.addEventListener('click', function(e) {
                if (e.target === overlay) {
                    overlay.classList.remove('show');
                }
            });
        });
    </script>
</body>

</html>
