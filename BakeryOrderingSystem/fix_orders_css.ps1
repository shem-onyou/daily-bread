$f = 'C:\xampp\htdocs\BakeryOrderingSystem\CSS\orders.css'
$bytes = [System.IO.File]::ReadAllBytes($f)
$text = [System.Text.Encoding]::UTF8.GetString($bytes)
$fixed = $text.TrimStart()
[System.IO.File]::WriteAllBytes($f, [System.Text.Encoding]::UTF8.GetBytes($fixed))
Write-Output "Fixed: orders.css"
