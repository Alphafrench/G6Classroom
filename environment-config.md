# Environment Configuration Guide

## Overview

This guide covers comprehensive environment configuration for the Employee Attendance System across different deployment environments (development, staging, production) and platforms.

## Environment Types

### 1. Development Environment
- **Purpose:** Local development and testing
- **Debugging:** Enabled
- **Performance:** Not critical
- **Security:** Basic security measures
- **Database:** Local or test database

### 2. Staging Environment
- **Purpose:** Pre-production testing
- **Debugging:** Limited logging
- **Performance:** Production-like
- **Security:** Enhanced security
- **Database:** Copy of production data

### 3. Production Environment
- **Purpose:** Live application
- **Debugging:** Disabled
- **Performance:** Optimized
- **Security:** Maximum security
- **Database:** Live database

## Configuration File Structure

### 1. Environment Variables (.env)

Create environment-specific `.env` files:

**`.env.development`**
```env
# Development Environment Configuration
APP_ENV=development
APP_DEBUG=true
APP_URL=http://localhost:8080
APP_LOG_LEVEL=debug

# Database Configuration
DB_HOST=localhost
DB_PORT=3306
DB_NAME=attendance_dev
DB_USER=dev_user
DB_PASS=dev_password
DB_CHARSET=utf8mb4
DB_SSL=false

# Session Configuration
SESSION_LIFETIME=3600
SESSION_NAME=attendance_dev
SESSION_SECURE=false
SESSION_HTTPONLY=true
SESSION_SAMESITE=Lax

# Cache Configuration
CACHE_DRIVER=file
CACHE_TTL=3600
CACHE_PREFIX=dev_

# File Upload Configuration
UPLOAD_MAX_SIZE=10485760
UPLOAD_ALLOWED_TYPES=jpg,jpeg,png,pdf,doc,docx
UPLOAD_PATH=uploads/dev/

# Email Configuration (Development)
MAIL_MAILER=log
MAIL_FROM_ADDRESS=dev@attendance.local
MAIL_FROM_NAME="${APP_NAME} Dev"
MAIL_LOG_CHANNEL=daily

# Third-party Services
GOOGLE_API_KEY=dev_google_key
RECAPTCHA_SITE_KEY=dev_recaptcha_key
RECAPTCHA_SECRET_KEY=dev_secret_key

# Logging Configuration
LOG_CHANNEL=stack
LOG_LEVEL=debug
LOG_MAX_FILES=30
```

**`.env.staging`**
```env
# Staging Environment Configuration
APP_ENV=staging
APP_DEBUG=false
APP_URL=https://staging.attendance.local
APP_LOG_LEVEL=info

# Database Configuration
DB_HOST=staging-db.xxxxxx.cloud
DB_PORT=3306
DB_NAME=attendance_staging
DB_USER=staging_user
DB_PASS=StagingPassword123!
DB_CHARSET=utf8mb4
DB_SSL=true
DB_SSL_CA=/etc/ssl/certs/ca-staging.pem

# Session Configuration
SESSION_LIFETIME=3600
SESSION_NAME=attendance_staging
SESSION_SECURE=true
SESSION_HTTPONLY=true
SESSION_SAMESITE=Strict

# Cache Configuration
CACHE_DRIVER=redis
CACHE_TTL=3600
CACHE_PREFIX=staging_
CACHE_REDIS_HOST=staging-redis.xxxxxx.cache
CACHE_REDIS_PORT=6379
CACHE_REDIS_PASSWORD=RedisPassword123

# File Upload Configuration
UPLOAD_MAX_SIZE=5242880
UPLOAD_ALLOWED_TYPES=jpg,jpeg,png,pdf,doc,docx
UPLOAD_PATH=uploads/staging/

# Email Configuration (Staging - Real SMTP)
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=staging@attendance.local
MAIL_PASSWORD=AppPassword123
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=staging@attendance.local
MAIL_FROM_NAME="${APP_NAME} Staging"

# Third-party Services
GOOGLE_API_KEY=staging_google_key
RECAPTCHA_SITE_KEY=staging_recaptcha_key
RECAPTCHA_SECRET_KEY=staging_secret_key

# Security Configuration
APP_KEY=base64:GeneratedKeyHere
ENCRYPTION_KEY=base64:EncryptionKeyHere
CSRF_TOKEN_NAME=_token
CSRF_TOKEN_HEADER=X-CSRF-TOKEN

# Logging Configuration
LOG_CHANNEL=stack
LOG_LEVEL=info
LOG_MAX_FILES=90
LOG_SLACK_WEBHOOK_URL=https://hooks.slack.com/services/YOUR/SLACK/WEBHOOK
```

