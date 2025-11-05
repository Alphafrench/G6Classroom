# Employee Management System - CRUD Module

A complete employee management system with Create, Read, Update, and Delete functionality built with PHP, MySQL, Bootstrap, and jQuery.

## Features

- ✅ **Complete CRUD Operations** - Create, Read, Update, Delete employees
- ✅ **Search & Filter** - Search employees by name, email, position, or department
- ✅ **Pagination** - Handle large datasets with pagination
- ✅ **Form Validation** - Client-side and server-side validation
- ✅ **Responsive Design** - Mobile-friendly Bootstrap interface
- ✅ **DataTables Integration** - Advanced table features with sorting and filtering
- ✅ **Security Features** - CSRF protection, input sanitization
- ✅ **Professional UI** - Clean, modern interface with icons and styling

## File Structure

```
/
├── config/
│   ├── config.php          # Application configuration
│   └── database.php        # Database connection and Employee class
├── pages/
│   └── employees/
│       ├── header.php      # Common header with navigation
│       ├── footer.php      # Common footer with JavaScript
│       ├── index.php       # Employee listing (Read)
│       ├── add.php         # Add new employee (Create)
│       ├── edit.php        # Edit employee (Update)
│       ├── view.php        # Employee details view
│       └── delete.php      # Delete confirmation (Delete)
├── database_schema.sql     # Database schema with sample data
└── README.md              # This file
```

## Installation

### 1. Database Setup

1. Create a MySQL database named `attendance_system` (or update the database name in `config/database.php`)
2. Import the `database_schema.sql` file which includes:
   - User authentication tables (for the existing system)
   - Employees table for the CRUD module
   - Sample data with 10 employees
   - Database views and functions

```sql
-- Import the database schema
mysql -u root -p < database_schema.sql
```

### 2. Update Database Configuration

Edit `config/database.php` with your database credentials:

```php
private $host = 'localhost';           // Your database host
private $dbname = 'attendance_system'; // Your database name
private $username = 'root';            // Your database username
private $password = '';                // Your database password
```

### 3. File Permissions

Ensure proper file permissions for web server:

```bash
chmod 755 pages/
chmod 644 pages/employees/*.php
```

### 4. Web Server Setup

Place the files in your web server directory and access:

```
http://yourdomain.com/pages/employees/index.php
```

## Usage Guide

### Employee List (index.php)

- **View all employees** in a paginated table
- **Search functionality** - Search by name, email, position, or department
- **Filter by department** - Filter employees by specific department
- **Quick actions** - View, Edit, or Delete buttons for each employee
- **Employee status badges** - Shows New, Active, or Veteran status based on tenure

### Add Employee (add.php)

**Personal Information:**
- First Name (required)
- Last Name (required)
- Email (required, validated for uniqueness)
- Phone (required, with format validation)
- Address (optional)

**Employment Information:**
- Position/Job Title (required)
- Department (required, dropdown)
- Hire Date (required, date picker)
- Annual Salary (required, numeric validation)

**Emergency Contact:**
- Emergency Contact Name (optional)
- Emergency Contact Phone (optional)

**Additional Notes:**
- Free text notes about the employee

### Edit Employee (edit.php)

- Pre-populated form with current employee data
- Same validation as Add form
- Email uniqueness check excludes current employee
- Shows employee statistics and tenure information

### View Employee (view.php)

**Employee Profile:**
- Large avatar with initials
- Contact information with clickable links
- Employment details and compensation
- Emergency contact information
- Record creation and modification dates

**Quick Actions:**
- Send email directly
- Call employee or emergency contact
- Edit profile or return to list

**Summary Statistics:**
- Days of service
- Annual salary
- Department and position information

### Delete Employee (delete.php)

- **Confirmation required** - Multiple confirmation steps
- **Preview employee data** - Shows all employee information before deletion
- **Safety warnings** - Clear warnings about permanent deletion
- **Alternative actions** - Suggests editing instead of deletion

## API Reference

### Employee Class Methods

#### `create(array $data): bool`
Create a new employee record.

**Parameters:**
```php
$data = [
    'first_name' => 'John',
    'last_name' => 'Doe',
    'email' => 'john.doe@company.com',
    'phone' => '(555) 123-4567',
    'position' => 'Software Engineer',
    'department' => 'Engineering',
    'hire_date' => '2023-01-15',
    'salary' => 75000.00,
    'address' => '123 Main St',
    'emergency_contact' => 'Jane Doe',
    'emergency_phone' => '(555) 123-4568',
    'notes' => 'Great employee'
];
```

#### `readOne(int $id): array|false`
Get a single employee by ID.

#### `readAll(array $params): array`
Get all employees with search and pagination.

**Parameters:**
```php
$params = [
    'search' => 'john',           // Optional search term
    'department' => 'Engineering', // Optional department filter
    'page' => 1,                  // Page number (default: 1)
    'limit' => 25                 // Records per page (default: RECORDS_PER_PAGE)
]
```

