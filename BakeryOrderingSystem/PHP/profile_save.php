<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}
require 'db.php';
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
$type = $data['type'] ?? '';
$uid  = $_SESSION['user_id'];

if ($type === 'personal') {
    $name  = trim(($data['first_name'] ?? '') . ' ' . ($data['last_name'] ?? ''));
    $email = trim($data['email'] ?? '');
    $phone = trim($data['phone'] ?? '');
    $dob   = trim($data['dob']   ?? '');

    // Try full update; fall back to name+email only if extra columns missing
    $stmt = $conn->prepare("UPDATE users SET name=?, email=?, phone=?, dob=? WHERE id=?");
    if (!$stmt) {
        $stmt = $conn->prepare("UPDATE users SET name=?, email=? WHERE id=?");
        $stmt->bind_param("ssi", $name, $email, $uid);
    } else {
        $stmt->bind_param("ssssi", $name, $email, $phone, $dob, $uid);
    }
    $_SESSION['user_name'] = $name;

} elseif ($type === 'address') {
    $country = trim($data['country'] ?? '');
    $city    = trim($data['city']    ?? '');
    $postal  = trim($data['postal']  ?? '');

    $stmt = $conn->prepare("UPDATE users SET country=?, city=?, postal=? WHERE id=?");
    if (!$stmt) {
        echo json_encode(['error' => 'Address columns not set up yet. Please run alter_users.sql in phpMyAdmin.']);
        exit;
    }
    $stmt->bind_param("sssi", $country, $city, $postal, $uid);

} elseif ($type === 'photo') {
    $photo = trim($data['photo'] ?? '') ?: null;

    $stmt = $conn->prepare("UPDATE users SET photo=? WHERE id=?");
    if (!$stmt) {
        echo json_encode(['error' => 'Photo column not set up yet. Please run alter_users.sql in phpMyAdmin.']);
        exit;
    }
    $stmt->bind_param("si", $photo, $uid);

} else {
    echo json_encode(['error' => 'Invalid type']);
    exit;
}

echo $stmt->execute() ? json_encode(['success' => true]) : json_encode(['error' => $conn->error]);
$stmt->close();
?>
