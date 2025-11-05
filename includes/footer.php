    </div> <!-- End Main Content Wrapper -->
    
    <!-- Footer -->
    <footer class="bg-dark text-light py-5 mt-5">
        <div class="container">
            <div class="row g-4">
                <!-- Brand Column -->
                <div class="col-lg-4 col-md-6">
                    <div class="d-flex align-items-center mb-3">
                        <i class="bi bi-mortarboard fs-3 text-primary me-2"></i>
                        <h5 class="mb-0">EduPlatform</h5>
                    </div>
                    <p class="text-muted mb-3">
                        Comprehensive Learning Management System designed for educators and students. 
                        Manage courses, assignments, grades, and student progress all in one place.
                    </p>
                    <div class="d-flex gap-3">
                        <a href="#" class="text-light" data-bs-toggle="tooltip" title="Facebook">
                            <i class="bi bi-facebook fs-5"></i>
                        </a>
                        <a href="#" class="text-light" data-bs-toggle="tooltip" title="Twitter">
                            <i class="bi bi-twitter fs-5"></i>
                        </a>
                        <a href="#" class="text-light" data-bs-toggle="tooltip" title="LinkedIn">
                            <i class="bi bi-linkedin fs-5"></i>
                        </a>
                        <a href="#" class="text-light" data-bs-toggle="tooltip" title="Instagram">
                            <i class="bi bi-instagram fs-5"></i>
                        </a>
                    </div>
                </div>
                
                <!-- Quick Links -->
                <div class="col-lg-2 col-md-6">
                    <h6 class="text-primary mb-3">Quick Links</h6>
                    <ul class="list-unstyled">
                        <li class="mb-2">
                            <a href="index.php" class="text-muted text-decoration-none">
                                <i class="bi bi-house me-1"></i>Dashboard
                            </a>
                        </li>
                        <li class="mb-2">
                            <a href="pages/templates/course-template.php" class="text-muted text-decoration-none">
                                <i class="bi bi-journal-bookmark me-1"></i>Courses
                            </a>
                        </li>
                        <li class="mb-2">
                            <a href="pages/templates/assignment-template.php" class="text-muted text-decoration-none">
                                <i class="bi bi-clipboard-check me-1"></i>Assignments
                            </a>
                        </li>
                        <li class="mb-2">
                            <a href="pages/templates/gradebook-template.php" class="text-muted text-decoration-none">
                                <i class="bi bi-award me-1"></i>Gradebook
                            </a>
                        </li>
                        <li class="mb-2">
                            <a href="students.php" class="text-muted text-decoration-none">
                                <i class="bi bi-people me-1"></i>Students
                            </a>
                        </li>
                    </ul>
                </div>
                
                <!-- Resources -->
                <div class="col-lg-2 col-md-6">
                    <h6 class="text-primary mb-3">Resources</h6>
                    <ul class="list-unstyled">
                        <li class="mb-2">
                            <a href="resources.php" class="text-muted text-decoration-none">
                                <i class="bi bi-folder me-1"></i>Course Materials
                            </a>
                        </li>
                        <li class="mb-2">
                            <a href="reports.php" class="text-muted text-decoration-none">
                                <i class="bi bi-graph-up me-1"></i>Analytics
                            </a>
                        </li>
                        <li class="mb-2">
                            <a href="#" class="text-muted text-decoration-none">
                                <i class="bi bi-question-circle me-1"></i>Help Center
                            </a>
                        </li>
                        <li class="mb-2">
                            <a href="#" class="text-muted text-decoration-none">
                                <i class="bi bi-calendar-event me-1"></i>Academic Calendar
                            </a>
                        </li>
                        <li class="mb-2">
                            <a href="#" class="text-muted text-decoration-none">
                                <i class="bi bi-download me-1"></i>Downloads
                            </a>
                        </li>
                    </ul>
                </div>
                
                <!-- Updates -->
                <div class="col-lg-4 col-md-6">
                    <h6 class="text-primary mb-3">Stay Informed</h6>
                    <p class="text-muted mb-3">
                        Get notified about new courses, assignments, grade updates, and important academic deadlines.
                    </p>
                    <form class="newsletter-form" data-newsletter>
                        <div class="input-group mb-3">
                            <input type="email" class="form-control" placeholder="Enter your email" 
                                   required aria-label="Email address">
                            <button class="btn btn-primary" type="submit">
                                <i class="bi bi-bell me-1"></i>Subscribe
                            </button>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="newsletter-consent" required>
                            <label class="form-check-label text-muted" for="newsletter-consent">
                                I agree to receive educational updates and notifications.
                            </label>
                        </div>
                    </form>
                </div>
            </div>
            
            <hr class="my-4 border-secondary">
            
            <!-- Footer Bottom -->
            <div class="row align-items-center">
                <div class="col-md-6">
                    <p class="text-muted mb-0">
                        &copy; <?php echo date('Y'); ?> Bootstrap UI Templates. All rights reserved.
                    </p>
                </div>
                <div class="col-md-6 text-md-end">
                    <div class="d-flex justify-content-md-end gap-3">
                        <a href="#" class="text-muted text-decoration-none small">Privacy Policy</a>
                        <a href="#" class="text-muted text-decoration-none small">Terms of Service</a>
                        <a href="#" class="text-muted text-decoration-none small">Cookie Policy</a>
                    </div>
                </div>
            </div>
        </div>
    </footer>
    
    <!-- Back to Top Button -->
    <button class="btn btn-primary btn-floating position-fixed bottom-0 end-0 m-4 rounded-circle" 
            id="backToTop" style="display: none; z-index: 1000;" 
            data-bs-toggle="tooltip" title="Back to top">
        <i class="bi bi-arrow-up"></i>
    </button>
    
    <!-- Loading Overlay (for form submissions) -->
    <div class="loading-overlay position-fixed top-0 start-0 w-100 h-100 d-none" 
         style="background-color: rgba(0, 0, 0, 0.5); z-index: 9999;">
        <div class="d-flex align-items-center justify-content-center h-100">
            <div class="bg-white p-4 rounded-3 text-center">
                <div class="spinner-border text-primary mb-3" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mb-0">Processing...</p>
            </div>
        </div>
    </div>
    
    <!-- Custom JavaScript -->
    <script src="assets/js/app.js"></script>
    
    <!-- Initialize Additional Features -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize global Bootstrap UI instance
            window.bootstrapUI = new BootstrapUI();
            
            // Back to Top Button
            const backToTopBtn = document.getElementById('backToTop');
            
            window.addEventListener('scroll', function() {
                if (window.pageYOffset > 300) {
                    backToTopBtn.style.display = 'block';
                } else {
                    backToTopBtn.style.display = 'none';
                }
            });
            
            backToTopBtn.addEventListener('click', function() {
                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });
            });
            
            // Newsletter Form
            const newsletterForm = document.querySelector('[data-newsletter]');
            if (newsletterForm) {
                newsletterForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    const email = this.querySelector('input[type="email"]').value;
                    
                    if (window.bootstrapUI) {
                        window.bootstrapUI.showAlert('success', 'Thank you for subscribing to our newsletter!');
                    }
                    
                    this.reset();
                });
            }
            
            // Auto-hide alerts after 5 seconds
            setTimeout(function() {
                const alerts = document.querySelectorAll('.alert:not(.alert-permanent)');
                alerts.forEach(alert => {
                    if (window.bootstrapUI) {
                        window.bootstrapUI.hideAlert(alert);
                    }
                });
            }, 5000);
            
            // Add loading state to all forms
            const forms = document.querySelectorAll('form');
            forms.forEach(form => {
                form.addEventListener('submit', function() {
                    const loadingOverlay = document.querySelector('.loading-overlay');
                    if (loadingOverlay) {
                        loadingOverlay.classList.remove('d-none');
                    }
                });
            });
            
            // Initialize tooltips
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
            
            // Handle responsive sidebar on mobile
            const sidebar = document.querySelector('.sidebar');
            const sidebarToggle = document.querySelector('[data-bs-toggle="sidebar"]');
            
            if (sidebar && sidebarToggle) {
                // Close sidebar when clicking outside
                document.addEventListener('click', function(e) {
                    if (window.innerWidth <= 768) {
                        if (!sidebar.contains(e.target) && !sidebarToggle.contains(e.target)) {
                            sidebar.classList.remove('show');
                        }
                    }
                });
                
                // Close sidebar on escape key
                document.addEventListener('keydown', function(e) {
                    if (e.key === 'Escape') {
                        sidebar.classList.remove('show');
                    }
                });
            }
            
            // Search functionality
            const searchForm = document.querySelector('form[role="search"]');
            if (searchForm) {
                searchForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    const searchTerm = this.querySelector('input[type="search"]').value;
                    
                    if (window.bootstrapUI) {
                        window.bootstrapUI.showAlert('info', `Searching for: "${searchTerm}"`);
                    }
                });
            }
            
            // Print functionality
            const printButtons = document.querySelectorAll('[data-print]');
            printButtons.forEach(button => {
                button.addEventListener('click', function() {
                    window.print();
                });
            });
            
            // Copy to clipboard functionality
            document.addEventListener('click', function(e) {
                if (e.target.matches('[data-copy]') || e.target.closest('[data-copy]')) {
                    e.preventDefault();
                    const element = e.target.matches('[data-copy]') ? e.target : e.target.closest('[data-copy]');
                    const text = element.dataset.copy || element.textContent;
                    
                    if (window.bootstrapUI) {
                        window.bootstrapUI.copyToClipboard(text);
                    }
                }
            });
            
            // Confirmation dialogs
            document.addEventListener('click', function(e) {
                if (e.target.matches('[data-confirm]') || e.target.closest('[data-confirm]')) {
                    e.preventDefault();
                    const element = e.target.matches('[data-confirm]') ? e.target : e.target.closest('[data-confirm]');
                    const message = element.dataset.confirm || 'Are you sure?';
                    const callback = element.getAttribute('href') || element.dataset.callback;
                    
                    if (confirm(message)) {
                        if (callback && !element.getAttribute('href')) {
                            // Execute callback function
                            if (window[callback]) {
                                window[callback]();
                            }
                        } else if (element.getAttribute('href')) {
                            // Follow link
                            window.location.href = element.getAttribute('href');
                        }
                    }
                }
            });
        });
        
        // Global utility functions
        window.formatDate = function(date, options = {}) {
            return window.bootstrapUI ? window.bootstrapUI.formatDate(date, options) : date;
        };
        
        window.formatCurrency = function(amount, currency = 'USD') {
            return window.bootstrapUI ? window.bootstrapUI.formatCurrency(amount, currency) : amount;
        };
    </script>
    
    <!-- Additional CSS for specific components -->
    <style>
        /* Notification dropdown styling */
        .notification-dropdown {
            width: 320px;
            max-height: 400px;
            overflow-y: auto;
        }
        
        /* Footer responsive adjustments */
        @media (max-width: 768px) {
            .footer .row > div {
                margin-bottom: 2rem;
            }
        }
        
        /* Back to top button animation */
        #backToTop {
            transition: all 0.3s ease;
            opacity: 0.8;
        }
        
        #backToTop:hover {
            opacity: 1;
            transform: translateY(-2px);
        }
        
        /* Loading overlay styling */
        .loading-overlay {
            backdrop-filter: blur(2px);
        }
        
        /* Newsletter form validation */
        .newsletter-form .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
        }
    </style>
    
</body>
</html>