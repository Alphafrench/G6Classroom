<?php 
$page_title = "Form Template - Bootstrap UI";
$breadcrumb = [
    ['title' => 'Home', 'url' => 'index.php'],
    ['title' => 'Templates', 'url' => 'index.php#templates'],
    ['title' => 'Form Template']
];
?>

<?php include '../includes/header.php'; ?>

<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-1">Form Template</h1>
                <p class="text-muted">Comprehensive form examples with Bootstrap 5 validation</p>
            </div>
            <div class="d-flex gap-2">
                <button class="btn btn-outline-primary" data-bs-toggle="tooltip" title="Preview">
                    <i class="bi bi-eye"></i>
                </button>
                <button class="btn btn-outline-secondary" data-bs-toggle="tooltip" title="Download">
                    <i class="bi bi-download"></i>
                </button>
                <button class="btn btn-primary" data-bs-toggle="tooltip" title="Edit">
                    <i class="bi bi-pencil"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Form Template Examples -->
<div class="row g-4">
    <!-- Basic Contact Form -->
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-person-lines-fill me-2"></i>Basic Contact Form
                </h5>
            </div>
            <div class="card-body">
                <form class="needs-validation" novalidate>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="firstName" class="form-label">First Name *</label>
                            <input type="text" class="form-control" id="firstName" required>
                            <div class="invalid-feedback">Please provide a first name.</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="lastName" class="form-label">Last Name *</label>
                            <input type="text" class="form-control" id="lastName" required>
                            <div class="invalid-feedback">Please provide a last name.</div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="email" class="form-label">Email Address *</label>
                        <input type="email" class="form-control" id="email" required>
                        <div class="invalid-feedback">Please provide a valid email address.</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="phone" class="form-label">Phone Number</label>
                        <input type="tel" class="form-control" id="phone" placeholder="+1 (555) 123-4567">
                    </div>
                    
                    <div class="mb-3">
                        <label for="subject" class="form-label">Subject *</label>
                        <select class="form-select" id="subject" required>
                            <option value="">Choose a subject...</option>
                            <option value="general">General Inquiry</option>
                            <option value="support">Technical Support</option>
                            <option value="sales">Sales</option>
                            <option value="feedback">Feedback</option>
                        </select>
                        <div class="invalid-feedback">Please select a subject.</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="message" class="form-label">Message *</label>
                        <textarea class="form-control" id="message" rows="4" required placeholder="Type your message here..."></textarea>
                        <div class="invalid-feedback">Please provide a message.</div>
                    </div>
                    
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="terms" required>
                        <label class="form-check-label" for="terms">
                            I agree to the <a href="#" class="text-decoration-none">Terms and Conditions</a> *
                        </label>
                        <div class="invalid-feedback">You must agree before submitting.</div>
                    </div>
                    
                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <button type="button" class="btn btn-outline-secondary">Reset</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-send me-1"></i>Send Message
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Registration Form -->
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-person-plus-fill me-2"></i>User Registration
                </h5>
            </div>
            <div class="card-body">
                <form class="needs-validation" novalidate>
                    <div class="mb-3">
                        <label for="username" class="form-label">Username *</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-person"></i></span>
                            <input type="text" class="form-control" id="username" required minlength="3">
                            <div class="invalid-feedback">Username must be at least 3 characters.</div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="regEmail" class="form-label">Email Address *</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                            <input type="email" class="form-control" id="regEmail" required>
                        </div>
                        <div class="invalid-feedback">Please provide a valid email address.</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="password" class="form-label">Password *</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-lock"></i></span>
                            <input type="password" class="form-control" id="password" required minlength="8">
                            <button class="btn btn-outline-secondary" type="button" data-bs-toggle="password" data-target="password">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                        <div class="form-text">Password must be at least 8 characters long.</div>
                        <div class="invalid-feedback">Password must be at least 8 characters.</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="confirmPassword" class="form-label">Confirm Password *</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                            <input type="password" class="form-control" id="confirmPassword" required>
                        </div>
                        <div class="invalid-feedback">Passwords must match.</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="birthDate" class="form-label">Date of Birth</label>
                        <input type="date" class="form-control" id="birthDate">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Gender</label>
                        <div class="d-flex gap-4">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="gender" id="male" value="male">
                                <label class="form-check-label" for="male">Male</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="gender" id="female" value="female">
                                <label class="form-check-label" for="female">Female</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="gender" id="other" value="other">
                                <label class="form-check-label" for="other">Other</label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="newsletter">
                        <label class="form-check-label" for="newsletter">
                            Subscribe to our newsletter
                        </label>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-person-plus me-1"></i>Create Account
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Search and Filter Form -->
<div class="row g-4 mt-1">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-funnel me-2"></i>Advanced Search & Filter Form
                </h5>
            </div>
            <div class="card-body">
                <form class="row g-3">
                    <div class="col-md-3">
                        <label for="searchQuery" class="form-label">Search</label>
                        <input type="text" class="form-control" id="searchQuery" placeholder="Search...">
                    </div>
                    <div class="col-md-3">
                        <label for="category" class="form-label">Category</label>
                        <select class="form-select" id="category">
                            <option value="">All Categories</option>
                            <option value="electronics">Electronics</option>
                            <option value="clothing">Clothing</option>
                            <option value="books">Books</option>
                            <option value="home">Home & Garden</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="priceMin" class="form-label">Min Price</label>
                        <input type="number" class="form-control" id="priceMin" min="0" step="0.01">
                    </div>
                    <div class="col-md-2">
                        <label for="priceMax" class="form-label">Max Price</label>
                        <input type="number" class="form-control" id="priceMax" min="0" step="0.01">
                    </div>
                    <div class="col-md-2">
                        <label for="rating" class="form-label">Min Rating</label>
                        <select class="form-select" id="rating">
                            <option value="">Any Rating</option>
                            <option value="5">5 Stars</option>
                            <option value="4">4+ Stars</option>
                            <option value="3">3+ Stars</option>
                            <option value="2">2+ Stars</option>
                            <option value="1">1+ Stars</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="dateFrom" class="form-label">Date From</label>
                        <input type="date" class="form-control" id="dateFrom">
                    </div>
                    <div class="col-md-3">
                        <label for="dateTo" class="form-label">Date To</label>
                        <input type="date" class="form-control" id="dateTo">
                    </div>
                    <div class="col-md-3">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" id="status">
                            <option value="">All Statuses</option>
                            <option value="active">Active</option>
                            <option value="pending">Pending</option>
                            <option value="completed">Completed</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="sortBy" class="form-label">Sort By</label>
                        <select class="form-select" id="sortBy">
                            <option value="date">Date Created</option>
                            <option value="name">Name</option>
                            <option value="price">Price</option>
                            <option value="rating">Rating</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-search me-1"></i>Search
                            </button>
                            <button type="reset" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-clockwise me-1"></i>Reset
                            </button>
                            <button type="button" class="btn btn-outline-success" data-bs-toggle="collapse" data-bs-target="#advancedFilters">
                                <i class="bi bi-gear me-1"></i>Advanced Filters
                            </button>
                        </div>
                    </div>
                    
                    <!-- Advanced Filters (Collapsible) -->
                    <div class="collapse" id="advancedFilters">
                        <div class="row g-3 pt-3 border-top">
                            <div class="col-md-4">
                                <label class="form-label">Features</label>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="feature1">
                                    <label class="form-check-label" for="feature1">Feature 1</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="feature2">
                                    <label class="form-check-label" for="feature2">Feature 2</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="feature3">
                                    <label class="form-check-label" for="feature3">Feature 3</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Colors</label>
                                <div class="d-flex flex-wrap gap-2">
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="checkbox" id="color1">
                                        <label class="form-check-label" for="color1">Red</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="checkbox" id="color2">
                                        <label class="form-check-label" for="color2">Blue</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="checkbox" id="color3">
                                        <label class="form-check-label" for="color3">Green</label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label for="customField" class="form-label">Custom Field</label>
                                <input type="text" class="form-control" id="customField" placeholder="Enter custom value">
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- File Upload Form -->
<div class="row g-4 mt-1">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-cloud-upload me-2"></i>File Upload Form
                </h5>
            </div>
            <div class="card-body">
                <form>
                    <div class="mb-3">
                        <label for="documentTitle" class="form-label">Document Title</label>
                        <input type="text" class="form-control" id="documentTitle" placeholder="Enter document title">
                    </div>
                    
                    <div class="mb-3">
                        <label for="documentType" class="form-label">Document Type</label>
                        <select class="form-select" id="documentType">
                            <option value="">Select type...</option>
                            <option value="pdf">PDF Document</option>
                            <option value="image">Image</option>
                            <option value="spreadsheet">Spreadsheet</option>
                            <option value="presentation">Presentation</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="fileUpload" class="form-label">Select File</label>
                        <div class="border border-dashed rounded p-4 text-center">
                            <input type="file" class="form-control" id="fileUpload" multiple accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.gif,.xls,.xlsx,.ppt,.pptx" hidden>
                            <div class="mb-2">
                                <i class="bi bi-cloud-upload fs-1 text-muted"></i>
                            </div>
                            <p class="text-muted mb-2">Drag and drop files here, or click to browse</p>
                            <button type="button" class="btn btn-outline-primary" onclick="document.getElementById('fileUpload').click()">
                                <i class="bi bi-folder me-1"></i>Choose Files
                            </button>
                            <p class="text-muted small mt-2">Supported formats: PDF, DOC, DOCX, JPG, PNG, XLS, PPT (Max 10MB)</p>
                        </div>
                        <div id="fileList" class="mt-2"></div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="uploadDescription" class="form-label">Description</label>
                        <textarea class="form-control" id="uploadDescription" rows="3" placeholder="Brief description of the document"></textarea>
                    </div>
                    
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="makePublic">
                        <label class="form-check-label" for="makePublic">
                            Make this document publicly accessible
                        </label>
                    </div>
                    
                    <div class="d-grid">
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-cloud-upload me-1"></i>Upload Document
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Settings Form -->
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-gear me-2"></i>User Settings
                </h5>
            </div>
            <div class="card-body">
                <form>
                    <div class="mb-4">
                        <h6 class="text-primary mb-3">Profile Settings</h6>
                        <div class="row">
                            <div class="col-12 mb-3 text-center">
                                <img src="https://via.placeholder.com/120x120" alt="Profile Picture" class="rounded-circle mb-3" width="120" height="120">
                                <div>
                                    <button type="button" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-camera me-1"></i>Change Photo
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="profileBio" class="form-label">Bio</label>
                            <textarea class="form-control" id="profileBio" rows="3" placeholder="Tell us about yourself..."></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label for="website" class="form-label">Website</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-globe"></i></span>
                                <input type="url" class="form-control" id="website" placeholder="https://example.com">
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <h6 class="text-primary mb-3">Notification Preferences</h6>
                        <div class="form-check form-switch mb-2">
                            <input class="form-check-input" type="checkbox" id="emailNotifications" checked>
                            <label class="form-check-label" for="emailNotifications">Email Notifications</label>
                        </div>
                        <div class="form-check form-switch mb-2">
                            <input class="form-check-input" type="checkbox" id="pushNotifications" checked>
                            <label class="form-check-label" for="pushNotifications">Push Notifications</label>
                        </div>
                        <div class="form-check form-switch mb-2">
                            <input class="form-check-input" type="checkbox" id="marketingEmails">
                            <label class="form-check-label" for="marketingEmails">Marketing Emails</label>
                        </div>
                        <div class="form-check form-switch mb-2">
                            <input class="form-check-input" type="checkbox" id="securityAlerts" checked>
                            <label class="form-check-label" for="securityAlerts">Security Alerts</label>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <h6 class="text-primary mb-3">Privacy Settings</h6>
                        <div class="mb-3">
                            <label class="form-label">Profile Visibility</label>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="profileVisibility" id="publicProfile" checked>
                                <label class="form-check-label" for="publicProfile">Public</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="profileVisibility" id="friendsProfile">
                                <label class="form-check-label" for="friendsProfile">Friends Only</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="profileVisibility" id="privateProfile">
                                <label class="form-check-label" for="privateProfile">Private</label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <button type="button" class="btn btn-outline-secondary">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check me-1"></i>Save Settings
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
// File upload handling
document.getElementById('fileUpload').addEventListener('change', function(e) {
    const fileList = document.getElementById('fileList');
    const files = e.target.files;
    
    if (files.length > 0) {
        let html = '<div class="border rounded p-3 bg-light">';
        html += '<h6 class="mb-2">Selected Files:</h6>';
        
        for (let i = 0; i < files.length; i++) {
            const file = files[i];
            const size = (file.size / 1024 / 1024).toFixed(2);
            html += `
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <span><i class="bi bi-file-earmark me-1"></i>${file.name}</span>
                    <span class="text-muted">${size} MB</span>
                </div>
            `;
        }
        
        html += '</div>';
        fileList.innerHTML = html;
    }
});

