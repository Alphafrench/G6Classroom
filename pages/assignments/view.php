<?php
/**
 * Assignment Details and View Page
 * 
 * This page displays assignment details, instructions, and submission status.
 * Supports both viewing for students and detailed management for teachers.
 */

session_start();
require_once '../../includes/config.php';
require_once '../../includes/class.Database.php';
require_once '../../includes/class.Assignment.php';
require_once '../../includes/auth.php';

// Initialize database and assignment manager
$db = getDatabase();
$assignmentManager = new Assignment($db);

// Check if user is logged in
if (!isLoggedIn()) {
    safeRedirect('../../pages/login.php', ['error' => 'Please log in to view assignments']);
}

$user = getCurrentUser();
$assignmentId = intval($_GET['id'] ?? 0);

if (!$assignmentId) {
    $_SESSION['error_message'] = 'Invalid assignment ID.';
    safeRedirect('../assignments/index.php');
}

// Get assignment details
$assignment = $assignmentManager->getById($assignmentId);

if (!$assignment) {
    $_SESSION['error_message'] = 'Assignment not found.';
    safeRedirect('../assignments/index.php');
}

// Get student submission (if user is a student)
$studentSubmission = null;
if ($user['role'] === 'employee') {
    $studentSubmission = $assignmentManager->getStudentSubmission($assignmentId, $user['id']);
}

// Handle file download for teachers (submission files)
if (isset($_GET['download_submission']) && $user['role'] !== 'employee') {
    $submissionId = intval($_GET['download_submission']);
    $submission = $db->fetchRow(
        "SELECT submission_file_path, original_filename, file_size 
         FROM assignment_submissions WHERE id = ? AND assignment_id = ?",
        [$submissionId, $assignmentId]
    );
    
    if ($submission && $submission['submission_file_path']) {
        $filePath = __DIR__ . '/../../' . $submission['submission_file_path'];
        if (file_exists($filePath)) {
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . $submission['original_filename'] . '"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($filePath));
            readfile($filePath);
            exit();
        }
    }
}

// Handle submission deletion for teachers
if (isset($_GET['delete_submission']) && $user['role'] !== 'employee') {
    $submissionId = intval($_GET['delete_submission']);
    $result = $db->delete('assignment_submissions', 'id = ? AND assignment_id = ?', [$submissionId, $assignmentId]);
    
    if ($result) {
        $_SESSION['success_message'] = 'Submission deleted successfully.';
    } else {
        $_SESSION['error_message'] = 'Failed to delete submission.';
    }
    safeRedirect("view.php?id={$assignmentId}");
}

// Get assignment statistics for teachers
$stats = null;
if ($user['role'] !== 'employee') {
    $stats = $assignmentManager->getStatistics($assignmentId);
}

// Get all submissions for this assignment (for teachers)
$submissions = [];
if ($user['role'] !== 'employee') {
    $sql = "SELECT s.*, u.username as student_name, u.email as student_email,
                   g.score, g.max_score, g.percentage, g.letter_grade, g.feedback, g.graded_at,
                   teacher.username as graded_by_name
            FROM assignment_submissions s
            LEFT JOIN users u ON s.student_id = u.id
            LEFT JOIN assignment_grades g ON s.id = g.submission_id
            LEFT JOIN users teacher ON g.graded_by = teacher.id
            WHERE s.assignment_id = ?
            ORDER BY s.submitted_at DESC";
    
    $submissions = $db->fetchAll($sql, [$assignmentId]);
}

$pageTitle = htmlspecialchars($assignment['title']);

// Check if student can still submit
$canSubmit = false;
$isOverdue = $assignment['is_overdue'];
$attemptsRemaining = 0;

