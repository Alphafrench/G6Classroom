/**
 * Bootstrap 5 Educational Platform UI Interactions
 * Handles educational platform UI interactions and functionality
 */

class BootstrapUI {
    constructor() {
        this.init();
    }

    init() {
        this.initTooltips();
        this.initPopovers();
        this.initFormValidation();
        this.initDataTables();
        this.initModals();
        this.initNavTabs();
        this.initProgressBars();
        this.initAlerts();
        this.initSidebar();
        this.initSearchFilters();
        this.initEducationalFeatures();
    }

    // Educational Platform Specific Features
    initEducationalFeatures() {
        this.initGradeCalculations();
        this.initAttendanceTracking();
        this.initCourseManagement();
        this.initAssignmentTracking();
        this.initStudentProgress();
        this.initScheduleTimeline();
    }

    // Initialize Bootstrap tooltips
    initTooltips() {
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    }

    // Initialize Bootstrap popovers
    initPopovers() {
        const popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
        popoverTriggerList.map(function (popoverTriggerEl) {
            return new bootstrap.Popover(popoverTriggerEl);
        });
    }

    // Enhanced form validation
    initFormValidation() {
        const forms = document.querySelectorAll('.needs-validation');
        
        Array.from(forms).forEach(form => {
            form.addEventListener('submit', event => {
                if (!form.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();
                    this.showValidationFeedback(form);
                } else {
                    this.handleFormSubmit(form);
                }
                form.classList.add('was-validated');
            });

            // Real-time validation
            const inputs = form.querySelectorAll('input, select, textarea');
            inputs.forEach(input => {
                input.addEventListener('input', () => {
                    this.validateField(input);
                });
            });
        });
    }

    validateField(field) {
        const isValid = field.checkValidity();
        const feedbackElement = field.parentNode.querySelector('.invalid-feedback');
        
        if (!isValid && feedbackElement) {
            field.classList.add('is-invalid');
            field.classList.remove('is-valid');
        } else {
            field.classList.add('is-valid');
            field.classList.remove('is-invalid');
        }
    }

    showValidationFeedback(form) {
        const invalidFields = form.querySelectorAll('.is-invalid');
        if (invalidFields.length > 0) {
            invalidFields[0].focus();
        }
    }

    handleFormSubmit(form) {
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        
        // Show loading state
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status"></span>Processing...';
        submitBtn.disabled = true;
        
        // Simulate form submission (replace with actual submission logic)
        setTimeout(() => {
            this.showAlert('success', 'Form submitted successfully!');
            form.reset();
            form.classList.remove('was-validated');
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        }, 2000);
    }

    // Initialize data tables with Bootstrap styling
    initDataTables() {
        const tables = document.querySelectorAll('.data-table');
        
        tables.forEach(table => {
            // Add Bootstrap classes
            table.classList.add('table', 'table-striped', 'table-hover');
            
            // Add search functionality
            this.addTableSearch(table);
            
            // Add sorting indicators
            this.addSortingIndicators(table);
            
            // Add pagination if needed
            this.addTablePagination(table);
        });
    }

    addTableSearch(table) {
        const searchInput = table.parentNode.querySelector('.table-search');
        if (searchInput) {
            searchInput.addEventListener('keyup', () => {
                this.filterTable(table, searchInput.value);
            });
        }
    }

