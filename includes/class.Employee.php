<?php
/**
 * Employee Management Class
 * 
 * This class handles all employee-related operations including CRUD (Create, Read, Update, Delete)
 * operations with proper validation, error handling, and security measures.
 * 
 * @package EmployeeManager
 * @version 1.0
 * @author Development Team
 */

require_once 'class.Database.php';
require_once 'functions.php';

class Employee {
    /**
     * Database instance
     * @var Database
     */
    private $db;
    
    /**
     * Employee table name
     * @var string
     */
    private $table = 'employees';
    
    /**
     * Validation rules for employee data
     * @var array
     */
    private $validationRules = [
        'first_name' => ['required' => true, 'max_length' => 50],
        'last_name' => ['required' => true, 'max_length' => 50],
        'email' => ['required' => true, 'email' => true, 'max_length' => 100],
        'phone' => ['required' => false, 'phone' => true, 'max_length' => 20],
        'date_of_birth' => ['required' => false, 'date' => true],
        'hire_date' => ['required' => true, 'date' => true],
        'department_id' => ['required' => true, 'numeric' => true],
        'position' => ['required' => true, 'max_length' => 100],
        'salary' => ['required' => false, 'numeric' => true, 'min_value' => 0],
        'address' => ['required' => false, 'max_length' => 255],
        'emergency_contact' => ['required' => false, 'max_length' => 255],
        'emergency_phone' => ['required' => false, 'phone' => true, 'max_length' => 20]
    ];
    
    /**
     * Constructor
     * 
     * @param Database|null $db Database instance (optional)
     */
    public function __construct($db = null) {
        $this->db = $db ?: getDatabase();
    }
    
    /**
     * Create a new employee
     * 
     * @param array $employeeData Employee data
     * @return array Result with 'success', 'employee_id', 'errors'
     * @throws Exception If validation fails
     */
    public function create($employeeData) {
        try {
            // Validate input data
            $validationResult = $this->validateEmployeeData($employeeData);
            if (!$validationResult['valid']) {
                return [
                    'success' => false,
                    'errors' => $validationResult['errors']
                ];
            }
            
            // Check for duplicate email
            if ($this->emailExists($employeeData['email'])) {
                return [
                    'success' => false,
                    'errors' => ['Email address already exists']
                ];
            }
            
            // Sanitize input data
            $cleanData = $this->sanitizeEmployeeData($employeeData);
            
            // Add creation timestamps
            $cleanData['created_at'] = date('Y-m-d H:i:s');
            $cleanData['updated_at'] = date('Y-m-d H:i:s');
            
            // Set default values
            $cleanData['status'] = $cleanData['status'] ?? 'active';
            $cleanData['is_active'] = 1;
            
            // Insert employee record
            $employeeId = $this->db->insert($this->table, $cleanData);
            
            logError("Employee created successfully", ['employee_id' => $employeeId, 'email' => $employeeData['email']]);
            
            return [
                'success' => true,
                'employee_id' => $employeeId,
                'message' => 'Employee created successfully'
            ];
            
        } catch (Exception $e) {
            logError("Failed to create employee: " . $e->getMessage(), ['employee_data' => $employeeData]);
            return [
                'success' => false,
                'errors' => ['Failed to create employee: ' . $e->getMessage()]
            ];
        }
    }
    
    /**
     * Get employee by ID
     * 
     * @param int $employeeId Employee ID
     * @return array|null Employee data or null if not found
     */
    public function getById($employeeId) {
        try {
            $sql = "SELECT e.*, d.name as department_name 
                   FROM {$this->table} e 
                   LEFT JOIN departments d ON e.department_id = d.id 
                   WHERE e.id = ?";
            
            $employee = $this->db->fetchRow($sql, [$employeeId]);
            
            if ($employee) {
                // Format dates for display
                $employee['formatted_hire_date'] = formatDate($employee['hire_date']);
                $employee['formatted_dob'] = formatDate($employee['date_of_birth']);
                $employee['formatted_created_at'] = formatDateTime($employee['created_at']);
                $employee['formatted_updated_at'] = formatDateTime($employee['updated_at']);
            }
            
            return $employee;
            
        } catch (Exception $e) {
            logError("Failed to get employee by ID: " . $e->getMessage(), ['employee_id' => $employeeId]);
            return null;
        }
    }
    
    /**
     * Get employee by email
     * 
     * @param string $email Employee email
     * @return array|null Employee data or null if not found
     */
    public function getByEmail($email) {
        try {
            $sql = "SELECT e.*, d.name as department_name 
                   FROM {$this->table} e 
                   LEFT JOIN departments d ON e.department_id = d.id 
                   WHERE e.email = ?";
            
            return $this->db->fetchRow($sql, [$email]);
            
        } catch (Exception $e) {
            logError("Failed to get employee by email: " . $e->getMessage(), ['email' => $email]);
            return null;
        }
    }
    
