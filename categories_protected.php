<?php
// ============================================================
// API: categories.php  (also handles suppliers via ?type=)
// ============================================================

require_once '../config/db.php';
require_once '../config/auth_middleware.php';

// Require authentication for all actions
requireAuth();

// DELETE requires admin role
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    requireAdmin();
}

$conn   = getConnection();
$method = $_SERVER['REQUEST_METHOD'];
$type   = $_GET['type'] ?? 'categories'; // 'categories' or 'suppliers'
$table  = $type === 'suppliers' ? 'suppliers' : 'categories';

switch ($method) {
    case 'GET':
        $result = $conn->query("SELECT * FROM $table ORDER BY name ASC");
        $data   = $result->fetch_all(MYSQLI_ASSOC);
        echo json_encode(['success' => true, 'data' => $data]);
        break;

    case 'POST':
        $body = json_decode(file_get_contents('php://input'), true);
        if (empty($body['name'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Name is required.']);
            exit();
        }
        if ($type === 'suppliers') {
            $stmt = $conn->prepare("INSERT INTO suppliers (name, contact_name, phone, email, address) VALUES (?,?,?,?,?)");
            $stmt->bind_param('sssss',
                $body['name'], $body['contact_name'] ?? null,
                $body['phone'] ?? null, $body['email'] ?? null, $body['address'] ?? null
            );
        } else {
            $stmt = $conn->prepare("INSERT INTO categories (name, description) VALUES (?,?)");
            $stmt->bind_param('ss', $body['name'], $body['description'] ?? null);
        }
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'id' => $conn->insert_id]);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Insert failed.']);
        }
        break;

    case 'DELETE':
        $id = isset($_GET['id']) ? (int)$_GET['id'] : null;
        if (!$id) { http_response_code(400); echo json_encode(['success'=>false,'message'=>'ID required']); exit(); }
        $stmt = $conn->prepare("DELETE FROM $table WHERE id = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        echo json_encode(['success' => true, 'message' => 'Deleted.']);
        break;

    default:
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
}

$conn->close();
