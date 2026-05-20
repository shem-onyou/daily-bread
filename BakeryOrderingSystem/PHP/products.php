<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}
require 'db.php';
header('Content-Type: application/json');

$result   = $conn->query("SELECT id, name, stock, price, image FROM products ORDER BY name ASC");
$products = [];
while ($row = $result->fetch_assoc()) {
    $products[] = $row;
}
echo json_encode($products);
?>
