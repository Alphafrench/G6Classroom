# Code Structure Documentation - Google Classroom Clone

## Table of Contents
1. [Overview](#overview)
2. [Project Organization](#project-organization)
3. [Directory Structure](#directory-structure)
4. [Core Components](#core-components)
5. [File Naming Conventions](#file-naming-conventions)
6. [Class Architecture](#class-architecture)
7. [Configuration Management](#configuration-management)
8. [Asset Management](#asset-management)
9. [Template System](#template-system)
10. [API Structure](#api-structure)
11. [Database Layer](#database-layer)
12. [Security Implementation](#security-implementation)
13. [Code Style Guidelines](#code-style-guidelines)
14. [Development Workflow](#development-workflow)

## Overview

The Google Classroom Clone follows a modular, object-oriented architecture with clear separation of concerns. The codebase is organized to promote maintainability, scalability, and ease of development.

### Architecture Principles
- **Separation of Concerns**: Each component has a single responsibility
- **DRY (Don't Repeat Yourself)**: Code reuse through classes and functions
- **SOLID Principles**: Single responsibility, Open/closed, Liskov substitution, Interface segregation, Dependency inversion
- **MVC Pattern**: Model-View-Controller separation
- **Security First**: Built-in security measures at every level

## Project Organization

### High-Level Structure
```
classroom-clone/
├── 📁 api/                    # RESTful API endpoints
├── 📁 assets/                 # Static assets (CSS, JS, images)
├── 📁 config/                 # Configuration files
├── 📁 database/               # Database schemas and migrations
├── 📁 includes/               # Core PHP classes and functions
├── 📁 pages/                  # Web pages and templates
├── 📁 uploads/                # File uploads directory
├── 📁 logs/                   # Application logs
├── 🐘 index.php               # Application entry point
├── 🐳 docker-compose.yml      # Docker configuration
└── 📄 README.md               # Project documentation
```

## Directory Structure

### 1. API Directory (`/api/`)
RESTful API endpoints organized by functionality.

```
api/
├── 📁 attendance/             # Attendance management API
│   ├── checkin.php           # Employee check-in endpoint
│   ├── checkout.php          # Employee check-out endpoint
│   ├── details.php           # Get attendance details
│   ├── export.php            # Export attendance data
│   ├── recent.php            # Recent attendance records
│   └── stats.php             # Attendance statistics
├── 📁 assignments/           # Assignment management API
│   ├── create.php            # Create new assignment
│   ├── submit.php            # Submit assignment
│   ├── grade.php             # Grade submissions
│   └── index.php             # List assignments
├── 📁 courses/               # Course management API
│   ├── create.php            # Create new course
│   ├── enroll.php            # Enroll students
│   ├── index.php             # List courses
│   └── view.php              # Get course details
└── 📁 users/                 # User management API
    ├── create.php            # Create user
    ├── index.php             # List users
    ├── update.php            # Update user
    └── delete.php            # Delete user
```

**API Endpoint Structure**:
```php
<?php
// api/attendance/checkin.php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

session_start();

// Authentication check
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit();
}

// Input validation
$input = json_decode(file_get_contents('php://input'), true);
if (!$input || !validateInput($input)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid input']);
    exit();
}

// Business logic
try {
    $attendance = new Attendance();
    $result = $attendance->checkIn($input['employee_id']);
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Internal server error']);
}
?>
```

### 2. Assets Directory (`/assets/`)
Static resources organized by type and purpose.

```
assets/
├── 📁 css/                    # Stylesheets
│   ├── attendance.css        # Attendance-specific styles
│   ├── dashboard.css         # Dashboard layout styles
│   ├── style.css             # Global styles
│   └── components.css        # Reusable UI components
├── 📁 js/                     # JavaScript files
│   ├── app.js                # Main application JavaScript
│   ├── attendance.js         # Attendance functionality
│   ├── reports.js            # Report generation
│   └── utils.js              # Utility functions
└── 📁 images/                 # Image assets
    ├── 📁 icons/             # System icons
    ├── 📁 logos/             # Application logos
    └── 📁 placeholders/      # Placeholder images
```

**CSS Organization**:
```css
/* assets/css/style.css */

/* CSS Custom Properties */
:root {
    --primary-color: #007bff;
    --secondary-color: #6c757d;
    --success-color: #28a745;
    --danger-color: #dc3545;
    --warning-color: #ffc107;
    --info-color: #17a2b8;
    --light-color: #f8f9fa;
    --dark-color: #343a40;
    
    --font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    --border-radius: 0.375rem;
    --box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    --transition: all 0.15s ease-in-out;
}

/* Global Styles */
body {
    font-family: var(--font-family);
    line-height: 1.6;
    color: #333;
}

/* Component Styles */
.btn {
    display: inline-block;
    padding: 0.5rem 1rem;
    border: 1px solid transparent;
    border-radius: var(--border-radius);
    font-size: 1rem;
    font-weight: 400;
    text-align: center;
    transition: var(--transition);
}

.btn-primary {
    color: #fff;
    background-color: var(--primary-color);
    border-color: var(--primary-color);
}
```

**JavaScript Organization**:
```javascript
// assets/js/app.js

// Application namespace
const ClassroomApp = {
    // Configuration
    config: {
        apiBaseUrl: '/api/v1',
        tokenKey: 'auth_token',
        csrfToken: null
    },
    
    // Initialize application
    init() {
        this.setupEventListeners();
        this.loadAuthToken();
        this.setupCSRF();
    },
    
    // Event listeners
    setupEventListeners() {
        // Form submissions
        document.addEventListener('submit', this.handleFormSubmit.bind(this));
        
        // AJAX requests
        document.addEventListener('click', this.handleAjaxClick.bind(this));
        
        // Auto-save functionality
        this.setupAutoSave();
    },
    
    // API helper
    async apiCall(endpoint, options = {}) {
        const url = `${this.config.apiBaseUrl}${endpoint}`;
        const config = {
            headers: {
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${this.getAuthToken()}`
            },
            ...options
        };
        
        try {
            const response = await fetch(url, config);
            return await response.json();
        } catch (error) {
            console.error('API call failed:', error);
            throw error;
        }
    },
    
    // Authentication
    getAuthToken() {
        return localStorage.getItem(this.config.tokenKey);
    },
    
    setAuthToken(token) {
        localStorage.setItem(this.config.tokenKey, token);
    }
};

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    ClassroomApp.init();
});
```

### 3. Configuration Directory (`/config/`)
Application configuration and environment settings.

```
config/
├── config.php                # Main application configuration
├── database.php              # Database configuration
└── environment.php           # Environment-specific settings
```

**Configuration Structure**:
```php
<?php
// config/config.php

// Load environment configuration
require_once __DIR__ . '/environment.php';

// Application constants
define('APP_NAME', 'Google Classroom Clone');
define('APP_VERSION', '1.0.0');
define('APP_URL', rtrim(APP_URL, '/'));

// Security settings
define('SECURITY_SALT', $_ENV['SECURITY_SALT'] ?? 'default-salt');
define('SESSION_TIMEOUT', 7200); // 2 hours
define('MAX_LOGIN_ATTEMPTS', 5);

// File upload settings
define('UPLOAD_MAX_SIZE', 10 * 1024 * 1024); // 10MB
define('ALLOWED_IMAGE_TYPES', ['jpg', 'jpeg', 'png', 'gif', 'webp']);

// Database configuration
define('DB_HOST', $_ENV['DB_HOST'] ?? 'localhost');
define('DB_NAME', $_ENV['DB_NAME'] ?? 'classroom_clone');
define('DB_USER', $_ENV['DB_USER'] ?? 'root');
define('DB_PASS', $_ENV['DB_PASS'] ?? '');

// Cache settings
define('ENABLE_CACHE', APP_ENV === 'production');
define('CACHE_LIFETIME', 3600);

// Feature flags
define('ENABLE_FILE_SHARING', true);
define('ENABLE_GRADING_SYSTEM', true);
define('ENABLE_ATTENDANCE_TRACKING', true);

/**
 * Auto-loader for classes
 */
function autoloadClasses($className) {
    $file = ROOT_PATH . '/includes/' . $className . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
}

spl_autoload_register('autoloadClasses');

/**
 * Global utility functions
 */
function sanitizeInput($data) {
    if (is_array($data)) {
        return array_map('sanitizeInput', $data);
    }
    return htmlspecialchars(strip_tags(trim($data)));
}

function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function hasPermission($permission) {
    if (!isLoggedIn()) {
        return false;
    }
    return isset($_SESSION['permissions']) && in_array($permission, $_SESSION['permissions']);
}
?>
```

### 4. Includes Directory (`/includes/`)
Core PHP classes, functions, and middleware.

```
includes/
├── 📄 class.Database.php        # Database connection and query management
├── 📄 class.User.php            # User management operations
├── 📄 class.Attendance.php      # Attendance tracking
├── 📄 class.Course.php          # Course management
├── 📄 class.Assignment.php      # Assignment system
├── 📄 class.Employee.php        # Employee management
├── 📄 class.Session.php         # Session management
├── 📄 auth.php                  # Authentication functions
├── 📄 middleware.php            # Middleware functions
├── 📄 functions.php             # Utility functions
├── 📄 header.php                # HTML header template
├── 📄 footer.php                # HTML footer template
├── 📄 config.php                # Configuration loader
└── 📄 export.php                # Export utilities
```

**Class Structure Example**:
```php
<?php
// includes/class.User.php

/**
 * User Management Class
 * 
 * Handles user operations including authentication, profile management,
 * and role-based access control.
 * 
 * @package AttendanceSystem
 * @version 1.0
 */
class User {
    /**
     * Database instance
     * @var Database
     */
    private $db;
    
    /**
     * Users table name
     * @var string
     */
    private $table = 'users';
    
    /**
     * Allowed user roles
     * @var array
     */
    private $allowedRoles = ['admin', 'teacher', 'student'];
    
    /**
     * Constructor
     * 
     * @param Database|null $db Database instance
     */
    public function __construct($db = null) {
        $this->db = $db ?: getDatabase();
        initialize_session();
    }
    
    /**
     * Create a new user
     * 
     * @param array $userData User data
     * @param int|null $createdBy User ID who created this user
     * @return array Result with success status and errors
     */
    public function create($userData, $createdBy = null) {
        try {
            // Validate input data
            $validationResult = $this->validateUserData($userData);
            if (!$validationResult['valid']) {
                return [
                    'success' => false,
                    'errors' => $validationResult['errors']
                ];
            }
            
            // Check for duplicates
            if ($this->usernameExists($userData['username'])) {
                return [
                    'success' => false,
                    'errors' => ['Username already exists']
                ];
            }
            
            // Sanitize and hash data
            $cleanData = $this->sanitizeUserData($userData);
            $cleanData['password_hash'] = hashPassword($cleanData['password']);
            unset($cleanData['password']);
            
            // Add metadata
            $cleanData['created_at'] = date('Y-m-d H:i:s');
            $cleanData['email_verified'] = 0;
            $cleanData['failed_attempts'] = 0;
            
            // Insert user
            $userId = $this->db->insert($this->table, $cleanData);
            
            // Log activity
            $this->logActivity($userId, 'user_created', "User created by user ID: {$createdBy}");
            
            return [
                'success' => true,
                'user_id' => $userId,
                'message' => 'User created successfully'
            ];
            
        } catch (Exception $e) {
            logError("Failed to create user: " . $e->getMessage());
            return [
                'success' => false,
                'errors' => ['Failed to create user: ' . $e->getMessage()]
            ];
        }
    }
    
    /**
     * Private helper methods
     */
    private function validateUserData($data) {
        // Validation implementation
    }
    
    private function sanitizeUserData($data) {
        // Sanitization implementation
    }
    
    private function logActivity($userId, $action, $description) {
        // Activity logging implementation
    }
}
?>
```

### 5. Pages Directory (`/pages/`)
Web pages and templates organized by functionality.

```
pages/
├── 📄 index.php                # Home page
├── 📄 dashboard.php            # Main dashboard
├── 📄 login.php                # Login page
├── 📄 register.php             # Registration page
├── 📄 logout.php               # Logout handler
├── 📁 admin/                   # Administrative interface
│   ├── index.php              # Admin dashboard
│   ├── users.php              # User management
│   ├── employees.php          # Employee management
│   ├── settings.php           # System settings
│   └── backup.php             # Backup management
├── 📁 assignments/             # Assignment pages
│   ├── index.php              # Assignment list
│   ├── create.php             # Create assignment
│   ├── view.php               # View assignment
│   ├── submit.php             # Submit assignment
│   └── grade.php              # Grade assignments
├── 📁 courses/                 # Course pages
│   ├── index.php              # Course list
│   ├── create.php             # Create course
│   ├── view.php               # View course
│   ├── edit.php               # Edit course
│   └── enroll.php             # Student enrollment
├── 📁 attendance/              # Attendance pages
│   ├── index.php              # Attendance dashboard
│   ├── take.php               # Take attendance
│   ├── records.php            # Attendance records
│   ├── reports.php            # Attendance reports
│   └── student.php            # Student attendance view
├── 📁 employees/               # Employee pages
│   ├── index.php              # Employee list
│   ├── add.php                # Add employee
│   ├── edit.php               # Edit employee
│   ├── view.php               # View employee
│   └── delete.php             # Delete employee
└── 📁 templates/               # HTML templates
    ├── dashboard-template.php  # Dashboard layout
    ├── form-template.php       # Form layout
    ├── table-template.php      # Table layout
    ├── card-template.php       # Card component
    └── modal-template.php      # Modal component
```

**Page Structure Example**:
```php
<?php
// pages/dashboard.php

require_once '../config/config.php';
require_once '../includes/auth.php';

// Check authentication
if (!isLoggedIn()) {
    redirect('/pages/login.php');
}

// Get current user
$currentUser = getCurrentUser();
$userRole = $currentUser['role'];

// Initialize dashboard data based on role
switch ($userRole) {
    case 'admin':
        $dashboardData = getAdminDashboardData();
        break;
    case 'teacher':
        $dashboardData = getTeacherDashboardData($currentUser['id']);
        break;
    case 'student':
        $dashboardData = getStudentDashboardData($currentUser['id']);
        break;
    default:
        $dashboardData = [];
}

// Include header
include '../includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-md-3">
            <?php include '../includes/sidebar.php'; ?>
        </div>
        
        <!-- Main content -->
        <div class="col-md-9">
            <div class="dashboard-header">
                <h1>Welcome, <?php echo htmlspecialchars($currentUser['first_name']); ?>!</h1>
                <p class="text-muted">Dashboard overview for your role</p>
            </div>
            
            <!-- Dashboard widgets -->
            <div class="row">
                <?php foreach ($dashboardData['widgets'] as $widget): ?>
                    <div class="col-md-6 col-lg-4 mb-4">
                        <?php include "../pages/templates/{$widget['template']}.php"; ?>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <!-- Recent activity -->
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5>Recent Activity</h5>
                        </div>
                        <div class="card-body">
                            <?php include '../includes/recent_activity.php'; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
```

## Core Components

### 1. Database Layer
The database layer provides a clean abstraction for data access.

**Database Class**:
```php
class Database {
    private $connection;
    private $config;
    
    public function __construct($config = []) {
        $this->config = array_merge([
            'host' => 'localhost',
            'port' => 3306,
            'database' => 'classroom_clone',
            'username' => 'root',
            'password' => '',
            'charset' => 'utf8mb4'
        ], $config);
        
        $this->connect();
    }
    
    private function connect() {
        try {
            $dsn = "mysql:host={$this->config['host']};port={$this->config['port']};dbname={$this->config['database']};charset={$this->config['charset']}";
            
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ];
            
            $this->connection = new PDO($dsn, $this->config['username'], $this->config['password'], $options);
            
        } catch (PDOException $e) {
            throw new PDOException("Database connection failed", 0, $e);
        }
    }
    
    public function query($sql, $params = []) {
        $stmt = $this->connection->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }
    
    public function insert($table, $data) {
        $columns = implode(',', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));
        
        $sql = "INSERT INTO {$table} ({$columns}) VALUES ({$placeholders})";
        $this->query($sql, $data);
        
        return $this->connection->lastInsertId();
    }
    
    public function update($table, $data, $where, $whereParams = []) {
        $setClause = [];
        foreach (array_keys($data) as $column) {
            $setClause[] = "{$column} = :{$column}";
        }
        $setClause = implode(', ', $setClause);
        
        $sql = "UPDATE {$table} SET {$setClause} WHERE {$where}";
        $params = array_merge($data, $whereParams);
        
        $stmt = $this->query($sql, $params);
        return $stmt->rowCount();
    }
    
    public function transaction($callback) {
        try {
            $this->beginTransaction();
            $result = $callback($this);
            $this->commit();
            return $result;
        } catch (Exception $e) {
            $this->rollback();
            throw $e;
        }
    }
}
```

### 2. Authentication System
```php
// includes/auth.php

class Auth {
    private $db;
    
    public function __construct($db = null) {
        $this->db = $db ?: getDatabase();
        $this->startSession();
    }
    
    public function login($username, $password) {
        // Get user by username or email
        $user = $this->getUserByUsername($username);
        
        if (!$user || !password_verify($password, $user['password_hash'])) {
            $this->logFailedAttempt($username);
            return false;
        }
        
        if ($user['status'] !== 'active') {
            return false;
        }
        
        // Check failed attempts
        if ($user['failed_attempts'] >= MAX_LOGIN_ATTEMPTS) {
            $lockoutTime = strtotime($user['last_failed_attempt']) + LOGIN_LOCKOUT_TIME;
            if (time() < $lockoutTime) {
                return false;
            }
        }
        
        // Successful login
        $this->setUserSession($user);
        $this->logSuccessfulLogin($user['id']);
        $this->resetFailedAttempts($user['id']);
        
        return true;
    }
    
    public function logout() {
        if (isset($_SESSION['user_id'])) {
            $this->logActivity($_SESSION['user_id'], 'logout', 'User logged out');
        }
        
        session_destroy();
        session_start();
    }
    
    private function setUserSession($user) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['logged_in_at'] = time();
        $_SESSION['last_activity'] = time();
    }
    
    private function logActivity($userId, $action, $description) {
        $activityData = [
            'user_id' => $userId,
            'action' => $action,
            'description' => $description,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $this->db->insert('activity_logs', $activityData);
    }
}
```

### 3. Middleware System
```php
// includes/middleware.php

class Middleware {
    /**
     * Authentication middleware
     */
    public static function auth() {
        if (!isLoggedIn()) {
            if (self::isAjaxRequest()) {
                http_response_code(401);
                echo json_encode(['success' => false, 'error' => 'Unauthorized']);
                exit;
            } else {
                redirect('/pages/login.php');
            }
        }
    }
    
    /**
     * Role-based authorization
     */
    public static function role($allowedRoles) {
        self::auth();
        
        $userRole = $_SESSION['role'] ?? 'guest';
        if (!in_array($userRole, $allowedRoles)) {
            if (self::isAjaxRequest()) {
                http_response_code(403);
                echo json_encode(['success' => false, 'error' => 'Forbidden']);
                exit;
            } else {
                redirect('/pages/dashboard.php');
            }
        }
    }
    
    /**
     * Permission-based authorization
     */
    public static function permission($permission) {
        self::auth();
        
        if (!hasPermission($permission)) {
            if (self::isAjaxRequest()) {
                http_response_code(403);
                echo json_encode(['success' => false, 'error' => 'Insufficient permissions']);
                exit;
            } else {
                die('Access denied');
            }
        }
    }
    
    /**
     * CSRF protection
     */
    public static function csrf() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $token = $_POST['csrf_token'] ?? '';
            if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
                if (self::isAjaxRequest()) {
                    http_response_code(400);
                    echo json_encode(['success' => false, 'error' => 'Invalid CSRF token']);
                    exit;
                } else {
                    die('Invalid CSRF token');
                }
            }
        }
    }
    
    /**
     * Rate limiting
     */
    public static function rateLimit($maxAttempts = 60, $timeWindow = 3600) {
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $key = "rate_limit_" . md5($ip);
        
        $attempts = $_SESSION[$key] ?? [];
        $now = time();
        
        // Remove old attempts
        $attempts = array_filter($attempts, function($timestamp) use ($now, $timeWindow) {
            return ($now - $timestamp) < $timeWindow;
        });
        
        if (count($attempts) >= $maxAttempts) {
            http_response_code(429);
            echo json_encode(['success' => false, 'error' => 'Rate limit exceeded']);
            exit;
        }
        
        $attempts[] = $now;
        $_SESSION[$key] = $attempts;
    }
    
    private static function isAjaxRequest() {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }
}
```

## File Naming Conventions

### PHP Files
- **Classes**: `class.ClassName.php` (PascalCase)
- **Pages**: `page-name.php` (kebab-case)
- **Templates**: `template-name.php` (kebab-case)
- **API Endpoints**: `endpoint-name.php` (kebab-case)

### CSS Files
- **Global**: `style.css` or `global.css`
- **Components**: `component-name.css` (kebab-case)
- **Pages**: `page-name.css` (kebab-case)

### JavaScript Files
- **Global**: `app.js`
- **Components**: `component-name.js` (kebab-case)
- **Pages**: `page-name.js` (kebab-case)
- **Utils**: `utils.js`

### Database Files
- **Schema**: `schema.sql`
- **Sample Data**: `sample_data.sql`
- **Migrations**: `migration_YYYY_MM_DD_HHMMSS.sql`

## Class Architecture

### Base Classes

#### 1. Base Model Class
```php
abstract class BaseModel {
    protected $db;
    protected $table;
    protected $primaryKey = 'id';
    
    public function __construct($db = null) {
        $this->db = $db ?: getDatabase();
    }
    
    public function find($id) {
        return $this->db->fetchRow(
            "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = ?",
            [$id]
        );
    }
    
    public function findAll($conditions = [], $limit = null, $offset = null) {
        $sql = "SELECT * FROM {$this->table}";
        $params = [];
        
        if (!empty($conditions)) {
            $where = [];
            foreach ($conditions as $field => $value) {
                $where[] = "{$field} = ?";
                $params[] = $value;
            }
            $sql .= " WHERE " . implode(' AND ', $where);
        }
        
        if ($limit) {
            $sql .= " LIMIT {$limit}";
            if ($offset) {
                $sql .= " OFFSET {$offset}";
            }
        }
        
        return $this->db->fetchAll($sql, $params);
    }
    
    public function create($data) {
        return $this->db->insert($this->table, $data);
    }
    
    public function update($id, $data) {
        return $this->db->update(
            $this->table,
            $data,
            "{$this->primaryKey} = ?",
            [$id]
        );
    }
    
    public function delete($id) {
        return $this->db->delete(
            $this->table,
            "{$this->primaryKey} = ?",
            [$id]
        );
    }
}
```

#### 2. Base Controller Class
```php
abstract class BaseController {
    protected $db;
    protected $user;
    protected $request;
    protected $response;
    
    public function __construct() {
        $this->db = getDatabase();
        $this->user = $this->getCurrentUser();
        $this->request = $this->getRequestData();
        $this->response = [];
    }
    
    protected function getCurrentUser() {
        if (isLoggedIn()) {
            return [
                'id' => $_SESSION['user_id'],
                'username' => $_SESSION['username'],
                'role' => $_SESSION['role']
            ];
        }
        return null;
    }
    
    protected function getRequestData() {
        $method = $_SERVER['REQUEST_METHOD'];
        
        switch ($method) {
            case 'GET':
                return $_GET;
            case 'POST':
            case 'PUT':
            case 'PATCH':
                $input = file_get_contents('php://input');
                return json_decode($input, true) ?: $_POST;
            case 'DELETE':
                return $_GET; // RESTful delete parameters
            default:
                return [];
        }
    }
    
    protected function jsonResponse($data, $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
    
    protected function validate($data, $rules) {
        // Validation implementation
        $errors = [];
        
        foreach ($rules as $field => $ruleSet) {
            $value = $data[$field] ?? null;
            
            foreach ($ruleSet as $rule => $param) {
                switch ($rule) {
                    case 'required':
                        if (empty($value)) {
                            $errors[$field][] = "{$field} is required";
                        }
                        break;
                    case 'email':
                        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                            $errors[$field][] = "{$field} must be a valid email";
                        }
                        break;
                    case 'min_length':
                        if (strlen($value) < $param) {
                            $errors[$field][] = "{$field} must be at least {$param} characters";
                        }
                        break;
                }
            }
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }
}
```

## Configuration Management

### Environment Configuration
```php
<?php
// config/environment.php

// Environment detection
$environment = $_ENV['APP_ENV'] ?? 'production';
define('APP_ENV', $environment);

// Environment-specific settings
$config = [
    'development' => [
        'debug' => true,
        'log_level' => 'debug',
        'display_errors' => true,
        'cache_enabled' => false
    ],
    'staging' => [
        'debug' => false,
        'log_level' => 'info',
        'display_errors' => false,
        'cache_enabled' => true
    ],
    'production' => [
        'debug' => false,
        'log_level' => 'error',
        'display_errors' => false,
        'cache_enabled' => true
    ]
];

// Apply environment configuration
foreach ($config[$environment] as $key => $value) {
    define(strtoupper($key), $value);
}

// Set error reporting based on environment
if (APP_ENV === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Load environment variables from file
if (file_exists(__DIR__ . '/.env')) {
    $lines = file(__DIR__ . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
            list($key, $value) = explode('=', $line, 2);
            $_ENV[trim($key)] = trim($value);
        }
    }
}
?>
```

## Asset Management

### CSS Organization
```css
/* assets/css/style.css */

/* CSS Reset and Base Styles */
* {
    box-sizing: border-box;
}

html {
    font-family: sans-serif;
    line-height: 1.15;
    -webkit-text-size-adjust: 100%;
}

body {
    margin: 0;
    font-family: var(--font-family);
    font-size: 1rem;
    font-weight: 400;
    line-height: 1.5;
    color: #212529;
    background-color: #fff;
}

/* Utility Classes */
.d-none { display: none !important; }
.d-block { display: block !important; }
.d-flex { display: flex !important; }

.text-center { text-align: center !important; }
.text-right { text-align: right !important; }

.mt-1 { margin-top: 0.25rem !important; }
.mt-2 { margin-top: 0.5rem !important; }
.mt-3 { margin-top: 1rem !important; }

.p-1 { padding: 0.25rem !important; }
.p-2 { padding: 0.5rem !important; }
.p-3 { padding: 1rem !important; }

/* Component Styles */
.btn {
    display: inline-block;
    font-weight: 400;
    color: #212529;
    text-align: center;
    vertical-align: middle;
    cursor: pointer;
    background-color: transparent;
    border: 1px solid transparent;
    padding: 0.375rem 0.75rem;
    font-size: 1rem;
    line-height: 1.5;
    border-radius: 0.375rem;
    text-decoration: none;
    transition: all 0.15s ease-in-out;
}

.btn-primary {
    color: #fff;
    background-color: #007bff;
    border-color: #007bff;
}

.btn-primary:hover {
    color: #fff;
    background-color: #0056b3;
    border-color: #0056b3;
}

.form-control {
    display: block;
    width: 100%;
    height: calc(1.5em + 0.75rem + 2px);
    padding: 0.375rem 0.75rem;
    font-size: 1rem;
    font-weight: 400;
    line-height: 1.5;
    color: #495057;
    background-color: #fff;
    background-clip: padding-box;
    border: 1px solid #ced4da;
    border-radius: 0.375rem;
    transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
}

.form-control:focus {
    color: #495057;
    background-color: #fff;
    border-color: #80bdff;
    outline: 0;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}

/* Page-specific styles */
.attendance-page {
    background-color: #f8f9fa;
}

.dashboard-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 1.5rem;
    margin-top: 2rem;
}

.card {
    position: relative;
    display: flex;
    flex-direction: column;
    min-width: 0;
    word-wrap: break-word;
    background-color: #fff;
    background-clip: border-box;
    border: 1px solid rgba(0, 0, 0, 0.125);
    border-radius: 0.375rem;
}

.card-header {
    padding: 0.75rem 1.25rem;
    margin-bottom: 0;
    background-color: rgba(0, 0, 0, 0.03);
    border-bottom: 1px solid rgba(0, 0, 0, 0.125);
}

.card-body {
    flex: 1 1 auto;
    padding: 1.25rem;
}
```

### JavaScript Module Pattern
```javascript
// assets/js/modules/Attendance.js

class AttendanceManager {
    constructor(options = {}) {
        this.options = {
            apiEndpoint: '/api/v1/attendance/',
            ...options
        };
        
        this.init();
    }
    
    init() {
        this.bindEvents();
        this.loadTodayRecord();
    }
    
    bindEvents() {
        // Check-in button
        document.getElementById('checkin-btn')?.addEventListener('click', () => {
            this.checkIn();
        });
        
        // Check-out button
        document.getElementById('checkout-btn')?.addEventListener('click', () => {
            this.checkOut();
        });
        
        // Auto-refresh every 30 seconds
        setInterval(() => {
            this.loadTodayRecord();
        }, 30000);
    }
    
    async checkIn() {
        try {
            const response = await fetch(`${this.options.apiEndpoint}checkin`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${this.getAuthToken()}`
                },
                body: JSON.stringify({
                    employee_id: this.options.employeeId,
                    timestamp: new Date().toISOString()
                })
            });
            
            const data = await response.json();
            
            if (data.success) {
                this.showSuccess('Successfully checked in!');
                this.loadTodayRecord();
            } else {
                this.showError(data.message || 'Check-in failed');
            }
        } catch (error) {
            this.showError('Network error occurred');
            console.error('Check-in error:', error);
        }
    }
    
    async checkOut() {
        try {
            const response = await fetch(`${this.options.apiEndpoint}checkout`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${this.getAuthToken()}`
                },
                body: JSON.stringify({
                    employee_id: this.options.employeeId,
                    timestamp: new Date().toISOString()
                })
            });
            
            const data = await response.json();
            
            if (data.success) {
                this.showSuccess('Successfully checked out!');
                this.loadTodayRecord();
            } else {
                this.showError(data.message || 'Check-out failed');
            }
        } catch (error) {
            this.showError('Network error occurred');
            console.error('Check-out error:', error);
        }
    }
    
    async loadTodayRecord() {
        try {
            const response = await fetch(`${this.options.apiEndpoint}today?employee_id=${this.options.employeeId}`, {
                headers: {
                    'Authorization': `Bearer ${this.getAuthToken()}`
                }
            });
            
            const data = await response.json();
            
            if (data.success) {
                this.updateUI(data.data);
            }
        } catch (error) {
            console.error('Load record error:', error);
        }
    }
    
    updateUI(record) {
        const checkinBtn = document.getElementById('checkin-btn');
        const checkoutBtn = document.getElementById('checkout-btn');
        const statusElement = document.getElementById('attendance-status');
        
        if (record.check_in_time && !record.check_out_time) {
            // Currently checked in
            checkinBtn.disabled = true;
            checkoutBtn.disabled = false;
            statusElement.textContent = 'Checked in';
            statusElement.className = 'status present';
        } else if (record.check_in_time && record.check_out_time) {
            // Completed day
            checkinBtn.disabled = false;
            checkoutBtn.disabled = true;
            statusElement.textContent = 'Completed';
            statusElement.className = 'status completed';
        } else {
            // Not checked in yet
            checkinBtn.disabled = false;
            checkoutBtn.disabled = true;
            statusElement.textContent = 'Not checked in';
            statusElement.className = 'status absent';
        }
    }
    
    showSuccess(message) {
        this.showNotification(message, 'success');
    }
    
    showError(message) {
        this.showNotification(message, 'error');
    }
    
    showNotification(message, type) {
        const notification = document.createElement('div');
        notification.className = `notification ${type}`;
        notification.textContent = message;
        
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.remove();
        }, 3000);
    }
    
    getAuthToken() {
        return localStorage.getItem('auth_token');
    }
}

