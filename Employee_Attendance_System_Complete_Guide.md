# Employee Attendance Management System: Complete Guide

## Introduction

This document provides a comprehensive guide to the Employee Attendance Management System. It covers all aspects of the system, from user-level functionality to developer-focused documentation. This guide is intended for end-users (employees and administrators), developers, and system administrators responsible for deploying and maintaining the application.

The Employee Attendance Management System is a web-based application designed to streamline and automate employee attendance tracking. It provides a user-friendly interface for employees to check in and out, and a powerful dashboard for administrators to manage employees, users, and system settings, as well as to generate insightful reports.

## 1. User Manual

This section provides a detailed guide for end-users of the Employee Attendance Management System. It is divided into two parts: a guide for employees and a guide for administrators.

### 1.1. Employee Guide

This guide covers the essential features available to all employees.

#### 1.1.1. Logging In

To access the system, you need to log in with your credentials (username/email and password).

1.  Open your web browser and navigate to the login page (e.g., `http://your_company.com/login.php`).
2.  Enter your username or email address in the **Username or Email** field.
3.  Enter your password in the **Password** field.
4.  If you want the system to remember you for 30 days, check the **Remember me for 30 days** checkbox.
5.  Click the **Login** button.

*![Screenshot: Login Page](placeholder_login_page.png)*

If the login is successful, you will be redirected to your dashboard. If you enter incorrect credentials, an error message will be displayed.

#### 1.1.2. The Employee Dashboard

After logging in, you will see your personal dashboard. The dashboard provides an overview of your attendance status and quick access to various features.

*![Screenshot: Employee Dashboard](placeholder_employee_dashboard.png)*

The dashboard includes:

*   **Real-time Clock**: Displays the current time and date.
*   **Today's Attendance Status**: Shows whether you are currently checked in or out. If you are checked in, it displays the time you checked in and the duration of your current work session.
*   **Check-in/Check-out buttons**: Allows you to check in or out with a single click.
*   **Quick Stats**: Provides a summary of your work hours for today, this week, and this month.
*   **Recent Attendance Records**: A list of your most recent attendance records.
*   **Quick Actions**: Buttons to quickly navigate to other pages like `View Records` or the main `Dashboard`.

#### 1.1.3. Checking In and Out

The primary function for employees is to check in when they start working and check out when they finish.

**To Check In:**

1.  On your dashboard, click the green **Check In** button.
2.  The system will record your check-in time, and your status on the dashboard will update to "Checked In".

**To Check Out:**

1.  When you are ready to end your work session, click the red **Check Out** button on your dashboard.
2.  The system will record your check-out time and calculate the total hours worked for that session.

#### 1.1.4. Viewing Attendance Records

You can view your complete attendance history in the `Records` section.

1.  From the dashboard, click on the **View All Records** button or use the navigation menu to go to the `My Records` page.
2.  You will see a table with your attendance history, including check-in/out times, total hours, and status for each day.
3.  You can filter your records by date to view a specific period.

*![Screenshot: Attendance Records Page](placeholder_attendance_records.png)*

### 1.2. Administrator Guide

This guide is for users with administrative privileges. Administrators have access to all employee features, plus additional function for managing users, employees, system settings, and viewing comprehensive reports.

#### 1.2.1. Administrator Dashboard

The administrator dashboard provides a high-level overview of the system and quick access to administrative functions. In addition to the standard employee dashboard features, administrators have access to a sidebar with links to:

*   User Management
*   Employee Management
*   System Settings
*   Reports

*![Screenshot: Admin Dashboard](placeholder_admin_dashboard.png)*

#### 1.2.2. User Management

Administrators can manage the users who have access to the system.

1.  Navigate to the **User Management** page from the admin sidebar.
2.  Here you can:
    *   **View all users**: A list of all users with their roles and status.
    *   **Add a new user**: Click the **Add User** button to open a modal where you can enter the new user's details (username, email, password, role).
    *   **Edit a user**: Click the **Edit** button next to a user to modify their information.
    *   **Reset a user's password**: Click the **Reset Password** button to set a new password for a user.
    *   **Delete a user**: Click the **Delete** button to remove a user from the system.

*![Screenshot: User Management Page](placeholder_user_management.png)*

#### 1.2.3. Employee Management

Administrators can perform full CRUD (Create, Read, Update, Delete) operations on employee records.

1.  Navigate to the **Employee Management** page from the admin sidebar.

*![Screenshot: Employee List Page](placeholder_employee_list.png)*

