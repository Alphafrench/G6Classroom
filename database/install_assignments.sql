-- Assignment System Installation Script
-- This script creates all the necessary tables for the assignment management system

-- ==============================================
-- ASSIGNMENT MANAGEMENT TABLES
-- ==============================================

-- Assignments table
CREATE TABLE IF NOT EXISTS assignments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    instructions TEXT,
    teacher_id INT NOT NULL,
    course_id INT NULL,
    assignment_type ENUM('homework', 'project', 'exam', 'quiz', 'essay', 'presentation') DEFAULT 'homework',
    total_points DECIMAL(5,2) NOT NULL DEFAULT 100.00,
    due_date DATETIME NOT NULL,
    allow_late_submission BOOLEAN DEFAULT FALSE,
    late_penalty_per_day DECIMAL(4,2) DEFAULT 5.00,
    max_attempts INT DEFAULT 1,
    requires_file_upload BOOLEAN DEFAULT FALSE,
    allowed_file_types TEXT, -- JSON array of allowed file extensions
    max_file_size INT DEFAULT 5242880, -- 5MB in bytes
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Add indexes for better performance
    INDEX idx_teacher_id (teacher_id),
    INDEX idx_course_id (course_id),
    INDEX idx_due_date (due_date),
    INDEX idx_is_active (is_active),
    INDEX idx_assignment_type (assignment_type),
    INDEX idx_created_at (created_at),
    
    FOREIGN KEY (teacher_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Assignment submissions table
CREATE TABLE IF NOT EXISTS assignment_submissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    assignment_id INT NOT NULL,
    student_id INT NOT NULL,
    submission_text TEXT,
    submission_file_path VARCHAR(500),
    original_filename VARCHAR(255),
    file_size INT,
    file_type VARCHAR(50),
    attempt_number INT DEFAULT 1,
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    is_late BOOLEAN DEFAULT FALSE,
    late_days INT DEFAULT 0,
    status ENUM('submitted', 'under_review', 'graded', 'returned') DEFAULT 'submitted',
    ip_address VARCHAR(45),
    user_agent TEXT,
    
    -- Add indexes for better performance
    INDEX idx_assignment_id (assignment_id),
    INDEX idx_student_id (student_id),
    INDEX idx_status (status),
    INDEX idx_submitted_at (submitted_at),
    INDEX idx_attempt_number (attempt_number),
    
    FOREIGN KEY (assignment_id) REFERENCES assignments(id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
    
    -- Ensure one submission per attempt per student per assignment
    UNIQUE KEY unique_attempt (assignment_id, student_id, attempt_number)
);

-- Assignment grades table
CREATE TABLE IF NOT EXISTS assignment_grades (
    id INT AUTO_INCREMENT PRIMARY KEY,
    submission_id INT NOT NULL,
    assignment_id INT NOT NULL,
    student_id INT NOT NULL,
    teacher_id INT NOT NULL,
    score DECIMAL(5,2) NOT NULL,
    max_score DECIMAL(5,2) NOT NULL,
    percentage DECIMAL(5,2) GENERATED ALWAYS AS (score * 100 / max_score) STORED,
    letter_grade VARCHAR(2),
    feedback TEXT,
    graded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    graded_by INT NOT NULL,
    
    -- Add indexes for better performance
    INDEX idx_submission_id (submission_id),
    INDEX idx_assignment_id (assignment_id),
    INDEX idx_student_id (student_id),
    INDEX idx_teacher_id (teacher_id),
    INDEX idx_graded_at (graded_at),
    INDEX idx_score (score),
    
    FOREIGN KEY (submission_id) REFERENCES assignment_submissions(id) ON DELETE CASCADE,
    FOREIGN KEY (assignment_id) REFERENCES assignments(id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (teacher_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (graded_by) REFERENCES users(id) ON DELETE CASCADE
);

-- Assignment comments/feedback table (for teacher comments)
CREATE TABLE IF NOT EXISTS assignment_comments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    assignment_id INT NOT NULL,
    student_id INT NOT NULL,
    teacher_id INT NOT NULL,
    comment_type ENUM('general', 'rubric', 'annotation') DEFAULT 'general',
    comment_text TEXT NOT NULL,
    line_number INT NULL, -- For line-specific comments
    is_private BOOLEAN DEFAULT FALSE, -- False means visible to student
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    -- Add indexes
    INDEX idx_assignment_id (assignment_id),
    INDEX idx_student_id (student_id),
    INDEX idx_teacher_id (teacher_id),
    INDEX idx_comment_type (comment_type),
    INDEX idx_is_private (is_private),
    
    FOREIGN KEY (assignment_id) REFERENCES assignments(id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (teacher_id) REFERENCES users(id) ON DELETE CASCADE
);

-- ==============================================
-- VIEWS FOR REPORTING AND ANALYTICS
-- ==============================================

-- View for assignment overview with submission statistics
CREATE OR REPLACE VIEW assignment_overview AS
SELECT 
    a.id,
    a.title,
    a.description,
    a.assignment_type,
    a.total_points,
    a.due_date,
    a.allow_late_submission,
    a.max_attempts,
    a.requires_file_upload,
    a.is_active,
    a.created_at,
    u.username as teacher_name,
    u.email as teacher_email,
    
    -- Submission statistics
    COUNT(DISTINCT s.id) as total_submissions,
    COUNT(DISTINCT CASE WHEN s.status = 'graded' THEN s.id END) as graded_submissions,
    COUNT(DISTINCT CASE WHEN s.is_late = 1 THEN s.id END) as late_submissions,
    COUNT(DISTINCT CASE WHEN s.submitted_at IS NOT NULL THEN s.id END) as submitted_count,
    
    -- Average scores
    AVG(CASE WHEN g.score IS NOT NULL THEN g.percentage END) as avg_score_percentage,
    MAX(CASE WHEN g.score IS NOT NULL THEN g.percentage END) as highest_score,
    MIN(CASE WHEN g.score IS NOT NULL THEN g.percentage END) as lowest_score,
    
    -- Grade distribution
    COUNT(CASE WHEN g.letter_grade = 'A' THEN 1 END) as count_A,
    COUNT(CASE WHEN g.letter_grade = 'B' THEN 1 END) as count_B,
    COUNT(CASE WHEN g.letter_grade = 'C' THEN 1 END) as count_C,
    COUNT(CASE WHEN g.letter_grade = 'D' THEN 1 END) as count_D,
    COUNT(CASE WHEN g.letter_grade = 'F' THEN 1 END) as count_F
    
FROM assignments a
LEFT JOIN users u ON a.teacher_id = u.id
LEFT JOIN assignment_submissions s ON a.id = s.assignment_id
LEFT JOIN assignment_grades g ON s.id = g.submission_id
GROUP BY a.id, a.title, a.description, a.assignment_type, a.total_points, 
         a.due_date, a.allow_late_submission, a.max_attempts, 
         a.requires_file_upload, a.is_active, a.created_at, 
         u.username, u.email;

-- View for student assignment status
CREATE OR REPLACE VIEW student_assignment_status AS
SELECT 
    s.id as submission_id,
    a.id as assignment_id,
    a.title,
    a.description,
    a.total_points,
    a.due_date,
    a.max_attempts,
    a.allow_late_submission,
    s.student_id,
    u.username as student_name,
    u.email as student_email,
    s.attempt_number,
    s.submission_text,
    s.submission_file_path,
    s.original_filename,
    s.submitted_at,
    s.is_late,
    s.status as submission_status,
    g.score,
    g.max_score,
    g.percentage,
    g.letter_grade,
    g.feedback as teacher_feedback,
    g.graded_at,
    teacher.username as teacher_name,
    
    -- Calculate days late if applicable
    CASE 
        WHEN s.is_late = 1 AND a.due_date < s.submitted_at 
        THEN DATEDIFF(s.submitted_at, a.due_date)
        ELSE 0 
    END as days_late,
    
    -- Calculate penalty percentage
    CASE 
        WHEN s.is_late = 1 AND a.late_penalty_per_day > 0
        THEN (DATEDIFF(s.submitted_at, a.due_date) * a.late_penalty_per_day)
        ELSE 0 
    END as late_penalty_percentage

FROM assignment_submissions s
JOIN assignments a ON s.assignment_id = a.id
JOIN users u ON s.student_id = u.id
LEFT JOIN assignment_grades g ON s.id = g.submission_id
LEFT JOIN users teacher ON g.graded_by = teacher.id;

-- ==============================================
-- STORED PROCEDURES
-- ==============================================

-- Procedure to get assignment statistics
DELIMITER //

CREATE PROCEDURE IF NOT EXISTS GetAssignmentStats(IN assignment_id INT)
BEGIN
    SELECT 
        -- Assignment details
        a.title,
        a.total_points,
        a.due_date,
        
        -- Submission counts
        (SELECT COUNT(*) FROM assignment_submissions WHERE assignment_id = assignment_id) as total_submissions,
        (SELECT COUNT(*) FROM assignment_submissions WHERE assignment_id = assignment_id AND status = 'graded') as graded_submissions,
        (SELECT COUNT(*) FROM assignment_submissions WHERE assignment_id = assignment_id AND is_late = 1) as late_submissions,
        
        -- Score statistics
        (SELECT AVG(percentage) FROM assignment_grades g 
         JOIN assignment_submissions s ON g.submission_id = s.id 
         WHERE s.assignment_id = assignment_id) as avg_score,
        (SELECT MAX(percentage) FROM assignment_grades g 
         JOIN assignment_submissions s ON g.submission_id = s.id 
         WHERE s.assignment_id = assignment_id) as highest_score,
        (SELECT MIN(percentage) FROM assignment_grades g 
         JOIN assignment_submissions s ON g.submission_id = s.id 
         WHERE s.assignment_id = assignment_id) as lowest_score,
        
        -- Grade distribution
        (SELECT COUNT(*) FROM assignment_grades g 
         JOIN assignment_submissions s ON g.submission_id = s.id 
         WHERE s.assignment_id = assignment_id AND g.letter_grade = 'A') as count_A,
        (SELECT COUNT(*) FROM assignment_grades g 
         JOIN assignment_submissions s ON g.submission_id = s.id 
         WHERE s.assignment_id = assignment_id AND g.letter_grade = 'B') as count_B,
        (SELECT COUNT(*) FROM assignment_grades g 
         JOIN assignment_submissions s ON g.submission_id = s.id 
         WHERE s.assignment_id = assignment_id AND g.letter_grade = 'C') as count_C,
        (SELECT COUNT(*) FROM assignment_grades g 
         JOIN assignment_submissions s ON g.submission_id = s.id 
         WHERE s.assignment_id = assignment_id AND g.letter_grade = 'D') as count_D,
        (SELECT COUNT(*) FROM assignment_grades g 
         JOIN assignment_submissions s ON g.submission_id = s.id 
         WHERE s.assignment_id = assignment_id AND g.letter_grade = 'F') as count_F;
        
END //

-- Function to calculate letter grade from percentage
DELIMITER //

CREATE FUNCTION IF NOT EXISTS CalculateLetterGrade(percentage DECIMAL(5,2))
RETURNS VARCHAR(2)
DETERMINISTIC
BEGIN
    IF percentage >= 90 THEN
        RETURN 'A';
    ELSEIF percentage >= 80 THEN
        RETURN 'B';
    ELSEIF percentage >= 70 THEN
        RETURN 'C';
    ELSEIF percentage >= 60 THEN
        RETURN 'D';
    ELSE
        RETURN 'F';
    END IF;
END //

DELIMITER ;

-- ==============================================
-- TRIGGERS FOR AUTOMATIC UPDATES
-- ==============================================

-- Trigger to automatically calculate letter grade when score is inserted
DELIMITER //

CREATE TRIGGER IF NOT EXISTS before_grade_insert
BEFORE INSERT ON assignment_grades
FOR EACH ROW
BEGIN
    SET NEW.letter_grade = CalculateLetterGrade(NEW.percentage);
END //

-- Trigger to automatically update letter grade when score is updated
CREATE TRIGGER IF NOT EXISTS before_grade_update
BEFORE UPDATE ON assignment_grades
FOR EACH ROW
BEGIN
    SET NEW.letter_grade = CalculateLetterGrade(NEW.percentage);
END //

-- Trigger to mark submission as late automatically
CREATE TRIGGER IF NOT EXISTS before_submission_insert
BEFORE INSERT ON assignment_submissions
FOR EACH ROW
BEGIN
    DECLARE assignment_due_date DATETIME;
    
    SELECT due_date INTO assignment_due_date 
    FROM assignments WHERE id = NEW.assignment_id;
    
    IF NEW.submitted_at > assignment_due_date THEN
        SET NEW.is_late = 1;
        SET NEW.late_days = DATEDIFF(NEW.submitted_at, assignment_due_date);
    END IF;
END //

DELIMITER ;

-- ==============================================
-- ADDITIONAL INDEXES FOR PERFORMANCE
-- ==============================================

-- Composite indexes for common query patterns
CREATE INDEX IF NOT EXISTS idx_assignments_teacher_active ON assignments(teacher_id, is_active);
CREATE INDEX IF NOT EXISTS idx_submissions_assignment_status ON assignment_submissions(assignment_id, status);
CREATE INDEX IF NOT EXISTS idx_grades_assignment_score ON assignment_grades(assignment_id, percentage);
CREATE INDEX IF NOT EXISTS idx_submissions_student_assignment ON assignment_submissions(student_id, assignment_id);

-- ==============================================
-- SAMPLE DATA FOR TESTING
-- ==============================================

-- Insert sample assignments (if admin user exists with ID 1)
INSERT INTO assignments (title, description, instructions, teacher_id, assignment_type, total_points, due_date, allow_late_submission, max_attempts, requires_file_upload, allowed_file_types) VALUES
('HTML/CSS Fundamentals Project', 'Create a responsive website using HTML5 and CSS3', 'Build a complete responsive website that includes: 1) Homepage with navigation 2) About page 3) Contact form 4) Responsive design 5) Valid HTML5 and CSS3. Make sure to use semantic HTML elements and modern CSS techniques.', 1, 'project', 100.00, '2024-12-15 23:59:59', TRUE, 1, TRUE, '["html", "css", "js", "zip"]'),
('JavaScript Quiz 1', 'Basic JavaScript concepts and syntax', 'Complete the quiz covering variables, functions, arrays, and objects. You have 30 minutes to complete. This quiz will test your understanding of fundamental JavaScript concepts.', 1, 'quiz', 50.00, '2024-12-10 16:00:00', FALSE, 1, FALSE, NULL),
('Database Design Essay', 'Design a database for an e-commerce system', 'Write a 1000-word essay describing your database design choices, entity relationships, and normalization considerations. Include diagrams and explain your reasoning.', 1, 'essay', 75.00, '2024-12-20 23:59:59', TRUE, 1, TRUE, '["doc", "docx", "pdf", "txt"]'),
('React Component Challenge', 'Build interactive React components', 'Create a set of reusable React components including forms, modals, and data tables. Focus on proper state management and component composition.', 1, 'project', 120.00, '2024-12-25 23:59:59', TRUE, 2, TRUE, '["js", "jsx", "zip"]')
ON DUPLICATE KEY UPDATE
title = VALUES(title),
description = VALUES(description),
instructions = VALUES(instructions),
due_date = VALUES(due_date);

-- Create uploads directory structure
-- Note: This needs to be done manually via file system
-- mkdir -p uploads/assignments/assignment_1
-- mkdir -p uploads/assignments/assignment_2
-- etc.

-- ==============================================
-- COMPLETION MESSAGE
-- ==============================================

SELECT 'Assignment system tables created successfully!' as status;