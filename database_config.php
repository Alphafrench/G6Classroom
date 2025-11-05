<?php
/**
 * Database Configuration for Employee Attendance Management System
 * Created: 2025-11-05
 * 
 * This file contains database connection configuration and utilities
 * for the Employee Attendance Management System
 */

// =====================================================
// Database Configuration Constants
// =====================================================

// Database connection parameters
define('DB_HOST', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'employee_attendance_system');
define('DB_CHARSET', 'utf8mb4');

// Database connection options
define('DB_OPTIONS', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
]);

// Connection timeout in seconds
define('DB_TIMEOUT', 30);

// =====================================================
// Database Connection Class
// =====================================================

class Database {
    private static $instance = null;
    private $connection = null;
    
    /**
     * Private constructor to prevent direct instantiation
     */
    private function __construct() {
        $this->connect();
    }
    
    /**
     * Get singleton instance of Database class
     * 
     * @return Database
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Establish database connection
     * 
     * @throws PDOException If connection fails
     */
    private function connect() {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            
            $this->connection = new PDO(
                $dsn,
                DB_USERNAME,
                DB_PASSWORD,
                DB_OPTIONS
            );
            
            // Set connection timeout
            $this->connection->exec("SET SESSION wait_timeout = " . DB_TIMEOUT);
            
            // Log successful connection (in development environment)
            if (defined('DEBUG_MODE') && DEBUG_MODE === true) {
                error_log("Database connection established successfully");
            }
            
        } catch (PDOException $e) {
            // Log error
            error_log("Database connection failed: " . $e->getMessage());
            
            // In production, don't expose detailed error messages
            if (defined('PRODUCTION') && PRODUCTION === true) {
                throw new Exception("Database connection failed");
            } else {
                throw new Exception("Database connection failed: " . $e->getMessage());
            }
        }
    }
    
    /**
     * Get database connection
     * 
     * @return PDO
     */
    public function getConnection() {
        // Check if connection is still alive
        try {
            $this->connection->query('SELECT 1');
        } catch (PDOException $e) {
            // Reconnect if connection is lost
            $this->connect();
        }
        
        return $this->connection;
    }
    
    /**
     * Execute a prepared statement
     * 
     * @param string $sql SQL query
     * @param array $params Parameters to bind
     * @return PDOStatement
     */
    public function query($sql, $params = []) {
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            error_log("Query failed: " . $e->getMessage() . " | SQL: " . $sql);
            throw new Exception("Database query failed");
        }
    }
    
    /**
     * Fetch single row
     * 
     * @param string $sql SQL query
     * @param array $params Parameters to bind
     * @return array|false
     */
    public function fetchRow($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt->fetch();
    }
    
    /**
     * Fetch all rows
     * 
     * @param string $sql SQL query
     * @param array $params Parameters to bind
     * @return array
     */
    public function fetchAll($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt->fetchAll();
    }
    
    /**
     * Insert a record and return the last insert ID
     * 
     * @param string $table Table name
     * @param array $data Data to insert
     * @return string Last insert ID
     */
    public function insert($table, $data) {
        $fields = array_keys($data);
        $placeholders = ':' . implode(', :', $fields);
        $fields = implode(', ', $fields);
        
        $sql = "INSERT INTO {$table} ({$fields}) VALUES ({$placeholders})";
        
        $this->query($sql, $data);
        return $this->connection->lastInsertId();
    }
    
    /**
     * Update records in a table
     * 
     * @param string $table Table name
     * @param array $data Data to update
     * @param string $where WHERE clause
     * @param array $whereParams WHERE parameters
     * @return int Number of affected rows
     */
    public function update($table, $data, $where, $whereParams = []) {
        $set = [];
        foreach (array_keys($data) as $field) {
            $set[] = "{$field} = :{$field}";
        }
        $set = implode(', ', $set);
        
        $sql = "UPDATE {$table} SET {$set} WHERE {$where}";
        $params = array_merge($data, $whereParams);
        
        $stmt = $this->query($sql, $params);
        return $stmt->rowCount();
    }
    
    /**
     * Delete records from a table
     * 
     * @param string $table Table name
     * @param string $where WHERE clause
     * @param array $whereParams WHERE parameters
     * @return int Number of affected rows
     */
    public function delete($table, $where, $whereParams = []) {
        $sql = "DELETE FROM {$table} WHERE {$where}";
        $stmt = $this->query($sql, $whereParams);
        return $stmt->rowCount();
    }
    
    /**
     * Begin a transaction
     * 
     * @return bool
     */
    public function beginTransaction() {
        return $this->connection->beginTransaction();
    }
    
    /**
     * Commit a transaction
     * 
     * @return bool
     */
    public function commit() {
        return $this->connection->commit();
    }
    
    /**
     * Rollback a transaction
     * 
     * @return bool
     */
    public function rollback() {
        return $this->connection->rollback();
    }
    
    /**
     * Check if in transaction
     * 
     * @return bool
     */
    public function inTransaction() {
        return $this->connection->inTransaction();
    }
    
    /**
     * Get the last error message
     * 
     * @return string
     */
    public function getLastError() {
        return $this->connection->errorInfo()[2];
    }
    
    /**
     * Close database connection
     */
    public function close() {
        $this->connection = null;
    }
    
    /**
     * Prevent cloning of singleton instance
     */
    private function __clone() {}
    
    /**
     * Prevent unserialization of singleton instance
     */
    public function __wakeup() {
        throw new Exception("Cannot unserialize singleton");
    }
}

