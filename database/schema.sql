-- Attendance Management System Database Schema
-- Run this script to create the necessary tables

-- Create database (if it doesn't exist)
CREATE DATABASE IF NOT EXISTS attendance_system CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE attendance_system;

-- Employees table
CREATE TABLE employees (
    id INT PRIMARY KEY AUTO_INCREMENT,
    employee_id VARCHAR(20) UNIQUE NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    department VARCHAR(100),
    position VARCHAR(100),
    hire_date DATE,
    status ENUM('active', 'inactive', 'terminated') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Attendance table
CREATE TABLE attendance (
    id INT PRIMARY KEY AUTO_INCREMENT,
    employee_id INT NOT NULL,
    check_in_time DATETIME NOT NULL,
    check_out_time DATETIME NULL,
    total_hours DECIMAL(5,2) DEFAULT 0.00,
    break_time DECIMAL(4,2) DEFAULT 0.00,
    status ENUM('present', 'absent', 'late', 'early', 'overtime', 'incomplete', 'holiday', 'leave') DEFAULT 'present',
    location VARCHAR(255),
    ip_address VARCHAR(45),
    user_agent TEXT,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE,
    INDEX idx_employee_date (employee_id, DATE(check_in_time)),
    INDEX idx_status (status),
    INDEX idx_date_range (check_in_time, check_out_time)
);

-- Attendance breaks table (for tracking breaks)
CREATE TABLE attendance_breaks (
    id INT PRIMARY KEY AUTO_INCREMENT,
    attendance_id INT NOT NULL,
    break_start_time DATETIME NOT NULL,
    break_end_time DATETIME NULL,
    break_type ENUM('lunch', 'coffee', 'personal', 'other') DEFAULT 'lunch',
    duration_minutes INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (attendance_id) REFERENCES attendance(id) ON DELETE CASCADE,
    INDEX idx_attendance (attendance_id)
);

-- Leave requests table
CREATE TABLE leave_requests (
    id INT PRIMARY KEY AUTO_INCREMENT,
    employee_id INT NOT NULL,
    leave_type ENUM('annual', 'sick', 'maternity', 'paternity', 'unpaid', 'other') NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    total_days INT NOT NULL,
    reason TEXT,
    status ENUM('pending', 'approved', 'rejected', 'cancelled') DEFAULT 'pending',
    approved_by INT NULL,
    approved_at TIMESTAMP NULL,
    rejection_reason TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE,
    FOREIGN KEY (approved_by) REFERENCES employees(id) ON DELETE SET NULL,
    INDEX idx_employee_dates (employee_id, start_date, end_date),
    INDEX idx_status (status)
);

-- Holiday calendar table
CREATE TABLE holidays (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    date DATE NOT NULL,
    type ENUM('national', 'religious', 'company', 'observed') DEFAULT 'national',
    recurring BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_date (date),
    UNIQUE KEY unique_date_name (date, name)
);

-- Settings table for system configuration
CREATE TABLE settings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert sample employees
INSERT INTO employees (employee_id, first_name, last_name, email, department, position, hire_date) VALUES
('EMP001', 'John', 'Doe', 'john.doe@company.com', 'IT', 'Software Developer', '2023-01-15'),
('EMP002', 'Jane', 'Smith', 'jane.smith@company.com', 'HR', 'HR Manager', '2022-06-01'),
('EMP003', 'Mike', 'Johnson', 'mike.johnson@company.com', 'Sales', 'Sales Representative', '2023-03-20'),
('EMP004', 'Sarah', 'Wilson', 'sarah.wilson@company.com', 'Marketing', 'Marketing Specialist', '2022-09-10'),
('EMP005', 'David', 'Brown', 'david.brown@company.com', 'IT', 'System Administrator', '2021-11-05');

-- Insert sample attendance records
INSERT INTO attendance (employee_id, check_in_time, check_out_time, total_hours, status, location) VALUES
(1, '2025-11-05 08:30:00', '2025-11-05 17:30:00', 8.00, 'present', 'Office - Floor 3'),
(1, '2025-11-04 08:45:00', '2025-11-04 17:15:00', 7.50, 'late', 'Office - Floor 3'),
(1, '2025-11-03 08:15:00', '2025-11-03 18:00:00', 8.75, 'overtime', 'Office - Floor 3'),
(2, '2025-11-05 09:00:00', '2025-11-05 17:00:00', 8.00, 'present', 'Office - Floor 2'),
(2, '2025-11-04 09:00:00', '2025-11-04 17:00:00', 8.00, 'present', 'Office - Floor 2'),
(3, '2025-11-05 08:00:00', '2025-11-05 16:00:00', 8.00, 'present', 'Office - Floor 1');

-- Insert sample holidays
INSERT INTO holidays (name, date, type, recurring) VALUES
('New Year\'s Day', '2025-01-01', 'national', TRUE),
('Independence Day', '2025-07-04', 'national', TRUE),
('Christmas Day', '2025-12-25', 'national', TRUE),
('Thanksgiving', '2025-11-27', 'national', FALSE),
('Company Founding Day', '2025-03-15', 'company', TRUE);

-- Insert system settings
INSERT INTO settings (setting_key, setting_value, description) VALUES
('workday_start_time', '09:00', 'Standard workday start time'),
('workday_end_time', '17:00', 'Standard workday end time'),
('lunch_break_duration', '60', 'Lunch break duration in minutes'),
('late_threshold_minutes', '15', 'Minutes after start time to be considered late'),
('overtime_threshold_hours', '8', 'Hours to work before overtime'),
('max_work_hours_per_day', '12', 'Maximum work hours allowed per day'),
('require_checkout', 'true', 'Whether checkout is required'),
('auto_approve_leave', 'false', 'Whether leave requests are auto-approved'),
('timezone', 'America/New_York', 'System timezone');

-- Create indexes for performance
CREATE INDEX idx_attendance_employee_date ON attendance(employee_id, DATE(check_in_time));
CREATE INDEX idx_attendance_date_range ON attendance(check_in_time, check_out_time);
CREATE INDEX idx_attendance_status ON attendance(status);
CREATE INDEX idx_leave_employee_date ON leave_requests(employee_id, start_date, end_date);
CREATE INDEX idx_leave_status ON leave_requests(status);

-- Create a view for attendance summary
CREATE VIEW attendance_summary AS
SELECT 
    e.id,
    e.employee_id,
    e.first_name,
    e.last_name,
    e.department,
    DATE(a.check_in_time) as work_date,
    MIN(a.check_in_time) as first_check_in,
    MAX(a.check_out_time) as last_check_out,
    SUM(a.total_hours) as total_hours_worked,
    COUNT(a.id) as attendance_records,
    AVG(CASE WHEN a.status = 'present' THEN a.total_hours END) as avg_hours_present,
    SUM(CASE WHEN a.status = 'late' THEN 1 ELSE 0 END) as late_count,
    SUM(CASE WHEN a.status = 'overtime' THEN 1 ELSE 0 END) as overtime_count,
    SUM(CASE WHEN a.status = 'absent' THEN 1 ELSE 0 END) as absent_count
FROM employees e
LEFT JOIN attendance a ON e.id = a.employee_id
GROUP BY e.id, DATE(a.check_in_time);

-- Create a stored procedure for getting monthly attendance report
DELIMITER //

CREATE PROCEDURE GetMonthlyAttendanceReport(
    IN p_employee_id INT,
    IN p_year INT,
    IN p_month INT
)
BEGIN
    SELECT 
        DATE(check_in_time) as work_date,
        DAYNAME(check_in_time) as day_name,
        check_in_time,
        check_out_time,
        total_hours,
        break_time,
        status,
        location,
        notes
    FROM attendance 
    WHERE employee_id = p_employee_id 
    AND YEAR(check_in_time) = p_year 
    AND MONTH(check_in_time) = p_month
    ORDER BY check_in_time;
END //

DELIMITER ;

-- Create a trigger to automatically update total_hours when check_out_time is set
DELIMITER //

CREATE TRIGGER update_total_hours_before_checkout
BEFORE UPDATE ON attendance
FOR EACH ROW
BEGIN
    IF NEW.check_out_time IS NOT NULL AND NEW.check_out_time != OLD.check_out_time THEN
        SET NEW.total_hours = TIMESTAMPDIFF(MINUTE, OLD.check_in_time, NEW.check_out_time) / 60;
        IF NEW.total_hours > 8 THEN
            SET NEW.status = 'overtime';
        ELSEIF NEW.total_hours < 7 THEN
            SET NEW.status = 'incomplete';
        ELSE
            SET NEW.status = 'present';
        END IF;
    END IF;
END //

DELIMITER ;

-- Grant permissions (adjust based on your MySQL setup)
-- GRANT ALL PRIVILEGES ON attendance_system.* TO 'attendance_user'@'localhost' IDENTIFIED BY 'secure_password';
-- FLUSH PRIVILEGES;

-- Show completion message
SELECT 'Attendance Management System database schema created successfully!' as message;