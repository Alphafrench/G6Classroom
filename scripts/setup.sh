#!/bin/bash
# Google Classroom Clone - Environment Setup Script
# This script sets up the application environment for different deployment scenarios

set -e  # Exit on any error

# Color codes for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Configuration
APP_NAME="classroom-clone"
APP_DIR="/var/www/html/$APP_NAME"
BACKUP_DIR="/var/backups/$APP_NAME"
LOG_DIR="/var/log/$APP_NAME"

# Functions
log_info() {
    echo -e "${BLUE}[INFO]${NC} $1"
}

log_success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1"
}

log_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

log_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

check_environment() {
    log_info "Checking system environment..."
    
    # Check PHP version
    if ! command -v php &> /dev/null; then
        log_error "PHP is not installed"
        exit 1
    fi
    
    PHP_VERSION=$(php -r "echo PHP_VERSION;")
    REQUIRED_PHP="8.0"
    if [[ $(echo "$PHP_VERSION >= $REQUIRED_PHP" | bc -l) -eq 0 ]]; then
        log_error "PHP $REQUIRED_PHP or higher is required. Found: $PHP_VERSION"
        exit 1
    fi
    log_success "PHP version: $PHP_VERSION"
    
    # Check required extensions
    REQUIRED_EXTENSIONS=("pdo" "pdo_mysql" "openssl" "curl" "gd")
    for ext in "${REQUIRED_EXTENSIONS[@]}"; do
        if ! php -m | grep -q "^$ext$"; then
            log_error "PHP extension '$ext' is not installed"
            exit 1
        fi
    done
    log_success "All required PHP extensions are installed"
    
    # Check MySQL
    if ! command -v mysql &> /dev/null; then
        log_warning "MySQL client not found. Make sure MySQL/MariaDB is installed"
    fi
    
    # Check Apache
    if command -v apache2 &> /dev/null; then
        log_success "Apache web server found"
        if apache2 -M | grep -q rewrite; then
            log_success "mod_rewrite is enabled"
        else
            log_error "mod_rewrite is not enabled"
            exit 1
        fi
    fi
    
    # Check disk space (minimum 1GB)
    AVAILABLE_SPACE=$(df / | awk 'NR==2 {print $4}')
    if [[ $AVAILABLE_SPACE -lt 1048576 ]]; then  # 1GB in KB
        log_warning "Low disk space. Available: $(($AVAILABLE_SPACE/1024))MB"
    fi
}

create_directories() {
    log_info "Creating application directories..."
    
    # Create main application directory
    if [[ ! -d "$APP_DIR" ]]; then
        sudo mkdir -p "$APP_DIR"
        sudo chown www-data:www-data "$APP_DIR"
        log_success "Created application directory: $APP_DIR"
    fi
    
    # Create necessary subdirectories
    SUBDIRS=("uploads/avatars" "uploads/assignments" "uploads/resources" "uploads/temp" "logs" "backups" "cache")
    for dir in "${SUBDIRS[@]}"; do
        FULL_PATH="$APP_DIR/$dir"
        sudo mkdir -p "$FULL_PATH"
        sudo chown www-data:www-data "$FULL_PATH"
        sudo chmod 755 "$FULL_PATH"
    done
    
    # Make uploads writable
    sudo chmod 777 "$APP_DIR/uploads"
    
    log_success "Directory structure created"
}

setup_environment_file() {
    log_info "Setting up environment configuration..."
    
    ENV_FILE="$APP_DIR/.env"
    
    if [[ -f "$ENV_FILE" ]]; then
        log_warning "Environment file already exists. Backing up..."
        sudo cp "$ENV_FILE" "$ENV_FILE.backup.$(date +%Y%m%d_%H%M%S)"
    fi
    
    # Copy example file
    sudo cp "$APP_DIR/.env.example" "$ENV_FILE"
    sudo chown www-data:www-data "$ENV_FILE"
    sudo chmod 644 "$ENV_FILE"
    
    log_success "Environment file created: $ENV_FILE"
    log_warning "Please edit $ENV_FILE with your actual configuration"
}