if ($user['role'] === 'employee') {
    // Check if student can submit
    if (!$isOverdue || $assignment['allow_late_submission']) {
        if (!$studentSubmission || $studentSubmission['attempt_number'] < $assignment['max_attempts']) {
            $attemptsRemaining = $assignment['max_attempts'] - ($studentSubmission['attempt_number'] ?? 0);
            $canSubmit = true;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - <?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .assignment-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 10px;
            padding: 30px;
            margin-bottom: 30px;
        }
        .info-card {
            border-left: 4px solid #007bff;
            margin-bottom: 20px;
        }
        .time-remaining {
            font-size: 1.1rem;
            font-weight: bold;
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
        .submission-card {
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 15px;
        }
        .status-badge {
            font-size: 0.8rem;
        }
        .grade-badge {
            font-size: 1rem;
            padding: 8px 12px;
        }
        .file-download {
            text-decoration: none;
            color: inherit;
        }
        .file-download:hover {
            color: #007bff;
        }
        .stats-section {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 30px;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="../../pages/dashboard.php">
                <i class="fas fa-graduation-cap"></i> <?php echo APP_NAME; ?>
            </a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="index.php">
                    <i class="fas fa-arrow-left"></i> Back to Assignments
                </a>
                <?php if ($user['role'] !== 'employee'): ?>
                    <a class="nav-link" href="grade.php?id=<?php echo $assignmentId; ?>">
                        <i class="fas fa-clipboard-check"></i> Grade Submissions
                    </a>
                    <a class="nav-link" href="edit.php?id=<?php echo $assignmentId; ?>">
                        <i class="fas fa-edit"></i> Edit Assignment
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container mt-4">
        <!-- Assignment Header -->
        <div class="assignment-header">
            <div class="row">
                <div class="col-md-8">
                    <h1 class="mb-2"><?php echo $assignment['title']; ?></h1>
                    <p class="mb-2">
                        <span class="badge bg-light text-dark">
                            <?php echo ucfirst($assignment['assignment_type']); ?>
                        </span>
                        <?php if (!$assignment['is_active']): ?>
                            <span class="badge bg-warning text-dark ms-2">Inactive</span>
                        <?php endif; ?>
                    </p>
                    <p class="mb-0">
                        <i class="fas fa-user"></i> 
                        Created by <?php echo htmlspecialchars($assignment['teacher_name']); ?>
                    </p>
                </div>
                <div class="col-md-4 text-end">
                    <div class="time-remaining <?php echo $isOverdue ? 'overdue' : ($assignment['time_remaining']['days'] <= 1 ? 'soon' : 'normal'); ?>">
                        <?php if ($isOverdue): ?>
                            <i class="fas fa-exclamation-triangle"></i><br>
                            Overdue
                        <?php else: ?>
                            <i class="fas fa-clock"></i><br>
                            <?php echo $assignment['time_remaining']['formatted']; ?> remaining
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Assignment Information -->
        <div class="row">
            <div class="col-md-8">
                <!-- Description -->
                <div class="card info-card">
                    <div class="card-header">
                        <h5><i class="fas fa-info-circle"></i> Description</h5>
                    </div>
                    <div class="card-body">
                        <p><?php echo nl2br(htmlspecialchars($assignment['description'])); ?></p>
                    </div>
                </div>

                <!-- Instructions -->
                <?php if (!empty($assignment['instructions'])): ?>
                    <div class="card info-card">
                        <div class="card-header">
                            <h5><i class="fas fa-list-ul"></i> Instructions</h5>
                        </div>
                        <div class="card-body">
                            <?php echo nl2br(htmlspecialchars($assignment['instructions'])); ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Student Submission Status -->
                <?php if ($user['role'] === 'employee'): ?>
                    <div class="card">
                        <div class="card-header">
                            <h5><i class="fas fa-file-upload"></i> Your Submission</h5>
                        </div>
                        <div class="card-body">
                            <?php if ($studentSubmission): ?>
                                <div class="submission-card">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <h6>Submission Details</h6>
                                        <span class="badge bg-<?php echo $studentSubmission['status'] === 'graded' ? 'success' : 'warning'; ?> status-badge">
                                            <?php echo ucfirst($studentSubmission['status']); ?>
                                        </span>
                                    </div>
                                    
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <small class="text-muted">
                                                <i class="fas fa-clock"></i> 
                                                Submitted: <?php echo formatDateTime($studentSubmission['submitted_at']); ?>
                                            </small>
                                        </div>
                                        <div class="col-md-6">
                                            <small class="text-muted">
                                                <i class="fas fa-hashtag"></i> 
                                                Attempt #<?php echo $studentSubmission['attempt_number']; ?>
                                            </small>
                                        </div>
                                    </div>

                                    <?php if ($studentSubmission['is_late']): ?>
                                        <div class="alert alert-warning">
                                            <i class="fas fa-exclamation-triangle"></i> 
                                            This submission was <?php echo $studentSubmission['late_days']; ?> day(s) late.
                                        </div>
                                    <?php endif; ?>

                                    <?php if (!empty($studentSubmission['submission_text'])): ?>
                                        <h6>Submission Text:</h6>
                                        <div class="bg-light p-3 rounded mb-3">
                                            <?php echo nl2br(htmlspecialchars($studentSubmission['submission_text'])); ?>
                                        </div>
                                    <?php endif; ?>

                                    <?php if ($studentSubmission['original_filename']): ?>
                                        <h6>Submitted File:</h6>
                                        <div class="alert alert-info">
                                            <i class="fas fa-file"></i> 
                                            <strong><?php echo htmlspecialchars($studentSubmission['original_filename']); ?></strong>
                                            <?php if ($studentSubmission['file_size']): ?>
                                                (<?php echo round($studentSubmission['file_size'] / 1024, 1); ?> KB)
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>

                                    <?php if ($studentSubmission['status'] === 'graded' && $studentSubmission['percentage'] !== null): ?>
                                        <div class="mt-3">
                                            <h6>Grade:</h6>
                                            <div class="d-flex align-items-center mb-2">
                                                <span class="badge bg-primary grade-badge me-2">
                                                    <?php echo $studentSubmission['percentage']; ?>%
                                                </span>
                                                <span class="badge bg-<?php echo $studentSubmission['percentage'] >= 60 ? 'success' : 'danger'; ?> grade-badge">
                                                    Grade: <?php echo $studentSubmission['letter_grade']; ?>
                                                </span>
                                                <small class="text-muted ms-2">
                                                    (<?php echo $studentSubmission['score']; ?> / <?php echo $studentSubmission['max_score']; ?> points)
                                                </small>
                                            </div>
                                            
                                            <?php if (!empty($studentSubmission['teacher_feedback'])): ?>
                                                <div class="mt-3">
                                                    <h6>Teacher Feedback:</h6>
                                                    <div class="bg-light p-3 rounded">
                                                        <?php echo nl2br(htmlspecialchars($studentSubmission['teacher_feedback'])); ?>
                                                    </div>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <?php if ($canSubmit && $studentSubmission['attempt_number'] < $assignment['max_attempts']): ?>
                                    <div class="text-center">
                                        <a href="submit.php?id=<?php echo $assignmentId; ?>" class="btn btn-primary">
                                            <i class="fas fa-redo"></i> Resubmit Assignment
                                        </a>
                                    </div>
                                <?php elseif (!$canSubmit): ?>
                                    <div class="text-center">
                                        <p class="text-muted">
                                            <?php if ($isOverdue && !$assignment['allow_late_submission']): ?>
                                                <i class="fas fa-lock"></i> Submissions are closed for this assignment.
                                            <?php elseif ($studentSubmission['attempt_number'] >= $assignment['max_attempts']): ?>
                                                <i class="fas fa-ban"></i> Maximum attempts reached.
                                            <?php endif; ?>
                                        </p>
                                    </div>
                                <?php endif; ?>

                            <?php elseif ($canSubmit): ?>
                                <div class="text-center">
                                    <p class="text-muted mb-3">You haven't submitted this assignment yet.</p>
                                    <a href="submit.php?id=<?php echo $assignmentId; ?>" class="btn btn-success">
                                        <i class="fas fa-upload"></i> Submit Assignment
                                    </a>
                                </div>
                            <?php else: ?>
                                <div class="text-center">
                                    <p class="text-muted">
                                        <?php if ($isOverdue && !$assignment['allow_late_submission']): ?>
                                            <i class="fas fa-lock"></i> Submissions are closed for this assignment.
                                        <?php endif; ?>
                                    </p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Teacher: View All Submissions -->
                <?php if ($user['role'] !== 'employee' && !empty($submissions)): ?>
                    <div class="card">
                        <div class="card-header">
                            <h5><i class="fas fa-users"></i> Submissions (<?php echo count($submissions); ?>)</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Student</th>
                                            <th>Submitted</th>
                                            <th>Status</th>
                                            <th>Grade</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($submissions as $submission): ?>
                                            <tr>
                                                <td>
                                                    <strong><?php echo htmlspecialchars($submission['student_name']); ?></strong>
                                                    <br><small class="text-muted"><?php echo htmlspecialchars($submission['student_email']); ?></small>
                                                </td>
                                                <td>
                                                    <?php echo formatDateTime($submission['submitted_at']); ?>
                                                    <?php if ($submission['is_late']): ?>
                                                        <br><span class="badge bg-warning text-dark">Late</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <span class="badge bg-<?php echo $submission['status'] === 'graded' ? 'success' : 'warning'; ?>">
                                                        <?php echo ucfirst($submission['status']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <?php if ($submission['percentage'] !== null): ?>
                                                        <span class="badge bg-primary"><?php echo $submission['percentage']; ?>%</span>
                                                        <br><small class="text-muted"><?php echo $submission['letter_grade']; ?></small>
                                                    <?php else: ?>
                                                        <span class="text-muted">Not graded</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <div class="btn-group btn-group-sm">
                                                        <a href="grade.php?submission_id=<?php echo $submission['id']; ?>" 
                                                           class="btn btn-outline-primary" title="Grade">
                                                            <i class="fas fa-clipboard-check"></i>
                                                        </a>
                                                        <?php if ($submission['original_filename']): ?>
                                                            <a href="?id=<?php echo $assignmentId; ?>&download_submission=<?php echo $submission['id']; ?>" 
                                                               class="btn btn-outline-info" title="Download File">
                                                                <i class="fas fa-download"></i>
                                                            </a>
                                                        <?php endif; ?>
                                                        <a href="?id=<?php echo $assignmentId; ?>&delete_submission=<?php echo $submission['id']; ?>" 
                                                           class="btn btn-outline-danger" 
                                                           onclick="return confirm('Are you sure you want to delete this submission?');" title="Delete">
                                                            <i class="fas fa-trash"></i>
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Sidebar -->
            <div class="col-md-4">
                <!-- Assignment Details -->
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fas fa-info"></i> Assignment Details</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-sm">
                            <tr>
                                <td><strong>Due Date:</strong></td>
                                <td><?php echo formatDateTime($assignment['due_date']); ?></td>
                            </tr>
                            <tr>
                                <td><strong>Total Points:</strong></td>
                                <td><?php echo $assignment['total_points']; ?></td>
                            </tr>
                            <tr>
                                <td><strong>Max Attempts:</strong></td>
                                <td><?php echo $assignment['max_attempts']; ?></td>
                            </tr>
                            <tr>
                                <td><strong>Late Submissions:</strong></td>
                                <td>
                                    <?php if ($assignment['allow_late_submission']): ?>
                                        <span class="text-success">Allowed</span>
                                        <?php if ($assignment['late_penalty_per_day'] > 0): ?>
                                            <br><small class="text-muted"><?php echo $assignment['late_penalty_per_day']; ?>% penalty/day</small>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="text-danger">Not Allowed</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <td><strong>File Upload:</strong></td>
                                <td>
                                    <?php if ($assignment['requires_file_upload']): ?>
                                        <span class="text-warning">Required</span>
                                        <?php if ($assignment['max_file_size']): ?>
                                            <br><small class="text-muted">Max: <?php echo round($assignment['max_file_size'] / 1024 / 1024, 1); ?>MB</small>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="text-info">Optional</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php if (!empty($assignment['allowed_file_types'])): ?>
                                <tr>
                                    <td><strong>Allowed Types:</strong></td>
                                    <td>
                                        <?php foreach ($assignment['allowed_file_types'] as $type): ?>
                                            <span class="badge bg-light text-dark"><?php echo strtoupper($type); ?></span>
                                        <?php endforeach; ?>
                                    </td>
                                </tr>
                            <?php endif; ?>
                            <tr>
                                <td><strong>Created:</strong></td>
                                <td><?php echo formatDateTime($assignment['created_at']); ?></td>
                            </tr>
                        </table>

                        <?php if ($user['role'] === 'employee' && $canSubmit): ?>
                            <div class="d-grid mt-3">
                                <a href="submit.php?id=<?php echo $assignmentId; ?>" class="btn btn-success">
                                    <i class="fas fa-upload"></i> Submit Assignment
                                </a>
                                <?php if ($attemptsRemaining > 1): ?>
                                    <small class="text-muted text-center mt-2">
                                        <?php echo $attemptsRemaining; ?> attempts remaining
                                    </small>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Teacher Statistics -->
                <?php if ($user['role'] !== 'employee' && $stats): ?>
                    <div class="stats-section mt-3">
                        <h5><i class="fas fa-chart-bar"></i> Statistics</h5>
                        <div class="row text-center">
                            <div class="col-6">
                                <h4 class="text-primary"><?php echo $stats['total_submissions']; ?></h4>
                                <small>Total Submissions</small>
                            </div>
                            <div class="col-6">
                                <h4 class="text-success"><?php echo $stats['graded_submissions']; ?></h4>
                                <small>Graded</small>
                            </div>
                        </div>
                        <?php if ($stats['total_submissions'] > 0): ?>
                            <hr>
                            <div class="row text-center">
                                <div class="col-6">
                                    <h5 class="text-info"><?php echo $stats['avg_score'] ? round($stats['avg_score'], 1) . '%' : 'N/A'; ?></h5>
                                    <small>Average Score</small>
                                </div>
                                <div class="col-6">
                                    <h5 class="text-warning"><?php echo $stats['submission_rate']; ?>%</h5>
                                    <small>Graded Rate</small>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>