<?php
/**
 * Core Utility Functions for Employee Management System
 * 
 * This file contains essential utility functions including validation,
 * sanitization, and formatting functions for the application.
 * 
 * @package EmployeeManager
 * @version 1.0
 * @author Development Team
 */

/**
 * Sanitize input data to prevent XSS and injection attacks
 * 
 * @param mixed $data The data to sanitize
 * @return mixed Sanitized data
 */
function sanitizeInput($data) {
    if (is_array($data)) {
        return array_map('sanitizeInput', $data);
    }
    
    if (is_string($data)) {
        // Remove HTML tags and trim whitespace
        $data = trim(strip_tags($data));
        
        // Remove potential JavaScript and special characters
        $data = preg_replace('/javascript:/i', '', $data);
        $data = preg_replace('/<script[^>]*>.*?<\/script>/gis', '', $data);
        
        // Escape special characters for HTML output
        $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
        
        return $data;
    }
    
    return $data;
}

/**
 * Sanitize HTML input while allowing specific tags
 * 
 * @param string $input Raw HTML input
 * @param array $allowedTags Array of allowed HTML tags
 * @return string Sanitized HTML
 */
function sanitizeHTML($input, $allowedTags = ['<p>', '<br>', '<strong>', '<em>', '<u>']) {
    return strip_tags($input, implode('', $allowedTags));
}

/**
 * Validate email address format
 * 
 * @param string $email Email address to validate
 * @return bool True if valid email, false otherwise
 */
function validateEmail($email) {
    if (empty($email) || !is_string($email)) {
        return false;
    }
    
    // Basic email validation
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return false;
    }
    
    // Check for common invalid patterns
    if (preg_match('/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/', $email)) {
        return true;
    }
    
    return false;
}

/**
 * Validate phone number format
 * 
 * @param string $phone Phone number to validate
 * @param string $country Country code for specific validation (optional)
 * @return bool True if valid phone number, false otherwise
 */
function validatePhone($phone, $country = 'US') {
    if (empty($phone) || !is_string($phone)) {
        return false;
    }
    
    // Remove all non-numeric characters except +
    $cleanPhone = preg_replace('/[^\d+]/', '', $phone);
    
    // US phone validation (10 digits)
    if ($country === 'US') {
        return preg_match('/^\+?1?[2-9]\d{2}[2-9]\d{2}\d{4}$/', $cleanPhone) === 1;
    }
    
    // International phone validation (general)
    return preg_match('/^\+?[1-9]\d{7,14}$/', $cleanPhone) === 1;
}

/**
 * Validate password strength
 * 
 * @param string $password Password to validate
 * @param array $options Validation options
 * @return array Result with 'valid' boolean and 'errors' array
 */
function validatePassword($password, $options = []) {
    $errors = [];
    
    if (empty($password)) {
        $errors[] = "Password cannot be empty";
        return ['valid' => false, 'errors' => $errors];
    }
    
    $minLength = $options['min_length'] ?? 8;
    $requireUppercase = $options['require_uppercase'] ?? true;
    $requireLowercase = $options['require_lowercase'] ?? true;
    $requireNumbers = $options['require_numbers'] ?? true;
    $requireSpecial = $options['require_special'] ?? true;
    
    if (strlen($password) < $minLength) {
        $errors[] = "Password must be at least {$minLength} characters long";
    }
    
    if ($requireUppercase && !preg_match('/[A-Z]/', $password)) {
        $errors[] = "Password must contain at least one uppercase letter";
    }
    
    if ($requireLowercase && !preg_match('/[a-z]/', $password)) {
        $errors[] = "Password must contain at least one lowercase letter";
    }
    
    if ($requireNumbers && !preg_match('/\d/', $password)) {
        $errors[] = "Password must contain at least one number";
    }
    
    if ($requireSpecial && !preg_match('/[^A-Za-z0-9]/', $password)) {
        $errors[] = "Password must contain at least one special character";
    }
    
    return [
        'valid' => empty($errors),
        'errors' => $errors
    ];
}

/**
 * Validate date format
 * 
 * @param string $date Date string to validate
 * @param string $format Expected date format (default: Y-m-d)
 * @return bool True if valid date, false otherwise
 */
