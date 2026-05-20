<?php
session_start();

// If step 1 was skipped, send back
if (empty($_SESSION['signup_name']) || empty($_SESSION['signup_email'])) {
    header("Location: signup.php");
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
        <div class="auth-container auth-container--wide">

            <h2>Personal Information</h2>
            <p class="auth-subtitle">Tell us a bit about yourself</p>

            <?php if ($error): ?>
                <p class="auth-error"><?= $error ?></p>
            <?php endif; ?>

            <form class="auth-form" action="signup_handler.php" method="POST">

                <div class="form-row">
                    <div class="form-group">
                        <label for="first_name">First Name</label>
                        <input type="text" id="first_name" name="first_name" required>
                    </div>
                    <div class="form-group">
                        <label for="last_name">Last Name</label>
                        <input type="text" id="last_name" name="last_name" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="dob">Date of Birth</label>
                        <input type="date" id="dob" name="dob" required>
                    </div>
                    <div class="form-group">
                        <label for="phone">Phone Number</label>
                        <input type="tel" id="phone" name="phone" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="country">Country</label>
                    <input type="text" id="country" name="country" required>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="city">City</label>
                        <input type="text" id="city" name="city" required>
                    </div>
                    <div class="form-group">
                        <label for="postal">Postal Code</label>
                        <input type="text" id="postal" name="postal"required>
                    </div>
                </div>

                <div class="step-actions">
                    <a href="signup.php" class="btn btn-secondary-outline">Back</a>
                    <button type="submit" class="btn btn-primary">Create Account</button>
                </div>

            </form>

            <p class="auth-link">Already have an account? <a href="login.php">Login</a></p>
        </div>
    </section>

</body>
</html>
