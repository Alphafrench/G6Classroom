<?php 
$page_title = "Data Table Template - Bootstrap UI";
$breadcrumb = [
    ['title' => 'Home', 'url' => 'index.php'],
    ['title' => 'Templates', 'url' => 'index.php#templates'],
    ['title' => 'Data Table Template']
];
?>

<?php include '../includes/header.php'; ?>

<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-1">Data Table Template</h1>
                <p class="text-muted">Responsive data tables with sorting, filtering, and pagination</p>
            </div>
            <div class="d-flex gap-2">
                <button class="btn btn-outline-primary" data-bs-toggle="tooltip" title="Export CSV">
                    <i class="bi bi-download"></i> CSV
                </button>
                <button class="btn btn-outline-success" data-bs-toggle="tooltip" title="Add New">
                    <i class="bi bi-plus-circle"></i> Add New
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Search and Filter Controls -->
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="input-group">
            <span class="input-group-text"><i class="bi bi-search"></i></span>
            <input type="text" class="form-control table-search" placeholder="Search table...">
        </div>
    </div>
    <div class="col-md-3">
        <select class="form-select">
            <option value="">All Categories</option>
            <option value="electronics">Electronics</option>
            <option value="clothing">Clothing</option>
            <option value="books">Books</option>
            <option value="accessories">Accessories</option>
        </select>
    </div>
    <div class="col-md-3">
        <select class="form-select">
            <option value="">All Status</option>
            <option value="active">Active</option>
            <option value="pending">Pending</option>
            <option value="completed">Completed</option>
            <option value="cancelled">Cancelled</option>
        </select>
    </div>
    <div class="col-md-2">
        <button class="btn btn-outline-secondary w-100">
            <i class="bi bi-funnel me-1"></i>Filter
        </button>
    </div>
</div>

<!-- Data Table 1: Basic Table -->
<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">
            <i class="bi bi-table me-2"></i>Product Inventory
        </h5>
        <div class="d-flex gap-2">
            <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#addProductModal">
                <i class="bi bi-plus"></i> Add Product
            </button>
            <button class="btn btn-sm btn-outline-secondary" onclick="printTable('productsTable')">
                <i class="bi bi-printer"></i> Print
            </button>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover data-table" id="productsTable">
                <thead>
                    <tr>
                        <th data-sort>ID</th>
                        <th data-sort>Product Name</th>
                        <th data-sort>Category</th>
                        <th data-sort>Price</th>
                        <th data-sort>Stock</th>
                        <th data-sort>Status</th>
                        <th data-sort>Date Added</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>#001</td>
                        <td>
                            <div class="d-flex align-items-center">
                                <img src="https://via.placeholder.com/40x40" alt="Product" class="rounded me-2" width="40" height="40">
                                <span>Wireless Headphones</span>
                            </div>
                        </td>
                        <td><span class="badge bg-primary">Electronics</span></td>
                        <td>$299.99</td>
                        <td>45</td>
                        <td><span class="badge bg-success">In Stock</span></td>
                        <td>2024-01-15</td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <button class="btn btn-outline-primary" data-bs-toggle="tooltip" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button class="btn btn-outline-info" data-bs-toggle="tooltip" title="View">
                                    <i class="bi bi-eye"></i>
                                </button>
                                <button class="btn btn-outline-danger" data-bs-toggle="tooltip" title="Delete" 
                                        data-confirm="Are you sure you want to delete this item?">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>#002</td>
                        <td>
                            <div class="d-flex align-items-center">
                                <img src="https://via.placeholder.com/40x40" alt="Product" class="rounded me-2" width="40" height="40">
                                <span>Cotton T-Shirt</span>
                            </div>
                        </td>
                        <td><span class="badge bg-info">Clothing</span></td>
                        <td>$29.99</td>
                        <td>120</td>
                        <td><span class="badge bg-success">In Stock</span></td>
                        <td>2024-01-20</td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <button class="btn btn-outline-primary" data-bs-toggle="tooltip" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button class="btn btn-outline-info" data-bs-toggle="tooltip" title="View">
                                    <i class="bi bi-eye"></i>
                                </button>
                                <button class="btn btn-outline-danger" data-bs-toggle="tooltip" title="Delete" 
                                        data-confirm="Are you sure you want to delete this item?">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>#003</td>
                        <td>
                            <div class="d-flex align-items-center">
                                <img src="https://via.placeholder.com/40x40" alt="Product" class="rounded me-2" width="40" height="40">
                                <span>JavaScript Guide</span>
                            </div>
                        </td>
                        <td><span class="badge bg-warning">Books</span></td>
                        <td>$49.99</td>
                        <td>8</td>
                        <td><span class="badge bg-warning">Low Stock</span></td>
                        <td>2024-01-25</td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <button class="btn btn-outline-primary" data-bs-toggle="tooltip" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button class="btn btn-outline-info" data-bs-toggle="tooltip" title="View">
                                    <i class="bi bi-eye"></i>
                                </button>
                                <button class="btn btn-outline-danger" data-bs-toggle="tooltip" title="Delete" 
                                        data-confirm="Are you sure you want to delete this item?">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>#004</td>
                        <td>
                            <div class="d-flex align-items-center">
                                <img src="https://via.placeholder.com/40x40" alt="Product" class="rounded me-2" width="40" height="40">
                                <span>Leather Wallet</span>
                            </div>
                        </td>
                        <td><span class="badge bg-secondary">Accessories</span></td>
                        <td>$79.99</td>
                        <td>0</td>
                        <td><span class="badge bg-danger">Out of Stock</span></td>
                        <td>2024-02-01</td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <button class="btn btn-outline-primary" data-bs-toggle="tooltip" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button class="btn btn-outline-info" data-bs-toggle="tooltip" title="View">
                                    <i class="bi bi-eye"></i>
                                </button>
                                <button class="btn btn-outline-danger" data-bs-toggle="tooltip" title="Delete" 
                                        data-confirm="Are you sure you want to delete this item?">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>#005</td>
                        <td>
                            <div class="d-flex align-items-center">
                                <img src="https://via.placeholder.com/40x40" alt="Product" class="rounded me-2" width="40" height="40">
                                <span>Smart Watch</span>
                            </div>
                        </td>
                        <td><span class="badge bg-primary">Electronics</span></td>
                        <td>$199.99</td>
                        <td>25</td>
                        <td><span class="badge bg-success">In Stock</span></td>
                        <td>2024-02-05</td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <button class="btn btn-outline-primary" data-bs-toggle="tooltip" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button class="btn btn-outline-info" data-bs-toggle="tooltip" title="View">
                                    <i class="bi bi-eye"></i>
                                </button>
                                <button class="btn btn-outline-danger" data-bs-toggle="tooltip" title="Delete" 
                                        data-confirm="Are you sure you want to delete this item?">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        
        <!-- Table Pagination -->
        <nav aria-label="Table pagination">
            <ul class="pagination justify-content-center mt-3">
                <li class="page-item disabled">
                    <a class="page-link" href="#" tabindex="-1" aria-disabled="true">Previous</a>
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