function validateDate($date, $format = 'Y-m-d') {
    if (empty($date)) {
        return false;
    }
    
    $dateTime = DateTime::createFromFormat($format, $date);
    return $dateTime && $dateTime->format($format) === $date;
}

/**
 * Validate required fields
 * 
 * @param array $data Data to validate
 * @param array $requiredFields Array of required field names
 * @return array Result with 'valid' boolean and 'missing_fields' array
 */
function validateRequiredFields($data, $requiredFields) {
    $missingFields = [];
    
    foreach ($requiredFields as $field) {
        if (!isset($data[$field]) || empty(trim($data[$field]))) {
            $missingFields[] = $field;
        }
    }
    
    return [
        'valid' => empty($missingFields),
        'missing_fields' => $missingFields
    ];
}

/**
 * Format phone number for display
 * 
 * @param string $phone Phone number to format
 * @param string $format Format pattern (default: (XXX) XXX-XXXX)
 * @return string Formatted phone number
 */
function formatPhoneNumber($phone, $format = '(XXX) XXX-XXXX') {
    // Remove all non-numeric characters
    $numbers = preg_replace('/\D/', '', $phone);
    
    // Ensure we have a valid phone number
    if (strlen($numbers) < 10) {
        return $phone;
    }
    
    // Apply formatting pattern
    return preg_replace_callback('/X/', function($matches) use (&$numbers) {
        return array_shift($numbers);
    }, $format);
}

/**
 * Format currency amount
 * 
 * @param float $amount Amount to format
 * @param string $currency Currency symbol (default: $)
 * @param int $decimals Number of decimal places (default: 2)
 * @return string Formatted currency
 */
function formatCurrency($amount, $currency = '$', $decimals = 2) {
    if (!is_numeric($amount)) {
        return $currency . '0.00';
    }
    
    return $currency . number_format((float)$amount, $decimals, '.', ',');
}

/**
 * Format date for display
 * 
 * @param string $date Date string or timestamp
 * @param string $format Output format (default: M j, Y)
 * @return string Formatted date
 */
function formatDate($date, $format = 'M j, Y') {
    if (empty($date)) {
        return '';
    }
    
    $timestamp = is_numeric($date) ? $date : strtotime($date);
    
    if ($timestamp === false) {
        return $date; // Return original if conversion fails
    }
    
    return date($format, $timestamp);
}

/**
 * Format datetime for display
 * 
 * @param string $datetime DateTime string or timestamp
 * @param string $format Output format (default: M j, Y g:i A)
 * @return string Formatted datetime
 */
function formatDateTime($datetime, $format = 'M j, Y g:i A') {
    if (empty($datetime)) {
        return '';
    }
    
    $timestamp = is_numeric($datetime) ? $datetime : strtotime($datetime);
    
    if ($timestamp === false) {
        return $datetime;
    }
    
    return date($format, $timestamp);
}

/**
 * Calculate age from birth date
 * 
 * @param string $birthDate Birth date (Y-m-d format)
 * @return int Age in years
 */
function calculateAge($birthDate) {
    if (empty($birthDate)) {
        return 0;
    }
    
    $birth = new DateTime($birthDate);
    $now = new DateTime();
    $age = $now->diff($birth);
    
    return $age->y;
}

/**
 * Generate random password
 * 
 * @param int $length Password length (default: 12)
 * @param bool $includeSpecial Whether to include special characters (default: true)
 * @return string Generated password
 */
function generatePassword($length = 12, $includeSpecial = true) {
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $numbers = '0123456789';
    $specials = '!@#$%^&*()_+-=[]{}|;:,.<>?';
    
    $password = '';
    
    // Add at least one lowercase letter
    $password .= $chars[random_int(0, 25)];
    
    // Add at least one uppercase letter
    $password .= $chars[random_int(26, 51)];
    
    // Add at least one number
    $password .= $numbers[random_int(0, 9)];
    
    // Add special character if requested
    if ($includeSpecial) {
        $password .= $specials[random_int(0, strlen($specials) - 1)];
    }
    
    // Fill remaining length with random characters
    $allChars = $chars . $numbers . ($includeSpecial ? $specials : '');
    for ($i = strlen($password); $i < $length; $i++) {
        $password .= $allChars[random_int(0, strlen($allChars) - 1)];
    }
    
    // Shuffle the password
    return str_shuffle($password);
}

