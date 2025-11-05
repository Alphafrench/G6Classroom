<?php
$page_title = "Course Details";
$breadcrumb = [
    ['title' => 'Dashboard', 'url' => '/pages/dashboard.php'],
    ['title' => 'Courses', 'url' => '/pages/courses/index.php'],
    ['title' => 'Course Details']
];

require_once __DIR__ . '/../../includes/middleware.php';
require_once __DIR__ . '/../../includes/class.Course.php';

// Initialize course management
$course = new Course();
$current_user = get_current_user();

// Check if course ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: /pages/courses/index.php');
    exit();
}

$course_id = (int)$_GET['id'];

// Verify course exists
$course_data = $course->getCourse($course_id);
if (!$course_data) {
    header('HTTP/1.0 404 Not Found');
    die('Course not found');
}

// Check user access
$is_teacher = $course->isCourseTeacher($course_id, $current_user['id']);
$is_enrolled = $current_user['role'] === 'student' ? $course->verifyEnrollment($course_id, $current_user['id']) : false;
$has_access = $is_teacher || $is_enrolled || $current_user['role'] === 'admin';

if (!$has_access) {
    header("HTTP/1.0 403 Forbidden");
    die('Access denied: You do not have permission to view this course');
}

// Handle actions
$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'create_announcement':
                if ($is_teacher) {
                    $announcement_data = [
                        'course_id' => $course_id,
                        'title' => trim($_POST['title']),
                        'content' => trim($_POST['content']),
                        'posted_by' => $current_user['id'],
                        'priority' => $_POST['priority'],
                        'is_published' => isset($_POST['is_published']),
                        'publish_date' => $_POST['publish_date'] ?: null
                    ];
                    $result = $course->createAnnouncement($announcement_data);
                    $message = $result['message'];
                    $message_type = $result['success'] ? 'success' : 'danger';
                }
                break;
                
            case 'create_discussion':
                if ($is_teacher || $is_enrolled) {
                    $discussion_data = [
                        'course_id' => $course_id,
                        'title' => trim($_POST['title']),
                        'description' => trim($_POST['description']),
                        'created_by' => $current_user['id']
                    ];
                    $result = $course->createDiscussion($discussion_data);
                    $message = $result['message'];
                    $message_type = $result['success'] ? 'success' : 'danger';
                }
                break;
        }
    }
}

// Get course data
$announcements = $course->getCourseAnnouncements($course_id, !$is_teacher);
$discussions = $course->getCourseDiscussions($course_id);
$materials = $course->getCourseMaterials($course_id);

