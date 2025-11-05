# Core PHP Functions and Classes Implementation

## Overview
This implementation provides a comprehensive set of modular PHP functions and classes for an employee management system with robust security, validation, and error handling.

## Files Created

### 1. includes/functions.php (503 lines)
**Core Utility Functions**

#### Features:
- **Input Sanitization**: `sanitizeInput()`, `sanitizeHTML()` - Prevent XSS attacks
- **Validation Functions**: 
  - `validateEmail()` - Email format validation
  - `validatePhone()` - Phone number validation
  - `validatePassword()` - Password strength checking
  - `validateDate()` - Date format validation
  - `validateRequiredFields()` - Required field checking
- **Formatting Functions**:
  - `formatPhoneNumber()`, `formatCurrency()`, `formatDate()`, `formatDateTime()`
  - `calculateAge()` - Age calculation from birth date
- **Security Functions**:
  - `generatePassword()`, `generateToken()`, `hashPassword()`, `verifyPassword()`
  - `generateCSRFToken()`, `verifyCSRFToken()` - CSRF protection
  - `getClientIP()` - IP address detection
- **Utility Functions**:
  - `logError()` - Error logging with context
  - `arrayToCSV()` - CSV export functionality
  - `safeRedirect()` - Safe URL redirection
  - `jsonResponse()` - JSON API responses

### 2. includes/class.Database.php (442 lines)
**Database Connection and Query Management**

#### Features:
- **Secure Connection**: PDO with prepared statements
- **Query Methods**: `query()`, `fetchRow()`, `fetchAll()`
- **CRUD Operations**: `insert()`, `update()`, `delete()`, `count()`, `exists()`
- **Transaction Support**: `beginTransaction()`, `commit()`, `rollback()`, `transaction()`
- **Security**: SQL injection prevention, input escaping
- **Error Handling**: Comprehensive logging and exception handling
- **Connection Management**: Singleton pattern, connection testing
- **Database Utilities**: Version info, statistics, health checks

### 3. includes/class.Employee.php (626 lines)
**Employee Management with CRUD Operations**

#### Features:
- **Create Employee**: Validation, duplicate checking, sanitization
- **Read Operations**: 
  - `getById()` - Single employee by ID
  - `getByEmail()` - Employee lookup by email
  - `getAll()` - Paginated employee listing with filters
  - `search()` - Full-text employee search
- **Update Employee**: Validation, conflict resolution, audit trail
- **Delete Operations**: 
  - `delete()` - Soft delete by default, optional hard delete
  - `restore()` - Restore soft-deleted employees
- **Reporting**: 
  - `getStatistics()` - Employee statistics and metrics
  - Department breakdowns, hiring trends, salary analytics
- **Security**: Input validation, SQL injection prevention
- **Data Integrity**: Email uniqueness checks, referential validation

### 4. includes/class.Attendance.php (738 lines)
**Attendance Tracking and Time Management**

#### Features:
- **Clock In/Out**: 
  - `clockIn()` - Employee check-in with location tracking
  - `clockOut()` - Employee check-out with hours calculation
  - `getCurrentStatus()` - Real-time attendance status
- **Time Tracking**:
  - Automatic hours calculation with validation
  - Suspicious time detection (overtime limits)
  - Break time validation (minimum 30 minutes)
- **Reporting & Analytics**:
  - `getEmployeeAttendance()` - Individual attendance history
  - `getEmployeeSummary()` - Attendance summary with metrics
  - `getAllAttendance()` - System-wide attendance with filtering
  - `generateReport()` - Summary, detailed, and CSV reports
- **Manual Adjustments**: `editAttendance()` - Admin-controlled time corrections
- **Audit Trail**: Complete action logging with IP/user agent tracking
- **Validation**: Time format validation, hours limit enforcement

### 5. includes/class.Session.php (712 lines)
**Secure Session Management**

