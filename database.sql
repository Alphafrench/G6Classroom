-- ============================================================================
-- EDUCATIONAL PLATFORM DATABASE SCHEMA
-- Inspired by Google Classroom Features
-- ============================================================================

-- Set database to use UTF8 encoding and proper time zone
SET NAMES utf8mb4;
SET time_zone = '+00:00';

-- ============================================================================
-- USER MANAGEMENT TABLES
-- ============================================================================

-- Users table: Stores information for teachers, students, and admins
CREATE TABLE users (
    user_id INT PRIMARY KEY AUTO_INCREMENT,
    email VARCHAR(255) UNIQUE NOT NULL,
    username VARCHAR(100) UNIQUE NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    avatar_url VARCHAR(500),
    role ENUM('admin', 'teacher', 'student') NOT NULL DEFAULT 'student',
    school_id INT,
    phone VARCHAR(20),
    bio TEXT,
    timezone VARCHAR(50) DEFAULT 'UTC',
    is_active BOOLEAN DEFAULT TRUE,
    last_login TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_user_email (email),
    INDEX idx_user_role (role),
    INDEX idx_user_school (school_id),
    INDEX idx_user_active (is_active),
    INDEX idx_user_login (last_login)
) ENGINE=InnoDB;

-- Schools/Institutions table
CREATE TABLE schools (
    school_id INT PRIMARY KEY AUTO_INCREMENT,
    school_name VARCHAR(255) NOT NULL,
    school_code VARCHAR(50) UNIQUE NOT NULL,
    address TEXT,
    phone VARCHAR(20),
    email VARCHAR(255),
    website VARCHAR(255),
    logo_url VARCHAR(500),
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_school_code (school_code),
    INDEX idx_school_active (is_active)
) ENGINE=InnoDB;

-- ============================================================================
-- COURSE MANAGEMENT TABLES
-- ============================================================================

-- Courses table: Stores course/class information
CREATE TABLE courses (
    course_id INT PRIMARY KEY AUTO_INCREMENT,
    course_name VARCHAR(255) NOT NULL,
    course_description TEXT,
    course_code VARCHAR(50) NOT NULL,
    section VARCHAR(100),
    subject VARCHAR(100),
    grade_level VARCHAR(50),
    room VARCHAR(50),
    teacher_id INT NOT NULL,
    school_id INT,
    course_state ENUM('active', 'archived', 'draft') DEFAULT 'active',
    is_public BOOLEAN DEFAULT FALSE,
    calendar_id VARCHAR(255),
    guard_invite_email_permission ENUM('all', 'teachers', 'course_teachers') DEFAULT 'teachers',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (teacher_id) REFERENCES users(user_id) ON DELETE RESTRICT,
    FOREIGN KEY (school_id) REFERENCES schools(school_id) ON DELETE SET NULL,
    INDEX idx_course_teacher (teacher_id),
    INDEX idx_course_school (school_id),
    INDEX idx_course_state (course_state),
    INDEX idx_course_code (course_code),
    INDEX idx_course_public (is_public)
) ENGINE=InnoDB;

-- ============================================================================
-- ENROLLMENT AND ROSTER MANAGEMENT
-- ============================================================================

