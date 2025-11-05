# Bootstrap Educational Platform UI Templates

A comprehensive collection of responsive Bootstrap 5 UI templates designed specifically for educational platforms. This package includes teacher and student dashboards, course management, assignment tracking, and more.

## 🎓 Features

- **Mobile-First Design**: Responsive layouts that work perfectly on all devices
- **Educational Focus**: Purpose-built components for learning management systems
- **Modern UI Components**: Clean, professional design with Bootstrap 5
- **Interactive Elements**: JavaScript-powered functionality for enhanced user experience
- **Accessibility**: WCAG-compliant design patterns
- **Customizable**: Easy to modify colors, fonts, and layouts

## 📁 File Structure

```
assets/
├── css/
│   └── style.css           # Custom educational theme styles
├── js/
│   └── app.js              # Interactive functionality
└── images/                 # Image assets directory

includes/
├── header.php              # Responsive navigation header
└── footer.php              # Common footer component

pages/templates/
├── teacher-dashboard-template.php     # Teacher dashboard
├── student-dashboard-template.php     # Student dashboard
├── course-template.php                # Course management
├── assignment-template.php            # Assignment tracking
├── dashboard-template.php             # General dashboard
├── form-template.php                  # Form components
├── table-template.php                 # Data tables
├── modal-template.php                 # Modal dialogs
└── card-template.php                  # Card components
```

## 🎨 Design System

### Color Palette
- **Primary**: #2563eb (Blue)
- **Success**: #059669 (Green)
- **Warning**: #d97706 (Orange)
- **Danger**: #dc2626 (Red)
- **Info**: #0284c7 (Light Blue)
- **Teacher Role**: #3b82f6 (Blue)
- **Student Role**: #10b981 (Green)
- **Admin Role**: #7c3aed (Purple)

### Typography
- Uses Bootstrap's native typography system
- Font weights: 400 (normal), 600 (semibold), 700 (bold)
- Responsive font sizes with rem units

## 🚀 Getting Started

### Prerequisites
- Bootstrap 5.3.0 or later
- Bootstrap Icons 1.10.0
- Modern web browser
- PHP 7.4+ (for PHP includes)

### Installation

1. **Include CSS Files**
```html
<!-- Bootstrap 5 CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

<!-- Bootstrap Icons -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">

<!-- Custom CSS -->
<link rel="stylesheet" href="assets/css/style.css">
```

2. **Include JavaScript Files**
```html
<!-- Bootstrap 5 JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- jQuery (optional) -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- Custom JS -->
<script src="assets/js/app.js"></script>
```

3. **Use PHP Includes** (Optional)
```php
<?php include 'includes/header.php'; ?>
<!-- Your content here -->
<?php include 'includes/footer.php'; ?>
```

## 📱 Responsive Breakpoints

- **XS**: < 576px (Mobile phones)
- **SM**: ≥ 576px (Large phones)
- **MD**: ≥ 768px (Tablets)
- **LG**: ≥ 992px (Desktops)
- **XL**: ≥ 1200px (Large desktops)
- **XXL**: ≥ 1400px (Extra large desktops)

## 🎯 Key Components

### 1. Dashboard Templates

#### Teacher Dashboard
- **File**: `pages/templates/teacher-dashboard-template.php`
- **Features**:
  - Quick stats (courses, students, pending grading)
  - Today's schedule with timeline view
  - Recent submissions table
  - Pending tasks list
  - Quick action buttons
  - Recent activity feed

#### Student Dashboard
- **File**: `pages/templates/student-dashboard-template.php`
- **Features**:
  - GPA display with visual grade indicator
  - Course progress cards
  - Upcoming deadlines
  - Today's class schedule
  - Assignment status tracking
  - Grade trend analysis

### 2. Course Management

#### Course Template
- **File**: `pages/templates/course-template.php`
- **Features**:
  - Course statistics overview
  - Filter and search functionality
  - Grid and list view options
  - Course status badges
  - Create/import course modals
  - Student enrollment tracking

### 3. Assignment Management

#### Assignment Template
- **File**: `pages/templates/assignment-template.php`
- **Features**:
  - Assignment statistics
  - Submissions tracking with progress bars
  - Bulk operations (grade, export, email)
  - Due date monitoring
  - Status indicators (pending, submitted, graded, overdue)
  - Assignment creation modal

### 4. Common Components

#### Header Navigation
- **File**: `includes/header.php`
- **Features**:
  - Responsive navigation menu
  - Search functionality
  - Notification dropdown
  - User profile dropdown
  - Course dropdown menu
  - Mobile-friendly hamburger menu

#### Footer
- **File**: `includes/footer.php`
- **Features**:
  - Multi-column layout
  - Social media links
  - Newsletter subscription
  - Quick links
  - Back to top button
  - Loading overlay

## 🔧 JavaScript Features

### BootstrapUI Class
The main JavaScript class (`assets/js/app.js`) provides:

#### Form Validation
- Real-time field validation
- Custom validation messages
- Form submission handling
- Loading states

#### Data Tables
- Search functionality
- Sorting indicators
- Pagination support
- Responsive tables

#### Educational Features
- **Grade Calculations**: GPA calculation and grade display
- **Attendance Tracking**: Visual attendance charts
- **Course Management**: Enrollment and progress tracking
- **Assignment Tracking**: Submission status monitoring
- **Student Progress**: Progress circle animations
- **Schedule Timeline**: Interactive schedule items

