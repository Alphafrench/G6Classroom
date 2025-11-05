-- Course Management System Database Schema
-- Run this SQL to add course management functionality

-- First, update users table to support teacher and student roles
ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'teacher', 'student', 'employee') DEFAULT 'student';

-- Insert sample teacher and student users if they don't exist
INSERT INTO users (username, email, password, role) VALUES 
('teacher1', 'teacher1@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'teacher'),
('student1', 'student1@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student'),
('teacher2', 'teacher2@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'teacher'),
('student2', 'student2@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student')
ON DUPLICATE KEY UPDATE role = VALUES(role);

-- Courses table
CREATE TABLE IF NOT EXISTS courses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    course_code VARCHAR(20) UNIQUE NOT NULL,
    teacher_id INT NOT NULL,
    semester VARCHAR(50),
    year YEAR,
    credits INT DEFAULT 0,
    max_students INT DEFAULT 50,
    is_active BOOLEAN DEFAULT TRUE,
    start_date DATE,
    end_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_course_code (course_code),
    INDEX idx_teacher_id (teacher_id),
    INDEX idx_semester_year (semester, year),
    INDEX idx_is_active (is_active),
    INDEX idx_created_at (created_at),
    
    FOREIGN KEY (teacher_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Course enrollments table
CREATE TABLE IF NOT EXISTS course_enrollments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    course_id INT NOT NULL,
    student_id INT NOT NULL,
    enrollment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('active', 'dropped', 'completed') DEFAULT 'active',
    grade DECIMAL(5,2),
    final_grade VARCHAR(5),
    notes TEXT,
    
    INDEX idx_course_id (course_id),
    INDEX idx_student_id (student_id),
    INDEX idx_status (status),
    INDEX idx_enrollment_date (enrollment_date),
    
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_enrollment (course_id, student_id)
);

-- Course materials table
CREATE TABLE IF NOT EXISTS course_materials (
    id INT AUTO_INCREMENT PRIMARY KEY,
    course_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    file_path VARCHAR(500),
    file_type VARCHAR(50),
    file_size BIGINT,
    uploaded_by INT NOT NULL,
    material_type ENUM('document', 'video', 'audio', 'image', 'other') DEFAULT 'document',
    is_public BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_course_id (course_id),
    INDEX idx_uploaded_by (uploaded_by),
    INDEX idx_material_type (material_type),
    INDEX idx_is_public (is_public),
    INDEX idx_created_at (created_at),
    
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE CASCADE
);

-- Course announcements table
CREATE TABLE IF NOT EXISTS course_announcements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    course_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    content TEXT NOT NULL,
    posted_by INT NOT NULL,
    priority ENUM('low', 'normal', 'high', 'urgent') DEFAULT 'normal',
    is_published BOOLEAN DEFAULT FALSE,
    publish_date TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_course_id (course_id),
    INDEX idx_posted_by (posted_by),
    INDEX idx_priority (priority),
    INDEX idx_is_published (is_published),
    INDEX idx_publish_date (publish_date),
    
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    FOREIGN KEY (posted_by) REFERENCES users(id) ON DELETE CASCADE
);

-- Course discussions table
CREATE TABLE IF NOT EXISTS course_discussions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    course_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    created_by INT NOT NULL,
    is_locked BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_course_id (course_id),
    INDEX idx_created_by (created_by),
    INDEX idx_is_locked (is_locked),
    INDEX idx_created_at (created_at),
    
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
);

-- Discussion posts table
CREATE TABLE IF NOT EXISTS discussion_posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    discussion_id INT NOT NULL,
    user_id INT NOT NULL,
    content TEXT NOT NULL,
    parent_post_id INT NULL,
    is_edited BOOLEAN DEFAULT FALSE,
    edit_date TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_discussion_id (discussion_id),
    INDEX idx_user_id (user_id),
    INDEX idx_parent_post_id (parent_post_id),
    INDEX idx_created_at (created_at),
    
    FOREIGN KEY (discussion_id) REFERENCES course_discussions(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (parent_post_id) REFERENCES discussion_posts(id) ON DELETE CASCADE
);

-- Course assignments table
CREATE TABLE IF NOT EXISTS course_assignments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    course_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    instructions TEXT,
    max_points INT DEFAULT 100,
    due_date TIMESTAMP NULL,
    assignment_type ENUM('homework', 'quiz', 'exam', 'project', 'other') DEFAULT 'homework',
    is_published BOOLEAN DEFAULT FALSE,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_course_id (course_id),
    INDEX idx_assignment_type (assignment_type),
    INDEX idx_is_published (is_published),
    INDEX idx_due_date (due_date),
    INDEX idx_created_by (created_by),
    
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
);

