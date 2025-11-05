<?php 
$page_title = "Assignment Management - EduPlatform";
$breadcrumb = [
    ['title' => 'Dashboard', 'url' => 'index.php'],
    ['title' => 'Assignments']
];
?>

<?php include '../../includes/header.php'; ?>

<!-- Page Header -->
<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="h3 mb-1">Assignment Management</h1>
                <p class="text-muted">Create, manage, and track student assignments across all courses.</p>
            </div>
            <div class="d-flex gap-2">
                <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#bulkGradeModal">
                    <i class="bi bi-award me-1"></i>Bulk Grade
                </button>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createAssignmentModal">
                    <i class="bi bi-plus-circle me-1"></i>Create Assignment
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Assignment Statistics -->
<div class="row g-4 mb-4">
    <div class="col-lg-3 col-md-6">
        <div class="stats-card assignments">
            <div class="stats-card-icon">
                <i class="bi bi-clipboard-check"></i>
            </div>
            <div class="stats-card-value">24</div>
            <div class="stats-card-label">Active Assignments</div>
            <div class="stats-card-change positive">
                <i class="bi bi-arrow-up me-1"></i>+6 this week
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="stats-card revenue">
            <div class="stats-card-icon">
                <i class="bi bi-check-circle"></i>
            </div>
            <div class="stats-card-value">156</div>
            <div class="stats-card-label">Submissions</div>
            <div class="stats-card-change positive">
                <i class="bi bi-arrow-up me-1"></i>89% response rate
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="stats-card courses">
            <div class="stats-card-icon">
                <i class="bi bi-clock"></i>
            </div>
            <div class="stats-card-value">8</div>
            <div class="stats-card-label">Pending Grading</div>
            <div class="stats-card-change negative">
                <i class="bi bi-arrow-down me-1"></i>2 overdue
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="stats-card students">
            <div class="stats-card-icon">
                <i class="bi bi-calendar-check"></i>
            </div>
            <div class="stats-card-value">92%</div>
            <div class="stats-card-label">On-Time Submissions</div>
            <div class="stats-card-change positive">
                <i class="bi bi-arrow-up me-1"></i>+3% from last week
            </div>
        </div>
    </div>
</div>

<!-- Filters and Search -->
<div class="row mb-4">
    <div class="col-lg-8">
        <div class="d-flex gap-2 flex-wrap">
            <div class="input-group" style="max-width: 300px;">
                <span class="input-group-text">
                    <i class="bi bi-search"></i>
                </span>
                <input type="text" class="form-control" placeholder="Search assignments..." 
                       data-filter data-target=".assignment-card">
            </div>
            <select class="form-select" style="max-width: 150px;" id="courseFilter">
                <option selected>All Courses</option>
                <option value="math">Mathematics</option>
                <option value="stats">Statistics</option>
                <option value="algebra">Linear Algebra</option>
                <option value="physics">Physics</option>
            </select>
            <select class="form-select" style="max-width: 150px;" id="statusFilter">
                <option selected>All Status</option>
                <option value="active">Active</option>
                <option value="draft">Draft</option>
                <option value="closed">Closed</option>
            </select>
            <select class="form-select" style="max-width: 150px;" id="dueFilter">
                <option selected>All Deadlines</option>
                <option value="overdue">Overdue</option>
                <option value="due-today">Due Today</option>
                <option value="due-week">Due This Week</option>
            </select>
        </div>
    </div>
    <div class="col-lg-4 text-end">
        <div class="btn-group" role="group">
            <button class="btn btn-outline-primary active" id="gridViewBtn">
                <i class="bi bi-grid-3x3-gap"></i>
            </button>
            <button class="btn btn-outline-primary" id="listViewBtn">
                <i class="bi bi-list"></i>
            </button>
        </div>
    </div>
</div>

