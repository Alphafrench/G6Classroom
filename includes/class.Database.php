<?php
/**
 * Database Connection and Query Management Class
 * 
 * This class handles database connections, query execution, and provides
 * secure database operations with prepared statements to prevent SQL injection.
 * 
 * @package EmployeeManager
 * @version 1.0
 * @author Development Team
 */

class Database {
    /**
     * Database connection instance
     * @var PDO
     */
    private $connection;
    
    /**
     * Database configuration
     * @var array
     */
    private $config;
    
    /**
     * Transaction state
     * @var bool
     */
    private $inTransaction = false;
    
    /**
     * Constructor
     * 
     * @param array $config Database configuration array
     * @throws PDOException If connection fails
     */
    public function __construct($config = []) {
        $this->config = array_merge([
            'host' => 'localhost',
            'port' => 3306,
            'database' => 'employee_manager',
            'username' => 'root',
            'password' => '',
            'charset' => 'utf8mb4',
            'options' => []
        ], $config);
        
        $this->connect();
    }
    
    /**
     * Establish database connection
     * 
     * @throws PDOException If connection fails
     */
    private function connect() {
        try {
            $dsn = "mysql:host={$this->config['host']};port={$this->config['port']};dbname={$this->config['database']};charset={$this->config['charset']}";
            
            $options = array_merge([
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES {$this->config['charset']}"
            ], $this->config['options']);
            
            $this->connection = new PDO($dsn, $this->config['username'], $this->config['password'], $options);
            
            // Log successful connection
            logError("Database connection established", [], 'info');
            
        } catch (PDOException $e) {
            $errorMessage = "Database connection failed: " . $e->getMessage();
            logError($errorMessage, ['config' => $this->config], 'error');
            throw new PDOException("Database connection failed", 0, $e);
        }
    }
    
    /**
     * Get database connection instance
     * 
     * @return PDO
     */
    public function getConnection() {
        return $this->connection;
    }
    
