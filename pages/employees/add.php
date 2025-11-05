<?php
$page_title = "Add Employee";
$page_description = "Add a new employee to the system";

include 'header.php';

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $_SESSION['error_message'] = 'Security token mismatch. Please try again.';
        header('Location: add.php');
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
    
    // Check if email already exists
    if (!empty($data['email']) && $employee->emailExists($data['email'])) {
        $errors[] = 'An employee with this email address already exists.';
    }
    
    if (empty($errors)) {
        // Insert employee
        if ($employee->create($data)) {
            $_SESSION['success_message'] = 'Employee added successfully!';
            header('Location: index.php');
            exit();
        } else {
            $_SESSION['error_message'] = 'Failed to add employee. Please try again.';
        }
    } else {
        $_SESSION['error_message'] = implode(' ', $errors);
        // Store form data for repopulation
        $_SESSION['form_data'] = $data;
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
?>

<div class="row mb-4">
    <div class="col-md-8">
        <h2><i class="fas fa-user-plus me-2"></i>Add New Employee</h2>
        <p class="text-muted">Enter the employee's information below</p>
    </div>
    <div class="col-md-4 text-end">
        <a href="index.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Back to List
        </a>
    </div>
</div>

<form method="POST" id="addEmployeeForm" novalidate>
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
                                   value="<?php echo isset($formData['first_name']) ? htmlspecialchars($formData['first_name']) : ''; ?>" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="last_name" class="form-label required-field">Last Name</label>
                            <input type="text" class="form-control" id="last_name" name="last_name" 
                                   value="<?php echo isset($formData['last_name']) ? htmlspecialchars($formData['last_name']) : ''; ?>" required>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="email" class="form-label required-field">Email Address</label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   value="<?php echo isset($formData['email']) ? htmlspecialchars($formData['email']) : ''; ?>" required>
                            <div class="form-text">This will be used for login and communications</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="phone" class="form-label required-field">Phone Number</label>
                            <input type="tel" class="form-control" id="phone" name="phone" 
                                   value="<?php echo isset($formData['phone']) ? htmlspecialchars($formData['phone']) : ''; ?>" required>
                        </div>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="address" class="form-label">Address</label>
                    <textarea class="form-control" id="address" name="address" rows="3" 
                              placeholder="Enter full address"><?php echo isset($formData['address']) ? htmlspecialchars($formData['address']) : ''; ?></textarea>
                </div>
            </div>
        </div>
        
        <!-- Quick Stats -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Quick Info</h6>
                </div>
                <div class="card-body">
                    <div class="text-center">
                        <div class="employee-avatar mb-3 mx-auto">
                            ?
                        </div>
                        <p class="text-muted small">Employee preview will appear here</p>
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
                           value="<?php echo isset($formData['position']) ? htmlspecialchars($formData['position']) : ''; ?>" required>
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="department" class="form-label required-field">Department</label>
                    <select class="form-select" id="department" name="department" required>
                        <option value="">Select Department</option>
                        <?php foreach ($departments as $dept): ?>
                            <option value="<?php echo htmlspecialchars($dept); ?>" 
                                    <?php echo (isset($formData['department']) && $formData['department'] === $dept) ? 'selected' : ''; ?>>
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
                           value="<?php echo isset($formData['hire_date']) ? htmlspecialchars($formData['hire_date']) : date('Y-m-d'); ?>" required>
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="salary" class="form-label required-field">Annual Salary</label>
                    <div class="input-group">
                        <span class="input-group-text">$</span>
                        <input type="number" class="form-control" id="salary" name="salary" 
                               value="<?php echo isset($formData['salary']) ? htmlspecialchars($formData['salary']) : ''; ?>" 
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
                           value="<?php echo isset($formData['emergency_contact']) ? htmlspecialchars($formData['emergency_contact']) : ''; ?>">
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="emergency_phone" class="form-label">Emergency Contact Phone</label>
                    <input type="tel" class="form-control" id="emergency_phone" name="emergency_phone" 
                           value="<?php echo isset($formData['emergency_phone']) ? htmlspecialchars($formData['emergency_phone']) : ''; ?>">
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
                      placeholder="Additional notes about the employee..."><?php echo isset($formData['notes']) ? htmlspecialchars($formData['notes']) : ''; ?></textarea>
        </div>
    </div>
    
    <!-- Form Actions -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="d-flex justify-content-between">
                <a href="index.php" class="btn btn-secondary">
                    <i class="fas fa-times me-2"></i>Cancel
                </a>
                <div>
                    <button type="reset" class="btn btn-outline-secondary me-2">
                        <i class="fas fa-undo me-2"></i>Reset
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Add Employee
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>

<script>
// Update avatar preview when name is entered
function updateAvatar() {
    const firstName = document.getElementById('first_name').value.trim();
    const lastName = document.getElementById('last_name').value.trim();
    const avatar = document.querySelector('.employee-avatar');
    
    if (firstName || lastName) {
        const initials = (firstName.charAt(0) + lastName.charAt(0)).toUpperCase();
        avatar.textContent = initials;
    } else {
        avatar.textContent = '?';
    }
}

document.getElementById('first_name').addEventListener('input', updateAvatar);
document.getElementById('last_name').addEventListener('input', updateAvatar);

// Auto-format salary input
document.getElementById('salary').addEventListener('input', function() {
    let value = this.value.replace(/[^\d.]/g, '');
    if (value && !isNaN(value)) {
        this.value = parseFloat(value).toFixed(2);
    }
});
</script>

<?php include 'footer.php'; ?>