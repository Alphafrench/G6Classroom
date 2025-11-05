<?php 
$page_title = "Student Dashboard - EduPlatform";
$breadcrumb = [
    ['title' => 'Dashboard', 'url' => 'index.php'],
    ['title' => 'Student Dashboard']
];
?>

<?php include '../../includes/header.php'; ?>

<!-- Dashboard Header -->
<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="h3 mb-1">Student Dashboard</h1>
                <p class="text-muted">Welcome back, John! Here's your academic progress overview.</p>
            </div>
            <div class="d-flex gap-2">
                <button class="btn btn-outline-primary">
                    <i class="bi bi-calendar-week me-1"></i>View Schedule
                </button>
                <button class="btn btn-primary">
                    <i class="bi bi-folder-plus me-1"></i>My Courses
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Quick Stats Row -->
<div class="row g-4 mb-4">
    <!-- Current GPA -->
    <div class="col-xl-3 col-md-6">
        <div class="stats-card revenue">
            <div class="stats-card-icon">
                <i class="bi bi-award"></i>
            </div>
            <div class="stats-card-value">3.85</div>
            <div class="stats-card-label">Current GPA</div>
            <div class="stats-card-change positive">
                <i class="bi bi-arrow-up me-1"></i>+0.15 this semester
            </div>
        </div>
    </div>
    
    <!-- Enrolled Courses -->
    <div class="col-xl-3 col-md-6">
        <div class="stats-card students">
            <div class="stats-card-icon">
                <i class="bi bi-journal-bookmark"></i>
            </div>
            <div class="stats-card-value">5</div>
            <div class="stats-card-label">Enrolled Courses</div>
            <div class="stats-card-change positive">
                <i class="bi bi-check me-1"></i>All active
            </div>
        </div>
    </div>
    
    <!-- Upcoming Deadlines -->
    <div class="col-xl-3 col-md-6">
        <div class="stats-card assignments">
            <div class="stats-card-icon">
                <i class="bi bi-calendar-exclamation"></i>
            </div>
            <div class="stats-card-value">3</div>
            <div class="stats-card-label">Upcoming Deadlines</div>
            <div class="stats-card-change negative">
                <i class="bi bi-clock me-1"></i>Next: 2 days
            </div>
        </div>
    </div>
    
    <!-- Attendance Rate -->
    <div class="col-xl-3 col-md-6">
        <div class="stats-card courses">
            <div class="stats-card-icon">
                <i class="bi bi-person-check"></i>
            </div>
            <div class="stats-card-value">96%</div>
            <div class="stats-card-label">Attendance Rate</div>
            <div class="stats-card-change positive">
                <i class="bi bi-arrow-up me-1"></i>Excellent!
            </div>
        </div>
    </div>
</div>

