<?php
// ============================================================
// API: users.php
// Handles: User management (admin only)
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

// All user management requires admin role
requireAdmin();

$conn = getConnection();
$method = $_SERVER['REQUEST_METHOD'];
$id = isset($_GET['id']) ? (int)$_GET['id'] : null;

switch ($method) {
    
    // --------------------------------------------------------
    // GET - fetch all users or single user
    // --------------------------------------------------------
    case 'GET':
        if ($id) {
            $stmt = $conn->prepare("SELECT id, username, full_name, role, created_at FROM users WHERE id = ?");
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $result = $stmt->get_result()->fetch_assoc();
            echo json_encode($result ?? ['success' => false, 'message' => 'User not found']);
        } else {
            $result = $conn->query("SELECT id, username, full_name, role, created_at FROM users ORDER BY created_at DESC");
            $data = [];
            while ($row = $result->fetch_assoc()) {
                $data[] = $row;
            }
            echo json_encode(['success' => true, 'data' => $data]);
        }
        break;

    // --------------------------------------------------------
    // POST - create new user (handled by auth.php register)
    // --------------------------------------------------------
    case 'POST':
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Use auth.php?action=register to create users']);
        break;

    // --------------------------------------------------------
    // PUT - update user
    // --------------------------------------------------------
    case 'PUT':
        if (!$id) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'ID required']);
            exit();
        }

        $body = json_decode(file_get_contents('php://input'), true);

        // Build update query dynamically based on provided fields
        $updates = [];
        $params = [];
        $types = '';

        if (isset($body['full_name'])) {
            $updates[] = 'full_name = ?';
            $params[] = $body['full_name'];
            $types .= 's';
        }

        if (isset($body['role'])) {
            if (!in_array($body['role'], ['admin', 'staff'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Invalid role']);
                exit();
            }
            $updates[] = 'role = ?';
            $params[] = $body['role'];
            $types .= 's';
        }

        if (isset($body['password']) && !empty($body['password'])) {
            if (strlen($body['password']) < 6) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Password must be at least 6 characters']);
                exit();
            }
            $updates[] = 'password = ?';
            $params[] = password_hash($body['password'], PASSWORD_BCRYPT);
            $types .= 's';
        }

        if (empty($updates)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'No fields to update']);
            exit();
        }

        // Add ID to params
        $params[] = $id;
        $types .= 'i';

        $sql = "UPDATE users SET " . implode(', ', $updates) . " WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param($types, ...$params);

        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'User updated successfully']);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Update failed']);
        }
        break;

    // --------------------------------------------------------
    // DELETE - remove user
    // --------------------------------------------------------
    case 'DELETE':
        if (!$id) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'ID required']);
            exit();
        }

        // Prevent deleting yourself
        if ($id == $_SESSION['user_id']) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Cannot delete your own account']);
            exit();
        }

        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->bind_param('i', $id);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'User deleted successfully']);
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