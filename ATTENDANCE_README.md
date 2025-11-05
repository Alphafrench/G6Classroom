# Attendance Management System

A comprehensive web-based attendance tracking system with real-time clock, check-in/check-out functionality, reporting, and analytics.

## 🚀 Features

### Core Functionality
- **Real-time Clock**: Live time display with automatic updates
- **Check-in/Check-out**: One-click attendance recording with AJAX
- **Time Calculations**: Automatic total hours calculation
- **Attendance Status**: Present, Late, Overtime, Incomplete status tracking
- **Employee-specific Views**: Personalized dashboards and reports

### Dashboard (`pages/attendance/index.php`)
- Real-time clock display
- Current attendance status with work duration timer
- Quick check-in/check-out buttons
- Today's, weekly, and monthly statistics
- Recent attendance records
- Quick action buttons

### Attendance Records (`pages/attendance/records.php`)
- Filterable attendance history
- Date range filtering
- Status filtering (present, absent, late, overtime, incomplete)
- Pagination support
- Bulk operations
- Export to CSV functionality

### Reports (`pages/attendance/report.php`)
- Individual employee attendance analytics
- Interactive charts and visualizations
- Performance insights and trends
- Export to PDF and Excel
- Weekly and monthly comparisons

### Real-time Features (`assets/js/attendance.js`)
- Live clock with millisecond precision
- Work duration timer while checked in
- AJAX-powered check-in/check-out
- Dynamic data updates
- Interactive charts using Chart.js

## 📁 File Structure

```
/
├── pages/attendance/
│   ├── index.php          # Main dashboard and check-in interface
│   ├── records.php        # Attendance history with filters
│   ├── report.php         # Individual employee reports
│   └── logout.php         # Session cleanup
├── api/attendance/
│   ├── checkin.php        # Check-in API endpoint
│   ├── checkout.php       # Check-out API endpoint
│   ├── recent.php         # Recent records API
│   ├── stats.php          # Statistics API
│   ├── details.php        # Record details API
│   └── export.php         # CSV export functionality
├── assets/
│   ├── js/
│   │   └── attendance.js  # Real-time functionality
│   └── css/
│       └── attendance.css # Styling and responsive design
└── database/
    └── schema.sql         # Complete database schema
```

## 🛠 Installation & Setup

### Prerequisites
- PHP 7.4+ with PDO MySQL extension
- MySQL 5.7+ or MariaDB 10.3+
- Web server (Apache/Nginx)
- Modern web browser with JavaScript enabled

### Step 1: Database Setup

1. **Create Database**:
   ```sql
   CREATE DATABASE attendance_system CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```

2. **Run Schema**:
   ```bash
   mysql -u username -p attendance_system < database/schema.sql
   ```

### Step 2: Configure Database Connection

Edit the following files to update database credentials:

**pages/attendance/index.php**:
```php
$host = 'localhost';
$dbname = 'attendance_system';
$username = 'your_username';
$password = 'your_password';
```

**api/attendance/*.php** files (similar configuration)

### Step 3: Web Server Setup

1. **For Apache**: Ensure mod_rewrite is enabled
2. **For Nginx**: Configure PHP-FPM and rewrite rules
3. **Permissions**: Ensure web server can read/write files

### Step 4: Authentication Setup

For production use, implement proper authentication:

1. Replace demo session data with actual user authentication
2. Add login/logout functionality
3. Implement role-based access control
4. Add password hashing and validation

## 🎯 Usage Guide

### For Employees

1. **Check-in**:
   - Navigate to the dashboard
   - Click "Check In" button
   - Real-time timer starts automatically

2. **Check-out**:
   - Click "Check Out" when leaving
   - Total hours are calculated automatically

3. **View Records**:
   - Use the "View Records" page for history
   - Apply filters for specific date ranges
   - Export data as needed

4. **Generate Reports**:
   - Use the "Reports" page for analytics
   - View performance insights
   - Export reports to PDF/Excel

### For Administrators

1. **Monitor Attendance**:
   - View employee dashboards
   - Check real-time status
   - Monitor attendance patterns

2. **Generate Reports**:
   - Access comprehensive analytics
   - Export data for external use
   - Track performance metrics

## 🔧 Customization

### Adding New Features

1. **Custom Status Types**: Update the `status` enum in database schema
2. **Additional Fields**: Modify attendance table structure
3. **New Reports**: Add new report types to `report.php`
4. **API Endpoints**: Create new files in `api/attendance/`

### Styling

The system uses CSS custom properties for easy theming:

```css
:root {
    --primary-color: #0d6efd;
    --success-color: #198754;
    --warning-color: #fd7e14;
    /* Modify colors to match your brand */
}
```

### JavaScript Configuration

Update `assets/js/attendance.js` settings:

```javascript
let currentEmployeeId = 1; // Set current employee ID
```

## 📊 Database Schema

### Main Tables

- **employees**: Employee information
- **attendance**: Check-in/out records
- **attendance_breaks**: Break time tracking
- **leave_requests**: Leave management
- **holidays**: Holiday calendar
- **settings**: System configuration

### Key Relationships

- `attendance.employee_id` → `employees.id`
- `attendance_breaks.attendance_id` → `attendance.id`
- `leave_requests.employee_id` → `employees.id`

## 🔌 API Endpoints

### Check-in
```
POST /api/attendance/checkin.php
Content-Type: application/json