<!-- Assignment List View (Default) -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="bi bi-clipboard-list me-2"></i>All Assignments
                </h5>
                <div class="d-flex gap-2">
                    <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="tooltip" title="Export">
                        <i class="bi bi-download"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="tooltip" title="Filter">
                        <i class="bi bi-funnel"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>
                                    <input type="checkbox" class="form-check-input" id="selectAll">
                                </th>
                                <th>Assignment</th>
                                <th>Course</th>
                                <th>Due Date</th>
                                <th>Submissions</th>
                                <th>Status</th>
                                <th>Grade Avg</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>
                                    <input type="checkbox" class="form-check-input assignment-select">
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="flex-shrink-0 me-3">
                                            <i class="bi bi-clipboard-check fs-4 text-primary"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-0">Calculus Problem Set #5</h6>
                                            <small class="text-muted">Integration techniques</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-info">Advanced Math</span>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-calendar me-1 text-muted"></i>
                                        <span>Nov 7, 2024</span>
                                    </div>
                                    <small class="text-muted">2 days remaining</small>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="progress flex-grow-1 me-2" style="height: 6px;">
                                            <div class="progress-bar" role="progressbar" style="width: 78%"></div>
                                        </div>
                                        <span class="small">35/45</span>
                                    </div>
                                </td>
                                <td>
                                    <span class="assignment-status submitted">Active</span>
                                </td>
                                <td>
                                    <span class="grade-display b-plus" style="width: 2rem; height: 2rem; font-size: 0.75rem;">B+</span>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <button class="btn btn-outline-primary" data-bs-toggle="tooltip" title="View">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                        <button class="btn btn-outline-success" data-bs-toggle="tooltip" title="Grade">
                                            <i class="bi bi-award"></i>
                                        </button>
                                        <button class="btn btn-outline-secondary dropdown-toggle" 
                                                data-bs-toggle="dropdown">
                                            <i class="bi bi-three-dots"></i>
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li><a class="dropdown-item" href="#">
                                                <i class="bi bi-pencil me-2"></i>Edit
                                            </a></li>
                                            <li><a class="dropdown-item" href="#">
                                                <i class="bi bi-download me-2"></i>Export Grades
                                            </a></li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li><a class="dropdown-item text-danger" href="#">
                                                <i class="bi bi-trash me-2"></i>Delete
                                            </a></li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <input type="checkbox" class="form-check-input assignment-select">
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="flex-shrink-0 me-3">
                                            <i class="bi bi-clipboard-data fs-4 text-success"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-0">Statistics Project</h6>
                                            <small class="text-muted">Data analysis and interpretation</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-warning">Statistics</span>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-calendar me-1 text-muted"></i>
                                        <span>Nov 10, 2024</span>
                                    </div>
                                    <small class="text-muted">5 days remaining</small>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="progress flex-grow-1 me-2" style="height: 6px;">
                                            <div class="progress-bar bg-warning" role="progressbar" style="width: 45%"></div>
                                        </div>
                                        <span class="small">17/38</span>
                                    </div>
                                </td>
                                <td>
                                    <span class="assignment-status pending">Active</span>
                                </td>
                                <td>
                                    <span class="text-muted">-</span>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <button class="btn btn-outline-primary" data-bs-toggle="tooltip" title="View">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                        <button class="btn btn-outline-success" data-bs-toggle="tooltip" title="Grade">
                                            <i class="bi bi-award"></i>
                                        </button>
                                        <button class="btn btn-outline-secondary dropdown-toggle" 
                                                data-bs-toggle="dropdown">
                                            <i class="bi bi-three-dots"></i>
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li><a class="dropdown-item" href="#">
                                                <i class="bi bi-pencil me-2"></i>Edit
                                            </a></li>
                                            <li><a class="dropdown-item" href="#">
                                                <i class="bi bi-download me-2"></i>Export Grades
                                            </a></li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li><a class="dropdown-item text-danger" href="#">
                                                <i class="bi bi-trash me-2"></i>Delete
                                            </a></li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <input type="checkbox" class="form-check-input assignment-select">
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="flex-shrink-0 me-3">
                                            <i class="bi bi-clipboard-pulse fs-4 text-danger"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-0">Linear Algebra Quiz #3</h6>
                                            <small class="text-muted">Eigenvalues and eigenvectors</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-primary">Linear Algebra</span>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-calendar me-1 text-danger"></i>
                                        <span class="text-danger">Nov 3, 2024</span>
                                    </div>
                                    <small class="text-danger">Overdue</small>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="progress flex-grow-1 me-2" style="height: 6px;">
                                            <div class="progress-bar bg-success" role="progressbar" style="width: 95%"></div>
                                        </div>
                                        <span class="small">49/52</span>
                                    </div>
                                </td>
                                <td>
                                    <span class="assignment-status graded">Closed</span>
                                </td>
                                <td>
                                    <span class="grade-display a-minus" style="width: 2rem; height: 2rem; font-size: 0.75rem;">A-</span>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <button class="btn btn-outline-primary" data-bs-toggle="tooltip" title="View">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                        <button class="btn btn-outline-info" data-bs-toggle="tooltip" title="View Grades">
                                            <i class="bi bi-graph-up"></i>
                                        </button>
                                        <button class="btn btn-outline-secondary dropdown-toggle" 
                                                data-bs-toggle="dropdown">
                                            <i class="bi bi-three-dots"></i>
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li><a class="dropdown-item" href="#">
                                                <i class="bi bi-pencil me-2"></i>Edit
                                            </a></li>
                                            <li><a class="dropdown-item" href="#">
                                                <i class="bi bi-download me-2"></i>Export Grades
                                            </a></li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li><a class="dropdown-item text-danger" href="#">
                                                <i class="bi bi-trash me-2"></i>Delete
                                            </a></li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <input type="checkbox" class="form-check-input assignment-select">
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="flex-shrink-0 me-3">
                                            <i class="bi bi-clipboard-heart fs-4 text-info"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-0">Physics Lab Report</h6>
                                            <small class="text-muted">Pendulum experiment analysis</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-info">Physics</span>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-calendar me-1 text-muted"></i>
                                        <span>Nov 12, 2024</span>
                                    </div>
                                    <small class="text-muted">1 week remaining</small>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="progress flex-grow-1 me-2" style="height: 6px;">
                                            <div class="progress-bar bg-info" role="progressbar" style="width: 12%"></div>
                                        </div>
                                        <span class="small">5/41</span>
                                    </div>
                                </td>
                                <td>
                                    <span class="assignment-status pending">Active</span>
                                </td>
                                <td>
                                    <span class="text-muted">-</span>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <button class="btn btn-outline-primary" data-bs-toggle="tooltip" title="View">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                        <button class="btn btn-outline-success" data-bs-toggle="tooltip" title="Grade">
                                            <i class="bi bi-award"></i>
                                        </button>
                                        <button class="btn btn-outline-secondary dropdown-toggle" 
                                                data-bs-toggle="dropdown">
                                            <i class="bi bi-three-dots"></i>
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li><a class="dropdown-item" href="#">
                                                <i class="bi bi-pencil me-2"></i>Edit
                                            </a></li>
                                            <li><a class="dropdown-item" href="#">
                                                <i class="bi bi-download me-2"></i>Export Grades
                                            </a></li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li><a class="dropdown-item text-danger" href="#">
                                                <i class="bi bi-trash me-2"></i>Delete
                                            </a></li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                <nav aria-label="Assignment pagination" class="mt-4">
                    <ul class="pagination justify-content-center">
                        <li class="page-item disabled">
                            <a class="page-link" href="#" tabindex="-1">Previous</a>
                        </li>
                        <li class="page-item active"><a class="page-link" href="#">1</a></li>
                        <li class="page-item"><a class="page-link" href="#">2</a></li>
                        <li class="page-item"><a class="page-link" href="#">3</a></li>
                        <li class="page-item">
                            <a class="page-link" href="#">Next</a>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>
    </div>
