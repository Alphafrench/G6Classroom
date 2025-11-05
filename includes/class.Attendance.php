<?php
/**
 * Attendance Management Class
 * 
 * This class handles all attendance-related operations including session tracking,
 * student attendance management, time tracking, attendance records, and reporting 
 * with comprehensive validation and security measures for educational institutions.
 * 
 * @package AttendanceSystem
 * @version 1.0
 * @author Development Team
 */

require_once 'class.Database.php';
require_once 'class.User.php';
require_once 'class.Course.php';
require_once 'functions.php';

class Attendance {
    /**
     * Database instance
     * @var Database
     */
    private $db;
    
    /**
     * User instance for user operations
     * @var User
     */
    private $user;
    
    /**
     * Course instance for course operations
     * @var Course
     */
    private $course;
    
    /**
     * Attendance table name
     * @var string
     */
    private $table = 'attendance_records';
    
    /**
     * Session attendance table name
     * @var string
     */
    private $sessionTable = 'session_attendance';
    
    /**
     * Attendance logs table name
     * @var string
     */
    private $logsTable = 'attendance_logs';
    
    /**
     * Maximum allowed daily hours (default: 12 hours for teachers)
     * @var int
     */
    private $maxDailyHours = 12;
    
    /**
     * Minimum break time required (default: 30 minutes)
     * @var int
     */
    private $minBreakTime = 30;
    
    /**
     * Constructor
     * 
     * @param Database|null $db Database instance (optional)
     */
    public function __construct($db = null) {
        $this->db = $db ?: getDatabase();
        $this->user = new User($this->db);
        $this->course = new Course($this->db);
    }
    
    /**
     * Clock in an employee
     * 
     * @param int $employeeId Employee ID
     * @param string $location Location information (optional)
     * @param string $notes Clock-in notes (optional)
     * @return array Result with 'success', 'attendance_id', 'message', 'errors'
     */
    public function clockIn($employeeId, $location = '', $notes = '') {
        try {
            // Validate employee exists and is active
            $employee = $this->db->fetchRow(
                "SELECT id, is_active FROM employees WHERE id = ?", 
                [$employeeId]
            );
            
            if (!$employee) {
                return [
                    'success' => false,
                    'errors' => ['Employee not found']
                ];
            }
            
            if (!$employee['is_active']) {
                return [
                    'success' => false,
                    'errors' => ['Employee is not active']
                ];
            }
            
            // Check if employee is already clocked in
            $existingAttendance = $this->db->fetchRow(
                "SELECT * FROM {$this->table} WHERE employee_id = ? AND clock_out IS NULL ORDER BY clock_in DESC LIMIT 1",
                [$employeeId]
            );
            
            if ($existingAttendance) {
                return [
                    'success' => false,
                    'errors' => ['Employee is already clocked in']
                ];
            }
            
            $currentTime = date('Y-m-d H:i:s');
            
            // Create attendance record
            $attendanceData = [
                'employee_id' => $employeeId,
                'clock_in' => $currentTime,
                'date' => date('Y-m-d'),
                'location' => sanitizeInput($location),
                'notes' => sanitizeInput($notes),
                'created_at' => $currentTime,
                'updated_at' => $currentTime
            ];
            
            $attendanceId = $this->db->insert($this->table, $attendanceData);
            
            // Log the action
            $this->logAction($employeeId, 'clock_in', $attendanceId, $currentTime, $location);
            
            logError("Employee clocked in successfully", [
                'employee_id' => $employeeId, 
                'attendance_id' => $attendanceId,
                'location' => $location
            ]);
            
            return [
                'success' => true,
                'attendance_id' => $attendanceId,
                'message' => 'Clocked in successfully',
                'clock_in_time' => $currentTime
            ];
            
        } catch (Exception $e) {
            logError("Failed to clock in employee: " . $e->getMessage(), ['employee_id' => $employeeId]);
            return [
                'success' => false,
                'errors' => ['Failed to clock in: ' . $e->getMessage()]
            ];
        }
    }
    
    /**
     * Clock out an employee
     * 
     * @param int $employeeId Employee ID
     * @param string $location Location information (optional)
     * @param string $notes Clock-out notes (optional)
     * @return array Result with 'success', 'message', 'errors', 'hours_worked'
     */
    public function clockOut($employeeId, $location = '', $notes = '') {
        try {
            // Get active attendance record
            $attendance = $this->db->fetchRow(
                "SELECT * FROM {$this->table} WHERE employee_id = ? AND clock_out IS NULL ORDER BY clock_in DESC LIMIT 1",
                [$employeeId]
            );
            
            if (!$attendance) {
                return [
                    'success' => false,
                    'errors' => ['No active clock-in found for this employee']
                ];
            }
            
            $currentTime = date('Y-m-d H:i:s');
            $clockInTime = $attendance['clock_in'];
            
            // Calculate hours worked
            $clockInTimestamp = strtotime($clockInTime);
            $clockOutTimestamp = strtotime($currentTime);
            $hoursWorked = ($clockOutTimestamp - $clockInTimestamp) / 3600;
            
            // Validate hours worked
            if ($hoursWorked > $this->maxDailyHours) {
                logError("Suspicious clock-out time detected", [
                    'employee_id' => $employeeId,
                    'attendance_id' => $attendance['id'],
                    'hours_worked' => $hoursWorked
                ]);
            }
            
            // Update attendance record
            $updateData = [
                'clock_out' => $currentTime,
                'hours_worked' => round($hoursWorked, 2),
                'location_out' => sanitizeInput($location),
                'notes_out' => sanitizeInput($notes),
                'updated_at' => $currentTime
            ];
            
            $affectedRows = $this->db->update(
                $this->table, 
                $updateData, 
                'id = ?', 
                [$attendance['id']]
            );
            
            if ($affectedRows === 0) {
                return [
                    'success' => false,
                    'errors' => ['Failed to update attendance record']
                ];
            }
            
            // Log the action
            $this->logAction($employeeId, 'clock_out', $attendance['id'], $currentTime, $location);
            
            logError("Employee clocked out successfully", [
                'employee_id' => $employeeId,
                'attendance_id' => $attendance['id'],
                'hours_worked' => $hoursWorked,
                'location' => $location
            ]);
            
            return [
                'success' => true,
                'message' => 'Clocked out successfully',
                'clock_out_time' => $currentTime,
                'hours_worked' => round($hoursWorked, 2),
                'attendance_id' => $attendance['id']
            ];
            
        } catch (Exception $e) {
            logError("Failed to clock out employee: " . $e->getMessage(), ['employee_id' => $employeeId]);
            return [
                'success' => false,
                'errors' => ['Failed to clock out: ' . $e->getMessage()]
            ];
        }
    }
    
