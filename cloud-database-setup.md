# Cloud Database Setup Guide

## Overview

This guide provides comprehensive instructions for setting up cloud databases for the Employee Attendance System, covering various cloud providers and database services.

## Cloud Database Options

### 1. AWS RDS (Relational Database Service)

#### Supported Engines
- MySQL 5.7, 8.0
- PostgreSQL 9.6, 10, 11, 12, 13, 14
- MariaDB 10.2, 10.3, 10.4, 10.5, 10.6

#### Setup Steps

1. **Access AWS RDS Console**
   - Sign in to AWS Management Console
   - Navigate to RDS service

2. **Create Database Instance**
   ```bash
   # Using AWS CLI (alternative)
   aws rds create-db-instance \
       --db-instance-identifier attendance-system-db \
       --db-instance-class db.t3.micro \
       --engine mysql \
       --engine-version 8.0.28 \
       --master-username admin \
       --master-user-password YourSecurePassword123 \
       --allocated-storage 20 \
       --vpc-security-group-ids sg-xxxxxxxxx
   ```

3. **Configure Database Instance**
   - **Engine:** MySQL 8.0
   - **Instance Type:** db.t3.micro (free tier eligible)
   - **Storage:** 20 GB GP2
   - **VPC:** Your application VPC
   - **Security Group:** Allow MySQL/Aurora (3306)

4. **Database Configuration**
   ```php
   // config/database.php for AWS RDS
   define('DB_HOST', 'attendance-system-db.xxxxxx.us-east-1.rds.amazonaws.com');
   define('DB_PORT', '3306');
   define('DB_NAME', 'attendance_db');
   define('DB_USER', 'admin');
   define('DB_PASS', 'YourSecurePassword123');
   define('DB_CHARSET', 'utf8mb4');
   ```

5. **Connection String (for environment variables)**
   ```env
   DATABASE_URL=mysql://admin:YourSecurePassword123@attendance-system-db.xxxxxx.us-east-1.rds.amazonaws.com:3306/attendance_db
   ```

6. **Backup Configuration**
   - **Backup Retention Period:** 7 days (recommended)
   - **Backup Window:** 03:00-04:00 UTC
   - **Automated Backups:** Enabled

#### Cost Optimization
- **Free Tier:** db.t3.micro, 750 hours/month, 20GB storage
- **Production:** db.t3.small ($16.45/month)
- **Use Reserved Instances for production databases**

#### Security Best Practices
```sql
-- Create application user with limited privileges
CREATE USER 'attendance_app'@'%' IDENTIFIED BY 'AppSecurePassword123';
GRANT SELECT, INSERT, UPDATE, DELETE ON attendance_db.* TO 'attendance_app'@'%';
FLUSH PRIVILEGES;

-- Revoke super user privileges
REVOKE ALL PRIVILEGES, GRANT OPTION FROM 'admin'@'%';
```

---

### 2. Google Cloud SQL

#### Supported Engines
- MySQL 5.6, 5.7, 8.0
- PostgreSQL 9.6, 10, 11, 12, 13, 14

#### Setup Steps

1. **Create Cloud SQL Instance**
   ```bash
   # Using gcloud CLI
   gcloud sql instances create attendance-db \
       --database-version=MYSQL_8_0 \
       --tier=db-f1-micro \
       --region=us-central1
   ```

2. **Create Database**
   ```bash
   gcloud sql databases create attendance_db --instance=attendance-db
   ```

3. **Create User**
   ```bash
   gcloud sql users create attendance_user \
       --instance=attendance-db \
       --password=SecurePassword123
   ```

4. **Configuration**
   ```php
   // config/database.php for Google Cloud SQL
   define('DB_HOST', 'attendance-db:us-central1:attendance-db');
   define('DB_PORT', '3306');
   define('DB_NAME', 'attendance_db');
   define('DB_USER', 'attendance_user');
   define('DB_PASS', 'SecurePassword123');
   define('DB_CHARSET', 'utf8mb4');
   ```

