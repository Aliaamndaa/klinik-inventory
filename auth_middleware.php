<?php
// ============================================================
// Middleware: auth_middleware.php
// Validates user session before accessing protected APIs
// FOR FLAT FOLDER STRUCTURE (all files in same directory)
// ============================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function requireAuth($requiredRole = null) {
    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        http_response_code(401);
        echo json_encode([
            'success' => false, 
            'message' => 'Unauthorized. Please login.',
            'redirect' => 'login.html'
        ]);
        exit();
    }

    // Check role if specified
    if ($requiredRole && $_SESSION['role'] !== $requiredRole) {
        http_response_code(403);
        echo json_encode([
            'success' => false, 
            'message' => 'Access denied. Insufficient permissions.'
        ]);
        exit();
    }

    return true;
}

function requireAdmin() {
    return requireAuth('admin');
}

function getCurrentUser() {
    if (!isset($_SESSION['user_id'])) {
        return null;
    }
    
    return [
        'id' => $_SESSION['user_id'],
        'username' => $_SESSION['username'],
        'full_name' => $_SESSION['full_name'],
        'role' => $_SESSION['role']
    ];
}

function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}
