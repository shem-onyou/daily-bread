$files = @('cart.php','orders.php','profile.php','home.php','shop.php','about.php','gallery.php')
foreach ($f in $files) {
    $bytes = [System.IO.File]::ReadAllBytes("C:\xampp\htdocs\BakeryOrderingSystem\PHP\$f")
    $text = [System.Text.Encoding]::UTF8.GetString($bytes)
    $issues = @()
    if ($text.Contains('DasmariA')) { $issues += 'DasmariA' }
    # Check for double-encoded sequences by looking for specific byte patterns
    $hasC3A2 = $false
    for ($i = 0; $i -lt $bytes.Length - 1; $i++) {
        if ($bytes[$i] -eq 0xC3 -and $bytes[$i+1] -eq 0xA2) { $hasC3A2 = $true; break }
    }
    if ($hasC3A2) { $issues += 'double-encoded' }
    if ($issues.Count -eq 0) { Write-Output "OK: $f" }
    else { Write-Output "ISSUES in ${f}: $($issues -join ', ')" }
}
