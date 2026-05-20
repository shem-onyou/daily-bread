$files = @('home.php','shop.php','cart.php','orders.php','profile.php','gallery.php','about.php')
foreach ($f in $files) {
    $text = [System.Text.Encoding]::UTF8.GetString([System.IO.File]::ReadAllBytes("C:\xampp\htdocs\BakeryOrderingSystem\PHP\$f"))
    $closeTag = $text.IndexOf('?>')
    $doctype  = $text.IndexOf('<!DOCTYPE')
    if ($closeTag -ge 0 -and $doctype -gt $closeTag) {
        $between = $text.Substring($closeTag + 2, $doctype - $closeTag - 2)
        $bytes   = [System.Text.Encoding]::UTF8.GetBytes($between) -join ','
        Write-Output "$f between ?> and DOCTYPE bytes: [$bytes]"
    }
}
