<?php
require_once '../../includes/config.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

// Check if user is logged in and has admin privileges
if (!isLoggedIn() || !isAdmin()) {
    header('Location: ../../login.php');
    exit();
}

$pageTitle = "Employee Management";
$pdo = getDBConnection();

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'add_employee':
            $employeeId = trim($_POST['employee_id']);
            $name = trim($_POST['name']);
            $email = trim($_POST['email']);
            $phone = trim($_POST['phone']);
            $department = trim($_POST['department']);
            $position = trim($_POST['position']);
            $hireDate = $_POST['hire_date'];
            $salary = floatval($_POST['salary']);
            $address = trim($_POST['address']);
            $emergencyContact = trim($_POST['emergency_contact']);
            $emergencyPhone = trim($_POST['emergency_phone']);
            $status = $_POST['status'];
            
            if (!empty($employeeId) && !empty($name) && !empty($email)) {
                // Check if employee ID already exists
                $stmt = $pdo->prepare("SELECT id FROM employees WHERE employee_id = ? OR email = ?");
                $stmt->execute([$employeeId, $email]);
                
                if ($stmt->fetch()) {
                    $error = "Employee ID or email already exists.";
                } else {
                    $stmt = $pdo->prepare("INSERT INTO employees (employee_id, name, email, phone, department, position, hire_date, salary, address, emergency_contact, emergency_phone, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
                    
                    if ($stmt->execute([$employeeId, $name, $email, $phone, $department, $position, $hireDate, $salary, $address, $emergencyContact, $emergencyPhone, $status])) {
                        $success = "Employee added successfully.";
                        
                        // Log activity
                        logActivity($_SESSION['user_id'], "Employee Management", "Added employee: $name ($employeeId)");
                    } else {
                        $error = "Failed to add employee.";
                    }
                }
            } else {
                $error = "Employee ID, name, and email are required.";
            }
            break;
            
        case 'edit_employee':
            $employeeId = (int)$_POST['employee_id'];
            $name = trim($_POST['name']);
            $email = trim($_POST['email']);
            $phone = trim($_POST['phone']);
            $department = trim($_POST['department']);
            $position = trim($_POST['position']);
            $hireDate = $_POST['hire_date'];
            $salary = floatval($_POST['salary']);
            $address = trim($_POST['address']);
            $emergencyContact = trim($_POST['emergency_contact']);
            $emergencyPhone = trim($_POST['emergency_phone']);
            $status = $_POST['status'];
            
            if (!empty($name) && !empty($email)) {
                // Check if email already exists (excluding current employee)
                $stmt = $pdo->prepare("SELECT id FROM employees WHERE email = ? AND id != ?");
                $stmt->execute([$email, $employeeId]);
                
                if ($stmt->fetch()) {
                    $error = "Email already exists.";
                } else {
                    $stmt = $pdo->prepare("UPDATE employees SET name = ?, email = ?, phone = ?, department = ?, position = ?, hire_date = ?, salary = ?, address = ?, emergency_contact = ?, emergency_phone = ?, status = ?, updated_at = NOW() WHERE id = ?");
                    
                    if ($stmt->execute([$name, $email, $phone, $department, $position, $hireDate, $salary, $address, $emergencyContact, $emergencyPhone, $status, $employeeId])) {
                        $success = "Employee updated successfully.";
                        
                        // Log activity
                        logActivity($_SESSION['user_id'], "Employee Management", "Updated employee: $name");
                    } else {
                        $error = "Failed to update employee.";
                    }
                }
            } else {
                $error = "Name and email are required.";
            }
            break;
            
        case 'delete_employee':
            $employeeId = (int)$_POST['employee_id'];
            
            // Get employee name for logging
            $stmt = $pdo->prepare("SELECT name FROM employees WHERE id = ?");
            $stmt->execute([$employeeId]);
            $employeeName = $stmt->fetch()['name'];
            
            // Check if employee has attendance records
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM attendance WHERE employee_id = ?");
            $stmt->execute([$employeeId]);
            $attendanceCount = $stmt->fetchColumn();
            
            if ($attendanceCount > 0) {
                $error = "Cannot delete employee with existing attendance records. Please deactivate instead.";
            } else {
                $stmt = $pdo->prepare("DELETE FROM employees WHERE id = ?");
                
                if ($stmt->execute([$employeeId])) {
                    $success = "Employee deleted successfully.";
                    
                    // Log activity
                    logActivity($_SESSION['user_id'], "Employee Management", "Deleted employee: $employeeName");
                } else {
                    $error = "Failed to delete employee.";
                }
            }
            break;
            
        case 'bulk_import':
            if (isset($_FILES['csv_file']) && $_FILES['csv_file']['error'] === UPLOAD_ERR_OK) {
                $csvFile = $_FILES['csv_file']['tmp_name'];
                $handle = fopen($csvFile, 'r');
                
                if ($handle) {
                    $header = fgetcsv($handle);
                    $imported = 0;
                    $errors = [];
                    
                    while (($data = fgetcsv($handle)) !== false) {
                        if (count($data) >= 3) { // At least employee_id, name, email
                            $employeeId = trim($data[0]);
                            $name = trim($data[1]);
                            $email = trim($data[2]);
                            $phone = isset($data[3]) ? trim($data[3]) : '';
                            $department = isset($data[4]) ? trim($data[4]) : '';
                            $position = isset($data[5]) ? trim($data[5]) : '';
                            
                            if (!empty($employeeId) && !empty($name) && !empty($email)) {
                                // Check if employee exists
                                $stmt = $pdo->prepare("SELECT id FROM employees WHERE employee_id = ? OR email = ?");
                                $stmt->execute([$employeeId, $email]);
                                
                                if (!$stmt->fetch()) {
                                    $stmt = $pdo->prepare("INSERT INTO employees (employee_id, name, email, phone, department, position, status, created_at) VALUES (?, ?, ?, ?, ?, ?, 'active', NOW())");
                                    
                                    if ($stmt->execute([$employeeId, $name, $email, $phone, $department, $position])) {
                                        $imported++;
                                    }
                                }
                            }
                        }
                    }
                    
                    fclose($handle);
                    
                    $success = "Successfully imported $imported employees.";
                    
                    // Log activity
                    logActivity($_SESSION['user_id'], "Employee Management", "Bulk imported $imported employees");
                } else {
                    $error = "Failed to open CSV file.";
                }
            } else {
                $error = "Please select a valid CSV file.";
            }
            break;
    }
}

