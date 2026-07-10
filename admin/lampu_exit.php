<?php
session_start();
include '../koneksi.php';

global $koneksi;

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: ../login.php");
    exit();
}

// QUERY UTAMA: Mengambil data dari master_lampu yang kodenya berawalan 'LE'
// dan digabungkan dengan data inspeksi terakhir dari inspeksi_lampu
$query = mysqli_query($koneksi, "SELECT ml.*, il.username, il.tanggal_inspeksi 
    FROM master_lampu ml
    LEFT JOIN (
        SELECT code_lampu, username, tanggal_inspeksi 
        FROM inspeksi_lampu 
        WHERE id_inspeksi IN (SELECT MAX(id_inspeksi) FROM inspeksi_lampu GROUP BY code_lampu)
    ) il ON ml.code = il.code_lampu
    WHERE ml.code LIKE 'LE%'");

// Ambil data departemen secara dinamis dari area line lampu exit
$query_area = mysqli_query($koneksi, "SELECT nama_line FROM area_line WHERE jenis = 'lampu_exit' ORDER BY nama_line ASC");
$daftar_area = [];
while ($area = mysqli_fetch_assoc($query_area)) {
    $daftar_area[] = $area['nama_line'];
}

$scan_data = null;
$error_scan_message = "";

if (isset($_GET['action']) && $_GET['action'] == 'scan_popup' && isset($_GET['scan_id'])) {
    $scan_id = mysqli_real_escape_string($koneksi, $_GET['scan_id']);

    // Cari data master berdasarkan ID atau Code Lampu (hanya yang berawalan LE)
    $query_master = mysqli_query($koneksi, "SELECT * FROM master_lampu 
        WHERE (id = '$scan_id' OR code = '$scan_id') AND code LIKE 'LE%'");

    if ($query_master && mysqli_num_rows($query_master) > 0) {
        $scan_data = mysqli_fetch_assoc($query_master);
        $code_lampu = $scan_data['code'];

        // --- PROSES VALIDASI INTERVAL 1 BULAN (30 HARI) ---
        $query_cek_inspeksi = mysqli_query($koneksi, "SELECT tanggal_inspeksi 
            FROM inspeksi_lampu 
            WHERE code_lampu = '$code_lampu' 
            AND tanggal_inspeksi >= DATE_SUB(NOW(), INTERVAL 1 MONTH)
            ORDER BY tanggal_inspeksi DESC 
            LIMIT 1");

        if ($query_cek_inspeksi && mysqli_num_rows($query_cek_inspeksi) > 0) {
            $row_inspeksi = mysqli_fetch_assoc($query_cek_inspeksi);
            $tanggal_terakhir = $row_inspeksi['tanggal_inspeksi'];

            $tanggal_bisa_kembali = date('d F Y', strtotime($tanggal_terakhir . ' + 1 month'));
            $tanggal_terakhir_indo = date('d F Y', strtotime($tanggal_terakhir));

            $scan_data = null;
            $error_scan_message = "Maaf, Alat dengan kode '$code_lampu' sudah diinspeksi pada $tanggal_terakhir_indo. Berdasarkan aturan 1 bulan sekali, alat ini baru bisa diinspeksi kembali pada tanggal $tanggal_bisa_kembali!";
        }
    } else {
        $error_scan_message = "Data Lampu Exit dengan kode '" . htmlspecialchars($scan_id) . "' tidak ditemukan di sistem data master.";
    }
}

// Format Tanggal Topbar
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
$hari_indo = $daftar_hari[$hari_ini] ?? $hari_ini;

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
$bulan_indo = $daftar_bulan[$bulan_ini] ?? $bulan_ini;

$tanggal_format = $hari_indo . ", " . date('d') . " " . $bulan_indo . " " . date('Y');
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Master Lampu Exit</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    <style>
        .table-container {
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
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

        .btn-barcode {
            background-color: #17a2b8;
        }

        .btn-edit {
            background-color: #ffc107;
            color: #000;
        }

        .btn-delete {
            background-color: #dc3545;
        }

        .table-action-btn:hover {
            opacity: 0.8;
        }

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
                    <li><a href="lampu_exit.php" style="color: #004ef5; font-weight: 600;"> Lampu Exit</a></li>
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
            <h2 class="page-title">Data Master Lampu Exit</h2>
            <div class="top-actions">
                <button type="button" onclick="document.getElementById('modalTambah').style.display='block'" style="background: #004ef5; border:none; padding:10px 15px; margin-bottom: 10px; color:white; border-radius:5px; cursor:pointer;">
                    <i class="fa-solid fa-plus"></i> Tambah Data Baru
                </button>
                <button onclick="window.location.href='../export/excel_exit.php'" style="background: #20c000; border:none; padding:10px 15px; margin-bottom: 10px; color:white; border-radius:5px; cursor:pointer;">📤 Export Data Ke Excel</button>
            </div>

            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th style="width: 5%">No</th>
                            <th>Inspektor</th>
                            <th>Kode</th>
                            <th>Departemen</th>
                            <th>Lokasi</th>
                            <th>Catatan</th>
                            <th>Lampu Exit</th>
                            <th style="width: 12%">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $no = 1;
                        if ($query && mysqli_num_rows($query) > 0) {
                            while ($row = mysqli_fetch_assoc($query)) {
                                $safeCode = htmlspecialchars($row['code'], ENT_QUOTES);
                                $safeDepartemen = htmlspecialchars($row['line_area'] ?? '', ENT_QUOTES);
                                $safeLokasi = htmlspecialchars($row['lokasi'], ENT_QUOTES);
                                $safeIndikator = htmlspecialchars($row['indikator_mati_menyala'] ?? '', ENT_QUOTES);
                                $safeKondisi = htmlspecialchars($row['kondisi'], ENT_QUOTES);
                                $safeCatatan = htmlspecialchars($row['catatan'] ?? '', ENT_QUOTES);

                                // Menentukan status Lampu Exit: Nyala atau Mati
                                $statusLampu = 'Mati';
                                if (!empty($safeIndikator)) {
                                    $statusLampu = (strtolower($safeIndikator) == 'nyala') ? 'Nyala' : 'Mati';
                                } else {
                                    $statusLampu = (strtolower($safeKondisi) == 'baik') ? 'Nyala' : 'Mati';
                                }
                        ?>
                                <tr>
                                    <td><?= $no++; ?></td>
                                    <td><?= !empty($row['username']) ? htmlspecialchars($row['username']) : '<span style="color:#999; font-style:italic;">Belum Diinspeksi</span>'; ?></td>
                                    <td><?= htmlspecialchars($row['code']); ?></td>
                                    <td><?= !empty($row['line_area']) ? htmlspecialchars($row['line_area']) : '-'; ?></td>
                                    <td><?= htmlspecialchars($row['lokasi']); ?></td>
                                    <td><?= !empty($row['catatan']) ? htmlspecialchars($row['catatan']) : '-'; ?></td>
                                    <td><?= $statusLampu; ?></td>
                                    <td>
                                        <button type="button" class="table-action-btn btn-barcode" onclick="bukaModalBarcode(<?= $row['id']; ?>, '<?= $safeCode; ?>')">
                                            <i class="fa-solid fa-barcode"></i>
                                        </button>

                                        <button type="button" class="table-action-btn btn-edit" onclick="bukaModalEdit(<?= $row['id']; ?>, '<?= $safeCode; ?>', '<?= $safeDepartemen; ?>', '<?= $safeLokasi; ?>', '<?= $statusLampu; ?>', '<?= $safeCatatan; ?>')">
                                            <i class="fa-solid fa-pencil"></i>
                                        </button>

                                        <button type="button" class="table-action-btn btn-delete" onclick="konfirmasiHapus(<?= $row['id']; ?>)">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                        <?php }
                        } else {
                            echo "<tr><td colspan='8' style='text-align:center;'>Tidak ada data master Lampu Exit tersedia</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>

    <div id="modalTambah" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:1000; overflow-y: auto;">
        <div style="background:#fff; width:420px; margin:40px auto; padding:20px; border-radius:8px; box-sizing: border-box;">
            <h3>Tambah Data Lampu Exit</h3>
            <form action="../proses/proses_tambah_lampu.php" method="POST">
                <div style="margin-bottom:12px;">
                    <label style="font-weight:600;">Kode (Gunakan awalan LE, contoh: LE01):</label><br>
                    <input type="text" name="code" placeholder="LE01" required style="width:100%; padding:8px; box-sizing: border-box; margin-top:4px;">
                </div>
                <div style="margin-bottom:12px;">
                    <label style="font-weight:600;">Departemen (Area Line):</label><br>
                    <select name="line_area" required style="width:100%; padding:8px; box-sizing: border-box; margin-top:4px;">
                        <option value="">-- Pilih Departemen --</option>
                        <?php foreach ($daftar_area as $area): ?>
                            <option value="<?= htmlspecialchars($area); ?>"><?= htmlspecialchars($area); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div style="margin-bottom:12px;">
                    <label style="font-weight:600;">Lokasi:</label><br>
                    <input type="text" name="lokasi" required style="width:100%; padding:8px; box-sizing: border-box; margin-top:4px;">
                </div>
                <div style="margin-bottom:12px;">
                    <label style="font-weight:600;">Lampu Exit:</label>
                    <div class="radio-group">
                        <label><input type="radio" name="indikator_mati_menyala" value="Nyala" checked> Nyala</label>
                        <label><input type="radio" name="indikator_mati_menyala" value="Mati"> Mati</label>
                    </div>
                </div>
                <div style="margin-bottom:15px;">
                    <label style="font-weight:600;">Catatan:</label><br>
                    <textarea name="catatan" style="width:100%; padding:8px; box-sizing: border-box; margin-top:4px;" rows="3"></textarea>
                </div>

                <button type="submit" name="submit" style="background:#004ef5; color:white; border:none; padding:10px 15px; border-radius:4px; cursor:pointer;">Simpan</button>
                <button type="button" onclick="document.getElementById('modalTambah').style.display='none'" style="padding:10px 15px; border:1px solid #ccc; background:#fff; border-radius:4px; cursor:pointer;">Batal</button>
            </form>
        </div>
    </div>

    <div id="modalEdit" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:1000; overflow-y: auto;">
        <div style="background:#fff; width:420px; margin:40px auto; padding:20px; border-radius:8px; box-sizing: border-box;">
            <h3 id="modalEditTitle">Edit Data Master Lampu Exit</h3>
            <form action="../proses/proses_edit_lampu.php" method="POST">
                <input type="hidden" name="id" id="edit_id">
                <div style="margin-bottom:12px;">
                    <label style="font-weight:600;">Kode:</label><br>
                    <input type="text" name="code" id="edit_code" required style="width:100%; padding:8px; box-sizing: border-box; margin-top:4px;">
                </div>
                <div style="margin-bottom:12px;">
                    <label style="font-weight:600;">Departemen (Area Line):</label><br>
                    <select name="line_area" id="edit_line_area" required style="width:100%; padding:8px; box-sizing: border-box; margin-top:4px;">
                        <option value="">-- Pilih Departemen --</option>
                        <?php foreach ($daftar_area as $area): ?>
                            <option value="<?= htmlspecialchars($area); ?>"><?= htmlspecialchars($area); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div style="margin-bottom:12px;">
                    <label style="font-weight:600;">Lokasi:</label><br>
                    <input type="text" name="lokasi" id="edit_lokasi" required style="width:100%; padding:8px; box-sizing: border-box; margin-top:4px;">
                </div>
                <div style="margin-bottom:12px;">
                    <label style="font-weight:600;">Lampu Exit:</label>
                    <div class="radio-group">
                        <label><input type="radio" name="indikator_mati_menyala" id="edit_status_nyala" value="Nyala"> Nyala</label>
                        <label><input type="radio" name="indikator_mati_menyala" id="edit_status_mati" value="Mati"> Mati</label>
                    </div>
                </div>
                <div style="margin-bottom:15px;">
                    <label style="font-weight:600;">Catatan:</label><br>
                    <textarea name="catatan" id="edit_catatan" style="width:100%; padding:8px; box-sizing: border-box; margin-top:4px;" rows="3"></textarea>
                </div>

                <button type="submit" name="submit" style="background:#ffc107; color:black; border:none; padding:10px 15px; border-radius:4px; cursor:pointer; font-weight:600;">Update Data</button>
                <button type="button" onclick="document.getElementById('modalEdit').style.display='none'" style="padding:10px 15px; border:1px solid #ccc; background:#fff; border-radius:4px; cursor:pointer;">Batal</button>
            </form>
        </div>
    </div>

    <div id="modalBarcode" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:1000; overflow-y: auto;">
        <div style="background:#fff; width:650px; margin:50px auto; padding:20px; border-radius:8px; text-align: center; box-sizing: border-box;">
            <h3 style="margin-bottom: 15px;">QR Code Generator (Lampu Exit)</h3>
            <input type="text" id="barcode_code_view" readonly style="width:50%; padding:8px; text-align:center; background:#f1f1f1; border:1px solid #ddd; margin-bottom: 15px; border-radius: 4px;">
            <div style="margin-bottom: 20px; border: 1px solid #ddd; padding: 10px; background: #fafafa; display: inline-block;">
                <img id="img_barcode_preview" src="" alt="Barcode Preview" style="max-width: 100%; height: auto; display: block;">
            </div>
            <br>
            <a id="btn_download_barcode" href="javascript:void(0)" download="barcode_exit.png" style="background:#28a745; color:white; text-decoration:none; padding:10px 20px; border-radius:5px; display:inline-block; margin-right: 10px; font-weight: 600;">
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
                var departemen = <?= json_encode($scan_data['line_area'] ?? ''); ?>;
                var lokasi = <?= json_encode($scan_data['lokasi']); ?>;
                var indikator = <?= json_encode($scan_data['indikator_mati_menyala'] ?? ''); ?>;
                var kondisi = <?= json_encode($scan_data['kondisi']); ?>;
                var catatan = <?= json_encode($scan_data['catatan'] ?? ''); ?>;

                var status_lampu = 'Mati';
                if (indikator && indikator.toLowerCase() === 'nyala') {
                    status_lampu = 'Nyala';
                } else if (kondisi && kondisi.toLowerCase() === 'baik') {
                    status_lampu = 'Nyala';
                }

                bukaModalEdit(id, code, departemen, lokasi, status_lampu, catatan);
                document.getElementById('modalEditTitle').innerHTML = "📋 Isi Data Hasil Scan Lampu Exit";
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

        function bukaModalEdit(id, code, departemen, lokasi, status_lampu, catatan) {
            document.getElementById('modalEdit').style.display = 'block';
            document.getElementById('edit_id').value = id;
            document.getElementById('edit_code').value = code;
            document.getElementById('edit_line_area').value = departemen;
            document.getElementById('edit_lokasi').value = lokasi;

            if (status_lampu.toLowerCase() === 'nyala') {
                document.getElementById('edit_status_nyala').checked = true;
            } else {
                document.getElementById('edit_status_mati').checked = true;
            }

            document.getElementById('edit_catatan').value = catatan;
        }

        function konfirmasiHapus(id) {
            if (confirm("Apakah Anda yakin ingin menghapus data master lampu exit ini?")) {
                window.location.href = "../proses/proses_hapus_lampu.php?id=" + id;
            }
        }

        function bukaModalBarcode(id, code) {
            document.getElementById('modalBarcode').style.display = 'block';
            document.getElementById('barcode_code_view').value = code;

            var srtPath = 'cetak_barcode.php?id=' + id;
            document.getElementById('img_barcode_preview').src = srtPath;

            var btnDownload = document.getElementById('btn_download_barcode');
            btnDownload.href = srtPath;
            btnDownload.download = 'Barcode_' + code + '.png';
        }
    </script>
</body>

</html>