#### Features:
- **Authentication**:
  - `login()` - Secure user authentication with password verification
  - `logout()` - Clean session termination
  - `isLoggedIn()` - Session status checking
- **Security Measures**:
  - Session ID regeneration on login
  - IP address validation (optional)
  - User agent validation (optional)
  - Concurrent session limits
  - Remember me functionality with secure tokens
- **Session Management**:
  - Automatic expiration handling
  - Database-backed session storage
  - Session termination by user
  - Concurrent session monitoring
- **Permission System**: Role-based access control
- **Security Logging**: Failed login attempts, session hijacking detection
- **Cleanup**: Automatic expired session removal

## Security Features Implemented

### 1. SQL Injection Prevention
- **Prepared Statements**: All database queries use PDO prepared statements
- **Input Sanitization**: All user inputs sanitized before database operations
- **Parameter Binding**: Proper parameter binding in all queries

### 2. Cross-Site Scripting (XSS) Prevention
- **HTML Entity Encoding**: All output properly escaped
- **Input Filtering**: Malicious script patterns filtered
- **Content Security**: Strict content validation

### 3. Session Security
- **Secure Session IDs**: Cryptographically secure session generation
- **Session Fixation Prevention**: ID regeneration on login
- **IP/UA Validation**: Optional binding to client characteristics
- **Remember Me Security**: Secure token-based persistent sessions

### 4. Authentication Security
- **Password Hashing**: PHP's `password_hash()` with bcrypt
- **Password Policies**: Configurable strength requirements
- **Brute Force Protection**: Login attempt logging
- **Account Lockout**: Framework for account disabling

### 5. CSRF Protection
- **Token Generation**: Secure CSRF tokens per session
- **Token Validation**: All state-changing operations validated
- **Token Rotation**: Fresh tokens for sensitive operations

## Error Handling

### 1. Comprehensive Logging
- **Error Context**: Detailed error information with context
- **Log Levels**: Error, warning, info, debug levels
- **Security Events**: Failed logins, suspicious activities logged
- **Log Rotation**: Automatic old log cleanup

### 2. Graceful Degradation
- **Exception Handling**: All operations wrapped in try-catch blocks
- **User-Friendly Messages**: Technical errors translated to user messages
- **Fallback Mechanisms**: Alternative paths when primary operations fail

### 3. Database Error Handling
- **Connection Failures**: Automatic retry mechanisms
- **Query Errors**: Detailed error logging with query context
- **Transaction Rollback**: Automatic rollback on failures

## Data Validation

### 1. Input Validation
- **Required Fields**: Comprehensive required field checking
- **Format Validation**: Email, phone, date format validation
- **Length Limits**: Configurable maximum field lengths
- **Data Type Validation**: Numeric, string, date type checking

### 2. Business Logic Validation
- **Employee Constraints**: Email uniqueness, department validity
- **Attendance Rules**: Working hours limits, break time validation
- **Session Limits**: Concurrent session restrictions
- **Temporal Validation**: Date range checking, time logic validation

## Usage Examples

### Basic Database Usage
```php
require_once 'includes/class.Database.php';
require_once 'includes/functions.php';

$db = new Database([
    'host' => 'localhost',
    'database' => 'employee_manager',
    'username' => 'root',
    'password' => 'password'
]);

$employee = $db->fetchRow("SELECT * FROM employees WHERE id = ?", [123]);
```

### Employee Management
```php
require_once 'includes/class.Employee.php';

$employee = new Employee($db);

// Create new employee
$result = $employee->create([
    'first_name' => 'John',
    'last_name' => 'Doe',
    'email' => 'john.doe@company.com',
    'hire_date' => '2025-01-01',
    'department_id' => 1,
    'position' => 'Developer'
]);

// Get employee with pagination
$employees = $employee->getAll([
    'department_id' => 1,
    'search_term' => 'john'
], 20, 0, 'last_name', 'ASC');
```

