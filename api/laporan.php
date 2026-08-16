<?php
/**
 * api/laporan.php - Kartu laporan bulanan per jenis inspeksi
 * Param: type = p3k | lampu_exit | lampu_emergency | eyewash
 *        tahun
 */
require_once __DIR__ . '/config.php';
require_login();

$type = api_get('type', 'p3k');
$tahun = (int) api_get('tahun', date('Y'));

$nama_bulan = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

$config = [
    'p3k' => ['table' => 'inspeksi_p3k', 'date' => 'tanggal_inspeksi'],
    'lampu_exit' => ['table' => 'inspeksi_lampu_exit', 'date' => 'tanggal_cek'],
    'lampu_emergency' => ['table' => 'inspeksi_lampu', 'date' => 'tanggal_inspeksi'],
    'eyewash' => ['table' => 'inspeksi_eyewash', 'date' => 'tanggal_inspeksi'],
];
$cfg = $config[$type] ?? $config['p3k'];

$bulan_data = [];
$st = mysqli_prepare($koneksi, "SELECT MONTH({$cfg['date']}) as bln, COUNT(*) as total
                                FROM `{$cfg['table']}`
                                WHERE YEAR({$cfg['date']}) = ?
                                GROUP BY MONTH({$cfg['date']})");
mysqli_stmt_bind_param($st, 'i', $tahun);
mysqli_stmt_execute($st);
$res = mysqli_stmt_get_result($st);
while ($r = mysqli_fetch_assoc($res)) {
    $bulan_data[(int) $r['bln']] = (int) $r['total'];
}

$months = [];
for ($b = 1; $b <= 12; $b++) {
    $total = $bulan_data[$b] ?? 0;
    $months[] = [
        'bulan' => $nama_bulan[$b],
        'total' => $total,
        'has_data' => $total > 0,
    ];
}

api_response([
    'success' => true,
    'type' => $type,
    'tahun' => $tahun,
    'months' => $months,
]);