**`.env.production`**
```env
# Production Environment Configuration
APP_ENV=production
APP_DEBUG=false
APP_URL=https://attendance.company.com
APP_LOG_LEVEL=error

# Database Configuration (High Availability)
DB_PRIMARY_HOST=prod-db-1.xxxxxx.cloud
DB_REPLICA_HOST=prod-db-2.xxxxxx.cloud
DB_PORT=3306
DB_NAME=attendance_prod
DB_USER=prod_user
DB_PASS=VerySecurePassword456!
DB_CHARSET=utf8mb4
DB_SSL=true
DB_SSL_CA=/etc/ssl/certs/ca-prod.pem
DB_CONNECTION_TIMEOUT=10
DB_READ_TIMEOUT=10

# Session Configuration
SESSION_LIFETIME=3600
SESSION_NAME=attendance_prod
SESSION_SECURE=true
SESSION_HTTPONLY=true
SESSION_SAMESITE=Strict
SESSION_DOMAIN=.attendance.company.com
SESSION_PATH=/

# Cache Configuration (Redis Cluster)
CACHE_DRIVER=redis
CACHE_TTL=3600
CACHE_PREFIX=prod_
CACHE_REDIS_HOST=prod-redis-cluster.xxxxxx.cache
CACHE_REDIS_PORT=6379
CACHE_REDIS_PASSWORD=RedisClusterPassword789
CACHE_REDIS_DATABASE=0

# File Upload Configuration
UPLOAD_MAX_SIZE=2097152
UPLOAD_ALLOWED_TYPES=jpg,jpeg,png,pdf
UPLOAD_PATH=uploads/prod/
UPLOAD_CDN_URL=https://cdn.attendance.company.com/

# Email Configuration (Production SMTP)
MAIL_MAILER=smtp
MAIL_HOST=smtp.sendgrid.net
MAIL_PORT=587
MAIL_USERNAME=apikey
MAIL_PASSWORD=SendGridAPIKey123
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@attendance.company.com
MAIL_FROM_NAME="${APP_NAME}"

# Third-party Services (Production Keys)
GOOGLE_API_KEY=prod_google_key
RECAPTCHA_SITE_KEY=prod_recaptcha_key
RECAPTCHA_SECRET_KEY=prod_secret_key

# Security Configuration (Strong Encryption)
APP_KEY=base64:StrongGeneratedKeyHere
ENCRYPTION_KEY=base64:StrongEncryptionKeyHere
JWT_SECRET=JWTGenerationKeyHere
BCRYPT_ROUNDS=12
CSRF_TOKEN_NAME=_token
CSRF_TOKEN_HEADER=X-CSRF-TOKEN

# Rate Limiting
RATE_LIMIT_REQUESTS=100
RATE_LIMIT_WINDOW=3600
LOGIN_RATE_LIMIT=5
LOGIN_RATE_WINDOW=900

# SSL Configuration
SSL_VERIFY_PEER=true
SSL_CA_BUNDLE=/etc/ssl/certs/ca-bundle.crt
SSL_CERT_PATH=/etc/ssl/certs/attendance.crt
SSL_KEY_PATH=/etc/ssl/private/attendance.key

# Monitoring and Analytics
MONITORING_ENABLED=true
ANALYTICS_TRACKING_ID=UA-XXXXXXXX-X
ERROR_TRACKING_DSN=https://sentry.io/api/project/123456/
HEALTH_CHECK_TOKEN=SecureHealthCheckToken123

# Backup Configuration
BACKUP_ENABLED=true
BACKUP_SCHEDULE="0 2 * * *"
BACKUP_RETENTION_DAYS=30
BACKUP_S3_BUCKET=attendance-backups-prod
AWS_ACCESS_KEY_ID=AKIA...
AWS_SECRET_ACCESS_KEY=...
AWS_DEFAULT_REGION=us-east-1

# Logging Configuration (Comprehensive)
LOG_CHANNEL=stack
LOG_LEVEL=error
LOG_MAX_FILES=365
LOG_SLACK_WEBHOOK_URL=https://hooks.slack.com/services/YOUR/PRODUCTION/SLACK/WEBHOOK
LOG_EMAIL_REPORT_RECIPIENT=admin@attendance.company.com
LOG_EMAIL_REPORT_FREQUENCY=daily

# CDN Configuration
CDN_ENABLED=true
CDN_DOMAIN=cdn.attendance.company.com
CDN_GZIP=true
CDN_CACHE_TTL=86400

# API Rate Limiting
API_RATE_LIMIT=1000
API_RATE_WINDOW=3600
API_AUTH_REQUIRED=true
```

