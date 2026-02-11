<?php
// ============================================================
// Klinik Dr. Azhar - Database Configuration
// ============================================================

define('DB_HOST', 'localhost');
define('DB_USER', 'root');        // Change to your MySQL username
define('DB_PASS', '');            // Change to your MySQL password
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

// FIXED: Headers removed from here to avoid conflicts
// Each API file should set its own headers AFTER including this file