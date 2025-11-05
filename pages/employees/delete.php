<?php
$page_title = "Delete Employee";
$page_description = "Confirm employee deletion";

include 'header.php';

// Get employee ID from URL
$employee_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$employee_id) {
    $_SESSION['error_message'] = 'Invalid employee ID.';
    header('Location: index.php');
    exit();
}

// Process deletion after confirmation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_delete'])) {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $_SESSION['error_message'] = 'Security token mismatch. Please try again.';
        header('Location: delete.php?id=' . $employee_id);
        exit();
    }
    
    // Perform deletion
    if ($employee->delete($employee_id)) {
        $_SESSION['success_message'] = 'Employee has been deleted successfully.';
        header('Location: index.php');
        exit();
    } else {
        $_SESSION['error_message'] = 'Failed to delete employee. Please try again.';
        header('Location: delete.php?id=' . $employee_id);
        exit();
    }
}

// Cancel deletion
if (isset($_POST['cancel_delete'])) {
    header('Location: view.php?id=' . $employee_id);
    exit();
}

// Get employee data for display
$emp = $employee->readOne($employee_id);

if (!$emp) {
    $_SESSION['error_message'] = 'Employee not found.';
    header('Location: index.php');
    exit();
}

// Calculate employee information
$hireDate = strtotime($emp['hire_date']);
$currentDate = time();
$daysSinceHire = floor(($currentDate - $hireDate) / (60 * 60 * 24));
$yearsOfService = floor($daysSinceHire / 365.25);
?>

<div class="row mb-4">
    <div class="col-md-8">
        <h2 class="text-danger"><i class="fas fa-exclamation-triangle me-2"></i>Delete Employee</h2>
        <p class="text-muted">This action cannot be undone. Please confirm the deletion below.</p>
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

<!-- Warning Alert -->
<div class="alert alert-warning d-flex align-items-center" role="alert">
    <i class="fas fa-exclamation-triangle fa-2x me-3"></i>
    <div>
        <h5 class="alert-heading">Warning: Permanent Action</h5>
        <p class="mb-0">
            You are about to permanently delete <strong><?php echo htmlspecialchars($emp['first_name'] . ' ' . $emp['last_name']); ?></strong> 
            from the system. This action will:
        </p>
        <ul class="mb-0 mt-2">
            <li>Remove all employee information from the database</li>
            <li>Delete any associated records or references</li>
            <li>Cannot be undone or reversed</li>
            <li>May affect related attendance or payroll records</li>
        </ul>
    </div>
</div>

