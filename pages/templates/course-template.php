<?php 
$page_title = "Course Management - EduPlatform";
$breadcrumb = [
    ['title' => 'Dashboard', 'url' => 'index.php'],
    ['title' => 'Courses']
];
?>

<?php include '../../includes/header.php'; ?>

<!-- Page Header -->
<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="h3 mb-1">Course Management</h1>
                <p class="text-muted">Manage your courses, enrollments, and course content.</p>
            </div>
            <div class="d-flex gap-2">
                <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#importModal">
                    <i class="bi bi-upload me-1"></i>Import Course
                </button>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createCourseModal">
                    <i class="bi bi-plus-circle me-1"></i>Create New Course
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Course Statistics -->
<div class="row g-4 mb-4">
    <div class="col-lg-3 col-md-6">
        <div class="card text-center">
            <div class="card-body">
                <i class="bi bi-journal-bookmark fs-1 text-primary mb-3"></i>
                <h3 class="mb-1">6</h3>
                <p class="text-muted mb-0">Active Courses</p>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="card text-center">
            <div class="card-body">
                <i class="bi bi-people fs-1 text-success mb-3"></i>
                <h3 class="mb-1">247</h3>
                <p class="text-muted mb-0">Total Students</p>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="card text-center">
            <div class="card-body">
                <i class="bi bi-calendar-check fs-1 text-info mb-3"></i>
                <h3 class="mb-1">18</h3>
                <p class="text-muted mb-0">Classes This Week</p>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="card text-center">
            <div class="card-body">
                <i class="bi bi-clipboard-check fs-1 text-warning mb-3"></i>
                <h3 class="mb-1">24</h3>
                <p class="text-muted mb-0">Pending Assignments</p>
            </div>
        </div>
    </div>
</div>

<!-- Filter and Search -->
<div class="row mb-4">
    <div class="col-lg-8">
        <div class="d-flex gap-2">
            <div class="input-group" style="max-width: 300px;">
                <span class="input-group-text">
                    <i class="bi bi-search"></i>
                </span>
                <input type="text" class="form-control" placeholder="Search courses..." 
                       data-filter data-target=".course-card">
            </div>
            <select class="form-select" style="max-width: 200px;">
                <option selected>All Courses</option>
                <option value="active">Active</option>
                <option value="archived">Archived</option>
                <option value="draft">Draft</option>
            </select>
            <select class="form-select" style="max-width: 200px;">
                <option selected>All Subjects</option>
                <option value="math">Mathematics</option>
                <option value="science">Science</option>
                <option value="english">English</option>
            </select>
        </div>
    </div>
    <div class="col-lg-4 text-end">
        <div class="btn-group" role="group">
            <input type="radio" class="btn-check" name="view" id="gridView" checked>
            <label class="btn btn-outline-primary" for="gridView">
                <i class="bi bi-grid-3x3-gap"></i>
            </label>
            <input type="radio" class="btn-check" name="view" id="listView">
            <label class="btn btn-outline-primary" for="listView">
                <i class="bi bi-list"></i>
            </label>
        </div>
    </div>
</div>

