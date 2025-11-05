-- Google Classroom Clone Database Schema
-- Comprehensive classroom management system database
-- Version: 1.0.0

-- Create database
CREATE DATABASE IF NOT EXISTS classroom_clone 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;
USE classroom_clone;

-- Users table (multi-role system: admin, teacher, student, parent)
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    role ENUM('admin', 'teacher', 'student', 'parent') NOT NULL DEFAULT 'student',
    avatar_path VARCHAR(500),
    phone VARCHAR(20),
    date_of_birth DATE,
    gender ENUM('male', 'female', 'other', 'prefer_not_to_say'),
    bio TEXT,
    school_name VARCHAR(255),
    grade_level VARCHAR(50),
    parent_contact_info JSON, -- For students: parent email, phone, etc.
    is_active BOOLEAN DEFAULT TRUE,
    is_verified BOOLEAN DEFAULT FALSE,
    email_verified_at TIMESTAMP NULL,
    last_login_at TIMESTAMP NULL,
    failed_login_attempts INT DEFAULT 0,
    locked_until TIMESTAMP NULL,
    timezone VARCHAR(50) DEFAULT 'UTC',
    language VARCHAR(10) DEFAULT 'en',
    preferences JSON, -- User preferences as JSON
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_username (username),
    INDEX idx_role (role),
    INDEX idx_active (is_active)
);

-- Classes table
CREATE TABLE classes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    class_code VARCHAR(20) UNIQUE NOT NULL,
    subject VARCHAR(100),
    grade_level VARCHAR(50),
    room_number VARCHAR(20),
    teacher_id INT NOT NULL,
    academic_year VARCHAR(20),
    semester VARCHAR(20),
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    max_students INT DEFAULT 30,
    is_active BOOLEAN DEFAULT TRUE,
    settings JSON, -- Class-specific settings
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (teacher_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_teacher (teacher_id),
    INDEX idx_class_code (class_code),
    INDEX idx_active (is_active),
    INDEX idx_subject (subject),
    INDEX idx_academic_year (academic_year)
);

-- Class enrollments
CREATE TABLE class_enrollments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    class_id INT NOT NULL,
    student_id INT NOT NULL,
    enrollment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('active', 'dropped', 'completed', 'transferred') DEFAULT 'active',
    grade_current DECIMAL(5,2) NULL,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_class_student (class_id, student_id),
    INDEX idx_class (class_id),
    INDEX idx_student (student_id),
    INDEX idx_status (status)
);

-- Assignments table
CREATE TABLE assignments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    class_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    instructions TEXT,
    assignment_type ENUM('homework', 'quiz', 'exam', 'project', 'discussion', 'essay', 'presentation') NOT NULL DEFAULT 'homework',
    points_possible INT DEFAULT 100,
    due_date DATETIME,
    available_from DATETIME,
    available_until DATETIME,
    submission_type ENUM('text', 'file', 'both') DEFAULT 'both',
    max_submissions INT DEFAULT 1, -- 0 for unlimited
    allow_late_submissions BOOLEAN DEFAULT FALSE,
    late_penalty_percent DECIMAL(5,2) DEFAULT 0.00,
    grade_category VARCHAR(50), -- Tests, Quizzes, Homework, etc.
    rubric TEXT, -- Grading rubric
    attachments JSON, -- Array of file paths
    is_published BOOLEAN DEFAULT FALSE,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_class (class_id),
    INDEX idx_due_date (due_date),
    INDEX idx_published (is_published),
    INDEX idx_type (assignment_type),
    INDEX idx_category (grade_category)
);

-- Assignment submissions
CREATE TABLE assignment_submissions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    assignment_id INT NOT NULL,
    student_id INT NOT NULL,
    submitted_text LONGTEXT,
    submission_files JSON, -- Array of submitted file paths
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    is_late BOOLEAN DEFAULT FALSE,
    attempt_number INT DEFAULT 1,
    status ENUM('draft', 'submitted', 'returned', 'graded') DEFAULT 'draft',
    grade DECIMAL(5,2) NULL,
    grade_percentage DECIMAL(5,2) NULL,
    grade_letter CHAR(2) NULL,
    feedback TEXT,
    graded_by INT NULL,
    graded_at TIMESTAMP NULL,
    rubric_scores JSON, -- Detailed rubric scoring
    version_number INT DEFAULT 1, -- For tracking multiple versions
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (assignment_id) REFERENCES assignments(id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (graded_by) REFERENCES users(id) ON DELETE SET NULL,
    UNIQUE KEY unique_assignment_student (assignment_id, student_id, version_number),
    INDEX idx_assignment (assignment_id),
    INDEX idx_student (student_id),
    INDEX idx_submitted_at (submitted_at),
    INDEX idx_status (status)
);

