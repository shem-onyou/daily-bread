<?php
// Shared navbar profile button — include after db.php is already required
$_navPhoto = null;
if (isset($conn) && isset($_SESSION['user_id'])) {
    $_navStmt = $conn->prepare("SELECT photo FROM users WHERE id = ?");
    $_navStmt->bind_param("i", $_SESSION['user_id']);
    $_navStmt->execute();
    $_navPhoto = $_navStmt->get_result()->fetch_assoc()['photo'] ?? null;
    $_navStmt->close();
}
?>
<a href="profile.php" class="nav-btn profile-btn">
    <?php if (!empty($_navPhoto)): ?>
        <img src="<?= htmlspecialchars($_navPhoto) ?>" alt="Profile" class="nav-profile-img">
    <?php else: ?>
        <i class='bx bx-user'></i>
    <?php endif; ?>
</a>
