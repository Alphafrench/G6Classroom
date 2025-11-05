<?php 
$page_title = "Card Template - Bootstrap UI";
$breadcrumb = [
    ['title' => 'Home', 'url' => 'index.php'],
    ['title' => 'Templates', 'url' => 'index.php#templates'],
    ['title' => 'Card Template']
];
?>

<?php include '../includes/header.php'; ?>

<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-1">Card Template</h1>
                <p class="text-muted">Bootstrap 5 card components with various layouts and features</p>
            </div>
            <div class="d-flex gap-2">
                <button class="btn btn-outline-primary" data-bs-toggle="tooltip" title="Add Card">
                    <i class="bi bi-plus-circle"></i>
                </button>
                <button class="btn btn-outline-secondary" data-bs-toggle="tooltip" title="Customize">
                    <i class="bi bi-gear"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Basic Cards -->
<div class="row g-4 mb-4">
    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-body">
                <h5 class="card-title">Simple Card</h5>
                <p class="card-text">This is a basic card with some example text content. Cards can contain various types of content including text, images, buttons, and more.</p>
                <a href="#" class="btn btn-primary">Learn More</a>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="card-title mb-0">Card with Header</h5>
            </div>
            <div class="card-body">
                <p class="card-text">This card includes a header section that's styled differently from the body content.</p>
                <a href="#" class="btn btn-outline-primary">Action</a>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-body">
                <h5 class="card-title">Card with Footer</h5>
                <p class="card-text">This card has a footer section for additional information or actions.</p>
            </div>
            <div class="card-footer text-muted">
                <i class="bi bi-clock me-1"></i>Updated 2 mins ago
            </div>
        </div>
    </div>
</div>

<!-- Card with Images -->
<div class="row g-4 mb-4">
    <div class="col-lg-6">
        <div class="card h-100">
            <img src="https://via.placeholder.com/600x300" class="card-img-top" alt="Card Image">
            <div class="card-body">
                <h5 class="card-title">Card with Image</h5>
                <p class="card-text">This card includes an image at the top, followed by content and actions.</p>
                <div class="d-flex justify-content-between align-items-center">
                    <small class="text-muted">
                        <i class="bi bi-person me-1"></i>John Doe
                    </small>
                    <button class="btn btn-primary btn-sm">
                        <i class="bi bi-heart me-1"></i>Like
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-body">
                <h5 class="card-title">
                    <i class="bi bi-camera me-2"></i>Image Overlay
                </h5>
                <div class="position-relative">
                    <img src="https://via.placeholder.com/600x300" class="img-fluid rounded" alt="Overlay Image">
                    <div class="position-absolute bottom-0 start-0 w-100 p-3 bg-dark bg-opacity-75 text-white rounded-bottom">
                        <h6 class="mb-1">Overlay Content</h6>
                        <small>This text has an image overlay background</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Profile Cards -->
