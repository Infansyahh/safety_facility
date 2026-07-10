<?php
session_start();
include '../koneksi.php';

global $koneksi;

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: ../login.php");
    exit();
}

// 1. AMBIL DATA DEPARTEMEN (AREA LINE EYEWASH) UNTUK DROPDOWN
$query_line = mysqli_query($koneksi, "SELECT nama_line FROM area_line WHERE jenis = 'eyewash' ORDER BY nama_line ASC");
$list_line = [];
if ($query_line) {
    while ($line = mysqli_fetch_assoc($query_line)) {
        $list_line[] = $line['nama_line'];
    }
}

// 2. LOGIKA GENERATE KODE EYE WASH OTOMATIS (EYE01, EYE02, dst)
$query_max_code = mysqli_query($koneksi, "SELECT code FROM master_eyewash WHERE code LIKE 'EYE%' ORDER BY id DESC LIMIT 1");
$next_code = "EYE01"; // Default jika belum ada data sama sekali
if ($query_max_code && mysqli_num_rows($query_max_code) > 0) {
    $row_max = mysqli_fetch_assoc($query_max_code);
    $max_code = $row_max['code']; 
    
    $num = (int)substr($max_code, 3);
    $next_num = $num + 1;
    
    $next_code = "EYE" . sprintf("%02d", $next_num);
}

// ==== SEARCH & PAGINATION ====
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
$limit_options = [10, 25, 50, 100];
if (!in_array($limit, $limit_options)) {
    $limit = 10;
}
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
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

// Hitung total data buat pagination
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

