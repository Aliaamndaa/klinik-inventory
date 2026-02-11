<?php
// ============================================================
// API: transactions.php
// Handles stock IN / OUT / ADJUSTMENT and updates stock qty
// ============================================================

require_once '../config/db.php';
require_once '../config/auth_middleware.php';

// All transaction endpoints require authentication
requireAuth();

$conn   = getConnection();
$method = $_SERVER['REQUEST_METHOD'];
$id     = isset($_GET['id']) ? (int)$_GET['id'] : null;

switch ($method) {

    // GET all transactions, optionally filtered by ?medicine_id=
    case 'GET':
        $sql = "
            SELECT t.*, m.name AS medicine_name, m.unit
            FROM stock_transactions t
            JOIN medicines m ON t.medicine_id = m.id
        ";
        $params = [];
        $types  = '';

        if ($id) {
            $sql .= " WHERE t.medicine_id = ?";
            $params[] = $id;
            $types    = 'i';
        }
        $sql .= " ORDER BY t.transaction_date DESC LIMIT 200";

        $stmt = $conn->prepare($sql);
        if ($types) $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
        $data   = [];
        while ($row = $result->fetch_assoc()) $data[] = $row;
        echo json_encode(['success' => true, 'data' => $data]);
        break;

    // POST - record a transaction and update stock
    case 'POST':
        $body = json_decode(file_get_contents('php://input'), true);
        $required = ['medicine_id', 'type', 'quantity'];
        foreach ($required as $f) {
            if (!isset($body[$f]) || $body[$f] === '') {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => "Field '$f' is required."]);
                exit();
            }
        }

        $qty  = (int)$body['quantity'];
        $type = $body['type'];
        $mid  = (int)$body['medicine_id'];

        // Update stock quantity
        if ($type === 'in') {
            $updateSql = "UPDATE medicines SET stock_quantity = stock_quantity + ? WHERE id = ?";
        } elseif ($type === 'out') {
            // Prevent negative stock
            $check = $conn->prepare("SELECT stock_quantity FROM medicines WHERE id = ?");
            $check->bind_param('i', $mid);
            $check->execute();
            $current = $check->get_result()->fetch_assoc()['stock_quantity'];
            if ($current < $qty) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => "Insufficient stock. Available: $current"]);
                exit();
            }
            $updateSql = "UPDATE medicines SET stock_quantity = stock_quantity - ? WHERE id = ?";
        } else {
            // adjustment: set to exact value
            $updateSql = "UPDATE medicines SET stock_quantity = ? WHERE id = ?";
        }

        $conn->begin_transaction();
        try {
            $upd = $conn->prepare($updateSql);
            $upd->bind_param('ii', $qty, $mid);
            $upd->execute();

            $ins = $conn->prepare("
                INSERT INTO stock_transactions (medicine_id, type, quantity, notes)
                VALUES (?, ?, ?, ?)
            ");
            $ins->bind_param('isis', $mid, $type, $qty, $body['notes'] ?? null);
            $ins->execute();

            $conn->commit();

            // Return updated stock
            $res = $conn->prepare("SELECT stock_quantity, reorder_level FROM medicines WHERE id = ?");
            $res->bind_param('i', $mid);
            $res->execute();
            $updated = $res->get_result()->fetch_assoc();

            echo json_encode([
                'success'       => true,
                'message'       => 'Transaction recorded.',
                'new_stock'     => $updated['stock_quantity'],
                'needs_reorder' => $updated['stock_quantity'] <= $updated['reorder_level']
            ]);
        } catch (Exception $e) {
            $conn->rollback();
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Transaction failed: ' . $e->getMessage()]);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
}

$conn->close();