{
    "employee_id": 1,
    "timestamp": "2025-11-05T14:30:00Z"
}
```

### Check-out
```
POST /api/attendance/checkout.php
Content-Type: application/json

{
    "employee_id": 1,
    "timestamp": "2025-11-05T17:30:00Z"
}
```

### Recent Records
```
GET /api/attendance/recent.php?employee_id=1&limit=5
```

### Statistics
```
GET /api/attendance/stats.php?employee_id=1
```

## 🎨 Features & Components

### Real-time Features
- **Live Clock**: Updates every second
- **Work Duration Timer**: Shows active work time
- **Status Updates**: Real-time attendance status
- **Dynamic Charts**: Interactive data visualizations

### Responsive Design
- Mobile-friendly interface
- Bootstrap 5 integration
- Touch-optimized controls
- Progressive web app ready

### Security Features
- SQL injection prevention (prepared statements)
- XSS protection (output sanitization)
- CSRF protection ready
- Session management

### Performance Optimizations
- AJAX for seamless interactions
- Efficient database queries with indexes
- Minimal JavaScript footprint
- Optimized CSS with custom properties

## 🔍 Troubleshooting

### Common Issues

1. **Database Connection Failed**:
   - Verify database credentials
   - Check MySQL service status
   - Ensure database exists

2. **JavaScript Errors**:
   - Check browser console for errors
   - Ensure Chart.js library loads correctly
   - Verify AJAX endpoints are accessible

3. **Time Zone Issues**:
   - Set correct timezone in PHP
   - Update JavaScript date handling
   - Check server time configuration

4. **Styling Problems**:
   - Clear browser cache
   - Check CSS file paths
   - Verify Bootstrap integration

### Debug Mode

Enable debug logging by adding to PHP files:

```php
ini_set('display_errors', 1);
ini_set('log_errors', 1);
error_reporting(E_ALL);
```

## 🚀 Production Deployment

### Security Checklist
- [ ] Change default database credentials
- [ ] Implement proper authentication
- [ ] Enable HTTPS
- [ ] Configure proper file permissions
- [ ] Set up regular database backups
- [ ] Implement rate limiting
- [ ] Add input validation
- [ ] Enable security headers

### Performance Checklist
- [ ] Enable query caching
- [ ] Optimize database indexes
- [ ] Configure web server caching
- [ ] Minimize CSS/JS files
- [ ] Set up CDN if needed
- [ ] Monitor server resources
- [ ] Implement error logging

### Backup Strategy
- [ ] Daily database backups
- [ ] File system backups
- [ ] Configuration backups
- [ ] Backup testing procedure
- [ ] Recovery documentation

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Add tests if applicable
5. Submit a pull request

## 📄 License

This project is licensed under the MIT License - see the LICENSE file for details.

## 🆘 Support

For support and questions:
- Check the troubleshooting section
- Review the API documentation
- Examine the code comments
- Create an issue in the repository

---

**Built with ❤️ using PHP, MySQL, JavaScript, and Bootstrap**