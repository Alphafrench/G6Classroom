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
    
    // Get all employees for admin/manager, just current user for employees
    if (in_array($user_role, ['admin', 'manager'])) {
        $employees = $employee->getAllEmployees();
    } else {
        $employees = [$employee->getEmployee($employee_id)];
    }
    
} catch (Exception $e) {
    logError("Take attendance error: " . $e->getMessage());
    $employees = [];
}

// Handle attendance submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    try {
        $employee_id = $_POST['employee_id'] ?? $employee_id;
        $action = $_POST['action'];
        
        $location = $_POST['location'] ?? '';
        $notes = $_POST['notes'] ?? '';
        
        if ($action === 'checkin') {
            $result = $attendance->clockIn($employee_id, $location, $notes);
        } elseif ($action === 'checkout') {
            $result = $attendance->clockOut($employee_id, $location, $notes);
        } else {
            $result = ['success' => false, 'errors' => ['Invalid action']];
        }
        
        echo json_encode($result);
        
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'errors' => ['System error: ' . $e->getMessage()]
        ]);
    }
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Take Attendance - HR Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../../assets/css/attendance.css" rel="stylesheet">
    <style>
        .employee-card {
            transition: all 0.3s ease;
            border-left: 4px solid transparent;
        }
        
        .employee-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        
        .employee-card.checked-in {
            border-left-color: #28a745;
            background: linear-gradient(135deg, rgba(40, 167, 69, 0.05) 0%, rgba(40, 167, 69, 0.02) 100%);
        }
        
        .employee-card.checked-out {
            border-left-color: #dc3545;
            background: linear-gradient(135deg, rgba(220, 53, 69, 0.05) 0%, rgba(220, 53, 69, 0.02) 100%);
        }
        
        .status-indicator {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 8px;
            animation: pulse 2s infinite;
        }
        
        .pulse-green { background-color: #28a745; }
        .pulse-red { background-color: #dc3545; }
        .pulse-blue { background-color: #17a2b8; }
        
        .quick-actions {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 1000;
        }
        
        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.5; }
            100% { opacity: 1; }
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
                        <h1 class="display-6 mb-0">
                            <i class="fas fa-hand-paper"></i> Take Attendance
                        </h1>
                        <p class="mb-0">
                            <?php if (in_array($user_role, ['admin', 'manager'])): ?>
                                Manage attendance for all employees
                            <?php else: ?>
                                Clock in/out for your attendance
                            <?php endif; ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Real-time Status Overview -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-clock"></i> Live Attendance Status
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-md-3">
                                <div class="stat-item">
                                    <h3 class="text-success" id="checked-in-count">-</h3>
                                    <small>Currently Checked In</small>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="stat-item">
                                    <h3 class="text-danger" id="checked-out-count">-</h3>
                                    <small>Checked Out</small>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="stat-item">
                                    <h3 class="text-info" id="total-employees"><?php echo count($employees); ?></h3>
                                    <small>Total Employees</small>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="stat-item">
                                    <h3 class="text-warning" id="current-time-large"><?php echo date('H:i:s'); ?></h3>
                                    <small>Current Time</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Attendance Form -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-edit"></i> Quick Attendance Entry
                        </h5>
                    </div>
                    <div class="card-body">
                        <form id="quick-attendance-form">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label for="employee-select" class="form-label">Employee</label>
                                    <select class="form-select" id="employee-select" required>
                                        <option value="">Select Employee</option>
                                        <?php foreach ($employees as $emp): ?>
                                            <?php if ($emp && isset($emp['id'])): ?>
                                                <option value="<?php echo $emp['id']; ?>" 
                                                        <?php echo $emp['id'] == $employee_id ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($emp['first_name'] . ' ' . $emp['last_name']); ?>
                                                    <?php if ($emp['position']): ?>
                                                        - <?php echo htmlspecialchars($emp['position']); ?>
                                                    <?php endif; ?>
                                                </option>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label for="attendance-action" class="form-label">Action</label>
                                    <select class="form-select" id="attendance-action" required>
                                        <option value="">Select Action</option>
                                        <option value="checkin">Check In</option>
                                        <option value="checkout">Check Out</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label for="attendance-location" class="form-label">Location</label>
                                    <input type="text" class="form-control" id="attendance-location" 
                                           placeholder="Office, Remote, etc.">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">&nbsp;</label>
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="fas fa-save"></i> Record
                                    </button>
                                </div>
                            </div>
                            <div class="row mt-3">
                                <div class="col-12">
                                    <label for="attendance-notes" class="form-label">Notes (Optional)</label>
                                    <textarea class="form-control" id="attendance-notes" rows="2" 
                                              placeholder="Any additional notes..."></textarea>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Employee Status Cards -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-users"></i> Employee Status
                        </h5>
                        <div class="btn-group btn-group-sm" role="group">
                            <button class="btn btn-outline-primary" onclick="refreshEmployeeStatus()">
                                <i class="fas fa-sync"></i> Refresh
                            </button>
                            <button class="btn btn-outline-success" onclick="showAllEmployees()">
                                <i class="fas fa-eye"></i> Show All
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row g-3" id="employee-cards">
                            <?php foreach ($employees as $emp): ?>
                                <?php if ($emp && isset($emp['id'])): ?>
                                    <?php
                                    $currentStatus = $attendance->getCurrentStatus($emp['id']);
                                    $statusClass = $currentStatus ? 'checked-in' : 'checked-out';
                                    $statusIndicator = $currentStatus ? 'pulse-green' : 'pulse-red';
                                    $statusText = $currentStatus ? 'Checked In' : 'Checked Out';
                                    $statusIcon = $currentStatus ? 'fa-sign-in-alt' : 'fa-sign-out-alt';
                                    $buttonText = $currentStatus ? 'Check Out' : 'Check In';
                                    $buttonAction = $currentStatus ? 'checkout' : 'checkin';
                                    ?>
                                    <div class="col-lg-4 col-md-6">
                                        <div class="card employee-card <?php echo $statusClass; ?>" 
                                             data-employee-id="<?php echo $emp['id']; ?>">
                                            <div class="card-body">
                                                <div class="d-flex justify-content-between align-items-start mb-2">
                                                    <h6 class="card-title mb-0">
                                                        <span class="status-indicator <?php echo $statusIndicator; ?>"></span>
                                                        <?php echo htmlspecialchars($emp['first_name'] . ' ' . $emp['last_name']); ?>
                                                    </h6>
                                                    <small class="text-muted"><?php echo htmlspecialchars($emp['position'] ?? ''); ?></small>
                                                </div>
                                                
                                                <?php if ($currentStatus): ?>
                                                    <div class="mb-2">
                                                        <small class="text-muted">
                                                            <i class="fas <?php echo $statusIcon; ?>"></i>
                                                            Since: <?php echo formatDateTime($currentStatus['clock_in']); ?>
                                                        </small>
                                                    </div>
                                                    <div class="mb-3">
                                                        <small class="text-info">
                                                            <i class="fas fa-clock"></i>
                                                            Duration: <span id="duration-<?php echo $emp['id']; ?>">-</span>
                                                        </small>
                                                    </div>
                                                <?php else: ?>
                                                    <div class="mb-3">
                                                        <small class="text-muted">
                                                            <i class="fas fa-calendar-times"></i>
                                                            Last active: 
                                                            <?php 
                                                            $lastRecord = $attendance->getEmployeeAttendance($emp['id'], null, null, 1, 0);
                                                            echo !empty($lastRecord['records']) ? formatDate($lastRecord['records'][0]['date']) : 'No records';
                                                            ?>
                                                        </small>
                                                    </div>
                                                <?php endif; ?>
                                                
                                                <div class="d-grid">
                                                    <button class="btn btn-sm <?php echo $currentStatus ? 'btn-danger' : 'btn-success'; ?> attendance-action-btn" 
                                                            data-employee-id="<?php echo $emp['id']; ?>"
                                                            data-action="<?php echo $buttonAction; ?>">
                                                        <i class="fas <?php echo $currentStatus ? 'fa-sign-out-alt' : 'fa-sign-in-alt'; ?>"></i>
                                                        <?php echo $buttonText; ?>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                        
                        <?php if (empty($employees)): ?>
                            <div class="text-center py-5">
                                <i class="fas fa-users fa-3x text-muted mb-3"></i>
                                <p class="text-muted">No employees found.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions Floating Button -->
    <div class="quick-actions">
        <div class="btn-group-vertical" role="group">
            <button class="btn btn-primary btn-lg rounded-circle" onclick="showQuickCheckIn()" 
                    title="Quick Check In" style="width: 60px; height: 60px;">
                <i class="fas fa-sign-in-alt"></i>
            </button>
            <button class="btn btn-danger btn-lg rounded-circle mt-2" onclick="showQuickCheckOut()" 
                    title="Quick Check Out" style="width: 60px; height: 60px;">
                <i class="fas fa-sign-out-alt"></i>
            </button>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../../assets/js/attendance.js"></script>
    <script>
        // Global variables
        let statusUpdateInterval;
        let durationUpdateInterval;
        
        document.addEventListener('DOMContentLoaded', function() {
            updateCurrentTime();
            updateEmployeeStatusCounts();
            startDurationTimers();
            
            // Update every 30 seconds
            statusUpdateInterval = setInterval(() => {
                refreshEmployeeStatus();
                updateEmployeeStatusCounts();
            }, 30000);
            
            // Update current time every second
            setInterval(updateCurrentTime, 1000);
            
            // Initialize form handlers
            initializeFormHandlers();
            initializeCardButtons();
        });
        
        function updateCurrentTime() {
            const now = new Date();
            const timeString = now.toLocaleTimeString();
            const timeElement = document.getElementById('current-time-large');
            if (timeElement) {
                timeElement.textContent = timeString;
            }
        }
        
        function updateEmployeeStatusCounts() {
            const checkedIn = document.querySelectorAll('.employee-card.checked-in').length;
            const checkedOut = document.querySelectorAll('.employee-card.checked-out').length;
            
            document.getElementById('checked-in-count').textContent = checkedIn;
            document.getElementById('checked-out-count').textContent = checkedOut;
        }
        
        function startDurationTimers() {
            durationUpdateInterval = setInterval(() => {
                document.querySelectorAll('[id^="duration-"]').forEach(element => {
                    const employeeId = element.id.replace('duration-', '');
                    const card = document.querySelector(`[data-employee-id="${employeeId}"]`);
                    
                    if (card && card.classList.contains('checked-in')) {
                        const statusDiv = card.querySelector('.text-info small');
                        if (statusDiv) {
                            const checkInTime = getCheckInTime(employeeId);
                            if (checkInTime) {
                                const now = new Date();
                                const diff = now - new Date(checkInTime);
                                const hours = Math.floor(diff / 3600000);
                                const minutes = Math.floor((diff % 3600000) / 60000);
                                element.textContent = `${hours}h ${minutes}m`;
                            }
                        }
                    }
                });
            }, 60000); // Update every minute
        }
        
        function getCheckInTime(employeeId) {
            const card = document.querySelector(`[data-employee-id="${employeeId}"]`);
            if (card) {
                const sinceText = card.querySelector('.text-muted');
                if (sinceText) {
                    // Extract time from "Since: YYYY-MM-DD HH:MM:SS"
                    const match = sinceText.textContent.match(/Since:\s+(.+)/);
                    if (match) {
                        return match[1];
                    }
                }
            }
            return null;
        }
        
        function initializeFormHandlers() {
            document.getElementById('quick-attendance-form').addEventListener('submit', async function(e) {
                e.preventDefault();
                
                const formData = new FormData(this);
                const data = {
                    employee_id: formData.get('employee_id') || document.getElementById('employee-select').value,
                    action: formData.get('action') || document.getElementById('attendance-action').value,
                    location: document.getElementById('attendance-location').value,
                    notes: document.getElementById('attendance-notes').value
                };
                
                if (!data.employee_id || !data.action) {
                    showMessage('Please select both employee and action', 'warning');
                    return;
                }
                
                await recordAttendance(data);
            });
        }
        
        function initializeCardButtons() {
            document.querySelectorAll('.attendance-action-btn').forEach(button => {
                button.addEventListener('click', function() {
                    const employeeId = this.dataset.employeeId;
                    const action = this.dataset.action;
                    
                    recordAttendance({
                        employee_id: employeeId,
                        action: action,
                        location: 'Quick action',
                        notes: 'Recorded via quick action'
                    });
                });
            });
        }
        
        async function recordAttendance(data) {
            try {
                const response = await fetch(window.location.href, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: new URLSearchParams(data)
                });
                
                const result = await response.json();
                
                if (result.success) {
                    showMessage(result.message, 'success');
                    setTimeout(() => {
                        refreshEmployeeStatus();
                        updateEmployeeStatusCounts();
                    }, 1000);
                } else {
                    showMessage(result.errors ? result.errors.join(', ') : result.message, 'danger');
                }
                
            } catch (error) {
                showMessage('Network error: ' + error.message, 'danger');
            }
        }
        
        function refreshEmployeeStatus() {
            // In a real implementation, this would fetch updated status from the server
            // For now, we'll just refresh the page data
            location.reload();
        }
        
        function showAllEmployees() {
            // Toggle visibility of all employees
            const cards = document.querySelectorAll('.employee-card');
            cards.forEach(card => {
                card.style.display = card.style.display === 'none' ? 'block' : 'block';
            });
        }
        
        function showQuickCheckIn() {
            if (confirm('Quick check in for <?php echo htmlspecialchars($employee_name); ?>?')) {
                recordAttendance({
                    employee_id: <?php echo $employee_id; ?>,
                    action: 'checkin',
                    location: 'Quick action',
                    notes: 'Quick check-in'
                });
            }
        }
        
        function showQuickCheckOut() {
            if (confirm('Quick check out for <?php echo htmlspecialchars($employee_name); ?>?')) {
                recordAttendance({
                    employee_id: <?php echo $employee_id; ?>,
                    action: 'checkout',
                    location: 'Quick action',
                    notes: 'Quick check-out'
                });
            }
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
        
        // Cleanup intervals on page unload
        window.addEventListener('beforeunload', function() {
            if (statusUpdateInterval) clearInterval(statusUpdateInterval);
            if (durationUpdateInterval) clearInterval(durationUpdateInterval);
        });
    </script>
</body>
</html>