setup_database() {
    log_info "Setting up database..."
    
    read -p "Enter MySQL root password: " -s MYSQL_ROOT_PASSWORD
    echo
    
    # Get database configuration from user
    read -p "Database host [localhost]: " DB_HOST
    DB_HOST=${DB_HOST:-localhost}
    
    read -p "Database name [$APP_NAME]: " DB_NAME
    DB_NAME=${DB_NAME:-$APP_NAME}
    
    read -p "Database username [classroom_user]: " DB_USER
    DB_USER=${DB_USER:-classroom_user}
    
    read -s -p "Database password: " DB_PASS
    echo
    
    # Create database
    log_info "Creating database..."
    mysql -h "$DB_HOST" -u root -p"$MYSQL_ROOT_PASSWORD" -e "
        CREATE DATABASE IF NOT EXISTS $DB_NAME CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
        CREATE USER IF NOT EXISTS '$DB_USER'@'$DB_HOST' IDENTIFIED BY '$DB_PASS';
        GRANT ALL PRIVILEGES ON $DB_NAME.* TO '$DB_USER'@'$DB_HOST';
        FLUSH PRIVILEGES;
    "
    
    # Import schema
    log_info "Importing database schema..."
    mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" < "$APP_DIR/database/classroom_schema.sql"
    
    # Update environment file with database settings
    sudo sed -i "s/DB_HOST=.*/DB_HOST=$DB_HOST/" "$ENV_FILE"
    sudo sed -i "s/DB_NAME=.*/DB_NAME=$DB_NAME/" "$ENV_FILE"
    sudo sed -i "s/DB_USER=.*/DB_USER=$DB_USER/" "$ENV_FILE"
    sudo sed -i "s/DB_PASS=.*/DB_PASS=$DB_PASS/" "$ENV_FILE"
    
    log_success "Database setup completed"
}

configure_apache() {
    log_info "Configuring Apache virtual host..."
    
    VHOST_FILE="/etc/apache2/sites-available/$APP_NAME.conf"
    
    # Get domain from user
    read -p "Enter your domain [classroom.local]: " DOMAIN
    DOMAIN=${DOMAIN:-classroom.local}
    
    # Create virtual host configuration
    sudo tee "$VHOST_FILE" > /dev/null <<EOF
<VirtualHost *:80>
    ServerName $DOMAIN
    DocumentRoot $APP_DIR
    
    <Directory $APP_DIR>
        AllowOverride All
        Require all granted
    </Directory>
    
    # Security headers
    Header always set X-Content-Type-Options nosniff
    Header always set X-Frame-Options SAMEORIGIN
    Header always set X-XSS-Protection "1; mode=block"
    
    # Log files
    ErrorLog \${APACHE_LOG_DIR}/${APP_NAME}_error.log
    CustomLog \${APACHE_LOG_DIR}/${APP_NAME}_access.log combined
    
    # PHP settings
    <IfModule mod_php.c>
        php_value upload_max_filesize 10M
        php_value post_max_size 10M
        php_value max_execution_time 60
        php_value memory_limit 256M
    </IfModule>
</VirtualHost>

# SSL virtual host (uncomment after getting SSL certificate)
# <VirtualHost *:443>
#     ServerName $DOMAIN
#     DocumentRoot $APP_DIR
#     
#     SSLEngine on
#     SSLCertificateFile /path/to/your/certificate.crt
#     SSLCertificateKeyFile /path/to/your/private.key
#     
#     <Directory $APP_DIR>
#         AllowOverride All
#         Require all granted
#     </Directory>
#     
#     Header always set Strict-Transport-Security "max-age=31536000"
#     
#     ErrorLog \${APACHE_LOG_DIR}/${APP_NAME}_ssl_error.log
#     CustomLog \${APACHE_LOG_DIR}/${APP_NAME}_ssl_access.log combined
# </VirtualHost>
EOF
    
    # Enable site and required modules
    sudo a2ensite "$APP_NAME"
    sudo a2enmod headers
    sudo a2enmod ssl
    sudo a2dissite 000-default
    
    # Test Apache configuration
    if sudo apache2ctl configtest; then
        log_success "Apache configuration is valid"
        read -p "Do you want to restart Apache now? (y/n): " -n 1 -r
        echo
        if [[ $REPLY =~ ^[Yy]$ ]]; then
            sudo systemctl restart apache2
            log_success "Apache restarted"
        fi
    else
        log_error "Apache configuration has errors"
    fi
    
    log_success "Apache virtual host configured: $VHOST_FILE"
}

