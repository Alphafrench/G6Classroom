<?php 
$page_title = "EduPlatform - Learning Management System";
?>

<?php include 'includes/header.php'; ?>

<!-- Hero Section -->
<div class="row mb-5">
    <div class="col-lg-8 mx-auto text-center">
        <div class="py-5">
            <i class="bi bi-mortarboard fs-1 text-primary mb-4"></i>
            <h1 class="display-4 fw-bold mb-3">Welcome to EduPlatform</h1>
            <p class="lead text-muted mb-4">
                A comprehensive Learning Management System built with Bootstrap 5. 
                Designed for teachers, students, and educational administrators.
            </p>
            <div class="d-flex justify-content-center gap-3 flex-wrap">
                <button class="btn btn-primary btn-lg">
                    <i class="bi bi-speedometer2 me-2"></i>View Dashboard
                </button>
                <button class="btn btn-outline-primary btn-lg" data-bs-toggle="modal" data-bs-target="#demoModal">
                    <i class="bi bi-play-circle me-2"></i>Watch Demo
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Features Section -->
<div class="row mb-5">
    <div class="col-lg-12">
        <div class="text-center mb-5">
            <h2 class="fw-bold">Powerful Features for Modern Education</h2>
            <p class="text-muted">Everything you need to manage courses, assignments, and student progress</p>
        </div>
        
        <div class="row g-4">
            <div class="col-lg-4 col-md-6">
                <div class="card h-100 text-center">
                    <div class="card-body">
                        <i class="bi bi-speedometer2 fs-1 text-primary mb-3"></i>
                        <h5 class="card-title">Interactive Dashboards</h5>
                        <p class="card-text">
                            Beautiful, responsive dashboards for both teachers and students with real-time data visualization.
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4 col-md-6">
                <div class="card h-100 text-center">
                    <div class="card-body">
                        <i class="bi bi-journal-bookmark fs-1 text-success mb-3"></i>
                        <h5 class="card-title">Course Management</h5>
                        <p class="card-text">
                            Create, organize, and manage courses with ease. Track enrollment and course progress.
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4 col-md-6">
                <div class="card h-100 text-center">
                    <div class="card-body">
                        <i class="bi bi-clipboard-check fs-1 text-info mb-3"></i>
                        <h5 class="card-title">Assignment Tracking</h5>
                        <p class="card-text">
                            Comprehensive assignment management with submission tracking and grading tools.
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4 col-md-6">
                <div class="card h-100 text-center">
                    <div class="card-body">
                        <i class="bi bi-award fs-1 text-warning mb-3"></i>
                        <h5 class="card-title">Grade Management</h5>
                        <p class="card-text">
                            Visual grade tracking with GPA calculations, grade trends, and detailed analytics.
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4 col-md-6">
                <div class="card h-100 text-center">
                    <div class="card-body">
                        <i class="bi bi-people fs-1 text-danger mb-3"></i>
                        <h5 class="card-title">Student Management</h5>
                        <p class="card-text">
                            Complete student profiles with attendance tracking and progress monitoring.
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4 col-md-6">
                <div class="card h-100 text-center">
                    <div class="card-body">
                        <i class="bi bi-calendar-week fs-1 text-purple mb-3"></i>
                        <h5 class="card-title">Schedule Management</h5>
                        <p class="card-text">
                            Visual schedules with timeline views and class management tools.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Template Showcase -->
