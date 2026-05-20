<?php
session_start();
require 'db.php';

$token = trim($_GET['token'] ?? '');
$error = '';
$success = '';

if (!$token) {
    header("Location: login.php?error=Invalid+reset+link.");
    exit;
}

// Validate token
$stmt = $conn->prepare("SELECT id, reset_token, reset_expiry FROM users WHERE reset_token = ?");
$stmt->bind_param("s", $token);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$user) {
    // Token not found at all — column missing or token was never saved
    header("Location: login.php?error=Reset+link+is+invalid.+Please+request+a+new+one.");
    exit;
}

if (strtotime($user['reset_expiry']) < time()) {
    header("Location: login.php?error=Reset+link+has+expired.+Please+request+a+new+one.");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newPw   = $_POST['password']         ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if (strlen($newPw) < 6) {
        $error = "Password must be at least 6 characters.";
    } elseif ($newPw !== $confirm) {
        $error = "Passwords do not match.";
    } else {
        $hashed = password_hash($newPw, PASSWORD_DEFAULT);
        $upd = $conn->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_expiry = NULL WHERE id = ?");
        $upd->bind_param("si", $hashed, $user['id']);
        $upd->execute();
        $upd->close();

        header("Location: login.php?success=Password+reset+successfully!+You+can+now+log+in.");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - DailyBread</title>
    <link rel="stylesheet" href="/BakeryOrderingSystem/CSS/auth.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
</head>
<body>
    <section class="auth-section">
        <div class="auth-container">
            <h2>Reset Password</h2>

            <?php if ($error): ?>
                <p class="auth-error"><?= htmlspecialchars($error) ?></p>
            <?php endif; ?>

            <form class="auth-form" method="POST">
                <div class="form-group">
                    <label for="password">New Password</label>
                    <div class="pw-wrap">
                        <input type="password" id="password" name="password" required placeholder="Min. 6 characters">
                        <button type="button" class="pw-toggle" onclick="togglePw('password', this)"><i class='bx bx-hide'></i></button>
                    </div>
                </div>
                <div class="form-group">
                    <label for="confirm_password">Confirm Password</label>
                    <div class="pw-wrap">
                        <input type="password" id="confirm_password" name="confirm_password" required placeholder="Repeat password">
                        <button type="button" class="pw-toggle" onclick="togglePw('confirm_password', this)"><i class='bx bx-hide'></i></button>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">Reset Password</button>
            </form>
            <p class="auth-link"><a href="login.php">Back to Login</a></p>
        </div>
    </section>
    <script>
        function togglePw(id, btn) {
            const input = document.getElementById(id);
            const icon  = btn.querySelector('i');
            const show  = input.type === 'password';
            input.type  = show ? 'text' : 'password';
            icon.className = show ? 'bx bx-show' : 'bx bx-hide';
        }
    </script>
</body>
</html>
