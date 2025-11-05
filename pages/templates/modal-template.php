<?php 
$page_title = "Modal Template - Bootstrap UI";
$breadcrumb = [
    ['title' => 'Home', 'url' => 'index.php'],
    ['title' => 'Templates', 'url' => 'index.php#templates'],
    ['title' => 'Modal Template']
];
?>

<?php include '../includes/header.php'; ?>

<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-1">Modal Template</h1>
                <p class="text-muted">Bootstrap 5 modal dialogs with various use cases</p>
            </div>
        </div>
    </div>
</div>

<!-- Modal Examples Grid -->
<div class="row g-4">
    <!-- Basic Modal Examples -->
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-window me-2"></i>Basic Modals
                </h5>
            </div>
            <div class="card-body">
                <p class="text-muted mb-3">Various modal sizes and configurations</p>
                
                <div class="d-grid gap-2">
                    <!-- Small Modal -->
                    <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#smallModal">
                        <i class="bi bi-arrows-angle-expand me-1"></i>Small Modal
                    </button>
                    
                    <!-- Medium Modal -->
                    <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#mediumModal">
                        <i class="bi bi-arrows-angle-expand me-1"></i>Medium Modal
                    </button>
                    
                    <!-- Large Modal -->
                    <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#largeModal">
                        <i class="bi bi-arrows-angle-expand me-1"></i>Large Modal
                    </button>
                    
                    <!-- Extra Large Modal -->
                    <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#extraLargeModal">
                        <i class="bi bi-arrows-angle-expand me-1"></i>Extra Large Modal
                    </button>
                    
                    <!-- Fullscreen Modal -->
                    <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#fullscreenModal">
                        <i bi bi-arrows-angle-expand me-1></i>Fullscreen Modal
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Functional Modals -->
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-gear me-2"></i>Functional Modals
                </h5>
            </div>
            <div class="card-body">
                <p class="text-muted mb-3">Modals for specific use cases</p>
                
                <div class="d-grid gap-2">
                    <!-- Confirmation Modal -->
                    <button class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#confirmModal">
                        <i class="bi bi-question-circle me-1"></i>Confirmation Modal
                    </button>
                    
                    <!-- Form Modal -->
                    <button class="btn btn-outline-success" data-bs-toggle="modal" data-bs-target="#formModal">
                        <i class="bi bi-file-earmark-text me-1"></i>Form Modal
                    </button>
                    
                    <!-- Image Gallery Modal -->
                    <button class="btn btn-outline-info" data-bs-toggle="modal" data-bs-target="#galleryModal">
                        <i class="bi bi-images me-1"></i>Image Gallery
                    </button>
                    
                    <!-- Video Modal -->
                    <button class="btn btn-outline-warning" data-bs-toggle="modal" data-bs-target="#videoModal">
                        <i class="bi bi-play-circle me-1"></i>Video Modal
                    </button>
                    
                    <!-- Loading Modal -->
                    <button class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#loadingModal">
                        <i class="bi bi-hourglass-split me-1"></i>Loading Modal
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Advanced Modals -->
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-stack me-2"></i>Advanced Modal Features
                </h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <h6 class="text-primary">Nested Modals</h6>
                        <p class="text-muted small">Open a modal from within another modal</p>
                        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#nestedModal">
                            <i class="bi bi-diagram-3 me-1"></i>Open Nested
                        </button>
                    </div>
                    <div class="col-md-4">
                        <h6 class="text-primary">Modal with Tabs</h6>
                        <p class="text-muted small">Content organized in tabs within modal</p>
                        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#tabsModal">
                            <i class="bi bi-layout-three-columns me-1"></i>Tabbed Modal
                        </button>
                    </div>
                    <div class="col-md-4">
                        <h6 class="text-primary">Stackable Modals</h6>
                        <p class="text-muted small">Multiple modals that stack on top</p>
                        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#stackModal">
                            <i class="bi bi-layers me-1"></i>Stack Modal
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Basic Modal Templates -->
<!-- Small Modal -->
<div class="modal fade" id="smallModal" tabindex="-1" aria-labelledby="smallModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="smallModalLabel">Small Modal</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>This is a small modal dialog. Perfect for simple notifications or quick actions.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary">Save changes</button>
            </div>
        </div>
    </div>
