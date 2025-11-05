# PHP Session-Based Authentication System

A comprehensive, secure authentication system built with PHP, featuring role-based access control, CSRF protection, session security, and modern design.

## 🚀 Features

- **Secure Session Management** - Advanced session handling with fingerprinting and validation
- **Role-Based Access Control** - Admin, Teacher, and Student roles with appropriate permissions
- **CSRF Protection** - Token-based protection against cross-site request forgery attacks
- **Password Security** - Bcrypt hashing with strength validation
- **Session Security** - Timeout, fixation protection, and fingerprint validation
- **Rate Limiting** - Protection against brute force attacks
- **Activity Logging** - Comprehensive audit trail of user activities
- **Modern UI** - Responsive design with smooth animations
- **Database Optimization** - Indexed tables with automatic cleanup

## 📋 Requirements

- PHP 7.4 or higher
- MySQL 5.7 or higher (or MariaDB 10.3+)
- Web server (Apache/Nginx)
- SSL certificate (recommended for production)

## 🛠 Installation

### 1. Database Setup

```sql
-- Create database
CREATE DATABASE auth_system CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Import the schema
mysql -u username -p auth_system < database_auth_schema.sql
```

### 2. Configuration

Edit `includes/auth.php` and update database credentials:

```php
$db_config = [
    'host' => 'localhost',
    'dbname' => 'auth_system',
    'username' => 'your_username',
    'password' => 'your_password'
];
```

### 3. File Permissions

Ensure proper permissions for session files (Linux/macOS):

```bash
chmod 755 pages/
chmod 755 includes/
chmod 600 includes/auth.php  # Protect configuration file
```

### 4. Web Server Configuration

#### Apache (.htaccess)
```apache
RewriteEngine On

# Force HTTPS (recommended)
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]

# Prevent direct access to includes
<Files "*.php">
    Order allow,deny
    Deny from all
</Files>

# Allow only specific files in pages
<Directory "pages">
    <Files "*.php">
        Order allow,deny
        Allow from all
    </Files>
</Directory>
```

#### Nginx
```nginx
server {
    listen 443 ssl;
    server_name yourdomain.com;
    
    # SSL configuration
    ssl_certificate /path/to/cert.pem;
    ssl_certificate_key /path/to/key.pem;
    
    root /path/to/webroot;
    index index.php;
    
    # Block direct access to includes
    location ~ ^/includes/ {
        deny all;
        return 403;
    }
    
    # PHP processing
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php7.4-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
    
    # Force HTTPS
    location / {
        if ($scheme != "https") {
            return 301 https://$host$request_uri;
        }
    }
}
```

## 📁 File Structure

```
workspace/
├── pages/
│   ├── login.php          # Login interface
│   ├── register.php       # User registration
│   ├── logout.php         # Session termination
│   └── dashboard.php      # Role-based dashboard
├── includes/
│   ├── auth.php           # Core authentication functions
│   ├── session.php        # Session management
│   └── middleware.php     # Route protection middleware
├── database_auth_schema.sql # Complete database schema
├── config/
│   └── database.php       # Database configuration
└── assets/
    ├── css/               # Stylesheets
    └── js/                # JavaScript files
```

## 🔐 Default Accounts

After installation, you can use these default accounts:

| Role    | Username  | Password  | Email               |
|---------|-----------|-----------|---------------------|
| Admin   | admin     | admin123! | admin@example.com   |
| Teacher | teacher1  | admin123! | teacher@example.com |
| Student | student1  | admin123! | student@example.com |

**⚠️ IMPORTANT**: Change these default passwords immediately after first login!

## 🛡 Security Features

### Session Security
- **Session Fingerprinting**: Validates user agent and IP consistency
- **Session Timeout**: Automatic logout after 30 minutes of inactivity
- **Session Regeneration**: Prevents session fixation attacks
- **Secure Cookies**: HTTPOnly, Secure, and SameSite attributes

### Authentication Security
- **Password Hashing**: Bcrypt with automatic cost factor adjustment
- **Failed Attempt Tracking**: Account lockout after 5 failed attempts
- **CSRF Protection**: Token-based form protection
- **Rate Limiting**: Prevents brute force attacks

### Data Protection
- **SQL Injection Prevention**: Prepared statements everywhere
- **XSS Protection**: Input sanitization and output encoding
- **Activity Logging**: Comprehensive audit trail
- **Security Events**: Automated threat detection

## 🎯 Usage Examples

### Protecting a Page
```php
<?php
define('AUTH_SYSTEM', true);
require_once '../includes/auth.php';

// Require authentication
require_auth();

// Require specific role
require_role('admin');

// Your protected code here
?>
```

### Using Session Management
```php
<?php
require_once '../includes/session.php';

// Initialize secure session
initialize_secure_session();

// Set flash message
set_flash_message('success', 'Operation completed successfully!');

// Rate limiting
if (!check_rate_limit('login', 5, 300)) {
    die('Too many attempts. Please try again later.');
}
?>
```