<!-- Data Table 2: Orders Table -->
<div class="row g-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="bi bi-cart-check me-2"></i>Recent Orders
                </h5>
                <div class="d-flex gap-2">
                    <button class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-download"></i> Export
                    </button>
                    <button class="btn btn-sm btn-outline-success">
                        <i class="bi bi-arrow-clockwise"></i> Refresh
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover data-table" id="ordersTable">
                        <thead class="table-dark">
                            <tr>
                                <th data-sort>Order #</th>
                                <th data-sort>Customer</th>
                                <th data-sort>Date</th>
                                <th data-sort>Items</th>
                                <th data-sort>Total</th>
                                <th data-sort>Status</th>
                                <th data-sort>Payment</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><strong>#ORD-001</strong></td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <img src="https://via.placeholder.com/32x32" alt="Customer" class="rounded-circle me-2" width="32" height="32">
                                        <div>
                                            <div class="fw-semibold">John Smith</div>
                                            <small class="text-muted">john@example.com</small>
                                        </div>
                                    </div>
                                </td>
                                <td>Feb 15, 2024</td>
                                <td>3 items</td>
                                <td><strong>$329.97</strong></td>
                                <td><span class="badge bg-success">Completed</span></td>
                                <td><span class="badge bg-info">Paid</span></td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <button class="btn btn-outline-primary" data-bs-toggle="tooltip" title="View Details">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                        <button class="btn btn-outline-secondary" data-bs-toggle="tooltip" title="Print">
                                            <i class="bi bi-printer"></i>
                                        </button>
                                        <div class="btn-group">
                                            <button class="btn btn-outline-info dropdown-toggle" data-bs-toggle="dropdown">
                                                <i class="bi bi-three-dots"></i>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a class="dropdown-item" href="#">Edit Order</a></li>
                                                <li><a class="dropdown-item" href="#">Send Invoice</a></li>
                                                <li><a class="dropdown-item" href="#">Track Shipment</a></li>
                                                <li><hr class="dropdown-divider"></li>
                                                <li><a class="dropdown-item text-danger" href="#">Cancel Order</a></li>
                                            </ul>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td><strong>#ORD-002</strong></td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <img src="https://via.placeholder.com/32x32" alt="Customer" class="rounded-circle me-2" width="32" height="32">
                                        <div>
                                            <div class="fw-semibold">Sarah Johnson</div>
                                            <small class="text-muted">sarah@example.com</small>
                                        </div>
                                    </div>
                                </td>
                                <td>Feb 14, 2024</td>
                                <td>1 item</td>
                                <td><strong>$29.99</strong></td>
                                <td><span class="badge bg-warning">Processing</span></td>
                                <td><span class="badge bg-warning">Pending</span></td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <button class="btn btn-outline-primary" data-bs-toggle="tooltip" title="View Details">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                        <button class="btn btn-outline-secondary" data-bs-toggle="tooltip" title="Print">
                                            <i class="bi bi-printer"></i>
                                        </button>
                                        <div class="btn-group">
                                            <button class="btn btn-outline-info dropdown-toggle" data-bs-toggle="dropdown">
                                                <i class="bi bi-three-dots"></i>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a class="dropdown-item" href="#">Edit Order</a></li>
                                                <li><a class="dropdown-item" href="#">Send Invoice</a></li>
                                                <li><a class="dropdown-item" href="#">Track Shipment</a></li>
                                                <li><hr class="dropdown-divider"></li>
                                                <li><a class="dropdown-item text-danger" href="#">Cancel Order</a></li>
                                            </ul>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td><strong>#ORD-003</strong></td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <img src="https://via.placeholder.com/32x32" alt="Customer" class="rounded-circle me-2" width="32" height="32">
                                        <div>
                                            <div class="fw-semibold">Mike Davis</div>
                                            <small class="text-muted">mike@example.com</small>
                                        </div>
                                    </div>
                                </td>
                                <td>Feb 13, 2024</td>
                                <td>5 items</td>
                                <td><strong>$579.95</strong></td>
                                <td><span class="badge bg-info">Shipped</span></td>
                                <td><span class="badge bg-success">Paid</span></td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <button class="btn btn-outline-primary" data-bs-toggle="tooltip" title="View Details">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                        <button class="btn btn-outline-secondary" data-bs-toggle="tooltip" title="Print">
                                            <i class="bi bi-printer"></i>
                                        </button>
                                        <div class="btn-group">
                                            <button class="btn btn-outline-info dropdown-toggle" data-bs-toggle="dropdown">
                                                <i class="bi bi-three-dots"></i>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a class="dropdown-item" href="#">Edit Order</a></li>
                                                <li><a class="dropdown-item" href="#">Send Invoice</a></li>
                                                <li><a class="dropdown-item" href="#">Track Shipment</a></li>
                                                <li><hr class="dropdown-divider"></li>
                                                <li><a class="dropdown-item text-danger" href="#">Cancel Order</a></li>
                                            </ul>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td><strong>#ORD-004</strong></td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <img src="https://via.placeholder.com/32x32" alt="Customer" class="rounded-circle me-2" width="32" height="32">
                                        <div>
                                            <div class="fw-semibold">Emma Wilson</div>
                                            <small class="text-muted">emma@example.com</small>
                                        </div>
                                    </div>
                                </td>
                                <td>Feb 12, 2024</td>
                                <td>2 items</td>
                                <td><strong>$149.98</strong></td>
                                <td><span class="badge bg-danger">Cancelled</span></td>
                                <td><span class="badge bg-secondary">Refunded</span></td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <button class="btn btn-outline-primary" data-bs-toggle="tooltip" title="View Details">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                        <button class="btn btn-outline-secondary" data-bs-toggle="tooltip" title="Print">
                                            <i class="bi bi-printer"></i>
                                        </button>
                                        <div class="btn-group">
                                            <button class="btn btn-outline-info dropdown-toggle" data-bs-toggle="dropdown">
                                                <i class="bi bi-three-dots"></i>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a class="dropdown-item" href="#">Edit Order</a></li>
                                                <li><a class="dropdown-item" href="#">Send Invoice</a></li>
                                                <li><a class="dropdown-item" href="#">Track Shipment</a></li>
                                                <li><hr class="dropdown-divider"></li>
                                                <li><a class="dropdown-item text-danger" href="#">Cancel Order</a></li>
                                            </ul>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <!-- Table Stats -->
                <div class="row mt-3">
                    <div class="col-md-4">
                        <div class="card bg-primary text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h6 class="card-title">Total Orders</h6>
                                        <h3 class="mb-0">1,234</h3>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="bi bi-cart fs-1 opacity-75"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-success text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h6 class="card-title">Completed</h6>
                                        <h3 class="mb-0">892</h3>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="bi bi-check-circle fs-1 opacity-75"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-warning text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h6 class="card-title">Processing</h6>
                                        <h3 class="mb-0">342</h3>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="bi bi-clock fs-1 opacity-75"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Product Modal -->
