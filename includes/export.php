<?php
/**
 * CSV Export Functions
 * Handles CSV export functionality for attendance reports
 */

/**
 * Generate CSV content for attendance data
 * 
 * @param array $data Attendance records
 * @param array $headers CSV headers
 * @param string $filename Output filename
 * @param array $options Export options
 * @return string CSV content
 */
function generateAttendanceCSV($data, $headers, $filename = 'attendance_export.csv', $options = []) {
    $defaultOptions = [
        'include_bom' => true,
        'delimiter' => ',',
        'enclosure' => '"',
        'line_ending' => "\n",
        'escape_char' => '\\'
    ];
    
    $options = array_merge($defaultOptions, $options);
    
    $csv = '';
    
    // Add BOM for UTF-8 encoding if requested
    if ($options['include_bom']) {
        $csv .= "\xEF\xBB\xBF";
    }
    
    // Add headers
    $csv .= implode($options['delimiter'], array_map(function($header) use ($options) {
        return $options['enclosure'] . str_replace($options['enclosure'], $options['escape_char'] . $options['enclosure'], $header) . $options['enclosure'];
    }, $headers)) . $options['line_ending'];
    
    // Add data rows
    foreach ($data as $row) {
        $csv .= implode($options['delimiter'], array_map(function($value) use ($options) {
            if ($value === null || $value === '') {
                return '';
            }
            return $options['enclosure'] . str_replace($options['enclosure'], $options['escape_char'] . $options['enclosure'], $value) . $options['enclosure'];
        }, $row)) . $options['line_ending'];
    }
    
    return $csv;
}

/**
 * Export attendance data to CSV file
 * 
 * @param array $attendanceData Attendance records
 * @param string $filename Output filename
 * @param array $options Export options
 * @return bool Success status
 */
function exportAttendanceToCSV($attendanceData, $filename = null, $options = []) {
    if (!$filename) {
        $filename = 'attendance_export_' . date('Y-m-d_H-i-s') . '.csv';
    }
    
    // Define CSV headers
    $headers = [
        'Date',
        'Employee ID',
        'Employee Name',
        'Department',
        'Position',
        'Time In',
        'Time Out',
        'Total Hours',
        'Overtime Hours',
        'Break Duration',
        'Location',
        'GPS Coordinates',
        'IP Address',
        'Status',
        'Notes'
    ];
    
    // Process data for CSV
    $csvData = [];
    foreach ($attendanceData as $record) {
        $csvData[] = [
            date('Y-m-d', strtotime($record['timestamp'])),
            $record['employee_id'] ?? '',
            $record['employee_name'] ?? 'Unknown',
            $record['department'] ?? '',
            $record['position'] ?? '',
            date('H:i:s', strtotime($record['timestamp'])),
            $record['time_out'] ? date('H:i:s', strtotime($record['time_out'])) : '',
            $record['total_hours'] ? number_format($record['total_hours'], 2) : '0.00',
            $record['overtime_hours'] ? number_format($record['overtime_hours'], 2) : '0.00',
            $record['break_duration'] ? number_format($record['break_duration'], 0) . ' min' : '0 min',
            $record['location'] ?? '',
            $record['gps_coordinates'] ?? '',
            $record['ip_address'] ?? '',
            $record['status'] ?? 'Present',
            $record['notes'] ?? ''
        ];
    }
    
    // Generate CSV content
    $csvContent = generateAttendanceCSV($csvData, $headers, $filename, $options);
    
    // Set headers for download
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: no-cache, no-store, must-revalidate');
    header('Pragma: no-cache');
    header('Expires: 0');
    
    // Output CSV content
    echo $csvContent;
    return true;
}

/**
 * Export summary report to CSV
 * 
 * @param array $summaryData Summary data
 * @param string $filename Output filename
 * @return bool Success status
 */
