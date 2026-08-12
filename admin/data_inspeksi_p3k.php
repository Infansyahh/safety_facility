<?php
session_start();
include '../koneksi.php';

global $koneksi;

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: ../login.php");
    exit();
}

$nama_bulan_map = [
    'Januari' => 1,
    'Februari' => 2,
    'Maret' => 3,
    'April' => 4,
    'Mei' => 5,
    'Juni' => 6,
    'Juli' => 7,
    'Agustus' => 8,
    'September' => 9,
    'Oktober' => 10,
    'November' => 11,
    'Desember' => 12
];

$bulan  = isset($_GET['bulan']) ? $_GET['bulan'] : date('F');
$tahun  = isset($_GET['tahun']) ? (int)$_GET['tahun'] : (int)date('Y');
$cari   = isset($_GET['cari'])  ? trim($_GET['cari']) : '';

// $bulan dikirim dalam bahasa Indonesia (dari laporan_p3k.php), jadi diubah ke angka bulan
$bulan_angka = $nama_bulan_map[$bulan] ?? (int)date('n');

/* ---- HAPUS ---- */
if (isset($_GET['hapus']) && is_numeric($_GET['hapus'])) {
    $id   = (int)$_GET['hapus'];
    $stmt = mysqli_prepare($koneksi, "DELETE FROM `inspeksi_p3k` WHERE `id_inspeksi` = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    header("Location: data_inspeksi_p3k.php?bulan=" . urlencode($bulan) . "&tahun=$tahun&msg=hapus");
    exit();
}

/* ---- QUERY DATA BERDASARKAN MASTER P3K ---- */
$sql = "SELECT i.id_inspeksi, i.username AS nama_operator, i.tanggal_inspeksi,
               m.code AS code_p3k, m.lokasi, i.line_area,
               i.kondisi_kotak, i.kelengkapan_isi, i.expired_obat, i.keterangan
        FROM master_p3k m
        LEFT JOIN inspeksi_p3k i ON m.code = i.code_p3k 
             AND MONTH(i.tanggal_inspeksi) = ? 
             AND YEAR(i.tanggal_inspeksi) = ?
             AND i.username = ?";

$nama_operator_login = $_SESSION['nama_operator_popup'] ?? '';

$param_types  = "iis";
$param_values = [$bulan_angka, $tahun, $nama_operator_login];

if ($cari !== '') {
    $like = "%$cari%";
    // Menggunakan WHERE karena kondisi sebelumnya sudah dipindahkan ke blok ON pada LEFT JOIN
    $sql .= " WHERE (i.username LIKE ? OR m.code LIKE ? OR m.lokasi LIKE ? OR i.line_area LIKE ?)";
    $param_types .= "ssss";
    $param_values[] = $like;
    $param_values[] = $like;
    $param_values[] = $like;
    $param_values[] = $like;
}

$sql .= " ORDER BY m.code ASC, i.tanggal_inspeksi DESC";

$stmt = mysqli_prepare($koneksi, $sql);
mysqli_stmt_bind_param($stmt, $param_types, ...$param_values);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$rows = [];
while ($r = mysqli_fetch_assoc($result)) $rows[] = $r;
$total = count($rows);

/* ---- EXPORT EXCEL ---- */
if (isset($_GET['export']) && $_GET['export'] === 'excel') {
    require '../vendor/autoload.php';
    require '../export_excel_helper.php';

    $headers = ['No', 'Nama Operator', 'Tanggal Inspeksi', 'Kode P3K', 'Lokasi', 'Line / Area', 'Kondisi Kotak', 'Kelengkapan Isi', 'Expired Obat', 'Keterangan'];
    $data = [];
    $no = 1;
    foreach ($rows as $r) {
        $vals = array_values($r);
        array_shift($vals);
        array_unshift($vals, $no++);
        // Handle format penanggalan untuk export excel
        $vals[2] = !empty($vals[2]) ? date('d-m-Y H:i', strtotime($vals[2])) : '-';
        $data[] = $vals;
    }
    export_excel_xlsx($headers, $data, 'Laporan_Kotak_P3K_' . $bulan . '_' . $tahun);
    exit();
}

/* ---- FORMAT TANGGAL ---- */
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
$bulan_indo = $db[$bulan] ?? $bulan;

$baik_words  = ['Baik', 'Layak', 'Lengkap', 'Belum Expired', 'Mengalir', 'Bersih'];
$tidak_words = ['Tidak', 'Tidak Layak', 'Tidak Lengkap', 'Ada yang Expired', 'Tidak Mengalir', 'Kotor'];
$baik_count  = 0;
$tidak_count = 0;
foreach ($rows as $r) {
    $vals = array_values($r);
    $cv = $vals[4] ?? '';
    if (in_array($cv, $baik_words)) $baik_count++;
    elseif (in_array($cv, $tidak_words)) $tidak_count++;
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Kotak P3K - <?= $bulan_indo . ' ' . $tahun; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    <style>
        .page-head {
            display: flex;
            align-items: center;
            gap: 16px;
            background: #fff;
            border-radius: 14px;
            padding: 20px 26px;
            margin-bottom: 22px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, .05);
            border-left: 6px solid #ef4444;
        }

        .ph-icon {
            width: 52px;
            height: 52px;
            border-radius: 11px;
            background: #fee2e2;
            color: #ef4444;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            flex-shrink: 0;
        }

        .ph-info h2 {
            font-size: 18px;
            font-weight: 700;
            color: #1e293b;
        }

        .ph-info p {
            font-size: 13px;
            color: #64748b;
            margin-top: 3px;
        }

        .stats-row {
            display: flex;
            gap: 14px;
            flex-wrap: wrap;
            margin-bottom: 20px;
        }

        .s-card {
            background: #fff;
            border-radius: 10px;
            padding: 14px 20px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, .05);
            display: flex;
            align-items: center;
            gap: 12px;
            min-width: 155px;
        }

        .s-icon {
            width: 38px;
            height: 38px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            color: #fff;
            flex-shrink: 0;
        }

        .s-info h4 {
            font-size: 11px;
            color: #64748b;
            font-weight: 500;
        }

        .s-info p {
            font-size: 20px;
            font-weight: 700;
            color: #1e293b;
        }

        .action-bar {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
        }

        .ab-left,
        .ab-right {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            align-items: center;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 9px 18px;
            border-radius: 8px;
            border: none;
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
            text-decoration: none;
            font-family: inherit;
            white-space: nowrap;
        }

        .btn-back {
            background: #64748b;
            color: #fff;
        }

        .btn-excel {
            background: #1d6f42;
            color: #fff;
        }

        .btn-pdf {
            background: #c0392b;
            color: #fff;
        }

        .btn-back:hover {
            background: #475569;
        }

        .btn-excel:hover {
            background: #155534;
        }

        .btn-pdf:hover {
            background: #a93226;
        }

        .btn-search {
            background: #ef4444;
            color: #fff;
        }

        .btn-reset {
            background: #e2e8f0;
            color: #64748b;
        }

        .search-wrap {
            display: flex;
            gap: 7px;
        }

        .search-wrap input {
            padding: 9px 14px;
            border: 1px solid #d8dee9;
            border-radius: 8px;
            font-size: 13px;
            font-family: inherit;
            outline: none;
            width: 240px;
        }

        .table-card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, .06);
            overflow-x: auto;
        }

        table.dt {
            width: 100%;
            border-collapse: collapse;
            min-width: 880px;
        }

        table.dt thead tr {
            background: #1e293b;
        }

        table.dt th {
            text-align: left;
            font-size: 11.5px;
            text-transform: uppercase;
            letter-spacing: .5px;
            color: #94a3b8;
            padding: 13px 15px;
            font-weight: 600;
            white-space: nowrap;
        }

        table.dt td {
            padding: 12px 15px;
            font-size: 13px;
            color: #334155;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
        }

        table.dt tr:last-child td {
            border-bottom: none;
        }

        table.dt tbody tr:hover td {
            background: #f8fafc;
        }

        table.dt tbody tr:nth-child(even) td {
            background: #fafbfd;
        }

        table.dt tbody tr:nth-child(even):hover td {
            background: #f1f5f9;
        }

        .no-col {
            width: 46px;
            text-align: center;
            font-weight: 600;
            color: #94a3b8;
        }

        .td-date {
            white-space: nowrap;
        }

        .badge {
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 11.5px;
            font-weight: 600;
            white-space: nowrap;
        }

        .b-green {
            background: #ddf3e8;
            color: #1e9e63;
        }

        .b-red {
            background: #fbe2e1;
            color: #d33a39;
        }

        .b-orange {
            background: #fdf0d8;
            color: #b8790a;
        }

        .b-blue {
            background: #e3edfb;
            color: #2b75cc;
        }

        .btn-del {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 30px;
            height: 30px;
            border-radius: 7px;
            background: #fbe2e1;
            color: #d33a39;
            border: none;
            cursor: pointer;
            text-decoration: none;
            font-size: 13px;
            transition: background .2s;
        }

        .btn-del:hover {
            background: #d33a39;
            color: #fff;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #94a3b8;
        }

        .empty-state i {
            font-size: 48px;
            margin-bottom: 14px;
            display: block;
        }

        .alert-ok {
            background: #ddf3e8;
            color: #1e9e63;
            padding: 12px 18px;
            border-radius: 8px;
            margin-bottom: 18px;
            font-size: 13.5px;
        }

        @media print {

            .sidebar,
            .topbar,
            .action-bar,
            .btn-del {
                display: none !important;
            }

            table.dt thead tr {
                background: #1e293b !important;
                -webkit-print-color-adjust: exact;
            }
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
            <?php if (isset($_GET['msg']) && $_GET['msg'] === 'hapus'): ?>
                <div class="alert-ok"><i class="fa-solid fa-circle-check"></i> Data inspeksi berhasil dihapus.</div>
            <?php endif; ?>

            <div class="page-head">
                <div class="ph-icon"><i class="fa-solid fa-kit-medical"></i></div>
                <div class="ph-info">
                    <h2>Laporan Inspeksi Kotak P3K</h2>
                    <p><?= $bulan_indo . " " . $tahun; ?></p>
                </div>
            </div>

            <div class="stats-row">
                <div class="s-card">
                    <div class="s-icon" style="background:#ef4444"><i class="fa-solid fa-list-check"></i></div>
                    <div class="s-info">
                        <h4>Total Data</h4>
                        <p><?= $total; ?></p>
                    </div>
                </div>
                <div class="s-card">
                    <div class="s-icon" style="background:#1e9e63"><i class="fa-solid fa-circle-check"></i></div>
                    <div class="s-info">
                        <h4>Kondisi Baik</h4>
                        <p><?= $baik_count; ?></p>
                    </div>
                </div>
                <div class="s-card">
                    <div class="s-icon" style="background:#d33a39"><i class="fa-solid fa-triangle-exclamation"></i></div>
                    <div class="s-info">
                        <h4>Perlu Perhatian</h4>
                        <p><?= $tidak_count; ?></p>
                    </div>
                </div>
            </div>

            <div class="action-bar">
                <div class="ab-left">
                    <a href="laporan_p3k.php?tahun=<?= $tahun; ?>" class="btn btn-back">
                        <i class="fa-solid fa-arrow-left"></i> Kembali
                    </a>
                    <a href="data_inspeksi_p3k.php?bulan=<?= urlencode($bulan); ?>&tahun=<?= $tahun; ?>&cari=<?= urlencode($cari); ?>&export=excel"
                        class="btn btn-excel">
                        <i class="fa-solid fa-file-excel"></i> Export Excel
                    </a>
                    <button onclick="window.print()" class="btn btn-pdf">
                        <i class="fa-solid fa-file-pdf"></i> Export PDF
                    </button>
                </div>
                <div class="ab-right">
                    <form method="GET" class="search-wrap">
                        <input type="hidden" name="bulan" value="<?= htmlspecialchars($bulan); ?>">
                        <input type="hidden" name="tahun" value="<?= $tahun; ?>">
                        <input type="text" name="cari" placeholder="Cari nama, ID, lokasi..." value="<?= htmlspecialchars($cari); ?>">
                        <button type="submit" class="btn btn-search"><i class="fa-solid fa-magnifying-glass"></i></button>
                        <?php if ($cari !== ''): ?>
                            <a href="data_inspeksi_p3k.php?bulan=<?= urlencode($bulan); ?>&tahun=<?= $tahun; ?>"
                                class="btn btn-reset"><i class="fa-solid fa-xmark"></i></a>
                        <?php endif; ?>
                    </form>
                </div>
            </div>

            <div class="table-card">
                <table class="dt">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Operator</th>
                            <th>Tanggal Inspeksi</th>
                            <th>Kode P3K</th>
                            <th>Lokasi</th>
                            <th>Line / Area</th>
                            <th>Kondisi Kotak</th>
                            <th>Kelengkapan Isi</th>
                            <th>Expired Obat</th>
                            <th>Keterangan</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($total > 0): ?>
                            <?php $no = 1;
                            foreach ($rows as $r):
                                $vals   = array_values($r);
                                $id_rec = $vals[0]; // PK Inspeksi (bisa NULL jika belum diinspeksi)
                            ?>
                                <tr>
                                    <td class="no-col"><?= $no++; ?></td>
                                    <td><?= htmlspecialchars($vals[1] ?? '-'); ?></td>
                                    <td class="td-date"><?= !empty($vals[2]) ? date('d-m-Y H:i', strtotime($vals[2])) : '-'; ?></td>
                                    <?php
                                    $baik_w  = ['Baik', 'Layak', 'Lengkap', 'Belum Expired', 'Mengalir', 'Bersih'];
                                    $tidak_w = ['Tidak', 'Tidak Layak', 'Tidak Lengkap', 'Ada yang Expired', 'Tidak Mengalir', 'Kotor'];
                                    foreach (array_slice($vals, 3) as $cv):
                                        if (in_array($cv, $baik_w)):
                                    ?>
                                            <td><span class="badge b-green"><?= $cv; ?></span></td>
                                        <?php elseif (in_array($cv, $tidak_w)): ?>
                                            <td><span class="badge b-red"><?= $cv; ?></span></td>
                                        <?php else: ?>
                                            <td><?= htmlspecialchars($cv ?? '-'); ?></td>
                                    <?php endif;
                                    endforeach; ?>
                                    <td>
                                        <?php if (!empty($id_rec)): ?>
                                            <a href="data_inspeksi_p3k.php?bulan=<?= urlencode($bulan); ?>&tahun=<?= $tahun; ?>&cari=<?= urlencode($cari); ?>&hapus=<?= $id_rec; ?>"
                                                class="btn-del" title="Hapus"
                                                onclick="return confirm('Yakin hapus data ini? Tidak bisa dibatalkan.')">
                                                <i class="fa-solid fa-trash"></i>
                                            </a>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="11">
                                    <div class="empty-state">
                                        <i class="fa-solid fa-folder-open"></i>
                                        <p>Tidak ada data Kotak P3K untuk <?= $bulan_indo . " " . $tahun; ?></p>
                                        <?php if ($cari !== ''): ?>
                                            <p style="margin-top:6px;font-size:13px;">
                                                Kata kunci: "<strong><?= htmlspecialchars($cari); ?></strong>"
                                            </p>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <p style="margin-top:12px;font-size:13px;color:#94a3b8;">
                Menampilkan <strong><?= $total; ?></strong> data Kotak P3K
                <?= $cari !== '' ? ' &mdash; pencarian: "<em>' . htmlspecialchars($cari) . '</em>"' : ''; ?>
            </p>
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
            const sub = el.nextElementSibling,
                icon = el.querySelector('.submenu-icon');
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