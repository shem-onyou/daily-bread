<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
    header("Location: login.php");
    exit;
}
require 'db.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - DailyBread</title>
    <link rel="stylesheet" href="/BakeryOrderingSystem/CSS/about.css">
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
            <a href="shop.php">Shop</a>
            <a href="about.php" class="active-link">About</a>
            <a href="gallery.php">Gallery</a>
        </div>
    </nav>

    <main class="page-main">
    <section class="about-hero">
        <div class="about-hero-content">
            <p class="about-hero-eyebrow">Our Story</p>
            <h1>Baked with Love,<br>Served with Heart</h1>
            <p class="about-hero-sub">From a small home kitchen to your table — DailyBread has been crafting honest, wholesome baked goods since day one.</p>
        </div>
        <div class="about-hero-image">
            <div class="about-hero-placeholder" style="background-image: url('/BakeryOrderingSystem/Image/DailyBread.png');"></div>
        </div>
    </section>

    <section class="about-intro">
        <div class="about-intro-inner">
            <div class="about-intro-text">
                <h2>Who We Are</h2>
                <p>DailyBread is a local Filipino bakery dedicated to making homemade bread and delightful treats accessible to everyone. We started as a small home kitchen operation and have grown into a beloved community bakery, all while keeping the same warmth and care in every loaf we bake.</p>
                <p>We specialize in traditional Filipino pastries and breads — from soft pandesal to buttery ensaymada — made with the finest locally sourced ingredients and no artificial preservatives.</p>
            </div>
            <div class="about-intro-stats">
                <div class="stat-card"><span class="stat-number">2+</span><span class="stat-label">Years Baking</span></div>
                <div class="stat-card"><span class="stat-number">15+</span><span class="stat-label">Products</span></div>
                <div class="stat-card"><span class="stat-number">50+</span><span class="stat-label">Happy Customers</span></div>
                <div class="stat-card"><span class="stat-number">100%</span><span class="stat-label">Homemade</span></div>
            </div>
        </div>
    </section>

    <section class="about-mission">
        <div class="about-mission-inner">
            <div class="mission-icon-wrap"><i class='bx bxs-heart'></i></div>
            <h2>Our Mission</h2>
            <p>To bring the warmth of a home kitchen to every household — one freshly baked loaf at a time. We believe good bread should be simple, honest, and made with ingredients you can trust.</p>
        </div>
    </section>

    <section class="about-values">
        <div class="about-values-inner">
            <h2>Our Values</h2>
            <p class="values-subtitle">The principles that guide every batch we bake</p>
            <div class="values-grid">
                <div class="value-card">
                    <div class="value-icon"><i class='bx bxs-leaf'></i></div>
                    <h3>Quality Ingredients</h3>
                    <p>We source fresh, local ingredients and never compromise on what goes into our products. No artificial flavors, no shortcuts.</p>
                </div>
                <div class="value-card">
                    <div class="value-icon"><i class='bx bxs-group'></i></div>
                    <h3>Community First</h3>
                    <p>We are proud to be a neighborhood bakery. Supporting local farmers and giving back to our community is at the core of who we are.</p>
                </div>
                <div class="value-card">
                    <div class="value-icon"><i class='bx bxs-star'></i></div>
                    <h3>Consistency</h3>
                    <p>Every product that leaves our kitchen meets the same high standard — whether it is your first order or your hundredth.</p>
                </div>
                <div class="value-card">
                    <div class="value-icon"><i class='bx bxs-smile'></i></div>
                    <h3>Customer Joy</h3>
                    <p>We bake to make people happy. Your satisfaction is not just a goal — it is the reason we wake up early every morning.</p>
                </div>
            </div>
        </div>
    </section>

    <section class="about-cta">
        <h2>Ready to taste the difference?</h2>
        <p>Browse our freshly baked selection and place your order today.</p>
        <a href="shop.php" class="btn btn-primary">Shop Now</a>
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
    <script src="/BakeryOrderingSystem/JS/about.js"></script>
</body>
</html>
