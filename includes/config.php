<?php
/**
 * Configuration File
 * Customize these settings for your environment
 */

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'your_database_name');
define('DB_USER', 'your_database_username');
define('DB_PASS', 'your_database_password');
define('DB_CHARSET', 'utf8mb4');

// Session Configuration
define('SESSION_TIMEOUT', 1800); // 30 minutes in seconds
define('SESSION_REGENERATE_INTERVAL', 900); // 15 minutes
define('REMEMBER_ME_DURATION', 30 * 24 * 60 * 60); // 30 days in seconds

// Security Configuration
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOCKOUT_DURATION', 900); // 15 minutes in seconds
define('CSRF_TOKEN_LENGTH', 32);

// Rate Limiting
define('RATE_LIMIT_WINDOW', 300); // 5 minutes
define('RATE_LIMIT_MAX_ATTEMPTS', 5);

// Application Settings
define('APP_NAME', 'Secure Authentication System');
define('APP_VERSION', '1.0.0');
define('APP_URL', 'http://localhost'); // Your domain

// Email Configuration (for future features like password reset)
define('MAIL_HOST', 'smtp.example.com');
define('MAIL_PORT', 587);
define('MAIL_USERNAME', 'your_email@example.com');
define('MAIL_PASSWORD', 'your_email_password');
define('MAIL_FROM_ADDRESS', 'noreply@example.com');
define('MAIL_FROM_NAME', APP_NAME);

// Logging Configuration
define('LOG_ERRORS', true);
define('LOG_FILE', __DIR__ . '/../logs/application.log');
define('LOG_LEVEL', 'ERROR'); // DEBUG, INFO, WARNING, ERROR

// File Upload Settings
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_FILE_TYPES', ['jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx']);

// Timezone
define('DEFAULT_TIMEZONE', 'UTC');
date_default_timezone_set(DEFAULT_TIMEZONE);

// Error Reporting (disable in production)
if (defined('DEBUG') && DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    ini_set('log_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
}

// Security Headers
header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');

/**
 * Get database connection
 */
function get_db_connection() {
    try {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::ATTR_TIMEOUT => 30
        ];
        
        return new PDO($dsn, DB_USER, DB_PASS, $options);
    } catch (PDOException $e) {
        if (LOG_ERRORS) {
            error_log("Database connection failed: " . $e->getMessage());
        }
        return null;
    }
}

/**
 * Log messages
 */
function log_message($level, $message, $context = []) {
    if (!LOG_ERRORS) {
        return;
    }
    
    $timestamp = date('Y-m-d H:i:s');
    $log_entry = "[$timestamp] [$level] $message";
    
    if (!empty($context)) {
        $log_entry .= ' ' . json_encode($context);
    }
    
    $log_entry .= PHP_EOL;
    
    // Ensure log directory exists
    $log_dir = dirname(LOG_FILE);
    if (!is_dir($log_dir)) {
        mkdir($log_dir, 0755, true);
    }
    
    file_put_contents(LOG_FILE, $log_entry, FILE_APPEND | LOCK_EX);
}

/**
 * Create admin user (run this function once after setup)
 */
function create_admin_user($username, $email, $password) {
    $pdo = get_db_connection();
    if (!$pdo) {
        return false;
    }
    
    try {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, 'admin')");
        $result = $stmt->execute([$username, $email, $hashed_password]);
        
        if ($result) {
            log_message('INFO', "Admin user created: $username");
            return true;
        }
        
        return false;
    } catch (PDOException $e) {
        log_message('ERROR', "Failed to create admin user: " . $e->getMessage());
        return false;
    }
}

/**
 * Create employee user
 */
function create_employee_user($username, $email, $password) {
    $pdo = get_db_connection();
    if (!$pdo) {
        return false;
    }
    
    try {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, 'employee')");
        $result = $stmt->execute([$username, $email, $hashed_password]);
        
        if ($result) {
            log_message('INFO', "Employee user created: $username");
            return true;
        }
        
        return false;
    } catch (PDOException $e) {
        log_message('ERROR', "Failed to create employee user: " . $e->getMessage());
        return false;
    }
}

/**
 * Check if system is properly configured
 */
function check_system_requirements() {
    $checks = [];
    
    // Check PHP version
    $checks['php_version'] = version_compare(PHP_VERSION, '7.4.0', '>=');
    
    // Check required extensions
    $checks['pdo_mysql'] = extension_loaded('pdo_mysql');
    $checks['openssl'] = extension_loaded('openssl');
    $checks['hash'] = extension_loaded('hash');
    
    // Check database connection
    $pdo = get_db_connection();
    $checks['database'] = $pdo !== null;
    
    // Check file permissions
    $checks['log_directory'] = is_writable(dirname(LOG_FILE));
    
    // Check if required tables exist
    if ($pdo) {
        try {
            $stmt = $pdo->query("SHOW TABLES LIKE 'users'");
            $checks['users_table'] = $stmt->rowCount() > 0;
            
            $stmt = $pdo->query("SHOW TABLES LIKE 'activity_logs'");
            $checks['activity_logs_table'] = $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            $checks['tables'] = false;
        }
    }
    
    return $checks;
}

/**
 * Get system status
 */
function get_system_status() {
    $requirements = check_system_requirements();
    $all_passed = !in_array(false, $requirements);
    
    return [
        'status' => $all_passed ? 'ready' : 'needs_setup',
        'checks' => $requirements,
        'version' => APP_VERSION
    ];
}
?>