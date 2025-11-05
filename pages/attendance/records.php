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

// Get filter parameters
$date_from = isset($_GET['date_from']) ? $_GET['date_from'] : date('Y-m-d', strtotime('-30 days'));
$date_to = isset($_GET['date_to']) ? $_GET['date_to'] : date('Y-m-d');
$status_filter = isset($_GET['status_filter']) ? $_GET['status_filter'] : '';
$per_page = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 20;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $per_page;

// Build query
$where_conditions = ["employee_id = ?"];
$params = [$employee_id];

if ($date_from) {
    $where_conditions[] = "DATE(check_in_time) >= ?";
    $params[] = $date_from;
}

if ($date_to) {
    $where_conditions[] = "DATE(check_in_time) <= ?";
    $params[] = $date_to;
}

if ($status_filter) {
    $where_conditions[] = "status = ?";
    $params[] = $status_filter;
}

$where_clause = implode(' AND ', $where_conditions);

// Get total records
$total_records = 0;
if ($pdo) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM attendance WHERE $where_clause");
    $stmt->execute($params);
    $total_records = $stmt->fetchColumn();
} else {
    // Simulate data for demo
    $total_records = 50;
}

// Get attendance records
$records = [];
if ($pdo) {
    $stmt = $pdo->prepare("
        SELECT * FROM attendance 
        WHERE $where_clause 
        ORDER BY check_in_time DESC 
        LIMIT $per_page OFFSET $offset
    ");
    $stmt->execute($params);
    $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    // Simulate data for demo
    for ($i = 0; $i < min($per_page, 20); $i++) {
        $check_in = date('Y-m-d H:i:s', strtotime("-$i days -" . rand(0, 4) . " hours"));
        $check_out = null;
        $total_hours = 0;
        $status = 'present';
        
        if (rand(0, 3) > 0) { // 75% chance of having check-out time
            $check_out_time = date('Y-m-d H:i:s', strtotime($check_in . ' +' . rand(7, 10) . ' hours'));
            $check_out = $check_out_time;
            $total_hours = round((strtotime($check_out_time) - strtotime($check_in)) / 3600, 2);
            
            if (rand(0, 10) > 8) { // 20% chance of overtime
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
}

$total_pages = ceil($total_records / $per_page);

// Calculate summary statistics
$total_hours_all = 0;
$present_days = 0;
$incomplete_days = 0;

foreach ($records as $record) {
    $total_hours_all += $record['total_hours'];
    if ($record['status'] === 'present' || $record['status'] === 'overtime') {
        $present_days++;
    } elseif ($record['status'] === 'incomplete') {
        $incomplete_days++;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance Records - HR Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
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
                <a href="../../index.php" class="btn btn-outline-light btn-sm me-2">
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
                <div class="card bg-gradient bg-primary text-white">
                    <div class="card-body">
                        <h1 class="display-6 mb-0">
                            <i class="fas fa-list-alt"></i> Attendance Records
                        </h1>
                        <p class="mb-0">View and manage your attendance history</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card bg-primary text-white">
                    <div class="card-body text-center">
                        <h3 class="mb-0"><?php echo count($records); ?></h3>
                        <small>Records Found</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body text-center">
                        <h3 class="mb-0"><?php echo number_format($total_hours_all, 1); ?></h3>
                        <small>Total Hours</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-info text-white">
                    <div class="card-body text-center">
                        <h3 class="mb-0"><?php echo $present_days; ?></h3>
                        <small>Present Days</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-warning text-white">
                    <div class="card-body text-center">
                        <h3 class="mb-0"><?php echo $incomplete_days; ?></h3>
                        <small>Incomplete</small>
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
                            <i class="fas fa-filter"></i> Filter Records
                        </h5>
                    </div>
                    <div class="card-body">
                        <form method="GET" id="filter-form">
                            <div class="row g-3">
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
                                <div class="col-md-3">
                                    <label for="status_filter" class="form-label">Status</label>
                                    <select class="form-select" id="status_filter" name="status_filter">
                                        <option value="">All Statuses</option>
                                        <option value="present" <?php echo $status_filter === 'present' ? 'selected' : ''; ?>>Present</option>
                                        <option value="absent" <?php echo $status_filter === 'absent' ? 'selected' : ''; ?>>Absent</option>
                                        <option value="late" <?php echo $status_filter === 'late' ? 'selected' : ''; ?>>Late</option>
                                        <option value="overtime" <?php echo $status_filter === 'overtime' ? 'selected' : ''; ?>>Overtime</option>
                                        <option value="incomplete" <?php echo $status_filter === 'incomplete' ? 'selected' : ''; ?>>Incomplete</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label for="per_page" class="form-label">Records per page</label>
                                    <select class="form-select" id="per_page" name="per_page">
                                        <option value="10" <?php echo $per_page == 10 ? 'selected' : ''; ?>>10</option>
                                        <option value="20" <?php echo $per_page == 20 ? 'selected' : ''; ?>>20</option>
                                        <option value="50" <?php echo $per_page == 50 ? 'selected' : ''; ?>>50</option>
                                        <option value="100" <?php echo $per_page == 100 ? 'selected' : ''; ?>>100</option>
                                    </select>
                                </div>
                            </div>
                            <div class="row mt-3">
                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary me-2">
                                        <i class="fas fa-search"></i> Apply Filters
                                    </button>
                                    <button type="button" class="btn btn-secondary me-2" onclick="clearFilters()">
                                        <i class="fas fa-times"></i> Clear
                                    </button>
                                    <button type="button" class="btn btn-success" onclick="exportRecords()">
                                        <i class="fas fa-download"></i> Export CSV
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Records Table -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">
                            <i class="fas fa-table"></i> Attendance Records
                        </h4>
                        <div>
                            <a href="index.php" class="btn btn-outline-primary btn-sm me-2">
                                <i class="fas fa-plus"></i> Quick Check-in
                            </a>
                            <a href="report.php" class="btn btn-outline-success btn-sm">
                                <i class="fas fa-chart-line"></i> Generate Report
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover" id="attendance-table">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Date</th>
                                        <th>Day</th>
                                        <th>Check In</th>
                                        <th>Check Out</th>
                                        <th>Total Hours</th>
                                        <th>Break Time</th>
                                        <th>Status</th>
                                        <th>Notes</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($records)): ?>
                                        <tr>
                                            <td colspan="9" class="text-center py-4">
                                                <i class="fas fa-search fa-3x text-muted mb-3"></i>
                                                <p class="text-muted">No attendance records found for the selected criteria.</p>
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($records as $record): 
                                            $check_in_date = new DateTime($record['check_in_time']);
                                            $check_in_formatted = $check_in_date->format('h:i A');
                                            $check_in_day = $check_in_date->format('l');
                                            $date_formatted = $check_in_date->format('M d, Y');
                                            
                                            $check_out_formatted = '-';
                                            $break_time = '-';
                                            if ($record['check_out_time']) {
                                                $check_out_date = new DateTime($record['check_out_time']);
                                                $check_out_formatted = $check_out_date->format('h:i A');
                                                $break_time = '1.0'; // Simulate break time
                                            }
                                            
                                            $total_hours = $record['total_hours'] ? number_format($record['total_hours'], 1) : '0.0';
                                            
                                            // Status badge
                                            $status_class = '';
                                            $status_icon = '';
                                            switch ($record['status']) {
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
                                                case 'incomplete':
                                                    $status_class = 'bg-info';
                                                    $status_icon = 'fa-minus';
                                                    break;
                                                default:
                                                    $status_class = 'bg-secondary';
                                                    $status_icon = 'fa-question';
                                            }
                                        ?>
                                            <tr>
                                                <td><strong><?php echo $date_formatted; ?></strong></td>
                                                <td><?php echo $check_in_day; ?></td>
                                                <td>
                                                    <i class="fas fa-sign-in-alt text-success me-1"></i>
                                                    <?php echo $check_in_formatted; ?>
                                                </td>
                                                <td>
                                                    <?php if ($record['check_out_time']): ?>
                                                        <i class="fas fa-sign-out-alt text-danger me-1"></i>
                                                        <?php echo $check_out_formatted; ?>
                                                    <?php else: ?>
                                                        <span class="text-muted">-</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <span class="badge bg-primary"><?php echo $total_hours; ?>h</span>
                                                </td>
                                                <td>
                                                    <small class="text-muted"><?php echo $break_time; ?>h</small>
                                                </td>
                                                <td>
                                                    <span class="badge <?php echo $status_class; ?>">
                                                        <i class="fas <?php echo $status_icon; ?> me-1"></i>
                                                        <?php echo ucfirst($record['status']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <small class="text-muted">
                                                        <?php echo $record['notes'] ?: '-'; ?>
                                                    </small>
                                                </td>
                                                <td>
                                                    <div class="btn-group btn-group-sm">
                                                        <button class="btn btn-outline-primary" 
                                                                onclick="viewDetails(<?php echo $record['id']; ?>)" 
                                                                title="View Details">
                                                            <i class="fas fa-eye"></i>
                                                        </button>
                                                        <?php if (!$record['check_out_time']): ?>
                                                            <button class="btn btn-outline-success" 
                                                                    onclick="quickCheckout(<?php echo $record['id']; ?>)" 
                                                                    title="Quick Check-out">
                                                                <i class="fas fa-sign-out-alt"></i>
                                                            </button>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <?php if ($total_pages > 1): ?>
                            <nav aria-label="Page navigation" class="mt-4">
                                <ul class="pagination justify-content-center">
                                    <?php if ($page > 1): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>">
                                                <i class="fas fa-chevron-left"></i> Previous
                                            </a>
                                        </li>
                                    <?php endif; ?>

                                    <?php
                                    $start_page = max(1, $page - 2);
                                    $end_page = min($total_pages, $page + 2);
                                    
                                    for ($i = $start_page; $i <= $end_page; $i++):
                                    ?>
                                        <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                            <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>">
                                                <?php echo $i; ?>
                                            </a>
                                        </li>
                                    <?php endfor; ?>

                                    <?php if ($page < $total_pages): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>">
                                                Next <i class="fas fa-chevron-right"></i>
                                            </a>
                                        </li>
                                    <?php endif; ?>
                                </ul>
                            </nav>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Detail Modal -->
    <div class="modal fade" id="detailModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-info-circle"></i> Attendance Details
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="modal-content">
                    <!-- Content will be loaded via AJAX -->
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../../assets/js/attendance.js"></script>
</body>
</html>