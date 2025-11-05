<?php
/**
 * API for fetching attendance statistics
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

$employee_id = isset($_GET['employee_id']) ? $_GET['employee_id'] : 1;

try {
    $pdo = new PDO("mysql:host=localhost;dbname=attendance_system;charset=utf8mb4", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    $pdo = null;
}

if ($pdo) {
    try {
        // Get today's hours
        $stmt = $pdo->prepare("
            SELECT COALESCE(SUM(total_hours), 0) as today_hours 
            FROM attendance 
            WHERE employee_id = ? AND DATE(check_in_time) = CURDATE()
        ");
        $stmt->execute([$employee_id]);
        $today_hours = $stmt->fetchColumn();
        
        // Get this week's hours
        $stmt = $pdo->prepare("
            SELECT COALESCE(SUM(total_hours), 0) as week_hours 
            FROM attendance 
            WHERE employee_id = ? AND YEARWEEK(check_in_time, 1) = YEARWEEK(NOW(), 1)
        ");
        $stmt->execute([$employee_id]);
        $week_hours = $stmt->fetchColumn();
        
        // Get this month's hours
        $stmt = $pdo->prepare("
            SELECT COALESCE(SUM(total_hours), 0) as month_hours 
            FROM attendance 
            WHERE employee_id = ? AND YEAR(check_in_time) = YEAR(NOW()) AND MONTH(check_in_time) = MONTH(NOW())
        ");
        $stmt->execute([$employee_id]);
        $month_hours = $stmt->fetchColumn();
        
        // Get overtime hours
        $stmt = $pdo->prepare("
            SELECT COALESCE(SUM(total_hours - 8), 0) as overtime_hours 
            FROM attendance 
            WHERE employee_id = ? AND status = 'overtime' AND total_hours > 8
        ");
        $stmt->execute([$employee_id]);
        $overtime_hours = $stmt->fetchColumn();
        
        echo json_encode([
            'success' => true,
            'today_hours' => round($today_hours, 1),
            'this_week_hours' => round($week_hours, 1),
            'this_month_hours' => round($month_hours, 1),
            'overtime_hours' => round($overtime_hours, 1)
        ]);
    } catch (PDOException $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Database error'
        ]);
    }
} else {
    // Simulate data for demo
    echo json_encode([
        'success' => true,
        'today_hours' => rand(7, 9),
        'this_week_hours' => rand(35, 45),
        'this_month_hours' => rand(160, 180),
        'overtime_hours' => rand(5, 15),
        'demo_mode' => true
    ]);
}
?>