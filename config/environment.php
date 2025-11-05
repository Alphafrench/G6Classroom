<?php
/**
 * Environment Configuration
 * Handles different deployment environments (development, staging, production)
 * 
 * @package ClassroomManager
 */

// Environment detection
$environment = $_ENV['APP_ENV'] ?? 'development';

// Define environment constants
define('APP_ENV', $environment);
define('IS_DEVELOPMENT', APP_ENV === 'development');
define('IS_STAGING', APP_ENV === 'staging');
define('IS_PRODUCTION', APP_ENV === 'production');

// Base configuration for all environments
define('BASE_URL', 'http://localhost');

// Environment-specific configurations
switch (APP_ENV) {
    case 'development':
        // Development settings
        define('APP_URL', 'http://localhost/classroom-clone');
        define('APP_TIMEZONE', 'UTC');
        
        // Database configuration
        define('DB_HOST', 'localhost');
        define('DB_NAME', 'classroom_clone_dev');
        define('DB_USER', 'root');
        define('DB_PASS', '');
        define('DB_PORT', 3306);
        
        // Debug settings
        define('DEBUG_MODE', true);
        define('LOG_LEVEL', 'debug');
        define('DISPLAY_ERRORS', true);
        
        // Email settings (for development/testing)
        define('SMTP_HOST', 'localhost');
        define('SMTP_PORT', 1025); // MailHog/MailCatcher
        define('SMTP_USERNAME', '');
        define('SMTP_PASSWORD', '');
        define('SMTP_ENCRYPTION', 'tls');
        
        // External services (development keys)
        define('GOOGLE_CLIENT_ID', 'your-dev-google-client-id');
        define('GOOGLE_CLIENT_SECRET', 'your-dev-google-client-secret');
        
        break;
        
    case 'staging':
        // Staging settings
        define('APP_URL', 'https://staging.classroom-manager.com');
        define('APP_TIMEZONE', 'UTC');
        
        // Database configuration
        define('DB_HOST', 'staging-db.example.com');
        define('DB_NAME', 'classroom_clone_staging');
        define('DB_USER', 'staging_user');
        define('DB_PASS', 'staging_password');
        define('DB_PORT', 3306);
        
        // Debug settings
        define('DEBUG_MODE', true);
        define('LOG_LEVEL', 'info');
        define('DISPLAY_ERRORS', false);
        
        // Email settings
        define('SMTP_HOST', 'smtp.sendgrid.net');
        define('SMTP_PORT', 587);
        define('SMTP_USERNAME', 'apikey');
        define('SMTP_PASSWORD', 'sendgrid-api-key');
        define('SMTP_ENCRYPTION', 'tls');
        
        // External services (staging keys)
        define('GOOGLE_CLIENT_ID', 'your-staging-google-client-id');
        define('GOOGLE_CLIENT_SECRET', 'your-staging-google-client-secret');
        
        break;
        
    case 'production':
        // Production settings
        define('APP_URL', 'https://classroom-manager.com');
        define('APP_TIMEZONE', 'America/New_York');
        
        // Database configuration (from environment variables)
        define('DB_HOST', $_ENV['DB_HOST'] ?? 'prod-db.example.com');
        define('DB_NAME', $_ENV['DB_NAME'] ?? 'classroom_clone_prod');
        define('DB_USER', $_ENV['DB_USER'] ?? 'prod_user');
        define('DB_PASS', $_ENV['DB_PASS'] ?? 'secure_password');
        define('DB_PORT', $_ENV['DB_PORT'] ?? 3306);
        
        // Debug settings
        define('DEBUG_MODE', false);
        define('LOG_LEVEL', 'error');
        define('DISPLAY_ERRORS', false);
        
        // Email settings
        define('SMTP_HOST', $_ENV['SMTP_HOST'] ?? 'smtp.sendgrid.net');
        define('SMTP_PORT', $_ENV['SMTP_PORT'] ?? 587);
        define('SMTP_USERNAME', $_ENV['SMTP_USERNAME'] ?? 'apikey');
        define('SMTP_PASSWORD', $_ENV['SMTP_PASSWORD'] ?? 'production-sendgrid-key');
        define('SMTP_ENCRYPTION', 'tls');
        
        // External services (production keys)
        define('GOOGLE_CLIENT_ID', $_ENV['GOOGLE_CLIENT_ID'] ?? 'prod-google-client-id');
        define('GOOGLE_CLIENT_SECRET', $_ENV['GOOGLE_CLIENT_SECRET'] ?? 'prod-google-client-secret');
        
        break;
        
    default:
        throw new Exception("Invalid environment: " . APP_ENV);
}