<div class="row g-4 mb-4">
    <div class="col-lg-4">
        <div class="card h-100 text-center">
            <div class="card-body">
                <img src="https://via.placeholder.com/120" class="rounded-circle mb-3" alt="Profile Picture" width="120" height="120">
                <h5 class="card-title">Sarah Johnson</h5>
                <p class="text-muted mb-3">Frontend Developer</p>
                <p class="card-text">Passionate about creating beautiful and functional user interfaces with modern web technologies.</p>
                <div class="d-flex justify-content-center gap-2 mb-3">
                    <a href="#" class="btn btn-outline-primary btn-sm">
                        <i class="bi bi-twitter"></i>
                    </a>
                    <a href="#" class="btn btn-outline-primary btn-sm">
                        <i class="bi bi-linkedin"></i>
                    </a>
                    <a href="#" class="btn btn-outline-primary btn-sm">
                        <i class="bi bi-github"></i>
                    </a>
                </div>
                <div class="btn-group">
                    <button class="btn btn-primary">
                        <i class="bi bi-person-plus me-1"></i>Follow
                    </button>
                    <button class="btn btn-outline-primary">
                        <i class="bi bi-chat"></i>
                    </button>
                </div>
            </div>
            <div class="card-footer">
                <small class="text-muted">Joined March 2024</small>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                    <img src="https://via.placeholder.com/60" class="rounded-circle me-3" alt="Profile Picture" width="60" height="60">
                    <div>
                        <h5 class="card-title mb-0">Mike Davis</h5>
                        <p class="text-muted mb-0">Product Manager</p>
                    </div>
                </div>
                
                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-1">
                        <small>Projects Completed</small>
                        <small><strong>24</strong></small>
                    </div>
                    <div class="progress" style="height: 6px;">
                        <div class="progress-bar bg-success" style="width: 80%"></div>
                    </div>
                </div>
                
                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-1">
                        <small>Team Collaboration</small>
                        <small><strong>92%</strong></small>
                    </div>
                    <div class="progress" style="height: 6px;">
                        <div class="progress-bar bg-info" style="width: 92%"></div>
                    </div>
                </div>
                
                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-1">
                        <small>Client Satisfaction</small>
                        <small><strong>88%</strong></small>
                    </div>
                    <div class="progress" style="height: 6px;">
                        <div class="progress-bar bg-warning" style="width: 88%"></div>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <small class="text-muted">
                    <i class="bi bi-calendar me-1"></i>Last active: 2 hours ago
                </small>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div class="d-flex align-items-center">
                        <img src="https://via.placeholder.com/50" class="rounded-circle me-3" alt="Profile Picture" width="50" height="50">
                        <div>
                            <h6 class="mb-0">Emma Wilson</h6>
                            <small class="text-muted">UX Designer</small>
                        </div>
                    </div>
                    <div class="dropdown">
                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                            <i class="bi bi-three-dots"></i>
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#">Edit Profile</a></li>
                            <li><a class="dropdown-item" href="#">Send Message</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="#">Remove</a></li>
                        </ul>
                    </div>
                </div>
                
                <div class="mb-3">
                    <span class="badge bg-primary me-1">UI/UX</span>
                    <span class="badge bg-success me-1">Design</span>
                    <span class="badge bg-info me-1">Research</span>
                </div>
                
                <p class="card-text small">Creating user-centered designs that solve real problems and enhance user experiences across digital platforms.</p>
                
                <div class="d-flex gap-2">
                    <button class="btn btn-outline-primary btn-sm flex-fill">
                        <i class="bi bi-envelope me-1"></i>Message
                    </button>
                    <button class="btn btn-primary btn-sm">
                        <i class="bi bi-telephone me-1"></i>Call
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Product Cards -->
<div class="row g-4 mb-4">
    <div class="col-lg-3 col-md-6">
        <div class="card h-100 product-card">
            <div class="position-relative">
                <img src="https://via.placeholder.com/300x200" class="card-img-top" alt="Product">
                <div class="position-absolute top-0 start-0 m-2">
                    <span class="badge bg-success">New</span>
                </div>
                <div class="position-absolute top-0 end-0 m-2">
                    <button class="btn btn-sm btn-outline-light">
                        <i class="bi bi-heart"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <h6 class="card-title">Wireless Headphones</h6>
                <div class="d-flex align-items-center mb-2">
                    <div class="text-warning me-2">
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-half"></i>
                    </div>
                    <small class="text-muted">(4.5)</small>
                </div>
                <p class="card-text small text-muted">Premium wireless headphones with noise cancellation</p>
                <div class="d-flex justify-content-between align-items-center">
                    <span class="fw-bold text-primary">$299.99</span>
                    <button class="btn btn-primary btn-sm">
                        <i class="bi bi-cart-plus me-1"></i>Add to Cart
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6">
        <div class="card h-100 product-card">
            <div class="position-relative">
                <img src="https://via.placeholder.com/300x200" class="card-img-top" alt="Product">
                <div class="position-absolute top-0 start-0 m-2">
                    <span class="badge bg-danger">-20%</span>
                </div>
            </div>
            <div class="card-body">
                <h6 class="card-title">Smart Watch</h6>
                <div class="d-flex align-items-center mb-2">
                    <div class="text-warning me-2">
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star"></i>
                    </div>
                    <small class="text-muted">(4.2)</small>
                </div>
                <p class="card-text small text-muted">Fitness tracking with heart rate monitoring</p>
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <span class="fw-bold text-primary">$199.99</span>
                        <small class="text-muted text-decoration-line-through">$249.99</small>
                    </div>
                    <button class="btn btn-primary btn-sm">
                        <i class="bi bi-cart-plus me-1"></i>Add to Cart
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6">
        <div class="card h-100 product-card">
            <img src="https://via.placeholder.com/300x200" class="card-img-top" alt="Product">
            <div class="card-body">
                <h6 class="card-title">Cotton T-Shirt</h6>
                <div class="d-flex align-items-center mb-2">
                    <div class="text-warning me-2">
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                    </div>
                    <small class="text-muted">(5.0)</small>
                </div>
                <p class="card-text small text-muted">100% organic cotton, comfortable fit</p>
                <div class="d-flex justify-content-between align-items-center">
                    <span class="fw-bold text-primary">$29.99</span>
                    <button class="btn btn-outline-primary btn-sm">
                        <i class="bi bi-cart-plus me-1"></i>Add to Cart
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6">
        <div class="card h-100 product-card">
            <img src="https://via.placeholder.com/300x200" class="card-img-top" alt="Product">
            <div class="card-body">
                <h6 class="card-title">Leather Wallet</h6>
                <div class="d-flex align-items-center mb-2">
                    <div class="text-warning me-2">
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-half"></i>
                        <i class="bi bi-star"></i>
                    </div>
                    <small class="text-muted">(3.5)</small>
                </div>
                <p class="card-text small text-muted">Genuine leather wallet with RFID protection</p>
                <div class="d-flex justify-content-between align-items-center">
                    <span class="fw-bold text-primary">$79.99</span>
                    <button class="btn btn-primary btn-sm">
                        <i class="bi bi-cart-plus me-1"></i>Add to Cart
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Stat Cards -->
<div class="row g-4 mb-4">
    <div class="col-lg-2 col-md-4 col-6">
        <div class="card bg-primary text-white text-center h-100">
            <div class="card-body">
                <i class="bi bi-people fs-1 mb-2"></i>
                <h3 class="mb-0">1,234</h3>
                <small class="text-white-50">Customers</small>
            </div>
        </div>
    </div>
    
    <div class="col-lg-2 col-md-4 col-6">
        <div class="card bg-success text-white text-center h-100">
            <div class="card-body">
                <i class="bi bi-cart-check fs-1 mb-2"></i>
                <h3 class="mb-0">567</h3>
                <small class="text-white-50">Orders</small>
            </div>
        </div>
    </div>
    
    <div class="col-lg-2 col-md-4 col-6">
        <div class="card bg-info text-white text-center h-100">
            <div class="card-body">
                <i class="bi bi-currency-dollar fs-1 mb-2"></i>
                <h3 class="mb-0">$12K</h3>
                <small class="text-white-50">Revenue</small>
            </div>
        </div>
    </div>
    
    <div class="col-lg-2 col-md-4 col-6">
        <div class="card bg-warning text-white text-center h-100">
            <div class="card-body">
                <i class="bi bi-graph-up fs-1 mb-2"></i>
                <h3 class="mb-0">8.2%</h3>
                <small class="text-white-50">Growth</small>
            </div>
        </div>
    </div>
    
    <div class="col-lg-2 col-md-4 col-6">
        <div class="card bg-danger text-white text-center h-100">
            <div class="card-body">
                <i class="bi bi-exclamation-triangle fs-1 mb-2"></i>
                <h3 class="mb-0">23</h3>
                <small class="text-white-50">Issues</small>
            </div>
        </div>
    </div>
    
    <div class="col-lg-2 col-md-4 col-6">
        <div class="card bg-secondary text-white text-center h-100">
            <div class="card-body">
                <i class="bi bi-clock fs-1 mb-2"></i>
                <h3 class="mb-0">99.9%</h3>
                <small class="text-white-50">Uptime</small>
            </div>
        </div>
    </div>
