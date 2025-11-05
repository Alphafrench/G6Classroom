<?php
$page_title = "Course Enrollment";
$breadcrumb = [
    ['title' => 'Dashboard', 'url' => '/pages/dashboard.php'],
    ['title' => 'Courses', 'url' => '/pages/courses/index.php'],
    ['title' => 'Enroll in Course']
];

require_once __DIR__ . '/../../includes/middleware.php';
require_once __DIR__ . '/../../includes/class.Course.php';

// Initialize course management
$course = new Course();
$current_user = get_current_user();

// Only students can enroll
if ($current_user['role'] !== 'student') {
    header("HTTP/1.0 403 Forbidden");
    die('Access denied: Students only');
}

// Handle enrollment request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error_message = 'Invalid security token';
    } else {
        // Validate required fields
        if (empty($_POST['course_id'])) {
            $error_message = 'Course ID is required';
        } else {
            $course_id = (int)$_POST['course_id'];
            $class_code = trim($_POST['class_code'] ?? '');
            
            // Verify course exists and is active
            $course_data = $course->getCourse($course_id);
            if (!$course_data || !$course_data['is_active']) {
                $error_message = 'Course not found or not available for enrollment';
            } else {
                // Attempt enrollment
                $result = $course->enrollStudent($course_id, $current_user['id'], $class_code);
                
                if ($result['success']) {
                    $success_message = $result['message'];
                    // Redirect to course view after 2 seconds
                    echo "<script>setTimeout(function() { window.location.href = '/pages/courses/view.php?id=" . $course_id . "'; }, 2000);</script>";
                } else {
                    $error_message = $result['message'];
                }
            }
        }
    }
}

// Generate CSRF token
$csrf_token = generate_csrf_token();

// If course_id is provided in URL, pre-fill the form
$pre_course_id = isset($_GET['course_id']) ? (int)$_GET['course_id'] : '';
$course_data = $pre_course_id ? $course->getCourse($pre_course_id) : null;

// Get user's current enrollments
$enrollments = $course->getStudentEnrollments($current_user['id']);
$enrolled_course_ids = array_column($enrollments, 'course_id');