setup_ssl() {
    log_info "SSL Certificate Setup"
    log_warning "This is optional but highly recommended for production"
    
    read -p "Do you want to set up SSL certificate? (y/n): " -n 1 -r
    echo
    
    if [[ $REPLY =~ ^[Yy]$ ]]; then
        # Check if Certbot is installed
        if ! command -v certbot &> /dev/null; then
            log_info "Installing Certbot..."
            sudo apt update
            sudo apt install -y certbot python3-certbot-apache
        fi
        
        read -p "Enter your email address for Let's Encrypt: " EMAIL
        read -p "Enter your domain: " DOMAIN
        
        sudo certbot --apache -d "$DOMAIN" --email "$EMAIL" --agree-tos --non-interactive
        log_success "SSL certificate installed and configured"
    fi
}

setup_permissions() {
    log_info "Setting up file permissions..."
    
    # Set ownership
    sudo chown -R www-data:www-data "$APP_DIR"
    
    # Set directory permissions
    sudo find "$APP_DIR" -type d -exec chmod 755 {} \;
    
    # Set file permissions
    sudo find "$APP_DIR" -type f -exec chmod 644 {} \;
    
    # Make specific files writable
    sudo chmod 777 "$APP_DIR/uploads"
    sudo chmod 777 "$APP_DIR/logs"
    sudo chmod 600 "$APP_DIR/.env"
    
    log_success "Permissions configured"
}

create_cron_jobs() {
    log_info "Setting up cron jobs..."
    
    # Backup cron job
    (crontab -l 2>/dev/null; echo "0 2 * * * $APP_DIR/scripts/backup.sh") | crontab -
    
    # Log cleanup cron job
    (crontab -l 2>/dev/null; echo "0 3 * * 0 find $APP_DIR/logs -name '*.log' -mtime +30 -delete") | crontab -
    
    log_success "Cron jobs configured"
}

setup_monitoring() {
    log_info "Setting up monitoring..."
    
    # Create monitoring script
    MONITOR_SCRIPT="$APP_DIR/scripts/monitor.sh"
    sudo mkdir -p "$APP_DIR/scripts"
    
    sudo tee "$MONITOR_SCRIPT" > /dev/null <<'EOF'
#!/bin/bash
# Application health monitoring script

APP_DIR="/var/www/html/classroom-clone"
LOG_FILE="$APP_DIR/logs/monitor.log"
ERROR_COUNT=0

# Check if application is responding
if ! curl -s -f http://localhost > /dev/null; then
    echo "$(date): Application not responding" >> "$LOG_FILE"
    ERROR_COUNT=$((ERROR_COUNT + 1))
fi

# Check database connection
if ! mysql -e "SELECT 1" > /dev/null 2>&1; then
    echo "$(date): Database connection failed" >> "$LOG_FILE"
    ERROR_COUNT=$((ERROR_COUNT + 1))
fi

# Check disk space
DISK_USAGE=$(df / | awk 'NR==2 {print $5}' | sed 's/%//')
if [ "$DISK_USAGE" -gt 90 ]; then
    echo "$(date): Disk usage high: $DISK_USAGE%" >> "$LOG_FILE"
    ERROR_COUNT=$((ERROR_COUNT + 1))
fi

if [ "$ERROR_COUNT" -gt 0 ]; then
    echo "$(date): $ERROR_COUNT issues detected" >> "$LOG_FILE"
fi
EOF
    
    sudo chmod +x "$MONITOR_SCRIPT"
    
    # Add to crontab (every 5 minutes)
    (crontab -l 2>/dev/null; echo "*/5 * * * * $MONITOR_SCRIPT") | crontab -
    
    log_success "Monitoring setup completed"
}

