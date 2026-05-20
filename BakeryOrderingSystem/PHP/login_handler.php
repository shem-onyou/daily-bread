<?php
session_start();
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: login.php");
    exit;
}

$email    = trim($_POST['email']    ?? '');
$password = $_POST['password']      ?? '';
$role     = trim($_POST['role']     ?? '');

// Fetch is_verified alongside credentials
$stmt = $conn->prepare("SELECT id, name, password, role, is_verified FROM users WHERE email = ? AND role = ?");
$stmt->bind_param("ss", $email, $role);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if ($user && password_verify($password, $user['password'])) {

    // Block login if email not yet verified (customers only â€” admin is always verified)
    if ($user['role'] === 'customer' && !$user['is_verified']) {
        header("Location: login.php?error=Please+verify+your+email+before+logging+in.+Check+your+inbox.");
        exit;
    }

    $_SESSION['user_id']   = $user['id'];
    $_SESSION['user_name'] = $user['name'];
    $_SESSION['role']      = $user['role'];

    if (isset($_POST['remember-me'])) {
        $expire = time() + (30 * 24 * 60 * 60); // 30 days
        setcookie('remember_email', $email, $expire, '/', '', false, true);
        setcookie('remember_role',  $role,  $expire, '/', '', false, true);
    } else {
        setcookie('remember_email', '', time() - 3600, '/');
        setcookie('remember_role',  '', time() - 3600, '/');
    }

    header("Location: " . ($role === 'admin' ? 'admin-dashboard.php' : 'home.php'));
} else {
    header("Location: login.php?error=Invalid+credentials");
}
exit;
?>
