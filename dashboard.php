<?php
// ============================================================
// API: dashboard.php
// Provides: Summary statistics, alerts, recent activity
// ============================================================

require_once 'db.php';
require_once 'auth_middleware.php';

// FIXED: Set headers AFTER requiring db.php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// All endpoints require authentication
requireAuth();

$conn = getConnection();

// Get summary statistics
$stats = [];

// Total medicines count
$result = $conn->query("SELECT COUNT(*) as total FROM medicines WHERE status = 'active'");
$stats['total_medicines'] = $result->fetch_assoc()['total'];

// Low stock alerts
$result = $conn->query("SELECT COUNT(*) as count FROM medicines WHERE stock_quantity <= reorder_level AND status = 'active'");
$stats['low_stock_count'] = $result->fetch_assoc()['count'];

// Expiring soon (within 90 days)
$result = $conn->query("
    SELECT COUNT(*) as count 
    FROM medicines 
    WHERE expiry_date <= DATE_ADD(CURDATE(), INTERVAL 90 DAY) 
    AND expiry_date >= CURDATE()
    AND status = 'active'
");
$stats['expiring_soon_count'] = $result->fetch_assoc()['count'];

// Already expired
$result = $conn->query("
    SELECT COUNT(*) as count 
    FROM medicines 
    WHERE expiry_date < CURDATE()
    AND status = 'active'
");
$stats['expired_count'] = $result->fetch_assoc()['count'];

// Total inventory value
$result = $conn->query("
    SELECT SUM(stock_quantity * unit_price) as total_value 
    FROM medicines 
    WHERE status = 'active'
");
$stats['total_inventory_value'] = (float)($result->fetch_assoc()['total_value'] ?? 0);

// Recent transactions (last 10)
$result = $conn->query("
    SELECT t.*, m.name as medicine_name
    FROM stock_transactions t
    LEFT JOIN medicines m ON t.medicine_id = m.id
    ORDER BY t.transaction_date DESC
    LIMIT 10
");
$recent_transactions = [];
while ($row = $result->fetch_assoc()) {
    $recent_transactions[] = $row;
}

// Low stock items
$result = $conn->query("
    SELECT m.*, c.name as category_name
    FROM medicines m
    LEFT JOIN categories c ON m.category_id = c.id
    WHERE m.stock_quantity <= m.reorder_level
    AND m.status = 'active'
    ORDER BY m.stock_quantity ASC
    LIMIT 10
");
$low_stock_items = [];
while ($row = $result->fetch_assoc()) {
    $low_stock_items[] = $row;
}

// Expiring items
$result = $conn->query("
    SELECT m.*, c.name as category_name,
           DATEDIFF(m.expiry_date, CURDATE()) as days_until_expiry
    FROM medicines m
    LEFT JOIN categories c ON m.category_id = c.id
    WHERE m.expiry_date <= DATE_ADD(CURDATE(), INTERVAL 90 DAY)
    AND m.status = 'active'
    ORDER BY m.expiry_date ASC
    LIMIT 10
");
$expiring_items = [];
while ($row = $result->fetch_assoc()) {
    $expiring_items[] = $row;
}

echo json_encode([
    'success' => true,
    'stats' => $stats,
    'recent_transactions' => $recent_transactions,
    'low_stock_items' => $low_stock_items,
    'expiring_items' => $expiring_items
]);

$conn->close();