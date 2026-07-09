<?php
include '../koneksi.php';
session_start();

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: ../login.php');
    exit();
}

if (isset($_POST['simpan_operator'])) {
    $_SESSION['nama_operator_popup'] = $_POST['nama_operator'];
}

$host = 'localhost';
$user = 'root';
$pass = '';
$db_name = 'safety_facility';

$db = mysqli_connect($host, $user, $pass, $db_name);

$total_pengguna = 0;
$total_fasilitas = 0;
$total_rusak = 0;
$detail_notifikasi = [];  // Array untuk menyimpan detail item yang bermasalah

if ($db) {
    $q_user = mysqli_query($db, 'SELECT COUNT(*) as total FROM users');
    if ($q_user) {
        $res = mysqli_fetch_assoc($q_user);
        $total_pengguna = $res['total'];
        if (isset($_SESSION['nama_operator_popup']) && !empty($_SESSION['nama_operator_popup'])) {
            $total_pengguna++;
        }
    }

    $c_lampu = mysqli_fetch_assoc(mysqli_query($db, 'SELECT COUNT(*) as total FROM master_lampu'))['total'] ?? 0;
    $c_p3k = mysqli_fetch_assoc(mysqli_query($db, 'SELECT COUNT(*) as total FROM master_p3k'))['total'] ?? 0;
    $c_eyewash = mysqli_fetch_assoc(mysqli_query($db, 'SELECT COUNT(*) as total FROM master_eyewash'))['total'] ?? 0;
    $total_fasilitas = $c_lampu + $c_p3k + $c_eyewash;

    $current_month = date('m');
    $current_year = date('Y');

    // Ambil detail & jumlah Lampu Rusak
    $r_lampu = mysqli_fetch_assoc(mysqli_query($db, "SELECT COUNT(*) as total FROM inspeksi_lampu WHERE kondisi = 'rusak'"))['total'] ?? 0;
    if ($r_lampu > 0) {
        $detail_notifikasi[] = [
            'icon' => 'fa-lightbulb',
            'text' => "$r_lampu Lampu Emergency dalam kondisi rusak.",
            'color' => '#dc3545',
            'url' => 'master_lampu.php'
        ];
    }

    // DISINI PERBAIKANNYA: Menggunakan code_p3k dan tanggal_inspeksi sesuai file .sql baru
    $r_p3k = mysqli_fetch_assoc(mysqli_query($db, "SELECT COUNT(DISTINCT code_p3k) as total FROM inspeksi_p3k 
        WHERE MONTH(tanggal_inspeksi) = '$current_month' AND YEAR(tanggal_inspeksi) = '$current_year' 
        AND kondisi = 'rusak'"))['total'] ?? 0;
    if ($r_p3k > 0) {
        $detail_notifikasi[] = [
            'icon' => 'fa-kit-medical',
            'text' => "$r_p3k Kotak P3K dalam kondisi rusak bulan ini.",
            'color' => '#ffc107',
            'url' => 'master_p3k.php'
        ];
    }

    // DISINI PERBAIKANNYA: Menggunakan code_eyewash dan tanggal_inspeksi sesuai file .sql baru
    $r_eyewash = mysqli_fetch_assoc(mysqli_query($db, "SELECT COUNT(DISTINCT code_eyewash) as total FROM inspeksi_eyewash 
        WHERE MONTH(tanggal_inspeksi) = '$current_month' AND YEAR(tanggal_inspeksi) = '$current_year' 
        AND kondisi = 'rusak'"))['total'] ?? 0;
    if ($r_eyewash > 0) {
        $detail_notifikasi[] = [
            'icon' => 'fa-eye-dropper',
            'text' => "$r_eyewash Eye Wash berstatus rusak bulan ini.",
            'color' => '#fd7e14',
            'url' => 'master_eyewash.php'
        ];
    }

    $total_rusak = $r_lampu + $r_p3k + $r_eyewash;
}

$hari_ini = date('l');
$daftar_hari = [
    'Sunday' => 'Minggu',
    'Monday' => 'Senin',
    'Tuesday' => 'Selasa',
    'Wednesday' => 'Rabu',
    'Thursday' => 'Kamis',
    'Friday' => 'Jumat',
    'Saturday' => 'Sabtu'
];
$hari_indo = $daftar_hari[$hari_ini];

$bulan_ini = date('F');
$daftar_bulan = [
    'January' => 'Januari',
    'February' => 'Februari',
    'March' => 'Maret',
    'April' => 'April',
    'May' => 'Mei',
    'June' => 'Juni',
    'July' => 'Juli',
    'August' => 'Agustus',
    'September' => 'September',
    'October' => 'Oktober',
    'November' => 'November',
    'December' => 'Desember'
];
$bulan_indo = $daftar_bulan[$bulan_ini];

$tanggal_format = $hari_indo . ', ' . date('d') . ' ' . $bulan_indo . ' ' . date('Y');
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cek Fasilitas Keselamatan - Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    <link rel="icon" type="image/x-icon" href="../foto/logo.png">
    <style>
        .notification-container {
            position: relative;
            display: inline-block;
        }

        .notification-icon {
            cursor: pointer;
            padding: 5px;
            position: relative;
        }

        .notification-dropdown {
            display: none;
            position: absolute;
            right: 0;
            top: 40px;
            background-color: #ffffff;
            width: 320px;
            box-shadow: 0px 8px 24px rgba(0, 0, 0, 0.15);
            border-radius: 8px;
            z-index: 1000;
            border: 1px solid #eef2f5;
            overflow: hidden;
        }

        .notification-dropdown.show {
            display: block;
        }

        .noti-header {
            padding: 12px 16px;
            background: #f8f9fa;
            border-bottom: 1px solid #eee;
            font-weight: 600;
            font-size: 14px;
            color: #333;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .noti-body {
            max-height: 280px;
            overflow-y: auto;
        }

        .noti-item {display: flex;
            align-items: flex-start;
            padding: 12px 16px;
            border-bottom: 1px solid #f1f1f1;
            transition: background 0.2s;
            text-decoration: none;
            color: #444;
            font-size: 13px;
        }x

        .noti-item:hover {
            background-color: #f9fafb;
        }

        .noti-item i {
            margin-right: 12px;
            margin-top: 2px;
            font-size: 16px;
        }

        .noti-empty {
            padding: 20px;
            text-align: center;
            color: #888;
            font-size: 13px;
        }
    </style>
</head>

<body>

    <aside class="sidebar" id="sidebar">
        <div class="sidebar-brand">
            <img src="../foto/logo.png" alt="Safety Facility Logo" class="sidebar-logo">
        </div>
        <ul class="sidebar-menu">
            <li class="active"><a href="index.php"><i class="fa-solid fa-gauge"></i> <span>Dashboard</span></a></li>
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
                    <li><a href="master_lampu.php"> • Lampu Emergency</a></li>
                    <li><a href="lampu_exit.php"> • Lampu Exit</a></li>
                    <li><a href="master_p3k.php"> • Kotak P3K</a></li>
                    <li><a href="master_eyewash.php"> • Eye Wash</a></li>
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
                    <li><a href="area_line_lampu_exit.php"> • Lampu Exit</a></li>
                    <li><a href="area_line_p3k.php"> • Kotak P3K</a></li>
                    <li><a href="area_line_eyewash.php"> • Eye Wash</a></li>
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
                    <li><a href="laporan_lampu_emergency.php"> • Lampu Emergency</a></li>
                    <li><a href="laporan_lampu_exit.php"> • Lampu Exit</a></li>
                    <li><a href="laporan_p3k.php"> • Kotak P3K</a></li>
                    <li><a href="laporan_eyewash.php"> • Eye Wash</a></li>
                </ul>
            </li>
            <li style="margin-top: 20px;"><a href="../logout.php"><i class="fa-solid fa-right-from-bracket"></i> <span>Log out</span></a></li>
        </ul>
    </aside>

    <div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

    <main class="main-content">
        <header class="topbar">
            <div class="topbar-left">
                <button class="toggle-sidebar-btn" onclick="toggleSidebar()">
                    <i class="fa-solid fa-bars"></i>
                </button>
                <div class="topbar-date">
                    <?= $tanggal_format; ?>
                </div>
            </div>
            <div class="topbar-right">
                <div class="notification-container">
                    <div class="notification-icon" id="notificationBtn">
                        <i class="fa-regular fa-bell"></i>
                        <?php if ($total_rusak > 0): ?>
                            <span class="notification-badge"><?= $total_rusak; ?></span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="notification-dropdown" id="notificationDropdown">
                        <div class="noti-header">
                            <span>Notifikasi Masalah</span>
                            <small><?= $total_rusak; ?> Temuan</small>
                        </div>
                        <div class="noti-body">
                            <?php if (!empty($detail_notifikasi)): ?>
                                <?php foreach ($detail_notifikasi as $noti): ?>
                                    <a href="<?= $noti['url']; ?>" class="noti-item">
                                        <i class="fa-solid <?= $noti['icon']; ?>" style="color: <?= $noti['color']; ?>;"></i>
                                        <div><?= $noti['text']; ?></div>
                                    </a>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="noti-empty">SEMUA FASILITAS DALAM KONDISI BAIK!</div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="user-profile">
                    <span>Hi, <strong><?= htmlspecialchars(@$_SESSION['nama_lengkap'] ?? 'Admin'); ?></strong></span>
                    <div class="user-avatar">
                        <img src="https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?q=80&w=100" alt="Avatar">
                    </div>
                </div>
            </div>
        </header>

        <section class="content-body">
            <h2 class="page-title">Dashboard</h2>

            <?php if ($total_rusak > 0): ?>
                <div class="alert-panel" id="alertNotification">
                    <div class="alert-header">
                        <span>Peringatan</span>
                        <span class="alert-close" onclick="document.getElementById('alertNotification').style.display='none'">&times;</span>
                    </div>
                    <div class="alert-body">
                        Ada <?= $total_rusak; ?> Fasilitas Keselamatan yang rusak, tidak lengkap, atau butuh perbaikan pada bulan ini.
                    </div>
                </div>
            <?php endif; ?>

            <div class="grid-shortcuts">
                <a href="scan.php" class="shortcut-card">
                    <i class="fa-solid fa-qrcode shortcut-icon"></i>
                    <span class="shortcut-title">Scan QR Code</span>
                </a>
                <a href="master_lampu.php" class="shortcut-card">
                    <i class="fa-solid fa-boxes-stacked shortcut-icon"></i>
                    <span class="shortcut-title">Data Master</span>
                </a>
                <a href="agenda.php" class="shortcut-card">
                    <i class="fa-solid fa-calendar-check shortcut-icon"></i>
                    <span class="shortcut-title">Agenda Inspeksi</span>
                </a>
                <a href="laporan_lampu_emergency.php" class="shortcut-card">
                    <i class="fa-solid fa-triangle-exclamation shortcut-icon"></i>
                    <span class="shortcut-title">Rusak & Expired</span>
                </a>
                <a href="laporan_lampu_emergency.php" class="shortcut-card">
                    <i class="fa-solid fa-file-lines shortcut-icon"></i>
                    <span class="shortcut-title">Laporan Inspeksi</span>
                </a>
                <a href="index.php" class="shortcut-card">
                    <i class="fa-solid fa-users shortcut-icon"></i>
                    <span class="shortcut-title">Pengguna</span>
                </a>
            </div>

            <div class="grid-stats">
                <div class="stat-card">
                    <div class="stat-info">
                        <h2>Total Pengguna</h2>
                        <p><?= $total_pengguna; ?></p>
                    </div>
                    <div class="stat-icon-box bg-blue">
                        <i class="fa-solid fa-users"></i>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-info">
                        <h2>Total Fasilitas</h2>
                        <p><?= $total_fasilitas; ?></p>
                    </div>
                    <div class="stat-icon-box bg-lightblue">
                        <i class="fa-solid fa-shield-heart"></i>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-info">
                        <h2>Total Masalah</h2>
                        <p><?= $total_rusak; ?></p>
                    </div>
                    <div class="stat-icon-box bg-red">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <div class="modal" id="operatorModal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <h2 style="margin-bottom:20px;">Masukkan Nama Anda</h2>
            <form method="POST">
                <label style="display:block;margin-bottom:8px;">Nama Operator</label>
                <input type="text" name="nama_operator" placeholder="Masukkan nama operator" required style="width:100%; padding:12px; border:1px solid #ddd; border-radius:6px; margin-bottom:15px;">
                <button type="submit" name="simpan_operator" style="background:#0d6efd; color:white; border:none; padding:10px 20px; border-radius:6px; cursor:pointer;">Simpan</button>
            </form>
        </div>
    </div>

    <script>
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

            if (submenu.style.display === "none" || submenu.style.display === "") {
                submenu.style.display = "block";
                icon.style.transform = "rotate(180deg)";
            } else {
                submenu.style.display = "none";
                icon.style.transform = "rotate(0deg)";
            }
        }

        function closeModal() {
            document.getElementById('operatorModal').style.display = 'none';
        }

        const notificationBtn = document.getElementById('notificationBtn');
        const notificationDropdown = document.getElementById('notificationDropdown');

        notificationBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            notificationDropdown.classList.toggle('show');
        });

        window.addEventListener('click', function(e) {
            if (!notificationBtn.contains(e.target) && !notificationDropdown.contains(e.target)) {
                notificationDropdown.classList.remove('show');
            }
        });

        window.onload = function() {
            <?php if (!isset($_SESSION['nama_operator_popup'])): ?>
                document.getElementById('operatorModal').style.display = 'block';
            <?php endif; ?>
        }
    </script>
</body>
</html>