// Common database settings
define('DB_CHARSET', 'utf8mb4');
define('DB_COLLATION', 'utf8mb4_unicode_ci');

// Security configuration
define('ENABLE_HTTPS', IS_PRODUCTION);
define('HSTS_MAX_AGE', IS_PRODUCTION ? 31536000 : 0); // 1 year in production

// File upload settings per environment
if (IS_DEVELOPMENT) {
    define('UPLOAD_DEBUG_MODE', true);
    define('MAX_CONCURRENT_UPLOADS', 3);
} elseif (IS_STAGING) {
    define('UPLOAD_DEBUG_MODE', true);
    define('MAX_CONCURRENT_UPLOADS', 5);
} else {
    define('UPLOAD_DEBUG_MODE', false);
    define('MAX_CONCURRENT_UPLOADS', 10);
}

// Performance settings per environment
if (IS_DEVELOPMENT) {
    ini_set('memory_limit', '256M');
    ini_set('max_execution_time', 60);
    define('QUERY_CACHE_TIME', 0); // No caching in development
} else {
    ini_set('memory_limit', '512M');
    ini_set('max_execution_time', 120);
    define('QUERY_CACHE_TIME', 3600); // 1 hour cache
}

// Logging configuration
define('LOG_PATH', ROOT_PATH . '/logs/');
define('LOG_MAX_SIZE', 10 * 1024 * 1024); // 10MB
define('LOG_ROTATION', IS_PRODUCTION); // Rotate logs in production

// Cache configuration
if (IS_DEVELOPMENT) {
    define('CACHE_DRIVER', 'array'); // No persistent cache in development
    define('REDIS_HOST', 'localhost');
    define('REDIS_PORT', 6379);
} else {
    define('CACHE_DRIVER', 'redis');
    define('REDIS_HOST', $_ENV['REDIS_HOST'] ?? 'localhost');
    define('REDIS_PORT', $_ENV['REDIS_PORT'] ?? 6379);
    define('REDIS_PASSWORD', $_ENV['REDIS_PASSWORD'] ?? null);
}

// Rate limiting per environment
if (IS_DEVELOPMENT) {
    define('RATE_LIMIT_REQUESTS', 1000);
    define('RATE_LIMIT_WINDOW', 3600); // 1 hour
} else {
    define('RATE_LIMIT_REQUESTS', 100);
    define('RATE_LIMIT_WINDOW', 3600); // 1 hour
}

// Feature flags per environment
if (IS_DEVELOPMENT) {
    define('ENABLE_ALL_FEATURES', true);
    define('ENABLE_MOCK_SERVICES', true);
} else {
    define('ENABLE_ALL_FEATURES', false);
    define('ENABLE_MOCK_SERVICES', false);
}

// Google Cloud Services (for production)
if (IS_PRODUCTION) {
    define('GOOGLE_PROJECT_ID', $_ENV['GOOGLE_PROJECT_ID'] ?? null);
    define('GOOGLE_KEY_FILE', $_ENV['GOOGLE_KEY_FILE'] ?? null);
    define('GCS_BUCKET_NAME', $_ENV['GCS_BUCKET_NAME'] ?? null);
} else {
    define('GOOGLE_PROJECT_ID', null);
    define('GOOGLE_KEY_FILE', null);
    define('GCS_BUCKET_NAME', null);
}

/**
 * Get environment variable with fallback
 * @param string $key
 * @param mixed $default
 * @return mixed
 */
function env($key, $default = null) {
    return $_ENV[$key] ?? getenv($key) ?: $default;
}

/**
 * Check if environment is valid
 * @return bool
 */
function isValidEnvironment() {
    return in_array(APP_ENV, ['development', 'staging', 'production']);
}

// Validate environment
if (!isValidEnvironment()) {
    throw new Exception('Invalid environment configuration: ' . APP_ENV);
}