    filterTable(table, searchTerm) {
        const rows = table.querySelectorAll('tbody tr');
        const term = searchTerm.toLowerCase();
        
        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(term) ? '' : 'none';
        });
    }

    addSortingIndicators(table) {
        const headers = table.querySelectorAll('th[data-sort]');
        
        headers.forEach(header => {
            header.style.cursor = 'pointer';
            header.addEventListener('click', () => {
                this.sortTable(table, header);
            });
        });
    }

    sortTable(table, header) {
        const tbody = table.querySelector('tbody');
        const rows = Array.from(tbody.querySelectorAll('tr'));
        const columnIndex = Array.from(header.parentNode.children).indexOf(header);
        const isAscending = header.classList.contains('sort-asc');
        
        // Remove sorting classes from all headers
        table.querySelectorAll('th').forEach(th => {
            th.classList.remove('sort-asc', 'sort-desc');
        });
        
        // Add appropriate class to clicked header
        header.classList.add(isAscending ? 'sort-desc' : 'sort-asc');
        
        rows.sort((a, b) => {
            const aVal = a.children[columnIndex].textContent.trim();
            const bVal = b.children[columnIndex].textContent.trim();
            
            if (isAscending) {
                return bVal.localeCompare(aVal);
            } else {
                return aVal.localeCompare(bVal);
            }
        });
        
        rows.forEach(row => tbody.appendChild(row));
    }

    addTablePagination(table) {
        // Add pagination logic here if needed
    }

    // Modal management
    initModals() {
        const modals = document.querySelectorAll('.modal');
        
        modals.forEach(modal => {
            modal.addEventListener('show.bs.modal', (event) => {
                this.onModalShow(modal, event.relatedTarget);
            });
            
            modal.addEventListener('hide.bs.modal', () => {
                this.onModalHide(modal);
            });
        });
    }

    onModalShow(modal, trigger) {
        // Clear form data
        const form = modal.querySelector('form');
        if (form) {
            form.reset();
            form.classList.remove('was-validated');
        }
        
        // Set modal title if triggered by data attribute
        if (trigger && trigger.dataset.modalTitle) {
            const title = modal.querySelector('.modal-title');
            if (title) title.textContent = trigger.dataset.modalTitle;
        }
    }

    onModalHide(modal) {
        // Cleanup after modal hide
    }

    // Show modal programmatically
    showModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            const bsModal = new bootstrap.Modal(modal);
            bsModal.show();
        }
    }

    // Navigation tabs
    initNavTabs() {
        const tabTriggers = document.querySelectorAll('[data-bs-toggle="tab"]');
        
        tabTriggers.forEach(trigger => {
            trigger.addEventListener('shown.bs.tab', (event) => {
                this.onTabShown(event.target);
            });
        });
    }

    onTabShown(target) {
        // Refresh content or perform actions when tab is shown
        console.log('Tab shown:', target.getAttribute('href'));
    }

    // Progress bars
    initProgressBars() {
        const progressBars = document.querySelectorAll('.progress-bar');
        
        progressBars.forEach(bar => {
            const target = bar.dataset.target || 100;
            this.animateProgressBar(bar, target);
        });
    }

    animateProgressBar(bar, target) {
        let width = 0;
        const increment = target / 100;
        
        const timer = setInterval(() => {
            width += increment;
            bar.style.width = width + '%';
            
            if (width >= target) {
                clearInterval(timer);
                bar.setAttribute('aria-valuenow', target);
            }
        }, 20);
    }

    // Alert management
    initAlerts() {
        const alerts = document.querySelectorAll('.alert');
        
        alerts.forEach(alert => {
            const closeBtn = alert.querySelector('.btn-close');
            if (closeBtn) {
                closeBtn.addEventListener('click', () => {
                    this.hideAlert(alert);
                });
            }
        });
    }

    showAlert(type, message, dismissible = true) {
        const alertContainer = document.querySelector('.alert-container') || 
                             document.querySelector('.container-fluid') ||
                             document.body;
        
        const alertId = 'alert-' + Date.now();
        const dismissBtn = dismissible ? 
            '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>' : '';
        
        const alertHtml = `
            <div class="alert alert-${type} alert-dismissible fade show" id="${alertId}">
                ${message}
                ${dismissBtn}
            </div>
        `;
        
        alertContainer.insertAdjacentHTML('afterbegin', alertHtml);
        
        // Auto-dismiss after 5 seconds
        if (dismissible) {
            setTimeout(() => {
                this.hideAlert(document.getElementById(alertId));
            }, 5000);
        }
    }

    hideAlert(alert) {
        if (alert) {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }
    }

    // Sidebar management
    initSidebar() {
        const sidebarToggle = document.querySelector('[data-bs-toggle="sidebar"]');
        const sidebar = document.querySelector('.sidebar');
        
        if (sidebarToggle && sidebar) {
            sidebarToggle.addEventListener('click', () => {
                sidebar.classList.toggle('show');
            });
            
            // Close sidebar when clicking outside on mobile
            document.addEventListener('click', (e) => {
                if (window.innerWidth <= 768 && 
                    !sidebar.contains(e.target) && 
                    !sidebarToggle.contains(e.target)) {
                    sidebar.classList.remove('show');
                }
            });
        }
    }

    // Search and filter functionality
    initSearchFilters() {
        const searchInputs = document.querySelectorAll('[data-filter]');
        
        searchInputs.forEach(input => {
            input.addEventListener('input', (e) => {
                this.applyFilters(e.target);
            });
        });
    }

    applyFilters(input) {
        const filterType = input.dataset.filter;
        const filterValue = input.value.toLowerCase();
        const targets = document.querySelectorAll(input.dataset.target);
        
        targets.forEach(target => {
            if (target.matches('.card, .table-row, .list-item')) {
                const text = target.textContent.toLowerCase();
                target.style.display = text.includes(filterValue) ? '' : 'none';
            }
        });
    }

    // Educational Platform Specific Methods
    
    // Grade Calculation and Display
    initGradeCalculations() {
        // Calculate GPA from grades array
        this.calculateGPA = (grades) => {
            if (!grades || grades.length === 0) return 0;
            
            const gradePoints = {
                'A+': 4.0, 'A': 4.0, 'A-': 3.7,
                'B+': 3.3, 'B': 3.0, 'B-': 2.7,
                'C+': 2.3, 'C': 2.0, 'C-': 1.7,
                'D+': 1.3, 'D': 1.0, 'F': 0.0
            };
            
            const totalPoints = grades.reduce((sum, grade) => {
                return sum + (gradePoints[grade.toUpperCase()] || 0);
            }, 0);
            
            return (totalPoints / grades.length).toFixed(2);
        };
        
        // Update grade displays
        const gradeDisplays = document.querySelectorAll('.grade-display');
        gradeDisplays.forEach(display => {
            const grade = display.dataset.grade;
            const percentage = display.dataset.percentage;
            
            if (percentage) {
                const gradeClass = this.getGradeClass(parseFloat(percentage));
                display.classList.add(gradeClass);
            }
        });
    }
    
    getGradeClass(percentage) {
        if (percentage >= 97) return 'a-plus';
        if (percentage >= 93) return 'a';
        if (percentage >= 90) return 'a-minus';
        if (percentage >= 87) return 'b-plus';
        if (percentage >= 83) return 'b';
        if (percentage >= 80) return 'b-minus';
        if (percentage >= 77) return 'c-plus';
        if (percentage >= 73) return 'c';
        if (percentage >= 70) return 'c-minus';
        if (percentage >= 67) return 'd-plus';
        if (percentage >= 65) return 'd';
        return 'f';
    }
    
    // Attendance Tracking
    initAttendanceTracking() {
        const attendanceRecords = document.querySelectorAll('.attendance-indicator');
        
        attendanceRecords.forEach(record => {
            const status = record.dataset.status;
            record.classList.add(status);
            
            // Add attendance chart functionality
            const chartContainer = record.closest('.card, .stats-card')?.querySelector('.attendance-chart');
            if (chartContainer && chartContainer.dataset) {
                this.createAttendanceChart(chartContainer);
            }
        });
    }
    
    createAttendanceChart(container) {
        const data = JSON.parse(container.dataset.chart || '[]');
        // Simple text-based chart (can be enhanced with Chart.js)
        const presentDays = data.filter(d => d.status === 'present').length;
        const totalDays = data.length;
        const percentage = totalDays > 0 ? (presentDays / totalDays) * 100 : 0;
        
        container.innerHTML = `
            <div class="progress-circle" style="--progress: ${percentage}%">
                <div class="progress-circle-content">
                    <div class="progress-circle-value">${Math.round(percentage)}%</div>
                    <div class="progress-circle-label">Present</div>
                </div>
            </div>
            <div class="mt-3 text-center">
                <small class="text-muted">${presentDays}/${totalDays} days</small>
            </div>
        `;
    }
    
    // Course Management
    initCourseManagement() {
        // Course enrollment functionality
        const enrollmentButtons = document.querySelectorAll('[data-enroll]');
        enrollmentButtons.forEach(button => {
            button.addEventListener('click', (e) => {
                e.preventDefault();
                const courseId = button.dataset.enroll;
                this.enrollInCourse(courseId, button);
            });
        });
        
        // Course completion tracking
        const courseCards = document.querySelectorAll('.course-card');
        courseCards.forEach(card => {
            const progress = card.dataset.progress;
            if (progress) {
                this.updateCourseProgress(card, parseFloat(progress));
            }
        });
    }
    
    enrollInCourse(courseId, button) {
        // Simulate enrollment
        button.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Enrolling...';
        button.disabled = true;
        
        setTimeout(() => {
            button.innerHTML = '<i class="bi bi-check me-2"></i>Enrolled';
            button.classList.remove('btn-primary');
            button.classList.add('btn-success');
            
            this.showAlert('success', 'Successfully enrolled in course!');
        }, 2000);
    }
    
    updateCourseProgress(card, progress) {
        const progressBar = card.querySelector('.progress-bar');
        if (progressBar) {
            progressBar.style.width = progress + '%';
            progressBar.setAttribute('aria-valuenow', progress);
            
            const progressText = card.querySelector('.progress-text');
            if (progressText) {
                progressText.textContent = `${Math.round(progress)}% Complete`;
            }
        }
    }
    
    // Assignment Tracking
    initAssignmentTracking() {
        const assignmentCards = document.querySelectorAll('.assignment-item');
        
        assignmentCards.forEach(card => {
            const status = card.dataset.status;
            const dueDate = new Date(card.dataset.dueDate);
            const today = new Date();
            
            // Update status based on due date
            if (dueDate < today && status !== 'submitted' && status !== 'graded') {
                const statusBadge = card.querySelector('.assignment-status');
                statusBadge.textContent = 'Overdue';
                statusBadge.classList.remove('pending', 'submitted', 'graded');
                statusBadge.classList.add('overdue');
            }
            
            // Add submission functionality
            const submitBtn = card.querySelector('[data-submit]');
            if (submitBtn) {
                submitBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    this.submitAssignment(card, submitBtn);
                });
            }
        });
    }
    
    submitAssignment(card, button) {
        button.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Submitting...';
        button.disabled = true;
        
        setTimeout(() => {
            button.innerHTML = '<i class="bi bi-check me-2"></i>Submitted';
            button.classList.remove('btn-warning');
            button.classList.add('btn-success');
            
            const statusBadge = card.querySelector('.assignment-status');
            statusBadge.textContent = 'Submitted';
            statusBadge.classList.remove('pending', 'overdue');
            statusBadge.classList.add('submitted');
            
            this.showAlert('success', 'Assignment submitted successfully!');
        }, 2000);
    }
    
    // Student Progress Tracking
    initStudentProgress() {
        const progressCircles = document.querySelectorAll('.progress-circle');
        progressCircles.forEach(circle => {
            const value = circle.dataset.value || 0;
            circle.style.setProperty('--progress', value + '%');
        });
        
        // Grade trend analysis
        const gradeCharts = document.querySelectorAll('.grade-trend-chart');
        gradeCharts.forEach(chart => {
            this.createGradeTrendChart(chart);
        });
    }
    
    createGradeTrendChart(container) {
        const data = JSON.parse(container.dataset.grades || '[]');
        if (data.length === 0) return;
        
        // Simple trend calculation
        const recentGrades = data.slice(-5); // Last 5 grades
        const avg = recentGrades.reduce((sum, grade) => sum + grade, 0) / recentGrades.length;
        const trend = avg > 80 ? 'improving' : avg < 70 ? 'declining' : 'stable';
        
        container.innerHTML = `
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <div class="h5 mb-0">${avg.toFixed(1)}%</div>
                    <small class="text-muted">Recent Average</small>
                </div>
                <div class="text-end">
                    <i class="bi bi-arrow-${trend === 'improving' ? 'up' : trend === 'declining' ? 'down' : 'right'} 
                       text-${trend === 'improving' ? 'success' : trend === 'declining' ? 'danger' : 'warning'} fs-4"></i>
                    <div class="small text-muted">${trend.charAt(0).toUpperCase() + trend.slice(1)}</div>
                </div>
            </div>
        `;
    }
    
    // Schedule Timeline
    initScheduleTimeline() {
        const timelineItems = document.querySelectorAll('.schedule-item');
        timelineItems.forEach((item, index) => {
            // Add staggered animation
            item.style.animationDelay = (index * 0.1) + 's';
            item.classList.add('slide-in');
            
            // Add click functionality for schedule details
            item.addEventListener('click', () => {
                const details = item.dataset.details;
                if (details) {
                    this.showScheduleDetails(JSON.parse(details));
                }
            });
        });
    }
    
    showScheduleDetails(details) {
        const modal = document.getElementById('scheduleModal') || this.createScheduleModal();
        const modalBody = modal.querySelector('.modal-body');
        
        modalBody.innerHTML = `
            <div class="row">
                <div class="col-md-6">
                    <h6>Course</h6>
                    <p>${details.course}</p>
                </div>
                <div class="col-md-6">
                    <h6>Time</h6>
                    <p>${details.time}</p>
                </div>
                <div class="col-md-6">
                    <h6>Room</h6>
                    <p>${details.room}</p>
                </div>
                <div class="col-md-6">
                    <h6>Instructor</h6>
                    <p>${details.instructor}</p>
                </div>
            </div>
            <div class="mt-3">
                <h6>Description</h6>
                <p>${details.description || 'No additional details available.'}</p>
            </div>
        `;
        
        const bsModal = new bootstrap.Modal(modal);
        bsModal.show();
    }
    
    createScheduleModal() {
        const modal = document.createElement('div');
        modal.className = 'modal fade';
        modal.id = 'scheduleModal';
        modal.innerHTML = `
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Schedule Details</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <!-- Content will be populated dynamically -->
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        `;
        document.body.appendChild(modal);
        return modal;
    }

    // Utility methods
    formatCurrency(amount, currency = 'USD') {
        return new Intl.NumberFormat('en-US', {
            style: 'currency',
            currency: currency
        }).format(amount);
    }

    formatDate(date, options = {}) {
        const defaultOptions = {
            year: 'numeric',
            month: 'short',
            day: 'numeric'
        };
        return new Intl.DateTimeFormat('en-US', { ...defaultOptions, ...options }).format(new Date(date));
    }

    confirmAction(message, callback) {
        if (confirm(message)) {
            callback();
        }
    }

    copyToClipboard(text) {
        navigator.clipboard.writeText(text).then(() => {
            this.showAlert('success', 'Copied to clipboard!');
        }).catch(() => {
            this.showAlert('danger', 'Failed to copy to clipboard');
        });
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    new BootstrapUI();
});

// Global utility functions
window.BootstrapUI = BootstrapUI;
window.showAlert = (type, message, dismissible = true) => {
    if (window.bootstrapUI) {
        window.bootstrapUI.showAlert(type, message, dismissible);
    }
};

window.showModal = (modalId) => {
    if (window.bootstrapUI) {
        window.bootstrapUI.showModal(modalId);
    }
};

window.confirmAction = (message, callback) => {
    if (window.bootstrapUI) {
        window.bootstrapUI.confirmAction(message, callback);
    }
};

// Export for module systems
if (typeof module !== 'undefined' && module.exports) {
    module.exports = BootstrapUI;
}