<?php
// ============================================================
// API: dashboard.php
// Returns summary stats, low stock alerts, expiry warnings
// ============================================================

require_once '../config/db.php';
require_once '../config/auth_middleware.php';

// Dashboard requires authentication
requireAuth();

$conn = getConnection();

// Total medicines
$total = $conn->query("SELECT COUNT(*) AS cnt FROM medicines WHERE status='active'")->fetch_assoc()['cnt'];

// Low stock (at or below reorder level)
$lowStock = $conn->query("
    SELECT id, name, stock_quantity, reorder_level, unit
    FROM medicines
    WHERE stock_quantity <= reorder_level AND status = 'active'
    ORDER BY stock_quantity ASC
")->fetch_all(MYSQLI_ASSOC);

// Expiring within 90 days
$expiringSoon = $conn->query("
    SELECT id, name, stock_quantity, unit, expiry_date
    FROM medicines
    WHERE expiry_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 90 DAY)
      AND status = 'active'
    ORDER BY expiry_date ASC
")->fetch_all(MYSQLI_ASSOC);

// Already expired
$expired = $conn->query("
    SELECT id, name, stock_quantity, unit, expiry_date
    FROM medicines
    WHERE expiry_date < CURDATE() AND status = 'active'
    ORDER BY expiry_date ASC
")->fetch_all(MYSQLI_ASSOC);

// Total stock value
$value = $conn->query("SELECT SUM(stock_quantity * unit_price) AS total FROM medicines WHERE status='active'")->fetch_assoc()['total'];

// Recent transactions (last 5)
$recentTx = $conn->query("
    SELECT t.type, t.quantity, t.notes, t.transaction_date, m.name AS medicine_name, m.unit
    FROM stock_transactions t
    JOIN medicines m ON t.medicine_id = m.id
    ORDER BY t.transaction_date DESC
    LIMIT 5
")->fetch_all(MYSQLI_ASSOC);

echo json_encode([
    'success'        => true,
    'total_medicines' => (int)$total,
    'total_value'    => round((float)$value, 2),
    'low_stock'      => $lowStock,
    'expiring_soon'  => $expiringSoon,
    'expired'        => $expired,
    'recent_transactions' => $recentTx,
    'alert_count'    => count($lowStock) + count($expiringSoon) + count($expired)
]);

$conn->close();
