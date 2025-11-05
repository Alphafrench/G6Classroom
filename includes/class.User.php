<?php
/**
 * User Management Class
 * 
 * This class handles user operations including authentication, profile management,
 * and role-based access control with teacher/student roles.
 * 
 * @package AttendanceSystem
 * @version 1.0
 * @author Development Team
 */

require_once 'class.Database.php';
require_once 'functions.php';
require_once 'auth.php';

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
     * User roles
     * @var array
     */
    private $allowedRoles = ['admin', 'teacher', 'student'];
    
    /**
     * User statuses
     * @var array
     */
    private $allowedStatuses = ['active', 'inactive', 'suspended'];
    
    /**
     * Validation rules for user data
     * @var array
     */
    private $validationRules = [
        'username' => ['required' => true, 'min_length' => 3, 'max_length' => 50],
        'email' => ['required' => true, 'email' => true, 'max_length' => 100],
        'password' => ['required' => false, 'min_length' => 8], // Only required for new users
        'first_name' => ['required' => true, 'max_length' => 50],
        'last_name' => ['required' => true, 'max_length' => 50],
        'role' => ['required' => true, 'in_array' => ['admin', 'teacher', 'student']],
        'phone' => ['required' => false, 'max_length' => 20],
        'date_of_birth' => ['required' => false, 'date' => true]
    ];
    
    /**
     * Constructor
     * 
     * @param Database|null $db Database instance (optional)
     */
    public function __construct($db = null) {
        $this->db = $db ?: getDatabase();
        initialize_session();
    }
    
    /**
     * Create a new user
     * 
     * @param array $userData User data
     * @param int|null $createdBy User ID who created this user (for audit log)
     * @return array Result with 'success', 'user_id', 'errors'
     * @throws Exception If validation fails
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
            
            // Check for duplicate username
            if ($this->usernameExists($userData['username'])) {
                return [
                    'success' => false,
                    'errors' => ['Username already exists']
                ];
            }
            
            // Check for duplicate email
            if ($this->emailExists($userData['email'])) {
                return [
                    'success' => false,
                    'errors' => ['Email address already exists']
                ];
            }
            
            // Require password for new users
            if (empty($userData['password'])) {
                return [
                    'success' => false,
                    'errors' => ['Password is required for new users']
                ];
            }
            
            // Sanitize input data
            $cleanData = $this->sanitizeUserData($userData);
            
            // Hash password
            $cleanData['password_hash'] = hashPassword($cleanData['password']);
            unset($cleanData['password']);
            
            // Add creation timestamps
            $cleanData['created_at'] = date('Y-m-d H:i:s');
            $cleanData['updated_at'] = date('Y-m-d H:i:s');
            $cleanData['email_verified'] = 0;
            $cleanData['failed_attempts'] = 0;
            $cleanData['last_login'] = null;
            
            // Set default status
            $cleanData['status'] = 'active';
            
            // Insert user record
            $userId = $this->db->insert($this->table, $cleanData);
            
            // Log the creation
            if ($createdBy) {
                $this->logActivity($userId, 'user_created', "User account created by user ID: {$createdBy}");
            }
            
            logError("User created successfully", [
                'user_id' => $userId, 
                'username' => $userData['username'],
                'role' => $userData['role'],
                'created_by' => $createdBy
            ]);
            
            return [
                'success' => true,
                'user_id' => $userId,
                'message' => 'User created successfully'
            ];
            
        } catch (Exception $e) {
            logError("Failed to create user: " . $e->getMessage(), ['user_data' => $userData, 'created_by' => $createdBy]);
            return [
                'success' => false,
                'errors' => ['Failed to create user: ' . $e->getMessage()]
            ];
        }
    }
    
    /**
     * Get user by ID
     * 
     * @param int $userId User ID
     * @return array|null User data or null if not found
     */
    public function getById($userId) {
        try {
            $sql = "SELECT id, username, email, first_name, last_name, role, phone, 
                           date_of_birth, status, email_verified, failed_attempts, 
                           last_login, last_failed_attempt, created_at, updated_at 
                   FROM {$this->table} 
                   WHERE id = ?";
            
            $user = $this->db->fetchRow($sql, [$userId]);
            
            if ($user) {
                // Format dates for display
                $user['formatted_date_of_birth'] = formatDate($user['date_of_birth']);
                $user['formatted_created_at'] = formatDateTime($user['created_at']);
                $user['formatted_updated_at'] = formatDateTime($user['updated_at']);
                $user['formatted_last_login'] = $user['last_login'] ? formatDateTime($user['last_login']) : 'Never';
                
                // Add role display name
                $user['role_display'] = ucfirst($user['role']);
            }
            
            return $user;
            
        } catch (Exception $e) {
            logError("Failed to get user by ID: " . $e->getMessage(), ['user_id' => $userId]);
            return null;
        }
    }
    
    /**
     * Get user by username or email
     * 
     * @param string $username Username or email
     * @return array|null User data or null if not found
     */
    public function getByUsername($username) {
        try {
            $sql = "SELECT id, username, email, first_name, last_name, role, phone, 
                           date_of_birth, status, email_verified, failed_attempts, 
                           last_login, last_failed_attempt, created_at, updated_at,
                           password_hash
                   FROM {$this->table} 
                   WHERE (username = ? OR email = ?) AND status != 'suspended'";
            
            return $this->db->fetchRow($sql, [$username, $username]);
            
        } catch (Exception $e) {
            logError("Failed to get user by username: " . $e->getMessage(), ['username' => $username]);
            return null;
        }
    }
    
    /**
     * Get all users with pagination and filtering
     * 
     * @param array $filters Optional filters (role, status, search_term)
     * @param int $limit Number of records per page
     * @param int $offset Offset for pagination
     * @param string $sortBy Column to sort by
     * @param string $sortOrder Sort order (ASC/DESC)
     * @return array Result with 'users', 'total', 'pagination'
     */
    public function getAll($filters = [], $limit = 20, $offset = 0, $sortBy = 'last_name', $sortOrder = 'ASC') {
        try {
            $whereConditions = [];
            $params = [];
            
            // Build WHERE clause based on filters
            if (!empty($filters['role']) && in_array($filters['role'], $this->allowedRoles)) {
                $whereConditions[] = 'role = ?';
                $params[] = $filters['role'];
            }
            
            if (!empty($filters['status']) && in_array($filters['status'], $this->allowedStatuses)) {
                $whereConditions[] = 'status = ?';
                $params[] = $filters['status'];
            }
            
            if (isset($filters['email_verified'])) {
                $whereConditions[] = 'email_verified = ?';
                $params[] = $filters['email_verified'] ? 1 : 0;
            }
            
            if (!empty($filters['search_term'])) {
                $whereConditions[] = '(first_name LIKE ? OR last_name LIKE ? OR username LIKE ? OR email LIKE ?)';
                $searchTerm = '%' . $filters['search_term'] . '%';
                $params[] = $searchTerm;
                $params[] = $searchTerm;
                $params[] = $searchTerm;
                $params[] = $searchTerm;
            }
            
            $whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';
            
            // Validate sort parameters
            $allowedSortColumns = ['first_name', 'last_name', 'username', 'email', 'role', 'created_at', 'last_login'];
            if (!in_array($sortBy, $allowedSortColumns)) {
                $sortBy = 'last_name';
            }
            
            $sortOrder = strtoupper($sortOrder) === 'DESC' ? 'DESC' : 'ASC';
            
            // Get total count
            $countSql = "SELECT COUNT(*) as total FROM {$this->table} {$whereClause}";
            $totalResult = $this->db->fetchRow($countSql, $params);
            $total = (int) $totalResult['total'];
            
            // Get users
            $sql = "SELECT id, username, email, first_name, last_name, role, phone, 
                           date_of_birth, status, email_verified, created_at, updated_at, last_login
                   FROM {$this->table} 
                   {$whereClause} 
                   ORDER BY {$sortBy} {$sortOrder} 
                   LIMIT ? OFFSET ?";
            
            $params[] = $limit;
            $params[] = $offset;
            
            $users = $this->db->fetchAll($sql, $params);
            
            // Format dates and add display names
            foreach ($users as &$user) {
                $user['formatted_date_of_birth'] = formatDate($user['date_of_birth']);
                $user['formatted_created_at'] = formatDateTime($user['created_at']);
                $user['formatted_updated_at'] = formatDateTime($user['updated_at']);
                $user['formatted_last_login'] = $user['last_login'] ? formatDateTime($user['last_login']) : 'Never';
                $user['full_name'] = $user['first_name'] . ' ' . $user['last_name'];
                $user['role_display'] = ucfirst($user['role']);
            }
            
            $totalPages = ceil($total / $limit);
            
            return [
                'users' => $users,
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
            logError("Failed to get all users: " . $e->getMessage(), ['filters' => $filters]);
            return [
                'users' => [],
                'total' => 0,
                'pagination' => []
            ];
        }
    }
    
    /**
     * Update user information
     * 
     * @param int $userId User ID
     * @param array $userData Updated user data
     * @param int|null $updatedBy User ID who made the update (for audit log)
     * @return array Result with 'success', 'message', 'errors'
     */
    public function update($userId, $userData, $updatedBy = null) {
        try {
            // Check if user exists
            $existingUser = $this->getById($userId);
            if (!$existingUser) {
                return [
                    'success' => false,
                    'errors' => ['User not found']
                ];
            }
            
            // Validate input data
            $validationResult = $this->validateUserData($userData, $userId);
            if (!$validationResult['valid']) {
                return [
                    'success' => false,
                    'errors' => $validationResult['errors']
                ];
            }
            
            // Check for duplicate username (excluding current user)
            if (isset($userData['username']) && $userData['username'] !== $existingUser['username']) {
                if ($this->usernameExists($userData['username'], $userId)) {
                    return [
                        'success' => false,
                        'errors' => ['Username already exists']
                    ];
                }
            }
            
            // Check for duplicate email (excluding current user)
            if (isset($userData['email']) && $userData['email'] !== $existingUser['email']) {
                if ($this->emailExists($userData['email'], $userId)) {
                    return [
                        'success' => false,
                        'errors' => ['Email address already exists']
                    ];
                }
            }
            
            // Sanitize input data
            $cleanData = $this->sanitizeUserData($userData);
            
            // Handle password update
            if (isset($cleanData['password']) && !empty($cleanData['password'])) {
                $cleanData['password_hash'] = hashPassword($cleanData['password']);
                unset($cleanData['password']);
            } else {
                unset($cleanData['password']);
            }
            
            // Add update timestamp
            $cleanData['updated_at'] = date('Y-m-d H:i:s');
            
            // Remove fields that shouldn't be updated directly
            unset($cleanData['id']);
            unset($cleanData['created_at']);
            unset($cleanData['failed_attempts']);
            unset($cleanData['last_login']);
            unset($cleanData['last_failed_attempt']);
            
            // Update user record
            $affectedRows = $this->db->update($this->table, $cleanData, 'id = ?', [$userId]);
            
            if ($affectedRows > 0) {
                // Log the update
                if ($updatedBy) {
                    $this->logActivity($userId, 'user_updated', "User profile updated by user ID: {$updatedBy}");
                }
                
                logError("User updated successfully", [
                    'user_id' => $userId,
                    'updated_by' => $updatedBy,
                    'changes' => array_keys($cleanData)
                ]);
                
                return [
                    'success' => true,
                    'message' => 'User updated successfully',
                    'affected_rows' => $affectedRows
                ];
            } else {
                return [
                    'success' => false,
                    'errors' => ['No changes were made']
                ];
            }
            
        } catch (Exception $e) {
            logError("Failed to update user: " . $e->getMessage(), [
                'user_id' => $userId, 
                'user_data' => $userData,
                'updated_by' => $updatedBy
            ]);
            return [
                'success' => false,
                'errors' => ['Failed to update user: ' . $e->getMessage()]
            ];
        }
    }
    
    /**
     * Change user password
     * 
     * @param int $userId User ID
     * @param string $newPassword New password
     * @param string $currentPassword Current password (for verification)
     * @param int|null $changedBy User ID who changed the password
     * @return array Result with 'success', 'message', 'errors'
     */
    public function changePassword($userId, $newPassword, $currentPassword = null, $changedBy = null) {
        try {
            // Get user with password hash
            $user = $this->db->fetchRow("SELECT id, password_hash FROM {$this->table} WHERE id = ?", [$userId]);
            
            if (!$user) {
                return [
                    'success' => false,
                    'errors' => ['User not found']
                ];
            }
            
            // Verify current password if provided
            if ($currentPassword !== null && !verifyPassword($currentPassword, $user['password_hash'])) {
                return [
                    'success' => false,
                    'errors' => ['Current password is incorrect']
                ];
            }
            
            // Validate new password strength
            $passwordValidation = validatePassword($newPassword);
            if (!$passwordValidation['valid']) {
                return [
                    'success' => false,
                    'errors' => $passwordValidation['errors']
                ];
            }
            
            // Update password
            $passwordHash = hashPassword($newPassword);
            $affectedRows = $this->db->update(
                $this->table, 
                ['password_hash' => $passwordHash, 'updated_at' => date('Y-m-d H:i:s')], 
                'id = ?', 
                [$userId]
            );
            
            if ($affectedRows > 0) {
                // Reset failed attempts on successful password change
                $this->db->update(
                    $this->table, 
                    ['failed_attempts' => 0, 'last_failed_attempt' => null], 
                    'id = ?', 
                    [$userId]
                );
                
                // Log the password change
                if ($changedBy) {
                    $this->logActivity($userId, 'password_changed', "Password changed by user ID: {$changedBy}");
                } else {
                    $this->logActivity($userId, 'password_changed', 'Password changed by user');
                }
                
                logError("Password changed successfully", [
                    'user_id' => $userId,
                    'changed_by' => $changedBy ?: 'self'
                ]);
                
                return [
                    'success' => true,
                    'message' => 'Password changed successfully'
                ];
            } else {
                return [
                    'success' => false,
                    'errors' => ['Failed to update password']
                ];
            }
            
        } catch (Exception $e) {
            logError("Failed to change password: " . $e->getMessage(), ['user_id' => $userId]);
            return [
                'success' => false,
                'errors' => ['Failed to change password: ' . $e->getMessage()]
            ];
        }
    }
    
    /**
     * Change user status
     * 
     * @param int $userId User ID
     * @param string $newStatus New status (active, inactive, suspended)
     * @param int|null $changedBy User ID who changed the status
     * @return array Result with 'success', 'message', 'errors'
     */
    public function changeStatus($userId, $newStatus, $changedBy = null) {
        try {
            if (!in_array($newStatus, $this->allowedStatuses)) {
                return [
                    'success' => false,
                    'errors' => ['Invalid status']
                ];
            }
            
            $existingUser = $this->getById($userId);
            if (!$existingUser) {
                return [
                    'success' => false,
                    'errors' => ['User not found']
                ];
            }
            
            if ($existingUser['status'] === $newStatus) {
                return [
                    'success' => false,
                    'errors' => ['User status is already ' . $newStatus]
                ];
            }
            
            $affectedRows = $this->db->update(
                $this->table, 
                ['status' => $newStatus, 'updated_at' => date('Y-m-d H:i:s')], 
                'id = ?', 
                [$userId]
            );
            
            if ($affectedRows > 0) {
                // Log the status change
                if ($changedBy) {
                    $this->logActivity($userId, 'status_changed', "Status changed from {$existingUser['status']} to {$newStatus} by user ID: {$changedBy}");
                } else {
                    $this->logActivity($userId, 'status_changed', "Status changed from {$existingUser['status']} to {$newStatus}");
                }
                
                logError("User status changed successfully", [
                    'user_id' => $userId,
                    'old_status' => $existingUser['status'],
                    'new_status' => $newStatus,
                    'changed_by' => $changedBy
                ]);
                
                return [
                    'success' => true,
                    'message' => "User status changed to {$newStatus} successfully"
                ];
            } else {
                return [
                    'success' => false,
                    'errors' => ['Failed to update user status']
                ];
            }
            
        } catch (Exception $e) {
            logError("Failed to change user status: " . $e->getMessage(), [
                'user_id' => $userId,
                'new_status' => $newStatus,
                'changed_by' => $changedBy
            ]);
            return [
                'success' => false,
                'errors' => ['Failed to change user status: ' . $e->getMessage()]
            ];
        }
    }
    
    /**
     * Delete user (soft delete)
     * 
     * @param int $userId User ID
     * @param int|null $deletedBy User ID who deleted the user
     * @return array Result with 'success', 'message', 'errors'
     */
    public function delete($userId, $deletedBy = null) {
        try {
            $existingUser = $this->getById($userId);
            if (!$existingUser) {
                return [
                    'success' => false,
                    'errors' => ['User not found']
                ];
            }
            
            // Soft delete - change status to inactive
            $affectedRows = $this->db->update(
                $this->table, 
                ['status' => 'inactive', 'updated_at' => date('Y-m-d H:i:s')], 
                'id = ?', 
                [$userId]
            );
            
            if ($affectedRows > 0) {
                // Log the deletion
                if ($deletedBy) {
                    $this->logActivity($userId, 'user_deleted', "User account deleted by user ID: {$deletedBy}");
                } else {
                    $this->logActivity($userId, 'user_deleted', 'User account deleted');
                }
                
                logError("User deleted successfully", [
                    'user_id' => $userId,
                    'deleted_by' => $deletedBy
                ]);
                
                return [
                    'success' => true,
                    'message' => 'User deleted successfully'
                ];
            } else {
                return [
                    'success' => false,
                    'errors' => ['Failed to delete user']
                ];
            }
            
        } catch (Exception $e) {
            logError("Failed to delete user: " . $e->getMessage(), [
                'user_id' => $userId,
                'deleted_by' => $deletedBy
            ]);
            return [
                'success' => false,
                'errors' => ['Failed to delete user: ' . $e->getMessage()]
            ];
        }
    }
    
    /**
     * Get users by role
     * 
     * @param string $role User role (teacher, student, admin)
     * @param bool $activeOnly Only active users (default: true)
     * @return array Array of users
     */
    public function getByRole($role, $activeOnly = true) {
        try {
            if (!in_array($role, $this->allowedRoles)) {
                return [];
            }
            
            $whereClause = 'role = ?';
            $params = [$role];
            
            if ($activeOnly) {
                $whereClause .= ' AND status = ?';
                $params[] = 'active';
            }
            
            $sql = "SELECT id, username, email, first_name, last_name, role, phone, 
                           date_of_birth, created_at 
                   FROM {$this->table} 
                   WHERE {$whereClause} 
                   ORDER BY last_name, first_name";
            
            $users = $this->db->fetchAll($sql, $params);
            
            // Format data
            foreach ($users as &$user) {
                $user['formatted_created_at'] = formatDateTime($user['created_at']);
                $user['formatted_date_of_birth'] = formatDate($user['date_of_birth']);
                $user['full_name'] = $user['first_name'] . ' ' . $user['last_name'];
            }
            
            return $users;
            
        } catch (Exception $e) {
            logError("Failed to get users by role: " . $e->getMessage(), ['role' => $role]);
            return [];
        }
    }
    
    /**
     * Get user statistics
     * 
     * @return array User statistics
     */
    public function getStatistics() {
        try {
            $stats = [];
            
            // Total users
            $totalResult = $this->db->fetchRow("SELECT COUNT(*) as total FROM {$this->table}");
            $stats['total_users'] = (int) $totalResult['total'];
            
            // Users by status
            $statusResult = $this->db->fetchAll("
                SELECT status, COUNT(*) as count 
                FROM {$this->table} 
                GROUP BY status 
                ORDER BY count DESC
            ");
            $stats['by_status'] = [];
            foreach ($statusResult as $row) {
                $stats['by_status'][$row['status']] = (int) $row['count'];
            }
            
            // Users by role
            $roleResult = $this->db->fetchAll("
                SELECT role, COUNT(*) as count 
                FROM {$this->table} 
                WHERE status != 'inactive'
                GROUP BY role 
                ORDER BY count DESC
            ");
            $stats['by_role'] = [];
            foreach ($roleResult as $row) {
                $stats['by_role'][$row['role']] = (int) $row['count'];
            }
            
            // Recent registrations (last 30 days)
            $recentResult = $this->db->fetchRow("
                SELECT COUNT(*) as total FROM {$this->table} 
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            ");
            $stats['recent_registrations'] = (int) $recentResult['total'];
            
            // Active users (logged in within last 30 days)
            $activeResult = $this->db->fetchRow("
                SELECT COUNT(*) as total FROM {$this->table} 
                WHERE last_login >= DATE_SUB(NOW(), INTERVAL 30 DAY) AND status = 'active'
            ");
            $stats['active_users'] = (int) $activeResult['total'];
            
            return $stats;
            
        } catch (Exception $e) {
            logError("Failed to get user statistics: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Validate user data
     * 
     * @param array $data User data to validate
     * @param int|null $excludeId User ID to exclude from uniqueness checks
     * @return array Validation result with 'valid' boolean and 'errors' array
     */
    private function validateUserData($data, $excludeId = null) {
        $errors = [];
        
        foreach ($this->validationRules as $field => $rules) {
            $value = $data[$field] ?? null;
            
            // Skip password validation if not provided (for updates)
            if ($field === 'password' && $excludeId !== null && empty($value)) {
                continue;
            }
            
            // Check if field is required
            if ($rules['required'] && (empty($value) || trim($value) === '')) {
                $errors[] = ucfirst(str_replace('_', ' ', $field)) . " is required";
                continue;
            }
            
            // Skip validation if field is not required and empty
            if (!$rules['required'] && (empty($value) || trim($value) === '')) {
                continue;
            }
            
            // Check min/max length
            if (isset($rules['min_length']) && strlen($value) < $rules['min_length']) {
                $errors[] = ucfirst(str_replace('_', ' ', $field)) . " must be at least {$rules['min_length']} characters";
            }
            
            if (isset($rules['max_length']) && strlen($value) > $rules['max_length']) {
                $errors[] = ucfirst(str_replace('_', ' ', $field)) . " must not exceed {$rules['max_length']} characters";
            }
            
            // Validate email format
            if (isset($rules['email']) && $rules['email'] && !validateEmail($value)) {
                $errors[] = "Invalid email format";
            }
            
            // Validate date format
            if (isset($rules['date']) && $rules['date'] && !validateDate($value)) {
                $errors[] = "Invalid date format";
            }
            
            // Validate role
            if (isset($rules['in_array']) && $rules['in_array'] && !in_array($value, $rules['in_array'])) {
                $errors[] = "Invalid role specified";
            }
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }
    
    /**
     * Sanitize user data for database insertion
     * 
     * @param array $data Raw user data
     * @return array Sanitized user data
     */
    private function sanitizeUserData($data) {
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
     * Check if username already exists
     * 
     * @param string $username Username to check
     * @param int|null $excludeId User ID to exclude from check
     * @return bool True if username exists, false otherwise
     */
    private function usernameExists($username, $excludeId = null) {
        $whereClause = "username = ?";
        $params = [$username];
        
        if ($excludeId) {
            $whereClause .= " AND id != ?";
            $params[] = $excludeId;
        }
        
        return $this->db->exists($this->table, $whereClause, $params);
    }
    
    /**
     * Check if email already exists
     * 
     * @param string $email Email to check
     * @param int|null $excludeId User ID to exclude from check
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
    
    /**
     * Log user activity
     * 
     * @param int $userId User ID
     * @param string $action Action performed
     * @param string $description Action description
     * @param array $additionalData Additional data to log
     */
    private function logActivity($userId, $action, $description, $additionalData = []) {
        try {
            $logData = [
                'user_id' => $userId,
                'action' => $action,
                'description' => $description,
                'ip_address' => getClientIP(),
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
                'additional_data' => json_encode($additionalData),
                'created_at' => date('Y-m-d H:i:s')
            ];
            
            // Assuming there's a user_activity_logs table
            if ($this->db->tableExists('user_activity_logs')) {
                $this->db->insert('user_activity_logs', $logData);
            }
            
        } catch (Exception $e) {
            logError("Failed to log user activity: " . $e->getMessage(), [
                'user_id' => $userId,
                'action' => $action
            ]);
        }
    }
}
?>