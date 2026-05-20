$f = 'C:\xampp\htdocs\BakeryOrderingSystem\CSS\home.css'
$bytes = [System.IO.File]::ReadAllBytes($f)
$text = [System.Text.Encoding]::UTF8.GetString($bytes)

# Remove the second @import line for hero.css
$text = $text -replace "@import url\('/BakeryOrderingSystem/CSS/hero\.css'\);\r?\n", ''

# Insert hero styles after the comment placeholder
$heroStyles = @'

/* -- Hero Section -- */
.hero {
    max-width: 1200px;
    margin: 0 auto;
    padding: 5rem 2rem 4rem;
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 3rem;
    align-items: center;
}

.hero-eyebrow {
    display: inline-block;
    background-color: #f0e8e3;
    color: #96715e;
    font-size: 0.82rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    padding: 0.3rem 0.9rem;
    border-radius: 20px;
    margin-bottom: 1.25rem;
}

.hero-content h1 {
    font-size: 3.5rem;
    color: #553423;
    line-height: 1.15;
    margin-bottom: 1.25rem;
}

.hero-content h1 span {
    font-family: 'Dancing Script', cursive;
    color: #96715e;
    font-size: 4rem;
}

.hero-content p {
    font-size: 1.1rem;
    color: #666;
    margin-bottom: 2rem;
    max-width: 460px;
}

.hero-cta { display: flex; gap: 1rem; flex-wrap: wrap; }

.hero-image { position: relative; display: flex; justify-content: center; }

.hero-img-wrap {
    width: 100%;
    max-width: 460px;
    aspect-ratio: 4 / 3;
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 12px 40px rgba(85,52,35,0.18);
}

.hero-img-wrap img { width: 100%; height: 100%; object-fit: cover; }

.hero-badge {
    position: absolute;
    top: -16px;
    right: 16px;
    width: 64px;
    height: 64px;
    background: linear-gradient(135deg, #96715e, #553423);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2rem;
    color: white;
    box-shadow: 0 4px 12px rgba(0,0,0,0.2);
    z-index: 1;
}

'@

$text = $text -replace '(/\* hero styles live in hero\.css \*/)', ($heroStyles)

[System.IO.File]::WriteAllBytes($f, [System.Text.Encoding]::UTF8.GetBytes($text))
Write-Output "Done"
