<?php
/**
 * Course Management Class
 * 
 * This class handles course operations including CRUD operations, enrollment management,
 * schedule management, and course-related reporting with comprehensive validation
 * and security measures.
 * 
 * @package AttendanceSystem
 * @version 1.0
 * @author Development Team
 */

require_once 'class.Database.php';
require_once 'class.User.php';
require_once 'functions.php';

class Course {
    /**
     * Database instance
     * @var Database
     */
    private $db;
    
    /**
     * Courses table name
     * @var string
     */
    private $table = 'courses';
    
    /**
     * Course enrollments table name
     * @var string
     */
    private $enrollmentsTable = 'course_enrollments';
    
    /**
     * Course schedules table name
     * @var string
     */
    private $schedulesTable = 'course_schedules';
    
    /**
     * Validation rules for course data
     * @var array
     */
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
    
    /**
     * Constructor
     * 
     * @param Database|null $db Database instance (optional)
     */
    public function __construct($db = null) {
        $this->db = $db ?: getDatabase();
    }
    
    /**
     * Create a new course
     * 
     * @param array $courseData Course data
     * @param int|null $createdBy User ID who created the course
     * @return array Result with 'success', 'course_id', 'errors'
     * @throws Exception If validation fails
     */
    public function create($courseData, $createdBy = null) {
        try {
            // Validate input data
            $validationResult = $this->validateCourseData($courseData);
            if (!$validationResult['valid']) {
                return [
                    'success' => false,
                    'errors' => $validationResult['errors']
                ];
            }
            
            // Check for duplicate course code
            if ($this->courseCodeExists($courseData['code'])) {
                return [
                    'success' => false,
                    'errors' => ['Course code already exists']
                ];
            }
            
            // Validate dates
            if (strtotime($courseData['end_date']) <= strtotime($courseData['start_date'])) {
                return [
                    'success' => false,
                    'errors' => ['End date must be after start date']
                ];
            }
            
            // Sanitize input data
            $cleanData = $this->sanitizeCourseData($courseData);
            
            // Add creation timestamps
            $cleanData['created_at'] = date('Y-m-d H:i:s');
            $cleanData['updated_at'] = date('Y-m-d H:i:s');
            $cleanData['created_by'] = $createdBy;
            
            // Set default values
            $cleanData['status'] = $cleanData['status'] ?? 'active';
            $cleanData['enrolled_students'] = 0;
            
            // Insert course record
            $courseId = $this->db->insert($this->table, $cleanData);
            
            // Log the creation
            if ($createdBy) {
                $this->logActivity($courseId, 'course_created', "Course created by user ID: {$createdBy}");
            }
            
            logError("Course created successfully", [
                'course_id' => $courseId, 
                'course_code' => $courseData['code'],
                'course_name' => $courseData['name'],
                'created_by' => $createdBy
            ]);
            
            return [
                'success' => true,
                'course_id' => $courseId,
                'message' => 'Course created successfully'
            ];
            
        } catch (Exception $e) {
            logError("Failed to create course: " . $e->getMessage(), [
                'course_data' => $courseData, 
                'created_by' => $createdBy
            ]);
            return [
                'success' => false,
                'errors' => ['Failed to create course: ' . $e->getMessage()]
            ];
        }
    }
    
