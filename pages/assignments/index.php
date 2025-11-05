<?php
/**
 * Assignment Listing Page
 * 
 * This page displays all assignments with filtering, searching, and pagination.
 * Supports both teacher and student views with appropriate permissions.
 */

session_start();
require_once '../includes/config.php';
require_once '../includes/class.Database.php';
require_once '../includes/class.Assignment.php';
require_once '../includes/auth.php';

// Initialize database and assignment manager
$db = getDatabase();
$assignmentManager = new Assignment($db);

// Check if user is logged in
if (!isLoggedIn()) {
    safeRedirect('../pages/login.php', ['error' => 'Please log in to access assignments']);
}

$user = getCurrentUser();
$userRole = $user['role'];

// Handle search and filtering
$searchTerm = sanitizeInput($_GET['search'] ?? '');
$assignmentType = sanitizeInput($_GET['type'] ?? '');
$status = sanitizeInput($_GET['status'] ?? 'active');
$page = max(1, intval($_GET['page'] ?? 1));
$limit = 10;
$offset = ($page - 1) * $limit;

// Build filters
$filters = [];
if (!empty($searchTerm)) {
    $filters['search_term'] = $searchTerm;
}
if (!empty($assignmentType)) {
    $filters['assignment_type'] = $assignmentType;
}
if ($userRole === 'employee') {
    // Students only see active assignments that haven't expired
    $filters['student_view'] = true;
} else {
    // Teachers can see all assignments
    if ($status === 'active') {
        $filters['is_active'] = true;
    } elseif ($status === 'inactive') {
        $filters['is_active'] = false;
    }
    // Teachers can filter by their own assignments
    if ($userRole === 'admin') {
        $filters['teacher_id'] = $user['id'];
    }
}

// Get assignments
$result = $assignmentManager->getAll($filters, $limit, $offset);
$assignments = $result['assignments'];
$pagination = $result['pagination'];

// Handle success/error messages
$successMessage = $_SESSION['success_message'] ?? null;
$errorMessage = $_SESSION['error_message'] ?? null;
unset($_SESSION['success_message'], $_SESSION['error_message']);

