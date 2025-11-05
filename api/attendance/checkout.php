<?php
/**
 * API for employee check-out functionality
 * Handles POST requests to record employee check-out
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
        // Get today's active attendance record
        $stmt = $pdo->prepare("
            SELECT * FROM attendance 
            WHERE employee_id = ? 
            AND DATE(check_in_time) = CURDATE() 
            AND check_out_time IS NULL 
            ORDER BY check_in_time DESC 
            LIMIT 1
        ");
        $stmt->execute([$employee_id]);
        $record = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$record) {
            echo json_encode([
                'success' => false,
                'message' => 'No active check-in found for today'
            ]);
            exit();
        }
        
        // Calculate total hours
        $check_in_time = new DateTime($record['check_in_time']);
        $check_out_time = new DateTime($input['timestamp'] ?? date('Y-m-d H:i:s'));
        $duration = $check_out_time->diff($check_in_time);
        $total_hours = $duration->h + ($duration->i / 60) + ($duration->s / 3600);
        
        // Determine status based on hours worked
        $status = 'present';
        if ($total_hours > 8) {
            $status = 'overtime';
        } elseif ($total_hours < 7) {
            $status = 'incomplete';
        }
        
        // Update attendance record
        $stmt = $pdo->prepare("
            UPDATE attendance 
            SET check_out_time = ?, total_hours = ?, status = ?, updated_at = NOW()
            WHERE id = ?
        ");
        
        $stmt->execute([
            $check_out_time->format('Y-m-d H:i:s'),
            round($total_hours, 2),
            $status,
            $record['id']
        ]);
        
        echo json_encode([
            'success' => true,
            'message' => 'Successfully checked out',
            'total_hours' => round($total_hours, 2),
            'check_out_time' => $check_out_time->format('Y-m-d H:i:s'),
            'status' => $status
        ]);
        
    } catch (PDOException $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Database error: ' . $e->getMessage()
        ]);
    }
} else {
    // Simulate successful check-out for demo
    $total_hours = round(rand(70, 90) / 10, 1); // 7.0 - 9.0 hours
    
    echo json_encode([
        'success' => true,
        'message' => 'Successfully checked out',
        'total_hours' => $total_hours,
        'check_out_time' => date('Y-m-d H:i:s'),
        'status' => $total_hours > 8 ? 'overtime' : 'present',
        'demo_mode' => true
    ]);
}
?>