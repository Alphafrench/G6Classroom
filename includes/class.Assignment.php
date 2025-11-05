<?php
/**
 * Assignment Management Class
 * 
 * This class handles all assignment-related operations including CRUD (Create, Read, Update, Delete)
 * operations, submission management, grading, and file uploads with proper validation, 
 * error handling, and security measures.
 * 
 * @package AssignmentManager
 * @version 1.0
 * @author Development Team
 */

require_once 'class.Database.php';
require_once 'functions.php';

class Assignment {
    /**
     * Database instance
     * @var Database
     */
    private $db;
    
    /**
     * Assignment table name
     * @var string
     */
    private $table = 'assignments';
    
    /**
     * Submission table name
     * @var string
     */
    private $submissionTable = 'assignment_submissions';
    
    /**
     * Grade table name
     * @var string
     */
    private $gradeTable = 'assignment_grades';
    
    /**
     * Comments table name
     * @var string
     */
    private $commentsTable = 'assignment_comments';
    
    /**
     * Upload directory for assignment files
     * @var string
     */
    private $uploadDir = 'uploads/assignments/';
    
    /**
     * Allowed file types for uploads
     * @var array
     */
    private $allowedFileTypes = ['pdf', 'doc', 'docx', 'txt', 'zip', 'rar', 'jpg', 'jpeg', 'png', 'gif', 'html', 'css', 'js'];
    
    /**
     * Maximum file size (5MB)
     * @var int
     */
    private $maxFileSize = 5242880;
    
    /**
     * Validation rules for assignment data
     * @var array
     */
    private $validationRules = [
        'title' => ['required' => true, 'max_length' => 255],
        'description' => ['required' => true, 'max_length' => 2000],
        'instructions' => ['required' => false, 'max_length' => 5000],
        'assignment_type' => ['required' => true, 'enum' => ['homework', 'project', 'exam', 'quiz', 'essay', 'presentation']],
        'total_points' => ['required' => true, 'numeric' => true, 'min_value' => 0, 'max_value' => 999.99],
        'due_date' => ['required' => true, 'date' => true],
        'max_attempts' => ['required' => true, 'numeric' => true, 'min_value' => 1, 'max_value' => 10],
        'max_file_size' => ['required' => false, 'numeric' => true, 'min_value' => 1024, 'max_value' => 52428800] // 1KB to 50MB
    ];
    
    /**
     * Constructor
     * 
     * @param Database|null $db Database instance (optional)
     */
    public function __construct($db = null) {
        $this->db = $db ?: getDatabase();
        $this->initializeUploadDirectory();
    }
    
    /**
     * Initialize upload directory
     */
    private function initializeUploadDirectory() {
        $fullPath = __DIR__ . '/../' . $this->uploadDir;
        if (!is_dir($fullPath)) {
            mkdir($fullPath, 0755, true);
        }
    }
    
    /**
     * Create a new assignment
     * 
     * @param array $assignmentData Assignment data
     * @param int $teacherId Teacher ID who created the assignment
     * @return array Result with 'success', 'assignment_id', 'errors'
     * @throws Exception If validation fails
     */
    public function create($assignmentData, $teacherId) {
        try {
            // Validate input data
            $validationResult = $this->validateAssignmentData($assignmentData);
            if (!$validationResult['valid']) {
                return [
                    'success' => false,
                    'errors' => $validationResult['errors']
                ];
            }
            
            // Validate due date is in the future
            if (strtotime($assignmentData['due_date']) <= time()) {
                return [
                    'success' => false,
                    'errors' => ['Due date must be in the future']
                ];
            }
            
            // Sanitize input data
            $cleanData = $this->sanitizeAssignmentData($assignmentData);
            
            // Add teacher ID and timestamps
            $cleanData['teacher_id'] = $teacherId;
            $cleanData['created_at'] = date('Y-m-d H:i:s');
            $cleanData['updated_at'] = date('Y-m-d H:i:s');
            
            // Set default values
            $cleanData['allow_late_submission'] = $cleanData['allow_late_submission'] ?? false;
            $cleanData['late_penalty_per_day'] = $cleanData['late_penalty_per_day'] ?? 5.00;
            $cleanData['requires_file_upload'] = $cleanData['requires_file_upload'] ?? false;
            $cleanData['is_active'] = $cleanData['is_active'] ?? true;
            $cleanData['max_file_size'] = $cleanData['max_file_size'] ?? $this->maxFileSize;
            
            // Convert allowed file types to JSON if provided
            if (isset($cleanData['allowed_file_types']) && is_array($cleanData['allowed_file_types'])) {
                $cleanData['allowed_file_types'] = json_encode($cleanData['allowed_file_types']);
            }
            
            // Insert assignment record
            $assignmentId = $this->db->insert($this->table, $cleanData);
            
            logError("Assignment created successfully", [
                'assignment_id' => $assignmentId, 
                'teacher_id' => $teacherId,
                'title' => $assignmentData['title']
            ]);
            
            return [
                'success' => true,
                'assignment_id' => $assignmentId,
                'message' => 'Assignment created successfully'
            ];
            
        } catch (Exception $e) {
            logError("Failed to create assignment: " . $e->getMessage(), [
                'teacher_id' => $teacherId, 
                'data' => $assignmentData
            ]);
            return [
                'success' => false,
                'errors' => ['Failed to create assignment: ' . $e->getMessage()]
            ];
        }
    }
    