## Platform-Specific Configuration

### 1. Docker Configuration

**`docker-compose.yml`**
```yaml
version: '3.8'

services:
  app:
    build:
      context: .
      dockerfile: Dockerfile
    environment:
      - APP_ENV=${APP_ENV}
      - APP_DEBUG=${APP_DEBUG}
      - DB_HOST=db
      - DB_PORT=3306
      - DB_NAME=${DB_NAME}
      - DB_USER=${DB_USER}
      - DB_PASS=${DB_PASS}
    volumes:
      - ./:/var/www/html
      - ./uploads:/var/www/html/uploads
    ports:
      - "8080:80"
    depends_on:
      - db
      - redis

  db:
    image: mysql:8.0
    environment:
      - MYSQL_ROOT_PASSWORD=${DB_ROOT_PASSWORD}
      - MYSQL_DATABASE=${DB_NAME}
      - MYSQL_USER=${DB_USER}
      - MYSQL_PASSWORD=${DB_PASS}
    volumes:
      - mysql_data:/var/lib/mysql
      - ./database/schema.sql:/docker-entrypoint-initdb.d/schema.sql
    ports:
      - "3306:3306"

  redis:
    image: redis:7-alpine
    command: redis-server --appendonly yes
    volumes:
      - redis_data:/data

  nginx:
    image: nginx:alpine
    volumes:
      - ./:/var/www/html
      - ./docker/nginx.conf:/etc/nginx/nginx.conf
    ports:
      - "80:80"
      - "443:443"
    depends_on:
      - app

volumes:
  mysql_data:
  redis_data:
```

**`.dockerignore`**
```
node_modules
npm-debug.log
.git
.gitignore
README.md
.env
.env.local
.env.production
*.log
uploads/*.log
```

### 2. Railway Configuration

**`railway.toml`**
```toml
[build]
builder = "NIXPACKS"

[deploy]
healthcheckPath = "/health"
healthcheckTimeout = 300

[[services]]
name = "attendance-app"
source = "./"

[services.variables]
APP_ENV = "production"
APP_DEBUG = "false"
DB_HOST = "${{ MySQL.HOSTNAME }}"
DB_NAME = "${{ MySQL.DATABASE }}"
DB_USER = "${{ MySQL.USERNAME }}"
DB_PASS = "${{ MySQL.PASSWORD }}"
```

### 3. Heroku Configuration

**`Procfile`**
```
web: vendor/bin/heroku-php-apache2 -f public/index.php
```

**`composer.json` (Heroku specific)**
```json
{
    "require": {
        "php": "^8.0",
        "ext-pdo": "*",
        "ext-mysql": "*",
        "ext-gd": "*"
    },
    "scripts": {
        "post-install-cmd": [
            "php -r \"copy('.env.production', '.env');\""
        ]
    }
}
```

### 4. DigitalOcean App Platform

**`.do/app.yaml`**
```yaml
name: attendance-system
services:
- name: web
  source_dir: /
  github:
    repo: yourusername/attendance-system
    branch: main
  run_command: php -S 0.0.0.0:$PORT -t public
  environment_slug: php
  instance_count: 1
  instance_size_slug: basic-xxs
  envs:
  - key: APP_ENV
    value: production
  - key: APP_DEBUG
    value: false
  - key: DB_HOST
    value: ${db.HOSTNAME}
  - key: DB_NAME
    value: ${db.DATABASE}
  - key: DB_USER
    value: ${db.USERNAME}
  - key: DB_PASS
    value: ${db.PASSWORD}

databases:
- name: db
  engine: MYSQL
  version: "8"
  size: db-s-dev-database
```

