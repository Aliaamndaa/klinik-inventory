<?php
// ============================================================
// API: categories.php
// Handles: Categories and Suppliers CRUD
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

// DELETE requires admin role
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    requireAdmin();
}

$conn = getConnection();
$method = $_SERVER['REQUEST_METHOD'];
$type = $_GET['type'] ?? 'categories'; // 'categories' or 'suppliers'
$id = isset($_GET['id']) ? (int)$_GET['id'] : null;

// Determine which table to use
$table = ($type === 'suppliers') ? 'suppliers' : 'categories';

switch ($method) {
    case 'GET':
        if ($id) {
            $stmt = $conn->prepare("SELECT * FROM $table WHERE id = ?");
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $result = $stmt->get_result()->fetch_assoc();
            echo json_encode($result ?? ['success' => false, 'message' => 'Not found']);
        } else {
            $result = $conn->query("SELECT * FROM $table ORDER BY name ASC");
            $data = [];
            while ($row = $result->fetch_assoc()) {
                $data[] = $row;
            }
            echo json_encode(['success' => true, 'data' => $data]);
        }
        break;

    case 'POST':
        $body = json_decode(file_get_contents('php://input'), true);
        if (empty($body['name'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Name is required']);
            exit();
        }

        if ($table === 'suppliers') {
            $stmt = $conn->prepare("INSERT INTO suppliers (name, contact_person, phone, email, address) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param('sssss', 
                $body['name'], 
                $body['contact_person'] ?? null,
                $body['phone'] ?? null,
                $body['email'] ?? null,
                $body['address'] ?? null
            );
        } else {
            $stmt = $conn->prepare("INSERT INTO categories (name, description) VALUES (?, ?)");
            $stmt->bind_param('ss', $body['name'], $body['description'] ?? null);
        }

        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => ucfirst($type) . ' added', 'id' => $conn->insert_id]);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Failed to add']);
        }
        break;

    case 'DELETE':
        if (!$id) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'ID required']);
            exit();
        }
        $stmt = $conn->prepare("DELETE FROM $table WHERE id = ?");
        $stmt->bind_param('i', $id);
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => ucfirst($type) . ' deleted']);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Delete failed']);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
}

$conn->close();