*   **View Employees**: The main page displays a list of all employees with their contact information, position, department, and hire date. You can search for employees or filter the list by department.
*   **Add a New Employee**: Click the **Add New Employee** button to go to a form where you can enter the new employee's details.
*   **View Employee Details**: Click on an employee's name or the **View** button to see their detailed profile.
*   **Edit an Employee**: Click the **Edit** button to modify an employee's record.
*   **Delete an Employee**: Click the **Delete** button to remove an employee. A confirmation will be required.

#### 1.2.4. System Settings

Administrators can configure various system-wide settings.

1.  Navigate to the **System Settings** page from the admin sidebar.

*![Screenshot: System Settings Page](placeholder_system_settings.png)*

Here you can configure:

*   **Company Information**: Company name, address, email, and phone.
*   **Working Hours**: Set the default start and end times for the workday.
*   **Notifications**: Enable or disable email and SMS notifications.
*   **Localization**: Set the timezone, date, and time formats.
*   **Security**: Configure session timeout and backup frequency.
*   **System Tools**: Clear the system cache or send a test email to verify email configuration.

#### 1.2.5. Viewing Reports

Administrators have access to a comprehensive reports dashboard with analytics and visualizations.

1.  Navigate to the **Reports** page from the admin sidebar.

*![Screenshot: Reports Dashboard](placeholder_reports_dashboard.png)*

The reports dashboard includes:

*   **Filters**: You can filter the report data by date range and department.
*   **Summary Cards**: High-level statistics such as total attendance records, active departments, and average daily attendance.
*   **Charts**: Visual representations of the data, including:
    *   **Daily Attendance Trend**: A line chart showing attendance over the selected period.
    *   **Department Distribution**: A doughnut chart showing the distribution of attendance by department.
    *   **Hourly Distribution**: A bar chart showing the peak hours of attendance.
*   **Detailed Records**: A table with detailed attendance records for the selected period.
*   **CSV Export**: You can export the filtered report data to a CSV file for further analysis.

## 2. Developer Documentation

This section provides technical documentation for developers working on the Employee Attendance Management System. It covers the code structure, database schema, and API endpoints.

### 2.1. Code Structure

The application is organized into the following directory structure:

```
/
├── api/                  # API endpoints for AJAX requests
│   └── attendance/         # Attendance-related API files
├── assets/               # CSS, JavaScript, and image files
│   ├── css/              # Stylesheets
│   ├── js/               # JavaScript files
│   └── images/           # Image assets
├── config/               # Configuration files
│   └── database.php      # Database connection and main classes
├── includes/             # Core PHP files and classes
│   ├── auth.php          # Authentication functions
│   ├── functions.php     # Core utility functions
│   ├── class.Database.php # Database class
│   ├── class.Employee.php # Employee class
│   ├── class.Attendance.php# Attendance class
│   └── class.Session.php # Session class
├── pages/                # User-facing pages
│   ├── login.php         # Login page
│   ├── dashboard.php     # Main dashboard
│   ├── admin/            # Admin-specific pages
│   ├── employees/        # Employee CRUD pages
│   └── attendance/       # Attendance-related pages
├── database.sql          # The complete database schema
├── README.md               # Project overview and setup instructions
└── ...                   # Other files
```

**Key Directories:**

*   **`/api`**: Contains all the backend API endpoints that the frontend JavaScript communicates with. This is where the business logic for AJAX requests resides.
*   **`/assets`**: Holds all the static assets like CSS for styling, JavaScript for frontend interactivity, and images.
*   **`/config`**: For application configuration. `database.php` is a crucial file here, containing the database connection details and the main `Database` and `Employee` classes.
*   **`/includes`**: A vital directory containing the core PHP logic of the application. This includes classes for database interaction (`class.Database.php`), employee management (`class.Employee.php`), attendance tracking (`class.Attendance.php`), and session handling (`class.Session.php`), as well as general utility functions (`functions.php`) and authentication logic (`auth.php`).
*   **`/pages`**: Contains the user interface files. These are the PHP files that render the HTML that the user sees in their browser.

### 2.2. Database Schema

The database schema is the backbone of the application. It is designed to be robust and scalable, with clear relationships between tables. The complete schema is defined in the `database.sql` file.

#### Tables

The following are the main tables in the database:

**`departments`**

Stores department information.

| Column | Type | Description |
|---|---|---|
| `id` | INT | Primary Key |
| `name` | VARCHAR(100) | Department name (unique) |
| `description` | TEXT | Department description |

**`users`**

Stores information about the users who can log in to the system.

