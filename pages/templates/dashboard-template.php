<?php 
$page_title = "Dashboard Template - Bootstrap UI";
$breadcrumb = [
    ['title' => 'Home', 'url' => 'index.php'],
    ['title' => 'Templates', 'url' => 'index.php#templates'],
    ['title' => 'Dashboard Template']
];
?>

<?php include '../includes/header.php'; ?>

<!-- Dashboard Header -->
<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="h3 mb-1">Dashboard</h1>
                <p class="text-muted">Welcome back! Here's what's happening with your business today.</p>
            </div>
            <div class="d-flex gap-2">
                <button class="btn btn-outline-primary">
                    <i class="bi bi-download me-1"></i>Export Report
                </button>
                <button class="btn btn-primary">
                    <i class="bi bi-plus-circle me-1"></i>New Order
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Stats Cards Row -->
<div class="row g-4 mb-4">
    <!-- Total Revenue -->
    <div class="col-xl-3 col-md-6">
        <div class="card bg-primary text-white h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="d-flex align-items-center mb-2">
                            <i class="bi bi-currency-dollar fs-2 me-2"></i>
                            <div>
                                <h6 class="card-title mb-0 text-white-50">Total Revenue</h6>
                            </div>
                        </div>
                        <h2 class="mb-0">$45,231</h2>
                        <div class="d-flex align-items-center mt-2">
                            <i class="bi bi-arrow-up text-success me-1"></i>
                            <small class="text-success">+20.1%</small>
                            <small class="text-white-50 ms-2">from last month</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer bg-transparent border-top border-white border-opacity-25">
                <a href="#" class="text-white text-decoration-none small">
                    View details <i class="bi bi-arrow-right ms-1"></i>
                </a>
            </div>
        </div>
    </div>
    
    <!-- New Orders -->
    <div class="col-xl-3 col-md-6">
        <div class="card bg-success text-white h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="d-flex align-items-center mb-2">
                            <i class="bi bi-cart-plus fs-2 me-2"></i>
                            <div>
                                <h6 class="card-title mb-0 text-white-50">New Orders</h6>
                            </div>
                        </div>
                        <h2 class="mb-0">1,234</h2>
                        <div class="d-flex align-items-center mt-2">
                            <i class="bi bi-arrow-up text-light me-1"></i>
                            <small class="text-light">+180.1%</small>
                            <small class="text-white-50 ms-2">from last month</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer bg-transparent border-top border-white border-opacity-25">
                <a href="#" class="text-white text-decoration-none small">
                    View orders <i class="bi bi-arrow-right ms-1"></i>
                </a>
            </div>
        </div>
    </div>
    
    <!-- Total Customers -->
    <div class="col-xl-3 col-md-6">
        <div class="card bg-info text-white h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="d-flex align-items-center mb-2">
                            <i class="bi bi-people fs-2 me-2"></i>
                            <div>
                                <h6 class="card-title mb-0 text-white-50">Total Customers</h6>
                            </div>
                        </div>
                        <h2 class="mb-0">45,231</h2>
                        <div class="d-flex align-items-center mt-2">
                            <i class="bi bi-arrow-up text-light me-1"></i>
                            <small class="text-light">+19%</small>
                            <small class="text-white-50 ms-2">from last month</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer bg-transparent border-top border-white border-opacity-25">
                <a href="#" class="text-white text-decoration-none small">
                    View customers <i class="bi bi-arrow-right ms-1"></i>
                </a>
            </div>
        </div>
    </div>
    
    <!-- Growth Rate -->
    <div class="col-xl-3 col-md-6">
        <div class="card bg-warning text-white h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="d-flex align-items-center mb-2">
                            <i class="bi bi-graph-up fs-2 me-2"></i>
                            <div>
                                <h6 class="card-title mb-0 text-white-50">Growth Rate</h6>
                            </div>
                        </div>
                        <h2 class="mb-0">8.2%</h2>
                        <div class="d-flex align-items-center mt-2">
                            <i class="bi bi-arrow-down text-light me-1"></i>
                            <small class="text-light">+4.3%</small>
                            <small class="text-white-50 ms-2">from last month</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer bg-transparent border-top border-white border-opacity-25">
                <a href="#" class="text-white text-decoration-none small">
                    View analytics <i class="bi bi-arrow-right ms-1"></i>
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Charts and Analytics Row -->
<div class="row g-4 mb-4">
    <!-- Revenue Chart -->
    <div class="col-lg-8">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="bi bi-bar-chart me-2"></i>Revenue Overview
                </h5>
                <div class="btn-group btn-group-sm">
                    <button type="button" class="btn btn-outline-secondary active">7D</button>
                    <button type="button" class="btn btn-outline-secondary">30D</button>
                    <button type="button" class="btn btn-outline-secondary">90D</button>
                    <button type="button" class="btn btn-outline-secondary">1Y</button>
                </div>
            </div>
            <div class="card-body">
                <div class="chart-container" style="position: relative; height: 300px;">
                    <div class="bg-light d-flex align-items-center justify-content-center h-100 rounded">
                        <div class="text-center">
                            <i class="bi bi-bar-chart fs-1 text-muted mb-3"></i>
                            <p class="text-muted mb-0">Revenue Chart Placeholder</p>
                            <small class="text-muted">Integrate with Chart.js or similar library</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Recent Activity -->
    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-clock-history me-2"></i>Recent Activity
                </h5>
            </div>
            <div class="card-body">
                <div class="activity-timeline">
                    <div class="activity-item d-flex mb-3">
                        <div class="activity-icon bg-success text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                            <i class="bi bi-check-circle"></i>
                        </div>
                        <div class="activity-content">
                            <h6 class="mb-1">Order #1234 completed</h6>
                            <small class="text-muted">2 minutes ago</small>
                        </div>
                    </div>
                    
                    <div class="activity-item d-flex mb-3">
                        <div class="activity-icon bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                            <i class="bi bi-person-plus"></i>
                        </div>
                        <div class="activity-content">
                            <h6 class="mb-1">New customer registered</h6>
                            <small class="text-muted">5 minutes ago</small>
                        </div>
                    </div>
                    
                    <div class="activity-item d-flex mb-3">
                        <div class="activity-icon bg-warning text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                            <i class="bi bi-exclamation-triangle"></i>
                        </div>
                        <div class="activity-content">
                            <h6 class="mb-1">Payment failed for order #1233</h6>
                            <small class="text-muted">15 minutes ago</small>
                        </div>
                    </div>
                    
                    <div class="activity-item d-flex mb-3">
                        <div class="activity-icon bg-info text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                            <i class="bi bi-box"></i>
                        </div>
                        <div class="activity-content">
                            <h6 class="mb-1">Product "Wireless Headphones" low stock</h6>
                            <small class="text-muted">1 hour ago</small>
                        </div>
                    </div>
                    
                    <div class="activity-item d-flex mb-3">
                        <div class="activity-icon bg-secondary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                            <i class="bi bi-graph-up"></i>
                        </div>
                        <div class="activity-content">
                            <h6 class="mb-1">Monthly report generated</h6>
                            <small class="text-muted">2 hours ago</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <a href="#" class="text-decoration-none small">View all activity</a>
            </div>
        </div>
    </div>
