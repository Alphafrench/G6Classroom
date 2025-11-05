<?php 
$page_title = "Teacher Dashboard - EduPlatform";
$breadcrumb = [
    ['title' => 'Dashboard', 'url' => 'index.php'],
    ['title' => 'Teacher Dashboard']
];
?>

<?php include '../../includes/header.php'; ?>

<!-- Dashboard Header -->
<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="h3 mb-1">Teacher Dashboard</h1>
                <p class="text-muted">Welcome back, Prof. Smith! Here's your teaching overview for today.</p>
            </div>
            <div class="d-flex gap-2">
                <button class="btn btn-outline-primary">
                    <i class="bi bi-calendar-week me-1"></i>View Schedule
                </button>
                <button class="btn btn-primary">
                    <i class="bi bi-plus-circle me-1"></i>Create Assignment
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Quick Stats Row -->
<div class="row g-4 mb-4">
    <!-- Active Courses -->
    <div class="col-xl-3 col-md-6">
        <div class="stats-card students">
            <div class="stats-card-icon">
                <i class="bi bi-journal-bookmark"></i>
            </div>
            <div class="stats-card-value">6</div>
            <div class="stats-card-label">Active Courses</div>
            <div class="stats-card-change positive">
                <i class="bi bi-arrow-up me-1"></i>+2 this semester
            </div>
        </div>
    </div>
    
    <!-- Total Students -->
    <div class="col-xl-3 col-md-6">
        <div class="stats-card revenue">
            <div class="stats-card-icon">
                <i class="bi bi-people"></i>
            </div>
            <div class="stats-card-value">247</div>
            <div class="stats-card-label">Total Students</div>
            <div class="stats-card-change positive">
                <i class="bi bi-arrow-up me-1"></i>+12 new enrollments
            </div>
        </div>
    </div>
    
    <!-- Pending Assignments -->
    <div class="col-xl-3 col-md-6">
        <div class="stats-card assignments">
            <div class="stats-card-icon">
                <i class="bi bi-clipboard-check"></i>
            </div>
            <div class="stats-card-value">18</div>
            <div class="stats-card-label">Pending Grading</div>
            <div class="stats-card-change negative">
                <i class="bi bi-arrow-down me-1"></i>3 overdue
            </div>
        </div>
    </div>
    
    <!-- Class Attendance -->
    <div class="col-xl-3 col-md-6">
        <div class="stats-card courses">
            <div class="stats-card-icon">
                <i class="bi bi-calendar-check"></i>
            </div>
            <div class="stats-card-value">92%</div>
            <div class="stats-card-label">Avg Attendance</div>
            <div class="stats-card-change positive">
                <i class="bi bi-arrow-up me-1"></i>+5% from last week
            </div>
        </div>
    </div>
</div>

