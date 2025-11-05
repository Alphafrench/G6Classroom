<?php
session_start();

// Authentication check
require_once '../../includes/auth.php';
require_once '../../includes/class.Database.php';
require_once '../../includes/class.Attendance.php';
require_once '../../includes/functions.php';

$employee_id = $_SESSION['employee_id'];
$employee_name = $_SESSION['employee_name'];

try {
    $db = getDatabase();
    $attendance = new Attendance($db);
    
    // Get current attendance status
    $currentStatus = $attendance->getCurrentStatus($employee_id);
    
    // Get recent attendance records
    $recentRecords = $attendance->getEmployeeAttendance($employee_id, null, null, 5, 0);
    
    // Get today's statistics
    $today = date('Y-m-d');
    $weekStart = date('Y-m-d', strtotime('monday this week'));
    $monthStart = date('Y-m-d', strtotime('first day of this month'));
    
    $todayStats = $attendance->getEmployeeSummary($employee_id, $today, $today);
    $weekStats = $attendance->getEmployeeSummary($employee_id, $weekStart, $today);
    $monthStats = $attendance->getEmployeeSummary($employee_id, $monthStart, $today);
    
} catch (Exception $e) {
    logError("Dashboard error: " . $e->getMessage());
    $currentStatus = null;
    $recentRecords = ['records' => []];
    $todayStats = $weekStats = $monthStats = ['days_worked' => 0, 'total_hours' => 0];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance Dashboard - HR Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../../assets/css/attendance.css" rel="stylesheet">
    <style>
        .attendance-timer {
            font-family: 'Courier New', monospace;
            font-size: 2rem;
            font-weight: bold;
            color: #28a745;
        }
        
        .status-indicator {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 8px;
            animation: pulse 2s infinite;
        }
        
        .status-present { background-color: #28a745; }
        .status-absent { background-color: #dc3545; }
        .status-checked-in { background-color: #17a2b8; }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="../../pages/dashboard.php">
                <i class="fas fa-building"></i> HR Management System
            </a>
            <div class="navbar-nav ms-auto">
                <span class="navbar-text me-3">
                    <i class="fas fa-user"></i> Welcome, <?php echo htmlspecialchars($employee_name); ?>
                </span>
                <a href="../../pages/logout.php" class="btn btn-outline-light btn-sm">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>
    </nav>

    <div class="container-fluid mt-4">
        <!-- Real-time Clock -->
        <div class="row">
            <div class="col-12">
                <div class="card bg-dark text-white mb-4 shadow-custom">
                    <div class="card-body text-center">
                        <h1 id="current-time" class="display-4 fw-bold">
                            <i class="fas fa-clock"></i> 
                            <span id="time-display">--:--:--</span>
                        </h1>
                        <p class="lead mb-0" id="current-date"></p>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Current Attendance Status -->
            <div class="col-lg-8">
                <div class="card shadow-custom">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">
                            <i class="fas fa-user-clock"></i> 
                            Today's Attendance Status
                        </h4>
                    </div>
                    <div class="card-body text-center">
                        <?php if ($currentStatus): ?>
                            <div class="alert alert-success" role="alert">
                                <h5>
                                    <span class="status-indicator status-checked-in"></span>
                                    You are currently checked in!
                                </h5>
                                <p class="mb-2">
                                    <strong>Check-in time:</strong> 
                                    <?php echo formatDateTime($currentStatus['clock_in']); ?>
                                </p>
                                <div class="attendance-timer" id="work-timer">
                                    <i class="fas fa-play"></i> 00:00:00
                                </div>
                                <p class="mt-3">
                                    <button id="check-out-btn" class="btn btn-danger btn-lg btn-attendance" 
                                            onclick="handleCheckout()">
                                        <i class="fas fa-sign-out-alt"></i> Check Out
                                    </button>
                                </p>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-warning" role="alert">
                                <h5><i class="fas fa-exclamation-triangle"></i> Not checked in yet</h5>
                                <p class="mb-3">Please check in to start your work day</p>
                                <button id="check-in-btn" class="btn btn-success btn-lg btn-attendance" 
                                        onclick="handleCheckin()">
                                    <i class="fas fa-sign-in-alt"></i> Check In
                                </button>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Quick Statistics -->
            <div class="col-lg-4">
                <div class="row g-3">
                    <div class="col-6">
                        <div class="card bg-primary text-white stats-card">
                            <div class="card-body text-center">
                                <h3 class="mb-0" id="today-hours"><?php echo number_format($todayStats['total_hours'], 1); ?></h3>
                                <small>Today's Hours</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="card bg-success text-white stats-card">
                            <div class="card-body text-center">
                                <h3 class="mb-0" id="week-hours"><?php echo number_format($weekStats['total_hours'], 1); ?></h3>
                                <small>This Week</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="card bg-info text-white stats-card">
                            <div class="card-body text-center">
                                <h3 class="mb-0" id="month-hours"><?php echo number_format($monthStats['total_hours'], 1); ?></h3>
                                <small>This Month</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="card bg-warning text-white stats-card">
                            <div class="card-body text-center">
                                <h3 class="mb-0" id="present-days"><?php echo $todayStats['days_worked']; ?></h3>
                                <small>Days Worked</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="mb-0">
                            <i class="fas fa-tools"></i> Quick Actions
                        </h4>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <a href="take.php" class="btn btn-outline-primary w-100 quick-action-card">
                                    <i class="fas fa-hand-paper"></i><br>
                                    Take Attendance
                                </a>
                            </div>
                            <div class="col-md-3">
                                <a href="records.php" class="btn btn-outline-success w-100 quick-action-card">
                                    <i class="fas fa-list"></i><br>
                                    View Records
                                </a>
                            </div>
                            <div class="col-md-3">
                                <a href="reports.php" class="btn btn-outline-info w-100 quick-action-card">
                                    <i class="fas fa-chart-line"></i><br>
                                    Reports & Analytics
                                </a>
                            </div>
                            <div class="col-md-3">
                                <a href="student.php" class="btn btn-outline-warning w-100 quick-action-card">
                                    <i class="fas fa-user-graduate"></i><br>
                                    Student View
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Attendance Records -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">
                            <i class="fas fa-history"></i> Recent Attendance Records
                        </h4>
                        <a href="records.php" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-eye"></i> View All Records
                        </a>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Check In</th>
                                        <th>Check Out</th>
                                        <th>Hours Worked</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="recent-records">
                                    <?php if (!empty($recentRecords['records'])): ?>
                                        <?php foreach ($recentRecords['records'] as $record): ?>
                                            <tr>
                                                <td><?php echo formatDate($record['date']); ?></td>
                                                <td>
                                                    <i class="fas fa-sign-in-alt text-success me-1"></i>
                                                    <?php echo formatDateTime($record['clock_in']); ?>
                                                </td>
                                                <td>
                                                    <?php if ($record['clock_out']): ?>
                                                        <i class="fas fa-sign-out-alt text-danger me-1"></i>
                                                        <?php echo formatDateTime($record['clock_out']); ?>
                                                    <?php else: ?>
                                                        <span class="text-muted">Still checked in</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <span class="badge bg-primary">
                                                        <?php echo $record['hours_worked'] ? number_format($record['hours_worked'], 2) . 'h' : '-'; ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="badge <?php echo $record['clock_out'] ? 'bg-success' : 'bg-info'; ?>">
                                                        <i class="fas <?php echo $record['clock_out'] ? 'fa-check' : 'fa-clock'; ?> me-1"></i>
                                                        <?php echo $record['clock_out'] ? 'Complete' : 'Active'; ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <button class="btn btn-sm btn-outline-primary" onclick="viewRecordDetails(<?php echo $record['id']; ?>)">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="6" class="text-center text-muted">
                                                <i class="fas fa-info-circle"></i> No recent records found
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
    </div>

    <!-- Record Details Modal -->
    <div class="modal fade" id="recordModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-info-circle"></i> Attendance Details
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="modal-content">
                    <div class="text-center">
                        <div class="spinner-border" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../../assets/js/attendance.js"></script>
    <script>
        // Enhanced dashboard functionality
        document.addEventListener('DOMContentLoaded', function() {
            initializeClock();
            if (document.getElementById('work-timer')) {
                startWorkTimer();
            }
        });

        function startWorkTimer() {
            const timerElement = document.getElementById('work-timer');
            let startTime = new Date('<?php echo $currentStatus ? $currentStatus['clock_in'] : date('Y-m-d H:i:s'); ?>');
            
            setInterval(() => {
                const now = new Date();
                const diff = now - startTime;
                const hours = Math.floor(diff / 3600000);
                const minutes = Math.floor((diff % 3600000) / 60000);
                const seconds = Math.floor((diff % 60000) / 1000);
                
                timerElement.innerHTML = `
                    <i class="fas fa-clock"></i> 
                    ${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}
                `;
            }, 1000);
        }

        async function handleCheckin() {
            try {
                const response = await fetch('../../api/attendance/checkin.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        employee_id: <?php echo $employee_id; ?>,
                        timestamp: new Date().toISOString(),
                        location: 'Main Office'
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showMessage('Successfully checked in! Welcome to work.', 'success');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    throw new Error(data.errors ? data.errors.join(', ') : data.message);
                }
            } catch (error) {
                showMessage('Error during check-in: ' + error.message, 'danger');
            }
        }

        async function handleCheckout() {
            try {
                const response = await fetch('../../api/attendance/checkout.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        employee_id: <?php echo $employee_id; ?>,
                        timestamp: new Date().toISOString()
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showMessage(`Successfully checked out! Hours worked: ${data.hours_worked}`, 'success');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    throw new Error(data.errors ? data.errors.join(', ') : data.message);
                }
            } catch (error) {
                showMessage('Error during check-out: ' + error.message, 'danger');
            }
        }

        function viewRecordDetails(recordId) {
            const modal = new bootstrap.Modal(document.getElementById('recordModal'));
            const modalContent = document.getElementById('modal-content');
            
            modalContent.innerHTML = `
                <div class="text-center">
                    <div class="spinner-border" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2">Loading record details...</p>
                </div>
            `;
            
            modal.show();
            
            // Load details
            fetch(`../../api/attendance/details.php?id=${recordId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const record = data.record;
                        modalContent.innerHTML = `
                            <div class="row">
                                <div class="col-md-6">
                                    <h6 class="text-primary">
                                        <i class="fas fa-sign-in-alt"></i> Check-in Information
                                    </h6>
                                    <table class="table table-sm">
                                        <tr><td><strong>Time:</strong></td><td>${new Date(record.clock_in).toLocaleString()}</td></tr>
                                        <tr><td><strong>Date:</strong></td><td>${new Date(record.date).toLocaleDateString()}</td></tr>
                                        <tr><td><strong>Location:</strong></td><td>${record.location || 'Office'}</td></tr>
                                        <tr><td><strong>IP Address:</strong></td><td>${record.ip_address || 'N/A'}</td></tr>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <h6 class="text-danger">
                                        <i class="fas fa-sign-out-alt"></i> Check-out Information
                                    </h6>
                                    ${record.clock_out ? `
                                        <table class="table table-sm">
                                            <tr><td><strong>Time:</strong></td><td>${new Date(record.clock_out).toLocaleString()}</td></tr>
                                            <tr><td><strong>Duration:</strong></td><td>${record.hours_worked} hours</td></tr>
                                            <tr><td><strong>Location:</strong></td><td>${record.location_out || 'Office'}</td></tr>
                                        </table>
                                    ` : '<p class="text-muted">Not checked out yet</p>'}
                                </div>
                            </div>
                            ${record.notes ? `
                                <div class="row mt-3">
                                    <div class="col-12">
                                        <h6 class="text-info">
                                            <i class="fas fa-sticky-note"></i> Notes
                                        </h6>
                                        <p>${record.notes}</p>
                                    </div>
                                </div>
                            ` : ''}
                        `;
                    } else {
                        modalContent.innerHTML = `
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-triangle"></i> 
                                Error loading record details: ${data.message}
                            </div>
                        `;
                    }
                })
                .catch(error => {
                    modalContent.innerHTML = `
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle"></i> 
                            Network error while loading details
                        </div>
                    `;
                });
        }

        function showMessage(message, type = 'info', duration = 5000) {
            const alertDiv = document.createElement('div');
            alertDiv.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
            alertDiv.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
            alertDiv.innerHTML = `
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            
            document.body.appendChild(alertDiv);
            
            setTimeout(() => {
                if (alertDiv.parentNode) {
                    alertDiv.remove();
                }
            }, duration);
        }
    </script>
</body>
</html>