</div>

<!-- Medium Modal -->
<div class="modal fade" id="mediumModal" tabindex="-1" aria-labelledby="mediumModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="mediumModalLabel">Medium Modal</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>This is a medium-sized modal dialog. It's the default size and works well for most use cases.</p>
                <p>You can include more content here, such as forms, images, or detailed information.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary">Confirm</button>
            </div>
        </div>
    </div>
</div>

<!-- Large Modal -->
<div class="modal fade" id="largeModal" tabindex="-1" aria-labelledby="largeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="largeModalLabel">Large Modal</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <h6>Large Modal Content</h6>
                <p>This is a large modal dialog that provides more space for content.</p>
                <div class="row">
                    <div class="col-md-6">
                        <h6>Left Column</h6>
                        <p>Additional content can be organized in columns within the modal body.</p>
                        <ul>
                            <li>Feature one</li>
                            <li>Feature two</li>
                            <li>Feature three</li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <h6>Right Column</h6>
                        <p>More content here to demonstrate the increased width of the large modal.</p>
                        <div class="progress">
                            <div class="progress-bar" role="progressbar" style="width: 75%">75%</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary">Continue</button>
            </div>
        </div>
    </div>
</div>

<!-- Extra Large Modal -->
<div class="modal fade" id="extraLargeModal" tabindex="-1" aria-labelledby="extraLargeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="extraLargeModalLabel">Extra Large Modal</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-8">
                        <h6>Main Content Area</h6>
                        <p>The extra large modal provides maximum space for complex layouts, data tables, or detailed forms.</p>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Item</th>
                                        <th>Quantity</th>
                                        <th>Price</th>
                                        <th>Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>Product A</td>
                                        <td>2</td>
                                        <td>$29.99</td>
                                        <td>$59.98</td>
                                    </tr>
                                    <tr>
                                        <td>Product B</td>
                                        <td>1</td>
                                        <td>$49.99</td>
                                        <td>$49.99</td>
                                    </tr>
                                    <tr>
                                        <td>Product C</td>
                                        <td>3</td>
                                        <td>$19.99</td>
                                        <td>$59.97</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <h6>Sidebar</h6>
                        <div class="card">
                            <div class="card-body">
                                <h6>Summary</h6>
                                <p>Subtotal: $169.94</p>
                                <p>Tax: $13.60</p>
                                <hr>
                                <p><strong>Total: $183.54</strong></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-success">Process Order</button>
            </div>
        </div>
    </div>
</div>

<!-- Fullscreen Modal -->
<div class="modal fade" id="fullscreenModal" tabindex="-1" aria-labelledby="fullscreenModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="fullscreenModalLabel">Fullscreen Modal</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="container-fluid">
                    <h4>Fullscreen Modal Content</h4>
                    <p>This modal takes up the entire viewport, providing maximum space for complex interfaces.</p>
                    <div class="row">
                        <div class="col-md-3">
                            <h6>Navigation</h6>
                            <ul class="nav flex-column">
                                <li class="nav-item"><a class="nav-link" href="#">Dashboard</a></li>
                                <li class="nav-item"><a class="nav-link" href="#">Analytics</a></li>
                                <li class="nav-item"><a class="nav-link" href="#">Reports</a></li>
                                <li class="nav-item"><a class="nav-link" href="#">Settings</a></li>
                            </ul>
                        </div>
                        <div class="col-md-9">
                            <h6>Main Content</h6>
                            <p>The fullscreen modal is perfect for complex applications, detailed forms, or when you need maximum screen real estate.</p>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="card">
                                        <div class="card-body">
                                            <h6>Chart 1</h6>
                                            <div class="bg-light p-4 text-center">
                                                <i class="bi bi-bar-chart fs-1 text-muted"></i>
                                                <p class="text-muted">Chart placeholder</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card">
                                        <div class="card-body">
                                            <h6>Chart 2</h6>
                                            <div class="bg-light p-4 text-center">
                                                <i class="bi bi-pie-chart fs-1 text-muted"></i>
                                                <p class="text-muted">Chart placeholder</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary">Save Changes</button>
            </div>
        </div>
    </div>