</div>

<!-- Recent Orders and Quick Actions -->
<div class="row g-4 mb-4">
    <!-- Recent Orders -->
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="bi bi-cart-check me-2"></i>Recent Orders
                </h5>
                <a href="#" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Order ID</th>
                                <th>Customer</th>
                                <th>Date</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><strong>#1234</strong></td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <img src="https://via.placeholder.com/32" alt="Customer" class="rounded-circle me-2" width="32" height="32">
                                        <span>John Doe</span>
                                    </div>
                                </td>
                                <td>Feb 15, 2024</td>
                                <td><strong>$329.99</strong></td>
                                <td><span class="badge bg-success">Completed</span></td>
                                <td>
                                    <button class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </td>
                            </tr>
                            <tr>
                                <td><strong>#1235</strong></td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <img src="https://via.placeholder.com/32" alt="Customer" class="rounded-circle me-2" width="32" height="32">
                                        <span>Jane Smith</span>
                                    </div>
                                </td>
                                <td>Feb 15, 2024</td>
                                <td><strong>$29.99</strong></td>
                                <td><span class="badge bg-warning">Processing</span></td>
                                <td>
                                    <button class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </td>
                            </tr>
                            <tr>
                                <td><strong>#1236</strong></td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <img src="https://via.placeholder.com/32" alt="Customer" class="rounded-circle me-2" width="32" height="32">
                                        <span>Mike Johnson</span>
                                    </div>
                                </td>
                                <td>Feb 14, 2024</td>
                                <td><strong>$579.95</strong></td>
                                <td><span class="badge bg-info">Shipped</span></td>
                                <td>
                                    <button class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </td>
                            </tr>
                            <tr>
                                <td><strong>#1237</strong></td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <img src="https://via.placeholder.com/32" alt="Customer" class="rounded-circle me-2" width="32" height="32">
                                        <span>Sarah Wilson</span>
                                    </div>
                                </td>
                                <td>Feb 14, 2024</td>
                                <td><strong>$149.98</strong></td>
                                <td><span class="badge bg-danger">Cancelled</span></td>
                                <td>
                                    <button class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Quick Actions -->
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-lightning me-2"></i>Quick Actions
                </h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-3">
                    <button class="btn btn-outline-primary">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-plus-circle fs-4 me-3"></i>
                            <div class="text-start">
                                <div class="fw-bold">Create Order</div>
                                <small class="text-muted">Add new customer order</small>
                            </div>
                        </div>
                    </button>
                    
                    <button class="btn btn-outline-success">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-person-plus fs-4 me-3"></i>
                            <div class="text-start">
                                <div class="fw-bold">Add Customer</div>
                                <small class="text-muted">Register new customer</small>
                            </div>
                        </div>
                    </button>
                    
                    <button class="btn btn-outline-info">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-box fs-4 me-3"></i>
                            <div class="text-start">
                                <div class="fw-bold">Manage Inventory</div>
                                <small class="text-muted">Update product stock</small>
                            </div>
                        </div>
                    </button>
                    
                    <button class="btn btn-outline-warning">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-graph-up fs-4 me-3"></i>
                            <div class="text-start">
                                <div class="fw-bold">View Reports</div>
                                <small class="text-muted">Generate sales reports</small>
                            </div>
                        </div>
                    </button>
                    
                    <button class="btn btn-outline-secondary">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-gear fs-4 me-3"></i>
                            <div class="text-start">
                                <div class="fw-bold">Settings</div>
                                <small class="text-muted">Configure system</small>
                            </div>
                        </div>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Top Products and System Status -->
