<?php
/**
 * Course Management System Installation Script
 * This script sets up the course management database schema
 */

$page_title = "Course Management System - Installation";
$breadcrumb = [
    ['title' => 'Installation']
];

require_once __DIR__ . '/../includes/middleware.php';

// Only allow admin users to run installation
$current_user = get_current_user();
if ($current_user['role'] !== 'admin') {
    header("HTTP/1.0 403 Forbidden");
    die('Access denied: Admin only');
}

$message = '';
$message_type = '';
$installation_complete = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    try {
        require_once __DIR__ . '/../includes/database_config.php';
        require_once __DIR__ . '/../includes/class.Database.php';
        
        $db = Database::getInstance();
        $pdo = $db->getConnection();
        
        if ($_POST['action'] === 'install') {
            // Read and execute the course schema
            $schema_file = __DIR__ . '/../course_schema.sql';
            if (!file_exists($schema_file)) {
                throw new Exception('Course schema file not found');
            }
            
            $sql = file_get_contents($schema_file);
            
            // Split SQL into individual statements
            $statements = array_filter(array_map('trim', explode(';', $sql)), function($stmt) {
                return !empty($stmt) && !preg_match('/^--/', $stmt);
            });
            
            $executed_statements = 0;
            foreach ($statements as $statement) {
                if (trim($statement)) {
                    try {
                        $pdo->exec($statement);
                        $executed_statements++;
                    } catch (PDOException $e) {
                        // Log error but continue with other statements
                        error_log("SQL execution error: " . $e->getMessage() . " | Statement: " . substr($statement, 0, 100));
                    }
                }
            }
            
            $message = "Course management system installed successfully! Executed {$executed_statements} SQL statements.";
            $message_type = 'success';
            $installation_complete = true;
            
        } elseif ($_POST['action'] === 'verify') {
            // Verify installation by checking key tables
            $required_tables = [
                'courses',
                'course_enrollments',
                'course_announcements',
                'course_discussions',
                'discussion_posts',
                'course_materials',
                'course_assignments',
                'assignment_submissions'
            ];
            
            $existing_tables = [];
            $missing_tables = [];
            
            foreach ($required_tables as $table) {
                try {
                    $result = $pdo->query("SHOW TABLES LIKE '{$table}'")->fetch();
                    if ($result) {
                        $existing_tables[] = $table;
                    } else {
                        $missing_tables[] = $table;
                    }
                } catch (PDOException $e) {
                    $missing_tables[] = $table . ' (error: ' . $e->getMessage() . ')';
                }
            }
            
            if (empty($missing_tables)) {
                $message = "All course management tables exist. Installation verified successfully!";
                $message_type = 'success';
                $installation_complete = true;
            } else {
                $message = "Missing tables: " . implode(', ', $missing_tables);
                $message_type = 'danger';
            }
        }
        
    } catch (Exception $e) {
        $message = "Installation error: " . $e->getMessage();
        $message_type = 'danger';
    }
}

