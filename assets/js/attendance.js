/**
 * Attendance Management System JavaScript
 * Handles real-time clock, AJAX requests, and interactive features
 */

// Global variables
let clockInterval;
let workDurationInterval;
let currentEmployeeId = 1;

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    initializeClock();
    initializeWorkDuration();
    loadRecentRecords();
    initializeEventListeners();
    initializeCharts();
});

// Real-time clock functionality
function initializeClock() {
    updateClock();
    clockInterval = setInterval(updateClock, 1000);
}

function updateClock() {
    const now = new Date();
    
    // Update time display
    const timeDisplay = document.getElementById('time-display');
    if (timeDisplay) {
        timeDisplay.textContent = now.toLocaleTimeString('en-US', {
            hour12: true,
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit'
        });
    }
    
    // Update date display
    const dateDisplay = document.getElementById('current-date');
    if (dateDisplay) {
        dateDisplay.textContent = now.toLocaleDateString('en-US', {
            weekday: 'long',
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });
    }
}

// Work duration timer for checked-in employees
function initializeWorkDuration() {
    const checkInBtn = document.getElementById('check-in-btn');
    const checkOutBtn = document.getElementById('check-out-btn');
    
    if (checkOutBtn) {
        // Employee is checked in, start duration timer
        startWorkDurationTimer();
    }
}

function startWorkDurationTimer() {
    const durationElement = document.getElementById('work-duration');
    if (!durationElement) return;
    
    // Get check-in time from the page
    const checkInTimeStr = durationElement.getAttribute('data-check-in-time') || 
                          document.querySelector('.alert-success p').textContent.match(/\d{1,2}:\d{2}\s?[AP]M/)?.[0];
    
    if (!checkInTimeStr) return;
    
    workDurationInterval = setInterval(() => {
        const now = new Date();
        const checkInDate = new Date();
        
        // Parse check-in time (assumes today)
        const timeMatch = checkInTimeStr.match(/(\d{1,2}):(\d{2})\s?([AP]M)/);
        if (timeMatch) {
            let hours = parseInt(timeMatch[1]);
            const minutes = parseInt(timeMatch[2]);
            const period = timeMatch[3];
            
            if (period === 'PM' && hours !== 12) hours += 12;
            if (period === 'AM' && hours === 12) hours = 0;
            
            checkInDate.setHours(hours, minutes, 0, 0);
            
            const diff = now - checkInDate;
            const hoursElapsed = Math.floor(diff / 3600000);
            const minutesElapsed = Math.floor((diff % 3600000) / 60000);
            const secondsElapsed = Math.floor((diff % 60000) / 1000);
            
            durationElement.textContent = 
                `${hoursElapsed.toString().padStart(2, '0')}:${minutesElapsed.toString().padStart(2, '0')}:${secondsElapsed.toString().padStart(2, '0')}`;
        }
    }, 1000);
}

// Initialize event listeners
function initializeEventListeners() {
    // Check-in button
    const checkInBtn = document.getElementById('check-in-btn');
    if (checkInBtn) {
        checkInBtn.addEventListener('click', handleCheckIn);
    }
    
    // Check-out button
    const checkOutBtn = document.getElementById('check-out-btn');
    if (checkOutBtn) {
        checkOutBtn.addEventListener('click', handleCheckOut);
    }
    
    // Report form changes
    const reportForm = document.getElementById('report-form');
    if (reportForm) {
        const reportTypeSelect = document.getElementById('report_type');
        if (reportTypeSelect) {
            reportTypeSelect.addEventListener('change', handleReportTypeChange);
        }
    }
    
    // Filter form auto-submit
    const filterForm = document.getElementById('filter-form');
    if (filterForm) {
        const inputs = filterForm.querySelectorAll('input, select');
        inputs.forEach(input => {
            input.addEventListener('change', () => {
                // Auto-submit after a short delay
                setTimeout(() => {
                    filterForm.submit();
                }, 500);
            });
        });
    }
}

// Handle check-in functionality
async function handleCheckIn() {
    const button = document.getElementById('check-in-btn');
    const originalText = button.innerHTML;
    
    try {
        // Show loading state
        button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Checking In...';
        button.disabled = true;
        
        const response = await fetch('/api/attendance/checkin.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                employee_id: currentEmployeeId,
                timestamp: new Date().toISOString()
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            showMessage('Successfully checked in! Welcome to work.', 'success');
            // Refresh page to update UI
            setTimeout(() => {
                location.reload();
            }, 1500);
        } else {
            throw new Error(data.message || 'Check-in failed');
        }
        
    } catch (error) {
        console.error('Check-in error:', error);
        showMessage('Error during check-in: ' + error.message, 'danger');
    } finally {
        // Restore button
        button.innerHTML = originalText;
        button.disabled = false;
    }
}

