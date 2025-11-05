<?php
session_start();

// Authentication check
require_once '../../includes/auth.php';
require_once '../../includes/class.Database.php';
require_once '../../includes/class.Attendance.php';
require_once '../../includes/class.Employee.php';
require_once '../../includes/functions.php';

$employee_id = $_SESSION['employee_id'];
$employee_name = $_SESSION['employee_name'];
$user_role = $_SESSION['user_role'] ?? 'employee';

try {
    $db = getDatabase();
    $attendance = new Attendance($db);
    $employee = new Employee($db);
    
    // Get filter parameters
    $start_date = $_GET['start_date'] ?? date('Y-m-d', strtotime('-30 days'));
    $end_date = $_GET['end_date'] ?? date('Y-m-d');
    $employee_filter = $_GET['employee_id'] ?? ($user_role === 'employee' ? $employee_id : '');
    $report_type = $_GET['report_type'] ?? 'summary';
    $export_format = $_GET['export'] ?? '';
    
    // Build filters
    $filters = [
        'start_date' => $start_date,
        'end_date' => $end_date
    ];
    
    if ($employee_filter) {
        $filters['employee_id'] = $employee_filter;
    }
    
    // Get employees list for filter
    if (in_array($user_role, ['admin', 'manager'])) {
        $employees = $employee->getAllEmployees();
    } else {
        $employees = [$employee->getEmployee($employee_id)];
    }
    
    // Generate report data
    $report_data = [];
    $summary_stats = [];
    
    if ($report_type === 'summary') {
        $report_data = $attendance->generateReport($filters, 'summary');
        $summary_stats = $attendance->getEmployeeSummary($employee_filter ?: $employee_id, $start_date, $end_date);
    } elseif ($report_type === 'detailed') {
        $report_data = $attendance->generateReport($filters, 'detailed');
        $detailed_data = $attendance->getAllAttendance($filters, 1000, 0);
        $report_data['data'] = $detailed_data['records'];
    } elseif ($report_type === 'statistics') {
        // Generate comprehensive statistics
        $report_data = generateComprehensiveStats($attendance, $filters, $employee_filter ?: $employee_id);
    }
    
    // Handle export
    if ($export_format) {
        handleExport($attendance, $filters, $export_format, $report_type);
        exit;
    }
    
} catch (Exception $e) {
    logError("Reports error: " . $e->getMessage());
    $employees = [];
    $report_data = ['data' => []];
    $summary_stats = [];
}

function generateComprehensiveStats($attendance, $filters, $employeeId) {
    $stats = [];
    
    // Daily statistics
    $daily_stats = [];
    $start = new DateTime($filters['start_date']);
    $end = new DateTime($filters['end_date']);
    
    while ($start <= $end) {
        $date = $start->format('Y-m-d');
        $day_stats = $attendance->getEmployeeSummary($employeeId, $date, $date);
        $daily_stats[] = [
            'date' => $date,
            'formatted_date' => formatDate($date),
            'worked' => $day_stats['days_worked'] > 0,
            'hours' => $day_stats['total_hours'],
            'status' => $day_stats['days_worked'] > 0 ? 'present' : 'absent'
        ];
        $start->add(new DateInterval('P1D'));
    }
    
    $stats['daily'] = $daily_stats;
    
    // Weekly statistics
    $weekly_stats = [];
    $current = clone $start;
    $current->setDate((int)$current->format('Y'), (int)$current->format('m'), (int)$current->format('d'));
    $current->modify('monday this week');
    
    while ($current <= $end) {
        $week_start = $current->format('Y-m-d');
        $current->modify('sunday this week');
        $week_end = $current->format('Y-m-d');
        
        $week_stats = $attendance->getEmployeeSummary($employeeId, $week_start, $week_end);
        $weekly_stats[] = [
            'week_start' => $week_start,
            'week_end' => $week_end,
            'formatted_week' => formatDate($week_start) . ' - ' . formatDate($week_end),
            'days_worked' => $week_stats['days_worked'],
            'total_hours' => $week_stats['total_hours'],
            'average_hours' => $week_stats['average_hours_per_day']
        ];
        
        $current->add(new DateInterval('P1D'));
        $current->modify('monday next week');
    }
    
    $stats['weekly'] = $weekly_stats;
    
    // Monthly overview
    $monthly_stats = [];
    $current_month = new DateTime($filters['start_date']);
    $current_month->setDate((int)$current_month->format('Y'), (int)$current_month->format('m'), 1);
    
    while ($current_month <= $end) {
        $month_start = $current_month->format('Y-m-d');
        $current_month->modify('last day of this month');
        $month_end = $current_month->format('Y-m-d');
        
        $month_stats = $attendance->getEmployeeSummary($employeeId, $month_start, $month_end);
        $monthly_stats[] = [
            'month' => $current_month->format('Y-m'),
            'month_name' => $current_month->format('F Y'),
            'days_worked' => $month_stats['days_worked'],
            'total_hours' => $month_stats['total_hours'],
            'average_hours' => $month_stats['average_hours_per_day']
        ];
        
        $current_month->add(new DateInterval('P1M'));
        $current_month->setDate((int)$current_month->format('Y'), (int)$current_month->format('m'), 1);
    }
    
    $stats['monthly'] = $monthly_stats;
    
    return $stats;
}

