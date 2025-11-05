<?php
/**
 * Student Assignment Submission Page
 * 
 * This page allows students to submit assignments with file upload support and text submission.
 */

session_start();
require_once '../../includes/config.php';
require_once '../../includes/class.Database.php';
require_once '../../includes/class.Assignment.php';
require_once '../../includes/auth.php';

// Initialize database and assignment manager
$db = getDatabase();
$assignmentManager = new Assignment($db);

// Check if user is logged in and is a student
if (!isLoggedIn()) {
    safeRedirect('../../pages/login.php', ['error' => 'Please log in to submit assignments']);
}

$user = getCurrentUser();
if ($user['role'] !== 'employee') {
    $_SESSION['error_message'] = 'Students only can access the submission page.';
    safeRedirect('../assignments/index.php');
}

$assignmentId = intval($_GET['id'] ?? 0);

if (!$assignmentId) {
    $_SESSION['error_message'] = 'Invalid assignment ID.';
    safeRedirect('../assignments/index.php');
}

// Get assignment details
$assignment = $assignmentManager->getById($assignmentId);

if (!$assignment || !$assignment['is_active']) {
    $_SESSION['error_message'] = 'Assignment not found or inactive.';
    safeRedirect('../assignments/index.php');
}

// Check if submission is allowed
$isOverdue = $assignment['is_overdue'];
$canSubmit = true;

if ($isOverdue && !$assignment['allow_late_submission']) {
    $canSubmit = false;
    $_SESSION['error_message'] = 'Late submissions are not allowed for this assignment.';
    safeRedirect("../assignments/view.php?id={$assignmentId}");
}

// Get existing submission
$existingSubmission = $assignmentManager->getStudentSubmission($assignmentId, $user['id']);

if ($existingSubmission && $existingSubmission['attempt_number'] >= $assignment['max_attempts']) {
    $canSubmit = false;
    $_SESSION['error_message'] = 'Maximum attempts exceeded for this assignment.';
    safeRedirect("../assignments/view.php?id={$assignmentId}");
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $canSubmit) {
    $submissionData = [
        'submission_text' => $_POST['submission_text'] ?? ''
    ];
    
    // Handle file upload
    $uploadedFile = null;
    if ($assignment['requires_file_upload'] || !empty($_FILES['submission_file']['name'])) {
        if (!empty($_FILES['submission_file']['name'])) {
            $uploadedFile = $_FILES['submission_file'];
        }
    }
    
    // Validate that at least one form of submission is provided
    if (empty(trim($submissionData['submission_text'])) && !$uploadedFile) {
        $errorMessage = 'Please provide either text submission or upload a file.';
    } else {
        $result = $assignmentManager->submitAssignment($assignmentId, $user['id'], $submissionData, $uploadedFile);
        
        if ($result['success']) {
            $successMessage = 'Assignment submitted successfully!';
            if ($result['is_late']) {
                $successMessage .= ' Note: This submission was marked as late.';
            }
            $_SESSION['success_message'] = $successMessage;
            safeRedirect("../assignments/view.php?id={$assignmentId}");
        } else {
            $errorMessage = implode('<br>', $result['errors']);
            $formData = array_merge($submissionData, $_POST);
        }
    }
} else {
    $formData = [
        'submission_text' => $existingSubmission['submission_text'] ?? ''
    ];
}

// Calculate attempts remaining
$attemptsUsed = $existingSubmission['attempt_number'] ?? 0;
$attemptsRemaining = $assignment['max_attempts'] - $attemptsUsed;
$isResubmission = $attemptsUsed > 0;