// Get available courses (not already enrolled)
$available_courses = $course->getAllCourses(['is_active' => true]);
$available_courses = array_filter($available_courses, function($course) use ($enrolled_course_ids) {
    return !in_array($course['id'], $enrolled_course_ids);
});
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <?php include __DIR__ . '/../../includes/header.php'; ?>
    <style>
        .enrollment-card {
            border: 2px solid #e9ecef;
            border-radius: 12px;
            padding: 2rem;
            background: white;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        
        .course-selection {
            max-height: 400px;
            overflow-y: auto;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 1rem;
        }
        
        .course-option {
            padding: 1rem;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            margin-bottom: 0.75rem;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .course-option:hover {
            background-color: #f8f9fa;
            border-color: #667eea;
        }
        
        .course-option.selected {
            background-color: #667eea;
            border-color: #667eea;
            color: white;
        }
        
        .course-option.selected .text-muted {
            color: rgba(255,255,255,0.8) !important;
        }
        
        .enrollment-info {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 8px;
            padding: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .search-box {
            position: relative;
            margin-bottom: 1rem;
        }
        
        .search-box .form-control {
            padding-left: 2.5rem;
        }
        
        .search-box .bi-search {
            position: absolute;
            left: 0.75rem;
            top: 50%;
            transform: translateY(-50%);
            color: #6c757d;
        }
        
        .enrolled-badge {
            position: absolute;
            top: 1rem;
            right: 1rem;
            background: rgba(255,255,255,0.2);
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 12px;
            font-size: 0.75rem;
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
                        <p class="text-muted mb-0">Enroll in available courses using class codes</p>
                    </div>
                    <div>
                        <a href="/pages/courses/index.php" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left me-2"></i>Browse All Courses
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

        <!-- Enrollment Information -->
        <div class="enrollment-info">
            <div class="row">
                <div class="col-md-8">
                    <h4 class="mb-2">How to Enroll in Courses</h4>
                    <p class="mb-0 opacity-75">
                        Browse available courses below or enroll directly using a class code. 
                        Some courses may require a class code provided by your instructor.
                    </p>
                </div>
                <div class="col-md-4 text-end">
                    <div class="h4 mb-0"><?php echo count($enrollments); ?></div>
                    <div class="opacity-75">Currently Enrolled</div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Course Selection -->
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-grid me-2"></i>Available Courses
                        </h5>
                    </div>
                    <div class="card-body">
                        <!-- Search -->
                        <div class="search-box">
                            <i class="bi bi-search"></i>
                            <input type="text" class="form-control" id="courseSearch" placeholder="Search courses...">
                        </div>
                        
                        <!-- Course List -->
                        <div class="course-selection" id="courseList">
                            <?php if (empty($available_courses)): ?>
                            <div class="text-center py-4">
                                <i class="bi bi-book display-4 text-muted"></i>
                                <h5 class="mt-3 text-muted">No available courses</h5>
                                <p class="text-muted">All courses are either full or you are already enrolled.</p>
                            </div>
                            <?php else: ?>
                                <?php foreach ($available_courses as $course_item): ?>
                                <div class="course-option" data-course-id="<?php echo $course_item['id']; ?>" 
                                     data-course-code="<?php echo htmlspecialchars($course_item['course_code']); ?>">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div class="flex-grow-1">
                                            <h6 class="mb-1">
                                                <i class="bi bi-book me-2"></i>
                                                <?php echo htmlspecialchars($course_item['title']); ?>
                                            </h6>
                                            <p class="text-muted mb-2">
                                                <?php echo htmlspecialchars($course_item['description']); ?>
                                            </p>
                                            <div class="d-flex gap-3 text-sm">
                                                <span>
                                                    <i class="bi bi-tag me-1"></i>
                                                    <?php echo htmlspecialchars($course_item['course_code']); ?>
                                                </span>
                                                <span>
                                                    <i class="bi bi-person me-1"></i>
                                                    <?php echo htmlspecialchars($course_item['teacher_name']); ?>
                                                </span>
                                                <span>
                                                    <i class="bi bi-calendar me-1"></i>
                                                    <?php echo htmlspecialchars($course_item['semester'] . ' ' . $course_item['year']); ?>
                                                </span>
                                                <span>
                                                    <i class="bi bi-award me-1"></i>
                                                    <?php echo $course_item['credits']; ?> credits
                                                </span>
                                            </div>
                                        </div>
                                        <div class="text-end">
                                            <small class="text-muted">
                                                <?php echo $course_item['enrolled_students']; ?>/<?php echo $course_item['max_students']; ?> enrolled
                                            </small>
                                            <?php if ($course_item['enrolled_students'] >= $course_item['max_students']): ?>
                                            <div class="badge bg-warning mt-1">Full</div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Enrollment Form -->
            <div class="col-lg-4">
                <div class="enrollment-card">
                    <h5 class="mb-4">
                        <i class="bi bi-person-plus me-2"></i>Enroll Now
                    </h5>
                    
                    <form method="POST" id="enrollmentForm" novalidate>
                        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                        <input type="hidden" name="course_id" id="selectedCourseId" value="<?php echo $pre_course_id; ?>">
                        
                        <div class="mb-3">
                            <label for="selectedCourse" class="form-label fw-bold">Selected Course</label>
                            <div id="selectedCourse" class="p-3 bg-light rounded">
                                <?php if ($course_data): ?>
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h6 class="mb-1"><?php echo htmlspecialchars($course_data['title']); ?></h6>
                                        <small class="text-muted">
                                            <i class="bi bi-tag me-1"></i><?php echo htmlspecialchars($course_data['course_code']); ?>
                                        </small>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="clearSelection()">
                                        <i class="bi bi-x"></i>
                                    </button>
                                </div>
                                <?php else: ?>
                                <p class="text-muted mb-0">Select a course from the list to enroll</p>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="class_code" class="form-label">Class Code (Optional)</label>
                            <input type="text" class="form-control" id="class_code" name="class_code" 
                                   value="<?php echo htmlspecialchars($_POST['class_code'] ?? ''); ?>" 
                                   placeholder="Enter class code if required">
                            <div class="form-text">
                                Some courses require a class code for enrollment. 
                                Check with your instructor if you're unsure.
                            </div>
                        </div>
                        
                        <!-- Class Code Verification -->
                        <div id="classCodeInfo" class="alert alert-info" style="display: none;">
                            <i class="bi bi-info-circle me-2"></i>
                            <span id="classCodeMessage"></span>
                        </div>
                        
                        <div class="d-grid">
                            <button type="submit" class="btn btn-success btn-lg" id="enrollBtn" 
                                    <?php echo (!$course_data || $course_data['enrolled_students'] >= $course_data['max_students']) ? 'disabled' : ''; ?>>
                                <i class="bi bi-check-circle me-2"></i>
                                <span id="enrollBtnText">Enroll in Course</span>
                            </button>
                        </div>
                        
                        <?php if ($course_data && $course_data['enrolled_students'] >= $course_data['max_students']): ?>
                        <div class="alert alert-warning mt-3">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            This course is currently full. You cannot enroll at this time.
                        </div>
                        <?php elseif (!$course_data): ?>
                        <div class="alert alert-secondary mt-3">
                            <i class="bi bi-info-circle me-2"></i>
                            Please select a course to continue with enrollment.
                        </div>
                        <?php endif; ?>
                    </form>
                    
                    <!-- Current Enrollments -->
                    <hr class="my-4">
                    <h6 class="mb-3">
                        <i class="bi bi-bookmark me-2"></i>My Enrolled Courses
                    </h6>
                    
                    <?php if (empty($enrollments)): ?>
                    <p class="text-muted">You are not currently enrolled in any courses.</p>
                    <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($enrollments as $enrollment): ?>
                        <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <div>
                                <h6 class="mb-1"><?php echo htmlspecialchars($enrollment['title']); ?></h6>
                                <small class="text-muted">
                                    <?php echo htmlspecialchars($enrollment['course_code'] . ' • ' . $enrollment['teacher_name']); ?>
                                </small>
                            </div>
                            <a href="/pages/courses/view.php?id=<?php echo $enrollment['course_id']; ?>" 
                               class="btn btn-sm btn-outline-primary">
                                View
                            </a>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script>
        let selectedCourse = null;
        const courseOptions = document.querySelectorAll('.course-option');
        const searchInput = document.getElementById('courseSearch');
        
        // Course selection functionality
        courseOptions.forEach(option => {
            option.addEventListener('click', function() {
                // Remove previous selection
                courseOptions.forEach(opt => opt.classList.remove('selected'));
                
                // Add selection to clicked option
                this.classList.add('selected');
                
                // Update form
                const courseId = this.dataset.courseId;
                const courseCode = this.dataset.courseCode;
                
                selectedCourse = {
                    id: courseId,
                    code: courseCode,
                    title: this.querySelector('h6').textContent,
                    teacher: this.querySelector('.text-muted').textContent.replace(/.*•\s*/, '').trim()
                };
                
                document.getElementById('selectedCourseId').value = courseId;
                updateSelectedCourseDisplay();
                updateEnrollmentButton();
            });
        });
        
        // Search functionality
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            courseOptions.forEach(option => {
                const title = option.querySelector('h6').textContent.toLowerCase();
                const description = option.querySelector('.text-muted').textContent.toLowerCase();
                const courseCode = option.dataset.courseCode.toLowerCase();
                
                if (title.includes(searchTerm) || description.includes(searchTerm) || courseCode.includes(searchTerm)) {
                    option.style.display = 'block';
                } else {
                    option.style.display = 'none';
                }
            });
        });
        
        // Update selected course display
        function updateSelectedCourseDisplay() {
            const display = document.getElementById('selectedCourse');
            
            if (selectedCourse) {
                display.innerHTML = `
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="mb-1">${selectedCourse.title}</h6>
                            <small class="text-muted">
                                <i class="bi bi-tag me-1"></i>${selectedCourse.code}
                                <span class="mx-2">•</span>
                                <i class="bi bi-person me-1"></i>${selectedCourse.teacher}
                            </small>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="clearSelection()">
                            <i class="bi bi-x"></i>
                        </button>
                    </div>
                `;
            } else {
                display.innerHTML = '<p class="text-muted mb-0">Select a course from the list to enroll</p>';
            }
        }
        
        // Clear course selection
        function clearSelection() {
            selectedCourse = null;
            courseOptions.forEach(opt => opt.classList.remove('selected'));
            document.getElementById('selectedCourseId').value = '';
            document.getElementById('selectedCourse').innerHTML = '<p class="text-muted mb-0">Select a course from the list to enroll</p>';
            updateEnrollmentButton();
            hideClassCodeInfo();
        }
        
        // Update enrollment button
        function updateEnrollmentButton() {
            const btn = document.getElementById('enrollBtn');
            const btnText = document.getElementById('enrollBtnText');
            
            if (selectedCourse) {
                btn.disabled = false;
                btnText.textContent = 'Enroll in ' + selectedCourse.code;
            } else {
                btn.disabled = true;
                btnText.textContent = 'Enroll in Course';
            }
        }
        
        // Class code verification
        const classCodeInput = document.getElementById('class_code');
        const classCodeInfo = document.getElementById('classCodeInfo');
        const classCodeMessage = document.getElementById('classCodeMessage');
        
        classCodeInput.addEventListener('input', function() {
            const enteredCode = this.value.trim().toUpperCase();
            
            if (enteredCode && selectedCourse) {
                if (enteredCode === selectedCourse.code) {
                    showClassCodeInfo('success', 'Class code matches this course!');
                } else {
                    showClassCodeInfo('warning', 'Class code does not match the selected course.');
                }
            } else {
                hideClassCodeInfo();
            }
        });
        
        function showClassCodeInfo(type, message) {
            classCodeInfo.className = 'alert alert-' + (type === 'success' ? 'success' : 'warning');
            classCodeMessage.textContent = message;
            classCodeInfo.style.display = 'block';
        }
        
        function hideClassCodeInfo() {
            classCodeInfo.style.display = 'none';
        }
        
        // Form validation
        document.getElementById('enrollmentForm').addEventListener('submit', function(e) {
            if (!selectedCourse) {
                e.preventDefault();
                alert('Please select a course to enroll in.');
                return;
            }
            
            const classCode = classCodeInput.value.trim();
            const enteredCode = classCode.toUpperCase();
            
            if (selectedCourse && enteredCode && enteredCode !== selectedCourse.code) {
                if (!confirm('The class code does not match the selected course. Do you want to continue?')) {
                    e.preventDefault();
                    return;
                }
            }
        });
        
        // Initialize
        updateEnrollmentButton();
        
        // Auto-select course if URL parameter provided
        <?php if ($pre_course_id): ?>
        const preCourseId = '<?php echo $pre_course_id; ?>';
        const preCourseOption = document.querySelector(`[data-course-id="${preCourseId}"]`);
        if (preCourseOption) {
            preCourseOption.click();
        }
        <?php endif; ?>
    </script>
</body>
</html>