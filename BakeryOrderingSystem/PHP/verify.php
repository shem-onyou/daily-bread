<?php
session_start();
require 'db.php';

$token = trim($_GET['token'] ?? '');

if (!$token) {
    header("Location: login.php?error=Invalid+verification+link.");
    exit;
}

// Find a matching unverified user whose token has not expired
$stmt = $conn->prepare("
    SELECT id, name FROM users
    WHERE verify_token = ?
      AND is_verified  = 0
      AND token_expiry > NOW()
");
$stmt->bind_param("s", $token);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$user) {
    header("Location: login.php?error=Verification+link+is+invalid+or+has+expired.+Please+sign+up+again.");
    exit;
}

// Mark account as verified and clear the token
$upd = $conn->prepare("
    UPDATE users
    SET is_verified  = 1,
        verify_token = NULL,
        token_expiry = NULL
    WHERE id = ?
");
$upd->bind_param("i", $user['id']);
$upd->execute();
$upd->close();

header("Location: login.php?success=Email+verified+successfully!+You+can+now+log+in.");
exit;
?>
