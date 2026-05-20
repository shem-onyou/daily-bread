<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - DailyBread Admin</title>
    <link rel="stylesheet" href="/BakeryOrderingSystem/CSS/admin-profile.css">
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
            <h1 class="page-title">Profile</h1>
            <div class="topbar-right">
                <span class="admin-name" id="topbarName"><?= htmlspecialchars($_SESSION['user_name']) ?></span>
                <a href="admin-profile.php" class="avatar" id="topbarAvatar"><?= strtoupper(substr($_SESSION['user_name'], 0, 1)) ?></a>
            </div>
        </header>

        <main class="content">

            <div class="chart-card profile-overview">
                <div class="profile-avatar-wrap">
                    <div class="profile-avatar" id="profileAvatar"><?= strtoupper(substr($_SESSION['user_name'], 0, 1)) ?></div>
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
                    <h2 id="displayFullName"><?= htmlspecialchars($_SESSION['user_name']) ?></h2>
                    <span class="role-badge">Admin</span>
                    <p class="profile-address" id="displayAddress">
                        <i class='bx bx-map'></i>
                        <span id="displayAddressText">&mdash;</span>
                    </p>
                </div>
                <div class="profile-actions">
                    <button class="btn-primary-action" id="changePasswordBtn">
                        <i class='bx bx-lock-alt'></i> Change Password
                    </button>
                </div>
            </div>

            <div class="chart-card">
                <div class="section-header">
                    <div>
                        <h3>Personal Information</h3>
                        <p class="chart-subtitle">Your personal details</p>
                    </div>
                    <button class="btn-edit-section" id="editPersonalBtn">
                        <i class='bx bxs-edit'></i> Edit
                    </button>
                </div>
                <div class="info-grid">
                    <div class="info-field">
                        <span class="info-label">First Name</span>
                        <span class="info-value" id="displayFirstName">&mdash;</span>
                    </div>
                    <div class="info-field">
                        <span class="info-label">Last Name</span>
                        <span class="info-value" id="displayLastName">&mdash;</span>
                    </div>
                    <div class="info-field">
                        <span class="info-label">Date of Birth</span>
                        <span class="info-value" id="displayDob">&mdash;</span>
                    </div>
                    <div class="info-field">
                        <span class="info-label">Email Address</span>
                        <span class="info-value" id="displayEmail">&mdash;</span>
                    </div>
                    <div class="info-field">
                        <span class="info-label">Phone Number</span>
                        <span class="info-value" id="displayPhone">&mdash;</span>
                    </div>
                </div>
            </div>

            <div class="chart-card">
                <div class="section-header">
                    <div>
                        <h3>Address Information</h3>
                        <p class="chart-subtitle">Your location details</p>
                    </div>
                    <button class="btn-edit-section" id="editAddressBtn">
                        <i class='bx bxs-edit'></i> Edit
                    </button>
                </div>
                <div class="info-grid">
                    <div class="info-field">
                        <span class="info-label">Country</span>
                        <span class="info-value" id="displayCountry">&mdash;</span>
                    </div>
                    <div class="info-field">
                        <span class="info-label">City</span>
                        <span class="info-value" id="displayCity">&mdash;</span>
                    </div>
                    <div class="info-field">
                        <span class="info-label">Postal Code</span>
                        <span class="info-value" id="displayPostal">&mdash;</span>
                    </div>
                </div>
            </div>

        </main>
    </div>

    <!-- Modal: Edit Personal Info -->
    <div class="modal-overlay" id="modalPersonal">
        <div class="modal">
            <div class="modal-header">
                <h3>Edit Personal Information</h3>
                <button class="modal-close" data-modal="modalPersonal"><i class='bx bx-x'></i></button>
            </div>
            <div class="modal-body">
                <div class="modal-row">
                    <div class="modal-field">
                        <label>First Name</label>
                        <input type="text" id="inputFirstName" placeholder="First name">
                    </div>
                    <div class="modal-field">
                        <label>Last Name</label>
                        <input type="text" id="inputLastName" placeholder="Last name">
                    </div>
                </div>
                <div class="modal-row">
                    <div class="modal-field">
                        <label>Date of Birth</label>
                        <input type="date" id="inputDob">
                    </div>
                </div>
                <div class="modal-row">
                    <div class="modal-field">
                        <label>Email Address</label>
                        <input type="email" id="inputEmail" placeholder="email@example.com">
                    </div>
                    <div class="modal-field">
                        <label>Phone Number</label>
                        <input type="tel" id="inputPhone" placeholder="+1 555-0000">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn-cancel" data-modal="modalPersonal">Cancel</button>
                <button class="btn-save" id="savePersonalBtn">Save Changes</button>
            </div>
        </div>
    </div>

    <!-- Modal: Edit Address -->
    <div class="modal-overlay" id="modalAddress">
        <div class="modal">
            <div class="modal-header">
                <h3>Edit Address Information</h3>
                <button class="modal-close" data-modal="modalAddress"><i class='bx bx-x'></i></button>
            </div>
            <div class="modal-body">
                <div class="modal-row">
                    <div class="modal-field">
                        <label>Country</label>
                        <input type="text" id="inputCountry" placeholder="Country">
                    </div>
                    <div class="modal-field">
                        <label>City</label>
                        <input type="text" id="inputCity" placeholder="City">
                    </div>
                    <div class="modal-field">
                        <label>Postal Code</label>
                        <input type="text" id="inputPostal" placeholder="Postal code">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn-cancel" data-modal="modalAddress">Cancel</button>
                <button class="btn-save" id="saveAddressBtn">Save Changes</button>
            </div>
        </div>
    </div>

    <!-- Modal: Change Password -->
    <div class="modal-overlay" id="modalPassword">
        <div class="modal">
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
                <button class="btn-cancel" data-modal="modalPassword">Cancel</button>
                <button class="btn-save" id="savePasswordBtn">Update Password</button>
            </div>
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

    <script src="/BakeryOrderingSystem/JS/admin-profile.js"></script>
</body>
</html>