</div>

<!-- Create Assignment Modal -->
<div class="modal fade" id="createAssignmentModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-plus-circle me-2"></i>Create New Assignment
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form class="needs-validation" novalidate>
                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label for="assignmentTitle" class="form-label">Assignment Title *</label>
                            <input type="text" class="form-control" id="assignmentTitle" required>
                            <div class="invalid-feedback">Please provide an assignment title.</div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="assignmentPoints" class="form-label">Total Points *</label>
                            <input type="number" class="form-control" id="assignmentPoints" value="100" min="1" max="1000" required>
                            <div class="invalid-feedback">Please provide total points.</div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="assignmentDescription" class="form-label">Description *</label>
                        <textarea class="form-control" id="assignmentDescription" rows="4" required 
                                  placeholder="Detailed assignment instructions..."></textarea>
                        <div class="invalid-feedback">Please provide assignment description.</div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="courseSelect" class="form-label">Course *</label>
                            <select class="form-select" id="courseSelect" required>
                                <option value="">Select course</option>
                                <option value="math">Advanced Mathematics</option>
                                <option value="stats">Statistics</option>
                                <option value="algebra">Linear Algebra</option>
                                <option value="physics">Physics</option>
                            </select>
                            <div class="invalid-feedback">Please select a course.</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="assignmentType" class="form-label">Assignment Type</label>
                            <select class="form-select" id="assignmentType">
                                <option value="homework">Homework</option>
                                <option value="quiz">Quiz</option>
                                <option value="project">Project</option>
                                <option value="exam">Exam</option>
                                <option value="lab">Lab Report</option>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="dueDate" class="form-label">Due Date *</label>
                            <input type="datetime-local" class="form-control" id="dueDate" required>
                            <div class="invalid-feedback">Please provide a due date.</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="latePenalty" class="form-label">Late Penalty (%)</label>
                            <input type="number" class="form-control" id="latePenalty" value="0" min="0" max="100">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="allowLateSubmission" checked>
                                <label class="form-check-label" for="allowLateSubmission">
                                    Allow late submissions
                                </label>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="enableRubric" checked>
                                <label class="form-check-label" for="enableRubric">
                                    Enable grading rubric
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="fileAttachments" class="form-label">Attachments</label>
                        <input type="file" class="form-control" id="fileAttachments" multiple accept=".pdf,.doc,.docx,.txt,.zip">
                        <div class="form-text">Supported formats: PDF, DOC, DOCX, TXT, ZIP</div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-outline-primary" data-bs-dismiss="modal">Save as Draft</button>
                <button type="button" class="btn btn-primary">
                    <i class="bi bi-check me-1"></i>Create & Publish
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Bulk Grade Modal -->
<div class="modal fade" id="bulkGradeModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-award me-2"></i>Bulk Grade Assignment
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
                    <i class="bi bi-info-circle me-2"></i>
                    Select assignments from the table above to bulk grade them.
                </div>
                <form>
                    <div class="mb-3">
                        <label for="bulkAction" class="form-label">Action</label>
                        <select class="form-select" id="bulkAction">
                            <option value="export">Export Grades to CSV</option>
                            <option value="email">Send Grade Reports</option>
                            <option value="remind">Send Reminders</option>
                            <option value="archive">Archive Assignments</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary">
                    <i class="bi bi-check me-1"></i>Execute
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Additional JavaScript for assignment management
document.addEventListener('DOMContentLoaded', function() {
    // Select all checkbox functionality
    const selectAll = document.getElementById('selectAll');
    const assignmentSelects = document.querySelectorAll('.assignment-select');
    
    if (selectAll) {
        selectAll.addEventListener('change', function() {
            assignmentSelects.forEach(select => {
                select.checked = selectAll.checked;
            });
        });
    }
    
    // Filter functionality
    const filters = ['courseFilter', 'statusFilter', 'dueFilter'];
    filters.forEach(filterId => {
        const filter = document.getElementById(filterId);
        if (filter) {
            filter.addEventListener('change', function() {
                // Apply filters (implementation would depend on filtering logic)
                console.log('Filter changed:', filterId, filter.value);
            });
        }
    });
});
</script>

<?php include '../../includes/footer.php'; ?>
