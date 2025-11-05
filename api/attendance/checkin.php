<?php
/**
 * API for employee check-in functionality
 * Handles POST requests to record employee check-in
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

session_start();

// Simulate current employee (in real app, this would come from authentication)
$employee_id = isset($_SESSION['employee_id']) ? $_SESSION['employee_id'] : 1;
$employee_name = isset($_SESSION['employee_name']) ? $_SESSION['employee_name'] : 'John Doe';

// Get POST data
$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['employee_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request data'
    ]);
    exit();
}

// Validate employee ID
if ($input['employee_id'] != $employee_id) {
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized access'
    ]);
    exit();
}

// Database connection (simulate - replace with actual DB)
$host = 'localhost';
$dbname = 'attendance_system';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // For demo purposes, we'll simulate success
    $pdo = null;
}

if ($pdo) {
    try {
        // Check if already checked in today
        $stmt = $pdo->prepare("SELECT * FROM attendance WHERE employee_id = ? AND DATE(check_in_time) = CURDATE() AND check_out_time IS NULL");
        $stmt->execute([$employee_id]);
        
        if ($stmt->fetch()) {
            echo json_encode([
                'success' => false,
                'message' => 'You are already checked in today'
            ]);
            exit();
        }
        
        // Insert new attendance record
        $stmt = $pdo->prepare("
            INSERT INTO attendance (employee_id, check_in_time, status, created_at) 
            VALUES (?, NOW(), 'present', NOW())
        ");
        
        $stmt->execute([
            $employee_id,
            $input['timestamp'] ?? date('Y-m-d H:i:s')
        ]);
        
        $record_id = $pdo->lastInsertId();
        
        echo json_encode([
            'success' => true,
            'message' => 'Successfully checked in',
            'record_id' => $record_id,
            'check_in_time' => date('Y-m-d H:i:s')
        ]);
        
    } catch (PDOException $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Database error: ' . $e->getMessage()
        ]);
    }
} else {
    // Simulate successful check-in for demo
    echo json_encode([
        'success' => true,
        'message' => 'Successfully checked in',
        'record_id' => rand(1000, 9999),
        'check_in_time' => date('Y-m-d H:i:s'),
        'demo_mode' => true
    ]);
}
?>