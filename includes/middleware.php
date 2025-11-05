<?php
/**
 * Session Verification Middleware
 * Handles session security and validation
 */

define('AUTH_SYSTEM', true);
require_once __DIR__ . '/auth.php';

/**
 * Middleware class for session management
 */
class SessionMiddleware {
    
    private $allowed_paths = [
        '/pages/login.php',
        '/pages/logout.php',
        '/pages/register.php' // if you have registration
    ];
    
    private $protected_paths = [
        '/pages/dashboard.php',
        '/pages/admin/',
        '/api/'
    ];
    
    /**
     * Check if current path requires authentication
     */
    private function requires_auth() {
        $current_path = $this->get_current_path();
        
        // Check if path is in protected areas
        foreach ($this->protected_paths as $path) {
            if (strpos($current_path, $path) === 0) {
                return true;
            }
        }
        
        // Check if path is in allowed areas for unauthenticated users
        foreach ($this->allowed_paths as $path) {
            if (strpos($current_path, $path) === 0) {
                return false;
            }
        }
        
        // Default: require auth for most pages
        return true;
    }
    
    /**
     * Get current request path
     */
    private function get_current_path() {
        $script_name = $_SERVER['SCRIPT_NAME'] ?? '/';
        $path = parse_url($script_name, PHP_URL_PATH);
        return $path ?: '/';
    }
    
    /**
     * Validate session security
     */
    private function validate_session_security() {
        $session_token = $_SESSION['session_token'] ?? null;
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $ip_address = $_SERVER['REMOTE_ADDR'] ?? '';
        
        // Check if session token exists
        if (!$session_token) {
            return false;
        }
        
        // Validate user agent (basic check)
        if (!isset($_SESSION['user_agent']) || $_SESSION['user_agent'] !== $user_agent) {
            $_SESSION['user_agent'] = $user_agent;
        }
        
        // Validate IP address (optional - can be disabled for mobile users)
        // Uncomment the following lines if you want strict IP validation
        /*
        if (!isset($_SESSION['ip_address']) || $_SESSION['ip_address'] !== $ip_address) {
            // Allow some flexibility for mobile users
            $last_ip = $_SESSION['ip_address'] ?? '';
            if ($last_ip && $last_ip !== $ip_address) {
                // Log suspicious IP change
                if (isset($_SESSION['user_id'])) {
                    log_activity($_SESSION['user_id'], 'ip_change', "IP changed from $last_ip to $ip_address");
                }
            }
            $_SESSION['ip_address'] = $ip_address;
        }
        */
        
        return true;
    }
    
    /**
     * Check for session fixation attacks
     */
    private function check_session_fixation() {
        // Regenerate session ID periodically (every 15 minutes)
        if (isset($_SESSION['login_time'])) {
            if (time() - $_SESSION['login_time'] > 900) { // 15 minutes
                session_regenerate_id(true);
                $_SESSION['login_time'] = time();
                $_SESSION['last_activity'] = time();
            }
        }
    }
    
    /**
     * Handle session timeout
     */
    private function handle_session_timeout() {
        if (isset($_SESSION['last_activity'])) {
            $idle_time = time() - $_SESSION['last_activity'];
            $timeout_duration = 1800; // 30 minutes
            
            if ($idle_time > $timeout_duration) {
                // Log timeout
                if (isset($_SESSION['user_id'])) {
                    log_activity($_SESSION['user_id'], 'session_timeout', 'Session timed out due to inactivity');
                }
                
                logout();
                return false;
            }
            
            $_SESSION['last_activity'] = time();
        }
        
        return true;
    }
    
    /**
     * Rate limiting for authentication attempts
     */
    private function check_rate_limit() {
        if (!isset($_SESSION['login_attempts'])) {
            $_SESSION['login_attempts'] = 0;
            $_SESSION['last_attempt_time'] = 0;
        }
        
        $current_time = time();
        $attempt_window = 300; // 5 minutes
        $max_attempts = 5;
        
        // Reset attempts if window has passed
        if ($current_time - $_SESSION['last_attempt_time'] > $attempt_window) {
            $_SESSION['login_attempts'] = 0;
        }
        
        return $_SESSION['login_attempts'] < $max_attempts;
    }
    
    /**
     * Record login attempt
     */
    public function record_login_attempt($success = false) {
        if (!$success) {
            $_SESSION['login_attempts']++;
            $_SESSION['last_attempt_time'] = time();
        } else {
            $_SESSION['login_attempts'] = 0;
        }
    }
    
    /**
     * Middleware execution
     */
    public function handle() {
        // Initialize session
        initialize_session();
        
        // Check if path requires authentication
        if (!$this->requires_auth()) {
            return true; // Allow access without authentication
        }
        
        // Check if user is logged in
        if (!is_logged_in()) {
            $this->redirect_to_login();
            return false;
        }
        
        // Validate session security
        if (!$this->validate_session_security()) {
            $this->handle_security_violation();
            return false;
        }
        
        // Check for session fixation
        $this->check_session_fixation();
        
        // Handle session timeout
        if (!$this->handle_session_timeout()) {
            $this->redirect_to_login('Session expired. Please login again.');
            return false;
        }
        
        // Check for suspicious activity
        if (check_suspicious_activity()) {
            error_log("Suspicious activity detected from IP: " . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'));
        }
        
        return true;
    }
    
    /**
     * Redirect to login page
     */
    private function redirect_to_login($message = null) {
        $login_url = '/pages/login.php';
        
        if ($message) {
            $_SESSION['error_message'] = $message;
            $login_url .= '?error=' . urlencode($message);
        }
        
        header("Location: $login_url");
        exit();
    }
    
    /**
     * Handle security violations
     */
    private function handle_security_violation() {
        // Log the security violation
        $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
        $user_id = $_SESSION['user_id'] ?? null;
        
        if ($user_id) {
            log_activity($user_id, 'security_violation', "Security violation detected from IP: $ip_address");
        }
        
        // Destroy session and redirect
        session_destroy();
        
        error_log("Security violation: IP $ip_address, User Agent: $user_agent, User ID: " . ($user_id ?? 'anonymous'));
        
        $this->redirect_to_login('Security violation detected. Please login again.');
    }
    
    /**
     * Set security headers
     */
    public static function set_security_headers() {
        // Prevent clickjacking
        header('X-Frame-Options: DENY');
        
        // Prevent MIME type sniffing
        header('X-Content-Type-Options: nosniff');
        
        // Enable XSS protection
        header('X-XSS-Protection: 1; mode=block');
        
        // Referrer policy
        header('Referrer-Policy: strict-origin-when-cross-origin');
        
        // Content Security Policy (basic)
        header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline'; img-src 'self' data:;");
    }
    
    /**
     * Clean up old sessions (call this periodically)
     */
    public static function cleanup_old_sessions() {
        $pdo = get_db_connection();
        if (!$pdo) {
            return false;
        }
        
        try {
            // Clean up old activity logs (older than 30 days)
            $stmt = $pdo->prepare("DELETE FROM activity_logs WHERE created_at < DATE_SUB(NOW(), INTERVAL 30 DAY)");
            $stmt->execute();
            
            return true;
        } catch (PDOException $e) {
            error_log("Session cleanup error: " . $e->getMessage());
            return false;
        }
    }
}

// Initialize middleware
$middleware = new SessionMiddleware();

// Set security headers for all requests
SessionMiddleware::set_security_headers();

// Handle the request
if (!$middleware->handle()) {
    exit();
}
?>