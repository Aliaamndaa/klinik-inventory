<?php
// ============================================================
// API: medicines.php
// Handles: GET all, GET one, POST (add), PUT (update), DELETE
// ============================================================

require_once '../config/db.php';
require_once '../config/auth_middleware.php';

// All endpoints require authentication
requireAuth();

// DELETE requires admin role
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    requireAdmin();
}

$conn   = getConnection();
$method = $_SERVER['REQUEST_METHOD'];
$id     = isset($_GET['id']) ? (int)$_GET['id'] : null;

switch ($method) {

    // --------------------------------------------------------
    // GET - fetch all medicines OR single by ?id=
    // --------------------------------------------------------
    case 'GET':
        if ($id) {
            $stmt = $conn->prepare("
                SELECT m.*, c.name AS category_name, s.name AS supplier_name
                FROM medicines m
                LEFT JOIN categories c ON m.category_id = c.id
                LEFT JOIN suppliers s ON m.supplier_id = s.id
                WHERE m.id = ?
            ");
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $result = $stmt->get_result()->fetch_assoc();
            echo json_encode($result ?? ['success' => false, 'message' => 'Not found']);
        } else {
            // Optional filters: ?search=&category=&status=low_stock|expiring
            $where  = ['1=1'];
            $params = [];
            $types  = '';

            if (!empty($_GET['search'])) {
                $where[]  = '(m.name LIKE ? OR m.generic_name LIKE ?)';
                $keyword  = '%' . $_GET['search'] . '%';
                $params[] = $keyword;
                $params[] = $keyword;
                $types   .= 'ss';
            }
            if (!empty($_GET['category'])) {
                $where[]  = 'm.category_id = ?';
                $params[] = (int)$_GET['category'];
                $types   .= 'i';
            }
            if (!empty($_GET['status'])) {
                if ($_GET['status'] === 'low_stock') {
                    $where[] = 'm.stock_quantity <= m.reorder_level';
                } elseif ($_GET['status'] === 'expiring') {
                    $where[] = 'm.expiry_date <= DATE_ADD(CURDATE(), INTERVAL 90 DAY) AND m.expiry_date >= CURDATE()';
                } elseif ($_GET['status'] === 'expired') {
                    $where[] = 'm.expiry_date < CURDATE()';
                }
            }

            $sql = "
                SELECT m.*, c.name AS category_name, s.name AS supplier_name,
                       CASE
                           WHEN m.expiry_date < CURDATE() THEN 'expired'
                           WHEN m.expiry_date <= DATE_ADD(CURDATE(), INTERVAL 90 DAY) THEN 'expiring_soon'
                           ELSE 'ok'
                       END AS expiry_status,
                       CASE
                           WHEN m.stock_quantity <= m.reorder_level THEN 1
                           ELSE 0
                       END AS needs_reorder
                FROM medicines m
                LEFT JOIN categories c ON m.category_id = c.id
                LEFT JOIN suppliers s ON m.supplier_id = s.id
                WHERE " . implode(' AND ', $where) . "
                ORDER BY m.name ASC
            ";

            $stmt = $conn->prepare($sql);
            if ($types && $params) {
                $stmt->bind_param($types, ...$params);
            }
            $stmt->execute();
            $result = $stmt->get_result();
            $data   = [];
            while ($row = $result->fetch_assoc()) {
                $data[] = $row;
            }
            echo json_encode(['success' => true, 'data' => $data, 'total' => count($data)]);
        }
        break;

    // --------------------------------------------------------
    // POST - add new medicine
    // --------------------------------------------------------
    case 'POST':
        $body = json_decode(file_get_contents('php://input'), true);
        $required = ['name', 'unit', 'stock_quantity', 'reorder_level'];
        foreach ($required as $field) {
            if (!isset($body[$field]) || $body[$field] === '') {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => "Field '$field' is required."]);
                exit();
            }
        }

        $stmt = $conn->prepare("
            INSERT INTO medicines (name, generic_name, category_id, supplier_id, unit,
                                   stock_quantity, reorder_level, unit_price, expiry_date, location, description)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param(
            'ssiiisidss​s',
            $body['name'],
            $body['generic_name'] ?? null,
            $body['category_id'] ?? null,
            $body['supplier_id'] ?? null,
            $body['unit'],
            $body['stock_quantity'],
            $body['reorder_level'],
            $body['unit_price'] ?? 0.00,
            $body['expiry_date'] ?? null,
            $body['location'] ?? null,
            $body['description'] ?? null
        );

        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Medicine added.', 'id' => $conn->insert_id]);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Failed to add medicine.']);
        }
        break;

    // --------------------------------------------------------
    // PUT - update medicine by ?id=
    // --------------------------------------------------------
    case 'PUT':
        if (!$id) { http_response_code(400); echo json_encode(['success'=>false,'message'=>'ID required']); exit(); }
        $body = json_decode(file_get_contents('php://input'), true);

        $stmt = $conn->prepare("
            UPDATE medicines SET
                name = ?, generic_name = ?, category_id = ?, supplier_id = ?,
                unit = ?, stock_quantity = ?, reorder_level = ?, unit_price = ?,
                expiry_date = ?, location = ?, description = ?, status = ?
            WHERE id = ?
        ");
        $stmt->bind_param(
            'ssiisiiidssssi',
            $body['name'],
            $body['generic_name'],
            $body['category_id'],
            $body['supplier_id'],
            $body['unit'],
            $body['stock_quantity'],
            $body['reorder_level'],
            $body['unit_price'],
            $body['expiry_date'],
            $body['location'],
            $body['description'],
            $body['status'] ?? 'active',
            $id
        );

        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Medicine updated.']);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Update failed.']);
        }
        break;

    // --------------------------------------------------------
    // DELETE - remove medicine by ?id=
    // --------------------------------------------------------
    case 'DELETE':
        if (!$id) { http_response_code(400); echo json_encode(['success'=>false,'message'=>'ID required']); exit(); }
        $stmt = $conn->prepare("DELETE FROM medicines WHERE id = ?");
        $stmt->bind_param('i', $id);
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Medicine deleted.']);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Delete failed.']);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
}

$conn->close();