</div>

<!-- Confirmation Modal -->
<div class="modal fade" id="confirmModal" tabindex="-1" aria-labelledby="confirmModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0">
                <div class="d-flex align-items-center">
                    <div class="bg-warning bg-opacity-10 rounded-circle p-3 me-3">
                        <i class="bi bi-exclamation-triangle-fill text-warning fs-4"></i>
                    </div>
                    <div>
                        <h5 class="modal-title mb-0" id="confirmModalLabel">Confirm Action</h5>
                        <small class="text-muted">This action cannot be undone</small>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this item? This action cannot be undone and all associated data will be permanently removed.</p>
                <div class="alert alert-warning">
                    <i class="bi bi-info-circle me-2"></i>
                    <strong>Warning:</strong> This will permanently delete the selected item and all related data.
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x me-1"></i>Cancel
                </button>
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal">
                    <i class="bi bi-trash me-1"></i>Delete Permanently
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Form Modal -->
<div class="modal fade" id="formModal" tabindex="-1" aria-labelledby="formModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="formModalLabel">
                    <i class="bi bi-person-plus me-2"></i>Add New User
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form class="needs-validation" novalidate>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="userFirstName" class="form-label">First Name *</label>
                            <input type="text" class="form-control" id="userFirstName" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="userLastName" class="form-label">Last Name *</label>
                            <input type="text" class="form-control" id="userLastName" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="userEmail" class="form-label">Email Address *</label>
                        <input type="email" class="form-control" id="userEmail" required>
                    </div>
                    <div class="mb-3">
                        <label for="userRole" class="form-label">Role *</label>
                        <select class="form-select" id="userRole" required>
                            <option value="">Select role...</option>
                            <option value="admin">Administrator</option>
                            <option value="editor">Editor</option>
                            <option value="viewer">Viewer</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="userBio" class="form-label">Bio</label>
                        <textarea class="form-control" id="userBio" rows="3" placeholder="Brief description..."></textarea>
                    </div>
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="userActive" checked>
                        <label class="form-check-label" for="userActive">Account is active</label>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success">
                    <i class="bi bi-person-plus me-1"></i>Create User
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Image Gallery Modal -->
<div class="modal fade" id="galleryModal" tabindex="-1" aria-labelledby="galleryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="galleryModalLabel">
                    <i class="bi bi-images me-2"></i>Image Gallery
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <img src="https://via.placeholder.com/300x200" alt="Gallery Image 1" 
                             class="img-fluid rounded cursor-pointer" onclick="openImageModal(this.src)">
                    </div>
                    <div class="col-md-4">
                        <img src="https://via.placeholder.com/300x200" alt="Gallery Image 2" 
                             class="img-fluid rounded cursor-pointer" onclick="openImageModal(this.src)">
                    </div>
                    <div class="col-md-4">
                        <img src="https://via.placeholder.com/300x200" alt="Gallery Image 3" 
                             class="img-fluid rounded cursor-pointer" onclick="openImageModal(this.src)">
                    </div>
                    <div class="col-md-4">
                        <img src="https://via.placeholder.com/300x200" alt="Gallery Image 4" 
                             class="img-fluid rounded cursor-pointer" onclick="openImageModal(this.src)">
                    </div>
                    <div class="col-md-4">
                        <img src="https://via.placeholder.com/300x200" alt="Gallery Image 5" 
                             class="img-fluid rounded cursor-pointer" onclick="openImageModal(this.src)">
                    </div>
                    <div class="col-md-4">
                        <img src="https://via.placeholder.com/300x200" alt="Gallery Image 6" 
                             class="img-fluid rounded cursor-pointer" onclick="openImageModal(this.src)">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Video Modal -->