// Handle check-out functionality
async function handleCheckOut() {
    const button = document.getElementById('check-out-btn');
    const originalText = button.innerHTML;
    
    try {
        // Show loading state
        button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Checking Out...';
        button.disabled = true;
        
        const response = await fetch('/api/attendance/checkout.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                employee_id: currentEmployeeId,
                timestamp: new Date().toISOString()
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            showMessage(`Successfully checked out! Total hours: ${data.total_hours}`, 'success');
            // Refresh page to update UI
            setTimeout(() => {
                location.reload();
            }, 1500);
        } else {
            throw new Error(data.message || 'Check-out failed');
        }
        
    } catch (error) {
        console.error('Check-out error:', error);
        showMessage('Error during check-out: ' + error.message, 'danger');
    } finally {
        // Restore button
        button.innerHTML = originalText;
        button.disabled = false;
    }
}

// Load recent attendance records
async function loadRecentRecords() {
    const container = document.getElementById('recent-records');
    if (!container) return;
    
    try {
        const response = await fetch(`/api/attendance/recent.php?employee_id=${currentEmployeeId}&limit=5`);
        const data = await response.json();
        
        if (data.success && data.records) {
            displayRecentRecords(data.records);
        } else {
            container.innerHTML = `
                <tr>
                    <td colspan="6" class="text-center text-muted">
                        <i class="fas fa-info-circle"></i> No recent records found
                    </td>
                </tr>
            `;
        }
        
    } catch (error) {
        console.error('Error loading recent records:', error);
        container.innerHTML = `
            <tr>
                <td colspan="6" class="text-center text-danger">
                    <i class="fas fa-exclamation-triangle"></i> Error loading records
                </td>
            </tr>
        `;
    }
}

// Display recent records in the table
function displayRecentRecords(records) {
    const container = document.getElementById('recent-records');
    if (!container) return;
    
    if (records.length === 0) {
        container.innerHTML = `
            <tr>
                <td colspan="6" class="text-center text-muted">
                    <i class="fas fa-info-circle"></i> No records found
                </td>
            </tr>
        `;
        return;
    }
    
    const rows = records.map(record => {
        const checkInDate = new Date(record.check_in_time);
        const checkOutDate = record.check_out_time ? new Date(record.check_out_time) : null;
        
        const statusClass = getStatusClass(record.status);
        const statusIcon = getStatusIcon(record.status);
        
        return `
            <tr>
                <td>${checkInDate.toLocaleDateString()}</td>
                <td>
                    <i class="fas fa-sign-in-alt text-success me-1"></i>
                    ${checkInDate.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' })}
                </td>
                <td>
                    ${checkOutDate ? 
                        `<i class="fas fa-sign-out-alt text-danger me-1"></i>
                         ${checkOutDate.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' })}` : 
                        '<span class="text-muted">-</span>'
                    }
                </td>
                <td>
                    <span class="badge bg-primary">${record.total_hours ? record.total_hours.toFixed(1) : '0.0'}h</span>
                </td>
                <td>
                    <span class="badge ${statusClass}">
                        <i class="fas ${statusIcon} me-1"></i>
                        ${record.status}
                    </span>
                </td>
                <td>
                    <button class="btn btn-sm btn-outline-primary" onclick="viewDetails(${record.id})">
                        <i class="fas fa-eye"></i>
                    </button>
                </td>
            </tr>
        `;
    }).join('');
    
    container.innerHTML = rows;
}

// Helper functions for status styling
function getStatusClass(status) {
    const classes = {
        'present': 'bg-success',
        'absent': 'bg-danger',
        'late': 'bg-warning',
        'overtime': 'bg-info',
        'incomplete': 'bg-secondary'
    };
    return classes[status] || 'bg-secondary';
}

function getStatusIcon(status) {
    const icons = {
        'present': 'fa-check',
        'absent': 'fa-times',
        'late': 'fa-exclamation-triangle',
        'overtime': 'fa-clock',
        'incomplete': 'fa-minus'
    };
    return icons[status] || 'fa-question';
}

// Show message to user
function showMessage(message, type = 'info', duration = 5000) {
    const container = document.getElementById('message-container') || createMessageContainer();
    
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
    alertDiv.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    container.appendChild(alertDiv);
    
    // Auto remove after duration
    setTimeout(() => {
        if (alertDiv.parentNode) {
            alertDiv.remove();
        }
    }, duration);
}

function createMessageContainer() {
    const container = document.createElement('div');
    container.id = 'message-container';
    container.style.cssText = 'position: fixed; top: 20px; right: 20px; z-index: 9999;';
    document.body.appendChild(container);
    return container;
}

// Handle report type changes
function handleReportTypeChange() {
    const reportType = document.getElementById('report_type').value;
    const dateFromInput = document.getElementById('date_from');
    const dateToInput = document.getElementById('date_to');
    
    const today = new Date();
    
    switch (reportType) {
        case 'weekly':
            const startOfWeek = new Date(today.setDate(today.getDate() - today.getDay() + 1));
            const endOfWeek = new Date(today.setDate(today.getDate() - today.getDay() + 7));
            dateFromInput.value = startOfWeek.toISOString().split('T')[0];
            dateToInput.value = endOfWeek.toISOString().split('T')[0];
            break;
        case 'monthly':
            const startOfMonth = new Date(today.getFullYear(), today.getMonth(), 1);
            const endOfMonth = new Date(today.getFullYear(), today.getMonth() + 1, 0);
            dateFromInput.value = startOfMonth.toISOString().split('T')[0];
            dateToInput.value = endOfMonth.toISOString().split('T')[0];
            break;
        case 'yearly':
            const startOfYear = new Date(today.getFullYear(), 0, 1);
            const endOfYear = new Date(today.getFullYear(), 11, 31);
            dateFromInput.value = startOfYear.toISOString().split('T')[0];
            dateToInput.value = endOfYear.toISOString().split('T')[0];
            break;
    }
}

// Utility functions
function clearFilters() {
    document.getElementById('date_from').value = '';
    document.getElementById('date_to').value = '';
    document.getElementById('status_filter').value = '';
    document.getElementById('per_page').value = '20';
    document.getElementById('filter-form').submit();
}

function refreshData() {
    showMessage('Refreshing data...', 'info', 2000);
    
    // Refresh current page data
    if (typeof loadRecentRecords === 'function') {
        loadRecentRecords();
    }
    
    // Update stats if present
    updateStats();
    
    setTimeout(() => {
        location.reload();
    }, 1000);
}

function updateStats() {
    // Update today's hours, weekly hours, etc.
    fetch(`/api/attendance/stats.php?employee_id=${currentEmployeeId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const elements = {
                    'today-hours': data.today_hours,
                    'this-week-hours': data.this_week_hours,
                    'this-month-hours': data.this_month_hours,
                    'overtime-hours': data.overtime_hours
                };
                
                Object.keys(elements).forEach(id => {
                    const element = document.getElementById(id);
                    if (element) {
                        element.textContent = elements[id];
                    }
                });
            }
        })
        .catch(error => console.error('Error updating stats:', error));
}

