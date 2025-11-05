<?php
require_once '../../includes/config.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

// Check if user is logged in and has admin privileges
if (!isLoggedIn() || !isAdmin()) {
    header('Location: ../../login.php');
    exit();
}

$pageTitle = "System Settings";
$pdo = getDBConnection();

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'update_settings':
            $settings = [
                'company_name' => trim($_POST['company_name']),
                'company_address' => trim($_POST['company_address']),
                'company_phone' => trim($_POST['company_phone']),
                'company_email' => trim($_POST['company_email']),
                'working_hours_start' => $_POST['working_hours_start'],
                'working_hours_end' => $_POST['working_hours_end'],
                'break_duration' => (int)$_POST['break_duration'],
                'max_overtime_hours' => (float)$_POST['max_overtime_hours'],
                'timezone' => $_POST['timezone'],
                'date_format' => $_POST['date_format'],
                'time_format' => $_POST['time_format'],
                'enable_email_notifications' => isset($_POST['enable_email_notifications']) ? 1 : 0,
                'enable_sms_notifications' => isset($_POST['enable_sms_notifications']) ? 1 : 0,
                'enable_geofencing' => isset($_POST['enable_geofencing']) ? 1 : 0,
                'require_photo' => isset($_POST['require_photo']) ? 1 : 0,
                'backup_frequency' => $_POST['backup_frequency'],
                'session_timeout' => (int)$_POST['session_timeout']
            ];
            
            $successCount = 0;
            $totalSettings = count($settings);
            
            foreach ($settings as $key => $value) {
                $stmt = $pdo->prepare("INSERT INTO system_settings (setting_key, setting_value, updated_at) 
                                     VALUES (?, ?, NOW()) 
                                     ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), updated_at = NOW()");
                if ($stmt->execute([$key, $value])) {
                    $successCount++;
                }
            }
            
            if ($successCount === $totalSettings) {
                $success = "Settings updated successfully.";
                
                // Log activity
                logActivity($_SESSION['user_id'], "System Settings", "Updated system settings");
            } else {
                $error = "Some settings could not be updated.";
            }
            break;
            
        case 'clear_cache':
            // Clear system cache
            if (function_exists('opcache_reset')) {
                opcache_reset();
            }
            
            // Clear any temporary files
            $tempFiles = glob('../../temp/*');
            foreach ($tempFiles as $file) {
                if (is_file($file)) {
                    unlink($file);
                }
            }
            
            $success = "System cache cleared successfully.";
            
            // Log activity
            logActivity($_SESSION['user_id'], "System Settings", "Cleared system cache");
            break;
            
        case 'test_email':
            $testEmail = trim($_POST['test_email']);
            
            if (!empty($testEmail) && filter_var($testEmail, FILTER_VALIDATE_EMAIL)) {
                // Send test email
                $subject = "Test Email - Attendance System";
                $message = "This is a test email from the attendance system. If you receive this, email functionality is working correctly.";
                
                if (mail($testEmail, $subject, $message)) {
                    $success = "Test email sent successfully to $testEmail.";
                } else {
                    $error = "Failed to send test email.";
                }
            } else {
                $error = "Please provide a valid email address.";
            }
            break;
    }
}

// Get current settings
$settings = [];
$stmt = $pdo->query("SELECT setting_key, setting_value FROM system_settings");
while ($row = $stmt->fetch()) {
    $settings[$row['setting_key']] = $row['setting_value'];
}

// Default values for new installations
$defaultSettings = [
    'company_name' => 'Your Company',
    'company_address' => '',
    'company_phone' => '',
    'company_email' => '',
    'working_hours_start' => '09:00',
    'working_hours_end' => '17:00',
    'break_duration' => 60,
    'max_overtime_hours' => 4,
    'timezone' => 'UTC',
    'date_format' => 'Y-m-d',
    'time_format' => 'H:i',
    'enable_email_notifications' => 1,
    'enable_sms_notifications' => 0,
    'enable_geofencing' => 0,
    'require_photo' => 0,
    'backup_frequency' => 'daily',
    'session_timeout' => 120
];

// Merge with defaults for display
foreach ($defaultSettings as $key => $defaultValue) {
    if (!isset($settings[$key])) {
        $settings[$key] = $defaultValue;
    }
}

