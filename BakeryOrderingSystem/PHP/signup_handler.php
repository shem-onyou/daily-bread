<?php
session_start();
require 'db.php';
require 'mailer.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: signup.php");
    exit;
}

// Step 1 data from session
$name     = $_SESSION['signup_name']     ?? '';
$email    = $_SESSION['signup_email']    ?? '';
$password = $_SESSION['signup_password'] ?? '';

// Step 2 data from POST
$firstName = trim($_POST['first_name'] ?? '');
$lastName  = trim($_POST['last_name']  ?? '');
$dob       = trim($_POST['dob']        ?? '') ?: null;
$phone     = trim($_POST['phone']      ?? '');
$country   = trim($_POST['country']    ?? '');
$city      = trim($_POST['city']       ?? '');
$postal    = trim($_POST['postal']     ?? '');

// Guard: step 1 must exist
if (!$name || !$email || !$password) {
    header("Location: signup.php?error=Session+expired.+Please+start+again.");
    exit;
}

// Guard: step 2 required fields
if (!$firstName || !$lastName || !$dob || !$phone || !$country || !$city || !$postal) {
    header("Location: signup_personal.php?error=Please+fill+in+all+fields");
    exit;
}

$fullName = trim("$firstName $lastName");
$hashed   = password_hash($password, PASSWORD_DEFAULT);

// Generate a secure 64-char token valid for 24 hours
$token  = bin2hex(random_bytes(32));
$expiry = date('Y-m-d H:i:s', strtotime('+24 hours'));

$stmt = $conn->prepare("
    INSERT INTO users
        (name, email, password, role, phone, dob, country, city, postal,
         is_verified, verify_token, token_expiry)
    VALUES (?, ?, ?, 'customer', ?, ?, ?, ?, ?, 0, ?, ?)
");
$stmt->bind_param(
    "ssssssssss",
    $fullName, $email, $hashed,
    $phone, $dob, $country, $city, $postal,
    $token, $expiry
);

if (!$stmt->execute()) {
    header("Location: signup.php?error=Email+already+exists.+Please+use+a+different+email.");
    $stmt->close();
    exit;
}
$stmt->close();

// Clear signup session data
unset($_SESSION['signup_name'], $_SESSION['signup_email'], $_SESSION['signup_password']);

// Send verification email
$sent = sendVerificationEmail($email, $firstName, $token);

if ($sent) {
    header("Location: login.php?success=Account+created.+Please+check+your+email+to+verify+your+account.");
} else {
    // Account created but email failed â€” let them know
    header("Location: login.php?success=Account+created+but+verification+email+could+not+be+sent.+Contact+support.");
}
exit;
?>