<div class="row mb-5">
    <div class="col-lg-12">
        <div class="text-center mb-5">
            <h2 class="fw-bold">Template Showcase</h2>
            <p class="text-muted">Explore our collection of educational UI templates</p>
        </div>
        
        <div class="row g-4">
            <!-- Teacher Dashboard -->
            <div class="col-lg-6">
                <div class="card h-100">
                    <div class="card-header bg-primary text-white">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-person-badge me-2"></i>Teacher Dashboard
                        </h5>
                    </div>
                    <div class="card-body">
                        <p class="card-text">
                            Comprehensive teacher dashboard with course overview, student management, 
                            assignment grading, and analytics.
                        </p>
                        <div class="d-flex gap-2 mb-3">
                            <span class="badge bg-info">Responsive</span>
                            <span class="badge bg-success">Interactive</span>
                            <span class="badge bg-warning">Data-Rich</span>
                        </div>
                        <a href="pages/templates/teacher-dashboard-template.php" class="btn btn-primary">
                            <i class="bi bi-eye me-1"></i>View Template
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Student Dashboard -->
            <div class="col-lg-6">
                <div class="card h-100">
                    <div class="card-header bg-success text-white">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-mortarboard me-2"></i>Student Dashboard
                        </h5>
                    </div>
                    <div class="card-body">
                        <p class="card-text">
                            Student-focused dashboard showing courses, assignments, grades, 
                            and academic progress at a glance.
                        </p>
                        <div class="d-flex gap-2 mb-3">
                            <span class="badge bg-info">Mobile-First</span>
                            <span class="badge bg-success">User-Friendly</span>
                            <span class="badge bg-primary">Progress Tracking</span>
                        </div>
                        <a href="pages/templates/student-dashboard-template.php" class="btn btn-success">
                            <i class="bi bi-eye me-1"></i>View Template
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Course Management -->
            <div class="col-lg-6">
                <div class="card h-100">
                    <div class="card-header bg-info text-white">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-journal-bookmark me-2"></i>Course Management
                        </h5>
                    </div>
                    <div class="card-body">
                        <p class="card-text">
                            Complete course management interface with creation tools, enrollment tracking, 
                            and course analytics.
                        </p>
                        <div class="d-flex gap-2 mb-3">
                            <span class="badge bg-warning">CRUD Operations</span>
                            <span class="badge bg-info">Import/Export</span>
                            <span class="badge bg-success">Filter & Search</span>
                        </div>
                        <a href="pages/templates/course-template.php" class="btn btn-info">
                            <i class="bi bi-eye me-1"></i>View Template
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Assignment Tracking -->
            <div class="col-lg-6">
                <div class="card h-100">
                    <div class="card-header bg-warning text-white">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-clipboard-check me-2"></i>Assignment Management
                        </h5>
                    </div>
                    <div class="card-body">
                        <p class="card-text">
                            Advanced assignment tracking with submission monitoring, bulk grading, 
                            and deadline management.
                        </p>
                        <div class="d-flex gap-2 mb-3">
                            <span class="badge bg-danger">Deadline Tracking</span>
                            <span class="badge bg-primary">Bulk Operations</span>
                            <span class="badge bg-success">Real-time Updates</span>
                        </div>
                        <a href="pages/templates/assignment-template.php" class="btn btn-warning">
                            <i class="bi bi-eye me-1"></i>View Template
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Technical Features -->
<div class="row mb-5">
    <div class="col-lg-12">
        <div class="text-center mb-5">
            <h2 class="fw-bold">Built with Modern Technologies</h2>
            <p class="text-muted">Leveraging the best tools for performance and maintainability</p>
        </div>
        
        <div class="row g-4">
            <div class="col-lg-3 col-md-6">
                <div class="text-center">
                    <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" 
                         style="width: 80px; height: 80px;">
                        <i class="bi bi-bootstrap fs-2"></i>
                    </div>
                    <h5>Bootstrap 5</h5>
                    <p class="text-muted">Modern CSS framework with utilities and components</p>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6">
                <div class="text-center">
                    <div class="bg-success text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" 
                         style="width: 80px; height: 80px;">
                        <i class="bi bi-filetype-js fs-2"></i>
                    </div>
                    <h5>Vanilla JavaScript</h5>
                    <p class="text-muted">Lightweight, framework-agnostic JavaScript</p>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6">
                <div class="text-center">
                    <div class="bg-info text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" 
                         style="width: 80px; height: 80px;">
                        <i class="bi bi-phone fs-2"></i>
                    </div>
                    <h5>Mobile-First</h5>
                    <p class="text-muted">Responsive design that works on all devices</p>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6">
                <div class="text-center">
                    <div class="bg-warning text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" 
                         style="width: 80px; height: 80px;">
                        <i class="bi bi-universal-access fs-2"></i>
                    </div>
                    <h5>Accessible</h5>
                    <p class="text-muted">WCAG compliant design patterns</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Call to Action -->
<div class="row">
    <div class="col-lg-8 mx-auto">
        <div class="card bg-primary text-white">
            <div class="card-body text-center py-5">
                <h2 class="fw-bold mb-3">Ready to Get Started?</h2>
                <p class="lead mb-4">
                    Explore our templates and see how EduPlatform can transform your educational workflow.
                </p>
                <div class="d-flex justify-content-center gap-3 flex-wrap">
                    <a href="pages/templates/teacher-dashboard-template.php" class="btn btn-light btn-lg">
                        <i class="bi bi-speedometer2 me-2"></i>Teacher Dashboard
                    </a>
                    <a href="pages/templates/student-dashboard-template.php" class="btn btn-outline-light btn-lg">
                        <i class="bi bi-mortarboard me-2"></i>Student Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Demo Modal -->
<div class="modal fade" id="demoModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-play-circle me-2"></i>EduPlatform Demo
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="ratio ratio-16x9 bg-dark rounded">
                    <div class="d-flex align-items-center justify-content-center h-100">
                        <div class="text-center text-white">
                            <i class="bi bi-play-circle fs-1 mb-3"></i>
                            <h5>Demo Video</h5>
                            <p>Video content would be embedded here</p>
                        </div>
                    </div>
                </div>
                <div class="mt-4">
                    <h6>What you'll see in this demo:</h6>
                    <ul>
                        <li>Teacher dashboard navigation and features</li>
                        <li>Student course enrollment process</li>
                        <li>Assignment creation and grading workflow</li>
                        <li>Real-time progress tracking</li>
                        <li>Mobile responsive design</li>
                    </ul>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <a href="pages/templates/teacher-dashboard-template.php" class="btn btn-primary">
                    Try It Now
                </a>
            </div>
        </div>
    </div>
</div>

<style>
.text-purple {
    color: #7c3aed !important;
}

.card:hover {
    transform: translateY(-5px);
    transition: all 0.3s ease;
}

.bg-primary {
    background: linear-gradient(135deg, #2563eb, #1d4ed8) !important;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Add hover effects to cards
    const cards = document.querySelectorAll('.card');
    cards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-5px)';
            this.style.boxShadow = '0 0.5rem 1rem rgba(0, 0, 0, 0.15)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
            this.style.boxShadow = '';
        });
    });
    
    // Smooth scroll for anchor links
    const anchorLinks = document.querySelectorAll('a[href^="#"]');
    anchorLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth'
                });
            }
        });
    });
    
    // Add animation to feature icons
    const featureIcons = document.querySelectorAll('.card .bi');
    featureIcons.forEach((icon, index) => {
        icon.style.animation = `bounce 2s infinite`;
        icon.style.animationDelay = `${index * 0.2}s`;
    });
});

// Add bounce animation
const style = document.createElement('style');
style.textContent = `
    @keyframes bounce {
        0%, 20%, 53%, 80%, 100% {
            transform: translate3d(0,0,0);
        }
        40%, 43% {
            transform: translate3d(0, -30px, 0);
        }
        70% {
            transform: translate3d(0, -15px, 0);
        }
        90% {
            transform: translate3d(0, -4px, 0);
        }
    }
`;
document.head.appendChild(style);
</script>

<?php include 'includes/footer.php'; ?>