### 5. AWS Elastic Beanstalk

**`.ebextensions/php-settings.config`**
```yaml
option_settings:
  aws:elasticbeanstalk:container:php:
    DocumentRoot: /
    MemoryLimit: 256M
    zlib.output_compression: On
    max_execution_time: 60
  
  aws:elasticbeanstalk:application:environment:
    APP_ENV: production
    APP_DEBUG: false
    
  aws:elasticbeanstalk:container:php:phpini:
    memory_limit: 256M
    max_execution_time: 60
    max_input_vars: 3000
    post_max_size: 16M
    upload_max_filesize: 2M
    date.timezone: UTC
    
  aws:elasticbeanstalk:application:proxy:
    staticfiles: /static=public/static
```

## Configuration Classes

### 1. Environment Loader Class

**`config/environment.php`**
```php
<?php

class Environment {
    private static $loaded = false;
    private static $variables = [];
    
    /**
     * Load environment variables from .env file
     */
    public static function load($file = null) {
        if (self::$loaded) {
            return;
        }
        
        // Determine environment file
        $envFile = $file ?? self::getEnvironmentFile();
        
        if (!file_exists($envFile)) {
            throw new Exception("Environment file not found: {$envFile}");
        }
        
        // Load environment variables
        $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        
        foreach ($lines as $line) {
            if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
                list($key, $value) = explode('=', $line, 2);
                $key = trim($key);
                $value = trim($value);
                
                // Remove quotes
                if (preg_match('/^["\'](.*)["\']$/', $value, $matches)) {
                    $value = $matches[1];
                }
                
                // Skip if already set (has priority)
                if (!isset($_ENV[$key])) {
                    $_ENV[$key] = $value;
                    putenv($key . '=' . $value);
                    self::$variables[$key] = $value;
                }
            }
        }
        
        self::$loaded = true;
    }
    
    /**
     * Get environment file path based on APP_ENV
     */
    private static function getEnvironmentFile() {
        $env = self::get('APP_ENV', 'development');
        
        switch ($env) {
            case 'production':
                return __DIR__ . '/../.env.production';
            case 'staging':
                return __DIR__ . '/../.env.staging';
            case 'testing':
                return __DIR__ . '/../.env.testing';
            default:
                return __DIR__ . '/../.env.development';
        }
    }
    
    /**
     * Get environment variable value
     */
    public static function get($key, $default = null) {
        $value = $_ENV[$key] ?? getenv($key) ?? $default;
        
        // Type conversion for boolean values
        if (in_array(strtolower($value), ['true', 'false'])) {
            return strtolower($value) === 'true';
        }
        
        return $value;
    }
    
    /**
     * Set environment variable
     */
    public static function set($key, $value) {
        $_ENV[$key] = $value;
        putenv($key . '=' . $value);
        self::$variables[$key] = $value;
    }
    
    /**
     * Check if environment variable exists
     */
    public static function has($key) {
        return isset($_ENV[$key]) || getenv($key) !== false;
    }
    
    /**
     * Get all environment variables
     */
    public static function all() {
        return self::$variables;
    }
    
    /**
     * Check if running in specific environment
     */
    public static function is($environment) {
        return self::get('APP_ENV') === $environment;
    }
    
    /**
     * Check if running in production
     */
    public static function isProduction() {
        return self::is('production');
    }
    
    /**
     * Check if debugging is enabled
     */
    public static function isDebug() {
        return self::get('APP_DEBUG', false);
    }
    
    /**
     * Validate required environment variables
     */
    public static function validateRequired(array $required) {
        $missing = [];
        
        foreach ($required as $key) {
            if (!self::has($key)) {
                $missing[] = $key;
            }
        }
        
        if (!empty($missing)) {
            throw new Exception("Missing required environment variables: " . implode(', ', $missing));
        }
        
        return true;
    }
}
```

### 2. Configuration Manager Class