<div class="modal fade" id="videoModal" tabindex="-1" aria-labelledby="videoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="videoModalLabel">
                    <i class="bi bi-play-circle me-2"></i>Product Demo Video
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="ratio ratio-16x9">
                    <div class="bg-dark d-flex align-items-center justify-content-center">
                        <div class="text-center text-white">
                            <i class="bi bi-play-circle fs-1 mb-3"></i>
                            <h6>Video Player Placeholder</h6>
                            <p class="text-muted">Replace with actual video embed or HTML5 video element</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary">
                    <i class="bi bi-download me-1"></i>Download
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Loading Modal -->
<div class="modal fade" id="loadingModal" tabindex="-1" aria-labelledby="loadingModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body text-center py-5">
                <div class="spinner-border text-primary mb-3" role="status" style="width: 3rem; height: 3rem;">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <h5 class="modal-title mb-3" id="loadingModalLabel">Processing...</h5>
                <p class="text-muted">Please wait while we process your request.</p>
                <div class="progress mt-3">
                    <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" 
                         style="width: 100%"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Nested Modal -->
<div class="modal fade" id="nestedModal" tabindex="-1" aria-labelledby="nestedModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="nestedModalLabel">Nested Modal</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>This is a modal opened from within another modal.</p>
                <button class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#deepNestedModal">
                    Open Another Nested Modal
                </button>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Deep Nested Modal -->
<div class="modal fade" id="deepNestedModal" tabindex="-1" aria-labelledby="deepNestedModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deepNestedModalLabel">Deep Nested Modal</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>This is a modal opened from within a nested modal.</p>
                <div class="alert alert-info">
                    <i class="bi bi-info-circle me-2"></i>
                    You can create complex modal hierarchies with Bootstrap 5.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Tabs Modal -->
<div class="modal fade" id="tabsModal" tabindex="-1" aria-labelledby="tabsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="tabsModalLabel">
                    <i class="bi bi-layout-three-columns me-2"></i>Modal with Tabs
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <ul class="nav nav-tabs" id="modalTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="profile-tab" data-bs-toggle="tab" 
                                data-bs-target="#profile" type="button" role="tab">
                            <i class="bi bi-person me-1"></i>Profile
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="settings-tab" data-bs-toggle="tab" 
                                data-bs-target="#settings" type="button" role="tab">
                            <i class="bi bi-gear me-1"></i>Settings
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="notifications-tab" data-bs-toggle="tab" 
                                data-bs-target="#notifications" type="button" role="tab">
                            <i class="bi bi-bell me-1"></i>Notifications
                        </button>
                    </li>
                </ul>
                <div class="tab-content mt-3" id="modalTabsContent">
                    <div class="tab-pane fade show active" id="profile" role="tabpanel">
                        <h6>Profile Information</h6>
                        <form>
                            <div class="mb-3">
                                <label class="form-label">Name</label>
                                <input type="text" class="form-control" value="John Doe">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control" value="john@example.com">
                            </div>
                        </form>
                    </div>
                    <div class="tab-pane fade" id="settings" role="tabpanel">
                        <h6>Settings</h6>
                        <div class="form-check form-switch mb-2">
                            <input class="form-check-input" type="checkbox" id="setting1" checked>
                            <label class="form-check-label" for="setting1">Enable notifications</label>
                        </div>
                        <div class="form-check form-switch mb-2">
                            <input class="form-check-input" type="checkbox" id="setting2">
                            <label class="form-check-label" for="setting2">Auto-save</label>
                        </div>
                        <div class="form-check form-switch mb-2">
                            <input class="form-check-input" type="checkbox" id="setting3" checked>
                            <label class="form-check-label" for="setting3">Public profile</label>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="notifications" role="tabpanel">
                        <h6>Notification Preferences</h6>
                        <div class="list-group">
                            <div class="list-group-item">
                                <div class="d-flex w-100 justify-content-between">
                                    <h6 class="mb-1">Email Notifications</h6>
                                    <small class="text-muted">Enabled</small>
                                </div>
                                <p class="mb-1">Receive notifications via email</p>
                            </div>
                            <div class="list-group-item">
                                <div class="d-flex w-100 justify-content-between">
                                    <h6 class="mb-1">Push Notifications</h6>
                                    <small class="text-muted">Disabled</small>
                                </div>
                                <p class="mb-1">Receive push notifications in browser</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary">Save Changes</button>
            </div>
        </div>
    </div>
