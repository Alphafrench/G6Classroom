<?php
/**
 * Session Management Class
 * 
 * This class handles secure session management including session creation,
 * authentication, token management, and security features like CSRF protection
 * and session hijacking prevention.
 * 
 * @package EmployeeManager
 * @version 1.0
 * @author Development Team
 */

require_once 'class.Database.php';
require_once 'functions.php';

class Session {
    /**
     * Database instance
     * @var Database
     */
    private $db;
    
    /**
     * Sessions table name
     * @var string
     */
    private $table = 'user_sessions';
    
    /**
     * Session configuration
     * @var array
     */
    private $config = [
        'session_name' => 'EMPLOYEE_MANAGER_SESSION',
        'lifetime' => 3600, // 1 hour in seconds
        'max_concurrent_sessions' => 3,
        'regenerate_on_login' => true,
        'require_ip_match' => false,
        'require_user_agent_match' => false
    ];
    
    /**
     * Current user data
     * @var array|null
     */
    private $currentUser = null;
    
    /**
     * Session ID
     * @var string|null
     */
    private $sessionId = null;
    
    /**
     * Constructor
     * 
     * @param Database|null $db Database instance (optional)
     * @param array $config Session configuration
     */
    public function __construct($db = null, $config = []) {
        $this->db = $db ?: getDatabase();
        $this->config = array_merge($this->config, $config);
        
        $this->startSession();
    }
    
    /**
     * Start session with security measures
     */
    private function startSession() {
        // Configure PHP session settings for security
        ini_set('session.cookie_httponly', 1);
        ini_set('session.use_only_cookies', 1);
        ini_set('session.cookie_secure', isset($_SERVER['HTTPS'])); // HTTPS only in production
        
        // Set session name
        session_name($this->config['session_name']);
        
        // Start session
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $this->sessionId = session_id();
        
        // Security checks and cleanup
        $this->performSecurityChecks();
        $this->cleanupExpiredSessions();
    }
    