### Middleware Protection
```php
<?php
// Add to top of any protected page
require_once '../includes/middleware.php';

// Middleware automatically handles:
// - Authentication checking
// - Session validation
// - Security headers
// - Rate limiting
?>
```

## 🔧 Customization

### Adding New Roles
1. Update the `role` enum in the users table:
   ```sql
   ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'teacher', 'student', 'moderator');
   ```

2. Update role checking in your code:
   ```php
   if (has_role('moderator')) {
       // Moderator-specific code
   }
   ```

### Custom Security Settings
Update system settings in the database or modify `includes/session.php`:

```php
// Session timeout (in seconds)
define('SESSION_TIMEOUT', 1800);

// Max login attempts
define('MAX_LOGIN_ATTEMPTS', 5);

// Rate limiting window (in seconds)
define('RATE_LIMIT_WINDOW', 300);
```

### Styling Customization
All styles are contained within each PHP file. Modify the CSS sections to match your brand:

```css
:root {
    --primary-color: #667eea;
    --secondary-color: #764ba2;
    --success-color: #27ae60;
    --error-color: #e74c3c;
}
```

## 📊 Monitoring

### Activity Logs
Monitor user activities through the `activity_logs` table:

```sql
-- View recent login activities
SELECT u.username, al.action, al.description, al.ip_address, al.created_at
FROM activity_logs al
JOIN users u ON al.user_id = u.id
WHERE al.action = 'login'
ORDER BY al.created_at DESC
LIMIT 10;
```

### Security Events
Track security incidents:

```sql
-- View security events by type
SELECT event_type, COUNT(*) as count, 
       AVG(CASE WHEN severity = 'critical' THEN 1 ELSE 0 END) as critical_rate
FROM security_events
WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
GROUP BY event_type;
```

### Session Statistics
Monitor active sessions:

```sql
-- Active sessions by role
SELECT role, COUNT(*) as active_sessions
FROM user_sessions s
JOIN users u ON s.user_id = u.id
WHERE s.is_active = 1 AND s.expires_at > NOW()
GROUP BY role;
```

## 🚨 Troubleshooting

### Common Issues

#### Session Not Working
- Check PHP session configuration
- Verify file permissions on session directory
- Ensure cookies are enabled in browser

#### Database Connection Failed
- Verify database credentials in `includes/auth.php`
- Check if MySQL service is running
- Ensure database exists and user has permissions

#### CSRF Token Mismatch
- Clear browser cookies and try again
- Check if session is properly initialized
- Verify no output before session_start()

#### Login Always Fails
- Check password hash in database
- Verify account is not locked
- Check activity logs for error details

### Debug Mode
Enable debug logging in `includes/auth.php`:

```php
// Add at the beginning of auth.php
define('DEBUG_MODE', true);
ini_set('display_errors', 1);
error_reporting(E_ALL);
```

## 🔄 Updates and Maintenance

### Regular Maintenance Tasks

1. **Clean up expired tokens** (automatic via cron job)
2. **Monitor security logs** for suspicious activity
3. **Review failed login attempts** regularly
4. **Update passwords** for default accounts
5. **Backup database** regularly

### Cron Job for Cleanup
Add to crontab for automatic cleanup:

```bash
# Clean up expired sessions and logs every hour
0 * * * * /usr/bin/php /path/to/workspace/pages/cleanup.php
```

## 📝 API Reference

### Authentication Functions

```php
// Check if user is logged in
bool is_logged_in()

// Get current user data
array get_current_user()

// Login user
array login($username, $password, $remember_me = false)

// Logout user
bool logout()

// Check user role
bool has_role($required_role)

// Require authentication
void require_auth($redirect_url = '/pages/login.php')

// Require specific role
void require_role($required_role, $redirect_url = '/pages/dashboard.php')
```

### Session Management

```php
// Initialize secure session
void initialize_secure_session()

// Validate session security
bool validate_session()

// Extend session
bool extend_session()

// Destroy session
void destroy_session()

// Generate CSRF token
string generate_csrf_token()

// Verify CSRF token
bool verify_csrf_token($token)
```

### Security Functions

```php
// Log user activity
bool log_activity($user_id, $action, $description)

// Check rate limiting
bool check_rate_limit($action, $max_attempts = 5, $time_window = 300)

// Generate password hash
string hash_password($password)

// Verify password
bool verify_password($password, $hash)
```

## 📄 License

This authentication system is provided as-is for educational and development purposes. Customize and use according to your project requirements.

## 🤝 Support

For issues and questions:
1. Check the troubleshooting section
2. Review the activity logs
3. Enable debug mode for detailed error information
4. Check the database connection and configuration

---

**Security Notice**: This system implements multiple security layers but should be regularly reviewed and updated to address new security threats. Always use HTTPS in production and keep your PHP version updated.