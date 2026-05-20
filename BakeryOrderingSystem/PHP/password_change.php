<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}
require 'db.php';
header('Content-Type: application/json');

$data    = json_decode(file_get_contents('php://input'), true);
$current = $data['current'] ?? '';
$newPw   = $data['new']     ?? '';
$confirm = $data['confirm'] ?? '';

if (!$current || !$newPw || !$confirm) {
    echo json_encode(['error' => 'All fields are required']); exit;
}
if ($newPw !== $confirm) {
    echo json_encode(['error' => 'New passwords do not match']); exit;
}
if (strlen($newPw) < 6) {
    echo json_encode(['error' => 'Password must be at least 6 characters']); exit;
}

$stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();

if (!password_verify($current, $row['password'])) {
    echo json_encode(['error' => 'Current password is incorrect']); exit;
}

$hashed = password_hash($newPw, PASSWORD_DEFAULT);
$update = $conn->prepare("UPDATE users SET password=? WHERE id=?");
$update->bind_param("si", $hashed, $_SESSION['user_id']);

echo $update->execute() ? json_encode(['success' => true]) : json_encode(['error' => $conn->error]);
?>