<!-- Main Content Row -->
<div class="row g-4">
    <!-- My Courses -->
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="bi bi-journal-bookmark me-2"></i>My Courses
                </h5>
                <a href="#" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <!-- Course Card -->
                    <div class="col-md-6">
                        <div class="course-card" data-progress="85">
                            <div class="course-card-header">
                                <h6 class="course-card-title">Advanced Mathematics</h6>
                                <div class="course-card-meta">
                                    <span><i class="bi bi-person me-1"></i>Prof. Smith</span>
                                    <span><i class="bi bi-calendar me-1"></i>Mon/Wed/Fri</span>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <small class="text-muted">Progress</small>
                                    <small class="fw-bold">85%</small>
                                </div>
                                <div class="progress mb-3" style="height: 6px;">
                                    <div class="progress-bar" role="progressbar" style="width: 85%"></div>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span class="badge bg-success">A-</span>
                                    <small class="text-muted">Next: Calculus Quiz</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Course Card -->
                    <div class="col-md-6">
                        <div class="course-card" data-progress="72">
                            <div class="course-card-header">
                                <h6 class="course-card-title">Statistics</h6>
                                <div class="course-card-meta">
                                    <span><i class="bi bi-person me-1"></i>Dr. Johnson</span>
                                    <span><i class="bi bi-calendar me-1"></i>Tue/Thu</span>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <small class="text-muted">Progress</small>
                                    <small class="fw-bold">72%</small>
                                </div>
                                <div class="progress mb-3" style="height: 6px;">
                                    <div class="progress-bar" role="progressbar" style="width: 72%"></div>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span class="badge bg-warning">B+</span>
                                    <small class="text-muted">Next: Project Due</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Course Card -->
                    <div class="col-md-6">
                        <div class="course-card" data-progress="90">
                            <div class="course-card-header">
                                <h6 class="course-card-title">Linear Algebra</h6>
                                <div class="course-card-meta">
                                    <span><i class="bi bi-person me-1"></i>Prof. Davis</span>
                                    <span><i class="bi bi-calendar me-1"></i>Mon/Wed</span>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <small class="text-muted">Progress</small>
                                    <small class="fw-bold">90%</small>
                                </div>
                                <div class="progress mb-3" style="height: 6px;">
                                    <div class="progress-bar" role="progressbar" style="width: 90%"></div>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span class="badge bg-success">A</span>
                                    <small class="text-muted">Next: Midterm</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Course Card -->
                    <div class="col-md-6">
                        <div class="course-card" data-progress="78">
                            <div class="course-card-header">
                                <h6 class="course-card-title">Physics</h6>
                                <div class="course-card-meta">
                                    <span><i class="bi bi-person me-1"></i>Dr. Wilson</span>
                                    <span><i class="bi bi-calendar me-1"></i>Tue/Thu</span>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <small class="text-muted">Progress</small>
                                    <small class="fw-bold">78%</small>
                                </div>
                                <div class="progress mb-3" style="height: 6px;">
                                    <div class="progress-bar" role="progressbar" style="width: 78%"></div>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span class="badge bg-info">B</span>
                                    <small class="text-muted">Next: Lab Report</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Right Sidebar -->
    <div class="col-lg-4">
        <!-- Today's Classes -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-calendar-event me-2"></i>Today's Classes
                </h5>
            </div>
            <div class="card-body">
                <div class="d-flex align-items-center mb-3 p-2 bg-light rounded">
                    <div class="flex-shrink-0 me-3">
                        <div class="text-center">
                            <div class="fw-bold text-primary">9:00</div>
                            <small class="text-muted">AM</small>
                        </div>
                    </div>
                    <div class="flex-grow-1">
                        <h6 class="mb-0">Advanced Mathematics</h6>
                        <small class="text-muted">Room 201 - Prof. Smith</small>
                    </div>
                </div>
                
                <div class="d-flex align-items-center mb-3 p-2 bg-light rounded">
                    <div class="flex-shrink-0 me-3">
                        <div class="text-center">
                            <div class="fw-bold text-primary">11:00</div>
                            <small class="text-muted">AM</small>
                        </div>
                    </div>
                    <div class="flex-grow-1">
                        <h6 class="mb-0">Statistics</h6>
                        <small class="text-muted">Room 305 - Dr. Johnson</small>
                    </div>
                </div>
                
                <div class="d-flex align-items-center p-2 bg-light rounded">
                    <div class="flex-shrink-0 me-3">
                        <div class="text-center">
                            <div class="fw-bold text-muted">2:00</div>
                            <small class="text-muted">PM</small>
                        </div>
                    </div>
                    <div class="flex-grow-1">
                        <h6 class="mb-0 text-muted">Free Period</h6>
                        <small class="text-muted">Library available</small>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Upcoming Deadlines -->
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="bi bi-calendar-exclamation me-2"></i>Upcoming Deadlines
                </h5>
                <span class="badge bg-warning">3</span>
            </div>
            <div class="card-body">
                <div class="list-group list-group-flush">
                    <div class="list-group-item px-0 d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1">Calculus Problem Set</h6>
                            <small class="text-muted">Advanced Mathematics</small>
                        </div>
                        <div class="text-end">
                            <div class="fw-bold text-danger">2 days</div>
                            <small class="text-muted">Nov 7</small>
                        </div>
                    </div>
                    <div class="list-group-item px-0 d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1">Statistics Project</h6>
                            <small class="text-muted">Statistics</small>
                        </div>
                        <div class="text-end">
                            <div class="fw-bold text-warning">5 days</div>
                            <small class="text-muted">Nov 10</small>
                        </div>
                    </div>
                    <div class="list-group-item px-0 d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1">Physics Lab Report</h6>
                            <small class="text-muted">Physics</small>
                        </div>
                        <div class="text-end">
                            <div class="fw-bold text-info">1 week</div>
                            <small class="text-muted">Nov 12</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Grade Overview -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-graph-up me-2"></i>Grade Overview
                </h5>
            </div>
            <div class="card-body">
                <div class="text-center mb-3">
                    <div class="grade-display a" data-grade="A" data-percentage="92">
                        A
                    </div>
                    <div class="mt-2">
                        <small class="text-muted">Current Semester GPA</small>
                    </div>
                </div>
                
                <div class="grade-trend-chart" 
                     data-grades='[88, 92, 85, 90, 87, 93, 89, 91, 94, 92]'></div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Assignments -->
<div class="row g-4 mt-2">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="bi bi-clipboard-check me-2"></i>Recent Assignments
                </h5>
                <a href="#" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Assignment</th>
                                <th>Course</th>
                                <th>Due Date</th>
                                <th>Status</th>
                                <th>Grade</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Calculus Quiz #4</td>
                                <td>Advanced Mathematics</td>
                                <td>Nov 3, 2024</td>
                                <td><span class="assignment-status graded">Graded</span></td>
                                <td><span class="grade-display a-minus" style="width: 2rem; height: 2rem; font-size: 0.875rem;">A-</span></td>
                                <td>
                                    <button class="btn btn-sm btn-outline-info" data-bs-toggle="tooltip" title="View Feedback">
                                        <i class="bi bi-chat-left-text"></i>
                                    </button>
                                </td>
                            </tr>
                            <tr>
                                <td>Statistics Project Phase 2</td>
                                <td>Statistics</td>
                                <td>Nov 7, 2024</td>
                                <td><span class="assignment-status submitted">Submitted</span></td>
                                <td><span class="text-muted">Pending</span></td>
                                <td>
                                    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="tooltip" title="View Submission">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </td>
                            </tr>
                            <tr>
                                <td>Linear Algebra Problem Set</td>
                                <td>Linear Algebra</td>
                                <td>Nov 10, 2024</td>
                                <td><span class="assignment-status pending">In Progress</span></td>
                                <td><span class="text-muted">-</span></td>
                                <td>
                                    <button class="btn btn-sm btn-outline-warning" data-bs-toggle="tooltip" title="Continue">
                                        <i class="bi bi-pencil"></i>
                                    </button>
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
