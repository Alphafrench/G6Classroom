<?php
require_once '../../includes/config.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';
require_once '../../includes/export.php';

// Check if user is logged in and has admin privileges
if (!isLoggedIn() || !isAdmin()) {
    header('Location: ../../login.php');
    exit();
}

// Get filter parameters
$startDate = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01');
$endDate = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-t');
$department = isset($_GET['department']) ? $_GET['department'] : '';
$format = isset($_GET['format']) ? $_GET['format'] : 'csv';

// Database connection
$pdo = getDBConnection();

// Build query based on filters
$query = "SELECT 
            DATE(a.timestamp) as date,
            e.name as employee_name,
            e.employee_id,
            a.department,
            TIME(a.timestamp) as time_in,
            a.time_out,
            a.total_hours,
            a.location,
            a.notes
          FROM attendance a 
          LEFT JOIN employees e ON a.employee_id = e.id 
          WHERE DATE(a.timestamp) BETWEEN :start_date AND :end_date";
$params = [':start_date' => $startDate, ':end_date' => $endDate];

if (!empty($department)) {
    $query .= " AND a.department = :department";
    $params[':department'] = $department;
}

$query .= " ORDER BY a.timestamp DESC";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$records = $stmt->fetchAll();

if ($format === 'csv') {
    // Generate CSV filename with timestamp
    $filename = "attendance_report_" . date('Y-m-d_H-i-s') . ".csv";
    
    // Set headers for CSV download
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: no-cache, no-store, must-revalidate');
    header('Pragma: no-cache');
    header('Expires: 0');
    
    // Create output stream
    $output = fopen('php://output', 'w');
    
    // Add BOM for UTF-8 encoding
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
    
    // Add CSV headers
    $headers = [
        'Date',
        'Employee ID',
        'Employee Name',
        'Department',
        'Time In',
        'Time Out',
        'Total Hours',
        'Location',
        'Notes'
    ];
    fputcsv($output, $headers);
    
    // Add data rows
    foreach ($records as $record) {
        $row = [
            $record['date'],
            $record['employee_id'] ?? '',
            $record['employee_name'] ?? 'Unknown',
            $record['department'],
            $record['time_in'] ?? '',
            $record['time_out'] ? date('H:i', strtotime($record['time_out'])) : '',
            $record['total_hours'] ? number_format($record['total_hours'], 2) : '',
            $record['location'] ?? '',
            $record['notes'] ?? ''
        ];
        fputcsv($output, $row);
    }
    
    fclose($output);
    exit();
} elseif ($format === 'excel') {
    // Excel export would require additional library
    // For now, redirect back with error message
    header('Location: index.php?error=excel_export_not_implemented');
    exit();
} elseif ($format === 'pdf') {
    // PDF export would require additional library
    // For now, redirect back with error message
    header('Location: index.php?error=pdf_export_not_implemented');
    exit();
} else {
    // Invalid format, redirect back
    header('Location: index.php?error=invalid_format');
    exit();
}
?>