<!-- Courses Grid -->
<div class="row g-4" id="coursesGrid">
    <!-- Course Card 1 -->
    <div class="col-lg-4 col-md-6">
        <div class="course-card">
            <div class="course-card-header">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <h6 class="course-card-title">Advanced Mathematics</h6>
                    <div class="dropdown">
                        <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="dropdown">
                            <i class="bi bi-three-dots"></i>
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#"><i class="bi bi-pencil me-2"></i>Edit</a></li>
                            <li><a class="dropdown-item" href="#"><i class="bi bi-clone me-2"></i>Duplicate</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="#"><i class="bi bi-archive me-2"></i>Archive</a></li>
                        </ul>
                    </div>
                </div>
                <div class="course-card-meta">
                    <span><i class="bi bi-person me-1"></i>Prof. Smith</span>
                    <span><i class="bi bi-calendar me-1"></i>M/W/F</span>
                    <span><i class="bi bi-people me-1"></i>45 students</span>
                </div>
            </div>
            <div class="card-body">
                <p class="text-muted small mb-3">
                    Comprehensive course covering calculus, differential equations, and advanced mathematical concepts.
                </p>
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="role-badge active">Active</span>
                    <small class="text-muted">Fall 2024</small>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-sm btn-primary flex-fill">
                        <i class="bi bi-eye me-1"></i>View Course
                    </button>
                    <button class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-people"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Course Card 2 -->
    <div class="col-lg-4 col-md-6">
        <div class="course-card">
            <div class="course-card-header">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <h6 class="course-card-title">Statistics</h6>
                    <div class="dropdown">
                        <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="dropdown">
                            <i class="bi bi-three-dots"></i>
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#"><i class="bi bi-pencil me-2"></i>Edit</a></li>
                            <li><a class="dropdown-item" href="#"><i class="bi bi-clone me-2"></i>Duplicate</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="#"><i class="bi bi-archive me-2"></i>Archive</a></li>
                        </ul>
                    </div>
                </div>
                <div class="course-card-meta">
                    <span><i class="bi bi-person me-1"></i>Dr. Johnson</span>
                    <span><i class="bi bi-calendar me-1"></i>T/Th</span>
                    <span><i class="bi bi-people me-1"></i>38 students</span>
                </div>
            </div>
            <div class="card-body">
                <p class="text-muted small mb-3">
                    Introduction to probability theory, statistical inference, and data analysis techniques.
                </p>
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="role-badge active">Active</span>
                    <small class="text-muted">Fall 2024</small>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-sm btn-primary flex-fill">
                        <i class="bi bi-eye me-1"></i>View Course
                    </button>
                    <button class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-people"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Course Card 3 -->
    <div class="col-lg-4 col-md-6">
        <div class="course-card">
            <div class="course-card-header">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <h6 class="course-card-title">Linear Algebra</h6>
                    <div class="dropdown">
                        <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="dropdown">
                            <i class="bi bi-three-dots"></i>
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#"><i class="bi bi-pencil me-2"></i>Edit</a></li>
                            <li><a class="dropdown-item" href="#"><i class="bi bi-clone me-2"></i>Duplicate</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="#"><i class="bi bi-archive me-2"></i>Archive</a></li>
                        </ul>
                    </div>
                </div>
                <div class="course-card-meta">
                    <span><i class="bi bi-person me-1"></i>Prof. Davis</span>
                    <span><i class="bi bi-calendar me-1"></i>M/W</span>
                    <span><i class="bi bi-people me-1"></i>52 students</span>
                </div>
            </div>
            <div class="card-body">
                <p class="text-muted small mb-3">
                    Matrix operations, vector spaces, eigenvalues, and applications in computer science.
                </p>
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="role-badge active">Active</span>
                    <small class="text-muted">Fall 2024</small>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-sm btn-primary flex-fill">
                        <i class="bi bi-eye me-1"></i>View Course
                    </button>
                    <button class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-people"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Course Card 4 -->
    <div class="col-lg-4 col-md-6">
        <div class="course-card">
            <div class="course-card-header">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <h6 class="course-card-title">Physics I</h6>
                    <div class="dropdown">
                        <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="dropdown">
                            <i class="bi bi-three-dots"></i>
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#"><i class="bi bi-pencil me-2"></i>Edit</a></li>
                            <li><a class="dropdown-item" href="#"><i class="bi bi-clone me-2"></i>Duplicate</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="#"><i class="bi bi-archive me-2"></i>Archive</a></li>
                        </ul>
                    </div>
                </div>
                <div class="course-card-meta">
                    <span><i class="bi bi-person me-1"></i>Dr. Wilson</span>
                    <span><i class="bi bi-calendar me-1"></i>T/Th</span>
                    <span><i class="bi bi-people me-1"></i>41 students</span>
                </div>
            </div>
            <div class="card-body">
                <p class="text-muted small mb-3">
                    Fundamental principles of mechanics, thermodynamics, and electromagnetic theory.
                </p>
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="role-badge active">Active</span>
                    <small class="text-muted">Fall 2024</small>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-sm btn-primary flex-fill">
                        <i class="bi bi-eye me-1"></i>View Course
                    </button>
                    <button class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-people"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Course Card 5 -->
    <div class="col-lg-4 col-md-6">
        <div class="course-card">
            <div class="course-card-header">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <h6 class="course-card-title">Chemistry</h6>
                    <div class="dropdown">
                        <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="dropdown">
                            <i class="bi bi-three-dots"></i>
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#"><i class="bi bi-pencil me-2"></i>Edit</a></li>
                            <li><a class="dropdown-item" href="#"><i class="bi bi-clone me-2"></i>Duplicate</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="#"><i class="bi bi-archive me-2"></i>Archive</a></li>
                        </ul>
                    </div>
                </div>
                <div class="course-card-meta">
                    <span><i class="bi bi-person me-1"></i>Dr. Brown</span>
                    <span><i class="bi bi-calendar me-1"></i>M/W/F</span>
                    <span><i class="bi bi-people me-1"></i>35 students</span>
                </div>
            </div>
            <div class="card-body">
                <p class="text-muted small mb-3">
                    Organic and inorganic chemistry principles with laboratory experiments.
                </p>
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="role-badge draft">Draft</span>
                    <small class="text-muted">Spring 2025</small>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-sm btn-primary flex-fill">
                        <i class="bi bi-eye me-1"></i>View Course
                    </button>
                    <button class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-people"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Course Card 6 -->
    <div class="col-lg-4 col-md-6">
        <div class="course-card">
            <div class="course-card-header">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <h6 class="course-card-title">Computer Science</h6>
                    <div class="dropdown">
                        <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="dropdown">
                            <i class="bi bi-three-dots"></i>
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#"><i class="bi bi-pencil me-2"></i>Edit</a></li>
                            <li><a class="dropdown-item" href="#"><i class="bi bi-clone me-2"></i>Duplicate</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="#"><i class="bi bi-archive me-2"></i>Archive</a></li>
                        </ul>
                    </div>
                </div>
                <div class="course-card-meta">
                    <span><i class="bi bi-person me-1"></i>Prof. Garcia</span>
                    <span><i class="bi bi-calendar me-1"></i>M/W</span>
                    <span><i class="bi bi-people me-1"></i>28 students</span>
                </div>
            </div>
            <div class="card-body">
                <p class="text-muted small mb-3">
                    Introduction to programming, data structures, and algorithm design.
                </p>
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="role-badge archived">Archived</span>
                    <small class="text-muted">Summer 2024</small>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-sm btn-primary flex-fill">
                        <i class="bi bi-eye me-1"></i>View Course
                    </button>
                    <button class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-people"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Create Course Modal -->