5. **Connection with SSL (Recommended)**
   ```php
   $pdo = new PDO(
       "mysql:host=attendance-db;dbname=attendance_db;charset=utf8mb4",
       "attendance_user",
       "SecurePassword123",
       [
           PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => true,
           PDO::MYSQL_ATTR_SSL_CA => '/path/to/server-ca.pem',
           PDO::MYSQL_ATTR_SSL_CERT => '/path/to/client-cert.pem',
           PDO::MYSQL_ATTR_SSL_KEY => '/path/to/client-key.pem'
       ]
   );
   ```

#### Cloud SQL Auth Proxy (Recommended for security)
```bash
# Download and install proxy
curl -o cloud_sql_proxy https://dl.google.com/cloudsql/cloud_sql_proxy.linux.amd64
chmod +x cloud_sql_proxy

# Start proxy
./cloud_sql_proxy -instances=attendance-db:us-central1:attendance-db=tcp:3306

# Use in application
// config/database.php
define('DB_HOST', '127.0.0.1');
define('DB_PORT', '3306');
define('DB_NAME', 'attendance_db');
define('DB_USER', 'attendance_user');
define('DB_PASS', 'SecurePassword123');
```

---

### 3. Microsoft Azure Database for MySQL

#### Setup Steps

1. **Create Azure Database for MySQL**
   ```bash
   # Using Azure CLI
   az mysql server create \
       --resource-group myResourceGroup \
       --name attendance-mysql-server \
       --location eastus \
       --admin-user myadmin \
       --admin-password SecurePassword123! \
       --sku-name B_Gen5_1 \
       --version 8.0
   ```

2. **Configure Firewall**
   ```bash
   # Allow Azure services
   az mysql server firewall-rule create \
       --resource-group myResourceGroup \
       --server attendance-mysql-server \
       --name AllowAzureIps \
       --start-ip-address 0.0.0.0 \
       --end-ip-address 0.0.0.0

   # Allow your application IP
   az mysql server firewall-rule create \
       --resource-group myResourceGroup \
       --server attendance-mysql-server \
       --name AllowAppIP \
       --start-ip-address YOUR_APP_IP \
       --end-ip-address YOUR_APP_IP
   ```

3. **Configuration**
   ```php
   // config/database.php for Azure
   define('DB_HOST', 'attendance-mysql-server.mysql.database.azure.com');
   define('DB_PORT', '3306');
   define('DB_NAME', 'attendance_db');
   define('DB_USER', 'myadmin@attendance-mysql-server');
   define('DB_PASS', 'SecurePassword123!');
   define('DB_CHARSET', 'utf8mb4');
   ```

---

### 4. Railway Cloud Database

#### Setup Steps

1. **Create Railway Project**
   - Sign up at railway.app
   - Create new project

2. **Add MySQL Database**
   - Click "Add Service"
   - Select "MySQL"
   - Railway automatically creates database

3. **Get Connection Details**
   - Go to your database service
   - Copy connection string

4. **Configuration**
   ```php
   // Railway provides connection string
   // Add as environment variable
   DATABASE_URL=mysql://username:password@hostname:port/database
   
   // Parse in config.php
   $db_url = parse_url(getenv('DATABASE_URL'));
   
   define('DB_HOST', $db_url['host']);
   define('DB_PORT', isset($db_url['port']) ? $db_url['port'] : '3306');
   define('DB_NAME', ltrim($db_url['path'], '/'));
   define('DB_USER', $db_url['user']);
   define('DB_PASS', $db_url['pass']);
   ```

---

### 5. PlanetScale (MySQL Serverless)

#### Setup Steps

1. **Create PlanetScale Database**
   ```bash
   # Install pscale CLI
   npm install -g @planetscale/database

   # Login
   pscale auth login

   # Create database
   pscale database create attendance-system
   ```

2. **Create Branch**
   ```bash
   pscale branch create attendance-system main
   ```

