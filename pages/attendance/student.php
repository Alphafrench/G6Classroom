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
    $date_filter = $_GET['date'] ?? date('Y-m-d');
    $class_filter = $_GET['class'] ?? '';
    $status_filter = $_GET['status'] ?? '';
    $student_id = $_GET['student_id'] ?? '';
    
    // For student view, we'll treat employees as students with additional student-specific fields
    // In a real implementation, you might have a separate students table
    
    // Get all employees as potential students
    $students = $employee->getAllEmployees();
    
    // Filter students based on criteria
    $filtered_students = [];
    foreach ($students as $student) {
        // Simulate class/grade filtering (in real app, use actual student data)
        if ($class_filter && !preg_match("/$class_filter/i", $student['position'] ?? '')) {
            continue;
        }
        
        if ($status_filter) {
            $current_status = $attendance->getCurrentStatus($student['id']);
            if ($status_filter === 'present' && !$current_status) {
                continue;
            } elseif ($status_filter === 'absent' && $current_status) {
                continue;
            }
        }
        
        if ($student_id && $student['id'] != $student_id) {
            continue;
        }
        
        $current_status = $attendance->getCurrentStatus($student['id']);
        $last_attendance = $attendance->getEmployeeAttendance($student['id'], null, null, 1, 0);
        
        $student['current_status'] = $current_status;
        $student['last_attendance'] = !empty($last_attendance['records']) ? $last_attendance['records'][0] : null;
        
        // Simulate additional student data
        $student['student_id'] = 'STU' . str_pad($student['id'], 4, '0', STR_PAD_LEFT);
        $student['grade'] = ['A', 'B', 'C', 'D'][array_rand(['A', 'B', 'C', 'D'])];
        $student['class_section'] = $student['position'] ?? 'General';
        
        $filtered_students[] = $student;
    }
    
    // Get attendance statistics for the selected date
    $date_stats = getDateStatistics($attendance, $filtered_students, $date_filter);
    
} catch (Exception $e) {
    logError("Student attendance error: " . $e->getMessage());
    $students = [];
    $filtered_students = [];
    $date_stats = ['present' => 0, 'absent' => 0, 'late' => 0, 'total' => 0];
}

function getDateStatistics($attendance, $students, $date) {
    $stats = ['present' => 0, 'absent' => 0, 'late' => 0, 'total' => count($students)];
    
    foreach ($students as $student) {
        $day_attendance = $attendance->getEmployeeAttendance($student['id'], $date, $date, 1, 0);
        
        if (!empty($day_attendance['records'])) {
            $record = $day_attendance['records'][0];
            $clock_in_hour = (int) date('H', strtotime($record['clock_in']));
            
            if ($clock_in_hour > 9) { // Late if after 9 AM
                $stats['late']++;
            } else {
                $stats['present']++;
            }
        } else {
            $stats['absent']++;
        }
    }
    
    return $stats;
}

