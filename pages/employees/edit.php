<?php
$page_title = "Edit Employee";
$page_description = "Edit employee information";

include 'header.php';

// Get employee ID from URL
$employee_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$employee_id) {
    $_SESSION['error_message'] = 'Invalid employee ID.';
    header('Location: index.php');
    exit();
}

// Get employee data
$emp = $employee->readOne($employee_id);

if (!$emp) {
    $_SESSION['error_message'] = 'Employee not found.';
    header('Location: index.php');
    exit();
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $_SESSION['error_message'] = 'Security token mismatch. Please try again.';
        header('Location: edit.php?id=' . $employee_id);
        exit();
    }
    
    // Sanitize and validate input data
    $data = [
        'first_name' => sanitizeInput($_POST['first_name']),
        'last_name' => sanitizeInput($_POST['last_name']),
        'email' => sanitizeInput($_POST['email']),
        'phone' => sanitizeInput($_POST['phone']),
        'position' => sanitizeInput($_POST['position']),
        'department' => sanitizeInput($_POST['department']),
        'hire_date' => sanitizeInput($_POST['hire_date']),
        'salary' => floatval($_POST['salary']),
        'address' => sanitizeInput($_POST['address']),
        'emergency_contact' => sanitizeInput($_POST['emergency_contact']),
        'emergency_phone' => sanitizeInput($_POST['emergency_phone']),
        'notes' => sanitizeInput($_POST['notes'])
    ];
    
    // Validation
    $errors = [];
    
    // Required fields
    if (empty($data['first_name'])) $errors[] = 'First name is required.';
    if (empty($data['last_name'])) $errors[] = 'Last name is required.';
    if (empty($data['email'])) $errors[] = 'Email is required.';
    if (empty($data['phone'])) $errors[] = 'Phone number is required.';
    if (empty($data['position'])) $errors[] = 'Position is required.';
    if (empty($data['department'])) $errors[] = 'Department is required.';
    if (empty($data['hire_date'])) $errors[] = 'Hire date is required.';
    if (empty($data['salary'])) $errors[] = 'Salary is required.';
    
    // Email format validation
    if (!empty($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    }
    
    // Phone format validation
    if (!empty($data['phone']) && !preg_match('/^[\+]?[1-9][\d]{0,15}$/', str_replace([' ', '-', '(', ')'], '', $data['phone']))) {
        $errors[] = 'Please enter a valid phone number.';
    }
    
    // Date validation
    if (!empty($data['hire_date'])) {
        $hireDate = strtotime($data['hire_date']);
        if ($hireDate === false || $hireDate > time()) {
            $errors[] = 'Please enter a valid hire date.';
        }
    }
    
    // Salary validation
    if ($data['salary'] < 0 || $data['salary'] > 999999) {
        $errors[] = 'Please enter a valid salary amount.';
    }
    
    // Check if email already exists (exclude current employee)
    if (!empty($data['email']) && $employee->emailExists($data['email'], $employee_id)) {
        $errors[] = 'Another employee with this email address already exists.';
    }
    
    if (empty($errors)) {
        // Update employee
        if ($employee->update($employee_id, $data)) {
            $_SESSION['success_message'] = 'Employee updated successfully!';
            header('Location: view.php?id=' . $employee_id);
            exit();
        } else {
            $_SESSION['error_message'] = 'Failed to update employee. Please try again.';
        }
    } else {
        $_SESSION['error_message'] = implode(' ', $errors);
        // Store form data for repopulation
        $_SESSION['form_data'] = $data;
        // Refresh employee data to merge with form data
        $emp = array_merge($emp, $data);
    }
}

// Get departments for dropdown
$departments = $employee->getDepartments();

// Add any default departments if none exist
if (empty($departments)) {
    $departments = ['Human Resources', 'Engineering', 'Marketing', 'Sales', 'Finance', 'Operations', 'Customer Support', 'Administration'];
}

// Get form data for repopulation if there was an error
$formData = isset($_SESSION['form_data']) ? $_SESSION['form_data'] : [];
unset($_SESSION['form_data']);

// Merge employee data with form data if available
if (!empty($formData)) {
    $emp = array_merge($emp, $formData);
}
?>

<div class="row mb-4">
    <div class="col-md-8">
        <h2><i class="fas fa-user-edit me-2"></i>Edit Employee</h2>
        <p class="text-muted">
            Editing: <strong><?php echo htmlspecialchars($emp['first_name'] . ' ' . $emp['last_name']); ?></strong>
            <span class="text-muted">| Employee ID: <?php echo str_pad($emp['id'], 4, '0', STR_PAD_LEFT); ?></span>
        </p>
    </div>
    <div class="col-md-4 text-end">
        <a href="view.php?id=<?php echo $emp['id']; ?>" class="btn btn-info me-2">
            <i class="fas fa-eye me-2"></i>View Details
        </a>
        <a href="index.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Back to List
        </a>
    </div>
</div>

