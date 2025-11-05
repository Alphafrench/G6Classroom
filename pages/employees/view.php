<?php
$page_title = "Employee Details";
$page_description = "View complete employee information";

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

// Calculate additional information
$hireDate = strtotime($emp['hire_date']);
$currentDate = time();
$daysSinceHire = floor(($currentDate - $hireDate) / (60 * 60 * 24));
$yearsOfService = floor($daysSinceHire / 365.25);
$monthsOfService = floor(($daysSinceHire % 365.25) / 30.44);

// Employee status based on tenure
if ($daysSinceHire < 30) {
    $status = 'New';
    $statusClass = 'success';
    $statusIcon = 'fas fa-star';
} elseif ($daysSinceHire < 365) {
    $status = 'Active';
    $statusClass = 'info';
    $statusIcon = 'fas fa-user-check';
} else {
    $status = 'Veteran';
    $statusClass = 'primary';
    $statusIcon = 'fas fa-medal';
}
?>

<div class="row mb-4">
    <div class="col-md-8">
        <h2><i class="fas fa-user-circle me-2"></i>Employee Profile</h2>
        <p class="text-muted">Complete employee information and details</p>
    </div>
    <div class="col-md-4 text-end">
        <a href="edit.php?id=<?php echo $emp['id']; ?>" class="btn btn-primary me-2">
            <i class="fas fa-edit me-2"></i>Edit
        </a>
        <a href="index.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Back to List
        </a>
    </div>
</div>