    /**
     * Get all employees with pagination
     * 
     * @param array $filters Optional filters (department_id, status, search_term)
     * @param int $limit Number of records per page
     * @param int $offset Offset for pagination
     * @param string $sortBy Column to sort by
     * @param string $sortOrder Sort order (ASC/DESC)
     * @return array Result with 'employees', 'total', 'pagination'
     */
    public function getAll($filters = [], $limit = 20, $offset = 0, $sortBy = 'last_name', $sortOrder = 'ASC') {
        try {
            $whereConditions = [];
            $params = [];
            
            // Build WHERE clause based on filters
            if (!empty($filters['department_id'])) {
                $whereConditions[] = 'e.department_id = ?';
                $params[] = $filters['department_id'];
            }
            
            if (!empty($filters['status'])) {
                $whereConditions[] = 'e.status = ?';
                $params[] = $filters['status'];
            }
            
            if (!empty($filters['is_active'])) {
                $whereConditions[] = 'e.is_active = ?';
                $params[] = $filters['is_active'];
            }
            
            if (!empty($filters['search_term'])) {
                $whereConditions[] = '(e.first_name LIKE ? OR e.last_name LIKE ? OR e.email LIKE ? OR e.position LIKE ?)';
                $searchTerm = '%' . $filters['search_term'] . '%';
                $params[] = $searchTerm;
                $params[] = $searchTerm;
                $params[] = $searchTerm;
                $params[] = $searchTerm;
            }
            
            $whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';
            
            // Validate sort parameters
            $allowedSortColumns = ['first_name', 'last_name', 'email', 'hire_date', 'department_id', 'position', 'salary', 'created_at'];
            if (!in_array($sortBy, $allowedSortColumns)) {
                $sortBy = 'last_name';
            }
            
            $sortOrder = strtoupper($sortOrder) === 'DESC' ? 'DESC' : 'ASC';
            
            // Get total count
            $countSql = "SELECT COUNT(*) as total 
                        FROM {$this->table} e 
                        LEFT JOIN departments d ON e.department_id = d.id 
                        {$whereClause}";
            $totalResult = $this->db->fetchRow($countSql, $params);
            $total = (int) $totalResult['total'];
            
            // Get employees
            $sql = "SELECT e.*, d.name as department_name 
                   FROM {$this->table} e 
                   LEFT JOIN departments d ON e.department_id = d.id 
                   {$whereClause} 
                   ORDER BY e.{$sortBy} {$sortOrder} 
                   LIMIT ? OFFSET ?";
            
            $params[] = $limit;
            $params[] = $offset;
            
            $employees = $this->db->fetchAll($sql, $params);
            
            // Format dates for display
            foreach ($employees as &$employee) {
                $employee['formatted_hire_date'] = formatDate($employee['hire_date']);
                $employee['formatted_dob'] = formatDate($employee['date_of_birth']);
                $employee['formatted_created_at'] = formatDateTime($employee['created_at']);
                $employee['formatted_updated_at'] = formatDateTime($employee['updated_at']);
            }
            
            $totalPages = ceil($total / $limit);
            
            return [
                'employees' => $employees,
                'total' => $total,
                'pagination' => [
                    'current_page' => floor($offset / $limit) + 1,
                    'total_pages' => $totalPages,
                    'per_page' => $limit,
                    'total_records' => $total,
                    'has_next' => ($offset + $limit) < $total,
                    'has_prev' => $offset > 0
                ]
            ];
            
        } catch (Exception $e) {
            logError("Failed to get all employees: " . $e->getMessage(), ['filters' => $filters]);
            return [
                'employees' => [],
                'total' => 0,
                'pagination' => []
            ];
        }
    }
    