// Update page title with course name
$page_title = $course_data['title'] . ' - Course Details';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?></title>
    <?php include __DIR__ . '/../../includes/header.php'; ?>
    <style>
        .course-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 8px;
            padding: 2rem;
            margin-bottom: 2rem;
        }
        
        .course-code {
            font-size: 1.2rem;
            font-weight: bold;
            background: rgba(255,255,255,0.2);
            padding: 0.25rem 0.75rem;
            border-radius: 4px;
            display: inline-block;
            margin-bottom: 0.5rem;
        }
        
        .nav-tabs .nav-link {
            color: #6c757d;
            border: none;
            padding: 1rem 1.5rem;
        }
        
        .nav-tabs .nav-link.active {
            color: #495057;
            background-color: #fff;
            border-bottom: 3px solid #667eea;
        }
        
        .tab-content {
            background: white;
            border-radius: 8px;
            padding: 2rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .announcement-card {
            border-left: 4px solid #dc3545;
            margin-bottom: 1rem;
            transition: transform 0.2s;
        }
        
        .announcement-card:hover {
            transform: translateY(-2px);
        }
        
        .announcement-card.high {
            border-left-color: #dc3545;
        }
        
        .announcement-card.normal {
            border-left-color: #0d6efd;
        }
        
        .announcement-card.low {
            border-left-color: #198754;
        }
        
        .discussion-card {
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .discussion-card:hover {
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            transform: translateY(-1px);
        }
        
        .material-item {
            display: flex;
            align-items: center;
            padding: 1rem;
            border: 1px solid #dee2e6;
            border-radius: 6px;
            margin-bottom: 0.5rem;
            transition: background-color 0.2s;
        }
        
        .material-item:hover {
            background-color: #f8f9fa;
        }
        
        .stat-card {
            text-align: center;
            padding: 1.5rem;
            border-radius: 8px;
            background: white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 1rem;
        }
        
        .stat-number {
            font-size: 2.5rem;
            font-weight: bold;
            color: #667eea;
            display: block;
        }
    </style>
</head>
<body>
    <div class="container-fluid main-container">
        <?php if ($message): ?>
        <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($message); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <!-- Course Header -->
        <div class="course-header">
            <div class="row">
                <div class="col-md-8">
                    <div class="course-code"><?php echo htmlspecialchars($course_data['course_code']); ?></div>
                    <h1 class="h2 mb-2"><?php echo htmlspecialchars($course_data['title']); ?></h1>
                    <p class="mb-2 opacity-75">
                        <i class="bi bi-person me-2"></i>
                        <?php echo htmlspecialchars($course_data['teacher_name']); ?>
                    </p>
                    <p class="mb-0 opacity-75">
                        <i class="bi bi-calendar me-2"></i>
                        <?php echo htmlspecialchars($course_data['semester'] . ' ' . $course_data['year']); ?>
                        <span class="ms-3">
                            <i class="bi bi-award me-2"></i>
                            <?php echo $course_data['credits']; ?> Credits
                        </span>
                    </p>
                </div>
                <div class="col-md-4 text-end">
                    <?php if ($is_teacher): ?>
                    <a href="/pages/courses/edit.php?id=<?php echo $course_id; ?>" class="btn btn-light">
                        <i class="bi bi-pencil me-2"></i>Edit Course
                    </a>
                    <?php endif; ?>
                    
                    <?php if ($current_user['role'] === 'student' && !$is_enrolled): ?>
                    <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#enrollModal">
                        <i class="bi bi-plus-circle me-2"></i>Enroll
                    </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Course Statistics -->
        <div class="row mb-4">
            <div class="col-md-3 col-6">
                <div class="stat-card">
                    <span class="stat-number"><?php echo $course_data['enrolled_students']; ?></span>
                    <div class="text-muted">Enrolled Students</div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="stat-card">
                    <span class="stat-number"><?php echo $course_data['max_students']; ?></span>
                    <div class="text-muted">Max Capacity</div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="stat-card">
                    <span class="stat-number"><?php echo count($announcements); ?></span>
                    <div class="text-muted">Announcements</div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="stat-card">
                    <span class="stat-number"><?php echo count($discussions); ?></span>
                    <div class="text-muted">Discussions</div>
                </div>
            </div>
        </div>

        <!-- Course Navigation -->
        <ul class="nav nav-tabs mb-4" id="courseTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="overview-tab" data-bs-toggle="tab" data-bs-target="#overview" type="button" role="tab">
                    <i class="bi bi-info-circle me-2"></i>Overview
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="announcements-tab" data-bs-toggle="tab" data-bs-target="#announcements" type="button" role="tab">
                    <i class="bi bi-megaphone me-2"></i>Announcements
                    <?php if (count($announcements) > 0): ?>
                    <span class="badge bg-primary ms-2"><?php echo count($announcements); ?></span>
                    <?php endif; ?>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="discussions-tab" data-bs-toggle="tab" data-bs-target="#discussions" type="button" role="tab">
                    <i class="bi bi-chat-dots me-2"></i>Discussions
                    <?php if (count($discussions) > 0): ?>
                    <span class="badge bg-primary ms-2"><?php echo count($discussions); ?></span>
                    <?php endif; ?>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="materials-tab" data-bs-toggle="tab" data-bs-target="#materials" type="button" role="tab">
                    <i class="bi bi-folder me-2"></i>Materials
                    <?php if (count($materials) > 0): ?>
                    <span class="badge bg-primary ms-2"><?php echo count($materials); ?></span>
                    <?php endif; ?>
                </button>
            </li>
        </ul>

        <!-- Tab Content -->
        <div class="tab-content" id="courseTabContent">
            <!-- Overview Tab -->
            <div class="tab-pane fade show active" id="overview" role="tabpanel">
                <h4 class="mb-3">Course Description</h4>
                <p class="lead">
                    <?php echo nl2br(htmlspecialchars($course_data['description'] ?: 'No description available.')); ?>
                </p>
                
                <hr class="my-4">
                
                <div class="row">
                    <div class="col-md-6">
                        <h5 class="mb-3">Course Information</h5>
                        <table class="table table-borderless">
                            <tr>
                                <td class="fw-bold">Course Code:</td>
                                <td><?php echo htmlspecialchars($course_data['course_code']); ?></td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Instructor:</td>
                                <td><?php echo htmlspecialchars($course_data['teacher_name']); ?></td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Credits:</td>
                                <td><?php echo $course_data['credits']; ?></td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Semester:</td>
                                <td><?php echo htmlspecialchars($course_data['semester'] . ' ' . $course_data['year']); ?></td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h5 class="mb-3">Schedule</h5>
                        <table class="table table-borderless">
                            <tr>
                                <td class="fw-bold">Start Date:</td>
                                <td><?php echo $course_data['start_date'] ? date('M j, Y', strtotime($course_data['start_date'])) : 'Not set'; ?></td>
                            </tr>
                            <tr>
                                <td class="fw-bold">End Date:</td>
                                <td><?php echo $course_data['end_date'] ? date('M j, Y', strtotime($course_data['end_date'])) : 'Not set'; ?></td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Enrolled:</td>
                                <td><?php echo $course_data['enrolled_students']; ?> / <?php echo $course_data['max_students']; ?> students</td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Status:</td>
                                <td>
                                    <span class="badge bg-<?php echo $course_data['is_active'] ? 'success' : 'secondary'; ?>">
                                        <?php echo $course_data['is_active'] ? 'Active' : 'Inactive'; ?>
                                    </span>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Announcements Tab -->
            <div class="tab-pane fade" id="announcements" role="tabpanel">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 class="mb-0">Course Announcements</h4>
                    <?php if ($is_teacher): ?>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#announcementModal">
                        <i class="bi bi-plus-circle me-2"></i>New Announcement
                    </button>
                    <?php endif; ?>
                </div>
                
                <?php if (empty($announcements)): ?>
                <div class="text-center py-5">
                    <i class="bi bi-megaphone display-1 text-muted"></i>
                    <h5 class="mt-3">No announcements yet</h5>
                    <p class="text-muted">Check back later for course updates and important information.</p>
                    <?php if ($is_teacher): ?>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#announcementModal">
                        Create First Announcement
                    </button>
                    <?php endif; ?>
                </div>
                <?php else: ?>
                    <?php foreach ($announcements as $announcement): ?>
                    <div class="card announcement-card <?php echo $announcement['priority']; ?> mb-3">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h5 class="card-title mb-0"><?php echo htmlspecialchars($announcement['title']); ?></h5>
                                <div>
                                    <span class="badge bg-<?php echo $announcement['priority'] === 'urgent' ? 'danger' : ($announcement['priority'] === 'high' ? 'warning' : 'primary'); ?> me-2">
                                        <?php echo ucfirst($announcement['priority']); ?>
                                    </span>
                                    <small class="text-muted">
                                        <?php echo date('M j, Y g:i A', strtotime($announcement['created_at'])); ?>
                                    </small>
                                </div>
                            </div>
                            <p class="card-text"><?php echo nl2br(htmlspecialchars($announcement['content'])); ?></p>
                            <div class="mt-3">
                                <small class="text-muted">
                                    <i class="bi bi-person me-1"></i>
                                    Posted by <?php echo htmlspecialchars($announcement['posted_by_name']); ?>
                                </small>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Discussions Tab -->
            <div class="tab-pane fade" id="discussions" role="tabpanel">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 class="mb-0">Course Discussions</h4>
                    <?php if ($is_teacher || $is_enrolled): ?>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#discussionModal">
                        <i class="bi bi-plus-circle me-2"></i>New Discussion
                    </button>
                    <?php endif; ?>
                </div>
                
                <?php if (empty($discussions)): ?>
                <div class="text-center py-5">
                    <i class="bi bi-chat-dots display-1 text-muted"></i>
                    <h5 class="mt-3">No discussions yet</h5>
                    <p class="text-muted">Start a conversation with your classmates and instructor.</p>
                    <?php if ($is_teacher || $is_enrolled): ?>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#discussionModal">
                        Start First Discussion
                    </button>
                    <?php endif; ?>
                </div>
                <?php else: ?>
                    <?php foreach ($discussions as $discussion): ?>
                    <div class="discussion-card" onclick="location.href='/pages/courses/discussion.php?id=<?php echo $discussion['id']; ?>'">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h5 class="mb-1"><?php echo htmlspecialchars($discussion['title']); ?></h5>
                                <p class="text-muted mb-2"><?php echo htmlspecialchars($discussion['description'] ?: 'No description'); ?></p>
                                <small class="text-muted">
                                    <i class="bi bi-person me-1"></i>
                                    <?php echo htmlspecialchars($discussion['created_by_name']); ?>
                                    <span class="mx-2">•</span>
                                    <i class="bi bi-chat me-1"></i>
                                    <?php echo $discussion['post_count']; ?> posts
                                    <span class="mx-2">•</span>
                                    <i class="bi bi-clock me-1"></i>
                                    <?php echo date('M j, Y', strtotime($discussion['updated_at'])); ?>
                                </small>
                            </div>
                            <div class="text-end">
                                <?php if ($discussion['is_locked']): ?>
                                <span class="badge bg-secondary">
                                    <i class="bi bi-lock me-1"></i>Locked
                                </span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Materials Tab -->
            <div class="tab-pane fade" id="materials" role="tabpanel">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 class="mb-0">Course Materials</h4>
                    <?php if ($is_teacher): ?>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#materialModal">
                        <i class="bi bi-plus-circle me-2"></i>Upload Material
                    </button>
                    <?php endif; ?>
                </div>
                
                <?php if (empty($materials)): ?>
                <div class="text-center py-5">
                    <i class="bi bi-folder display-1 text-muted"></i>
                    <h5 class="mt-3">No materials uploaded</h5>
                    <p class="text-muted">Course materials will appear here when uploaded.</p>
                    <?php if ($is_teacher): ?>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#materialModal">
                        Upload First Material
                    </button>
                    <?php endif; ?>
                </div>
                <?php else: ?>
                    <div class="row">
                        <?php foreach ($materials as $material): ?>
                        <div class="col-md-6 mb-3">
                            <div class="material-item">
                                <div class="flex-grow-1">
                                    <h6 class="mb-1"><?php echo htmlspecialchars($material['title']); ?></h6>
                                    <p class="text-muted small mb-1"><?php echo htmlspecialchars($material['description'] ?: 'No description'); ?></p>
                                    <small class="text-muted">
                                        <i class="bi bi-file-earmark me-1"></i>
                                        <?php echo strtoupper($material['file_type'] ?: 'Unknown'); ?>
                                        <?php if ($material['file_size']): ?>
                                        • <?php echo number_format($material['file_size'] / 1024, 1); ?> KB
                                        <?php endif; ?>
                                    </small>
                                </div>
                                <div class="ms-3">
                                    <a href="<?php echo htmlspecialchars($material['file_path']); ?>" class="btn btn-outline-primary btn-sm" download>
                                        <i class="bi bi-download"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Back Button -->
        <div class="mt-4">
            <a href="/pages/courses/index.php" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-2"></i>Back to Courses
            </a>
        </div>
    </div>

    <!-- Create Announcement Modal -->
    <?php if ($is_teacher): ?>
    <div class="modal fade" id="announcementModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Create New Announcement</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="create_announcement">
                        
                        <div class="mb-3">
                            <label for="announcement_title" class="form-label required-field">Title</label>
                            <input type="text" class="form-control" id="announcement_title" name="title" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="announcement_content" class="form-label required-field">Content</label>
                            <textarea class="form-control" id="announcement_content" name="content" rows="6" required 
                                      placeholder="Enter your announcement content..."></textarea>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="announcement_priority" class="form-label">Priority</label>
                                <select class="form-select" id="announcement_priority" name="priority">
                                    <option value="normal">Normal</option>
                                    <option value="high">High</option>
                                    <option value="urgent">Urgent</option>
                                    <option value="low">Low</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="announcement_publish_date" class="form-label">Publish Date (Optional)</label>
                                <input type="datetime-local" class="form-control" id="announcement_publish_date" name="publish_date">
                                <div class="form-text">Leave empty to publish immediately</div>
                            </div>
                        </div>
                        
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="announcement_published" name="is_published" checked>
                            <label class="form-check-label" for="announcement_published">
                                Publish immediately
                            </label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Create Announcement</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Create Discussion Modal -->
    <div class="modal fade" id="discussionModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Create New Discussion</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="create_discussion">
                        
                        <div class="mb-3">
                            <label for="discussion_title" class="form-label required-field">Title</label>
                            <input type="text" class="form-control" id="discussion_title" name="title" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="discussion_description" class="form-label">Description (Optional)</label>
                            <textarea class="form-control" id="discussion_description" name="description" rows="3" 
                                      placeholder="Brief description of the discussion topic..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Create Discussion</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Student Enrollment Modal -->
    <?php if ($current_user['role'] === 'student' && !$is_enrolled): ?>
    <div class="modal fade" id="enrollModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Enroll in Course</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="/pages/courses/enroll.php">
                    <div class="modal-body">
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle me-2"></i>
                            Course: <strong><?php echo htmlspecialchars($course_data['title']); ?></strong><br>
                            Code: <strong><?php echo htmlspecialchars($course_data['course_code']); ?></strong>
                        </div>
                        <input type="hidden" name="course_id" value="<?php echo $course_id; ?>">
                        
                        <div class="mb-3">
                            <label for="class_code" class="form-label">Class Code (Optional)</label>
                            <input type="text" class="form-control" id="class_code" name="class_code" 
                                   placeholder="Enter class code if required">
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

    <script>
        // Tab persistence
        const urlParams = new URLSearchParams(window.location.search);
        const activeTab = urlParams.get('tab');
        if (activeTab) {
            const tabTrigger = document.querySelector(`[data-bs-target="#${activeTab}"]`);
            if (tabTrigger) {
                const tab = new bootstrap.Tab(tabTrigger);
                tab.show();
            }
        }

        // Update URL when tabs change
        document.querySelectorAll('[data-bs-toggle="tab"]').forEach(tab => {
            tab.addEventListener('shown.bs.tab', function (e) {
                const targetId = e.target.getAttribute('data-bs-target').substring(1);
                const newUrl = new URL(window.location);
                newUrl.searchParams.set('tab', targetId);
                window.history.replaceState({}, '', newUrl);
            });
        });

        // Auto-save form data
        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', function() {
                // Clear any auto-saved data
                localStorage.removeItem('announcement_draft');
                localStorage.removeItem('discussion_draft');
            });
        });

        // Character counters
        function addCharacterCounter(textarea, maxLength = 1000) {
            const counter = document.createElement('small');
            counter.className = 'text-muted';
            counter.style.display = 'block';
            counter.style.marginTop = '0.25rem';
            
            function updateCounter() {
                const remaining = maxLength - textarea.value.length;
                counter.textContent = `${remaining} characters remaining`;
                counter.className = remaining < 0 ? 'text-danger' : 'text-muted';
            }
            
            textarea.addEventListener('input', updateCounter);
            updateCounter();
            textarea.parentNode.appendChild(counter);
        }

        const announcementContent = document.getElementById('announcement_content');
        if (announcementContent) {
            addCharacterCounter(announcementContent);
        }

        const discussionDescription = document.getElementById('discussion_description');
        if (discussionDescription) {
            addCharacterCounter(discussionDescription, 500);
        }
    </script>
</body>
</html>