    /**
     * Get employee's current attendance status
     * 
     * @param int $employeeId Employee ID
     * @return array|null Current attendance status or null if not clocked in
     */
    public function getCurrentStatus($employeeId) {
        try {
            $sql = "SELECT a.*, e.first_name, e.last_name, e.position 
                   FROM {$this->table} a 
                   JOIN employees e ON a.employee_id = e.id 
                   WHERE a.employee_id = ? AND a.clock_out IS NULL 
                   ORDER BY a.clock_in DESC LIMIT 1";
            
            $attendance = $this->db->fetchRow($sql, [$employeeId]);
            
            if ($attendance) {
                // Calculate current work duration
                $clockInTime = strtotime($attendance['clock_in']);
                $currentTime = time();
                $currentDuration = ($currentTime - $clockInTime) / 3600; // in hours
                
                $attendance['current_duration_hours'] = round($currentDuration, 2);
                $attendance['formatted_clock_in'] = formatDateTime($attendance['clock_in']);
            }
            
            return $attendance;
            
        } catch (Exception $e) {
            logError("Failed to get current attendance status: " . $e->getMessage(), ['employee_id' => $employeeId]);
            return null;
        }
    }
    
    /**
     * Get attendance records for an employee
     * 
     * @param int $employeeId Employee ID
     * @param string $startDate Start date (Y-m-d)
     * @param string $endDate End date (Y-m-d)
     * @param int $limit Number of records to return
     * @param int $offset Offset for pagination
     * @return array Attendance records with pagination info
     */
    public function getEmployeeAttendance($employeeId, $startDate = null, $endDate = null, $limit = 30, $offset = 0) {
        try {
            $whereConditions = ['employee_id = ?'];
            $params = [$employeeId];
            
            // Add date range filter if provided
            if ($startDate) {
                $whereConditions[] = 'date >= ?';
                $params[] = $startDate;
            }
            
            if ($endDate) {
                $whereConditions[] = 'date <= ?';
                $params[] = $endDate;
            }
            
            $whereClause = implode(' AND ', $whereConditions);
            
            // Get total count
            $countSql = "SELECT COUNT(*) as total FROM {$this->table} WHERE {$whereClause}";
            $totalResult = $this->db->fetchRow($countSql, $params);
            $total = (int) $totalResult['total'];
            
            // Get attendance records
            $sql = "SELECT * FROM {$this->table} 
                   WHERE {$whereClause} 
                   ORDER BY date DESC, clock_in DESC 
                   LIMIT ? OFFSET ?";
            
            $params[] = $limit;
            $params[] = $offset;
            
            $records = $this->db->fetchAll($sql, $params);
            
            // Format times for display
            foreach ($records as &$record) {
                $record['formatted_clock_in'] = formatDateTime($record['clock_in']);
                $record['formatted_clock_out'] = $record['clock_out'] ? formatDateTime($record['clock_out']) : 'Still clocked in';
                $record['formatted_date'] = formatDate($record['date']);
                
                if ($record['hours_worked']) {
                    $record['formatted_hours_worked'] = number_format($record['hours_worked'], 2) . ' hours';
                }
            }
            
            $totalPages = ceil($total / $limit);
            
            return [
                'records' => $records,
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
            logError("Failed to get employee attendance: " . $e->getMessage(), [
                'employee_id' => $employeeId,
                'start_date' => $startDate,
                'end_date' => $endDate
            ]);
            return [
                'records' => [],
                'total' => 0,
                'pagination' => []
            ];
        }
    }
    
    /**
     * Get attendance summary for an employee
     * 
     * @param int $employeeId Employee ID
     * @param string $startDate Start date (Y-m-d)
     * @param @param string $endDate End date (Y-m-d)
     * @return array Attendance summary
     */
    public function getEmployeeSummary($employeeId, $startDate, $endDate) {
        try {
            $summary = [];
            
            // Total days worked
            $daysResult = $this->db->fetchRow(
                "SELECT COUNT(DISTINCT date) as days_worked 
                 FROM {$this->table} 
                 WHERE employee_id = ? AND date BETWEEN ? AND ?",
                [$employeeId, $startDate, $endDate]
            );
            $summary['days_worked'] = (int) $daysResult['days_worked'];
            
            // Total hours worked
            $hoursResult = $this->db->fetchRow(
                "SELECT SUM(hours_worked) as total_hours 
                 FROM {$this->table} 
                 WHERE employee_id = ? AND date BETWEEN ? AND ? AND hours_worked IS NOT NULL",
                [$employeeId, $startDate, $endDate]
            );
            $summary['total_hours'] = round($hoursResult['total_hours'] ?? 0, 2);
            
            // Average hours per day
            $summary['average_hours_per_day'] = $summary['days_worked'] > 0 
                ? round($summary['total_hours'] / $summary['days_worked'], 2) 
                : 0;
            
            // Clock-ins without clock-outs (incomplete days)
            $incompleteResult = $this->db->fetchRow(
                "SELECT COUNT(*) as incomplete_days 
                 FROM {$this->table} 
                 WHERE employee_id = ? AND date BETWEEN ? AND ? AND clock_out IS NULL",
                [$employeeId, $startDate, $endDate]
            );
            $summary['incomplete_days'] = (int) $incompleteResult['incomplete_days'];
            
            return $summary;
            
        } catch (Exception $e) {
            logError("Failed to get employee summary: " . $e->getMessage(), [
                'employee_id' => $employeeId,
                'start_date' => $startDate,
                'end_date' => $endDate
            ]);
            return [];
        }
    }
    
    /**
     * Get attendance records for all employees (for reporting)
     * 
     * @param array $filters Optional filters (date range, department, employee)
     * @param int $limit Number of records to return
     * @param int $offset Offset for pagination
     * @return array Attendance records with pagination
     */
    public function getAllAttendance($filters = [], $limit = 50, $offset = 0) {
        try {
            $whereConditions = [];
            $params = [];
            
            // Build WHERE clause based on filters
            if (!empty($filters['start_date'])) {
                $whereConditions[] = 'a.date >= ?';
                $params[] = $filters['start_date'];
            }
            
            if (!empty($filters['end_date'])) {
                $whereConditions[] = 'a.date <= ?';
                $params[] = $filters['end_date'];
            }
            
            if (!empty($filters['employee_id'])) {
                $whereConditions[] = 'a.employee_id = ?';
                $params[] = $filters['employee_id'];
            }
            
            if (!empty($filters['department_id'])) {
                $whereConditions[] = 'e.department_id = ?';
                $params[] = $filters['department_id'];
            }
            
            if (isset($filters['incomplete_only']) && $filters['incomplete_only']) {
                $whereConditions[] = 'a.clock_out IS NULL';
            }
            
            $whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';
            
            // Get total count
            $countSql = "SELECT COUNT(*) as total 
                        FROM {$this->table} a 
                        JOIN employees e ON a.employee_id = e.id 
                        LEFT JOIN departments d ON e.department_id = d.id 
                        {$whereClause}";
            $totalResult = $this->db->fetchRow($countSql, $params);
            $total = (int) $totalResult['total'];
            
            // Get attendance records with employee info
            $sql = "SELECT a.*, e.first_name, e.last_name, e.email, 
                           e.position, d.name as department_name 
                   FROM {$this->table} a 
                   JOIN employees e ON a.employee_id = e.id 
                   LEFT JOIN departments d ON e.department_id = d.id 
                   {$whereClause} 
                   ORDER BY a.date DESC, a.clock_in DESC 
                   LIMIT ? OFFSET ?";
            
            $params[] = $limit;
            $params[] = $offset;
            
            $records = $this->db->fetchAll($sql, $params);
            
            // Format times for display
            foreach ($records as &$record) {
                $record['formatted_clock_in'] = formatDateTime($record['clock_in']);
                $record['formatted_clock_out'] = $record['clock_out'] ? formatDateTime($record['clock_out']) : 'Still clocked in';
                $record['formatted_date'] = formatDate($record['date']);
                
                if ($record['hours_worked']) {
                    $record['formatted_hours_worked'] = number_format($record['hours_worked'], 2) . ' hours';
                }
            }
            
            $totalPages = ceil($total / $limit);
            
            return [
                'records' => $records,
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
            logError("Failed to get all attendance: " . $e->getMessage(), ['filters' => $filters]);
            return [
                'records' => [],
                'total' => 0,
                'pagination' => []
            ];
        }
    }
    
    /**
     * Edit attendance record manually
     * 
     * @param int $attendanceId Attendance record ID
     * @param array $data Updated data (clock_in, clock_out, notes)
     * @param int $editedBy User ID who made the edit
     * @return array Result with 'success', 'message', 'errors'
     */
    public function editAttendance($attendanceId, $data, $editedBy) {
        try {
            // Get existing record
            $existingRecord = $this->db->fetchRow("SELECT * FROM {$this->table} WHERE id = ?", [$attendanceId]);
            
            if (!$existingRecord) {
                return [
                    'success' => false,
                    'errors' => ['Attendance record not found']
                ];
            }
            
            // Validate and sanitize data
            $updateData = [];
            
            if (isset($data['clock_in'])) {
                if (!validateDateTime($data['clock_in'])) {
                    return [
                        'success' => false,
                        'errors' => ['Invalid clock-in time format']
                    ];
                }
                $updateData['clock_in'] = $data['clock_in'];
            }
            
            if (isset($data['clock_out'])) {
                if (!empty($data['clock_out']) && !validateDateTime($data['clock_out'])) {
                    return [
                        'success' => false,
                        'errors' => ['Invalid clock-out time format']
                    ];
                }
                $updateData['clock_out'] = $data['clock_out'] ?: null;
            }
            
            if (isset($data['notes'])) {
                $updateData['notes'] = sanitizeInput($data['notes']);
            }
            
            // Recalculate hours worked if times were updated
            if (isset($updateData['clock_in']) || isset($updateData['clock_out'])) {
                $clockInTime = $updateData['clock_in'] ?? $existingRecord['clock_in'];
                $clockOutTime = $updateData['clock_out'] ?? $existingRecord['clock_out'];
                
                if ($clockInTime && $clockOutTime) {
                    $clockInTimestamp = strtotime($clockInTime);
                    $clockOutTimestamp = strtotime($clockOutTime);
                    $hoursWorked = ($clockOutTimestamp - $clockInTimestamp) / 3600;
                    $updateData['hours_worked'] = round($hoursWorked, 2);
                } else {
                    $updateData['hours_worked'] = null;
                }
            }
            
            $updateData['updated_at'] = date('Y-m-d H:i:s');
            
            // Update the record
            $affectedRows = $this->db->update(
                $this->table,
                $updateData,
                'id = ?',
                [$attendanceId]
            );
            
            if ($affectedRows > 0) {
                // Log the edit
                $this->logAction($existingRecord['employee_id'], 'manual_edit', $attendanceId, null, '', [
                    'edited_by' => $editedBy,
                    'old_data' => $existingRecord,
                    'new_data' => $updateData
                ]);
                
                logError("Attendance record edited manually", [
                    'attendance_id' => $attendanceId,
                    'edited_by' => $editedBy,
                    'changes' => $updateData
                ]);
                
                return [
                    'success' => true,
                    'message' => 'Attendance record updated successfully'
                ];
            } else {
                return [
                    'success' => false,
                    'errors' => ['No changes were made']
                ];
            }
            
        } catch (Exception $e) {
            logError("Failed to edit attendance record: " . $e->getMessage(), [
                'attendance_id' => $attendanceId,
                'data' => $data
            ]);
            return [
                'success' => false,
                'errors' => ['Failed to update attendance record: ' . $e->getMessage()]
            ];
        }
    }
    
    /**
     * Generate attendance report
     * 
     * @param array $filters Report filters
     * @param string $format Report format (summary, detailed, csv)
     * @return array Report data
     */
    public function generateReport($filters = [], $format = 'summary') {
        try {
            $report = [];
            
            if ($format === 'summary') {
                // Summary report by employee
                $sql = "SELECT e.id, e.first_name, e.last_name, e.position, d.name as department_name,
                               COUNT(a.id) as days_worked,
                               SUM(a.hours_worked) as total_hours,
                               AVG(a.hours_worked) as average_hours,
                               MIN(a.date) as first_work_day,
                               MAX(a.date) as last_work_day
                       FROM employees e
                       LEFT JOIN {$this->table} a ON e.id = a.employee_id 
                           AND a.date BETWEEN ? AND ?
                       LEFT JOIN departments d ON e.department_id = d.id
                       WHERE e.is_active = 1";
                
                $params = [$filters['start_date'], $filters['end_date']];
                
                if (!empty($filters['department_id'])) {
                    $sql .= " AND e.department_id = ?";
                    $params[] = $filters['department_id'];
                }
                
                $sql .= " GROUP BY e.id ORDER BY e.last_name, e.first_name";
                
                $report['data'] = $this->db->fetchAll($sql, $params);
                
            } elseif ($format === 'detailed') {
                // Detailed report with all records
                $result = $this->getAllAttendance($filters, 10000, 0); // Large limit for report
                $report['data'] = $result['records'];
                
            } elseif ($format === 'csv') {
                // CSV format for export
                $result = $this->getAllAttendance($filters, 10000, 0);
                $csvData = [];
                
                // CSV headers
                $csvData[] = ['Date', 'Employee', 'Department', 'Position', 'Clock In', 'Clock Out', 'Hours Worked', 'Location'];
                
                // CSV rows
                foreach ($result['records'] as $record) {
                    $csvData[] = [
                        $record['formatted_date'],
                        $record['first_name'] . ' ' . $record['last_name'],
                        $record['department_name'] ?? '',
                        $record['position'],
                        $record['formatted_clock_in'],
                        $record['formatted_clock_out'],
                        $record['hours_worked'] ?? 0,
                        $record['location'] ?? ''
                    ];
                }
                
                $report['csv_data'] = $csvData;
                $report['csv_string'] = arrayToCSV($csvData);
            }
            
            $report['filters'] = $filters;
            $report['generated_at'] = date('Y-m-d H:i:s');
            
            return $report;
            
        } catch (Exception $e) {
            logError("Failed to generate attendance report: " . $e->getMessage(), ['filters' => $filters]);
            return [
                'error' => 'Failed to generate report: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Mark student attendance for a session
     * 
     * @param int $courseId Course ID
     * @param int $studentId Student ID
     * @param string $status Attendance status (present, absent, late, excused)
     * @param string $sessionDate Session date (Y-m-d)
     * @param int|null $markedBy User ID who marked the attendance
     * @param string $notes Optional notes
     * @return array Result with 'success', 'message', 'errors'
     */
    public function markStudentAttendance($courseId, $studentId, $status, $sessionDate, $markedBy = null, $notes = '') {
        try {
            // Verify course exists and teacher has access
            $course = $this->course->getById($courseId);
            if (!$course) {
                return [
                    'success' => false,
                    'errors' => ['Course not found']
                ];
            }
            
            // Check if student is enrolled in the course
            if (!$this->isStudentEnrolledInCourse($courseId, $studentId)) {
                return [
                    'success' => false,
                    'errors' => ['Student is not enrolled in this course']
                ];
            }
            
            // Validate status
            $allowedStatuses = ['present', 'absent', 'late', 'excused'];
            if (!in_array($status, $allowedStatuses)) {
                return [
                    'success' => false,
                    'errors' => ['Invalid attendance status']
                ];
            }
            
            // Check if attendance already marked for this session
            $existingAttendance = $this->db->fetchRow(
                "SELECT id FROM {$this->sessionTable} WHERE course_id = ? AND student_id = ? AND session_date = ?",
                [$courseId, $studentId, $sessionDate]
            );
            
            $attendanceData = [
                'course_id' => $courseId,
                'student_id' => $studentId,
                'session_date' => $sessionDate,
                'status' => $status,
                'notes' => sanitizeInput($notes),
                'marked_by' => $markedBy,
                'marked_at' => date('Y-m-d H:i:s'),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            if ($existingAttendance) {
                // Update existing attendance
                $affectedRows = $this->db->update(
                    $this->sessionTable,
                    $attendanceData,
                    'id = ?',
                    [$existingAttendance['id']]
                );
            } else {
                // Create new attendance record
                $affectedRows = $this->db->insert($this->sessionTable, $attendanceData);
            }
            
            // Log the action
            $action = $existingAttendance ? 'attendance_updated' : 'attendance_marked';
            $this->logAction($studentId, $action, $affectedRows, null, '', [
                'course_id' => $courseId,
                'status' => $status,
                'session_date' => $sessionDate,
                'marked_by' => $markedBy,
                'notes' => $notes
            ]);
            
            logError("Student attendance marked", [
                'course_id' => $courseId,
                'student_id' => $studentId,
                'status' => $status,
                'session_date' => $sessionDate,
                'marked_by' => $markedBy
            ]);
            
            return [
                'success' => true,
                'message' => 'Attendance marked successfully',
                'attendance_id' => $existingAttendance ? $existingAttendance['id'] : $affectedRows
            ];
            
        } catch (Exception $e) {
            logError("Failed to mark student attendance: " . $e->getMessage(), [
                'course_id' => $courseId,
                'student_id' => $studentId,
                'status' => $status,
                'marked_by' => $markedBy
            ]);
            return [
                'success' => false,
                'errors' => ['Failed to mark attendance: ' . $e->getMessage()]
            ];
        }
    }
    
    /**
     * Get student attendance for a course
     * 
     * @param int $courseId Course ID
     * @param int $studentId Student ID
     * @param string $startDate Start date (Y-m-d)
     * @param string $endDate End date (Y-m-d)
     * @return array Student attendance records
     */
    public function getStudentAttendance($courseId, $studentId, $startDate = null, $endDate = null) {
        try {
            $whereConditions = ['course_id = ?', 'student_id = ?'];
            $params = [$courseId, $studentId];
            
            if ($startDate) {
                $whereConditions[] = 'session_date >= ?';
                $params[] = $startDate;
            }
            
            if ($endDate) {
                $whereConditions[] = 'session_date <= ?';
                $params[] = $endDate;
            }
            
            $whereClause = implode(' AND ', $whereConditions);
            
            $sql = "SELECT * FROM {$this->sessionTable} 
                   WHERE {$whereClause} 
                   ORDER BY session_date DESC";
            
            $attendanceRecords = $this->db->fetchAll($sql, $params);
            
            // Format dates for display
            foreach ($attendanceRecords as &$record) {
                $record['formatted_session_date'] = formatDate($record['session_date']);
                $record['formatted_marked_at'] = formatDateTime($record['marked_at']);
            }
            
            return $attendanceRecords;
            
        } catch (Exception $e) {
            logError("Failed to get student attendance: " . $e->getMessage(), [
                'course_id' => $courseId,
                'student_id' => $studentId,
                'start_date' => $startDate,
                'end_date' => $endDate
            ]);
            return [];
        }
    }
    
    /**
     * Get course attendance summary
     * 
     * @param int $courseId Course ID
     * @param string $startDate Start date (Y-m-d)
     * @param string $endDate End date (Y-m-d)
     * @return array Attendance summary
     */
    public function getCourseAttendanceSummary($courseId, $startDate, $endDate) {
        try {
            // Get enrolled students
            $enrolledStudents = $this->course->getEnrolledStudents($courseId);
            $studentIds = array_column($enrolledStudents, 'id');
            
            if (empty($studentIds)) {
                return [
                    'total_sessions' => 0,
                    'total_students' => 0,
                    'average_attendance' => 0,
                    'attendance_by_status' => [],
                    'student_summaries' => []
                ];
            }
            
            // Build placeholders for IN clause
            $placeholders = str_repeat('?,', count($studentIds) - 1) . '?';
            
            // Get attendance statistics
            $sql = "SELECT status, COUNT(*) as count 
                   FROM {$this->sessionTable} 
                   WHERE course_id = ? AND student_id IN ({$placeholders}) 
                   AND session_date BETWEEN ? AND ?
                   GROUP BY status";
            
            $params = array_merge([$courseId], $studentIds, [$startDate, $endDate]);
            $statusCounts = $this->db->fetchAll($sql, $params);
            
            // Get total sessions
            $sessionSql = "SELECT COUNT(DISTINCT session_date) as total_sessions 
                          FROM {$this->sessionTable} 
                          WHERE course_id = ? AND session_date BETWEEN ? AND ?";
            $sessionResult = $this->db->fetchRow($sessionSql, [$courseId, $startDate, $endDate]);
            $totalSessions = (int) $sessionResult['total_sessions'];
            
            // Calculate attendance rates per student
            $studentSummaries = [];
            foreach ($enrolledStudents as $student) {
                $studentSql = "SELECT status, COUNT(*) as count 
                              FROM {$this->sessionTable} 
                              WHERE course_id = ? AND student_id = ? AND session_date BETWEEN ? AND ?
                              GROUP BY status";
                $studentParams = [$courseId, $student['id'], $startDate, $endDate];
                $studentStats = $this->db->fetchAll($studentSql, $studentParams);
                
                $totalMarked = 0;
                $presentCount = 0;
                $statusBreakdown = [];
                
                foreach ($studentStats as $stat) {
                    $statusBreakdown[$stat['status']] = (int) $stat['count'];
                    $totalMarked += (int) $stat['count'];
                    if ($stat['status'] === 'present') {
                        $presentCount = (int) $stat['count'];
                    }
                }
                
                $attendanceRate = $totalSessions > 0 ? round(($presentCount / $totalSessions) * 100, 2) : 0;
                
                $studentSummaries[] = [
                    'student_id' => $student['id'],
                    'student_name' => $student['full_name'],
                    'total_sessions' => $totalSessions,
                    'marked_sessions' => $totalMarked,
                    'present_sessions' => $presentCount,
                    'attendance_rate' => $attendanceRate,
                    'status_breakdown' => $statusBreakdown
                ];
            }
            
            // Calculate overall statistics
            $attendanceByStatus = [];
            $totalRecords = 0;
            $totalPresent = 0;
            
            foreach ($statusCounts as $stat) {
                $attendanceByStatus[$stat['status']] = (int) $stat['count'];
                $totalRecords += (int) $stat['count'];
                if ($stat['status'] === 'present') {
                    $totalPresent = (int) $stat['count'];
                }
            }
            
            $averageAttendance = $totalSessions > 0 ? round(($totalPresent / ($totalSessions * count($studentIds))) * 100, 2) : 0;
            
            return [
                'total_sessions' => $totalSessions,
                'total_students' => count($studentIds),
                'total_attendance_records' => $totalRecords,
                'average_attendance' => $averageAttendance,
                'attendance_by_status' => $attendanceByStatus,
                'student_summaries' => $studentSummaries
            ];
            
        } catch (Exception $e) {
            logError("Failed to get course attendance summary: " . $e->getMessage(), [
                'course_id' => $courseId,
                'start_date' => $startDate,
                'end_date' => $endDate
            ]);
            return [];
        }
    }
    
    /**
     * Bulk mark attendance for multiple students
     * 
     * @param int $courseId Course ID
     * @param array $attendanceData Array of student attendance data
     * @param int $markedBy User ID who marked the attendance
     * @param string $sessionDate Session date (Y-m-d)
     * @return array Result with 'success', 'message', 'errors', 'processed'
     */
    public function bulkMarkAttendance($courseId, $attendanceData, $markedBy, $sessionDate) {
        try {
            $processed = 0;
            $errors = [];
            
            foreach ($attendanceData as $studentId => $data) {
                $result = $this->markStudentAttendance(
                    $courseId, 
                    $studentId, 
                    $data['status'], 
                    $sessionDate, 
                    $markedBy, 
                    $data['notes'] ?? ''
                );
                
                if ($result['success']) {
                    $processed++;
                } else {
                    $errors[] = "Student ID {$studentId}: " . implode(', ', $result['errors']);
                }
            }
            
            logError("Bulk attendance marking completed", [
                'course_id' => $courseId,
                'total_students' => count($attendanceData),
                'processed' => $processed,
                'marked_by' => $markedBy,
                'session_date' => $sessionDate
            ]);
            
            return [
                'success' => $processed > 0,
                'message' => "Processed {$processed} out of " . count($attendanceData) . " students",
                'processed' => $processed,
                'errors' => $errors
            ];
            
        } catch (Exception $e) {
            logError("Failed to bulk mark attendance: " . $e->getMessage(), [
                'course_id' => $courseId,
                'marked_by' => $markedBy,
                'session_date' => $sessionDate
            ]);
            return [
                'success' => false,
                'message' => 'Failed to process bulk attendance',
                'errors' => ['System error: ' . $e->getMessage()]
            ];
        }
    }
    
    /**
     * Check if student is enrolled in course
     * 
     * @param int $courseId Course ID
     * @param int $studentId Student ID
     * @return bool True if enrolled, false otherwise
     */
    private function isStudentEnrolledInCourse($courseId, $studentId) {
        try {
            $sql = "SELECT id FROM course_enrollments 
                   WHERE course_id = ? AND student_id = ? AND status = 'active'";
            $result = $this->db->fetchRow($sql, [$courseId, $studentId]);
            return !empty($result);
        } catch (Exception $e) {
            logError("Failed to check enrollment: " . $e->getMessage(), [
                'course_id' => $courseId,
                'student_id' => $studentId
            ]);
            return false;
        }
    }
    
    /**
     * Log attendance actions
     * 
     * @param int $userId User ID (student or employee)
     * @param string $action Action type (attendance_marked, attendance_updated, clock_in, clock_out, manual_edit)
     * @param int|null $attendanceId Attendance record ID
     * @param string|null $timestamp Action timestamp
     * @param string $location Location information
     * @param array $additionalData Additional data to log
     */
    private function logAction($userId, $action, $attendanceId = null, $timestamp = null, $location = '', $additionalData = []) {
        try {
            $logData = [
                'user_id' => $userId,
                'action' => $action,
                'attendance_id' => $attendanceId,
                'timestamp' => $timestamp ?: date('Y-m-d H:i:s'),
                'ip_address' => getClientIP(),
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
                'location' => $location,
                'additional_data' => json_encode($additionalData),
                'created_at' => date('Y-m-d H:i:s')
            ];
            
            $this->db->insert($this->logsTable, $logData);
            
        } catch (Exception $e) {
            logError("Failed to log attendance action: " . $e->getMessage(), [
                'user_id' => $userId,
                'action' => $action
            ]);
        }
    }
    
    /**
     * Validate datetime format
     * 
     * @param string $datetime Datetime string
     * @return bool True if valid, false otherwise
     */
    private function validateDateTime($datetime) {
        $dateTime = DateTime::createFromFormat('Y-m-d H:i:s', $datetime);
        return $dateTime && $dateTime->format('Y-m-d H:i:s') === $datetime;
    }
}
?>    
    /**
     * Get real-time attendance statistics
     * 
     * @param string $date Date to get statistics for (Y-m-d)
     * @return array Real-time statistics
     */
    public function getRealTimeStats($date = null) {
        try {
            $date = $date ?: date('Y-m-d');
            
            // Total employees
            $totalEmployees = $this->db->fetchRow(
                "SELECT COUNT(*) as total FROM employees WHERE is_active = 1"
            )['total'];
            
            // Currently checked in
            $checkedIn = $this->db->fetchRow(
                "SELECT COUNT(*) as total FROM {$this->table} WHERE date = ? AND clock_out IS NULL",
                [$date]
            )['total'];
            
            // Completed today's attendance
            $completed = $this->db->fetchRow(
                "SELECT COUNT(*) as total FROM {$this->table} WHERE date = ? AND clock_out IS NOT NULL",
                [$date]
            )['total'];
            
            // Late arrivals (after 9 AM)
            $lateArrivals = $this->db->fetchRow(
                "SELECT COUNT(*) as total FROM {$this->table} 
                 WHERE date = ? AND HOUR(clock_in) >= 9 AND clock_in IS NOT NULL",
                [$date]
            )['total'];
            
            // Absent (total - checked in - completed)
            $absent = $totalEmployees - $checkedIn - $completed;
            
            return [
                'total_employees' => $totalEmployees,
                'checked_in' => $checkedIn,
                'completed' => $completed,
                'late_arrivals' => $lateArrivals,
                'absent' => max(0, $absent),
                'attendance_rate' => $totalEmployees > 0 ? 
                    round((($checkedIn + $completed) / $totalEmployees) * 100, 1) : 0
            ];
            
        } catch (Exception $e) {
            logError("Failed to get real-time stats: " . $e->getMessage());
            return [
                'total_employees' => 0,
                'checked_in' => 0,
                'completed' => 0,
                'late_arrivals' => 0,
                'absent' => 0,
                'attendance_rate' => 0
            ];
        }
    }
    
    /**
     * Bulk attendance operations
     * 
     * @param array $employeeIds Array of employee IDs
     * @param string $action Action to perform (bulk_clock_in, bulk_clock_out)
     * @param string $location Location information
     * @param string $notes Notes for the action
     * @return array Results for each employee
     */
    public function bulkOperation($employeeIds, $action, $location = '', $notes = '') {
        $results = [];
        
        foreach ($employeeIds as $employeeId) {
            try {
                if ($action === 'bulk_clock_in') {
                    $result = $this->clockIn($employeeId, $location, $notes . ' (Bulk action)');
                } elseif ($action === 'bulk_clock_out') {
                    $result = $this->clockOut($employeeId, $location, $notes . ' (Bulk action)');
                } else {
                    $result = ['success' => false, 'errors' => ['Invalid action']];
                }
                
                $results[$employeeId] = $result;
                
            } catch (Exception $e) {
                $results[$employeeId] = [
                    'success' => false,
                    'errors' => ['System error: ' . $e->getMessage()]
                ];
            }
        }
        
        return $results;
    }
    
    /**
     * Get attendance notifications and alerts
     * 
     * @return array Notifications
     */
    public function getNotifications() {
        try {
            $notifications = [];
            
            // Employees who are still checked in from yesterday (overnight)
            $overnightEmployees = $this->db->fetchAll(
                "SELECT a.*, e.first_name, e.last_name 
                 FROM {$this->table} a 
                 JOIN employees e ON a.employee_id = e.id 
                 WHERE a.date < CURDATE() AND a.clock_out IS NULL"
            );
            
            foreach ($overnightEmployees as $employee) {
                $notifications[] = [
                    'type' => 'warning',
                    'title' => 'Overnight Check-in',
                    'message' => $employee['first_name'] . ' ' . $employee['last_name'] . ' has been checked in since ' . 
                               formatDateTime($employee['clock_in']),
                    'employee_id' => $employee['employee_id'],
                    'attendance_id' => $employee['id']
                ];
            }
            
            // Employees with suspicious long hours
            $longHoursEmployees = $this->db->fetchAll(
                "SELECT a.*, e.first_name, e.last_name 
                 FROM {$this->table} a 
                 JOIN employees e ON a.employee_id = e.id 
                 WHERE a.date = CURDATE() AND a.hours_worked > 12"
            );
            
            foreach ($longHoursEmployees as $employee) {
                $notifications[] = [
                    'type' => 'info',
                    'title' => 'Long Hours Detected',
                    'message' => $employee['first_name'] . ' ' . $employee['last_name'] . ' has worked ' . 
                               $employee['hours_worked'] . ' hours today',
                    'employee_id' => $employee['employee_id'],
                    'attendance_id' => $employee['id']
                ];
            }
            
            return $notifications;
            
        } catch (Exception $e) {
            logError("Failed to get notifications: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Export attendance data in various formats
     * 
     * @param array $filters Filter parameters
     * @param string $format Export format (csv, excel, pdf)
     * @param string $filename Output filename
     * @return string|array Export data
     */
    public function exportData($filters = [], $format = 'csv', $filename = null) {
        try {
            $filename = $filename ?: 'attendance_export_' . date('Y-m-d_H-i-s');
            
            // Get all filtered data
            $allData = $this->getAllAttendance($filters, 10000, 0);
            $records = $allData['records'];
            
            if ($format === 'csv') {
                return $this->exportToCSV($records, $filename);
            } elseif ($format === 'excel') {
                return $this->exportToExcel($records, $filename);
            } elseif ($format === 'pdf') {
                return $this->exportToPDF($records, $filename);
            } else {
                throw new Exception("Unsupported export format: $format");
            }
            
        } catch (Exception $e) {
            logError("Export failed: " . $e->getMessage());
            return ['error' => $e->getMessage()];
        }
    }
    
    /**
     * Export data to CSV format
     */
    private function exportToCSV($records, $filename) {
        $csvData = [];
        
        // CSV headers
        $csvData[] = [
            'Date', 'Employee Name', 'Employee ID', 'Department', 'Position',
            'Clock In', 'Clock Out', 'Hours Worked', 'Status', 'Location', 'Notes'
        ];
        
        // CSV rows
        foreach ($records as $record) {
            $csvData[] = [
                formatDate($record['date']),
                ($record['first_name'] ?? '') . ' ' . ($record['last_name'] ?? ''),
                $record['employee_id'],
                $record['department_name'] ?? '',
                $record['position'] ?? '',
                formatDateTime($record['clock_in']),
                $record['clock_out'] ? formatDateTime($record['clock_out']) : 'Still checked in',
                $record['hours_worked'] ?? 0,
                $record['clock_out'] ? 'Complete' : 'Incomplete',
                $record['location'] ?? '',
                $record['notes'] ?? ''
            ];
        }
        
        return [
            'filename' => $filename . '.csv',
            'data' => $this->arrayToCSV($csvData)
        ];
    }
    
    /**
     * Convert array to CSV string
     */
    private function arrayToCSV($array) {
        $output = fopen('php://temp', 'r+');
        
        foreach ($array as $row) {
            fputcsv($output, $row);
        }
        
        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);
        
        return $csv;
    }
    
    /**
     * Get attendance analytics and insights
     * 
     * @param array $filters Filter parameters
     * @return array Analytics data
     */
    public function getAnalytics($filters = []) {
        try {
            $analytics = [];
            
            // Attendance patterns by day of week
            $dayPatterns = $this->db->fetchAll(
                "SELECT DAYNAME(date) as day_name, 
                        COUNT(*) as total_records,
                        AVG(hours_worked) as avg_hours,
                        SUM(hours_worked) as total_hours
                 FROM {$this->table} 
                 WHERE date BETWEEN ? AND ?
                 GROUP BY DAYNAME(date), DAYOFWEEK(date)
                 ORDER BY DAYOFWEEK(date)",
                [$filters['start_date'], $filters['end_date']]
            );
            $analytics['day_patterns'] = $dayPatterns;
            
            // Monthly trends
            $monthlyTrends = $this->db->fetchAll(
                "SELECT DATE_FORMAT(date, '%Y-%m') as month,
                        COUNT(DISTINCT employee_id) as active_employees,
                        SUM(hours_worked) as total_hours,
                        AVG(hours_worked) as avg_hours_per_day
                 FROM {$this->table} 
                 WHERE date BETWEEN ? AND ?
                 GROUP BY DATE_FORMAT(date, '%Y-%m')
                 ORDER BY month",
                [$filters['start_date'], $filters['end_date']]
            );
            $analytics['monthly_trends'] = $monthlyTrends;
            
            // Late arrival analysis
            $lateAnalysis = $this->db->fetchAll(
                "SELECT employee_id, 
                        COUNT(*) as late_days,
                        AVG(TIMESTAMPDIFF(MINUTE, date + INTERVAL 9 HOUR, clock_in)) as avg_late_minutes
                 FROM {$this->table} 
                 WHERE date BETWEEN ? AND ? 
                   AND HOUR(clock_in) >= 9
                 GROUP BY employee_id
                 ORDER BY late_days DESC",
                [$filters['start_date'], $filters['end_date']]
            );
            $analytics['late_analysis'] = $lateAnalysis;
            
            // Peak hours analysis
            $peakHours = $this->db->fetchAll(
                "SELECT HOUR(clock_in) as hour,
                        COUNT(*) as check_ins
                 FROM {$this->table} 
                 WHERE date BETWEEN ? AND ?
                 GROUP BY HOUR(clock_in)
                 ORDER BY hour",
                [$filters['start_date'], $filters['end_date']]
            );
            $analytics['peak_hours'] = $peakHours;
            
            return $analytics;
            
        } catch (Exception $e) {
            logError("Failed to get analytics: " . $e->getMessage());
            return [];
        }
    }
}