create_backup_script() {
    log_info "Creating backup script..."
    
    BACKUP_SCRIPT="$APP_DIR/scripts/backup.sh"
    sudo mkdir -p "$APP_DIR/scripts"
    
    sudo tee "$BACKUP_SCRIPT" > /dev/null <<'EOF'
#!/bin/bash
# Application backup script

APP_DIR="/var/www/html/classroom-clone"
BACKUP_DIR="/var/backups/classroom-clone"
DATE=$(date +%Y%m%d_%H%M%S)

# Create backup directory
mkdir -p "$BACKUP_DIR"

# Database backup
echo "Backing up database..."
source "$APP_DIR/.env"
mysqldump -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" > "$BACKUP_DIR/database_$DATE.sql"

# File backup
echo "Backing up files..."
tar -czf "$BACKUP_DIR/files_$DATE.tar.gz" "$APP_DIR/uploads" "$APP_DIR/.env"

# Keep only last 7 days of backups
find "$BACKUP_DIR" -name "*.sql" -mtime +7 -delete
find "$BACKUP_DIR" -name "*.tar.gz" -mtime +7 -delete

echo "Backup completed: $DATE"
EOF
    
    sudo chmod +x "$BACKUP_SCRIPT"
    log_success "Backup script created"
}

generate_security_keys() {
    log_info "Generating security keys..."
    
    # Generate JWT secret
    JWT_SECRET=$(openssl rand -hex 32)
    
    # Generate security salt
    SECURITY_SALT=$(openssl rand -hex 16)
    
    # Update environment file
    sudo sed -i "s/JWT_SECRET=.*/JWT_SECRET=$JWT_SECRET/" "$ENV_FILE"
    sudo sed -i "s/SECURITY_SALT=.*/SECURITY_SALT=$SECURITY_SALT/" "$ENV_FILE"
    
    log_success "Security keys generated and configured"
}

test_installation() {
    log_info "Testing installation..."
    
    # Test database connection
    if php -r "
        require_once '$APP_DIR/config/config.php';
        try {
            \$db = Database::getInstance();
            echo \$db->testConnection() ? 'Database: OK\n' : 'Database: FAILED\n';
        } catch (Exception \$e) {
            echo 'Database: FAILED - ' . \$e->getMessage() . '\n';
        }
    "; then
        log_success "Database connection test passed"
    else
        log_error "Database connection test failed"
    fi
    
    # Test file permissions
    if [[ -w "$APP_DIR/uploads" ]]; then
        log_success "File upload permissions: OK"
    else
        log_error "File upload permissions: FAILED"
    fi
    
    # Test Apache configuration
    if apache2ctl -t &> /dev/null; then
        log_success "Apache configuration: OK"
    else
        log_error "Apache configuration: FAILED"
    fi
    
    log_info "Installation test completed"
}

print_completion_message() {
    echo
    echo "==========================================="
    echo -e "${GREEN}Installation Completed Successfully!${NC}"
    echo "==========================================="
    echo
    echo "Application URL: http://$DOMAIN"
    echo "Application Directory: $APP_DIR"
    echo "Log Directory: $LOG_DIR"
    echo "Backup Directory: $BACKUP_DIR"
    echo
    echo "Default Login Credentials:"
    echo "Admin: admin@classroom.com / admin123"
    echo "Teacher: teacher1@school.com / teacher123"
    echo "Student: student1@school.com / student123"
    echo
    echo "⚠️  IMPORTANT:"
    echo "1. Change all default passwords immediately"
    echo "2. Edit $ENV_FILE with your settings"
    echo "3. Review security settings in Apache config"
    echo "4. Set up SSL certificate for production"
    echo "5. Configure email settings in .env file"
    echo
    echo "Next Steps:"
    echo "1. Visit the application URL"
    echo "2. Login with admin credentials"
    echo "3. Create your first class"
    echo "4. Add students and teachers"
    echo "5. Start creating assignments"
    echo
    echo "For support: https://github.com/your-repo/classroom-clone"
    echo "==========================================="
}

# Main installation process
main() {
    echo "==========================================="
    echo "Google Classroom Clone - Setup Script"
    echo "==========================================="
    echo
    
    check_environment
    create_directories
    setup_environment_file
    generate_security_keys
    
    read -p "Do you want to set up the database? (y/n): " -n 1 -r
    echo
    if [[ $REPLY =~ ^[Yy]$ ]]; then
        setup_database
    fi
    
    read -p "Do you want to configure Apache virtual host? (y/n): " -n 1 -r
    echo
    if [[ $REPLY =~ ^[Yy]$ ]]; then
        configure_apache
        setup_ssl
    fi
    
    setup_permissions
    create_backup_script
    create_cron_jobs
    setup_monitoring
    
    test_installation
    print_completion_message
}

# Run main function
if [[ "${BASH_SOURCE[0]}" == "${0}" ]]; then
    main "$@"
fi
