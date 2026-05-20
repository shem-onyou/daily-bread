<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
    header("Location: login.php");
    exit;
}
require 'db.php';

// â”€â”€ Handle POST (save personal, save address, change password) â”€â”€
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    $data = json_decode(file_get_contents('php://input'), true);
    $type = $data['type'] ?? '';
    $uid  = $_SESSION['user_id'];

    if ($type === 'personal') {
        $name  = trim(($data['first_name'] ?? '') . ' ' . ($data['last_name'] ?? ''));
        $email = trim($data['email'] ?? '');
        $phone = trim($data['phone'] ?? '');
        $dob   = trim($data['dob']   ?? '');
        $stmt  = $conn->prepare("UPDATE users SET name=?, email=?, phone=?, dob=? WHERE id=?");
        $stmt->bind_param("ssssi", $name, $email, $phone, $dob, $uid);
        $stmt->execute();
        $_SESSION['user_name'] = $name;
        echo json_encode(['success' => true]);

    } elseif ($type === 'address') {
        $country = trim($data['country'] ?? '');
        $city    = trim($data['city']    ?? '');
        $postal  = trim($data['postal']  ?? '');
        $stmt    = $conn->prepare("UPDATE users SET country=?, city=?, postal=? WHERE id=?");
        $stmt->bind_param("sssi", $country, $city, $postal, $uid);
        $stmt->execute();
        echo json_encode(['success' => true]);

    } elseif ($type === 'password') {
        $current = $data['current'] ?? '';
        $newPw   = $data['new']     ?? '';
        $confirm = $data['confirm'] ?? '';

        if ($newPw !== $confirm)      { echo json_encode(['error' => 'New passwords do not match']); exit; }
        if (strlen($newPw) < 6)       { echo json_encode(['error' => 'Password must be at least 6 characters']); exit; }

        $row = $conn->prepare("SELECT password FROM users WHERE id=?");
        $row->bind_param("i", $uid);
        $row->execute();
        $hash = $row->get_result()->fetch_assoc()['password'];

        if (!password_verify($current, $hash)) { echo json_encode(['error' => 'Current password is incorrect']); exit; }

        $hashed = password_hash($newPw, PASSWORD_DEFAULT);
        $upd    = $conn->prepare("UPDATE users SET password=? WHERE id=?");
        $upd->bind_param("si", $hashed, $uid);
        $upd->execute();
        echo json_encode(['success' => true]);

    } elseif ($type === 'photo') {
        $photo = trim($data['photo'] ?? '') ?: null;
        $stmt  = $conn->prepare("UPDATE users SET photo=? WHERE id=?");
        $stmt->bind_param("si", $photo, $uid);
        $stmt->execute();
        echo json_encode(['success' => true]);

    } else {
        echo json_encode(['error' => 'Invalid request type']);
    }
    exit;
}

