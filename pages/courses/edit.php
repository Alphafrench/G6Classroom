<?php
$page_title = "Edit Course";
$breadcrumb = [
    ['title' => 'Dashboard', 'url' => '/pages/dashboard.php'],
    ['title' => 'Courses', 'url' => '/pages/courses/index.php'],
    ['title' => 'Edit Course']
];

require_once __DIR__ . '/../../includes/middleware.php';
require_once __DIR__ . '/../../includes/class.Course.php';

// Initialize course management
$course = new Course();
$current_user = get_current_user();

// Require teacher role
if ($current_user['role'] !== 'teacher') {
    header("HTTP/1.0 403 Forbidden");
    die('Access denied: Teachers only');
}

// Check if course ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: /pages/courses/index.php');
    exit();
}

$course_id = (int)$_GET['id'];

// Verify course exists and belongs to teacher
$course_data = $course->getCourse($course_id);
if (!$course_data) {
    header('HTTP/1.0 404 Not Found');
    die('Course not found');
}

if ($course_data['teacher_id'] != $current_user['id']) {
    header("HTTP/1.0 403 Forbidden");
    die('Access denied: You can only edit your own courses');
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error_message = 'Invalid security token';
    } else {
        // Validate required fields
        $required_fields = ['title', 'course_code', 'semester', 'year', 'start_date', 'end_date'];
        $missing_fields = [];
        
        foreach ($required_fields as $field) {
            if (empty($_POST[$field])) {
                $missing_fields[] = str_replace('_', ' ', ucfirst($field));
            }
        }
        
        if (!empty($missing_fields)) {
            $error_message = 'Please fill in all required fields: ' . implode(', ', $missing_fields);
        } else {
            // Prepare course data
            $update_data = [
                'title' => trim($_POST['title']),
                'description' => trim($_POST['description']),
                'course_code' => trim($_POST['course_code']),
                'semester' => $_POST['semester'],
                'year' => $_POST['year'],
                'credits' => (int)($_POST['credits'] ?: 3),
                'max_students' => (int)($_POST['max_students'] ?: 50),
                'start_date' => $_POST['start_date'],
                'end_date' => $_POST['end_date'],
                'is_active' => isset($_POST['is_active']) ? 1 : 0
            ];
            
            // Update course
            $result = $course->updateCourse($course_id, $update_data);
            
            if ($result['success']) {
                $success_message = $result['message'];
                // Refresh course data
                $course_data = $course->getCourse($course_id);
            } else {
                $error_message = $result['message'];
            }
        }
    }
}

// Generate CSRF token
$csrf_token = generate_csrf_token();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - <?php echo htmlspecialchars($course_data['title']); ?></title>
    <?php include __DIR__ . '/../../includes/header.php'; ?>
    <style>
        .form-section {
            background: white;
            border-radius: 8px;
            padding: 2rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 1.5rem;
        }
        
        .form-section h5 {
            color: #495057;
            border-bottom: 2px solid #e9ecef;
            padding-bottom: 0.5rem;
            margin-bottom: 1.5rem;
        }
        
        .required-field::after {
            content: " *";
            color: #dc3545;
        }
        
        .course-stats {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 8px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }
        
        .stat-item {
            text-align: center;
        }
        
        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            display: block;
        }
        
        .stat-label {
            font-size: 0.875rem;
            opacity: 0.9;
        }
    </style>
