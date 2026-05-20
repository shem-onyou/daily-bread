<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
    header("Location: login.php");
    exit;
}
require 'db.php';

$result   = $conn->query("SELECT id, name, stock, price, image FROM products ORDER BY name ASC");
$products = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shop - DailyBread</title>
    <link rel="stylesheet" href="/BakeryOrderingSystem/CSS/shop.css">
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
            <a href="home.php">Home</a>
            <a href="shop.php" class="active-link">Shop</a>
            <a href="about.php">About</a>
            <a href="gallery.php">Gallery</a>
        </div>
    </nav>

    <main class="page-main">
        <section class="featured-products" id="products">
            <h2>Featured Products</h2>
            <div class="products-grid">
                <?php if (empty($products)): ?>
                    <p style="padding:20px;">No products available yet.</p>
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
                            <div class="product-action">
                                <div class="product-price">₱<?= number_format($p['price'], 2) ?></div>
                                <button class="btn btn-secondary btn-add-cart">
                                    <i class='bx bx-cart-add'></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>

        <footer class="footer">
            <div class="footer-content">
                <div class="footer-section main">
                    <h1>The DailyBread</h1>
                    <p>"Fresh from the oven, straight to your heart"<br>Get your appetizing pastries now.</p>
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
    <script src="/BakeryOrderingSystem/JS/shop.js"></script>
</body>
</html>