/**
 * Generate secure random token
 * 
 * @param int $length Token length (default: 32)
 * @return string Secure random token
 */
function generateToken($length = 32) {
    return bin2hex(random_bytes($length / 2));
}

/**
 * Hash password using PHP password_hash
 * 
 * @param string $password Plain text password
 * @param string $algorithm Hash algorithm (default: PASSWORD_DEFAULT)
 * @return string Hashed password
 */
function hashPassword($password, $algorithm = PASSWORD_DEFAULT) {
    return password_hash($password, $algorithm);
}

/**
 * Verify password against hash
 * 
 * @param string $password Plain text password
 * @param string $hash Hashed password
 * @return bool True if password matches, false otherwise
 */
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

/**
 * Log error messages
 * 
 * @param string $message Error message
 * @param array $context Additional context data
 * @param string $level Log level (default: 'error')
 */
function logError($message, $context = [], $level = 'error') {
    $logFile = __DIR__ . '/../logs/error.log';
    
    // Ensure logs directory exists
    $logDir = dirname($logFile);
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }
    
    $timestamp = date('Y-m-d H:i:s');
    $contextStr = !empty($context) ? ' Context: ' . json_encode($context) : '';
    $logEntry = "[{$timestamp}] [{$level}] {$message}{$contextStr}" . PHP_EOL;
    
    file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
}

/**
 * Convert array to CSV string
 * 
 * @param array $data Array of data to convert
 * @param string $delimiter CSV delimiter (default: comma)
 * @return string CSV formatted string
 */
function arrayToCSV($data, $delimiter = ',') {
    if (empty($data)) {
        return '';
    }
    
    $output = fopen('php://temp', 'r+');
    
    foreach ($data as $row) {
        fputcsv($output, $row, $delimiter);
    }
    
    rewind($output);
    $csv = stream_get_contents($output);
    fclose($output);
    
    return rtrim($csv, "\n");
}

/**
 * Safe redirect with optional query parameters
 * 
 * @param string $url Redirect URL
 * @param array $params Query parameters to append
 */
function safeRedirect($url, $params = []) {
    if (!empty($params)) {
        $url .= '?' . http_build_query($params);
    }
    
    header("Location: {$url}");
    exit();
}

/**
 * Get client IP address
 * 
 * @return string Client IP address
 */
function getClientIP() {
    $ipKeys = ['HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'HTTP_CLIENT_IP', 'REMOTE_ADDR'];
    
    foreach ($ipKeys as $key) {
        if (!empty($_SERVER[$key])) {
            $ip = $_SERVER[$key];
            // Handle comma-separated IPs from proxies
            if (strpos($ip, ',') !== false) {
                $ip = trim(explode(',', $ip)[0]);
            }
            if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                return $ip;
            }
        }
    }
    
    return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
}

/**
 * Set HTTP response code
 * 
 * @param int $code HTTP status code
 * @param string $message Optional status message
 */
function setHttpStatus($code, $message = '') {
    $statusMessages = [
        200 => 'OK',
        201 => 'Created',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        422 => 'Unprocessable Entity',
        500 => 'Internal Server Error',
        503 => 'Service Unavailable'
    ];
    
    $statusMessage = $message ?: ($statusMessages[$code] ?? '');
    
    http_response_code($code);
    
    if (!empty($statusMessage)) {
        header("Status: {$code} {$statusMessage}");
    }
}

/**
 * Return JSON response
 * 
 * @param mixed $data Data to return
 * @param int $statusCode HTTP status code
 */
function jsonResponse($data, $statusCode = 200) {
    setHttpStatus($statusCode);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

/**
 * Cross-site request forgery (CSRF) token generation
 * 
 * @return string CSRF token
 */
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token
 * 
 * @param string $token Token to verify
 * @return bool True if valid, false otherwise
 */
function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}
?>