</head>
<body>
    <div class="container-fluid main-container">
        <!-- Page Header -->
        <div class="row mb-4">
            <div class="col">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h1 class="h3 mb-0"><?php echo $page_title; ?></h1>
                        <p class="text-muted mb-0">
                            <span class="badge bg-primary me-2"><?php echo htmlspecialchars($course_data['course_code']); ?></span>
                            <?php echo htmlspecialchars($course_data['title']); ?>
                        </p>
                    </div>
                    <div>
                        <a href="/pages/courses/view.php?id=<?php echo $course_id; ?>" class="btn btn-outline-info me-2">
                            <i class="bi bi-eye me-2"></i>View Course
                        </a>
                        <a href="/pages/courses/index.php" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left me-2"></i>Back to Courses
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <?php if (isset($success_message)): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i>
            <?php echo htmlspecialchars($success_message); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <?php if (isset($error_message)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle me-2"></i>
            <?php echo htmlspecialchars($error_message); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <!-- Course Statistics -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="course-stats">
                    <div class="row text-center">
                        <div class="col-md-3 col-6">
                            <div class="stat-item">
                                <span class="stat-number"><?php echo $course_data['enrolled_students']; ?></span>
                                <span class="stat-label">Enrolled Students</span>
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="stat-item">
                                <span class="stat-number"><?php echo $course_data['max_students']; ?></span>
                                <span class="stat-label">Max Capacity</span>
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="stat-item">
                                <span class="stat-number"><?php echo $course_data['credits']; ?></span>
                                <span class="stat-label">Credits</span>
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="stat-item">
                                <span class="stat-number"><?php echo $course_data['semester']; ?></span>
                                <span class="stat-label"><?php echo $course_data['year']; ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Edit Course Form -->
        <div class="row">
            <div class="col-lg-8 mx-auto">
                <form method="POST" id="editCourseForm" novalidate>
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                    
                    <!-- Course Information -->
                    <div class="form-section">
                        <h5><i class="bi bi-info-circle me-2"></i>Course Information</h5>
                        
                        <div class="row">
                            <div class="col-md-8 mb-3">
                                <label for="title" class="form-label required-field">Course Title</label>
                                <input type="text" class="form-control" id="title" name="title" 
                                       value="<?php echo htmlspecialchars($_POST['title'] ?? $course_data['title']); ?>" required>
                                <div class="form-text">Enter a descriptive title for your course</div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="course_code" class="form-label required-field">Course Code</label>
                                <input type="text" class="form-control" id="course_code" name="course_code" 
                                       value="<?php echo htmlspecialchars($_POST['course_code'] ?? $course_data['course_code']); ?>" 
                                       placeholder="CS101" required pattern="[A-Z0-9]+">
                                <div class="form-text">Unique identifier (e.g., CS101, MATH201)</div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="description" class="form-label">Course Description</label>
                            <textarea class="form-control" id="description" name="description" rows="4" 
                                      placeholder="Provide a detailed description of the course content, objectives, and learning outcomes..."><?php echo htmlspecialchars($_POST['description'] ?? $course_data['description']); ?></textarea>
                            <div id="descriptionCounter" class="form-text"></div>
                        </div>
                    </div>

                    <!-- Course Details -->
                    <div class="form-section">
                        <h5><i class="bi bi-calendar me-2"></i>Course Details</h5>
                        
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="semester" class="form-label required-field">Semester</label>
                                <select class="form-select" id="semester" name="semester" required>
                                    <option value="">Select Semester</option>
                                    <option value="Fall" <?php echo ($_POST['semester'] ?? $course_data['semester']) === 'Fall' ? 'selected' : ''; ?>>Fall</option>
                                    <option value="Spring" <?php echo ($_POST['semester'] ?? $course_data['semester']) === 'Spring' ? 'selected' : ''; ?>>Spring</option>
                                    <option value="Summer" <?php echo ($_POST['semester'] ?? $course_data['semester']) === 'Summer' ? 'selected' : ''; ?>>Summer</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="year" class="form-label required-field">Year</label>
                                <select class="form-select" id="year" name="year" required>
                                    <option value="">Select Year</option>
                                    <option value="2024" <?php echo ($_POST['year'] ?? $course_data['year']) === '2024' ? 'selected' : ''; ?>>2024</option>
                                    <option value="2025" <?php echo ($_POST['year'] ?? $course_data['year']) === '2025' ? 'selected' : ''; ?>>2025</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="credits" class="form-label">Credits</label>
                                <input type="number" class="form-control" id="credits" name="credits" 
                                       value="<?php echo htmlspecialchars($_POST['credits'] ?? $course_data['credits']); ?>" 
                                       min="1" max="10">
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="start_date" class="form-label required-field">Start Date</label>
                                <input type="date" class="form-control" id="start_date" name="start_date" 
                                       value="<?php echo htmlspecialchars($_POST['start_date'] ?? $course_data['start_date']); ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="end_date" class="form-label required-field">End Date</label>
                                <input type="date" class="form-control" id="end_date" name="end_date" 
                                       value="<?php echo htmlspecialchars($_POST['end_date'] ?? $course_data['end_date']); ?>" required>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="max_students" class="form-label">Maximum Students</label>
                                <input type="number" class="form-control" id="max_students" name="max_students" 
                                       value="<?php echo htmlspecialchars($_POST['max_students'] ?? $course_data['max_students']); ?>" 
                                       min="1" max="200">
                                <div class="form-text">Maximum number of students that can enroll</div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="form-check form-switch mt-4">
                                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active" 
                                           <?php echo ($_POST['is_active'] ?? $course_data['is_active']) ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="is_active">
                                        Course is Active
                                    </label>
                                    <div class="form-text">Inactive courses are not visible to students</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Warning for active enrollments -->
                    <?php if ($course_data['enrolled_students'] > 0): ?>
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        <strong>Note:</strong> This course currently has <?php echo $course_data['enrolled_students']; ?> enrolled student(s). 
                        Reducing the maximum capacity below the current enrollment will prevent new enrollments but won't affect existing students.
                    </div>
                    <?php endif; ?>

                    <!-- Submit -->
                    <div class="form-section text-center">
                        <div class="d-flex gap-2 justify-content-center">
                            <a href="/pages/courses/view.php?id=<?php echo $course_id; ?>" class="btn btn-outline-info">
                                <i class="bi bi-eye me-2"></i>View Course
                            </a>
                            <a href="/pages/courses/index.php" class="btn btn-outline-secondary">
                                <i class="bi bi-x-circle me-2"></i>Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle me-2"></i>Update Course
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Form validation and enhancement
        document.getElementById('editCourseForm').addEventListener('submit', function(e) {
            const title = document.getElementById('title').value.trim();
            const courseCode = document.getElementById('course_code').value.trim();
            const semester = document.getElementById('semester').value;
            const year = document.getElementById('year').value;
            const startDate = document.getElementById('start_date').value;
            const endDate = document.getElementById('end_date').value;
            const maxStudents = parseInt(document.getElementById('max_students').value);
            const currentEnrolled = <?php echo $course_data['enrolled_students']; ?>;
            
            let errors = [];
            
            if (!title) errors.push('Course title is required');
            if (!courseCode) errors.push('Course code is required');
            if (!semester) errors.push('Semester is required');
            if (!year) errors.push('Year is required');
            if (!startDate) errors.push('Start date is required');
            if (!endDate) errors.push('End date is required');
            
            // Validate course code format
            if (courseCode && !/^[A-Z0-9]+$/.test(courseCode)) {
                errors.push('Course code should contain only letters and numbers');
            }
            
            // Validate dates
            if (startDate && endDate && new Date(startDate) >= new Date(endDate)) {
                errors.push('End date must be after start date');
            }
            
            // Check if reducing capacity below current enrollment
            if (maxStudents < currentEnrolled) {
                if (!confirm(`You are reducing the capacity from ${currentEnrolled} to ${maxStudents} students. This will prevent new enrollments but won't affect existing students. Continue?`)) {
                    e.preventDefault();
                    return;
                }
            }
            
            if (errors.length > 0) {
                e.preventDefault();
                alert('Please fix the following errors:\n\n' + errors.join('\n'));
            }
        });

        // Auto-update end date based on semester and start date
        document.getElementById('semester').addEventListener('change', function() {
            const semester = this.value;
            const startDate = document.getElementById('start_date').value;
            const endDateField = document.getElementById('end_date');
            
            if (semester && startDate) {
                const start = new Date(startDate);
                let end = new Date(start);
                
                // Set typical semester end dates
                if (semester === 'Fall') {
                    end.setMonth(11); // December
                    end.setDate(15);
                } else if (semester === 'Spring') {
                    end.setMonth(4);  // May
                    end.setDate(15);
                } else if (semester === 'Summer') {
                    end.setMonth(7);  // August
                    end.setDate(15);
                }
                
                if (end > start) {
                    endDateField.value = end.toISOString().split('T')[0];
                }
            }
        });

        // Character count for description
        const descriptionField = document.getElementById('description');
        const counter = document.getElementById('descriptionCounter');
        
        function updateDescriptionCounter() {
            const remaining = 1000 - descriptionField.value.length;
            counter.textContent = `${remaining} characters remaining`;
            counter.className = remaining < 0 ? 'form-text text-danger' : 'form-text';
        }
        
        if (descriptionField && counter) {
            descriptionField.addEventListener('input', updateDescriptionCounter);
            updateDescriptionCounter(); // Initialize counter
        }
    </script>
</body>
</html>