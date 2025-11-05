<?php
/**
 * Session Extension Endpoint
 * Allows users to extend their session timeout
 */

define('AUTH_SYSTEM', true);
require_once __DIR__ . '/../includes/auth.php';

header('Content-Type: application/json');

// Initialize session
initialize_session();

// Check if user is logged in
if (!is_logged_in()) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit();
}

// Extend session timeout
$_SESSION['last_activity'] = time();

echo json_encode(['success' => true, 'message' => 'Session extended']);
?>