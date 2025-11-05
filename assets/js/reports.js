/**
 * Reports Dashboard JavaScript
 * Handles chart generation, filtering, and interactive features
 */

const ReportsDashboard = {
    charts: {
        daily: null,
        department: null,
        hourly: null
    },

    /**
     * Initialize all charts with data
     */
    initCharts: function(dailyData, departmentData, hourlyData) {
        this.initDailyChart(dailyData);
        this.initDepartmentChart(departmentData);
        this.initHourlyChart(hourlyData);
    },

    /**
     * Initialize daily attendance trend chart
     */
    initDailyChart: function(data) {
        const ctx = document.getElementById('dailyChart');
        if (!ctx) return;

        const labels = data.map(item => this.formatDate(item.date));
        const counts = data.map(item => parseInt(item.count));

        this.charts.daily = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Daily Attendance',
                    data: counts,
                    borderColor: 'rgb(75, 192, 192)',
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    title: {
                        display: true,
                        text: 'Daily Attendance Trend'
                    },
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                },
                elements: {
                    point: {
                        radius: 4,
                        hoverRadius: 6
                    }
                }
            }
        });
    },

    /**
     * Initialize department distribution chart
     */
    initDepartmentChart: function(data) {
        const ctx = document.getElementById('departmentChart');
        if (!ctx) return;

        const labels = data.map(item => item.department || 'Unknown');
        const counts = data.map(item => parseInt(item.count));
        const colors = this.generateColors(counts.length);

        this.charts.department = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: labels,
                datasets: [{
                    data: counts,
                    backgroundColor: colors,
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    title: {
                        display: true,
                        text: 'Department Distribution'
                    },
                    legend: {
                        position: 'bottom',
                        labels: {
                            usePointStyle: true,
                            padding: 20
                        }
                    }
                }
            }
        });
    },

    /**
     * Initialize hourly distribution chart
     */
    initHourlyChart: function(data) {
        const ctx = document.getElementById('hourlyChart');
        if (!ctx) return;

        // Create full 24-hour dataset
        const hourLabels = [];
        const hourCounts = new Array(24).fill(0);
        
        for (let i = 0; i < 24; i++) {
            hourLabels.push(this.formatHour(i));
        }
        
        // Fill actual data
        data.forEach(item => {
            const hour = parseInt(item.hour);
            if (hour >= 0 && hour < 24) {
                hourCounts[hour] = parseInt(item.count);
            }
        });

        this.charts.hourly = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: hourLabels,
                datasets: [{
                    label: 'Attendance by Hour',
                    data: hourCounts,
                    backgroundColor: 'rgba(54, 162, 235, 0.6)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    title: {
                        display: true,
                        text: 'Hourly Attendance Distribution'
                    },
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Hour of Day'
                        }
                    }
                }
            }
        });
    },

    /**
     * Generate colors for charts
     */
    generateColors: function(count) {
        const colors = [
            '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF',
            '#FF9F40', '#FF6384', '#C9CBCF', '#4BC0C0', '#FF6384',
            '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF', '#FF9F40'
        ];
        
        const result = [];
        for (let i = 0; i < count; i++) {
            result.push(colors[i % colors.length]);
        }
        return result;
    },

    /**
     * Format date for display
     */
    formatDate: function(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString('en-US', { 
            month: 'short', 
            day: 'numeric' 
        });
    },

    /**
     * Format hour for display
     */
    formatHour: function(hour) {
        const period = hour >= 12 ? 'PM' : 'AM';
        const displayHour = hour === 0 ? 12 : hour > 12 ? hour - 12 : hour;
        return `${displayHour}:00 ${period}`;
    },

    /**
     * Update charts with new data (for filtering)
     */
    updateCharts: function(dailyData, departmentData, hourlyData) {
        // Update daily chart
        if (this.charts.daily) {
            this.charts.daily.data.labels = dailyData.map(item => this.formatDate(item.date));
            this.charts.daily.data.datasets[0].data = dailyData.map(item => parseInt(item.count));
            this.charts.daily.update();
        }

        // Update department chart
        if (this.charts.department) {
            this.charts.department.data.labels = departmentData.map(item => item.department || 'Unknown');
            this.charts.department.data.datasets[0].data = departmentData.map(item => parseInt(item.count));
            this.charts.department.data.datasets[0].backgroundColor = this.generateColors(departmentData.length);
            this.charts.department.update();
        }

        // Update hourly chart
        if (this.charts.hourly) {
            const hourCounts = new Array(24).fill(0);
            hourlyData.forEach(item => {
                const hour = parseInt(item.hour);
                if (hour >= 0 && hour < 24) {
                    hourCounts[hour] = parseInt(item.count);
                }
            });
            
            this.charts.hourly.data.datasets[0].data = hourCounts;
            this.charts.hourly.update();
        }
    },

    /**
     * Export chart as image
     */
    exportChart: function(chartType) {
        const chart = this.charts[chartType];
        if (!chart) return;

        const url = chart.toBase64Image();
        const link = document.createElement('a');
        link.download = `${chartType}_chart_${new Date().getTime()}.png`;
        link.href = url;
        link.click();
    },

    /**
     * Print chart
     */
    printChart: function(chartType) {
        const chart = this.charts[chartType];
        if (!chart) return;

        const printWindow = window.open('', '_blank');
        printWindow.document.write(`
            <html>
                <head>
                    <title>${chartType} Chart Report</title>
                    <style>
                        body { 
                            font-family: Arial, sans-serif; 
                            margin: 20px;
                            text-align: center;
                        }
                        .chart-container {
                            max-width: 800px;
                            margin: 0 auto;
                        }
                        .header {
                            margin-bottom: 20px;
                            padding-bottom: 10px;
                            border-bottom: 2px solid #333;
                        }
                        img {
                            max-width: 100%;
                            height: auto;
                        }
                        @media print {
                            body { margin: 0; }
                            .no-print { display: none; }
                        }
                    </style>
                </head>
                <body>
                    <div class="header">
                        <h1>Attendance System - ${chartType} Chart</h1>
                        <p>Generated on: ${new Date().toLocaleDateString()}</p>
                    </div>
                    <div class="chart-container">
                        <img src="${chart.toBase64Image()}" alt="${chartType} Chart">
                    </div>
                    <div class="no-print" style="margin-top: 20px;">
                        <button onclick="window.print()">Print Report</button>
                    </div>
                </body>
            </html>
        `);
        printWindow.document.close();
    },

    /**
     * Initialize filter functionality
     */
    initFilters: function() {
        const startDateInput = document.getElementById('start_date');
        const endDateInput = document.getElementById('end_date');
        const departmentSelect = document.getElementById('department');

        if (startDateInput) {
            startDateInput.addEventListener('change', this.validateDateRange.bind(this));
        }

        if (endDateInput) {
            endDateInput.addEventListener('change', this.validateDateRange.bind(this));
        }

        if (departmentSelect) {
            departmentSelect.addEventListener('change', this.applyFilters.bind(this));
        }
    },

    /**
     * Validate date range
     */
    validateDateRange: function() {
        const startDate = new Date(document.getElementById('start_date').value);
        const endDate = new Date(document.getElementById('end_date').value);

        if (startDate && endDate && startDate > endDate) {
            alert('Start date cannot be after end date.');
            return false;
        }

        const diffTime = Math.abs(endDate - startDate);
        const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));

        if (diffDays > 365) {
            alert('Date range cannot exceed 1 year.');
            return false;
        }

        return true;
    },

    /**
     * Apply filters and reload data
     */
    applyFilters: function() {
        if (!this.validateDateRange()) {
            return;
        }

        // Show loading indicator
        this.showLoading();

        // Get filter values
        const startDate = document.getElementById('start_date').value;
        const endDate = document.getElementById('end_date').value;
        const department = document.getElementById('department').value;

        // Fetch new data
        fetch(`ajax/get_report_data.php?start_date=${startDate}&end_date=${endDate}&department=${department}`)
            .then(response => response.json())
            .then(data => {
                this.updateCharts(data.daily, data.department, data.hourly);
                this.hideLoading();
            })
            .catch(error => {
                console.error('Error loading filtered data:', error);
                this.hideLoading();
                alert('Failed to load filtered data. Please try again.');
            });
    },

    /**
     * Show loading indicator
     */
    showLoading: function() {
        const loadingHtml = '<div id="chart-loading" style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); z-index: 1000;"><i class="fas fa-spinner fa-spin fa-2x"></i> Loading...</div>';
        
        // Add loading to all chart containers
        document.querySelectorAll('.card-body canvas').forEach(canvas => {
            const container = canvas.parentElement;
            container.style.position = 'relative';
            container.innerHTML += loadingHtml;
        });
    },

    /**
     * Hide loading indicator
     */
    hideLoading: function() {
        document.querySelectorAll('#chart-loading').forEach(loading => {
            loading.remove();
        });
    },

    /**
     * Initialize tooltips
     */
    initTooltips: function() {
        // Initialize Chart.js tooltips
        Object.values(this.charts).forEach(chart => {
            if (chart) {
                chart.options.plugins.tooltip = {
                    enabled: true,
                    mode: 'index',
                    intersect: false,
                    callbacks: {
                        title: function(context) {
                            return context[0].label;
                        },
                        label: function(context) {
                            return `${context.dataset.label}: ${context.parsed.y}`;
                        }
                    }
                };
                chart.update();
            }
        });
    },

    /**
     * Initialize all functionality
     */
    init: function() {
        this.initFilters();
        this.initTooltips();
        
        // Add window resize handler
        window.addEventListener('resize', function() {
            Object.values(this.charts).forEach(chart => {
                if (chart) {
                    chart.resize();
                }
            });
        }.bind(this));
    }
};