-- Enrollments table: Manages student enrollment in courses
CREATE TABLE enrollments (
    enrollment_id INT PRIMARY KEY AUTO_INCREMENT,
    course_id INT NOT NULL,
    student_id INT NOT NULL,
    enrollment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('active', 'completed', 'dropped') DEFAULT 'active',
    grade VARCHAR(5),
    teacher_id INT NOT NULL, -- Who enrolled the student
    
    FOREIGN KEY (course_id) REFERENCES courses(course_id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (teacher_id) REFERENCES users(user_id) ON DELETE RESTRICT,
    UNIQUE KEY unique_enrollment (course_id, student_id),
    INDEX idx_enrollment_course (course_id),
    INDEX idx_enrollment_student (student_id),
    INDEX idx_enrollment_status (status),
    INDEX idx_enrollment_date (enrollment_date)
) ENGINE=InnoDB;

-- ============================================================================
-- ASSIGNMENT SYSTEM TABLES
-- ============================================================================

-- Assignment categories for organizing assignments
CREATE TABLE assignment_categories (
    category_id INT PRIMARY KEY AUTO_INCREMENT,
    course_id INT NOT NULL,
    category_name VARCHAR(100) NOT NULL,
    description TEXT,
    weight DECIMAL(5,2) DEFAULT 0, -- Weight in final grade calculation
    color VARCHAR(7) DEFAULT '#3366CC', -- Hex color code
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (course_id) REFERENCES courses(course_id) ON DELETE CASCADE,
    INDEX idx_category_course (course_id),
    INDEX idx_category_sort (sort_order)
) ENGINE=InnoDB;

-- Assignments/Coursework table
CREATE TABLE assignments (
    assignment_id INT PRIMARY KEY AUTO_INCREMENT,
    course_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    instructions TEXT,
    category_id INT,
    assignment_type ENUM('ASSIGNMENT', 'SHORT_ANSWER_QUESTION', 'MULTIPLE_CHOICE_QUESTION', 'QUIZ_ASSIGNMENT') DEFAULT 'ASSIGNMENT',
    max_points DECIMAL(6,2),
    due_date TIMESTAMP NULL,
    published_date TIMESTAMP NULL,
    state ENUM('draft', 'published', 'archived') DEFAULT 'draft',
    allow_late_submissions BOOLEAN DEFAULT TRUE,
    max_points_override BOOLEAN DEFAULT FALSE, -- Allow teachers to override max points
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (course_id) REFERENCES courses(course_id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES assignment_categories(category_id) ON DELETE SET NULL,
    INDEX idx_assignment_course (course_id),
    INDEX idx_assignment_category (category_id),
    INDEX idx_assignment_state (state),
    INDEX idx_assignment_due (due_date),
    INDEX idx_assignment_type (assignment_type),
    INDEX idx_assignment_published (published_date)
) ENGINE=InnoDB;

-- ============================================================================
-- SUBMISSION SYSTEM TABLES
-- ============================================================================

-- Student submissions for assignments
CREATE TABLE submissions (
    submission_id INT PRIMARY KEY AUTO_INCREMENT,
    assignment_id INT NOT NULL,
    student_id INT NOT NULL,
    state ENUM('new', 'turned_in', 'returned', 'reclaimed_by_student') DEFAULT 'new',
    submission_date TIMESTAMP NULL,
    late_submission BOOLEAN DEFAULT FALSE,
    grade_points DECIMAL(6,2) NULL,
    grade_letter VARCHAR(5) NULL,
    late_penalty DECIMAL(6,2) DEFAULT 0,
    draft_grade DECIMAL(6,2) NULL,
    assigned_grade DECIMAL(6,2) NULL,
    rubric_feedback JSON,
    teacher_feedback TEXT,
    private_comments TEXT,
    return_date TIMESTAMP NULL,
    modified_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (assignment_id) REFERENCES assignments(assignment_id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES users(user_id) ON DELETE CASCADE,
    UNIQUE KEY unique_submission (assignment_id, student_id),
    INDEX idx_submission_assignment (assignment_id),
    INDEX idx_submission_student (student_id),
    INDEX idx_submission_state (state),
    INDEX idx_submission_grade (assigned_grade),
    INDEX idx_submission_date (submission_date)
) ENGINE=InnoDB;

-- Submission attachments (files, links, etc.)
CREATE TABLE submission_attachments (
    attachment_id INT PRIMARY KEY AUTO_INCREMENT,
    submission_id INT NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    file_type VARCHAR(100),
    file_size BIGINT,
    drive_file_id VARCHAR(255), -- Google Drive integration
    is_drive_file BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (submission_id) REFERENCES submissions(submission_id) ON DELETE CASCADE,
    INDEX idx_attachment_submission (submission_id),
    INDEX idx_attachment_type (file_type)
) ENGINE=InnoDB;

-- ============================================================================
-- COURSE MATERIALS TABLES
-- ============================================================================

-- Course materials (documents, videos, links, etc.)
CREATE TABLE course_materials (
    material_id INT PRIMARY KEY AUTO_INCREMENT,
    course_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    material_type ENUM('link', 'pdf', 'video', 'document', 'image', 'presentation') NOT NULL,
    file_path VARCHAR(500),
    file_url VARCHAR(500),
    drive_file_id VARCHAR(255),
    thumbnail_url VARCHAR(500),
    file_size BIGINT,
    file_type VARCHAR(100),
    is_published BOOLEAN DEFAULT TRUE,
    sort_order INT DEFAULT 0,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (course_id) REFERENCES courses(course_id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(user_id) ON DELETE RESTRICT,
    INDEX idx_material_course (course_id),
    INDEX idx_material_type (material_type),
    INDEX idx_material_published (is_published),
    INDEX idx_material_sort (sort_order)
) ENGINE=InnoDB;

-- ============================================================================
-- ANNOUNCEMENTS TABLES
-- ============================================================================

-- Course announcements
CREATE TABLE announcements (
    announcement_id INT PRIMARY KEY AUTO_INCREMENT,
    course_id INT NOT NULL,
    title VARCHAR(255),
    content TEXT NOT NULL,
    is_published BOOLEAN DEFAULT TRUE,
    state ENUM('draft', 'published', 'archived') DEFAULT 'published',
    created_by INT NOT NULL,
    publish_date TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (course_id) REFERENCES courses(course_id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(user_id) ON DELETE RESTRICT,
    INDEX idx_announcement_course (course_id),
    INDEX idx_announcement_published (is_published),
    INDEX idx_announcement_state (state),
    INDEX idx_announcement_date (publish_date)
) ENGINE=InnoDB;

-- Announcement materials/attachments
CREATE TABLE announcement_materials (
    material_id INT PRIMARY KEY AUTO_INCREMENT,
    announcement_id INT NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(500),
    file_url VARCHAR(500),
    drive_file_id VARCHAR(255),
    file_type VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (announcement_id) REFERENCES announcements(announcement_id) ON DELETE CASCADE,
    INDEX idx_ann_material_announcement (announcement_id)
) ENGINE=InnoDB;

-- ============================================================================
-- DISCUSSIONS TABLES
-- ============================================================================

-- Discussion topics/threads
CREATE TABLE discussion_topics (
    topic_id INT PRIMARY KEY AUTO_INCREMENT,
    course_id INT NOT NULL,
    assignment_id INT NULL, -- NULL if standalone discussion
    title VARCHAR(255) NOT NULL,
    description TEXT,
    is_pinned BOOLEAN DEFAULT FALSE,
    is_locked BOOLEAN DEFAULT FALSE,
    sort_order INT DEFAULT 0,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (course_id) REFERENCES courses(course_id) ON DELETE CASCADE,
    FOREIGN KEY (assignment_id) REFERENCES assignments(assignment_id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(user_id) ON DELETE RESTRICT,
    INDEX idx_topic_course (course_id),
    INDEX idx_topic_assignment (assignment_id),
    INDEX idx_topic_pinned (is_pinned),
    INDEX idx_topic_locked (is_locked),
    INDEX idx_topic_sort (sort_order)
) ENGINE=InnoDB;

-- Discussion posts/replies
CREATE TABLE discussion_posts (
    post_id INT PRIMARY KEY AUTO_INCREMENT,
    topic_id INT NOT NULL,
    parent_post_id INT NULL, -- NULL for original posts, not NULL for replies
    content TEXT NOT NULL,
    is_edited BOOLEAN DEFAULT FALSE,
    is_deleted BOOLEAN DEFAULT FALSE,
    edited_at TIMESTAMP NULL,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (topic_id) REFERENCES discussion_topics(topic_id) ON DELETE CASCADE,
    FOREIGN KEY (parent_post_id) REFERENCES discussion_posts(post_id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(user_id) ON DELETE RESTRICT,
    INDEX idx_post_topic (topic_id),
    INDEX idx_post_parent (parent_post_id),
    INDEX idx_post_user (created_by),
    INDEX idx_post_created (created_at),
    INDEX idx_post_deleted (is_deleted)
) ENGINE=InnoDB;

-- ============================================================================
-- ATTENDANCE TRACKING TABLES
-- ============================================================================

-- Course sessions for attendance tracking
CREATE TABLE course_sessions (
    session_id INT PRIMARY KEY AUTO_INCREMENT,
    course_id INT NOT NULL,
    session_date DATE NOT NULL,
    start_time TIME,
    end_time TIME,
    session_title VARCHAR(255),
    session_description TEXT,
    location VARCHAR(255),
    is_virtual BOOLEAN DEFAULT FALSE,
    meeting_link VARCHAR(500),
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (course_id) REFERENCES courses(course_id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(user_id) ON DELETE RESTRICT,
    UNIQUE KEY unique_session (course_id, session_date, start_time),
    INDEX idx_session_course (course_id),
    INDEX idx_session_date (session_date),
    INDEX idx_session_virtual (is_virtual)
) ENGINE=InnoDB;

-- Attendance records
CREATE TABLE attendance_records (
    attendance_id INT PRIMARY KEY AUTO_INCREMENT,
    session_id INT NOT NULL,
    student_id INT NOT NULL,
    status ENUM('present', 'absent', 'late', 'excused', 'virtual_present') NOT NULL,
    attendance_time TIMESTAMP NULL, -- When they joined/recorded
    notes TEXT,
    recorded_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (session_id) REFERENCES course_sessions(session_id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (recorded_by) REFERENCES users(user_id) ON DELETE RESTRICT,
    UNIQUE KEY unique_attendance (session_id, student_id),
    INDEX idx_attendance_session (session_id),
    INDEX idx_attendance_student (student_id),
    INDEX idx_attendance_status (status),
    INDEX idx_attendance_time (attendance_time)
) ENGINE=InnoDB;

-- ============================================================================
-- GRADING AND RUBRICS TABLES
-- ============================================================================

-- Rubrics for assignment grading
CREATE TABLE rubrics (
    rubric_id INT PRIMARY KEY AUTO_INCREMENT,
    assignment_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    max_points DECIMAL(6,2) NOT NULL,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (assignment_id) REFERENCES assignments(assignment_id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(user_id) ON DELETE RESTRICT,
    INDEX idx_rubric_assignment (assignment_id)
) ENGINE=InnoDB;

-- Rubric criteria
CREATE TABLE rubric_criteria (
    criterion_id INT PRIMARY KEY AUTO_INCREMENT,
    rubric_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    max_points DECIMAL(6,2) NOT NULL,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (rubric_id) REFERENCES rubrics(rubric_id) ON DELETE CASCADE,
    INDEX idx_criterion_rubric (rubric_id),
    INDEX idx_criterion_sort (sort_order)
) ENGINE=InnoDB;

-- Rubric grades/scoring
CREATE TABLE rubric_grades (
    grade_id INT PRIMARY KEY AUTO_INCREMENT,
    submission_id INT NOT NULL,
    criterion_id INT NOT NULL,
    score DECIMAL(6,2) NOT NULL,
    feedback TEXT,
    graded_by INT NOT NULL,
    graded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (submission_id) REFERENCES submissions(submission_id) ON DELETE CASCADE,
    FOREIGN KEY (criterion_id) REFERENCES rubric_criteria(criterion_id) ON DELETE CASCADE,
    FOREIGN KEY (graded_by) REFERENCES users(user_id) ON DELETE RESTRICT,
    UNIQUE KEY unique_grade (submission_id, criterion_id),
    INDEX idx_grade_submission (submission_id),
    INDEX idx_grade_criterion (criterion_id),
    INDEX idx_grade_points (score)
) ENGINE=InnoDB;

-- ============================================================================
-- CALENDAR AND EVENTS TABLES
-- ============================================================================

-- Calendar events related to courses
CREATE TABLE calendar_events (
    event_id INT PRIMARY KEY AUTO_INCREMENT,
    course_id INT NOT NULL,
    assignment_id INT NULL,
    session_id INT NULL,
    event_title VARCHAR(255) NOT NULL,
    event_description TEXT,
    start_date_time TIMESTAMP NOT NULL,
    end_date_time TIMESTAMP NOT NULL,
    is_all_day BOOLEAN DEFAULT FALSE,
    location VARCHAR(255),
    calendar_provider ENUM('google', 'outlook', 'apple', 'native') DEFAULT 'google',
    external_event_id VARCHAR(255),
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (course_id) REFERENCES courses(course_id) ON DELETE CASCADE,
    FOREIGN KEY (assignment_id) REFERENCES assignments(assignment_id) ON DELETE CASCADE,
    FOREIGN KEY (session_id) REFERENCES course_sessions(session_id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(user_id) ON DELETE RESTRICT,
    INDEX idx_event_course (course_id),
    INDEX idx_event_assignment (assignment_id),
    INDEX idx_event_session (session_id),
    INDEX idx_event_start (start_date_time),
    INDEX idx_event_end (end_date_time),
    INDEX idx_event_all_day (is_all_day)
) ENGINE=InnoDB;

-- ============================================================================
-- NOTIFICATIONS AND ACTIVITY TABLES
-- ============================================================================

-- User notifications
CREATE TABLE notifications (
    notification_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    notification_type ENUM('assignment', 'grade', 'announcement', 'discussion', 'attendance', 'course', 'system') NOT NULL,
    related_id INT NULL, -- ID of related item (assignment_id, course_id, etc.)
    related_type VARCHAR(50) NULL, -- Type of related item
    is_read BOOLEAN DEFAULT FALSE,
    priority ENUM('low', 'normal', 'high') DEFAULT 'normal',
    action_url VARCHAR(500),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    INDEX idx_notification_user (user_id),
    INDEX idx_notification_type (notification_type),
    INDEX idx_notification_read (is_read),
    INDEX idx_notification_created (created_at),
    INDEX idx_notification_priority (priority)
) ENGINE=InnoDB;

-- Activity log for audit trails
CREATE TABLE activity_log (
    log_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    course_id INT NULL,
    activity_type ENUM('login', 'assignment_created', 'assignment_submitted', 'grade_assigned', 'announcement_posted', 'discussion_posted', 'material_uploaded', 'attendance_recorded', 'course_created', 'student_enrolled') NOT NULL,
    target_type VARCHAR(50), -- 'assignment', 'submission', 'announcement', etc.
    target_id INT NULL, -- ID of the target item
    description TEXT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(course_id) ON DELETE SET NULL,
    INDEX idx_activity_user (user_id),
    INDEX idx_activity_course (course_id),
    INDEX idx_activity_type (activity_type),
    INDEX idx_activity_target (target_type, target_id),
    INDEX idx_activity_created (created_at)
) ENGINE=InnoDB;

-- ============================================================================
-- PARENT/GUARDIAN INTEGRATION TABLES
-- ============================================================================

-- Parent/Guardian information
CREATE TABLE guardians (
    guardian_id INT PRIMARY KEY AUTO_INCREMENT,
    email VARCHAR(255) UNIQUE NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    relationship VARCHAR(50), -- 'mother', 'father', 'guardian', etc.
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_guardian_email (email),
    INDEX idx_guardian_name (last_name, first_name),
    INDEX idx_guardian_active (is_active)
) ENGINE=InnoDB;

-- Guardian-student relationships
CREATE TABLE guardian_student_relations (
    relation_id INT PRIMARY KEY AUTO_INCREMENT,
    guardian_id INT NOT NULL,
    student_id INT NOT NULL,
    is_primary BOOLEAN DEFAULT FALSE, -- Primary guardian for notifications
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (guardian_id) REFERENCES guardians(guardian_id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES users(user_id) ON DELETE CASCADE,
    UNIQUE KEY unique_guardian_student (guardian_id, student_id),
    INDEX idx_relation_guardian (guardian_id),
    INDEX idx_relation_student (student_id),
    INDEX idx_relation_primary (is_primary)
) ENGINE=InnoDB;

-- Guardian invitations to courses
CREATE TABLE guardian_invitations (
    invitation_id INT PRIMARY KEY AUTO_INCREMENT,
    course_id INT NOT NULL,
    guardian_email VARCHAR(255) NOT NULL,
    invitation_state ENUM('pending', 'accepted', 'declined', 'expired') DEFAULT 'pending',
    invited_by INT NOT NULL,
    accepted_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (course_id) REFERENCES courses(course_id) ON DELETE CASCADE,
    FOREIGN KEY (invited_by) REFERENCES users(user_id) ON DELETE RESTRICT,
    INDEX idx_invitation_course (course_id),
    INDEX idx_invitation_email (guardian_email),
    INDEX idx_invitation_state (invitation_state)
) ENGINE=InnoDB;

-- ============================================================================
-- SAMPLE DATA FOR TESTING
-- ============================================================================

-- Insert sample schools
INSERT INTO schools (school_name, school_code, address, phone, email, website) VALUES
('Lincoln High School', 'LHS', '123 Education Street, Springfield, IL 62701', '555-0123', 'info@lincolnhigh.edu', 'https://lincolnhigh.edu'),
('Washington Elementary', 'WE', '456 Learning Avenue, Springfield, IL 62702', '555-0456', 'contact@washingtonelem.edu', 'https://washingtonelem.edu'),
('Roosevelt Middle School', 'RMS', '789 Knowledge Drive, Springfield, IL 62703', '555-0789', 'info@roosevelt.edu', 'https://roosevelt.edu');

-- Insert sample users
INSERT INTO users (email, username, first_name, last_name, role, school_id, bio, phone) VALUES
-- Admins
('admin@lhs.edu', 'admin001', 'System', 'Administrator', 'admin', 1, 'System Administrator', '555-0001'),
('admin2@lhs.edu', 'admin002', 'Sarah', 'Johnson', 'admin', 2, 'District Administrator', '555-0002'),

-- Teachers
('john.smith@lhs.edu', 'teacher001', 'John', 'Smith', 'teacher', 1, 'Mathematics Teacher - 10 years experience', '555-1001'),
('mary.davis@lhs.edu', 'teacher002', 'Mary', 'Davis', 'teacher', 1, 'Science Teacher - Physics and Chemistry', '555-1002'),
('james.wilson@rms.edu', 'teacher003', 'James', 'Wilson', 'teacher', 3, 'English Literature Teacher', '555-1003'),
('lisa.brown@we.edu', 'teacher004', 'Lisa', 'Brown', 'teacher', 2, 'Elementary Teacher - Grade 5', '555-1004'),
('michael.taylor@lhs.edu', 'teacher005', 'Michael', 'Taylor', 'teacher', 1, 'History and Social Studies', '555-1005'),

-- Students
('student001@lhs.edu', 'student001', 'Alice', 'Johnson', 'student', 1, 'Grade 11 - Class of 2026', NULL),
('student002@lhs.edu', 'student002', 'Bob', 'Williams', 'student', 1, 'Grade 11 - Class of 2026', NULL),
('student003@lhs.edu', 'student003', 'Charlie', 'Davis', 'student', 1, 'Grade 11 - Class of 2026', NULL),
('student004@lhs.edu', 'student004', 'Diana', 'Miller', 'student', 1, 'Grade 12 - Class of 2025', NULL),
('student005@lhs.edu', 'student005', 'Ethan', 'Garcia', 'student', 1, 'Grade 12 - Class of 2025', NULL),
('student006@rms.edu', 'student006', 'Fiona', 'Martinez', 'student', 3, 'Grade 7 - Class of 2028', NULL),
('student007@rms.edu', 'student007', 'George', 'Anderson', 'student', 3, 'Grade 7 - Class of 2028', NULL),
('student008@rms.edu', 'student008', 'Hannah', 'Thompson', 'student', 3, 'Grade 8 - Class of 2027', NULL),
('student009@we.edu', 'student009', 'Ian', 'White', 'student', 2, 'Grade 5 - Class of 2030', NULL),
('student010@we.edu', 'student010', 'Julia', 'Harris', 'student', 2, 'Grade 5 - Class of 2030', NULL),

-- Additional students for testing
('student011@lhs.edu', 'student011', 'Kevin', 'Clark', 'student', 1, 'Grade 10 - Class of 2027', NULL),
('student012@lhs.edu', 'student012', 'Laura', 'Lewis', 'student', 1, 'Grade 10 - Class of 2027', NULL),
('student013@lhs.edu', 'student013', 'Matthew', 'Walker', 'student', 1, 'Grade 9 - Class of 2028', NULL),
('student014@lhs.edu', 'student014', 'Nicole', 'Hall', 'student', 1, 'Grade 9 - Class of 2028', NULL);

-- Insert sample courses
INSERT INTO courses (course_name, course_description, course_code, section, subject, grade_level, room, teacher_id, school_id, is_public) VALUES
('Advanced Mathematics', 'Comprehensive algebra, geometry, and pre-calculus course', 'MATH-101', 'Period 1', 'Mathematics', 'Grade 11', 'Room 101', 3, 1, TRUE),
('Physics Fundamentals', 'Introduction to physics concepts and laboratory work', 'PHYS-201', 'Period 3', 'Science', 'Grade 11', 'Science Lab A', 4, 1, TRUE),
('English Literature', 'Study of classic and contemporary literature', 'ENG-301', 'Period 2', 'English', 'Grade 7', 'Room 205', 5, 3, TRUE),
('Grade 5 Mathematics', 'Elementary mathematics for 5th grade students', 'MATH-501', 'Morning Session', 'Mathematics', 'Grade 5', 'Room 305', 6, 2, TRUE),
('American History', 'Comprehensive study of American history and civics', 'HIST-401', 'Period 4', 'History', 'Grade 12', 'Room 150', 7, 1, TRUE),
('Chemistry Basics', 'Introduction to chemical principles and reactions', 'CHEM-301', 'Period 5', 'Science', 'Grade 12', 'Science Lab B', 4, 1, TRUE);

-- Insert assignment categories
INSERT INTO assignment_categories (course_id, category_name, description, weight, color, sort_order) VALUES
(1, 'Homework', 'Regular homework assignments', 25.00, '#4CAF50', 1),
(1, 'Quizzes', 'Short quizzes and tests', 20.00, '#2196F3', 2),
(1, 'Exams', 'Major examinations', 35.00, '#FF9800', 3),
(1, 'Projects', 'Long-term projects and presentations', 20.00, '#9C27B0', 4),
(2, 'Lab Reports', 'Laboratory experiment reports', 30.00, '#607D8B', 1),
(2, 'Problem Sets', 'Physics problem solving exercises', 25.00, '#795548', 2),
(2, 'Tests', 'Chapter and unit tests', 30.00, '#F44336', 3),
(2, 'Participation', 'Class participation and discussion', 15.00, '#009688', 4);

-- Insert assignments
INSERT INTO assignments (course_id, title, description, category_id, max_points, due_date, state, allow_late_submissions) VALUES
(1, 'Algebra Chapter 1 Test', 'Test on linear equations and inequalities', 3, 100.00, '2025-11-15 23:59:59', 'published', TRUE),
(1, 'Homework Set 3', 'Solve problems 1-20 from textbook page 45', 1, 20.00, '2025-11-08 23:59:59', 'published', TRUE),
(1, 'Quadratic Equations Project', 'Research and presentation on quadratic equations applications', 4, 50.00, '2025-11-25 23:59:59', 'published', TRUE),
(1, 'Weekly Quiz 1', 'Quick assessment on recent topics', 2, 10.00, '2025-11-07 15:30:00', 'published', FALSE),
(2, 'Lab Report: Motion and Forces', 'Write a detailed lab report on the motion experiment', 1, 25.00, '2025-11-20 23:59:59', 'published', TRUE),
(2, 'Problem Set 1: Kinematics', 'Solve kinematics problems 1-15', 2, 30.00, '2025-11-10 23:59:59', 'published', TRUE),
(3, 'Poetry Analysis Essay', 'Analyze assigned poems with proper citations', 1, 75.00, '2025-11-18 23:59:59', 'published', TRUE),
(4, 'Fraction Practice', 'Practice problems with fractions and decimals', 1, 15.00, '2025-11-06 23:59:59', 'published', TRUE);

-- Insert enrollments
INSERT INTO enrollments (course_id, student_id, teacher_id) VALUES
(1, 11, 3), (1, 12, 3), (1, 13, 3), (1, 14, 3), -- Math course students
(2, 11, 4), (2, 12, 4), (2, 13, 4), (2, 14, 4), -- Physics course students
(3, 6, 5), (3, 7, 5), (3, 8, 5),                 -- English course students  
(4, 9, 6), (4, 10, 6),                           -- Elementary math students
(5, 4, 7), (5, 5, 7),                            -- History course students
(2, 4, 4), (2, 5, 4);                            -- Physics students in Grade 12

-- Insert submissions
INSERT INTO submissions (assignment_id, student_id, state, submission_date, grade_points, assigned_grade, teacher_feedback, private_comments) VALUES
(1, 11, 'returned', '2025-11-05 14:30:00', 85.00, 85.00, 'Good work on most problems. Review section 1.3 for improvement.', 'Shows understanding but needs more practice with word problems.'),
(1, 12, 'turned_in', '2025-11-05 15:45:00', NULL, NULL, NULL, NULL),
(1, 13, 'returned', '2025-11-05 16:20:00', 92.00, 92.00, 'Excellent work! Clear explanations and accurate solutions.', NULL),
(2, 11, 'turned_in', '2025-11-05 18:30:00', NULL, NULL, NULL, NULL),
(2, 12, 'turned_in', '2025-11-04 20:15:00', NULL, NULL, NULL, NULL),
(3, 11, 'new', NULL, NULL, NULL, NULL, NULL),
(3, 12, 'new', NULL, NULL, NULL, NULL, NULL),
(4, 11, 'new', NULL, NULL, NULL, NULL, NULL),
(5, 11, 'new', NULL, NULL, NULL, NULL, NULL),
(5, 12, 'new', NULL, NULL, NULL, NULL, NULL);

-- Insert course materials
INSERT INTO course_materials (course_id, title, description, material_type, file_url, file_type, sort_order, created_by) VALUES
(1, 'Algebra Textbook Chapter 1', 'Digital version of algebra textbook chapter 1', 'pdf', 'https://example.com/textbook-ch1.pdf', 'application/pdf', 1, 3),
(1, 'Linear Equations Video', 'Introduction to linear equations', 'video', 'https://youtube.com/watch?v=linear-eqs', 'video/mp4', 2, 3),
(2, 'Physics Lab Safety Guidelines', 'Important safety information for laboratory work', 'document', 'https://docs.google.com/lab-safety', 'application/pdf', 1, 4),
(2, 'Motion and Forces Tutorial', 'Interactive tutorial on motion concepts', 'link', 'https://phets.colorado.edu/simulation/motion', 'text/html', 2, 4),
(3, 'Poetry Collection', 'Selected poems for analysis', 'pdf', 'https://example.com/poetry-collection.pdf', 'application/pdf', 1, 5);

-- Insert announcements
INSERT INTO announcements (course_id, title, content, created_by, publish_date) VALUES
(1, 'Welcome to Advanced Mathematics!', 'Welcome everyone to our Advanced Mathematics course. This will be an exciting year of learning and problem-solving!', 3, '2025-09-01 08:00:00'),
(1, 'Exam Next Week Reminder', 'Don''t forget about the Chapter 1 test next Tuesday. Make sure to review all topics covered so far.', 3, '2025-11-05 10:00:00'),
(2, 'Lab Equipment Check', 'Please check your lab equipment at the beginning of each lab session and report any issues immediately.', 4, '2025-11-01 14:30:00'),
(3, 'Reading Assignment', 'Please read chapters 3-4 from your textbook before our next class discussion.', 5, '2025-11-04 09:15:00');

-- Insert course sessions
INSERT INTO course_sessions (course_id, session_date, start_time, end_time, session_title, location, created_by) VALUES
(1, '2025-11-06', '09:00:00', '09:50:00', 'Linear Equations Review', 'Room 101', 3),
(1, '2025-11-07', '09:00:00', '09:50:00', 'Word Problems Workshop', 'Room 101', 3),
(1, '2025-11-08', '09:00:00', '09:50:00', 'Quiz Day', 'Room 101', 3),
(2, '2025-11-06', '11:00:00', '11:50:00', 'Lab: Motion Experiment', 'Science Lab A', 4),
(2, '2025-11-07', '11:00:00', '11:50:00', 'Kinematics Discussion', 'Science Lab A', 4);

-- Insert attendance records
INSERT INTO attendance_records (session_id, student_id, status, attendance_time, recorded_by) VALUES
(1, 11, 'present', '2025-11-06 08:58:00', 3),
(1, 12, 'late', '2025-11-06 09:05:00', 3),
(1, 13, 'present', '2025-11-06 08:59:00', 3),
(1, 14, 'present', '2025-11-06 08:55:00', 3),
(2, 11, 'present', '2025-11-07 08:58:00', 3),
(2, 12, 'present', '2025-11-07 08:57:00', 3),
(2, 13, 'absent', NULL, 3),
(2, 14, 'present', '2025-11-07 08:59:00', 3),
(3, 11, 'present', '2025-11-08 08:58:00', 3),
(3, 12, 'present', '2025-11-08 08:56:00', 3),
(3, 13, 'present', '2025-11-08 08:58:00', 3),
(3, 14, 'present', '2025-11-08 08:59:00', 3);

-- Insert discussion topics
INSERT INTO discussion_topics (course_id, assignment_id, title, description, is_pinned, created_by) VALUES
(1, NULL, 'Study Group Discussion', 'Discuss homework problems and study tips', TRUE, 3),
(1, 3, 'Quadratic Equations Project Ideas', 'Share your project ideas and get feedback', FALSE, 11),
(2, NULL, 'Physics Concepts Q&A', 'Ask questions about physics concepts', TRUE, 4);

-- Insert discussion posts
INSERT INTO discussion_posts (topic_id, content, created_by) VALUES
(1, 'Has anyone started working on homework set 3? I''m stuck on problem 12.', 11),
(1, 'I finished the homework! Problem 12 is tricky - try breaking it down step by step.', 12),
(1, 'Thanks! That helped a lot. Let''s meet at the library tomorrow to study together.', 11),
(2, 'I''m thinking of doing my project on quadratic equations in architecture. Any suggestions?', 12),
(2, 'Great idea! You could look into parabolic arches and bridges.', 13),
(2, 'I was thinking about that too. Maybe we could collaborate?', 11);

-- Insert guardians
INSERT INTO guardians (email, first_name, last_name, phone, relationship) VALUES
('parent1@email.com', 'Robert', 'Johnson', '555-2001', 'father'),
('parent2@email.com', 'Carol', 'Johnson', '555-2002', 'mother'),
('parent3@email.com', 'David', 'Williams', '555-2003', 'father'),
('parent4@email.com', 'Emma', 'Williams', '555-2004', 'mother'),
('parent5@email.com', 'Frank', 'Davis', '555-2005', 'father');

-- Insert guardian-student relations
INSERT INTO guardian_student_relations (guardian_id, student_id, is_primary) VALUES
(1, 11, TRUE),
(2, 11, FALSE),
(3, 2, TRUE),
(4, 2, FALSE),
(5, 3, TRUE);

-- Insert calendar events
INSERT INTO calendar_events (course_id, assignment_id, event_title, start_date_time, end_date_time, location, created_by) VALUES
(1, 1, 'Math Exam - Chapter 1', '2025-11-15 09:00:00', '2025-11-15 10:30:00', 'Room 101', 3),
(1, 2, 'Homework Due', '2025-11-08 23:59:59', '2025-11-08 23:59:59', NULL, 3),
(1, 3, 'Project Presentation Day', '2025-11-25 09:00:00', '2025-11-25 12:00:00', 'Room 101', 3),
(2, 5, 'Physics Lab Session', '2025-11-06 11:00:00', '2025-11-06 11:50:00', 'Science Lab A', 4);

-- Insert notifications
INSERT INTO notifications (user_id, title, message, notification_type, related_id, action_url) VALUES
(11, 'New Assignment Posted', 'A new assignment "Algebra Chapter 1 Test" has been posted in Advanced Mathematics', 'assignment', 1, '/courses/1/assignments/1'),
(12, 'Assignment Due Soon', 'Reminder: "Homework Set 3" is due tomorrow', 'assignment', 2, '/courses/1/assignments/2'),
(11, 'Grade Posted', 'Your grade for "Algebra Chapter 1 Test" has been posted', 'grade', 1, '/courses/1/grades'),
(4, 'New Course Enrollment', 'You have been enrolled in Chemistry Basics', 'course', 6, '/courses/6'),
(11, 'Class Announcement', 'New announcement posted in Advanced Mathematics', 'announcement', 1, '/courses/1');

-- ============================================================================
-- PERFORMANCE OPTIMIZATION AND INDEXES
-- ============================================================================

-- Additional composite indexes for better query performance
CREATE INDEX idx_enrollment_course_status ON enrollments(course_id, status);
CREATE INDEX idx_assignment_course_due ON assignments(course_id, due_date, state);
CREATE INDEX idx_submission_assignment_grade ON submissions(assignment_id, assigned_grade);
CREATE INDEX idx_submission_student_state ON submissions(student_id, state);
CREATE INDEX idx_course_teacher_state ON courses(teacher_id, course_state);
CREATE INDEX idx_activity_user_created ON activity_log(user_id, created_at DESC);
CREATE INDEX idx_notification_user_read ON notifications(user_id, is_read, created_at DESC);

-- Full-text search indexes for content search
ALTER TABLE course_materials ADD FULLTEXT(title, description);
ALTER TABLE announcements ADD FULLTEXT(content);
ALTER TABLE discussion_posts ADD FULLTEXT(content);

-- ============================================================================
-- DATABASE SCHEMA COMPLETE
-- ============================================================================