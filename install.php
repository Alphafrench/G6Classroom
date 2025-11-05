<?php
/**
 * Installation Script
 * Run this script once to set up the authentication system
 */

// Prevent direct access in production
if (basename($_SERVER['PHP_SELF']) !== 'install.php') {
    die('Installation script can only be run directly');
}

$install_complete = false;
$errors = [];
$success_messages = [];

// Handle installation form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate inputs
    $db_host = trim($_POST['db_host'] ?? '');
    $db_name = trim($_POST['db_name'] ?? '');
    $db_user = trim($_POST['db_user'] ?? '');
    $db_pass = trim($_POST['db_pass'] ?? '');
    $admin_username = trim($_POST['admin_username'] ?? '');
    $admin_email = trim($_POST['admin_email'] ?? '');
    $admin_password = trim($_POST['admin_password'] ?? '');
    
    if (empty($db_host) || empty($db_name) || empty($db_user) || 
        empty($admin_username) || empty($admin_email) || empty($admin_password)) {
        $errors[] = 'All fields are required.';
    } else {
        try {
            // Test database connection
            $dsn = "mysql:host=$db_host;dbname=$db_name;charset=utf8mb4";
            $pdo = new PDO($dsn, $db_user, $db_pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
            ]);
            
            // Create tables
            $sql = file_get_contents(__DIR__ . '/../database_schema.sql');
            $statements = explode(';', $sql);
            
            foreach ($statements as $statement) {
                $statement = trim($statement);
                if (!empty($statement) && !preg_match('/^(DELIMITER|--|\/\*)/', $statement)) {
                    $pdo->exec($statement);
                }
            }
            
            // Create admin user
            $hashed_password = password_hash($admin_password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, 'admin') 
                                 ON DUPLICATE KEY UPDATE username = VALUES(username), email = VALUES(email), password = VALUES(password)");
            $stmt->execute([$admin_username, $admin_email, $hashed_password]);
            
            // Create config file
            $config_content = "<?php
// Auto-generated configuration file
define('DB_HOST', '$db_host');
define('DB_NAME', '$db_name');
define('DB_USER', '$db_user');
define('DB_PASS', '$db_pass');
define('DB_CHARSET', 'utf8mb4');

// Copy the rest of the config from includes/config.php
require_once __DIR__ . '/config_base.php';
?>";
            
            file_put_contents(__DIR__ . '/config.php', $config_content);
            
            // Copy base config
            $base_config = file_get_contents(__DIR__ . '/config.php');
            file_put_contents(__DIR__ . '/config_base.php', $base_config);
            
            // Remove admin password from POST for security
            unset($_POST['admin_password']);
            
            $install_complete = true;
            $success_messages[] = 'Installation completed successfully!';
            $success_messages[] = "Admin user created: $admin_username";
            $success_messages[] = "You can now delete this install.php file for security.";
            
        } catch (PDOException $e) {
            $errors[] = 'Database error: ' . $e->getMessage();
        } catch (Exception $e) {
            $errors[] = 'Installation error: ' . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Install Authentication System</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .install-container {
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 600px;
        }

        .install-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .install-header h1 {
            color: #333;
            margin-bottom: 10px;
        }

        .install-header p {
            color: #666;
        }

        .alert {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 500;
        }

        .form-group input {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
        }

        .form-group input:focus {
            outline: none;
            border-color: #667eea;
        }

        .btn {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
        }

        .btn:hover {
            opacity: 0.9;
        }

        .section {
            margin-bottom: 30px;
            padding: 20px;
            border: 1px solid #eee;
            border-radius: 5px;
        }

        .section h3 {
            margin-bottom: 15px;
            color: #333;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        .security-notice {
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            color: #856404;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .success-actions {
            background-color: #d1ecf1;
            border: 1px solid #bee5eb;
            color: #0c5460;
            padding: 20px;
            border-radius: 5px;
            margin-top: 20px;
        }

        .success-actions h3 {
            margin-bottom: 15px;
        }

        .success-actions ul {
            margin-left: 20px;
        }

        .success-actions li {
            margin-bottom: 8px;
        }
    </style>
</head>
<body>
    <div class="install-container">
        <div class="install-header">
            <h1>🔧 Installation</h1>
            <p>Secure Authentication System Setup</p>
        </div>

        <?php if (!empty($errors)): ?>
            <?php foreach ($errors as $error): ?>
                <div class="alert alert-error">
                    ❌ <?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

        <?php if (!empty($success_messages)): ?>
            <?php foreach ($success_messages as $message): ?>
                <div class="alert alert-success">
                    ✅ <?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

        <?php if ($install_complete): ?>
            <div class="success-actions">
                <h3>🎉 Installation Complete!</h3>
                <p>Your authentication system is now ready to use.</p>
                <ul>
                    <li>Delete the <code>install.php</code> file for security</li>
                    <li>Update the admin password if needed</li>
                    <li>Configure additional users</li>
                    <li>Test the login system</li>
                </ul>
                <p style="margin-top: 15px;">
                    <a href="login.php" style="background: #667eea; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">
                        Go to Login Page →
                    </a>
                </p>
            </div>
        <?php else: ?>
            <div class="security-notice">
                <strong>⚠️ Security Notice:</strong> Delete this installation file after setup to prevent unauthorized access.
            </div>

            <form method="POST" id="installForm">
                <div class="section">
                    <h3>📊 Database Configuration</h3>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="db_host">Database Host</label>
                            <input type="text" id="db_host" name="db_host" value="localhost" required>
                        </div>
                        <div class="form-group">
                            <label for="db_name">Database Name</label>
                            <input type="text" id="db_name" name="db_name" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="db_user">Database Username</label>
                            <input type="text" id="db_user" name="db_user" required>
                        </div>
                        <div class="form-group">
                            <label for="db_pass">Database Password</label>
                            <input type="password" id="db_pass" name="db_pass">
                        </div>
                    </div>
                </div>

                <div class="section">
                    <h3>👤 Admin Account</h3>
                    <div class="form-group">
                        <label for="admin_username">Admin Username</label>
                        <input type="text" id="admin_username" name="admin_username" required>
                    </div>
                    <div class="form-group">
                        <label for="admin_email">Admin Email</label>
                        <input type="email" id="admin_email" name="admin_email" required>
                    </div>
                    <div class="form-group">
                        <label for="admin_password">Admin Password</label>
                        <input type="password" id="admin_password" name="admin_password" required>
                        <small style="color: #666; font-size: 12px;">Use a strong password with at least 8 characters</small>
                    </div>
                </div>

                <button type="submit" class="btn">🚀 Install System</button>
            </form>
        <?php endif; ?>
    </div>

    <script>
        document.getElementById('installForm')?.addEventListener('submit', function(e) {
            const password = document.getElementById('admin_password').value;
            
            if (password.length < 8) {
                e.preventDefault();
                alert('Password must be at least 8 characters long.');
                return false;
            }
        });
    </script>
</body>
</html>