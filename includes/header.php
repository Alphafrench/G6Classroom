<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title><?php echo isset($page_title) ? $page_title : 'EduPlatform - Learning Management System'; ?></title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="assets/img/favicon.ico">
</head>
<body class="bg-light">
    <!-- Navigation Header -->
    <nav class="navbar navbar-expand-lg navbar-light fixed-top">
        <div class="container-fluid">
            <!-- Brand/Logo -->
            <a class="navbar-brand d-flex align-items-center" href="index.php">
                <i class="bi bi-mortarboard me-2"></i>
                <span>EduPlatform</span>
            </a>
            
            <!-- Mobile Toggle Button -->
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" 
                    data-bs-target="#navbarNav" aria-controls="navbarNav" 
                    aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <!-- Navigation Menu -->
            <div class="collapse navbar-collapse" id="navbarNav">
                <!-- Main Navigation Links -->
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>" 
                           href="index.php">
                            <i class="bi bi-house me-1"></i>Dashboard
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle <?php echo strpos($_SERVER['PHP_SELF'], 'pages/') !== false ? 'active' : ''; ?>" 
                           href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-book me-1"></i>Courses
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="pages/templates/course-template.php">
                                <i class="bi bi-journal-bookmark me-2"></i>My Courses
                            </a></li>
                            <li><a class="dropdown-item" href="pages/templates/assignment-template.php">
                                <i class="bi bi-clipboard-check me-2"></i>Assignments
                            </a></li>
                            <li><a class="dropdown-item" href="pages/templates/gradebook-template.php">
                                <i class="bi bi-award me-2"></i>Gradebook
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="pages/templates/schedule-template.php">
                                <i class="bi bi-calendar-week me-2"></i>Schedule
                            </a></li>
                            <li><a class="dropdown-item" href="pages/templates/attendance-template.php">
                                <i class="bi bi-people me-2"></i>Attendance
                            </a></li>
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'students.php' ? 'active' : ''; ?>" 
                           href="students.php">
                            <i class="bi bi-person me-1"></i>Students
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'resources.php' ? 'active' : ''; ?>" 
                           href="resources.php">
                            <i class="bi bi-folder me-1"></i>Resources
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'reports.php' ? 'active' : ''; ?>" 
                           href="reports.php">
                            <i class="bi bi-graph-up me-1"></i>Reports
                        </a>
                    </li>
                </ul>
                
                <!-- Search Bar -->
                <form class="d-flex me-3" role="search">
                    <div class="input-group">
                        <input class="form-control" type="search" placeholder="Search..." 
                               aria-label="Search" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Search">
                        <button class="btn btn-outline-primary" type="submit">
                            <i class="bi bi-search"></i>
                        </button>
                    </div>
                </form>
                
                <!-- User Menu -->
                <ul class="navbar-nav">
                    <!-- Notifications -->
                    <li class="nav-item dropdown">
                        <a class="nav-link position-relative" href="#" role="button" 
                           data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-bell"></i>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                3
                                <span class="visually-hidden">unread notifications</span>
                            </span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end notification-dropdown">
                            <li><h6 class="dropdown-header">Notifications</h6></li>
                            <li><a class="dropdown-item" href="#">
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-info-circle text-info me-2"></i>
                                    <div>
                                        <div class="fw-semibold">New user registered</div>
                                        <small class="text-muted">2 minutes ago</small>
                                    </div>
                                </div>
                            </a></li>
                            <li><a class="dropdown-item" href="#">
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-check-circle text-success me-2"></i>
                                    <div>
                                        <div class="fw-semibold">Task completed</div>
                                        <small class="text-muted">1 hour ago</small>
                                    </div>
                                </div>
                            </a></li>
                            <li><a class="dropdown-item" href="#">
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-exclamation-triangle text-warning me-2"></i>
                                    <div>
                                        <div class="fw-semibold">Server warning</div>
                                        <small class="text-muted">3 hours ago</small>
                                    </div>
                                </div>
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-center" href="#">View all notifications</a></li>
                        </ul>
                    </li>
                    
                    <!-- User Profile Dropdown -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" 
                           role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <img src="https://via.placeholder.com/32x32" alt="User Avatar" 
                                 class="rounded-circle me-2" width="32" height="32">
                            <div class="d-none d-md-inline">
                                <div class="small fw-bold">Prof. Smith</div>
                                <div class="small text-muted">Mathematics</div>
                            </div>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><h6 class="dropdown-header">Welcome, Prof. Smith!</h6></li>
                            <li><a class="dropdown-item" href="#">
                                <i class="bi bi-person me-2"></i>My Profile
                            </a></li>
                            <li><a class="dropdown-item" href="#">
                                <i class="bi bi-calendar me-2"></i>My Schedule
                            </a></li>
                            <li><a class="dropdown-item" href="#">
                                <i class="bi bi-gear me-2"></i>Settings
                            </a></li>
                            <li><a class="dropdown-item" href="#">
                                <i class="bi bi-shield-check me-2"></i>Privacy
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="logout.php">
                                <i class="bi bi-box-arrow-right me-2"></i>Logout
                            </a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    
    <!-- Main Content Wrapper -->
    <div class="container-fluid main-container" style="margin-top: 76px;">
        <!-- Alert Container -->
        <div class="alert-container position-fixed top-0 start-50 translate-middle-x" style="z-index: 1055; margin-top: 90px;">
            <!-- Alerts will be dynamically inserted here -->
        </div>
        
        <!-- Breadcrumb (optional) -->
        <?php if (isset($breadcrumb) && $breadcrumb): ?>
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                <?php foreach ($breadcrumb as $item): ?>
                    <?php if (isset($item['url'])): ?>
                        <li class="breadcrumb-item">
                            <a href="<?php echo $item['url']; ?>"><?php echo $item['title']; ?></a>
                        </li>
                    <?php else: ?>
                        <li class="breadcrumb-item active" aria-current="page"><?php echo $item['title']; ?></li>
                    <?php endif; ?>
                <?php endforeach; ?>
            </ol>
        </nav>
        <?php endif; ?>

<!-- JavaScript Libraries -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- jQuery (optional, for additional functionality) -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- DataTables CSS and JS (included globally for table templates) -->
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
<script type="text/javascript" src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>

<!-- Chart.js for data visualization -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>