# Deployment Guide - Classroom Management System

This guide covers deploying the Classroom Management System to various hosting environments, from development to production.

## Table of Contents

- [Prerequisites](#prerequisites)
- [Environment Setup](#environment-setup)
- [Local Development](#local-development)
- [Docker Deployment](#docker-deployment)
- [Shared Hosting](#shared-hosting)
- [VPS/Dedicated Server](#vpsdedicated-server)
- [Cloud Deployment](#cloud-deployment)
- [Production Checklist](#production-checklist)
- [Monitoring and Maintenance](#monitoring-and-maintenance)

## Prerequisites

### System Requirements

- **PHP**: 8.2 or higher
- **MySQL**: 8.0 or higher (or MariaDB 10.3+)
- **Web Server**: Apache 2.4+ or Nginx 1.18+
- **Disk Space**: Minimum 2GB free space
- **RAM**: Minimum 1GB available memory
- **SSL Certificate**: For production environments

### Required PHP Extensions

```bash
# Check required extensions
php -m | grep -E "(pdo|pdo_mysql|mysqlnd|gd|curl|openssl|mbstring|xml|zip)"
```

Required extensions:
- `pdo` - Database connections
- `pdo_mysql` - MySQL driver
- `mysqlnd` - MySQL native driver
- `gd` - Image processing
- `curl` - HTTP requests
- `openssl` - SSL/TLS
- `mbstring` - Multibyte string handling
- `xml` - XML processing
- `zip` - Archive handling

## Environment Setup

### Development Environment

```bash
# Clone repository
git clone https://github.com/your-username/classroom-management.git
cd classroom-management

# Install dependencies
composer install

# Setup environment
cp config/environment.example.php config/environment.php

# Edit configuration
nano config/environment.php
```

### Production Environment Variables

```env
# .env file for production
APP_ENV=production
DEBUG_MODE=false
LOG_LEVEL=error

# Database Configuration
DB_HOST=localhost
DB_NAME=classroom_management
DB_USER=prod_user
DB_PASS=secure_password_here
DB_CHARSET=utf8mb4

# Security Settings
JWT_SECRET=your_jwt_secret_key_here
SECURITY_SALT=your_security_salt_here
SESSION_TIMEOUT=3600

# Mail Configuration
SMTP_HOST=smtp.yourdomain.com
SMTP_PORT=587
SMTP_USERNAME=noreply@yourdomain.com
SMTP_PASSWORD=your_smtp_password
SMTP_ENCRYPTION=tls

# File Storage
UPLOAD_PATH=/var/www/classroom/uploads
MAX_FILE_SIZE=10485760
ALLOWED_FILE_TYPES=pdf,doc,docx,txt,jpg,jpeg,png,gif

# Cache Settings
CACHE_ENABLED=true
CACHE_DRIVER=redis
REDIS_HOST=localhost
REDIS_PORT=6379
```

## Local Development

### Using PHP Built-in Server

```bash
# Start development server
php -S localhost:8080 -t .

# Access application
open http://localhost:8080
```

### Using XAMPP/WAMP/LAMP

1. **Install XAMPP** (or similar stack)
2. **Configure virtual host**
   ```apache
   # httpd-vhosts.conf
   <VirtualHost *:80>
       ServerName classroom.local
       DocumentRoot "/path/to/classroom-management"
       
       <Directory "/path/to/classroom-management">
           AllowOverride All
           Require all granted
       </Directory>
       
       ErrorLog "logs/classroom-error.log"
       CustomLog "logs/classroom-access.log" common
   </VirtualHost>
   ```

3. **Update hosts file**
   ```bash
   # /etc/hosts (Linux/Mac) or C:\Windows\System32\drivers\etc\hosts (Windows)
   127.0.0.1 classroom.local
   ```

## Docker Deployment

### Quick Start with Docker Compose

```yaml
# docker-compose.yml
version: '3.8'

services:
  app:
    build: .
    ports:
      - "8080:80"
    depends_on:
      - db
      - redis
    environment:
      DB_HOST: db
      DB_NAME: classroom_management
      DB_USER: root
      DB_PASS: secret
      REDIS_HOST: redis
    volumes:
      - ./uploads:/var/www/html/uploads
      - ./logs:/var/www/html/logs

  db:
    image: mysql:8.0
    environment:
      MYSQL_ROOT_PASSWORD: secret
      MYSQL_DATABASE: classroom_management
    volumes:
      - mysql_data:/var/lib/mysql
      - ./database/schema.sql:/docker-entrypoint-initdb.d/schema.sql
    ports:
      - "3306:3306"

  redis:
    image: redis:alpine
    ports:
      - "6379:6379"

volumes:
  mysql_data:
```

### Production Docker Setup

```bash
# Build production image
docker build -t classroom-management:latest .

# Run with environment file
docker run -d \
  --name classroom-app \
  --env-file .env.production \
  -p 443:443 \
  -v $(pwd)/uploads:/var/www/html/uploads \
  -v $(pwd)/logs:/var/www/html/logs \
  classroom-management:latest
```

### Docker Optimization

```dockerfile
# Multi-stage build for production
FROM php:8.2-apache AS builder

# Install dependencies
COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-scripts

FROM php:8.2-apache AS production

# Copy application
COPY --from=builder /var/www/html/vendor /var/www/html/vendor
COPY . /var/www/html/

# Configure Apache
RUN a2enmod rewrite ssl headers
RUN a2enconf php8.2

# Set permissions
RUN chown -R www-data:www-data /var/www/html/uploads
RUN chown -R www-data:www-data /var/www/html/logs

# Enable SSL
COPY docker/apache-ssl.conf /etc/apache2/sites-available/default-ssl.conf
RUN a2ensite default-ssl

EXPOSE 443

CMD ["apache2-foreground"]
```

## Shared Hosting

### cPanel Hosting

1. **Upload Files**
   ```bash
   # Using FTP/SFTP
   # Upload all files to public_html or www directory
   ```

2. **Create Database**
   - Use cPanel MySQL Databases
   - Create database and user
   - Note credentials

3. **Import Database**
   ```bash
   # Through phpMyAdmin or command line
   mysql -u username -p database_name < database/schema.sql
   ```

4. **Configure Application**
   ```php
   // config/environment.php
   return [
       'database' => [
           'host' => 'localhost',
           'database' => 'cpanel_username_classroom',
           'username' => 'cpanel_username_user',
           'password' => 'secure_password'
       ],
       'app' => [
           'url' => 'https://yourdomain.com'
       ]
   ];
   ```

5. **Set Permissions**
   ```bash
   # Set uploads directory writable
   chmod 755 uploads/
   chmod 777 uploads/  # If required by hosting provider
   ```

### File Permissions for Shared Hosting

```bash
# Typical permissions for shared hosting
find . -type d -exec chmod 755 {} \;
find . -type f -exec chmod 644 {} \;
chmod 777 uploads/
chmod 777 logs/
```

## VPS/Dedicated Server

### Ubuntu/Debian Server Setup

```bash
# Update system
sudo apt update && sudo apt upgrade -y

# Install Apache
sudo apt install apache2 -y
sudo systemctl enable apache2

# Install PHP 8.2
sudo apt install software-properties-common -y
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update
sudo apt install php8.2 php8.2-cli php8.2-fpm php8.2-mysql php8.2-curl php8.2-gd php8.2-mbstring php8.2-xml php8.2-zip php8.2-intl -y

# Install MySQL
sudo apt install mysql-server -y
sudo mysql_secure_installation

# Install Redis
sudo apt install redis-server -y
sudo systemctl enable redis-server

# Install Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
```

### Apache Virtual Host Configuration

```apache
# /etc/apache2/sites-available/classroom.conf
<VirtualHost *:80>
    ServerName classroom.yourdomain.com
    DocumentRoot /var/www/classroom/public
    
    <Directory /var/www/classroom/public>
        AllowOverride All
        Require all granted
    </Directory>
    
    # Security headers
    Header always set X-Content-Type-Options nosniff
    Header always set X-Frame-Options DENY
    Header always set X-XSS-Protection "1; mode=block"
    Header always set Strict-Transport-Security "max-age=63072000; includeSubDomains; preload"
    Header always set Content-Security-Policy "default-src 'self'; script-src 'self' 'unsafe-inline' cdn.jsdelivr.net; style-src 'self' 'unsafe-inline' cdn.jsdelivr.net; img-src 'self' data: https:; font-src 'self' cdn.jsdelivr.net"
    
    # Logging
    ErrorLog ${APACHE_LOG_DIR}/classroom_error.log
    CustomLog ${APACHE_LOG_DIR}/classroom_access.log combined
</VirtualHost>

# SSL Configuration
<VirtualHost *:443>
    ServerName classroom.yourdomain.com
    DocumentRoot /var/www/classroom/public
    
    SSLEngine on
    SSLCertificateFile /etc/ssl/certs/classroom.crt
    SSLCertificateKeyFile /etc/ssl/private/classroom.key
    SSLCertificateChainFile /etc/ssl/certs/classroom-chain.crt
    
    <Directory /var/www/classroom/public>
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog ${APACHE_LOG_DIR}/classroom_ssl_error.log
    CustomLog ${APACHE_LOG_DIR}/classroom_ssl_access.log combined
</VirtualHost>
```

### Nginx Configuration

```nginx
# /etc/nginx/sites-available/classroom
server {
    listen 80;
    server_name classroom.yourdomain.com;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    server_name classroom.yourdomain.com;
    root /var/www/classroom/public;
    index index.php index.html;
    
    # SSL Configuration
    ssl_certificate /etc/ssl/certs/classroom.crt;
    ssl_certificate_key /etc/ssl/private/classroom.key;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers ECDHE-RSA-AES256-GCM-SHA512:DHE-RSA-AES256-GCM-SHA512:ECDHE-RSA-AES256-GCM-SHA384:DHE-RSA-AES256-GCM-SHA384;
    
    # Security Headers
    add_header X-Frame-Options DENY;
    add_header X-Content-Type-Options nosniff;
    add_header X-XSS-Protection "1; mode=block";
    add_header Strict-Transport-Security "max-age=63072000; includeSubDomains; preload";
    
    # Gzip Compression
    gzip on;
    gzip_vary on;
    gzip_min_length 1024;
    gzip_proxied expired no-cache no-store private must-revalidate;
    gzip_types
        text/plain
        text/css
        text/xml
        text/javascript
        application/javascript
        application/xml+rss
        application/json;
    
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }
    
    # Block access to sensitive files
    location ~ /\. {
        deny all;
    }
    
    location ~ \.(env|log|sql)$ {
        deny all;
    }
}
```

### Deploy Application

```bash
# Create application directory
sudo mkdir -p /var/www/classroom
sudo chown $USER:www-data /var/www/classroom

# Clone repository
git clone https://github.com/your-username/classroom-management.git /var/www/classroom

# Install dependencies
cd /var/www/classroom
composer install --no-dev --optimize-autoloader

# Setup configuration
cp config/environment.example.php config/environment.php
nano config/environment.php

# Create uploads and logs directories
mkdir -p uploads logs
chmod 755 uploads logs
chown -R www-data:www-data uploads logs

# Import database
mysql -u root -p classroom_management < database/schema.sql

# Enable site
sudo a2ensite classroom
sudo systemctl reload apache2
```

## Cloud Deployment

### AWS EC2 Deployment

1. **Launch EC2 Instance**
   ```bash
   # Choose Ubuntu Server 22.04 LTS
   # Instance type: t3.medium or larger
   # Configure security group (HTTP, HTTPS, SSH)
   ```

2. **Setup Instance**
   ```bash
   # Follow VPS/Dedicated Server setup steps
   # Additionally, setup RDS for MySQL
   ```

3. **RDS MySQL Setup**
   ```bash
   # Create RDS MySQL 8.0 instance
   # Configure security group
   # Update application database configuration
   ```

4. **Setup Load Balancer (Optional)**
   ```bash
   # Create Application Load Balancer
   # Configure SSL certificate
   # Attach target group
   ```

### Google Cloud Platform

1. **Create Compute Engine Instance**
   ```bash
   gcloud compute instances create classroom-app \
       --image-family=ubuntu-2204-lts \
       --image-project=ubuntu-os-cloud \
       --machine-type=e2-medium \
       --zone=us-central1-a
   ```

2. **Setup Cloud SQL**
   ```bash
   gcloud sql instances create classroom-db \
       --database-version=MYSQL_8_0 \
       --tier=db-f1-micro \
       --zone=us-central1-a
   ```

3. **Configure Cloud Storage**
   ```bash
   gsutil mb gs://classroom-uploads
   gsutil iam ch allUsers:objectViewer gs://classroom-uploads
   ```

### DigitalOcean Droplet

1. **Create Droplet**
   ```bash
   # Choose Ubuntu 22.04
   # Size: 2GB RAM minimum
   # Add SSH keys
   ```

2. **Setup with Cloud-Init**
   ```yaml
   # cloud-config.yaml
   packages:
     - apache2
     - php8.2
     - php8.2-mysql
     - php8.2-curl
     - php8.2-gd
     - php8.2-mbstring
     - php8.2-xml
     - mysql-server
   
   runcmd:
     - [git, clone, https://github.com/your-username/classroom-management.git, /var/www/html]
     - [chown, -R, www-data:www-data, /var/www/html/uploads]
   ```

## Production Checklist

### Security Checklist

- [ ] **SSL Certificate**: Install and configure SSL/TLS
- [ ] **Firewall**: Configure server firewall (UFW/iptables)
- [ ] **SSH Keys**: Disable password authentication, use SSH keys only
- [ ] **Regular Updates**: Enable automatic security updates
- [ ] **File Permissions**: Set proper file and directory permissions
- [ ] **Security Headers**: Implement all recommended security headers
- [ ] **Database Security**: Use strong passwords, limit user privileges
- [ ] **Backup Strategy**: Implement automated backup system
- [ ] **Monitoring**: Setup security monitoring and alerting

### Performance Checklist

- [ ] **Database Optimization**: Add proper indexes, optimize queries
- [ ] **Caching**: Implement Redis/Memcached for session and data caching
- [ ] **CDN**: Use CDN for static assets
- [ ] **Image Optimization**: Optimize images and enable WebP support
- [ ] **Compression**: Enable Gzip/Brotli compression
- [ ] **HTTP/2**: Enable HTTP/2 on web server
- [ ] **PHP Optimization**: Configure PHP OPcache
- [ ] **Database Connection Pooling**: Enable persistent connections

### Application Checklist

- [ ] **Environment Configuration**: Set production environment variables
- [ ] **Error Handling**: Disable debug mode, implement proper error handling
- [ ] **Logging**: Setup structured logging with log rotation
- [ ] **Monitoring**: Implement application performance monitoring
- [ ] **Backup**: Configure automated database and file backups
- [ ] **Testing**: Run all tests in production environment
- [ ] **Documentation**: Update deployment documentation

### Pre-Deployment Commands

```bash
# Security audit
composer audit

# Optimize autoloader
composer dump-autoload --optimize --classmap-authoritative

# Clear cache
php -r "opcache_reset();"

# Set permissions
find /var/www/classroom -type d -exec chmod 755 {} \;
find /var/www/classroom -type f -exec chmod 644 {} \;
chmod -R 777 /var/www/classroom/uploads
chmod -R 777 /var/www/classroom/logs

# Secure sensitive files
chmod 600 config/environment.php

# Test configuration
php -l index.php
php -l config/environment.php
```

## Monitoring and Maintenance

### Log Management

```bash
# Setup log rotation
sudo nano /etc/logrotate.d/classroom

/var/www/classroom/logs/*.log {
    daily
    missingok
    rotate 52
    compress
    delaycompress
    notifempty
    create 644 www-data www-data
    postrotate
        systemctl reload apache2
    endscript
}
```

### Health Checks

```bash
# Create health check script
#!/bin/bash
# health-check.sh

# Check web server
curl -f http://localhost/health || exit 1

# Check database connection
mysql -u $DB_USER -p$DB_PASS -e "SELECT 1" $DB_NAME || exit 1

# Check disk space
df /var/www/classroom | awk 'NR==2 {if($5>90) exit 1}'

# Check memory usage
free | awk 'NR==2{printf "%.0f", $3*100/$2 }' | awk '{if($1>90) exit 1}'

echo "All health checks passed"
```

### Automated Backups

```bash
#!/bin/bash
# backup.sh

BACKUP_DIR="/var/backups/classroom"
DATE=$(date +%Y%m%d_%H%M%S)

# Create backup directory
mkdir -p $BACKUP_DIR

# Database backup
mysqldump -u $DB_USER -p$DB_PASS $DB_NAME | gzip > $BACKUP_DIR/db_$DATE.sql.gz

# File backup
tar -czf $BACKUP_DIR/files_$DATE.tar.gz /var/www/classroom/uploads

# Remove backups older than 30 days
find $BACKUP_DIR -name "*.gz" -mtime +30 -delete

echo "Backup completed: $DATE"
```

### Cron Jobs

```bash
# Add to crontab (crontab -e)

# Daily backup at 2 AM
0 2 * * * /path/to/backup.sh >> /var/log/backup.log 2>&1

# Weekly log cleanup
0 0 * * 0 /usr/sbin/logrotate /etc/logrotate.d/classroom

# Health check every 5 minutes
*/5 * * * * /path/to/health-check.sh || echo "Health check failed" | mail -s "Classroom Health Alert" admin@yourdomain.com
```

### Monitoring Tools

1. **Server Monitoring**
   ```bash
   # Install monitoring tools
   sudo apt install htop iotop nethogs
   
   # Setup monit for process monitoring
   sudo apt install monit
   ```

2. **Application Monitoring**
   ```php
   // Add to your application
   // monitoring/metrics.php
   
   $metrics = [
       'response_time' => microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'],
       'memory_usage' => memory_get_usage(true),
       'peak_memory' => memory_get_peak_usage(true)
   ];
   
   // Log or send to monitoring service
   error_log(json_encode($metrics));
   ```

### Performance Optimization

```bash
# Enable PHP OPcache
# Edit /etc/php/8.2/apache2/php.ini
opcache.enable=1
opcache.memory_consumption=256
opcache.interned_strings_buffer=12
opcache.max_accelerated_files=10000
opcache.revalidate_freq=60
opcache.fast_shutdown=1

# Enable Apache mod_deflate
sudo a2enmod deflate
sudo systemctl reload apache2

# Setup Redis caching
# Edit /etc/redis/redis.conf
maxmemory 256mb
maxmemory-policy allkeys-lru
```

### SSL Certificate Renewal

```bash
# For Let's Encrypt
sudo apt install certbot python3-certbot-apache
sudo certbot --apache -d classroom.yourdomain.com

# Auto-renewal (already configured)
sudo crontab -e
# Add: 0 12 * * * /usr/bin/certbot renew --quiet
```

### Disaster Recovery

1. **Backup Strategy**
   - Daily database backups
   - Weekly full application backups
   - Offsite backup storage
   - Regular restoration testing

2. **Recovery Plan**
   ```bash
   # Recovery script example
   #!/bin/bash
   
   BACKUP_FILE=$1
   RESTORE_DIR="/var/www/classroom-restore"
   
   echo "Stopping services..."
   sudo systemctl stop apache2
   
   echo "Creating current backup..."
   cp -r /var/www/classroom /var/www/classroom-backup-$(date +%Y%m%d)
   
   echo "Extracting backup..."
   tar -xzf $BACKUP_FILE -C /var/www/
   
   echo "Restoring permissions..."
   sudo chown -R www-data:www-data $RESTORE_DIR
   
   echo "Starting services..."
   sudo systemctl start apache2
   
   echo "Recovery completed"
   ```

---

## Support

For deployment issues:
- Check logs in `/var/www/classroom/logs/`
- Review web server error logs
- Verify database connectivity
- Test with debug mode enabled (development only)

**Emergency Contact**: admin@yourdomain.com