    /**
     * Get assignment by ID
     * 
     * @param int $assignmentId Assignment ID
     * @return array|null Assignment data or null if not found
     */
    public function getById($assignmentId) {
        try {
            $sql = "SELECT a.*, u.username as teacher_name, u.email as teacher_email
                   FROM {$this->table} a 
                   LEFT JOIN users u ON a.teacher_id = u.id 
                   WHERE a.id = ?";
            
            $assignment = $this->db->fetchRow($sql, [$assignmentId]);
            
            if ($assignment) {
                // Decode allowed file types
                if ($assignment['allowed_file_types']) {
                    $assignment['allowed_file_types'] = json_decode($assignment['allowed_file_types'], true);
                }
                
                // Format dates for display
                $assignment['formatted_due_date'] = formatDateTime($assignment['due_date']);
                $assignment['formatted_created_at'] = formatDateTime($assignment['created_at']);
                $assignment['formatted_updated_at'] = formatDateTime($assignment['updated_at']);
                
                // Calculate time remaining
                $assignment['time_remaining'] = $this->calculateTimeRemaining($assignment['due_date']);
                $assignment['is_overdue'] = time() > strtotime($assignment['due_date']);
            }
            
            return $assignment;
            
        } catch (Exception $e) {
            logError("Failed to get assignment by ID: " . $e->getMessage(), ['assignment_id' => $assignmentId]);
            return null;
        }
    }
    
