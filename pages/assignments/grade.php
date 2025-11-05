<?php
/**
 * Assignment Grading Page
 * 
 * This page allows teachers to view submissions and grade assignments with detailed feedback.
 */

session_start();
require_once '../../includes/config.php';
require_once '../../includes/class.Database.php';
require_once '../../includes/class.Assignment.php';
require_once '../../includes/auth.php';

// Initialize database and assignment manager
$db = getDatabase();
$assignmentManager = new Assignment($db);

// Check if user is logged in and is a teacher/admin
if (!isLoggedIn()) {
    safeRedirect('../../pages/login.php', ['error' => 'Please log in to access grading']);
}

$user = getCurrentUser();
if ($user['role'] === 'employee') {
    $_SESSION['error_message'] = 'You do not have permission to access the grading interface.';
    safeRedirect('../assignments/index.php');
}

// Get assignment ID (either from URL or from submission grading)
$assignmentId = intval($_GET['id'] ?? $_GET['assignment_id'] ?? 0);
$submissionId = intval($_GET['submission_id'] ?? 0);

// Handle individual submission grading
if ($submissionId) {
    // Get specific submission for grading
    $sql = "SELECT s.*, a.title as assignment_title, a.total_points, a.due_date, a.teacher_id,
                   u.username as student_name, u.email as student_email
            FROM assignment_submissions s
            JOIN assignments a ON s.assignment_id = a.id
            JOIN users u ON s.student_id = u.id
            WHERE s.id = ? AND s.assignment_id = ?";
    
    $submission = $db->fetchRow($sql, [$submissionId, $assignmentId]);
    
    if (!$submission) {
        $_SESSION['error_message'] = 'Submission not found.';
        safeRedirect("../assignments/index.php");
    }
    
    // Check if teacher owns this assignment
    if ($submission['teacher_id'] != $user['id']) {
        $_SESSION['error_message'] = 'You do not have permission to grade this submission.';
        safeRedirect("../assignments/index.php");
    }
}

// Get assignment details
$assignment = $assignmentManager->getById($assignmentId);

if (!$assignment) {
    $_SESSION['error_message'] = 'Assignment not found.';
    safeRedirect('../assignments/index.php');
}

// Check if teacher owns this assignment
if ($assignment['teacher_id'] != $user['id']) {
    $_SESSION['error_message'] = 'You do not have permission to grade this assignment.';
    safeRedirect('../assignments/index.php');
}

// Handle grading form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['grade_submission'])) {
    $submissionId = intval($_POST['submission_id']);
    $score = floatval($_POST['score']);
    $feedback = $_POST['feedback'] ?? '';
    
    $result = $assignmentManager->gradeSubmission($submissionId, $user['id'], $score, $feedback);
    
    if ($result['success']) {
        $_SESSION['success_message'] = "Submission graded successfully! Score: {$result['percentage']}% ({$result['letter_grade']})";
        
        // If this was from a specific submission, redirect back to assignment view
        if (isset($_GET['submission_id'])) {
            safeRedirect("view.php?id={$assignmentId}");
        } else {
            // Redirect to same page to refresh the list
            safeRedirect("grade.php?id={$assignmentId}");
        }
    } else {
        $gradingError = implode('<br>', $result['errors']);
    }
}

// Get all submissions for this assignment
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

// Get assignment statistics
$stats = $assignmentManager->getStatistics($assignmentId);

$pageTitle = 'Grade Assignment - ' . htmlspecialchars($assignment['title']);

