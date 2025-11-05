-- Authentication System Database Schema
-- Complete database structure for secure user authentication

-- Create database (optional - use your existing database)
-- CREATE DATABASE IF NOT EXISTS auth_system CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
-- USE auth_system;

-- Users table - Core user information
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL COMMENT 'Hashed password using password_hash()',
    full_name VARCHAR(100) NOT NULL,
    role ENUM('admin', 'teacher', 'student') NOT NULL DEFAULT 'student',
    is_active TINYINT(1) NOT NULL DEFAULT 1 COMMENT '0 = inactive, 1 = active',
    is_verified TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Email verification status',
    failed_attempts INT NOT NULL DEFAULT 0 COMMENT 'Failed login attempts count',
    last_failed_attempt TIMESTAMP NULL DEFAULT NULL COMMENT 'Timestamp of last failed attempt',
    last_login TIMESTAMP NULL DEFAULT NULL COMMENT 'Last successful login timestamp',
    password_changed_at TIMESTAMP NULL DEFAULT NULL COMMENT 'When password was last changed',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_username (username),
    INDEX idx_email (email),
    INDEX idx_role (role),
    INDEX idx_active (is_active),
    INDEX idx_failed_attempts (failed_attempts),
    INDEX idx_last_login (last_login)
) ENGINE=InnoDB COMMENT='Core users table with authentication data';

-- User profiles - Extended user information
CREATE TABLE IF NOT EXISTS user_profiles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    avatar_url VARCHAR(255) NULL DEFAULT NULL,
    phone VARCHAR(20) NULL DEFAULT NULL,
    date_of_birth DATE NULL DEFAULT NULL,
    gender ENUM('male', 'female', 'other') NULL DEFAULT NULL,
    address TEXT NULL DEFAULT NULL,
    bio TEXT NULL DEFAULT NULL,
    preferences JSON NULL DEFAULT NULL COMMENT 'User preferences and settings',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id)
) ENGINE=InnoDB COMMENT='Extended user profile information';

-- User sessions - Track active sessions
CREATE TABLE IF NOT EXISTS user_sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    session_id VARCHAR(128) NOT NULL,
    session_token VARCHAR(255) NOT NULL,
    ip_address VARCHAR(45) NOT NULL COMMENT 'IPv4 or IPv6 address',
    user_agent TEXT NULL DEFAULT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1 COMMENT 'Session active status',
    last_activity TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP NOT NULL COMMENT 'Session expiration timestamp',
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_session_id (session_id),
    INDEX idx_session_token (session_token),
    INDEX idx_user_id (user_id),
    INDEX idx_active (is_active),
    INDEX idx_expires (expires_at)
) ENGINE=InnoDB COMMENT='Active user sessions tracking';

-- Password reset tokens
CREATE TABLE IF NOT EXISTS password_resets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    token VARCHAR(255) UNIQUE NOT NULL,
    ip_address VARCHAR(45) NOT NULL,
    user_agent TEXT NULL DEFAULT NULL,
    is_used TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Token usage status',
    used_at TIMESTAMP NULL DEFAULT NULL,
    expires_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_token (token),
    INDEX idx_user_id (user_id),
    INDEX idx_expires (expires_at)
) ENGINE=InnoDB COMMENT='Password reset tokens';

-- Email verification tokens
CREATE TABLE IF NOT EXISTS email_verifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    email VARCHAR(100) NOT NULL,
    token VARCHAR(255) UNIQUE NOT NULL,
    ip_address VARCHAR(45) NOT NULL,
    is_verified TINYINT(1) NOT NULL DEFAULT 0,
    verified_at TIMESTAMP NULL DEFAULT NULL,
    expires_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_token (token),
    INDEX idx_user_id (user_id),
    INDEX idx_email (email),
    INDEX idx_expires (expires_at)
) ENGINE=InnoDB COMMENT='Email verification tokens';

-- Activity logs - Track user activities
CREATE TABLE IF NOT EXISTS activity_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL DEFAULT NULL,
    action VARCHAR(50) NOT NULL,
    description TEXT NULL DEFAULT NULL,
    ip_address VARCHAR(45) NOT NULL,
    user_agent TEXT NULL DEFAULT NULL,
    request_data JSON NULL DEFAULT NULL COMMENT 'Additional request data',
    severity ENUM('info', 'warning', 'error', 'critical') DEFAULT 'info',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_user_id (user_id),
    INDEX idx_action (action),
    INDEX idx_severity (severity),
    INDEX idx_created_at (created_at),
    INDEX idx_user_action (user_id, action)
) ENGINE=InnoDB COMMENT='User activity and security logs';

