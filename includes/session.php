<?php
/**
 * Session Management Functions
 * Secure session handling with advanced security features
 */

define('AUTH_SYSTEM', true);

/**
 * Initialize secure session with optimal settings
 */
function initialize_secure_session() {
    if (session_status() === PHP_SESSION_NONE) {
        // Configure secure session settings
        ini_set('session.cookie_httponly', 1);
        ini_set('session.cookie_secure', isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on');
        ini_set('session.cookie_samesite', 'Strict');
        ini_set('session.use_strict_mode', 1);
        ini_set('session.cookie_lifetime', 0); // Session cookie expires when browser closes
        ini_set('session.gc_maxlifetime', 1800); // 30 minutes
        ini_set('session.gc_probability', 1);
        ini_set('session.gc_divisor', 100);
        
        // Start session
        session_start();
    }
}

/**
 * Regenerate session ID for security
 */
function regenerate_session_id($delete_old = true) {
    session_regenerate_id($delete_old);
}

/**
 * Check if session is valid and secure
 */
function validate_session() {
    // Check basic session integrity
    if (!isset($_SESSION['created_at'])) {
        return false;
    }
    
    // Check session timeout
    if (time() - $_SESSION['created_at'] > 1800) { // 30 minutes
        return false;
    }
    
    // Validate session token if exists
    if (isset($_SESSION['session_token'])) {
        // Check for session hijacking attempts
        $user_agent = hash('sha256', $_SERVER['HTTP_USER_AGENT'] ?? '');
        $ip_address = hash('sha256', $_SERVER['REMOTE_ADDR'] ?? '');
        
        if (isset($_SESSION['user_agent_hash']) && $_SESSION['user_agent_hash'] !== $user_agent) {
            log_security_event('user_agent_mismatch', 'Potential session hijacking detected');
            return false;
        }
        
        if (isset($_SESSION['ip_hash']) && $_SESSION['ip_hash'] !== $ip_address) {
            // Log IP change but don't immediately invalidate (could be legitimate)
            log_activity($_SESSION['user_id'] ?? 0, 'ip_change', 'IP address changed');
        }
    }
    
    return true;
}

/**
 * Extend session validity
 */
function extend_session() {
    if (isset($_SESSION['created_at'])) {
        $_SESSION['created_at'] = time();
        $_SESSION['last_activity'] = time();
        return true;
    }
    return false;
}

/**
 * Destroy session completely
 */
function destroy_session() {
    // Clear all session data
    $_SESSION = array();
    
    // Delete session cookie
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time() - 42000, '/');
    }
    
    // Destroy session
    session_destroy();
    
    // Start new session for flash messages
    session_start();
}

/**
 * Set session value with validation
 */
function set_session_value($key, $value, $validate = true) {
    if ($validate && !is_safe_session_key($key)) {
        return false;
    }
    
    $_SESSION[$key] = $value;
    return true;
}

/**
 * Get session value safely
 */
function get_session_value($key, $default = null) {
    return $_SESSION[$key] ?? $default;
}

/**
 * Delete session value
 */
function delete_session_value($key) {
    unset($_SESSION[$key]);
}

/**
 * Check if session key is safe
 */
function is_safe_session_key($key) {
    return preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $key);
}

/**
 * Flash message functionality
 */
function set_flash_message($type, $message) {
    $_SESSION['flash'][$type] = $message;
}

/**
 * Get and clear flash message
 */
function get_flash_message($type) {
    $message = $_SESSION['flash'][$type] ?? null;
    unset($_SESSION['flash'][$type]);
    return $message;
}

/**
 * Check if flash message exists
 */
function has_flash_message($type) {
    return isset($_SESSION['flash'][$type]);
}

/**
 * Session-based CSRF protection
 */
function generate_session_csrf_token() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        $_SESSION['csrf_token_time'] = time();
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify session CSRF token
 */
