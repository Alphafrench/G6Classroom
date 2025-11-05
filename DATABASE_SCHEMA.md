# Database Schema Documentation - Google Classroom Clone

## Table of Contents
1. [Overview](#overview)
2. [Entity Relationship Diagram](#entity-relationship-diagram)
3. [Core Tables](#core-tables)
4. [User Management](#user-management)
5. [Course Management](#course-management)
6. [Assignment System](#assignment-system)
7. [Attendance Tracking](#attendance-tracking)
8. [File Management](#file-management)
9. [Security and Audit](#security-and-audit)
10. [Indexes and Performance](#indexes-and-performance)
11. [Database Functions](#database-functions)
12. [Views](#views)
13. [Triggers](#triggers)
14. [Maintenance](#maintenance)

## Overview

The Google Classroom Clone uses MySQL 5.7+ or MariaDB 10.2+ as its database. The schema is designed with the following principles:

- **Normalization**: Third normal form (3NF) with appropriate denormalization for performance
- **Scalability**: Optimized for handling thousands of users and courses
- **Security**: Built-in security features and audit trails
- **Flexibility**: Extensible design for future enhancements
- **Performance**: Strategic indexing and query optimization

### Database Configuration
```sql
-- Recommended MySQL settings
character-set-server = utf8mb4
collation-server = utf8mb4_unicode_ci
innodb_buffer_pool_size = 70% of RAM
innodb_log_file_size = 256MB
innodb_flush_log_at_trx_commit = 2
```

## Entity Relationship Diagram

```mermaid
erDiagram
    USERS ||--o{ ENROLLMENTS : has
    USERS ||--o{ ATTENDANCE : creates
    USERS ||--o{ ASSIGNMENTS : creates
    USERS ||--o{ SUBMISSIONS : submits
    USERS ||--o{ GRADES : receives
    USERS ||--o{ ACTIVITY_LOGS : generates
    USERS ||--o{ SECURITY_EVENTS : triggers
    USERS ||--o{ USER_SESSIONS : has
    
    COURSES ||--o{ ENROLLMENTS : contains
    COURSES ||--o{ ASSIGNMENTS : contains
    COURSES ||--o{ ATTENDANCE : tracks
    
    ASSIGNMENTS ||--o{ SUBMISSIONS : receives
    ASSIGNMENTS ||--o{ GRADES : has
    
    FILES ||--o{ ASSIGNMENTS : attached_to
    FILES ||--o{ USERS : avatar_of
    FILES ||--o{ SUBMISSIONS : contains
    
    {
        "USERS" - user management
        "COURSES" - course information
        "ENROLLMENTS" - student-course relationships
        "ASSIGNMENTS" - assignment data
        "SUBMISSIONS" - student submissions
        "GRADES" - grading data
        "ATTENDANCE" - attendance records
        "FILES" - file storage
        "ACTIVITY_LOGS" - audit trail
        "SECURITY_EVENTS" - security monitoring
        "USER_SESSIONS" - session management
    }
```

## Core Tables

### 1. users
Central user management table for all system users.

```sql
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    role ENUM('admin', 'teacher', 'student', 'parent') DEFAULT 'student',
    status ENUM('active', 'inactive', 'suspended') DEFAULT 'active',
    
    -- Personal Information
    phone VARCHAR(20),
    date_of_birth DATE,
    gender ENUM('male', 'female', 'other', 'prefer_not_to_say'),
    address TEXT,
    profile_image VARCHAR(255),
    
    -- Academic Information
    student_id VARCHAR(20),
    employee_id VARCHAR(20),
    department VARCHAR(100),
    
    -- Security and Verification
    email_verified BOOLEAN DEFAULT FALSE,
    email_verification_token VARCHAR(255),
    password_reset_token VARCHAR(255),
    password_reset_expires TIMESTAMP NULL,
    failed_attempts INT DEFAULT 0,
    last_failed_attempt TIMESTAMP NULL,
    two_factor_enabled BOOLEAN DEFAULT FALSE,
    two_factor_secret VARCHAR(255),
    
    -- Session and Activity
    last_login TIMESTAMP NULL,
    last_activity TIMESTAMP NULL,
    login_count INT DEFAULT 0,
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Indexes
    INDEX idx_username (username),
    INDEX idx_email (email),
    INDEX idx_role (role),
    INDEX idx_status (status),
    INDEX idx_student_id (student_id),
    INDEX idx_employee_id (employee_id),
    INDEX idx_last_activity (last_activity),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### 2. courses
Course management table for organizing classes.

```sql
CREATE TABLE courses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    code VARCHAR(20) UNIQUE NOT NULL,
    description TEXT,
    teacher_id INT NOT NULL,
    
    -- Course Details
    room VARCHAR(50),
    schedule VARCHAR(255),
    credits INT DEFAULT 3,
    semester VARCHAR(20),
    academic_year VARCHAR(20),
    
    -- Course Configuration
    status ENUM('active', 'inactive', 'completed', 'archived') DEFAULT 'active',
    max_students INT DEFAULT 30,
    start_date DATE,
    end_date DATE,
    
    -- Grading Configuration
    grading_scale ENUM('percentage', 'letter', 'points') DEFAULT 'percentage',
    attendance_required BOOLEAN DEFAULT TRUE,
    allow_late_submissions BOOLEAN DEFAULT FALSE,
    late_penalty_percent DECIMAL(5,2) DEFAULT 0.00,
    
    -- Metadata
    tags JSON,
    syllabus TEXT,
    prerequisites JSON,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (teacher_id) REFERENCES users(id) ON DELETE RESTRICT ON UPDATE CASCADE,
    
    INDEX idx_code (code),
    INDEX idx_teacher_id (teacher_id),
    INDEX idx_status (status),
    INDEX idx_semester (semester),
    INDEX idx_academic_year (academic_year),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### 3. enrollments
Student-course enrollment relationships.

```sql
CREATE TABLE enrollments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    course_id INT NOT NULL,
    
    -- Enrollment Details
    enrollment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('active', 'dropped', 'completed', 'withdrawn') DEFAULT 'active',
    grade ENUM('A', 'A-', 'B+', 'B', 'B-', 'C+', 'C', 'C-', 'D', 'F', 'I', 'W'),
    final_grade DECIMAL(5,2),
    letter_grade ENUM('A', 'A-', 'B+', 'B', 'B-', 'C+', 'C', 'C-', 'D', 'F'),
    
    -- Progress Tracking
    attendance_rate DECIMAL(5,2) DEFAULT 0.00,
    assignment_completion_rate DECIMAL(5,2) DEFAULT 0.00,
    current_points DECIMAL(8,2) DEFAULT 0.00,
    max_points DECIMAL(8,2) DEFAULT 0.00,
    
    -- Notes
    instructor_notes TEXT,
    student_notes TEXT,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE ON UPDATE CASCADE,
    
    UNIQUE KEY unique_enrollment (student_id, course_id),
    INDEX idx_student_id (student_id),
    INDEX idx_course_id (course_id),
    INDEX idx_status (status),
    INDEX idx_enrollment_date (enrollment_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

## User Management

### User Profiles
Extended user profile information.

```sql
CREATE TABLE user_profiles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL UNIQUE,
    
    -- Emergency Contact
    emergency_contact_name VARCHAR(255),
    emergency_contact_phone VARCHAR(20),
    emergency_contact_relationship VARCHAR(100),
    
    -- Academic Information
    major VARCHAR(100),
    graduation_date DATE,
    gpa DECIMAL(4,2),
    academic_standing ENUM('good', 'warning', 'probation', 'suspended'),
    
    -- Employment Information
    job_title VARCHAR(100),
    department VARCHAR(100),
    hire_date DATE,
    employment_type ENUM('full_time', 'part_time', 'contract', 'intern'),
    
    -- Preferences
    timezone VARCHAR(50) DEFAULT 'UTC',
    language VARCHAR(10) DEFAULT 'en',
    notification_preferences JSON,
    
    -- Custom Fields
    bio TEXT,
    interests JSON,
    skills JSON,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE ON UPDATE CASCADE,
    
    INDEX idx_user_id (user_id),
    INDEX idx_employment_type (employment_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### User Sessions
Session management for security.

```sql
CREATE TABLE user_sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    session_token VARCHAR(64) UNIQUE NOT NULL,
    refresh_token VARCHAR(64) UNIQUE,
    
    -- Session Details
    ip_address VARCHAR(45),
    user_agent TEXT,
    device_type ENUM('desktop', 'mobile', 'tablet', 'api'),
    browser VARCHAR(100),
    os VARCHAR(100),
    
    -- Session Control
    is_active BOOLEAN DEFAULT TRUE,
    is_persistent BOOLEAN DEFAULT FALSE,
    last_activity TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    expires_at TIMESTAMP NOT NULL,
    
    -- Two Factor Authentication
    two_factor_verified BOOLEAN DEFAULT FALSE,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE ON UPDATE CASCADE,
    
    INDEX idx_user_id (user_id),
    INDEX idx_session_token (session_token),
    INDEX idx_refresh_token (refresh_token),
    INDEX idx_expires_at (expires_at),
    INDEX idx_last_activity (last_activity)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

## Course Management

### Course Categories
Course categorization system.

```sql
CREATE TABLE course_categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) UNIQUE NOT NULL,
    description TEXT,
    parent_id INT NULL,
    sort_order INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (parent_id) REFERENCES course_categories(id) ON DELETE SET NULL,
    
    INDEX idx_parent_id (parent_id),
    INDEX idx_name (name),
    INDEX idx_is_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### Course-Category Relationships
```sql
CREATE TABLE course_category_relations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    course_id INT NOT NULL,
    category_id INT NOT NULL,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES course_categories(id) ON DELETE CASCADE,
    
    UNIQUE KEY unique_course_category (course_id, category_id),
    INDEX idx_course_id (course_id),
    INDEX idx_category_id (category_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

## Assignment System

### Assignments
Assignment management table.

```sql
CREATE TABLE assignments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    course_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    instructions TEXT,
    
    -- Assignment Details
    type ENUM('homework', 'quiz', 'exam', 'project', 'discussion', 'lab') NOT NULL,
    points DECIMAL(8,2) NOT NULL DEFAULT 0.00,
    max_attempts INT DEFAULT 1,
    time_limit_minutes INT NULL,
    
    -- Scheduling
    available_from TIMESTAMP NULL,
    available_until TIMESTAMP NULL,
    due_date TIMESTAMP NULL,
    late_submission_allowed BOOLEAN DEFAULT FALSE,
    late_penalty_percent DECIMAL(5,2) DEFAULT 0.00,
    
    -- Configuration
    status ENUM('draft', 'published', 'closed', 'archived') DEFAULT 'draft',
    is_required BOOLEAN DEFAULT TRUE,
    allow_text_submission BOOLEAN DEFAULT TRUE,
    allow_file_submission BOOLEAN DEFAULT TRUE,
    max_file_size_mb INT DEFAULT 10,
    allowed_file_types JSON,
    
    -- Grading
    auto_grade BOOLEAN DEFAULT FALSE,
    rubric JSON,
    grade_distribution JSON,
    
    -- Additional Features
    peer_review_enabled BOOLEAN DEFAULT FALSE,
    plagiarism_check_enabled BOOLEAN DEFAULT FALSE,
    discussion_enabled BOOLEAN DEFAULT FALSE,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE ON UPDATE CASCADE,
    
    INDEX idx_course_id (course_id),
    INDEX idx_type (type),
    INDEX idx_status (status),
    INDEX idx_due_date (due_date),
    INDEX idx_available_from (available_from),
    INDEX idx_available_until (available_until),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### Submissions
Student assignment submissions.

```sql
CREATE TABLE submissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    assignment_id INT NOT NULL,
    student_id INT NOT NULL,
    
    -- Submission Content
    submission_text LONGTEXT,
    submission_data JSON,
    
    -- File Submissions
    files JSON, -- Array of file IDs and metadata
    
    -- Submission Details
    attempt_number INT DEFAULT 1,
    is_late BOOLEAN DEFAULT FALSE,
    late_minutes INT DEFAULT 0,
    
    -- Status and Progress
    status ENUM('draft', 'submitted', 'graded', 'returned', 'resubmitted') DEFAULT 'draft',
    submitted_at TIMESTAMP NULL,
    
    -- Grading
    auto_score DECIMAL(8,2) DEFAULT 0.00,
    manual_score DECIMAL(8,2) DEFAULT 0.00,
    total_score DECIMAL(8,2) DEFAULT 0.00,
    grade ENUM('A', 'A-', 'B+', 'B', 'B-', 'C+', 'C', 'C-', 'D', 'F'),
    
    -- Feedback
    instructor_feedback TEXT,
    peer_feedback JSON,
    rubric_scores JSON,
    
    -- Metadata
    plagiarism_score DECIMAL(5,2) DEFAULT 0.00,
    word_count INT DEFAULT 0,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (assignment_id) REFERENCES assignments(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE ON UPDATE CASCADE,
    
    UNIQUE KEY unique_submission (assignment_id, student_id, attempt_number),
    INDEX idx_assignment_id (assignment_id),
    INDEX idx_student_id (student_id),
    INDEX idx_status (status),
    INDEX idx_submitted_at (submitted_at),
    INDEX idx_is_late (is_late)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### Grades
Detailed grading information.

```sql
CREATE TABLE grades (
    id INT AUTO_INCREMENT PRIMARY KEY,
    submission_id INT NOT NULL,
    assignment_id INT NOT NULL,
    student_id INT NOT NULL,
    grader_id INT NOT NULL,
    
    -- Grade Details
    points_earned DECIMAL(8,2) NOT NULL DEFAULT 0.00,
    points_possible DECIMAL(8,2) NOT NULL DEFAULT 0.00,
    percentage DECIMAL(5,2) NOT NULL DEFAULT 0.00,
    letter_grade ENUM('A', 'A-', 'B+', 'B', 'B-', 'C+', 'C', 'C-', 'D', 'F'),
    
    -- Grading Details
    rubric_scores JSON,
    deduction_reason VARCHAR(255),
    bonus_points DECIMAL(8,2) DEFAULT 0.00,
    
    -- Feedback
    feedback TEXT,
    private_feedback TEXT,
    audio_feedback_file VARCHAR(255),
    
    -- Grading Control
    is_final BOOLEAN DEFAULT FALSE,
    graded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    graded_by_type ENUM('instructor', 'ta', 'peer', 'auto') NOT NULL,
    
    -- Moderation
    moderation_status ENUM('pending', 'approved', 'rejected') DEFAULT 'approved',
    moderation_notes TEXT,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (submission_id) REFERENCES submissions(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (assignment_id) REFERENCES assignments(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (grader_id) REFERENCES users(id) ON DELETE RESTRICT ON UPDATE CASCADE,
    
    INDEX idx_submission_id (submission_id),
    INDEX idx_assignment_id (assignment_id),
    INDEX idx_student_id (student_id),
    INDEX idx_grader_id (grader_id),
    INDEX idx_letter_grade (letter_grade),
    INDEX idx_graded_at (graded_at),
    INDEX idx_is_final (is_final)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

## Attendance Tracking

### Attendance Records
```sql
CREATE TABLE attendance (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    course_id INT NOT NULL,
    date DATE NOT NULL,
    
    -- Time Records
    check_in_time TIME NULL,
    check_out_time TIME NULL,
    expected_duration_minutes INT DEFAULT 60,
    actual_duration_minutes INT NULL,
    
    -- Status and Notes
    status ENUM('present', 'absent', 'late', 'excused', 'half_day') DEFAULT 'absent',
    late_minutes INT DEFAULT 0,
    notes TEXT,
    
    -- Manual Adjustments
    is_manually_adjusted BOOLEAN DEFAULT FALSE,
    adjusted_by INT NULL,
    adjustment_reason VARCHAR(255),
    adjusted_at TIMESTAMP NULL,
    
    -- Location Tracking
    location VARCHAR(255),
    ip_address VARCHAR(45),
    device_info VARCHAR(255),
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (adjusted_by) REFERENCES users(id) ON DELETE SET NULL ON UPDATE CASCADE,
    
    UNIQUE KEY unique_attendance (student_id, course_id, date),
    INDEX idx_student_id (student_id),
    INDEX idx_course_id (course_id),
    INDEX idx_date (date),
    INDEX idx_status (status),
    INDEX idx_check_in_time (check_in_time)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### Attendance Policies
Course-specific attendance policies.

```sql
CREATE TABLE attendance_policies (
    id INT AUTO_INCREMENT PRIMARY KEY,
    course_id INT NOT NULL UNIQUE,
    
    -- Policy Settings
    required_attendance_rate DECIMAL(5,2) DEFAULT 80.00,
    track_daily_attendance BOOLEAN DEFAULT TRUE,
    track_by_time BOOLEAN DEFAULT FALSE,
    
    -- Late Policy
    late_arrival_tolerance_minutes INT DEFAULT 10,
    late_penalty_per_occurrence DECIMAL(5,2) DEFAULT 0.00,
    
    -- Absence Policy
    allow_absence_notifications BOOLEAN DEFAULT TRUE,
    require_absence_justification BOOLEAN DEFAULT FALSE,
    max_unexcused_absences INT DEFAULT 3,
    
    -- Grade Impact
    attendance_grade_weight DECIMAL(5,2) DEFAULT 10.00,
    
    -- Weekend/Holiday Settings
    skip_weekends BOOLEAN DEFAULT TRUE,
    skip_holidays BOOLEAN DEFAULT TRUE,
    custom_schedule JSON, -- Course-specific schedule
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE ON UPDATE CASCADE,
    
    INDEX idx_course_id (course_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

## File Management

### File Uploads
```sql
CREATE TABLE files (
    id INT AUTO_INCREMENT PRIMARY KEY,
    filename VARCHAR(255) NOT NULL,
    original_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    file_size BIGINT NOT NULL,
    mime_type VARCHAR(100) NOT NULL,
    file_hash VARCHAR(64) NOT NULL,
    
    -- File Context
    type ENUM('avatar', 'assignment', 'resource', 'syllabus', 'audio', 'video', 'other') NOT NULL,
    uploaded_by INT NOT NULL,
    
    -- Related Context
    course_id INT NULL,
    assignment_id INT NULL,
    submission_id INT NULL,
    
    -- File Details
    description TEXT,
    tags JSON,
    
    -- Access Control
    is_public BOOLEAN DEFAULT FALSE,
    access_level ENUM('private', 'course', 'public') DEFAULT 'private',
    
    -- Processing
    is_processed BOOLEAN DEFAULT FALSE,
    processing_status ENUM('pending', 'processing', 'completed', 'failed') DEFAULT 'pending',
    processing_errors TEXT,
    
    -- Versioning
    version INT DEFAULT 1,
    parent_file_id INT NULL,
    
    -- Usage Tracking
    download_count INT DEFAULT 0,
    last_accessed TIMESTAMP NULL,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE RESTRICT ON UPDATE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE SET NULL ON UPDATE CASCADE,
    FOREIGN KEY (assignment_id) REFERENCES assignments(id) ON DELETE SET NULL ON UPDATE CASCADE,
    FOREIGN KEY (submission_id) REFERENCES submissions(id) ON DELETE SET NULL ON UPDATE CASCADE,
    FOREIGN KEY (parent_file_id) REFERENCES files(id) ON DELETE SET NULL ON UPDATE CASCADE,
    
    INDEX idx_filename (filename),
    INDEX idx_type (type),
    INDEX idx_uploaded_by (uploaded_by),
    INDEX idx_course_id (course_id),
    INDEX idx_assignment_id (assignment_id),
    INDEX idx_file_hash (file_hash),
    INDEX idx_is_public (is_public),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

## Security and Audit

### Activity Logs
```sql
CREATE TABLE activity_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    action VARCHAR(100) NOT NULL,
    resource_type VARCHAR(50) NOT NULL,
    resource_id INT NULL,
    
    -- Action Details
    description TEXT NOT NULL,
    old_values JSON,
    new_values JSON,
    
    -- Request Information
    ip_address VARCHAR(45),
    user_agent TEXT,
    session_id VARCHAR(64),
    
    -- Context
    request_method VARCHAR(10),
    request_url VARCHAR(500),
    request_params JSON,
    
    -- System Information
    severity ENUM('info', 'warning', 'error', 'critical') DEFAULT 'info',
    tags JSON,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL ON UPDATE CASCADE,
    
    INDEX idx_user_id (user_id),
    INDEX idx_action (action),
    INDEX idx_resource_type (resource_type),
    INDEX idx_resource_id (resource_id),
    INDEX idx_severity (severity),
    INDEX idx_created_at (created_at),
    INDEX idx_ip_address (ip_address)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### Security Events
```sql
CREATE TABLE security_events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    event_type VARCHAR(50) NOT NULL,
    severity ENUM('low', 'medium', 'high', 'critical') NOT NULL,
    description TEXT NOT NULL,
    
    -- Event Details
    event_data JSON,
    affected_user_id INT NULL,
    
    -- Request Information
    ip_address VARCHAR(45),
    user_agent TEXT,
    request_url VARCHAR(500),
    
    -- Response
    action_taken VARCHAR(100),
    resolved BOOLEAN DEFAULT FALSE,
    resolved_by INT NULL,
    resolved_at TIMESTAMP NULL,
    resolution_notes TEXT,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (affected_user_id) REFERENCES users(id) ON DELETE SET NULL ON UPDATE CASCADE,
    FOREIGN KEY (resolved_by) REFERENCES users(id) ON DELETE SET NULL ON UPDATE CASCADE,
    
    INDEX idx_event_type (event_type),
    INDEX idx_severity (severity),
    INDEX idx_affected_user_id (affected_user_id),
    INDEX idx_ip_address (ip_address),
    INDEX idx_resolved (resolved),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

## Indexes and Performance

### Performance Indexes
```sql
-- Composite indexes for common queries
CREATE INDEX idx_users_active_teachers ON users(role, status) WHERE role = 'teacher' AND status = 'active';
CREATE INDEX idx_enrollments_active_course ON enrollments(course_id, status) WHERE status = 'active';
CREATE INDEX idx_assignments_due_course ON assignments(course_id, due_date) WHERE status = 'published';
CREATE INDEX idx_submissions_late ON submissions(is_late, submitted_at) WHERE is_late = TRUE;

-- Full-text indexes
ALTER TABLE courses ADD FULLTEXT(name, description);
ALTER TABLE assignments ADD FULLTEXT(title, description);
ALTER TABLE submissions ADD FULLTEXT(submission_text);

-- Partial indexes for performance
CREATE INDEX idx_attendance_2025 ON attendance(date) WHERE date >= '2025-01-01';
CREATE INDEX idx_active_sessions ON user_sessions(expires_at, is_active) WHERE is_active = TRUE;
```

### Query Optimization
```sql
-- Optimized query examples

-- Get course enrollments with user details
EXPLAIN SELECT 
    e.id, e.enrollment_date, e.status,
    u.id, u.username, u.first_name, u.last_name, u.email,
    c.name as course_name, c.code
FROM enrollments e
JOIN users u ON e.student_id = u.id
JOIN courses c ON e.course_id = c.id
WHERE e.status = 'active' AND c.status = 'active'
ORDER BY u.last_name, u.first_name;

-- Get assignment submission statistics
EXPLAIN SELECT 
    a.id, a.title, a.points, a.due_date,
    COUNT(s.id) as total_submissions,
    COUNT(CASE WHEN s.submitted_at <= a.due_date THEN 1 END) as on_time_submissions,
    AVG(s.total_score) as average_score
FROM assignments a
LEFT JOIN submissions s ON a.id = s.assignment_id
WHERE a.course_id = 45 AND a.status = 'published'
GROUP BY a.id;
```

## Database Functions

### Utility Functions
```sql
DELIMITER //

-- Calculate years of service
CREATE FUNCTION get_years_of_service(hire_date DATE)
RETURNS INT
DETERMINISTIC
BEGIN
    RETURN FLOOR(DATEDIFF(CURDATE(), hire_date) / 365.25);
END//

-- Calculate attendance percentage
CREATE FUNCTION calculate_attendance_rate(
    student_id INT, 
    course_id INT, 
    start_date DATE, 
    end_date DATE
)
RETURNS DECIMAL(5,2)
DETERMINISTIC
BEGIN
    DECLARE total_sessions INT;
    DECLARE attended_sessions INT;
    
    SELECT COUNT(*) INTO total_sessions
    FROM attendance 
    WHERE student_id = student_id 
      AND course_id = course_id 
      AND date BETWEEN start_date AND end_date;
    
    SELECT COUNT(*) INTO attended_sessions
    FROM attendance 
    WHERE student_id = student_id 
      AND course_id = course_id 
      AND date BETWEEN start_date AND end_date 
      AND status IN ('present', 'late', 'half_day');
    
    IF total_sessions = 0 THEN
        RETURN 0.00;
    END IF;
    
    RETURN ROUND((attended_sessions / total_sessions) * 100, 2);
END//

-- Calculate grade point average
CREATE FUNCTION calculate_gpa(student_id INT)
RETURNS DECIMAL(4,2)
DETERMINISTIC
BEGIN
    DECLARE total_points DECIMAL(8,2) DEFAULT 0;
    DECLARE total_credits INT DEFAULT 0;
    DECLARE gpa DECIMAL(4,2) DEFAULT 0;
    
    SELECT 
        SUM(CASE WHEN g.letter_grade IN ('A', 'A-') THEN c.credits * 4.0
                 WHEN g.letter_grade IN ('B+') THEN c.credits * 3.5
                 WHEN g.letter_grade IN ('B', 'B-') THEN c.credits * 3.0
                 WHEN g.letter_grade IN ('C+') THEN c.credits * 2.5
                 WHEN g.letter_grade IN ('C', 'C-') THEN c.credits * 2.0
                 WHEN g.letter_grade = 'D' THEN c.credits * 1.0
                 ELSE 0 END) INTO total_points,
        SUM(c.credits) INTO total_credits
    FROM grades g
    JOIN assignments a ON g.assignment_id = a.id
    JOIN courses c ON a.course_id = c.id
    WHERE g.student_id = student_id AND g.is_final = TRUE;
    
    IF total_credits > 0 THEN
        SET gpa = total_points / total_credits;
    END IF;
    
    RETURN gpa;
END//

DELIMITER ;
```

## Views

### User Statistics View
```sql
CREATE OR REPLACE VIEW user_statistics AS
SELECT 
    u.id,
    u.username,
    u.email,
    u.first_name,
    u.last_name,
    u.role,
    u.status,
    u.created_at,
    u.last_login,
    
    -- Course statistics
    (SELECT COUNT(*) FROM enrollments WHERE student_id = u.id AND status = 'active') as active_courses,
    (SELECT COUNT(*) FROM enrollments WHERE student_id = u.id) as total_courses,
    (SELECT COUNT(*) FROM assignments a 
     JOIN enrollments e ON a.course_id = e.course_id 
     WHERE e.student_id = u.id AND a.status = 'published') as total_assignments,
    (SELECT COUNT(*) FROM submissions WHERE student_id = u.id) as total_submissions,
    
    -- Attendance statistics
    (SELECT COUNT(*) FROM attendance a2 
     JOIN enrollments e2 ON a2.student_id = e2.student_id AND a2.course_id = e2.course_id
     WHERE a2.student_id = u.id AND a2.status = 'present' 
     AND a2.date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)) as attendance_last_30_days,
    
    -- Login statistics
    (SELECT COUNT(*) FROM user_sessions WHERE user_id = u.id AND created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)) as sessions_last_30_days
    
FROM users u;
```

### Course Overview View
```sql
CREATE OR REPLACE VIEW course_overview AS
SELECT 
    c.id,
    c.name,
    c.code,
    c.description,
    c.status,
    c.credits,
    c.start_date,
    c.end_date,
    CONCAT(u.first_name, ' ', u.last_name) as teacher_name,
    
    -- Enrollment statistics
    (SELECT COUNT(*) FROM enrollments WHERE course_id = c.id AND status = 'active') as active_students,
    (SELECT COUNT(*) FROM enrollments WHERE course_id = c.id) as total_enrollments,
    
    -- Assignment statistics
    (SELECT COUNT(*) FROM assignments WHERE course_id = c.id AND status = 'published') as published_assignments,
    (SELECT COUNT(*) FROM assignments WHERE course_id = c.id) as total_assignments,
    
    -- Grade statistics
    (SELECT AVG(total_score) FROM submissions s 
     JOIN assignments a ON s.assignment_id = a.id 
     WHERE a.course_id = c.id AND s.status = 'graded') as average_grade,
     
    -- Attendance statistics
    (SELECT AVG(
        (SELECT COUNT(*) FROM attendance a2 
         WHERE a2.course_id = c.id AND a2.status = 'present' AND a2.student_id = e.student_id) / 
        (SELECT COUNT(*) FROM attendance a3 
         WHERE a3.course_id = c.id AND a3.student_id = e.student_id) * 100
     ) FROM enrollments e 
     WHERE e.course_id = c.id AND e.status = 'active') as average_attendance_rate
     
FROM courses c
JOIN users u ON c.teacher_id = u.id;
```

### Recent Activity View
```sql
CREATE OR REPLACE VIEW recent_activity AS
SELECT 
    'assignment_created' as activity_type,
    a.id as resource_id,
    a.title as resource_name,
    a.course_id,
    c.name as course_name,
    a.created_at,
    CONCAT(u.first_name, ' ', u.last_name) as user_name,
    u.id as user_id
FROM assignments a
JOIN courses c ON a.course_id = c.id
JOIN users u ON a.created_by = u.id

UNION ALL

SELECT 
    'submission_created' as activity_type,
    s.id as resource_id,
    CONCAT('Submission for ', a.title) as resource_name,
    a.course_id,
    c.name as course_name,
    s.submitted_at as created_at,
    CONCAT(u.first_name, ' ', u.last_name) as user_name,
    u.id as user_id
FROM submissions s
JOIN assignments a ON s.assignment_id = a.id
JOIN courses c ON a.course_id = c.id
JOIN users u ON s.student_id = u.id
WHERE s.status = 'submitted'

UNION ALL

SELECT 
    'grade_assigned' as activity_type,
    g.id as resource_id,
    CONCAT('Grade for ', a.title) as resource_name,
    a.course_id,
    c.name as course_name,
    g.graded_at as created_at,
    CONCAT(u.first_name, ' ', u.last_name) as user_name,
    u.id as user_id
FROM grades g
JOIN assignments a ON g.assignment_id = a.id
JOIN courses c ON a.course_id = c.id
JOIN users u ON g.grader_id = u.id
WHERE g.is_final = TRUE

ORDER BY created_at DESC
LIMIT 100;
```

## Triggers

### Audit Triggers
```sql
DELIMITER //

-- User audit trigger
CREATE TRIGGER users_audit_insert
AFTER INSERT ON users
FOR EACH ROW
BEGIN
    INSERT INTO activity_logs (
        user_id, action, resource_type, resource_id, 
        description, ip_address, user_agent
    ) VALUES (
        NEW.id, 'user_created', 'user', NEW.id,
        CONCAT('User created: ', NEW.username, ' (', NEW.role, ')'),
        @current_ip, @current_user_agent
    );
END//

CREATE TRIGGER users_audit_update
AFTER UPDATE ON users
FOR EACH ROW
BEGIN
    IF OLD.status != NEW.status THEN
        INSERT INTO activity_logs (
            user_id, action, resource_type, resource_id,
            description, old_values, new_values, ip_address, user_agent
        ) VALUES (
            NEW.id, 'user_status_changed', 'user', NEW.id,
            CONCAT('Status changed from ', OLD.status, ' to ', NEW.status),
            JSON_OBJECT('status', OLD.status),
            JSON_OBJECT('status', NEW.status),
            @current_ip, @current_user_agent
        );
    END IF;
END//

-- Assignment submission trigger
CREATE TRIGGER submissions_audit_insert
AFTER INSERT ON submissions
FOR EACH ROW
BEGIN
    INSERT INTO activity_logs (
        user_id, action, resource_type, resource_id,
        description, ip_address, user_agent
    ) VALUES (
        NEW.student_id, 'submission_created', 'submission', NEW.id,
        CONCAT('Assignment submission created (ID: ', NEW.assignment_id, ')'),
        @current_ip, @current_user_agent
    );
    
    -- Check for late submission
    IF NEW.is_late THEN
        INSERT INTO activity_logs (
            user_id, action, resource_type, resource_id,
            description, severity, ip_address, user_agent
        ) VALUES (
            NEW.student_id, 'late_submission', 'submission', NEW.id,
            CONCAT('Late assignment submission (', NEW.late_minutes, ' minutes)'),
            'warning', @current_ip, @current_user_agent
        );
    END IF;
END//

-- Login attempt tracking
CREATE TRIGGER login_attempt_insert
AFTER INSERT ON activity_logs
FOR EACH ROW
BEGIN
    IF NEW.action = 'login_failed' THEN
        UPDATE users 
        SET failed_attempts = failed_attempts + 1,
            last_failed_attempt = NEW.created_at
        WHERE id = NEW.user_id;
    END IF;
    
    IF NEW.action = 'login_success' THEN
        UPDATE users 
        SET failed_attempts = 0,
            last_login = NEW.created_at,
            login_count = login_count + 1
        WHERE id = NEW.user_id;
    END IF;
END//

DELIMITER ;
```

## Maintenance

### Automated Cleanup Procedures
```sql
DELIMITER //

CREATE PROCEDURE CleanupOldData()
BEGIN
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        RESIGNAL;
    END;
    
    START TRANSACTION;
    
    -- Clean up old activity logs (keep 1 year)
    DELETE FROM activity_logs 
    WHERE created_at < DATE_SUB(NOW(), INTERVAL 1 YEAR)
    AND severity = 'info';
    
    -- Clean up expired sessions
    DELETE FROM user_sessions 
    WHERE expires_at < NOW() OR (last_activity < DATE_SUB(NOW(), INTERVAL 30 DAY) AND is_active = FALSE);
    
    -- Clean up old security events (keep 6 months for non-critical)
    DELETE FROM security_events 
    WHERE severity = 'low' AND created_at < DATE_SUB(NOW(), INTERVAL 6 MONTH);
    
    -- Clean up draft submissions older than 30 days
    DELETE FROM submissions 
    WHERE status = 'draft' AND created_at < DATE_SUB(NOW(), INTERVAL 30 DAY);
    
    -- Reset failed attempts for users who haven't attempted login in 24 hours
    UPDATE users 
    SET failed_attempts = 0, last_failed_attempt = NULL 
    WHERE failed_attempts > 0 
    AND (last_failed_attempt < DATE_SUB(NOW(), INTERVAL 24 HOUR) OR last_failed_attempt IS NULL);
    
    -- Update file access statistics
    UPDATE files f
    SET download_count = (
        SELECT COUNT(*) 
        FROM activity_logs al 
        WHERE al.resource_type = 'file' 
        AND al.resource_id = f.id 
        AND al.action = 'file_downloaded'
    )
    WHERE f.updated_at < DATE_SUB(NOW(), INTERVAL 1 DAY);
    
    COMMIT;
END//

-- Schedule cleanup (requires event scheduler)
-- SET GLOBAL event_scheduler = ON;
-- CREATE EVENT IF NOT EXISTS cleanup_event
-- ON SCHEDULE EVERY 1 DAY
-- DO CALL CleanupOldData();

DELIMITER ;
```

### Backup and Recovery
```sql
-- Daily backup procedure
DELIMITER //

CREATE PROCEDURE CreateDailyBackup()
BEGIN
    DECLARE backup_name VARCHAR(255);
    SET backup_name = CONCAT('classroom_backup_', DATE_FORMAT(NOW(), '%Y%m%d_%H%i%s'));
    
    -- Create backup using system command (requires appropriate permissions)
    SET @sql = CONCAT('mysqldump --single-transaction --routines --triggers ',
                     '--databases ', DATABASE(), ' > /backup/', backup_name, '.sql');
    PREPARE stmt FROM @sql;
    EXECUTE stmt;
    DEALLOCATE PREPARE stmt;
    
    -- Log the backup
    INSERT INTO activity_logs (
        user_id, action, resource_type, resource_id,
        description, severity
    ) VALUES (
        NULL, 'database_backup', 'system', NULL,
        CONCAT('Daily backup created: ', backup_name), 'info'
    );
END//

DELIMITER ;
```

### Performance Monitoring
```sql
-- Create performance monitoring views
CREATE OR REPLACE VIEW slow_queries AS
SELECT 
    query_time,
    lock_time,
    rows_sent,
    rows_examined,
    sql_text,
    db,
    user_host
FROM mysql.slow_log
WHERE start_time >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
ORDER BY query_time DESC;

CREATE OR REPLACE VIEW table_sizes AS
SELECT 
    table_name,
    table_rows,
    ROUND(((data_length + index_length) / 1024 / 1024), 2) AS size_mb,
    engine
FROM information_schema.TABLES
WHERE table_schema = DATABASE()
ORDER BY (data_length + index_length) DESC;

CREATE OR REPLACE VIEW index_usage AS
SELECT 
    TABLE_NAME,
    INDEX_NAME,
    NON_UNIQUE,
    SEQ_IN_INDEX,
    COLUMN_NAME,
    CARDINALITY
FROM information_schema.STATISTICS
WHERE TABLE_SCHEMA = DATABASE()
ORDER BY TABLE_NAME, INDEX_NAME, SEQ_IN_INDEX;
```

This comprehensive database schema documentation provides a detailed overview of the Google Classroom Clone database structure, including relationships, performance optimizations, security features, and maintenance procedures.