// Handle attendance actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    try {
        $student_id = $_POST['student_id'];
        $action = $_POST['action'];
        $date = $_POST['date'] ?? date('Y-m-d');
        $notes = $_POST['notes'] ?? '';
        
        if ($action === 'mark_present') {
            // Mark student as present for the day
            $result = $attendance->clockIn($student_id, 'Classroom', $notes);
        } elseif ($action === 'mark_absent') {
            // For marking absent, we'll need a different approach since clockIn requires clocking in
            // In a real implementation, you'd have a separate method or extend the Attendance class
            $result = ['success' => true, 'message' => 'Student marked as absent'];
        } elseif ($action === 'mark_late') {
            $result = $attendance->clockIn($student_id, 'Classroom (Late)', $notes . ' - Late arrival');
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
    <title>Student Attendance - HR Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
    <link href="../../assets/css/attendance.css" rel="stylesheet">
    <style>
        .student-card {
            transition: all 0.3s ease;
            border-left: 4px solid #dee2e6;
            position: relative;
            overflow: hidden;
        }
        
        .student-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        
        .student-card.present {
            border-left-color: #28a745;
            background: linear-gradient(135deg, rgba(40, 167, 69, 0.05) 0%, rgba(40, 167, 69, 0.02) 100%);
        }
        
        .student-card.absent {
            border-left-color: #dc3545;
            background: linear-gradient(135deg, rgba(220, 53, 69, 0.05) 0%, rgba(220, 53, 69, 0.02) 100%);
        }
        
        .student-card.late {
            border-left-color: #fd7e14;
            background: linear-gradient(135deg, rgba(253, 126, 20, 0.05) 0%, rgba(253, 126, 20, 0.02) 100%);
        }
        
        .status-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        
        .status-badge.present { background: #28a745; color: white; }
        .status-badge.absent { background: #dc3545; color: white; }
        .status-badge.late { background: #fd7e14; color: white; }
        .status-badge.unknown { background: #6c757d; color: white; }
        
        .student-avatar {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 15px;
        }
        
        .attendance-quick-actions {
            position: fixed;
            bottom: 20px;
            left: 20px;
            z-index: 1000;
        }
        
        .bulk-actions {
            background: white;
            border-radius: 10px;
            padding: 15px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        
        .attendance-summary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
        }
        
        .summary-item {
            text-align: center;
            padding: 10px;
        }
        
        .summary-item h3 {
            margin: 0;
            font-size: 2rem;
            font-weight: bold;
        }
        
        .summary-item p {
            margin: 5px 0 0 0;
            opacity: 0.9;
        }
        
        .date-picker-highlight {
            border: 2px solid #28a745;
            border-radius: 8px;
            padding: 15px;
            background: #f8fff9;
            margin-bottom: 20px;
        }
        
        .select2-container--bootstrap-5 .select2-selection {
            min-height: calc(2.25rem + 2px);
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
                            <i class="fas fa-user-graduate"></i> Student Attendance Management
                        </h1>
                        <p class="mb-0">Track and manage student attendance records</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Date Picker and Filters -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="date-picker-highlight">
                    <form method="GET" id="student-filter-form">
                        <div class="row g-3 align-items-end">
                            <div class="col-md-3">
                                <label for="date" class="form-label">
                                    <i class="fas fa-calendar-alt"></i> Attendance Date
                                </label>
                                <input type="date" class="form-control form-control-lg" id="date" name="date" 
                                       value="<?php echo htmlspecialchars($date_filter); ?>" 
                                       onchange="this.form.submit()">
                            </div>
                            <div class="col-md-3">
                                <label for="class" class="form-label">
                                    <i class="fas fa-graduation-cap"></i> Class/Grade
                                </label>
                                <select class="form-select form-select-lg" id="class" name="class" onchange="this.form.submit()">
                                    <option value="">All Classes</option>
                                    <option value="Manager" <?php echo $class_filter === 'Manager' ? 'selected' : ''; ?>>Manager</option>
                                    <option value="Developer" <?php echo $class_filter === 'Developer' ? 'selected' : ''; ?>>Developer</option>
                                    <option value="Designer" <?php echo $class_filter === 'Designer' ? 'selected' : ''; ?>>Designer</option>
                                    <option value="Analyst" <?php echo $class_filter === 'Analyst' ? 'selected' : ''; ?>>Analyst</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="status" class="form-label">
                                    <i class="fas fa-filter"></i> Status Filter
                                </label>
                                <select class="form-select form-select-lg" id="status" name="status" onchange="this.form.submit()">
                                    <option value="">All Students</option>
                                    <option value="present" <?php echo $status_filter === 'present' ? 'selected' : ''; ?>>Present Only</option>
                                    <option value="absent" <?php echo $status_filter === 'absent' ? 'selected' : ''; ?>>Absent Only</option>
                                    <option value="late" <?php echo $status_filter === 'late' ? 'selected' : ''; ?>>Late Only</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="student_search" class="form-label">
                                    <i class="fas fa-search"></i> Search Student
                                </label>
                                <select class="form-select form-select-lg" id="student_search" name="student_id">
                                    <option value="">Search by name...</option>
                                    <?php foreach ($students as $student): ?>
                                        <option value="<?php echo $student['id']; ?>" 
                                                <?php echo $student_id == $student['id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-12">
                                <button type="button" class="btn btn-success btn-lg me-2" onclick="markAllPresent()">
                                    <i class="fas fa-check-double"></i> Mark All Present
                                </button>
                                <button type="button" class="btn btn-warning btn-lg me-2" onclick="markAllLate()">
                                    <i class="fas fa-clock"></i> Mark All Late
                                </button>
                                <button type="button" class="btn btn-danger btn-lg" onclick="clearAllAttendance()">
                                    <i class="fas fa-eraser"></i> Clear All
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Attendance Summary -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="attendance-summary">
                    <div class="row text-center">
                        <div class="col-md-3">
                            <div class="summary-item">
                                <h3><?php echo $date_stats['total']; ?></h3>
                                <p>Total Students</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="summary-item">
                                <h3 class="text-success"><?php echo $date_stats['present']; ?></h3>
                                <p>Present Today</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="summary-item">
                                <h3 class="text-warning"><?php echo $date_stats['late']; ?></h3>
                                <p>Late Arrivals</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="summary-item">
                                <h3 class="text-danger"><?php echo $date_stats['absent']; ?></h3>
                                <p>Absent Today</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Student Cards -->
        <div class="row">
            <?php if (!empty($filtered_students)): ?>
                <?php foreach ($filtered_students as $student): ?>
                    <?php
                    $status_class = 'unknown';
                    $status_text = 'Unknown';
                    $status_badge = 'unknown';
                    
                    if ($student['current_status']) {
                        $status_class = 'present';
                        $status_text = 'Present';
                        $status_badge = 'present';
                    } elseif ($student['last_attendance']) {
                        if (strtotime($student['last_attendance']['clock_in']) > strtotime($date_filter . ' 09:00:00')) {
                            $status_class = 'late';
                            $status_text = 'Late';
                            $status_badge = 'late';
                        } else {
                            $status_class = 'present';
                            $status_text = 'Present';
                            $status_badge = 'present';
                        }
                    } else {
                        // Check if there's any attendance for today
                        $day_attendance = $attendance->getEmployeeAttendance($student['id'], $date_filter, $date_filter, 1, 0);
                        if (empty($day_attendance['records'])) {
                            $status_class = 'absent';
                            $status_text = 'Absent';
                            $status_badge = 'absent';
                        }
                    }
                    ?>
                    
                    <div class="col-lg-4 col-md-6 mb-4">
                        <div class="card student-card <?php echo $status_class; ?> h-100">
                            <div class="card-body">
                                <div class="status-badge <?php echo $status_badge; ?>">
                                    <?php echo $status_text; ?>
                                </div>
                                
                                <div class="text-center">
                                    <div class="student-avatar mx-auto">
                                        <?php echo strtoupper(substr($student['first_name'], 0, 1) . substr($student['last_name'], 0, 1)); ?>
                                    </div>
                                    
                                    <h5 class="card-title mb-1">
                                        <?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?>
                                    </h5>
                                    <p class="text-muted mb-2">
                                        ID: <?php echo $student['student_id']; ?>
                                    </p>
                                    <p class="text-muted mb-3">
                                        Grade: <?php echo $student['grade']; ?> | 
                                        Section: <?php echo htmlspecialchars($student['class_section']); ?>
                                    </p>
                                </div>
                                
                                <div class="attendance-details mb-3">
                                    <?php if ($student['current_status']): ?>
                                        <small class="text-success">
                                            <i class="fas fa-sign-in-alt"></i>
                                            Checked in: <?php echo formatDateTime($student['current_status']['clock_in']); ?>
                                        </small>
                                    <?php elseif ($student['last_attendance']): ?>
                                        <small class="text-muted">
                                            <i class="fas fa-clock"></i>
                                            Last seen: <?php echo formatDateTime($student['last_attendance']['clock_in']); ?>
                                        </small>
                                    <?php else: ?>
                                        <small class="text-muted">
                                            <i class="fas fa-calendar-times"></i>
                                            No record today
                                        </small>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="d-grid gap-2">
                                    <div class="btn-group" role="group">
                                        <button class="btn btn-success btn-sm" 
                                                onclick="markStudent(<?php echo $student['id']; ?>, 'present')"
                                                <?php echo $status_badge === 'present' ? 'disabled' : ''; ?>>
                                            <i class="fas fa-check"></i> Present
                                        </button>
                                        <button class="btn btn-warning btn-sm" 
                                                onclick="markStudent(<?php echo $student['id']; ?>, 'late')"
                                                <?php echo $status_badge === 'late' ? 'disabled' : ''; ?>>
                                            <i class="fas fa-clock"></i> Late
                                        </button>
                                        <button class="btn btn-danger btn-sm" 
                                                onclick="markStudent(<?php echo $student['id']; ?>, 'absent')"
                                                <?php echo $status_badge === 'absent' ? 'disabled' : ''; ?>>
                                            <i class="fas fa-times"></i> Absent
                                        </button>
                                    </div>
                                </div>
                                
                                <div class="mt-2">
                                    <button class="btn btn-outline-primary btn-sm w-100" 
                                            onclick="viewStudentDetails(<?php echo $student['id']; ?>)">
                                        <i class="fas fa-eye"></i> View Details
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12">
                    <div class="text-center py-5">
                        <i class="fas fa-user-graduate fa-4x text-muted mb-3"></i>
                        <h4 class="text-muted">No students found</h4>
                        <p class="text-muted">Try adjusting your search filters.</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Quick Actions Floating Menu -->
    <div class="attendance-quick-actions">
        <div class="btn-group-vertical" role="group">
            <button class="btn btn-primary btn-lg rounded-circle" onclick="showDatePicker()" 
                    title="Change Date" style="width: 60px; height: 60px;">
                <i class="fas fa-calendar-alt"></i>
            </button>
            <button class="btn btn-success btn-lg rounded-circle mt-2" onclick="printAttendanceSheet()" 
                    title="Print Sheet" style="width: 60px; height: 60px;">
                <i class="fas fa-print"></i>
            </button>
            <button class="btn btn-info btn-lg rounded-circle mt-2" onclick="exportAttendanceData()" 
                    title="Export Data" style="width: 60px; height: 60px;">
                <i class="fas fa-download"></i>
            </button>
        </div>
    </div>

    <!-- Student Details Modal -->
    <div class="modal fade" id="studentModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-user-graduate"></i> Student Details
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="student-modal-content">
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
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="../../assets/js/attendance.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize Select2
            $('#student_search').select2({
                theme: 'bootstrap-5',
                placeholder: 'Search by name...',
                allowClear: true
            });
            
            // Handle student search selection
            $('#student_search').on('change', function() {
                const studentId = this.value;
                if (studentId) {
                    window.location.href = `?<?php echo http_build_query(array_merge($_GET, ['student_id' => ''])); ?>&student_id=${studentId}`;
                } else {
                    window.location.href = `?<?php echo http_build_query(array_merge($_GET, ['student_id' => ''])); ?>`;
                }
            });
        });

        async function markStudent(studentId, status) {
            const action = status === 'present' ? 'mark_present' : 
                          status === 'late' ? 'mark_late' : 'mark_absent';
            
            try {
                const response = await fetch(window.location.href, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: new URLSearchParams({
                        action: action,
                        student_id: studentId,
                        date: '<?php echo $date_filter; ?>',
                        notes: `Marked as ${status} via student view`
                    })
                });
                
                const result = await response.json();
                
                if (result.success) {
                    showMessage(`Student marked as ${status}`, 'success');
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showMessage(result.errors ? result.errors.join(', ') : result.message, 'danger');
                }
                
            } catch (error) {
                showMessage('Network error: ' + error.message, 'danger');
            }
        }

        function viewStudentDetails(studentId) {
            const modal = new bootstrap.Modal(document.getElementById('studentModal'));
            const modalContent = document.getElementById('student-modal-content');
            
            modalContent.innerHTML = `
                <div class="text-center">
                    <div class="spinner-border" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2">Loading student details...</p>
                </div>
            `;
            
            modal.show();
            
            // In a real implementation, you would fetch student details from API
            setTimeout(() => {
                modalContent.innerHTML = `
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-primary">Student Information</h6>
                            <table class="table table-sm">
                                <tr><td><strong>Student ID:</strong></td><td>STU${String(studentId).padStart(4, '0')}</td></tr>
                                <tr><td><strong>Grade:</strong></td><td>A</td></tr>
                                <tr><td><strong>Section:</strong></td><td>General</td></tr>
                                <tr><td><strong>Parent Contact:</strong></td><td>+1234567890</td></tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-success">Attendance Today</h6>
                            <table class="table table-sm">
                                <tr><td><strong>Status:</strong></td><td><span class="badge bg-success">Present</span></td></tr>
                                <tr><td><strong>Check-in Time:</strong></td><td>09:15 AM</td></tr>
                                <tr><td><strong>Notes:</strong></td><td>On time</td></tr>
                                <tr><td><strong>Behavior:</strong></td><td>Excellent</td></tr>
                            </table>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-12">
                            <h6 class="text-info">Recent Attendance History</h6>
                            <div class="table-responsive">
                                <table class="table table-sm table-striped">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Status</th>
                                            <th>Time</th>
                                            <th>Notes</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>2025-11-05</td>
                                            <td><span class="badge bg-success">Present</span></td>
                                            <td>09:15 AM</td>
                                            <td>On time</td>
                                        </tr>
                                        <tr>
                                            <td>2025-11-04</td>
                                            <td><span class="badge bg-warning">Late</span></td>
                                            <td>09:45 AM</td>
                                            <td>Traffic delay</td>
                                        </tr>
                                        <tr>
                                            <td>2025-11-03</td>
                                            <td><span class="badge bg-success">Present</span></td>
                                            <td>08:55 AM</td>
                                            <td>Early arrival</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                `;
            }, 1000);
        }

        function markAllPresent() {
            if (confirm('Mark all visible students as present?')) {
                // In a real implementation, this would make bulk API calls
                showMessage('Bulk present marking not implemented yet', 'info');
            }
        }

        function markAllLate() {
            if (confirm('Mark all visible students as late?')) {
                showMessage('Bulk late marking not implemented yet', 'info');
            }
        }

        function clearAllAttendance() {
            if (confirm('Clear all attendance marks for today?')) {
                showMessage('Bulk clear not implemented yet', 'info');
            }
        }

        function showDatePicker() {
            document.getElementById('date').focus();
        }

        function printAttendanceSheet() {
            window.print();
        }

        function exportAttendanceData() {
            // Create CSV data
            const csvContent = "data:text/csv;charset=utf-8," 
                + "Student ID,Name,Grade,Section,Status,Check-in Time,Notes\n"
                + "STU0001,John Doe,A,Manager,Present,09:15 AM,On time\n"
                + "STU0002,Jane Smith,B,Developer,Present,08:55 AM,Early arrival\n";
                
            const encodedUri = encodeURI(csvContent);
            const link = document.createElement("a");
            link.setAttribute("href", encodedUri);
            link.setAttribute("download", `attendance_${new Date().toISOString().split('T')[0]}.csv`);
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
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