3. **Get Connection String**
   ```php
   // PlanetScale connection string format
   // mysql://username:password@aws.connect.psdb.cloud/database_name?ssl_accept_strict=true

   // config/database.php
   define('DB_HOST', 'aws.connect.psdb.cloud');
   define('DB_PORT', '3306');
   define('DB_NAME', 'attendance-system');
   define('DB_USER', 'your_username');
   define('DB_PASS', 'your_password');
   define('DB_SSL', true);
   ```

4. **SSL Configuration**
   ```php
   $pdo = new PDO(
       "mysql:host=aws.connect.psdb.cloud;dbname=attendance-system;charset=utf8mb4",
       "username",
       "password",
       [
           PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => true,
           PDO::MYSQL_ATTR_SSL_CA => '/path/to/ca-cert.pem'
       ]
   );
   ```

---

### 6. Supabase (PostgreSQL)

#### Setup Steps

1. **Create Supabase Project**
   - Go to supabase.com
   - Create new project

2. **Database Connection**
   ```php
   // config/database.php for Supabase
   define('DB_HOST', 'db.xxxxxx.supabase.co');
   define('DB_PORT', '5432');
   define('DB_NAME', 'postgres');
   define('DB_USER', 'postgres');
   define('DB_PASS', 'your_password');
   define('DB_ENGINE', 'postgresql');
   ```

3. **Connection with PDO**
   ```php
   try {
       $pdo = new PDO(
           "pgsql:host=db.xxxxxx.supabase.co;port=5432;dbname=postgres",
           "postgres",
           "your_password",
           [
               PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
               PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
           ]
       );
   } catch (PDOException $e) {
       die("Connection failed: " . $e->getMessage());
   }
   ```

---

## Database Migration Guide

### From Local MySQL to Cloud

1. **Export Local Database**
   ```bash
   mysqldump -u root -p --single-transaction --routines --triggers attendance_db > backup.sql
   ```

2. **Transfer to Cloud Provider**
   - Upload backup.sql to cloud storage
   - Or use secure file transfer

3. **Import to Cloud Database**

   **AWS RDS:**
   ```bash
   mysql -h attendance-system-db.xxxxxx.us-east-1.rds.amazonaws.com \
         -u admin -p attendance_db < backup.sql
   ```

   **Google Cloud SQL:**
   ```bash
   gcloud sql import sql attendance-db backup.sql --database=attendance_db
   ```

   **Azure Database:**
   ```bash
   az mysql db import -g myResourceGroup -s attendance-mysql-server \
                      --name attendance_db --file backup.sql
   ```

---

## Configuration Best Practices

### 1. Environment Variables (.env)
```env
# Database Configuration
DB_HOST=your-cloud-host
DB_PORT=3306
DB_NAME=attendance_db
DB_USER=app_user
DB_PASS=secure_password
DB_ENGINE=mysql

# SSL Configuration
DB_SSL=true
DB_SSL_CA=/path/to/ca-cert.pem
DB_SSL_CERT=/path/to/client-cert.pem
DB_SSL_KEY=/path/to/client-key.pem

# Connection Pool
DB_POOL_SIZE=10
DB_TIMEOUT=30
DB_MAX_RETRIES=3
```

### 2. Connection Pool Configuration
```php
// config/database.php
class Database {
    private static $instance = null;
    private $connection;
    
    private function __construct() {
        $config = [
            'host' => getenv('DB_HOST'),
            'port' => getenv('DB_PORT'),
            'dbname' => getenv('DB_NAME'),
            'username' => getenv('DB_USER'),
            'password' => getenv('DB_PASS'),
            'charset' => 'utf8mb4'
        ];
        
        $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['dbname']};charset={$config['charset']}";
        
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_PERSISTENT => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES {$config['charset']} COLLATE utf8mb4_unicode_ci",
            PDO::ATTR_TIMEOUT => 30,
            PDO::ATTR_EMULATE_PREPARES => false
        ];
        
        if (getenv('DB_SSL') === 'true') {
            $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = true;
            if (getenv('DB_SSL_CA')) {
                $options[PDO::MYSQL_ATTR_SSL_CA] = getenv('DB_SSL_CA');
            }
        }
        
        try {
            $this->connection = new PDO($dsn, $config['username'], $config['password'], $options);
        } catch (PDOException $e) {
            error_log("Database connection failed: " . $e->getMessage());
            throw $e;
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        return $this->connection;
    }
}
```