### Attendance Tracking
```php
require_once 'includes/class.Attendance.php';

$attendance = new Attendance($db);

// Clock in employee
$result = $attendance->clockIn(123, 'Main Office', 'Starting work');

// Clock out employee
$result = $attendance->clockOut(123, 'Main Office', 'End of day');

// Get attendance report
$report = $attendance->generateReport([
    'start_date' => '2025-01-01',
    'end_date' => '2025-01-31',
    'department_id' => 1
], 'summary');
```

### Session Management
```php
require_once 'includes/class.Session.php';

$session = new Session($db);

// Login user
$result = $session->login([
    'email' => 'user@company.com',
    'password' => 'password123'
], true); // Remember me

// Check authentication
if ($session->isLoggedIn()) {
    $user = $session->getCurrentUser();
    echo "Welcome, " . $user['first_name'];
}

// Check permissions
if ($session->hasPermission('edit_employees')) {
    // Show edit controls
}
```

## Configuration

All classes support configuration options:

### Database Configuration
```php
$config = [
    'host' => 'localhost',
    'port' => 3306,
    'database' => 'employee_manager',
    'username' => 'root',
    'password' => 'password',
    'charset' => 'utf8mb4',
    'options' => [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]
];
```

### Session Configuration
```php
$config = [
    'session_name' => 'EMPLOYEE_MANAGER_SESSION',
    'lifetime' => 3600, // 1 hour
    'max_concurrent_sessions' => 3,
    'regenerate_on_login' => true,
    'require_ip_match' => false,
    'require_user_agent_match' => false
];
```

## Installation Requirements

### PHP Version
- PHP 7.4 or higher
- PDO MySQL extension
- OpenSSL extension (for secure tokens)

### Database Tables Required
- `employees` - Employee records
- `departments` - Department information  
- `attendance` - Attendance records
- `attendance_logs` - Attendance audit log
- `user_sessions` - Session management
- `users` - User accounts
- `roles` - User roles

### Directory Structure
```
project/
├── includes/
│   ├── functions.php
│   ├── class.Database.php
│   ├── class.Employee.php
│   ├── class.Attendance.php
│   └── class.Session.php
├── logs/
│   └── error.log
└── config/
    └── database.php
```

## Performance Considerations

### Database Optimization
- **Indexed Queries**: All search and filter fields properly indexed
- **Pagination**: Large datasets paginated for performance
- **Connection Pooling**: Efficient database connection management
- **Query Optimization**: Minimal query execution time

### Caching Opportunities
- **Session Cache**: Frequently accessed user data cached
- **Query Results**: Employee data can be cached for reporting
- **Statistics**: Pre-calculated metrics for dashboard

### Memory Management
- **Lazy Loading**: Data loaded only when needed
- **Large Dataset Handling**: Memory-efficient pagination
- **Resource Cleanup**: Automatic connection and resource cleanup

## Testing Recommendations

### Unit Testing
- Test all validation functions
- Test database operations with mock connections
- Test session management with mock data

### Integration Testing  
- Test complete user workflows
- Test database transaction handling
- Test security measures under load

### Security Testing
- SQL injection attempts
- XSS vulnerability testing
- Session hijacking prevention
- CSRF token validation

## Maintenance

### Regular Tasks
- **Log Rotation**: Implement log file rotation
- **Session Cleanup**: Automatic expired session removal
- **Database Optimization**: Query performance monitoring
- **Security Updates**: Regular dependency updates

### Monitoring
- **Error Rates**: Monitor application error rates
- **Performance**: Track query execution times
- **Security Events**: Monitor failed login attempts
- **Resource Usage**: Track memory and CPU usage

## Conclusion

This implementation provides a robust, secure, and scalable foundation for an employee management system with comprehensive features for user management, attendance tracking, and session handling. The modular design allows for easy extension and customization while maintaining high security standards and proper error handling throughout the application.
