<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
    header("Location: login.php");
    exit;
}
require 'db.php';

// -- Handle stock check GET --
if ($_SERVER['REQUEST_METHOD'] === 'GET' && ($_GET['action'] ?? '') === 'stock') {
    header('Content-Type: application/json');
    $ids = array_filter(array_map('trim', explode(',', $_GET['ids'] ?? '')));
    if (empty($ids)) { echo json_encode([]); exit; }
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $types = str_repeat('s', count($ids));
    $stmt  = $conn->prepare("SELECT id, stock FROM products WHERE id IN ($placeholders)");
    $stmt->bind_param($types, ...$ids);
    $stmt->execute();
    $rows  = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    $stock = [];
    foreach ($rows as $r) $stock[$r['id']] = (int)$r['stock'];
    echo json_encode($stock);
    exit;
}

// -- Handle checkout POST --
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    $data    = json_decode(file_get_contents('php://input'), true);
    $items   = $data['items']   ?? [];
    $payment = trim($data['payment'] ?? '');
    $proof   = trim($data['proof']   ?? '');

    if (empty($items) || !$payment) {
        echo json_encode(['error' => 'Missing order data']);
        exit;
    }

    // Reject oversized proof images (10MB limit)
    if (!empty($proof) && strlen($proof) * 0.75 > 10_000_000) {
        echo json_encode(['error' => 'Proof image is too large. Please use an image under 10MB.']);
        exit;
    }

    // Validate proof image type (JPEG or PNG only)
    if (!empty($proof)) {
        $mime = '';
        if (str_starts_with($proof, 'data:image/jpeg')) $mime = 'image/jpeg';
        elseif (str_starts_with($proof, 'data:image/png')) $mime = 'image/png';
        if (!$mime) {
            echo json_encode(['error' => 'Invalid file type. Only JPEG and PNG are accepted.']);
            exit;
        }
    }

    // Server-side stock validation before placing order
    $ids = array_filter(array_map(fn($i) => trim($i['id'] ?? ''), $items));
    if (!empty($ids)) {
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $types = str_repeat('s', count($ids));
        $chk   = $conn->prepare("SELECT id, stock FROM products WHERE id IN ($placeholders)");
        $chk->bind_param($types, ...$ids);
        $chk->execute();
        $stockMap = [];
        foreach ($chk->get_result()->fetch_all(MYSQLI_ASSOC) as $r) $stockMap[$r['id']] = (int)$r['stock'];
        $chk->close();

        foreach ($items as $item) {
            $pid = trim($item['id'] ?? '');
            $qty = intval($item['qty'] ?? 0);
            if ($pid && isset($stockMap[$pid]) && $qty > $stockMap[$pid]) {
                echo json_encode(['error' => 'Not enough stock for "' . htmlspecialchars($item['name']) . '". Only ' . $stockMap[$pid] . ' left.']);
                exit;
            }
        }
    }

    $userId  = $_SESSION['user_id'];
    $orderId = 'ORD-' . strtoupper(substr(uniqid(), -6));

    // Determine shipping fee based on customer's city
    $cityRow  = $conn->prepare("SELECT city FROM users WHERE id=?");
    $cityRow->bind_param("i", $userId);
    $cityRow->execute();
    $cityData = $cityRow->get_result()->fetch_assoc();
    $cityRow->close();
    $city     = strtolower(trim($cityData['city'] ?? ''));
    $shipping = (strcasecmp($city, 'Dasmariñas') === 0) ? 5.00 : 10.00;

    $stmt = $conn->prepare("INSERT INTO orders (id, user_id, payment, proof_image, shipping, status) VALUES (?,?,?,?,?,'pending')");
    $stmt->bind_param("sissd", $orderId, $userId, $payment, $proof, $shipping);

    if (!$stmt->execute()) {
        echo json_encode(['error' => $stmt->error]);
        exit;
    }
    $stmt->close();

    $itemStmt  = $conn->prepare("INSERT INTO order_items (order_id, product_id, product_name, qty, price) VALUES (?,?,?,?,?)");
    $stockStmt = $conn->prepare("UPDATE products SET stock = GREATEST(stock - ?, 0) WHERE id = ?");
    foreach ($items as $item) {
        $productId = trim($item['id']    ?? '');
        $name      = trim($item['name']  ?? '');
        $qty       = intval($item['qty']   ?? 0);
        $price     = floatval($item['price'] ?? 0);
        $itemStmt->bind_param("sssid", $orderId, $productId, $name, $qty, $price);
        $itemStmt->execute();
        if ($productId && $qty > 0) {
            $stockStmt->bind_param("is", $qty, $productId);
            $stockStmt->execute();
        }
    }
    $itemStmt->close();
    $stockStmt->close();

    echo json_encode(['success' => true, 'order_id' => $orderId]);
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Cart - DailyBread</title>
    <link rel="stylesheet" href="/BakeryOrderingSystem/CSS/cart.css">
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
                <button class="nav-btn cart-btn active-cart" onclick="window.location.href='cart.php'">
                    <i class='bx bx-cart'></i>
                    <span class="cart-count" id="cartBadge"></span>
                </button>
                <?php include 'nav_profile_btn.php'; ?>
            </div>
        </div>
        <div class="nav-dropdown" id="navDropdown">
            <a href="home.php">Home</a>
            <a href="shop.php">Shop</a>
            <a href="about.php">About</a>
            <a href="gallery.php">Gallery</a>
        </div>
    </nav>

    <main class="page-main">

        <section class="cart-header">
            <h1>My Cart</h1>
            <p>Review your items before placing your order</p>
        </section>

        <div class="cart-body">
            <div class="cart-items-col">
                <div class="cart-items-header">
                    <h2>Cart Items</h2>
                    <button class="btn-clear-cart" id="clearCartBtn">
                        <i class='bx bx-trash'></i> Clear All
                    </button>
                </div>
                <div class="cart-empty" id="cartEmpty">
                    <i class='bx bx-cart'></i>
                    <p>Your cart is empty.</p>
                    <a href="shop.php" class="btn btn-primary">Browse Products</a>
                </div>
                <div class="cart-list" id="cartList"></div>
            </div>

            <div class="cart-summary-col">
                <div class="summary-card">
                    <h2>Order Summary</h2>
                    <div class="summary-row">
                        <span>Subtotal</span><span id="summarySubtotal">₱0.00</span>
                    </div>
                    <div class="summary-row">
                        <span>Shipping Fee</span><span id="summaryTax">₱0.00</span>
                    </div>
                    <div class="summary-divider"></div>
                    <div class="summary-row total">
                        <span>Total</span><span id="summaryTotal">₱0.00</span>
                    </div>

                    <div class="payment-section">
                        <h3>Mode of Payment</h3>
                        <div class="payment-options">
                            <label class="payment-option">
                                <input type="radio" name="payment" value="cod" id="payCod" checked>
                                <span class="payment-label"><i class='bx bx-money'></i> Cash on Delivery</span>
                            </label>
                            <label class="payment-option">
                                <input type="radio" name="payment" value="gcash" id="payGcash">
                                <span class="payment-label"><i class='bx bx-mobile-alt'></i> GCash</span>
                            </label>
                            <label class="payment-option">
                                <input type="radio" name="payment" value="pickup" id="payPickup">
                                <span class="payment-label"><i class='bx bx-store'></i> Pick-up</span>
                            </label>
                        </div>
                        <div class="gcash-details" id="gcashDetails">
                            <p class="gcash-instruction">Scan the QR code below and upload your proof of payment.</p>
                            <div class="qr-placeholder">
                                <img src="/BakeryOrderingSystem/Image/gcash-qr.jpg" alt="GCash QR Code">
                            </div>
                            <label class="upload-label" for="proofUpload">
                                <i class='bx bx-upload'></i> Attach Proof of Payment
                            </label>
                            <input type="file" id="proofUpload" accept="image/jpeg,image/png" hidden>
                            <p class="upload-filename" id="uploadFilename">No file chosen</p>
                        </div>
                    </div>

                    <button class="btn btn-checkout" id="checkoutBtn">
                        <i class='bx bx-check-circle'></i> Place Order
                    </button>
                </div>
            </div>
        </div>

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

    <div class="modal-overlay" id="confirmModal">
        <div class="confirm-modal">
            <div class="confirm-icon"><i class='bx bx-check-circle'></i></div>
            <h3>Order Placed!</h3>
            <p id="confirmMessage">Your order has been placed successfully.</p>
            <a href="shop.php" class="btn btn-primary">Continue Shopping</a>
        </div>
    </div>

    <script>
        const CHECKOUT_URL    = 'cart.php';
        const STOCK_CHECK_URL = 'cart.php?action=stock';
        <?php
        $uid   = $_SESSION['user_id'];
        $cStmt = $conn->prepare("SELECT city FROM users WHERE id=?");
        $cStmt->bind_param("i", $uid);
        $cStmt->execute();
        $cData = $cStmt->get_result()->fetch_assoc();
        $cStmt->close();
        $cCity = strtolower(trim($cData['city'] ?? ''));
        $shippingFee = (strcasecmp($cCity, 'Dasmariñas') === 0) ? 5 : 10;
        ?>
        const SHIPPING_FEE = <?= $shippingFee ?>;
    </script>
    <script src="/BakeryOrderingSystem/JS/cart-badge.js"></script>
    <script src="/BakeryOrderingSystem/JS/navbar.js"></script>
    <script src="/BakeryOrderingSystem/JS/cart.js"></script>
</body>
</html>
