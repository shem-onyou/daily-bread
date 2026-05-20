$files = @(
    'C:\xampp\htdocs\BakeryOrderingSystem\PHP\orders.php',
    'C:\xampp\htdocs\BakeryOrderingSystem\PHP\profile.php',
    'C:\xampp\htdocs\BakeryOrderingSystem\PHP\about.php',
    'C:\xampp\htdocs\BakeryOrderingSystem\PHP\gallery.php'
)

function Replace-Bytes {
    param([byte[]]$source, [byte[]]$find, [byte[]]$replace)
    $result = New-Object System.Collections.Generic.List[byte]
    $i = 0
    while ($i -lt $source.Length) {
        $match = $true
        if ($i + $find.Length -le $source.Length) {
            for ($j = 0; $j -lt $find.Length; $j++) {
                if ($source[$i+$j] -ne $find[$j]) { $match = $false; break }
            }
        } else { $match = $false }
        if ($match) {
            $result.AddRange($replace)
            $i += $find.Length
        } else {
            $result.Add($source[$i])
            $i++
        }
    }
    return $result.ToArray()
}

# Em dash corrupted: C3 A2 E2 80 9C -> E2 80 94 (—)
$badDash  = [byte[]]@(0xC3,0xA2,0xE2,0x80,0x9C)
$goodDash = [byte[]]@(0xE2,0x80,0x94)

foreach ($f in $files) {
    $bytes = [System.IO.File]::ReadAllBytes($f)
    $bytes = Replace-Bytes $bytes $badDash $goodDash
    [System.IO.File]::WriteAllBytes($f, $bytes)
    Write-Output "Fixed: $([System.IO.Path]::GetFileName($f))"
}