// Export for use in other modules
if (typeof module !== 'undefined' && module.exports) {
    module.exports = AttendanceManager;
}
```

## Template System

### Template Components
```php
<?php
// pages/templates/card-template.php

/**
 * Card Template Component
 * 
 * Reusable card component for displaying content blocks
 * 
 * @param array $data Template data
 */
function renderCard($data) {
    $defaults = [
        'title' => '',
        'content' => '',
        'footer' => '',
        'class' => '',
        'header_icon' => '',
        'actions' => []
    ];
    
    $data = array_merge($defaults, $data);
    
    ob_start();
    ?>
    <div class="card <?php echo htmlspecialchars($data['class']); ?>">
        <?php if ($data['title'] || $data['header_icon'] || !empty($data['actions'])): ?>
        <div class="card-header d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center">
                <?php if ($data['header_icon']): ?>
                    <i class="<?php echo htmlspecialchars($data['header_icon']); ?> mr-2"></i>
                <?php endif; ?>
                <h5 class="mb-0"><?php echo htmlspecialchars($data['title']); ?></h5>
            </div>
            <?php if (!empty($data['actions'])): ?>
                <div class="card-actions">
                    <?php foreach ($data['actions'] as $action): ?>
                        <a href="<?php echo htmlspecialchars($action['url']); ?>" 
                           class="btn btn-sm <?php echo htmlspecialchars($action['class'] ?? 'btn-outline-primary'); ?>">
                            <?php echo htmlspecialchars($action['label']); ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>
        
        <div class="card-body">
            <?php echo $data['content']; ?>
        </div>
        
        <?php if ($data['footer']): ?>
        <div class="card-footer">
            <?php echo $data['footer']; ?>
        </div>
        <?php endif; ?>
    </div>
    <?php
    return ob_get_clean();
}