<div class="row g-4">
    <!-- Top Products -->
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="bi bi-trophy me-2"></i>Top Products
                </h5>
                <div class="dropdown">
                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                        This Month
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="#">This Week</a></li>
                        <li><a class="dropdown-item" href="#">This Month</a></li>
                        <li><a class="dropdown-item" href="#">This Year</a></li>
                    </ul>
                </div>
            </div>
            <div class="card-body">
                <div class="product-item d-flex align-items-center mb-3 pb-3 border-bottom">
                    <img src="https://via.placeholder.com/50" alt="Product" class="rounded me-3" width="50" height="50">
                    <div class="flex-grow-1">
                        <h6 class="mb-1">Wireless Headphones</h6>
                        <small class="text-muted">123 sales</small>
                    </div>
                    <div class="text-end">
                        <div class="fw-bold">$299.99</div>
                        <small class="text-success">+15%</small>
                    </div>
                </div>
                
                <div class="product-item d-flex align-items-center mb-3 pb-3 border-bottom">
                    <img src="https://via.placeholder.com/50" alt="Product" class="rounded me-3" width="50" height="50">
                    <div class="flex-grow-1">
                        <h6 class="mb-1">Smart Watch</h6>
                        <small class="text-muted">98 sales</small>
                    </div>
                    <div class="text-end">
                        <div class="fw-bold">$199.99</div>
                        <small class="text-success">+8%</small>
                    </div>
                </div>
                
                <div class="product-item d-flex align-items-center mb-3 pb-3 border-bottom">
                    <img src="https://via.placeholder.com/50" alt="Product" class="rounded me-3" width="50" height="50">
                    <div class="flex-grow-1">
                        <h6 class="mb-1">Cotton T-Shirt</h6>
                        <small class="text-muted">87 sales</small>
                    </div>
                    <div class="text-end">
                        <div class="fw-bold">$29.99</div>
                        <small class="text-danger">-5%</small>
                    </div>
                </div>
                
                <div class="product-item d-flex align-items-center">
                    <img src="https://via.placeholder.com/50" alt="Product" class="rounded me-3" width="50" height="50">
                    <div class="flex-grow-1">
                        <h6 class="mb-1">Leather Wallet</h6>
                        <small class="text-muted">65 sales</small>
                    </div>
                    <div class="text-end">
                        <div class="fw-bold">$79.99</div>
                        <small class="text-success">+12%</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- System Status -->
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-speedometer2 me-2"></i>System Status
                </h5>
            </div>
            <div class="card-body">
                <div class="status-item mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="fw-semibold">Server Uptime</span>
                        <span class="text-success">99.9%</span>
                    </div>
                    <div class="progress" style="height: 8px;">
                        <div class="progress-bar bg-success" style="width: 99.9%"></div>
                    </div>
                </div>
                
                <div class="status-item mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="fw-semibold">CPU Usage</span>
                        <span class="text-warning">68%</span>
                    </div>
                    <div class="progress" style="height: 8px;">
                        <div class="progress-bar bg-warning" style="width: 68%"></div>
                    </div>
                </div>
                
                <div class="status-item mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="fw-semibold">Memory Usage</span>
                        <span class="text-info">45%</span>
                    </div>
                    <div class="progress" style="height: 8px;">
                        <div class="progress-bar bg-info" style="width: 45%"></div>
                    </div>
                </div>
                
                <div class="status-item mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="fw-semibold">Disk Usage</span>
                        <span class="text-danger">85%</span>
                    </div>
                    <div class="progress" style="height: 8px;">
                        <div class="progress-bar bg-danger" style="width: 85%"></div>
                    </div>
                </div>
                
                <div class="status-item">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="fw-semibold">Network Traffic</span>
                        <span class="text-success">Normal</span>
                    </div>
                    <div class="progress" style="height: 8px;">
                        <div class="progress-bar bg-success" style="width: 25%"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Initialize dashboard features