**`config/config.php`**
```php
<?php

require_once __DIR__ . '/environment.php';

// Load environment variables
Environment::load();

// Application Configuration
define('APP_NAME', 'Employee Attendance System');
define('APP_VERSION', '1.0.0');
define('APP_ENV', Environment::get('APP_ENV', 'development'));
define('APP_DEBUG', Environment::get('APP_DEBUG', false));
define('APP_URL', Environment::get('APP_URL', 'http://localhost:8080'));
define('APP_TIMEZONE', Environment::get('APP_TIMEZONE', 'UTC'));

// Database Configuration
define('DB_HOST', Environment::get('DB_HOST', 'localhost'));
define('DB_PORT', Environment::get('DB_PORT', '3306'));
define('DB_NAME', Environment::get('DB_NAME', 'attendance'));
define('DB_USER', Environment::get('DB_USER', 'root'));
define('DB_PASS', Environment::get('DB_PASS', ''));
define('DB_CHARSET', Environment::get('DB_CHARSET', 'utf8mb4'));
define('DB_SSL', Environment::get('DB_SSL', false));

// Session Configuration
define('SESSION_LIFETIME', Environment::get('SESSION_LIFETIME', 3600));
define('SESSION_NAME', Environment::get('SESSION_NAME', 'attendance_session'));
define('SESSION_SECURE', Environment::get('SESSION_SECURE', false));
define('SESSION_HTTPONLY', Environment::get('SESSION_HTTPONLY', true));
define('SESSION_SAMESITE', Environment::get('SESSION_SAMESITE', 'Lax'));

// Cache Configuration
define('CACHE_DRIVER', Environment::get('CACHE_DRIVER', 'file'));
define('CACHE_TTL', Environment::get('CACHE_TTL', 3600));
define('CACHE_PREFIX', Environment::get('CACHE_PREFIX', 'attendance_'));

// File Upload Configuration
define('UPLOAD_MAX_SIZE', Environment::get('UPLOAD_MAX_SIZE', 2097152));
define('UPLOAD_ALLOWED_TYPES', Environment::get('UPLOAD_ALLOWED_TYPES', 'jpg,jpeg,png,pdf'));
define('UPLOAD_PATH', Environment::get('UPLOAD_PATH', 'uploads/'));
define('UPLOAD_CDN_URL', Environment::get('UPLOAD_CDN_URL', ''));

// Email Configuration
define('MAIL_MAILER', Environment::get('MAIL_MAILER', 'smtp'));
define('MAIL_HOST', Environment::get('MAIL_HOST', 'localhost'));
define('MAIL_PORT', Environment::get('MAIL_PORT', '587'));
define('MAIL_USERNAME', Environment::get('MAIL_USERNAME', ''));
define('MAIL_PASSWORD', Environment::get('MAIL_PASSWORD', ''));
define('MAIL_ENCRYPTION', Environment::get('MAIL_ENCRYPTION', 'tls'));
define('MAIL_FROM_ADDRESS', Environment::get('MAIL_FROM_ADDRESS', 'noreply@example.com'));
define('MAIL_FROM_NAME', Environment::get('MAIL_FROM_NAME', APP_NAME));

// Security Configuration
define('APP_KEY', Environment::get('APP_KEY', ''));
define('ENCRYPTION_KEY', Environment::get('ENCRYPTION_KEY', ''));
define('JWT_SECRET', Environment::get('JWT_SECRET', ''));
define('BCRYPT_ROUNDS', Environment::get('BCRYPT_ROUNDS', 10));
define('CSRF_TOKEN_NAME', Environment::get('CSRF_TOKEN_NAME', '_token'));

// Rate Limiting
define('RATE_LIMIT_REQUESTS', Environment::get('RATE_LIMIT_REQUESTS', 100));
define('RATE_LIMIT_WINDOW', Environment::get('RATE_LIMIT_WINDOW', 3600));
define('LOGIN_RATE_LIMIT', Environment::get('LOGIN_RATE_LIMIT', 5));
define('LOGIN_RATE_WINDOW', Environment::get('LOGIN_RATE_WINDOW', 900));

// Third-party Services
define('GOOGLE_API_KEY', Environment::get('GOOGLE_API_KEY', ''));
define('RECAPTCHA_SITE_KEY', Environment::get('RECAPTCHA_SITE_KEY', ''));
define('RECAPTCHA_SECRET_KEY', Environment::get('RECAPTCHA_SECRET_KEY', ''));

// Logging Configuration
define('LOG_CHANNEL', Environment::get('LOG_CHANNEL', 'daily'));
define('LOG_LEVEL', Environment::get('LOG_LEVEL', 'info'));
define('LOG_MAX_FILES', Environment::get('LOG_MAX_FILES', 30));

// Monitoring Configuration
define('MONITORING_ENABLED', Environment::get('MONITORING_ENABLED', false));
define('ANALYTICS_TRACKING_ID', Environment::get('ANALYTICS_TRACKING_ID', ''));
define('ERROR_TRACKING_DSN', Environment::get('ERROR_TRACKING_DSN', ''));

// CDN Configuration
define('CDN_ENABLED', Environment::get('CDN_ENABLED', false));
define('CDN_DOMAIN', Environment::get('CDN_DOMAIN', ''));
define('CDN_CACHE_TTL', Environment::get('CDN_CACHE_TTL', 86400));

// SSL Configuration
define('SSL_VERIFY_PEER', Environment::get('SSL_VERIFY_PEER', true));
define('SSL_CA_BUNDLE', Environment::get('SSL_CA_BUNDLE', ''));

// API Configuration
define('API_RATE_LIMIT', Environment::get('API_RATE_LIMIT', 1000));
define('API_AUTH_REQUIRED', Environment::get('API_AUTH_REQUIRED', false));

// Set timezone
date_default_timezone_set(APP_TIMEZONE);

// Validate required configuration
Environment::validateRequired([
    'DB_HOST',
    'DB_NAME',
    'DB_USER'
]);

// Set session configuration
ini_set('session.name', SESSION_NAME);
ini_set('session.gc_maxlifetime', SESSION_LIFETIME);
ini_set('session.cookie_lifetime', SESSION_LIFETIME);
ini_set('session.cookie_secure', SESSION_SECURE ? '1' : '0');
ini_set('session.cookie_httponly', SESSION_HTTPONLY ? '1' : '0');
ini_set('session.cookie_samesite', SESSION_SAMESITE);

// Set PHP configuration based on environment
if (APP_ENV === 'production') {
    ini_set('display_errors', '0');
    ini_set('display_startup_errors', '0');
    ini_set('log_errors', '1');
    error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT);
} else {
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);
}

// Set upload limits
ini_set('upload_max_filesize', UPLOAD_MAX_SIZE);
ini_set('post_max_size', UPLOAD_MAX_SIZE);
ini_set('max_input_vars', '3000');
ini_set('memory_limit', '256M');
ini_set('max_execution_time', '60');
```

