-- Database Schema for Secure Authentication System
-- Run this SQL to create the required tables

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'employee') DEFAULT 'employee',
    is_active BOOLEAN DEFAULT TRUE,
    failed_attempts INT DEFAULT 0,
    last_failed_attempt TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_username (username),
    INDEX idx_email (email),
    INDEX idx_role (role)
);

-- Activity logs table
CREATE TABLE IF NOT EXISTS activity_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    action VARCHAR(100) NOT NULL,
    description TEXT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user_id (user_id),
    INDEX idx_action (action),
    INDEX idx_created_at (created_at),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Session tracking table (optional, for additional security)
CREATE TABLE IF NOT EXISTS user_sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    session_token VARCHAR(64) UNIQUE NOT NULL,
    ip_address VARCHAR(45),
    user_agent TEXT,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_activity TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    expires_at TIMESTAMP,
    INDEX idx_user_id (user_id),
    INDEX idx_session_token (session_token),
    INDEX idx_expires_at (expires_at),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Security events table (for monitoring suspicious activity)
CREATE TABLE IF NOT EXISTS security_events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    event_type VARCHAR(50) NOT NULL,
    severity ENUM('low', 'medium', 'high', 'critical') DEFAULT 'low',
    description TEXT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    user_id INT,
    additional_data JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_event_type (event_type),
    INDEX idx_severity (severity),
    INDEX idx_created_at (created_at),
    INDEX idx_user_id (user_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Insert sample admin user (password: admin123)
-- IMPORTANT: Change this password in production!
INSERT INTO users (username, email, password, role) 
VALUES (
    'admin', 
    'admin@example.com', 
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 
    'admin'
) ON DUPLICATE KEY UPDATE 
username = VALUES(username),
email = VALUES(email),
password = VALUES(password),
role = VALUES(role);

-- Insert sample employee user (password: employee123)
-- IMPORTANT: Change this password in production!
INSERT INTO users (username, email, password, role) 
VALUES (
    'employee', 
    'employee@example.com', 
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 
    'employee'
) ON DUPLICATE KEY UPDATE 
username = VALUES(username),
email = VALUES(email),
password = VALUES(password),
role = VALUES(role);

-- Create views for better reporting
CREATE OR REPLACE VIEW user_activity_summary AS
SELECT 
    u.id,
    u.username,
    u.email,
    u.role,
    u.is_active,
    COUNT(al.id) as total_activities,
    MAX(al.created_at) as last_activity,
    SUM(CASE WHEN al.action = 'login' THEN 1 ELSE 0 END) as login_count,
    SUM(CASE WHEN al.action = 'failed_login' THEN 1 ELSE 0 END) as failed_login_count
FROM users u
LEFT JOIN activity_logs al ON u.id = al.user_id
GROUP BY u.id, u.username, u.email, u.role, u.is_active;

-- Create indexes for better performance
CREATE INDEX idx_users_active ON users(is_active);
CREATE INDEX idx_activity_logs_user_date ON activity_logs(user_id, created_at);
CREATE INDEX idx_security_events_severity_date ON security_events(severity, created_at);

-- Create stored procedure to clean up old data
DELIMITER //

CREATE PROCEDURE CleanupOldData()
BEGIN
    -- Delete activity logs older than 30 days
    DELETE FROM activity_logs WHERE created_at < DATE_SUB(NOW(), INTERVAL 30 DAY);
    
    -- Delete inactive sessions older than 7 days
    DELETE FROM user_sessions WHERE is_active = FALSE AND last_activity < DATE_SUB(NOW(), INTERVAL 7 DAY);
    
    -- Delete security events older than 90 days for low severity
    DELETE FROM security_events WHERE severity = 'low' AND created_at < DATE_SUB(NOW(), INTERVAL 90 DAY);
    
    -- Reset failed attempts for users who haven't attempted login in 24 hours
    UPDATE users SET failed_attempts = 0, last_failed_attempt = NULL 
    WHERE failed_attempts > 0 AND last_failed_attempt < DATE_SUB(NOW(), INTERVAL 24 HOUR);
END//

DELIMITER ;

-- Schedule cleanup (adjust based on your database system)
-- For MySQL: Create event scheduler
-- SET GLOBAL event_scheduler = ON;
-- CREATE EVENT IF NOT EXISTS cleanup_old_data
-- ON SCHEDULE EVERY 1 DAY
-- DO CALL CleanupOldData();

-- Sample queries for monitoring and administration

-- Get active users in the last hour
/*
SELECT DISTINCT u.username, u.role, al.created_at
FROM users u
JOIN activity_logs al ON u.id = al.user_id
WHERE al.created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)
ORDER BY al.created_at DESC;
*/

-- Get users with failed login attempts
/*
SELECT username, email, failed_attempts, last_failed_attempt
FROM users
WHERE failed_attempts > 0
ORDER BY failed_attempts DESC;
*/

-- Get security events summary
/*
SELECT event_type, severity, COUNT(*) as count
FROM security_events
WHERE created_at > DATE_SUB(NOW(), INTERVAL 24 HOUR)
GROUP BY event_type, severity
ORDER BY severity DESC, count DESC;
*/

-- Get user login statistics for today
/*
SELECT 
    u.username,
    u.role,
    SUM(CASE WHEN al.action = 'login' THEN 1 ELSE 0 END) as logins_today,
    SUM(CASE WHEN al.action = 'failed_login' THEN 1 ELSE 0 END) as failed_attempts_today
FROM users u
LEFT JOIN activity_logs al ON u.id = al.user_id AND DATE(al.created_at) = CURDATE()
GROUP BY u.id, u.username, u.role
ORDER BY logins_today DESC;
*/

-- ==============================================
-- EMPLOYEE MANAGEMENT SYSTEM TABLES
-- ==============================================

-- Employees table for employee CRUD module
CREATE TABLE IF NOT EXISTS employees (
    id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    phone VARCHAR(20) NOT NULL,
    position VARCHAR(100) NOT NULL,
    department VARCHAR(100) NOT NULL,
    hire_date DATE NOT NULL,
    salary DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    address TEXT,
    emergency_contact VARCHAR(200),
    emergency_phone VARCHAR(20),
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Add indexes for better performance
    INDEX idx_email (email),
    INDEX idx_department (department),
    INDEX idx_position (position),
    INDEX idx_hire_date (hire_date),
    INDEX idx_created_at (created_at)
);

-- Additional indexes for better search performance
CREATE INDEX IF NOT EXISTS idx_full_name ON employees(first_name, last_name);
CREATE INDEX IF NOT EXISTS idx_search ON employees(first_name, last_name, email, position, department);

-- Create a view for easy employee statistics
CREATE OR REPLACE VIEW employee_stats AS
SELECT 
    department,
    COUNT(*) as employee_count,
    AVG(salary) as avg_salary,
    MIN(salary) as min_salary,
    MAX(salary) as max_salary,
    COUNT(CASE WHEN hire_date >= DATE_SUB(CURDATE(), INTERVAL 1 YEAR) THEN 1 END) as hires_last_year
FROM employees 
GROUP BY department;

-- Create a function to calculate years of service
DELIMITER //

CREATE FUNCTION IF NOT EXISTS get_years_of_service(hire_date DATE)
RETURNS INT
DETERMINISTIC
BEGIN
    RETURN FLOOR(DATEDIFF(CURDATE(), hire_date) / 365.25);
END //

DELIMITER ;

-- Create a view with years of service calculation
CREATE OR REPLACE VIEW employees_with_service AS
SELECT 
    *,
    get_years_of_service(hire_date) as years_of_service,
    CASE 
        WHEN get_years_of_service(hire_date) = 0 THEN 'New'
        WHEN get_years_of_service(hire_date) < 1 THEN 'Active'
        ELSE 'Veteran'
    END as employee_status
FROM employees;

-- Insert sample employee data for testing
INSERT INTO employees (first_name, last_name, email, phone, position, department, hire_date, salary, address, emergency_contact, emergency_phone, notes) VALUES
('John', 'Smith', 'john.smith@company.com', '(555) 123-4567', 'Senior Software Engineer', 'Engineering', '2020-03-15', 85000.00, '123 Main St, Anytown, ST 12345', 'Jane Smith', '(555) 123-4568', 'Lead developer on core product'),
('Sarah', 'Johnson', 'sarah.johnson@company.com', '(555) 234-5678', 'Marketing Manager', 'Marketing', '2019-07-22', 72000.00, '456 Oak Ave, Somewhere, ST 23456', 'Mike Johnson', '(555) 234-5679', 'Experienced in digital marketing campaigns'),
('Michael', 'Williams', 'michael.williams@company.com', '(555) 345-6789', 'Sales Representative', 'Sales', '2021-01-10', 55000.00, '789 Pine Rd, Elsewhere, ST 34567', 'Lisa Williams', '(555) 345-6780', 'Top performer in Q4 2021'),
('Emily', 'Brown', 'emily.brown@company.com', '(555) 456-7890', 'HR Coordinator', 'Human Resources', '2020-09-01', 48000.00, '321 Elm St, Nowhere, ST 45678', 'Tom Brown', '(555) 456-7891', 'Handles employee relations and onboarding'),
('David', 'Davis', 'david.davis@company.com', '(555) 567-8901', 'Financial Analyst', 'Finance', '2018-11-30', 68000.00, '654 Maple Dr, Anywhere, ST 56789', 'Susan Davis', '(555) 567-8902', 'Expert in budget planning and analysis'),
('Jessica', 'Wilson', 'jessica.wilson@company.com', '(555) 678-9012', 'Customer Support Specialist', 'Customer Support', '2022-05-15', 42000.00, '987 Cedar Ln, Someplace, ST 67890', 'Robert Wilson', '(555) 678-9013', 'Excellent customer satisfaction ratings'),
('Christopher', 'Martinez', 'christopher.martinez@company.com', '(555) 789-0123', 'Operations Manager', 'Operations', '2017-08-20', 78000.00, '147 Birch Way, Everyplace, ST 78901', 'Maria Martinez', '(555) 789-0124', 'Streamlined warehouse operations'),
('Amanda', 'Taylor', 'amanda.taylor@company.com', '(555) 890-1234', 'Graphic Designer', 'Marketing', '2021-12-01', 52000.00, '258 Spruce Ct, Anyplace, ST 89012', 'James Taylor', '(555) 890-1235', 'Creative lead for brand identity projects'),
('Daniel', 'Anderson', 'daniel.anderson@company.com', '(555) 901-2345', 'IT Administrator', 'Engineering', '2019-04-18', 65000.00, '369 Willow Ave, Allplace, ST 90123', 'Nancy Anderson', '(555) 901-2346', 'Maintains network infrastructure and security'),
('Jennifer', 'Thomas', 'jennifer.thomas@company.com', '(555) 012-3456', 'Accountant', 'Finance', '2020-02-28', 58000.00, '741 Aspen Blvd, Placeholder, ST 01234', 'Kevin Thomas', '(555) 012-3457', 'Handles monthly financial reporting')
ON DUPLICATE KEY UPDATE
first_name = VALUES(first_name),
last_name = VALUES(last_name),
phone = VALUES(phone),
position = VALUES(position),
department = VALUES(department),
hire_date = VALUES(hire_date),
salary = VALUES(salary),
address = VALUES(address),
emergency_contact = VALUES(emergency_contact),
emergency_phone = VALUES(emergency_phone),
notes = VALUES(notes);

-- Sample queries for the employee management system:

-- Get all employees in a specific department
-- SELECT * FROM employees WHERE department = 'Engineering';

-- Get employees hired in the last year
-- SELECT * FROM employees WHERE hire_date >= DATE_SUB(CURDATE(), INTERVAL 1 YEAR);

-- Get employee statistics by department
-- SELECT * FROM employee_stats;

-- Search employees by name or email
-- SELECT * FROM employees WHERE first_name LIKE '%john%' OR last_name LIKE '%john%' OR email LIKE '%john%';

-- Get employees with their years of service
-- SELECT first_name, last_name, position, department, years_of_service FROM employees_with_service;

-- Get employees by status (New/Active/Veteran)
-- SELECT * FROM employees_with_service WHERE employee_status = 'New';