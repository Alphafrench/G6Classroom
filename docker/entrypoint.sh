#!/bin/bash
# Google Classroom Clone - Docker Entry Point Script
# Handles initialization and startup tasks

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

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

# Wait for database
wait_for_db() {
    log_info "Waiting for database connection..."
    
    # Skip if no database connection configured
    if [[ -z "${DB_HOST:-}" ]] || [[ -z "${DB_NAME:-}" ]]; then
        log_warning "Database configuration not found, skipping database check"
        return 0
    fi
    
    local max_attempts=30
    local attempt=1
    
    while [ $attempt -le $max_attempts ]; do
        if mysql -h "${DB_HOST}" -u "${DB_USER:-root}" -p"${DB_PASS:-}" -e "SELECT 1" "${DB_NAME}" &> /dev/null; then
            log_success "Database connection established"
            return 0
        fi
        
        log_info "Attempt $attempt/$max_attempts - waiting for database..."
        sleep 2
        attempt=$((attempt + 1))
    done
    
    log_error "Database connection failed after $max_attempts attempts"
    return 1
}

# Initialize application
init_app() {
    log_info "Initializing application..."
    
    # Check if .env file exists
    if [[ ! -f ".env" ]]; then
        if [[ -f ".env.example" ]]; then
            log_info "Creating .env file from example..."
            cp .env.example .env
            log_warning "Please update .env file with your configuration"
        else
            log_error ".env file not found"
            return 1
        fi
    fi
    
    # Set file permissions
    log_info "Setting file permissions..."
    chown -R www-data:www-data /var/www/html
    chmod -R 755 /var/www/html
    chmod -R 777 /var/www/html/uploads 2>/dev/null || true
    chmod -R 777 /var/www/html/logs 2>/dev/null || true
    chmod -R 777 /var/www/html/cache 2>/dev/null || true
    chmod 644 /var/www/html/.env
    
    # Initialize database if needed
    if [[ "${INIT_DB:-false}" == "true" ]] && [[ -f "database/classroom_schema.sql" ]]; then
        log_info "Initializing database..."
        wait_for_db
        mysql -h "${DB_HOST}" -u "${DB_USER:-root}" -p"${DB_PASS:-}" "${DB_NAME}" < database/classroom_schema.sql || {
            log_error "Database initialization failed"
            return 1
        }
        log_success "Database initialized"
    fi
    
    # Generate security keys if not present
    if [[ -n "${GENERATE_KEYS:-true}" ]]; then
        log_info "Generating security keys..."
        
        # Generate JWT secret
        if ! grep -q "JWT_SECRET=.*" .env; then
            JWT_SECRET=$(openssl rand -hex 32)
            sed -i "s/# JWT_SECRET=.*/JWT_SECRET=$JWT_SECRET/" .env
        fi
        
        # Generate security salt
        if ! grep -q "SECURITY_SALT=.*" .env; then
            SECURITY_SALT=$(openssl rand -hex 16)
            sed -i "s/# SECURITY_SALT=.*/SECURITY_SALT=$SECURITY_SALT/" .env
        fi
        
        log_success "Security keys generated"
    fi
    
    # Create sessions directory
    mkdir -p /var/www/html/sessions
    chown www-data:www-data /var/www/html/sessions
    chmod 700 /var/www/html/sessions
    
    # Test configuration
    log_info "Testing application configuration..."
    php -r "
        require_once 'config/config.php';
        echo 'Configuration loaded successfully\n';
        echo 'Environment: ' . APP_ENV . '\n';
        echo 'Debug Mode: ' . (DEBUG_MODE ? 'Enabled' : 'Disabled') . '\n';
    " || {
        log_error "Configuration test failed"
        return 1
    }
    
    log_success "Application initialization completed"
}

# Start services
start_services() {
    log_info "Starting services..."
    
    # Create log directory
    mkdir -p /var/www/html/logs
    chown www-data:www-data /var/www/html/logs
    
    # Set proper permissions for Apache
    chown -R www-data:www-data /var/www/html
    chmod -R 755 /var/www/html
    
    # Check if Apache configuration is valid
    if ! apache2ctl configtest; then
        log_error "Apache configuration is invalid"
        return 1
    fi
    
    log_success "Services ready"
}

# Health check
health_check() {
    local max_attempts=5
    local attempt=1
    
    while [ $attempt -le $max_attempts ]; do
        if curl -sf http://localhost/ > /dev/null 2>&1; then
            log_success "Application is healthy and responding"
            return 0
        fi
        
        log_info "Health check attempt $attempt/$max_attempts"
        sleep 2
        attempt=$((attempt + 1))
    done
    
    log_error "Health check failed"
    return 1
}

# Main execution
main() {
    echo "=========================================="
    echo "Google Classroom Clone - Container Startup"
    echo "=========================================="
    echo "Environment: ${APP_ENV:-production}"
    echo "PHP Version: $(php -v | head -n1)"
    echo "Apache Version: $(apache2 -v | head -n1)"
    echo "=========================================="
    echo
    
    # Run initialization steps
    init_app || {
        log_error "Application initialization failed"
        exit 1
    }
    
    # Wait for database if configured
    if [[ -n "${DB_HOST:-}" ]]; then
        wait_for_db || {
            log_warning "Database not available - application may not function properly"
        }
    fi
    
    # Start services
    start_services
    
    # Health check
    health_check || {
        log_warning "Health check failed but continuing..."
    }
    
    log_success "Container startup completed successfully!"
    echo
    echo "Default login credentials:"
    echo "- Admin: admin@classroom.com / admin123"
    echo "- Teacher: teacher1@school.com / teacher123"
    echo "- Student: student1@school.com / student123"
    echo
    echo "⚠️  Change default passwords immediately!"
    echo
    echo "Environment variables configured:"
    echo "- Database Host: ${DB_HOST:-not configured}"
    echo "- Database Name: ${DB_NAME:-not configured}"
    echo "- App Environment: ${APP_ENV:-production}"
    echo "=========================================="
    
    # Execute the main command
    exec "$@"
}

# Run main function if script is executed directly
if [[ "${BASH_SOURCE[0]}" == "${0}" ]]; then
    main "$@"
fi