| Column | Type | Description |
|---|---|---|
| `id` | INT | Primary Key |
| `username` | VARCHAR(50) | Unique username for login |
| `email` | VARCHAR(100) | User's email address (unique) |
| `password_hash` | VARCHAR(255) | Hashed password |
| `first_name` | VARCHAR(50) | User's first name |
| `last_name` | VARCHAR(50) | User's last name |
| `role` | ENUM('admin', 'manager', 'hr', 'employee') | User's role in the system |
| `status` | ENUM('active', 'inactive', 'suspended') | Account status |

**`employees`**

This is the master table for employee data.

| Column | Type | Description |
|---|---|---|
| `id` | INT | Primary Key |
| `employee_code` | VARCHAR(20) | Unique employee identifier |
| `user_id` | INT | Foreign key to the `users` table (optional) |
| `department_id` | INT | Foreign key to the `departments` table |
| `first_name` | VARCHAR(50) | Employee's first name |
| `last_name` | VARCHAR(50) | Employee's last name |
| `email` | VARCHAR(100) | Employee's email address (unique) |
| `phone` | VARCHAR(20) | Employee's phone number |
| `hire_date` | DATE | Date of hiring |
| ... | ... | Other employee details... |

**`attendance`**

This table stores the daily attendance records for each employee.

| Column | Type | Description |
|---|---|---|
| `id` | INT | Primary Key |
| `employee_id` | INT | Foreign key to the `employees` table |
| `attendance_date` | DATE | The date of the attendance record |
| `clock_in_time` | TIMESTAMP | The time the employee clocked in |
| `clock_out_time` | TIMESTAMP | The time the employee clocked out |
| `total_hours` | DECIMAL(4,2) | Total hours worked |
| `attendance_status` | ENUM('present', 'absent', 'late', ...) | The status of the attendance for the day |

**`leave_requests`**

Manages employee leave requests.

| Column | Type | Description |
|---|---|---|
| `id` | INT | Primary Key |
| `employee_id` | INT | Foreign key to the `employees` table |
| `leave_type` | ENUM('vacation', 'sick', ...) | The type of leave requested |
| `start_date` | DATE | Leave start date |
| `end_date` | DATE | Leave end date |
| `status` | ENUM('pending', 'approved', 'rejected', 'cancelled') | The status of the leave request |

**`audit_logs`**

This table provides an audit trail for important changes in the system.

| Column | Type | Description |
|---|---|---|
| `id` | INT | Primary Key |
| `table_name` | VARCHAR(50) | The name of the table that was modified |
| `record_id` | INT | The ID of the record that was modified |
| `action` | ENUM('INSERT', 'UPDATE', 'DELETE') | The action performed |
| `changed_by` | INT | The user ID of the user who made the change |
| `changed_at` | TIMESTAMP | When the change was made |

#### Views

The database also includes views for common queries:

*   **`v_employee_summary`**: Provides a summary of employee information, joining the `employees` and `departments` tables.
*   **`v_today_attendance`**: Shows the attendance records for the current day.

#### Stored Procedures

Stored procedures are used for common database operations:

*   **`CalculateEmployeeAttendance`**: Calculates attendance statistics for a given employee and date range.
*   **`ClockIn`** and **`ClockOut`**: Procedures to handle the clock-in and clock-out operations.

### 2.3. API Endpoints

The system uses a set of API endpoints to handle asynchronous requests from the frontend, primarily for attendance tracking and data retrieval. These endpoints are located in the `/api/attendance/` directory.

#### Attendance API

**1. Check-in**

*   **Endpoint**: `/api/attendance/checkin.php`
*   **Method**: `POST`
*   **Description**: Records an employee's check-in time.
*   **Request Body**:
    ```json
    {
        "employee_id": 1,
        "timestamp": "2025-11-05T09:00:00Z"
    }
    ```

**2. Check-out**

*   **Endpoint**: `/api/attendance/checkout.php`
*   **Method**: `POST`
*   **Description**: Records an employee's check-out time and calculates the total hours worked.
*   **Request Body**:
    ```json
    {
        "employee_id": 1,
        "timestamp": "2025-11-05T17:30:00Z"
    }
    ```

**3. Get Recent Records**

*   **Endpoint**: `/api/attendance/recent.php`
*   **Method**: `GET`
*   **Description**: Retrieves a list of recent attendance records for a given employee.
*   **Query Parameters**:
    *   `employee_id` (int, required): The ID of the employee.
    *   `limit` (int, optional): The number of records to return (default: 5).

**4. Get Attendance Statistics**