// Query untuk mengambil data master eyewash beserta data inspeksi terakhir
$query = mysqli_query($koneksi, "SELECT me.*, ie.username, ie.tanggal_inspeksi $join_sql
    ORDER BY me.id ASC
    LIMIT $limit OFFSET $offset");

$scan_data = null;
$error_scan_message = "";

if (isset($_GET['action']) && $_GET['action'] == 'scan_popup' && isset($_GET['scan_id'])) {
    $scan_id = mysqli_real_escape_string($koneksi, $_GET['scan_id']);

    $query_master = mysqli_query($koneksi, "SELECT * FROM master_eyewash WHERE id = '$scan_id' OR code = '$scan_id'");

    if ($query_master && mysqli_num_rows($query_master) > 0) {
        $scan_data = mysqli_fetch_assoc($query_master);
        $code_eyewash = $scan_data['code'];

        $query_cek_inspeksi = mysqli_query($koneksi, "SELECT tanggal_inspeksi 
            FROM inspeksi_eyewash 
            WHERE code_eyewash = '$code_eyewash' 
            AND tanggal_inspeksi >= DATE_SUB(NOW(), INTERVAL 1 MONTH)
            ORDER BY tanggal_inspeksi DESC 
            LIMIT 1");

        if ($query_cek_inspeksi && mysqli_num_rows($query_cek_inspeksi) > 0) {
            $row_inspeksi = mysqli_fetch_assoc($query_cek_inspeksi);
            $tanggal_terakhir = $row_inspeksi['tanggal_inspeksi'];

            $tanggal_bisa_kembali = date('d F Y', strtotime($tanggal_terakhir . ' + 1 month'));
            $tanggal_terakhir_indo = date('d F Y', strtotime($tanggal_terakhir));

            $scan_data = null;
            $error_scan_message = "Maaf, Alat dengan kode '$code_eyewash' sudah diinspeksi pada $tanggal_terakhir_indo. Berdasarkan aturan 1 bulan sekali, alat ini baru bisa diinspeksi kembali pada tanggal $tanggal_bisa_kembali!";
        }
    } else {
        $error_scan_message = "Data Eye Wash dengan kode '" . htmlspecialchars($scan_id) . "' tidak ditemukan atau bukan bagian dari sistem data master Eye Wash.";
    }
}

$hari_ini = date('l');
$daftar_hari = [
    'Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa',
    'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu'
];
$hari_indo = $daftar_hari[$hari_ini] ?? $hari_ini;

$bulan_ini = date('F');
$daftar_bulan = [
    'January' => 'Januari', 'February' => 'Februari', 'March' => 'Maret',
    'April' => 'April', 'May' => 'Mei', 'June' => 'Juni', 'July' => 'Juli',
    'August' => 'Agustus', 'September' => 'September', 'October' => 'Oktober',
    'November' => 'November', 'December' => 'Desember'
];
$bulan_indo = $daftar_bulan[$bulan_ini] ?? $bulan_ini;

$tanggal_format = $hari_indo . ", " . date('d') . " " . $bulan_indo . " " . date('Y');
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Master Eye Wash</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    <style>
        .table-container {
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, .1);
            overflow-x: auto;
            overflow-y: hidden;
            white-space: nowrap;
        }

        .table-container table {
            min-width: 1200px;
            width: max-content;
            border-collapse: collapse;
            margin-top: 10px;
        }

        thead tr {
            background-color: #004ef5;
            color: white;
        }

        thead th {
            padding: 15px;
            text-align: left;
            font-weight: 600;
            border: 1px solid #0042d1;
        }

        tbody td {
            padding: 12px;
            border: 1px solid #ddd;
        }

        tbody tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        tbody tr:hover {
            background-color: #f1f1f1;
        }

        .table-action-btn {
            padding: 6px 10px;
            border-radius: 4px;
            border: none;
            cursor: pointer;
            color: white;
            margin-right: 3px;
            transition: 0.3s;
        }

        .btn-barcode { background-color: #17a2b8; }
        .btn-edit { background-color: #ffc107; color: #000; }
        .btn-delete { background-color: #dc3545; }
        .table-action-btn:hover { opacity: 0.8; }

        .radio-group {
            display: flex;
            gap: 20px;
            margin-top: 5px;
            margin-bottom: 5px;
        }

        .radio-group label {
            display: flex;
            align-items: center;
            gap: 6px;
            cursor: pointer;
            font-weight: 500;
        }

        .radio-group input[type="radio"] {
            cursor: pointer;
            width: 16px;
            height: 16px;
        }
        
        .checklist-box {
            background: #f9f9f9; 
            padding: 10px; 
            border-radius: 6px; 
            border: 1px solid #ddd; 
            margin-bottom: 12px;
        }
        
        .checklist-title {
            font-size: 13px; 
            font-weight: 600; 
            color: #555;
            display: block;
            margin-top: 5px;
        }

        select.form-input-dropdown {
            width: 100%; 
            padding: 10px; 
            box-sizing: border-box; 
            margin-top: 4px; 
            border: 1px solid #ccc; 
            border-radius: 4px; 
            font-family: inherit;
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

            <li class="has-submenu active">
                <a href="javascript:void(0)" onclick="toggleSubmenu(this)">
                    <i class="fa-solid fa-boxes-stacked"></i>
                    <span>Data Master</span>
                    <i class="fa-solid fa-chevron-down submenu-icon"></i>
                </a>
                <ul class="submenu" style="display: block;">
                    <li><a href="master_lampu.php"> Lampu Emergency</a></li>
                    <li><a href="lampu_exit.php"> Lampu Exit</a></li>
                    <li><a href="master_p3k.php"> Kotak P3K</a></li>
                    <li><a href="master_eyewash.php" style="color: #004ef5; font-weight: 600;"> Eye Wash</a></li>
                </ul>
            </li>

            <li class="has-submenu">
                <a href="javascript:void(0)" onclick="toggleSubmenu(this)">
                    <i class="fa-solid fa-location-dot"></i>
                    <span>Area Line</span>
                    <i class="fa-solid fa-chevron-down submenu-icon"></i>
                </a>
                <ul class="submenu" style="display: none;">
                    <li><a href="area_line_lampu_emergency.php"> Lampu Emergency</a></li>
                    <li><a href="area_line_lampu_exit.php"> Lampu Exit</a></li>
                    <li><a href="area_line_p3k.php"> Kotak P3K</a></li>
                    <li><a href="area_line_eyewash.php"> Eye Wash</a></li>
                </ul>
            </li>

            <li><a href="aktivitas.php"><i class="fa-solid fa-clock-rotate-left"></i> <span>Aktivitas Pengguna</span></a></li>
            <li><a href="agenda.php"><i class="fa-solid fa-calendar-check"></i> <span>Agenda Inspeksi</span></a></li>

            <li class="has-submenu">
                <a href="javascript:void(0)" onclick="toggleSubmenu(this)">
                    <i class="fa-solid fa-file-invoice"></i>
                    <span>Laporan Inspeksi</span>
                    <i class="fa-solid fa-chevron-down submenu-icon"></i>
                </a>
                <ul class="submenu" style="display: none;">
                    <li><a href="laporan_lampu_emergency.php"> Lampu Emergency</a></li>
                    <li><a href="laporan_lampu_exit.php"> Lampu Exit</a></li>
                    <li><a href="laporan_p3k.php"> Kotak P3K</a></li>
                    <li><a href="laporan_eyewash.php"> Eye Wash</a></li>
                </ul>
            </li>

            <li style="margin-top: 20px;"><a href="../logout.php"><i class="fa-solid fa-right-from-bracket"></i> <span>Log out</span></a></li>
        </ul>
    </aside>

    <div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

    <main class="main-content">
        <header class="topbar">
            <div class="topbar-left">
                <button class="toggle-sidebar-btn" onclick="toggleSidebar()"><i class="fa-solid fa-bars"></i></button>
                <div class="topbar-date"><?= $tanggal_format; ?></div>
            </div>
            <div class="user-profile">
                <span>Hi, <strong><?= htmlspecialchars($_SESSION['nama_lengkap'] ?? 'Admin'); ?></strong></span>
                <div class="user-avatar">
                    <img src="https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?q=80&w=100" alt="Avatar">
                </div>
            </div>
        </header>

        <section class="content-body">
            <h2 class="page-title">Data Master Eye Wash</h2>
            <div class="top-actions">
                <button type="button" onclick="document.getElementById('modalTambah').style.display='block'" style="background: #004ef5; border:none; padding:10px 15px; margin-bottom: 10px; color:white; border-radius:5px; cursor:pointer;">
                    <i class="fa-solid fa-plus"></i> Tambah Data Baru
                </button>
                <button onclick="window.location.href='../export/excel_eyewash.php'" style="background: #20c000; border:none; padding:10px 15px; margin-bottom: 10px; color:white; border-radius:5px; cursor:pointer;">📤 Export Data Ke Excel</button>
            </div>

            <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px; margin-bottom:12px;">
                <form method="GET" style="display:flex; align-items:center; gap:8px;">
                    <input type="text" name="search" value="<?= htmlspecialchars($search); ?>" placeholder="Cari kode, departemen, lokasi, inspektor..." style="padding:8px; border:1px solid #ccc; border-radius:4px; width:280px;">
                    <input type="hidden" name="limit" value="<?= $limit; ?>">
                    <button type="submit" style="background:#004ef5; color:white; border:none; padding:8px 15px; border-radius:4px; cursor:pointer;">
                        <i class="fa-solid fa-magnifying-glass"></i> Cari
                    </button>
                    <?php if ($search !== ''): ?>
                        <a href="master_eyewash.php?limit=<?= $limit; ?>" style="padding:8px 12px; border:1px solid #ccc; background:#fff; border-radius:4px; text-decoration:none; color:#333;">Reset</a>
                    <?php endif; ?>
                </form>
                <form method="GET" style="display:flex; align-items:center; gap:8px;">
                    <input type="hidden" name="search" value="<?= htmlspecialchars($search); ?>">
                    <label style="font-weight:600;">Tampilkan:</label>
                    <select name="limit" onchange="this.form.submit()" style="padding:8px; border:1px solid #ccc; border-radius:4px;">
                        <?php foreach ($limit_options as $opt): ?>
                            <option value="<?= $opt; ?>" <?= $limit == $opt ? 'selected' : ''; ?>><?= $opt; ?></option>
                        <?php endforeach; ?>
                    </select>
                    <span>data</span>
                </form>
            </div>

            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            
                            <th style="width: 3%">No</th>
                            <th>Inspektor</th>
                            <th>Kode Eye Wash</th>
                            <th>Departemen</th>
                            <th>Lokasi</th>
                            <th>Catatan</th>
                            <th>Aliran Air (15 Menit)</th>
                            <th>Kondisi Air</th>
                            <th>Kondisi Kotak</th>
                            <th>Kondisi Akhir</th>
                            <th style="width: 10%">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $no = $offset + 1;
                        if ($query && mysqli_num_rows($query) > 0) {
                            while ($row = mysqli_fetch_assoc($query)) {
                                $safeId = $row['id'];
                                $safeCode = htmlspecialchars($row['code'], ENT_QUOTES);
                                $safeLokasi = htmlspecialchars($row['lokasi'], ENT_QUOTES);
                                $safeLineArea = htmlspecialchars($row['line_area'] ?? '', ENT_QUOTES);
                                $safeKondisi = htmlspecialchars($row['kondisi'], ENT_QUOTES);
                                $safeCatatan = htmlspecialchars($row['catatan'] ?? '', ENT_QUOTES);

                                // LOGIKA MEMECAH CATATAN MENJADI PARAMETER TERPISAH DI TABEL
                                $val_air = '-'; $val_kondisi_air = '-'; $val_kotak = '-';
                                if (!empty($row['catatan'])) {
                                    $parts = explode(', ', $row['catatan']);
                                    if (count($parts) == 3) {
                                        $val_air = htmlspecialchars($parts[0]);
                                        $val_kondisi_air = htmlspecialchars($parts[1]);
                                        $val_kotak = htmlspecialchars($parts[2]);
                                    } else {
                                        // Fallback jika catatan diubah manual dan tidak memakai format koma standar
                                        $val_air = htmlspecialchars($row['catatan']);
                                    }
                                }
                        ?>
                                <tr>
                                    <td><?= $no++; ?></td>
                                    <td><?= !empty($row['username']) ? htmlspecialchars($row['username']) : '<span style="color:#999; font-style:italic;">Belum Diinspeksi</span>'; ?></td>
                                    <td><strong><?= htmlspecialchars($row['code']); ?></strong></td>
                                    <td><?= !empty($row['line_area']) ? htmlspecialchars($row['line_area']) : '-'; ?></td>
                                    <td><?= htmlspecialchars($row['lokasi']); ?></td>
                                    <td><?= !empty($row['catatan']) ? htmlspecialchars($row['catatan']) : '-'; ?></td>
                                    <td><?= $val_air; ?></td>
                                    <td><?= $val_kondisi_air; ?></td>
                                    <td><?= $val_kotak; ?></td>
                                    <td>
                                        <?php if (strtolower($safeKondisi) == 'baik') : ?>
                                            <span style="background: #d4edda; color: #155724; padding: 4px 8px; border-radius: 4px; font-weight: 600;">Baik</span>
                                        <?php else : ?>
                                            <span style="background: #f8d7da; color: #721c24; padding: 4px 8px; border-radius: 4px; font-weight: 600;">Rusak</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <button type="button" class="table-action-btn btn-barcode" onclick="bukaModalBarcode(<?= $safeId; ?>, '<?= $safeCode; ?>')">
                                            <i class="fa-solid fa-barcode"></i>
                                        </button>

                                        <button type="button" class="table-action-btn btn-edit" onclick="bukaModalEdit(<?= $safeId; ?>, '<?= $safeCode; ?>', '<?= $safeLineArea; ?>', '<?= $safeLokasi; ?>', '<?= $safeKondisi; ?>', '<?= $safeCatatan; ?>')">
                                            <i class="fa-solid fa-pencil"></i>
                                        </button>

                                        <button type="button" class="table-action-btn btn-delete" onclick="konfirmasiHapus(<?= $safeId; ?>)">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                        <?php }
                        } else {
                            echo "<tr><td colspan='12' style='text-align:center;'>Tidak ada data master eyewash tersedia</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>

            <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px; margin-top:12px;">
                <div>
                    Menampilkan <?= $total_rows > 0 ? ($offset + 1) : 0; ?>-<?= min($offset + $limit, $total_rows); ?> dari <?= $total_rows; ?> data
                </div>
                <?php if ($total_pages > 1): ?>
                    <div style="display:flex; gap:5px; flex-wrap:wrap;">
                        <?php
                        $base_params = [];
                        if ($search !== '') $base_params['search'] = $search;
                        $base_params['limit'] = $limit;

                        function buildPageUrl($page_num, $base_params)
                        {
                            $params = $base_params;
                            $params['page'] = $page_num;
                            return 'master_eyewash.php?' . http_build_query($params);
                        }
                        ?>
                        <?php if ($page > 1): ?>
                            <a href="<?= buildPageUrl($page - 1, $base_params); ?>" style="padding:8px 12px; border:1px solid #ccc; background:#fff; border-radius:4px; text-decoration:none; color:#333;">&laquo; Sebelumnya</a>
                        <?php endif; ?>

                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <a href="<?= buildPageUrl($i, $base_params); ?>" style="padding:8px 12px; border:1px solid <?= $i == $page ? '#004ef5' : '#ccc'; ?>; background:<?= $i == $page ? '#004ef5' : '#fff'; ?>; color:<?= $i == $page ? '#fff' : '#333'; ?>; border-radius:4px; text-decoration:none; font-weight:<?= $i == $page ? '700' : '400'; ?>;"><?= $i; ?></a>
                        <?php endfor; ?>

                        <?php if ($page < $total_pages): ?>
                            <a href="<?= buildPageUrl($page + 1, $base_params); ?>" style="padding:8px 12px; border:1px solid #ccc; background:#fff; border-radius:4px; text-decoration:none; color:#333;">Selanjutnya &raquo;</a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </section>
    </main>

    <div id="modalTambah" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:1000; overflow-y: auto;">
        <div style="background:#fff; width:450px; margin:40px auto; padding:20px; border-radius:8px; box-sizing: border-box;">
            <h3>Tambah Data Eye Wash baru</h3>
            <form action="../proses/proses_tambah_eyewash.php" method="POST">
                <div style="margin-bottom:12px;">
                    <label style="font-weight:600;">Kode Eye Wash (Otomatis):</label><br>
                    <input type="text" name="code" value="<?= $next_code; ?>" readonly style="width:100%; padding:8px; box-sizing: border-box; margin-top:4px; background-color: #e9ecef; cursor: not-allowed; font-weight: bold; border: 1px solid #ccc; border-radius: 4px;">
                </div>
                <div style="margin-bottom:12px;">
                    <label style="font-weight:600;">Departemen:</label><br>
                    <select name="line_area" required class="form-input-dropdown">
                        <option value="">-- Pilih Departemen --</option>
                        <?php foreach ($list_line as $line) : ?>
                            <option value="<?= htmlspecialchars($line); ?>"><?= htmlspecialchars($line); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div style="margin-bottom:12px;">
                    <label style="font-weight:600;">Lokasi:</label><br>
                    <input type="text" name="lokasi" required placeholder="Contoh: Dekat Pintu Masuk Office 1" style="width:100%; padding:8px; box-sizing: border-box; margin-top:4px; border: 1px solid #ccc; border-radius: 4px;">
                </div>

                <div class="checklist-box">
                    <label style="font-weight:700; color:#004ef5;">Parameter Cek Fisik Eyewash:</label>
                    
                    <span class="checklist-title">1. Aliran Air selama 15 Menit</span>
                    <div class="radio-group">
                        <label><input type="radio" name="cek_air_tambah" value="Aliran Lancar" checked onclick="hitungOtomatisTambah()"> Lancar</label>
                        <label><input type="radio" name="cek_air_tambah" value="Aliran Tidak Lancar" onclick="hitungOtomatisTambah()"> Tidak Lancar</label>
                    </div>

                    <span class="checklist-title">2. Kondisi Air</span>
                    <div class="radio-group">
                        <label><input type="radio" name="cek_kondisi_air_tambah" value="Air Bersih" checked onclick="hitungOtomatisTambah()"> Bersih</label>
                        <label><input type="radio" name="cek_kondisi_air_tambah" value="Air Kotor" onclick="hitungOtomatisTambah()"> Kotor</label>
                    </div>

                    <span class="checklist-title">3. Kondisi Kotak Eyewash</span>
                    <div class="radio-group">
                        <label><input type="radio" name="cek_kotak_tambah" value="Kotak Bagus" checked onclick="hitungOtomatisTambah()"> Bagus</label>
                        <label><input type="radio" name="cek_kotak_tambah" value="Kotak Tidak Bagus" onclick="hitungOtomatisTambah()"> Tidak Bagus</label>
                    </div>
                </div>

                <div style="margin-bottom:12px;">
                    <label style="font-weight:600;">Kondisi Akhir Alat:</label>
                    <div class="radio-group">
                        <label><input type="radio" name="kondisi" id="tambah_kondisi_baik" value="baik" checked> Baik</label>
                        <label><input type="radio" name="kondisi" id="tambah_kondisi_rusak" value="rusak"> Rusak</label>
                    </div>
                </div>

                <div style="margin-bottom:15px;">
                    <label style="font-weight:600;">Catatan Detail (Bisa diedit manual):</label><br>
                    <textarea name="catatan" id="tambah_catatan" style="width:100%; padding:8px; box-sizing: border-box; margin-top:4px;" rows="3">Aliran Lancar, Air Bersih, Kotak Bagus</textarea>
                </div>

                <button type="submit" name="submit" style="background:#004ef5; color:white; border:none; padding:10px 15px; border-radius:4px; cursor:pointer;">Simpan</button>
                <button type="button" onclick="document.getElementById('modalTambah').style.display='none'" style="padding:10px 15px; border:1px solid #ccc; background:#fff; border-radius:4px; cursor:pointer;">Batal</button>
            </form>
        </div>
    </div>

    <div id="modalEdit" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:1000; overflow-y: auto;">
        <div style="background:#fff; width:450px; margin:40px auto; padding:20px; border-radius:8px; box-sizing: border-box;">
            <h3 id="modalEditTitle">Edit Data Master Eye Wash</h3>
            <form action="../proses/proses_edit_eyewash.php" method="POST">
                <input type="hidden" name="id" id="edit_id">
                <div style="margin-bottom:12px;">
                    <label style="font-weight:600;">Kode Eye Wash:</label><br>
                    <input type="text" name="code" id="edit_code" required style="width:100%; padding:8px; box-sizing: border-box; margin-top:4px;">
                </div>
                <div style="margin-bottom:12px;">
                    <label style="font-weight:600;">Departemen:</label><br>
                    <select name="line_area" id="edit_line_area" required class="form-input-dropdown">
                        <option value="">-- Pilih Departemen --</option>
                        <?php foreach ($list_line as $line) : ?>
                            <option value="<?= htmlspecialchars($line); ?>"><?= htmlspecialchars($line); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div style="margin-bottom:12px;">
                    <label style="font-weight:600;">Lokasi:</label><br>
                    <input type="text" name="lokasi" id="edit_lokasi" required style="width:100%; padding:8px; box-sizing: border-box; margin-top:4px; border: 1px solid #ccc; border-radius: 4px;">
                </div>

                <div class="checklist-box">
                    <label style="font-weight:700; color:#ffc107;">Update Parameter Cek Eyewash:</label>
                    
                    <span class="checklist-title">1. Aliran Air selama 15 Menit</span>
                    <div class="radio-group">
                        <label><input type="radio" name="cek_air_edit" id="edit_air_lancar" value="Aliran Lancar" checked onclick="hitungOtomatisEdit()"> Lancar</label>
                        <label><input type="radio" name="cek_air_edit" id="edit_air_tidak_lancar" value="Aliran Tidak Lancar" onclick="hitungOtomatisEdit()"> Tidak Lancar</label>
                    </div>

                    <span class="checklist-title">2. Kondisi Air</span>
                    <div class="radio-group">
                        <label><input type="radio" name="cek_kondisi_air_edit" id="edit_kondisi_air_bersih" value="Air Bersih" checked onclick="hitungOtomatisEdit()"> Bersih</label>
                        <label><input type="radio" name="cek_kondisi_air_edit" id="edit_kondisi_air_kotor" value="Air Kotor" onclick="hitungOtomatisEdit()"> Kotor</label>
                    </div>

                    <span class="checklist-title">3. Kondisi Kotak Eyewash</span>
                    <div class="radio-group">
                        <label><input type="radio" name="cek_kotak_edit" id="edit_kotak_bagus" value="Kotak Bagus" checked onclick="hitungOtomatisEdit()"> Bagus</label>
                        <label><input type="radio" name="cek_kotak_edit" id="edit_kotak_tidak_bagus" value="Kotak Tidak Bagus" onclick="hitungOtomatisEdit()"> Tidak Bagus</label>
                    </div>
                </div>

                <div style="margin-bottom:12px;">
                    <label style="font-weight:600;">Kondisi Akhir Alat:</label>
                    <div class="radio-group">
                        <label><input type="radio" name="kondisi" id="edit_kondisi_baik" value="baik"> Baik</label>
                        <label><input type="radio" name="kondisi" id="edit_kondisi_rusak" value="rusak"> Rusak</label>
                    </div>
                </div>

                <div style="margin-bottom:15px;">
                    <label style="font-weight:600;">Catatan Detail (Bisa diedit manual):</label><br>
                    <textarea name="catatan" id="edit_catatan" style="width:100%; padding:8px; box-sizing: border-box; margin-top:4px;" rows="3"></textarea>
                </div>

                <button type="submit" name="submit" style="background:#ffc107; color:black; border:none; padding:10px 15px; border-radius:4px; cursor:pointer; font-weight:600;">Update Data</button>
                <button type="button" onclick="document.getElementById('modalEdit').style.display='none'" style="padding:10px 15px; border:1px solid #ccc; background:#fff; border-radius:4px; cursor:pointer;">Batal</button>
            </form>
        </div>
    </div>

    <div id="modalBarcode" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:1000; overflow-y: auto;">
        <div style="background:#fff; width:650px; margin:50px auto; padding:20px; border-radius:8px; text-align: center; box-sizing: border-box;">
            <h3 style="margin-bottom: 15px;">QR Code Generator</h3>
            <input type="text" id="barcode_code_view" readonly style="width:50%; padding:8px; text-align:center; background:#f1f1f1; border:1px solid #ddd; margin-bottom: 15px; border-radius: 4px;">
            <div style="margin-bottom: 20px; border: 1px solid #ddd; padding: 10px; background: #fafafa; display: inline-block;">
                <img id="img_barcode_preview" src="" alt="Barcode Preview" style="max-width: 100%; height: auto; display: block;">
            </div>
            <br>
            <a id="btn_download_barcode" href="javascript:void(0)" download="template_barcode_eyewash.png" style="background:#28a745; color:white; text-decoration:none; padding:10px 20px; border-radius:5px; display:inline-block; margin-right: 10px; font-weight: 600;">
                <i class="fa-solid fa-download"></i> Download as Image
            </a>
            <button type="button" onclick="document.getElementById('modalBarcode').style.display='none'" style="padding:10px 20px; border-radius:5px; border:1px solid #ccc; cursor:pointer; background:#fff;">Tutup</button>
        </div>
    </div>

    <script>
        window.onload = function() {
            <?php if (!empty($error_scan_message)): ?>
                alert("⚠️ <?= addslashes($error_scan_message); ?>");
                window.history.replaceState({}, document.title, window.location.pathname);
            <?php elseif ($scan_data): ?>
                var id = "<?= $scan_data['id']; ?>";
                var code = <?= json_encode($scan_data['code']); ?>;
                var lineArea = <?= json_encode($scan_data['line_area'] ?? ''); ?>;
                var lokasi = <?= json_encode($scan_data['lokasi']); ?>;
                var kondisi = <?= json_encode($scan_data['kondisi']); ?>;
                var catatan = <?= json_encode($scan_data['catatan'] ?? ''); ?>;

                bukaModalEdit(id, code, lineArea, lokasi, kondisi, catatan);
                document.getElementById('modalEditTitle').innerHTML = "📋 Isi Data Hasil Scan Eye Wash";
            <?php endif; ?>
        }

        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebarOverlay');
            sidebar.classList.toggle('minimized');
            const isOpen = sidebar.classList.contains('minimized');

            if (window.innerWidth <= 768) {
                // Mobile: "minimized" = sidebar kebuka. Overlay nongol biar bisa klik luar buat tutup.
                overlay.classList.toggle('show', isOpen);
                return;
            }

            const mainContent = document.querySelector('.main-content');
            mainContent.style.marginLeft = isOpen ? '70px' : '230px';
        }

        function toggleSubmenu(element) {
            const submenu = element.nextElementSibling;
            const icon = element.querySelector('.submenu-icon');
            submenu.style.display = (submenu.style.display === "none") ? "block" : "none";
            icon.style.transform = (submenu.style.display === "block") ? "rotate(180deg)" : "rotate(0deg)";
        }

        function hitungOtomatisTambah() {
            let air = document.querySelector('input[name="cek_air_tambah"]:checked').value;
            let kondisiAir = document.querySelector('input[name="cek_kondisi_air_tambah"]:checked').value;
            let kotak = document.querySelector('input[name="cek_kotak_tambah"]:checked').value;

            if(air.includes("Tidak Lancar") || kondisiAir.includes("Kotor") || kotak.includes("Tidak Bagus")) {
                document.getElementById('tambah_kondisi_rusak').checked = true;
            } else {
                document.getElementById('tambah_kondisi_baik').checked = true;
            }
            document.getElementById('tambah_catatan').value = air + ", " + kondisiAir + ", " + kotak;
        }

        function hitungOtomatisEdit() {
            let air = document.querySelector('input[name="cek_air_edit"]:checked').value;
            let kondisiAir = document.querySelector('input[name="cek_kondisi_air_edit"]:checked').value;
            let kotak = document.querySelector('input[name="cek_kotak_edit"]:checked').value;

            if(air.includes("Tidak Lancar") || kondisiAir.includes("Kotor") || kotak.includes("Tidak Bagus")) {
                document.getElementById('edit_kondisi_rusak').checked = true;
            } else {
                document.getElementById('edit_kondisi_baik').checked = true;
            }
            document.getElementById('edit_catatan').value = air + ", " + kondisiAir + ", " + kotak;
        }

        function bukaModalEdit(id, code, lineArea, lokasi, kondisi, catatan) {
            document.getElementById('modalEdit').style.display = 'block';
            document.getElementById('edit_id').value = id;
            document.getElementById('edit_code').value = code;
            document.getElementById('edit_line_area').value = lineArea;
            document.getElementById('edit_lokasi').value = lokasi;

            if (kondisi.toLowerCase() === 'baik') {
                document.getElementById('edit_kondisi_baik').checked = true;
            } else {
                document.getElementById('edit_kondisi_rusak').checked = true;
            }

            document.getElementById('edit_catatan').value = catatan;

            if(catatan.includes("Tidak Lancar")) document.getElementById('edit_air_tidak_lancar').checked = true;
            else document.getElementById('edit_air_lancar').checked = true;

            if(catatan.includes("Kotor")) document.getElementById('edit_kondisi_air_kotor').checked = true;
            else document.getElementById('edit_kondisi_air_bersih').checked = true;

            if(catatan.includes("Tidak Bagus")) document.getElementById('edit_kotak_tidak_bagus').checked = true;
            else document.getElementById('edit_kotak_bagus').checked = true;
        }

        function konfirmasiHapus(id) {
            if (confirm("Apakah Anda yakin ingin menghapus data master eyewash ini?")) {
                window.location.href = "../proses/proses_hapus_eyewash.php?id=" + id;
            }
        }

        function bukaModalBarcode(id, code) {
            document.getElementById('modalBarcode').style.display = 'block';
            document.getElementById('barcode_code_view').value = code;

            var timestamp = new Date().getTime();
            var srtPath = 'cetak_barcode_eyewash.php?id=' + id + '&t=' + timestamp;

            document.getElementById('img_barcode_preview').src = srtPath;

            var btnDownload = document.getElementById('btn_download_barcode');
            btnDownload.href = srtPath;
            btnDownload.download = 'Barcode_Eyewash_' + code + '.png';
        }
    
    </script>
</body>
</html>