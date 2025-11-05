<?php
session_start();

// Simulate current employee (in real app, this would come from authentication)
$employee_id = isset($_SESSION['employee_id']) ? $_SESSION['employee_id'] : 1;
$employee_name = isset($_SESSION['employee_name']) ? $_SESSION['employee_name'] : 'John Doe';

// Database connection (simulate - replace with actual DB)
$host = 'localhost';
$dbname = 'attendance_system';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // For demo purposes, we'll simulate the database
    $pdo = null;
}

// Get report parameters
$report_type = isset($_GET['report_type']) ? $_GET['report_type'] : 'monthly';
$date_from = isset($_GET['date_from']) ? $_GET['date_from'] : date('Y-m-01');
$date_to = isset($_GET['date_to']) ? $_GET['date_to'] : date('Y-m-t');
$employee_filter = isset($_GET['employee_filter']) ? $_GET['employee_filter'] : $employee_id;

// Set date ranges based on report type
switch ($report_type) {
    case 'weekly':
        $date_from = date('Y-m-d', strtotime('monday this week'));
        $date_to = date('Y-m-d', strtotime('sunday this week'));
        break;
    case 'monthly':
        $date_from = date('Y-m-01');
        $date_to = date('Y-m-t');
        break;
    case 'yearly':
        $date_from = date('Y-01-01');
        $date_to = date('Y-12-31');
        break;
}

// Build query for attendance data
$where_conditions = ["employee_id = ?"];
$params = [$employee_filter];

if ($date_from) {
    $where_conditions[] = "DATE(check_in_time) >= ?";
    $params[] = $date_from;
}

if ($date_to) {
    $where_conditions[] = "DATE(check_in_time) <= ?";
    $params[] = $date_to;
}

$where_clause = implode(' AND ', $where_conditions);

// Get attendance statistics
$total_records = 0;
$total_hours = 0;
$total_overtime = 0;
$present_days = 0;
$late_arrivals = 0;
$early_departures = 0;

