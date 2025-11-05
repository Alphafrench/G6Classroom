<?php
/**
 * Google Classroom Clone - Database Configuration
 * Centralized database connection for classroom management system
 * 
 * @package ClassroomManager
 */

class Database {
    private static $instance = null;
    private $host;
    private $dbname;
    private $username;
    private $password;
    private $charset = 'utf8mb4';
    private $pdo = null;
    
    /**
     * Private constructor to prevent direct instantiation
     */
    private function __construct() {
        $this->host = DB_HOST;
        $this->dbname = DB_NAME;
        $this->username = DB_USER;
        $this->password = DB_PASS;
    }
    
    /**
     * Get database connection instance (Singleton pattern)
     * @return Database
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Get PDO connection
     * @return PDO
     * @throws PDOException
     */
    public function getConnection() {
        if ($this->pdo === null) {
            try {
                $dsn = "mysql:host={$this->host};dbname={$this->dbname};charset={$this->charset}";
                $options = [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                    PDO::ATTR_STRINGIFY_FETCHES => false,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES {$this->charset} COLLATE utf8mb4_unicode_ci"
                ];
                
                $this->pdo = new PDO($dsn, $this->username, $this->password, $options);
                
                // Enable foreign key constraints
                $this->pdo->exec('SET FOREIGN_KEY_CHECKS = 1');
                
            } catch (PDOException $e) {
                error_log("Database connection failed: " . $e->getMessage());
                throw new PDOException("Database connection failed. Please check your configuration.");
            }
        }
        
        return $this->pdo;
    }
    
    /**
     * Close database connection
     */
    public function closeConnection() {
        $this->pdo = null;
    }
    
    /**
     * Begin a transaction
     * @return bool
     */
    public function beginTransaction() {
        return $this->getConnection()->beginTransaction();
    }
    
    /**
     * Commit a transaction
     * @return bool
     */
    public function commit() {
        return $this->getConnection()->commit();
    }
    
    /**
     * Rollback a transaction
     * @return bool
     */
    public function rollback() {
        return $this->getConnection()->rollback();
    }
    
