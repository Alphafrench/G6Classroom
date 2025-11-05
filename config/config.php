<?php
/**
 * Google Classroom Clone - Application Configuration
 * Professional classroom management system configuration
 * 
 * @package ClassroomManager
 * @version 1.0.0
 */

// Load environment configuration
require_once __DIR__ . '/environment.php';

// Error reporting based on environment
if (APP_ENV === 'development') {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
    error_reporting(0);
}

// Session configuration
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', isset($_SERVER['HTTPS']));
ini_set('session.cookie_samesite', 'Strict');

// Time zone
date_default_timezone_set(APP_TIMEZONE);

// Application constants
define('APP_NAME', 'Google Classroom Clone');
define('APP_VERSION', '1.0.0');
define('APP_DESCRIPTION', 'Professional Classroom Management System');
define('APP_AUTHOR', 'Classroom Manager Team');
define('APP_URL', rtrim(APP_URL, '/'));
define('ROOT_PATH', dirname(__DIR__));
define('INCLUDES_PATH', ROOT_PATH . '/includes/');
define('PAGES_PATH', ROOT_PATH . '/pages/');
define('ASSETS_PATH', ROOT_PATH . '/assets/');
define('UPLOADS_PATH', ROOT_PATH . '/uploads/');

// Security settings
define('SECURITY_SALT', 'classroom-salt-2024-change-in-production');
define('PASSWORD_MIN_LENGTH', 8);
define('SESSION_TIMEOUT', 7200); // 2 hours
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOGIN_LOCKOUT_TIME', 900); // 15 minutes
define('CSRF_TOKEN_EXPIRE', 3600); // 1 hour

// File upload settings
define('UPLOAD_MAX_SIZE', 10 * 1024 * 1024); // 10MB
define('UPLOAD_AVATAR_SIZE', 2 * 1024 * 1024); // 2MB
define('ALLOWED_IMAGE_TYPES', ['jpg', 'jpeg', 'png', 'gif', 'webp']);
define('ALLOWED_DOCUMENT_TYPES', ['pdf', 'doc', 'docx', 'txt', 'ppt', 'pptx']);
define('ALLOWED_VIDEO_TYPES', ['mp4', 'avi', 'mov', 'wmv', 'webm']);
define('UPLOAD_PATH', UPLOADS_PATH);
define('AVATAR_PATH', UPLOADS_PATH . 'avatars/');
define('ASSIGNMENT_PATH', UPLOADS_PATH . 'assignments/');
define('RESOURCE_PATH', UPLOADS_PATH . 'resources/');

// Email settings (for notifications)
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'your-email@gmail.com');
define('SMTP_PASSWORD', 'your-app-password');
define('FROM_EMAIL', 'noreply@classroom-manager.com');
define('FROM_NAME', APP_NAME);

// Application settings
define('TIME_FORMAT', 'H:i');
define('DATE_FORMAT', 'M j, Y');
define('DATETIME_FORMAT', 'M j, Y H:i');
define('DATETIME_FULL_FORMAT', 'Y-m-d H:i:s');

// Pagination and display
define('RECORDS_PER_PAGE', 20);
define('ASSIGNMENTS_PER_PAGE', 10);
define('SUBMISSIONS_PER_PAGE', 15);

// User roles and permissions
define('USER_ROLES', [
    'admin' => 'Administrator',
    'teacher' => 'Teacher', 
    'student' => 'Student',
    'parent' => 'Parent'
]);

// Class statuses
define('CLASS_STATUSES', [
    'active' => 'Active',
    'inactive' => 'Inactive',
    'completed' => 'Completed'
]);

// Assignment types
define('ASSIGNMENT_TYPES', [
    'homework' => 'Homework',
    'quiz' => 'Quiz', 
    'exam' => 'Exam',
    'project' => 'Project',
    'discussion' => 'Discussion'
]);

// Grade scales
define('GRADE_SCALES', [
    'percentage' => 'Percentage (0-100)',
    'letter' => 'Letter Grades (A-F)',
    'points' => 'Points System',
    'custom' => 'Custom Scale'
]);

// Notification settings
define('ENABLE_EMAIL_NOTIFICATIONS', true);
define('ENABLE_SMS_NOTIFICATIONS', false);
define('ENABLE_PUSH_NOTIFICATIONS', true);
define('ENABLE_GRADE_NOTIFICATIONS', true);
define('ENABLE_ASSIGNMENT_REMINDERS', true);

// Feature flags
define('ENABLE_VIDEO_CONFERENCING', false);
define('ENABLE_SCREEN_SHARING', false);
define('ENABLE_CHAT_FEATURES', true);
define('ENABLE_FILE_SHARING', true);
define('ENABLE_GRADING_SYSTEM', true);
define('ENABLE_ATTENDANCE_TRACKING', true);
define('ENABLE_PARENT_PORTAL', true);

// API settings
define('API_VERSION', 'v1');
define('API_RATE_LIMIT', 100); // requests per hour

// Cache settings
define('ENABLE_CACHE', APP_ENV === 'production');
define('CACHE_LIFETIME', 3600); // 1 hour

// Backup settings
define('AUTO_BACKUP', true);
define('BACKUP_FREQUENCY', 'daily'); // daily, weekly, monthly
define('BACKUP_RETENTION_DAYS', 30);

/**
 * Auto-loader for includes
 */
function autoloadClasses($className) {
    $file = ROOT_PATH . '/includes/' . $className . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
}

spl_autoload_register('autoloadClasses');

/**
 * Get current timestamp in database format
 * @return string
 */
function getCurrentTimestamp() {
    return date(DATETIME_FORMAT);
}

/**
 * Sanitize input data
 * @param mixed $data
 * @return mixed
 */
function sanitizeInput($data) {
    if (is_array($data)) {
        return array_map('sanitizeInput', $data);
    }
    return htmlspecialchars(strip_tags(trim($data)));
}

/**
 * Check if user is logged in
 * @return bool
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Check if user has permission
 * @param string $permission
 * @return bool
 */
function hasPermission($permission) {
    if (!isLoggedIn()) {
        return false;
    }
    
    // Add your permission logic here
    return isset($_SESSION['permissions']) && in_array($permission, $_SESSION['permissions']);
}