// Get system information
$systemInfo = [
    'php_version' => PHP_VERSION,
    'mysql_version' => $pdo->query("SELECT VERSION()")->fetchColumn(),
    'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
    'disk_space' => disk_free_space('.') ? round(disk_free_space('.') / (1024*1024*1024), 2) . ' GB' : 'Unknown',
    'max_execution_time' => ini_get('max_execution_time'),
    'memory_limit' => ini_get('memory_limit'),
    'upload_max_filesize' => ini_get('upload_max_filesize')
];
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

                <?php if (isset($success)): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?php echo $success; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if (isset($error)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?php echo $error; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- System Information -->
                <div class="row mb-4">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title">System Information</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <table class="table table-sm">
                                            <tr>
                                                <td><strong>PHP Version:</strong></td>
                                                <td><?php echo $systemInfo['php_version']; ?></td>
                                            </tr>
                                            <tr>
                                                <td><strong>MySQL Version:</strong></td>
                                                <td><?php echo $systemInfo['mysql_version']; ?></td>
                                            </tr>
                                            <tr>
                                                <td><strong>Server Software:</strong></td>
                                                <td><?php echo $systemInfo['server_software']; ?></td>
                                            </tr>
                                        </table>
                                    </div>
                                    <div class="col-md-6">
                                        <table class="table table-sm">
                                            <tr>
                                                <td><strong>Disk Space:</strong></td>
                                                <td><?php echo $systemInfo['disk_space']; ?> free</td>
                                            </tr>
                                            <tr>
                                                <td><strong>Memory Limit:</strong></td>
                                                <td><?php echo $systemInfo['memory_limit']; ?></td>
                                            </tr>
                                            <tr>
                                                <td><strong>Upload Max Size:</strong></td>
                                                <td><?php echo $systemInfo['upload_max_filesize']; ?></td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Company Settings -->
                <div class="row mb-4">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title">Company Information</h5>
                            </div>
                            <div class="card-body">
                                <form method="POST">
                                    <input type="hidden" name="action" value="update_settings">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Company Name</label>
                                                <input type="text" class="form-control" name="company_name" 
                                                       value="<?php echo htmlspecialchars($settings['company_name']); ?>">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Company Email</label>
                                                <input type="email" class="form-control" name="company_email" 
                                                       value="<?php echo htmlspecialchars($settings['company_email']); ?>">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Company Address</label>
                                        <textarea class="form-control" name="company_address" rows="2"><?php echo htmlspecialchars($settings['company_address']); ?></textarea>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Company Phone</label>
                                                <input type="text" class="form-control" name="company_phone" 
                                                       value="<?php echo htmlspecialchars($settings['company_phone']); ?>">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Timezone</label>
                                                <select class="form-select" name="timezone">
                                                    <option value="UTC" <?php echo $settings['timezone'] === 'UTC' ? 'selected' : ''; ?>>UTC</option>
                                                    <option value="America/New_York" <?php echo $settings['timezone'] === 'America/New_York' ? 'selected' : ''; ?>>Eastern Time</option>
                                                    <option value="America/Chicago" <?php echo $settings['timezone'] === 'America/Chicago' ? 'selected' : ''; ?>>Central Time</option>
                                                    <option value="America/Denver" <?php echo $settings['timezone'] === 'America/Denver' ? 'selected' : ''; ?>>Mountain Time</option>
                                                    <option value="America/Los_Angeles" <?php echo $settings['timezone'] === 'America/Los_Angeles' ? 'selected' : ''; ?>>Pacific Time</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Working Hours Settings -->
                <div class="row mb-4">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title">Working Hours & Attendance Settings</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="mb-3">
                                            <label class="form-label">Work Start Time</label>
                                            <input type="time" class="form-control" name="working_hours_start" 
                                                   value="<?php echo htmlspecialchars($settings['working_hours_start']); ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="mb-3">
                                            <label class="form-label">Work End Time</label>
                                            <input type="time" class="form-control" name="working_hours_end" 
                                                   value="<?php echo htmlspecialchars($settings['working_hours_end']); ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="mb-3">
                                            <label class="form-label">Break Duration (minutes)</label>
                                            <input type="number" class="form-control" name="break_duration" 
                                                   value="<?php echo htmlspecialchars($settings['break_duration']); ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="mb-3">
                                            <label class="form-label">Max Overtime Hours</label>
                                            <input type="number" step="0.5" class="form-control" name="max_overtime_hours" 
                                                   value="<?php echo htmlspecialchars($settings['max_overtime_hours']); ?>">
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="enable_email_notifications" 
                                                   <?php echo $settings['enable_email_notifications'] ? 'checked' : ''; ?>>
                                            <label class="form-check-label">Enable Email Notifications</label>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="enable_sms_notifications" 
                                                   <?php echo $settings['enable_sms_notifications'] ? 'checked' : ''; ?>>
                                            <label class="form-check-label">Enable SMS Notifications</label>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="require_photo" 
                                                   <?php echo $settings['require_photo'] ? 'checked' : ''; ?>>
                                            <label class="form-check-label">Require Photo for Check-in</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Display Settings -->
                <div class="row mb-4">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title">Display & Localization Settings</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label class="form-label">Date Format</label>
                                            <select class="form-select" name="date_format">
                                                <option value="Y-m-d" <?php echo $settings['date_format'] === 'Y-m-d' ? 'selected' : ''; ?>>YYYY-MM-DD</option>
                                                <option value="m/d/Y" <?php echo $settings['date_format'] === 'm/d/Y' ? 'selected' : ''; ?>>MM/DD/YYYY</option>
                                                <option value="d/m/Y" <?php echo $settings['date_format'] === 'd/m/Y' ? 'selected' : ''; ?>>DD/MM/YYYY</option>
                                                <option value="F j, Y" <?php echo $settings['date_format'] === 'F j, Y' ? 'selected' : ''; ?>>Month DD, YYYY</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label class="form-label">Time Format</label>
                                            <select class="form-select" name="time_format">
                                                <option value="H:i" <?php echo $settings['time_format'] === 'H:i' ? 'selected' : ''; ?>>24 Hour (HH:MM)</option>
                                                <option value="h:i A" <?php echo $settings['time_format'] === 'h:i A' ? 'selected' : ''; ?>>12 Hour (HH:MM AM/PM)</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label class="form-label">Session Timeout (minutes)</label>
                                            <input type="number" class="form-control" name="session_timeout" 
                                                   value="<?php echo htmlspecialchars($settings['session_timeout']); ?>">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Security Settings -->
                <div class="row mb-4">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title">Security & Backup Settings</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Backup Frequency</label>
                                            <select class="form-select" name="backup_frequency">
                                                <option value="hourly" <?php echo $settings['backup_frequency'] === 'hourly' ? 'selected' : ''; ?>>Hourly</option>
                                                <option value="daily" <?php echo $settings['backup_frequency'] === 'daily' ? 'selected' : ''; ?>>Daily</option>
                                                <option value="weekly" <?php echo $settings['backup_frequency'] === 'weekly' ? 'selected' : ''; ?>>Weekly</option>
                                                <option value="monthly" <?php echo $settings['backup_frequency'] === 'monthly' ? 'selected' : ''; ?>>Monthly</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="row mb-4">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-body">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Save Settings
                                </button>
                                <button type="button" class="btn btn-warning ms-2" onclick="clearCache()">
                                    <i class="fas fa-trash"></i> Clear Cache
                                </button>
                                <button type="button" class="btn btn-info ms-2" data-bs-toggle="modal" data-bs-target="#testEmailModal">
                                    <i class="fas fa-envelope"></i> Test Email
                                </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Test Email Modal -->
    <div class="modal fade" id="testEmailModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title">Send Test Email</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="action" value="test_email">
                        <div class="mb-3">
                            <label class="form-label">Email Address</label>
                            <input type="email" class="form-control" name="test_email" required 
                                   value="<?php echo htmlspecialchars($settings['company_email']); ?>">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-info">Send Test Email</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Clear Cache Modal -->
    <div class="modal fade" id="clearCacheModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title">Clear System Cache</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="action" value="clear_cache">
                        <p>Are you sure you want to clear the system cache? This will remove all temporary files and cached data.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-warning">Clear Cache</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function clearCache() {
            new bootstrap.Modal(document.getElementById('clearCacheModal')).show();
        }
    </script>
</body>
</html>