## Configuration Validation

### Validation Script

**`scripts/validate-config.php`**
```php
<?php

/**
 * Configuration Validation Script
 * Run this to validate your environment configuration
 */

require_once __DIR__ . '/../config/config.php';

class ConfigValidator {
    private $errors = [];
    private $warnings = [];
    
    public function validate() {
        $this->validateDatabase();
        $this->validateSecurity();
        $this->validateFilePermissions();
        $this->validateExtensions();
        $this->validateEnvironment();
        
        return [
            'errors' => $this->errors,
            'warnings' => $this->warnings,
            'valid' => empty($this->errors)
        ];
    }
    
    private function validateDatabase() {
        try {
            $pdo = new PDO(
                "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";charset=" . DB_CHARSET,
                DB_USER,
                DB_PASS,
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
            
            $stmt = $pdo->query("SELECT 1");
            $this->warnings[] = "Database connection successful";
            
        } catch (PDOException $e) {
            $this->errors[] = "Database connection failed: " . $e->getMessage();
        }
    }
    
    private function validateSecurity() {
        if (APP_ENV === 'production') {
            if (!SSL_VERIFY_PEER) {
                $this->warnings[] = "SSL peer verification is disabled";
            }
            
            if (empty(APP_KEY)) {
                $this->errors[] = "APP_KEY is required in production";
            }
            
            if (empty(ENCRYPTION_KEY)) {
                $this->errors[] = "ENCRYPTION_KEY is required in production";
            }
        }
    }
    
    private function validateFilePermissions() {
        $paths = [
            'uploads/',
            'logs/',
            'cache/'
        ];
        
        foreach ($paths as $path) {
            if (!is_writable($path)) {
                $this->errors[] = "Directory '$path' is not writable";
            }
        }
    }
    
    private function validateExtensions() {
        $required = ['pdo', 'pdo_mysql', 'gd', 'curl', 'openssl', 'mbstring'];
        $missing = [];
        
        foreach ($required as $ext) {
            if (!extension_loaded($ext)) {
                $missing[] = $ext;
            }
        }
        
        if (!empty($missing)) {
            $this->errors[] = "Missing PHP extensions: " . implode(', ', $missing);
        }
    }
    
    private function validateEnvironment() {
        $required_vars = [
            'APP_ENV',
            'DB_HOST',
            'DB_NAME',
            'DB_USER'
        ];
        
        foreach ($required_vars as $var) {
            if (!Environment::has($var)) {
                $this->errors[] = "Required environment variable missing: $var";
            }
        }
    }
}

// Run validation
$validator = new ConfigValidator();
$result = $validator->validate();

echo "Configuration Validation Results:\n";
echo "=================================\n";

if ($result['valid']) {
    echo "✅ Configuration is valid!\n";
} else {
    echo "❌ Configuration errors found:\n";
    foreach ($result['errors'] as $error) {
        echo "   - $error\n";
    }
}

if (!empty($result['warnings'])) {
    echo "\n⚠️  Warnings:\n";
    foreach ($result['warnings'] as $warning) {
        echo "   - $warning\n";
    }
}

exit($result['valid'] ? 0 : 1);
```

