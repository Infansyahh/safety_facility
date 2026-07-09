<?php
session_start();
include '../koneksi.php';

global $koneksi;

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: ../login.php");
    exit();
}

/* Auto-add kolom jenis jika belum ada */
mysqli_query($koneksi, "ALTER TABLE area_line ADD COLUMN IF NOT EXISTS jenis VARCHAR(30) NOT NULL DEFAULT 'lampu_emergency'");

/* ---- TAMBAH ---- */
if (isset($_POST['tambah_line'])) {
    $nama  = trim($_POST['nama_line']);
    $jenis = 'lampu_emergency';
    if ($nama !== '') {
        $stmt = mysqli_prepare($koneksi, "INSERT INTO area_line (nama_line, jenis) VALUES (?, ?)");
        mysqli_stmt_bind_param($stmt, "ss", $nama, $jenis);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }
    header("Location: area_line_lampu_emergency.php?msg=tambah");
    exit();
}

/* ---- EDIT ---- */
if (isset($_POST['edit_line'])) {
    $id   = (int)$_POST['id_line'];
    $nama = trim($_POST['nama_line']);
    $stmt = mysqli_prepare($koneksi, "UPDATE area_line SET nama_line = ? WHERE id_line = ? AND jenis = 'lampu_emergency'");
    mysqli_stmt_bind_param($stmt, "si", $nama, $id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    header("Location: area_line_lampu_emergency.php?msg=edit");
    exit();
}

/* ---- HAPUS ---- */
if (isset($_GET['hapus'])) {
    $id  = (int)$_GET['hapus'];
    $cek = mysqli_query($koneksi,
        "SELECT COUNT(*) as total FROM `master_lampu`
         WHERE `line_area` = (SELECT nama_line FROM area_line WHERE id_line = $id)");
    $row_cek = mysqli_fetch_assoc($cek);
    if ($row_cek['total'] > 0) {
        header("Location: area_line_lampu_emergency.php?msg=gagal_hapus");
    } else {
        $stmt = mysqli_prepare($koneksi, "DELETE FROM area_line WHERE id_line = ? AND jenis = 'lampu_emergency'");
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        header("Location: area_line_lampu_emergency.php?msg=hapus");
    }
    exit();
}

/* ---- AMBIL DATA ---- */
$cari = isset($_GET['cari']) ? trim($_GET['cari']) : '';

if ($cari !== '') {
    $like = "%$cari%";
    $stmt = mysqli_prepare($koneksi,
        "SELECT a.*, COUNT(m.id) as total_item
         FROM area_line a
         LEFT JOIN `master_lampu` m ON m.`line_area` = a.nama_line
         WHERE a.jenis = 'lampu_emergency' AND a.nama_line LIKE ?
         GROUP BY a.id_line ORDER BY a.nama_line ASC");
    mysqli_stmt_bind_param($stmt, "s", $like);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
} else {
    $result = mysqli_query($koneksi,
        "SELECT a.*, COUNT(m.id) as total_item
         FROM area_line a
         LEFT JOIN `master_lampu` m ON m.`line_area` = a.nama_line
         WHERE a.jenis = 'lampu_emergency'
         GROUP BY a.id_line ORDER BY a.nama_line ASC");
}

$total_line = mysqli_num_rows($result);

$dh = ['Sunday'=>'Minggu','Monday'=>'Senin','Tuesday'=>'Selasa','Wednesday'=>'Rabu',
       'Thursday'=>'Kamis','Friday'=>'Jumat','Saturday'=>'Sabtu'];
$db = ['January'=>'Januari','February'=>'Februari','March'=>'Maret','April'=>'April',
       'May'=>'Mei','June'=>'Juni','July'=>'Juli','August'=>'Agustus',
       'September'=>'September','October'=>'Oktober','November'=>'November','December'=>'Desember'];
$tanggal_format = $dh[date('l')] . ", " . date('d') . " " . $db[date('F')] . " " . date('Y');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Area Line Lampu Emergency</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    <style>
        .page-head {
            display:flex; align-items:center; gap:16px;
            background:#fff; border-radius:14px; padding:20px 26px;
            margin-bottom:24px; box-shadow:0 2px 8px rgba(0,0,0,.05);
            border-left:6px solid #f59e0b;
        }
        .ph-icon {
            width:52px; height:52px; border-radius:11px;
            background:#fef3c7; color:#f59e0b;
            display:flex; align-items:center; justify-content:center;
            font-size:22px; flex-shrink:0;
        }
        .ph-info h2 { font-size:18px; font-weight:700; color:#1e293b; }
        .ph-info p  { font-size:13px; color:#64748b; margin-top:3px; }

        .stat-mini {
            background:#fff; border-radius:10px; padding:16px 22px;
            margin-bottom:22px; display:inline-flex; align-items:center;
            gap:16px; box-shadow:0 2px 6px rgba(0,0,0,.05);
            border-left:5px solid #f59e0b;
        }
        .stat-mini i { font-size:24px; color:#f59e0b; }
        .stat-mini h3 { font-size:12px; color:#64748b; font-weight:500; }
        .stat-mini p  { font-size:24px; font-weight:700; color:#1e293b; line-height:1.1; }

        .page-toolbar {
            display:flex; flex-wrap:wrap; gap:12px;
            align-items:center; justify-content:space-between; margin-bottom:18px;
        }
        .search-bar { display:flex; gap:8px; align-items:center; }
        .search-bar input {
            padding:9px 14px; border:1px solid #d8dee9; border-radius:6px;
            font-size:13px; font-family:inherit; outline:none; width:230px;
        }

        .btn {
            display:inline-flex; align-items:center; gap:6px;
            padding:9px 16px; border-radius:6px; border:none;
            font-size:13px; font-weight:500; cursor:pointer;
            text-decoration:none; white-space:nowrap; font-family:inherit;
        }
        .btn-primary { background:#f59e0b; color:#fff; }
        .btn-primary:hover { opacity:.88; }
        .btn-outline { background:#fff; color:#000766; border:1px solid #000766; }
        .btn-outline:hover { background:#f0f3ff; }

        .table-card {
            background:#fff; border-radius:12px;
            box-shadow:0 2px 8px rgba(0,0,0,.06); overflow-x:auto;
        }
        table.area-table { width:100%; border-collapse:collapse; }
        table.area-table thead tr { background:#1e293b; }
        table.area-table th {
            text-align:left; font-size:11.5px; text-transform:uppercase;
            letter-spacing:.5px; color:#94a3b8; padding:13px 18px;
            font-weight:600; white-space:nowrap;
        }
        table.area-table td {
            padding:13px 18px; font-size:13.5px; color:#334155;
            border-bottom:1px solid #f1f5f9; vertical-align:middle;
        }
        table.area-table tr:last-child td { border-bottom:none; }
        table.area-table tbody tr:hover td { background:#f8fafc; }
        table.area-table tbody tr:nth-child(even) td { background:#fafbfd; }

        .badge-item {
            background:#fef3c7; color:#f59e0b;
            padding:4px 12px; border-radius:20px;
            font-size:12px; font-weight:600;
        }
        .action-icons a, .action-icons button {
            display:inline-flex; align-items:center; justify-content:center;
            width:32px; height:32px; border-radius:6px; margin-right:4px;
            color:#fff; text-decoration:none; font-size:13px;
            border:none; cursor:pointer;
        }
        .ic-edit   { background:#2b75cc; }
        .ic-delete { background:#e15554; }

        .empty-row td { text-align:center; padding:48px; color:#94a3b8; font-size:14px; }

        .alert-success { background:#ddf3e8; color:#1e9e63; padding:12px 18px; border-radius:8px; margin-bottom:18px; font-size:13.5px; }
        .alert-danger  { background:#fbe2e1; color:#d33a39; padding:12px 18px; border-radius:8px; margin-bottom:18px; font-size:13.5px; }

        .modal-overlay {
            display:none; position:fixed; inset:0;
            background:rgba(15,23,42,.5); z-index:999;
            align-items:center; justify-content:center;
        }
        .modal-overlay.show { display:flex; }
        .modal-box { background:#fff; width:100%; max-width:440px; border-radius:12px; padding:28px; }
        .modal-box h3 { margin-bottom:18px; color:#f59e0b; font-size:16px; }
        .form-group { margin-bottom:14px; }
        .form-group label { display:block; font-size:13px; font-weight:500; margin-bottom:6px; color:#334155; }
        .form-group input {
            width:100%; padding:10px 13px; border:1px solid #d8dee9; border-radius:6px;
            font-size:13.5px; font-family:inherit; outline:none;
        }
        .form-group input:focus { border-color:#f59e0b; }
        .input-hint { font-size:11.5px; color:#94a3b8; margin-top:4px; }
        .modal-footer { display:flex; justify-content:flex-end; gap:10px; margin-top:18px; }
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
        <li class="has-submenu active">
            <a href="javascript:void(0)" onclick="toggleSubmenu(this)">
                <i class="fa-solid fa-location-dot"></i><span>Area Line</span>
                <i class="fa-solid fa-chevron-down submenu-icon"></i>
            </a>
            <ul class="submenu" style="display:block;">
                <li class="active"><a href="area_line_lampu_emergency.php">Lampu Emergency</a></li>
                <li class=""><a href="area_line_lampu_exit.php">Lampu Exit</a></li>
                <li class=""><a href="area_line_p3k.php">Kotak P3K</a></li>
                <li class=""><a href="area_line_eyewash.php">Eye Wash</a></li>
            </ul>
        </li>
        <li><a href="aktivitas.php"><i class="fa-solid fa-clock-rotate-left"></i> <span>Aktivitas Pengguna</span></a></li>
        <li><a href="kalender.php"><i class="fa-solid fa-calendar-days"></i> <span>Kalender Inspeksi</span></a></li>
        <li><a href="agenda.php"><i class="fa-solid fa-calendar-check"></i> <span>Agenda Inspeksi</span></a></li>
        <li class="has-submenu">
            <a href="javascript:void(0)" onclick="toggleSubmenu(this)">
                <i class="fa-solid fa-file-invoice"></i><span>Laporan Inspeksi</span>
                <i class="fa-solid fa-chevron-down submenu-icon"></i>
            </a>
            <ul class="submenu" style="display:none;">
                <li><a href="laporan_lampu_emergency.php">Lampu Emergency</a></li>
                <li><a href="laporan_lampu_exit.php">Lampu Exit</a></li>
                <li><a href="laporan_p3k.php">Kotak P3K</a></li>
                <li><a href="laporan_eyewash.php">Eye Wash</a></li>
            </ul>
        </li>
        <li style="margin-top:20px;"><a href="../logout.php"><i class="fa-solid fa-right-from-bracket"></i> <span>Log out</span></a></li>
    </ul>
</aside>

<main class="main-content">
    <header class="topbar">
        <div class="topbar-left">
            <button class="toggle-sidebar-btn" onclick="toggleSidebar()"><i class="fa-solid fa-bars"></i></button>
            <div class="topbar-date"><?= $tanggal_format; ?></div>
        </div>
        <div class="topbar-right">
            <div class="user-profile">
                <span>Hi, <strong><?= htmlspecialchars($_SESSION['nama_lengkap'] ?? 'User'); ?></strong></span>
                <div class="user-avatar">
                    <img src="https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?q=80&w=100" alt="Avatar">
                </div>
            </div>
        </div>
    </header>

    <section class="content-body">

        <?php if (isset($_GET['msg'])): ?>
            <?php if ($_GET['msg'] === 'gagal_hapus'): ?>
                <div class="alert-danger"><i class="fa-solid fa-circle-xmark"></i> Area Line tidak bisa dihapus karena masih digunakan oleh data master.</div>
            <?php else: ?>
                <div class="alert-success"><i class="fa-solid fa-circle-check"></i>
                    <?php $p = ['tambah'=>'Area Line baru berhasil ditambahkan.','edit'=>'Area Line berhasil diperbarui.','hapus'=>'Area Line berhasil dihapus.']; echo $p[$_GET['msg']] ?? 'Berhasil.'; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <div class="page-head">
            <div class="ph-icon"><i class="fa-solid fa-bolt"></i></div>
            <div class="ph-info">
                <h2>Area Line â€” Lampu Emergency</h2>
                <p>Kelola daftar area / line untuk inspeksi Lampu Emergency</p>
            </div>
        </div>

        <div class="stat-mini">
            <i class="fa-solid fa-location-dot"></i>
            <div><h3>Total Area Terdaftar</h3><p><?= $total_line; ?></p></div>
        </div>

        <div class="page-toolbar">
            <form method="GET" class="search-bar">
                <input type="text" name="cari" placeholder="Cari nama area..." value="<?= htmlspecialchars($cari); ?>">
                <button type="submit" class="btn btn-outline"><i class="fa-solid fa-magnifying-glass"></i> Cari</button>
                <?php if ($cari !== ''): ?>
                    <a href="area_line_lampu_emergency.php" class="btn btn-outline"><i class="fa-solid fa-rotate-left"></i> Reset</a>
                <?php endif; ?>
            </form>
            <button class="btn btn-primary" onclick="openModal('modalTambah')">
                <i class="fa-solid fa-plus"></i> Tambah Area Line
            </button>
        </div>

        <div class="table-card">
            <table class="area-table">
                <thead>
                    <tr>
                        <th style="width:52px;">No</th>
                        <th>Nama Area / Line</th>
                        <th>Item Terdaftar</th>
                        <th>Dibuat Pada</th>
                        <th style="width:110px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (mysqli_num_rows($result) > 0): ?>
                    <?php $no = 1; while ($row = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td style="text-align:center;font-weight:600;color:#94a3b8;"><?= $no++; ?></td>
                            <td><strong><?= htmlspecialchars($row['nama_line']); ?></strong></td>
                            <td>
                                <span class="badge-item">
                                    <i class="fa-solid fa-bolt"></i>
                                    <?= $row['total_item']; ?> item
                                </span>
                            </td>
                            <td><?= isset($row['created_at']) ? date('d-m-Y H:i', strtotime($row['created_at'])) : '-'; ?></td>
                            <td class="action-icons">
                                <a href="javascript:void(0)" class="ic-edit" title="Edit"
                                   onclick='bukaEdit(<?= json_encode(["id_line"=>$row["id_line"],"nama_line"=>$row["nama_line"]]); ?>)'>
                                    <i class="fa-solid fa-pen"></i>
                                </a>
                                <a href="area_line_lampu_emergency.php?hapus=<?= $row['id_line']; ?>" class="ic-delete" title="Hapus"
                                   onclick="return confirm('Hapus area ini? Pastikan tidak ada data yang masih menggunakannya.')">
                                    <i class="fa-solid fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr class="empty-row">
                        <td colspan="5">
                            <i class="fa-solid fa-folder-open" style="font-size:28px;margin-bottom:8px;display:block;"></i>
                            Belum ada area line untuk Lampu Emergency.
                        </td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
</main>

<div class="modal-overlay" id="modalTambah">
    <div class="modal-box">
        <h3><i class="fa-solid fa-bolt"></i> Tambah Area Line â€” Lampu Emergency</h3>
        <form method="POST">
            <div class="form-group">
                <label>Nama Area / Line <span style="color:#e15554;">*</span></label>
                <input type="text" name="nama_line" placeholder="Contoh: FA, Line A, Gudang B..." required autofocus>
                <p class="input-hint">Area ini khusus untuk Lampu Emergency.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeModal('modalTambah')">Batal</button>
                <button type="submit" name="tambah_line" class="btn btn-primary">
                    <i class="fa-solid fa-floppy-disk"></i> Simpan
                </button>
            </div>
        </form>
    </div>
</div>

<div class="modal-overlay" id="modalEdit">
    <div class="modal-box">
        <h3><i class="fa-solid fa-pen"></i> Edit Area Line</h3>
        <form method="POST">
            <input type="hidden" name="id_line" id="edit_id_line">
            <div class="form-group">
                <label>Nama Area / Line <span style="color:#e15554;">*</span></label>
                <input type="text" name="nama_line" id="edit_nama_line" required>
                <p class="input-hint">Perubahan nama akan otomatis berlaku di data master Lampu Emergency.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeModal('modalEdit')">Batal</button>
                <button type="submit" name="edit_line" class="btn btn-primary">
                    <i class="fa-solid fa-floppy-disk"></i> Simpan Perubahan
                </button>
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
    function toggleSubmenu(el) {
        const sub = el.nextElementSibling, icon = el.querySelector('.submenu-icon');
        if (sub.style.display === 'none' || sub.style.display === '') {
            sub.style.display = 'block'; icon.style.transform = 'rotate(180deg)';
        } else {
            sub.style.display = 'none'; icon.style.transform = 'rotate(0deg)';
        }
    }
    function openModal(id)  { document.getElementById(id).classList.add('show'); }
    function closeModal(id) { document.getElementById(id).classList.remove('show'); }
    function bukaEdit(data) {
        document.getElementById('edit_id_line').value  = data.id_line;
        document.getElementById('edit_nama_line').value = data.nama_line;
        openModal('modalEdit');
    }
    document.querySelectorAll('.modal-overlay').forEach(o => {
        o.addEventListener('click', e => { if (e.target === o) o.classList.remove('show'); });
    });
</script>
</body>
</html>
