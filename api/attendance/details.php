<?php
/**
 * API for fetching attendance record details
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

session_start();

$record_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

try {
    $pdo = new PDO("mysql:host=localhost;dbname=attendance_system;charset=utf8mb4", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    $pdo = null;
}

if ($pdo && $record_id > 0) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM attendance WHERE id = ?");
        $stmt->execute([$record_id]);
        $record = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($record) {
            echo json_encode([
                'success' => true,
                'record' => $record
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Record not found'
            ]);
        }
    } catch (PDOException $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Database error'
        ]);
    }
} else {
    // Simulate data for demo
    $record = [
        'id' => $record_id,
        'employee_id' => 1,
        'check_in_time' => date('Y-m-d H:i:s', strtotime('today 08:30')),
        'check_out_time' => date('Y-m-d H:i:s', strtotime('today 17:30')),
        'total_hours' => 8.0,
        'status' => 'present',
        'location' => 'Office - Floor 3',
        'ip_address' => '192.168.1.100',
        'notes' => 'Regular work day'
    ];
    
    echo json_encode([
        'success' => true,
        'record' => $record,
        'demo_mode' => true
    ]);
}
?>