function handleExport($attendance, $filters, $format, $report_type) {
    try {
        if ($format === 'csv') {
            $report = $attendance->generateReport($filters, 'csv');
            
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="attendance_report_' . date('Y-m-d') . '.csv"');
            header('Pragma: no-cache');
            header('Expires: 0');
            
            echo $report['csv_string'];
            
        } elseif ($format === 'pdf') {
            // Generate PDF report (would require additional library in real implementation)
            $report = $attendance->generateReport($filters, 'summary');
            
            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename="attendance_report_' . date('Y-m-d') . '.pdf"');
            
            // For now, just return HTML that can be printed as PDF
            echo "<h1>Attendance Report</h1>";
            echo "<p>Generated on: " . date('Y-m-d H:i:s') . "</p>";
            echo "<pre>" . json_encode($report, JSON_PRETTY_PRINT) . "</pre>";
        }
    } catch (Exception $e) {
        logError("Export error: " . $e->getMessage());
        echo json_encode(['error' => 'Export failed: ' . $e->getMessage()]);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance Reports & Analytics - HR Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.css" rel="stylesheet">
    <link href="../../assets/css/attendance.css" rel="stylesheet">
    <style>
        .chart-container {
            position: relative;
            height: 400px;
            margin: 20px 0;
        }
        
        .stats-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        
        .stats-card.primary { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .stats-card.success { background: linear-gradient(135deg, #56ab2f 0%, #a8e6cf 100%); }
        .stats-card.warning { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); }
        .stats-card.info { background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); }
        
        .insights-section {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin: 20px 0;
        }
        
        .insight-item {
            display: flex;
            align-items: center;
            padding: 10px;
            margin: 5px 0;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .insight-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            font-size: 18px;
        }
        
        .export-buttons {
            position: sticky;
            top: 20px;
            z-index: 100;
        }
        
        .tab-content {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-building"></i> HR Management System
            </a>
            <div class="navbar-nav ms-auto">
                <span class="navbar-text me-3">
                    <i class="fas fa-user"></i> Welcome, <?php echo htmlspecialchars($employee_name); ?>
                </span>
                <a href="index.php" class="btn btn-outline-light btn-sm me-2">
                    <i class="fas fa-arrow-left"></i> Dashboard
                </a>
                <a href="../../pages/logout.php" class="btn btn-outline-light btn-sm">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>
    </nav>

    <div class="container-fluid mt-4">
        <!-- Page Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card bg-gradient bg-primary text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h1 class="display-6 mb-0">
                                    <i class="fas fa-chart-line"></i> Attendance Reports & Analytics
                                </h1>
                                <p class="mb-0">Comprehensive attendance insights and reporting</p>
                            </div>
                            <div class="export-buttons">
                                <div class="btn-group" role="group">
                                    <a href="?<?php echo http_build_query(array_merge($_GET, ['export' => 'csv'])); ?>" 
                                       class="btn btn-light">
                                        <i class="fas fa-download"></i> CSV
                                    </a>
                                    <a href="?<?php echo http_build_query(array_merge($_GET, ['export' => 'pdf'])); ?>" 
                                       class="btn btn-light">
                                        <i class="fas fa-file-pdf"></i> PDF
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter Section -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-filter"></i> Report Filters
                        </h5>
                    </div>
                    <div class="card-body">
                        <form method="GET" id="report-filter-form">
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <label for="start_date" class="form-label">Start Date</label>
                                    <input type="date" class="form-control" id="start_date" name="start_date" 
                                           value="<?php echo htmlspecialchars($start_date); ?>">
                                </div>
                                <div class="col-md-3">
                                    <label for="end_date" class="form-label">End Date</label>
                                    <input type="date" class="form-control" id="end_date" name="end_date" 
                                           value="<?php echo htmlspecialchars($end_date); ?>">
                                </div>
                                <div class="col-md-3">
                                    <label for="employee_id" class="form-label">
                                        Employee <?php if ($user_role === 'employee'): ?>(Only you)<?php endif; ?>
                                    </label>
                                    <?php if ($user_role === 'employee'): ?>
                                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($employee_name); ?>" readonly>
                                        <input type="hidden" name="employee_id" value="<?php echo $employee_id; ?>">
                                    <?php else: ?>
                                        <select class="form-select" id="employee_id" name="employee_id">
                                            <option value="">All Employees</option>
                                            <?php foreach ($employees as $emp): ?>
                                                <?php if ($emp && isset($emp['id'])): ?>
                                                    <option value="<?php echo $emp['id']; ?>" 
                                                            <?php echo $employee_filter == $emp['id'] ? 'selected' : ''; ?>>
                                                        <?php echo htmlspecialchars($emp['first_name'] . ' ' . $emp['last_name']); ?>
                                                    </option>
                                                <?php endif; ?>
                                            <?php endforeach; ?>
                                        </select>
                                    <?php endif; ?>
                                </div>
                                <div class="col-md-3">
                                    <label for="report_type" class="form-label">Report Type</label>
                                    <select class="form-select" id="report_type" name="report_type">
                                        <option value="summary" <?php echo $report_type === 'summary' ? 'selected' : ''; ?>>Summary Report</option>
                                        <option value="detailed" <?php echo $report_type === 'detailed' ? 'selected' : ''; ?>>Detailed Report</option>
                                        <option value="statistics" <?php echo $report_type === 'statistics' ? 'selected' : ''; ?>>Statistics & Analytics</option>
                                    </select>
                                </div>
                            </div>
                            <div class="row mt-3">
                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-chart-bar"></i> Generate Report
                                    </button>
                                    <button type="button" class="btn btn-secondary" onclick="clearFilters()">
                                        <i class="fas fa-times"></i> Clear Filters
                                    </button>
                                    <button type="button" class="btn btn-success" onclick="quickReport('this-week')">
                                        <i class="fas fa-calendar-week"></i> This Week
                                    </button>
                                    <button type="button" class="btn btn-info" onclick="quickReport('this-month')">
                                        <i class="fas fa-calendar-alt"></i> This Month
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Report Content -->
        <?php if ($report_type === 'summary'): ?>
            <!-- Summary Report -->
            <div class="row">
                <!-- Summary Statistics -->
                <div class="col-md-3">
                    <div class="stats-card primary">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h3><?php echo number_format($summary_stats['total_hours'] ?? 0, 1); ?></h3>
                                <p class="mb-0">Total Hours</p>
                            </div>
                            <div>
                                <i class="fas fa-clock fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stats-card success">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h3><?php echo $summary_stats['days_worked'] ?? 0; ?></h3>
                                <p class="mb-0">Days Worked</p>
                            </div>
                            <div>
                                <i class="fas fa-calendar-check fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stats-card warning">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h3><?php echo number_format($summary_stats['average_hours_per_day'] ?? 0, 1); ?></h3>
                                <p class="mb-0">Avg Hours/Day</p>
                            </div>
                            <div>
                                <i class="fas fa-chart-line fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stats-card info">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h3><?php echo $summary_stats['incomplete_days'] ?? 0; ?></h3>
                                <p class="mb-0">Incomplete Days</p>
                            </div>
                            <div>
                                <i class="fas fa-exclamation-triangle fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Summary Chart -->
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-chart-area"></i> Hours Overview
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="chart-container">
                                <canvas id="summaryChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Detailed Summary Table -->
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-table"></i> Summary Details
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Employee</th>
                                            <th>Days Worked</th>
                                            <th>Total Hours</th>
                                            <th>Average Hours</th>
                                            <th>First Day</th>
                                            <th>Last Day</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($report_data['data'])): ?>
                                            <?php foreach ($report_data['data'] as $row): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></td>
                                                    <td><?php echo $row['days_worked']; ?></td>
                                                    <td><?php echo number_format($row['total_hours'] ?? 0, 2); ?></td>
                                                    <td><?php echo number_format($row['average_hours'] ?? 0, 2); ?></td>
                                                    <td><?php echo $row['first_work_day'] ? formatDate($row['first_work_day']) : '-'; ?></td>
                                                    <td><?php echo $row['last_work_day'] ? formatDate($row['last_work_day']) : '-'; ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="6" class="text-center text-muted">No data available</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        <?php elseif ($report_type === 'statistics'): ?>
            <!-- Statistics & Analytics -->
            <div class="row">
                <!-- Daily Chart -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-calendar-day"></i> Daily Attendance
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="chart-container">
                                <canvas id="dailyChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Weekly Chart -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-calendar-week"></i> Weekly Trends
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="chart-container">
                                <canvas id="weeklyChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Monthly Overview -->
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-calendar-alt"></i> Monthly Overview
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="chart-container">
                                <canvas id="monthlyChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Insights Section -->
            <div class="row mt-4">
                <div class="col-12">
                    <div class="insights-section">
                        <h5>
                            <i class="fas fa-lightbulb"></i> Attendance Insights
                        </h5>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="insight-item">
                                    <div class="insight-icon bg-success text-white">
                                        <i class="fas fa-trending-up"></i>
                                    </div>
                                    <div>
                                        <strong>Consistency Score</strong>
                                        <p class="mb-0 text-muted">
                                            <?php 
                                            $consistency = !empty($report_data['daily']) ? 
                                                (count(array_filter($report_data['daily'], function($d) { return $d['worked']; })) / count($report_data['daily']) * 100) : 0;
                                            echo number_format($consistency, 1) . '%';
                                            ?>
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="insight-item">
                                    <div class="insight-icon bg-info text-white">
                                        <i class="fas fa-fire"></i>
                                    </div>
                                    <div>
                                        <strong>Peak Performance</strong>
                                        <p class="mb-0 text-muted">Highest daily hours recorded</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="insight-item">
                                    <div class="insight-icon bg-warning text-white">
                                        <i class="fas fa-exclamation-circle"></i>
                                    </div>
                                    <div>
                                        <strong>Areas for Improvement</strong>
                                        <p class="mb-0 text-muted">Inconsistent attendance patterns</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        <?php else: ?>
            <!-- Detailed Report -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-list"></i> Detailed Attendance Records
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>Date</th>
                                            <th>Employee</th>
                                            <th>Department</th>
                                            <th>Clock In</th>
                                            <th>Clock Out</th>
                                            <th>Hours Worked</th>
                                            <th>Status</th>
                                            <th>Location</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($report_data['data'])): ?>
                                            <?php foreach ($report_data['data'] as $record): ?>
                                                <tr>
                                                    <td><?php echo formatDate($record['date']); ?></td>
                                                    <td><?php echo htmlspecialchars($record['first_name'] . ' ' . $record['last_name']); ?></td>
                                                    <td><?php echo htmlspecialchars($record['department_name'] ?? ''); ?></td>
                                                    <td>
                                                        <i class="fas fa-sign-in-alt text-success me-1"></i>
                                                        <?php echo formatDateTime($record['clock_in']); ?>
                                                    </td>
                                                    <td>
                                                        <?php if ($record['clock_out']): ?>
                                                            <i class="fas fa-sign-out-alt text-danger me-1"></i>
                                                            <?php echo formatDateTime($record['clock_out']); ?>
                                                        <?php else: ?>
                                                            <span class="text-muted">-</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-primary">
                                                            <?php echo $record['hours_worked'] ? number_format($record['hours_worked'], 2) . 'h' : '-'; ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <span class="badge <?php echo $record['clock_out'] ? 'bg-success' : 'bg-warning'; ?>">
                                                            <?php echo $record['clock_out'] ? 'Complete' : 'Incomplete'; ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <small class="text-muted"><?php echo htmlspecialchars($record['location'] ?? ''); ?></small>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="8" class="text-center text-muted">
                                                    <i class="fas fa-info-circle"></i> No attendance records found for the selected criteria
                                                </td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
    <script src="../../assets/js/attendance.js"></script>
    <script src="../../assets/js/reports.js"></script>
    <script>
        // Initialize charts based on report type
        document.addEventListener('DOMContentLoaded', function() {
            const reportType = '<?php echo $report_type; ?>';
            
            if (reportType === 'summary') {
                initializeSummaryCharts();
            } else if (reportType === 'statistics') {
                initializeStatisticsCharts();
            }
            
            initializeReportFilters();
        });

        function initializeSummaryCharts() {
            // Summary bar chart
            const ctx = document.getElementById('summaryChart').getContext('2d');
            
            // Sample data - in real implementation, this would come from the server
            const labels = ['Week 1', 'Week 2', 'Week 3', 'Week 4'];
            const data = [40, 38, 42, 45];
            
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Hours Worked',
                        data: data,
                        backgroundColor: 'rgba(54, 162, 235, 0.6)',
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 2,
                        borderRadius: 8
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        title: {
                            display: true,
                            text: 'Weekly Hours Distribution'
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 50,
                            title: {
                                display: true,
                                text: 'Hours'
                            }
                        }
                    }
                }
            });
        }

        function initializeStatisticsCharts() {
            // Daily attendance line chart
            const dailyCtx = document.getElementById('dailyChart').getContext('2d');
            const labels = [];
            const hours = [];
            
            // Generate sample data for last 14 days
            const today = new Date();
            for (let i = 13; i >= 0; i--) {
                const date = new Date(today);
                date.setDate(date.getDate() - i);
                labels.push(date.toLocaleDateString('en-US', { weekday: 'short', day: 'numeric' }));
                hours.push(Math.random() * 2 + 7); // Random hours between 7-9
            }
            
            new Chart(dailyCtx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Daily Hours',
                        data: hours,
                        borderColor: 'rgb(75, 192, 192)',
                        backgroundColor: 'rgba(75, 192, 192, 0.2)',
                        tension: 0.4,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        title: {
                            display: true,
                            text: 'Last 14 Days'
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 10,
                            title: {
                                display: true,
                                text: 'Hours'
                            }
                        }
                    }
                }
            });

            // Weekly trends chart
            const weeklyCtx = document.getElementById('weeklyChart').getContext('2d');
            const weeklyLabels = ['Week 1', 'Week 2', 'Week 3', 'Week 4'];
            const weeklyHours = [38, 42, 40, 45];
            
            new Chart(weeklyCtx, {
                type: 'line',
                data: {
                    labels: weeklyLabels,
                    datasets: [{
                        label: 'Weekly Hours',
                        data: weeklyHours,
                        borderColor: 'rgb(255, 99, 132)',
                        backgroundColor: 'rgba(255, 99, 132, 0.2)',
                        tension: 0.3
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        title: {
                            display: true,
                            text: 'Weekly Trends'
                        }
                    }
                }
            });

            // Monthly overview chart
            const monthlyCtx = document.getElementById('monthlyChart').getContext('2d');
            const monthlyLabels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'];
            const thisYear = [160, 165, 158, 172, 168, 170];
            const lastYear = [155, 162, 160, 165, 158, 168];
            
            new Chart(monthlyCtx, {
                type: 'bar',
                data: {
                    labels: monthlyLabels,
                    datasets: [
                        {
                            label: 'This Year',
                            data: thisYear,
                            backgroundColor: 'rgba(54, 162, 235, 0.6)',
                            borderColor: 'rgba(54, 162, 235, 1)',
                            borderWidth: 2
                        },
                        {
                            label: 'Last Year',
                            data: lastYear,
                            backgroundColor: 'rgba(255, 159, 64, 0.6)',
                            borderColor: 'rgba(255, 159, 64, 1)',
                            borderWidth: 2
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        title: {
                            display: true,
                            text: 'Monthly Comparison'
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 180,
                            title: {
                                display: true,
                                text: 'Total Hours'
                            }
                        }
                    }
                }
            });
        }

        function initializeReportFilters() {
            const form = document.getElementById('report-filter-form');
            const inputs = form.querySelectorAll('input, select');
            
            inputs.forEach(input => {
                input.addEventListener('change', function() {
                    // Auto-submit form after a short delay
                    setTimeout(() => {
                        form.submit();
                    }, 500);
                });
            });
        }

        function clearFilters() {
            document.getElementById('start_date').value = '';
            document.getElementById('end_date').value = '';
            document.getElementById('employee_id').value = '';
            document.getElementById('report_type').value = 'summary';
            document.getElementById('report-filter-form').submit();
        }

        function quickReport(period) {
            const today = new Date();
            const startDate = document.getElementById('start_date');
            const endDate = document.getElementById('end_date');
            
            if (period === 'this-week') {
                const start = new Date(today);
                start.setDate(today.getDate() - today.getDay() + 1); // Monday
                startDate.value = start.toISOString().split('T')[0];
                
                const end = new Date(start);
                end.setDate(start.getDate() + 6); // Sunday
                endDate.value = end.toISOString().split('T')[0];
            } else if (period === 'this-month') {
                startDate.value = new Date(today.getFullYear(), today.getMonth(), 1).toISOString().split('T')[0];
                endDate.value = new Date(today.getFullYear(), today.getMonth() + 1, 0).toISOString().split('T')[0];
            }
            
            document.getElementById('report-filter-form').submit();
        }
    </script>
</body>
</html>