**Returns:**
```php
[
    'data' => [...],              // Array of employee records
    'total' => 100,               // Total number of records
    'pages' => 4,                 // Total number of pages
    'current_page' => 1           // Current page number
]
```

#### `update(int $id, array $data): bool`
Update an existing employee.

#### `delete(int $id): bool`
Delete an employee by ID.

#### `getDepartments(): array`
Get all unique departments.

#### `emailExists(string $email, int $excludeId = null): bool`
Check if email already exists.

## Database Schema

### Employees Table

| Column | Type | Description |
|--------|------|-------------|
| id | INT AUTO_INCREMENT | Primary key |
| first_name | VARCHAR(100) | Employee first name |
| last_name | VARCHAR(100) | Employee last name |
| email | VARCHAR(255) UNIQUE | Employee email (unique) |
| phone | VARCHAR(20) | Phone number |
| position | VARCHAR(100) | Job title/position |
| department | VARCHAR(100) | Department name |
| hire_date | DATE | Date of hire |
| salary | DECIMAL(10,2) | Annual salary |
| address | TEXT | Employee address |
| emergency_contact | VARCHAR(200) | Emergency contact name |
| emergency_phone | VARCHAR(20) | Emergency contact phone |
| notes | TEXT | Additional notes |
| created_at | TIMESTAMP | Record creation date |
| updated_at | TIMESTAMP | Last modification date |

### Indexes

- `idx_email` - For email uniqueness checks
- `idx_department` - For department filtering
- `idx_position` - For position-based queries
- `idx_hire_date` - For date-based filtering
- `idx_full_name` - For name search
- `idx_search` - Multi-column search index

### Views

#### `employee_stats`
Department-wise statistics including employee count, average salary, and hire trends.

#### `employees_with_service`
Employee data with calculated years of service and status badges.

## Security Features

### CSRF Protection
- All forms include CSRF tokens
- Tokens validated on form submission

### Input Sanitization
- All user input is sanitized using `sanitizeInput()` function
- HTML special characters are escaped
- SQL injection protection through prepared statements

### Validation
- Client-side validation with real-time feedback
- Server-side validation for all form submissions
- Email format validation
- Phone number format validation
- Numeric validation for salary and IDs
- Date validation for hire dates

### Database Security
- Prepared statements for all database queries
- No dynamic SQL construction
- Proper parameter binding

## Customization

### Adding New Fields

1. **Database**: Add column to `employees` table
2. **Employee Class**: Update `create()`, `update()`, and property mapping
3. **Forms**: Add form fields to `add.php` and `edit.php`
4. **Display**: Update `view.php` and `index.php` to show new field

### Styling Customization

Edit the CSS in `header.php` or create separate CSS files:

```css
/* Custom employee avatar styling */
.employee-avatar {
    background: linear-gradient(45deg, #667eea, #764ba2);
}

/* Custom badge colors */
.status-new { background-color: #28a745; }
.status-active { background-color: #17a2b8; }
.status-veteran { background-color: #6f42c1; }
```

### Adding Search Fields

Update the search form in `index.php` and modify the `readAll()` method to include new search parameters.

## Browser Compatibility

- **Modern Browsers**: Chrome, Firefox, Safari, Edge (latest versions)
- **Responsive**: Bootstrap 5 responsive design
- **JavaScript**: Requires JavaScript enabled for full functionality
- **DataTables**: Requires jQuery 3.6+ and DataTables 1.13+

## Performance Optimization

### Database Optimization
- Indexed columns for common queries
- Efficient pagination with LIMIT/OFFSET
- Prepared statements for better performance

### Frontend Optimization
- DataTables for efficient table rendering
- Bootstrap 5 for responsive design
- CDN resources for faster loading
- Minified CSS/JS in production

## Troubleshooting

### Common Issues

**Database Connection Failed:**
- Check database credentials in `config/database.php`
- Ensure MySQL service is running
- Verify database exists

**Employee Not Found:**
- Check employee ID parameter in URL
- Verify employee exists in database

**Form Validation Errors:**
- Check for required field validation
- Verify email format and uniqueness
- Check phone number format

**CSS/JavaScript Not Loading:**
- Check CDN resources are accessible
- Verify Bootstrap 5 and jQuery are loaded
- Check browser console for errors

### Debug Mode

Enable error reporting in `config/config.php`:

```php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
```

## Support

For issues or questions:

1. Check the troubleshooting section above
2. Review the database schema and Employee class methods
3. Test with the provided sample data
4. Check browser console for JavaScript errors
5. Enable debug mode for detailed error messages

## License

This employee CRUD module is provided as-is for educational and commercial use. Modify and distribute as needed for your project requirements.

## Version History

- **v1.0.0** - Initial release with full CRUD functionality
- Complete employee management system
- Search, filter, and pagination
- Responsive design with Bootstrap 5
- Security features with CSRF protection
- Form validation and error handling
- Professional UI with DataTables integration