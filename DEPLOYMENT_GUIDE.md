# Employee Attendance System - Complete Deployment Guide

## Overview

This guide provides comprehensive deployment instructions for the Employee Attendance System, a PHP-based web application with features for employee management, course management, assignments, and attendance tracking.

## Prerequisites

Before deploying, ensure you have:

- **Web Server** (Apache/Nginx)
- **PHP 8.0+** with required extensions:
  - PDO
  - MySQLi
  - GD/LibJPEG
  - cURL
  - OpenSSL
  - mbstring
- **MySQL/MariaDB 5.7+** or **PostgreSQL**
- **Composer** (for PHP dependencies)
- **Git** (for version control)

## Quick Start Deployment

### Option 1: Local Development with Docker

1. **Clone the repository:**
   ```bash
   git clone <your-repository-url>
   cd employee-attendance-system
   ```

2. **Start with Docker:**
   ```bash
   docker-compose up -d
   ```

3. **Access the application:**
   - Web Interface: `http://localhost:8080`
   - phpMyAdmin: `http://localhost:8081`

### Option 2: Manual Installation

1. **Upload files to your web server**
2. **Configure database connection**
3. **Run the installer**

## Step-by-Step Deployment Instructions

### 1. File Upload

#### For Shared Hosting (cPanel/FTP):
1. Compress the project folder into a ZIP file
2. Upload and extract in your public_html directory
3. Set proper file permissions:
   ```bash
   chmod 755 -R ./
   chmod 777 uploads/
   chmod 777 database/
   ```

#### For VPS/Dedicated Server:
1. Use SFTP or SCP to upload files:
   ```bash
   scp -r ./employee-attendance-system/ user@server:/var/www/html/
   ```

2. Set ownership and permissions:
   ```bash
   chown -R www-data:www-data /var/www/html/employee-attendance-system/
   chmod 755 -R /var/www/html/employee-attendance-system/
   chmod 777 uploads/
   ```

### 2. Database Setup

#### MySQL Setup:
1. Create a new database:
   ```sql
   CREATE DATABASE employee_attendance;
   ```

2. Create a database user:
   ```sql
   CREATE USER 'attendance_user'@'localhost' IDENTIFIED BY 'secure_password';
   GRANT ALL PRIVILEGES ON employee_attendance.* TO 'attendance_user'@'localhost';
   FLUSH PRIVILEGES;
   ```

3. Import the database schema:
   ```bash
   mysql -u attendance_user -p employee_attendance < database/database_schema.sql
   mysql -u attendance_user -p employee_attendance < database/auth_schema.sql
   mysql -u attendance_user -p employee_attendance < sample_data.sql
   ```

### 3. Configuration

#### Environment Configuration:
1. **Database Configuration** (`config/database.php`):
   ```php
   <?php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'employee_attendance');
   define('DB_USER', 'attendance_user');
   define('DB_PASS', 'your_secure_password');
   define('DB_CHARSET', 'utf8mb4');
   ?>
   ```

2. **Application Settings** (`config/config.php`):
   ```php
   // Application Settings
   define('APP_NAME', 'Employee Attendance System');
   define('APP_URL', 'https://yourdomain.com');
   define('APP_VERSION', '1.0.0');
   define('APP_DEBUG', false); // Set to true for development

   // Security Settings
   define('SESSION_TIMEOUT', 3600); // 1 hour
   define('MAX_LOGIN_ATTEMPTS', 5);
   define('PASSWORD_MIN_LENGTH', 8);
   ```

#### Environment Variables (.env):
```env
# Database Configuration
DB_HOST=localhost
DB_NAME=employee_attendance
DB_USER=attendance_user
DB_PASS=your_secure_password

# Application Settings
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

# Security
SESSION_TIMEOUT=3600
MAX_LOGIN_ATTEMPTS=5

# File Upload
MAX_FILE_SIZE=5242880
ALLOWED_FILE_TYPES=pdf,doc,docx,jpg,jpeg,png

# Email Configuration (for notifications)
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
```