    /**
     * Get all assignments with pagination and filtering
     * 
     * @param array $filters Optional filters (teacher_id, assignment_type, is_active, search_term)
     * @param int $limit Number of records per page
     * @param int $offset Offset for pagination
     * @param string $sortBy Column to sort by
     * @param string $sortOrder Sort order (ASC/DESC)
     * @return array Result with 'assignments', 'total', 'pagination'
     */
    public function getAll($filters = [], $limit = 20, $offset = 0, $sortBy = 'due_date', $sortOrder = 'ASC') {
        try {
            $whereConditions = ['1'];
            $params = [];
            
            // Build WHERE clause based on filters
            if (!empty($filters['teacher_id'])) {
                $whereConditions[] = 'a.teacher_id = ?';
                $params[] = $filters['teacher_id'];
            }
            
            if (isset($filters['is_active'])) {
                $whereConditions[] = 'a.is_active = ?';
                $params[] = $filters['is_active'] ? 1 : 0;
            }
            
            if (!empty($filters['assignment_type'])) {
                $whereConditions[] = 'a.assignment_type = ?';
                $params[] = $filters['assignment_type'];
            }
            
            if (!empty($filters['search_term'])) {
                $whereConditions[] = '(a.title LIKE ? OR a.description LIKE ?)';
                $searchTerm = '%' . $filters['search_term'] . '%';
                $params[] = $searchTerm;
                $params[] = $searchTerm;
            }
            
            // Only show active assignments by default for students
            if (isset($filters['student_view']) && $filters['student_view']) {
                $whereConditions[] = 'a.is_active = 1';
                $whereConditions[] = 'a.due_date >= NOW()';
            }
            
            $whereClause = implode(' AND ', $whereConditions);
            
            // Validate sort parameters
            $allowedSortColumns = ['title', 'assignment_type', 'total_points', 'due_date', 'created_at', 'teacher_id'];
            if (!in_array($sortBy, $allowedSortColumns)) {
                $sortBy = 'due_date';
            }
            
            $sortOrder = strtoupper($sortOrder) === 'DESC' ? 'DESC' : 'ASC';
            
            // Get total count
            $countSql = "SELECT COUNT(*) as total 
                        FROM {$this->table} a 
                        WHERE {$whereClause}";
            $totalResult = $this->db->fetchRow($countSql, $params);
            $total = (int) $totalResult['total'];
            
            // Get assignments
            $sql = "SELECT a.*, u.username as teacher_name,
                           COUNT(DISTINCT s.id) as submission_count,
                           COUNT(DISTINCT CASE WHEN s.status = 'graded' THEN s.id END) as graded_count
                   FROM {$this->table} a 
                   LEFT JOIN users u ON a.teacher_id = u.id
                   LEFT JOIN assignment_submissions s ON a.id = s.assignment_id
                   WHERE {$whereClause} 
                   GROUP BY a.id
                   ORDER BY a.{$sortBy} {$sortOrder} 
                   LIMIT ? OFFSET ?";
            
            $params[] = $limit;
            $params[] = $offset;
            
            $assignments = $this->db->fetchAll($sql, $params);
            
            // Format dates and calculate additional data
            foreach ($assignments as &$assignment) {
                // Decode allowed file types
                if ($assignment['allowed_file_types']) {
                    $assignment['allowed_file_types'] = json_decode($assignment['allowed_file_types'], true);
                }
                
                $assignment['formatted_due_date'] = formatDateTime($assignment['due_date']);
                $assignment['formatted_created_at'] = formatDateTime($assignment['created_at']);
                $assignment['time_remaining'] = $this->calculateTimeRemaining($assignment['due_date']);
                $assignment['is_overdue'] = time() > strtotime($assignment['due_date']);
                $assignment['completion_rate'] = $assignment['submission_count'] > 0 ? 
                    round(($assignment['graded_count'] / $assignment['submission_count']) * 100, 1) : 0;
            }
            
            $totalPages = ceil($total / $limit);
            
            return [
                'assignments' => $assignments,
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
            logError("Failed to get all assignments: " . $e->getMessage(), ['filters' => $filters]);
            return [
                'assignments' => [],
                'total' => 0,
                'pagination' => []
            ];
        }
    }
    
    /**
     * Update assignment information
     * 
     * @param int $assignmentId Assignment ID
     * @param array $assignmentData Updated assignment data
     * @param int $teacherId Teacher ID making the update
     * @return array Result with 'success', 'message', 'errors'
     */
    public function update($assignmentId, $assignmentData, $teacherId) {
        try {
            // Check if assignment exists and belongs to teacher
            $existingAssignment = $this->getById($assignmentId);
            if (!$existingAssignment) {
                return [
                    'success' => false,
                    'errors' => ['Assignment not found']
                ];
            }
            
            if ($existingAssignment['teacher_id'] != $teacherId) {
                return [
                    'success' => false,
                    'errors' => ['You do not have permission to update this assignment']
                ];
            }
            
            // Validate input data
            $validationResult = $this->validateAssignmentData($assignmentData, $assignmentId);
            if (!$validationResult['valid']) {
                return [
                    'success' => false,
                    'errors' => $validationResult['errors']
                ];
            }
            
            // Sanitize input data
            $cleanData = $this->sanitizeAssignmentData($assignmentData);
            
            // Add update timestamp
            $cleanData['updated_at'] = date('Y-m-d H:i:s');
            
            // Convert allowed file types to JSON if provided
            if (isset($cleanData['allowed_file_types']) && is_array($cleanData['allowed_file_types'])) {
                $cleanData['allowed_file_types'] = json_encode($cleanData['allowed_file_types']);
            }
            
            // Remove fields that shouldn't be updated
            unset($cleanData['id']);
            unset($cleanData['created_at']);
            unset($cleanData['teacher_id']);
            
            // Update assignment record
            $affectedRows = $this->db->update($this->table, $cleanData, 'id = ?', [$assignmentId]);
            
            if ($affectedRows > 0) {
                logError("Assignment updated successfully", [
                    'assignment_id' => $assignmentId,
                    'teacher_id' => $teacherId
                ]);
                
                return [
                    'success' => true,
                    'message' => 'Assignment updated successfully',
                    'affected_rows' => $affectedRows
                ];
            } else {
                return [
                    'success' => false,
                    'errors' => ['No changes were made']
                ];
            }
            
        } catch (Exception $e) {
            logError("Failed to update assignment: " . $e->getMessage(), [
                'assignment_id' => $assignmentId,
                'teacher_id' => $teacherId,
                'data' => $assignmentData
            ]);
            return [
                'success' => false,
                'errors' => ['Failed to update assignment: ' . $e->getMessage()]
            ];
        }
    }
    
    /**
     * Delete assignment
     * 
     * @param int $assignmentId Assignment ID
     * @param int $teacherId Teacher ID making the deletion
     * @param bool $forceDelete Whether to perform hard delete (default: false)
     * @return array Result with 'success', 'message', 'errors'
     */
    public function delete($assignmentId, $teacherId, $forceDelete = false) {
        try {
            // Check if assignment exists and belongs to teacher
            $assignment = $this->getById($assignmentId);
            if (!$assignment) {
                return [
                    'success' => false,
                    'errors' => ['Assignment not found']
                ];
            }
            
            if ($assignment['teacher_id'] != $teacherId) {
                return [
                    'success' => false,
                    'errors' => ['You do not have permission to delete this assignment']
                ];
            }
            
            // Check if assignment has submissions
            $submissionCount = $this->db->count($this->submissionTable, 'assignment_id = ?', [$assignmentId]);
            if ($submissionCount > 0 && !$forceDelete) {
                return [
                    'success' => false,
                    'errors' => ['Cannot delete assignment with existing submissions. Use force delete to override.']
                ];
            }
            
            if ($forceDelete) {
                // Hard delete - remove from database
                $result = $this->db->delete($this->table, 'id = ?', [$assignmentId]);
            } else {
                // Soft delete - mark as inactive
                $result = $this->db->update(
                    $this->table, 
                    ['is_active' => 0, 'updated_at' => date('Y-m-d H:i:s')], 
                    'id = ?', 
                    [$assignmentId]
                );
            }
            
            if ($result > 0) {
                logError("Assignment deleted successfully", [
                    'assignment_id' => $assignmentId,
                    'teacher_id' => $teacherId,
                    'force_delete' => $forceDelete
                ]);
                
                return [
                    'success' => true,
                    'message' => 'Assignment deleted successfully',
                    'force_delete' => $forceDelete
                ];
            } else {
                return [
                    'success' => false,
                    'errors' => ['Failed to delete assignment']
                ];
            }
            
        } catch (Exception $e) {
            logError("Failed to delete assignment: " . $e->getMessage(), [
                'assignment_id' => $assignmentId,
                'teacher_id' => $teacherId
            ]);
            return [
                'success' => false,
                'errors' => ['Failed to delete assignment: ' . $e->getMessage()]
            ];
        }
    }
    
    /**
     * Submit an assignment
     * 
     * @param int $assignmentId Assignment ID
     * @param int $studentId Student ID
     * @param array $submissionData Submission data
     * @param array|null $uploadedFile Uploaded file data (optional)
     * @return array Result with 'success', 'submission_id', 'errors'
     */
    public function submitAssignment($assignmentId, $studentId, $submissionData, $uploadedFile = null) {
        try {
            // Check if assignment exists and is active
            $assignment = $this->getById($assignmentId);
            if (!$assignment || !$assignment['is_active']) {
                return [
                    'success' => false,
                    'errors' => ['Assignment not found or inactive']
                ];
            }
            
            // Check if submission is within allowed attempts
            $currentAttempts = $this->db->count(
                $this->submissionTable, 
                'assignment_id = ? AND student_id = ?', 
                [$assignmentId, $studentId]
            );
            
            if ($currentAttempts >= $assignment['max_attempts']) {
                return [
                    'success' => false,
                    'errors' => ['Maximum attempts exceeded for this assignment']
                ];
            }
            
            // Check if late submission is allowed
            $isLate = time() > strtotime($assignment['due_date']);
            if ($isLate && !$assignment['allow_late_submission']) {
                return [
                    'success' => false,
                    'errors' => ['Late submissions are not allowed for this assignment']
                ];
            }
            
            // Handle file upload if required
            $filePath = null;
            $originalFilename = null;
            $fileSize = null;
            $fileType = null;
            
            if ($assignment['requires_file_upload']) {
                if (!$uploadedFile || $uploadedFile['error'] !== UPLOAD_ERR_OK) {
                    return [
                        'success' => false,
                        'errors' => ['File upload is required for this assignment']
                    ];
                }
                
                // Validate file
                $fileValidation = $this->validateUploadedFile($uploadedFile, $assignment);
                if (!$fileValidation['valid']) {
                    return [
                        'success' => false,
                        'errors' => $fileValidation['errors']
                    ];
                }
                
                // Move uploaded file
                $uploadResult = $this->moveUploadedFile($uploadedFile, $assignmentId, $studentId);
                if (!$uploadResult['success']) {
                    return [
                        'success' => false,
                        'errors' => $uploadResult['errors']
                    ];
                }
                
                $filePath = $uploadResult['file_path'];
                $originalFilename = $uploadedFile['name'];
                $fileSize = $uploadedFile['size'];
                $fileType = $uploadedFile['type'];
            }
            
            // Validate submission text
            if (empty(trim($submissionData['submission_text'] ?? '')) && !$assignment['requires_file_upload']) {
                return [
                    'success' => false,
                    'errors' => ['Submission text is required']
                ];
            }
            
            // Prepare submission data
            $submissionDataClean = [
                'assignment_id' => $assignmentId,
                'student_id' => $studentId,
                'submission_text' => sanitizeInput($submissionData['submission_text'] ?? ''),
                'submission_file_path' => $filePath,
                'original_filename' => $originalFilename,
                'file_size' => $fileSize,
                'file_type' => $fileType,
                'attempt_number' => $currentAttempts + 1,
                'submitted_at' => date('Y-m-d H:i:s'),
                'status' => 'submitted',
                'ip_address' => getClientIP(),
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? ''
            ];
            
            // Insert submission
            $submissionId = $this->db->insert($this->submissionTable, $submissionDataClean);
            
            logError("Assignment submitted successfully", [
                'submission_id' => $submissionId,
                'assignment_id' => $assignmentId,
                'student_id' => $studentId,
                'attempt_number' => $submissionDataClean['attempt_number']
            ]);
            
            return [
                'success' => true,
                'submission_id' => $submissionId,
                'message' => 'Assignment submitted successfully',
                'is_late' => $isLate
            ];
            
        } catch (Exception $e) {
            logError("Failed to submit assignment: " . $e->getMessage(), [
                'assignment_id' => $assignmentId,
                'student_id' => $studentId
            ]);
            return [
                'success' => false,
                'errors' => ['Failed to submit assignment: ' . $e->getMessage()]
            ];
        }
    }
    
    /**
     * Get student submission for an assignment
     * 
     * @param int $assignmentId Assignment ID
     * @param int $studentId Student ID
     * @return array|null Submission data or null if not found
     */
    public function getStudentSubmission($assignmentId, $studentId) {
        try {
            $sql = "SELECT s.*, a.title as assignment_title, a.due_date, a.total_points,
                           g.score, g.max_score, g.percentage, g.letter_grade, g.feedback, g.graded_at,
                           teacher.username as graded_by_name
                   FROM {$this->submissionTable} s
                   JOIN assignments a ON s.assignment_id = a.id
                   LEFT JOIN assignment_grades g ON s.id = g.submission_id
                   LEFT JOIN users teacher ON g.graded_by = teacher.id
                   WHERE s.assignment_id = ? AND s.student_id = ?
                   ORDER BY s.attempt_number DESC
                   LIMIT 1";
            
            $submission = $this->db->fetchRow($sql, [$assignmentId, $studentId]);
            
            if ($submission) {
                $submission['formatted_submitted_at'] = formatDateTime($submission['submitted_at']);
                $submission['formatted_graded_at'] = formatDateTime($submission['graded_at']);
                
                // Calculate time remaining for submission
                $submission['time_remaining'] = $this->calculateTimeRemaining($submission['due_date']);
                $submission['is_overdue'] = time() > strtotime($submission['due_date']);
            }
            
            return $submission;
            
        } catch (Exception $e) {
            logError("Failed to get student submission: " . $e->getMessage(), [
                'assignment_id' => $assignmentId,
                'student_id' => $studentId
            ]);
            return null;
        }
    }
    
    /**
     * Grade a submission
     * 
     * @param int $submissionId Submission ID
     * @param int $teacherId Teacher ID grading the submission
     * @param float $score Score awarded
     * @param string $feedback Teacher feedback
     * @return array Result with 'success', 'grade_id', 'errors'
     */
    public function gradeSubmission($submissionId, $teacherId, $score, $feedback = '') {
        try {
            // Get submission details
            $submission = $this->db->fetchRow(
                "SELECT s.*, a.total_points, a.teacher_id 
                 FROM {$this->submissionTable} s 
                 JOIN assignments a ON s.assignment_id = a.id 
                 WHERE s.id = ?", 
                [$submissionId]
            );
            
            if (!$submission) {
                return [
                    'success' => false,
                    'errors' => ['Submission not found']
                ];
            }
            
            // Check if teacher owns the assignment
            if ($submission['teacher_id'] != $teacherId) {
                return [
                    'success' => false,
                    'errors' => ['You do not have permission to grade this submission']
                ];
            }
            
            // Validate score
            if ($score < 0 || $score > $submission['total_points']) {
                return [
                    'success' => false,
                    'errors' => ["Score must be between 0 and {$submission['total_points']}"]
                ];
            }
            
            // Calculate percentage
            $percentage = ($score / $submission['total_points']) * 100;
            $letterGrade = $this->calculateLetterGrade($percentage);
            
            // Prepare grade data
            $gradeData = [
                'submission_id' => $submissionId,
                'assignment_id' => $submission['assignment_id'],
                'student_id' => $submission['student_id'],
                'teacher_id' => $teacherId,
                'score' => $score,
                'max_score' => $submission['total_points'],
                'percentage' => $percentage,
                'letter_grade' => $letterGrade,
                'feedback' => sanitizeInput($feedback),
                'graded_at' => date('Y-m-d H:i:s'),
                'graded_by' => $teacherId
            ];
            
            // Check if grade already exists
            $existingGrade = $this->db->fetchRow(
                "SELECT id FROM {$this->gradeTable} WHERE submission_id = ?",
                [$submissionId]
            );
            
            if ($existingGrade) {
                // Update existing grade
                $gradeId = $existingGrade['id'];
                $this->db->update($this->gradeTable, $gradeData, 'id = ?', [$gradeId]);
            } else {
                // Insert new grade
                $gradeId = $this->db->insert($this->gradeTable, $gradeData);
            }
            
            // Update submission status
            $this->db->update(
                $this->submissionTable, 
                ['status' => 'graded'], 
                'id = ?', 
                [$submissionId]
            );
            
            logError("Submission graded successfully", [
                'submission_id' => $submissionId,
                'grade_id' => $gradeId,
                'teacher_id' => $teacherId,
                'score' => $score,
                'percentage' => $percentage
            ]);
            
            return [
                'success' => true,
                'grade_id' => $gradeId,
                'percentage' => $percentage,
                'letter_grade' => $letterGrade,
                'message' => 'Submission graded successfully'
            ];
            
        } catch (Exception $e) {
            logError("Failed to grade submission: " . $e->getMessage(), [
                'submission_id' => $submissionId,
                'teacher_id' => $teacherId,
                'score' => $score
            ]);
            return [
                'success' => false,
                'errors' => ['Failed to grade submission: ' . $e->getMessage()]
            ];
        }
    }
    
    /**
     * Get assignment statistics
     * 
     * @param int $assignmentId Assignment ID
     * @return array Assignment statistics
     */
    public function getStatistics($assignmentId) {
        try {
            // Call stored procedure to get comprehensive statistics
            $stats = $this->db->fetchRow("CALL GetAssignmentStats(?)", [$assignmentId]);
            
            if ($stats) {
                // Calculate additional metrics
                $stats['submission_rate'] = $stats['total_submissions'] > 0 ? 
                    round(($stats['graded_submissions'] / $stats['total_submissions']) * 100, 1) : 0;
                
                $stats['on_time_submissions'] = $stats['total_submissions'] - $stats['late_submissions'];
                $stats['on_time_rate'] = $stats['total_submissions'] > 0 ? 
                    round((($stats['total_submissions'] - $stats['late_submissions']) / $stats['total_submissions']) * 100, 1) : 0;
            }
            
            return $stats ?: [];
            
        } catch (Exception $e) {
            logError("Failed to get assignment statistics: " . $e->getMessage(), ['assignment_id' => $assignmentId]);
            return [];
        }
    }
    
    /**
     * Validate assignment data
     * 
     * @param array $data Assignment data to validate
     * @param int|null $excludeId Assignment ID to exclude from checks
     * @return array Validation result with 'valid' boolean and 'errors' array
     */
    private function validateAssignmentData($data, $excludeId = null) {
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
            
            // Validate enum values
            if (isset($rules['enum']) && !in_array($value, $rules['enum'])) {
                $errors[] = "Invalid " . str_replace('_', ' ', $field) . " value";
            }
            
            // Validate numeric values
            if (isset($rules['numeric']) && $rules['numeric'] && !is_numeric($value)) {
                $errors[] = ucfirst(str_replace('_', ' ', $field)) . " must be a number";
            }
            
            // Validate minimum value
            if (isset($rules['min_value']) && $value < $rules['min_value']) {
                $errors[] = ucfirst(str_replace('_', ' ', $field)) . " must be at least {$rules['min_value']}";
            }
            
            // Validate maximum value
            if (isset($rules['max_value']) && $value > $rules['max_value']) {
                $errors[] = ucfirst(str_replace('_', ' ', $field)) . " must not exceed {$rules['max_value']}";
            }
            
            // Validate date format
            if (isset($rules['date']) && $rules['date'] && !validateDate($value)) {
                $errors[] = "Invalid date format for " . str_replace('_', ' ', $field);
            }
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }
    
    /**
     * Sanitize assignment data for database insertion
     * 
     * @param array $data Raw assignment data
     * @return array Sanitized assignment data
     */
    private function sanitizeAssignmentData($data) {
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
     * Validate uploaded file
     * 
     * @param array $uploadedFile Uploaded file data
     * @param array $assignment Assignment data
     * @return array Validation result
     */
    private function validateUploadedFile($uploadedFile, $assignment) {
        $errors = [];
        
        // Check for upload errors
        if ($uploadedFile['error'] !== UPLOAD_ERR_OK) {
            $errors[] = 'File upload error: ' . $this->getUploadErrorMessage($uploadedFile['error']);
            return ['valid' => false, 'errors' => $errors];
        }
        
        // Check file size
        if ($uploadedFile['size'] > $assignment['max_file_size']) {
            $maxSizeMB = round($assignment['max_file_size'] / 1024 / 1024, 1);
            $errors[] = "File size exceeds maximum allowed size of {$maxSizeMB}MB";
        }
        
        // Check file type
        $fileExtension = strtolower(pathinfo($uploadedFile['name'], PATHINFO_EXTENSION));
        
        if ($assignment['allowed_file_types']) {
            $allowedTypes = is_string($assignment['allowed_file_types']) ? 
                json_decode($assignment['allowed_file_types'], true) : 
                $assignment['allowed_file_types'];
            
            if (!in_array($fileExtension, $allowedTypes)) {
                $errors[] = "File type '{$fileExtension}' is not allowed";
            }
        } else {
            // Use default allowed types
            if (!in_array($fileExtension, $this->allowedFileTypes)) {
                $errors[] = "File type '{$fileExtension}' is not allowed";
            }
        }
        
        // Additional security checks
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $uploadedFile['tmp_name']);
        finfo_close($finfo);
        
        // Check for executable files
        $dangerousExtensions = ['exe', 'bat', 'cmd', 'com', 'pif', 'scr', 'vbs', 'js'];
        if (in_array($fileExtension, $dangerousExtensions)) {
            $errors[] = "Executable files are not allowed";
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }
    
    /**
     * Move uploaded file to secure location
     * 
     * @param array $uploadedFile Uploaded file data
     * @param int $assignmentId Assignment ID
     * @param int $studentId Student ID
     * @return array Result with file path or errors
     */
    private function moveUploadedFile($uploadedFile, $assignmentId, $studentId) {
        try {
            $fileExtension = strtolower(pathinfo($uploadedFile['name'], PATHINFO_EXTENSION));
            $newFilename = "assignment_{$assignmentId}_student_{$studentId}_" . time() . '.' . $fileExtension;
            $assignmentDir = $this->uploadDir . "assignment_{$assignmentId}/";
            $fullDirPath = __DIR__ . '/../' . $assignmentDir;
            
            // Create assignment directory if it doesn't exist
            if (!is_dir($fullDirPath)) {
                mkdir($fullDirPath, 0755, true);
            }
            
            $destinationPath = $fullDirPath . $newFilename;
            
            if (move_uploaded_file($uploadedFile['tmp_name'], $destinationPath)) {
                return [
                    'success' => true,
                    'file_path' => $assignmentDir . $newFilename
                ];
            } else {
                return [
                    'success' => false,
                    'errors' => ['Failed to save uploaded file']
                ];
            }
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'errors' => ['File upload failed: ' . $e->getMessage()]
            ];
        }
    }
    
    /**
     * Calculate time remaining until due date
     * 
     * @param string $dueDate Due date
     * @return array Time remaining data
     */
    private function calculateTimeRemaining($dueDate) {
        $now = time();
        $due = strtotime($dueDate);
        $difference = $due - $now;
        
        if ($difference <= 0) {
            return [
                'overdue' => true,
                'days' => 0,
                'hours' => 0,
                'minutes' => 0,
                'seconds' => 0,
                'formatted' => 'Overdue'
            ];
        }
        
        $days = floor($difference / (24 * 60 * 60));
        $hours = floor(($difference % (24 * 60 * 60)) / (60 * 60));
        $minutes = floor(($difference % (60 * 60)) / 60);
        $seconds = $difference % 60;
        
        $formatted = '';
        if ($days > 0) {
            $formatted = "{$days} day" . ($days != 1 ? 's' : '');
        } elseif ($hours > 0) {
            $formatted = "{$hours} hour" . ($hours != 1 ? 's' : '');
        } elseif ($minutes > 0) {
            $formatted = "{$minutes} minute" . ($minutes != 1 ? 's' : '');
        } else {
            $formatted = "{$seconds} second" . ($seconds != 1 ? 's' : '');
        }
        
        return [
            'overdue' => false,
            'days' => $days,
            'hours' => $hours,
            'minutes' => $minutes,
            'seconds' => $seconds,
            'formatted' => $formatted
        ];
    }
    
    /**
     * Calculate letter grade from percentage
     * 
     * @param float $percentage Score percentage
     * @return string Letter grade
     */
    private function calculateLetterGrade($percentage) {
        if ($percentage >= 90) return 'A';
        if ($percentage >= 80) return 'B';
        if ($percentage >= 70) return 'C';
        if ($percentage >= 60) return 'D';
        return 'F';
    }
    
    /**
     * Get upload error message
     * 
     * @param int $errorCode Upload error code
     * @return string Error message
     */
    private function getUploadErrorMessage($errorCode) {
        $errors = [
            UPLOAD_ERR_INI_SIZE => 'File exceeds upload_max_filesize directive',
            UPLOAD_ERR_FORM_SIZE => 'File exceeds MAX_FILE_SIZE directive',
            UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
            UPLOAD_ERR_NO_FILE => 'No file was uploaded',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
            UPLOAD_ERR_EXTENSION => 'File upload stopped by extension'
        ];
        
        return $errors[$errorCode] ?? 'Unknown upload error';
    }
}
?>