### 3. Backup Strategy
```bash
#!/bin/bash
# backup-database.sh

# Configuration
DB_HOST=$1
DB_NAME=$2
DB_USER=$3
DB_PASS=$4
BACKUP_DIR="/backups/$(date +%Y-%m-%d)"

# Create backup directory
mkdir -p $BACKUP_DIR

# Create database dump
mysqldump -h $DB_HOST -u $DB_USER -p$DB_PASS \
    --single-transaction --routines --triggers \
    $DB_NAME > $BACKUP_DIR/attendance_db.sql

# Compress backup
gzip $BACKUP_DIR/attendance_db.sql

# Upload to cloud storage (example with AWS S3)
aws s3 cp $BACKUP_DIR/attendance_db.sql.gz s3://your-backup-bucket/

# Clean old backups (keep 30 days)
find /backups -type f -mtime +30 -delete

echo "Backup completed: $BACKUP_DIR/attendance_db.sql.gz"
```

### 4. Monitoring and Maintenance
```sql
-- Add to cron job (daily)
-- Check database health

-- Check database size
SELECT 
    table_schema AS 'Database',
    ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS 'Size (MB)'
FROM information_schema.tables 
WHERE table_schema = 'attendance_db'
GROUP BY table_schema;

-- Check connection count
SHOW STATUS LIKE 'Threads_connected';
SHOW STATUS LIKE 'Max_used_connections';

-- Check slow queries
SELECT * FROM performance_schema.events_statements_history 
WHERE event_time > DATE_SUB(NOW(), INTERVAL 1 HOUR)
ORDER BY timer_wait DESC LIMIT 10;
```

---

## Security Considerations

### 1. Network Security
- Use VPCs/private networks when possible
- Configure security groups/firewalls
- Enable SSL/TLS for all connections
- Use connection strings with embedded credentials

### 2. Authentication
- Use strong passwords (minimum 16 characters)
- Enable multi-factor authentication
- Rotate credentials regularly
- Use IAM roles when available

### 3. Encryption
- Encrypt data at rest
- Encrypt data in transit
- Use managed encryption keys (AWS KMS, Azure Key Vault)

### 4. Access Control
- Principle of least privilege
- Separate users for application and admin
- Audit all database access
- Log all connection attempts

---

## Troubleshooting Common Issues

### Connection Timeouts
```php
// Increase timeout in connection string
$dsn = "mysql:host=$host;dbname=$db;charset=utf8mb4;connect_timeout=60";
```

### SSL Certificate Issues
```php
// Disable SSL verification (not recommended for production)
// Only for testing
$options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
```

### Performance Issues
- Enable query caching
- Optimize database indexes
- Use read replicas for reporting
- Monitor slow query logs

### Backup Verification
```bash
# Test backup restore
mysql -h test-host -u test-user -p test_db < test_backup.sql
```

## Cost Comparison

| Provider | Free Tier | Price (Basic) | Storage | Backup |
|----------|-----------|---------------|---------|--------|
| AWS RDS | ✅ | $13/month | 20GB | Included |
| Google Cloud SQL | ✅ | $10/month | 10GB | $0.12/GB |
| Azure MySQL | ✅ | $12/month | 7GB | $0.12/GB |
| Railway | ✅ | $5/month | 1GB | Included |
| PlanetScale | ✅ | $8/month | 1 database | $2/DB |
| Supabase | ✅ | $25/month | 500MB | $2/GB |

Choose based on your specific needs, budget, and existing cloud infrastructure.