</div>

<!-- Advanced Card Layouts -->
<div class="row g-4 mb-4">
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-chat-dots me-2"></i>Latest Comments
                    </h5>
                    <span class="badge bg-primary">5</span>
                </div>
            </div>
            <div class="card-body">
                <div class="comment-item d-flex mb-3 pb-3 border-bottom">
                    <img src="https://via.placeholder.com/40" class="rounded-circle me-3" alt="User" width="40" height="40">
                    <div class="flex-grow-1">
                        <div class="d-flex justify-content-between align-items-start mb-1">
                            <h6 class="mb-0">John Doe</h6>
                            <small class="text-muted">2 min ago</small>
                        </div>
                        <p class="mb-2 small">Great work on the new dashboard design! The interface is much more intuitive now.</p>
                        <div class="d-flex gap-2">
                            <button class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-heart"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-reply"></i>
                            </button>
                        </div>
                    </div>
                </div>
                
                <div class="comment-item d-flex mb-3 pb-3 border-bottom">
                    <img src="https://via.placeholder.com/40" class="rounded-circle me-3" alt="User" width="40" height="40">
                    <div class="flex-grow-1">
                        <div class="d-flex justify-content-between align-items-start mb-1">
                            <h6 class="mb-0">Sarah Johnson</h6>
                            <small class="text-muted">5 min ago</small>
                        </div>
                        <p class="mb-2 small">I think we should add a dark mode toggle. Many users prefer dark themes nowadays.</p>
                        <div class="d-flex gap-2">
                            <button class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-heart"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-reply"></i>
                            </button>
                        </div>
                    </div>
                </div>
                
                <div class="comment-item d-flex">
                    <img src="https://via.placeholder.com/40" class="rounded-circle me-3" alt="User" width="40" height="40">
                    <div class="flex-grow-1">
                        <div class="d-flex justify-content-between align-items-start mb-1">
                            <h6 class="mb-0">Mike Davis</h6>
                            <small class="text-muted">10 min ago</small>
                        </div>
                        <p class="mb-2 small">The loading performance has improved significantly. Great optimization work!</p>
                        <div class="d-flex gap-2">
                            <button class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-heart"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-reply"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-calendar3 me-2"></i>Upcoming Events
                </h5>
            </div>
            <div class="card-body">
                <div class="event-item d-flex mb-3 pb-3 border-bottom">
                    <div class="event-date text-center me-3">
                        <div class="bg-primary text-white rounded p-2">
                            <div class="fs-6 fw-bold">15</div>
                            <small>FEB</small>
                        </div>
                    </div>
                    <div class="flex-grow-1">
                        <h6 class="mb-1">Team Meeting</h6>
                        <p class="mb-2 small text-muted">Quarterly review and planning session</p>
                        <div class="d-flex align-items-center">
                            <small class="text-muted me-3">
                                <i class="bi bi-clock me-1"></i>10:00 AM
                            </small>
                            <small class="text-muted">
                                <i class="bi bi-geo-alt me-1"></i>Conference Room A
                            </small>
                        </div>
                    </div>
                </div>
                
                <div class="event-item d-flex mb-3 pb-3 border-bottom">
                    <div class="event-date text-center me-3">
                        <div class="bg-success text-white rounded p-2">
                            <div class="fs-6 fw-bold">18</div>
                            <small>FEB</small>
                        </div>
                    </div>
                    <div class="flex-grow-1">
                        <h6 class="mb-1">Product Launch</h6>
                        <p class="mb-2 small text-muted">Launch event for the new mobile app</p>
                        <div class="d-flex align-items-center">
                            <small class="text-muted me-3">
                                <i class="bi bi-clock me-1"></i>2:00 PM
                            </small>
                            <small class="text-muted">
                                <i class="bi bi-geo-alt me-1"></i>Main Auditorium
                            </small>
                        </div>
                    </div>
                </div>
                
                <div class="event-item d-flex mb-3 pb-3 border-bottom">
                    <div class="event-date text-center me-3">
                        <div class="bg-warning text-white rounded p-2">
                            <div class="fs-6 fw-bold">22</div>
                            <small>FEB</small>
                        </div>
                    </div>
                    <div class="flex-grow-1">
                        <h6 class="mb-1">Training Workshop</h6>
                        <p class="mb-2 small text-muted">Advanced React development techniques</p>
                        <div class="d-flex align-items-center">
                            <small class="text-muted me-3">
                                <i class="bi bi-clock me-1"></i>9:00 AM
                            </small>
                            <small class="text-muted">
                                <i class="bi bi-geo-alt me-1"></i>Training Room
                            </small>
                        </div>
                    </div>
                </div>
                
                <div class="event-item d-flex">
                    <div class="event-date text-center me-3">
                        <div class="bg-info text-white rounded p-2">
                            <div class="fs-6 fw-bold">25</div>
                            <small>FEB</small>
                        </div>
                    </div>
                    <div class="flex-grow-1">
                        <h6 class="mb-1">Client Presentation</h6>
                        <p class="mb-2 small text-muted">Final presentation for Project Alpha</p>
                        <div class="d-flex align-items-center">
                            <small class="text-muted me-3">
                                <i class="bi bi-clock me-1"></i>3:30 PM
                            </small>
                            <small class="text-muted">
                                <i class="bi bi-geo-alt me-1"></i>Client Office
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Weather Card -->
<div class="row g-4 mb-4">
    <div class="col-lg-4">
        <div class="card bg-primary text-white h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h6 class="card-title">Current Weather</h6>
                        <h2 class="mb-0">22°C</h2>
                    </div>
                    <i class="bi bi-sun fs-1 opacity-75"></i>
                </div>
                <div class="row text-center">
                    <div class="col-6">
                        <div class="border-end border-white border-opacity-25">
                            <div class="small">Humidity</div>
                            <div class="fw-bold">65%</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="small">Wind</div>
                        <div class="fw-bold">15 km/h</div>
                    </div>
                </div>
            </div>
            <div class="card-footer bg-transparent border-top border-white border-opacity-25">
                <small>San Francisco, CA</small>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-body">
                <h6 class="card-title mb-3">
                    <i class="bi bi-list-task me-2"></i>Tasks Progress
                </h6>
                <div class="task-item mb-3">
                    <div class="d-flex justify-content-between mb-1">
                        <small>Design Mockups</small>
                        <small class="text-success">Done</small>
                    </div>
                    <div class="progress" style="height: 4px;">
                        <div class="progress-bar bg-success" style="width: 100%"></div>
                    </div>
                </div>
                <div class="task-item mb-3">
                    <div class="d-flex justify-content-between mb-1">
                        <small>Frontend Development</small>
                        <small class="text-warning">In Progress</small>
                    </div>
                    <div class="progress" style="height: 4px;">
                        <div class="progress-bar bg-warning" style="width: 75%"></div>
                    </div>
                </div>
                <div class="task-item mb-3">
                    <div class="d-flex justify-content-between mb-1">
                        <small>Backend API</small>
                        <small class="text-info">Planning</small>
                    </div>
                    <div class="progress" style="height: 4px;">
                        <div class="progress-bar bg-info" style="width: 25%"></div>
                    </div>
                </div>
                <div class="task-item">
                    <div class="d-flex justify-content-between mb-1">
                        <small>Testing & QA</small>
                        <small class="text-muted">Not Started</small>
                    </div>
                    <div class="progress" style="height: 4px;">
                        <div class="progress-bar" style="width: 0%"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-body">
                <h6 class="card-title mb-3">
                    <i class="bi bi-graph-up me-2"></i>Performance Metrics
                </h6>
                <div class="metric-item d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                    <div>
                        <div class="fw-semibold">Page Load Time</div>
                        <small class="text-muted">Average response time</small>
                    </div>
                    <div class="text-end">
                        <div class="fw-bold text-success">1.2s</div>
                        <small class="text-success">
                            <i class="bi bi-arrow-down"></i> -0.3s
                        </small>
                    </div>
                </div>
                <div class="metric-item d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                    <div>
                        <div class="fw-semibold">Server Uptime</div>
                        <small class="text-muted">Last 30 days</small>
                    </div>
                    <div class="text-end">
                        <div class="fw-bold text-success">99.9%</div>
                        <small class="text-muted">Stable</small>
                    </div>
                </div>
                <div class="metric-item d-flex justify-content-between align-items-center">
                    <div>
                        <div class="fw-semibold">Error Rate</div>
                        <small class="text-muted">System errors</small>
                    </div>
                    <div class="text-end">
                        <div class="fw-bold text-warning">0.1%</div>
                        <small class="text-warning">
                            <i class="bi bi-arrow-up"></i> +0.05%
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Interactive Cards -->
<div class="row g-4">
    <div class="col-lg-6">
        <div class="card h-100 interactive-card" style="cursor: pointer;">
            <div class="card-body text-center">
                <div class="card-icon mb-3">
                    <i class="bi bi-camera fs-1 text-primary"></i>
                </div>
                <h5 class="card-title">Click to Expand</h5>
                <p class="card-text">Click on this card to see an expanded view with more details and functionality.</p>
                <button class="btn btn-primary">
                    <i class="bi bi-arrows-expand me-1"></i>View Details
                </button>
            </div>
        </div>
    </div>
    
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-gear me-2"></i>Settings Panel
                    </h5>
                    <button class="btn btn-sm btn-outline-secondary" id="toggleSettings">
                        <i class="bi bi-chevron-down"></i>
                    </button>
                </div>
                
                <div class="settings-content" id="settingsContent" style="display: none;">
                    <div class="mb-3">
                        <label class="form-label">Theme</label>
                        <select class="form-select">
                            <option>Light</option>
                            <option>Dark</option>
                            <option>Auto</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="emailNotifications">
                            <label class="form-check-label" for="emailNotifications">Email Notifications</label>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="pushNotifications" checked>
                            <label class="form-check-label" for="pushNotifications">Push Notifications</label>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Language</label>
                        <select class="form-select">
                            <option>English</option>
                            <option>Spanish</option>
                            <option>French</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Interactive card functionality