-- Assignment submissions table
CREATE TABLE IF NOT EXISTS assignment_submissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    assignment_id INT NOT NULL,
    student_id INT NOT NULL,
    submission_text TEXT,
    file_path VARCHAR(500),
    file_name VARCHAR(255),
    submission_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    is_late BOOLEAN DEFAULT FALSE,
    grade DECIMAL(5,2),
    feedback TEXT,
    graded_by INT NULL,
    graded_date TIMESTAMP NULL,
    status ENUM('submitted', 'graded', 'returned') DEFAULT 'submitted',
    
    INDEX idx_assignment_id (assignment_id),
    INDEX idx_student_id (student_id),
    INDEX idx_submission_date (submission_date),
    INDEX idx_status (status),
    INDEX idx_is_late (is_late),
    INDEX idx_graded_by (graded_by),
    
    FOREIGN KEY (assignment_id) REFERENCES course_assignments(id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (graded_by) REFERENCES users(id) ON DELETE SET NULL,
    UNIQUE KEY unique_submission (assignment_id, student_id)
);

-- Create views for better reporting

-- View for teacher courses with enrollment counts
CREATE OR REPLACE VIEW teacher_courses_view AS
SELECT 
    c.id,
    c.title,
    c.course_code,
    c.semester,
    c.year,
    c.description,
    u.username as teacher_name,
    u.email as teacher_email,
    COUNT(ce.id) as enrolled_students,
    c.max_students,
    c.is_active,
    c.start_date,
    c.end_date,
    c.created_at
FROM courses c
JOIN users u ON c.teacher_id = u.id
LEFT JOIN course_enrollments ce ON c.id = ce.course_id AND ce.status = 'active'
GROUP BY c.id, c.title, c.course_code, c.semester, c.year, c.description, 
         u.username, u.email, c.max_students, c.is_active, c.start_date, c.end_date, c.created_at;

-- View for student enrollments
CREATE OR REPLACE VIEW student_enrollments_view AS
SELECT 
    ce.id as enrollment_id,
    ce.enrollment_date,
    ce.status as enrollment_status,
    c.id as course_id,
    c.title as course_title,
    c.course_code,
    c.description as course_description,
    c.semester,
    c.year,
    u.username as teacher_name,
    ce.grade,
    ce.final_grade,
    ce.notes
FROM course_enrollments ce
JOIN courses c ON ce.course_id = c.id
JOIN users u ON c.teacher_id = u.id
ORDER BY ce.enrollment_date DESC;

-- Create indexes for better performance
CREATE INDEX IF NOT EXISTS idx_courses_teacher_active ON courses(teacher_id, is_active);
CREATE INDEX IF NOT EXISTS idx_enrollments_student_status ON course_enrollments(student_id, status);
CREATE INDEX IF NOT EXISTS idx_materials_course_type ON course_materials(course_id, material_type);
CREATE INDEX IF NOT EXISTS idx_announcements_course_published ON course_announcements(course_id, is_published);
CREATE INDEX IF NOT EXISTS idx_discussions_course_active ON course_discussions(course_id, is_locked);

-- Insert sample course data
INSERT INTO courses (title, description, course_code, teacher_id, semester, year, credits, max_students, start_date, end_date) VALUES
('Introduction to Programming', 'Learn the fundamentals of programming using Python', 'CS101', (SELECT id FROM users WHERE username = 'teacher1'), 'Fall', 2024, 3, 30, '2024-09-01', '2024-12-15'),
('Web Development', 'Build modern web applications with HTML, CSS, and JavaScript', 'CS201', (SELECT id FROM users WHERE username = 'teacher1'), 'Fall', 2024, 4, 25, '2024-09-01', '2024-12-15'),
('Database Systems', 'Understanding relational databases and SQL', 'CS301', (SELECT id FROM users WHERE username = 'teacher2'), 'Fall', 2024, 3, 35, '2024-09-01', '2024-12-15'),
('Data Structures and Algorithms', 'Advanced algorithmic thinking and problem solving', 'CS202', (SELECT id FROM users WHERE username = 'teacher2'), 'Fall', 2024, 4, 20, '2024-09-01', '2024-12-15')
ON DUPLICATE KEY UPDATE 
title = VALUES(title),
description = VALUES(description),
semester = VALUES(semester),
year = VALUES(year);

-- Enroll some students in courses
INSERT INTO course_enrollments (course_id, student_id, status) VALUES
((SELECT id FROM courses WHERE course_code = 'CS101'), (SELECT id FROM users WHERE username = 'student1'), 'active'),
((SELECT id FROM courses WHERE course_code = 'CS101'), (SELECT id FROM users WHERE username = 'student2'), 'active'),
((SELECT id FROM courses WHERE course_code = 'CS201'), (SELECT id FROM users WHERE username = 'student1'), 'active'),
((SELECT id FROM courses WHERE course_code = 'CS301'), (SELECT id FROM users WHERE username = 'student2'), 'active')
ON DUPLICATE KEY UPDATE status = VALUES(status);

