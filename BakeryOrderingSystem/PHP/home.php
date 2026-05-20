<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
    header("Location: login.php");
    exit;
}
require 'db.php';

// Handle contact form POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    $data    = json_decode(file_get_contents('php://input'), true);
    $name    = trim($data['name']    ?? '');
    $email   = trim($data['email']   ?? '');
    $message = trim($data['message'] ?? '');
    $userId  = $_SESSION['user_id'];
    if ($name && $email && $message) {
        $stmt = $conn->prepare("INSERT INTO messages (user_id, name, email, message) VALUES (?,?,?,?)");
        $stmt->bind_param("isss", $userId, $name, $email, $message);
        echo $stmt->execute() ? json_encode(['success' => true]) : json_encode(['error' => $stmt->error]);
        $stmt->close();
    } else {
        echo json_encode(['error' => 'All fields are required']);
    }
    exit;
}

// Fetch featured products (first 6)
$result   = $conn->query("SELECT id, name, stock, price, image FROM products ORDER BY name ASC LIMIT 6");
$products = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DailyBread</title>
    <link rel="stylesheet" href="/BakeryOrderingSystem/CSS/home.css">
    <link rel="stylesheet" href="/BakeryOrderingSystem/CSS/animations.css">
    <link rel="stylesheet" href="/BakeryOrderingSystem/CSS/navbar.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <button class="nav-hamburger" id="navHamburger" aria-label="Toggle menu" aria-expanded="false">
                <i class='bx bx-menu'></i>
            </button>
            <div class="nav-logo"><span>The DailyBread</span></div>
            <div class="nav-menu">
                <a href="home.php" class="nav-link home">Home</a>
                <a href="shop.php" class="nav-link shop">Shop</a>
                <a href="about.php" class="nav-link about">About</a>
                <a href="gallery.php" class="nav-link gallery">Gallery</a>
            </div>
            <div class="nav-icons">
                <button class="nav-btn orders-btn" onclick="window.location.href='orders.php'">
                    <i class='bx bx-shopping-bag'></i>
                </button>
                <button class="nav-btn cart-btn" onclick="window.location.href='cart.php'">
                    <i class='bx bx-cart'></i>
                    <span class="cart-count" id="cartBadge"></span>
                </button>
                <?php include 'nav_profile_btn.php'; ?>
            </div>
        </div>
        <div class="nav-dropdown" id="navDropdown">
            <a href="home.php" class="active-link">Home</a>
            <a href="shop.php">Shop</a>
            <a href="about.php">About</a>
            <a href="gallery.php">Gallery</a>
        </div>
    </nav>

    <!-- Hero Section -->
    <main class="page-main">
    <section class="hero" id="home">
        <div class="hero-content">
            <span class="hero-eyebrow">Freshly Baked Daily</span>
            <h1>A Taste Like<br><span>Home</span></h1>
            <p>Handcrafted pastries and breads delivered fresh to your door. Made with love, served with heart.</p>
            <div class="hero-cta">
                <a href="shop.php" class="btn btn-primary btn-lg">Shop Now</a>
                <a href="about.php" class="btn btn-outline btn-lg">Learn More</a>
            </div>
        </div>
        <div class="hero-image">
            <div class="hero-img-wrap">
                <img src="/BakeryOrderingSystem/Image/Hero-bg.png" alt="DailyBread">
            </div>
        </div>
    </section>

    <!-- Best Seller Section -->
    <section class="best-seller">
        <h2>Our Best Seller</h2>
        <div class="best-seller-grid">
            <div class="best-seller-card">
                <div class="best-seller-image">
                    <img src="/BakeryOrderingSystem/Image/Pandesal.jpeg" alt="Pandesal">
                </div>
                <h3>Pandesal</h3>
                <p>Pandesal, a staple bread roll in the Philippines, is a popular yeast-raised bread that is most commonly eaten for breakfast.</p>
            </div>
            <div class="best-seller-card">
                <div class="best-seller-image">
                    <img src="/BakeryOrderingSystem/Image/Crinkles.jpg" alt="Crinkles">
                </div>
                <h3>Crinkles</h3>
                <p>Crinkles is a beloved Filipino sweet treat, known for their soft and fudgy texture inside with a slightly crisp outer layer.</p>
            </div>
            <div class="best-seller-card">
                <div class="best-seller-image">
                    <img src="/BakeryOrderingSystem/Image/Cinnamon.jpg" alt="Cinnamon Bread">
                </div>
                <h3>Cinnamon</h3>
                <p>Cinnamon bread is a soft, fluffy loaf infused with the warm spice of cinnamon, often topped with sugar or glaze.</p>
            </div>
        </div>
    </section>

    <!-- About Us -->
    <section class="about-us" id="about">
        <h2>About Us</h2>
        <p>DailyBread is a local bakery aiming to make homemade bread and delightful treats accessible to everyone. <br>We specialize in traditional Filipino pastries and breads made with the finest ingredients.</p>
        <a href="about.php" class="btn btn-primary">Learn More</a>
    </section>

    <!-- Featured Products -->
    <section class="featured-products" id="products">
        <h2>Featured Products</h2>
        <div class="products-grid">
            <?php if (empty($products)): ?>
                <p style="padding:20px;grid-column:1/-1;">No products available yet.</p>
            <?php else: ?>
                <?php foreach ($products as $p): ?>
                <div class="product-card"
                    data-id="<?= htmlspecialchars($p['id']) ?>"
                    data-name="<?= htmlspecialchars($p['name']) ?>"
                    data-price="<?= htmlspecialchars($p['price']) ?>"
                    data-image="<?= htmlspecialchars($p['image'] ?? '') ?>">
                    <div class="product-image">
                        <div class="placeholder">
                            <?php if (!empty($p['image'])): ?>
                                <img src="<?= htmlspecialchars($p['image']) ?>" alt="<?= htmlspecialchars($p['name']) ?>">
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="product-info">
                        <?php $stock = (int)$p['stock']; ?>
                        <div class="product-name-row">
                            <h3><?= htmlspecialchars($p['name']) ?></h3>
                            <span class="product-stock <?= $stock === 0 ? 'out-of-stock' : ($stock <= 10 ? 'low-stock' : '') ?>">
                                <?= $stock === 0 ? 'Out of stock' : ($stock <= 10 ? "Only {$stock} left" : "{$stock} in stock") ?>
                            </span>
                        </div>
                        <div class="product-price">₱<?= number_format($p['price'], 2) ?></div>
                        <div class="product-action">
                            <div class="product-qty">
                                <button class="qty-btn">-</button>
                                <input type="number" value="1" min="1" class="qty-input">
                                <button class="qty-btn">+</button>
                            </div>
                            <button class="btn btn-secondary btn-add-cart">Add to Cart</button>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </section>

    <!-- Message Section -->
    <section class="message" id="contact">
        <div class="message-content">
            <h2>Connect With Us</h2>
            <p>Get in touch with us for any inquiries or feedback!</p>
            <form class="message-form">
                <input type="text" name="name" placeholder="Your Name" required>
                <input type="email" name="email" placeholder="Your Email" required>
                <textarea name="message" placeholder="Your Message" rows="5" required></textarea>
                <button type="submit" class="btn btn-primary">Send</button>
            </form>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="footer-content">
            <div class="footer-section main">
                <h1>The DailyBread</h1>
                <p>"Fresh from the oven, straight to your heart" <br>Get your appetizing pastries now.</p>
                <div class="social-links">
                    <a href="https://www.facebook.com/share/1CiKg16Baj/"><i class='bx bxl-facebook'></i></a>
                    <a href="https://www.instagram.com/dailybread.2k25?igsh=dG1oZTEwaWNmamdk"><i class='bx bxl-instagram'></i></a>
                    <a href="#"><i class='bx bxl-twitter'></i></a>
                    <a href="#"><i class='bx bxl-pinterest'></i></a>
                </div>
            </div>
            <div class="footer-section">
                <h3>Menu</h3>
                <ul>
                    <li><a href="home.php">Home</a></li>
                    <li><a href="about.php">About Us</a></li>
                    <li><a href="shop.php">Products</a></li>
                    <li><a href="gallery.php">Gallery</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h3>Contact Us</h3>
                <ul>
                    <li>dailybread2k25@gmail.com</li>
                    <li>0929-776-3880</li>
                </ul>
            </div>
            <div class="footer-section">
                <h3>Location</h3>
                <p>Gov. Drive, San Juan, Dasmarinas Cavite, 4114</p>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2026 DailyBread. All rights reserved.</p>
        </div>
    </footer>
    </main>
    <script src="/BakeryOrderingSystem/JS/cart-badge.js"></script>
    <script src="/BakeryOrderingSystem/JS/navbar.js"></script>
    <script src="/BakeryOrderingSystem/JS/home.js"></script>
</body>
</html>