// =====================================================
// Database Utility Functions
// =====================================================

/**
 * Get database instance
 * 
 * @return Database
 */
function getDB() {
    return Database::getInstance();
}

/**
 * Escape string for safe SQL usage
 * 
 * @param string $value Value to escape
 * @return string Escaped value
 */
function escapeString($value) {
    $db = Database::getInstance();
    return $db->getConnection()->quote($value);
}

/**
 * Check if database connection is working
 * 
 * @return bool
 */
function checkDatabaseConnection() {
    try {
        $db = Database::getInstance();
        $db->query("SELECT 1");
        return true;
    } catch (Exception $e) {
        return false;
    }
}

/**
 * Get database version information
 * 
 * @return array
 */
function getDatabaseVersion() {
    $db = Database::getInstance();
    $result = $db->fetchRow("SELECT VERSION() as version, DATABASE() as database_name");
    return $result;
}

/**
 * Execute database backup (requires appropriate privileges)
 * 
 * @param string $backupPath Path to save backup file
 * @return bool
 */
function createDatabaseBackup($backupPath) {
    $command = sprintf(
        "mysqldump -h%s -u%s -p%s %s > %s",
        DB_HOST,
        DB_USERNAME,
        DB_PASSWORD,
        DB_NAME,
        escapeshellarg($backupPath)
    );
    
    exec($command, $output, $returnCode);
    return $returnCode === 0;
}

// =====================================================
// Employee Attendance Helper Functions
// =====================================================

/**
 * Calculate employee worked hours between two dates
 * 
 * @param int $employeeId Employee ID
 * @param string $startDate Start date (Y-m-d)
 * @param string $endDate End date (Y-m-d)
 * @return float Total worked hours
 */
function calculateEmployeeHours($employeeId, $startDate, $endDate) {
    $db = Database::getInstance();
    $result = $db->fetchRow(
        "SELECT SUM(total_hours) as total_hours 
         FROM attendance 
         WHERE employee_id = ? AND attendance_date BETWEEN ? AND ?",
        [$employeeId, $startDate, $endDate]
    );
    return (float) ($result['total_hours'] ?? 0);
}

/**
 * Get employee attendance summary for a month
 * 
 * @param int $employeeId Employee ID
 * @param int $year Year
 * @param int $month Month
 * @return array
 */
