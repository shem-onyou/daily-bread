<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}
require 'db.php';
header('Content-Type: application/json');

// Try full query first; fall back if extra columns don't exist yet
$stmt = $conn->prepare("SELECT name, email, phone, dob, country, city, postal, photo FROM users WHERE id = ?");
if (!$stmt) {
    $stmt = $conn->prepare("SELECT name, email FROM users WHERE id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $user = array_merge(
        ['phone' => null, 'dob' => null, 'country' => null, 'city' => null, 'postal' => null, 'photo' => null],
        $stmt->get_result()->fetch_assoc() ?? []
    );
} else {
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc() ?? [];
}
$stmt->close();

$parts = explode(' ', $user['name'] ?? '', 2);
$user['first_name'] = $parts[0] ?? '';
$user['last_name']  = $parts[1] ?? '';

echo json_encode($user);
?>
