<?php
session_start();
include '../koneksi.php';

global $koneksi;

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: ../login.php");
    exit();
}

$tahun_aktif = isset($_GET['tahun']) ? (int)$_GET['tahun'] : (int)date('Y');

$bulan_data = [];
$st = mysqli_prepare(
    $koneksi,
    "SELECT MONTH(tanggal_inspeksi) as bln, COUNT(*) as total
     FROM `inspeksi_p3k`
     WHERE YEAR(tanggal_inspeksi) = ?
     GROUP BY MONTH(tanggal_inspeksi)"
);
mysqli_stmt_bind_param($st, "i", $tahun_aktif);
mysqli_stmt_execute($st);
$res = mysqli_stmt_get_result($st);
while ($r = mysqli_fetch_assoc($res)) {
    $bulan_data[(int)$r['bln']] = (int)$r['total'];
}

$nama_bulan = [
    '',
    'Januari',
    'Februari',
    'Maret',
    'April',
    'Mei',
    'Juni',
    'Juli',
    'Agustus',
    'September',
    'Oktober',
    'November',
    'Desember'
];

$dh = [
    'Sunday' => 'Minggu',
    'Monday' => 'Senin',
    'Tuesday' => 'Selasa',
    'Wednesday' => 'Rabu',
    'Thursday' => 'Kamis',
    'Friday' => 'Jumat',
    'Saturday' => 'Sabtu'
];
$db = [
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
$tanggal_format = $dh[date('l')] . ", " . date('d') . " " . $db[date('F')] . " " . date('Y');
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Inspeksi Kotak P3K</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    <style>
        .laporan-header {
            display: flex;
            align-items: center;
            gap: 18px;
            background: #fff;
            border-radius: 14px;
            padding: 22px 28px;
            margin-bottom: 28px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, .05);
            border-left: 6px solid #ef4444;
        }

        .lh-icon {
            width: 56px;
            height: 56px;
            border-radius: 12px;
            background: #fee2e2;
            color: #ef4444;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            flex-shrink: 0;
        }

        .laporan-header h2 {
            font-size: 20px;
            font-weight: 700;
            color: #1e293b;
        }

        .laporan-header p {
            font-size: 13px;
            color: #64748b;
            margin-top: 3px;
        }

        .year-nav {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 24px;
            margin-bottom: 30px;
        }

        .year-nav h3 {
            font-size: 28px;
            font-weight: 700;
            color: #1e293b;
            min-width: 110px;
            text-align: center;
        }

        .btn-year {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 10px 22px;
            border-radius: 8px;
            border: none;
            font-size: 13.5px;
            font-weight: 500;
            cursor: pointer;
            text-decoration: none;
            background: #ef4444;
            color: #fff;
            font-family: inherit;
            transition: opacity .2s;
        }

        .btn-year:hover {
            opacity: .85;
        }

        .month-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
        }

        .month-card {
            background: #fff;
            border-radius: 14px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, .06);
            padding: 32px 16px 24px;
            text-align: center;
            text-decoration: none;
            border: 2px solid transparent;
            transition: transform .2s, box-shadow .2s;
        }

        .month-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 22px rgba(0, 0, 0, .12);
            border-color: #ef4444;
        }

        .folder-icon {
            font-size: 52px;
            margin-bottom: 14px;
            display: block;
        }

        .month-card.has-data .folder-icon {
            color: #f0a500;
        }

        .month-card.no-data .folder-icon {
            color: #cbd5e1;
        }

        .month-name {
            font-size: 15px;
            font-weight: 600;
            color: #1e293b;
        }

        .month-card.no-data .month-name {
            color: #94a3b8;
        }

        .record-badge {
            display: inline-block;
            margin-top: 10px;
            padding: 3px 14px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .month-card.has-data .record-badge {
            background: #fee2e2;
            color: #ef4444;
        }

        .month-card.no-data .record-badge {
            background: #f1f5f9;
            color: #94a3b8;
        }
    </style>
</head>

<body>
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-brand">
            <img src="../foto/logo.png" alt="Logo" class="sidebar-logo">
        </div>
        <ul class="sidebar-menu">
            <li><a href="index.php"><i class="fa-solid fa-gauge"></i> <span>Dashboard</span></a></li>
            <li class="menu-header">Menu</li>
            <li><a href="scan.php"><i class="fa-solid fa-qrcode"></i> <span>Scan Code</span></a></li>
            <li><a href="pengguna.php"><i class="fa-solid fa-users"></i> <span>Data Pengguna</span></a></li>
            <li class="has-submenu">
                <a href="javascript:void(0)" onclick="toggleSubmenu(this)">
                    <i class="fa-solid fa-boxes-stacked"></i><span>Data Master</span>
                    <i class="fa-solid fa-chevron-down submenu-icon"></i>
                </a>
                <ul class="submenu" style="display:none;">
                    <li><a href="master_lampu.php">Lampu Emergency</a></li>
                    <li><a href="lampu_exit.php">Lampu Exit</a></li>
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

            <li style="margin-top:20px;"><a href="../logout.php"><i class="fa-solid fa-right-from-bracket"></i> <span>Log out</span></a></li>
        </ul>
    </aside>

    <div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

    <main class="main-content">
        <header class="topbar">
            <div class="topbar-left">
                <button class="toggle-sidebar-btn" onclick="toggleSidebar()"><i class="fa-solid fa-bars"></i></button>
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
            <div class="laporan-header">
                <div class="lh-icon"><i class="fa-solid fa-kit-medical"></i></div>
                <div>
                    <h2>Laporan Inspeksi Kotak P3K</h2>
                    <p>Klik bulan untuk melihat data hasil inspeksi Kotak P3K</p>
                </div>
            </div>

            <div class="year-nav">
                <a href="laporan_p3k.php?tahun=<?= $tahun_aktif - 1; ?>" class="btn-year">
                    <i class="fa-solid fa-chevron-left"></i> Tahun Sebelumnya
                </a>
                <h3><?= $tahun_aktif; ?></h3>
                <a href="laporan_p3k.php?tahun=<?= $tahun_aktif + 1; ?>" class="btn-year">
                    Tahun Berikutnya <i class="fa-solid fa-chevron-right"></i>
                </a>
            </div>

            <div class="month-grid">
                <?php for ($b = 1; $b <= 12; $b++):
                    $total = $bulan_data[$b] ?? 0;
                    $has   = $total > 0;
                    $url   = "data_inspeksi_p3k.php?bulan=" . $nama_bulan[$b] . "&tahun=" . $tahun_aktif;
                ?>
                    <a href="<?= $url; ?>" class="month-card <?= $has ? 'has-data' : 'no-data'; ?>">
                        <?php if ($has): ?>
                            <i class="fa-solid fa-folder-open folder-icon"></i>
                        <?php else: ?>
                            <i class="fa-solid fa-folder folder-icon"></i>
                        <?php endif; ?>
                        <div class="month-name"><?= $nama_bulan[$b] . " " . $tahun_aktif; ?></div>
                        <span class="record-badge">
                            <?= $has ? $total . " data" : "Kosong"; ?>
                        </span>
                    </a>
                <?php endfor; ?>
            </div>
        </section>
    </main>

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

        function toggleSubmenu(el) {
            const sub = el.nextElementSibling;
            const icon = el.querySelector('.submenu-icon');
            if (sub.style.display === 'none' || sub.style.display === '') {
                sub.style.display = 'block';
                icon.style.transform = 'rotate(180deg)';
            } else {
                sub.style.display = 'none';
                icon.style.transform = 'rotate(0deg)';
            }
        }
    </script>
</body>

</html>