// Get all employees with pagination
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$limit = 20;
$offset = ($page - 1) * $limit;

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$departmentFilter = isset($_GET['department']) ? trim($_GET['department']) : '';

$whereClause = "WHERE 1=1";
$params = [];

if (!empty($search)) {
    $whereClause .= " AND (name LIKE ? OR employee_id LIKE ? OR email LIKE ?)";
    $searchTerm = "%$search%";
    $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm]);
}

if (!empty($departmentFilter)) {
    $whereClause .= " AND department = ?";
    $params[] = $departmentFilter;
}

// Get total count
$countQuery = "SELECT COUNT(*) FROM employees $whereClause";
$stmt = $pdo->prepare($countQuery);
$stmt->execute($params);
$totalEmployees = $stmt->fetchColumn();

$totalPages = ceil($totalEmployees / $limit);

// Get employees
$query = "SELECT * FROM employees $whereClause ORDER BY name ASC LIMIT $limit OFFSET $offset";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$employees = $stmt->fetchAll();

// Get available departments for filter
$stmt = $pdo->query("SELECT DISTINCT department FROM employees WHERE department IS NOT NULL AND department != '' ORDER BY department");
$departments = $stmt->fetchAll();

// Get employee for editing
$editEmployee = null;
if (isset($_GET['edit'])) {
    $editId = (int)$_GET['edit'];
    $stmt = $pdo->prepare("SELECT * FROM employees WHERE id = ?");
    $stmt->execute([$editId]);
    $editEmployee = $stmt->fetch();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - Attendance System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../../assets/css/dashboard.css" rel="stylesheet">
</head>
<body>
    <?php include '../../includes/header.php'; ?>
    
    <div class="container-fluid">
        <div class="row">
            <?php include '../../includes/sidebar.php'; ?>
            
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2"><?php echo $pageTitle; ?></h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addEmployeeModal">
                                <i class="fas fa-plus"></i> Add Employee
                            </button>
                            <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#importModal">
                                <i class="fas fa-upload"></i> Bulk Import
                            </button>
                        </div>
                    </div>
                </div>

                <?php if (isset($success)): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?php echo $success; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if (isset($error)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?php echo $error; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- Search and Filter -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <div class="col-md-4">
                                <input type="text" class="form-control" name="search" placeholder="Search employees..." 
                                       value="<?php echo htmlspecialchars($search); ?>">
                            </div>
                            <div class="col-md-3">
                                <select class="form-select" name="department">
                                    <option value="">All Departments</option>
                                    <?php foreach ($departments as $dept): ?>
                                        <option value="<?php echo htmlspecialchars($dept['department']); ?>" 
                                                <?php echo ($dept['department'] == $departmentFilter) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($dept['department']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search"></i> Search
                                </button>
                            </div>
                            <div class="col-md-3">
                                <a href="employees.php" class="btn btn-secondary">
                                    <i class="fas fa-refresh"></i> Reset
                                </a>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Employee List -->
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Employee ID</th>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Phone</th>
                                        <th>Department</th>
                                        <th>Position</th>
                                        <th>Hire Date</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($employees)): ?>
                                        <tr>
                                            <td colspan="9" class="text-center">No employees found.</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($employees as $employee): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($employee['employee_id']); ?></td>
                                                <td><?php echo htmlspecialchars($employee['name']); ?></td>
                                                <td><?php echo htmlspecialchars($employee['email']); ?></td>
                                                <td><?php echo htmlspecialchars($employee['phone']); ?></td>
                                                <td><?php echo htmlspecialchars($employee['department']); ?></td>
                                                <td><?php echo htmlspecialchars($employee['position']); ?></td>
                                                <td><?php echo $employee['hire_date'] ? date('Y-m-d', strtotime($employee['hire_date'])) : '-'; ?></td>
                                                <td>
                                                    <span class="badge bg-<?php echo $employee['status'] === 'active' ? 'success' : 'secondary'; ?>">
                                                        <?php echo ucfirst($employee['status']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <button type="button" class="btn btn-sm btn-outline-primary" 
                                                            onclick="editEmployee(<?php echo htmlspecialchars(json_encode($employee)); ?>)">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-outline-danger" 
                                                            onclick="deleteEmployee(<?php echo $employee['id']; ?>, '<?php echo htmlspecialchars($employee['name']); ?>')">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Pagination -->
                        <?php if ($totalPages > 1): ?>
                            <nav aria-label="Employee pagination">
                                <ul class="pagination justify-content-center">
                                    <?php if ($page > 1): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>&department=<?php echo urlencode($departmentFilter); ?>">Previous</a>
                                        </li>
                                    <?php endif; ?>
                                    
                                    <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                                        <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                            <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&department=<?php echo urlencode($departmentFilter); ?>"><?php echo $i; ?></a>
                                        </li>
                                    <?php endfor; ?>
                                    
                                    <?php if ($page < $totalPages): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>&department=<?php echo urlencode($departmentFilter); ?>">Next</a>
                                        </li>
                                    <?php endif; ?>
                                </ul>
                            </nav>
                        <?php endif; ?>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Add Employee Modal -->
    <div class="modal fade" id="addEmployeeModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title">Add New Employee</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add_employee">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Employee ID *</label>
                                    <input type="text" class="form-control" name="employee_id" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Full Name *</label>
                                    <input type="text" class="form-control" name="name" required>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Email *</label>
                                    <input type="email" class="form-control" name="email" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Phone</label>
                                    <input type="tel" class="form-control" name="phone">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Department</label>
                                    <input type="text" class="form-control" name="department">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Position</label>
                                    <input type="text" class="form-control" name="position">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Hire Date</label>
                                    <input type="date" class="form-control" name="hire_date" value="<?php echo date('Y-m-d'); ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Salary</label>
                                    <input type="number" step="0.01" class="form-control" name="salary">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Emergency Contact</label>
                                    <input type="text" class="form-control" name="emergency_contact">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Emergency Phone</label>
                                    <input type="tel" class="form-control" name="emergency_phone">
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Address</label>
                            <textarea class="form-control" name="address" rows="2"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select class="form-select" name="status">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                                <option value="terminated">Terminated</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Employee</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Employee Modal -->
    <div class="modal fade" id="editEmployeeModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Employee</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="action" value="edit_employee">
                        <input type="hidden" name="employee_id" id="edit_employee_id">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Employee ID *</label>
                                    <input type="text" class="form-control" id="edit_employee_id_display" readonly>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Full Name *</label>
                                    <input type="text" class="form-control" name="name" id="edit_name" required>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Email *</label>
                                    <input type="email" class="form-control" name="email" id="edit_email" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Phone</label>
                                    <input type="tel" class="form-control" name="phone" id="edit_phone">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Department</label>
                                    <input type="text" class="form-control" name="department" id="edit_department">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Position</label>
                                    <input type="text" class="form-control" name="position" id="edit_position">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Hire Date</label>
                                    <input type="date" class="form-control" name="hire_date" id="edit_hire_date">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Salary</label>
                                    <input type="number" step="0.01" class="form-control" name="salary" id="edit_salary">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Emergency Contact</label>
                                    <input type="text" class="form-control" name="emergency_contact" id="edit_emergency_contact">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Emergency Phone</label>
                                    <input type="tel" class="form-control" name="emergency_phone" id="edit_emergency_phone">
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Address</label>
                            <textarea class="form-control" name="address" id="edit_address" rows="2"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select class="form-select" name="status" id="edit_status">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                                <option value="terminated">Terminated</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update Employee</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Import Modal -->
    <div class="modal fade" id="importModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" enctype="multipart/form-data">
                    <div class="modal-header">
                        <h5 class="modal-title">Bulk Import Employees</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="action" value="bulk_import">
                        <div class="mb-3">
                            <label class="form-label">CSV File</label>
                            <input type="file" class="form-control" name="csv_file" accept=".csv" required>
                        </div>
                        <div class="alert alert-info">
                            <strong>CSV Format:</strong><br>
                            Column 1: Employee ID<br>
                            Column 2: Full Name<br>
                            Column 3: Email<br>
                            Column 4: Phone (optional)<br>
                            Column 5: Department (optional)<br>
                            Column 6: Position (optional)
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">Import</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteEmployeeModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title">Confirm Delete</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="action" value="delete_employee">
                        <input type="hidden" name="employee_id" id="delete_employee_id">
                        <p>Are you sure you want to delete employee "<span id="delete_employee_name"></span>"? This action cannot be undone.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Delete Employee</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function editEmployee(employee) {
            document.getElementById('edit_employee_id').value = employee.id;
            document.getElementById('edit_employee_id_display').value = employee.employee_id;
            document.getElementById('edit_name').value = employee.name || '';
            document.getElementById('edit_email').value = employee.email || '';
            document.getElementById('edit_phone').value = employee.phone || '';
            document.getElementById('edit_department').value = employee.department || '';
            document.getElementById('edit_position').value = employee.position || '';
            document.getElementById('edit_hire_date').value = employee.hire_date || '';
            document.getElementById('edit_salary').value = employee.salary || '';
            document.getElementById('edit_emergency_contact').value = employee.emergency_contact || '';
            document.getElementById('edit_emergency_phone').value = employee.emergency_phone || '';
            document.getElementById('edit_address').value = employee.address || '';
            document.getElementById('edit_status').value = employee.status;
            
            new bootstrap.Modal(document.getElementById('editEmployeeModal')).show();
        }
        
        function deleteEmployee(employeeId, employeeName) {
            document.getElementById('delete_employee_id').value = employeeId;
            document.getElementById('delete_employee_name').textContent = employeeName;
            new bootstrap.Modal(document.getElementById('deleteEmployeeModal')).show();
        }
    </script>
</body>
</html>