<div class="modal fade" id="createCourseModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-plus-circle me-2"></i>Create New Course
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form class="needs-validation" novalidate>
                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label for="courseName" class="form-label">Course Name *</label>
                            <input type="text" class="form-control" id="courseName" required>
                            <div class="invalid-feedback">Please provide a course name.</div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="courseCode" class="form-label">Course Code *</label>
                            <input type="text" class="form-control" id="courseCode" required>
                            <div class="invalid-feedback">Please provide a course code.</div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="courseDescription" class="form-label">Description</label>
                        <textarea class="form-control" id="courseDescription" rows="3" 
                                  placeholder="Brief description of the course..."></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="instructor" class="form-label">Instructor *</label>
                            <select class="form-select" id="instructor" required>
                                <option value="">Select instructor</option>
                                <option value="smith">Prof. Smith</option>
                                <option value="johnson">Dr. Johnson</option>
                                <option value="davis">Prof. Davis</option>
                                <option value="wilson">Dr. Wilson</option>
                            </select>
                            <div class="invalid-feedback">Please select an instructor.</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="credits" class="form-label">Credits *</label>
                            <select class="form-select" id="credits" required>
                                <option value="">Select credits</option>
                                <option value="3">3 Credits</option>
                                <option value="4">4 Credits</option>
                                <option value="5">5 Credits</option>
                            </select>
                            <div class="invalid-feedback">Please select credits.</div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="semester" class="form-label">Semester *</label>
                            <select class="form-select" id="semester" required>
                                <option value="">Select semester</option>
                                <option value="fall2024">Fall 2024</option>
                                <option value="spring2025">Spring 2025</option>
                                <option value="summer2025">Summer 2025</option>
                            </select>
                            <div class="invalid-feedback">Please select a semester.</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="maxStudents" class="form-label">Max Students</label>
                            <input type="number" class="form-control" id="maxStudents" value="50" min="1" max="200">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Schedule</label>
                        <div class="row">
                            <div class="col-md-4">
                                <select class="form-select mb-2">
                                    <option>Monday</option>
                                    <option>Tuesday</option>
                                    <option>Wednesday</option>
                                    <option>Thursday</option>
                                    <option>Friday</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <input type="time" class="form-control mb-2" value="09:00">
                            </div>
                            <div class="col-md-4">
                                <input type="time" class="form-control mb-2" value="10:30">
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary">
                    <i class="bi bi-check me-1"></i>Create Course
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Import Course Modal -->
<div class="modal fade" id="importModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-upload me-2"></i>Import Course
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form>
                    <div class="mb-3">
                        <label for="importFile" class="form-label">Course File</label>
                        <input type="file" class="form-control" id="importFile" accept=".json,.csv,.xlsx">
                        <div class="form-text">Supported formats: JSON, CSV, Excel</div>
                    </div>
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="overwriteExisting" checked>
                            <label class="form-check-label" for="overwriteExisting">
                                Overwrite existing courses with same code
                            </label>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary">
                    <i class="bi bi-upload me-1"></i>Import
                </button>
            </div>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