    /**
     * Test database connection
     * @return bool
     */
    public function testConnection() {
        try {
            $this->getConnection()->query('SELECT 1');
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }
    
    /**
     * Get database version
     * @return string|false
     */
    public function getVersion() {
        try {
            return $this->getConnection()->query('SELECT VERSION()')->fetchColumn();
        } catch (PDOException $e) {
            return false;
        }
    }
    
    /**
     * Execute raw SQL query
     * @param string $query
     * @return PDOStatement|false
     */
    public function query($query) {
        try {
            return $this->getConnection()->query($query);
        } catch (PDOException $e) {
            error_log("Query failed: " . $e->getMessage());
            return false;
        }
    }
}

/**
 * Employee Class - Handles all employee CRUD operations
 */
class Employee {
    private $conn;
    private $table = 'employees';

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Create new employee
     * @param array $data Employee data
     * @return bool
     */
    public function create($data) {
        try {
            $query = "INSERT INTO " . $this->table . " 
                     (first_name, last_name, email, phone, position, department, 
                      hire_date, salary, address, emergency_contact, emergency_phone, notes, created_at) 
                     VALUES 
                     (:first_name, :last_name, :email, :phone, :position, :department, 
                      :hire_date, :salary, :address, :emergency_contact, :emergency_phone, :notes, NOW())";
            
            $stmt = $this->conn->prepare($query);
            
            // Bind parameters
            $stmt->bindParam(':first_name', $data['first_name']);
            $stmt->bindParam(':last_name', $data['last_name']);
            $stmt->bindParam(':email', $data['email']);
            $stmt->bindParam(':phone', $data['phone']);
            $stmt->bindParam(':position', $data['position']);
            $stmt->bindParam(':department', $data['department']);
            $stmt->bindParam(':hire_date', $data['hire_date']);
            $stmt->bindParam(':salary', $data['salary']);
            $stmt->bindParam(':address', $data['address']);
            $stmt->bindParam(':emergency_contact', $data['emergency_contact']);
            $stmt->bindParam(':emergency_phone', $data['emergency_phone']);
            $stmt->bindParam(':notes', $data['notes']);
            
            return $stmt->execute();
        } catch(PDOException $e) {
            error_log("Employee create error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get single employee by ID
     * @param int $id Employee ID
     * @return array|false
     */
    public function readOne($id) {
        try {
            $query = "SELECT * FROM " . $this->table . " WHERE id = ? LIMIT 1";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(1, $id, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            error_log("Employee read error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get all employees with pagination and search
     * @param array $params Search and pagination parameters
     * @return array
     */
    public function readAll($params = []) {
        try {
            $page = isset($params['page']) ? (int)$params['page'] : 1;
            $limit = isset($params['limit']) ? (int)$params['limit'] : RECORDS_PER_PAGE;
            $search = isset($params['search']) ? $params['search'] : '';
            $department = isset($params['department']) ? $params['department'] : '';
            
            $offset = ($page - 1) * $limit;
            
            $whereClause = " WHERE 1=1";
            $queryParams = [];
            
            // Add search conditions
            if (!empty($search)) {
                $whereClause .= " AND (first_name LIKE :search OR last_name LIKE :search OR email LIKE :search OR position LIKE :search OR department LIKE :search)";
                $queryParams[':search'] = "%{$search}%";
            }
            
            if (!empty($department)) {
                $whereClause .= " AND department = :department";
                $queryParams[':department'] = $department;
            }
            
            // Main query with pagination
            $query = "SELECT * FROM " . $this->table . $whereClause . " ORDER BY created_at DESC LIMIT :limit OFFSET :offset";
            
            // Count query for pagination
            $countQuery = "SELECT COUNT(*) FROM " . $this->table . $whereClause;
            
            $stmt = $this->conn->prepare($query);
            $countStmt = $this->conn->prepare($countQuery);
            
            // Bind all parameters
            foreach ($queryParams as $key => $value) {
                $stmt->bindValue($key, $value);
                $countStmt->bindValue($key, $value);
            }
            
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
            
            $stmt->execute();
            $employees = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $countStmt->execute();
            $totalRecords = $countStmt->fetchColumn();
            
            return [
                'data' => $employees,
                'total' => $totalRecords,
                'pages' => ceil($totalRecords / $limit),
                'current_page' => $page
            ];
        } catch(PDOException $e) {
            error_log("Employee readAll error: " . $e->getMessage());
            return ['data' => [], 'total' => 0, 'pages' => 0, 'current_page' => 1];
        }
    }

    /**
     * Update employee
     * @param int $id Employee ID
     * @param array $data Employee data
     * @return bool
     */
    public function update($id, $data) {
        try {
            $query = "UPDATE " . $this->table . " 
                     SET first_name = :first_name, last_name = :last_name, email = :email, 
                         phone = :phone, position = :position, department = :department, 
                         hire_date = :hire_date, salary = :salary, address = :address, 
                         emergency_contact = :emergency_contact, emergency_phone = :emergency_phone, 
                         notes = :notes, updated_at = NOW() 
                     WHERE id = :id";
            
            $stmt = $this->conn->prepare($query);
            
            // Bind parameters
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->bindParam(':first_name', $data['first_name']);
            $stmt->bindParam(':last_name', $data['last_name']);
            $stmt->bindParam(':email', $data['email']);
            $stmt->bindParam(':phone', $data['phone']);
            $stmt->bindParam(':position', $data['position']);
            $stmt->bindParam(':department', $data['department']);
            $stmt->bindParam(':hire_date', $data['hire_date']);
            $stmt->bindParam(':salary', $data['salary']);
            $stmt->bindParam(':address', $data['address']);
            $stmt->bindParam(':emergency_contact', $data['emergency_contact']);
            $stmt->bindParam(':emergency_phone', $data['emergency_phone']);
            $stmt->bindParam(':notes', $data['notes']);
            
            return $stmt->execute();
        } catch(PDOException $e) {
            error_log("Employee update error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete employee
     * @param int $id Employee ID
     * @return bool
     */
    public function delete($id) {
        try {
            $query = "DELETE FROM " . $this->table . " WHERE id = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(1, $id, PDO::PARAM_INT);
            
            return $stmt->execute();
        } catch(PDOException $e) {
            error_log("Employee delete error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get unique departments
     * @return array
     */
    public function getDepartments() {
        try {
            $query = "SELECT DISTINCT department FROM " . $this->table . " WHERE department IS NOT NULL AND department != '' ORDER BY department";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_COLUMN);
        } catch(PDOException $e) {
            error_log("Get departments error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Check if email already exists
     * @param string $email Email to check
     * @param int $excludeId Employee ID to exclude from check
     * @return bool
     */
    public function emailExists($email, $excludeId = null) {
        try {
            $query = "SELECT COUNT(*) FROM " . $this->table . " WHERE email = :email";
            if ($excludeId) {
                $query .= " AND id != :exclude_id";
            }
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':email', $email);
            if ($excludeId) {
                $stmt->bindParam(':exclude_id', $excludeId, PDO::PARAM_INT);
            }
            
            $stmt->execute();
            return $stmt->fetchColumn() > 0;
        } catch(PDOException $e) {
            error_log("Email exists check error: " . $e->getMessage());
            return false;
        }
    }
}