document.addEventListener('DOMContentLoaded', function() {
    // Settings toggle
    const toggleBtn = document.getElementById('toggleSettings');
    const settingsContent = document.getElementById('settingsContent');
    
    if (toggleBtn && settingsContent) {
        toggleBtn.addEventListener('click', function() {
            const isVisible = settingsContent.style.display !== 'none';
            
            if (isVisible) {
                settingsContent.style.display = 'none';
                this.querySelector('i').className = 'bi bi-chevron-down';
            } else {
                settingsContent.style.display = 'block';
                this.querySelector('i').className = 'bi bi-chevron-up';
            }
        });
    }
    
    // Interactive cards hover effects
    const interactiveCards = document.querySelectorAll('.interactive-card, .product-card');
    interactiveCards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-5px)';
            this.style.boxShadow = '0 0.5rem 1rem rgba(0, 0, 0, 0.15)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
            this.style.boxShadow = '';
        });
    });
    
    // Like buttons functionality
    document.querySelectorAll('[data-like]').forEach(button => {
        button.addEventListener('click', function() {
            const icon = this.querySelector('i');
            if (icon.classList.contains('bi-heart')) {
                icon.className = 'bi bi-heart-fill text-danger';
            } else {
                icon.className = 'bi bi-heart';
            }
        });
    });
    
    // Comment interactions
    document.querySelectorAll('.comment-item .btn').forEach(button => {
        button.addEventListener('click', function() {
            const type = this.querySelector('i').classList.contains('bi-heart') ? 'like' : 'reply';
            if (window.bootstrapUI) {
                window.bootstrapUI.showAlert('info', `Comment ${type} action triggered!`);
            }
        });
    });
    
    // Add to cart buttons
    document.querySelectorAll('.product-card .btn').forEach(button => {
        button.addEventListener('click', function() {
            if (this.textContent.includes('Add to Cart')) {
                const originalText = this.innerHTML;
                this.innerHTML = '<i class="bi bi-check me-1"></i>Added!';
                this.classList.remove('btn-primary');
                this.classList.add('btn-success');
                
                setTimeout(() => {
                    this.innerHTML = originalText;
                    this.classList.remove('btn-success');
                    this.classList.add('btn-primary');
                }, 2000);
                
                if (window.bootstrapUI) {
                    window.bootstrapUI.showAlert('success', 'Product added to cart!');
                }
            }
        });
    });
});

