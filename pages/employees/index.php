<?php
$page_title = "Employee List";
$page_description = "View and manage all employees in the system";

include 'header.php';

// Get search parameters
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$department = isset($_GET['department']) ? trim($_GET['department']) : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

// Build search parameters for pagination links
$searchParams = [];
if (!empty($search)) $searchParams['search'] = $search;
if (!empty($department)) $searchParams['department'] = $department;

// Get employees data
$result = $employee->readAll([
    'search' => $search,
    'department' => $department,
    'page' => $page,
    'limit' => RECORDS_PER_PAGE
]);

$employees = $result['data'];
$totalPages = $result['pages'];
$currentPage = $result['current_page'];
$totalRecords = $result['total'];

// Get departments for filter dropdown
$departments = $employee->getDepartments();
?>

<div class="row mb-4">
    <div class="col-md-8">
        <h2><i class="fas fa-users me-2"></i>Employee Directory</h2>
        <p class="text-muted">Manage all employees in your organization</p>
    </div>
    <div class="col-md-4 text-end">
        <a href="add.php" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Add New Employee
        </a>
    </div>
</div>

<!-- Search and Filter -->
<div class="search-filter">
    <form method="GET" id="searchForm">
        <div class="row g-3">
            <div class="col-md-6">
                <label for="searchInput" class="form-label">Search</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                    <input type="text" class="form-control" id="searchInput" name="search" 
                           placeholder="Search by name, email, position..." 
                           value="<?php echo htmlspecialchars($search); ?>">
                </div>
            </div>
            <div class="col-md-4">
                <label for="department" class="form-label">Department</label>
                <select class="form-select" id="department" name="department">
                    <option value="">All Departments</option>
                    <?php foreach ($departments as $dept): ?>
                        <option value="<?php echo htmlspecialchars($dept); ?>" 
                                <?php echo ($department === $dept) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($dept); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">&nbsp;</label>
                <div class="d-grid">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-filter me-1"></i>Filter
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- Results Summary -->
<div class="row mb-3">
    <div class="col-sm-6">
        <p class="text-muted mb-0">
            <?php if ($totalRecords > 0): ?>
                Showing <?php echo (($currentPage - 1) * RECORDS_PER_PAGE + 1); ?> to 
                <?php echo min($currentPage * RECORDS_PER_PAGE, $totalRecords); ?> of 
                <?php echo $totalRecords; ?> employees
            <?php else: ?>
                No employees found
            <?php endif; ?>
        </p>
    </div>
    <div class="col-sm-6 text-sm-end">
        <?php if (!empty($search) || !empty($department)): ?>
            <a href="index.php" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-times me-1"></i>Clear Filters
            </a>
        <?php endif; ?>
    </div>
</div>

<?php if (empty($employees)): ?>
    <div class="text-center py-5">
        <i class="fas fa-users fa-3x text-muted mb-3"></i>
        <h4 class="text-muted">No Employees Found</h4>
        <?php if (!empty($search) || !empty($department)): ?>
            <p class="text-muted">Try adjusting your search criteria or add a new employee.</p>
            <a href="index.php" class="btn btn-primary me-2">
                <i class="fas fa-times me-1"></i>Clear Filters
            </a>
        <?php else: ?>
            <p class="text-muted">Get started by adding your first employee to the system.</p>
        <?php endif; ?>
        <a href="add.php" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i>Add Employee
        </a>
    </div>
