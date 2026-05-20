<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
    header("Location: login.php");
    exit;
}
require 'db.php';

// â”€â”€ Handle cancel POST â”€â”€
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    $data    = json_decode(file_get_contents('php://input'), true);
    $orderId = trim($data['order_id'] ?? '');
    $userId  = $_SESSION['user_id'];

    if (!$orderId) {
        echo json_encode(['error' => 'Order ID required']);
        exit;
    }
    $stmt = $conn->prepare("UPDATE orders SET status='cancelled' WHERE id=? AND user_id=? AND status='pending'");
    $stmt->bind_param("si", $orderId, $userId);
    $stmt->execute();
    echo $stmt->affected_rows > 0
        ? json_encode(['success' => true])
        : json_encode(['error'   => 'Order not found or cannot be cancelled']);
    $stmt->close();
    exit;
}

// â”€â”€ Fetch orders for this customer â”€â”€
$userId = $_SESSION['user_id'];
$stmt   = $conn->prepare("
    SELECT o.id, o.payment, o.shipping, o.status,
        DATE_FORMAT(o.created_at, '%Y-%m-%d') AS date
    FROM orders o
    WHERE o.user_id = ?
    ORDER BY o.created_at DESC
");
$stmt->bind_param("i", $userId);
$stmt->execute();
$orders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$itemStmt = $conn->prepare("SELECT product_name AS name, qty, price FROM order_items WHERE order_id = ?");
foreach ($orders as &$order) {
    $itemStmt->bind_param("s", $order['id']);
    $itemStmt->execute();
    $order['items'] = $itemStmt->get_result()->fetch_all(MYSQLI_ASSOC);
}
$itemStmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders - DailyBread</title>
    <link rel="stylesheet" href="/BakeryOrderingSystem/CSS/orders.css">
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
                <button class="nav-btn orders-btn active-orders" onclick="window.location.href='orders.php'">
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
            <a href="about.php">About</a>
            <a href="gallery.php">Gallery</a>
        </div>
    </nav>

    <main class="page-main">

        <section class="orders-header">
            <h1>My Orders</h1>
            <p>Track and manage your order history</p>
        </section>

        <div class="orders-body">
            <div class="orders-filters">
                <button class="filter-btn active" data-filter="all">All</button>
                <button class="filter-btn" data-filter="pending">Pending</button>
                <button class="filter-btn" data-filter="completed">Completed</button>
                <button class="filter-btn" data-filter="cancelled">Cancelled</button>
            </div>
            <div class="orders-empty" id="ordersEmpty">
                <i class='bx bx-shopping-bag'></i>
                <p>No orders found.</p>
                <a href="shop.php" class="btn btn-primary">Start Shopping</a>
            </div>
            <div class="orders-list" id="ordersList"></div>
            <div id="ordersPagination" class="orders-pagination"></div>
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

    <div class="modal-overlay" id="orderModal">
        <div class="order-modal">
            <div class="order-modal-header">
                <div>
                    <h3 id="modalOrderId"></h3>
                    <p id="modalOrderDate" class="modal-date"></p>
                </div>
                <button class="modal-close" id="modalClose"><i class='bx bx-x'></i></button>
            </div>
            <div class="order-modal-body">
                <div class="modal-status-row">
                    <span class="modal-status-label">Status</span>
                    <span class="order-status-badge" id="modalStatus"></span>
                </div>
                <div class="modal-section">
                    <h4><i class='bx bx-package'></i> Items Ordered</h4>
                    <div class="modal-items" id="modalItems"></div>
                </div>
                <div class="modal-section">
                    <h4><i class='bx bx-receipt'></i> Price Breakdown</h4>
                    <div class="modal-price-row"><span>Subtotal</span><span id="modalSubtotal"></span></div>
                    <div class="modal-price-row"><span>Shipping Fee</span><span id="modalShipping"></span></div>
                    <div class="modal-price-divider"></div>
                    <div class="modal-price-row total"><span>Total</span><span id="modalTotal"></span></div>
                </div>
                <div class="modal-section">
                    <h4><i class='bx bx-credit-card'></i> Payment</h4>
                    <p id="modalPayment" class="modal-detail-text"></p>
                </div>
            </div>
            <div class="order-modal-footer">
                <button class="btn-cancel-order" id="cancelOrderBtn">
                    <i class='bx bx-x-circle'></i> Cancel Order
                </button>
                <button class="btn-close-modal" id="closeModalBtn">Close</button>
            </div>
        </div>
    </div>

    <script>
        // Orders data embedded by PHP — no fetch needed
        const ORDERS_DATA  = <?= json_encode($orders) ?>;
        const CANCEL_URL   = 'orders.php';
    </script>
    <script src="/BakeryOrderingSystem/JS/cart-badge.js"></script>
    <script src="/BakeryOrderingSystem/JS/navbar.js"></script>
    <script src="/BakeryOrderingSystem/JS/orders.js"></script>
</body>
</html>
