<?php
/**
 * Authentication Functions
 * Secure session-based authentication with password hashing
 */

// Prevent direct access
if (!defined('AUTH_SYSTEM')) {
    die('Direct access not permitted');
}

// Database configuration - replace with your actual database details
$db_config = [
    'host' => 'localhost',
    'dbname' => 'your_database',
    'username' => 'your_username',
    'password' => 'your_password'
];

/**
 * Initialize secure session
 */
function initialize_session() {
    require_once __DIR__ . '/session.php';
    initialize_secure_session();
}

/**
 * Connect to database
 */
function get_db_connection() {
    global $db_config;
    try {
        $pdo = new PDO(
            "mysql:host={$db_config['host']};dbname={$db_config['dbname']};charset=utf8mb4",
            $db_config['username'],
            $db_config['password'],
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ]
        );
        return $pdo;
    } catch (PDOException $e) {
        error_log("Database connection failed: " . $e->getMessage());
        return null;
    }
}

/**
 * Hash password using bcrypt
 */
function hash_password($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

/**
 * Verify password against hash
 */
function verify_password($password, $hash) {
    return password_verify($password, $hash);
}

/**
 * Authenticate user
 */
function login($username, $password, $remember_me = false) {
    initialize_session();
    
    $pdo = get_db_connection();
    if (!$pdo) {
        return ['success' => false, 'message' => 'Database connection failed'];
    }
    
    try {
        // Prepare statement to prevent SQL injection
        $stmt = $pdo->prepare("SELECT id, username, email, password, role, is_active, failed_attempts, last_failed_attempt FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $username]);
        $user = $stmt->fetch();
        
        if (!$user) {
            return ['success' => false, 'message' => 'Invalid credentials'];
        }
        
        // Check if account is locked due to failed attempts
        if ($user['failed_attempts'] >= 5 && $user['last_failed_attempt']) {
            $last_attempt = strtotime($user['last_failed_attempt']);
            if (time() - $last_attempt < 900) { // 15 minutes lock
                return ['success' => false, 'message' => 'Account temporarily locked due to multiple failed attempts'];
            }
        }
        
        // Check if account is active
        if (!$user['is_active']) {
            return ['success' => false, 'message' => 'Account is deactivated'];
        }
        
        // Verify password
        if (!verify_password($password, $user['password'])) {
            // Increment failed attempts
            $update_stmt = $pdo->prepare("UPDATE users SET failed_attempts = failed_attempts + 1, last_failed_attempt = NOW() WHERE id = ?");
            $update_stmt->execute([$user['id']]);
            
            return ['success' => false, 'message' => 'Invalid credentials'];
        }
        
        // Reset failed attempts on successful login
        $reset_stmt = $pdo->prepare("UPDATE users SET failed_attempts = 0, last_failed_attempt = NULL WHERE id = ?");
        $reset_stmt->execute([$user['id']]);
        
        // Generate session token
        $session_token = bin2hex(random_bytes(32));
        
        // Set up secure session
        setup_secure_session($user['id'], $user['username'], $user['role']);
        $_SESSION['email'] = $user['email'];
        $_SESSION['login_time'] = time();
        
        // Handle remember me functionality
        if ($remember_me) {
            setcookie('remember_token', $session_token, time() + (30 * 24 * 60 * 60), '/', '', true, true); // 30 days
        }
        
        // Log successful login
        log_activity($user['id'], 'login', 'User logged in successfully');
        
        return ['success' => true, 'message' => 'Login successful'];
        
    } catch (PDOException $e) {
        error_log("Login error: " . $e->getMessage());
        return ['success' => false, 'message' => 'An error occurred during login'];
    }
}

/**
 * Logout user
 */
function logout() {
    initialize_session();
    
    if (isset($_SESSION['user_id'])) {
        log_activity($_SESSION['user_id'], 'logout', 'User logged out');
    }
    
    // Clear remember me cookie
    if (isset($_COOKIE['remember_token'])) {
        setcookie('remember_token', '', time() - 42000, '/', '', true, true);
    }
    
    // Destroy session securely
    destroy_session();
    
    return true;
}

/**
 * Check if user is logged in
 */
function is_logged_in() {
    initialize_session();
    
    // Validate session security
    if (!validate_session()) {
        logout();
        return false;
    }
    
    // Verify session fingerprint
    if (!verify_session_fingerprint()) {
        logout();
        return false;
    }
    
    // Check if all required session variables exist
    $required_vars = ['user_id', 'session_token', 'username', 'role', 'created_at'];
    foreach ($required_vars as $var) {
        if (!isset($_SESSION[$var])) {
            return false;
        }
    }
    
    // Update last activity
    $_SESSION['last_activity'] = time();
    
    return true;
}

/**
 * Get current user information
 */
function get_current_user() {
    if (!is_logged_in()) {
        return null;
    }
    
    return [
        'id' => $_SESSION['user_id'],
        'username' => $_SESSION['username'],
        'email' => $_SESSION['email'],
        'role' => $_SESSION['role']
    ];
}

/**
 * Check if user has specific role
 */
function has_role($required_role) {
    if (!is_logged_in()) {
        return false;
    }
    
    $user_role = $_SESSION['role'];
    
    // Admin has access to everything
    if ($user_role === 'admin') {
        return true;
    }
    
    // Check specific role
    return $user_role === $required_role;
}

/**
 * Require authentication
 */
function require_auth($redirect_url = '/pages/login.php') {
    if (!is_logged_in()) {
        header("Location: $redirect_url");
        exit();
    }
}

/**
 * Require specific role
 */
function require_role($required_role, $redirect_url = '/pages/dashboard.php') {
    require_auth();
    
    if (!has_role($required_role)) {
        header("HTTP/1.0 403 Forbidden");
        die('Access denied: Insufficient privileges');
    }
}

/**
 * Log user activity
 */
function log_activity($user_id, $action, $description) {
    $pdo = get_db_connection();
    if (!$pdo) {
        return false;
    }
    
    try {
        $stmt = $pdo->prepare("INSERT INTO activity_logs (user_id, action, description, ip_address, user_agent, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
        return $stmt->execute([
            $user_id,
            $action,
            $description,
            $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
        ]);
    } catch (PDOException $e) {
        error_log("Activity log error: " . $e->getMessage());
        return false;
    }
}

/**
 * Generate CSRF token
 */
function generate_csrf_token() {
    return generate_session_csrf_token();
}

/**
 * Verify CSRF token
 */
function verify_csrf_token($token) {
    return verify_session_csrf_token($token);
}

/**
 * Check for suspicious activity
 */
function check_suspicious_activity() {
    // Check for multiple failed login attempts from same IP
    $pdo = get_db_connection();
    if (!$pdo) {
        return false;
    }
    
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    
    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) as failed_count FROM users WHERE last_failed_attempt > DATE_SUB(NOW(), INTERVAL 1 HOUR)");
        $stmt->execute();
        $result = $stmt->fetch();
        
        return $result['failed_count'] > 10; // More than 10 failed attempts in the last hour
    } catch (PDOException $e) {
        error_log("Suspicious activity check error: " . $e->getMessage());
        return false;
    }
}
?>