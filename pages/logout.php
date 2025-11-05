<?php
/**
 * Logout Page
 * Secure logout with session cleanup
 */

define('AUTH_SYSTEM', true);
require_once __DIR__ . '/../includes/auth.php';

// Initialize session
initialize_session();

// Get current user info before logout
$current_user = get_current_user();
$is_ajax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

// Perform logout
$logout_success = logout();

// Handle AJAX logout requests
if ($is_ajax) {
    header('Content-Type: application/json');
    
    if ($logout_success) {
        echo json_encode([
            'success' => true,
            'message' => 'Logged out successfully',
            'redirect' => '/pages/login.php'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Logout failed'
        ]);
    }
    exit();
}

// For regular requests, redirect to login page with success message
if ($logout_success) {
    // Set success message for login page
    session_start();
    $_SESSION['success_message'] = 'You have been logged out successfully.';
    session_write_close();
    
    header('Location: login.php?logout=success');
} else {
    // If logout failed, still redirect to login but with error
    session_start();
    $_SESSION['error_message'] = 'Logout encountered an issue. You have been logged out.';
    session_write_close();
    
    header('Location: login.php?logout=error');
}
exit();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logging Out...</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            margin: 0;
            padding: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .logout-container {
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            text-align: center;
            max-width: 400px;
            width: 100%;
        }

        .spinner {
            width: 50px;
            height: 50px;
            border: 4px solid #f3f3f3;
            border-top: 4px solid #667eea;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto 20px;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .logout-message {
            color: #333;
            font-size: 18px;
            margin-bottom: 10px;
        }

        .redirect-message {
            color: #666;
            font-size: 14px;
        }

        .progress-bar {
            width: 100%;
            height: 4px;
            background-color: #f0f0f0;
            border-radius: 2px;
            margin: 20px 0;
            overflow: hidden;
        }

        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #667eea, #764ba2);
            width: 0%;
            animation: fillProgress 3s ease-in-out forwards;
        }

        @keyframes fillProgress {
            to { width: 100%; }
        }
    </style>
</head>
<body>
    <div class="logout-container">
        <div class="spinner"></div>
        <h2 class="logout-message">Logging you out...</h2>
        <p class="redirect-message">Please wait while we securely close your session.</p>
        
        <div class="progress-bar">
            <div class="progress-fill"></div>
        </div>
        
        <p style="font-size: 12px; color: #999; margin-top: 20px;">
            Redirecting to login page in a moment...
        </p>
    </div>

    <script>
        // Auto-redirect after 3 seconds
        setTimeout(function() {
            window.location.href = '/pages/login.php?logout=success';
        }, 3000);

        // Show logout confirmation
        if (typeof(Storage) !== "undefined") {
            // Store logout timestamp for analytics
            localStorage.setItem('lastLogout', new Date().toISOString());
        }

        // Log logout activity
        console.log('User logged out at:', new Date().toISOString());

        // Clear any cached data
        if ('caches' in window) {
            caches.keys().then(function(names) {
                names.forEach(function(name) {
                    caches.delete(name);
                });
            });
        }
    </script>
</body>
</html>