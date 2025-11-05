<?php
/**
 * Database Cleanup Script
 * Automated maintenance for the authentication system
 * 
 * Run this script via cron job for regular maintenance:
 * 0 * * * * /usr/bin/php /path/to/cleanup.php
 */

define('AUTH_SYSTEM', true);
require_once __DIR__ . '/includes/auth.php';

echo "Starting database cleanup at " . date('Y-m-d H:i:s') . "\n";

// Initialize database connection
$pdo = get_db_connection();

if (!$pdo) {
    die("ERROR: Could not connect to database\n");
}

$cleaned_records = [];

// 1. Clean up expired password reset tokens
try {
    $stmt = $pdo->prepare("DELETE FROM password_resets WHERE expires_at < NOW()");
    $affected = $stmt->rowCount();
    $cleaned_records['password_resets'] = $affected;
    echo "Cleaned $affected expired password reset tokens\n";
} catch (PDOException $e) {
    echo "ERROR cleaning password reset tokens: " . $e->getMessage() . "\n";
}

// 2. Clean up expired email verification tokens
try {
    $stmt = $pdo->prepare("DELETE FROM email_verifications WHERE expires_at < NOW() AND is_verified = 0");
    $affected = $stmt->rowCount();
    $cleaned_records['email_verifications'] = $affected;
    echo "Cleaned $affected expired email verification tokens\n";
} catch (PDOException $e) {
    echo "ERROR cleaning email verification tokens: " . $e->getMessage() . "\n";
}

// 3. Clean up expired remember me tokens
try {
    $stmt = $pdo->prepare("DELETE FROM remember_tokens WHERE expires_at < NOW()");
    $affected = $stmt->rowCount();
    $cleaned_records['remember_tokens'] = $affected;
    echo "Cleaned $affected expired remember me tokens\n";
} catch (PDOException $e) {
    echo "ERROR cleaning remember me tokens: " . $e->getMessage() . "\n";
}

// 4. Clean up expired and inactive sessions
try {
    $stmt = $pdo->prepare("DELETE FROM user_sessions WHERE expires_at < NOW() OR (last_activity < DATE_SUB(NOW(), INTERVAL 1 DAY) AND is_active = 0)");
    $affected = $stmt->rowCount();
    $cleaned_records['expired_sessions'] = $affected;
    echo "Cleaned $affected expired/inactive sessions\n";
} catch (PDOException $e) {
    echo "ERROR cleaning sessions: " . $e->getMessage() . "\n";
}

// 5. Clean up old activity logs (older than 30 days)
try {
    $stmt = $pdo->prepare("DELETE FROM activity_logs WHERE created_at < DATE_SUB(NOW(), INTERVAL 30 DAY)");
    $affected = $stmt->rowCount();
    $cleaned_records['old_activity_logs'] = $affected;
    echo "Cleaned $affected old activity logs\n";
} catch (PDOException $e) {
    echo "ERROR cleaning activity logs: " . $e->getMessage() . "\n";
}

// 6. Clean up old login attempts (older than 7 days)
try {
    $stmt = $pdo->prepare("DELETE FROM login_attempts WHERE created_at < DATE_SUB(NOW(), INTERVAL 7 DAY)");
    $affected = $stmt->rowCount();
    $cleaned_records['old_login_attempts'] = $affected;
    echo "Cleaned $affected old login attempt records\n";
} catch (PDOException $e) {
    echo "ERROR cleaning login attempts: " . $e->getMessage() . "\n";
}

// 7. Clean up resolved security events (older than 90 days)
try {
    $stmt = $pdo->prepare("DELETE FROM security_events WHERE status = 'resolved' AND resolved_at < DATE_SUB(NOW(), INTERVAL 90 DAY)");
    $affected = $stmt->rowCount();
    $cleaned_records['old_security_events'] = $affected;
    echo "Cleaned $affected old resolved security events\n";
} catch (PDOException $e) {
    echo "ERROR cleaning security events: " . $e->getMessage() . "\n";
}

// 8. Reset failed login attempts for users whose lockout period has expired
try {
    $stmt = $pdo->prepare("UPDATE users SET failed_attempts = 0, last_failed_attempt = NULL WHERE failed_attempts >= 5 AND last_failed_attempt < DATE_SUB(NOW(), INTERVAL 15 MINUTE)");
    $affected = $stmt->rowCount();
    $cleaned_records['reset_failed_attempts'] = $affected;
    echo "Reset failed attempts for $affected users\n";
} catch (PDOException $e) {
    echo "ERROR resetting failed attempts: " . $e->getMessage() . "\n";
}

