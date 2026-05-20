<?php
session_start();
$cookieEmail = htmlspecialchars($_COOKIE['remember_email'] ?? '');
$cookieRole  = htmlspecialchars($_COOKIE['remember_role']  ?? '');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - DailyBread</title>
    <link rel="stylesheet" href="/BakeryOrderingSystem/CSS/auth.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css">
</head>
<body>

    <section class="auth-section">
        <div class="auth-container">
            <h2>Login to Your Account</h2>

            <?php if (!empty($_GET['error'])): ?>
                <p class="auth-error"><?= htmlspecialchars($_GET['error']) ?></p>
            <?php endif; ?>
            <?php if (!empty($_GET['success'])): ?>
                <p class="auth-success"><?= htmlspecialchars($_GET['success']) ?></p>
            <?php endif; ?>

            <form class="auth-form" action="login_handler.php" method="POST">
                <div class="form-group">
                    <label for="role">Login As</label>
                    <select id="role" name="role" required>
                        <option value="" disabled <?= !$cookieRole ? 'selected' : '' ?>>Select Role</option>
                        <option value="admin" <?= $cookieRole === 'admin' ? 'selected' : '' ?>>Admin</option>
                        <option value="customer" <?= $cookieRole === 'customer' ? 'selected' : '' ?>>Customer</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" value="<?= $cookieEmail ?>" required>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="pw-wrap">
                        <input type="password" id="password" name="password" required>
                        <button type="button" class="pw-toggle" aria-label="Toggle password visibility" onclick="togglePw('password', this)"><i class='bx bx-hide'></i></button>
                    </div>
                </div>
                <div class="form-group remember-me">
                    <input type="checkbox" id="remember-me" name="remember-me" <?= $cookieEmail ? 'checked' : '' ?>>
                    <label for="remember-me">Remember Me</label>
                    <a href="forgot_password.php" class="forgot-password">Forgot Password?</a>
                </div>
                <button type="submit" class="btn btn-primary">Login</button>
            </form>
            <p class="auth-link">Don't have an account? <a href="signup.php">Sign Up</a></p>
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
