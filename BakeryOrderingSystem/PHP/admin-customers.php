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

// Fetch customers with order count and latest message
$result = $conn->query("
    SELECT u.id, u.name, u.email, u.phone, u.city, u.country,
        COUNT(DISTINCT o.id) AS orders,
        MAX(m.message)       AS message
    FROM users u
    LEFT JOIN orders   o ON o.user_id = u.id
    LEFT JOIN messages m ON m.user_id = u.id
    WHERE u.role = 'customer'
    GROUP BY u.id
    ORDER BY u.name ASC
");
$customers = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customers - DailyBread Admin</title>
    <link rel="stylesheet" href="/BakeryOrderingSystem/CSS/admin-customers.css">
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
            <a href="admin-customers.php" class="nav-item active">
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
            <h1 class="page-title">Customers</h1>
            <div class="topbar-right">
                <span class="admin-name"><?= htmlspecialchars($_SESSION['user_name']) ?></span>
                <a href="admin-profile.php" class="avatar"><?php if (!empty($_adminPhotoVal)): ?><img src="<?= htmlspecialchars($_adminPhotoVal) ?>" alt=""><?php else: ?><?= strtoupper(substr($_SESSION['user_name'], 0, 1)) ?><?php endif; ?></a>
            </div>
        </header>

        <main class="content">
            <div class="chart-card">
                <div class="table-header">
                    <div>
                        <h3>All Customers</h3>
                        <p class="chart-subtitle">View and manage your registered customers</p>
                    </div>
                </div>
                <div class="table-wrapper">
                    <table class="customers-table">
                        <thead>
                            <tr>
                                <th>#</th><th>Username</th><th>Email</th>
                                <th>Address</th><th>Contact No.</th><th>Orders</th><th>Message</th>
                            </tr>
                        </thead>
                        <tbody id="customersTableBody">
                        <?php foreach ($customers as $i => $c): ?>
                        <tr>
                            <td><?= $i + 1 ?></td>
                            <td><?= htmlspecialchars($c['name']) ?></td>
                            <td><?= htmlspecialchars($c['email']) ?></td>
                            <td><?= htmlspecialchars(implode(', ', array_filter([$c['city'], $c['country']])) ?: '&mdash;') ?></td>
                            <td><?= htmlspecialchars($c['phone'] ?? '&mdash;') ?></td>
                            <td><span class="orders-count"><?= (int)$c['orders'] ?></span></td>
                            <td>
                                <?php if (!empty($c['message'])): ?>
                                <button class="btn-message"
                                    data-name="<?= htmlspecialchars($c['name']) ?>"
                                    data-message="<?= htmlspecialchars($c['message']) ?>">
                                    <i class='bx bx-message-rounded-dots'></i> View
                                </button>
                                <?php else: ?>
                                <span style="color:#bbb;font-style:italic;font-size:0.85rem;">&mdash;</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($customers)): ?>
                        <tr><td colspan="7" style="text-align:center;padding:20px;">No customers yet.</td></tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div id="customersPagination" class="pagination"></div>
        </main>
    </div>

    <!-- Message Modal -->
    <div class="modal-overlay" id="modalOverlay">
        <div class="modal">
            <div class="modal-header">
                <h3 id="modalCustomerName">Customer Message</h3>
                <button class="modal-close" id="modalClose"><i class='bx bx-x'></i></button>
            </div>
            <p class="modal-message" id="modalMessage"></p>
        </div>
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

    <script src="/BakeryOrderingSystem/JS/pagination.js"></script>
    <script src="/BakeryOrderingSystem/JS/admin-customers.js"></script>
</html>