// 9. Analyze and optimize tables
try {
    $tables = ['users', 'user_profiles', 'user_sessions', 'password_resets', 'email_verifications', 'activity_logs', 'security_events', 'login_attempts', 'remember_tokens', 'system_settings'];
    
    foreach ($tables as $table) {
        $pdo->exec("ANALYZE TABLE $table");
    }
    echo "Analyzed and optimized database tables\n";
    $cleaned_records['optimized_tables'] = count($tables);
} catch (PDOException $e) {
    echo "ERROR optimizing tables: " . $e->getMessage() . "\n";
}

// 10. Generate cleanup summary
try {
    // Get current statistics
    $stats = [];
    
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM users WHERE is_active = 1");
    $stats['active_users'] = $stmt->fetch()['count'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM user_sessions WHERE is_active = 1 AND expires_at > NOW()");
    $stats['active_sessions'] = $stmt->fetch()['count'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM activity_logs WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)");
    $stats['activities_today'] = $stmt->fetch()['count'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM login_attempts WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR) AND success = 0");
    $stats['failed_logins_today'] = $stmt->fetch()['count'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM security_events WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR) AND severity IN ('high', 'critical')");
    $stats['security_events_today'] = $stmt->fetch()['count'];
    
    // Log cleanup summary
    $summary = [
        'timestamp' => date('Y-m-d H:i:s'),
        'total_records_cleaned' => array_sum($cleaned_records),
        'cleanup_breakdown' => $cleaned_records,
        'current_statistics' => $stats
    ];
    
    error_log("DATABASE CLEANUP SUMMARY: " . json_encode($summary));
    
    echo "\n--- CLEANUP SUMMARY ---\n";
    echo "Total records cleaned: " . array_sum($cleaned_records) . "\n";
    echo "Active users: " . $stats['active_users'] . "\n";
    echo "Active sessions: " . $stats['active_sessions'] . "\n";
    echo "Activities today: " . $stats['activities_today'] . "\n";
    echo "Failed logins today: " . $stats['failed_logins_today'] . "\n";
    echo "Security events today: " . $stats['security_events_today'] . "\n";
    
} catch (PDOException $e) {
    echo "ERROR generating summary: " . $e->getMessage() . "\n";
}

// 11. Check for potential security issues
try {
    // Check for users with too many failed attempts
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM users WHERE failed_attempts >= 3 AND last_failed_attempt > DATE_SUB(NOW(), INTERVAL 1 HOUR)");
    $users_with_attempts = $stmt->fetch()['count'];
    
    if ($users_with_attempts > 0) {
        echo "WARNING: $users_with_attempts users have multiple failed login attempts in the last hour\n";
        error_log("SECURITY ALERT: $users_with_attempts users with multiple failed attempts");
    }
    
    // Check for suspicious IP activity
    $stmt = $pdo->prepare("SELECT ip_address, COUNT(*) as attempts FROM login_attempts WHERE created_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR) AND success = 0 GROUP BY ip_address HAVING attempts > 10");
    $stmt->execute();
    $suspicious_ips = $stmt->fetchAll();
    
    if (!empty($suspicious_ips)) {
        echo "WARNING: " . count($suspicious_ips) . " IP addresses with excessive failed attempts\n";
        foreach ($suspicious_ips as $ip) {
            echo "  - {$ip['ip_address']}: {$ip['attempts']} attempts\n";
            error_log("SUSPICIOUS IP: {$ip['ip_address']} with {$ip['attempts']} failed attempts");
        }
    }
    
} catch (PDOException $e) {
    echo "ERROR checking security issues: " . $e->getMessage() . "\n";
}

// 12. Auto-cleanup PHP session files (if using file-based sessions)
try {
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_gc();
        echo "Cleaned up PHP session files\n";
    }
} catch (Exception $e) {
    echo "ERROR cleaning PHP sessions: " . $e->getMessage() . "\n";
}

echo "\nDatabase cleanup completed at " . date('Y-m-d H:i:s') . "\n";
echo "Summary logged to error log for monitoring\n";

// Optional: Send notification email to admin (uncomment and configure)
// send_cleanup_notification($summary);

exit(0);

function send_cleanup_notification($summary) {
    $to = 'admin@yourdomain.com';
    $subject = 'Database Cleanup Summary - ' . date('Y-m-d H:i:s');
    $message = "Database cleanup completed successfully.\n\n";
    $message .= "Total records cleaned: " . $summary['total_records_cleaned'] . "\n";
    $message .= "Active users: " . $summary['current_statistics']['active_users'] . "\n";
    $message .= "Active sessions: " . $summary['current_statistics']['active_sessions'] . "\n";
    $message .= "Security events today: " . $summary['current_statistics']['security_events_today'] . "\n\n";
    
    $headers = "From: system@yourdomain.com\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
    
    mail($to, $subject, $message, $headers);
}
?>