<?php
session_start();
include '../koneksi.php';
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: ../login.php");
    exit();
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

$tanggal_format = $hari_indo . ", " . date('d') . " " . $bulan_indo . " " . date('Y');
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Scan Code - Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    <script src="https://unpkg.com/html5-qrcode"></script>
    <link rel="icon" type="image/x-icon" href="../foto/logo.png">
</head>

<body>
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-brand">
            <img src="../foto/logo.png" alt="Safety Facility Logo" class="sidebar-logo">
        </div>
        <ul class="sidebar-menu">
            <li><a href="index.php"><i class="fa-solid fa-gauge"></i> <span>Dashboard</span></a></li>
            <li class="menu-header">Menu</li>
            <li class="active"><a href="scan.php"><i class="fa-solid fa-qrcode"></i> <span>Scan Code</span></a></li>
            <li><a href="../login.php"><i class="fa-solid fa-users"></i> <span>Data Pengguna</span></a></li>

            <li class="has-submenu">
                <a href="javascript:void(0)" onclick="toggleSubmenu(this)">
                    <i class="fa-solid fa-boxes-stacked"></i>
                    <span>Data Master</span>
                    <i class="fa-solid fa-chevron-down submenu-icon"></i>
                </a>
                <ul class="submenu" style="display: none;">
                    <li><a href="master_lampu.php"> Lampu Emergency</a></li>
                    <li><a href="lampu_exit.php"> Lampu Exit</a></li>
                    <li><a href="master_p3k.php"> Kotak P3K</a></li>
                    <li><a href="master_eyewash.php"> Eye Wash</a></li>
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
                    <li><a href="lampu_exit.php"> Lampu Exit</a></li>
                    <li><a href="area_line_p3k.php"> Kotak P3K</a></li>
                    <li><a href="#"> Eye Wash</a></li>
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
                    <li><a href="master_lampu.php"> Lampu Emergency</a></li>
                    <li><a href="#"> Lampu Exit</a></li>
                    <li><a href="laporan_p3k.php"> Kotak P3K</a></li>
                    <li><a href="#"> Eye Wash</a></li>
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
                <div class="topbar-date"><?= $tanggal_format; ?></div>
            </div>
            <div class="user-profile">
                <span>Hi, <strong><?= htmlspecialchars($_SESSION['nama_lengkap']); ?></strong></span>
                <div class="user-avatar">
                    <img src="https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?q=80&w=100" alt="Avatar">
                </div>
        </header>

        <section class="content-body">
            <h2 class="page-title">Scan QR Code</h2>
            <div style="background: #fff; padding: 20px; border-radius: 10px; border: 2px solid #000; max-width: 500px; margin: auto;">
                <div id="reader"></div>
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

        // --- GANTI BAGIAN INI ---
        let html5QrcodeScanner = new Html5QrcodeScanner("reader", {
            fps: 15, // Dinaikkan sedikit agar pembacaan kamera lebih responsif
            qrbox: {
                width: 300,
                height: 300
            }, // Memperbesar area deteksi kotak
            formatsToSupport: [Html5QrcodeSupportedFormats.QR_CODE], // Kunci HANYA membaca QR Code agar tidak terganggu teks lain
            experimentalFeatures: {
                useBarCodeDetectorIfSupported: true // Menggunakan hardware akselerasi jika tersedia
            }
        });
        // -------------------------

        html5QrcodeScanner.render((decodedText) => {
            // Bersihkan spasi di awal/akhir jika ada
            let cleanText = decodedText.trim();

            if (cleanText !== "") {
                // Arahkan ke proses_scan.php untuk deteksi jenis alat secara dinamis
                window.location.href = "../proses/proses_scan.php?scan_id=" + encodeURIComponent(cleanText);
            } else {
                alert("QR Code kosong atau tidak terbaca dengan jelas.");
            }
        });

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

        window.onload = function() {
            <?php if (!isset($_SESSION['nama_operator_popup'])): ?>
                document.getElementById('operatorModal').style.display = 'block';
            <?php endif; ?>
        }
    </script>
</body>

</html>