$pageTitle = $isResubmission ? 'Resubmit Assignment' : 'Submit Assignment';
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
        .submission-header {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            border-radius: 10px;
            padding: 30px;
            margin-bottom: 30px;
        }
        .form-section {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 25px;
            margin-bottom: 25px;
            border: 1px solid #dee2e6;
        }
        .form-section h5 {
            color: #495057;
            margin-bottom: 20px;
            border-bottom: 2px solid #28a745;
            padding-bottom: 10px;
        }
        .file-upload-area {
            border: 2px dashed #28a745;
            border-radius: 8px;
            padding: 40px 20px;
            text-align: center;
            background: #f8fff9;
            transition: all 0.3s ease;
        }
        .file-upload-area:hover {
            border-color: #20c997;
            background: #f0fff4;
        }
        .file-upload-area.dragover {
            border-color: #20c997;
            background: #e6fffa;
            transform: scale(1.02);
        }
        .upload-icon {
            font-size: 3rem;
            color: #28a745;
            margin-bottom: 15px;
        }
        .submission-status {
            background: #e9ecef;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 25px;
        }
        .warning-box {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
        }
        .success-box {
            background: #d1edff;
            border: 1px solid #b8daff;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
        }
        .deadline-warning {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
            color: #721c24;
        }
        .current-submission {
            background: #e7f3ff;
            border: 1px solid #b3d9ff;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .word-count {
            font-size: 0.875rem;
            color: #6c757d;
            text-align: right;
            margin-top: 5px;
        }
        .remaining-attempts {
            font-weight: bold;
            color: #28a745;
        }
        .no-remaining-attempts {
            font-weight: bold;
            color: #dc3545;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-success">
        <div class="container">
            <a class="navbar-brand" href="../../pages/dashboard.php">
                <i class="fas fa-graduation-cap"></i> <?php echo APP_NAME; ?>
            </a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="view.php?id=<?php echo $assignmentId; ?>">
                    <i class="fas fa-arrow-left"></i> Back to Assignment
                </a>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container mt-4">
        <!-- Submission Header -->
        <div class="submission-header">
            <div class="row">
                <div class="col-md-8">
                    <h1 class="mb-2">
                        <i class="fas fa-<?php echo $isResubmission ? 'redo' : 'upload'; ?>"></i> 
                        <?php echo $pageTitle; ?>
                    </h1>
                    <h4 class="mb-2"><?php echo htmlspecialchars($assignment['title']); ?></h4>
                    <p class="mb-0">
                        <span class="badge bg-light text-dark">
                            <?php echo ucfirst($assignment['assignment_type']); ?>
                        </span>
                        <span class="badge bg-light text-dark ms-2">
                            <?php echo $assignment['total_points']; ?> points
                        </span>
                    </p>
                </div>
                <div class="col-md-4 text-end">
                    <?php if ($isOverdue): ?>
                        <div class="deadline-warning">
                            <i class="fas fa-exclamation-triangle"></i><br>
                            <strong>LATE SUBMISSION</strong><br>
                            <small>This submission will be marked as late</small>
                        </div>
                    <?php else: ?>
                        <div class="time-remaining text-white">
                            <i class="fas fa-clock"></i><br>
                            <?php echo $assignment['time_remaining']['formatted']; ?> remaining
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Success/Error Messages -->
        <?php if (isset($successMessage)): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?php echo $successMessage; ?>
            </div>
        <?php endif; ?>

        <?php if (isset($errorMessage)): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i> <?php echo $errorMessage; ?>
            </div>
        <?php endif; ?>

        <?php if (!$canSubmit): ?>
            <div class="alert alert-warning">
                <i class="fas fa-ban"></i> Submissions are not currently allowed for this assignment.
            </div>
        <?php else: ?>
            <form method="POST" enctype="multipart/form-data" id="submissionForm">
                <!-- Current Submission (if resubmitting) -->
                <?php if ($isResubmission): ?>
                    <div class="current-submission">
                        <h5><i class="fas fa-info-circle"></i> Current Submission</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <strong>Attempt #<?php echo $attemptsUsed; ?></strong><br>
                                <small class="text-muted">
                                    <i class="fas fa-clock"></i> 
                                    Submitted: <?php echo formatDateTime($existingSubmission['submitted_at']); ?>
                                </small>
                            </div>
                            <div class="col-md-6 text-end">
                                <?php if ($existingSubmission['is_late']): ?>
                                    <span class="badge bg-warning text-dark">Late Submission</span>
                                <?php else: ?>
                                    <span class="badge bg-success">On Time</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <?php if (!empty($existingSubmission['submission_text'])): ?>
                            <div class="mt-3">
                                <strong>Previous Text Submission:</strong>
                                <div class="bg-light p-3 rounded mt-2">
                                    <?php echo nl2br(htmlspecialchars(substr($existingSubmission['submission_text'], 0, 300))); ?>
                                    <?php if (strlen($existingSubmission['submission_text']) > 300): ?>
                                        <br><em class="text-muted">(content truncated)</em>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if ($existingSubmission['original_filename']): ?>
                            <div class="mt-3">
                                <strong>Previous File:</strong>
                                <div class="alert alert-info mt-2">
                                    <i class="fas fa-file"></i> 
                                    <?php echo htmlspecialchars($existingSubmission['original_filename']); ?>
                                    <?php if ($existingSubmission['file_size']): ?>
                                        (<?php echo round($existingSubmission['file_size'] / 1024, 1); ?> KB)
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <div class="mt-3">
                            <div class="<?php echo $attemptsRemaining > 0 ? 'remaining-attempts' : 'no-remaining-attempts'; ?>">
                                <i class="fas fa-hashtag"></i> 
                                <?php echo $attemptsRemaining; ?> <?php echo $attemptsRemaining == 1 ? 'attempt' : 'attempts'; ?> remaining
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Text Submission -->
                <?php if (!$assignment['requires_file_upload']): ?>
                    <div class="form-section">
                        <h5><i class="fas fa-edit"></i> Text Submission</h5>
                        
                        <div class="mb-3">
                            <label for="submission_text" class="form-label">
                                Your Answer/Response
                                <?php if (!$assignment['requires_file_upload']): ?>
                                    <span class="text-danger">*</span>
                                <?php endif; ?>
                            </label>
                            <textarea class="form-control" id="submission_text" name="submission_text" rows="12" 
                                      placeholder="Enter your assignment submission here..."><?php echo htmlspecialchars($formData['submission_text'] ?? ''); ?></textarea>
                            <div class="word-count">
                                <span id="wordCount">0</span> words | 
                                <span id="charCount">0</span> characters
                            </div>
                            <div class="form-text">
                                You can enter your complete assignment here. Make sure to address all requirements and instructions.
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- File Upload -->
                <div class="form-section">
                    <h5><i class="fas fa-file-upload"></i> File Upload</h5>
                    
                    <?php if ($assignment['requires_file_upload']): ?>
                        <div class="warning-box">
                            <i class="fas fa-info-circle"></i> 
                            <strong>File upload is required</strong> for this assignment.
                        </div>
                    <?php else: ?>
                        <div class="success-box">
                            <i class="fas fa-check-circle"></i> 
                            File upload is optional. You can submit text only, file only, or both.
                        </div>
                    <?php endif; ?>

                    <!-- Upload Area -->
                    <div class="file-upload-area" id="fileUploadArea">
                        <div class="upload-icon">
                            <i class="fas fa-cloud-upload-alt"></i>
                        </div>
                        <h5>Drag & Drop Your File Here</h5>
                        <p class="text-muted mb-3">or click to browse</p>
                        
                        <input type="file" id="submission_file" name="submission_file" 
                               class="d-none" 
                               accept="<?php echo $assignment['allowed_file_types'] ? '.' . implode(',.', $assignment['allowed_file_types']) : ''; ?>">
                        <button type="button" class="btn btn-success" onclick="document.getElementById('submission_file').click();">
                            <i class="fas fa-folder-open"></i> Choose File
                        </button>
                    </div>

                    <!-- File Information -->
                    <div id="fileInfo" class="mt-3" style="display: none;">
                        <div class="alert alert-info">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <i class="fas fa-file"></i> 
                                    <strong id="fileName"></strong>
                                    <br>
                                    <small class="text-muted">
                                        Size: <span id="fileSize"></span> | 
                                        Type: <span id="fileType"></span>
                                    </small>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-danger" onclick="clearFile()">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- File Requirements -->
                    <?php if ($assignment['requires_file_upload'] || !empty($assignment['allowed_file_types'])): ?>
                        <div class="mt-3">
                            <h6>File Requirements:</h6>
                            <ul class="text-muted">
                                <?php if ($assignment['max_file_size']): ?>
                                    <li>Maximum file size: <?php echo round($assignment['max_file_size'] / 1024 / 1024, 1); ?>MB</li>
                                <?php endif; ?>
                                <?php if (!empty($assignment['allowed_file_types'])): ?>
                                    <li>Allowed file types: 
                                        <?php foreach ($assignment['allowed_file_types'] as $type): ?>
                                            <span class="badge bg-light text-dark"><?php echo strtoupper($type); ?></span>
                                        <?php endforeach; ?>
                                    </li>
                                <?php endif; ?>
                                <?php if (!$assignment['requires_file_upload']): ?>
                                    <li>File upload is optional - you can submit text only if you prefer</li>
                                <?php endif; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Submission Guidelines -->
                <div class="form-section">
                    <h5><i class="fas fa-clipboard-check"></i> Before You Submit</h5>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Checklist:</h6>
                            <ul class="text-muted">
                                <li>I've read all assignment instructions carefully</li>
                                <li>My submission addresses all requirements</li>
                                <li>I've proofread my work for errors</li>
                                <?php if ($assignment['requires_file_upload']): ?>
                                    <li>I've selected the correct file to upload</li>
                                <?php endif; ?>
                                <li>I'm ready to submit (you cannot undo this action)</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h6>Important Notes:</h6>
                            <ul class="text-muted">
                                <?php if ($assignment['allow_late_submission']): ?>
                                    <li>Late submissions are allowed with <?php echo $assignment['late_penalty_per_day']; ?>% penalty per day</li>
                                <?php else: ?>
                                    <li>Late submissions are <strong>not allowed</strong></li>
                                <?php endif; ?>
                                <?php if ($assignment['max_attempts'] > 1): ?>
                                    <li>You have <?php echo $assignment['max_attempts']; ?> attempts for this assignment</li>
                                    <li>Only your latest submission will be graded</li>
                                <?php else: ?>
                                    <li>This is a one-time submission</li>
                                <?php endif; ?>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="row">
                    <div class="col-12">
                        <div class="d-flex justify-content-between">
                            <a href="view.php?id=<?php echo $assignmentId; ?>" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Cancel
                            </a>
                            <div>
                                <button type="button" class="btn btn-info me-2" onclick="saveDraft()">
                                    <i class="fas fa-save"></i> Save Draft
                                </button>
                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-paper-plane"></i> Submit Assignment
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Character and word counting
        const textarea = document.getElementById('submission_text');
        const wordCount = document.getElementById('wordCount');
        const charCount = document.getElementById('charCount');

        function updateCounts() {
            if (textarea) {
                const text = textarea.value.trim();
                const words = text ? text.split(/\s+/).length : 0;
                wordCount.textContent = words;
                charCount.textContent = text.length;
            }
        }

        if (textarea) {
            textarea.addEventListener('input', updateCounts);
            updateCounts(); // Initial count
        }

        // File upload functionality
        const fileUploadArea = document.getElementById('fileUploadArea');
        const fileInput = document.getElementById('submission_file');
        const fileInfo = document.getElementById('fileInfo');
        const fileName = document.getElementById('fileName');
        const fileSize = document.getElementById('fileSize');
        const fileType = document.getElementById('fileType');

        // Drag and drop functionality
        fileUploadArea.addEventListener('dragover', function(e) {
            e.preventDefault();
            this.classList.add('dragover');
        });

        fileUploadArea.addEventListener('dragleave', function(e) {
            e.preventDefault();
            this.classList.remove('dragover');
        });

        fileUploadArea.addEventListener('drop', function(e) {
            e.preventDefault();
            this.classList.remove('dragover');
            
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                fileInput.files = files;
                displayFileInfo(files[0]);
            }
        });

        fileUploadArea.addEventListener('click', function() {
            fileInput.click();
        });

        fileInput.addEventListener('change', function() {
            if (this.files.length > 0) {
                displayFileInfo(this.files[0]);
            }
        });

        function displayFileInfo(file) {
            fileName.textContent = file.name;
            fileSize.textContent = formatFileSize(file.size);
            fileType.textContent = file.type || 'Unknown';
            fileInfo.style.display = 'block';
        }

        function clearFile() {
            fileInput.value = '';
            fileInfo.style.display = 'none';
        }

        function formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }

        // Save draft functionality
        function saveDraft() {
            const formData = new FormData(document.getElementById('submissionForm'));
            
            // Store draft in localStorage
            const draft = {
                submission_text: formData.get('submission_text'),
                timestamp: Date.now()
            };
            
            localStorage.setItem('assignment_<?php echo $assignmentId; ?>_draft', JSON.stringify(draft));
            
            // Show success message
            const alertDiv = document.createElement('div');
            alertDiv.className = 'alert alert-info alert-dismissible fade show';
            alertDiv.innerHTML = `
                <i class="fas fa-save"></i> Draft saved locally. Your work will be restored when you return.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            
            document.querySelector('.container').insertBefore(alertDiv, document.querySelector('.container').firstChild);
        }

        // Load draft on page load
        window.addEventListener('load', function() {
            const draft = localStorage.getItem('assignment_<?php echo $assignmentId; ?>_draft');
            if (draft) {
                const draftData = JSON.parse(draft);
                
                // Show option to restore draft
                const alertDiv = document.createElement('div');
                alertDiv.className = 'alert alert-warning alert-dismissible fade show';
                alertDiv.innerHTML = `
                    <i class="fas fa-exclamation-triangle"></i> 
                    <strong>Draft Found:</strong> You have a saved draft from a previous session.
                    <button type="button" class="btn btn-sm btn-outline-primary ms-2" onclick="restoreDraft()">Restore</button>
                    <button type="button" class="btn btn-sm btn-outline-danger ms-1" onclick="clearSavedDraft()">Discard</button>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                `;
                
                document.querySelector('.container').insertBefore(alertDiv, document.querySelector('.container').firstChild);
            }
        });

        function restoreDraft() {
            const draft = localStorage.getItem('assignment_<?php echo $assignmentId; ?>_draft');
            if (draft) {
                const draftData = JSON.parse(draft);
                document.getElementById('submission_text').value = draftData.submission_text || '';
                updateCounts();
                
                // Remove alert
                document.querySelector('.alert').remove();
            }
        }

        function clearSavedDraft() {
            localStorage.removeItem('assignment_<?php echo $assignmentId; ?>_draft');
            document.querySelector('.alert').remove();
        }

        // Form validation
        document.getElementById('submissionForm').addEventListener('submit', function(e) {
            const hasText = textarea && textarea.value.trim().length > 0;
            const hasFile = fileInput.files.length > 0;
            
            <?php if ($assignment['requires_file_upload']): ?>
                if (!hasFile) {
                    alert('File upload is required for this assignment.');
                    e.preventDefault();
                    return false;
                }
            <?php else: ?>
                if (!hasText && !hasFile) {
                    alert('Please provide either a text submission or upload a file.');
                    e.preventDefault();
                    return false;
                }
            <?php endif; ?>
            
            // File size validation
            <?php if ($assignment['max_file_size']): ?>
                if (hasFile) {
                    const maxSize = <?php echo $assignment['max_file_size']; ?>;
                    if (fileInput.files[0].size > maxSize) {
                        alert('File size exceeds the maximum allowed size of <?php echo round($assignment['max_file_size'] / 1024 / 1024, 1); ?>MB.');
                        e.preventDefault();
                        return false;
                    }
                }
            <?php endif; ?>
            
            // Confirmation dialog
            const isResubmission = <?php echo $isResubmission ? 'true' : 'false'; ?>;
            const message = isResubmission ? 
                'This will replace your previous submission. Are you sure you want to submit?' :
                'Are you sure you want to submit this assignment? You cannot undo this action.';
            
            if (!confirm(message)) {
                e.preventDefault();
                return false;
            }
        });

        // Auto-save draft every 30 seconds
        setInterval(function() {
            if (textarea && textarea.value.trim().length > 0) {
                saveDraft();
            }
        }, 30000);
    </script>
</body>
</html>