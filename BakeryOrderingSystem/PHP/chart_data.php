<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}
require 'db.php';
header('Content-Type: application/json');

$type = $_GET['type'] ?? '';

if ($type === 'revenue') {
    // Monthly revenue for the current year (completed orders only)
    $year   = (int) date('Y');
    $months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
    $data   = array_fill(0, 12, 0);

    $result = $conn->query("
        SELECT MONTH(o.created_at) AS month,
               COALESCE(SUM(oi.price * oi.qty), 0) AS total
        FROM orders o
        JOIN order_items oi ON oi.order_id = o.id
        WHERE o.status = 'completed'
        AND YEAR(o.created_at) = $year
        GROUP BY MONTH(o.created_at)
    ");

    while ($row = $result->fetch_assoc()) {
        $data[(int)$row['month'] - 1] = (float)$row['total'];
    }

    echo json_encode(['labels' => $months, 'data' => $data]);

} elseif ($type === 'traffic') {
    // Orders placed per day for the current week (Monâ€“Sun)
    $days = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
    $data = array_fill(0, 7, 0);

    $result = $conn->query("
        SELECT DAYOFWEEK(created_at) AS dow, COUNT(*) AS total
        FROM orders
        WHERE created_at >= DATE(NOW()) - INTERVAL WEEKDAY(NOW()) DAY
        AND created_at <  DATE(NOW()) - INTERVAL WEEKDAY(NOW()) DAY + INTERVAL 7 DAY
        GROUP BY DAYOFWEEK(created_at)
    ");

    // MySQL DAYOFWEEK: 1=Sun, 2=Mon ... 7=Sat â€” remap to Mon=0 ... Sun=6
    while ($row = $result->fetch_assoc()) {
        $dow = (int)$row['dow'];
        $idx = ($dow === 1) ? 6 : $dow - 2;
        $data[$idx] = (int)$row['total'];
    }

    echo json_encode(['labels' => $days, 'data' => $data]);

} else {
    echo json_encode(['error' => 'Invalid type. Use ?type=revenue or ?type=traffic']);
}
?>
