<?php
// ============================================================
// API: transactions.php
// Handles: Stock in, stock out, adjustments
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
$method = $_SERVER['REQUEST_METHOD'];
$medicine_id = isset($_GET['medicine_id']) ? (int)$_GET['medicine_id'] : null;

switch ($method) {
    
    // --------------------------------------------------------
    // GET - fetch transactions
    // --------------------------------------------------------
    case 'GET':
        if ($medicine_id) {
            // Get transactions for specific medicine
            $stmt = $conn->prepare("
                SELECT t.*, m.name as medicine_name
                FROM stock_transactions t
                LEFT JOIN medicines m ON t.medicine_id = m.id
                WHERE t.medicine_id = ?
                ORDER BY t.transaction_date DESC
            ");
            $stmt->bind_param('i', $medicine_id);
            $stmt->execute();
            $result = $stmt->get_result();
        } else {
            // Get all transactions
            $result = $conn->query("
                SELECT t.*, m.name as medicine_name
                FROM stock_transactions t
                LEFT JOIN medicines m ON t.medicine_id = m.id
                ORDER BY t.transaction_date DESC
                LIMIT 100
            ");
        }
        
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        echo json_encode(['success' => true, 'data' => $data]);
        break;

    // --------------------------------------------------------
    // POST - record new transaction
    // --------------------------------------------------------
    case 'POST':
        $body = json_decode(file_get_contents('php://input'), true);
        
        // Validate required fields
        $required = ['medicine_id', 'transaction_type', 'quantity'];
        foreach ($required as $field) {
            if (!isset($body[$field]) || $body[$field] === '') {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => "Field '$field' is required"]);
                exit();
            }
        }

        // Validate transaction type
        $valid_types = ['stock_in', 'stock_out', 'adjustment'];
        if (!in_array($body['transaction_type'], $valid_types)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid transaction type']);
            exit();
        }

        // Start transaction
        $conn->begin_transaction();

        try {
            // Get current stock
            $stmt = $conn->prepare("SELECT stock_quantity FROM medicines WHERE id = ?");
            $stmt->bind_param('i', $body['medicine_id']);
            $stmt->execute();
            $result = $stmt->get_result();
            $medicine = $result->fetch_assoc();

            if (!$medicine) {
                throw new Exception('Medicine not found');
            }

            $current_stock = $medicine['stock_quantity'];
            $quantity = (int)$body['quantity'];
            
            // Calculate new stock based on transaction type
            switch ($body['transaction_type']) {
                case 'stock_in':
                    $new_stock = $current_stock + $quantity;
                    break;
                case 'stock_out':
                    if ($current_stock < $quantity) {
                        throw new Exception('Insufficient stock');
                    }
                    $new_stock = $current_stock - $quantity;
                    break;
                case 'adjustment':
                    // For adjustment, quantity can be positive or negative
                    $new_stock = $current_stock + $quantity;
                    if ($new_stock < 0) {
                        throw new Exception('Adjustment would result in negative stock');
                    }
                    break;
            }

            // Update medicine stock
            $stmt = $conn->prepare("UPDATE medicines SET stock_quantity = ? WHERE id = ?");
            $stmt->bind_param('ii', $new_stock, $body['medicine_id']);
            $stmt->execute();

            // Record transaction
            $stmt = $conn->prepare("
                INSERT INTO stock_transactions 
                (medicine_id, transaction_type, quantity, reference_number, notes, performed_by)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            
            $performed_by = $_SESSION['username'] ?? 'system';
            
            $stmt->bind_param(
                'ississ',
                $body['medicine_id'],
                $body['transaction_type'],
                $quantity,
                $body['reference_number'] ?? null,
                $body['notes'] ?? null,
                $performed_by
            );
            $stmt->execute();

            $conn->commit();

            echo json_encode([
                'success' => true,
                'message' => 'Transaction recorded successfully',
                'transaction_id' => $conn->insert_id,
                'new_stock' => $new_stock
            ]);

        } catch (Exception $e) {
            $conn->rollback();
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
}

$conn->close();