$files = @(
    'C:\xampp\htdocs\BakeryOrderingSystem\PHP\cart.php',
    'C:\xampp\htdocs\BakeryOrderingSystem\PHP\orders.php',
    'C:\xampp\htdocs\BakeryOrderingSystem\PHP\profile.php',
    'C:\xampp\htdocs\BakeryOrderingSystem\PHP\home.php',
    'C:\xampp\htdocs\BakeryOrderingSystem\PHP\shop.php',
    'C:\xampp\htdocs\BakeryOrderingSystem\PHP\about.php',
    'C:\xampp\htdocs\BakeryOrderingSystem\PHP\gallery.php'
)

# Corrupted byte sequences and their correct replacements
# Peso sign: C3 A2 E2 80 9A C2 B1 -> E2 82 B1
$badPeso    = [byte[]]@(0xC3,0xA2,0xE2,0x80,0x9A,0xC2,0xB1)
$goodPeso   = [byte[]]@(0xE2,0x82,0xB1)

# Em dash: C3 A2 E2 80 9C -> E2 80 94
$badDash    = [byte[]]@(0xC3,0xA2,0xE2,0x80,0x9C)
$goodDash   = [byte[]]@(0xE2,0x80,0x94)

# Box drawing dash pair used in comments: C3 A2 E2 80 9C C3 A2 E2 80 9C -> E2 94 80 E2 94 80
$badBox     = [byte[]]@(0xC3,0xA2,0xE2,0x80,0x9C,0xC3,0xA2,0xE2,0x80,0x9C)
$goodBox    = [byte[]]@(0xE2,0x94,0x80,0xE2,0x94,0x80)

# Dasmariñas: C3 83 C2 B1 -> C3 B1
$badNtilde  = [byte[]]@(0x41,0xC3,0xB1)   # 'A' + corrupted ñ
$goodNtilde = [byte[]]@(0xC3,0xB1)         # correct ñ

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

foreach ($f in $files) {
    $bytes = [System.IO.File]::ReadAllBytes($f)
    $bytes = Replace-Bytes $bytes $badPeso $goodPeso
    $bytes = Replace-Bytes $bytes $badBox $goodBox
    $bytes = Replace-Bytes $bytes $badDash $goodDash
    $bytes = Replace-Bytes $bytes $badNtilde $goodNtilde
    [System.IO.File]::WriteAllBytes($f, $bytes)
    Write-Output "Fixed: $([System.IO.Path]::GetFileName($f))"
}
