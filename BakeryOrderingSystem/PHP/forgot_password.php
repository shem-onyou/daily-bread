<?php
session_start();
require 'db.php';
require 'mailer.php';

$message = '';
$isError = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');

    $stmt = $conn->prepare("SELECT id, name FROM users WHERE email = ? AND role = 'customer'");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    // Always show success to prevent email enumeration
    $message = "If that email exists, a reset link has been sent.";

    if ($user) {
        $token  = bin2hex(random_bytes(32));
        $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));

        $upd = $conn->prepare("UPDATE users SET reset_token = ?, reset_expiry = ? WHERE id = ?");
        $upd->bind_param("ssi", $token, $expiry, $user['id']);
        $upd->execute();
        $upd->close();

        sendPasswordResetEmail($email, $user['name'], $token);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - DailyBread</title>
    <link rel="stylesheet" href="/BakeryOrderingSystem/CSS/auth.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
</head>
<body>
    <section class="auth-section">
        <div class="auth-container">
            <h2>Forgot Password</h2>
            <p style="color:#777;margin-bottom:1.2rem;font-size:0.95rem;">Enter your email and we'll send you a reset link.</p>

            <?php if ($message): ?>
                <p class="auth-success"><?= htmlspecialchars($message) ?></p>
            <?php endif; ?>

            <form class="auth-form" method="POST">
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" required placeholder="email@example.com">
                </div>
                <button type="submit" class="btn btn-primary">Send Reset Link</button>
            </form>
            <p class="auth-link"><a href="login.php">Back to Login</a></p>
        </div>
    </section>
</body>
</html>