-- Security events - Track security-related events
CREATE TABLE IF NOT EXISTS security_events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL DEFAULT NULL,
    event_type VARCHAR(50) NOT NULL,
    ip_address VARCHAR(45) NOT NULL,
    user_agent TEXT NULL DEFAULT NULL,
    session_id VARCHAR(128) NULL DEFAULT NULL,
    details JSON NULL DEFAULT NULL COMMENT 'Event-specific details',
    severity ENUM('low', 'medium', 'high', 'critical') DEFAULT 'medium',
    status ENUM('open', 'investigated', 'resolved', 'false_positive') DEFAULT 'open',
    resolved_at TIMESTAMP NULL DEFAULT NULL,
    resolved_by INT NULL DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (resolved_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_event_type (event_type),
    INDEX idx_severity (severity),
    INDEX idx_status (status),
    INDEX idx_ip_address (ip_address),
    INDEX idx_user_id (user_id),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB COMMENT='Security events and incidents tracking';

-- Login attempts - Track authentication attempts
CREATE TABLE IF NOT EXISTS login_attempts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ip_address VARCHAR(45) NOT NULL,
    username VARCHAR(100) NULL DEFAULT NULL,
    email VARCHAR(100) NULL DEFAULT NULL,
    user_agent TEXT NULL DEFAULT NULL,
    success TINYINT(1) NOT NULL DEFAULT 0 COMMENT '1 = successful, 0 = failed',
    failure_reason VARCHAR(100) NULL DEFAULT NULL,
    session_id VARCHAR(128) NULL DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_ip_address (ip_address),
    INDEX idx_username (username),
    INDEX idx_email (email),
    INDEX idx_success (success),
    INDEX idx_created_at (created_at),
    INDEX idx_ip_success (ip_address, success)
) ENGINE=InnoDB COMMENT='Login attempt tracking for security';

-- API keys - For API authentication (optional)
CREATE TABLE IF NOT EXISTS api_keys (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    api_key VARCHAR(255) UNIQUE NOT NULL,
    api_secret VARCHAR(255) NOT NULL,
    name VARCHAR(100) NOT NULL COMMENT 'User-defined name for the API key',
    permissions JSON NULL DEFAULT NULL COMMENT 'API permissions and scopes',
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    last_used_at TIMESTAMP NULL DEFAULT NULL,
    expires_at TIMESTAMP NULL DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_api_key (api_key),
    INDEX idx_user_id (user_id),
    INDEX idx_active (is_active),
    INDEX idx_expires (expires_at)
) ENGINE=InnoDB COMMENT='API keys for external access';

-- Remember me tokens - For "Remember Me" functionality
CREATE TABLE IF NOT EXISTS remember_tokens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    token VARCHAR(255) UNIQUE NOT NULL,
    ip_address VARCHAR(45) NOT NULL,
    user_agent TEXT NULL DEFAULT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    expires_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_used_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_token (token),
    INDEX idx_user_id (user_id),
    INDEX idx_active (is_active),
    INDEX idx_expires (expires_at)
) ENGINE=InnoDB COMMENT='Remember me tokens';

-- System settings - Application configuration
CREATE TABLE IF NOT EXISTS system_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT NOT NULL,
    description TEXT NULL DEFAULT NULL,
    type ENUM('string', 'integer', 'boolean', 'json') DEFAULT 'string',
    is_encrypted TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Whether the value is encrypted',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    updated_by INT NULL DEFAULT NULL,
    
    FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_setting_key (setting_key)
) ENGINE=InnoDB COMMENT='System configuration settings';