*   **Endpoint**: `/api/attendance/stats.php`
*   **Method**: `GET`
*   **Description**: Retrieves attendance statistics for an employee (e.g., total hours for the week/month).
*   **Query Parameters**:
    *   `employee_id` (int, required): The ID of the employee.

**5. Export Attendance Data**

*   **Endpoint**: `/api/attendance/export.php`
*   **Method**: `GET`
*   **Description**: Exports attendance records to a CSV file.
*   **Query Parameters**:
    *   `start_date` (string, required): The start date for the export (format: YYYY-MM-DD).
    *   `end_date` (string, required): The end date for the export (format: YYYY-MM-DD).
    *   `department` (string, optional): The department to filter by.

## 3. Installation Guide

This section provides detailed instructions for setting up the Employee Attendance Management System in a production environment.

### 3.1. Prerequisites

Before you begin, ensure your server meets the following requirements:

*   **Web Server**: Apache or Nginx
*   **PHP**: Version 7.4 or higher, with the `PDO_MySQL` extension enabled.
*   **Database**: MySQL 5.7+ or MariaDB 10.3+
*   **Browser**: A modern web browser with JavaScript enabled.

### 3.2. Step 1: Database Setup

1.  **Create the Database**: Log in to your MySQL server and create a new database. You can do this with the following SQL command:

    ```sql
    CREATE DATABASE employee_attendance_system CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
    ```

2.  **Import the Schema**: Import the provided `database.sql` file into your new database. This file contains the complete schema for all the necessary tables, views, and stored procedures.

    ```bash
    mysql -u your_username -p employee_attendance_system < database.sql
    ```

    Replace `your_username` with your MySQL username. You will be prompted for your password.

### 3.3. Step 2: Configure the Application

1.  **Upload Files**: Upload all the project files to your web server's document root (e.g., `/var/www/html`).

2.  **Configure Database Connection**: You need to update the database connection settings in the configuration file. Open `config/database.php` and modify the following lines with your database credentials:

    ```php
    private $host = 'localhost';
    private $dbname = 'employee_attendance_system';
    private a$username = 'your_username';
    private $password = 'your_password';
    ```

### 3.4. Step 3: Deployment

1.  **Web Server Configuration**:
    *   **Apache**: Ensure that `mod_rewrite` is enabled.
    *   **Nginx**: Configure your server block to handle PHP files with PHP-FPM.

2.  **File Permissions**: Ensure that the web server has the correct permissions to read the application files. The `uploads/` and `logs/` directories (if you create them) should be writable by the web server.

3.  **Access the Application**: Open your web browser and navigate to the application's URL (e.g., `http://yourdomain.com/pages/login.php`).

### 3.5. Default Login Credentials

After a fresh installation, you can use the following default credentials to log in:

*   **Administrator**:
    *   **Username**: `admin`
    *   **Password**: `admin123`

*   **Employee**:
    *   **Username**: `employee`
    *   **Password**: `employee123`

**Important**: It is strongly recommended that you change these default passwords immediately after your first login.

## 4. Testing Guide

This section provides a guide for testing the Employee Attendance Management System to ensure its functionality, security, and usability.

### 4.1. Functional Testing

**1. Authentication**

*   [ ] **Test Case 1.1**: Valid Login: Log in with correct admin credentials. You should be redirected to the admin dashboard.
*   [ ] **Test Case 1.2**: Valid Login: Log in with correct employee credentials. You should be redirected to the employee dashboard.
*   [ ] **Test Case 1.3**: Invalid Login: Attempt to log in with an incorrect password. An error message should be displayed.
*   [ ] **Test Case 1.4**: Invalid Login: Attempt to log in with a non-existent username. An error message should be displayed.
*   [ ] **Test Case 1.5**: Logout: Log in and then log out. You should be redirected to the login page.

**2. Attendance Tracking (Employee)**

*   [ ] **Test Case 2.1**: Check-in: Log in as an employee and click the "Check In" button. The status should change to "Checked In" and the timer should start.
*   [ ] **Test Case 2.2**: Check-out: After checking in, click the "Check Out" button. The status should change to "Checked Out" and the total hours should be calculated.
*   [ ] **Test Case 2.3**: View Records: Check the `My Records` page to see if the new attendance record is listed.

**3. Employee Management (Admin)**