-- Assignment comments/discussions
CREATE TABLE assignment_comments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    assignment_id INT NOT NULL,
    submission_id INT NULL, -- NULL for general assignment comments
    user_id INT NOT NULL,
    parent_id INT NULL, -- For threaded comments
    comment_text TEXT NOT NULL,
    attachment_path VARCHAR(500),
    is_teacher_comment BOOLEAN DEFAULT FALSE,
    is_private BOOLEAN DEFAULT FALSE, -- Private feedback vs public discussion
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (assignment_id) REFERENCES assignments(id) ON DELETE CASCADE,
    FOREIGN KEY (submission_id) REFERENCES assignment_submissions(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (parent_id) REFERENCES assignment_comments(id) ON DELETE CASCADE,
    INDEX idx_assignment (assignment_id),
    INDEX idx_submission (submission_id),
    INDEX idx_user (user_id),
    INDEX idx_parent (parent_id)
);

-- Classes announcements/discussions
CREATE TABLE class_announcements (
    id INT PRIMARY KEY AUTO_INCREMENT,
    class_id INT NOT NULL,
    user_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    announcement_type ENUM('general', 'assignment', 'exam', 'grade', 'event', 'urgent') DEFAULT 'general',
    attachments JSON,
    is_published BOOLEAN DEFAULT FALSE,
    published_at TIMESTAMP NULL,
    is_important BOOLEAN DEFAULT FALSE,
    expires_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_class (class_id),
    INDEX idx_user (user_id),
    INDEX idx_published (is_published),
    INDEX idx_type (announcement_type),
    INDEX idx_published_at (published_at)
);

-- Student attendance for classes
CREATE TABLE class_attendance (
    id INT PRIMARY KEY AUTO_INCREMENT,
    class_id INT NOT NULL,
    student_id INT NOT NULL,
    attendance_date DATE NOT NULL,
    status ENUM('present', 'absent', 'late', 'excused') DEFAULT 'present',
    arrival_time TIME NULL,
    departure_time TIME NULL,
    minutes_present INT NULL,
    notes TEXT,
    recorded_by INT NOT NULL,
    recorded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (recorded_by) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_class_student_date (class_id, student_id, attendance_date),
    INDEX idx_class_date (class_id, attendance_date),
    INDEX idx_student_date (student_id, attendance_date),
    INDEX idx_status (status)
);

-- File uploads table
CREATE TABLE file_uploads (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    original_filename VARCHAR(255) NOT NULL,
    stored_filename VARCHAR(255) NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    file_size INT NOT NULL,
    mime_type VARCHAR(100) NOT NULL,
    file_type ENUM('avatar', 'assignment', 'submission', 'resource', 'announcement', 'other') NOT NULL,
    reference_id INT NULL, -- ID of related entity (assignment_id, class_id, etc.)
    is_public BOOLEAN DEFAULT FALSE,
    upload_ip VARCHAR(45),
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user (user_id),
    INDEX idx_reference (file_type, reference_id),
    INDEX idx_uploaded_at (uploaded_at)
);

-- Grade categories (for organizing grades)
CREATE TABLE grade_categories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    class_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    weight_percentage DECIMAL(5,2) DEFAULT 0.00, -- Weight in final grade calculation
    drop_lowest BOOLEAN DEFAULT FALSE, -- Drop lowest grades in category
    assignment_order INT DEFAULT 0, -- Display order
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE,
    INDEX idx_class (class_id),
    UNIQUE KEY unique_class_category (class_id, name)
);

-- Class materials/resources
CREATE TABLE class_resources (
    id INT PRIMARY KEY AUTO_INCREMENT,
    class_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    resource_type ENUM('document', 'video', 'link', 'image', 'audio', 'presentation') NOT NULL,
    file_path VARCHAR(500),
    external_url VARCHAR(500),
    uploaded_by INT NOT NULL,
    is_required BOOLEAN DEFAULT FALSE,
    view_count INT DEFAULT 0,
    download_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE,
    FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_class (class_id),
    INDEX idx_type (resource_type),
    INDEX idx_required (is_required)
);