// Usage example
$widgetData = [
    'title' => 'Quick Stats',
    'class' => 'stats-widget',
    'header_icon' => 'fas fa-chart-bar',
    'actions' => [
        ['label' => 'View Details', 'url' => '/reports/stats.php', 'class' => 'btn-primary']
    ],
    'content' => '<div class="stats-grid"><div class="stat-item"><strong>150</strong><span>Students</span></div></div>'
];

echo renderCard($widgetData);
?>
```

## Code Style Guidelines

### PSR-12 Compliance
The codebase follows PSR-12 PHP coding standards.

#### Naming Conventions
- **Classes**: PascalCase (e.g., `DatabaseManager`)
- **Methods**: camelCase (e.g., `getUserData()`)
- **Properties**: camelCase (e.g., `$userName`)
- **Constants**: UPPER_CASE (e.g., `MAX_RECORDS`)
- **Variables**: camelCase (e.g., `$studentCount`)

#### Code Formatting
```php
<?php
/**
 * This file contains the User management class
 * 
 * @package ClassroomClone
 * @version 1.0
 */

declare(strict_types=1);

namespace ClassroomClone\User;

use ClassroomClone\Database\Database;
use ClassroomClone\Security\Authenticator;
use PDOException;
use Exception;

/**
 * User Management Class
 * 
 * Handles all user-related operations including authentication,
 * profile management, and role-based access control.
 * 
 * @package ClassroomClone
 * @version 1.0
 */
