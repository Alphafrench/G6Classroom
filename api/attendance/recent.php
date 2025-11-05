<?php
/**
 * API for fetching recent attendance records
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

$employee_id = isset($_SESSION['employee_id']) ? $_SESSION['employee_id'] : 1;
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 5;

// Database connection (simulate - replace with actual DB)
$host = 'localhost';
$dbname = 'attendance_system';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    $pdo = null;
}

if ($pdo) {
    try {
        $stmt = $pdo->prepare("
            SELECT * FROM attendance 
            WHERE employee_id = ? 
            ORDER BY check_in_time DESC 
            LIMIT ?
        ");
        $stmt->execute([$employee_id, $limit]);
        $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'records' => $records
        ]);
    } catch (PDOException $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Database error'
        ]);
    }
} else {
    // Simulate data for demo
    $records = [];
    for ($i = 0; $i < $limit; $i++) {
        $check_in = date('Y-m-d H:i:s', strtotime("-$i days -" . rand(0, 2) . " hours"));
        $check_out = null;
        $total_hours = 0;
        $status = 'present';
        
        if (rand(0, 4) > 0) {
            $check_out_time = date('Y-m-d H:i:s', strtotime($check_in . ' +' . rand(7, 9) . ' hours'));
            $check_out = $check_out_time;
            $total_hours = round((strtotime($check_out_time) - strtotime($check_in)) / 3600, 2);
            
            if ($total_hours > 8) {
                $status = 'overtime';
            }
        } else {
            $status = 'incomplete';
        }
        
        $records[] = [
            'id' => $i + 1,
            'employee_id' => $employee_id,
            'check_in_time' => $check_in,
            'check_out_time' => $check_out,
            'total_hours' => $total_hours,
            'status' => $status,
            'notes' => ''
        ];
    }
    
    echo json_encode([
        'success' => true,
        'records' => $records,
        'demo_mode' => true
    ]);
}
?>