<div class="modal fade" id="addProductModal" tabindex="-1" aria-labelledby="addProductModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addProductModalLabel">
                    <i class="bi bi-plus-circle me-2"></i>Add New Product
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form class="needs-validation" novalidate>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="productName" class="form-label">Product Name *</label>
                            <input type="text" class="form-control" id="productName" required>
                            <div class="invalid-feedback">Please provide a product name.</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="productCategory" class="form-label">Category *</label>
                            <select class="form-select" id="productCategory" required>
                                <option value="">Select category...</option>
                                <option value="electronics">Electronics</option>
                                <option value="clothing">Clothing</option>
                                <option value="books">Books</option>
                                <option value="accessories">Accessories</option>
                            </select>
                            <div class="invalid-feedback">Please select a category.</div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="productPrice" class="form-label">Price *</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" class="form-control" id="productPrice" min="0" step="0.01" required>
                            </div>
                            <div class="invalid-feedback">Please provide a valid price.</div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="productStock" class="form-label">Stock Quantity *</label>
                            <input type="number" class="form-control" id="productStock" min="0" required>
                            <div class="invalid-feedback">Please provide stock quantity.</div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="productSKU" class="form-label">SKU</label>
                            <input type="text" class="form-control" id="productSKU">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="productDescription" class="form-label">Description</label>
                        <textarea class="form-control" id="productDescription" rows="3"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary">
                    <i class="bi bi-save me-1"></i>Add Product
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Print table function
function printTable(tableId) {
    const table = document.getElementById(tableId);
    if (table) {
        const printWindow = window.open('', '_blank');
        printWindow.document.write(`
            <html>
                <head>
                    <title>Print Table</title>
                    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
                    <style>
                        .table th, .table td { padding: 8px; }
                        @media print {
                            .no-print { display: none !important; }
                        }
                    </style>
                </head>
                <body>
                    <div class="container-fluid">
                        <h2 class="mb-4">${tableId} - Data Export</h2>
                        ${table.outerHTML}
                    </div>
                </body>
            </html>
        `);
        printWindow.document.close();
        printWindow.print();
    }
}