<?php else: ?>
    <!-- Employee Table -->
    <div class="table-responsive">
        <table id="employeeTable" class="table table-striped table-hover">
            <thead class="table-dark">
                <tr>
                    <th>Employee</th>
                    <th>Contact</th>
                    <th>Position</th>
                    <th>Department</th>
                    <th>Hire Date</th>
                    <th>Salary</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($employees as $emp): ?>
                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="employee-avatar me-3">
                                    <?php echo strtoupper(substr($emp['first_name'], 0, 1) . substr($emp['last_name'], 0, 1)); ?>
                                </div>
                                <div>
                                    <h6 class="mb-0">
                                        <a href="view.php?id=<?php echo $emp['id']; ?>" class="text-decoration-none">
                                            <?php echo htmlspecialchars($emp['first_name'] . ' ' . $emp['last_name']); ?>
                                        </a>
                                    </h6>
                                    <small class="text-muted">ID: <?php echo str_pad($emp['id'], 4, '0', STR_PAD_LEFT); ?></small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div>
                                <div><i class="fas fa-envelope text-muted me-2"></i>
                                    <a href="mailto:<?php echo htmlspecialchars($emp['email']); ?>" class="text-decoration-none">
                                        <?php echo htmlspecialchars($emp['email']); ?>
                                    </a>
                                </div>
                                <div><i class="fas fa-phone text-muted me-2"></i>
                                    <a href="tel:<?php echo htmlspecialchars($emp['phone']); ?>" class="text-decoration-none">
                                        <?php echo htmlspecialchars($emp['phone']); ?>
                                    </a>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-info"><?php echo htmlspecialchars($emp['position']); ?></span>
                        </td>
                        <td>
                            <span class="badge bg-secondary"><?php echo htmlspecialchars($emp['department']); ?></span>
                        </td>
                        <td>
                            <?php echo formatDate($emp['hire_date']); ?>
                        </td>
                        <td>
                            <strong><?php echo formatCurrency($emp['salary']); ?></strong>
                        </td>
                        <td>
                            <?php 
                            $hireDate = strtotime($emp['hire_date']);
                            $currentDate = time();
                            $daysDiff = ($currentDate - $hireDate) / (60 * 60 * 24);
                            
                            if ($daysDiff < 30) {
                                echo '<span class="badge bg-success">New</span>';
                            } elseif ($daysDiff < 365) {
                                echo '<span class="badge bg-info">Active</span>';
                            } else {
                                echo '<span class="badge bg-primary">Veteran</span>';
                            }
                            ?>
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="view.php?id=<?php echo $emp['id']; ?>" 
                                   class="btn btn-outline-info btn-action" 
                                   title="View Details">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="edit.php?id=<?php echo $emp['id']; ?>" 
                                   class="btn btn-outline-primary btn-action" 
                                   title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="delete.php?id=<?php echo $emp['id']; ?>" 
                                   class="btn btn-outline-danger btn-action delete-btn" 
                                   title="Delete">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
        <nav aria-label="Employee pagination" class="mt-4">
            <ul class="pagination justify-content-center">
                <!-- Previous Page -->
                <li class="page-item <?php echo ($currentPage <= 1) ? 'disabled' : ''; ?>">
                    <a class="page-link" href="?page=<?php echo $currentPage - 1; ?><?php echo !empty($searchParams) ? '&' . http_build_query($searchParams) : ''; ?>">
                        <i class="fas fa-chevron-left"></i> Previous
                    </a>
                </li>

                <!-- Page Numbers -->
                <?php 
                $startPage = max(1, $currentPage - 2);
                $endPage = min($totalPages, $currentPage + 2);
                
                if ($startPage > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=1<?php echo !empty($searchParams) ? '&' . http_build_query($searchParams) : ''; ?>">1</a>
                    </li>
                    <?php if ($startPage > 2): ?>
                        <li class="page-item disabled"><span class="page-link">...</span></li>
                    <?php endif; ?>
                <?php endif; ?>

                <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                    <li class="page-item <?php echo ($i == $currentPage) ? 'active' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $i; ?><?php echo !empty($searchParams) ? '&' . http_build_query($searchParams) : ''; ?>">
                            <?php echo $i; ?>
                        </a>
                    </li>
                <?php endfor; ?>

                <?php if ($endPage < $totalPages): ?>
                    <?php if ($endPage < $totalPages - 1): ?>
                        <li class="page-item disabled"><span class="page-link">...</span></li>
                    <?php endif; ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?php echo $totalPages; ?><?php echo !empty($searchParams) ? '&' . http_build_query($searchParams) : ''; ?>">
                            <?php echo $totalPages; ?>
                        </a>
                    </li>
                <?php endif; ?>

                <!-- Next Page -->
                <li class="page-item <?php echo ($currentPage >= $totalPages) ? 'disabled' : ''; ?>">
                    <a class="page-link" href="?page=<?php echo $currentPage + 1; ?><?php echo !empty($searchParams) ? '&' . http_build_query($searchParams) : ''; ?>">
                        Next <i class="fas fa-chevron-right"></i>
                    </a>
                </li>
            </ul>
        </nav>
    <?php endif; ?>
<?php endif; ?>

<?php include 'footer.php'; ?>