### 4. Web Server Configuration

#### Apache Configuration:
Create `.htaccess` file in the root directory:
```apache
# Enable Rewrite Engine
RewriteEngine On

# Security Headers
Header always set X-Content-Type-Options nosniff
Header always set X-Frame-Options DENY
Header always set X-XSS-Protection "1; mode=block"
Header always set Strict-Transport-Security "max-age=63072000; includeSubDomains; preload"
Header always set Content-Security-Policy "default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline';"

# Prevent access to sensitive files
<Files "*.php~">
    Order allow,deny
    Deny from all
</Files>

<Files ".env">
    Order allow,deny
    Deny from all
</Files>

# URL Rewriting for Clean URLs
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^([^/]+)/?$ index.php?page=$1 [L,QSA]

# Force HTTPS (Production)
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
```

#### Nginx Configuration:
```nginx
server {
    listen 80;
    server_name yourdomain.com;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    server_name yourdomain.com;
    root /var/www/html/employee-attendance-system;
    index index.php index.html;

    # SSL Configuration
    ssl_certificate /path/to/your/certificate.crt;
    ssl_certificate_key /path/to/your/private.key;

    # Security Headers
    add_header X-Content-Type-Options nosniff;
    add_header X-Frame-Options DENY;
    add_header X-XSS-Protection "1; mode=block";
    add_header Strict-Transport-Security "max-age=63072000; includeSubDomains; preload";
    add_header Content-Security-Policy "default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline';";

    # PHP Configuration
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.0-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    # Prevent access to sensitive files
    location ~ /\. {
        deny all;
    }
    
    location ~ ^/(config|includes|scripts)/ {
        deny all;
    }

    # File Upload Security
    location ~ \.(php|php5|phtml)$ {
        deny all;
    }
}
```

### 5. SSL Certificate Setup

#### Using Let's Encrypt (Free):
```bash
# Install Certbot
sudo apt-get install certbot python3-certbot-apache

# Obtain certificate
sudo certbot --apache -d yourdomain.com

# Auto-renewal
sudo crontab -e
# Add: 0 12 * * * /usr/bin/certbot renew --quiet
```

### 6. Final Setup Steps

1. **Run the installation script:**
   ```
   https://yourdomain.com/install.php
   ```

2. **Create admin user:**
   - Default credentials: `admin` / `admin123`
   - **IMPORTANT:** Change default password immediately

3. **Verify installation:**
   - Test all major functions
   - Check database connections
   - Verify file uploads work
   - Test authentication system

### 7. Post-Deployment Tasks

1. **Set up regular backups:**
   ```bash
   # Database backup script
   #!/bin/bash
   DATE=$(date +%Y%m%d_%H%M%S)
   mysqldump -u attendance_user -p employee_attendance > backup_$DATE.sql
   tar -czf files_backup_$DATE.tar.gz uploads/
   ```

2. **Configure monitoring:**
   - Set up error log monitoring
   - Configure uptime monitoring
   - Set up database performance monitoring

3. **Security hardening:**
   - Remove install.php after setup
   - Regular security updates
   - Monitor access logs

## Production Checklist

- [ ] SSL certificate installed and working
- [ ] Database configured with proper user permissions
- [ ] File permissions set correctly
- [ ] Security headers configured
- [ ] Error logging enabled
- [ ] Backup system configured
- [ ] Admin user password changed
- [ ] Application tested thoroughly
- [ ] Monitoring and alerts set up
- [ ] Performance optimized

## Support and Troubleshooting

For common issues and solutions, refer to:
- `troubleshooting.md` - Common deployment issues
- `hosting-options.md` - Hosting service configurations
- `performance-optimization.md` - Performance tuning

## Version Information

- **Application Version:** 1.0.0
- **PHP Requirement:** 8.0+
- **Database:** MySQL 5.7+ / PostgreSQL 9.6+
- **Framework:** Custom PHP with Bootstrap UI
- **Last Updated:** November 2025