// Initialize DataTables with custom options
document.addEventListener('DOMContentLoaded', function() {
    // Apply DataTables styling to all data tables
    const tables = document.querySelectorAll('.data-table');
    tables.forEach(table => {
        // Add bootstrap classes if not present
        if (!table.classList.contains('table')) {
            table.classList.add('table', 'table-striped', 'table-hover');
        }
        
        // Make table responsive
        const wrapper = table.closest('.table-responsive');
        if (!wrapper) {
            table.wrap('<div class="table-responsive"></div>');
        }
    });
    
    // Enhanced table search functionality
    const searchInputs = document.querySelectorAll('.table-search');
    searchInputs.forEach(input => {
        input.addEventListener('keyup', function() {
            const searchTerm = this.value.toLowerCase();
            const targetTable = this.closest('.card').querySelector('.data-table tbody');
            
            if (targetTable) {
                const rows = targetTable.querySelectorAll('tr');
                rows.forEach(row => {
                    const text = row.textContent.toLowerCase();
                    row.style.display = text.includes(searchTerm) ? '' : 'none';
                });
            }
        });
    });
    
    // Column sorting functionality
    const sortableHeaders = document.querySelectorAll('th[data-sort]');
    sortableHeaders.forEach(header => {
        header.style.cursor = 'pointer';
        header.innerHTML += ' <i class="bi bi-chevron-expand text-muted"></i>';
        
        header.addEventListener('click', function() {
            const table = this.closest('table');
            const tbody = table.querySelector('tbody');
            const rows = Array.from(tbody.querySelectorAll('tr'));
            const columnIndex = Array.from(this.parentNode.children).indexOf(this);
            const isAscending = !this.classList.contains('sort-asc');
            
            // Remove sort classes from all headers
            table.querySelectorAll('th[data-sort]').forEach(th => {
                th.classList.remove('sort-asc', 'sort-desc');
                th.innerHTML = th.innerHTML.replace(/<i class=".*"><\/i>/, '') + ' <i class="bi bi-chevron-expand text-muted"></i>';
            });
            
            // Add sort class to clicked header
            this.classList.add(isAscending ? 'sort-asc' : 'sort-desc');
            this.innerHTML = this.innerHTML.replace(/<i class=".*"><\/i>/, '') + 
                           ` <i class="bi bi-chevron-${isAscending ? 'up' : 'down'} text-primary"></i>`;
            
            // Sort rows
            rows.sort((a, b) => {
                const aVal = a.children[columnIndex].textContent.trim();
                const bVal = b.children[columnIndex].textContent.trim();
                
                if (!isNaN(aVal) && !isNaN(bVal)) {
                    return isAscending ? parseFloat(bVal) - parseFloat(aVal) : parseFloat(aVal) - parseFloat(bVal);
                } else {
                    return isAscending ? bVal.localeCompare(aVal) : aVal.localeCompare(bVal);
                }
            });
            
            // Re-append sorted rows
            rows.forEach(row => tbody.appendChild(row));
        });
    });
});
</script>

<?php include '../includes/footer.php'; ?>