    /**
     * Authenticate user and create session
     * 
     * @param array $credentials User credentials (email, password)
     * @param bool $rememberMe Whether to create persistent session
     * @return array Result with 'success', 'user', 'message', 'errors'
     */
    public function login($credentials, $rememberMe = false) {
        try {
            // Validate input
            if (empty($credentials['email']) || empty($credentials['password'])) {
                return [
                    'success' => false,
                    'errors' => ['Email and password are required']
                ];
            }
            
            $email = sanitizeInput($credentials['email']);
            
            // Get user from database
            $user = $this->db->fetchRow(
                "SELECT id, email, password_hash, first_name, last_name, is_active, role_id FROM users WHERE email = ?",
                [$email]
            );
            
            if (!$user) {
                logError("Login attempt with non-existent email", ['email' => $email]);
                return [
                    'success' => false,
                    'errors' => ['Invalid email or password']
                ];
            }
            
            if (!$user['is_active']) {
                logError("Login attempt with inactive account", ['email' => $email, 'user_id' => $user['id']]);
                return [
                    'success' => false,
                    'errors' => ['Account is inactive']
                ];
            }
            
            // Verify password
            if (!verifyPassword($credentials['password'], $user['password_hash'])) {
                logError("Failed login attempt - invalid password", ['email' => $email, 'user_id' => $user['id']]);
                return [
                    'success' => false,
                    'errors' => ['Invalid email or password']
                ];
            }
            
            // Check concurrent sessions limit
            if ($this->config['max_concurrent_sessions'] > 0) {
                $activeSessions = $this->getActiveSessionCount($user['id']);
                
                if ($activeSessions >= $this->config['max_concurrent_sessions']) {
                    logError("Login blocked - maximum concurrent sessions reached", [
                        'user_id' => $user['id'],
                        'active_sessions' => $activeSessions
                    ]);
                    
                    return [
                        'success' => false,
                        'errors' => ['Maximum number of concurrent sessions reached. Please logout from other devices.']
                    ];
                }
            }
            
            // Regenerate session ID for security
            if ($this->config['regenerate_on_login']) {
                $this->regenerateSessionId();
            }
            
            // Create session record
            $sessionData = [
                'user_id' => $user['id'],
                'session_id' => $this->sessionId,
                'ip_address' => getClientIP(),
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
                'login_time' => date('Y-m-d H:i:s'),
                'last_activity' => date('Y-m-d H:i:s'),
                'expires_at' => date('Y-m-d H:i:s', time() + $this->config['lifetime']),
                'is_active' => 1,
                'remember_token' => $rememberMe ? $this->generateRememberToken() : null,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            $sessionId = $this->db->insert($this->table, $sessionData);
            
            // Store user data in session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['first_name'] = $user['first_name'];
            $_SESSION['last_name'] = $user['last_name'];
            $_SESSION['role_id'] = $user['role_id'];
            $_SESSION['session_db_id'] = $sessionId;
            $_SESSION['login_time'] = $sessionData['login_time'];
            $_SESSION['csrf_token'] = generateCSRFToken();
            
            // Set remember me cookie if requested
            if ($rememberMe && $sessionData['remember_token']) {
                $this->setRememberCookie($sessionData['remember_token']);
            }
            
            // Load current user data
            $this->currentUser = $user;
            
            logError("User logged in successfully", [
                'user_id' => $user['id'],
                'email' => $user['email'],
                'session_id' => $sessionId
            ]);
            
            return [
                'success' => true,
                'user' => $user,
                'message' => 'Login successful',
                'session_id' => $sessionId
            ];
            
        } catch (Exception $e) {
            logError("Login failed: " . $e->getMessage(), ['email' => $credentials['email'] ?? '']);
            return [
                'success' => false,
                'errors' => ['Login failed: ' . $e->getMessage()]
            ];
        }
    }
    
    /**
     * Logout user and destroy session
     * 
     * @return array Result with 'success', 'message'
     */
    public function logout() {
        try {
            if (!$this->isLoggedIn()) {
                return [
                    'success' => true,
                    'message' => 'Already logged out'
                ];
            }
            
            $userId = $this->getCurrentUserId();
            
            // Update session record
            if (isset($_SESSION['session_db_id'])) {
                $this->db->update(
                    $this->table,
                    [
                        'logout_time' => date('Y-m-d H:i:s'),
                        'is_active' => 0,
                        'updated_at' => date('Y-m-d H:i:s')
                    ],
                    'id = ?',
                    [$_SESSION['session_db_id']]
                );
            }
            
            // Destroy PHP session
            session_destroy();
            
            // Clear remember me cookie
            $this->clearRememberCookie();
            
            logError("User logged out successfully", ['user_id' => $userId]);
            
            return [
                'success' => true,
                'message' => 'Logged out successfully'
            ];
            
        } catch (Exception $e) {
            logError("Logout failed: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Logout failed'
            ];
        }
    }
    
    /**
     * Check if user is logged in
     * 
     * @return bool True if logged in, false otherwise
     */
    public function isLoggedIn() {
        return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
    }
    
    /**
     * Get current user data
     * 
     * @return array|null User data or null if not logged in
     */
    public function getCurrentUser() {
        if ($this->currentUser !== null) {
            return $this->currentUser;
        }
        
        if (!$this->isLoggedIn()) {
            return null;
        }
        
        try {
            $userId = $this->getCurrentUserId();
            
            // Verify session is still active in database
            if (!$this->isSessionActive()) {
                $this->logout();
                return null;
            }
            
            // Get fresh user data
            $user = $this->db->fetchRow(
                "SELECT id, email, first_name, last_name, role_id, is_active FROM users WHERE id = ?",
                [$userId]
            );
            
            $this->currentUser = $user;
            return $user;
            
        } catch (Exception $e) {
            logError("Failed to get current user: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Get current user ID
     * 
     * @return int|null User ID or null if not logged in
     */
    public function getCurrentUserId() {
        return $_SESSION['user_id'] ?? null;
    }
    
    /**
     * Check if current session is active
     * 
     * @return bool True if session is active, false otherwise
     */
    private function isSessionActive() {
        try {
            if (!isset($_SESSION['session_db_id'])) {
                return false;
            }
            
            $session = $this->db->fetchRow(
                "SELECT * FROM {$this->table} WHERE id = ? AND is_active = 1 AND expires_at > NOW()",
                [$_SESSION['session_db_id']]
            );
            
            if (!$session) {
                return false;
            }
            
            // Update last activity
            $this->db->update(
                $this->table,
                ['last_activity' => date('Y-m-d H:i:s')],
                'id = ?',
                [$_SESSION['session_db_id']]
            );
            
            return true;
            
        } catch (Exception $e) {
            logError("Failed to check session activity: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Perform security checks on session
     */
    private function performSecurityChecks() {
        // Check if session exists in database
        if (isset($_SESSION['session_db_id'])) {
            $session = $this->db->fetchRow(
                "SELECT * FROM {$this->table} WHERE id = ?",
                [$_SESSION['session_db_id']]
            );
            
            if (!$session || !$session['is_active'] || strtotime($session['expires_at']) <= time()) {
                // Session is invalid or expired
                $this->logout();
                return;
            }
            
            // Check IP address if required
            if ($this->config['require_ip_match'] && $session['ip_address'] !== getClientIP()) {
                logError("Session hijacking attempt detected - IP mismatch", [
                    'expected_ip' => $session['ip_address'],
                    'actual_ip' => getClientIP(),
                    'user_id' => $session['user_id']
                ]);
                $this->logout();
                return;
            }
            
            // Check user agent if required
            if ($this->config['require_user_agent_match'] && $session['user_agent'] !== ($_SERVER['HTTP_USER_AGENT'] ?? '')) {
                logError("Session hijacking attempt detected - User agent mismatch", [
                    'user_id' => $session['user_id']
                ]);
                $this->logout();
                return;
            }
        }
        
        // Check remember me token
        $this->checkRememberMe();
    }
    
    /**
     * Check and validate remember me token
     */
    private function checkRememberMe() {
        if (isset($_COOKIE['remember_token'])) {
            $token = $_COOKIE['remember_token'];
            
            $session = $this->db->fetchRow(
                "SELECT * FROM {$this->table} WHERE remember_token = ? AND is_active = 1 AND expires_at > NOW()",
                [$token]
            );
            
            if ($session) {
                // Restore session data
                $user = $this->db->fetchRow(
                    "SELECT id, email, first_name, last_name, role_id FROM users WHERE id = ?",
                    [$session['user_id']]
                );
                
                if ($user) {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['email'] = $user['email'];
                    $_SESSION['first_name'] = $user['first_name'];
                    $_SESSION['last_name'] = $user['last_name'];
                    $_SESSION['role_id'] = $user['role_id'];
                    $_SESSION['session_db_id'] = $session['id'];
                    $_SESSION['login_time'] = $session['login_time'];
                    $_SESSION['csrf_token'] = generateCSRFToken();
                    
                    $this->currentUser = $user;
                    
                    // Update session activity
                    $this->db->update(
                        $this->table,
                        ['last_activity' => date('Y-m-d H:i:s')],
                        'id = ?',
                        [$session['id']]
                    );
                }
            } else {
                // Invalid remember token, clear cookie
                $this->clearRememberCookie();
            }
        }
    }
    
    /**
     * Get active session count for user
     * 
     * @param int $userId User ID
     * @return int Number of active sessions
     */
    private function getActiveSessionCount($userId) {
        try {
            $result = $this->db->fetchRow(
                "SELECT COUNT(*) as count FROM {$this->table} WHERE user_id = ? AND is_active = 1 AND expires_at > NOW()",
                [$userId]
            );
            return (int) $result['count'];
        } catch (Exception $e) {
            return 0;
        }
    }
    
    /**
     * Regenerate session ID
     * 
     * @return bool True if successful, false otherwise
     */
    public function regenerateSessionId() {
        session_regenerate_id(true);
        $this->sessionId = session_id();
        
        // Update session ID in database if session exists
        if (isset($_SESSION['session_db_id'])) {
            try {
                $this->db->update(
                    $this->table,
                    ['session_id' => $this->sessionId],
                    'id = ?',
                    [$_SESSION['session_db_id']]
                );
                return true;
            } catch (Exception $e) {
                logError("Failed to update session ID in database: " . $e->getMessage());
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Generate remember me token
     * 
     * @return string Remember me token
     */
    private function generateRememberToken() {
        return hash('sha256', bin2hex(random_bytes(32)) . time());
    }
    
    /**
     * Set remember me cookie
     * 
     * @param string $token Remember me token
     */
    private function setRememberCookie($token) {
        setcookie('remember_token', $token, time() + (86400 * 30), '/', '', true, true); // 30 days
    }
    
    /**
     * Clear remember me cookie
     */
    private function clearRememberCookie() {
        setcookie('remember_token', '', time() - 3600, '/', '', true, true);
    }
    
    /**
     * Cleanup expired sessions
     */
    private function cleanupExpiredSessions() {
        try {
            // Deactivate expired sessions
            $this->db->update(
                $this->table,
                ['is_active' => 0],
                'expires_at < NOW()',
                []
            );
            
            // Delete very old sessions (older than 7 days)
            $this->db->delete(
                $this->table,
                'created_at < DATE_SUB(NOW(), INTERVAL 7 DAY)',
                []
            );
            
        } catch (Exception $e) {
            logError("Failed to cleanup expired sessions: " . $e->getMessage());
        }
    }
    
    /**
     * Get all sessions for current user
     * 
     * @return array Array of user sessions
     */
    public function getUserSessions() {
        try {
            if (!$this->isLoggedIn()) {
                return [];
            }
            
            return $this->db->fetchAll(
                "SELECT * FROM {$this->table} WHERE user_id = ? ORDER BY login_time DESC",
                [$this->getCurrentUserId()]
            );
            
        } catch (Exception $e) {
            logError("Failed to get user sessions: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Terminate specific session
     * 
     * @param int $sessionId Session database ID
     * @return array Result with 'success', 'message'
     */
    public function terminateSession($sessionId) {
        try {
            if (!$this->isLoggedIn()) {
                return [
                    'success' => false,
                    'message' => 'Not authenticated'
                ];
            }
            
            // Verify session belongs to current user
            $session = $this->db->fetchRow(
                "SELECT * FROM {$this->table} WHERE id = ? AND user_id = ?",
                [$sessionId, $this->getCurrentUserId()]
            );
            
            if (!$session) {
                return [
                    'success' => false,
                    'message' => 'Session not found'
                ];
            }
            
            // Deactivate session
            $this->db->update(
                $this->table,
                ['is_active' => 0, 'logout_time' => date('Y-m-d H:i:s')],
                'id = ?',
                [$sessionId]
            );
            
            // If terminating current session, logout
            if (isset($_SESSION['session_db_id']) && $_SESSION['session_db_id'] == $sessionId) {
                $this->logout();
            }
            
            logError("Session terminated by user", [
                'terminated_session_id' => $sessionId,
                'user_id' => $this->getCurrentUserId()
            ]);
            
            return [
                'success' => true,
                'message' => 'Session terminated successfully'
            ];
            
        } catch (Exception $e) {
            logError("Failed to terminate session: " . $e->getMessage(), ['session_id' => $sessionId]);
            return [
                'success' => false,
                'message' => 'Failed to terminate session'
            ];
        }
    }
    
    /**
     * Get session statistics
     * 
     * @return array Session statistics
     */
    public function getSessionStatistics() {
        try {
            $stats = [];
            
            // Total active sessions
            $activeResult = $this->db->fetchRow(
                "SELECT COUNT(*) as count FROM {$this->table} WHERE is_active = 1 AND expires_at > NOW()"
            );
            $stats['active_sessions'] = (int) $activeResult['count'];
            
            // Sessions by user
            $userSessions = $this->db->fetchAll(
                "SELECT u.email, COUNT(s.id) as session_count 
                 FROM {$this->table} s 
                 JOIN users u ON s.user_id = u.id 
                 WHERE s.is_active = 1 AND s.expires_at > NOW() 
                 GROUP BY s.user_id 
                 ORDER BY session_count DESC 
                 LIMIT 10"
            );
            $stats['top_users'] = $userSessions;
            
            // Recent logins (last 24 hours)
            $recentResult = $this->db->fetchRow(
                "SELECT COUNT(*) as count FROM {$this->table} WHERE login_time > DATE_SUB(NOW(), INTERVAL 24 HOUR)"
            );
            $stats['recent_logins'] = (int) $recentResult['count'];
            
            return $stats;
            
        } catch (Exception $e) {
            logError("Failed to get session statistics: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Check if current user has permission
     * 
     * @param string $permission Permission name
     * @return bool True if has permission, false otherwise
     */
    public function hasPermission($permission) {
        $user = $this->getCurrentUser();
        if (!$user) {
            return false;
        }
        
        // Simple role-based permission check (expand as needed)
        $rolePermissions = [
            'admin' => ['*'], // Admin has all permissions
            'manager' => ['view_employees', 'edit_employees', 'view_attendance', 'edit_attendance'],
            'employee' => ['view_own_data', 'clock_in_out']
        ];
        
        $userRole = $this->getUserRole($user['role_id']);
        $permissions = $rolePermissions[$userRole] ?? [];
        
        return in_array('*', $permissions) || in_array($permission, $permissions);
    }
    
    /**
     * Get user role name by ID
     * 
     * @param int $roleId Role ID
     * @return string Role name
     */
    private function getUserRole($roleId) {
        try {
            $role = $this->db->fetchRow("SELECT name FROM roles WHERE id = ?", [$roleId]);
            return $role['name'] ?? 'employee';
        } catch (Exception $e) {
            return 'employee';
        }
    }
}
?>