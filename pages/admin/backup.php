<?php
require_once '../../includes/config.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

// Check if user is logged in and has admin privileges
if (!isLoggedIn() || !isAdmin()) {
    header('Location: ../../login.php');
    exit();
}

$pageTitle = "Backup & Restore";
$pdo = getDBConnection();

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'create_backup':
            try {
                $backupDir = '../../backups/';
                if (!is_dir($backupDir)) {
                    mkdir($backupDir, 0755, true);
                }
                
                $timestamp = date('Y-m-d_H-i-s');
                $backupFile = $backupDir . "backup_{$timestamp}.sql";
                
                // Get database credentials from config
                $host = DB_HOST;
                $dbname = DB_NAME;
                $username = DB_USER;
                $password = DB_PASS;
                
                // Create mysqldump command
                $command = sprintf(
                    'mysqldump --host=%s --user=%s --password=%s --single-transaction --routines --triggers %s > %s',
                    escapeshellarg($host),
                    escapeshellarg($username),
                    escapeshellarg($password),
                    escapeshellarg($dbname),
                    escapeshellarg($backupFile)
                );
                
                exec($command, $output, $returnCode);
                
                if ($returnCode === 0 && file_exists($backupFile)) {
                    // Log backup creation
                    logActivity($_SESSION['user_id'], 'Backup Management', "Created database backup: backup_{$timestamp}.sql");
                    $success = "Backup created successfully: backup_{$timestamp}.sql";
                } else {
                    $error = "Failed to create backup.";
                }
            } catch (Exception $e) {
                $error = "Error creating backup: " . $e->getMessage();
            }
            break;
            
        case 'restore_backup':
            if (!empty($_POST['backup_file'])) {
                try {
                    $backupFile = '../../backups/' . basename($_POST['backup_file']);
                    
                    if (file_exists($backupFile)) {
                        // Get database credentials
                        $host = DB_HOST;
                        $dbname = DB_NAME;
                        $username = DB_USER;
                        $password = DB_PASS;
                        
                        // Create mysql command
                        $command = sprintf(
                            'mysql --host=%s --user=%s --password=%s %s < %s',
                            escapeshellarg($host),
                            escapeshellarg($username),
                            escapeshellarg($password),
                            escapeshellarg($dbname),
                            escapeshellarg($backupFile)
                        );
                        
                        exec($command, $output, $returnCode);
                        
                        if ($returnCode === 0) {
                            // Log restore operation
                            logActivity($_SESSION['user_id'], 'Backup Management', "Restored database from: {$_POST['backup_file']}");
                            $success = "Database restored successfully from: {$_POST['backup_file']}";
                        } else {
                            $error = "Failed to restore database.";
                        }
                    } else {
                        $error = "Backup file not found.";
                    }
                } catch (Exception $e) {
                    $error = "Error restoring backup: " . $e->getMessage();
                }
            } else {
                $error = "Please select a backup file.";
            }
            break;
            
        case 'download_backup':
            if (!empty($_POST['backup_file'])) {
                $backupFile = '../../backups/' . basename($_POST['backup_file']);
                
                if (file_exists($backupFile)) {
                    header('Content-Type: application/sql');
                    header('Content-Disposition: attachment; filename="' . basename($backupFile) . '"');
                    header('Content-Length: ' . filesize($backupFile));
                    header('Cache-Control: no-cache, no-store, must-revalidate');
                    header('Pragma: no-cache');
                    header('Expires: 0');
                    
                    readfile($backupFile);
                    exit;
                } else {
                    $error = "Backup file not found.";
                }
            } else {
                $error = "Please select a backup file.";
            }
            break;
            
        case 'delete_backup':
            if (!empty($_POST['backup_file'])) {
                $backupFile = '../../backups/' . basename($_POST['backup_file']);
                
                if (file_exists($backupFile)) {
                    if (unlink($backupFile)) {
                        // Log deletion
                        logActivity($_SESSION['user_id'], 'Backup Management', "Deleted backup file: {$_POST['backup_file']}");
                        $success = "Backup file deleted successfully.";
                    } else {
                        $error = "Failed to delete backup file.";
                    }
                } else {
                    $error = "Backup file not found.";
                }
            } else {
                $error = "Please select a backup file.";
            }
            break;
            
        case 'cleanup_old_backups':
            $days = (int)$_POST['cleanup_days'];
            $backupDir = '../../backups/';
            
            if (is_dir($backupDir)) {
                $deleted = 0;
                $cutoffTime = time() - ($days * 24 * 60 * 60);
                
                $files = glob($backupDir . '*.sql');
                foreach ($files as $file) {
                    if (is_file($file) && filemtime($file) < $cutoffTime) {
                        if (unlink($file)) {
                            $deleted++;
                        }
                    }
                }
                
                // Log cleanup
                logActivity($_SESSION['user_id'], 'Backup Management', "Cleaned up $deleted old backup files (older than $days days)");
                $success = "Cleaned up $deleted old backup files.";
            } else {
                $error = "Backup directory not found.";
            }
            break;
    }
}