#### UI Interactions
- Tooltip and popover initialization
- Modal management
- Tab navigation
- Progress bar animations
- Alert management
- Sidebar toggle (mobile)
- Search and filter functionality

## 📊 CSS Components

### Educational-Specific Classes

#### Role Badges
```html
<span class="role-badge student">Student</span>
<span class="role-badge teacher">Teacher</span>
<span class="role-badge admin">Admin</span>
```

#### Grade Display
```html
<div class="grade-display a-plus" data-grade="A+" data-percentage="97">
    A+
</div>
```

#### Course Cards
```html
<div class="course-card" data-progress="85">
    <div class="course-card-header">
        <h6 class="course-card-title">Course Name</h6>
        <div class="course-card-meta">
            <span><i class="bi bi-person me-1"></i>Instructor</span>
            <span><i class="bi bi-calendar me-1"></i>Schedule</span>
        </div>
    </div>
    <div class="card-body">
        <div class="progress">
            <div class="progress-bar" style="width: 85%"></div>
        </div>
    </div>
</div>
```

#### Assignment Status
```html
<span class="assignment-status pending">Pending</span>
<span class="assignment-status submitted">Submitted</span>
<span class="assignment-status graded">Graded</span>
<span class="assignment-status overdue">Overdue</span>
```

#### Attendance Indicators
```html
<div class="attendance-indicator present">
    <i class="bi bi-check-circle"></i> Present
</div>
<div class="attendance-indicator absent">
    <i class="bi bi-x-circle"></i> Absent
</div>
<div class="attendance-indicator late">
    <i class="bi bi-clock"></i> Late
</div>
```

#### Progress Circles
```html
<div class="progress-circle" style="--progress: 85%">
    <div class="progress-circle-content">
        <div class="progress-circle-value">85%</div>
        <div class="progress-circle-label">Complete</div>
    </div>
</div>
```

#### Schedule Timeline
```html
<div class="schedule-timeline">
    <div class="schedule-item" data-details='{"course":"Math 101","time":"9:00 AM","room":"Room 201"}'>
        <h6>Mathematics 101</h6>
        <p>Calculus fundamentals</p>
    </div>
</div>
```

#### Stats Cards
```html
<div class="stats-card revenue">
    <div class="stats-card-icon">
        <i class="bi bi-people"></i>
    </div>
    <div class="stats-card-value">247</div>
    <div class="stats-card-label">Total Students</div>
    <div class="stats-card-change positive">
        <i class="bi bi-arrow-up"></i> +12 new
    </div>
</div>
```

## 🎨 Customization

### Changing Colors
Update the CSS variables in `assets/css/style.css`:
```css
:root {
    --primary-color: #your-color;
    --student-color: #your-student-color;
    --teacher-color: #your-teacher-color;
    /* Add more variables */
}
```

### Adding New Templates
1. Create a new PHP file in `pages/templates/`
2. Include the header: `<?php include '../../includes/header.php'; ?>`
3. Add your content
4. Include the footer: `<?php include '../../includes/footer.php'; ?>`
5. Set page title and breadcrumb:
```php
$page_title = "Your Page Title";
$breadcrumb = [
    ['title' => 'Home', 'url' => 'index.php'],
    ['title' => 'Your Page']
];
```

## 📱 Mobile Features

- Touch-friendly navigation
- Responsive tables with horizontal scroll
- Collapsible sidebar (mobile)
- Mobile-optimized modals
- Touch gestures support
- Optimized font sizes for mobile

## ♿ Accessibility Features

- ARIA labels and roles
- Keyboard navigation support
- Focus indicators
- Screen reader friendly
- High contrast support
- Semantic HTML structure
- Alt text for images
- Form labels and descriptions

## 🐛 Browser Support

- Chrome (latest)
- Firefox (latest)
- Safari (latest)
- Edge (latest)
- Internet Explorer 11+ (with polyfills)

## 📦 Dependencies

### Required
- Bootstrap 5.3.0
- Bootstrap Icons 1.10.0

### Optional
- jQuery 3.6.0
- Chart.js (for advanced charts)
- DataTables (for advanced tables)

## 🔄 Updates & Maintenance

### Regular Updates
- Keep Bootstrap version current
- Update icons to latest version
- Monitor accessibility standards
- Review responsive breakpoints

### Performance Optimization
- Minify CSS and JavaScript
- Optimize images
- Use CDN for external resources
- Enable GZIP compression

## 🆘 Troubleshooting

### Common Issues

1. **Bootstrap styles not loading**
   - Check CDN links
   - Verify CSS file paths
   - Clear browser cache

2. **JavaScript errors**
   - Check console for errors
   - Verify jQuery is loaded
   - Ensure proper initialization

3. **Mobile layout issues**
   - Test responsive breakpoints
   - Check viewport meta tag
   - Verify touch interactions

4. **PHP includes not working**
   - Check file paths
   - Verify PHP syntax
   - Check server configuration

## 📞 Support

For questions or issues:
1. Check this documentation
2. Review code comments
3. Test with provided examples
4. Contact development team

## 📄 License

This project is licensed under the MIT License. See LICENSE file for details.

## 🙏 Credits

- Bootstrap Team for the excellent framework
- Bootstrap Icons for the icon set
- Educational design patterns inspiration from modern LMS platforms
- Community feedback and contributions

---

**Version**: 1.0.0  
**Last Updated**: November 2024  
**Compatible with**: Bootstrap 5.3.0+