## Environment-Specific Features

### 1. Development Tools

**`.env.development` additions:**
```env
# Development Tools
DEBUG_TOOLBAR=true
PROFILER_ENABLED=true
LOG_QUERIES=true
MOCK_EMAIL_SERVICE=true
FAKE_DATA_GENERATOR=true
HOT_RELOAD=true

# Database Seeding
SEED_DATABASE=true
SEED_SAMPLE_DATA=true
```

### 2. Staging Features

**`.env.staging` additions:**
```env
# Staging Testing
INTEGRATION_TESTS=true
API_TESTING_ENABLED=true
LOAD_TESTING=false
SECURITY_SCANNING=true
ACCESS_LOGS_RETENTION=7
```

### 3. Production Monitoring

**`.env.production` additions:**
```env
# Production Monitoring
HEALTH_CHECK_ENABLED=true
UPTIME_MONITORING=true
PERFORMANCE_MONITORING=true
ERROR_TRACKING_ENABLED=true
SECURITY_AUDIT_LOGS=true
AUDIT_LOG_RETENTION=2555

# Business Intelligence
ANALYTICS_ENABLED=true
REPORTING_ENABLED=true
DATA_EXPORT_ENABLED=false

# High Availability
FAILOVER_ENABLED=true
LOAD_BALANCER_CHECKS=true
HEALTH_CHECK_INTERVAL=30
```

## Configuration Best Practices

### 1. Security
- Never commit `.env` files to version control
- Use strong, unique passwords for each environment
- Enable SSL/TLS for all database connections
- Rotate credentials regularly
- Use environment-specific encryption keys

### 2. Performance
- Enable caching in production
- Optimize database connection pooling
- Use CDN for static assets
- Configure appropriate timeouts
- Monitor resource usage

### 3. Maintenance
- Document all configuration changes
- Keep environment files organized
- Use configuration management tools
- Regular backup of configuration
- Version control configuration templates

### 4. Monitoring
- Set up health checks
- Monitor configuration drift
- Log configuration changes
- Alert on configuration errors
- Regular configuration audits

## Troubleshooting

### Common Configuration Issues

1. **Database Connection Fails**
   - Check host, port, username, password
   - Verify database exists
   - Check firewall/security group settings
   - Verify SSL settings

2. **Session Issues**
   - Check session configuration
   - Verify file permissions
   - Check session storage settings
   - Validate session domain

3. **File Upload Problems**
   - Check upload size limits
   - Verify file permissions
   - Validate allowed file types
   - Check disk space

4. **Email Configuration**
   - Verify SMTP settings
   - Check authentication credentials
   - Test connection to mail server
   - Validate from address

This comprehensive environment configuration guide ensures your Employee Attendance System runs smoothly across all deployment environments with proper security, performance, and monitoring configurations.