    /**
     * Execute a prepared statement
     * 
     * @param string $sql SQL query with placeholders
     * @param array $params Parameters to bind to the query
     * @return PDOStatement
     * @throws PDOException If execution fails
     */
    public function query($sql, $params = []) {
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            
            logError("Query executed: " . substr($sql, 0, 100), ['params' => $params], 'debug');
            
            return $stmt;
            
        } catch (PDOException $e) {
            $errorMessage = "Query execution failed: " . $e->getMessage();
            logError($errorMessage, ['sql' => $sql, 'params' => $params], 'error');
            throw $e;
        }
    }
    
    /**
     * Execute a query and fetch single row
     * 
     * @param string $sql SQL query with placeholders
     * @param array $params Parameters to bind to the query
     * @return array|false Single row or false if no results
     */
    public function fetchRow($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt->fetch();
    }
    
    /**
     * Execute a query and fetch all rows
     * 
     * @param string $sql SQL query with placeholders
     * @param array $params Parameters to bind to the query
     * @return array Array of rows
     */
    public function fetchAll($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt->fetchAll();
    }
    
    /**
     * Execute INSERT query
     * 
     * @param string $table Table name
     * @param array $data Associative array of column => value
     * @return int Last insert ID
     * @throws PDOException If execution fails
     */
    public function insert($table, $data) {
        $columns = implode(',', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));
        
        $sql = "INSERT INTO {$table} ({$columns}) VALUES ({$placeholders})";
        
        $this->query($sql, $data);
        
        return $this->connection->lastInsertId();
    }
    
    /**
     * Execute UPDATE query
     * 
     * @param string $table Table name
     * @param array $data Associative array of column => value
     * @param string $where WHERE clause with placeholders
     * @param array $whereParams Parameters for WHERE clause
     * @return int Number of affected rows
     * @throws PDOException If execution fails
     */
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
    
    /**
     * Execute DELETE query
     * 
     * @param string $table Table name
     * @param string $where WHERE clause with placeholders
     * @param array $whereParams Parameters for WHERE clause
     * @return int Number of affected rows
     * @throws PDOException If execution fails
     */
    public function delete($table, $where, $whereParams = []) {
        $sql = "DELETE FROM {$table} WHERE {$where}";
        $stmt = $this->query($sql, $whereParams);
        
        return $stmt->rowCount();
    }
    
    /**
     * Begin a database transaction
     * 
     * @return bool True on success, false on failure
     */
    public function beginTransaction() {
        if ($this->inTransaction) {
            return false;
        }
        
        $result = $this->connection->beginTransaction();
        if ($result) {
            $this->inTransaction = true;
            logError("Transaction begun", [], 'debug');
        }
        
        return $result;
    }
    
    /**
     * Commit the current transaction
     * 
     * @return bool True on success, false on failure
     */
    public function commit() {
        if (!$this->inTransaction) {
            return false;
        }
        
        $result = $this->connection->commit();
        if ($result) {
            $this->inTransaction = false;
            logError("Transaction committed", [], 'debug');
        }
        
        return $result;
    }
    
    /**
     * Rollback the current transaction
     * 
     * @return bool True on success, false on failure
     */
    public function rollback() {
        if (!$this->inTransaction) {
            return false;
        }
        
        $result = $this->connection->rollback();
        if ($result) {
            $this->inTransaction = false;
            logError("Transaction rolled back", [], 'debug');
        }
        
        return $result;
    }
    
    /**
     * Check if currently in a transaction
     * 
     * @return bool
     */
    public function inTransaction() {
        return $this->inTransaction;
    }
    
    /**
     * Execute a transaction with automatic rollback on failure
     * 
     * @param callable $callback Function to execute within transaction
     * @return mixed Result of the callback function
     * @throws Exception If transaction fails
     */
    public function transaction($callback) {
        try {
            $this->beginTransaction();
            $result = $callback($this);
            $this->commit();
            return $result;
            
        } catch (Exception $e) {
            $this->rollback();
            logError("Transaction failed: " . $e->getMessage(), [], 'error');
            throw $e;
        }
    }
    
    /**
     * Count rows in a table
     * 
     * @param string $table Table name
     * @param string $where WHERE clause (optional)
     * @param array $whereParams Parameters for WHERE clause
     * @return int Number of rows
     */
    public function count($table, $where = '1', $whereParams = []) {
        $sql = "SELECT COUNT(*) as total FROM {$table} WHERE {$where}";
        $result = $this->fetchRow($sql, $whereParams);
        return (int) $result['total'];
    }
    
    /**
     * Check if a record exists
     * 
     * @param string $table Table name
     * @param string $where WHERE clause
     * @param array $params Parameters for WHERE clause
     * @return bool True if exists, false otherwise
     */
    public function exists($table, $where, $params = []) {
        $count = $this->count($table, $where, $params);
        return $count > 0;
    }
    
    /**
     * Get the last error message
     * 
     * @return string Error message or empty string
     */
    public function getLastError() {
        return $this->connection->errorInfo()[2] ?? '';
    }
    
    /**
     * Escape string for safe SQL usage
     * 
     * @param string $string String to escape
     * @return string Escaped string
     */
    public function escape($string) {
        return $this->connection->quote($string);
    }
    
    /**
     * Test database connection
     * 
     * @return bool True if connection is valid, false otherwise
     */
    public function testConnection() {
        try {
            $this->query("SELECT 1");
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }
    
    /**
     * Get database version
     * 
     * @return string Database version
     */
    public function getVersion() {
        try {
            $result = $this->fetchRow("SELECT VERSION() as version");
            return $result['version'];
        } catch (PDOException $e) {
            return 'Unknown';
        }
    }
    
    /**
     * Get database statistics
     * 
     * @return array Database statistics
     */
    public function getStats() {
        try {
            $stats = [];
            
            // Get table count
            $tables = $this->fetchAll("SHOW TABLES");
            $stats['table_count'] = count($tables);
            
            // Get database size
            $size = $this->fetchRow("SELECT 
                ROUND(SUM(data_length + index_length) / 1024 / 1024, 1) AS 'size_mb' 
                FROM information_schema.tables 
                WHERE table_schema = DATABASE()
            ");
            $stats['size_mb'] = $size['size_mb'] ?? 0;
            
            return $stats;
            
        } catch (PDOException $e) {
            return [];
        }
    }
    
    /**
     * Close database connection
     */
    public function close() {
        $this->connection = null;
    }
    
    /**
     * Destructor
     */
    public function __destruct() {
        if ($this->inTransaction) {
            logError("Warning: Database object destroyed while in transaction", [], 'warning');
            $this->rollback();
        }
    }
}

/**
 * Global database instance
 * @var Database|null
 */
$db = null;

/**
 * Get database instance (singleton pattern)
 * 
 * @param array $config Optional database configuration
 * @return Database Database instance
 */
function getDatabase($config = []) {
    global $db;
    
    if ($db === null) {
        $db = new Database($config);
    }
    
    return $db;
}

/**
 * Initialize database with environment configuration
 * 
 * @return Database Database instance
 */
function initDatabase() {
    $config = [
        'host' => $_ENV['DB_HOST'] ?? 'localhost',
        'port' => $_ENV['DB_PORT'] ?? 3306,
        'database' => $_ENV['DB_NAME'] ?? 'employee_manager',
        'username' => $_ENV['DB_USER'] ?? 'root',
        'password' => $_ENV['DB_PASS'] ?? ''
    ];
    
    return getDatabase($config);
}
?>