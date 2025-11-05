<?php
/**
 * Dashboard Page
 * Role-based dashboard with proper access control
 */

define('AUTH_SYSTEM', true);
require_once __DIR__ . '/../includes/auth.php';

// Require authentication
require_auth();

// Get current user
$current_user = get_current_user();

if (!$current_user) {
    header('Location: login.php');
    exit();
}

// Log dashboard access
log_activity($current_user['id'], 'dashboard_access', 'User accessed dashboard');

// Include middleware for additional security
require_once __DIR__ . '/../includes/middleware.php';

// Get user activity log (last 10 activities)
function get_user_activities($user_id, $limit = 10) {
    $pdo = get_db_connection();
    if (!$pdo) {
        return [];
    }
    
    try {
        $stmt = $pdo->prepare("SELECT action, description, ip_address, created_at FROM activity_logs WHERE user_id = ? ORDER BY created_at DESC LIMIT ?");
        $stmt->execute([$user_id, $limit]);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Error fetching user activities: " . $e->getMessage());
        return [];
    }
}

// Get system statistics for admin
function get_system_statistics() {
    $pdo = get_db_connection();
    if (!$pdo) {
        return null;
    }
    
    try {
        $stats = [];
        
        // Total users
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM users");
        $stats['total_users'] = $stmt->fetch()['total'];
        
        // Active users (logged in recently)
        $stmt = $pdo->query("SELECT COUNT(DISTINCT user_id) as active FROM activity_logs WHERE created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)");
        $stats['active_users'] = $stmt->fetch()['active'];
        
        // Recent logins today
        $stmt = $pdo->query("SELECT COUNT(*) as today_logins FROM activity_logs WHERE action = 'login' AND DATE(created_at) = CURDATE()");
        $stats['today_logins'] = $stmt->fetch()['today_logins'];
        
        // Failed login attempts today
        $stmt = $pdo->query("SELECT COUNT(*) as failed_attempts FROM activity_logs WHERE action = 'failed_login' AND DATE(created_at) = CURDATE()");
        $stats['failed_attempts'] = $stmt->fetch()['failed_attempts'];
        
        return $stats;
    } catch (PDOException $e) {
        error_log("Error fetching system statistics: " . $e->getMessage());
        return null;
    }
}

$user_activities = get_user_activities($current_user['id']);
$system_stats = has_role('admin') ? get_system_statistics() : null;