// â”€â”€ Fetch user profile â”€â”€
$stmt = $conn->prepare("SELECT name, email, phone, dob, country, city, postal, photo FROM users WHERE id=?");
if (!$stmt) {
    // Extra columns missing — fall back to basic query
    $stmt = $conn->prepare("SELECT name, email FROM users WHERE id=?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $row  = $stmt->get_result()->fetch_assoc();
    $user = array_merge(['phone'=>null,'dob'=>null,'country'=>null,'city'=>null,'postal'=>null,'photo'=>null], $row ?? []);
} else {
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc() ?? [];
}
$stmt->close();

// Guard: if user not found redirect to login
if (empty($user)) {
    header("Location: login.php");
    exit;
}

$parts     = explode(' ', $user['name'], 2);
$firstName = $parts[0] ?? '';
$lastName  = $parts[1] ?? '';
$address   = implode(', ', array_filter([$user['city'], $user['country'], $user['postal']])) ?: '—';

function fmtDate($d) {
    if (!$d || $d === '0000-00-00') return '—';
    $parts = explode('-', $d);
    if (count($parts) !== 3) return '—';
    [$y, $m, $day] = $parts;
    if ((int)$m < 1 || (int)$m > 12) return '—';
    $months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
    return $months[(int)$m - 1] . ' ' . (int)$day . ', ' . $y;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - DailyBread</title>
    <link rel="stylesheet" href="/BakeryOrderingSystem/CSS/profile.css">
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
            <a href="about.php">About</a>
            <a href="gallery.php">Gallery</a>
        </div>
    </nav>

    <main class="page-main">

        <section class="profile-header">
            <h1>My Profile</h1>
            <p>Manage your personal information and preferences</p>
        </section>

        <div class="profile-content">

            <div class="profile-card profile-overview">
                <div class="profile-avatar-wrap">
                    <?php if (!empty($user['photo'])): ?>
                        <div class="profile-avatar" id="profileAvatar"><img src="<?= htmlspecialchars($user['photo']) ?>" alt="Profile photo"></div>
                    <?php else: ?>
                        <div class="profile-avatar" id="profileAvatar"><?= strtoupper(substr($firstName ?: $user['name'], 0, 1)) ?></div>
                    <?php endif; ?>
                    <input type="file" id="avatarInput" accept="image/*" hidden>
                    <div class="pic-btn-group">
                        <button class="btn-edit-pic" id="editPicBtn">
                            <i class='bx bx-camera'></i> Change Photo
                        </button>
                        <button class="btn-remove-pic" id="removePicBtn">
                            <i class='bx bx-trash'></i>
                        </button>
                    </div>
                </div>
                <div class="profile-meta">
                    <h2 id="displayFullName"><?= htmlspecialchars($user['name']) ?></h2>
                    <span class="role-badge">Customer</span>
                    <p class="profile-address">
                        <i class='bx bx-map'></i>
                        <span id="displayAddressText"><?= htmlspecialchars($address) ?></span>
                    </p>
                </div>
                <div class="profile-actions">
                    <button class="btn-profile-action" id="changePasswordBtn">
                        <i class='bx bx-lock-alt'></i> Change Password
                    </button>
                </div>
            </div>

            <div class="profile-card">
                <div class="profile-section-header">
                    <div>
                        <h3>Personal Information</h3>
                        <p class="profile-subtitle">Your personal details</p>
                    </div>
                    <button class="btn-edit-section" id="editPersonalBtn">
                        <i class='bx bxs-edit'></i> Edit
                    </button>
                </div>
                <div class="info-grid">
                    <div class="info-field">
                        <span class="info-label">First Name</span>
                        <span class="info-value" id="displayFirstName"><?= htmlspecialchars($firstName ?: '—') ?></span>
                    </div>
                    <div class="info-field">
                        <span class="info-label">Last Name</span>
                        <span class="info-value" id="displayLastName"><?= htmlspecialchars($lastName ?: '—') ?></span>
                    </div>
                    <div class="info-field">
                        <span class="info-label">Date of Birth</span>
                        <span class="info-value" id="displayDob"><?= fmtDate($user['dob']) ?></span>
                    </div>
                    <div class="info-field">
                        <span class="info-label">Email Address</span>
                        <span class="info-value" id="displayEmail"><?= htmlspecialchars($user['email'] ?? '—') ?></span>
                    </div>
                    <div class="info-field">
                        <span class="info-label">Phone Number</span>
                        <span class="info-value" id="displayPhone"><?= htmlspecialchars($user['phone'] ?? '—') ?></span>
                    </div>
                </div>
            </div>

            <div class="profile-card">
                <div class="profile-section-header">
                    <div>
                        <h3>Address Information</h3>
                        <p class="profile-subtitle">Your delivery address</p>
                    </div>
                    <button class="btn-edit-section" id="editAddressBtn">
                        <i class='bx bxs-edit'></i> Edit
                    </button>
                </div>
                <div class="info-grid">
                    <div class="info-field">
                        <span class="info-label">Country</span>
                        <span class="info-value" id="displayCountry"><?= htmlspecialchars($user['country'] ?? '—') ?></span>
                    </div>
                    <div class="info-field">
                        <span class="info-label">City</span>
                        <span class="info-value" id="displayCity"><?= htmlspecialchars($user['city'] ?? '—') ?></span>
                    </div>
                    <div class="info-field">
                        <span class="info-label">Postal Code</span>
                        <span class="info-value" id="displayPostal"><?= htmlspecialchars($user['postal'] ?? '—') ?></span>
                    </div>
                </div>
            </div>

        </div>

        <div class="profile-logout-wrap">
            <button class="btn-logout-bottom" id="logoutBtn">
                <i class='bx bx-log-out'></i> Logout
            </button>
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

    <!-- Modal: Edit Personal Info -->
    <div class="modal-overlay" id="modalPersonal">
        <div class="profile-modal">
            <div class="modal-header">
                <h3>Edit Personal Information</h3>
                <button class="modal-close" data-modal="modalPersonal"><i class='bx bx-x'></i></button>
            </div>
            <div class="modal-body">
                <div class="modal-row">
                    <div class="modal-field">
                        <label>First Name</label>
                        <input type="text" id="inputFirstName" value="<?= htmlspecialchars($firstName) ?>" placeholder="First name">
                    </div>
                    <div class="modal-field">
                        <label>Last Name</label>
                        <input type="text" id="inputLastName" value="<?= htmlspecialchars($lastName) ?>" placeholder="Last name">
                    </div>
                </div>
                <div class="modal-row">
                    <div class="modal-field">
                        <label>Date of Birth</label>
                        <input type="date" id="inputDob" value="<?= htmlspecialchars($user['dob'] ?? '') ?>">
                    </div>
                </div>
                <div class="modal-row">
                    <div class="modal-field">
                        <label>Email Address</label>
                        <input type="email" id="inputEmail" value="<?= htmlspecialchars($user['email'] ?? '') ?>" placeholder="email@example.com">
                    </div>
                    <div class="modal-field">
                        <label>Phone Number</label>
                        <input type="tel" id="inputPhone" value="<?= htmlspecialchars($user['phone'] ?? '') ?>" placeholder="+63 9xx xxx xxxx">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn-modal-cancel" data-modal="modalPersonal">Cancel</button>
                <button class="btn-modal-save" id="savePersonalBtn">Save Changes</button>
            </div>
        </div>
    </div>

    <!-- Modal: Edit Address -->
    <div class="modal-overlay" id="modalAddress">
        <div class="profile-modal">
            <div class="modal-header">
                <h3>Edit Address Information</h3>
                <button class="modal-close" data-modal="modalAddress"><i class='bx bx-x'></i></button>
            </div>
            <div class="modal-body">
                <div class="modal-row">
                    <div class="modal-field">
                        <label>Country</label>
                        <input type="text" id="inputCountry" value="<?= htmlspecialchars($user['country'] ?? '') ?>" placeholder="Country">
                    </div>
                    <div class="modal-field">
                        <label>City</label>
                        <input type="text" id="inputCity" value="<?= htmlspecialchars($user['city'] ?? '') ?>" placeholder="City">
                    </div>
                    <div class="modal-field">
                        <label>Postal Code</label>
                        <input type="text" id="inputPostal" value="<?= htmlspecialchars($user['postal'] ?? '') ?>" placeholder="Postal code">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn-modal-cancel" data-modal="modalAddress">Cancel</button>
                <button class="btn-modal-save" id="saveAddressBtn">Save Changes</button>
            </div>
        </div>
    </div>

    <!-- Modal: Change Password -->
    <div class="modal-overlay" id="modalPassword">
        <div class="profile-modal">
            <div class="modal-header">
                <h3>Change Password</h3>
                <button class="modal-close" data-modal="modalPassword"><i class='bx bx-x'></i></button>
            </div>
            <div class="modal-body">
                <div class="modal-row">
                    <div class="modal-field">
                        <label>Current Password</label>
                        <input type="password" id="inputCurrentPw" placeholder="Current password">
                    </div>
                </div>
                <div class="modal-row">
                    <div class="modal-field">
                        <label>New Password</label>
                        <input type="password" id="inputNewPw" placeholder="New password">
                    </div>
                    <div class="modal-field">
                        <label>Confirm New Password</label>
                        <input type="password" id="inputConfirmPw" placeholder="Confirm new password">
                    </div>
                </div>
                <p class="pw-error" id="pwError"></p>
            </div>
            <div class="modal-footer">
                <button class="btn-modal-cancel" data-modal="modalPassword">Cancel</button>
                <button class="btn-modal-save" id="savePasswordBtn">Update Password</button>
            </div>
        </div>
    </div>

    <!-- Logout Modal -->
    <div class="modal-overlay" id="logoutModal">
        <div class="profile-modal logout-modal">
            <div class="logout-icon"><i class='bx bx-log-out'></i></div>
            <h3>Confirm Logout</h3>
            <p>Are you sure you want to logout?</p>
            <div class="logout-modal-footer">
                <button class="btn-modal-cancel" id="logoutCancelBtn">Cancel</button>
                <a href="logout.php" class="btn-logout-confirm">Confirm</a>
            </div>
        </div>
    </div>

    <script>
        const PROFILE_URL = 'profile.php';
    </script>
    <script src="/BakeryOrderingSystem/JS/cart-badge.js"></script>
    <script src="/BakeryOrderingSystem/JS/navbar.js"></script>
    <script src="/BakeryOrderingSystem/JS/profile.js"></script>
</body>
</html>