    /**
     * Get course by ID
     * 
     * @param int $courseId Course ID
     * @return array|null Course data or null if not found
     */
    public function getById($courseId) {
        try {
            $sql = "SELECT c.*, u.first_name as creator_first_name, u.last_name as creator_last_name,
                           COUNT(DISTINCT e.student_id) as current_enrolled,
                           COUNT(DISTINCT s.id) as total_sessions
                   FROM {$this->table} c
                   LEFT JOIN users u ON c.created_by = u.id
                   LEFT JOIN {$this->enrollmentsTable} e ON c.id = e.course_id AND e.status = 'active'
                   LEFT JOIN {$this->schedulesTable} s ON c.id = s.course_id
                   WHERE c.id = ?
                   GROUP BY c.id";
            
            $course = $this->db->fetchRow($sql, [$courseId]);
            
            if ($course) {
                // Format dates for display
                $course['formatted_start_date'] = formatDate($course['start_date']);
                $course['formatted_end_date'] = formatDate($course['end_date']);
                $course['formatted_created_at'] = formatDateTime($course['created_at']);
                $course['formatted_updated_at'] = formatDateTime($course['updated_at']);
                
                if ($course['creator_first_name'] && $course['creator_last_name']) {
                    $course['creator_name'] = $course['creator_first_name'] . ' ' . $course['creator_last_name'];
                }
                
                // Check if course is full
                $course['is_full'] = $course['capacity'] && $course['current_enrolled'] >= $course['capacity'];
                $course['available_slots'] = $course['capacity'] ? max(0, $course['capacity'] - $course['current_enrolled']) : null;
            }
            
            return $course;
            
        } catch (Exception $e) {
            logError("Failed to get course by ID: " . $e->getMessage(), ['course_id' => $courseId]);
            return null;
        }
    }
    
    /**
     * Get course by code
     * 
     * @param string $courseCode Course code
     * @return array|null Course data or null if not found
     */
    public function getByCode($courseCode) {
        try {
            $sql = "SELECT * FROM {$this->table} WHERE code = ?";
            return $this->db->fetchRow($sql, [$courseCode]);
            
        } catch (Exception $e) {
            logError("Failed to get course by code: " . $e->getMessage(), ['course_code' => $courseCode]);
            return null;
        }
    }
    
