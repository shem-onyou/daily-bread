<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}
require 'db.php';

// Admin photo
$_adminPhoto = $conn->prepare("SELECT photo FROM users WHERE id=?");
$_adminPhoto->bind_param("i", $_SESSION['user_id']); $_adminPhoto->execute();
$_adminPhotoVal = $_adminPhoto->get_result()->fetch_assoc()['photo'] ?? null;
$_adminPhoto->close();

// Handle status update POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    $data    = json_decode(file_get_contents('php://input'), true);
    $orderId = trim($data['order_id'] ?? '');
    $status  = trim($data['status']   ?? '');
    if (!in_array($status, ['pending','completed','cancelled'])) {
        echo json_encode(['error' => 'Invalid status']); exit;
    }
    $stmt = $conn->prepare("UPDATE orders SET status=? WHERE id=?");
    $stmt->bind_param("ss", $status, $orderId); $stmt->execute();
    echo $stmt->affected_rows > 0 ? json_encode(['success' => true]) : json_encode(['error' => 'Not found']);
    $stmt->close();
    exit;
}

// Fetch all orders with customer + items
$result = $conn->query("
    SELECT o.id, o.payment, o.proof_image, o.shipping, o.status,
        DATE_FORMAT(o.created_at,'%Y-%m-%d') AS date,
        u.name AS customer
    FROM orders o
    JOIN users u ON u.id = o.user_id
    ORDER BY o.created_at DESC
");
$orders = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];

$itemStmt = $conn->prepare("SELECT product_name AS name, qty, price FROM order_items WHERE order_id=?");
foreach ($orders as &$order) {
    $itemStmt->bind_param("s", $order['id']); $itemStmt->execute();
    $order['items'] = $itemStmt->get_result()->fetch_all(MYSQLI_ASSOC);
}
$itemStmt->close();
unset($order);

$countAll       = count($orders);
$countPending   = count(array_filter($orders, fn($o) => $o['status'] === 'pending'));
$countCompleted = count(array_filter($orders, fn($o) => $o['status'] === 'completed'));
$countCancelled = count(array_filter($orders, fn($o) => $o['status'] === 'cancelled'));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orders - DailyBread Admin</title>
    <link rel="stylesheet" href="/BakeryOrderingSystem/CSS/admin-orders.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
</head>
<body>

    <aside class="sidebar" id="sidebar">
        <div class="sidebar-brand">
            <span>The DailyBread</span>
        </div>
        <nav class="sidebar-nav">
            <a href="admin-dashboard.php" class="nav-item">
                <i class='bx bxs-dashboard'></i><span>Dashboard</span>
            </a>
            <a href="admin-products.php" class="nav-item">
                <i class='bx bxs-package'></i><span>Products</span>
            </a>
            <a href="admin-customers.php" class="nav-item">
                <i class='bx bxs-group'></i><span>Customers</span>
            </a>
            <a href="admin-orders.php" class="nav-item active">
                <i class='bx bxs-cart'></i><span>Orders</span>
            </a>
        </nav>
        <div class="sidebar-footer">
            <button class="btn-logout" id="logoutBtn">
                <i class='bx bx-log-out'></i><span>Logout</span>
            </button>
        </div>
    </aside>

    <div class="overlay" id="overlay"></div>

    <div class="main-wrapper">
        <header class="topbar">
            <button class="toggle-btn" id="toggleBtn"><i class='bx bx-menu'></i></button>
            <h1 class="page-title">Orders</h1>
            <div class="topbar-right">
                <span class="admin-name"><?= htmlspecialchars($_SESSION['user_name']) ?></span>
                <a href="admin-profile.php" class="avatar"><?php if (!empty($_adminPhotoVal)): ?><img src="<?= htmlspecialchars($_adminPhotoVal) ?>" alt=""><?php else: ?><?= strtoupper(substr($_SESSION['user_name'], 0, 1)) ?><?php endif; ?></a>
            </div>
        </header>

        <main class="content">

            <section class="summary-grid">
                <div class="summary-card">
                    <div class="summary-icon all"><i class='bx bxs-receipt'></i></div>
                    <div class="summary-info">
                        <span class="summary-label">Total Orders</span>
                        <span class="summary-value" id="countAll"><?= $countAll ?></span>
                    </div>
                </div>
                <div class="summary-card">
                    <div class="summary-icon pending"><i class='bx bxs-time'></i></div>
                    <div class="summary-info">
                        <span class="summary-label">Pending</span>
                        <span class="summary-value" id="countPending"><?= $countPending ?></span>
                    </div>
                </div>
                <div class="summary-card">
                    <div class="summary-icon completed"><i class='bx bxs-check-circle'></i></div>
                    <div class="summary-info">
                        <span class="summary-label">Completed</span>
                        <span class="summary-value" id="countCompleted"><?= $countCompleted ?></span>
                    </div>
                </div>
                <div class="summary-card">
                    <div class="summary-icon cancelled"><i class='bx bxs-x-circle'></i></div>
                    <div class="summary-info">
                        <span class="summary-label">Cancelled</span>
                        <span class="summary-value" id="countCancelled"><?= $countCancelled ?></span>
                    </div>
                </div>
            </section>

            <div class="chart-card">
                <div class="table-header">
                    <div>
                        <h3>All Orders</h3>
                        <p class="chart-subtitle">Manage and update the status of customer orders</p>
                    </div>
                    <div class="filter-group">
                        <label for="statusFilter">Filter:</label>
                        <select id="statusFilter" class="filter-select">
                            <option value="all">All</option>
                            <option value="pending">Pending</option>
                            <option value="completed">Completed</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>
                </div>
                <div class="table-wrapper">
                    <table class="orders-table">
                        <thead>
                            <tr>
                                <th>#</th><th>Product Name</th><th>Customer Name</th>
                                <th>Payment Method</th><th>Date</th><th>Qty</th>
                                <th>Price</th><th>Status</th><th>Proof of Payment</th><th>Receipt</th>
                            </tr>
                        </thead>
                        <tbody id="ordersTableBody"></tbody>
                    </table>
                </div>
            </div>
            <div id="ordersPagination" class="pagination"></div>

        </main>
    </div>

    <!-- Logout Modal -->
    <div class="modal-overlay" id="logoutModal">
        <div class="modal logout-modal">
            <div class="logout-icon"><i class='bx bx-log-out'></i></div>
            <h3>Confirm Logout</h3>
            <p>Are you sure you want to logout?</p>
            <div class="logout-modal-footer">
                <button class="btn-logout-cancel" id="logoutCancelBtn">Cancel</button>
                <a href="logout.php" class="btn-logout-confirm" id="logoutConfirmBtn">Confirm</a>
            </div>
        </div>
    </div>

    <!-- Proof of Payment Modal -->
    <div class="modal-overlay" id="proofModal">
        <div class="modal proof-modal">
            <div class="proof-modal-header">
                <h3>Proof of Payment</h3>
                <button class="proof-modal-close" id="proofModalClose"><i class='bx bx-x'></i></button>
            </div>
            <div class="proof-modal-body">
                <img id="proofModalImg" src="" alt="Proof of Payment">
            </div>
        </div>
    </div>

    <script>
        const ORDERS_DATA   = <?= json_encode($orders) ?>;
        const ORDERS_URL    = 'admin-orders.php';
    </script>
    <script src="/BakeryOrderingSystem/JS/pagination.js"></script>
    <script src="/BakeryOrderingSystem/JS/admin-orders.js"></script>
</body>
</html>
