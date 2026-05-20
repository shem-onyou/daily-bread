$files = @(
    'C:\xampp\htdocs\BakeryOrderingSystem\CSS\home.css',
    'C:\xampp\htdocs\BakeryOrderingSystem\CSS\shop.css',
    'C:\xampp\htdocs\BakeryOrderingSystem\CSS\cart.css',
    'C:\xampp\htdocs\BakeryOrderingSystem\CSS\orders.css',
    'C:\xampp\htdocs\BakeryOrderingSystem\CSS\profile.css',
    'C:\xampp\htdocs\BakeryOrderingSystem\CSS\index.css'
)

foreach ($f in $files) {
    $bytes = [System.IO.File]::ReadAllBytes($f)
    $text = [System.Text.Encoding]::UTF8.GetString($bytes)
    # Add margin-top: 0 to .navbar rule if not already present
    if ($text -match '\.navbar\s*\{' -and $text -notmatch 'margin-top:\s*0') {
        $text = [regex]::Replace($text, '(\.navbar\s*\{)', '$1' + "`r`n    margin-top: 0;")
        [System.IO.File]::WriteAllBytes($f, [System.Text.Encoding]::UTF8.GetBytes($text))
        Write-Output "Fixed: $([System.IO.Path]::GetFileName($f))"
    } else {
        Write-Output "Skipped: $([System.IO.Path]::GetFileName($f))"
    }
}