    /**
     * Get all courses with pagination and filtering
     * 
     * @param array $filters Optional filters (status, semester, academic_year, teacher_id, search_term)
     * @param int $limit Number of records per page
     * @param int $offset Offset for pagination
     * @param string $sortBy Column to sort by
     * @param string $sortOrder Sort order (ASC/DESC)
     * @return array Result with 'courses', 'total', 'pagination'
     */
    public function getAll($filters = [], $limit = 20, $offset = 0, $sortBy = 'created_at', $sortOrder = 'DESC') {
        try {
            $whereConditions = [];
            $params = [];
            
            // Build WHERE clause based on filters
            if (!empty($filters['status']) && in_array($filters['status'], ['active', 'inactive', 'completed', 'cancelled'])) {
                $whereConditions[] = 'c.status = ?';
                $params[] = $filters['status'];
            }
            
            if (!empty($filters['semester'])) {
                $whereConditions[] = 'c.semester = ?';
                $params[] = $filters['semester'];
            }
            
            if (!empty($filters['academic_year'])) {
                $whereConditions[] = 'c.academic_year = ?';
                $params[] = $filters['academic_year'];
            }
            
            if (!empty($filters['teacher_id'])) {
                $whereConditions[] = 'c.teacher_id = ?';
                $params[] = $filters['teacher_id'];
            }
            
            if (!empty($filters['search_term'])) {
                $whereConditions[] = '(c.name LIKE ? OR c.code LIKE ? OR c.description LIKE ?)';
                $searchTerm = '%' . $filters['search_term'] . '%';
                $params[] = $searchTerm;
                $params[] = $searchTerm;
                $params[] = $searchTerm;
            }
            
            $whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';
            
            // Validate sort parameters
            $allowedSortColumns = ['name', 'code', 'start_date', 'end_date', 'semester', 'academic_year', 'created_at', 'enrolled_students'];
            if (!in_array($sortBy, $allowedSortColumns)) {
                $sortBy = 'created_at';
            }
            
            $sortOrder = strtoupper($sortOrder) === 'DESC' ? 'DESC' : 'ASC';
            
            // Get total count
            $countSql = "SELECT COUNT(*) as total 
                        FROM {$this->table} c 
                        LEFT JOIN users u ON c.teacher_id = u.id 
                        {$whereClause}";
            $totalResult = $this->db->fetchRow($countSql, $params);
            $total = (int) $totalResult['total'];
            
            // Get courses
            $sql = "SELECT c.*, 
                           CONCAT(u.first_name, ' ', u.last_name) as teacher_name,
                           COUNT(DISTINCT e.student_id) as current_enrolled
                   FROM {$this->table} c
                   LEFT JOIN users u ON c.teacher_id = u.id
                   LEFT JOIN {$this->enrollmentsTable} e ON c.id = e.course_id AND e.status = 'active'
                   {$whereClause} 
                   GROUP BY c.id 
                   ORDER BY c.{$sortBy} {$sortOrder} 
                   LIMIT ? OFFSET ?";
            
            $params[] = $limit;
            $params[] = $offset;
            
            $courses = $this->db->fetchAll($sql, $params);
            
            // Format dates and add additional data
            foreach ($courses as &$course) {
                $course['formatted_start_date'] = formatDate($course['start_date']);
                $course['formatted_end_date'] = formatDate($course['end_date']);
                $course['formatted_created_at'] = formatDateTime($course['created_at']);
                $course['formatted_updated_at'] = formatDateTime($course['updated_at']);
                
                // Check if course is full
                $course['is_full'] = $course['capacity'] && $course['current_enrolled'] >= $course['capacity'];
                $course['available_slots'] = $course['capacity'] ? max(0, $course['capacity'] - $course['current_enrolled']) : null;
            }
            
            $totalPages = ceil($total / $limit);
            
            return [
                'courses' => $courses,
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
            logError("Failed to get all courses: " . $e->getMessage(), ['filters' => $filters]);
            return [
                'courses' => [],
                'total' => 0,
                'pagination' => []
            ];
        }
    }
    
    /**
     * Update course information
     * 
     * @param int $courseId Course ID
     * @param array $courseData Updated course data
     * @param int|null $updatedBy User ID who made the update
     * @return array Result with 'success', 'message', 'errors'
     */
    public function update($courseId, $courseData, $updatedBy = null) {
        try {
            // Check if course exists
            $existingCourse = $this->getById($courseId);
            if (!$existingCourse) {
                return [
                    'success' => false,
                    'errors' => ['Course not found']
                ];
            }
            
            // Validate input data
            $validationResult = $this->validateCourseData($courseData, $courseId);
            if (!$validationResult['valid']) {
                return [
                    'success' => false,
                    'errors' => $validationResult['errors']
                ];
            }
            
            // Check for duplicate course code (excluding current course)
            if (isset($courseData['code']) && $courseData['code'] !== $existingCourse['code']) {
                if ($this->courseCodeExists($courseData['code'], $courseId)) {
                    return [
                        'success' => false,
                        'errors' => ['Course code already exists']
                    ];
                }
            }
            
            // Validate dates if both provided
            if (isset($courseData['start_date']) && isset($courseData['end_date'])) {
                if (strtotime($courseData['end_date']) <= strtotime($courseData['start_date'])) {
                    return [
                        'success' => false,
                        'errors' => ['End date must be after start date']
                    ];
                }
            } elseif (isset($courseData['start_date']) && $existingCourse['end_date']) {
                if (strtotime($existingCourse['end_date']) <= strtotime($courseData['start_date'])) {
                    return [
                        'success' => false,
                        'errors' => ['End date must be after start date']
                    ];
                }
            } elseif (isset($courseData['end_date']) && $existingCourse['start_date']) {
                if (strtotime($courseData['end_date']) <= strtotime($existingCourse['start_date'])) {
                    return [
                        'success' => false,
                        'errors' => ['End date must be after start date']
                    ];
                }
            }
            
            // Sanitize input data
            $cleanData = $this->sanitizeCourseData($courseData);
            
            // Add update timestamp
            $cleanData['updated_at'] = date('Y-m-d H:i:s');
            
            // Remove fields that shouldn't be updated directly
            unset($cleanData['id']);
            unset($cleanData['created_at']);
            unset($cleanData['created_by']);
            unset($cleanData['enrolled_students']);
            
            // Update course record
            $affectedRows = $this->db->update($this->table, $cleanData, 'id = ?', [$courseId]);
            
            if ($affectedRows > 0) {
                // Log the update
                if ($updatedBy) {
                    $this->logActivity($courseId, 'course_updated', "Course updated by user ID: {$updatedBy}");
                }
                
                logError("Course updated successfully", [
                    'course_id' => $courseId,
                    'updated_by' => $updatedBy,
                    'changes' => array_keys($cleanData)
                ]);
                
                return [
                    'success' => true,
                    'message' => 'Course updated successfully',
                    'affected_rows' => $affectedRows
                ];
            } else {
                return [
                    'success' => false,
                    'errors' => ['No changes were made']
                ];
            }
            
        } catch (Exception $e) {
            logError("Failed to update course: " . $e->getMessage(), [
                'course_id' => $courseId, 
                'course_data' => $courseData,
                'updated_by' => $updatedBy
            ]);
            return [
                'success' => false,
                'errors' => ['Failed to update course: ' . $e->getMessage()]
            ];
        }
    }
    
    /**
     * Enroll student in course
     * 
     * @param int $courseId Course ID
     * @param int $studentId Student ID
     * @param int|null $enrolledBy User ID who enrolled the student
     * @return array Result with 'success', 'message', 'errors'
     */
    public function enrollStudent($courseId, $studentId, $enrolledBy = null) {
        try {
            // Check if course exists and is active
            $course = $this->getById($courseId);
            if (!$course) {
                return [
                    'success' => false,
                    'errors' => ['Course not found']
                ];
            }
            
            if ($course['status'] !== 'active') {
                return [
                    'success' => false,
                    'errors' => ['Cannot enroll in inactive course']
                ];
            }
            
            // Check if user is a student
            $userClass = new User($this->db);
            $student = $userClass->getById($studentId);
            if (!$student || $student['role'] !== 'student') {
                return [
                    'success' => false,
                    'errors' => ['Invalid student ID']
                ];
            }
            
            // Check if already enrolled
            if ($this->isStudentEnrolled($courseId, $studentId)) {
                return [
                    'success' => false,
                    'errors' => ['Student is already enrolled in this course']
                ];
            }
            
            // Check if course is full
            if ($course['is_full']) {
                return [
                    'success' => false,
                    'errors' => ['Course is full']
                ];
            }
            
            // Create enrollment record
            $enrollmentData = [
                'course_id' => $courseId,
                'student_id' => $studentId,
                'enrolled_at' => date('Y-m-d H:i:s'),
                'status' => 'active',
                'enrolled_by' => $enrolledBy
            ];
            
            $enrollmentId = $this->db->insert($this->enrollmentsTable, $enrollmentData);
            
            // Update enrolled students count
            $this->db->update(
                $this->table,
                ['enrolled_students' => $course['current_enrolled'] + 1],
                'id = ?',
                [$courseId]
            );
            
            // Log the enrollment
            if ($enrolledBy) {
                $this->logActivity($courseId, 'student_enrolled', "Student ID {$studentId} enrolled by user ID: {$enrolledBy}");
            } else {
                $this->logActivity($courseId, 'student_enrolled', "Student ID {$studentId} self-enrolled");
            }
            
            logError("Student enrolled successfully", [
                'course_id' => $courseId,
                'student_id' => $studentId,
                'enrollment_id' => $enrollmentId,
                'enrolled_by' => $enrolledBy
            ]);
            
            return [
                'success' => true,
                'enrollment_id' => $enrollmentId,
                'message' => 'Student enrolled successfully'
            ];
            
        } catch (Exception $e) {
            logError("Failed to enroll student: " . $e->getMessage(), [
                'course_id' => $courseId,
                'student_id' => $studentId,
                'enrolled_by' => $enrolledBy
            ]);
            return [
                'success' => false,
                'errors' => ['Failed to enroll student: ' . $e->getMessage()]
            ];
        }
    }
    
    /**
     * Unenroll student from course
     * 
     * @param int $courseId Course ID
     * @param int $studentId Student ID
     * @param int|null $unenrolledBy User ID who unenrolled the student
     * @return array Result with 'success', 'message', 'errors'
     */
    public function unenrollStudent($courseId, $studentId, $unenrolledBy = null) {
        try {
            // Check if enrollment exists
            $enrollment = $this->db->fetchRow(
                "SELECT * FROM {$this->enrollmentsTable} WHERE course_id = ? AND student_id = ? AND status = 'active'",
                [$courseId, $studentId]
            );
            
            if (!$enrollment) {
                return [
                    'success' => false,
                    'errors' => ['Student is not enrolled in this course']
                ];
            }
            
            // Mark enrollment as inactive
            $affectedRows = $this->db->update(
                $this->enrollmentsTable,
                ['status' => 'inactive', 'unenrolled_at' => date('Y-m-d H:i:s')],
                'id = ?',
                [$enrollment['id']]
            );
            
            if ($affectedRows > 0) {
                // Update enrolled students count
                $course = $this->getById($courseId);
                if ($course) {
                    $this->db->update(
                        $this->table,
                        ['enrolled_students' => max(0, $course['current_enrolled'] - 1)],
                        'id = ?',
                        [$courseId]
                    );
                }
                
                // Log the unenrollment
                if ($unenrolledBy) {
                    $this->logActivity($courseId, 'student_unenrolled', "Student ID {$studentId} unenrolled by user ID: {$unenrolledBy}");
                } else {
                    $this->logActivity($courseId, 'student_unenrolled', "Student ID {$studentId} unenrolled");
                }
                
                logError("Student unenrolled successfully", [
                    'course_id' => $courseId,
                    'student_id' => $studentId,
                    'unenrolled_by' => $unenrolledBy
                ]);
                
                return [
                    'success' => true,
                    'message' => 'Student unenrolled successfully'
                ];
            } else {
                return [
                    'success' => false,
                    'errors' => ['Failed to unenroll student']
                ];
            }
            
        } catch (Exception $e) {
            logError("Failed to unenroll student: " . $e->getMessage(), [
                'course_id' => $courseId,
                'student_id' => $studentId,
                'unenrolled_by' => $unenrolledBy
            ]);
            return [
                'success' => false,
                'errors' => ['Failed to unenroll student: ' . $e->getMessage()]
            ];
        }
    }
    
    /**
     * Get enrolled students for a course
     * 
     * @param int $courseId Course ID
     * @param bool $activeOnly Only active enrollments (default: true)
     * @return array Array of enrolled students
     */
    public function getEnrolledStudents($courseId, $activeOnly = true) {
        try {
            $whereClause = 'c.course_id = ?';
            $params = [$courseId];
            
            if ($activeOnly) {
                $whereClause .= ' AND c.status = ?';
                $params[] = 'active';
            }
            
            $sql = "SELECT u.id, u.username, u.email, u.first_name, u.last_name, 
                           c.enrolled_at, c.status as enrollment_status
                   FROM {$this->enrollmentsTable} c
                   JOIN users u ON c.student_id = u.id
                   WHERE {$whereClause}
                   ORDER BY u.last_name, u.first_name";
            
            $students = $this->db->fetchAll($sql, $params);
            
            // Format data
            foreach ($students as &$student) {
                $student['formatted_enrolled_at'] = formatDateTime($student['enrolled_at']);
                $student['full_name'] = $student['first_name'] . ' ' . $student['last_name'];
            }
            
            return $students;
            
        } catch (Exception $e) {
            logError("Failed to get enrolled students: " . $e->getMessage(), ['course_id' => $courseId]);
            return [];
        }
    }
    
    /**
     * Get courses for a student
     * 
     * @param int $studentId Student ID
     * @param string|null $status Filter by enrollment status (default: active)
     * @return array Array of courses
     */
    public function getStudentCourses($studentId, $status = 'active') {
        try {
            $whereClause = 'c.student_id = ?';
            $params = [$studentId];
            
            if ($status) {
                $whereClause .= ' AND c.status = ?';
                $params[] = $status;
            }
            
            $sql = "SELECT c.*, CONCAT(u.first_name, ' ', u.last_name) as teacher_name
                   FROM {$this->enrollmentsTable} c
                   JOIN courses co ON c.course_id = co.id
                   LEFT JOIN users u ON co.teacher_id = u.id
                   WHERE {$whereClause}
                   ORDER BY co.start_date DESC";
            
            $courses = $this->db->fetchAll($sql, $params);
            
            // Format dates
            foreach ($courses as &$course) {
                $course['formatted_start_date'] = formatDate($course['start_date']);
                $course['formatted_end_date'] = formatDate($course['end_date']);
            }
            
            return $courses;
            
        } catch (Exception $e) {
            logError("Failed to get student courses: " . $e->getMessage(), ['student_id' => $studentId]);
            return [];
        }
    }
    
    /**
     * Get courses taught by a teacher
     * 
     * @param int $teacherId Teacher ID
     * @param string|null $status Filter by course status (default: active)
     * @return array Array of courses
     */
    public function getTeacherCourses($teacherId, $status = 'active') {
        try {
            $whereClause = 'teacher_id = ?';
            $params = [$teacherId];
            
            if ($status) {
                $whereClause .= ' AND status = ?';
                $params[] = $status;
            }
            
            $sql = "SELECT *, 
                           COUNT(DISTINCT e.student_id) as current_enrolled
                   FROM {$this->table}
                   LEFT JOIN {$this->enrollmentsTable} e ON id = e.course_id AND e.status = 'active'
                   WHERE {$whereClause}
                   GROUP BY id
                   ORDER BY start_date DESC";
            
            $courses = $this->db->fetchAll($sql, $params);
            
            // Format dates and add computed fields
            foreach ($courses as &$course) {
                $course['formatted_start_date'] = formatDate($course['start_date']);
                $course['formatted_end_date'] = formatDate($course['end_date']);
                
                $course['is_full'] = $course['capacity'] && $course['current_enrolled'] >= $course['capacity'];
                $course['available_slots'] = $course['capacity'] ? max(0, $course['capacity'] - $course['current_enrolled']) : null;
            }
            
            return $courses;
            
        } catch (Exception $e) {
            logError("Failed to get teacher courses: " . $e->getMessage(), ['teacher_id' => $teacherId]);
            return [];
        }
    }
    
    /**
     * Delete course (soft delete)
     * 
     * @param int $courseId Course ID
     * @param int|null $deletedBy User ID who deleted the course
     * @return array Result with 'success', 'message', 'errors'
     */
    public function delete($courseId, $deletedBy = null) {
        try {
            $existingCourse = $this->getById($courseId);
            if (!$existingCourse) {
                return [
                    'success' => false,
                    'errors' => ['Course not found']
                ];
            }
            
            // Soft delete - change status to inactive
            $affectedRows = $this->db->update(
                $this->table,
                ['status' => 'inactive', 'updated_at' => date('Y-m-d H:i:s')],
                'id = ?',
                [$courseId]
            );
            
            if ($affectedRows > 0) {
                // Log the deletion
                if ($deletedBy) {
                    $this->logActivity($courseId, 'course_deleted', "Course deleted by user ID: {$deletedBy}");
                } else {
                    $this->logActivity($courseId, 'course_deleted', 'Course deleted');
                }
                
                logError("Course deleted successfully", [
                    'course_id' => $courseId,
                    'deleted_by' => $deletedBy
                ]);
                
                return [
                    'success' => true,
                    'message' => 'Course deleted successfully'
                ];
            } else {
                return [
                    'success' => false,
                    'errors' => ['Failed to delete course']
                ];
            }
            
        } catch (Exception $e) {
            logError("Failed to delete course: " . $e->getMessage(), [
                'course_id' => $courseId,
                'deleted_by' => $deletedBy
            ]);
            return [
                'success' => false,
                'errors' => ['Failed to delete course: ' . $e->getMessage()]
            ];
        }
    }
    
    /**
     * Get course statistics
     * 
     * @return array Course statistics
     */
    public function getStatistics() {
        try {
            $stats = [];
            
            // Total courses
            $totalResult = $this->db->fetchRow("SELECT COUNT(*) as total FROM {$this->table}");
            $stats['total_courses'] = (int) $totalResult['total'];
            
            // Courses by status
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
            
            // Courses by semester
            $semesterResult = $this->db->fetchAll("
                SELECT semester, COUNT(*) as count 
                FROM {$this->table} 
                WHERE status = 'active'
                GROUP BY semester 
                ORDER BY count DESC
            ");
            $stats['by_semester'] = [];
            foreach ($semesterResult as $row) {
                $stats['by_semester'][$row['semester']] = (int) $row['count'];
            }
            
            // Average enrollment per course
            $enrollmentResult = $this->db->fetchRow("
                SELECT AVG(enrolled_students) as average 
                FROM {$this->table} 
                WHERE status = 'active'
            ");
            $stats['average_enrollment'] = round($enrollmentResult['average'], 2);
            
            // Total enrolled students
            $totalEnrollmentResult = $this->db->fetchRow("
                SELECT COUNT(*) as total 
                FROM {$this->enrollmentsTable} 
                WHERE status = 'active'
            ");
            $stats['total_enrolled_students'] = (int) $totalEnrollmentResult['total'];
            
            return $stats;
            
        } catch (Exception $e) {
            logError("Failed to get course statistics: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Check if student is enrolled in course
     * 
     * @param int $courseId Course ID
     * @param int $studentId Student ID
     * @return bool True if enrolled, false otherwise
     */
    private function isStudentEnrolled($courseId, $studentId) {
        return $this->db->exists(
            $this->enrollmentsTable,
            'course_id = ? AND student_id = ? AND status = ?',
            [$courseId, $studentId, 'active']
        );
    }
    
    /**
     * Validate course data
     * 
     * @param array $data Course data to validate
     * @param int|null $excludeId Course ID to exclude from uniqueness checks
     * @return array Validation result with 'valid' boolean and 'errors' array
     */
    private function validateCourseData($data, $excludeId = null) {
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
            
            // Validate date format
            if (isset($rules['date']) && $rules['date'] && !validateDate($value)) {
                $errors[] = "Invalid date format for {$field}";
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
            
            // Validate status
            if (isset($rules['in_array']) && $rules['in_array'] && !in_array($value, $rules['in_array'])) {
                $errors[] = "Invalid status specified for {$field}";
            }
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }
    
    /**
     * Sanitize course data for database insertion
     * 
     * @param array $data Raw course data
     * @return array Sanitized course data
     */
    private function sanitizeCourseData($data) {
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
     * Check if course code already exists
     * 
     * @param string $courseCode Course code to check
     * @param int|null $excludeId Course ID to exclude from check
     * @return bool True if course code exists, false otherwise
     */
    private function courseCodeExists($courseCode, $excludeId = null) {
        $whereClause = "code = ?";
        $params = [$courseCode];
        
        if ($excludeId) {
            $whereClause .= " AND id != ?";
            $params[] = $excludeId;
        }
        
        return $this->db->exists($this->table, $whereClause, $params);
    }
    
    /**
     * Log course activity
     * 
     * @param int $courseId Course ID
     * @param string $action Action performed
     * @param string $description Action description
     * @param array $additionalData Additional data to log
     */
    private function logActivity($courseId, $action, $description, $additionalData = []) {
        try {
            $logData = [
                'course_id' => $courseId,
                'action' => $action,
                'description' => $description,
                'ip_address' => getClientIP(),
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
                'additional_data' => json_encode($additionalData),
                'created_at' => date('Y-m-d H:i:s')
            ];
            
            // Assuming there's a course_activity_logs table
            if ($this->db->tableExists('course_activity_logs')) {
                $this->db->insert('course_activity_logs', $logData);
            }
            
        } catch (Exception $e) {
            logError("Failed to log course activity: " . $e->getMessage(), [
                'course_id' => $courseId,
                'action' => $action
            ]);
        }
    }
}
?>