// Get available backups
$backupDir = '../../backups/';
$backups = [];

if (is_dir($backupDir)) {
    $files = glob($backupDir . '*.sql');
    foreach ($files as $file) {
        $backups[] = [
            'filename' => basename($file),
            'size' => filesize($file),
            'created' => filemtime($file),
            'path' => $file
        ];
    }
    
    // Sort by creation date (newest first)
    usort($backups, function($a, $b) {
        return $b['created'] - $a['created'];
    });
}

// Get system storage information
$storageInfo = [
    'total_space' => disk_total_space('.'),
    'free_space' => disk_free_space('.'),
    'backup_dir_size' => 0
];

// Calculate backup directory size
if (is_dir($backupDir)) {
    $files = glob($backupDir . '*');
    foreach ($files as $file) {
        if (is_file($file)) {
            $storageInfo['backup_dir_size'] += filesize($file);
        }
    }
}

function formatBytes($bytes) {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= pow(1024, $pow);
    return round($bytes, 2) . ' ' . $units[$pow];
}

function formatDate($timestamp) {
    return date('Y-m-d H:i:s', $timestamp);
}
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
                    <button type="button" class="btn btn-primary" onclick="createBackup()">
                        <i class="fas fa-download"></i> Create Backup
                    </button>
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

                <!-- Storage Information -->
                <div class="row mb-4">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title">Storage Information</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="text-center">
                                            <h4><?php echo formatBytes($storageInfo['total_space']); ?></h4>
                                            <p class="text-muted">Total Space</p>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="text-center">
                                            <h4><?php echo formatBytes($storageInfo['free_space']); ?></h4>
                                            <p class="text-muted">Free Space</p>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="text-center">
                                            <h4><?php echo formatBytes($storageInfo['backup_dir_size']); ?></h4>
                                            <p class="text-muted">Backup Storage Used</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="progress mt-3">
                                    <div class="progress-bar" role="progressbar" 
                                         style="width: <?php echo (($storageInfo['total_space'] - $storageInfo['free_space']) / $storageInfo['total_space']) * 100; ?>%">
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
                                    <div class="col-md-4 mb-3">
                                        <button type="button" class="btn btn-outline-primary w-100" onclick="createBackup()">
                                            <i class="fas fa-plus"></i><br>
                                            Create New Backup
                                        </button>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <button type="button" class="btn btn-outline-info w-100" data-bs-toggle="modal" data-bs-target="#cleanupModal">
                                            <i class="fas fa-broom"></i><br>
                                            Clean Up Old Backups
                                        </button>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <button type="button" class="btn btn-outline-warning w-100" data-bs-toggle="modal" data-bs-target="#restoreModal">
                                            <i class="fas fa-upload"></i><br>
                                            Restore from Backup
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Backup Files -->
                <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title">Available Backups</h5>
                            </div>
                            <div class="card-body">
                                <?php if (empty($backups)): ?>
                                    <div class="text-center text-muted">
                                        <i class="fas fa-inbox fa-3x mb-3"></i>
                                        <p>No backups found. Create your first backup using the button above.</p>
                                    </div>
                                <?php else: ?>
                                    <div class="table-responsive">
                                        <table class="table table-striped">
                                            <thead>
                                                <tr>
                                                    <th>Filename</th>
                                                    <th>Size</th>
                                                    <th>Created</th>
                                                    <th>Age</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($backups as $backup): ?>
                                                    <tr>
                                                        <td>
                                                            <i class="fas fa-database text-primary"></i>
                                                            <?php echo htmlspecialchars($backup['filename']); ?>
                                                        </td>
                                                        <td><?php echo formatBytes($backup['size']); ?></td>
                                                        <td><?php echo formatDate($backup['created']); ?></td>
                                                        <td>
                                                            <?php
                                                            $age = time() - $backup['created'];
                                                            $days = floor($age / (24 * 60 * 60));
                                                            $hours = floor(($age % (24 * 60 * 60)) / (60 * 60));
                                                            
                                                            if ($days > 0) {
                                                                echo $days . ' day' . ($days > 1 ? 's' : '');
                                                            } elseif ($hours > 0) {
                                                                echo $hours . ' hour' . ($hours > 1 ? 's' : '');
                                                            } else {
                                                                echo 'Less than 1 hour';
                                                            }
                                                            ?>
                                                        </td>
                                                        <td>
                                                            <div class="btn-group" role="group">
                                                                <button type="button" class="btn btn-sm btn-outline-info" 
                                                                        onclick="downloadBackup('<?php echo htmlspecialchars($backup['filename']); ?>')">
                                                                    <i class="fas fa-download"></i>
                                                                </button>
                                                                <button type="button" class="btn btn-sm btn-outline-warning" 
                                                                        onclick="confirmRestore('<?php echo htmlspecialchars($backup['filename']); ?>')">
                                                                    <i class="fas fa-upload"></i>
                                                                </button>
                                                                <button type="button" class="btn btn-sm btn-outline-danger" 
                                                                        onclick="deleteBackup('<?php echo htmlspecialchars($backup['filename']); ?>')">
                                                                    <i class="fas fa-trash"></i>
                                                                </button>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Create Backup Modal -->
    <div class="modal fade" id="createBackupModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" id="createBackupForm">
                    <div class="modal-header">
                        <h5 class="modal-title">Create Backup</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="action" value="create_backup">
                        <p>Are you sure you want to create a new backup of the database?</p>
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i>
                            <strong>Warning:</strong> This may take a few moments and will create a new backup file.
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Create Backup</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Restore Modal -->
    <div class="modal fade" id="restoreModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" id="restoreForm">
                    <div class="modal-header">
                        <h5 class="modal-title">Restore Database</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="action" value="restore_backup">
                        <div class="mb-3">
                            <label class="form-label">Select Backup File</label>
                            <select class="form-select" name="backup_file" required>
                                <option value="">Choose a backup file...</option>
                                <?php foreach ($backups as $backup): ?>
                                    <option value="<?php echo htmlspecialchars($backup['filename']); ?>">
                                        <?php echo htmlspecialchars($backup['filename']); ?> (<?php echo formatBytes($backup['size']); ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle"></i>
                            <strong>Danger:</strong> Restoring a backup will replace ALL current data. This action cannot be undone!
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-warning">Restore Database</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Cleanup Modal -->
    <div class="modal fade" id="cleanupModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title">Clean Up Old Backups</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="action" value="cleanup_old_backups">
                        <div class="mb-3">
                            <label class="form-label">Delete backups older than (days)</label>
                            <input type="number" class="form-control" name="cleanup_days" value="30" min="1">
                        </div>
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i>
                            This will permanently delete all backup files older than the specified number of days.
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-warning">Clean Up</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Backup Modal -->
    <div class="modal fade" id="deleteBackupModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" id="deleteBackupForm">
                    <div class="modal-header">
                        <h5 class="modal-title">Delete Backup</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="action" value="delete_backup">
                        <input type="hidden" name="backup_file" id="delete_backup_file">
                        <p>Are you sure you want to delete backup file "<span id="delete_backup_name"></span>"? This action cannot be undone.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Delete Backup</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function createBackup() {
            new bootstrap.Modal(document.getElementById('createBackupModal')).show();
        }
        
        function confirmRestore(filename) {
            document.querySelector('#restoreModal select[name="backup_file"]').value = filename;
            new bootstrap.Modal(document.getElementById('restoreModal')).show();
        }
        
        function downloadBackup(filename) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.style.display = 'none';
            
            const actionInput = document.createElement('input');
            actionInput.name = 'action';
            actionInput.value = 'download_backup';
            form.appendChild(actionInput);
            
            const backupInput = document.createElement('input');
            backupInput.name = 'backup_file';
            backupInput.value = filename;
            form.appendChild(backupInput);
            
            document.body.appendChild(form);
            form.submit();
            document.body.removeChild(form);
        }
        
        function deleteBackup(filename) {
            document.getElementById('delete_backup_file').value = filename;
            document.getElementById('delete_backup_name').textContent = filename;
            new bootstrap.Modal(document.getElementById('deleteBackupModal')).show();
        }
    </script>
</body>
</html>