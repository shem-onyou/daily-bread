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

// Handle POST (save, delete)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    $data   = json_decode(file_get_contents('php://input'), true);
    $action = $data['action'] ?? '';

    if ($action === 'save') {
        $id    = trim($data['id']    ?? '');
        $name  = trim($data['name']  ?? '');
        $stock = intval($data['stock']  ?? 0);
        $price = floatval($data['price'] ?? 0);
        $image = trim($data['image'] ?? '');

        if (!$id || !$name) { echo json_encode(['error' => 'ID and name are required']); exit; }
        if (!empty($image) && str_starts_with($image, 'data:') && strlen($image) * 0.75 > 1_000_000) {
            echo json_encode(['error' => 'Image too large. Use an image under 1MB or paste a URL.']); exit;
        }

        $check = $conn->prepare("SELECT id FROM products WHERE id=?");
        $check->bind_param("s", $id); $check->execute();
        $exists = $check->get_result()->num_rows > 0;
        $check->close();

        if ($exists) {
            $stmt = $conn->prepare("UPDATE products SET name=?,stock=?,price=?,image=? WHERE id=?");
            $stmt->bind_param("sidss", $name, $stock, $price, $image, $id);
        } else {
            $stmt = $conn->prepare("INSERT INTO products (id,name,stock,price,image) VALUES (?,?,?,?,?)");
            $stmt->bind_param("ssids", $id, $name, $stock, $price, $image);
        }
        echo $stmt->execute() ? json_encode(['success' => true]) : json_encode(['error' => $stmt->error]);
        $stmt->close();

    } elseif ($action === 'delete') {
        $id   = trim($data['id'] ?? '');
        $stmt = $conn->prepare("DELETE FROM products WHERE id=?");
        $stmt->bind_param("s", $id); $stmt->execute();
        echo $stmt->affected_rows > 0 ? json_encode(['success' => true]) : json_encode(['error' => 'Not found']);
        $stmt->close();
    }
    exit;
}

// Fetch all products
$result   = $conn->query("SELECT id, name, stock, price, image FROM products ORDER BY id ASC");
$products = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products - DailyBread Admin</title>
    <link rel="stylesheet" href="/BakeryOrderingSystem/CSS/admin-products.css">
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
            <a href="admin-products.php" class="nav-item active">
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
            <h1 class="page-title">Products</h1>
            <div class="topbar-right">
                <span class="admin-name"><?= htmlspecialchars($_SESSION['user_name']) ?></span>
                <a href="admin-profile.php" class="avatar"><?php if (!empty($_adminPhotoVal)): ?><img src="<?= htmlspecialchars($_adminPhotoVal) ?>" alt=""><?php else: ?><?= strtoupper(substr($_SESSION['user_name'], 0, 1)) ?><?php endif; ?></a>
            </div>
        </header>

        <main class="content">
            <div class="chart-card">
                <div class="table-header">
                    <div>
                        <h3>All Products</h3>
                        <p class="chart-subtitle">Manage your bakery product inventory</p>
                    </div>
                    <button class="btn-add" id="addProductBtn">
                        <i class='bx bx-plus'></i> Add Product
                    </button>
                </div>
                <div class="table-wrapper">
                    <table class="products-table">
                        <thead>
                            <tr>
                                <th>#</th><th>Image</th><th>Product ID</th>
                                <th>Product Name</th><th>Stock</th><th>Price</th><th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="productsTableBody"></tbody>
                    </table>
                </div>
            </div>
            <div id="productsPagination" class="pagination"></div>
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

    <!-- Add Product Modal -->
    <div class="modal-overlay" id="addProductModal">
        <div class="modal product-modal">
            <div class="modal-header">
                <h3>Add Product</h3>
                <button class="modal-close" id="addProductModalClose"><i class='bx bx-x'></i></button>
            </div>
            <div class="modal-body">
                <div class="modal-image-upload">
                    <div class="image-preview" id="imagePreview"><i class='bx bx-image-alt'></i></div>
                    <div class="image-upload-actions">
                        <p class="image-upload-label">Product Image</p>
                        <p class="image-upload-hint">Upload a file or paste an image URL below</p>
                        <label class="btn-upload" for="inputProductFile">
                            <i class='bx bx-upload'></i> Choose File
                        </label>
                        <input type="file" id="inputProductFile" accept="image/*" hidden>
                    </div>
                </div>
                <div class="modal-row">
                    <div class="modal-field">
                        <label for="inputProductImageUrl">Image URL (optional)</label>
                        <input type="text" id="inputProductImageUrl" placeholder="https://...">
                    </div>
                </div>
                <div class="modal-row">
                    <div class="modal-field">
                        <label for="inputProductId">Product ID</label>
                        <input type="text" id="inputProductId" readonly>
                    </div>
                    <div class="modal-field">
                        <label for="inputProductName">Product Name</label>
                        <input type="text" id="inputProductName" placeholder="e.g. Sourdough Bread">
                    </div>
                </div>
                <div class="modal-row">
                    <div class="modal-field">
                        <label for="inputProductStock">Stock</label>
                        <input type="number" id="inputProductStock" placeholder="0" min="0" step="1">
                    </div>
                    <div class="modal-field">
                        <label for="inputProductPrice">Price (&#8369;)</label>
                        <input type="number" id="inputProductPrice" placeholder="0.00" min="0" step="0.01">
                    </div>
                </div>
                <p class="modal-error" id="addProductError"></p>
            </div>
            <div class="modal-footer">
                <button class="btn-modal-cancel" id="addProductCancel">Cancel</button>
                <button class="btn-modal-save" id="addProductSave">
                    <i class='bx bx-plus'></i> Add Product
                </button>
            </div>
        </div>
    </div>

    <script>
        const PRODUCTS_DATA = <?= json_encode(array_map(fn($p) => ['id'=>$p['id'],'name'=>$p['name'],'stock'=>$p['stock'],'price'=>$p['price']], $products)) ?>;
        const PRODUCTS_IMAGES = <?= json_encode(array_column($products, 'image', 'id')) ?>;
        const PRODUCTS_URL  = 'admin-products.php';
    </script>
    <script src="/BakeryOrderingSystem/JS/sanitize.js"></script>
    <script src="/BakeryOrderingSystem/JS/pagination.js"></script>
    <script src="/BakeryOrderingSystem/JS/admin-products.js"></script>
</body>
</html>
