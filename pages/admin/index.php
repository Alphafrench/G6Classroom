<?php
require_once '../../includes/config.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

// Check if user is logged in and has admin privileges
if (!isLoggedIn() || !isAdmin()) {
    header('Location: ../../login.php');
    exit();
}

$pageTitle = "Administration Dashboard";

// Database connection
$pdo = getDBConnection();

// Get system statistics
$stats = [];

// Total users
$stmt = $pdo->query("SELECT COUNT(*) as count FROM users WHERE role != 'admin'");
$stats['total_users'] = $stmt->fetch()['count'];

// Total employees
$stmt = $pdo->query("SELECT COUNT(*) as count FROM employees");
$stats['total_employees'] = $stmt->fetch()['count'];

// Today's attendance
$stmt = $pdo->prepare("SELECT COUNT(*) as count FROM attendance WHERE DATE(timestamp) = CURDATE()");
$stmt->execute();
$stats['today_attendance'] = $stmt->fetch()['count'];

// Recent activities (system logs)
$stmt = $pdo->query("SELECT * FROM activity_logs ORDER BY created_at DESC LIMIT 10");
$recentActivities = $stmt->fetchAll();

// Active users today
$stmt = $pdo->query("SELECT COUNT(DISTINCT employee_id) as count FROM attendance WHERE DATE(timestamp) = CURDATE()");
$stats['active_today'] = $stmt->fetch()['count'];

// Departments count
$stmt = $pdo->query("SELECT COUNT(DISTINCT department) as count FROM employees WHERE department IS NOT NULL AND department != ''");
$stats['departments'] = $stmt->fetch()['count'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - Attendance System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../../assets/css/dashboard.css" rel="stylesheet">
</head>
<body>
    <?php include '../../includes/header.php'; ?>
    
    <div class="container-fluid">
        <div class="row">
            <?php include '../../includes/sidebar.php'; ?>
            
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2"><?php echo $pageTitle; ?></h1>
                </div>

                <!-- Quick Stats -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card bg-primary text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h4><?php echo number_format($stats['total_users']); ?></h4>
                                        <p class="mb-0">Total Users</p>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="fas fa-users fa-2x"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-success text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h4><?php echo number_format($stats['total_employees']); ?></h4>
                                        <p class="mb-0">Employees</p>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="fas fa-user-tie fa-2x"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-info text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h4><?php echo number_format($stats['today_attendance']); ?></h4>
                                        <p class="mb-0">Today's Attendance</p>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="fas fa-clock fa-2x"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-warning text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h4><?php echo number_format($stats['departments']); ?></h4>
                                        <p class="mb-0">Departments</p>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="fas fa-building fa-2x"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="row mb-4">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title">Quick Actions</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-3 mb-3">
                                        <a href="users.php" class="btn btn-outline-primary w-100">
                                            <i class="fas fa-users"></i><br>
                                            Manage Users
                                        </a>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <a href="employees.php" class="btn btn-outline-success w-100">
                                            <i class="fas fa-user-tie"></i><br>
                                            Manage Employees
                                        </a>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <a href="settings.php" class="btn btn-outline-warning w-100">
                                            <i class="fas fa-cog"></i><br>
                                            System Settings
                                        </a>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <a href="backup.php" class="btn btn-outline-info w-100">
                                            <i class="fas fa-database"></i><br>
                                            Backup & Restore
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <!-- Recent Activities -->
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title">Recent Activities</h5>
                            </div>
                            <div class="card-body">
                                <div class="list-group list-group-flush">
                                    <?php if (empty($recentActivities)): ?>
                                        <div class="list-group-item text-muted">
                                            No recent activities found.
                                        </div>
                                    <?php else: ?>
                                        <?php foreach ($recentActivities as $activity): ?>
                                            <div class="list-group-item">
                                                <div class="d-flex w-100 justify-content-between">
                                                    <h6 class="mb-1"><?php echo htmlspecialchars($activity['action']); ?></h6>
                                                    <small><?php echo timeAgo($activity['created_at']); ?></small>
                                                </div>
                                                <p class="mb-1"><?php echo htmlspecialchars($activity['description'] ?? ''); ?></p>
                                                <small class="text-muted">by <?php echo htmlspecialchars($activity['user_id'] ?? 'System'); ?></small>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- System Information -->
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title">System Information</h5>
                            </div>
                            <div class="card-body">
                                <table class="table table-sm">
                                    <tr>
                                        <td><strong>PHP Version:</strong></td>
                                        <td><?php echo PHP_VERSION; ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>MySQL Version:</strong></td>
                                        <td><?php echo $pdo->query("SELECT VERSION()")->fetchColumn(); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Server Time:</strong></td>
                                        <td><?php echo date('Y-m-d H:i:s'); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Active Users Today:</strong></td>
                                        <td><?php echo number_format($stats['active_today']); ?></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>