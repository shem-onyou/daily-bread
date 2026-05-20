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

// Total revenue (sum of all order items regardless of status)
$revenueRow = $conn->query("
    SELECT COALESCE(SUM(oi.price * oi.qty), 0) AS total
    FROM order_items oi
    JOIN orders o ON o.id = oi.order_id
")->fetch_assoc();
$totalRevenue = $revenueRow['total'];

// Revenue this month vs last month
$revThisRow = $conn->query("
    SELECT COALESCE(SUM(oi.price * oi.qty), 0) AS total
    FROM order_items oi
    JOIN orders o ON o.id = oi.order_id
    WHERE MONTH(o.created_at) = MONTH(CURDATE())
    AND YEAR(o.created_at)  = YEAR(CURDATE())
")->fetch_assoc();
$revLastRow = $conn->query("
    SELECT COALESCE(SUM(oi.price * oi.qty), 0) AS total
    FROM order_items oi
    JOIN orders o ON o.id = oi.order_id
    WHERE MONTH(o.created_at) = MONTH(CURDATE() - INTERVAL 1 MONTH)
    AND YEAR(o.created_at)  = YEAR(CURDATE()  - INTERVAL 1 MONTH)
")->fetch_assoc();
$revThis = (float) $revThisRow['total'];
$revLast = (float) $revLastRow['total'];
$revChange = $revLast > 0 ? round((($revThis - $revLast) / $revLast) * 100, 1) : null;

// Total orders
$totalOrders = $conn->query("SELECT COUNT(*) AS c FROM orders")->fetch_assoc()['c'];
$ordThisRow  = $conn->query("SELECT COUNT(*) AS c FROM orders WHERE MONTH(created_at)=MONTH(CURDATE()) AND YEAR(created_at)=YEAR(CURDATE())")->fetch_assoc()['c'];
$ordLastRow  = $conn->query("SELECT COUNT(*) AS c FROM orders WHERE MONTH(created_at)=MONTH(CURDATE()-INTERVAL 1 MONTH) AND YEAR(created_at)=YEAR(CURDATE()-INTERVAL 1 MONTH)")->fetch_assoc()['c'];
$ordChange   = $ordLastRow > 0 ? round((($ordThisRow - $ordLastRow) / $ordLastRow) * 100, 1) : null;

// Total customers
$totalCustomers = $conn->query("SELECT COUNT(*) AS c FROM users WHERE role='customer'")->fetch_assoc()['c'];
$custThisRow    = $conn->query("SELECT COUNT(*) AS c FROM users WHERE role='customer' AND MONTH(created_at)=MONTH(CURDATE()) AND YEAR(created_at)=YEAR(CURDATE())")->fetch_assoc()['c'];
$custLastRow    = $conn->query("SELECT COUNT(*) AS c FROM users WHERE role='customer' AND MONTH(created_at)=MONTH(CURDATE()-INTERVAL 1 MONTH) AND YEAR(created_at)=YEAR(CURDATE()-INTERVAL 1 MONTH)")->fetch_assoc()['c'];
$custChange     = $custLastRow > 0 ? round((($custThisRow - $custLastRow) / $custLastRow) * 100, 1) : null;

// Total products
$totalProducts = $conn->query("SELECT COUNT(*) AS c FROM products")->fetch_assoc()['c'];

// Recent orders (last 5)
$recentOrders = $conn->query("
    SELECT
        o.id,
        o.status,
        o.created_at,
        u.name AS customer,
        COALESCE(SUM(oi.price * oi.qty), 0) AS amount,
        COALESCE(SUM(oi.qty), 0)            AS total_qty,
        MIN(oi.product_name)                AS first_product,
        COUNT(oi.id)                        AS item_count
    FROM orders o
    JOIN users u        ON u.id       = o.user_id
    LEFT JOIN order_items oi ON oi.order_id = o.id
    GROUP BY o.id, o.status, o.created_at, u.name
    ORDER BY o.created_at DESC
    LIMIT 5
");

// Helper: format change badge
function changeBadge($change, $thisMonth) {
    if ($change === null) {
        return $thisMonth > 0
            ? '<span class="metric-change positive">+' . $thisMonth . ' this month</span>'
            : '<span class="metric-change">No data yet</span>';
    }
    $sign  = $change >= 0 ? '+' : '';
    $class = $change >= 0 ? 'positive' : 'negative';
    return '<span class="metric-change ' . $class . '">' . $sign . $change . '% this month</span>';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - DailyBread</title>
    <link rel="stylesheet" href="/BakeryOrderingSystem/CSS/admin-dashboard.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>

    <aside class="sidebar" id="sidebar">
        <div class="sidebar-brand">
            <span>The DailyBread</span>
        </div>
        <nav class="sidebar-nav">
            <a href="admin-dashboard.php" class="nav-item active">
                <i class='bx bxs-dashboard'></i><span>Dashboard</span>
            </a>
            <a href="admin-products.php" class="nav-item">
                <i class='bx bxs-package'></i><span>Products</span>
            </a>
            <a href="admin-customers.php" class="nav-item">
                <i class='bx bxs-group'></i><span>Customers</span>
            </a>
            <a href="admin-orders.php" class="nav-item">
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
            <h1 class="page-title">Dashboard</h1>
            <div class="topbar-right">
                <span class="admin-name"><?= htmlspecialchars($_SESSION['user_name']) ?></span>
                <a href="admin-profile.php" class="avatar"><?php if (!empty($_adminPhotoVal)): ?><img src="<?= htmlspecialchars($_adminPhotoVal) ?>" alt=""><?php else: ?><?= strtoupper(substr($_SESSION['user_name'], 0, 1)) ?><?php endif; ?></a>
            </div>
        </header>

        <main class="content">

            <section class="metrics-grid">
                <div class="metric-card">
                    <div class="metric-icon"><i class='bx bxs-dollar-circle'></i></div>
                    <div class="metric-info">
                        <span class="metric-label">Total Revenue</span>
                        <span class="metric-value">&#8369;<?= number_format($totalRevenue, 2) ?></span>
                        <?= changeBadge($revChange, $revThis) ?>
                    </div>
                </div>
                <div class="metric-card">
                    <div class="metric-icon"><i class='bx bxs-cart-alt'></i></div>
                    <div class="metric-info">
                        <span class="metric-label">Total Orders</span>
                        <span class="metric-value"><?= number_format($totalOrders) ?></span>
                        <?= changeBadge($ordChange, $ordThisRow) ?>
                    </div>
                </div>
                <div class="metric-card">
                    <div class="metric-icon"><i class='bx bxs-group'></i></div>
                    <div class="metric-info">
                        <span class="metric-label">Customers</span>
                        <span class="metric-value"><?= number_format($totalCustomers) ?></span>
                        <?= changeBadge($custChange, $custThisRow) ?>
                    </div>
                </div>
                <div class="metric-card">
                    <div class="metric-icon"><i class='bx bxs-package'></i></div>
                    <div class="metric-info">
                        <span class="metric-label">Products</span>
                        <span class="metric-value"><?= number_format($totalProducts) ?></span>
                        <span class="metric-change">Total in inventory</span>
                    </div>
                </div>
            </section>

            <section class="charts-grid">
                <div class="chart-card large">
                    <h3>Revenue Overview</h3>
                    <p class="chart-subtitle">Monthly revenue for the current year</p>
                    <div class="chart-canvas-wrap">
                        <canvas id="revenueChart"></canvas>
                    </div>
                </div>
                <div class="chart-card">
                    <h3>Weekly Traffic</h3>
                    <p class="chart-subtitle">Orders per day this week</p>
                    <div class="chart-canvas-wrap">
                        <canvas id="trafficChart"></canvas>
                    </div>
                </div>
            </section>

            <section>
                <div class="chart-card">
                    <h3>Recent Orders</h3>
                    <p class="chart-subtitle">Last 5 transactions</p>
                    <div class="table-wrapper">
                    <table class="orders-table">
                        <thead>
                            <tr>
                                <th>Order</th><th>Customer</th><th>Product</th>
                                <th>Quantity</th><th>Date</th><th>Amount</th><th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if ($recentOrders && $recentOrders->num_rows > 0): ?>
                            <?php while ($row = $recentOrders->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['id']) ?></td>
                                <td><?= htmlspecialchars($row['customer']) ?></td>
                                <td>
                                    <?= htmlspecialchars($row['first_product']) ?>
                                    <?= $row['item_count'] > 1 ? '<span class="more-items">+' . ($row['item_count'] - 1) . ' more</span>' : '' ?>
                                </td>
                                <td><?= (int) $row['total_qty'] ?></td>
                                <td><?= date('M j, Y', strtotime($row['created_at'])) ?></td>
                                <td>&#8369;<?= number_format($row['amount'], 2) ?></td>
                                <td><span class="status-badge <?= $row['status'] ?>"><?= ucfirst($row['status']) ?></span></td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" style="text-align:center;padding:20px;">No orders yet.</td>
                            </tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                    </div>
                </div>
            </section>

        </main>
    </div>

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

    <script src="/BakeryOrderingSystem/JS/admin-dashboard.js"></script>
</body>
</html>