document.addEventListener('DOMContentLoaded', function() {
    // Auto-refresh data every 30 seconds
    setInterval(function() {
        // Simulate data refresh
        console.log('Dashboard data refreshed');
    }, 30000);
    
    // Chart timeframe buttons
    const chartButtons = document.querySelectorAll('.btn-group .btn');
    chartButtons.forEach(button => {
        button.addEventListener('click', function() {
            chartButtons.forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');
            
            // Here you would typically fetch new data based on timeframe
            console.log('Chart timeframe changed to:', this.textContent);
        });
    });
    
    // Quick action buttons
    const quickActionButtons = document.querySelectorAll('.d-grid .btn');
    quickActionButtons.forEach(button => {
        button.addEventListener('click', function() {
            const action = this.querySelector('.fw-bold').textContent;
            if (window.bootstrapUI) {
                window.bootstrapUI.showAlert('info', `${action} clicked!`);
            }
        });
    });
    
    // Simulate real-time updates
    setInterval(function() {
        const revenueCard = document.querySelector('.card.bg-primary');
        if (revenueCard) {
            // Simulate small revenue increase
            const currentAmount = revenueCard.querySelector('h2').textContent;
            const newAmount = '$' + (parseFloat(currentAmount.replace('$', '').replace(',', '')) + Math.random() * 100).toFixed(0).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
            revenueCard.querySelector('h2').textContent = newAmount;
        }
    }, 10000);
});

// Real-time notification system
function addNotification(type, message) {
    const notification = document.createElement('div');
    notification.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
    notification.style.cssText = 'top: 100px; right: 20px; z-index: 9999; min-width: 300px;';
    notification.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.body.appendChild(notification);
    
    // Auto-remove after 5 seconds
    setTimeout(() => {
        if (notification.parentNode) {
            notification.remove();
        }
    }, 5000);
}

// Simulate real-time notifications
setInterval(function() {
    if (Math.random() > 0.7) { // 30% chance every interval
        const notifications = [
            { type: 'success', message: 'New order received!' },
            { type: 'info', message: 'System backup completed.' },
            { type: 'warning', message: 'Low stock alert for Product X.' }
        ];
        
        const randomNotification = notifications[Math.floor(Math.random() * notifications.length)];
        addNotification(randomNotification.type, randomNotification.message);
    }
}, 15000);
</script>

<style>
.activity-timeline {
    position: relative;
}

.activity-timeline::before {
    content: '';
    position: absolute;
    left: 20px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #e9ecef;
}

.activity-icon {
    position: relative;
    z-index: 1;
}

.chart-container {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
}

.product-item:last-child {
    border-bottom: none !important;
    margin-bottom: 0 !important;
    padding-bottom: 0 !important;
}

.status-item:last-child {
    margin-bottom: 0 !important;
}

.card {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.card:hover {
    transform: translateY(-2px);
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
}

.progress {
    background-color: #e9ecef;
}

@media (max-width: 768px) {
    .card-body .d-flex.align-items-center {
        flex-direction: column;
        text-align: center;
    }
    
    .activity-icon {
        margin-bottom: 0.5rem;
    }
}
</style>

<?php include '../includes/footer.php'; ?>