class UserManager 
{
    /**
     * Database connection
     * 
     * @var Database
     */
    private $database;
    
    /**
     * Authenticator instance
     * 
     * @var Authenticator
     */
    private $authenticator;
    
    /**
     * User table name
     * 
     * @var string
     */
    private const USER_TABLE = 'users';
    
    /**
     * Constructor
     * 
     * @param Database $database Database connection
     * @param Authenticator $authenticator Authentication handler
     */
    public function __construct(Database $database, Authenticator $authenticator)
    {
        $this->database = $database;
        $this->authenticator = $authenticator;
    }
    
    /**
     * Create a new user
     * 
     * @param array $userData User information
     * @param int|null $createdBy User ID who created this user
     * 
     * @return array{success: bool, user_id?: int, errors?: string[]}
     * 
     * @throws Exception If user creation fails
     */
    public function createUser(array $userData, ?int $createdBy = null): array
    {
        // Validation
        $validation = $this->validateUserData($userData);
        if (!$validation['valid']) {
            return [
                'success' => false,
                'errors' => $validation['errors']
            ];
        }
        
        // Check for duplicates
        if ($this->emailExists($userData['email'])) {
            return [
                'success' => false,
                'errors' => ['Email already exists']
            ];
        }
        
        try {
            // Prepare user data
            $userData = $this->prepareUserData($userData);
            $userData['created_at'] = date('Y-m-d H:i:s');
            $userData['created_by'] = $createdBy;
            
            // Create user
            $userId = $this->database->insert(self::USER_TABLE, $userData);
            
            // Log activity
            $this->logUserActivity($userId, 'user_created', "User created by user ID: {$createdBy}");
            
            return [
                'success' => true,
                'user_id' => $userId
            ];
            
        } catch (PDOException $e) {
            error_log("User creation failed: " . $e->getMessage());
            
            return [
                'success' => false,
                'errors' => ['Failed to create user']
            ];
        }
    }
    