function exportSummaryToCSV($summaryData, $filename = null) {
    if (!$filename) {
        $filename = 'attendance_summary_' . date('Y-m-d_H-i-s') . '.csv';
    }
    
    // Define headers for summary report
    $headers = [
        'Metric',
        'Value',
        'Description'
    ];
    
    $csvData = [];
    
    foreach ($summaryData as $metric => $data) {
        $csvData[] = [
            $data['label'],
            $data['value'],
            $data['description']
        ];
    }
    
    // Generate and output CSV
    $csvContent = generateAttendanceCSV($csvData, $headers, $filename);
    
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    
    echo $csvContent;
    return true;
}

/**
 * Export department report to CSV
 * 
 * @param array $departmentData Department statistics
 * @param string $filename Output filename
 * @return bool Success status
 */
function exportDepartmentToCSV($departmentData, $filename = null) {
    if (!$filename) {
        $filename = 'department_report_' . date('Y-m-d_H-i-s') . '.csv';
    }
    
    $headers = [
        'Department',
        'Total Attendance',
        'Average Daily',
        'Peak Hour',
        'Late Arrivals',
        'Early Departures',
        'Overtime Hours',
        'Attendance Rate (%)'
    ];
    
    $csvData = [];
    foreach ($departmentData as $dept) {
        $csvData[] = [
            $dept['department'],
            $dept['total_attendance'],
            number_format($dept['average_daily'], 1),
            $dept['peak_hour'] . ':00',
            $dept['late_arrivals'],
            $dept['early_departures'],
            number_format($dept['overtime_hours'], 2),
            number_format($dept['attendance_rate'], 1)
        ];
    }
    
    $csvContent = generateAttendanceCSV($csvData, $headers, $filename);
    
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    
    echo $csvContent;
    return true;
}

/**
 * Export monthly report to CSV
 * 
 * @param array $monthlyData Monthly statistics
 * @param string $filename Output filename
 * @return bool Success status
 */
function exportMonthlyToCSV($monthlyData, $filename = null) {
    if (!$filename) {
        $filename = 'monthly_report_' . date('Y-m-d_H-i-s') . '.csv';
    }
    
    $headers = [
        'Date',
        'Day',
        'Total Attendance',
        'Present',
        'Absent',
        'Late',
        'Early Leave',
        'Overtime'
    ];
    
    $csvData = [];
    foreach ($monthlyData as $day) {
        $csvData[] = [
            $day['date'],
            $day['day_name'],
            $day['total'],
            $day['present'],
            $day['absent'],
            $day['late'],
            $day['early_leave'],
            $day['overtime']
        ];
    }
    
    $csvContent = generateAttendanceCSV($csvData, $headers, $filename);
    
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    
    echo $csvContent;
    return true;
}

/**
 * Generate CSV with custom formatting
 * 
 * @param array $data Data to export
 * @param array $headers Column headers
 * @param array $options Formatting options
 * @return string CSV content
 */
function generateCustomCSV($data, $headers, $options = []) {
    $defaultOptions = [
        'delimiter' => ',',
        'enclosure' => '"',
        'escape_char' => '\\',
        'line_ending' => "\n",
        'include_headers' => true,
        'trim_fields' => true
    ];
    
    $options = array_merge($defaultOptions, $options);
    $csv = '';
    
    // Add headers if requested
    if ($options['include_headers']) {
        $headerRow = [];
        foreach ($headers as $header) {
            $headerRow[] = $options['enclosure'] . 
                          str_replace($options['enclosure'], $options['escape_char'] . $options['enclosure'], $header) . 
                          $options['enclosure'];
        }
        $csv .= implode($options['delimiter'], $headerRow) . $options['line_ending'];
    }
    
    // Add data rows
    foreach ($data as $row) {
        $csvRow = [];
        foreach ($row as $field) {
            $value = $options['trim_fields'] ? trim($field) : $field;
            $csvRow[] = $options['enclosure'] . 
                       str_replace($options['enclosure'], $options['escape_char'] . $options['enclosure'], $value) . 
                       $options['enclosure'];
        }
        $csv .= implode($options['delimiter'], $csvRow) . $options['line_ending'];
    }
    
    return $csv;
}