// Dynamic card creation
function createStatCard(title, value, icon, color, change = null) {
    return `
        <div class="col-lg-2 col-md-4 col-6">
            <div class="card bg-${color} text-white text-center h-100">
                <div class="card-body">
                    <i class="bi bi-${icon} fs-1 mb-2"></i>
                    <h3 class="mb-0">${value}</h3>
                    <small class="text-white-50">${title}</small>
                    ${change ? `<div class="mt-2"><small class="text-light">${change}</small></div>` : ''}
                </div>
            </div>
        </div>
    `;
}

// Example of dynamic stat card addition
function addDynamicStatCard() {
    const container = document.querySelector('.row.g-4.mb-4');
    if (container && window.bootstrapUI) {
        const newCard = createStatCard('New Users', '47', 'person-plus', 'info', '+12% today');
        container.insertAdjacentHTML('afterbegin', newCard);
        window.bootstrapUI.showAlert('success', 'New stat card added!');
    }
}
</script>

<style>
.product-card {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.product-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
}

.interactive-card {
    transition: all 0.3s ease;
}

.interactive-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
}

.comment-item:last-child,
.event-item:last-child,
.metric-item:last-child {
    border-bottom: none !important;
    margin-bottom: 0 !important;
    padding-bottom: 0 !important;
}

.event-date {
    min-width: 60px;
}

.card-icon {
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0% { opacity: 1; }
    50% { opacity: 0.7; }
    100% { opacity: 1; }
}

@media (max-width: 768px) {
    .event-item {
        flex-direction: column;
        text-align: center;
    }
    
    .event-date {
        margin-bottom: 1rem;
        margin-right: 0 !important;
    }
    
    .comment-item {
        flex-direction: column;
        text-align: center;
    }
    
    .comment-item img {
        margin-bottom: 0.5rem;
        margin-right: 0 !important;
    }
}
</style>

<?php include '../includes/footer.php'; ?>