<?php
session_start();

// Redirect logged-in users straight to their module
if (isset($_SESSION['user_id'])) {
    header("Location: " . ($_SESSION['role'] === 'admin' ? 'admin-dashboard.php' : 'home.php'));
    exit;
}

// Fetch a few products for the preview (no auth needed - public landing)
require 'db.php';
$result   = $conn->query("SELECT id, name, price, image FROM products ORDER BY name ASC LIMIT 6");
$products = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>The DailyBread &ndash; Fresh From the Oven</title>
    <link rel="stylesheet" href="/BakeryOrderingSystem/CSS/index.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-logo">
                
                <span>The DailyBread</span>
            </div>
            <div class="nav-menu">
                <a href="#hero"     class="nav-link">Home</a>
                <a href="#products" class="nav-link">Shop</a>
                <a href="#about"    class="nav-link">About</a>
                <a href="#gallery"  class="nav-link">Gallery</a>
            </div>
            <div class="nav-auth">
                <a href="login.php"  class="btn btn-outline">Login</a>
                <a href="signup.php" class="btn btn-primary">Sign Up</a>
            </div>
        </div>
    </nav>

    <main>

        <!-- Hero -->
        <section class="hero" id="hero">
            <div class="hero-content">
                <span class="hero-eyebrow">Freshly Baked Daily</span>
                <h1>A Taste Like<br><span>Home</span></h1>
                <p>Handcrafted Filipino pastries and breads delivered fresh to your door. Made with love, served with heart.</p>
                <div class="hero-cta">
                    <a href="signup.php" class="btn btn-primary btn-lg">Get Started &ndash; It&#39;s Free</a>
                    <a href="login.php"  class="btn btn-outline btn-lg">Login to Order</a>
                </div>
            </div>
            <div class="hero-image">
                <div class="hero-img-wrap">
                    <img src="/BakeryOrderingSystem/Image/Hero-bg.png" alt="DailyBread">
                </div>
            </div>
        </section>

        <!-- Stats -->
        <section class="stats-bar">
            <div class="stats-inner">
                <div class="stat-item">
                    <span class="stat-num">2+</span>
                    <span class="stat-lbl">Years Baking</span>
                </div>
                <div class="stat-divider"></div>
                <div class="stat-item">
                    <span class="stat-num">15+</span>
                    <span class="stat-lbl">Products</span>
                </div>
                <div class="stat-divider"></div>
                <div class="stat-item">
                    <span class="stat-num">50+</span>
                    <span class="stat-lbl">Happy Customers</span>
                </div>
                <div class="stat-divider"></div>
                <div class="stat-item">
                    <span class="stat-num">100%</span>
                    <span class="stat-lbl">Homemade</span>
                </div>
            </div>
        </section>

        <!-- Best Sellers -->
        <section class="best-sellers">
            <div class="section-inner">
                <p class="section-eyebrow">Fan Favourites</p>
                <h2>Our Best Sellers</h2>
                <div class="bs-grid">
                    <div class="bs-card">
                        <div class="bs-img"><img src="/BakeryOrderingSystem/Image/Pandesal.jpeg" alt="Pandesal"></div>
                        <h3>Pandesal</h3>
                        <p>Soft, fluffy Filipino bread rolls &ndash; best enjoyed warm straight from the oven.</p>
                    </div>
                    <div class="bs-card">
                        <div class="bs-img"><img src="/BakeryOrderingSystem/Image/Crinkles.jpg" alt="Crinkles"></div>
                        <h3>Crinkles</h3>
                        <p>Fudgy chocolate cookies with a crisp powdered-sugar crust. Impossible to eat just one.</p>
                    </div>
                    <div class="bs-card">
                        <div class="bs-img"><img src="/BakeryOrderingSystem/Image/Cinnamon.jpg" alt="Cinnamon Bread"></div>
                        <h3>Cinnamon Bread</h3>
                        <p>Warm, spiced loaf with a sweet glaze &ndash; perfect with your morning coffee.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Products Preview -->
        <section class="products-preview" id="products">
            <div class="section-inner">
                <p class="section-eyebrow">Our Menu</p>
                <h2>Featured Products</h2>
                <p class="section-sub">Browse our freshly baked selection. Log in to add items to your cart and place an order.</p>

                <div class="products-grid">
                    <?php if (empty($products)): ?>
                        <p class="no-products">No products available yet &ndash; check back soon!</p>
                    <?php else: ?>
                        <?php foreach ($products as $p): ?>
                        <div class="product-card">
                            <div class="product-img">
                                <?php if (!empty($p['image'])): ?>
                                    <img src="<?= htmlspecialchars($p['image']) ?>" alt="<?= htmlspecialchars($p['name']) ?>">
                                <?php else: ?>
                                    <div class="product-img-placeholder"></div>
                                <?php endif; ?>
                            </div>
                            <div class="product-info">
                                <h3><?= htmlspecialchars($p['name']) ?></h3>
                                <span class="product-price">&#8369;<?= number_format($p['price'], 2) ?></span>
                                <a href="login.php" class="btn btn-primary btn-sm">
                                    <i class='bx bx-lock-alt'></i> Login to Order
                                </a>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <div class="products-cta">
                    <a href="signup.php" class="btn btn-primary btn-lg">Sign Up to See Full Menu</a>
                </div>
            </div>
        </section>

        <!-- About -->
        <section class="about-preview" id="about">
            <div class="about-inner">
                <div class="about-img-wrap">
                    <div class="about-img" style="background-image:url('/BakeryOrderingSystem/Image/Bakery2.png');"></div>
                </div>
                <div class="about-content">
                    <p class="section-eyebrow">Our Story</p>
                    <h2>Baked with Love,<br>Served with Heart</h2>
                    <p>DailyBread is a local Filipino bakery dedicated to making homemade bread and delightful treats accessible to everyone. We started as a small home kitchen and have grown into a beloved community bakery &ndash; all while keeping the same warmth in every loaf.</p>
                    <p>We specialize in traditional Filipino pastries and breads made with the finest locally sourced ingredients and no artificial preservatives.</p>
                    <div class="about-values">
                        <div class="about-value"><i class='bx bxs-leaf'></i><span>Quality Ingredients</span></div>
                        <div class="about-value"><i class='bx bxs-group'></i><span>Community First</span></div>
                        <div class="about-value"><i class='bx bxs-heart'></i><span>Made with Love</span></div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Gallery Teaser -->
        <section class="gallery-teaser" id="gallery">
            <div class="section-inner">
                <p class="section-eyebrow">Our Baked Goods</p>
                <h2>From Our Kitchen</h2>
                <p class="section-sub">A glimpse of what we bake fresh every day.</p>
                <div class="gallery-grid">
                    <div class="gallery-item gi-tall" style="background-image:url('/BakeryOrderingSystem/Image/Pandesal.jpeg');"></div>
                    <div class="gallery-item" style="background-image:url('/BakeryOrderingSystem/Image/Crinkles2.jpg');"></div>
                    <div class="gallery-item" style="background-image:url('/BakeryOrderingSystem/Image/Bread2.jpg');"></div>
                    <div class="gallery-item gi-wide" style="background-image:url('/BakeryOrderingSystem/Image/Bakery3.jpg');"></div>
                </div>
                <div class="products-cta">
                    <a href="login.php" class="btn btn-outline btn-lg">Login to Explore More</a>
                </div>
            </div>
        </section>

        <!-- CTA Banner -->
        <section class="cta-banner">
            <div class="cta-inner">
                <h2>Ready to taste the difference?</h2>
                <p>Join DailyBread today and get fresh baked goods delivered straight to your door.</p>
                <div class="cta-btns">
                    <a href="signup.php" class="btn btn-light btn-lg">Create Free Account</a>
                    <a href="login.php"  class="btn btn-outline-light btn-lg">Login</a>
                </div>
            </div>
        </section>

        <!-- Footer -->
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
                    <h3>Quick Links</h3>
                    <ul>
                        <li><a href="#hero">Home</a></li>
                        <li><a href="#products">Shop</a></li>
                        <li><a href="#about">About Us</a></li>
                        <li><a href="#gallery">Gallery</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h3>Account</h3>
                    <ul>
                        <li><a href="login.php">Login</a></li>
                        <li><a href="signup.php">Sign Up</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h3>Contact</h3>
                    <ul>
                        <li>dailybread2k25@gmail.com</li>
                        <li>0929-776-3880</li>
                        <li>Gov. Drive, San Juan,<br>Dasmarinas Cavite, 4114</li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2026 DailyBread. All rights reserved.</p>
            </div>
        </footer>

    </main>

</body>
</html>