// Password toggle functionality
document.querySelector('[data-bs-toggle="password"]').addEventListener('click', function() {
    const target = document.getElementById(this.dataset.target);
    const icon = this.querySelector('i');
    
    if (target.type === 'password') {
        target.type = 'text';
        icon.className = 'bi bi-eye-slash';
    } else {
        target.type = 'password';
        icon.className = 'bi bi-eye';
    }
});

// Drag and drop for file upload
const dropArea = document.querySelector('.border-dashed');

['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
    dropArea.addEventListener(eventName, preventDefaults, false);
});

function preventDefaults(e) {
    e.preventDefault();
    e.stopPropagation();
}

['dragenter', 'dragover'].forEach(eventName => {
    dropArea.addEventListener(eventName, highlight, false);
});

['dragleave', 'drop'].forEach(eventName => {
    dropArea.addEventListener(eventName, unhighlight, false);
});

function highlight(e) {
    dropArea.classList.add('border-primary', 'bg-light');
}

function unhighlight(e) {
    dropArea.classList.remove('border-primary', 'bg-light');
}

dropArea.addEventListener('drop', handleDrop, false);

function handleDrop(e) {
    const dt = e.dataTransfer;
    const files = dt.files;
    
    document.getElementById('fileUpload').files = files;
    
    // Trigger change event
    const event = new Event('change');
    document.getElementById('fileUpload').dispatchEvent(event);
}
</script>

<?php include '../includes/footer.php'; ?>