-- Create sample announcements
INSERT INTO course_announcements (course_id, title, content, posted_by, priority, is_published, publish_date) VALUES
((SELECT id FROM courses WHERE course_code = 'CS101'), 'Welcome to Introduction to Programming', 'Welcome everyone to the course! Please review the syllabus and complete the pre-course survey.', (SELECT id FROM users WHERE username = 'teacher1'), 'high', TRUE, NOW()),
((SELECT id FROM courses WHERE course_code = 'CS101'), 'Assignment 1 Posted', 'Your first programming assignment is now available. Due date is next Friday.', (SELECT id FROM users WHERE username = 'teacher1'), 'normal', TRUE, NOW()),
((SELECT id FROM courses WHERE course_code = 'CS201'), 'Web Development Course Start', 'Excited to start this journey in web development with all of you!', (SELECT id FROM users WHERE username = 'teacher1'), 'normal', TRUE, NOW())
ON DUPLICATE KEY UPDATE content = VALUES(content);

-- Create sample discussions
INSERT INTO course_discussions (course_id, title, description, created_by) VALUES
((SELECT id FROM courses WHERE course_code = 'CS101'), 'General Discussion', 'General questions and discussions about the course', (SELECT id FROM users WHERE username = 'teacher1')),
((SELECT id FROM courses WHERE course_code = 'CS101'), 'Assignment Help', 'Ask questions about programming assignments', (SELECT id FROM users WHERE username = 'teacher1')),
((SELECT id FROM courses WHERE course_code = 'CS201'), 'General Web Dev Discussion', 'Discussion about web development concepts', (SELECT id FROM users WHERE username = 'teacher1'))
ON DUPLICATE KEY UPDATE description = VALUES(description);

-- Create sample discussion posts
INSERT INTO discussion_posts (discussion_id, user_id, content) VALUES
((SELECT id FROM course_discussions WHERE title = 'General Discussion'), (SELECT id FROM users WHERE username = 'teacher1'), 'Welcome to the discussion forum! Feel free to ask any questions.'),
((SELECT id FROM course_discussions WHERE title = 'General Discussion'), (SELECT id FROM users WHERE username = 'student1'), 'Thank you! Looking forward to learning programming.'),
((SELECT id FROM course_discussions WHERE title = 'General Discussion'), (SELECT id FROM users WHERE username = 'student2'), 'Excited to start this course!')
ON DUPLICATE KEY UPDATE content = VALUES(content);

-- Create sample assignments
INSERT INTO course_assignments (course_id, title, description, instructions, max_points, due_date, assignment_type, is_published, created_by) VALUES
((SELECT id FROM courses WHERE course_code = 'CS101'), 'Hello World Program', 'Write your first Python program', 'Create a Python program that prints "Hello, World!" and save it as hello_world.py', 10, '2024-11-15 23:59:59', 'homework', TRUE, (SELECT id FROM users WHERE username = 'teacher1')),
((SELECT id FROM courses WHERE course_code = 'CS101'), 'Variables and Data Types', 'Practice with Python variables', 'Create variables of different data types and perform basic operations', 15, '2024-11-22 23:59:59', 'homework', TRUE, (SELECT id FROM users WHERE username = 'teacher1')),
((SELECT id FROM courses WHERE course_code = 'CS201'), 'HTML Page Creation', 'Create a simple HTML webpage', 'Build a basic HTML page with proper structure, headings, and paragraphs', 20, '2024-11-20 23:59:59', 'project', TRUE, (SELECT id FROM users WHERE username = 'teacher1'))
ON DUPLICATE KEY UPDATE title = VALUES(title);

-- Sample queries for the course management system:

-- Get all courses for a teacher
-- SELECT * FROM teacher_courses_view WHERE teacher_name = 'teacher1';

-- Get all courses for a student
-- SELECT * FROM student_enrollments_view WHERE student_id = 5;

-- Get course statistics
-- SELECT c.title, COUNT(ce.id) as enrollments, c.max_students 
-- FROM courses c 
-- LEFT JOIN course_enrollments ce ON c.id = ce.course_id AND ce.status = 'active' 
-- GROUP BY c.id, c.title, c.max_students;

-- Get recent announcements
-- SELECT ca.*, c.title as course_title, u.username as posted_by_name
-- FROM course_announcements ca
-- JOIN courses c ON ca.course_id = c.id
-- JOIN users u ON ca.posted_by = u.id
-- WHERE ca.is_published = TRUE AND (ca.publish_date IS NULL OR ca.publish_date <= NOW())
-- ORDER BY ca.created_at DESC
-- LIMIT 10;

-- Get discussion activity
-- SELECT cd.title, cd.course_id, c.title as course_title, COUNT(dp.id) as post_count
-- FROM course_discussions cd
-- JOIN courses c ON cd.course_id = c.id
-- LEFT JOIN discussion_posts dp ON cd.id = dp.discussion_id
-- GROUP BY cd.id, cd.title, cd.course_id, c.title
-- ORDER BY cd.updated_at DESC;