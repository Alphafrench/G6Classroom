<?php
/**
 * Registration Page
 * Secure user registration with role selection (teacher/student)
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
$form_data = [
    'username' => '',
    'email' => '',
    'full_name' => '',
    'role' => ''
];

// Handle registration form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
        $error_message = 'Invalid form submission. Please try again.';
    } else {
        // Sanitize and validate input
        $form_data['username'] = trim($_POST['username'] ?? '');
        $form_data['email'] = trim($_POST['email'] ?? '');
        $form_data['password'] = $_POST['password'] ?? '';
        $form_data['confirm_password'] = $_POST['confirm_password'] ?? '';
        $form_data['full_name'] = trim($_POST['full_name'] ?? '');
        $form_data['role'] = $_POST['role'] ?? '';
        
        // Validation
        if (empty($form_data['username']) || empty($form_data['email']) || 
            empty($form_data['password']) || empty($form_data['confirm_password']) || 
            empty($form_data['full_name']) || empty($form_data['role'])) {
            $error_message = 'Please fill in all required fields.';
        } elseif (!filter_var($form_data['email'], FILTER_VALIDATE_EMAIL)) {
            $error_message = 'Please enter a valid email address.';
        } elseif (strlen($form_data['password']) < 8) {
            $error_message = 'Password must be at least 8 characters long.';
        } elseif ($form_data['password'] !== $form_data['confirm_password']) {
            $error_message = 'Passwords do not match.';
        } elseif (!in_array($form_data['role'], ['teacher', 'student'])) {
            $error_message = 'Please select a valid role.';
        } elseif (!preg_match('/^[a-zA-Z0-9_]{3,20}$/', $form_data['username'])) {
            $error_message = 'Username must be 3-20 characters and contain only letters, numbers, and underscores.';
        } elseif (!preg_match('/^[a-zA-Z\s]{2,50}$/', $form_data['full_name'])) {
            $error_message = 'Full name must be 2-50 characters and contain only letters and spaces.';
        } else {
            // Attempt registration
            $result = register_user($form_data);
            
            if ($result['success']) {
                $success_message = 'Registration successful! You can now login with your credentials.';
                // Clear form data on success
                $form_data = ['username' => '', 'email' => '', 'full_name' => '', 'role' => ''];
            } else {
                $error_message = $result['message'];
            }
        }
    }
}

// User registration function
function register_user($data) {
    $pdo = get_db_connection();
    if (!$pdo) {
        return ['success' => false, 'message' => 'Database connection failed'];
    }
    
    try {
        // Check if username already exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$data['username']]);
        if ($stmt->fetch()) {
            return ['success' => false, 'message' => 'Username already exists'];
        }
        
        // Check if email already exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$data['email']]);
        if ($stmt->fetch()) {
            return ['success' => false, 'message' => 'Email address already registered'];
        }
        
        // Create new user
        $hashed_password = hash_password($data['password']);
        
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password, full_name, role, is_active, created_at) VALUES (?, ?, ?, ?, ?, 1, NOW())");
        $result = $stmt->execute([
            $data['username'],
            $data['email'],
            $hashed_password,
            $data['full_name'],
            $data['role']
        ]);
        
        if ($result) {
            $user_id = $pdo->lastInsertId();
            
            // Log registration activity
            log_activity($user_id, 'registration', "New {$data['role']} user registered");
            
            return ['success' => true, 'message' => 'User registered successfully'];
        } else {
            return ['success' => false, 'message' => 'Registration failed. Please try again.'];
        }
        
    } catch (PDOException $e) {
        error_log("Registration error: " . $e->getMessage());
        return ['success' => false, 'message' => 'An error occurred during registration'];
    }
}

// Generate CSRF token
$csrf_token = generate_csrf_token();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Secure Authentication System</title>
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

        .register-container {
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 450px;
        }

        .register-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .register-header h1 {
            color: #333;
            margin-bottom: 10px;
            font-size: 28px;
        }

        .register-header p {
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
        .form-group input[type="email"],
        .form-group input[type="password"],
        .form-group select {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
            transition: border-color 0.3s ease;
        }

        .form-group input:focus,
        .form-group select:focus {
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

        .form-group select {
            cursor: pointer;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        .role-selection {
            display: flex;
            gap: 15px;
            margin-top: 8px;
        }

        .role-option {
            flex: 1;
            padding: 15px;
            border: 2px solid #ddd;
            border-radius: 5px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .role-option:hover {
            border-color: #667eea;
            background-color: #f8f9ff;
        }

        .role-option.selected {
            border-color: #667eea;
            background-color: #667eea;
            color: white;
        }

        .role-option input[type="radio"] {
            display: none;
        }

        .role-icon {
            font-size: 24px;
            margin-bottom: 8px;
        }

        .role-title {
            font-weight: 500;
            margin-bottom: 4px;
        }

        .role-desc {
            font-size: 12px;
            opacity: 0.8;
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

        .password-strength {
            margin-top: 8px;
        }

        .strength-bar {
            width: 100%;
            height: 4px;
            background-color: #ddd;
            border-radius: 2px;
            overflow: hidden;
            margin-bottom: 8px;
        }

        .strength-fill {
            height: 100%;
            width: 0%;
            transition: all 0.3s ease;
            border-radius: 2px;
        }

        .strength-weak { background-color: #e74c3c; width: 25%; }
        .strength-fair { background-color: #f39c12; width: 50%; }
        .strength-good { background-color: #f1c40f; width: 75%; }
        .strength-strong { background-color: #27ae60; width: 100%; }

        .strength-text {
            font-size: 12px;
            color: #666;
        }

        .login-link {
            text-align: center;
            margin-top: 25px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }

        .login-link a {
            color: #667eea;
            text-decoration: none;
            font-weight: 500;
        }

        .login-link a:hover {
            text-decoration: underline;
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

        .form-hint {
            font-size: 12px;
            color: #666;
            margin-top: 4px;
        }

        @media (max-width: 480px) {
            .form-row {
                grid-template-columns: 1fr;
            }
            
            .role-selection {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="register-container">
        <div class="register-header">
            <h1>Create Account</h1>
            <p>Join our secure authentication system</p>
        </div>

        <?php if ($error_message): ?>
            <div class="alert alert-error">
                <?php echo htmlspecialchars($error_message, ENT_QUOTES, 'UTF-8'); ?>
            </div>
        <?php endif; ?>

        <?php if ($success_message): ?>
            <div class="alert alert-success">
                <?php echo htmlspecialchars($success_message, ENT_QUOTES, 'UTF-8'); ?>
                <br>
                <a href="login.php" style="color: #363; font-weight: 500;">Click here to login</a>
            </div>
        <?php endif; ?>

        <form method="POST" id="registerForm">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8'); ?>">
            
            <div class="form-row">
                <div class="form-group">
                    <label for="username">Username *</label>
                    <input 
                        type="text" 
                        id="username" 
                        name="username" 
                        required 
                        autocomplete="username"
                        value="<?php echo htmlspecialchars($form_data['username'], ENT_QUOTES, 'UTF-8'); ?>"
                        pattern="[a-zA-Z0-9_]{3,20}"
                        title="3-20 characters, letters, numbers, and underscores only"
                    >
                    <div class="form-hint">3-20 characters, letters, numbers, underscores</div>
                </div>

                <div class="form-group">
                    <label for="full_name">Full Name *</label>
                    <input 
                        type="text" 
                        id="full_name" 
                        name="full_name" 
                        required 
                        autocomplete="name"
                        value="<?php echo htmlspecialchars($form_data['full_name'], ENT_QUOTES, 'UTF-8'); ?>"
                    >
                </div>
            </div>

            <div class="form-group">
                <label for="email">Email Address *</label>
                <input 
                    type="email" 
                    id="email" 
                    name="email" 
                    required 
                    autocomplete="email"
                    value="<?php echo htmlspecialchars($form_data['email'], ENT_QUOTES, 'UTF-8'); ?>"
                >
            </div>

            <div class="form-group">
                <label for="role">Account Type *</label>
                <div class="role-selection">
                    <label class="role-option <?php echo $form_data['role'] === 'student' ? 'selected' : ''; ?>" for="role_student">
                        <input type="radio" id="role_student" name="role" value="student" <?php echo $form_data['role'] === 'student' ? 'checked' : ''; ?>>
                        <div class="role-icon">🎓</div>
                        <div class="role-title">Student</div>
                        <div class="role-desc">Access learning materials and assignments</div>
                    </label>

                    <label class="role-option <?php echo $form_data['role'] === 'teacher' ? 'selected' : ''; ?>" for="role_teacher">
                        <input type="radio" id="role_teacher" name="role" value="teacher" <?php echo $form_data['role'] === 'teacher' ? 'checked' : ''; ?>>
                        <div class="role-icon">👨‍🏫</div>
                        <div class="role-title">Teacher</div>
                        <div class="role-desc">Create content and manage students</div>
                    </label>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="password">Password *</label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        required 
                        autocomplete="new-password"
                        minlength="8"
                    >
                    <div class="password-strength">
                        <div class="strength-bar">
                            <div class="strength-fill" id="strengthFill"></div>
                        </div>
                        <div class="strength-text" id="strengthText">Password strength</div>
                    </div>
                    <div class="form-hint">Minimum 8 characters</div>
                </div>

                <div class="form-group">
                    <label for="confirm_password">Confirm Password *</label>
                    <input 
                        type="password" 
                        id="confirm_password" 
                        name="confirm_password" 
                        required 
                        autocomplete="new-password"
                    >
                </div>
            </div>

            <button type="submit" class="btn" id="registerBtn">
                <span id="registerText">Create Account</span>
            </button>

            <div class="loading" id="loading">
                Creating your account...
            </div>
        </form>

        <div class="login-link">
            Already have an account? <a href="login.php">Login here</a>
        </div>
    </div>

    <script>
        document.getElementById('registerForm').addEventListener('submit', function(e) {
            const btn = document.getElementById('registerBtn');
            const text = document.getElementById('registerText');
            const loading = document.getElementById('loading');
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            // Check if passwords match
            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Passwords do not match!');
                return;
            }
            
            // Check if role is selected
            const roleSelected = document.querySelector('input[name="role"]:checked');
            if (!roleSelected) {
                e.preventDefault();
                alert('Please select an account type!');
                return;
            }
            
            // Show loading state
            btn.disabled = true;
            text.textContent = 'Creating Account...';
            loading.style.display = 'block';
        });

        // Password strength checker
        document.getElementById('password').addEventListener('input', function() {
            const password = this.value;
            const strengthFill = document.getElementById('strengthFill');
            const strengthText = document.getElementById('strengthText');
            
            if (password.length === 0) {
                strengthFill.className = 'strength-fill';
                strengthText.textContent = 'Password strength';
                return;
            }
            
            let strength = 0;
            let strengthLabel = '';
            let strengthClass = '';
            
            // Length check
            if (password.length >= 8) strength++;
            if (password.length >= 12) strength++;
            
            // Character type checks
            if (/[a-z]/.test(password)) strength++;
            if (/[A-Z]/.test(password)) strength++;
            if (/[0-9]/.test(password)) strength++;
            if (/[^a-zA-Z0-9]/.test(password)) strength++;
            
            // Determine strength
            if (strength < 3) {
                strengthLabel = 'Weak';
                strengthClass = 'strength-weak';
            } else if (strength < 4) {
                strengthLabel = 'Fair';
                strengthClass = 'strength-fair';
            } else if (strength < 5) {
                strengthLabel = 'Good';
                strengthClass = 'strength-good';
            } else {
                strengthLabel = 'Strong';
                strengthClass = 'strength-strong';
            }
            
            strengthFill.className = `strength-fill ${strengthClass}`;
            strengthText.textContent = `Password strength: ${strengthLabel}`;
        });

        // Role selection styling
        document.querySelectorAll('.role-option').forEach(option => {
            option.addEventListener('click', function() {
                // Remove selected class from all options
                document.querySelectorAll('.role-option').forEach(opt => {
                    opt.classList.remove('selected');
                });
                
                // Add selected class to clicked option
                this.classList.add('selected');
                
                // Check the radio button
                const radio = this.querySelector('input[type="radio"]');
                radio.checked = true;
            });
        });

        // Real-time validation feedback
        document.getElementById('username').addEventListener('blur', function() {
            validateField(this);
        });

        document.getElementById('email').addEventListener('blur', function() {
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

        // Auto-focus username field
        document.getElementById('username').focus();
    </script>
</body>
</html>