# Performance Optimization Guide

## Overview

This comprehensive guide covers performance optimization techniques for the Employee Attendance System across different layers - database, application code, web server, and infrastructure.

## Table of Contents

1. [Database Performance](#database-performance)
2. [Application Code Optimization](#application-code-optimization)
3. [Caching Strategies](#caching-strategies)
4. [Web Server Optimization](#web-server-optimization)
5. [Frontend Optimization](#frontend-optimization)
6. [CDN and Static Assets](#cdn-and-static-assets)
7. [Database Optimization](#database-optimization)
8. [Memory and Resource Management](#memory-and-resource-management)
9. [Monitoring and Profiling](#monitoring-and-profiling)
10. [Production Performance Tuning](#production-performance-tuning)

## Database Performance

### 1. Query Optimization

#### Use Prepared Statements
```php
// Good - Prepared statement
$stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND is_active = 1");
$stmt->execute([$email]);
$user = $stmt->fetch();

// Bad - String concatenation
$query = "SELECT * FROM users WHERE email = '$email' AND is_active = 1";
$result = $pdo->query($query);
```

#### Optimize WHERE Clauses
```sql
-- Use indexed columns in WHERE clauses
SELECT * FROM attendance WHERE user_id = 123 AND date >= '2023-01-01';

-- Avoid functions on indexed columns
-- Bad: WHERE DATE(created_at) = '2023-01-01'
-- Good: WHERE created_at >= '2023-01-01' AND created_at < '2023-01-02'
```

#### Limit Result Sets
```php
// Always limit results for large datasets
$stmt = $pdo->prepare("
    SELECT id, name, email 
    FROM users 
    WHERE is_active = 1 
    ORDER BY created_at DESC 
    LIMIT 20 OFFSET ?
");
$stmt->execute([$offset]);
```

### 2. Index Optimization

#### Create Essential Indexes
```sql
-- User table indexes
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_users_active ON users(is_active);
CREATE INDEX idx_users_created_at ON users(created_at);

-- Attendance table indexes
CREATE INDEX idx_attendance_user_date ON attendance(user_id, date);
CREATE INDEX idx_attendance_date ON attendance(date);
CREATE INDEX idx_attendance_status ON attendance(status);

-- Composite indexes for common queries
CREATE INDEX idx_attendance_user_date_status 
ON attendance(user_id, date, status);

-- Course enrollments
CREATE INDEX idx_enrollments_course_student ON enrollments(course_id, student_id);
```

#### Analyze Index Usage
```sql
-- Check index usage
SHOW INDEX FROM users;
SHOW INDEX FROM attendance;

-- Find unused indexes
SELECT 
    s.table_schema,
    s.table_name,
    s.index_name,
    s.cardinality
FROM information_schema.statistics s
LEFT JOIN information_schema.index_statistics i 
    ON s.table_schema = i.table_schema 
    AND s.table_name = i.table_name 
    AND s.index_name = i.index_name
WHERE i.table_name IS NULL 
    AND s.table_schema = 'attendance';
```

### 3. Query Analysis

#### Enable Slow Query Log
```sql
-- Enable slow query log
SET GLOBAL slow_query_log = 'ON';
SET GLOBAL long_query_time = 2;
SET GLOBAL log_queries_not_using_indexes = 'ON';

-- View slow queries
SELECT * FROM mysql.slow_log 
ORDER BY start_time DESC 
LIMIT 10;
```

#### Explain Query Execution
```sql
-- Analyze query execution plan
EXPLAIN SELECT u.name, a.date, a.status 
FROM users u 
JOIN attendance a ON u.id = a.user_id 
WHERE a.date >= '2023-01-01' 
ORDER BY a.date DESC;

-- Use extended explain
EXPLAIN EXTENDED SELECT * FROM attendance WHERE user_id = 123;
```

### 4. Database Connection Pooling

```php
// config/database.php
class DatabasePool {
    private static $connections = [];
    private static $maxConnections = 10;
    
    public static function getConnection() {
        if (count(self::$connections) < self::$maxConnections) {
            $config = [
                'host' => DB_HOST,
                'port' => DB_PORT,
                'dbname' => DB_NAME,
                'username' => DB_USER,
                'password' => DB_PASS,
                'charset' => 'utf8mb4'
            ];
            
            $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['dbname']};charset={$config['charset']}";
            
            $pdo = new PDO($dsn, $config['username'], $config['password'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_PERSISTENT => true, // Enable persistent connections
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4",
                PDO::ATTR_TIMEOUT => 30
            ]);
            
            self::$connections[] = $pdo;
            return $pdo;
        }
        
        return array_pop(self::$connections);
    }
    
    public static function returnConnection($connection) {
        self::$connections[] = $connection;
    }
}
```

## Application Code Optimization

### 1. Code Profiling

```php
// Simple profiler
class Profiler {
    private static $marks = [];
    
    public static function mark($name) {
        self::$marks[$name] = microtime(true);
    }
    
    public static function measure($start, $end) {
        return self::$marks[$end] - self::$marks[$start];
    }
    
    public static function report() {
        foreach (self::$marks as $name => $time) {
            printf("%s: %.4f seconds\n", $name, $time);
        }
    }
}

// Usage
Profiler::mark('start');

$users = getUsers(); // Your function
Profiler::mark('after_users');

$stats = generateStats();
Profiler::mark('after_stats');

echo "Total time: " . Profiler::measure('start', 'after_stats') . "\n";
```

### 2. Lazy Loading

```php
// User model with lazy loading
class User {
    private $id;
    private $data = null;
    private $attendance = null;
    private $courses = null;
    
    public function __construct($id) {
        $this->id = $id;
    }
    
    // Load data only when needed
    public function getName() {
        if ($this->data === null) {
            $this->loadData();
        }
        return $this->data['name'];
    }
    
    // Lazy load related data
    public function getAttendance($month = null) {
        if ($this->attendance === null) {
            $this->loadAttendance();
        }
        
        if ($month) {
            return array_filter($this->attendance, function($record) use ($month) {
                return date('Y-m', strtotime($record['date'])) === $month;
            });
        }
        
        return $this->attendance;
    }
    
    private function loadData() {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$this->id]);
        $this->data = $stmt->fetch();
    }
    
    private function loadAttendance() {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM attendance WHERE user_id = ? ORDER BY date DESC");
        $stmt->execute([$this->id]);
        $this->attendance = $stmt->fetchAll();
    }
}
```

### 3. Object Pooling

```php
// Connection pooling for database operations
class DatabaseConnectionPool {
    private static $pool = [];
    private static $maxSize = 20;
    private static $created = 0;
    
    public static function getConnection() {
        if (!empty(self::$pool)) {
            return array_pop(self::$pool);
        }
        
        if (self::$created < self::$maxSize) {
            self::$created++;
            return self::createConnection();
        }
        
        // Wait or throw exception
        throw new Exception("Connection pool exhausted");
    }
    
    public static function returnConnection($connection) {
        if (count(self::$pool) < self::$maxSize) {
            self::$pool[] = $connection;
        }
    }
    
    private static function createConnection() {
        return new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME,
            DB_USER,
            DB_PASS,
            [PDO::ATTR_PERSISTENT => true]
        );
    }
}
```

### 4. Optimize Loops and Arrays

```php
// Good - Use associative arrays for lookups
$users = [
    1 => ['name' => 'John', 'email' => 'john@example.com'],
    2 => ['name' => 'Jane', 'email' => 'jane@example.com']
];

// Fast lookup
$user = $users[$userId];

// Avoid nested loops when possible
$userNames = array_column($users, 'name');

// Use array_map for transformations
$formattedUsers = array_map(function($user) {
    return [
        'name' => ucwords($user['name']),
        'email' => strtolower($user['email'])
    ];
}, $users);

// Use array_filter for filtering
$activeUsers = array_filter($users, function($user) {
    return $user['is_active'];
});
```

## Caching Strategies

### 1. File-based Cache

```php
class FileCache {
    private $cacheDir;
    
    public function __construct($cacheDir = 'cache/') {
        $this->cacheDir = $cacheDir;
        if (!is_dir($this->cacheDir)) {
            mkdir($this->cacheDir, 0755, true);
        }
    }
    
    public function get($key) {
        $file = $this->cacheDir . md5($key) . '.cache';
        
        if (!file_exists($file)) {
            return null;
        }
        
        $data = unserialize(file_get_contents($file));
        
        if ($data['expires'] < time()) {
            unlink($file);
            return null;
        }
        
        return $data['value'];
    }
    
    public function set($key, $value, $ttl = 3600) {
        $file = $this->cacheDir . md5($key) . '.cache';
        $data = [
            'value' => $value,
            'expires' => time() + $ttl
        ];
        
        file_put_contents($file, serialize($data), LOCK_EX);
    }
    
    public function delete($key) {
        $file = $this->cacheDir . md5($key) . '.cache';
        if (file_exists($file)) {
            unlink($file);
        }
    }
    
    public function flush() {
        $files = glob($this->cacheDir . '*.cache');
        foreach ($files as $file) {
            unlink($file);
        }
    }
}

// Usage
$cache = new FileCache();

$cacheKey = "user_stats_" . $userId;
$stats = $cache->get($cacheKey);

if ($stats === null) {
    $stats = generateUserStats($userId);
    $cache->set($cacheKey, $stats, 3600); // Cache for 1 hour
}
```

### 2. Redis Cache (Production)

```php
class RedisCache {
    private $redis;
    
    public function __construct() {
        $this->redis = new Redis();
        $this->redis->connect('127.0.0.1', 6379);
        $this->redis->select(0); // Database 0
    }
    
    public function get($key) {
        $data = $this->redis->get($key);
        return $data ? unserialize($data) : null;
    }
    
    public function set($key, $value, $ttl = 3600) {
        $data = serialize($value);
        $this->redis->setex($key, $ttl, $data);
    }
    
    public function delete($key) {
        $this->redis->del($key);
    }
    
    public function flush() {
        $this->redis->flushdb();
    }
    
    // Cache tags for selective invalidation
    public function setWithTags($key, $value, $tags, $ttl = 3600) {
        $this->set($key, $value, $ttl);
        
        foreach ($tags as $tag) {
            $this->redis->sAdd("tag:$tag", $key);
        }
    }
    
    public function invalidateTag($tag) {
        $keys = $this->redis->sMembers("tag:$tag");
        if (!empty($keys)) {
            $this->redis->del($keys);
            $this->redis->del("tag:$tag");
        }
    }
}
```

### 3. Database Query Cache

```php
class QueryCache {
    private $cache;
    
    public function __construct($cache) {
        $this->cache = $cache;
    }
    
    public function cachedQuery($sql, $params = [], $ttl = 3600) {
        $cacheKey = md5($sql . serialize($params));
        
        $result = $this->cache->get($cacheKey);
        if ($result !== null) {
            return $result;
        }
        
        $db = Database::getConnection();
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetchAll();
        
        $this->cache->set($cacheKey, $result, $ttl);
        return $result;
    }
    
    public function invalidateQuery($sql, $params = []) {
        $cacheKey = md5($sql . serialize($params));
        $this->cache->delete($cacheKey);
    }
    
    public function invalidateByTable($table) {
        // Invalidate all queries related to a table
        // Implementation depends on your cache strategy
    }
}
```

## Web Server Optimization

### 1. Apache Optimization

**`.htaccess` optimization:**
```apache
# Enable compression
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/html
    AddOutputFilterByType DEFLATE text/css
    AddOutputFilterByType DEFLATE text/javascript
    AddOutputFilterByType DEFLATE application/javascript
    AddOutputFilterByType DEFLATE application/x-javascript
    AddOutputFilterByType DEFLATE application/json
    AddOutputFilterByType DEFLATE application/xml
    AddOutputFilterByType DEFLATE text/xml
</IfModule>

# Set cache headers
<IfModule mod_expires.c>
    ExpiresActive On
    ExpiresByType text/css "access plus 1 year"
    ExpiresByType application/javascript "access plus 1 year"
    ExpiresByType image/png "access plus 1 year"
    ExpiresByType image/jpg "access plus 1 year"
    ExpiresByType image/jpeg "access plus 1 year"
    ExpiresByType image/gif "access plus 1 year"
    ExpiresByType application/pdf "access plus 1 year"
</IfModule>

# Enable browser caching
<IfModule mod_headers.c>
    <FilesMatch "\.(css|js|png|jpg|jpeg|gif|pdf)$">
        Header set Cache-Control "max-age=31536000, public"
    </FilesMatch>
</IfModule>

# Optimize PHP
<IfModule mod_php.c>
    php_value memory_limit 256M
    php_value max_execution_time 60
    php_value upload_max_filesize 10M
    php_value post_max_size 10M
    php_flag log_errors on
    php_flag display_errors off
</IfModule>

# Prevent access to sensitive files
<Files ~ "^\.">
    Order allow,deny
    Deny from all
</Files>

<Files "*.log">
    Order allow,deny
    Deny from all
</Files>

<Files "config.php">
    Order allow,deny
    Deny from all
</Files>
```

### 2. Nginx Optimization

```nginx
# nginx.conf optimizations

# Worker processes
worker_processes auto;
worker_connections 1024;

# Buffer sizes
client_body_buffer_size 128k;
client_max_body_size 10m;
client_header_buffer_size 1k;
large_client_header_buffers 4 4k;

# Timeouts
client_body_timeout 12;
client_header_timeout 12;
keepalive_timeout 15;
send_timeout 10;

# Gzip compression
gzip on;
gzip_vary on;
gzip_min_length 1024;
gzip_proxied any;
gzip_comp_level 6;
gzip_types
    text/plain
    text/css
    text/xml
    text/javascript
    application/json
    application/javascript
    application/xml+rss
    application/atom+xml
    image/svg+xml;

# FastCGI caching
fastcgi_cache_path /var/cache/nginx levels=1:2 keys_zone=php:10m max_size=1g inactive=60m;
fastcgi_cache_key "$scheme$request_method$host$request_uri";

server {
    listen 80;
    server_name yourdomain.com;
    root /var/www/html/attendance-system;
    index index.php index.html;

    # Static file caching
    location ~* \.(css|js|png|jpg|jpeg|gif|ico|svg)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
        add_header Vary Accept-Encoding;
        access_log off;
    }

    # PHP handling
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.0-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
        
        # Enable FastCGI caching
        fastcgi_cache php;
        fastcgi_cache_valid 200 301 302 10m;
        fastcgi_cache_valid 404 1m;
        fastcgi_cache_use_stale updating error timeout invalid_header http_500;
        add_header X-FastCGI-Cache $upstream_cache_status;
    }

    # Security
    location ~ /\. {
        deny all;
    }
    
    location ~ ^/(config|logs|scripts)/ {
        deny all;
    }

    # Health check
    location = /health {
        access_log off;
        return 200 "healthy\n";
        add_header Content-Type text/plain;
    }
}
```

### 3. PHP-FPM Optimization

```ini
; /etc/php/8.0/fpm/pool.d/www.conf

[www]
user = www-data
group = www-data

; Process management
pm = dynamic
pm.max_children = 50
pm.start_servers = 5
pm.min_spare_servers = 5
pm.max_spare_servers = 35
pm.max_requests = 1000

; Performance
pm.process_idle_timeout = 10s
request_terminate_timeout = 60s
rlimit_files = 131072
rlimit_core = 0

; Monitoring
pm.status_path = /status
ping.path = /ping

; Logging
access.log = /var/log/php8.0-fpm.access.log
slowlog = /var/log/php8.0-fpm.slow.log
request_slowlog_timeout = 5s

; PHP settings
php_admin_value[error_log] = /var/log/php8.0-fpm.error.log
php_admin_flag[log_errors] = on
php_value[session.save_handler] = files
php_value[session.save_path] = /var/lib/php/sessions
php_value[soap.wsdl_cache_dir] = /var/lib/php/wsdlcache
```

## Frontend Optimization

### 1. CSS and JavaScript Minification

```bash
#!/bin/bash
# minify-assets.sh

# Minify CSS
find assets/css/ -name "*.css" -exec minify {} -o {}.min \;

# Minify JavaScript
find assets/js/ -name "*.js" -exec uglifyjs {} -o {}.min \;

# Combine files
cat assets/css/*.min.css > assets/css/combined.min.css
cat assets/js/*.min.js > assets/js/combined.min.js
```

### 2. Image Optimization

```bash
#!/bin/bash
# optimize-images.sh

# Optimize JPEG images
find assets/images/ -name "*.jpg" -o -name "*.jpeg" | while read img; do
    jpegoptim --max=85 --strip-all --preserve "$img"
done

# Optimize PNG images
find assets/images/ -name "*.png" | while read img; do
    optipng -o2 "$img"
done

# Create WebP versions
find assets/images/ -name "*.jpg" -o -name "*.png" | while read img; do
    cwebp -q 85 "$img" -o "${img%.*}.webp"
done
```

### 3. Lazy Loading Implementation

```javascript
// Lazy load images
document.addEventListener('DOMContentLoaded', function() {
    const images = document.querySelectorAll('img[data-src]');
    
    const imageObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const img = entry.target;
                img.src = img.dataset.src;
                img.removeAttribute('data-src');
                observer.unobserve(img);
            }
        });
    });
    
    images.forEach(img => imageObserver.observe(img));
});

// Lazy load JavaScript modules
function loadModule(moduleName) {
    return import(`./modules/${moduleName}.js`);
}
```

## CDN and Static Assets

### 1. CDN Configuration

```php
class CDNHelper {
    private static $cdnDomain = 'https://cdn.yourdomain.com';
    
    public static function asset($path) {
        if (CDN_ENABLED) {
            return self::$cdnDomain . '/' . ltrim($path, '/');
        }
        return APP_URL . '/' . ltrim($path, '/');
    }
    
    public static function css($file) {
        return '<link rel="stylesheet" href="' . self::asset('css/' . $file) . '">';
    }
    
    public static function js($file) {
        return '<script src="' . self::asset('js/' . $file) . '"></script>';
    }
    
    public static function image($path, $alt = '', $attributes = []) {
        $attrs = '';
        foreach ($attributes as $key => $value) {
            $attrs .= " $key=\"$value\"";
        }
        return '<img src="' . self::asset($path) . "\" alt=\"$alt\"$attrs>";
    }
}
```

### 2. Asset Versioning

```php
class AssetManager {
    public static function css($file) {
        $version = self::getFileVersion('assets/css/' . $file);
        return '<link rel="stylesheet" href="' . APP_URL . '/assets/css/' . $file . '?v=' . $version . '">';
    }
    
    public static function js($file) {
        $version = self::getFileVersion('assets/js/' . $file);
        return '<script src="' . APP_URL . '/assets/js/' . $file . '?v=' . $version . '"></script>';
    }
    
    private static function getFileVersion($file) {
        $path = __DIR__ . '/../' . $file;
        if (file_exists($path)) {
            return filemtime($path);
        }
        return '1.0.0';
    }
}
```

## Database Optimization

### 1. Query Result Caching

```php
class CachedDatabase {
    private $db;
    private $cache;
    private $cacheTime = 300; // 5 minutes
    
    public function __construct($database, $cache) {
        $this->db = $database;
        $this->cache = $cache;
    }
    
    public function query($sql, $params = [], $cache = true) {
        if (!$cache) {
            return $this->db->query($sql, $params);
        }
        
        $cacheKey = md5($sql . serialize($params));
        $cached = $this->cache->get($cacheKey);
        
        if ($cached !== null) {
            return $cached;
        }
        
        $result = $this->db->query($sql, $params);
        $this->cache->set($cacheKey, $result, $this->cacheTime);
        
        return $result;
    }
    
    public function invalidate($pattern) {
        $this->cache->invalidatePattern($pattern);
    }
}
```

### 2. Batch Operations

```php
// Batch insert for better performance
class BatchInsert {
    public function insertAttendance($records) {
        if (empty($records)) {
            return 0;
        }
        
        $db = Database::getConnection();
        
        // Prepare statement once
        $sql = "INSERT INTO attendance (user_id, date, status, notes) VALUES (?, ?, ?, ?)";
        $stmt = $db->prepare($sql);
        
        $inserted = 0;
        $db->beginTransaction();
        
        try {
            foreach ($records as $record) {
                $stmt->execute([
                    $record['user_id'],
                    $record['date'],
                    $record['status'],
                    $record['notes'] ?? null
                ]);
                $inserted++;
            }
            
            $db->commit();
            return $inserted;
        } catch (Exception $e) {
            $db->rollback();
            throw $e;
        }
    }
    
    // Bulk update
    public function updateStatus($userIds, $status) {
        $placeholders = str_repeat('?,', count($userIds) - 1) . '?';
        
        $sql = "UPDATE attendance SET status = ? WHERE user_id IN ($placeholders)";
        $params = array_merge([$status], $userIds);
        
        $db = Database::getConnection();
        $stmt = $db->prepare($sql);
        return $stmt->execute($params);
    }
}
```

### 3. Database Connection Optimization

```php
// Connection pooling for read replicas
class ReadReplicaDatabase {
    private $master;
    private $replicas = [];
    
    public function __construct($master, $replicas) {
        $this->master = $master;
        $this->replicas = $replicas;
    }
    
    public function query($sql, $params = []) {
        // Use master for writes or complex queries
        if ($this->isWriteQuery($sql) || $this->isComplexQuery($sql)) {
            return $this->master->query($sql, $params);
        }
        
        // Use replica for reads
        $replica = $this->getRandomReplica();
        return $replica->query($sql, $params);
    }
    
    private function isWriteQuery($sql) {
        return preg_match('/^\s*(INSERT|UPDATE|DELETE|REPLACE|CREATE|DROP|ALTER)/i', $sql);
    }
    
    private function isComplexQuery($sql) {
        return preg_match('/(JOIN|UNION|SUBQUERY)/i', $sql);
    }
    
    private function getRandomReplica() {
        return $this->replicas[array_rand($this->replicas)];
    }
}
```

## Memory and Resource Management

### 1. Memory Optimization

```php
// Stream large datasets
class StreamProcessor {
    public function processLargeDataset($query, $callback) {
        $db = Database::getConnection();
        $stmt = $db->prepare($query);
        $stmt->execute();
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $callback($row);
            
            // Free memory periodically
            if (memory_get_usage() > 100 * 1024 * 1024) { // 100MB
                gc_collect_cycles();
            }
        }
    }
    
    public function exportLargeCSV($query, $filename) {
        $handle = fopen($filename, 'w');
        
        // Write header
        $first = true;
        $this->processLargeDataset($query, function($row) use ($handle, &$first) {
            if ($first) {
                fputcsv($handle, array_keys($row));
                $first = false;
            }
            fputcsv($handle, $row);
        });
        
        fclose($handle);
    }
}
```

### 2. Garbage Collection

```php
// Explicit garbage collection when needed
class MemoryManager {
    public static function monitor($threshold = 80) {
        $usage = memory_get_usage(true);
        $limit = ini_get('memory_limit');
        $limitBytes = self::parseMemoryLimit($limit);
        
        $percentage = ($usage / $limitBytes) * 100;
        
        if ($percentage > $threshold) {
            self::optimize();
        }
        
        return [
            'usage' => $usage,
            'limit' => $limitBytes,
            'percentage' => $percentage
        ];
    }
    
    private static function optimize() {
        // Clear object caches
        if (class_exists('APC')) {
            apc_clear_cache();
        }
        
        // Force garbage collection
        gc_collect_cycles();
        
        // Close unnecessary database connections
        Database::closeInactiveConnections();
    }
    
    private static function parseMemoryLimit($limit) {
        $limit = trim($limit);
        $last = strtolower($limit[strlen($limit) - 1]);
        $limit = (int) $limit;
        
        switch ($last) {
            case 'g':
                $limit *= 1024;
            case 'm':
                $limit *= 1024;
            case 'k':
                $limit *= 1024;
        }
        
        return $limit;
    }
}
```

## Monitoring and Profiling

### 1. Performance Monitoring

```php
class PerformanceMonitor {
    private static $metrics = [];
    
    public static function startTimer($name) {
        self::$metrics[$name] = [
            'start' => microtime(true),
            'memory_start' => memory_get_usage()
        ];
    }
    
    public static function endTimer($name) {
        if (!isset(self::$metrics[$name])) {
            return;
        }
        
        self::$metrics[$name]['end'] = microtime(true);
        self::$metrics[$name]['memory_end'] = memory_get_usage();
        self::$metrics[$name]['duration'] = 
            self::$metrics[$name]['end'] - self::$metrics[$name]['start'];
        self::$metrics[$name]['memory_usage'] = 
            self::$metrics[$name]['memory_end'] - self::$metrics[$name]['memory_start'];
    }
    
    public static function getMetrics() {
        return self::$metrics;
    }
    
    public static function logMetrics() {
        foreach (self::$metrics as $name => $metric) {
            if (isset($metric['duration'])) {
                error_log(sprintf(
                    "Metric %s: %.4f seconds, %.2f KB",
                    $name,
                    $metric['duration'],
                    $metric['memory_usage'] / 1024
                ));
            }
        }
    }
}

// Usage
PerformanceMonitor::startTimer('user_dashboard');

$users = getUsers(); // Your function
$stats = generateStats();

PerformanceMonitor::endTimer('user_dashboard');
PerformanceMonitor::logMetrics();
```

### 2. Database Query Profiler

```php
class QueryProfiler {
    private static $queries = [];
    private static $enabled = false;
    
    public static function enable() {
        self::$enabled = true;
    }
    
    public static function record($sql, $params, $duration) {
        if (!self::$enabled) {
            return;
        }
        
        self::$queries[] = [
            'sql' => $sql,
            'params' => $params,
            'duration' => $duration,
            'timestamp' => microtime(true)
        ];
    }
    
    public static function getSlowQueries($threshold = 0.1) {
        return array_filter(self::$queries, function($query) use ($threshold) {
            return $query['duration'] > $threshold;
        });
    }
    
    public static function getReport() {
        $totalTime = array_sum(array_column(self::$queries, 'duration'));
        $avgTime = $totalTime / count(self::$queries);
        
        return [
            'total_queries' => count(self::$queries),
            'total_time' => $totalTime,
            'average_time' => $avgTime,
            'slow_queries' => self::getSlowQueries()
        ];
    }
}
```

## Production Performance Tuning

### 1. Production PHP Configuration

```ini
; production-php.ini

; Memory and execution
memory_limit = 256M
max_execution_time = 60
max_input_time = 60
max_input_vars = 3000

; Error reporting
display_errors = Off
display_startup_errors = Off
log_errors = On
error_reporting = E_ALL & ~E_DEPRECATED & ~E_STRICT

; Session configuration
session.save_handler = files
session.save_path = /var/lib/php/sessions
session.gc_maxlifetime = 3600
session.cookie_secure = 1
session.cookie_httponly = 1
session.use_only_cookies = 1

; OPcache configuration
opcache.enable = 1
opcache.enable_cli = 1
opcache.memory_consumption = 256
opcache.interned_strings_buffer = 16
opcache.max_accelerated_files = 10000
opcache.validate_timestamps = 0
opcache.revalidate_freq = 0
opcache.fast_shutdown = 1

; Realpath cache
realpath_cache_size = 4096k
realpath_cache_ttl = 600

; Upload limits
upload_max_filesize = 10M
post_max_size = 10M
max_file_uploads = 20
```

### 2. MySQL Production Configuration

```ini
; my.cnf production settings

[mysqld]
# Memory settings
innodb_buffer_pool_size = 1G
innodb_log_file_size = 256M
innodb_log_buffer_size = 16M
innodb_flush_log_at_trx_commit = 2
innodb_flush_method = O_DIRECT

# Query cache (MySQL 5.7 and earlier)
query_cache_type = 1
query_cache_size = 128M
query_cache_limit = 2M

# Connection settings
max_connections = 200
max_connect_errors = 100000
thread_cache_size = 50
table_open_cache = 4000

# Slow query log
slow_query_log = 1
slow_query_log_file = /var/log/mysql/slow.log
long_query_time = 2

# Binary logging
log_bin = /var/log/mysql/mysql-bin.log
expire_logs_days = 7
max_binlog_size = 100M

# InnoDB settings
innodb_file_per_table = 1
innodb_open_files = 400
innodb_io_capacity = 400
innodb_read_io_threads = 8
innodb_write_io_threads = 8
```

### 3. Monitoring Scripts

```bash
#!/bin/bash
# monitor-performance.sh

# Check memory usage
MEMORY_USAGE=$(free | grep Mem | awk '{printf "%.2f", $3/$2 * 100.0}')
echo "Memory usage: ${MEMORY_USAGE}%"

# Check disk usage
DISK_USAGE=$(df / | tail -1 | awk '{print $5}' | sed 's/%//')
echo "Disk usage: ${DISK_USAGE}%"

# Check database connections
DB_CONNECTIONS=$(mysql -e "SHOW STATUS LIKE 'Threads_connected';" | grep Threads_connected | awk '{print $2}')
echo "Database connections: ${DB_CONNECTIONS}"

# Check slow queries
SLOW_QUERIES=$(mysql -e "SHOW STATUS LIKE 'Slow_queries';" | grep Slow_queries | awk '{print $2}')
echo "Slow queries: ${SLOW_QUERIES}"

# Check PHP-FPM status
PHP_FPM_STATUS=$(systemctl is-active php8.0-fpm)
echo "PHP-FPM status: ${PHP_FPM_STATUS}"

# Check Apache/Nginx status
WEB_SERVER_STATUS=$(systemctl is-active apache2 || systemctl is-active nginx)
echo "Web server status: ${WEB_SERVER_STATUS}"

# Log to file
{
    date
    echo "Memory: ${MEMORY_USAGE}%, Disk: ${DISK_USAGE}%"
    echo "DB Connections: ${DB_CONNECTIONS}, Slow Queries: ${SLOW_QUERIES}"
    echo "PHP-FPM: ${PHP_FPM_STATUS}, Web Server: ${WEB_SERVER_STATUS}"
    echo "---"
} >> /var/log/performance.log

# Alert if thresholds exceeded
if (( $(echo "$MEMORY_USAGE > 80" | bc -l) )); then
    echo "High memory usage alert!" | mail -s "Performance Alert" admin@example.com
fi

if (( DISK_USAGE > 90 )); then
    echo "High disk usage alert!" | mail -s "Performance Alert" admin@example.com
fi
```

### 4. Auto-scaling Configuration

```php
// Auto-scaling based on load
class AutoScaler {
    private $minInstances = 2;
    private $maxInstances = 10;
    private $targetCPU = 70;
    
    public function checkAndScale() {
        $currentLoad = $this->getCurrentCPUUsage();
        $currentInstances = $this->getCurrentInstances();
        
        if ($currentLoad > $this->targetCPU && $currentInstances < $this->maxInstances) {
            $this->scaleUp();
        } elseif ($currentLoad < 30 && $currentInstances > $this->minInstances) {
            $this->scaleDown();
        }
    }
    
    private function getCurrentCPUUsage() {
        $load = sys_getloadavg();
        return $load[0] * 100 / CPU_COUNT;
    }
    
    private function getCurrentInstances() {
        // Implement based on your infrastructure
        return shell_exec("ps aux | grep -c '[p]hp-fpm'");
    }
    
    private function scaleUp() {
        // Implement based on your hosting provider
        // Example for AWS Auto Scaling
        shell_exec("aws autoscaling set-desired-capacity --auto-scaling-group-name attendance-asg --desired-capacity 3");
    }
    
    private function scaleDown() {
        // Implement based on your hosting provider
        shell_exec("aws autoscaling set-desired-capacity --auto-scaling-group-name attendance-asg --desired-capacity 2");
    }
}
```

This comprehensive performance optimization guide covers all aspects of optimizing your Employee Attendance System for production use. Implement these optimizations incrementally and monitor the impact on your specific workload and infrastructure.