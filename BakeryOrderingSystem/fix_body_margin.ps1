$files = @(
    'C:\xampp\htdocs\BakeryOrderingSystem\CSS\shop.css',
    'C:\xampp\htdocs\BakeryOrderingSystem\CSS\cart.css',
    'C:\xampp\htdocs\BakeryOrderingSystem\CSS\orders.css',
    'C:\xampp\htdocs\BakeryOrderingSystem\CSS\profile.css'
)

foreach ($f in $files) {
    $bytes = [System.IO.File]::ReadAllBytes($f)
    $text = [System.Text.Encoding]::UTF8.GetString($bytes)
    # Add margin:0;padding:0 to body rule
    $text = [regex]::Replace($text,
        '(body\s*\{)',
        '$1' + "`n    margin: 0;`n    padding: 0;")
    [System.IO.File]::WriteAllBytes($f, [System.Text.Encoding]::UTF8.GetBytes($text))
    Write-Output "Fixed: $([System.IO.Path]::GetFileName($f))"
}
