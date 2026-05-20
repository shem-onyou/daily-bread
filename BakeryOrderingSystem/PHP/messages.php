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
$name    = trim($data['name']    ?? '');
$email   = trim($data['email']   ?? '');
$message = trim($data['message'] ?? '');
$userId  = $_SESSION['user_id'];

if (!$name || !$email || !$message) {
    echo json_encode(['error' => 'All fields are required']); exit;
}

$stmt = $conn->prepare("INSERT INTO messages (user_id, name, email, message) VALUES (?,?,?,?)");
$stmt->bind_param("isss", $userId, $name, $email, $message);

echo $stmt->execute() ? json_encode(['success' => true]) : json_encode(['error' => $conn->error]);
?>