</div>

<!-- Stack Modal -->
<div class="modal fade" id="stackModal" tabindex="-1" aria-labelledby="stackModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="stackModalLabel">Stackable Modal</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>This modal demonstrates stackable modals. You can open multiple modals that will stack on top of each other.</p>
                <button class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#stackModal2">
                    Open Another Modal
                </button>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Second Stack Modal -->
<div class="modal fade" id="stackModal2" tabindex="-1" aria-labelledby="stackModal2Label" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="stackModal2Label">Second Stack Modal</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>This is the second modal in the stack. Notice how it appears above the previous modal.</p>
                <button class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#stackModal3">
                    Open One More Modal
                </button>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Third Stack Modal -->
<div class="modal fade" id="stackModal3" tabindex="-1" aria-labelledby="stackModal3Label" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="stackModal3Label">Third Stack Modal</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>This is the third modal in the stack. Modals stack in reverse order when closing.</p>
                <div class="alert alert-info">
                    <i class="bi bi-info-circle me-2"></i>
                    You can create complex modal interactions with this approach.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Single Image Modal for Gallery -->
<div class="modal fade" id="singleImageModal" tabindex="-1" aria-labelledby="singleImageModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-body p-0">
                <button type="button" class="btn-close position-absolute end-0 m-3" style="z-index: 1000;" 
                        data-bs-dismiss="modal" aria-label="Close"></button>
                <img src="" id="singleImageModalSrc" alt="Full size image" class="img-fluid w-100">
            </div>
        </div>
    </div>
</div>

<script>
// Image modal functionality
function openImageModal(src) {
    document.getElementById('singleImageModalSrc').src = src;
    new bootstrap.Modal(document.getElementById('singleImageModal')).show();
}

// Demonstrate loading modal
document.querySelector('[data-bs-target="#loadingModal"]').addEventListener('click', function() {
    setTimeout(function() {
        bootstrap.Modal.getInstance(document.getElementById('loadingModal')).hide();
        if (window.bootstrapUI) {
            window.bootstrapUI.showAlert('success', 'Processing completed successfully!');
        }
    }, 3000);
});

// Modal event listeners for demonstration
document.querySelectorAll('.modal').forEach(modal => {
    modal.addEventListener('show.bs.modal', function(e) {
        console.log('Modal about to show:', this.id);
    });
    
    modal.addEventListener('shown.bs.modal', function(e) {
        console.log('Modal fully shown:', this.id);
    });
    
    modal.addEventListener('hide.bs.modal', function(e) {
        console.log('Modal about to hide:', this.id);
    });
    
    modal.addEventListener('hidden.bs.modal', function(e) {
        console.log('Modal fully hidden:', this.id);
    });
});

// Enhanced modal interactions
document.addEventListener('DOMContentLoaded', function() {
    // Add keyboard navigation to modals
    document.addEventListener('keydown', function(e) {
        const modals = document.querySelectorAll('.modal.show');
        if (modals.length > 0 && e.key === 'Escape') {
            modals[modals.length - 1].querySelector('.btn-close').click();
        }
    });
    
    // Add focus management
    document.querySelectorAll('.modal').forEach(modal => {
        modal.addEventListener('shown.bs.modal', function() {
            const firstInput = this.querySelector('input, button, select, textarea');
            if (firstInput) {
                firstInput.focus();
            }
        });
    });
});
</script>

<style>
.cursor-pointer {
    cursor: pointer;
    transition: transform 0.2s ease;
}

.cursor-pointer:hover {
    transform: scale(1.02);
}

.modal-content {
    border: none;
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
}

.modal-header {
    border-bottom: 1px solid rgba(0, 0, 0, 0.1);
}

.modal-footer {
    border-top: 1px solid rgba(0, 0, 0, 0.1);
}
</style>

<?php include '../includes/footer.php'; ?>