    /**
     * Update employee information
     * 
     * @param int $employeeId Employee ID
     * @param array $employeeData Updated employee data
     * @return array Result with 'success', 'message', 'errors'
     */
    public function update($employeeId, $employeeData) {
        try {
            // Check if employee exists
            $existingEmployee = $this->getById($employeeId);
            if (!$existingEmployee) {
                return [
                    'success' => false,
                    'errors' => ['Employee not found']
                ];
            }
            
            // Validate input data
            $validationResult = $this->validateEmployeeData($employeeData, $employeeId);
            if (!$validationResult['valid']) {
                return [
                    'success' => false,
                    'errors' => $validationResult['errors']
                ];
            }
            
            // Check for duplicate email (excluding current employee)
            if (isset($employeeData['email']) && $employeeData['email'] !== $existingEmployee['email']) {
                if ($this->emailExists($employeeData['email'], $employeeId)) {
                    return [
                        'success' => false,
                        'errors' => ['Email address already exists']
                    ];
                }
            }
            
            // Sanitize input data
            $cleanData = $this->sanitizeEmployeeData($employeeData);
            
            // Add update timestamp
            $cleanData['updated_at'] = date('Y-m-d H:i:s');
            
            // Remove ID from update data if present
            unset($cleanData['id']);
            unset($cleanData['created_at']);
            
            // Update employee record
            $affectedRows = $this->db->update($this->table, $cleanData, 'id = ?', [$employeeId]);
            
            if ($affectedRows > 0) {
                logError("Employee updated successfully", ['employee_id' => $employeeId]);
                
                return [
                    'success' => true,
                    'message' => 'Employee updated successfully',
                    'affected_rows' => $affectedRows
                ];
            } else {
                return [
                    'success' => false,
                    'errors' => ['No changes were made']
                ];
            }
            
        } catch (Exception $e) {
            logError("Failed to update employee: " . $e->getMessage(), ['employee_id' => $employeeId, 'data' => $employeeData]);
            return [
                'success' => false,
                'errors' => ['Failed to update employee: ' . $e->getMessage()]
            ];
        }
    }
    
    /**
     * Delete employee
     * 
     * @param int $employeeId Employee ID
     * @param bool $softDelete Whether to perform soft delete (default: true)
     * @return array Result with 'success', 'message', 'errors'
     */
    public function delete($employeeId, $softDelete = true) {
        try {
            // Check if employee exists
            $employee = $this->getById($employeeId);
            if (!$employee) {
                return [
                    'success' => false,
                    'errors' => ['Employee not found']
                ];
            }
            
            if ($softDelete) {
                // Soft delete - mark as inactive
                $result = $this->db->update(
                    $this->table, 
                    ['is_active' => 0, 'status' => 'inactive', 'updated_at' => date('Y-m-d H:i:s')], 
                    'id = ?', 
                    [$employeeId]
                );
            } else {
                // Hard delete - remove from database
                $result = $this->db->delete($this->table, 'id = ?', [$employeeId]);
            }
            
            if ($result > 0) {
                logError("Employee deleted successfully", [
                    'employee_id' => $employeeId, 
                    'soft_delete' => $softDelete
                ]);
                
                return [
                    'success' => true,
                    'message' => 'Employee deleted successfully',
                    'soft_delete' => $softDelete
                ];
            } else {
                return [
                    'success' => false,
                    'errors' => ['Failed to delete employee']
                ];
            }
            
        } catch (Exception $e) {
            logError("Failed to delete employee: " . $e->getMessage(), ['employee_id' => $employeeId]);
            return [
                'success' => false,
                'errors' => ['Failed to delete employee: ' . $e->getMessage()]
            ];
        }
    }
    
    /**
     * Restore soft-deleted employee
     * 
     * @param int $employeeId Employee ID
     * @return array Result with 'success', 'message', 'errors'
     */
    public function restore($employeeId) {
        try {
            $result = $this->db->update(
                $this->table, 
                ['is_active' => 1, 'status' => 'active', 'updated_at' => date('Y-m-d H:i:s')], 
                'id = ?', 
                [$employeeId]
            );
            
            if ($result > 0) {
                logError("Employee restored successfully", ['employee_id' => $employeeId]);
                
                return [
                    'success' => true,
                    'message' => 'Employee restored successfully'
                ];
            } else {
                return [
                    'success' => false,
                    'errors' => ['Failed to restore employee or employee not found']
                ];
            }
            
        } catch (Exception $e) {
            logError("Failed to restore employee: " . $e->getMessage(), ['employee_id' => $employeeId]);
            return [
                'success' => false,
                'errors' => ['Failed to restore employee: ' . $e->getMessage()]
            ];
        }
    }
    
