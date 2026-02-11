<?php
// ============================================================
// API: auth.php
// Handles: Login, Register, Logout, Session Check
// ============================================================

// Start session FIRST before any output
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Set headers BEFORE requiring db.php to avoid conflicts
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Allow-Credentials: true');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Database connection
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'klinik_azhar_db');

function getConnection() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($conn->connect_error) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Database connection failed: ' . $conn->connect_error]);
        exit();
    }
    $conn->set_charset('utf8mb4');
    return $conn;
}

$conn   = getConnection();
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

// Add error logging
error_reporting(E_ALL);
ini_set('display_errors', 0); // Don't display errors in output
ini_set('log_errors', 1);

switch ($action) {

    // --------------------------------------------------------
    // LOGIN - authenticate user and create session
    // --------------------------------------------------------
    case 'login':
        if ($method !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            exit();
        }

        $body = json_decode(file_get_contents('php://input'), true);
        
        if (empty($body['username']) || empty($body['password'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Username and password are required']);
            exit();
        }

        $stmt = $conn->prepare("SELECT id, username, password, full_name, role FROM users WHERE username = ?");
        $stmt->bind_param('s', $body['username']);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        if (!$user) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Invalid username or password']);
            exit();
        }

        // Verify password
        if (!password_verify($body['password'], $user['password'])) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Invalid username or password']);
            exit();
        }

        // Create session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['full_name'] = $user['full_name'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['login_time'] = time();

        echo json_encode([
            'success' => true,
            'message' => 'Login successful',
            'user' => [
                'id' => $user['id'],
                'username' => $user['username'],
                'full_name' => $user['full_name'],
                'role' => $user['role']
            ]
        ]);
        break;

    // --------------------------------------------------------
    // REGISTER - create new user account
    // --------------------------------------------------------
    case 'register':
        try {
            if ($method !== 'POST') {
                http_response_code(405);
                echo json_encode(['success' => false, 'message' => 'Method not allowed']);
                exit();
            }

            $body = json_decode(file_get_contents('php://input'), true);
            
            // Debug: Log received data (remove in production)
            error_log("Registration attempt - Data received: " . json_encode($body));
            
            // Validation
            $required = ['username', 'password', 'full_name'];
            foreach ($required as $field) {
                if (!isset($body[$field]) || trim($body[$field]) === '') {
                    http_response_code(400);
                    echo json_encode(['success' => false, 'message' => "Field '$field' is required"]);
                    exit();
                }
            }

            // Password strength check
            if (strlen($body['password']) < 6) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Password must be at least 6 characters']);
                exit();
            }

            // Check if username already exists
            $check = $conn->prepare("SELECT id FROM users WHERE username = ?");
            if (!$check) {
                throw new Exception('Database prepare failed: ' . $conn->error);
            }
            
            $check->bind_param('s', $body['username']);
            $check->execute();
            $checkResult = $check->get_result();
            
            if ($checkResult->num_rows > 0) {
                http_response_code(409);
                echo json_encode(['success' => false, 'message' => 'Username already exists']);
                exit();
            }

            // Hash password
            $hashedPassword = password_hash($body['password'], PASSWORD_BCRYPT);
            $role = isset($body['role']) ? $body['role'] : 'staff'; // Default to staff role

            // Check if any users exist
            $countResult = $conn->query("SELECT COUNT(*) as count FROM users");
            if (!$countResult) {
                throw new Exception('Count query failed: ' . $conn->error);
            }
            $userCount = $countResult->fetch_assoc()['count'];
            
            // Only allow admin creation if:
            // 1. No users exist yet (first user can be admin), OR
            // 2. Current user is already an admin
            if ($role === 'admin' && $userCount > 0 && (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin')) {
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'Only admins can create admin accounts. Please select Staff role.']);
                exit();
            }

            // Insert new user
            $stmt = $conn->prepare("INSERT INTO users (username, password, full_name, role) VALUES (?, ?, ?, ?)");
            if (!$stmt) {
                throw new Exception('Insert prepare failed: ' . $conn->error);
            }
            
            $stmt->bind_param('ssss', $body['username'], $hashedPassword, $body['full_name'], $role);

            if ($stmt->execute()) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Registration successful! You can now login.',
                    'user_id' => $conn->insert_id
                ]);
            } else {
                throw new Exception('Insert execution failed: ' . $stmt->error);
            }
            
        } catch (Exception $e) {
            error_log("Registration error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Registration failed: ' . $e->getMessage()]);
        }
        break;

    // --------------------------------------------------------
    // LOGOUT - destroy session
    // --------------------------------------------------------
    case 'logout':
        if ($method !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            exit();
        }

        session_destroy();
        echo json_encode(['success' => true, 'message' => 'Logged out successfully']);
        break;

    // --------------------------------------------------------
    // CHECK - verify session status
    // --------------------------------------------------------
    case 'check':
        if ($method !== 'GET') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            exit();
        }

        if (isset($_SESSION['user_id'])) {
            echo json_encode([
                'success' => true,
                'authenticated' => true,
                'user' => [
                    'id' => $_SESSION['user_id'],
                    'username' => $_SESSION['username'],
                    'full_name' => $_SESSION['full_name'],
                    'role' => $_SESSION['role']
                ]
            ]);
        } else {
            echo json_encode([
                'success' => true,
                'authenticated' => false
            ]);
        }
        break;

    default:
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
}

$conn->close();