function getMonthlyAttendanceSummary($employeeId, $year, $month) {
    $db = Database::getInstance();
    $startDate = sprintf('%04d-%02d-01', $year, $month);
    $endDate = date('Y-m-t', strtotime($startDate));
    
    $result = $db->fetchRow(
        "SELECT 
            COUNT(*) as total_days,
            SUM(CASE WHEN attendance_status = 'present' THEN 1 ELSE 0 END) as present_days,
            SUM(CASE WHEN attendance_status = 'absent' THEN 1 ELSE 0 END) as absent_days,
            SUM(CASE WHEN attendance_status = 'late' THEN 1 ELSE 0 END) as late_days,
            SUM(CASE WHEN attendance_status IN ('leave', 'holiday', 'weekend') THEN 1 ELSE 0 END) as non_working_days,
            SUM(total_hours) as total_hours,
            SUM(overtime_hours) as total_overtime,
            AVG(total_hours) as avg_daily_hours
         FROM attendance 
         WHERE employee_id = ? AND attendance_date BETWEEN ? AND ?",
        [$employeeId, $startDate, $endDate]
    );
    
    return $result ?: [];
}

/**
 * Check if employee is currently checked in
 * 
 * @param int $employeeId Employee ID
 * @return array|false Employee info if checked in, false otherwise
 */
function isEmployeeCheckedIn($employeeId) {
    $db = Database::getInstance();
    $result = $db->fetchRow(
        "SELECT e.*, a.clock_in_time, a.attendance_date
         FROM employees e
         JOIN attendance a ON e.id = a.employee_id
         WHERE e.id = ? AND a.attendance_date = CURDATE() 
         AND a.clock_in_time IS NOT NULL AND a.clock_out_time IS NULL",
        [$employeeId]
    );
    
    return $result ?: false;
}

/**
 * Get department-wise attendance summary for today
 * 
 * @return array
 */
function getTodayAttendanceByDepartment() {
    $db = Database::getInstance();
    $results = $db->fetchAll(
        "SELECT 
            d.name as department,
            COUNT(a.id) as total_employees,
            SUM(CASE WHEN a.clock_in_time IS NOT NULL THEN 1 ELSE 0 END) as checked_in,
            SUM(CASE WHEN a.attendance_status = 'present' THEN 1 ELSE 0 END) as present,
            SUM(CASE WHEN a.attendance_status = 'absent' THEN 1 ELSE 0 END) as absent,
            SUM(CASE WHEN a.attendance_status = 'late' THEN 1 ELSE 0 END) as late
         FROM departments d
         LEFT JOIN employees e ON d.id = e.department_id
         LEFT JOIN attendance a ON e.id = a.employee_id AND a.attendance_date = CURDATE()
         GROUP BY d.id, d.name
         ORDER BY d.name"
    );
    
    return $results;
}

// =====================================================
// Environment Configuration
// =====================================================

// Set environment based on server configuration
if (!defined('ENVIRONMENT')) {
    define('ENVIRONMENT', $_SERVER['SERVER_NAME'] === 'localhost' ? 'development' : 'production');
}

// Set debug and production flags based on environment
define('DEBUG_MODE', ENVIRONMENT === 'development');
define('PRODUCTION', ENVIRONMENT === 'production');

// Error reporting settings
if (DEBUG_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
}

// =====================================================
// Security Functions
// =====================================================

/**
 * Generate secure random token
 * 
 * @param int $length Token length
 * @return string
 */
function generateSecureToken($length = 32) {
    return bin2hex(random_bytes($length));
}

/**
 * Hash password using bcrypt
 * 
 * @param string $password Plain text password
 * @return string Hashed password
 */
function hashPassword($password) {
    return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
}

/**
 * Verify password against hash
 * 
 * @param string $password Plain text password
 * @param string $hash Hashed password
 * @return bool
 */
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

// =====================================================
// Database Connection Test
// =====================================================

// Test database connection when this file is included
try {
    if (checkDatabaseConnection()) {
        if (DEBUG_MODE) {
            $version = getDatabaseVersion();
            echo "<!-- Database connected successfully to: {$version['database_name']} (MySQL {$version['version']}) -->\n";
        }
    } else {
        throw new Exception("Database connection test failed");
    }
} catch (Exception $e) {
    if (DEBUG_MODE) {
        echo "<!-- Database connection error: " . $e->getMessage() . " -->\n";
    }
}

?>