    /**
     * Validate user data
     * 
     * @param array $data User data to validate
     * 
     * @return array{valid: bool, errors: string[]}
     */
    private function validateUserData(array $data): array
    {
        $errors = [];
        
        // Required fields
        $requiredFields = ['first_name', 'last_name', 'email', 'password'];
        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                $errors[] = ucfirst(str_replace('_', ' ', $field)) . ' is required';
            }
        }
        
        // Email validation
        if (!empty($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Invalid email format';
        }
        
        // Password validation
        if (!empty($data['password']) && strlen($data['password']) < 8) {
            $errors[] = 'Password must be at least 8 characters';
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }
    
    /**
     * Prepare user data for database insertion
     * 
     * @param array $data Raw user data
     * 
     * @return array Prepared user data
     */
    private function prepareUserData(array $data): array
    {
        // Sanitize inputs
        $prepared = [];
        foreach ($data as $key => $value) {
            if (is_string($value)) {
                $prepared[$key] = htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
            } else {
                $prepared[$key] = $value;
            }
        }
        
        // Hash password
        if (isset($prepared['password'])) {
            $prepared['password_hash'] = password_hash($prepared['password'], PASSWORD_DEFAULT);
            unset($prepared['password']);
        }
        
        return $prepared;
    }
}
?>
```

This comprehensive code structure documentation provides a complete overview of the Google Classroom Clone codebase organization, architecture patterns, and development guidelines. The structure promotes maintainability, scalability, and adherence to modern PHP development practices.