// Modal and view functions
function viewDetails(recordId) {
    // Show loading state
    const modal = new bootstrap.Modal(document.getElementById('detailModal'));
    document.getElementById('modal-content').innerHTML = `
        <div class="text-center">
            <div class="spinner-border" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2">Loading attendance details...</p>
        </div>
    `;
    modal.show();
    
    // Load details via AJAX
    fetch(`/api/attendance/details.php?id=${recordId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('modal-content').innerHTML = `
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Check-in Information</h6>
                            <p><strong>Time:</strong> ${new Date(data.record.check_in_time).toLocaleString()}</p>
                            <p><strong>Location:</strong> ${data.record.location || 'Office'}</p>
                            <p><strong>IP Address:</strong> ${data.record.ip_address || 'N/A'}</p>
                        </div>
                        <div class="col-md-6">
                            <h6>Check-out Information</h6>
                            ${data.record.check_out_time ? 
                                `<p><strong>Time:</strong> ${new Date(data.record.check_out_time).toLocaleString()}</p>
                                 <p><strong>Duration:</strong> ${data.record.total_hours} hours</p>` : 
                                '<p class="text-muted">Not checked out yet</p>'
                            }
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-12">
                            <h6>Notes</h6>
                            <p>${data.record.notes || 'No additional notes'}</p>
                        </div>
                    </div>
                `;
            } else {
                document.getElementById('modal-content').innerHTML = `
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle"></i> 
                        Error loading attendance details: ${data.message}
                    </div>
                `;
            }
        })
        .catch(error => {
            console.error('Error loading details:', error);
            document.getElementById('modal-content').innerHTML = `
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle"></i> 
                    Network error while loading details
                </div>
            `;
        });
}

function quickCheckout(recordId) {
    if (confirm('Are you sure you want to check out this record?')) {
        fetch('/api/attendance/quick_checkout.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ record_id: recordId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showMessage('Successfully checked out!', 'success');
                setTimeout(() => location.reload(), 1500);
            } else {
                showMessage('Error: ' + data.message, 'danger');
            }
        })
        .catch(error => {
            console.error('Quick checkout error:', error);
            showMessage('Network error during checkout', 'danger');
        });
    }
}

// Export functions
function exportRecords() {
    const form = document.getElementById('filter-form');
    const formData = new FormData(form);
    
    // Build query string
    const params = new URLSearchParams();
    for (let [key, value] of formData.entries()) {
        if (value) params.append(key, value);
    }
    
    window.open(`/api/attendance/export.php?${params.toString()}`, '_blank');
}

function exportToExcel() {
    const reportForm = document.getElementById('report-form');
    const formData = new FormData(reportForm);
    
    const params = new URLSearchParams();
    for (let [key, value] of formData.entries()) {
        if (value) params.append(key, value);
    }
    
    window.open(`/api/attendance/export_excel.php?${params.toString()}`, '_blank');
}

function generatePDF() {
    const reportForm = document.getElementById('report-form');
    const formData = new FormData(reportForm);
    
    const params = new URLSearchParams();
    for (let [key, value] of formData.entries()) {
        if (value) params.append(key, value);
    }
    
    window.open(`/api/attendance/export_pdf.php?${params.toString()}`, '_blank');
}

function printReport() {
    window.print();
}

// Chart initialization functions
function initializeCharts() {
    // Only initialize if Chart.js is available and we're on the report page
    if (typeof Chart === 'undefined') return;
    
    initializeDailyHoursChart();
    initializeStatusChart();
    initializeWeeklyChart();
    initializeMonthlyChart();
}

function initializeDailyHoursChart() {
    const ctx = document.getElementById('dailyHoursChart');
    if (!ctx) return;
    
    // Sample data - replace with actual API data
    const labels = [];
    const data = [];
    const today = new Date();
    
    for (let i = 14; i >= 0; i--) {
        const date = new Date(today);
        date.setDate(date.getDate() - i);
        labels.push(date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' }));
        
        // Simulate hours data
        if (date.getDay() !== 0 && date.getDay() !== 6) { // Not weekend
            data.push(Math.random() * 2 + 7); // 7-9 hours
        } else {
            data.push(0);
        }
    }
    
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Daily Hours',
                data: data,
                borderColor: 'rgb(75, 192, 192)',
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                tension: 0.1,
                fill: true
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    max: 10,
                    title: {
                        display: true,
                        text: 'Hours'
                    }
                }
            },
            plugins: {
                title: {
                    display: true,
                    text: 'Last 15 Working Days'
                }
            }
        }
    });
}

function initializeStatusChart() {
    const ctx = document.getElementById('statusChart');
    if (!ctx) return;
    
    // Sample data
    const data = {
        labels: ['Present', 'Late', 'Overtime', 'Incomplete'],
        datasets: [{
            data: [18, 2, 3, 1],
            backgroundColor: [
                'rgba(75, 192, 192, 0.8)',
                'rgba(255, 206, 86, 0.8)',
                'rgba(255, 159, 64, 0.8)',
                'rgba(153, 102, 255, 0.8)'
            ],
            borderWidth: 2,
            borderColor: '#fff'
        }]
    };
    
    new Chart(ctx, {
        type: 'doughnut',
        data: data,
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom'
                },
                title: {
                    display: true,
                    text: 'Attendance Status Distribution'
                }
            }
        }
    });
}

function initializeWeeklyChart() {
    const ctx = document.getElementById('weeklyChart');
    if (!ctx) return;
    
    const labels = ['Week 1', 'Week 2', 'Week 3', 'Week 4'];
    const data = [40, 42, 38, 44];
    
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Weekly Hours',
                data: data,
                backgroundColor: 'rgba(54, 162, 235, 0.8)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    max: 50
                }
            }
        }
    });
}

function initializeMonthlyChart() {
    const ctx = document.getElementById('monthlyChart');
    if (!ctx) return;
    
    const labels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'];
    const thisYear = [160, 165, 158, 172, 168, 170];
    const lastYear = [155, 162, 160, 165, 158, 168];
    
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'This Year',
                    data: thisYear,
                    borderColor: 'rgb(75, 192, 192)',
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    tension: 0.1
                },
                {
                    label: 'Last Year',
                    data: lastYear,
                    borderColor: 'rgb(255, 99, 132)',
                    backgroundColor: 'rgba(255, 99, 132, 0.2)',
                    tension: 0.1
                }
            ]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    max: 180
                }
            }
        }
    });
}

// Cleanup function
function cleanup() {
    if (clockInterval) clearInterval(clockInterval);
    if (workDurationInterval) clearInterval(workDurationInterval);
}

// Cleanup on page unload
window.addEventListener('beforeunload', cleanup);