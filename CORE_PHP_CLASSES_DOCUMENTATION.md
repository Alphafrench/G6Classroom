# Core PHP Classes and Functions Documentation

This documentation provides comprehensive details about the core PHP classes and functions developed for the Attendance Management System with teacher/student roles.

## Table of Contents
1. [Database Class](#database-class)
2. [User Class](#user-class)
3. [Course Class](#course-class)
4. [Attendance Class](#attendance-class)
5. [Utility Functions](#utility-functions)
6. [Authentication System](#authentication-system)
7. [Usage Examples](#usage-examples)
8. [Security Features](#security-features)

---

## Database Class

### Overview
The `Database` class provides a secure and efficient database abstraction layer using PDO. It implements the singleton pattern to ensure a single database connection throughout the application.

### Key Features
- **Singleton Pattern**: Ensures single database instance
- **Prepared Statements**: Prevents SQL injection attacks
- **Transaction Support**: ACID compliance for data integrity
- **Error Handling**: Comprehensive error logging and reporting
- **Connection Testing**: Built-in connection validation

### Methods

#### Constructor
```php
$db = new Database($config = []);
```
- **Parameters**: Optional configuration array
- **Config Options**: host, port, database, username, password, charset, options

#### Core Methods
- `getConnection()` - Returns PDO connection instance
- `query($sql, $params = [])` - Execute prepared statement
- `fetchRow($sql, $params = [])` - Fetch single row
- `fetchAll($sql, $params = [])` - Fetch multiple rows
- `insert($table, $data)` - Insert data and return last insert ID
- `update($table, $data, $where, $params = [])` - Update records
- `delete($table, $where, $params = [])` - Delete records

#### Transaction Methods
- `beginTransaction()` - Start database transaction
- `commit()` - Commit transaction
- `rollback()` - Rollback transaction
- `transaction($callback)` - Execute function within transaction

#### Utility Methods
- `count($table, $where = '1', $params = [])` - Count table rows
- `exists($table, $where, $params = [])` - Check if record exists
- `testConnection()` - Test database connectivity
- `getVersion()` - Get database version
- `getStats()` - Get database statistics

### Usage Example
```php
require_once 'includes/class.Database.php';

// Initialize database
$db = getDatabase();

// Fetch single record
$user = $db->fetchRow("SELECT * FROM users WHERE id = ?", [1]);

// Insert new record
$userId = $db->insert('users', [
    'username' => 'john_doe',
    'email' => 'john@example.com',
    'created_at' => date('Y-m-d H:i:s')
]);

// Update record
$affected = $db->update('users', 
    ['email' => 'john.doe@example.com'], 
    'id = ?', 
    [$userId]
);
```

---

## User Class

### Overview
The `User` class manages all user-related operations including authentication, profile management, and role-based access control. It supports teacher and student roles with comprehensive security measures.

### Key Features
- **Role-Based Access**: Supports admin, teacher, and student roles
- **Password Security**: Uses PHP's password_hash() with proper validation
- **Account Status Management**: Active, inactive, suspended states
- **Activity Logging**: Comprehensive audit trail
- **Validation**: Extensive input validation and sanitization

### Constructor
```php
$user = new User($db = null);
```

### Core Methods

#### User Creation and Management
- `create($userData, $createdBy = null)` - Create new user
- `getById($userId)` - Get user by ID
- `getByUsername($username)` - Get user by username/email
- `getAll($filters = [], $limit = 20, $offset = 0, $sortBy = 'last_name', $sortOrder = 'ASC')` - Get all users with pagination
- `update($userId, $userData, $updatedBy = null)` - Update user information

#### Authentication Methods
- `changePassword($userId, $newPassword, $currentPassword = null, $changedBy = null)` - Change password
- `changeStatus($userId, $newStatus, $changedBy = null)` - Change user status
- `delete($userId, $deletedBy = null)` - Delete user (soft delete)

#### Query Methods
- `getByRole($role, $activeOnly = true)` - Get users by role
- `getStatistics()` - Get user statistics

### Validation Rules
```php
private $validationRules = [
    'username' => ['required' => true, 'min_length' => 3, 'max_length' => 50],
    'email' => ['required' => true, 'email' => true, 'max_length' => 100],
    'password' => ['required' => false, 'min_length' => 8],
    'first_name' => ['required' => true, 'max_length' => 50],
    'last_name' => ['required' => true, 'max_length' => 50],
    'role' => ['required' => true, 'in_array' => ['admin', 'teacher', 'student']],
    'phone' => ['required' => false, 'max_length' => 20],
    'date_of_birth' => ['required' => false, 'date' => true]
];
```

### Usage Example
```php
require_once 'includes/class.User.php';

$user = new User($db);

// Create new teacher
$result = $user->create([
    'username' => 'teacher_john',
    'email' => 'john.teacher@school.edu',
    'password' => 'SecurePassword123!',
    'first_name' => 'John',
    'last_name' => 'Smith',
    'role' => 'teacher',
    'phone' => '555-0123'
]);

if ($result['success']) {
    echo "Teacher created with ID: " . $result['user_id'];
} else {
    echo "Error: " . implode(', ', $result['errors']);
}

// Get all teachers
$teachers = $user->getByRole('teacher');

// Change user status
$user->changeStatus($userId, 'suspended', $adminId);
```

---

## Course Class

### Overview
The `Course` class handles all course-related operations including creation, enrollment management, and course administration. It provides comprehensive course management functionality for educational institutions.

### Key Features
- **Course Management**: Full CRUD operations for courses
- **Enrollment System**: Student enrollment and unenrollment
- **Capacity Management**: Automatic capacity checking
- **Status Tracking**: Active, inactive, completed, cancelled states
- **Statistics**: Comprehensive course analytics

### Constructor
```php
$course = new Course($db = null);
```

### Core Methods

#### Course Management
- `create($courseData, $createdBy = null)` - Create new course
- `getById($courseId)` - Get course by ID
- `getByCode($courseCode)` - Get course by code
- `getAll($filters = [], $limit = 20, $offset = 0, $sortBy = 'created_at', $sortOrder = 'DESC')` - Get all courses
- `update($courseId, $courseData, $updatedBy = null)` - Update course
- `delete($courseId, $deletedBy = null)` - Delete course

#### Enrollment Management
- `enrollStudent($courseId, $studentId, $enrolledBy = null)` - Enroll student
- `unenrollStudent($courseId, $studentId, $unenrolledBy = null)` - Unenroll student
- `getEnrolledStudents($courseId, $activeOnly = true)` - Get enrolled students
- `getStudentCourses($studentId, $status = 'active')` - Get student's courses
- `getTeacherCourses($teacherId, $status = 'active')` - Get teacher's courses

#### Analytics
- `getStatistics()` - Get course statistics

### Validation Rules
```php
private $validationRules = [
    'name' => ['required' => true, 'max_length' => 255],
    'description' => ['required' => true, 'max_length' => 1000],
    'code' => ['required' => true, 'max_length' => 50],
    'credits' => ['required' => false, 'numeric' => true, 'min_value' => 0, 'max_value' => 50],
    'semester' => ['required' => true, 'max_length' => 50],
    'academic_year' => ['required' => true, 'max_length' => 50],
    'start_date' => ['required' => true, 'date' => true],
    'end_date' => ['required' => true, 'date' => true],
    'capacity' => ['required' => false, 'numeric' => true, 'min_value' => 1, 'max_value' => 500],
    'status' => ['required' => true, 'in_array' => ['active', 'inactive', 'completed', 'cancelled']]
];
```

### Usage Example
```php
require_once 'includes/class.Course.php';

$course = new Course($db);

// Create new course
$result = $course->create([
    'name' => 'Introduction to Computer Science',
    'description' => 'Fundamental concepts of programming and algorithms',
    'code' => 'CS101',
    'credits' => 3,
    'semester' => 'Fall 2024',
    'academic_year' => '2024-2025',
    'start_date' => '2024-09-01',
    'end_date' => '2024-12-15',
    'capacity' => 30,
    'teacher_id' => $teacherId
], $adminId);

if ($result['success']) {
    $courseId = $result['course_id'];
    
    // Enroll students
    $course->enrollStudent($courseId, $studentId1, $teacherId);
    $course->enrollStudent($courseId, $studentId2, $teacherId);
    
    // Get enrolled students
    $students = $course->getEnrolledStudents($courseId);
    echo "Enrolled students: " . count($students);
}
```

---

## Attendance Class

### Overview
The `Attendance` class manages attendance tracking for both students and teachers. It provides session-based attendance marking for students and time tracking for teachers.

### Key Features
- **Student Attendance**: Session-based attendance marking
- **Teacher Time Tracking**: Clock in/out functionality
- **Bulk Operations**: Bulk attendance marking for efficiency
- **Analytics**: Comprehensive attendance reporting
- **Validation**: Input validation and business logic checks

### Constructor
```php
$attendance = new Attendance($db = null);
```

### Core Methods

#### Student Attendance
- `markStudentAttendance($courseId, $studentId, $status, $sessionDate, $markedBy = null, $notes = '')` - Mark single student attendance
- `bulkMarkAttendance($courseId, $attendanceData, $markedBy, $sessionDate)` - Bulk mark attendance
- `getStudentAttendance($courseId, $studentId, $startDate = null, $endDate = null)` - Get student attendance records
- `getCourseAttendanceSummary($courseId, $startDate, $endDate)` - Get course attendance summary

#### Teacher Time Tracking (Legacy/Compatibility)
- `clockIn($employeeId, $location = '', $notes = '')` - Clock in employee
- `clockOut($employeeId, $location = '', $notes = '')` - Clock out employee
- `getCurrentStatus($employeeId)` - Get current attendance status

#### Query Methods
- `getEmployeeAttendance($employeeId, $startDate = null, $endDate = null, $limit = 30, $offset = 0)` - Get employee attendance
- `generateReport($filters = [], $format = 'summary')` - Generate attendance reports

### Attendance Status Types
- `present` - Student attended the session
- `absent` - Student was absent
- `late` - Student arrived late
- `excused` - Absence was excused

### Usage Example
```php
require_once 'includes/class.Attendance.php';

$attendance = new Attendance($db);

// Mark single student attendance
$result = $attendance->markStudentAttendance(
    $courseId = 1,
    $studentId = 123,
    $status = 'present',
    $sessionDate = '2024-11-05',
    $markedBy = 456, // Teacher ID
    $notes = 'On time and prepared'
);

if ($result['success']) {
    echo "Attendance marked successfully";
}

// Bulk mark attendance for multiple students
$attendanceData = [
    123 => ['status' => 'present', 'notes' => ''],
    124 => ['status' => 'absent', 'notes' => 'Sick leave'],
    125 => ['status' => 'late', 'notes' => 'Traffic delay']
];

$result = $attendance->bulkMarkAttendance($courseId, $attendanceData, 456, '2024-11-05');
echo "Processed: {$result['processed']} students";

// Get attendance summary for course
$summary = $attendance->getCourseAttendanceSummary($courseId, '2024-09-01', '2024-12-15');
echo "Average attendance: {$summary['average_attendance']}%";
```

---

## Utility Functions

### Security Functions

#### Input Sanitization
```php
sanitizeInput($data) // Sanitize single value or array
sanitizeHTML($input, $allowedTags) // Sanitize HTML with allowed tags
```

#### Validation Functions
```php
validateEmail($email) // Validate email format
validatePhone($phone, $country) // Validate phone number
validatePassword($password, $options) // Validate password strength
validateDate($date, $format) // Validate date format
validateRequiredFields($data, $requiredFields) // Validate required fields
```

### Formatting Functions
```php
formatDate($date, $format) // Format date for display
formatDateTime($datetime, $format) // Format datetime for display
formatPhoneNumber($phone, $format) // Format phone number
formatCurrency($amount, $currency, $decimals) // Format currency
calculateAge($birthDate) // Calculate age from birth date
```

### Security and Utilities
```php
hashPassword($password) // Hash password securely
verifyPassword($password, $hash) // Verify password
generatePassword($length, $includeSpecial) // Generate random password
generateToken($length) // Generate secure token
generateCSRFToken() // Generate CSRF token
verifyCSRFToken($token) // Verify CSRF token
```

### System Functions
```php
logError($message, $context, $level) // Log errors with context
getClientIP() // Get client IP address safely
arrayToCSV($data, $delimiter) // Convert array to CSV
safeRedirect($url, $params) // Safe redirect with parameters
jsonResponse($data, $statusCode) // JSON response helper
```

---

## Authentication System

### Session Management
The authentication system uses secure PHP sessions with the following features:

- **Secure Session Settings**: HTTP-only, secure cookies, strict mode
- **Session Timeout**: Automatic logout after 30 minutes of inactivity
- **CSRF Protection**: CSRF tokens for all forms
- **Remember Me**: Optional persistent login

### Core Functions

#### Session Functions
```php
initialize_session() // Initialize secure session
is_logged_in() // Check if user is logged in
get_current_user() // Get current user data
require_auth($redirect_url) // Require authentication
require_role($required_role, $redirect_url) // Require specific role
```

#### Authentication Functions
```php
login($username, $password, $remember_me) // Authenticate user
logout() // Logout user
has_role($required_role) // Check user role
```

#### Activity Tracking
```php
log_activity($user_id, $action, $description) // Log user activity
check_suspicious_activity() // Check for suspicious activities
```

### Usage Example
```php
require_once 'includes/auth.php';

// Initialize session
initialize_session();

// Require authentication for protected pages
require_auth('/pages/login.php');

// Require teacher role
require_role('teacher', '/access-denied.php');

// Check current user
$currentUser = get_current_user();
if ($currentUser && has_role('teacher')) {
    echo "Welcome, Teacher " . $currentUser['username'];
}
```

---

## Usage Examples

### Complete Workflow Example

#### 1. Initialize System
```php
<?php
require_once 'includes/class.Database.php';
require_once 'includes/class.User.php';
require_once 'includes/class.Course.php';
require_once 'includes/class.Attendance.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

// Initialize secure session
initialize_session();

// Get database instance
$db = getDatabase();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Attendance Management System</title>
</head>
<body>
```

#### 2. User Management Example
```php
<?php
// Create admin user
$user = new User($db);

$adminResult = $user->create([
    'username' => 'admin',
    'email' => 'admin@school.edu',
    'password' => 'AdminPassword123!',
    'first_name' => 'System',
    'last_name' => 'Administrator',
    'role' => 'admin'
], 1); // Created by system

// Create teacher
$teacherResult = $user->create([
    'username' => 'teacher_john',
    'email' => 'john.teacher@school.edu',
    'password' => 'TeacherPass123!',
    'first_name' => 'John',
    'last_name' => 'Smith',
    'role' => 'teacher',
    'phone' => '555-0123'
], $adminResult['user_id']);

// Create student
$studentResult = $user->create([
    'username' => 'student_jane',
    'email' => 'jane.student@school.edu',
    'password' => 'StudentPass123!',
    'first_name' => 'Jane',
    'last_name' => 'Doe',
    'role' => 'student'
], $adminResult['user_id']);
?>
```

#### 3. Course Management Example
```php
<?php
$course = new Course($db);

// Create course
$courseResult = $course->create([
    'name' => 'Mathematics 101',
    'description' => 'Basic mathematics concepts',
    'code' => 'MATH101',
    'credits' => 3,
    'semester' => 'Fall 2024',
    'academic_year' => '2024-2025',
    'start_date' => '2024-09-01',
    'end_date' => '2024-12-15',
    'capacity' => 25,
    'teacher_id' => $teacherResult['user_id']
], $adminResult['user_id']);

$courseId = $courseResult['course_id'];

// Enroll student
$course->enrollStudent($courseId, $studentResult['user_id'], $teacherResult['user_id']);

// Get course information
$courseInfo = $course->getById($courseId);
echo "Course: " . $courseInfo['name'] . " - " . $courseInfo['current_enrolled'] . " students enrolled";
?>
```

#### 4. Attendance Management Example
```php
<?php
$attendance = new Attendance($db);

// Mark attendance for a session
$attendanceResult = $attendance->markStudentAttendance(
    $courseId,
    $studentResult['user_id'],
    'present',
    '2024-11-05', // Session date
    $teacherResult['user_id'],
    'Excellent participation'
);

// Get attendance summary
$summary = $attendance->getCourseAttendanceSummary($courseId, '2024-09-01', '2024-12-15');

echo "Course Attendance Summary:";
echo "Total Sessions: " . $summary['total_sessions'];
echo "Average Attendance: " . $summary['average_attendance'] . "%";
echo "Present Sessions: " . $summary['attendance_by_status']['present'];
echo "Absent Sessions: " . $summary['attendance_by_status']['absent'];
?>
```

#### 5. Dashboard Example
```php
<?php
// Check if user is logged in
if (is_logged_in()) {
    $currentUser = get_current_user();
    
    echo "<h1>Dashboard</h1>";
    echo "<p>Welcome, " . $currentUser['first_name'] . " " . $currentUser['last_name'] . "</p>";
    echo "<p>Role: " . ucfirst($currentUser['role']) . "</p>";
    
    if (has_role('teacher')) {
        // Teacher dashboard
        $course = new Course($db);
        $teacherCourses = $course->getTeacherCourses($currentUser['id']);
        
        echo "<h2>Your Courses</h2>";
        foreach ($teacherCourses as $course) {
            echo "<div>";
            echo "<h3>" . $course['name'] . " (" . $course['code'] . ")</h3>";
            echo "<p>Students: " . $course['current_enrolled'] . "/" . $course['capacity'] . "</p>";
            echo "<p>Status: " . ucfirst($course['status']) . "</p>";
            echo "</div>";
        }
        
    } elseif (has_role('student')) {
        // Student dashboard
        $course = new Course($db);
        $studentCourses = $course->getStudentCourses($currentUser['id']);
        
        echo "<h2>Your Courses</h2>";
        foreach ($studentCourses as $course) {
            echo "<div>";
            echo "<h3>" . $course['name'] . " (" . $course['code'] . ")</h3>";
            echo "<p>Teacher: " . $course['teacher_name'] . "</p>";
            echo "<p>Semester: " . $course['semester'] . "</p>";
            echo "</div>";
        }
    }
} else {
    // Redirect to login
    safeRedirect('/pages/login.php');
}
?>
```

---

## Security Features

### Database Security
- **Prepared Statements**: All SQL queries use prepared statements
- **Input Sanitization**: Comprehensive input sanitization
- **SQL Injection Prevention**: Parameter binding prevents SQL injection
- **Connection Security**: Secure database connections with proper configuration

### Authentication Security
- **Password Hashing**: Uses PHP's password_hash() with bcrypt
- **Session Security**: Secure session configuration with HTTP-only cookies
- **Session Timeout**: Automatic logout after inactivity
- **CSRF Protection**: CSRF tokens for all forms
- **Failed Login Protection**: Account lockout after multiple failed attempts

### Data Validation
- **Input Validation**: Comprehensive validation for all user inputs
- **Type Checking**: Proper type checking for all parameters
- **Length Validation**: Maximum length checks for all text fields
- **Format Validation**: Email, phone, date, and custom format validation

### Error Handling
- **Comprehensive Logging**: All errors are logged with context
- **Error Sanitization**: Error messages don't expose system details
- **Graceful Degradation**: System continues to function despite errors
- **Debug Information**: Detailed error information for development

### Access Control
- **Role-Based Access**: Different access levels for different roles
- **Method-Level Security**: Access control at method level
- **Data Isolation**: Users can only access their authorized data
- **Audit Trail**: Comprehensive activity logging

### Best Practices
1. **Never Trust User Input**: Always validate and sanitize
2. **Use Prepared Statements**: For all database operations
3. **Implement Least Privilege**: Users only get necessary permissions
4. **Log Everything**: Comprehensive activity and error logging
5. **Secure Sessions**: Proper session configuration
6. **Regular Updates**: Keep dependencies updated
7. **Test Thoroughly**: Comprehensive testing of all features

---

## Conclusion

This comprehensive PHP framework provides a solid foundation for building attendance management systems for educational institutions. The modular design, extensive security measures, and comprehensive validation make it suitable for production use in schools, colleges, and universities.

The framework supports:
- **Multiple User Roles**: Admin, Teacher, Student
- **Course Management**: Complete course lifecycle management
- **Attendance Tracking**: Both session-based and time-based attendance
- **Comprehensive Reporting**: Detailed analytics and reports
- **Security**: Enterprise-level security features
- **Scalability**: Designed to handle large user bases
- **Maintainability**: Clean, documented, and well-structured code

All classes follow consistent patterns and include comprehensive error handling, making the system robust and reliable for educational environments.