// Check current installation status
try {
    require_once __DIR__ . '/../includes/database_config.php';
    require_once __DIR__ . '/../includes/class.Database.php';
    
    $db = Database::getInstance();
    $pdo = $db->getConnection();
    
    // Check if courses table exists
    $result = $pdo->query("SHOW TABLES LIKE 'courses'")->fetch();
    $courses_table_exists = (bool)$result;
    
    // Check if user role includes teacher and student
    $role_check = $pdo->query("SHOW COLUMNS FROM users LIKE 'role'")->fetch();
    $role_enum_check = $pdo->query("SELECT COLUMN_TYPE FROM information_schema.COLUMNS WHERE TABLE_NAME = 'users' AND COLUMN_NAME = 'role'")->fetch();
    
} catch (Exception $e) {
    $courses_table_exists = false;
    $role_enum_check = false;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <?php include __DIR__ . '/../includes/header.php'; ?>
    <style>
        .installation-card {
            background: white;
            border-radius: 8px;
            padding: 2rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }
        
        .status-indicator {
            width: 20px;
            height: 20px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 0.5rem;
        }
        
        .status-success { background-color: #28a745; }
        .status-warning { background-color: #ffc107; }
        .status-error { background-color: #dc3545; }
        
        .installation-steps {
            counter-reset: step-counter;
        }
        
        .installation-step {
            counter-increment: step-counter;
            position: relative;
            padding-left: 3rem;
            margin-bottom: 2rem;
        }
        
        .installation-step::before {
            content: counter(step-counter);
            position: absolute;
            left: 0;
            top: 0;
            width: 2rem;
            height: 2rem;
            background: #667eea;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }
        
        .feature-list {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1rem;
            margin-top: 2rem;
        }
        
        .feature-item {
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 6px;
            border-left: 4px solid #667eea;
        }
    </style>
</head>
<body>
    <div class="container-fluid main-container">
        <!-- Page Header -->
        <div class="row mb-4">
            <div class="col">
                <h1 class="h3 mb-0"><?php echo $page_title; ?></h1>
                <p class="text-muted mb-0">Install and configure the course management system</p>
            </div>
        </div>

        <?php if ($message): ?>
        <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
            <i class="bi bi-<?php echo $message_type === 'success' ? 'check-circle' : 'exclamation-triangle'; ?> me-2"></i>
            <?php echo htmlspecialchars($message); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <!-- Installation Status -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="installation-card">
                    <h4 class="mb-3">
                        <i class="bi bi-gear me-2"></i>Installation Status
                    </h4>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Database Tables</h6>
                            <div class="mb-2">
                                <span class="status-indicator <?php echo $courses_table_exists ? 'status-success' : 'status-error'; ?>"></span>
                                <strong>Courses Table:</strong> 
                                <?php echo $courses_table_exists ? 'Installed' : 'Not Found'; ?>
                            </div>
                            <div class="mb-2">
                                <span class="status-indicator <?php echo $courses_table_exists ? 'status-success' : 'status-warning'; ?>"></span>
                                <strong>Related Tables:</strong> 
                                <?php echo $courses_table_exists ? 'Ready' : 'Pending Installation'; ?>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <h6>User System</h6>
                            <div class="mb-2">
                                <span class="status-indicator <?php echo $role_enum_check ? 'status-success' : 'status-warning'; ?>"></span>
                                <strong>Extended Roles:</strong> 
                                <?php echo $role_enum_check ? 'Available' : 'Check Required'; ?>
                            </div>
                            <div class="mb-2">
                                <span class="status-indicator status-success"></span>
                                <strong>Authentication:</strong> Ready
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Installation Steps -->
        <div class="row">
            <div class="col-lg-8">
                <div class="installation-card">
                    <h4 class="mb-4">
                        <i class="bi bi-list-check me-2"></i>Installation Steps
                    </h4>
                    
                    <div class="installation-steps">
                        <!-- Step 1: Database Schema -->
                        <div class="installation-step">
                            <h5>Install Database Schema</h5>
                            <p class="text-muted">
                                Create all required database tables for course management including courses, 
                                enrollments, announcements, discussions, materials, and assignments.
                            </p>
                            
                            <?php if (!$courses_table_exists): ?>
                            <form method="POST" class="d-inline">
                                <input type="hidden" name="action" value="install">
                                <button type="submit" class="btn btn-primary" 
                                        onclick="return confirm('This will create new database tables for the course management system. Continue?');">
                                    <i class="bi bi-download me-2"></i>Install Course Tables
                                </button>
                            </form>
                            <?php else: ?>
                            <div class="alert alert-success">
                                <i class="bi bi-check-circle me-2"></i>
                                Database schema already installed
                            </div>
                            <?php endif; ?>
                        </div>

                        <!-- Step 2: Verify Installation -->
                        <div class="installation-step">
                            <h5>Verify Installation</h5>
                            <p class="text-muted">
                                Check that all course management tables were created successfully and 
                                the system is ready for use.
                            </p>
                            
                            <?php if ($courses_table_exists): ?>
                            <form method="POST" class="d-inline">
                                <input type="hidden" name="action" value="verify">
                                <button type="submit" class="btn btn-outline-primary">
                                    <i class="bi bi-check-square me-2"></i>Verify Installation
                                </button>
                            </form>
                            <?php else: ?>
                            <button type="button" class="btn btn-outline-secondary" disabled>
                                <i class="bi bi-hourglass-split me-2"></i>Complete Step 1 First
                            </button>
                            <?php endif; ?>
                        </div>

                        <!-- Step 3: Sample Data -->
                        <div class="installation-step">
                            <h5>Create Sample Data (Optional)</h5>
                            <p class="text-muted">
                                Add sample courses, teachers, and students for testing and demonstration purposes.
                            </p>
                            
                            <?php if ($courses_table_exists): ?>
                            <a href="#" class="btn btn-outline-success" onclick="alert('Sample data will be created automatically with the schema.'); return false;">
                                <i class="bi bi-plus-circle me-2"></i>Sample Data Ready
                            </a>
                            <?php else: ?>
                            <button type="button" class="btn btn-outline-secondary" disabled>
                                <i class="bi bi-hourglass-split me-2"></i>Complete Installation First
                            </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar Features -->
            <div class="col-lg-4">
                <div class="installation-card">
                    <h5 class="mb-3">
                        <i class="bi bi-star me-2"></i>Course Management Features
                    </h5>
                    
                    <div class="feature-list">
                        <div class="feature-item">
                            <h6><i class="bi bi-book me-2"></i>Course Creation</h6>
                            <p class="small text-muted mb-0">Teachers can create and manage courses with detailed information</p>
                        </div>
                        
                        <div class="feature-item">
                            <h6><i class="bi bi-person-plus me-2"></i>Student Enrollment</h6>
                            <p class="small text-muted mb-0">Students can browse and enroll in courses using class codes</p>
                        </div>
                        
                        <div class="feature-item">
                            <h6><i class="bi bi-megaphone me-2"></i>Announcements</h6>
                            <p class="small text-muted mb-0">Post and view course announcements with priority levels</p>
                        </div>
                        
                        <div class="feature-item">
                            <h6><i class="bi bi-chat-dots me-2"></i>Discussions</h6>
                            <p class="small text-muted mb-0">Interactive discussion forums for each course</p>
                        </div>
                        
                        <div class="feature-item">
                            <h6><i class="bi bi-folder me-2"></i>Course Materials</h6>
                            <p class="small text-muted mb-0">Upload and share course materials and resources</p>
                        </div>
                        
                        <div class="feature-item">
                            <h6><i class="bi bi-clipboard me-2"></i>Assignments</h6>
                            <p class="small text-muted mb-0">Create assignments and manage student submissions</p>
                        </div>
                    </div>
                </div>

                <!-- Quick Links -->
                <div class="installation-card">
                    <h5 class="mb-3">
                        <i class="bi bi-link-45deg me-2"></i>Quick Links
                    </h5>
                    
                    <div class="d-grid gap-2">
                        <a href="/pages/courses/index.php" class="btn btn-outline-primary">
                            <i class="bi bi-grid me-2"></i>View Courses
                        </a>
                        <a href="/pages/dashboard.php" class="btn btn-outline-secondary">
                            <i class="bi bi-speedometer2 me-2"></i>Dashboard
                        </a>
                        <a href="/pages/admin/users.php" class="btn btn-outline-info">
                            <i class="bi bi-people me-2"></i>Manage Users
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <?php if ($installation_complete): ?>
        <!-- Success Actions -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="alert alert-success">
                    <h5><i class="bi bi-check-circle me-2"></i>Installation Complete!</h5>
                    <p class="mb-3">The course management system has been successfully installed and configured.</p>
                    
                    <div class="d-flex gap-2 flex-wrap">
                        <a href="/pages/courses/index.php" class="btn btn-success">
                            <i class="bi bi-arrow-right me-2"></i>Start Using Course Management
                        </a>
                        <a href="/pages/courses/create.php" class="btn btn-primary">
                            <i class="bi bi-plus-circle me-2"></i>Create Your First Course
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <script>
        // Auto-refresh status every 30 seconds during installation
        let statusInterval;
        
        function startStatusMonitoring() {
            statusInterval = setInterval(function() {
                // Check installation status via AJAX
                fetch('installation_status.php')
                    .then(response => response.json())
                    .then(data => {
                        if (data.complete) {
                            clearInterval(statusInterval);
                            location.reload();
                        }
                    })
                    .catch(error => {
                        console.log('Status check failed:', error);
                    });
            }, 30000);
        }
        
        // Start monitoring if installation is in progress
        <?php if (!$installation_complete && $courses_table_exists): ?>
        startStatusMonitoring();
        <?php endif; ?>
        
        // Show loading states on form submission
        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', function() {
                const submitBtn = this.querySelector('button[type="submit"]');
                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<i class="bi bi-hourglass-split me-2"></i>Processing...';
                }
            });
        });
    </script>
</body>
</html>