$pageTitle = $userRole === 'employee' ? 'My Assignments' : 'Assignment Management';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?> - <?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .assignment-card {
            transition: transform 0.2s;
            border-left: 4px solid #007bff;
        }
        .assignment-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        .assignment-card.overdue {
            border-left-color: #dc3545;
        }
        .assignment-card.due-soon {
            border-left-color: #ffc107;
        }
        .status-badge {
            font-size: 0.75rem;
        }
        .stats-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .time-remaining {
            font-weight: bold;
            font-size: 0.9rem;
        }
        .time-remaining.overdue {
            color: #dc3545;
        }
        .time-remaining.soon {
            color: #ffc107;
        }
        .time-remaining.normal {
            color: #28a745;
        }
        .action-buttons .btn {
            margin-right: 0.5rem;
            margin-bottom: 0.5rem;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="../pages/dashboard.php">
                <i class="fas fa-graduation-cap"></i> <?php echo APP_NAME; ?>
            </a>
            <div class="navbar-nav ms-auto">
                <div class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-user"></i> <?php echo htmlspecialchars($user['username']); ?>
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="profile.php"><i class="fas fa-user-edit"></i> Profile</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="../pages/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container mt-4">
        <!-- Header -->
        <div class="row mb-4">
            <div class="col-md-8">
                <h2><i class="fas fa-tasks"></i> <?php echo htmlspecialchars($pageTitle); ?></h2>
                <p class="text-muted">
                    <?php if ($userRole === 'employee'): ?>
                        View and submit your assignments
                    <?php else: ?>
                        Manage assignments, submissions, and grading
                    <?php endif; ?>
                </p>
            </div>
            <div class="col-md-4 text-end">
                <?php if ($userRole !== 'employee'): ?>
                    <a href="assignments/create.php" class="btn btn-success">
                        <i class="fas fa-plus"></i> Create Assignment
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <!-- Success/Error Messages -->
        <?php if ($successMessage): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($successMessage); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if ($errorMessage): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($errorMessage); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Search and Filter -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" action="">
                    <div class="row">
                        <div class="col-md-4">
                            <label for="search" class="form-label">Search</label>
                            <input type="text" class="form-control" id="search" name="search" 
                                   value="<?php echo htmlspecialchars($searchTerm); ?>" 
                                   placeholder="Search assignments...">
                        </div>
                        <div class="col-md-3">
                            <label for="type" class="form-label">Type</label>
                            <select class="form-select" id="type" name="type">
                                <option value="">All Types</option>
                                <option value="homework" <?php echo $assignmentType === 'homework' ? 'selected' : ''; ?>>Homework</option>
                                <option value="project" <?php echo $assignmentType === 'project' ? 'selected' : ''; ?>>Project</option>
                                <option value="exam" <?php echo $assignmentType === 'exam' ? 'selected' : ''; ?>>Exam</option>
                                <option value="quiz" <?php echo $assignmentType === 'quiz' ? 'selected' : ''; ?>>Quiz</option>
                                <option value="essay" <?php echo $assignmentType === 'essay' ? 'selected' : ''; ?>>Essay</option>
                                <option value="presentation" <?php echo $assignmentType === 'presentation' ? 'selected' : ''; ?>>Presentation</option>
                            </select>
                        </div>
                        <?php if ($userRole !== 'employee'): ?>
                        <div class="col-md-3">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select" id="status" name="status">
                                <option value="active" <?php echo $status === 'active' ? 'selected' : ''; ?>>Active</option>
                                <option value="inactive" <?php echo $status === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                            </select>
                        </div>
                        <?php endif; ?>
                        <div class="col-md-2">
                            <label class="form-label">&nbsp;</label>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search"></i> Filter
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Assignment Stats (for teachers) -->
        <?php if ($userRole !== 'employee' && !empty($assignments)): ?>
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card stats-card">
                        <div class="card-body text-center">
                            <h4><?php echo count($assignments); ?></h4>
                            <small>Total Assignments</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card stats-card">
                        <div class="card-body text-center">
                            <h4><?php echo array_sum(array_column($assignments, 'submission_count')); ?></h4>
                            <small>Total Submissions</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card stats-card">
                        <div class="card-body text-center">
                            <h4><?php echo array_sum(array_column($assignments, 'graded_count')); ?></h4>
                            <small>Graded Submissions</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card stats-card">
                        <div class="card-body text-center">
                            <?php 
                            $avgCompletion = 0;
                            if (!empty($assignments)) {
                                $totalCompletion = array_sum(array_column($assignments, 'completion_rate'));
                                $avgCompletion = round($totalCompletion / count($assignments), 1);
                            }
                            ?>
                            <h4><?php echo $avgCompletion; ?>%</h4>
                            <small>Avg Completion</small>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Assignment List -->
        <?php if (!empty($assignments)): ?>
            <div class="row">
                <?php foreach ($assignments as $assignment): 
                    $timeRemaining = $assignment['time_remaining'];
                    $isOverdue = $timeRemaining['overdue'];
                    $timeClass = $isOverdue ? 'overdue' : ($timeRemaining['days'] <= 1 ? 'soon' : 'normal');
                ?>
                    <div class="col-md-6 mb-4">
                        <div class="card assignment-card <?php echo $isOverdue ? 'overdue' : ($timeRemaining['days'] <= 1 ? 'due-soon' : ''); ?>">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="card-title mb-0">
                                    <?php echo htmlspecialchars($assignment['title']); ?>
                                </h5>
                                <span class="badge bg-<?php echo $assignment['assignment_type'] === 'project' ? 'warning' : 'primary'; ?> status-badge">
                                    <?php echo ucfirst($assignment['assignment_type']); ?>
                                </span>
                            </div>
                            <div class="card-body">
                                <p class="card-text text-muted">
                                    <?php echo htmlspecialchars(substr($assignment['description'], 0, 150)); ?>
                                    <?php echo strlen($assignment['description']) > 150 ? '...' : ''; ?>
                                </p>
                                
                                <div class="row mb-3">
                                    <div class="col-6">
                                        <small class="text-muted">
                                            <i class="fas fa-calendar-alt"></i> Due: 
                                            <?php echo formatDate($assignment['due_date'], 'M j, Y g:i A'); ?>
                                        </small>
                                    </div>
                                    <div class="col-6">
                                        <small class="text-muted">
                                            <i class="fas fa-star"></i> Points: <?php echo $assignment['total_points']; ?>
                                        </small>
                                    </div>
                                </div>

                                <div class="time-remaining <?php echo $timeClass; ?> mb-3">
                                    <i class="fas fa-clock"></i>
                                    <?php if ($isOverdue): ?>
                                        Overdue
                                    <?php else: ?>
                                        <?php echo $timeRemaining['formatted']; ?> remaining
                                    <?php endif; ?>
                                </div>

                                <?php if ($userRole !== 'employee'): ?>
                                    <div class="row mb-3">
                                        <div class="col-6">
                                            <small class="text-muted">
                                                <i class="fas fa-user"></i> 
                                                <?php echo htmlspecialchars($assignment['teacher_name']); ?>
                                            </small>
                                        </div>
                                        <div class="col-6">
                                            <small class="text-muted">
                                                <i class="fas fa-paper-plane"></i> 
                                                <?php echo $assignment['submission_count']; ?> submissions
                                            </small>
                                        </div>
                                    </div>
                                    <div class="progress mb-3">
                                        <div class="progress-bar" role="progressbar" 
                                             style="width: <?php echo $assignment['completion_rate']; ?>%">
                                            <?php echo $assignment['completion_rate']; ?>% graded
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <div class="action-buttons">
                                    <a href="assignments/view.php?id=<?php echo $assignment['id']; ?>" 
                                       class="btn btn-primary btn-sm">
                                        <i class="fas fa-eye"></i> View Details
                                    </a>
                                    
                                    <?php if ($userRole !== 'employee'): ?>
                                        <a href="assignments/grade.php?id=<?php echo $assignment['id']; ?>" 
                                           class="btn btn-success btn-sm">
                                            <i class="fas fa-clipboard-check"></i> Grade
                                        </a>
                                        <a href="assignments/edit.php?id=<?php echo $assignment['id']; ?>" 
                                           class="btn btn-warning btn-sm">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>
                                    <?php else: ?>
                                        <?php
                                        // Check if student has submitted
                                        $studentSubmission = $assignmentManager->getStudentSubmission($assignment['id'], $user['id']);
                                        ?>
                                        <?php if ($studentSubmission): ?>
                                            <?php if ($studentSubmission['status'] === 'graded'): ?>
                                                <span class="badge bg-success">Graded</span>
                                                <?php if ($studentSubmission['percentage'] !== null): ?>
                                                    <small class="text-muted">
                                                        Score: <?php echo $studentSubmission['percentage']; ?>% 
                                                        (<?php echo $studentSubmission['letter_grade']; ?>)
                                                    </small>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <span class="badge bg-warning">Submitted</span>
                                            <?php endif; ?>
                                        <?php elseif (!$isOverdue || $assignment['allow_late_submission']): ?>
                                            <a href="assignments/submit.php?id=<?php echo $assignment['id']; ?>" 
                                               class="btn btn-success btn-sm">
                                                <i class="fas fa-upload"></i> Submit
                                            </a>
                                        <?php else: ?>
                                            <span class="badge bg-danger">Closed</span>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Pagination -->
            <?php if ($pagination['total_pages'] > 1): ?>
                <nav aria-label="Assignment pagination">
                    <ul class="pagination justify-content-center">
                        <?php if ($pagination['has_prev']): ?>
                            <li class="page-item">
                                <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $pagination['current_page'] - 1])); ?>">
                                    <i class="fas fa-chevron-left"></i> Previous
                                </a>
                            </li>
                        <?php endif; ?>

                        <?php
                        $startPage = max(1, $pagination['current_page'] - 2);
                        $endPage = min($pagination['total_pages'], $pagination['current_page'] + 2);
                        
                        for ($i = $startPage; $i <= $endPage; $i++):
                        ?>
                            <li class="page-item <?php echo $i === $pagination['current_page'] ? 'active' : ''; ?>">
                                <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>">
                                    <?php echo $i; ?>
                                </a>
                            </li>
                        <?php endfor; ?>

                        <?php if ($pagination['has_next']): ?>
                            <li class="page-item">
                                <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $pagination['current_page'] + 1])); ?>">
                                    Next <i class="fas fa-chevron-right"></i>
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            <?php endif; ?>

        <?php else: ?>
            <div class="text-center py-5">
                <i class="fas fa-tasks fa-3x text-muted mb-3"></i>
                <h4>No assignments found</h4>
                <p class="text-muted">
                    <?php if (!empty($searchTerm) || !empty($assignmentType)): ?>
                        Try adjusting your search criteria or filters.
                    <?php elseif ($userRole === 'employee'): ?>
                        You don't have any assignments yet.
                    <?php else: ?>
                        Create your first assignment to get started.
                        <br>
                        <a href="assignments/create.php" class="btn btn-success mt-3">
                            <i class="fas fa-plus"></i> Create Assignment
                        </a>
                    <?php endif; ?>
                </p>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>