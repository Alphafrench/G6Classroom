<?php
/**
 * Login Page
 * Secure login form with CSRF protection
 */

define('AUTH_SYSTEM', true);
require_once __DIR__ . '/../includes/auth.php';

// Initialize session
initialize_session();

// If user is already logged in, redirect to dashboard
if (is_logged_in()) {
    header('Location: dashboard.php');
    exit();
}

$error_message = '';
$success_message = '';

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
        $error_message = 'Invalid form submission. Please try again.';
    } else {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $remember_me = isset($_POST['remember_me']);
        
        // Validate input
        if (empty($username) || empty($password)) {
            $error_message = 'Please enter both username and password.';
        } else {
            // Attempt login
            $result = login($username, $password, $remember_me);
            
            if ($result['success']) {
                // Redirect based on user role
                $user = get_current_user();
                if ($user && $user['role'] === 'admin') {
                    header('Location: dashboard.php?role=admin');
                } else {
                    header('Location: dashboard.php?role=employee');
                }
                exit();
            } else {
                $error_message = $result['message'];
            }
        }
    }
}

// Get error message from session if redirected due to security
if (isset($_SESSION['error_message'])) {
    $error_message = $_SESSION['error_message'];
    unset($_SESSION['error_message']);
}

// Generate CSRF token
$csrf_token = generate_csrf_token();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Secure Authentication System</title>
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

        .login-container {
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
        }

        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .login-header h1 {
            color: #333;
            margin-bottom: 10px;
            font-size: 28px;
        }

        .login-header p {
            color: #666;
            font-size: 14px;
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

        .form-group input[type="text"],
        .form-group input[type="password"] {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
            transition: border-color 0.3s ease;
        }

        .form-group input[type="text"]:focus,
        .form-group input[type="password"]:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .form-group input.error {
            border-color: #e74c3c;
        }

        .form-group input.success {
            border-color: #27ae60;
        }

        .checkbox-group {
            display: flex;
            align-items: center;
            margin-bottom: 25px;
        }

        .checkbox-group input[type="checkbox"] {
            margin-right: 8px;
            transform: scale(1.2);
        }

        .checkbox-group label {
            margin-bottom: 0;
            cursor: pointer;
            color: #666;
        }

        .btn {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }

        .btn:active {
            transform: translateY(0);
        }

        .btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        .alert {
            padding: 12px 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .alert-error {
            background-color: #fee;
            color: #c33;
            border: 1px solid #fcc;
        }

        .alert-success {
            background-color: #efe;
            color: #363;
            border: 1px solid #cfc;
        }

        .alert-info {
            background-color: #eef;
            color: #336;
            border: 1px solid #ccf;
        }

        .security-notice {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            padding: 15px;
            margin-top: 20px;
            font-size: 12px;
            color: #6c757d;
        }

        .security-notice h4 {
            margin-bottom: 8px;
            color: #495057;
            font-size: 13px;
        }

        .loading {
            display: none;
            text-align: center;
            margin-top: 10px;
        }

        .loading::after {
            content: '';
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 2px solid #f3f3f3;
            border-top: 2px solid #667eea;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #eee;
            color: #999;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <h1>Secure Login</h1>
            <p>Authentication System</p>
        </div>

        <?php if ($error_message): ?>
            <div class="alert alert-error">
                <?php echo htmlspecialchars($error_message, ENT_QUOTES, 'UTF-8'); ?>
            </div>
        <?php endif; ?>

        <?php if ($success_message): ?>
            <div class="alert alert-success">
                <?php echo htmlspecialchars($success_message, ENT_QUOTES, 'UTF-8'); ?>
            </div>
        <?php endif; ?>

        <form method="POST" id="loginForm" autocomplete="on">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8'); ?>">
            
            <div class="form-group">
                <label for="username">Username or Email</label>
                <input 
                    type="text" 
                    id="username" 
                    name="username" 
                    required 
                    autocomplete="username"
                    value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username'], ENT_QUOTES, 'UTF-8') : ''; ?>"
                >
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input 
                    type="password" 
                    id="password" 
                    name="password" 
                    required 
                    autocomplete="current-password"
                >
            </div>

            <div class="checkbox-group">
                <input type="checkbox" id="remember_me" name="remember_me">
                <label for="remember_me">Remember me for 30 days</label>
            </div>

            <button type="submit" class="btn" id="loginBtn">
                <span id="loginText">Login</span>
            </button>

            <div class="loading" id="loading">
                Authenticating...
            </div>
        </form>

        <div class="security-notice">
            <h4>🔒 Security Features</h4>
            <ul>
                <li>CSRF protection enabled</li>
                <li>Session timeout: 30 minutes</li>
                <li>Account lockout after 5 failed attempts</li>
                <li>Secure session management</li>
            </ul>
        </div>

        <div class="footer">
            <p>&copy; 2025 Secure Authentication System. All rights reserved.</p>
        </div>
    </div>

    <script>
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            const btn = document.getElementById('loginBtn');
            const text = document.getElementById('loginText');
            const loading = document.getElementById('loading');
            
            // Show loading state
            btn.disabled = true;
            text.textContent = 'Logging in...';
            loading.style.display = 'block';
            
            // Basic validation
            const username = document.getElementById('username').value.trim();
            const password = document.getElementById('password').value;
            
            if (!username || !password) {
                e.preventDefault();
                btn.disabled = false;
                text.textContent = 'Login';
                loading.style.display = 'none';
                alert('Please fill in all fields.');
            }
        });

        // Real-time validation feedback
        document.getElementById('username').addEventListener('blur', function() {
            validateField(this);
        });

        document.getElementById('password').addEventListener('blur', function() {
            validateField(this);
        });

        function validateField(field) {
            if (field.value.trim()) {
                field.classList.remove('error');
                field.classList.add('success');
            } else {
                field.classList.remove('success');
                field.classList.add('error');
            }
        }

        // Clear validation classes on input
        document.getElementById('username').addEventListener('input', function() {
            this.classList.remove('error', 'success');
        });

        document.getElementById('password').addEventListener('input', function() {
            this.classList.remove('error', 'success');
        });

        // Auto-focus username field
        document.getElementById('username').focus();
    </script>
</body>
</html>