<div class="row">
    <!-- Employee Information -->
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-user me-2"></i>Employee Information</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <!-- Employee Photo/Avatar -->
                    <div class="col-md-3 text-center">
                        <div class="employee-avatar mb-3 mx-auto" style="width: 80px; height: 80px; font-size: 2rem;">
                            <?php echo strtoupper(substr($emp['first_name'], 0, 1) . substr($emp['last_name'], 0, 1)); ?>
                        </div>
                        <div class="mb-2">
                            <span class="badge bg-danger fs-6">
                                <i class="fas fa-trash me-1"></i>To be deleted
                            </span>
                        </div>
                    </div>
                    
                    <!-- Employee Details -->
                    <div class="col-md-9">
                        <h4 class="mb-2"><?php echo htmlspecialchars($emp['first_name'] . ' ' . $emp['last_name']); ?></h4>
                        <p class="text-muted mb-3"><?php echo htmlspecialchars($emp['position']); ?> - <?php echo htmlspecialchars($emp['department']); ?></p>
                        
                        <div class="row">
                            <div class="col-sm-6">
                                <div class="mb-2">
                                    <small class="text-muted">Employee ID:</small><br>
                                    <strong><?php echo str_pad($emp['id'], 4, '0', STR_PAD_LEFT); ?></strong>
                                </div>
                                <div class="mb-2">
                                    <small class="text-muted">Email:</small><br>
                                    <strong><?php echo htmlspecialchars($emp['email']); ?></strong>
                                </div>
                                <div class="mb-2">
                                    <small class="text-muted">Phone:</small><br>
                                    <strong><?php echo htmlspecialchars($emp['phone']); ?></strong>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="mb-2">
                                    <small class="text-muted">Hire Date:</small><br>
                                    <strong><?php echo formatDate($emp['hire_date']); ?></strong>
                                </div>
                                <div class="mb-2">
                                    <small class="text-muted">Years of Service:</small><br>
                                    <strong><?php echo $yearsOfService; ?> years</strong>
                                </div>
                                <div class="mb-2">
                                    <small class="text-muted">Annual Salary:</small><br>
                                    <strong><?php echo formatCurrency($emp['salary']); ?></strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Employment History Summary -->
        <div class="card mt-3">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-history me-2"></i>Employment Summary</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 text-center">
                        <div class="border rounded p-3">
                            <i class="fas fa-calendar-day fa-2x text-primary mb-2"></i>
                            <div>
                                <h6 class="text-muted mb-1">Days Employed</h6>
                                <h4 class="text-primary"><?php echo number_format($daysSinceHire); ?></h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 text-center">
                        <div class="border rounded p-3">
                            <i class="fas fa-building fa-2x text-info mb-2"></i>
                            <div>
                                <h6 class="text-muted mb-1">Department</h6>
                                <h5 class="text-info"><?php echo htmlspecialchars($emp['department']); ?></h5>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 text-center">
                        <div class="border rounded p-3">
                            <i class="fas fa-briefcase fa-2x text-warning mb-2"></i>
                            <div>
                                <h6 class="text-muted mb-1">Position</h6>
                                <h5 class="text-warning"><?php echo htmlspecialchars($emp['position']); ?></h5>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Deletion Confirmation -->
    <div class="col-md-4">
        <div class="card border-danger">
            <div class="card-header bg-danger text-white">
                <h5 class="mb-0"><i class="fas fa-trash me-2"></i>Confirm Deletion</h5>
            </div>
            <div class="card-body">
                <div class="text-center mb-3">
                    <i class="fas fa-exclamation-triangle fa-3x text-danger"></i>
                </div>
                <p class="text-center mb-4">
                    Are you absolutely sure you want to delete 
                    <strong><?php echo htmlspecialchars($emp['first_name'] . ' ' . $emp['last_name']); ?></strong>?
                </p>
                
                <form method="POST" id="deleteForm">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                    
                    <div class="d-grid gap-2">
                        <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#confirmDeleteModal">
                            <i class="fas fa-trash me-2"></i>Yes, Delete This Employee
                        </button>
                        <button type="submit" name="cancel_delete" class="btn btn-secondary">
                            <i class="fas fa-times me-2"></i>Cancel
                        </button>
                    </div>
                </form>
                
                <div class="mt-3">
                    <small class="text-muted">
                        <i class="fas fa-info-circle me-1"></i>
                        This action will permanently remove all employee data from the system.
                    </small>
                </div>
            </div>
        </div>
        
        <!-- Alternative Actions -->
        <div class="card mt-3">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-cog me-2"></i>Alternative Actions</h6>
            </div>
            <div class="card-body">
                <p class="text-muted mb-3">Instead of deletion, you might want to:</p>
                <div class="d-grid gap-2">
                    <a href="edit.php?id=<?php echo $emp['id']; ?>" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-edit me-2"></i>Edit Employee Details
                    </a>
                    <a href="view.php?id=<?php echo $emp['id']; ?>" class="btn btn-outline-info btn-sm">
                        <i class="fas fa-eye me-2"></i>View Full Profile
                    </a>
                    <a href="index.php" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-list me-2"></i>Back to Employee List
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-labelledby="confirmDeleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="confirmDeleteModalLabel">
                    <i class="fas fa-exclamation-triangle me-2"></i>Final Confirmation
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-3">
                    <i class="fas fa-user-slash fa-4x text-danger mb-3"></i>
                    <h5>Are you absolutely sure?</h5>
                    <p class="text-muted">
                        This will permanently delete <strong><?php echo htmlspecialchars($emp['first_name'] . ' ' . $emp['last_name']); ?></strong> 
                        and all associated information from the system.
                    </p>
                    <div class="alert alert-warning">
                        <strong>Warning:</strong> This action cannot be undone!
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i>Cancel
                </button>
                <form method="POST" style="display: inline;">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                    <button type="submit" name="confirm_delete" class="btn btn-danger">
                        <i class="fas fa-trash me-2"></i>Yes, Delete Forever
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
// Show confirmation modal on form submission
document.getElementById('deleteForm').addEventListener('submit', function(e) {
    // Prevent immediate submission - let the modal handle it
    e.preventDefault();
    
    // Update modal content with specific employee name
    const employeeName = '<?php echo addslashes($emp['first_name'] . ' ' . $emp['last_name']); ?>';
    document.querySelector('#confirmDeleteModal p').innerHTML = 
        `This will permanently delete <strong>${employeeName}</strong> and all associated information from the system.`;
    
    // Show the modal
    const modal = new bootstrap.Modal(document.getElementById('confirmDeleteModal'));
    modal.show();
});

// Prevent accidental deletion on accidental button clicks
document.querySelector('.btn-danger').addEventListener('click', function(e) {
    // Add loading state
    this.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Confirming...';
    this.disabled = true;
});
</script>

<?php include 'footer.php'; ?>