-- Insert default system settings
INSERT INTO system_settings (setting_key, setting_value, description, type) VALUES
('site_name', 'Secure Authentication System', 'Website name', 'string'),
('max_login_attempts', '5', 'Maximum failed login attempts before lockout', 'integer'),
('login_timeout', '900', 'Account lockout duration in seconds (15 minutes)', 'integer'),
('session_timeout', '1800', 'Session timeout in seconds (30 minutes)', 'integer'),
('registration_enabled', 'true', 'Allow new user registrations', 'boolean'),
('email_verification_required', 'true', 'Require email verification for new accounts', 'boolean'),
('password_min_length', '8', 'Minimum password length', 'integer'),
('csrf_token_expiry', '3600', 'CSRF token expiry in seconds (1 hour)', 'integer'),
('rate_limit_enabled', 'true', 'Enable rate limiting for login attempts', 'boolean'),
('remember_me_duration', '2592000', 'Remember me token duration in seconds (30 days)', 'integer');

-- Create default admin user (username: admin, password: admin123!)
-- IMPORTANT: Change this password immediately after first login!
INSERT INTO users (username, email, password, full_name, role, is_active, is_verified, password_changed_at) VALUES
('admin', 'admin@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'System Administrator', 'admin', 1, 1, NOW());

-- Create sample teacher user
INSERT INTO users (username, email, password, full_name, role, is_active, is_verified, password_changed_at) VALUES
('teacher1', 'teacher@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'John Teacher', 'teacher', 1, 1, NOW());

-- Create sample student user
INSERT INTO users (username, email, password, full_name, role, is_active, is_verified, password_changed_at) VALUES
('student1', 'student@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Jane Student', 'student', 1, 1, NOW());

-- Create indexes for better performance
CREATE INDEX idx_users_active_role ON users(is_active, role);
CREATE INDEX idx_users_login_attempts ON users(failed_attempts, last_failed_attempt);
CREATE INDEX idx_activity_logs_user_action_date ON activity_logs(user_id, action, created_at);
CREATE INDEX idx_login_attempts_ip_time ON login_attempts(ip_address, created_at);

-- Views for easier querying
CREATE VIEW active_users AS
SELECT u.id, u.username, u.email, u.full_name, u.role, u.last_login
FROM users u
WHERE u.is_active = 1 AND u.is_verified = 1;

CREATE VIEW recent_activities AS
SELECT 
    u.username,
    al.action,
    al.description,
    al.ip_address,
    al.created_at
FROM activity_logs al
LEFT JOIN users u ON al.user_id = u.id
WHERE al.created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
ORDER BY al.created_at DESC;

CREATE VIEW security_summary AS
SELECT 
    event_type,
    COUNT(*) as total_events,
    COUNT(CASE WHEN severity = 'critical' THEN 1 END) as critical_events,
    COUNT(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR) THEN 1 END) as recent_events
FROM security_events
GROUP BY event_type;

-- Triggers for automatic cleanup
DELIMITER //

CREATE EVENT IF NOT EXISTS cleanup_expired_tokens
ON SCHEDULE EVERY 1 HOUR
DO
BEGIN
    -- Clean up expired password reset tokens
    DELETE FROM password_resets WHERE expires_at < NOW();
    
    -- Clean up expired email verification tokens
    DELETE FROM email_verifications WHERE expires_at < NOW() AND is_verified = 0;
    
    -- Clean up expired remember tokens
    DELETE FROM remember_tokens WHERE expires_at < NOW();
    
    -- Clean up inactive sessions older than 1 day
    DELETE FROM user_sessions WHERE expires_at < NOW() OR (last_activity < DATE_SUB(NOW(), INTERVAL 1 DAY) AND is_active = 0);
    
    -- Clean up old activity logs (older than 30 days)
    DELETE FROM activity_logs WHERE created_at < DATE_SUB(NOW(), INTERVAL 30 DAY);
    
    -- Clean up old login attempts (older than 7 days)
    DELETE FROM login_attempts WHERE created_at < DATE_SUB(NOW(), INTERVAL 7 DAY);
END;//

CREATE TRIGGER IF NOT EXISTS update_user_last_login
AFTER UPDATE ON users
FOR EACH ROW
BEGIN
    IF NEW.last_login != OLD.last_login AND NEW.last_login IS NOT NULL THEN
        INSERT INTO activity_logs (user_id, action, description, ip_address, severity)
        VALUES (NEW.id, 'login', 'User logged in successfully', 'system', 'info');
    END IF;
END;//

DELIMITER ;

-- Performance optimization: Analyze tables
ANALYZE TABLE users, user_profiles, user_sessions, password_resets, 
              email_verifications, activity_logs, security_events, 
              login_attempts, api_keys, remember_tokens, system_settings;