-- Parent-Student relationships
CREATE TABLE parent_student_relationships (
    id INT PRIMARY KEY AUTO_INCREMENT,
    parent_id INT NOT NULL,
    student_id INT NOT NULL,
    relationship_type ENUM('father', 'mother', 'guardian', 'other') DEFAULT 'parent',
    is_primary BOOLEAN DEFAULT FALSE, -- Primary contact
    notification_preferences JSON, -- Email, SMS preferences
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (parent_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_parent_student (parent_id, student_id),
    INDEX idx_parent (parent_id),
    INDEX idx_student (student_id)
);

-- System settings
CREATE TABLE system_settings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT,
    setting_type ENUM('string', 'number', 'boolean', 'json') DEFAULT 'string',
    category VARCHAR(50) DEFAULT 'general',
    is_public BOOLEAN DEFAULT FALSE, -- Can be accessed by non-admin users
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_key (setting_key),
    INDEX idx_category (category),
    INDEX idx_public (is_public)
);

-- Activity logs
CREATE TABLE activity_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NULL,
    action VARCHAR(100) NOT NULL,
    entity_type VARCHAR(50), -- assignment, class, submission, etc.
    entity_id INT NULL,
    description TEXT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    severity ENUM('info', 'warning', 'error', 'critical') DEFAULT 'info',
    metadata JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_user (user_id),
    INDEX idx_action (action),
    INDEX idx_entity (entity_type, entity_id),
    INDEX idx_created_at (created_at),
    INDEX idx_severity (severity)
);

-- Notifications
CREATE TABLE notifications (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    notification_type ENUM('info', 'success', 'warning', 'error', 'assignment', 'grade', 'announcement', 'reminder') DEFAULT 'info',
    is_read BOOLEAN DEFAULT FALSE,
    action_url VARCHAR(500),
    related_entity_type VARCHAR(50),
    related_entity_id INT NULL,
    sent_email BOOLEAN DEFAULT FALSE,
    sent_sms BOOLEAN DEFAULT FALSE,
    expires_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    read_at TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user (user_id),
    INDEX idx_read (is_read),
    INDEX idx_type (notification_type),
    INDEX idx_created_at (created_at)
);

-- Sessions table for enhanced session management
CREATE TABLE user_sessions (
    id VARCHAR(128) PRIMARY KEY,
    user_id INT NOT NULL,
    ip_address VARCHAR(45),
    user_agent TEXT,
    payload LONGTEXT NOT NULL,
    last_activity TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    is_active BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user (user_id),
    INDEX idx_last_activity (last_activity),
    INDEX idx_active (is_active)
);

-- ================================================
-- SAMPLE DATA INSERTION
-- ================================================