if ($pdo) {
    // Get total records and basic stats
    $stmt = $pdo->prepare("
        SELECT 
            COUNT(*) as total_records,
            COALESCE(SUM(total_hours), 0) as total_hours,
            COALESCE(SUM(CASE WHEN status = 'overtime' THEN total_hours ELSE 0 END), 0) as total_overtime,
            COALESCE(SUM(CASE WHEN status IN ('present', 'overtime') THEN 1 ELSE 0 END), 0) as present_days,
            COALESCE(SUM(CASE WHEN status = 'late' THEN 1 ELSE 0 END), 0) as late_arrivals,
            COALESCE(SUM(CASE WHEN status = 'early' THEN 1 ELSE 0 END), 0) as early_departures
        FROM attendance 
        WHERE $where_clause
    ");
    $stmt->execute($params);
    $stats = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $total_records = $stats['total_records'];
    $total_hours = $stats['total_hours'];
    $total_overtime = $stats['total_overtime'];
    $present_days = $stats['present_days'];
    $late_arrivals = $stats['late_arrivals'];
    $early_departures = $stats['early_departures'];
} else {
    // Simulate data for demo
    $total_records = 22;
    $total_hours = 176.5;
    $total_overtime = 12.5;
    $present_days = 20;
    $late_arrivals = 3;
    $early_departures = 1;
}

// Get daily breakdown for charts
$daily_data = [];
if ($pdo) {
    $stmt = $pdo->prepare("
        SELECT 
            DATE(check_in_time) as work_date,
            SUM(total_hours) as daily_hours,
            COUNT(*) as records_count,
            CASE 
                WHEN SUM(CASE WHEN status = 'late' THEN 1 ELSE 0 END) > 0 THEN 'late'
                WHEN SUM(CASE WHEN status = 'early' THEN 1 ELSE 0 END) > 0 THEN 'early'
                WHEN SUM(CASE WHEN status = 'overtime' THEN 1 ELSE 0 END) > 0 THEN 'overtime'
                WHEN SUM(CASE WHEN status IN ('present', 'overtime') THEN 1 ELSE 0 END) > 0 THEN 'present'
                ELSE 'absent'
            END as day_status
        FROM attendance 
        WHERE $where_clause
        GROUP BY DATE(check_in_time)
        ORDER BY work_date DESC
    ");
    $stmt->execute($params);
    $daily_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    // Simulate daily data
    for ($i = 0; $i < 22; $i++) {
        $date = date('Y-m-d', strtotime("-$i days"));
        $is_weekend = date('N', strtotime($date)) >= 6;
        
        if (!$is_weekend && rand(0, 5) < 4) { // 80% chance of working day
            $hours = rand(7, 10);
            if ($hours > 8) $overtime = $hours - 8;
            
            $status = 'present';
            if (rand(0, 10) > 8) $status = 'late';
            elseif (rand(0, 20) > 18) $status = 'early';
            elseif ($hours > 8) $status = 'overtime';
            
            $daily_data[] = [
                'work_date' => $date,
                'daily_hours' => $hours,
                'records_count' => 1,
                'day_status' => $status
            ];
        }
    }
}

// Calculate averages
$working_days = $report_type === 'weekly' ? 5 : ($report_type === 'monthly' ? 22 : 260);
$avg_hours_per_day = $present_days > 0 ? round($total_hours / $present_days, 1) : 0;
$avg_hours_per_working_day = $present_days > 0 ? round($total_hours / $present_days, 1) : 0;
$attendance_rate = $working_days > 0 ? round(($present_days / $working_days) * 100, 1) : 0;

// Get weekly breakdown
$weekly_data = [];
if ($pdo) {
    $stmt = $pdo->prepare("
        SELECT 
            YEARWEEK(check_in_time, 1) as work_week,
            SUM(total_hours) as weekly_hours,
            COUNT(DISTINCT DATE(check_in_time)) as working_days
        FROM attendance 
        WHERE $where_clause
        GROUP BY YEARWEEK(check_in_time, 1)
        ORDER BY work_week DESC
    ");
    $stmt->execute($params);
    $weekly_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    // Simulate weekly data
    $current_week = date('Y-W');
    for ($i = 0; $i < 4; $i++) {
        $week = date('Y-W', strtotime("-$i weeks"));
        $weekly_data[] = [
            'work_week' => $week,
            'weekly_hours' => rand(35, 45),
            'working_days' => rand(4, 5)
        ];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance Report - HR Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.css" rel="stylesheet">
    <link href="../../assets/css/attendance.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="../../index.php">
                <i class="fas fa-building"></i> HR Management System
            </a>
            <div class="navbar-nav ms-auto">
                <span class="navbar-text me-3">
                    <i class="fas fa-user"></i> Welcome, <?php echo htmlspecialchars($employee_name); ?>
                </span>
                <a href="index.php" class="btn btn-outline-light btn-sm me-2">
                    <i class="fas fa-home"></i> Dashboard
                </a>
                <a href="logout.php" class="btn btn-outline-light btn-sm">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>
    </nav>

    <div class="container-fluid mt-4">
        <!-- Page Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card bg-gradient bg-success text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h1 class="display-6 mb-0">
                                    <i class="fas fa-chart-line"></i> Attendance Report
                                </h1>
                                <p class="mb-0">Detailed analysis and insights for <?php echo htmlspecialchars($employee_name); ?></p>
                            </div>
                            <div>
                                <button class="btn btn-light" onclick="generatePDF()">
                                    <i class="fas fa-file-pdf"></i> Export PDF
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Report Filters -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-filter"></i> Report Filters
                        </h5>
                    </div>
                    <div class="card-body">
                        <form method="GET" id="report-form">
                            <div class="row g-3">
                                <div class="col-md-2">
                                    <label for="report_type" class="form-label">Report Type</label>
                                    <select class="form-select" id="report_type" name="report_type">
                                        <option value="weekly" <?php echo $report_type === 'weekly' ? 'selected' : ''; ?>>Weekly</option>
                                        <option value="monthly" <?php echo $report_type === 'monthly' ? 'selected' : ''; ?>>Monthly</option>
                                        <option value="yearly" <?php echo $report_type === 'yearly' ? 'selected' : ''; ?>>Yearly</option>
                                        <option value="custom" <?php echo $report_type === 'custom' ? 'selected' : ''; ?>>Custom</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label for="date_from" class="form-label">From Date</label>
                                    <input type="date" class="form-control" id="date_from" name="date_from" 
                                           value="<?php echo htmlspecialchars($date_from); ?>">
                                </div>
                                <div class="col-md-3">
                                    <label for="date_to" class="form-label">To Date</label>
                                    <input type="date" class="form-control" id="date_to" name="date_to" 
                                           value="<?php echo htmlspecialchars($date_to); ?>">
                                </div>
                                <div class="col-md-2">
                                    <label for="employee_filter" class="form-label">Employee</label>
                                    <select class="form-select" id="employee_filter" name="employee_filter">
                                        <option value="<?php echo $employee_id; ?>" selected>Current User</option>
                                        <!-- Add more employees if needed -->
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">&nbsp;</label>
                                    <div class="d-grid">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-sync"></i> Generate Report
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Summary Statistics -->
        <div class="row mb-4">
            <div class="col-md-2">
                <div class="card bg-primary text-white text-center">
                    <div class="card-body">
                        <h3 class="mb-0"><?php echo $total_records; ?></h3>
                        <small>Total Records</small>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card bg-success text-white text-center">
                    <div class="card-body">
                        <h3 class="mb-0"><?php echo number_format($total_hours, 1); ?></h3>
                        <small>Total Hours</small>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card bg-info text-white text-center">
                    <div class="card-body">
                        <h3 class="mb-0"><?php echo number_format($avg_hours_per_working_day, 1); ?></h3>
                        <small>Avg Hours/Day</small>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card bg-warning text-white text-center">
                    <div class="card-body">
                        <h3 class="mb-0"><?php echo $present_days; ?></h3>
                        <small>Present Days</small>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card bg-danger text-white text-center">
                    <div class="card-body">
                        <h3 class="mb-0"><?php echo $late_arrivals; ?></h3>
                        <small>Late Arrivals</small>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card bg-secondary text-white text-center">
                    <div class="card-body">
                        <h3 class="mb-0"><?php echo number_format($attendance_rate, 1); ?>%</h3>
                        <small>Attendance Rate</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Row -->
        <div class="row mb-4">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-chart-bar"></i> Daily Hours Trend
                        </h5>
                    </div>
                    <div class="card-body">
                        <canvas id="dailyHoursChart" height="100"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-chart-pie"></i> Attendance Distribution
                        </h5>
                    </div>
                    <div class="card-body">
                        <canvas id="statusChart" height="200"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Additional Charts -->
        <div class="row mb-4">
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-chart-line"></i> Weekly Performance
                        </h5>
                    </div>
                    <div class="card-body">
                        <canvas id="weeklyChart" height="100"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-calendar-alt"></i> Monthly Comparison
                        </h5>
                    </div>
                    <div class="card-body">
                        <canvas id="monthlyChart" height="100"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Detailed Table -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">
                            <i class="fas fa-table"></i> Detailed Daily Breakdown
                        </h4>
                        <div>
                            <button class="btn btn-success btn-sm" onclick="exportToExcel()">
                                <i class="fas fa-file-excel"></i> Export Excel
                            </button>
                            <button class="btn btn-primary btn-sm" onclick="printReport()">
                                <i class="fas fa-print"></i> Print
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Date</th>
                                        <th>Day</th>
                                        <th>Status</th>
                                        <th>Check In</th>
                                        <th>Check Out</th>
                                        <th>Total Hours</th>
                                        <th>Overtime</th>
                                        <th>Notes</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($daily_data)): ?>
                                        <tr>
                                            <td colspan="8" class="text-center py-4">
                                                <i class="fas fa-chart-line fa-3x text-muted mb-3"></i>
                                                <p class="text-muted">No data available for the selected period.</p>
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach (array_slice($daily_data, 0, 15) as $day): 
                                            $date_obj = new DateTime($day['work_date']);
                                            $date_formatted = $date_obj->format('M d, Y');
                                            $day_name = $date_obj->format('l');
                                            $is_weekend = $date_obj->format('N') >= 6;
                                            
                                            $hours = $day['daily_hours'];
                                            $overtime = max(0, $hours - 8);
                                            
                                            // Status badge
                                            $status_class = '';
                                            $status_icon = '';
                                            switch ($day['day_status']) {
                                                case 'present':
                                                    $status_class = 'bg-success';
                                                    $status_icon = 'fa-check';
                                                    break;
                                                case 'overtime':
                                                    $status_class = 'bg-warning';
                                                    $status_icon = 'fa-clock';
                                                    break;
                                                case 'late':
                                                    $status_class = 'bg-danger';
                                                    $status_icon = 'fa-exclamation-triangle';
                                                    break;
                                                case 'early':
                                                    $status_class = 'bg-info';
                                                    $status_icon = 'fa-exclamation-circle';
                                                    break;
                                                default:
                                                    $status_class = 'bg-secondary';
                                                    $status_icon = 'fa-times';
                                            }
                                        ?>
                                            <tr class="<?php echo $is_weekend ? 'table-secondary' : ''; ?>">
                                                <td><strong><?php echo $date_formatted; ?></strong></td>
                                                <td>
                                                    <?php echo $day_name; ?>
                                                    <?php if ($is_weekend): ?>
                                                        <small class="text-muted">(Weekend)</small>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <span class="badge <?php echo $status_class; ?>">
                                                        <i class="fas <?php echo $status_icon; ?> me-1"></i>
                                                        <?php echo ucfirst($day['day_status']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <small class="text-muted">08:30 AM</small>
                                                </td>
                                                <td>
                                                    <small class="text-muted">05:30 PM</small>
                                                </td>
                                                <td>
                                                    <strong><?php echo number_format($hours, 1); ?>h</strong>
                                                </td>
                                                <td>
                                                    <?php if ($overtime > 0): ?>
                                                        <span class="badge bg-warning"><?php echo number_format($overtime, 1); ?>h</span>
                                                    <?php else: ?>
                                                        <span class="text-muted">-</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <small class="text-muted">Regular work day</small>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Performance Insights -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-lightbulb"></i> Performance Insights
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="alert alert-success">
                                    <h6><i class="fas fa-thumbs-up"></i> Strengths</h6>
                                    <ul class="mb-0">
                                        <li>Excellent attendance rate of <?php echo $attendance_rate; ?>%</li>
                                        <li>Consistent working hours</li>
                                        <li>Good punctuality record</li>
                                    </ul>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="alert alert-warning">
                                    <h6><i class="fas fa-exclamation-triangle"></i> Areas for Improvement</h6>
                                    <ul class="mb-0">
                                        <li>Monitor late arrivals (<?php echo $late_arrivals; ?> occurrences)</li>
                                        <li>Track overtime hours closely</li>
                                        <li>Maintain consistent break times</li>
                                    </ul>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="alert alert-info">
                                    <h6><i class="fas fa-chart-line"></i> Trends</h6>
                                    <ul class="mb-0">
                                        <li>Average hours: <?php echo $avg_hours_per_working_day; ?> per day</li>
                                        <li>Peak performance on <?php echo date('l', strtotime('monday')); ?>s</li>
                                        <li>Consistent weekly schedule</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
    <script src="../../assets/js/attendance.js"></script>
    <script>
        // Initialize charts when page loads
        document.addEventListener('DOMContentLoaded', function() {
            initializeCharts();
        });
    </script>
</body>
</html>