// Handle AJAX requests for grade updates
if (isset($_GET['ajax']) && $_GET['ajax'] === 'update_grade' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    
    $submissionId = intval($_POST['submission_id']);
    $score = floatval($_POST['score']);
    $feedback = $_POST['feedback'] ?? '';
    
    $result = $assignmentManager->gradeSubmission($submissionId, $user['id'], $score, $feedback);
    
    echo json_encode($result);
    exit;
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
        .grading-header {
            background: linear-gradient(135deg, #6f42c1 0%, #e83e8c 100%);
            color: white;
            border-radius: 10px;
            padding: 30px;
            margin-bottom: 30px;
        }
        .submission-card {
            border: 1px solid #dee2e6;
            border-radius: 10px;
            margin-bottom: 20px;
            transition: all 0.3s ease;
        }
        .submission-card:hover {
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        .submission-header {
            background: #f8f9fa;
            border-bottom: 1px solid #dee2e6;
            border-radius: 10px 10px 0 0;
            padding: 15px 20px;
        }
        .submission-content {
            padding: 20px;
        }
        .submission-text {
            background: #f8f9fa;
            border-left: 4px solid #007bff;
            padding: 15px;
            margin: 15px 0;
            border-radius: 0 5px 5px 0;
            max-height: 300px;
            overflow-y: auto;
        }
        .grade-input-group {
            background: #e7f3ff;
            border: 1px solid #b3d9ff;
            border-radius: 8px;
            padding: 20px;
            margin-top: 15px;
        }
        .grade-display {
            background: #f0f8f0;
            border: 1px solid #c3e6c3;
            border-radius: 8px;
            padding: 15px;
            margin-top: 15px;
        }
        .stats-card {
            background: linear-gradient(135deg, #17a2b8 0%, #6f42c1 100%);
            color: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .file-download-link {
            text-decoration: none;
            color: inherit;
            display: block;
            padding: 10px;
            background: #f8f9fa;
            border-radius: 5px;
            margin: 10px 0;
            border: 1px solid #dee2e6;
            transition: all 0.2s;
        }
        .file-download-link:hover {
            background: #e9ecef;
            color: #007bff;
        }
        .status-badge {
            font-size: 0.75rem;
            padding: 4px 8px;
        }
        .quick-grade-btn {
            margin: 2px;
            padding: 2px 8px;
            font-size: 0.8rem;
        }
        .feedback-textarea {
            min-height: 100px;
        }
        .submission-meta {
            font-size: 0.875rem;
            color: #6c757d;
        }
        .late-submission {
            border-left: 4px solid #ffc107;
        }
        .graded-submission {
            border-left: 4px solid #28a745;
        }
        .ungraded-submission {
            border-left: 4px solid #dc3545;
        }
        .expand-content {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease;
        }
        .expand-content.expanded {
            max-height: 1000px;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark" style="background: linear-gradient(135deg, #6f42c1 0%, #e83e8c 100%);">
        <div class="container">
            <a class="navbar-brand" href="../../pages/dashboard.php">
                <i class="fas fa-graduation-cap"></i> <?php echo APP_NAME; ?>
            </a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="view.php?id=<?php echo $assignmentId; ?>">
                    <i class="fas fa-eye"></i> View Assignment
                </a>
                <a class="nav-link" href="index.php">
                    <i class="fas fa-arrow-left"></i> Back to Assignments
                </a>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container mt-4">
        <!-- Header -->
        <div class="grading-header">
            <div class="row">
                <div class="col-md-8">
                    <h1 class="mb-2"><i class="fas fa-clipboard-check"></i> Grade Submissions</h1>
                    <h4 class="mb-2"><?php echo htmlspecialchars($assignment['title']); ?></h4>
                    <p class="mb-0">
                        <span class="badge bg-light text-dark">
                            <?php echo ucfirst($assignment['assignment_type']); ?>
                        </span>
                        <span class="badge bg-light text-dark ms-2">
                            <?php echo $assignment['total_points']; ?> points
                        </span>
                        <span class="badge bg-light text-dark ms-2">
                            Due: <?php echo formatDate($assignment['due_date'], 'M j, Y g:i A'); ?>
                        </span>
                    </p>
                </div>
                <div class="col-md-4 text-end">
                    <?php if ($stats): ?>
                        <div class="text-white">
                            <h5><?php echo $stats['total_submissions']; ?></h5>
                            <small>Total Submissions</small>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Success/Error Messages -->
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($_SESSION['success_message']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['success_message']); ?>
        <?php endif; ?>

        <?php if (isset($gradingError)): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i> <?php echo $gradingError; ?>
            </div>
        <?php endif; ?>

        <!-- Statistics -->
        <?php if ($stats && $stats['total_submissions'] > 0): ?>
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="stats-card text-center">
                        <h3><?php echo $stats['total_submissions']; ?></h3>
                        <p class="mb-0">Total Submissions</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stats-card text-center">
                        <h3><?php echo $stats['graded_submissions']; ?></h3>
                        <p class="mb-0">Graded</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stats-card text-center">
                        <h3><?php echo $stats['submission_rate']; ?>%</h3>
                        <p class="mb-0">Grading Progress</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stats-card text-center">
                        <h3><?php echo $stats['avg_score'] ? round($stats['avg_score'], 1) . '%' : 'N/A'; ?></h3>
                        <p class="mb-0">Average Score</p>
                    </div>
                </div>
            </div>

            <!-- Grade Distribution -->
            <?php if ($stats['total_submissions'] > 0 && ($stats['count_A'] + $stats['count_B'] + $stats['count_C'] + $stats['count_D'] + $stats['count_F']) > 0): ?>
                <div class="card mb-4">
                    <div class="card-header">
                        <h5><i class="fas fa-chart-bar"></i> Grade Distribution</h5>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col">
                                <h5 class="text-success">A</h5>
                                <span class="badge bg-success fs-6"><?php echo $stats['count_A']; ?></span>
                            </div>
                            <div class="col">
                                <h5 class="text-info">B</h5>
                                <span class="badge bg-info fs-6"><?php echo $stats['count_B']; ?></span>
                            </div>
                            <div class="col">
                                <h5 class="text-warning">C</h5>
                                <span class="badge bg-warning fs-6"><?php echo $stats['count_C']; ?></span>
                            </div>
                            <div class="col">
                                <h5 class="text-danger">D</h5>
                                <span class="badge bg-danger fs-6"><?php echo $stats['count_D']; ?></span>
                            </div>
                            <div class="col">
                                <h5 class="text-dark">F</h5>
                                <span class="badge bg-dark fs-6"><?php echo $stats['count_F']; ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <!-- Submissions List -->
        <?php if (!empty($submissions)): ?>
            <div class="row">
                <div class="col-12">
                    <h5><i class="fas fa-list"></i> Submissions (<?php echo count($submissions); ?>)</h5>
                    
                    <?php foreach ($submissions as $index => $submission): 
                        $isGraded = $submission['percentage'] !== null;
                        $isLate = $submission['is_late'];
                        $cardClass = $isGraded ? 'graded-submission' : ($isLate ? 'late-submission' : 'ungraded-submission');
                    ?>
                        <div class="card submission-card <?php echo $cardClass; ?>">
                            <div class="submission-header d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-1">
                                        <i class="fas fa-user"></i> 
                                        <?php echo htmlspecialchars($submission['student_name']); ?>
                                        <small class="text-muted">(<?php echo htmlspecialchars($submission['student_email']); ?>)</small>
                                    </h6>
                                    <small class="submission-meta">
                                        <i class="fas fa-clock"></i> 
                                        Submitted: <?php echo formatDateTime($submission['submitted_at']); ?>
                                        <?php if ($isLate): ?>
                                            <span class="badge bg-warning text-dark ms-1">Late</span>
                                        <?php endif; ?>
                                        <span class="badge bg-secondary ms-1">Attempt #<?php echo $submission['attempt_number']; ?></span>
                                    </small>
                                </div>
                                <div class="text-end">
                                    <?php if ($isGraded): ?>
                                        <span class="badge bg-success fs-6"><?php echo $submission['percentage']; ?>%</span>
                                        <br>
                                        <small class="text-muted">Grade: <?php echo $submission['letter_grade']; ?></small>
                                    <?php else: ?>
                                        <span class="badge bg-danger fs-6">Not Graded</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="submission-content">
                                <!-- Submission Text -->
                                <?php if (!empty($submission['submission_text'])): ?>
                                    <div class="mb-3">
                                        <h6>Submission Text:</h6>
                                        <div class="submission-text">
                                            <?php echo nl2br(htmlspecialchars($submission['submission_text'])); ?>
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <!-- File Download -->
                                <?php if ($submission['original_filename']): ?>
                                    <div class="mb-3">
                                        <h6>Submitted File:</h6>
                                        <a href="view.php?id=<?php echo $assignmentId; ?>&download_submission=<?php echo $submission['id']; ?>" 
                                           class="file-download-link">
                                            <i class="fas fa-download"></i> 
                                            <?php echo htmlspecialchars($submission['original_filename']); ?>
                                            <?php if ($submission['file_size']): ?>
                                                (<?php echo round($submission['file_size'] / 1024, 1); ?> KB)
                                            <?php endif; ?>
                                        </a>
                                    </div>
                                <?php endif; ?>

                                <!-- Existing Grade Display -->
                                <?php if ($isGraded): ?>
                                    <div class="grade-display">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <strong>Current Grade:</strong> 
                                                <span class="badge bg-primary fs-6"><?php echo $submission['percentage']; ?>%</span>
                                                <span class="badge bg-<?php echo $submission['percentage'] >= 60 ? 'success' : 'danger'; ?> fs-6">
                                                    <?php echo $submission['letter_grade']; ?>
                                                </span>
                                                <br>
                                                <small class="text-muted">
                                                    Score: <?php echo $submission['score']; ?> / <?php echo $submission['max_score']; ?> points
                                                </small>
                                            </div>
                                            <div class="col-md-6">
                                                <small class="text-muted">
                                                    Graded by <?php echo htmlspecialchars($submission['graded_by_name']); ?><br>
                                                    <?php echo formatDateTime($submission['graded_at']); ?>
                                                </small>
                                            </div>
                                        </div>
                                        
                                        <?php if (!empty($submission['feedback'])): ?>
                                            <div class="mt-2">
                                                <strong>Teacher Feedback:</strong>
                                                <div class="bg-light p-3 rounded mt-1">
                                                    <?php echo nl2br(htmlspecialchars($submission['feedback'])); ?>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>

                                <!-- Grade/Update Form -->
                                <div class="mt-3">
                                    <button type="button" class="btn btn-primary btn-sm" 
                                            onclick="toggleGradeForm(<?php echo $submission['id']; ?>)">
                                        <i class="fas fa-clipboard-check"></i> 
                                        <?php echo $isGraded ? 'Update Grade' : 'Grade Submission'; ?>
                                    </button>
                                    
                                    <?php if ($isGraded): ?>
                                        <button type="button" class="btn btn-outline-secondary btn-sm ms-2" 
                                                onclick="quickGrade(<?php echo $submission['id']; ?>, <?php echo $assignment['total_points']; ?>)">
                                            <i class="fas fa-star"></i> Quick Grade
                                        </button>
                                    <?php endif; ?>
                                </div>

                                <!-- Grade Form -->
                                <div id="gradeForm_<?php echo $submission['id']; ?>" class="grade-input-group mt-3" style="display: none;">
                                    <form method="POST" onsubmit="return validateGrade(<?php echo $submission['id']; ?>)">
                                        <input type="hidden" name="submission_id" value="<?php echo $submission['id']; ?>">
                                        <input type="hidden" name="grade_submission" value="1">
                                        
                                        <div class="row">
                                            <div class="col-md-4">
                                                <label for="score_<?php echo $submission['id']; ?>" class="form-label">
                                                    Score (out of <?php echo $assignment['total_points']; ?>)
                                                </label>
                                                <div class="input-group">
                                                    <input type="number" class="form-control" 
                                                           id="score_<?php echo $submission['id']; ?>" 
                                                           name="score" 
                                                           value="<?php echo $isGraded ? $submission['score'] : ''; ?>"
                                                           min="0" max="<?php echo $assignment['total_points']; ?>" 
                                                           step="0.5" required>
                                                    <span class="input-group-text">/ <?php echo $assignment['total_points']; ?></span>
                                                </div>
                                            </div>
                                            <div class="col-md-8">
                                                <label class="form-label">Quick Grade</label><br>
                                                <?php 
                                                $quickGrades = [
                                                    'A' => $assignment['total_points'] * 0.9,
                                                    'B' => $assignment['total_points'] * 0.8,
                                                    'C' => $assignment['total_points'] * 0.7,
                                                    'D' => $assignment['total_points'] * 0.6,
                                                    'F' => $assignment['total_points'] * 0.5
                                                ];
                                                foreach ($quickGrades as $grade => $points):
                                                ?>
                                                    <button type="button" class="btn btn-outline-primary quick-grade-btn" 
                                                            onclick="setQuickScore(<?php echo $submission['id']; ?>, <?php echo round($points, 1); ?>, '<?php echo $grade; ?>')">
                                                        <?php echo $grade; ?> (<?php echo round($points, 1); ?>)
                                                    </button>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                        
                                        <div class="mt-3">
                                            <label for="feedback_<?php echo $submission['id']; ?>" class="form-label">Feedback (Optional)</label>
                                            <textarea class="form-control feedback-textarea" 
                                                      id="feedback_<?php echo $submission['id']; ?>" 
                                                      name="feedback" 
                                                      placeholder="Provide feedback to the student..."><?php echo $isGraded ? htmlspecialchars($submission['feedback']) : ''; ?></textarea>
                                            <div class="form-text">This feedback will be visible to the student</div>
                                        </div>
                                        
                                        <div class="mt-3">
                                            <button type="submit" class="btn btn-success">
                                                <i class="fas fa-save"></i> 
                                                <?php echo $isGraded ? 'Update Grade' : 'Save Grade'; ?>
                                            </button>
                                            <button type="button" class="btn btn-secondary ms-2" 
                                                    onclick="toggleGradeForm(<?php echo $submission['id']; ?>)">
                                                Cancel
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php else: ?>
            <div class="text-center py-5">
                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                <h4>No submissions yet</h4>
                <p class="text-muted">Students haven't submitted this assignment yet.</p>
                <a href="view.php?id=<?php echo $assignmentId; ?>" class="btn btn-primary">
                    <i class="fas fa-eye"></i> View Assignment Details
                </a>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function toggleGradeForm(submissionId) {
            const form = document.getElementById('gradeForm_' + submissionId);
            if (form.style.display === 'none') {
                form.style.display = 'block';
            } else {
                form.style.display = 'none';
            }
        }

        function setQuickScore(submissionId, score, grade) {
            document.getElementById('score_' + submissionId).value = score;
            
            // Update button states
            const buttons = document.querySelectorAll('#gradeForm_' + submissionId + ' .quick-grade-btn');
            buttons.forEach(btn => {
                btn.classList.remove('btn-primary', 'active');
                btn.classList.add('btn-outline-primary');
            });
            
            event.target.classList.remove('btn-outline-primary');
            event.target.classList.add('btn-primary', 'active');
        }

        function validateGrade(submissionId) {
            const score = parseFloat(document.getElementById('score_' + submissionId).value);
            const maxScore = <?php echo $assignment['total_points']; ?>;
            
            if (score < 0 || score > maxScore) {
                alert('Score must be between 0 and ' + maxScore);
                return false;
            }
            
            return confirm('Are you sure you want to save this grade?');
        }

        function quickGrade(submissionId, maxScore) {
            const percentages = {
                'A': 95,
                'B': 85,
                'C': 75,
                'D': 65,
                'F': 50
            };
            
            let grade = prompt('Enter quick grade (A, B, C, D, F):', 'A');
            grade = grade ? grade.toUpperCase() : '';
            
            if (percentages[grade]) {
                const score = Math.round((maxScore * percentages[grade] / 100) * 10) / 10;
                document.getElementById('score_' + submissionId).value = score;
                toggleGradeForm(submissionId);
            } else if (grade !== '') {
                alert('Invalid grade. Please enter A, B, C, D, or F.');
            }
        }

        // Auto-save feedback as draft
        document.addEventListener('DOMContentLoaded', function() {
            const feedbackTextareas = document.querySelectorAll('.feedback-textarea');
            
            feedbackTextareas.forEach(textarea => {
                let saveTimeout;
                
                textarea.addEventListener('input', function() {
                    clearTimeout(saveTimeout);
                    saveTimeout = setTimeout(() => {
                        // Save to localStorage
                        const submissionId = this.id.replace('feedback_', '');
                        localStorage.setItem('feedback_' + submissionId, this.value);
                    }, 1000);
                });
                
                // Load saved feedback
                const submissionId = textarea.id.replace('feedback_', '');
                const saved = localStorage.getItem('feedback_' + submissionId);
                if (saved && !textarea.value) {
                    textarea.value = saved;
                }
            });
        });

        // Show current grade percentage in real-time
        document.addEventListener('input', function(e) {
            if (e.target.name === 'score' && e.target.id.startsWith('score_')) {
                const submissionId = e.target.id.replace('score_', '');
                const maxScore = <?php echo $assignment['total_points']; ?>;
                const score = parseFloat(e.target.value) || 0;
                const percentage = Math.round((score / maxScore) * 100);
                
                // Find or create percentage display
                let percentageDisplay = document.getElementById('percentage_' + submissionId);
                if (!percentageDisplay) {
                    percentageDisplay = document.createElement('small');
                    percentageDisplay.id = 'percentage_' + submissionId;
                    percentageDisplay.className = 'text-muted';
                    e.target.parentNode.appendChild(percentageDisplay);
                }
                
                percentageDisplay.textContent = ` (${percentage}%)`;
            }
        });
    </script>
</body>
</html>