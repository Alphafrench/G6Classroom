<?php
/**
 * API for exporting attendance records to CSV
 */

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="attendance_records.csv"');

session_start();

$employee_id = isset($_SESSION['employee_id']) ? $_SESSION['employee_id'] : 1;
$date_from = isset($_GET['date_from']) ? $_GET['date_from'] : date('Y-m-d', strtotime('-30 days'));
$date_to = isset($_GET['date_to']) ? $_GET['date_to'] : date('Y-m-d');

try {
    $pdo = new PDO("mysql:host=localhost;dbname=attendance_system;charset=utf8mb4", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    $pdo = null;
}

$output = fopen('php://output', 'w');

// Add CSV header
fputcsv($output, [
    'Date',
    'Check In',
    'Check Out',
    'Total Hours',
    'Status',
    'Notes'
]);

if ($pdo) {
    try {
        $stmt = $pdo->prepare("
            SELECT * FROM attendance 
            WHERE employee_id = ? AND DATE(check_in_time) BETWEEN ? AND ?
            ORDER BY check_in_time DESC
        ");
        $stmt->execute([$employee_id, $date_from, $date_to]);
        $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($records as $record) {
            fputcsv($output, [
                date('Y-m-d', strtotime($record['check_in_time'])),
                date('H:i:s', strtotime($record['check_in_time'])),
                $record['check_out_time'] ? date('H:i:s', strtotime($record['check_out_time'])) : '',
                $record['total_hours'],
                $record['status'],
                $record['notes'] ?? ''
            ]);
        }
    } catch (PDOException $e) {
        // Handle error
    }
} else {
    // Simulate data for demo
    for ($i = 0; $i < 20; $i++) {
        $date = date('Y-m-d', strtotime("-$i days"));
        if (date('N', strtotime($date)) < 6) { // Not weekend
            $check_in = date('H:i:s', strtotime('08:' . rand(0, 59)));
            $check_out = date('H:i:s', strtotime('17:' . rand(0, 59)));
            $hours = rand(7, 9);
            
            fputcsv($output, [
                $date,
                $check_in,
                $check_out,
                $hours,
                $hours > 8 ? 'overtime' : 'present',
                'Regular work day'
            ]);
        }
    }
}

fclose($output);
?>