<form method="POST" id="editEmployeeForm" novalidate>
    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
    
    <div class="row">
        <!-- Personal Information -->
        <div class="col-md-8">
            <div class="form-section">
                <h5 class="form-section-title"><i class="fas fa-user me-2"></i>Personal Information</h5>
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="first_name" class="form-label required-field">First Name</label>
                            <input type="text" class="form-control" id="first_name" name="first_name" 
                                   value="<?php echo htmlspecialchars($emp['first_name']); ?>" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="last_name" class="form-label required-field">Last Name</label>
                            <input type="text" class="form-control" id="last_name" name="last_name" 
                                   value="<?php echo htmlspecialchars($emp['last_name']); ?>" required>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="email" class="form-label required-field">Email Address</label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   value="<?php echo htmlspecialchars($emp['email']); ?>" required>
                            <div class="form-text">This will be used for login and communications</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="phone" class="form-label required-field">Phone Number</label>
                            <input type="tel" class="form-control" id="phone" name="phone" 
                                   value="<?php echo htmlspecialchars($emp['phone']); ?>" required>
                        </div>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="address" class="form-label">Address</label>
                    <textarea class="form-control" id="address" name="address" rows="3" 
                              placeholder="Enter full address"><?php echo htmlspecialchars($emp['address']); ?></textarea>
                </div>
            </div>
        </div>
        
        <!-- Employee Stats -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Employee Stats</h6>
                </div>
                <div class="card-body">
                    <div class="text-center">
                        <div class="employee-avatar mb-3 mx-auto">
                            <?php echo strtoupper(substr($emp['first_name'], 0, 1) . substr($emp['last_name'], 0, 1)); ?>
                        </div>
                        <div class="mb-2">
                            <strong><?php echo htmlspecialchars($emp['first_name'] . ' ' . $emp['last_name']); ?></strong>
                        </div>
                        <div class="mb-2">
                            <span class="badge bg-info"><?php echo htmlspecialchars($emp['position']); ?></span>
                        </div>
                        <div class="mb-2">
                            <span class="badge bg-secondary"><?php echo htmlspecialchars($emp['department']); ?></span>
                        </div>
                        <hr>
                        <div class="small text-muted">
                            <div><i class="fas fa-calendar me-1"></i>Joined: <?php echo formatDate($emp['hire_date']); ?></div>
                            <div><i class="fas fa-clock me-1"></i>
                                Years of service: <?php echo floor((time() - strtotime($emp['hire_date'])) / (365.25 * 24 * 60 * 60)); ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Employment Information -->
    <div class="form-section">
        <h5 class="form-section-title"><i class="fas fa-briefcase me-2"></i>Employment Information</h5>
        <div class="row">
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="position" class="form-label required-field">Position/Job Title</label>
                    <input type="text" class="form-control" id="position" name="position" 
                           value="<?php echo htmlspecialchars($emp['position']); ?>" required>
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="department" class="form-label required-field">Department</label>
                    <select class="form-select" id="department" name="department" required>
                        <option value="">Select Department</option>
                        <?php foreach ($departments as $dept): ?>
                            <option value="<?php echo htmlspecialchars($dept); ?>" 
                                    <?php echo ($emp['department'] === $dept) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($dept); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="hire_date" class="form-label required-field">Hire Date</label>
                    <input type="date" class="form-control" id="hire_date" name="hire_date" 
                           value="<?php echo htmlspecialchars($emp['hire_date']); ?>" required>
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="salary" class="form-label required-field">Annual Salary</label>
                    <div class="input-group">
                        <span class="input-group-text">$</span>
                        <input type="number" class="form-control" id="salary" name="salary" 
                               value="<?php echo htmlspecialchars($emp['salary']); ?>" 
                               min="0" max="999999" step="0.01" required>
                    </div>
                    <div class="form-text">Enter annual salary in dollars</div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Emergency Contact -->
    <div class="form-section">
        <h5 class="form-section-title"><i class="fas fa-phone-alt me-2"></i>Emergency Contact</h5>
        <div class="row">
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="emergency_contact" class="form-label">Emergency Contact Name</label>
                    <input type="text" class="form-control" id="emergency_contact" name="emergency_contact" 
                           value="<?php echo htmlspecialchars($emp['emergency_contact']); ?>">
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="emergency_phone" class="form-label">Emergency Contact Phone</label>
                    <input type="tel" class="form-control" id="emergency_phone" name="emergency_phone" 
                           value="<?php echo htmlspecialchars($emp['emergency_phone']); ?>">
                </div>
            </div>
        </div>
    </div>
    
    <!-- Additional Notes -->
    <div class="form-section">
        <h5 class="form-section-title"><i class="fas fa-sticky-note me-2"></i>Additional Notes</h5>
        <div class="mb-3">
            <label for="notes" class="form-label">Notes</label>
            <textarea class="form-control" id="notes" name="notes" rows="4" 
                      placeholder="Additional notes about the employee..."><?php echo htmlspecialchars($emp['notes']); ?></textarea>
        </div>
    </div>
    
    <!-- Record Information -->
    <div class="form-section">
        <h5 class="form-section-title"><i class="fas fa-info-circle me-2"></i>Record Information</h5>
        <div class="row">
            <div class="col-md-6">
                <div class="text-muted small">
                    <div><i class="fas fa-calendar-plus me-1"></i>Created: <?php echo formatDate($emp['created_at']); ?></div>
                    <?php if ($emp['updated_at']): ?>
                        <div><i class="fas fa-calendar-edit me-1"></i>Last Updated: <?php echo formatDate($emp['updated_at']); ?></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Form Actions -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="d-flex justify-content-between">
                <div>
                    <a href="view.php?id=<?php echo $emp['id']; ?>" class="btn btn-info me-2">
                        <i class="fas fa-eye me-2"></i>View Details
                    </a>
                    <a href="index.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Back to List
                    </a>
                </div>
                <div>
                    <button type="reset" class="btn btn-outline-secondary me-2">
                        <i class="fas fa-undo me-2"></i>Reset
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Update Employee
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>

<script>
// Auto-format salary input
document.getElementById('salary').addEventListener('input', function() {
    let value = this.value.replace(/[^\d.]/g, '');
    if (value && !isNaN(value)) {
        this.value = parseFloat(value).toFixed(2);
    }
});
</script>

<?php include 'footer.php'; ?>