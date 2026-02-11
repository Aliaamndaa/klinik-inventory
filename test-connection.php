<?php
// ============================================================
// TEST FILE: test-connection.php
// Place this in: backend/api/test-connection.php
// Then visit: http://localhost/klinik-inventory/backend/api/test-connection.php
// ============================================================

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

echo json_encode([
    'status' => 'success',
    'message' => 'PHP is working!',
    'php_version' => phpversion(),
    'timestamp' => date('Y-m-d H:i:s')
]);

// Test database connection
try {
    $conn = new mysqli('localhost', 'root', '', 'klinik_azhar_db');
    
    if ($conn->connect_error) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Database connection failed: ' . $conn->connect_error
        ]);
        exit();
    }
    
    echo json_encode([
        'status' => 'success',
        'message' => 'Database connected successfully!',
        'database' => 'klinik_azhar_db',
        'php_version' => phpversion()
    ]);
    
    $conn->close();
    
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
