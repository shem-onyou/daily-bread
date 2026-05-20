$f = 'C:\xampp\htdocs\BakeryOrderingSystem\PHP\index.php'
$bytes = [System.IO.File]::ReadAllBytes($f)
$text = [System.Text.Encoding]::UTF8.GetString($bytes)

# Remove the blank line + corrupted comment between <body> and <nav
# Replace: <body>\n\n    <!-- corrupted --> \n    <nav
# With:    <body>\n    <nav
$text = [regex]::Replace($text, '<body>\r?\n\r?\n\s*<!--[^>]*-->\r?\n', "<body>`n")

$newBytes = [System.Text.Encoding]::UTF8.GetBytes($text)
[System.IO.File]::WriteAllBytes($f, $newBytes)
Write-Output "Fixed index.php"