/**
 * Stream CSV file directly to browser
 * 
 * @param string $csvContent CSV content
 * @param string $filename Filename
 * @param array $headers Additional headers
 */
function streamCSV($csvContent, $filename, $headers = []) {
    // Set default headers
    $defaultHeaders = [
        'Content-Type' => 'text/csv; charset=utf-8',
        'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        'Cache-Control' => 'no-cache, no-store, must-revalidate',
        'Pragma' => 'no-cache',
        'Expires' => '0'
    ];
    
    // Merge with additional headers
    $headers = array_merge($defaultHeaders, $headers);
    
    // Set headers
    foreach ($headers as $name => $value) {
        header("$name: $value");
    }
    
    // Output content
    echo $csvContent;
    exit;
}

/**
 * Validate CSV data before export
 * 
 * @param array $data Data to validate
 * @return array Validation results
 */
function validateCSVData($data) {
    $errors = [];
    $warnings = [];
    
    if (empty($data)) {
        $errors[] = 'No data to export';
        return ['valid' => false, 'errors' => $errors, 'warnings' => $warnings];
    }
    
    // Check data structure
    if (!is_array($data[0])) {
        $errors[] = 'Invalid data structure';
        return ['valid' => false, 'errors' => $errors, 'warnings' => $warnings];
    }
    
    // Check for sensitive data
    $sensitiveFields = ['password', 'ssn', 'social_security', 'bank_account'];
    foreach ($data as $row) {
        foreach ($row as $field => $value) {
            if (in_array(strtolower($field), $sensitiveFields)) {
                $warnings[] = "Sensitive data detected in field: $field";
            }
        }
    }
    
    return [
        'valid' => empty($errors),
        'errors' => $errors,
        'warnings' => $warnings,
        'record_count' => count($data)
    ];
}

/**
 * Get CSV mime type based on content
 * 
 * @param string $content CSV content
 * @return string MIME type
 */
function getCSVMimeType($content) {
    // Check for UTF-8 BOM
    if (substr($content, 0, 3) === "\xEF\xBB\xBF") {
        return 'text/csv; charset=utf-8';
    }
    
    return 'text/csv; charset=iso-8859-1';
}

/**
 * Compress CSV data for large exports
 * 
 * @param string $csvContent CSV content
 * @return string Compressed content
 */
function compressCSV($csvContent) {
    if (!function_exists('gzencode')) {
        return $csvContent; // Return uncompressed if gzip not available
    }
    
    return gzencode($csvContent);
}

/**
 * Log CSV export activity
 * 
 * @param int $userId User ID
 * @param string $filename Exported filename
 * @param array $data Export data info
 */
function logCSVExport($userId, $filename, $data = []) {
    global $pdo;
    
    $description = "Exported CSV file: $filename";
    if (isset($data['record_count'])) {
        $description .= " ({$data['record_count']} records)";
    }
    
    try {
        $stmt = $pdo->prepare("INSERT INTO activity_logs (user_id, action, description, created_at) VALUES (?, ?, ?, NOW())");
        $stmt->execute([$userId, 'CSV Export', $description]);
    } catch (Exception $e) {
        // Log export failed, but don't interrupt the export process
        error_log("Failed to log CSV export: " . $e->getMessage());
    }
}

/**
 * Create CSV template for data import
 * 
 * @param array $headers Column headers
 * @param array $sampleData Sample data rows
 * @param string $filename Template filename
 */
function createCSVTemplate($headers, $sampleData = [], $filename = 'import_template.csv') {
    $csvContent = '';
    
    // Add headers
    $csvContent .= implode(',', array_map(function($header) {
        return '"' . str_replace('"', '""', $header) . '"';
    }, $headers)) . "\n";
    
    // Add sample data if provided
    foreach ($sampleData as $sample) {
        $csvContent .= implode(',', array_map(function($value) {
            return '"' . str_replace('"', '""', $value) . '"';
        }, $sample)) . "\n";
    }
    
    // Set headers for download
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    
    echo $csvContent;
}
?>