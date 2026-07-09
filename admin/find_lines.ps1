$files = @('data_inspeksi_p3k.php','lampu_exit.php','laporan_eyewash.php','laporan_lampu_emergency.php','laporan_lampu_exit.php','laporan_p3k.php','master_lampu.php','scan.php')
foreach ($f in $files) {
    $path = "c:\xampp\htdocs\safety_facility\admin\$f"
    if (Test-Path $path) {
        $content = Get-Content $path
        for ($i = 0; $i -lt $content.Count; $i++) {
            if ($content[$i] -match 'function toggleSidebar') {
                Write-Output "${f}: line $($i+1)"
            }
        }
    } else {
        Write-Output "${f}: NOT FOUND"
    }
}