/**
 * Helper functions
 */
const ReportHelpers = {
    /**
     * Generate CSV data from chart
     */
    generateCSV: function(data, filename) {
        let csvContent = "data:text/csv;charset=utf-8,";
        
        data.forEach((row, index) => {
            if (index === 0) {
                csvContent += Object.keys(row).join(",") + "\n";
            }
            csvContent += Object.values(row).join(",") + "\n";
        });

        const encodedUri = encodeURI(csvContent);
        const link = document.createElement("a");
        link.setAttribute("href", encodedUri);
        link.setAttribute("download", filename);
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    },

    /**
     * Format numbers with commas
     */
    formatNumber: function(num) {
        return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
    },

    /**
     * Calculate percentage
     */
    calculatePercentage: function(value, total) {
        return total > 0 ? ((value / total) * 100).toFixed(1) : 0;
    },

    /**
     * Get time ago string
     */
    timeAgo: function(date) {
        const now = new Date();
        const past = new Date(date);
        const diffInSeconds = Math.floor((now - past) / 1000);

        if (diffInSeconds < 60) return 'Just now';
        if (diffInSeconds < 3600) return `${Math.floor(diffInSeconds / 60)} minutes ago`;
        if (diffInSeconds < 86400) return `${Math.floor(diffInSeconds / 3600)} hours ago`;
        return `${Math.floor(diffInSeconds / 86400)} days ago`;
    }
};

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    ReportsDashboard.init();
});

// Export for global access
window.ReportsDashboard = ReportsDashboard;
window.ReportHelpers = ReportHelpers;