    /**
     * Get employee statistics
     * 
     * @return array Employee statistics
     */
    public function getStatistics() {
        try {
            $stats = [];
            
            // Total employees
            $totalResult = $this->db->fetchRow("SELECT COUNT(*) as total FROM {$this->table}");
            $stats['total_employees'] = (int) $totalResult['total'];
            
            // Active employees
            $activeResult = $this->db->fetchRow("SELECT COUNT(*) as total FROM {$this->table} WHERE is_active = 1");
            $stats['active_employees'] = (int) $activeResult['total'];
            
            // Employees by department
            $deptResult = $this->db->fetchAll("
                SELECT d.name as department_name, COUNT(e.id) as count 
                FROM departments d 
                LEFT JOIN {$this->table} e ON d.id = e.department_id AND e.is_active = 1 
                GROUP BY d.id, d.name 
                ORDER BY count DESC
            ");
            $stats['by_department'] = $deptResult;
            
            // Recent hires (last 30 days)
            $recentHiresResult = $this->db->fetchRow("
                SELECT COUNT(*) as total FROM {$this->table} 
                WHERE hire_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
            ");
            $stats['recent_hires'] = (int) $recentHiresResult['total'];
            
            // Average salary
            $avgSalaryResult = $this->db->fetchRow("
                SELECT AVG(salary) as average FROM {$this->table} 
                WHERE salary > 0 AND is_active = 1
            ");
            $stats['average_salary'] = round($avgSalaryResult['average'], 2);
            
            return $stats;
            
        } catch (Exception $e) {
            logError("Failed to get employee statistics: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Search employees by various criteria
     * 
     * @param string $searchTerm Search term
     * @param array $searchFields Fields to search in
     * @param int $limit Maximum results to return
     * @return array Array of matching employees
     */
    public function search($searchTerm, $searchFields = ['first_name', 'last_name', 'email', 'position'], $limit = 50) {
        try {
            $whereConditions = [];
            $params = [];
            
            foreach ($searchFields as $field) {
                $whereConditions[] = "{$field} LIKE ?";
                $params[] = '%' . $searchTerm . '%';
            }
            
            $whereClause = '(' . implode(' OR ', $whereConditions) . ') AND is_active = 1';
            
            $sql = "SELECT e.*, d.name as department_name 
                   FROM {$this->table} e 
                   LEFT JOIN departments d ON e.department_id = d.id 
                   WHERE {$whereClause} 
                   ORDER BY e.first_name, e.last_name 
                   LIMIT ?";
            
            $params[] = $limit;
            
            return $this->db->fetchAll($sql, $params);
            
        } catch (Exception $e) {
            logError("Failed to search employees: " . $e->getMessage(), ['search_term' => $searchTerm]);
            return [];
        }
    }
    
    /**
     * Validate employee data
     * 
     * @param array $data Employee data to validate
     * @param int|null $excludeId Employee ID to exclude from uniqueness checks
     * @return array Validation result with 'valid' boolean and 'errors' array
     */
    private function validateEmployeeData($data, $excludeId = null) {
        $errors = [];
        
        foreach ($this->validationRules as $field => $rules) {
            $value = $data[$field] ?? null;
            
            // Check if field is required
            if ($rules['required'] && (empty($value) || trim($value) === '')) {
                $errors[] = ucfirst(str_replace('_', ' ', $field)) . " is required";
                continue;
            }
            
            // Skip validation if field is not required and empty
            if (!$rules['required'] && (empty($value) || trim($value) === '')) {
                continue;
            }
            
            // Check max length
            if (isset($rules['max_length']) && strlen($value) > $rules['max_length']) {
                $errors[] = ucfirst(str_replace('_', ' ', $field)) . " must not exceed {$rules['max_length']} characters";
            }
            
            // Validate email format
            if (isset($rules['email']) && $rules['email'] && !validateEmail($value)) {
                $errors[] = "Invalid email format";
            }
            
            // Validate phone format
            if (isset($rules['phone']) && $rules['phone'] && !validatePhone($value)) {
                $errors[] = "Invalid phone number format";
            }
            
            // Validate date format
            if (isset($rules['date']) && $rules['date'] && !validateDate($value)) {
                $errors[] = "Invalid date format";
            }
            
            // Validate numeric values
            if (isset($rules['numeric']) && $rules['numeric'] && !is_numeric($value)) {
                $errors[] = ucfirst(str_replace('_', ' ', $field)) . " must be a number";
            }
            
            // Validate minimum value
            if (isset($rules['min_value']) && $value < $rules['min_value']) {
                $errors[] = ucfirst(str_replace('_', ' ', $field)) . " must be at least {$rules['min_value']}";
            }
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }
    
    /**
     * Sanitize employee data for database insertion
     * 
     * @param array $data Raw employee data
     * @return array Sanitized employee data
     */
    private function sanitizeEmployeeData($data) {
        $sanitized = [];
        
        foreach ($data as $key => $value) {
            if (is_string($value)) {
                $sanitized[$key] = sanitizeInput($value);
            } else {
                $sanitized[$key] = $value;
            }
        }
        
        return $sanitized;
    }
    
    /**
     * Check if email already exists
     * 
     * @param string $email Email to check
     * @param int|null $excludeId Employee ID to exclude from check
     * @return bool True if email exists, false otherwise
     */
    private function emailExists($email, $excludeId = null) {
        $whereClause = "email = ?";
        $params = [$email];
        
        if ($excludeId) {
            $whereClause .= " AND id != ?";
            $params[] = $excludeId;
        }
        
        return $this->db->exists($this->table, $whereClause, $params);
    }
}
?>