<div class="row">
    <!-- Employee Profile Card -->
    <div class="col-md-4">
        <div class="card">
            <div class="card-body text-center">
                <div class="employee-avatar mb-3 mx-auto" style="width: 100px; height: 100px; font-size: 2.5rem;">
                    <?php echo strtoupper(substr($emp['first_name'], 0, 1) . substr($emp['last_name'], 0, 1)); ?>
                </div>
                <h4 class="mb-1"><?php echo htmlspecialchars($emp['first_name'] . ' ' . $emp['last_name']); ?></h4>
                <p class="text-muted mb-3"><?php echo htmlspecialchars($emp['position']); ?></p>
                <div class="mb-3">
                    <span class="badge bg-<?php echo $statusClass; ?> fs-6">
                        <i class="<?php echo $statusIcon; ?> me-1"></i><?php echo $status; ?>
                    </span>
                </div>
                <hr>
                <div class="text-start">
                    <div class="row mb-2">
                        <div class="col-6 text-muted">Employee ID:</div>
                        <div class="col-6"><strong><?php echo str_pad($emp['id'], 4, '0', STR_PAD_LEFT); ?></strong></div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-6 text-muted">Department:</div>
                        <div class="col-6"><strong><?php echo htmlspecialchars($emp['department']); ?></strong></div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-6 text-muted">Hire Date:</div>
                        <div class="col-6"><strong><?php echo formatDate($emp['hire_date']); ?></strong></div>
                    </div>
                    <div class="row">
                        <div class="col-6 text-muted">Service Time:</div>
                        <div class="col-6"><strong><?php echo $yearsOfService; ?> years <?php echo $monthsOfService; ?> months</strong></div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Quick Actions -->
        <div class="card mt-3">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-bolt me-2"></i>Quick Actions</h6>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="mailto:<?php echo htmlspecialchars($emp['email']); ?>" class="btn btn-outline-primary">
                        <i class="fas fa-envelope me-2"></i>Send Email
                    </a>
                    <a href="tel:<?php echo htmlspecialchars($emp['phone']); ?>" class="btn btn-outline-success">
                        <i class="fas fa-phone me-2"></i>Call Employee
                    </a>
                    <?php if (!empty($emp['emergency_phone'])): ?>
                        <a href="tel:<?php echo htmlspecialchars($emp['emergency_phone']); ?>" class="btn btn-outline-warning">
                            <i class="fas fa-phone-alt me-2"></i>Emergency Contact
                        </a>
                    <?php endif; ?>
                    <a href="edit.php?id=<?php echo $emp['id']; ?>" class="btn btn-outline-info">
                        <i class="fas fa-edit me-2"></i>Edit Profile
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Employee Details -->
    <div class="col-md-8">
        <!-- Contact Information -->
        <div class="card mb-3">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-address-card me-2"></i>Contact Information</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="text-muted mb-2">Primary Contact</h6>
                        <div class="mb-3">
                            <i class="fas fa-envelope text-muted me-2"></i>
                            <strong>Email:</strong><br>
                            <a href="mailto:<?php echo htmlspecialchars($emp['email']); ?>" class="text-decoration-none">
                                <?php echo htmlspecialchars($emp['email']); ?>
                            </a>
                        </div>
                        <div class="mb-3">
                            <i class="fas fa-phone text-muted me-2"></i>
                            <strong>Phone:</strong><br>
                            <a href="tel:<?php echo htmlspecialchars($emp['phone']); ?>" class="text-decoration-none">
                                <?php echo htmlspecialchars($emp['phone']); ?>
                            </a>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-muted mb-2">Address</h6>
                        <div>
                            <?php if (!empty($emp['address'])): ?>
                                <?php echo nl2br(htmlspecialchars($emp['address'])); ?>
                            <?php else: ?>
                                <span class="text-muted">No address provided</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Employment Information -->
        <div class="card mb-3">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-briefcase me-2"></i>Employment Details</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="text-muted mb-2">Position & Department</h6>
                        <div class="mb-3">
                            <strong>Position:</strong><br>
                            <span class="badge bg-info fs-6"><?php echo htmlspecialchars($emp['position']); ?></span>
                        </div>
                        <div class="mb-3">
                            <strong>Department:</strong><br>
                            <span class="badge bg-secondary fs-6"><?php echo htmlspecialchars($emp['department']); ?></span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-muted mb-2">Compensation & Tenure</h6>
                        <div class="mb-3">
                            <strong>Annual Salary:</strong><br>
                            <span class="h5 text-success"><?php echo formatCurrency($emp['salary']); ?></span>
                        </div>
                        <div class="mb-3">
                            <strong>Years of Service:</strong><br>
                            <span class="badge bg-<?php echo $statusClass; ?> fs-6">
                                <?php echo $yearsOfService; ?> years <?php echo $monthsOfService; ?> months
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Emergency Contact -->
        <?php if (!empty($emp['emergency_contact']) || !empty($emp['emergency_phone'])): ?>
            <div class="card mb-3">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-phone-alt me-2"></i>Emergency Contact</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-muted mb-2">Contact Person</h6>
                            <div class="mb-3">
                                <?php if (!empty($emp['emergency_contact'])): ?>
                                    <strong><?php echo htmlspecialchars($emp['emergency_contact']); ?></strong>
                                <?php else: ?>
                                    <span class="text-muted">No emergency contact provided</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-muted mb-2">Contact Phone</h6>
                            <div class="mb-3">
                                <?php if (!empty($emp['emergency_phone'])): ?>
                                    <a href="tel:<?php echo htmlspecialchars($emp['emergency_phone']); ?>" class="text-decoration-none">
                                        <i class="fas fa-phone text-muted me-2"></i>
                                        <?php echo htmlspecialchars($emp['emergency_phone']); ?>
                                    </a>
                                <?php else: ?>
                                    <span class="text-muted">No emergency phone provided</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
        
        <!-- Additional Notes -->
        <?php if (!empty($emp['notes'])): ?>
            <div class="card mb-3">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-sticky-note me-2"></i>Additional Notes</h5>
                </div>
                <div class="card-body">
                    <div>
                        <?php echo nl2br(htmlspecialchars($emp['notes'])); ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
        
        <!-- Record Information -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Record Information</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="text-muted mb-2">Creation</h6>
                        <div class="mb-2">
                            <strong>Created:</strong> <?php echo formatDate($emp['created_at']); ?>
                        </div>
                        <div>
                            <strong>Employee ID:</strong> <?php echo str_pad($emp['id'], 6, '0', STR_PAD_LEFT); ?>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-muted mb-2">Last Modified</h6>
                        <?php if (!empty($emp['updated_at'])): ?>
                            <div class="mb-2">
                                <strong>Updated:</strong> <?php echo formatDate($emp['updated_at']); ?>
                            </div>
                            <div>
                                <strong>Last Modified:</strong> <?php echo date('H:i:s', strtotime($emp['updated_at'])); ?>
                            </div>
                        <?php else: ?>
                            <div class="text-muted">Never updated</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Action Buttons -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-1">Employee Management Actions</h6>
                        <small class="text-muted">Perform actions on this employee record</small>
                    </div>
                    <div>
                        <a href="index.php" class="btn btn-secondary me-2">
                            <i class="fas fa-list me-2"></i>Back to Employee List
                        </a>
                        <a href="edit.php?id=<?php echo $emp['id']; ?>" class="btn btn-primary me-2">
                            <i class="fas fa-edit me-2"></i>Edit Employee
                        </a>
                        <a href="delete.php?id=<?php echo $emp['id']; ?>" class="btn btn-danger delete-btn">
                            <i class="fas fa-trash me-2"></i>Delete Employee
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Employee Summary Cards -->
<div class="row mt-4">
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <i class="fas fa-calendar-day fa-2x text-primary mb-2"></i>
                <h6 class="text-muted">Days of Service</h6>
                <h4 class="text-primary"><?php echo number_format($daysSinceHire); ?></h4>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <i class="fas fa-dollar-sign fa-2x text-success mb-2"></i>
                <h6 class="text-muted">Annual Salary</h6>
                <h4 class="text-success"><?php echo number_format($emp['salary']); ?></h4>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <i class="fas fa-building fa-2x text-info mb-2"></i>
                <h6 class="text-muted">Department</h6>
                <h4 class="text-info"><?php echo htmlspecialchars($emp['department']); ?></h4>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <i class="fas fa-briefcase fa-2x text-warning mb-2"></i>
                <h6 class="text-muted">Position</h6>
                <h4 class="text-warning"><?php echo htmlspecialchars($emp['position']); ?></h4>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>