<?php
$page_title = "Course Management";
$breadcrumb = [
    ['title' => 'Dashboard', 'url' => '/pages/dashboard.php'],
    ['title' => 'Courses']
];

require_once __DIR__ . '/../../includes/middleware.php';
require_once __DIR__ . '/../../includes/class.Course.php';

// Initialize course management
$course = new Course();
$current_user = get_current_user();

// Handle actions
$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'enroll':
                $course_id = $_POST['course_id'];
                $class_code = $_POST['class_code'] ?? null;
                $result = $course->enrollStudent($course_id, $current_user['id'], $class_code);
                $message = $result['message'];
                $message_type = $result['success'] ? 'success' : 'danger';
                break;
        }
    }
}

// Get courses based on user role
if ($current_user['role'] === 'teacher') {
    $courses = $course->getAllCourses(['teacher_id' => $current_user['id']]);
} else {
    // For students, show all available courses
    $courses = $course->getAllCourses(['is_active' => true]);
    $enrollments = $course->getStudentEnrollments($current_user['id']);
    $enrolled_course_ids = array_column($enrollments, 'course_id');
}

// Get user's enrolled courses (for students)
if ($current_user['role'] === 'student') {
    $enrolled_courses = $course->getAllCourses(['student_id' => $current_user['id']]);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <?php include __DIR__ . '/../../includes/header.php'; ?>
</head>
<body>
    <div class="container-fluid main-container">
        <?php if ($message): ?>
        <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($message); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <!-- Page Header -->
        <div class="row mb-4">
            <div class="col">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h1 class="h3 mb-0"><?php echo $page_title; ?></h1>
                        <p class="text-muted mb-0">
                            <?php if ($current_user['role'] === 'teacher'): ?>
                                Manage your courses and students
                            <?php else: ?>
                                Browse and enroll in available courses
                            <?php endif; ?>
                        </p>
                    </div>
                    <?php if ($current_user['role'] === 'teacher'): ?>
                    <div>
                        <a href="/pages/courses/create.php" class="btn btn-primary">
                            <i class="bi bi-plus-circle me-2"></i>Create New Course
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <?php if ($current_user['role'] === 'teacher'): ?>
            <div class="col-md-3">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h5 class="card-title">Total Courses</h5>
                                <h2 class="mb-0"><?php echo count($courses); ?></h2>
                            </div>
                            <div class="align-self-center">
                                <i class="bi bi-book fs-1 opacity-75"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h5 class="card-title">Active Courses</h5>
                                <h2 class="mb-0"><?php echo count(array_filter($courses, function($c) { return $c['is_active']; })); ?></h2>
                            </div>
                            <div class="align-self-center">
                                <i class="bi bi-check-circle fs-1 opacity-75"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h5 class="card-title">Total Students</h5>
                                <h2 class="mb-0"><?php echo array_sum(array_column($courses, 'enrolled_students')); ?></h2>
                            </div>
                            <div class="align-self-center">
                                <i class="bi bi-people fs-1 opacity-75"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-warning text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h5 class="card-title">This Semester</h5>
                                <h2 class="mb-0"><?php echo date('Y'); ?></h2>
                            </div>
                            <div class="align-self-center">
                                <i class="bi bi-calendar fs-1 opacity-75"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php else: ?>
            <div class="col-md-3">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h5 class="card-title">Enrolled Courses</h5>
                                <h2 class="mb-0"><?php echo count($enrolled_courses ?? []); ?></h2>
                            </div>
                            <div class="align-self-center">
                                <i class="bi bi-bookmark fs-1 opacity-75"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h5 class="card-title">Available Courses</h5>
                                <h2 class="mb-0"><?php echo count($courses); ?></h2>
                            </div>
                            <div class="align-self-center">
                                <i class="bi bi-grid fs-1 opacity-75"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h5 class="card-title">Current Semester</h5>
                                <h2 class="mb-0"><?php echo date('Y'); ?></h2>
                            </div>
                            <div class="align-self-center">
                                <i class="bi bi-calendar fs-1 opacity-75"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-secondary text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h5 class="card-title">Total Credits</h5>
                                <h2 class="mb-0"><?php echo array_sum(array_column($enrolled_courses ?? [], 'credits')); ?></h2>
                            </div>
                            <div class="align-self-center">
                                <i class="bi bi-award fs-1 opacity-75"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Search and Filter -->
        <?php if ($current_user['role'] === 'teacher'): ?>
        <div class="row mb-3">
            <div class="col-md-6">
                <form method="GET" class="d-flex">
                    <input type="text" name="search" class="form-control me-2" 
                           placeholder="Search courses..." value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
                    <button type="submit" class="btn btn-outline-primary">
                        <i class="bi bi-search"></i>
                    </button>
                </form>
            </div>
            <div class="col-md-6">
                <form method="GET" class="d-flex">
                    <select name="semester" class="form-select me-2" onchange="this.form.submit()">
                        <option value="">All Semesters</option>
                        <option value="Fall" <?php echo ($_GET['semester'] ?? '') === 'Fall' ? 'selected' : ''; ?>>Fall</option>
                        <option value="Spring" <?php echo ($_GET['semester'] ?? '') === 'Spring' ? 'selected' : ''; ?>>Spring</option>
                        <option value="Summer" <?php echo ($_GET['semester'] ?? '') === 'Summer' ? 'selected' : ''; ?>>Summer</option>
                    </select>
                    <select name="year" class="form-select me-2" onchange="this.form.submit()">
                        <option value="">All Years</option>
                        <option value="2024" <?php echo ($_GET['year'] ?? '') === '2024' ? 'selected' : ''; ?>>2024</option>
                        <option value="2025" <?php echo ($_GET['year'] ?? '') === '2025' ? 'selected' : ''; ?>>2025</option>
                    </select>
                </form>
            </div>
        </div>
        <?php endif; ?>

        <!-- Courses Grid -->
        <div class="row">
            <?php if (empty($courses)): ?>
            <div class="col-12">
                <div class="text-center py-5">
                    <i class="bi bi-book display-1 text-muted"></i>
                    <h4 class="mt-3">No courses found</h4>
                    <p class="text-muted">
                        <?php if ($current_user['role'] === 'teacher'): ?>
                            Create your first course to get started.
                        <?php else: ?>
                            Check back later for new course offerings.
                        <?php endif; ?>
                    </p>
                    <?php if ($current_user['role'] === 'teacher'): ?>
                    <a href="/pages/courses/create.php" class="btn btn-primary">
                        <i class="bi bi-plus-circle me-2"></i>Create New Course
                    </a>
                    <?php endif; ?>
                </div>
            </div>
            <?php else: ?>
            <?php foreach ($courses as $course_item): ?>
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="card h-100 shadow-sm">
                    <div class="card-header bg-<?php echo $course_item['is_active'] ? 'success' : 'secondary'; ?> text-white">
                        <div class="d-flex justify-content-between align-items-center">
                            <h6 class="card-title mb-0">
                                <i class="bi bi-book me-2"></i><?php echo htmlspecialchars($course_item['course_code']); ?>
                            </h6>
                            <span class="badge bg-light text-<?php echo $course_item['is_active'] ? 'success' : 'secondary'; ?>">
                                <?php echo $course_item['semester']; ?> <?php echo $course_item['year']; ?>
                            </span>
                        </div>
                    </div>
                    <div class="card-body">
                        <h5 class="card-title"><?php echo htmlspecialchars($course_item['title']); ?></h5>
                        <p class="card-text text-muted small">
                            <?php echo htmlspecialchars(substr($course_item['description'] ?? 'No description available', 0, 150)); ?>
                            <?php echo strlen($course_item['description'] ?? '') > 150 ? '...' : ''; ?>
                        </p>
                        
                        <div class="row text-center mb-3">
                            <div class="col-4">
                                <div class="border rounded p-2">
                                    <div class="fw-bold text-primary"><?php echo $course_item['credits']; ?></div>
                                    <small class="text-muted">Credits</small>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="border rounded p-2">
                                    <div class="fw-bold text-info"><?php echo $course_item['enrolled_students']; ?></div>
                                    <small class="text-muted">Enrolled</small>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="border rounded p-2">
                                    <div class="fw-bold text-warning"><?php echo $course_item['max_students']; ?></div>
                                    <small class="text-muted">Capacity</small>
                                </div>
                            </div>
                        </div>
                        
                        <?php if ($current_user['role'] === 'student'): ?>
                            <?php if (in_array($course_item['id'], $enrolled_course_ids ?? [])): ?>
                                <div class="alert alert-success py-2 mb-2">
                                    <i class="bi bi-check-circle me-2"></i>Enrolled
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                    <div class="card-footer bg-transparent">
                        <div class="d-flex gap-2">
                            <a href="/pages/courses/view.php?id=<?php echo $course_item['id']; ?>" 
                               class="btn btn-primary flex-fill">
                                <i class="bi bi-eye me-1"></i>View Course
                            </a>
                            <?php if ($current_user['role'] === 'teacher' && $course_item['teacher_id'] == $current_user['id']): ?>
                            <a href="/pages/courses/edit.php?id=<?php echo $course_item['id']; ?>" 
                               class="btn btn-outline-secondary">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <?php endif; ?>
                            <?php if ($current_user['role'] === 'student' && !in_array($course_item['id'], $enrolled_course_ids ?? [])): ?>
                            <button type="button" class="btn btn-success" data-bs-toggle="modal" 
                                    data-bs-target="#enrollModal<?php echo $course_item['id']; ?>">
                                <i class="bi bi-plus-circle"></i>Enroll
                            </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Enrollment Modal for Students -->
            <?php if ($current_user['role'] === 'student' && !in_array($course_item['id'], $enrolled_course_ids ?? [])): ?>
            <div class="modal fade" id="enrollModal<?php echo $course_item['id']; ?>" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Enroll in <?php echo htmlspecialchars($course_item['title']); ?></h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <form method="POST">
                            <div class="modal-body">
                                <div class="alert alert-info">
                                    <i class="bi bi-info-circle me-2"></i>
                                    Course Code: <strong><?php echo htmlspecialchars($course_item['course_code']); ?></strong>
                                </div>
                                <input type="hidden" name="action" value="enroll">
                                <input type="hidden" name="course_id" value="<?php echo $course_item['id']; ?>">
                                
                                <div class="mb-3">
                                    <label for="class_code<?php echo $course_item['id']; ?>" class="form-label">
                                        Class Code (Optional)
                                    </label>
                                    <input type="text" class="form-control" id="class_code<?php echo $course_item['id']; ?>" 
                                           name="class_code" placeholder="Enter class code if required">
                                    <div class="form-text">Some courses may require a class code for enrollment.</div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-success">
                                    <i class="bi bi-check-circle me-2"></i>Enroll Now
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Auto-submit filter forms
        document.querySelectorAll('select[name="semester"], select[name="year"]').forEach(select => {
            select.addEventListener('change', function() {
                this.form.submit();
            });
        });
    </script>
</body>
</html>