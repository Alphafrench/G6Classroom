<?php
/**
 * Assignment Creation Page
 * 
 * This page allows teachers to create new assignments with proper validation and file upload settings.
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
    safeRedirect('../../pages/login.php', ['error' => 'Please log in to create assignments']);
}

$user = getCurrentUser();
if ($user['role'] === 'employee') {
    $_SESSION['error_message'] = 'You do not have permission to create assignments.';
    safeRedirect('../assignments/index.php');
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $assignmentData = $_POST;
    
    // Process allowed file types
    if (isset($assignmentData['allowed_file_types']) && is_array($assignmentData['allowed_file_types'])) {
        $assignmentData['allowed_file_types'] = array_filter($assignmentData['allowed_file_types']);
    }
    
    // Set checkbox values
    $assignmentData['allow_late_submission'] = isset($assignmentData['allow_late_submission']);
    $assignmentData['requires_file_upload'] = isset($assignmentData['requires_file_upload']);
    $assignmentData['is_active'] = isset($assignmentData['is_active']);
    
    // Validate due date format
    if (!empty($assignmentData['due_date'])) {
        $assignmentData['due_date'] = date('Y-m-d H:i:s', strtotime($assignmentData['due_date']));
    }
    
    $result = $assignmentManager->create($assignmentData, $user['id']);
    
    if ($result['success']) {
        $_SESSION['success_message'] = 'Assignment created successfully!';
        safeRedirect('../assignments/index.php');
    } else {
        $errorMessage = implode('<br>', $result['errors']);
        $formData = $assignmentData; // Keep form data for repopulation
    }
} else {
    // Default form data
    $formData = [
        'title' => '',
        'description' => '',
        'instructions' => '',
        'assignment_type' => 'homework',
        'total_points' => 100,
        'due_date' => date('Y-m-d\TH:i', strtotime('+7 days')), // Default to 1 week from now
        'allow_late_submission' => false,
        'late_penalty_per_day' => 5,
        'max_attempts' => 1,
        'requires_file_upload' => false,
        'max_file_size' => 5242880, // 5MB
        'is_active' => true
    ];
}

$pageTitle = 'Create Assignment';
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
        .form-section {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .section-title {
            border-bottom: 2px solid #007bff;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .preview-section {
            background: #e9ecef;
            border-radius: 8px;
            padding: 15px;
            border: 1px solid #dee2e6;
        }
        .file-type-checkbox {
            margin-right: 10px;
        }
        .datetime-input {
            max-width: 200px;
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
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container mt-4">
        <!-- Header -->
        <div class="row mb-4">
            <div class="col">
                <h2><i class="fas fa-plus-circle"></i> Create New Assignment</h2>
                <p class="text-muted">Create a new assignment for your students</p>
            </div>
        </div>

        <!-- Error Messages -->
        <?php if (isset($errorMessage)): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i> <?php echo $errorMessage; ?>
            </div>
        <?php endif; ?>

        <form method="POST" id="assignmentForm" enctype="multipart/form-data">
            <!-- Basic Information -->
            <div class="form-section">
                <h4 class="section-title"><i class="fas fa-info-circle"></i> Basic Information</h4>
                
                <div class="row">
                    <div class="col-md-8">
                        <div class="mb-3">
                            <label for="title" class="form-label">Assignment Title *</label>
                            <input type="text" class="form-control" id="title" name="title" 
                                   value="<?php echo htmlspecialchars($formData['title']); ?>" 
                                   required maxlength="255" placeholder="Enter assignment title">
                            <div class="form-text">A clear, descriptive title for your assignment</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="assignment_type" class="form-label">Assignment Type *</label>
                            <select class="form-select" id="assignment_type" name="assignment_type" required>
                                <option value="homework" <?php echo $formData['assignment_type'] === 'homework' ? 'selected' : ''; ?>>Homework</option>
                                <option value="project" <?php echo $formData['assignment_type'] === 'project' ? 'selected' : ''; ?>>Project</option>
                                <option value="exam" <?php echo $formData['assignment_type'] === 'exam' ? 'selected' : ''; ?>>Exam</option>
                                <option value="quiz" <?php echo $formData['assignment_type'] === 'quiz' ? 'selected' : ''; ?>>Quiz</option>
                                <option value="essay" <?php echo $formData['assignment_type'] === 'essay' ? 'selected' : ''; ?>>Essay</option>
                                <option value="presentation" <?php echo $formData['assignment_type'] === 'presentation' ? 'selected' : ''; ?>>Presentation</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="description" class="form-label">Description *</label>
                    <textarea class="form-control" id="description" name="description" rows="4" required 
                              maxlength="2000" placeholder="Brief description of the assignment"><?php echo htmlspecialchars($formData['description']); ?></textarea>
                    <div class="form-text">A brief overview of what students need to do (max 2000 characters)</div>
                </div>

                <div class="mb-3">
                    <label for="instructions" class="form-label">Detailed Instructions</label>
                    <textarea class="form-control" id="instructions" name="instructions" rows="6" 
                              maxlength="5000" placeholder="Detailed instructions, requirements, and submission guidelines"><?php echo htmlspecialchars($formData['instructions']); ?></textarea>
                    <div class="form-text">Detailed instructions, rubric, or any specific requirements (max 5000 characters)</div>
                </div>
            </div>

            <!-- Grading Settings -->
            <div class="form-section">
                <h4 class="section-title"><i class="fas fa-star"></i> Grading Settings</h4>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="total_points" class="form-label">Total Points *</label>
                            <div class="input-group">
                                <input type="number" class="form-control" id="total_points" name="total_points" 
                                       value="<?php echo $formData['total_points']; ?>" 
                                       required min="0" max="999.99" step="0.01">
                                <span class="input-group-text">points</span>
                            </div>
                            <div class="form-text">Maximum score students can achieve</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="max_attempts" class="form-label">Maximum Attempts</label>
                            <select class="form-select" id="max_attempts" name="max_attempts">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <option value="<?php echo $i; ?>" <?php echo $formData['max_attempts'] == $i ? 'selected' : ''; ?>>
                                        <?php echo $i; ?> <?php echo $i == 1 ? 'attempt' : 'attempts'; ?>
                                    </option>
                                <?php endfor; ?>
                            </select>
                            <div class="form-text">Number of times students can resubmit</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Deadline Settings -->
            <div class="form-section">
                <h4 class="section-title"><i class="fas fa-calendar-alt"></i> Deadline Settings</h4>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="due_date" class="form-label">Due Date & Time *</label>
                            <input type="datetime-local" class="form-control datetime-input" 
                                   id="due_date" name="due_date" 
                                   value="<?php echo date('Y-m-d\TH:i', strtotime($formData['due_date'])); ?>" 
                                   required>
                            <div class="form-text">When the assignment is due</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <div class="form-check mt-4">
                                <input class="form-check-input" type="checkbox" id="allow_late_submission" 
                                       name="allow_late_submission" <?php echo $formData['allow_late_submission'] ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="allow_late_submission">
                                    Allow Late Submissions
                                </label>
                            </div>
                            
                            <div id="latePenaltySection" style="<?php echo !$formData['allow_late_submission'] ? 'display:none;' : ''; ?>">
                                <label for="late_penalty_per_day" class="form-label mt-2">Late Penalty (per day)</label>
                                <div class="input-group">
                                    <input type="number" class="form-control" id="late_penalty_per_day" 
                                           name="late_penalty_per_day" 
                                           value="<?php echo $formData['late_penalty_per_day']; ?>" 
                                           min="0" max="50" step="0.5">
                                    <span class="input-group-text">%</span>
                                </div>
                                <div class="form-text">Percentage penalty for late submissions</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- File Upload Settings -->
            <div class="form-section">
                <h4 class="section-title"><i class="fas fa-file-upload"></i> File Upload Settings</h4>
                
                <div class="mb-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="requires_file_upload" 
                               name="requires_file_upload" <?php echo $formData['requires_file_upload'] ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="requires_file_upload">
                            Require File Upload
                        </label>
                        <div class="form-text">Students must upload a file to complete this assignment</div>
                    </div>
                </div>

                <div id="fileSettings" style="<?php echo !$formData['requires_file_upload'] ? 'display:none;' : ''; ?>">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="max_file_size" class="form-label">Maximum File Size</label>
                                <select class="form-select" id="max_file_size" name="max_file_size">
                                    <option value="1048576" <?php echo $formData['max_file_size'] == 1048576 ? 'selected' : ''; ?>>1 MB</option>
                                    <option value="5242880" <?php echo $formData['max_file_size'] == 5242880 ? 'selected' : ''; ?>>5 MB</option>
                                    <option value="10485760" <?php echo $formData['max_file_size'] == 10485760 ? 'selected' : ''; ?>>10 MB</option>
                                    <option value="20971520" <?php echo $formData['max_file_size'] == 20971520 ? 'selected' : ''; ?>>20 MB</option>
                                    <option value="52428800" <?php echo $formData['max_file_size'] == 52428800 ? 'selected' : ''; ?>>50 MB</option>
                                </select>
                                <div class="form-text">Maximum file size students can upload</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Allowed File Types</label>
                                <div class="row">
                                    <div class="col-6">
                                        <div class="file-type-checkbox">
                                            <input class="form-check-input" type="checkbox" id="file_pdf" 
                                                   name="allowed_file_types[]" value="pdf" checked disabled>
                                            <label class="form-check-label" for="file_pdf">PDF</label>
                                        </div>
                                        <div class="file-type-checkbox">
                                            <input class="form-check-input" type="checkbox" id="file_doc" 
                                                   name="allowed_file_types[]" value="doc" checked>
                                            <label class="form-check-label" for="file_doc">Word (.doc)</label>
                                        </div>
                                        <div class="file-type-checkbox">
                                            <input class="form-check-input" type="checkbox" id="file_docx" 
                                                   name="allowed_file_types[]" value="docx" checked>
                                            <label class="form-check-label" for="file_docx">Word (.docx)</label>
                                        </div>
                                        <div class="file-type-checkbox">
                                            <input class="form-check-input" type="checkbox" id="file_txt" 
                                                   name="allowed_file_types[]" value="txt" checked>
                                            <label class="form-check-label" for="file_txt">Text (.txt)</label>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="file-type-checkbox">
                                            <input class="form-check-input" type="checkbox" id="file_zip" 
                                                   name="allowed_file_types[]" value="zip">
                                            <label class="form-check-label" for="file_zip">Archive (.zip)</label>
                                        </div>
                                        <div class="file-type-checkbox">
                                            <input class="form-check-input" type="checkbox" id="file_html" 
                                                   name="allowed_file_types[]" value="html">
                                            <label class="form-check-label" for="file_html">HTML (.html)</label>
                                        </div>
                                        <div class="file-type-checkbox">
                                            <input class="form-check-input" type="checkbox" id="file_css" 
                                                   name="allowed_file_types[]" value="css">
                                            <label class="form-check-label" for="file_css">CSS (.css)</label>
                                        </div>
                                        <div class="file-type-checkbox">
                                            <input class="form-check-input" type="checkbox" id="file_js" 
                                                   name="allowed_file_types[]" value="js">
                                            <label class="form-check-label" for="file_js">JavaScript (.js)</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-text">Select which file types students can upload</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Visibility Settings -->
            <div class="form-section">
                <h4 class="section-title"><i class="fas fa-eye"></i> Visibility Settings</h4>
                
                <div class="mb-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="is_active" 
                               name="is_active" <?php echo $formData['is_active'] ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="is_active">
                            Active (visible to students)
                        </label>
                        <div class="form-text">Uncheck to hide this assignment from students</div>
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="row">
                <div class="col-12">
                    <div class="d-flex justify-content-between">
                        <a href="index.php" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Cancel
                        </a>
                        <div>
                            <button type="button" class="btn btn-info me-2" id="previewBtn">
                                <i class="fas fa-eye"></i> Preview
                            </button>
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-save"></i> Create Assignment
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- Preview Modal -->
    <div class="modal fade" id="previewModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-eye"></i> Assignment Preview</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="previewContent">
                        <!-- Preview content will be populated by JavaScript -->
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Toggle late penalty section
        document.getElementById('allow_late_submission').addEventListener('change', function() {
            const penaltySection = document.getElementById('latePenaltySection');
            if (this.checked) {
                penaltySection.style.display = 'block';
            } else {
                penaltySection.style.display = 'none';
            }
        });

        // Toggle file settings
        document.getElementById('requires_file_upload').addEventListener('change', function() {
            const fileSettings = document.getElementById('fileSettings');
            if (this.checked) {
                fileSettings.style.display = 'block';
            } else {
                fileSettings.style.display = 'none';
            }
        });

        // Preview functionality
        document.getElementById('previewBtn').addEventListener('click', function() {
            const form = document.getElementById('assignmentForm');
            const formData = new FormData(form);
            
            let previewHtml = `
                <div class="preview-section">
                    <h5 id="preview_title">${formData.get('title') || 'Untitled Assignment'}</h5>
                    <span class="badge bg-primary mb-2">${formData.get('assignment_type') || 'homework'}</span>
                    
                    <p class="text-muted"><strong>Due Date:</strong> ${new Date(formData.get('due_date')).toLocaleString()}</p>
                    <p class="text-muted"><strong>Total Points:</strong> ${formData.get('total_points') || 100}</p>
                    
                    <h6>Description:</h6>
                    <p>${formData.get('description') || 'No description provided.'}</p>
                    
                    ${formData.get('instructions') ? `
                        <h6>Instructions:</h6>
                        <div class="bg-light p-3 rounded">${formData.get('instructions').replace(/\n/g, '<br>')}</div>
                    ` : ''}
                    
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <small class="text-muted">
                                <strong>Late Submissions:</strong> ${formData.get('allow_late_submission') ? 'Allowed' : 'Not Allowed'}
                                ${formData.get('allow_late_submission') ? `<br><strong>Late Penalty:</strong> ${formData.get('late_penalty_per_day') || 5}% per day` : ''}
                            </small>
                        </div>
                        <div class="col-md-6">
                            <small class="text-muted">
                                <strong>Max Attempts:</strong> ${formData.get('max_attempts') || 1}<br>
                                <strong>File Upload:</strong> ${formData.get('requires_file_upload') ? 'Required' : 'Optional'}
                                ${formData.get('requires_file_upload') ? `<br><strong>Max Size:</strong> ${Math.round(formData.get('max_file_size') / 1024 / 1024)}MB` : ''}
                            </small>
                        </div>
                    </div>
                </div>
            `;
            
            document.getElementById('previewContent').innerHTML = previewHtml;
            new bootstrap.Modal(document.getElementById('previewModal')).show();
        });

        // Form validation
        document.getElementById('assignmentForm').addEventListener('submit', function(e) {
            const title = document.getElementById('title').value.trim();
            const description = document.getElementById('description').value.trim();
            const dueDate = document.getElementById('due_date').value;
            
            if (!title) {
                alert('Please enter an assignment title.');
                e.preventDefault();
                return false;
            }
            
            if (!description) {
                alert('Please enter a description.');
                e.preventDefault();
                return false;
            }
            
            if (!dueDate) {
                alert('Please set a due date and time.');
                e.preventDefault();
                return false;
            }
            
            // Check if due date is in the future
            if (new Date(dueDate) <= new Date()) {
                alert('Due date must be in the future.');
                e.preventDefault();
                return false;
            }
        });
    </script>
</body>
</html>