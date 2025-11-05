<?php
require_once '../../includes/config.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

// Check if user is logged in and has admin privileges
if (!isLoggedIn() || !isAdmin()) {
    header('Location: ../../login.php');
    exit();
}

$pageTitle = "Attendance Reports Dashboard";

// Get date range for reports (default to current month)
$startDate = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01');
$endDate = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-t');
$department = isset($_GET['department']) ? $_GET['department'] : '';

// Database connection
$pdo = getDBConnection();

// Get attendance statistics
$stats = [];

// Total attendance count
$query = "SELECT COUNT(*) as total FROM attendance WHERE DATE(timestamp) BETWEEN :start_date AND :end_date";
$params = [':start_date' => $startDate, ':end_date' => $endDate];

if (!empty($department)) {
    $query .= " AND department = :department";
    $params[':department'] = $department;
}

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$stats['total_attendance'] = $stmt->fetch()['total'];

// Daily attendance breakdown
$query = "SELECT DATE(timestamp) as date, COUNT(*) as count 
          FROM attendance 
          WHERE DATE(timestamp) BETWEEN :start_date AND :end_date";
$params = [':start_date' => $startDate, ':end_date' => $endDate];

if (!empty($department)) {
    $query .= " AND department = :department";
    $params[':department'] = $department;
}

$query .= " GROUP BY DATE(timestamp) ORDER BY date";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$dailyStats = $stmt->fetchAll();

// Department breakdown
$query = "SELECT department, COUNT(*) as count 
          FROM attendance 
          WHERE DATE(timestamp) BETWEEN :start_date AND :end_date 
          GROUP BY department 
          ORDER BY count DESC";
$stmt = $pdo->prepare($query);
$stmt->execute([':start_date' => $startDate, ':end_date' => $endDate]);
$deptStats = $stmt->fetchAll();

// Peak hours analysis
$query = "SELECT HOUR(timestamp) as hour, COUNT(*) as count 
          FROM attendance 
          WHERE DATE(timestamp) BETWEEN :start_date AND :end_date 
          GROUP BY HOUR(timestamp) 
          ORDER BY hour";
$stmt = $pdo->prepare($query);
$stmt->execute([':start_date' => $startDate, ':end_date' => $endDate]);
$hourlyStats = $stmt->fetchAll();

// Get available departments
$query = "SELECT DISTINCT department FROM attendance ORDER BY department";
$stmt = $pdo->query($query);
$departments = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - Attendance System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../../assets/css/dashboard.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <?php include '../../includes/header.php'; ?>
    
    <div class="container-fluid">
        <div class="row">
            <?php include '../../includes/sidebar.php'; ?>
            
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2"><?php echo $pageTitle; ?></h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <a href="export.php?start_date=<?php echo $startDate; ?>&end_date=<?php echo $endDate; ?>&department=<?php echo $department; ?>" class="btn btn-sm btn-outline-secondary">
                                <i class="fas fa-download"></i> Export CSV
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Filters -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <div class="col-md-3">
                                <label for="start_date" class="form-label">Start Date</label>
                                <input type="date" class="form-control" id="start_date" name="start_date" value="<?php echo $startDate; ?>">
                            </div>
                            <div class="col-md-3">
                                <label for="end_date" class="form-label">End Date</label>
                                <input type="date" class="form-control" id="end_date" name="end_date" value="<?php echo $endDate; ?>">
                            </div>
                            <div class="col-md-3">
                                <label for="department" class="form-label">Department</label>
                                <select class="form-select" id="department" name="department">
                                    <option value="">All Departments</option>
                                    <?php foreach ($departments as $dept): ?>
                                        <option value="<?php echo htmlspecialchars($dept['department']); ?>" 
                                                <?php echo ($dept['department'] == $department) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($dept['department']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-filter"></i> Apply Filters
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Summary Cards -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card bg-primary text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h4><?php echo number_format($stats['total_attendance']); ?></h4>
                                        <p class="mb-0">Total Attendance</p>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="fas fa-users fa-2x"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-success text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h4><?php echo count($deptStats); ?></h4>
                                        <p class="mb-0">Departments</p>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="fas fa-building fa-2x"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-info text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h4><?php echo count($dailyStats); ?></h4>
                                        <p class="mb-0">Active Days</p>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="fas fa-calendar fa-2x"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-warning text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h4><?php echo $stats['total_attendance'] > 0 ? round($stats['total_attendance'] / count($dailyStats), 1) : 0; ?></h4>
                                        <p class="mb-0">Avg Daily</p>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="fas fa-chart-line fa-2x"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Charts -->
                <div class="row mb-4">
                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title">Daily Attendance Trend</h5>
                            </div>
                            <div class="card-body">
                                <canvas id="dailyChart" width="400" height="200"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title">Department Distribution</h5>
                            </div>
                            <div class="card-body">
                                <canvas id="departmentChart" width="400" height="200"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title">Hourly Distribution</h5>
                            </div>
                            <div class="card-body">
                                <canvas id="hourlyChart" width="400" height="200"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Data Table -->
                <div class="card mt-4">
                    <div class="card-header">
                        <h5 class="card-title">Detailed Records</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Employee Name</th>
                                        <th>Department</th>
                                        <th>Time In</th>
                                        <th>Time Out</th>
                                        <th>Total Hours</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $query = "SELECT a.*, e.name as employee_name 
                                              FROM attendance a 
                                              LEFT JOIN employees e ON a.employee_id = e.id 
                                              WHERE DATE(a.timestamp) BETWEEN :start_date AND :end_date";
                                    $params = [':start_date' => $startDate, ':end_date' => $endDate];
                                    
                                    if (!empty($department)) {
                                        $query .= " AND a.department = :department";
                                        $params[':department'] = $department;
                                    }
                                    
                                    $query .= " ORDER BY a.timestamp DESC LIMIT 100";
                                    $stmt = $pdo->prepare($query);
                                    $stmt->execute($params);
                                    $records = $stmt->fetchAll();
                                    
                                    foreach ($records as $record):
                                    ?>
                                        <tr>
                                            <td><?php echo date('Y-m-d', strtotime($record['timestamp'])); ?></td>
                                            <td><?php echo htmlspecialchars($record['employee_name'] ?? 'Unknown'); ?></td>
                                            <td><?php echo htmlspecialchars($record['department']); ?></td>
                                            <td><?php echo date('H:i', strtotime($record['timestamp'])); ?></td>
                                            <td><?php echo $record['time_out'] ? date('H:i', strtotime($record['time_out'])) : '-'; ?></td>
                                            <td><?php echo $record['total_hours'] ? number_format($record['total_hours'], 2) : '-'; ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="../../assets/js/reports.js"></script>
    <script>
        // Initialize charts with PHP data
        const dailyData = <?php echo json_encode($dailyStats); ?>;
        const departmentData = <?php echo json_encode($deptStats); ?>;
        const hourlyData = <?php echo json_encode($hourlyStats); ?>;
        
        ReportsDashboard.initCharts(dailyData, departmentData, hourlyData);
    </script>
</body>
</html>