// Set page-specific headers
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - <?php echo htmlspecialchars($current_user['username'], ENT_QUOTES, 'UTF-8'); ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f6fa;
            color: #333;
        }

        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .header-content {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .user-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: rgba(255,255,255,0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            font-weight: bold;
        }

        .logout-btn {
            background: rgba(255,255,255,0.2);
            color: white;
            border: 1px solid rgba(255,255,255,0.3);
            padding: 8px 16px;
            border-radius: 5px;
            text-decoration: none;
            transition: background 0.3s ease;
        }

        .logout-btn:hover {
            background: rgba(255,255,255,0.3);
        }

        .container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 0 20px;
        }

        .welcome-section {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }

        .welcome-section h1 {
            color: #333;
            margin-bottom: 10px;
        }

        .welcome-section .role-badge {
            display: inline-block;
            background: #667eea;
            color: white;
            padding: 4px 12px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: 500;
            text-transform: uppercase;
        }

        .role-badge.admin {
            background: #e74c3c;
        }

        .role-badge.employee {
            background: #27ae60;
        }

        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .card {
            background: white;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: transform 0.2s ease;
        }

        .card:hover {
            transform: translateY(-2px);
        }

        .card h3 {
            color: #333;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .card-icon {
            width: 30px;
            height: 30px;
            border-radius: 5px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
        }

        .card-icon.blue { background: #e3f2fd; color: #1976d2; }
        .card-icon.green { background: #e8f5e8; color: #388e3c; }
        .card-icon.orange { background: #fff3e0; color: #f57c00; }
        .card-icon.purple { background: #f3e5f5; color: #7b1fa2; }

        .stat-number {
            font-size: 36px;
            font-weight: bold;
            color: #667eea;
            margin: 10px 0;
        }

        .activity-list {
            list-style: none;
        }

        .activity-item {
            padding: 12px 0;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .activity-item:last-child {
            border-bottom: none;
        }

        .activity-action {
            font-weight: 500;
            color: #333;
        }

        .activity-time {
            color: #666;
            font-size: 12px;
        }

        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-top: 20px;
        }

        .action-btn {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            padding: 15px;
            border-radius: 5px;
            text-decoration: none;
            color: #495057;
            text-align: center;
            transition: all 0.2s ease;
        }

        .action-btn:hover {
            background: #e9ecef;
            border-color: #667eea;
            color: #667eea;
        }

        .security-info {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            padding: 15px;
            margin-top: 20px;
        }

        .security-info h4 {
            margin-bottom: 10px;
            color: #495057;
        }

        .security-item {
            display: flex;
            justify-content: space-between;
            padding: 5px 0;
            font-size: 14px;
        }

        .status-indicator {
            display: inline-block;
            width: 8px;
            height: 8px;
            border-radius: 50%;
            margin-right: 8px;
        }

        .status-indicator.online {
            background: #27ae60;
        }

        .status-indicator.warning {
            background: #f39c12;
        }

        .alert {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .alert-info {
            background-color: #e3f2fd;
            color: #1976d2;
            border: 1px solid #bbdefb;
        }

        .alert-success {
            background-color: #e8f5e8;
            color: #388e3c;
            border: 1px solid #c8e6c9;
        }

        @media (max-width: 768px) {
            .header-content {
                flex-direction: column;
                gap: 15px;
            }

            .dashboard-grid {
                grid-template-columns: 1fr;
            }

            .quick-actions {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="header-content">
            <div class="user-info">
                <div class="user-avatar">
                    <?php echo strtoupper(substr($current_user['username'], 0, 2)); ?>
                </div>
                <div>
                    <h2>Welcome, <?php echo htmlspecialchars($current_user['username'], ENT_QUOTES, 'UTF-8'); ?></h2>
                    <span class="role-badge <?php echo $current_user['role']; ?>">
                        <?php echo htmlspecialchars($current_user['role'], ENT_QUOTES, 'UTF-8'); ?>
                    </span>
                </div>
            </div>
            <div>
                <a href="logout.php" class="logout-btn" onclick="return confirm('Are you sure you want to logout?')">
                    🚪 Logout
                </a>
            </div>
        </div>
    </header>

    <div class="container">
        <div class="welcome-section">
            <h1>Dashboard Overview</h1>
            <p>Last login: <?php echo date('F j, Y \a\t g:i A'); ?></p>
        </div>

        <?php if (isset($_GET['login']) && $_GET['login'] === 'success'): ?>
            <div class="alert alert-success">
                ✅ Successfully logged in! Welcome back.
            </div>
        <?php endif; ?>

        <div class="dashboard-grid">
            <!-- User Statistics -->
            <div class="card">
                <h3>
                    <div class="card-icon blue">📊</div>
                    Your Activity
                </h3>
                <div class="stat-number"><?php echo count($user_activities); ?></div>
                <p>Recent activities recorded</p>
            </div>

            <?php if (has_role('admin')): ?>
                <!-- Admin Statistics -->
                <div class="card">
                    <h3>
                        <div class="card-icon purple">👥</div>
                        System Overview
                    </h3>
                    <div class="stat-number"><?php echo $system_stats['total_users'] ?? '0'; ?></div>
                    <p>Total registered users</p>
                </div>

                <div class="card">
                    <h3>
                        <div class="card-icon green">🟢</div>
                        Active Users
                    </h3>
                    <div class="stat-number"><?php echo $system_stats['active_users'] ?? '0'; ?></div>
                    <p>Currently active (last hour)</p>
                </div>

                <div class="card">
                    <h3>
                        <div class="card-icon orange">⚠️</div>
                        Security Status
                    </h3>
                    <div class="stat-number"><?php echo $system_stats['failed_attempts'] ?? '0'; ?></div>
                    <p>Failed login attempts today</p>
                </div>
            <?php endif; ?>
        </div>

        <div class="dashboard-grid">
            <!-- Recent Activity -->
            <div class="card">
                <h3>
                    <div class="card-icon blue">📋</div>
                    Recent Activity
                </h3>
                <?php if (!empty($user_activities)): ?>
                    <ul class="activity-list">
                        <?php foreach (array_slice($user_activities, 0, 5) as $activity): ?>
                            <li class="activity-item">
                                <div>
                                    <div class="activity-action"><?php echo htmlspecialchars($activity['action'], ENT_QUOTES, 'UTF-8'); ?></div>
                                    <div style="font-size: 12px; color: #666;">
                                        <?php echo htmlspecialchars($activity['description'], ENT_QUOTES, 'UTF-8'); ?>
                                    </div>
                                </div>
                                <div class="activity-time">
                                    <?php 
                                    $activity_time = strtotime($activity['created_at']);
                                    $time_diff = time() - $activity_time;
                                    
                                    if ($time_diff < 60) {
                                        echo 'Just now';
                                    } elseif ($time_diff < 3600) {
                                        echo floor($time_diff / 60) . 'm ago';
                                    } elseif ($time_diff < 86400) {
                                        echo floor($time_diff / 3600) . 'h ago';
                                    } else {
                                        echo date('M j', $activity_time);
                                    }
                                    ?>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p>No recent activity found.</p>
                <?php endif; ?>
            </div>

            <!-- Quick Actions -->
            <div class="card">
                <h3>
                    <div class="card-icon green">⚡</div>
                    Quick Actions
                </h3>
                <div class="quick-actions">
                    <a href="#" class="action-btn" onclick="refreshDashboard()">
                        🔄 Refresh Data
                    </a>
                    <?php if (has_role('admin')): ?>
                        <a href="#" class="action-btn">
                            👥 Manage Users
                        </a>
                        <a href="#" class="action-btn">
                            📊 System Reports
                        </a>
                        <a href="#" class="action-btn">
                            🔧 System Settings
                        </a>
                    <?php else: ?>
                        <a href="#" class="action-btn">
                            📝 My Profile
                        </a>
                        <a href="#" class="action-btn">
                            📁 My Files
                        </a>
                        <a href="#" class="action-btn">
                            💬 Support
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Security Information -->
        <div class="card">
            <h3>
                <div class="card-icon orange">🔒</div>
                Security Information
            </h3>
            <div class="security-info">
                <div class="security-item">
                    <span><span class="status-indicator online"></span>Session Status</span>
                    <span>Active & Secure</span>
                </div>
                <div class="security-item">
                    <span><span class="status-indicator online"></span>CSRF Protection</span>
                    <span>Enabled</span>
                </div>
                <div class="security-item">
                    <span><span class="status-indicator online"></span>Session Timeout</span>
                    <span>30 minutes</span>
                </div>
                <div class="security-item">
                    <span><span class="status-indicator warning"></span>Your IP</span>
                    <span><?php echo htmlspecialchars($_SERVER['REMOTE_ADDR'] ?? 'Unknown', ENT_QUOTES, 'UTF-8'); ?></span>
                </div>
            </div>
        </div>

        <?php if (has_role('admin')): ?>
            <div class="card">
                <h3>
                    <div class="card-icon purple">📈</div>
                    Today's Statistics
                </h3>
                <div class="dashboard-grid" style="grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); margin-top: 20px;">
                    <div style="text-align: center;">
                        <div style="font-size: 24px; font-weight: bold; color: #667eea;">
                            <?php echo $system_stats['today_logins'] ?? '0'; ?>
                        </div>
                        <div style="color: #666;">Successful Logins</div>
                    </div>
                    <div style="text-align: center;">
                        <div style="font-size: 24px; font-weight: bold; color: #e74c3c;">
                            <?php echo $system_stats['failed_attempts'] ?? '0'; ?>
                        </div>
                        <div style="color: #666;">Failed Attempts</div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script>
        // Auto-refresh dashboard data every 5 minutes
        setInterval(refreshDashboard, 300000);

        function refreshDashboard() {
            location.reload();
        }

        // Logout confirmation
        document.querySelector('.logout-btn').addEventListener('click', function(e) {
            if (!confirm('Are you sure you want to logout?')) {
                e.preventDefault();
            }
        });

        // Session timeout warning (show warning at 25 minutes)
        setTimeout(function() {
            if (confirm('Your session will expire in 5 minutes. Would you like to extend it?')) {
                // Make a request to extend session
                fetch('extend_session.php')
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert('Session extended successfully!');
                        }
                    })
                    .catch(error => console.log('Session extension failed:', error));
            }
        }, 25 * 60 * 1000); // 25 minutes

        // Update activity time every minute
        setInterval(function() {
            location.reload();
        }, 60000); // 1 minute
    </script>
</body>
</html>