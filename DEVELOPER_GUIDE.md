# Developer Guide - Google Classroom Clone

## Table of Contents
1. [Overview](#overview)
2. [Architecture](#architecture)
3. [Development Environment](#development-environment)
4. [Code Organization](#code-organization)
5. [Core Components](#core-components)
6. [Development Workflow](#development-workflow)
7. [Best Practices](#best-practices)
8. [Troubleshooting](#troubleshooting)

## Overview

The Google Classroom Clone is a comprehensive educational management system built with PHP 8+, providing classroom management, assignment tracking, attendance monitoring, and user management capabilities.

### System Requirements
- **PHP**: 8.0 or higher
- **MySQL**: 5.7+ or MariaDB 10.2+
- **Web Server**: Apache 2.4+ with mod_rewrite
- **Extensions**: PDO, PDO_MySQL, OpenSSL, cURL, GD/ImageMagick
- **Memory**: 512MB minimum, 1GB recommended
- **Storage**: 2GB minimum

## Architecture

### System Architecture Diagram
```
┌─────────────────────────────────────────────────────────────┐
│                    Client Layer                             │
│  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐         │
│  │   Web UI    │  │   Mobile    │  │    API      │         │
│  │   (PHP)     │  │   (Future)  │  │  (RESTful)  │         │
│  └─────────────┘  └─────────────┘  └─────────────┘         │
└─────────────────────────────────────────────────────────────┘
                            │
┌─────────────────────────────────────────────────────────────┐
│                   Application Layer                         │
│  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐         │
│  │ Controllers │  │    Models   │  │  Services   │         │
│  │    (PHP)    │  │    (PHP)    │  │    (PHP)    │         │
│  └─────────────┘  └─────────────┘  └─────────────┘         │
│  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐         │
│  │   Middleware│  │   Security  │  │ Validation  │         │
│  │             │  │             │  │             │         │
│  └─────────────┘  └─────────────┘  └─────────────┘         │
└─────────────────────────────────────────────────────────────┘
                            │
┌─────────────────────────────────────────────────────────────┐
│                      Data Layer                             │
│  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐         │
│  │  Database   │  │    File     │  │   Cache     │         │
│  │   (MySQL)   │  │   System    │  │  (Optional) │         │
│  └─────────────┘  └─────────────┘  └─────────────┘         │
└─────────────────────────────────────────────────────────────┘
```

### Design Patterns Used

#### 1. Model-View-Controller (MVC)
- **Models**: Database interaction classes in `/includes/class.*.php`
- **Views**: HTML templates and pages in `/pages/`
- **Controllers**: Business logic distributed across classes

#### 2. Repository Pattern
```php
class Database {
    // Provides centralized data access
    public function query($sql, $params = [])
    public function fetchRow($sql, $params = [])
    public function fetchAll($sql, $params = [])
}
```

#### 3. Singleton Pattern
```php
// Global database instance
$db = null;
function getDatabase($config = []) {
    global $db;
    if ($db === null) {
        $db = new Database($config);
    }
    return $db;
}
```

#### 4. Factory Pattern
```php
class User {
    public function __construct($db = null) {
        $this->db = $db ?: getDatabase();
    }
}
```

## Development Environment

### Setting Up Development Environment

#### 1. Using Docker (Recommended)
```bash
# Clone the repository
git clone <repository-url>
cd classroom-clone

# Start the development environment
docker-compose up -d

# Access the application
# http://localhost:8080
```

#### 2. Manual Setup
```bash
# Install dependencies
composer install

# Set up environment
cp .env.example .env
# Edit .env with your configuration

# Set up database
mysql -u root -p < database/schema.sql

# Start development server
php -S localhost:8080 -t .
```

### Development Tools

#### Code Editor Setup
- **PHPStorm/VS Code** with PHP extensions
- **Xdebug** for debugging
- **Composer** for dependency management

#### Database Tools
- **phpMyAdmin** (via Docker)
- **MySQL Workbench**
- **Command line MySQL**

#### Testing Tools
```bash
# If PHPUnit is installed
./vendor/bin/phpunit tests/

# Manual testing endpoints
php -S localhost:8080 -t . &
```

## Code Organization

### Directory Structure
```
classroom-clone/
├── api/                    # RESTful API endpoints
│   ├── attendance/         # Attendance API
│   ├── assignments/        # Assignment API
│   ├── courses/           # Course API
│   └── users/             # User API
├── assets/                # Static assets
│   ├── css/              # Stylesheets
│   ├── js/               # JavaScript
│   └── images/           # Images
├── config/               # Configuration files
│   ├── config.php        # Main configuration
│   ├── database.php      # Database configuration
│   └── environment.php   # Environment settings
├── includes/             # Core PHP classes
│   ├── class.Database.php
│   ├── class.User.php
│   ├── class.Attendance.php
│   ├── class.Course.php
│   ├── class.Assignment.php
│   ├── auth.php          # Authentication
│   ├── middleware.php    # Middleware functions
│   └── functions.php     # Utility functions
├── pages/                # Web pages
│   ├── admin/            # Admin interface
│   ├── auth/             # Authentication pages
│   ├── dashboard.php     # Main dashboard
│   ├── courses/          # Course management
│   ├── assignments/      # Assignment management
│   ├── attendance/       # Attendance tracking
│   └── templates/        # HTML templates
├── database/             # Database files
│   ├── schema.sql        # Database schema
│   ├── sample_data.sql   # Sample data
│   └── migrations/       # Database migrations
├── uploads/              # File uploads
│   ├── avatars/          # User avatars
│   ├── assignments/      # Assignment files
│   └── resources/        # Shared resources
└── logs/                 # Application logs
    ├── application.log   # General logs
    ├── error.log         # Error logs
    ├── security.log      # Security logs
    └── audit.log         # Audit logs
```

### Naming Conventions

#### Files
- **Classes**: `class.ClassName.php` (PascalCase)
- **Pages**: `page-name.php` (kebab-case)
- **Templates**: `template-name.php` (kebab-case)
- **Assets**: `asset-name.css` (kebab-case)

#### Database
- **Tables**: `snake_case` (e.g., `user_accounts`)
- **Columns**: `snake_case` (e.g., `first_name`)
- **Primary Keys**: `id` (auto-increment)
- **Foreign Keys**: `table_name_id`

#### Variables and Functions
- **Variables**: `$camelCase` (e.g., `$userId`)
- **Functions**: `snake_case` (e.g., `get_user_data()`)
- **Constants**: `UPPER_CASE` (e.g., `MAX_LOGIN_ATTEMPTS`)
- **Classes**: `PascalCase` (e.g., `DatabaseManager`)

## Core Components

### 1. Database Layer

#### Database Class
```php
class Database {
    // Singleton pattern
    private $connection;
    private $config;
    
    // CRUD operations
    public function insert($table, $data)
    public function update($table, $data, $where, $whereParams = [])
    public function delete($table, $where, $whereParams = [])
    public function query($sql, $params = [])
    
    // Transaction support
    public function beginTransaction()
    public function commit()
    public function rollback()
    public function transaction($callback)
}
```

#### Usage Example
```php
$db = getDatabase();

// INSERT
$userId = $db->insert('users', [
    'username' => 'john_doe',
    'email' => 'john@example.com',
    'password_hash' => password_hash('password', PASSWORD_DEFAULT)
]);

// UPDATE
$affectedRows = $db->update('users', 
    ['email' => 'newemail@example.com'], 
    'id = ?', 
    [$userId]
);

// SELECT
$user = $db->fetchRow("SELECT * FROM users WHERE id = ?", [$userId]);
$users = $db->fetchAll("SELECT * FROM users WHERE status = ?", ['active']);
```

### 2. User Management

#### User Class
```php
class User {
    private $db;
    
    // CRUD operations
    public function create($userData, $createdBy = null)
    public function getById($userId)
    public function getAll($filters = [], $limit = 20, $offset = 0)
    public function update($userId, $userData, $updatedBy = null)
    public function delete($userId, $deletedBy = null)
    
    // Authentication
    public function authenticate($username, $password)
    public function changePassword($userId, $newPassword, $currentPassword = null)
    
    // User utilities
    public function getByRole($role, $activeOnly = true)
    public function getStatistics()
}
```

#### Usage Example
```php
$user = new User();

// Create user
$result = $user->create([
    'username' => 'john_doe',
    'email' => 'john@example.com',
    'password' => 'secure_password',
    'first_name' => 'John',
    'last_name' => 'Doe',
    'role' => 'student'
]);

if ($result['success']) {
    echo "User created with ID: " . $result['user_id'];
}

// Get user by ID
$userData = $user->getById(123);

// Get all teachers
$teachers = $user->getByRole('teacher');
```

### 3. Attendance System

#### Attendance Class
```php
class Attendance {
    public function checkIn($employeeId, $timestamp = null)
    public function checkOut($employeeId, $timestamp = null)
    public function getTodayRecord($employeeId)
    public function getMonthlyStats($employeeId, $year, $month)
    public function getClassAttendance($classId, $date = null)
}
```

#### Usage Example
```php
$attendance = new Attendance();

// Employee check-in
$result = $attendance->checkIn(123);
if ($result['success']) {
    echo "Checked in at: " . $result['check_in_time'];
}

// Employee check-out
$result = $attendance->checkOut(123);
if ($result['success']) {
    echo "Checked out at: " . $result['check_out_time'];
}

// Get today's record
$record = $attendance->getTodayRecord(123);
```

### 4. API Layer

#### RESTful API Structure
```
/api/v1/
├── auth/
│   ├── login.php         # User authentication
│   ├── logout.php        # User logout
│   └── register.php      # User registration
├── users/
│   ├── index.php         # GET /users
│   ├── create.php        # POST /users
│   ├── view.php          # GET /users/{id}
│   ├── update.php        # PUT /users/{id}
│   └── delete.php        # DELETE /users/{id}
├── courses/
│   ├── index.php         # GET /courses
│   ├── create.php        # POST /courses
│   ├── enroll.php        # POST /courses/{id}/enroll
│   └── assignments.php   # GET /courses/{id}/assignments
├── assignments/
│   ├── index.php         # GET /assignments
│   ├── create.php        # POST /assignments
│   ├── submit.php        # POST /assignments/{id}/submit
│   └── grade.php         # PUT /assignments/{id}/grade
└── attendance/
    ├── checkin.php       # POST /attendance/checkin
    ├── checkout.php      # POST /attendance/checkout
    ├── today.php         # GET /attendance/today
    └── stats.php         # GET /attendance/stats/{employee_id}
```

#### API Response Format
```json
{
    "success": true,
    "data": {
        "user_id": 123,
        "username": "john_doe",
        "email": "john@example.com"
    },
    "message": "User retrieved successfully",
    "pagination": {
        "current_page": 1,
        "total_pages": 5,
        "per_page": 20,
        "total_records": 95
    },
    "timestamp": "2025-11-05T22:44:46Z"
}
```

#### API Usage Example
```javascript
// Login
fetch('/api/v1/auth/login', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json'
    },
    body: JSON.stringify({
        username: 'john_doe',
        password: 'secure_password'
    })
})
.then(response => response.json())
.then(data => {
    if (data.success) {
        localStorage.setItem('auth_token', data.token);
    }
});

// Get user profile
fetch('/api/v1/users/123', {
    headers: {
        'Authorization': 'Bearer ' + localStorage.getItem('auth_token')
    }
})
.then(response => response.json())
.then(data => {
    console.log('User:', data.data);
});
```

## Development Workflow

### 1. Feature Development

#### Creating a New Feature
1. **Plan**: Define requirements and API endpoints
2. **Database**: Add necessary tables/columns
3. **Backend**: Create model classes and API endpoints
4. **Frontend**: Create UI components and pages
5. **Testing**: Write tests and manual testing
6. **Documentation**: Update documentation

#### Example: Adding a New Module
```sql
-- Step 1: Database schema
CREATE TABLE announcements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_created_by (created_by),
    FOREIGN KEY (created_by) REFERENCES users(id)
);
```

```php
// Step 2: Model class
class Announcement {
    private $db;
    
    public function create($data) {
        return $this->db->insert('announcements', $data);
    }
    
    public function getAll() {
        return $this->db->fetchAll("SELECT * FROM announcements ORDER BY created_at DESC");
    }
}
```

```php
// Step 3: API endpoint
// /api/v1/announcements/create.php
header('Content-Type: application/json');

try {
    $input = json_decode(file_get_contents('php://input'), true);
    $announcement = new Announcement();
    
    $result = $announcement->create([
        'title' => $input['title'],
        'content' => $input['content'],
        'created_by' => $_SESSION['user_id']
    ]);
    
    echo json_encode(['success' => true, 'announcement_id' => $result]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
```

### 2. Code Review Process

#### Code Quality Checklist
- [ ] Follow PSR-12 coding standards
- [ ] Add comprehensive docblocks
- [ ] Include error handling
- [ ] Use prepared statements
- [ ] Validate all inputs
- [ ] Log important operations
- [ ] Write unit tests
- [ ] Update documentation

#### Example Code Review
```php
/**
 * Create a new course
 * 
 * @param array $courseData Course information
 * @param int $createdBy User ID who created the course
 * @return array Result with success status and course ID
 * @throws InvalidArgumentException If validation fails
 * @throws PDOException If database operation fails
 */
public function createCourse($courseData, $createdBy) {
    try {
        // Validate required fields
        if (empty($courseData['name']) || empty($courseData['code'])) {
            throw new InvalidArgumentException('Course name and code are required');
        }
        
        // Check for duplicate course code
        if ($this->courseCodeExists($courseData['code'])) {
            throw new InvalidArgumentException('Course code already exists');
        }
        
        // Sanitize input
        $cleanData = $this->sanitizeCourseData($courseData);
        $cleanData['created_by'] = $createdBy;
        $cleanData['created_at'] = date('Y-m-d H:i:s');
        
        // Insert course
        $courseId = $this->db->insert('courses', $cleanData);
        
        // Log activity
        $this->logActivity($createdBy, 'course_created', "Created course: {$courseData['name']}");
        
        return [
            'success' => true,
            'course_id' => $courseId,
            'message' => 'Course created successfully'
        ];
        
    } catch (Exception $e) {
        $this->logError('Course creation failed: ' . $e->getMessage(), [
            'course_data' => $courseData,
            'created_by' => $createdBy
        ]);
        throw $e;
    }
}
```

### 3. Version Control

#### Git Workflow
```bash
# Feature development
git checkout -b feature/new-course-management
git add .
git commit -m "Add course management feature"
git push origin feature/new-course-management

# Create pull request
# After review and merge
git checkout main
git pull origin main
git branch -d feature/new-course-management
```

#### Commit Message Format
```
type(scope): description

[optional body]

[optional footer]
```

**Types**:
- `feat`: New feature
- `fix`: Bug fix
- `docs`: Documentation changes
- `style`: Code style changes
- `refactor`: Code refactoring
- `test`: Adding tests
- `chore`: Maintenance tasks

**Examples**:
```
feat(auth): add password reset functionality
fix(database): resolve connection timeout issue
docs(api): update authentication endpoints
```

## Best Practices

### 1. Security

#### Input Validation
```php
// Always validate and sanitize input
function validateUserInput($input) {
    $rules = [
        'email' => ['required' => true, 'email' => true],
        'password' => ['required' => true, 'min_length' => 8],
        'age' => ['required' => true, 'numeric' => true, 'min' => 13, 'max' => 120]
    ];
    
    return validateData($input, $rules);
}

// Sanitize all user input
function sanitizeInput($data) {
    if (is_array($data)) {
        return array_map('sanitizeInput', $data);
    }
    return htmlspecialchars(strip_tags(trim($data)));
}
```

#### SQL Injection Prevention
```php
// Always use prepared statements
$stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
$stmt->execute([$email]);

// Using Database class
$user = $db->fetchRow("SELECT * FROM users WHERE email = ?", [$email]);
```

#### Password Security
```php
// Hash passwords with bcrypt
$passwordHash = password_hash($password, PASSWORD_DEFAULT);

// Verify passwords
if (password_verify($password, $passwordHash)) {
    // Password is correct
}
```

### 2. Performance

#### Database Optimization
```php
// Use indexes for frequently queried columns
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_assignments_due_date ON assignments(due_date);

// Optimize queries
$users = $db->fetchAll("
    SELECT u.id, u.username, u.email, p.profile_image 
    FROM users u 
    LEFT JOIN profiles p ON u.id = p.user_id 
    WHERE u.status = 'active' 
    ORDER BY u.created_at DESC 
    LIMIT 20
");
```

#### Caching
```php
// Cache frequently accessed data
function getCachedData($key, $callback, $ttl = 3600) {
    $cached = getCache($key);
    if ($cached !== null) {
        return $cached;
    }
    
    $data = $callback();
    setCache($key, $data, $ttl);
    return $data;
}

// Usage
$userStats = getCachedData("user_stats_123", function() use ($userId) {
    return getUserStatistics($userId);
}, 1800); // 30 minutes
```

### 3. Error Handling

#### Exception Handling
```php
try {
    $user = new User();
    $result = $user->create($userData);
    
    if (!$result['success']) {
        throw new Exception('User creation failed: ' . implode(', ', $result['errors']));
    }
    
} catch (InvalidArgumentException $e) {
    // Handle validation errors
    logError("Validation error: " . $e->getMessage());
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid input data']);
    
} catch (PDOException $e) {
    // Handle database errors
    logError("Database error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database operation failed']);
    
} catch (Exception $e) {
    // Handle all other errors
    logError("Unexpected error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'An unexpected error occurred']);
}
```

#### Logging
```php
function logError($message, $context = [], $level = 'error') {
    $logEntry = [
        'timestamp' => date('Y-m-d H:i:s'),
        'level' => $level,
        'message' => $message,
        'context' => $context,
        'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
    ];
    
    $logLine = json_encode($logEntry) . "\n";
    file_put_contents(__DIR__ . '/../logs/application.log', $logLine, FILE_APPEND | LOCK_EX);
}
```

### 4. Code Organization

#### Class Structure
```php
<?php
/**
 * Class Description
 * 
 * Detailed description of what this class does and its purpose
 * 
 * @package PackageName
 * @version 1.0
 * @author Author Name
 */

class ClassName {
    // Constants
    const DEFAULT_LIMIT = 20;
    const MAX_RECORDS = 1000;
    
    // Properties
    private $property1;
    private $property2;
    
    // Constructor
    public function __construct($dependency1 = null, $dependency2 = null) {
        $this->property1 = $dependency1;
        $this->property2 = $dependency2;
        $this->initialize();
    }
    
    // Public methods
    public function publicMethod() {
        // Implementation
    }
    
    // Private methods
    private function privateMethod() {
        // Implementation
    }
    
    // Static methods
    public static function staticMethod() {
        // Implementation
    }
}
?>
```

#### Function Documentation
```php
/**
 * Get user information with related data
 * 
 * This function retrieves user information along with their profile,
 * permissions, and activity statistics. It includes data caching
 * to improve performance for frequently accessed users.
 * 
 * @param int $userId The ID of the user to retrieve
 * @param bool $includeProfile Whether to include profile information
 * @param bool $useCache Whether to use cached data if available
 * @return array|null User information array or null if user not found
 * @throws InvalidArgumentException If userId is not a positive integer
 * @throws PDOException If database query fails
 * 
 * @example
 * $user = getUserInfo(123, true, true);
 * if ($user) {
 *     echo "User: " . $user['username'];
 *     echo "Profile: " . $user['profile']['bio'];
 * }
 */
function getUserInfo($userId, $includeProfile = false, $useCache = true) {
    // Implementation
}
```

## Troubleshooting

### Common Issues

#### 1. Database Connection Issues
```bash
# Test database connection
php -r "
try {
    \$pdo = new PDO('mysql:host=localhost;dbname=test', 'root', '');
    echo 'Connection successful';
} catch (PDOException \$e) {
    echo 'Connection failed: ' . \$e->getMessage();
}
"

# Check MySQL status
sudo systemctl status mysql

# Check database permissions
mysql -u root -p -e "SHOW GRANTS FOR 'username'@'localhost';"
```

#### 2. Session Issues
```php
// Check session configuration
echo "Session save path: " . session_save_path() . "\n";
echo "Session status: " . session_status() . "\n";

// Debug session data
var_dump($_SESSION);

// Regenerate session ID
session_regenerate_id(true);
```

#### 3. File Upload Issues
```bash
# Check upload directory permissions
ls -la uploads/
chmod 777 uploads/
chown www-data:www-data uploads/

# Check PHP upload limits
php -r "echo 'Upload max filesize: ' . ini_get('upload_max_filesize') . \"\n\";"
php -r "echo 'Post max size: ' . ini_get('post_max_size') . \"\n\";"
php -r "echo 'Max file uploads: ' . ini_get('max_file_uploads') . \"\n\";"
```

#### 4. Permission Denied
```bash
# Fix ownership and permissions
sudo chown -R www-data:www-data /path/to/application
sudo chmod -R 755 /path/to/application
sudo chmod -R 777 /path/to/uploads
sudo chmod -R 777 /path/to/logs
```

### Debug Mode

#### Enable Debug Mode
```php
// In config/environment.php
define('APP_ENV', 'development');
define('DEBUG_MODE', true);
define('LOG_LEVEL', 'debug');

// Enable error display
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
```

#### Debug Logging
```php
// Add debug logging
function debugLog($message, $data = []) {
    if (DEBUG_MODE) {
        $backtrace = debug_backtrace();
        $caller = $backtrace[0];
        
        logError("DEBUG: {$message}", [
            'file' => $caller['file'],
            'line' => $caller['line'],
            'data' => $data
        ], 'debug');
    }
}

// Usage
debugLog("User data received", $userData);
```

#### Database Query Debugging
```php
class Database {
    public function query($sql, $params = []) {
        if (DEBUG_MODE) {
            $startTime = microtime(true);
        }
        
        // Execute query
        $stmt = $this->connection->prepare($sql);
        $stmt->execute($params);
        
        if (DEBUG_MODE) {
            $executionTime = microtime(true) - $startTime;
            debugLog("Query executed", [
                'sql' => $sql,
                'params' => $params,
                'execution_time' => $executionTime,
                'rows_affected' => $stmt->rowCount()
            ]);
        }
        
        return $stmt;
    }
}
```

### Performance Monitoring

#### Query Performance
```sql
-- Enable slow query log in MySQL
SET GLOBAL slow_query_log = 'ON';
SET GLOBAL long_query_time = 2;

-- Analyze slow queries
SELECT query_time, lock_time, rows_sent, rows_examined, sql_text
FROM mysql.slow_log
ORDER BY query_time DESC
LIMIT 10;
```

#### Application Performance
```php
// Add performance monitoring
function measurePerformance($label, $callback) {
    $startTime = microtime(true);
    $startMemory = memory_get_usage();
    
    $result = $callback();
    
    $endTime = microtime(true);
    $endMemory = memory_get_usage();
    
    debugLog("Performance: {$label}", [
        'execution_time' => ($endTime - $startTime) * 1000 . 'ms',
        'memory_used' => ($endMemory - $startMemory) / 1024 / 1024 . 'MB',
        'peak_memory' => memory_get_peak_usage() / 1024 / 1024 . 'MB'
    ]);
    
    return $result;
}

// Usage
$users = measurePerformance("Get all users", function() use ($db) {
    return $db->fetchAll("SELECT * FROM users");
});
```

---

This developer guide provides a comprehensive overview of the Google Classroom Clone system. For specific API documentation, database schema, and security details, refer to the other documentation files in this guide.