*   [ ] **Test Case 3.1**: Add Employee: Log in as an admin and create a new employee. The new employee should appear in the employee list.
*   [ ] **Test Case 3.2**: Edit Employee: Edit the details of an existing employee. The changes should be saved and reflected in the employee list.
*   [ ] **Test Case 3.3**: Search Employee: Use the search bar to find an employee by name or email.
*   [ ] **Test Case 3.4**: Delete Employee: Delete an employee. A confirmation should be required, and the employee should be removed from the list.

**4. User Management (Admin)**

*   [ ] **Test Case 4.1**: Add User: Create a new user with the `employee` role.
*   [ ] **Test Case 4.2**: Test New User: Log out and log in with the new user's credentials to ensure they have the correct permissions.
*   [ ] **Test Case 4.3**: Delete User: Delete the newly created user.

### 4.2. Security Testing

*   [ ] **Test Case 5.1**: Role-Based Access Control: Log in as an employee and try to access an admin-only page (e.g., `/pages/admin/users.php`). You should be denied access.
*   [ ] **Test Case 5.2**: SQL Injection: Attempt to inject SQL commands into input fields like the search bar or login form.
*   [ ] **Test Case 5.3**: Cross-Site Scripting (XSS): Attempt to inject JavaScript code into input fields (e.g., in the employee notes). The input should be sanitized and the script should not execute.
*   [ ] **Test Case 5.4**: CSRF Protection: Use browser developer tools to inspect the forms and ensure they contain a hidden CSRF token.

### 4.3. UI/UX Testing

*   [ ] **Test Case 6.1**: Responsiveness: Test the application on different screen sizes (desktop, tablet, mobile) to ensure the layout adapts correctly.
*   [ ] **Test Case 6.2**: Browser Compatibility: Test the application on different web browsers (e.g., Chrome, Firefox, Safari, Edge).
*   [ ] **Test Case 6.3**: Usability: Navigate through the application as both an employee and an admin to ensure the user flow is intuitive and easy to understand.

## 5. Security Documentation

This section outlines the security features implemented in the Employee Attendance Management System and provides best practices for maintaining a secure environment.

### 5.1. Authentication and Session Management

*   **Secure Login**: The system uses a secure login mechanism that validates users against credentials stored in the database. It includes protection against brute-force attacks by locking accounts after multiple failed login attempts.
*   **Password Hashing**: User passwords are not stored in plaintext. They are hashed using PHP's `password_hash()` function, which uses the robust bcrypt algorithm.
*   **Secure Sessions**: The system uses a secure, session-based authentication mechanism. Sessions are configured with `HttpOnly` and `Secure` flags to prevent access from JavaScript and to ensure they are only transmitted over HTTPS.
*   **Session Regeneration**: The session ID is regenerated upon login to prevent session fixation attacks.
*   **Session Timeout**: Sessions automatically time out after a period of inactivity, requiring the user to log in again.

### 5.2. Input Sanitization and Validation

*   **Input Sanitization**: All user-supplied data is sanitized before being used in the application. The `sanitizeInput()` function (from `includes/functions.php`) is used to remove HTML tags and other potentially malicious code.
*   **Output Encoding**: All data rendered in the HTML is properly escaped using `htmlspecialchars()` to prevent Cross-Site Scripting (XSS) attacks.
*   **Data Validation**: The system performs strict data validation on both the client-side and server-side to ensure that only valid data is processed.

### 5.3. SQL Injection Prevention

All database queries are executed using **prepared statements** with PDO (PHP Data Objects). This means that user input is separated from the SQL query, making the application highly resistant to SQL injection attacks.

### 5.4. Cross-Site Request Forgery (CSRF) Protection

All sensitive forms in the application (like login, user creation, and settings changes) are protected against CSRF attacks. The system generates a unique CSRF token for each user session and includes it as a hidden field in forms. This token is validated on the server-side before any action is performed.

### 5.5. Role-Based Access Control (RBAC)

The system implements a role-based access control mechanism to ensure that users can only access the features and data appropriate for their role.

*   **Roles**: The system has predefined roles such as `employee` and `admin`.
*   **Access Control**: Before rendering any page or performing any action, the system checks if the user is logged in and has the required role.

### 5.6. Security Best Practices

**For Developers:**

*   **Always use HTTPS** in production to encrypt all communication between the browser and the server.
*   **Keep all dependencies up to date** to protect against known vulnerabilities.
*   **Follow the principle of least privilege**: Grant users the minimum permissions they need to perform their tasks.

**For Administrators:**

*   **Change default passwords** immediately after installation.
*   **Enforce strong password policies** for all users.
*   **Regularly review audit logs** for any suspicious activity.
*   **Keep the server environment secure** with regular security patches and a firewall.