-- Insert default admin user
INSERT INTO users (username, email, password_hash, first_name, last_name, role, is_verified) VALUES
('admin', 'admin@classroom.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'System', 'Administrator', 'admin', TRUE);

-- Insert sample teachers
INSERT INTO users (username, email, password_hash, first_name, last_name, role, school_name, is_verified) VALUES
('teacher1', 'teacher1@school.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'John', 'Smith', 'teacher', 'Lincoln High School', TRUE),
('teacher2', 'teacher2@school.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Sarah', 'Johnson', 'teacher', 'Lincoln High School', TRUE);

-- Insert sample students
INSERT INTO users (username, email, password_hash, first_name, last_name, role, grade_level, is_verified) VALUES
('student1', 'student1@school.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Alice', 'Williams', 'student', '10th Grade', TRUE),
('student2', 'student2@school.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Bob', 'Davis', 'student', '10th Grade', TRUE),
('student3', 'student3@school.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Carol', 'Brown', 'student', '11th Grade', TRUE);

-- Insert sample parents
INSERT INTO users (username, email, password_hash, first_name, last_name, role, is_verified) VALUES
('parent1', 'parent1@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Michael', 'Williams', 'parent', TRUE),
('parent2', 'parent2@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Lisa', 'Davis', 'parent', TRUE);

-- Insert sample classes
INSERT INTO classes (name, description, class_code, subject, grade_level, teacher_id, start_date, end_date, max_students) VALUES
('Advanced Mathematics', 'Advanced algebra and calculus concepts', 'MATH101', 'Mathematics', '10th Grade', 2, '2025-09-01', '2026-06-15', 25),
('World History', 'Comprehensive world history course', 'HIST201', 'History', '11th Grade', 3, '2025-09-01', '2026-06-15', 30),
('Physics Fundamentals', 'Introduction to physics principles', 'PHYS101', 'Science', '11th Grade', 2, '2025-09-01', '2026-06-15', 20);

-- Insert grade categories
INSERT INTO grade_categories (class_id, name, weight_percentage, assignment_order) VALUES
(1, 'Tests', 40.00, 1),
(1, 'Quizzes', 20.00, 2),
(1, 'Homework', 30.00, 3),
(1, 'Participation', 10.00, 4),
(2, 'Essays', 35.00, 1),
(2, 'Exams', 45.00, 2),
(2, 'Participation', 20.00, 3);

-- Enroll students in classes
INSERT INTO class_enrollments (class_id, student_id) VALUES
(1, 3), -- Alice in Math
(1, 4), -- Bob in Math
(2, 5), -- Carol in History
(3, 3), -- Alice in Physics
(3, 4); -- Bob in Physics

-- Create parent-student relationships
INSERT INTO parent_student_relationships (parent_id, student_id, relationship_type, is_primary) VALUES
(4, 3, 'father', TRUE), -- Michael is Alice's father
(5, 4, 'mother', TRUE), -- Lisa is Bob's mother

-- Insert sample assignments
INSERT INTO assignments (class_id, title, description, assignment_type, points_possible, due_date, created_by) VALUES
(1, 'Algebra Fundamentals Quiz', 'Test your understanding of basic algebraic concepts', 'quiz', 50, '2025-11-15 23:59:59', 2),
(1, 'Calculus Problem Set', 'Complete problems 1-20 from Chapter 3', 'homework', 100, '2025-11-20 23:59:59', 2),
(2, 'World War II Essay', 'Write a 1000-word essay on the causes of WWII', 'essay', 150, '2025-11-25 23:59:59', 3),
(3, 'Physics Lab Report', 'Conduct experiment and write lab report', 'project', 200, '2025-12-01 23:59:59', 2);

-- Insert sample system settings
INSERT INTO system_settings (setting_key, setting_value, setting_type, category, is_public, description) VALUES
('school_name', 'Lincoln High School', 'string', 'general', TRUE, 'Name of the school'),
('academic_year', '2025-2026', 'string', 'general', TRUE, 'Current academic year'),
('max_file_upload_size', '10485760', 'number', 'uploads', FALSE, 'Maximum file upload size in bytes'),
('allowed_file_types', '["pdf", "doc", "docx", "txt", "jpg", "jpeg", "png", "gif"]', 'json', 'uploads', FALSE, 'Allowed file types for uploads'),
('email_notifications_enabled', 'true', 'boolean', 'notifications', FALSE, 'Enable email notifications'),
('assignment_due_reminder_days', '3', 'number', 'notifications', FALSE, 'Days before due date to send reminder'),
('default_timezone', 'America/New_York', 'string', 'general', TRUE, 'Default timezone for the system'),
('grade_scale_type', 'percentage', 'string', 'grades', TRUE, 'Default grading scale type'),
('passing_grade', '60', 'number', 'grades', TRUE, 'Minimum passing grade percentage');

-- Create sample class resources
INSERT INTO class_resources (class_id, title, description, resource_type, external_url, uploaded_by) VALUES
(1, 'Algebra Reference Guide', 'Comprehensive guide to algebraic formulas', 'document', 'https://example.com/algebra-guide.pdf', 2),
(2, 'Historical Timeline', 'Interactive timeline of major world events', 'link', 'https://www.history.com/timeline', 3),
(1, 'Calculus Tutorial Video', 'Step-by-step calculus tutorial', 'video', 'https://www.youtube.com/watch?v=example', 2);

-- ================================================
-- INDEXES FOR PERFORMANCE
-- ================================================

-- Additional performance indexes
CREATE INDEX idx_users_role_active ON users(role, is_active);
CREATE INDEX idx_classes_teacher_active ON classes(teacher_id, is_active);
CREATE INDEX idx_assignments_class_published ON assignments(class_id, is_published);
CREATE INDEX idx_submissions_assignment_status ON assignment_submissions(assignment_id, status);
CREATE INDEX idx_enrollments_student_status ON class_enrollments(student_id, status);
CREATE INDEX idx_attendance_class_date ON class_attendance(class_id, attendance_date);

-- ================================================
-- VIEWS FOR COMMON QUERIES
-- ================================================

-- View for class information with enrollment counts
CREATE VIEW class_overview AS
SELECT 
    c.id,
    c.name,
    c.class_code,
    c.subject,
    c.grade_level,
    u.first_name as teacher_first_name,
    u.last_name as teacher_last_name,
    c.max_students,
    c.start_date,
    c.end_date,
    c.is_active,
    COUNT(ce.student_id) as enrolled_students,
    COUNT(CASE WHEN ce.status = 'active' THEN 1 END) as active_students
FROM classes c
JOIN users u ON c.teacher_id = u.id
LEFT JOIN class_enrollments ce ON c.id = ce.class_id
GROUP BY c.id;

-- View for student progress in classes
CREATE VIEW student_class_progress AS
SELECT 
    s.id as student_id,
    s.first_name as student_first_name,
    s.last_name as student_last_name,
    c.id as class_id,
    c.name as class_name,
    c.subject,
    ce.status,
    ce.grade_current,
    COUNT(a.id) as total_assignments,
    COUNT(CASE WHEN asb.id IS NOT NULL THEN 1 END) as submitted_assignments,
    COUNT(CASE WHEN asb.grade IS NOT NULL THEN 1 END) as graded_assignments,
    AVG(CASE WHEN asb.grade_percentage IS NOT NULL THEN asb.grade_percentage END) as average_grade
FROM users s
JOIN class_enrollments ce ON s.id = ce.student_id
JOIN classes c ON ce.class_id = c.id
LEFT JOIN assignments a ON c.id = a.class_id
LEFT JOIN assignment_submissions asb ON a.id = asb.assignment_id AND s.id = asb.student_id
WHERE s.role = 'student' AND c.is_active = TRUE
GROUP BY s.id, c.id;

-- ================================================
-- STORED PROCEDURES
-- ================================================

-- Procedure to get class roster with grades
DELIMITER //

CREATE PROCEDURE GetClassRoster(
    IN p_class_id INT
)
BEGIN
    SELECT 
        u.id,
        u.first_name,
        u.last_name,
        u.email,
        ce.status,
        ce.grade_current,
        AVG(asb.grade_percentage) as average_grade,
        COUNT(DISTINCT a.id) as total_assignments,
        COUNT(CASE WHEN asb.submitted_at IS NOT NULL THEN 1 END) as submissions_count
    FROM users u
    JOIN class_enrollments ce ON u.id = ce.student_id
    LEFT JOIN assignments a ON ce.class_id = a.class_id
    LEFT JOIN assignment_submissions asb ON a.id = asb.assignment_id AND u.id = asb.student_id
    WHERE ce.class_id = p_class_id AND ce.status = 'active'
    GROUP BY u.id, u.first_name, u.last_name, u.email, ce.status, ce.grade_current
    ORDER BY u.last_name, u.first_name;
END //

DELIMITER ;

-- ================================================
-- TRIGGERS
-- ================================================

-- Trigger to auto-generate class codes
DELIMITER //

CREATE TRIGGER generate_class_code
BEFORE INSERT ON classes
FOR EACH ROW
BEGIN
    IF NEW.class_code IS NULL OR NEW.class_code = '' THEN
        SET NEW.class_code = CONCAT(
            UPPER(SUBSTRING(NEW.subject, 1, 3)),
            LPAD(FLOOR(RAND() * 10000), 4, '0')
        );
    END IF;
END //

DELIMITER ;

-- ================================================
-- GRANT PERMISSIONS
-- ================================================

-- Create application user and grant permissions
-- GRANT SELECT, INSERT, UPDATE, DELETE ON classroom_clone.* TO 'classroom_app'@'localhost' IDENTIFIED BY 'secure_password';
-- FLUSH PRIVILEGES;

-- Show completion message
SELECT 'Google Classroom Clone database schema created successfully!' as message;