function verify_session_csrf_token($token) {
    if (!isset($_SESSION['csrf_token']) || !isset($_SESSION['csrf_token_time'])) {
        return false;
    }
    
    // Check if token is too old (1 hour)
    if (time() - $_SESSION['csrf_token_time'] > 3600) {
        unset($_SESSION['csrf_token']);
        unset($_SESSION['csrf_token_time']);
        return false;
    }
    
    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Get session information for debugging
 */
function get_session_info() {
    return [
        'session_id' => session_id(),
        'session_name' => session_name(),
        'session_status' => session_status(),
        'session_cache_limiter' => session_cache_limiter(),
        'session_cache_expire' => session_cache_expire(),
        'session_save_path' => session_save_path(),
        'session_data_size' => strlen(serialize($_SESSION)),
        'last_activity' => $_SESSION['last_activity'] ?? 'never',
        'created_at' => $_SESSION['created_at'] ?? 'never',
        'user_logged_in' => isset($_SESSION['user_id']),
        'session_variables' => array_keys($_SESSION)
    ];
}

/**
 * Clean up expired sessions (should be called periodically)
 */
function cleanup_expired_sessions() {
    if (session_status() === PHP_SESSION_ACTIVE) {
        // This is handled automatically by PHP's garbage collection
        // but we can force a collection attempt
        if (mt_rand(1, 100) === 1) { // 1% chance
            session_gc();
        }
    }
}

/**
 * Log security events related to sessions
 */
function log_security_event($event_type, $description) {
    $log_entry = [
        'timestamp' => date('Y-m-d H:i:s'),
        'event_type' => $event_type,
        'description' => $description,
        'session_id' => session_id(),
        'user_id' => $_SESSION['user_id'] ?? 'anonymous',
        'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
        'request_uri' => $_SERVER['REQUEST_URI'] ?? 'unknown'
    ];
    
    error_log("SESSION SECURITY: " . json_encode($log_entry));
}

/**
 * Rate limiting using sessions
 */
function check_rate_limit($action, $max_attempts = 5, $time_window = 300) {
    $key = "rate_limit_{$action}";
    $current_time = time();
    
    if (!isset($_SESSION[$key])) {
        $_SESSION[$key] = [];
    }
    
    // Clean old attempts
    $_SESSION[$key] = array_filter($_SESSION[$key], function($timestamp) use ($current_time, $time_window) {
        return ($current_time - $timestamp) < $time_window;
    });
    
    // Check if limit exceeded
    if (count($_SESSION[$key]) >= $max_attempts) {
        return false;
    }
    
    // Add current attempt
    $_SESSION[$key][] = $current_time;
    return true;
}

/**
 * Get rate limit status
 */
function get_rate_limit_status($action, $max_attempts = 5, $time_window = 300) {
    $key = "rate_limit_{$action}";
    $current_time = time();
    
    if (!isset($_SESSION[$key])) {
        return [
            'remaining_attempts' => $max_attempts,
            'reset_time' => $current_time + $time_window,
            'blocked' => false
        ];
    }
    
    // Clean old attempts
    $valid_attempts = array_filter($_SESSION[$key], function($timestamp) use ($current_time, $time_window) {
        return ($current_time - $timestamp) < $time_window;
    });
    
    $_SESSION[$key] = $valid_attempts;
    
    $remaining = max(0, $max_attempts - count($valid_attempts));
    $reset_time = !empty($valid_attempts) ? max($valid_attempts) + $time_window : $current_time + $time_window;
    
    return [
        'remaining_attempts' => $remaining,
        'reset_time' => $reset_time,
        'blocked' => $remaining === 0
    ];
}

/**
 * Session fingerprinting for additional security
 */
function create_session_fingerprint() {
    $fingerprint_components = [
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
        'accept_language' => $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '',
        'accept_encoding' => $_SERVER['HTTP_ACCEPT_ENCODING'] ?? '',
        'remote_addr' => $_SERVER['REMOTE_ADDR'] ?? ''
    ];
    
    return hash('sha256', serialize($fingerprint_components));
}

/**
 * Verify session fingerprint
 */
function verify_session_fingerprint() {
    if (!isset($_SESSION['fingerprint'])) {
        $_SESSION['fingerprint'] = create_session_fingerprint();
        return true; // First time, accept
    }
    
    $current_fingerprint = create_session_fingerprint();
    $stored_fingerprint = $_SESSION['fingerprint'];
    
    if ($current_fingerprint !== $stored_fingerprint) {
        log_security_event('fingerprint_mismatch', 'Session fingerprint mismatch detected');
        return false;
    }
    
    return true;
}

/**
 * Initialize session with security features
 */
function setup_secure_session($user_id = null, $username = null, $role = null) {
    // Set session creation time
    $_SESSION['created_at'] = time();
    $_SESSION['last_activity'] = time();
    
    // Set security features if user is logged in
    if ($user_id && $username && $role) {
        $_SESSION['user_id'] = $user_id;
        $_SESSION['username'] = $username;
        $_SESSION['role'] = $role;
        
        // Generate session token
        $_SESSION['session_token'] = bin2hex(random_bytes(32));
        
        // Store fingerprint
        $_SESSION['fingerprint'] = create_session_fingerprint();
        
        // Store hashed security data
        $_SESSION['user_agent_hash'] = hash('sha256', $_SERVER['HTTP_USER_AGENT'] ?? '');
        $_SESSION['ip_hash'] = hash('sha256', $_SERVER['REMOTE_ADDR'] ?? '');
    }
    
    // Regenerate session ID
    regenerate_session_id(true);
    
    return true;
}

/**
 * Get session statistics
 */
function get_session_statistics() {
    $stats = [
        'total_variables' => count($_SESSION),
        'session_size' => strlen(serialize($_SESSION)),
        'is_secure' => isset($_SESSION['session_token']),
        'has_user' => isset($_SESSION['user_id']),
        'user_role' => $_SESSION['role'] ?? null,
        'session_age' => isset($_SESSION['created_at']) ? time() - $_SESSION['created_at'] : 0,
        'idle_time' => isset($_SESSION['last_activity']) ? time() - $_SESSION['last_activity'] : 0,
        'csrf_protection' => isset($_SESSION['csrf_token']),
        'rate_limits' => []
    ];
    
    // Count rate limits
    foreach ($_SESSION as $key => $value) {
        if (strpos($key, 'rate_limit_') === 0 && is_array($value)) {
            $stats['rate_limits'][] = [
                'action' => substr($key, 11), // Remove 'rate_limit_' prefix
                'attempts' => count($value)
            ];
        }
    }
    
    return $stats;
}

/**
 * Reset session data (keep user logged in but clear other data)
 */
function reset_session_data() {
    $user_data = [];
    
    if (isset($_SESSION['user_id'])) {
        $user_data['user_id'] = $_SESSION['user_id'];
        $user_data['username'] = $_SESSION['username'];
        $user_data['role'] = $_SESSION['role'];
        $user_data['session_token'] = $_SESSION['session_token'];
    }
    
    // Reset session but keep user data
    $_SESSION = $user_data;
    $_SESSION['reset_at'] = time();
    
    // Extend session
    extend_session();
    
    return true;
}
?>