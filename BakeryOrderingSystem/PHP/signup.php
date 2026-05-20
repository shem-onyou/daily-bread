<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header("Location: home.php");
    exit;
}

// Handle POST from this form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = trim($_POST['name']             ?? '');
    $email    = trim($_POST['email']            ?? '');
    $password = $_POST['password']              ?? '';
    $confirm  = $_POST['confirm-password']      ?? '';

    if (!$name || !$email || !$password || !$confirm) {
        header("Location: signup.php?error=Please+fill+in+all+fields");
        exit;
    }
    if (strlen($password) < 8 || !preg_match('/[A-Z]/', $password) || !preg_match('/[a-z]/', $password) || !preg_match('/[0-9]/', $password) || !preg_match('/[^A-Za-z0-9]/', $password)) {
        header("Location: signup.php?error=Password+must+be+at+least+8+characters+and+include+uppercase,+lowercase,+a+number,+and+a+special+character");
        exit;
    }
    if ($password !== $confirm) {
        header("Location: signup.php?error=Passwords+do+not+match");
        exit;
    }

    // Store step 1 data in session, move to step 2
    $_SESSION['signup_name']     = $name;
    $_SESSION['signup_email']    = $email;
    $_SESSION['signup_password'] = $password;

    header("Location: signup_personal.php");
    exit;
}

$error = htmlspecialchars($_GET['error'] ?? '');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - DailyBread</title>
    <link rel="stylesheet" href="/BakeryOrderingSystem/CSS/auth.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css">
</head>
<body>

    <section class="auth-section">
        <div class="auth-container">

            <h2>Create Your Account</h2>

            <?php if ($error): ?>
                <p class="auth-error"><?= $error ?></p>
            <?php endif; ?>

            <form class="auth-form" action="signup.php" method="POST" autocomplete="off">
                <div class="form-group">
                    <label for="name">Username</label>
                    <input type="text" id="name" name="name"
                        autocomplete="off" required>
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email"
                        autocomplete="off" required>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="pw-wrap">
                        <input type="password" id="password" name="password" placeholder="Min 8 chars, A-Z, a-z, 0-9, symbol" required>
                        <button type="button" class="pw-toggle" aria-label="Toggle password visibility" onclick="togglePw('password', this)"><i class='bx bx-hide'></i></button>
                    </div>
                    <p class="pw-hint" id="pwHint"></p>
                </div>
                <div class="form-group">
                    <label for="confirm-password">Confirm Password</label>
                    <div class="pw-wrap">
                        <input type="password" id="confirm-password" name="confirm-password" required>
                        <button type="button" class="pw-toggle" aria-label="Toggle password visibility" onclick="togglePw('confirm-password', this)"><i class='bx bx-hide'></i></button>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">Next</button>
            </form>

            <p class="auth-link">Already have an account? <a href="login.php">Login</a></p>
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

        function validatePassword(val) {
            if (!val) return '';
            const rules = [
                [val.length >= 8,          'At least 8 characters'],
                [/[A-Z]/.test(val),        'One uppercase letter'],
                [/[a-z]/.test(val),        'One lowercase letter'],
                [/[0-9]/.test(val),        'One number'],
                [/[^A-Za-z0-9]/.test(val), 'One special character'],
            ];
            const failed = rules.filter(r => !r[0]).map(r => r[1]);
            return failed.length ? 'Missing: ' + failed.join(', ') : '';
        }

        const pwInput = document.getElementById('password');
        const pwHint  = document.getElementById('pwHint');

        pwInput.addEventListener('input', () => {
            const msg = validatePassword(pwInput.value);
            pwHint.textContent   = msg;
            pwHint.className     = 'pw-hint ' + (msg ? 'pw-hint--error' : 'pw-hint--ok');
            if (!msg && pwInput.value) pwHint.textContent = 'Password looks good!';
        });

        document.querySelector('.auth-form').addEventListener('submit', (e) => {
            const msg = validatePassword(pwInput.value);
            if (msg) {
                e.preventDefault();
                pwHint.textContent = msg;
                pwHint.className   = 'pw-hint pw-hint--error';
                pwInput.focus();
            }
        });
    </script>
</body>
</html>