<!-- Main Content Row -->
<div class="row g-4">
    <!-- Today's Classes -->
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-calendar-event me-2"></i>Today's Schedule
                </h5>
            </div>
            <div class="card-body">
                <div class="schedule-timeline">
                    <div class="schedule-item" data-details='{"course":"Advanced Mathematics","time":"9:00 AM - 10:30 AM","room":"Room 201","instructor":"Prof. Smith","description":"Calculus II - Integration techniques and applications"}'>
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h6 class="mb-1">Advanced Mathematics</h6>
                                <p class="text-muted mb-1">Calculus II - Integration techniques</p>
                                <div class="d-flex gap-3 text-sm">
                                    <span><i class="bi bi-clock me-1"></i>9:00 AM - 10:30 AM</span>
                                    <span><i class="bi bi-geo-alt me-1"></i>Room 201</span>
                                </div>
                            </div>
                            <span class="role-badge teacher">Teaching</span>
                        </div>
                    </div>
                    
                    <div class="schedule-item" data-details='{"course":"Statistics","time":"11:00 AM - 12:30 PM","room":"Room 305","instructor":"Prof. Smith","description":"Probability distributions and hypothesis testing"}'>
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h6 class="mb-1">Statistics</h6>
                                <p class="text-muted mb-1">Probability distributions and hypothesis testing</p>
                                <div class="d-flex gap-3 text-sm">
                                    <span><i class="bi bi-clock me-1"></i>11:00 AM - 12:30 PM</span>
                                    <span><i class="bi bi-geo-alt me-1"></i>Room 305</span>
                                </div>
                            </div>
                            <span class="role-badge teacher">Teaching</span>
                        </div>
                    </div>
                    
                    <div class="schedule-item" data-details='{"course":"Linear Algebra","time":"2:00 PM - 3:30 PM","room":"Room 101","instructor":"Prof. Smith","description":"Matrix operations and vector spaces"}'>
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h6 class="mb-1">Linear Algebra</h6>
                                <p class="text-muted mb-1">Matrix operations and vector spaces</p>
                                <div class="d-flex gap-3 text-sm">
                                    <span><i class="bi bi-clock me-1"></i>2:00 PM - 3:30 PM</span>
                                    <span><i class="bi bi-geo-alt me-1"></i>Room 101</span>
                                </div>
                            </div>
                            <span class="role-badge teacher">Teaching</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Right Sidebar -->
    <div class="col-lg-4">
        <!-- Quick Actions -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-lightning me-2"></i>Quick Actions
                </h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <button class="btn btn-outline-primary">
                        <i class="bi bi-file-earmark-plus me-2"></i>Create Assignment
                    </button>
                    <button class="btn btn-outline-success">
                        <i class="bi bi-person-plus me-2"></i>Add Student
                    </button>
                    <button class="btn btn-outline-info">
                        <i class="bi bi-calendar-plus me-2"></i>Schedule Class
                    </button>
                    <button class="btn btn-outline-warning">
                        <i class="bi bi-graph-up me-2"></i>View Reports
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Pending Tasks -->
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="bi bi-list-check me-2"></i>Pending Tasks
                </h5>
                <span class="badge bg-danger">3</span>
            </div>
            <div class="card-body">
                <div class="list-group list-group-flush">
                    <div class="list-group-item px-0 d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1">Grade Calculus Assignments</h6>
                            <small class="text-muted">24 submissions pending</small>
                        </div>
                        <span class="assignment-status overdue">Overdue</span>
                    </div>
                    <div class="list-group-item px-0 d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1">Update Gradebook</h6>
                            <small class="text-muted">Statistics course</small>
                        </div>
                        <span class="assignment-status pending">Pending</span>
                    </div>
                    <div class="list-group-item px-0 d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1">Review Attendance</h6>
                            <small class="text-muted">This week's classes</small>
                        </div>
                        <span class="assignment-status pending">Pending</span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Recent Activity -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-clock-history me-2"></i>Recent Activity
                </h5>
            </div>
            <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                    <div class="flex-shrink-0">
                        <i class="bi bi-check-circle text-success fs-4"></i>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h6 class="mb-0">Assignment graded</h6>
                        <small class="text-muted">Calculus Quiz #3 - 2 hours ago</small>
                    </div>
                </div>
                <div class="d-flex align-items-center mb-3">
                    <div class="flex-shrink-0">
                        <i class="bi bi-person-plus text-primary fs-4"></i>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h6 class="mb-0">New student enrolled</h6>
                        <small class="text-muted">John Doe in Statistics - 1 day ago</small>
                    </div>
                </div>
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <i class="bi bi-calendar-event text-info fs-4"></i>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h6 class="mb-0">Class scheduled</h6>
                        <small class="text-muted">Linear Algebra - Tomorrow 2:00 PM</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Submissions -->
<div class="row g-4 mt-2">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="bi bi-file-earmark-text me-2"></i>Recent Submissions
                </h5>
                <a href="#" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Student</th>
                                <th>Assignment</th>
                                <th>Course</th>
                                <th>Submitted</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <img src="https://via.placeholder.com/32x32" alt="Student" 
                                             class="rounded-circle me-2" width="32" height="32">
                                        <span>Alice Johnson</span>
                                    </div>
                                </td>
                                <td>Calculus Problem Set #5</td>
                                <td>Advanced Mathematics</td>
                                <td>2 hours ago</td>
                                <td><span class="assignment-status submitted">Submitted</span></td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <button class="btn btn-outline-primary" data-bs-toggle="tooltip" title="Review">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                        <button class="btn btn-outline-success" data-bs-toggle="tooltip" title="Grade">
                                            <i class="bi bi-award"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <img src="https://via.placeholder.com/32x32" alt="Student" 
                                             class="rounded-circle me-2" width="32" height="32">
                                        <span>Bob Smith</span>
                                    </div>
                                </td>
                                <td>Statistics Project</td>
                                <td>Statistics</td>
                                <td>5 hours ago</td>
                                <td><span class="assignment-status submitted">Submitted</span></td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <button class="btn btn-outline-primary" data-bs-toggle="tooltip" title="Review">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                        <button class="btn btn-outline-success" data-bs-toggle="tooltip" title="Grade">
                                            <i class="bi bi-award"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <img src="https://via.placeholder.com/32x32" alt="Student" 
                                             class="rounded-circle me-2" width="32" height="32">
                                        <span>Carol Davis</span>
                                    </div>
                                </td>
                                <td>Linear Algebra Quiz</td>
                                <td>Linear Algebra</td>
                                <td>1 day ago</td>
                                <td><span class="assignment-status graded">Graded</span></td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <button class="btn btn-outline-info" data-bs-toggle="tooltip" title="View Grade">
                                            <i class="bi bi-award-fill"></i>
                                        </button>
                                        <button class="btn btn-outline-secondary" data-bs-toggle="tooltip" title="Feedback">
                                            <i class="bi bi-chat-left-text"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
