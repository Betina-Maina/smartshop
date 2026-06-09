<?php
/**
 * SmartShop User Login Handler
 * Authenticates users and creates secure PHP sessions
 */

require_once '../config/database.php';
require_once '../config/session.php';

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Get form inputs
    $username = sanitize_input($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    // Validation
    $errors = [];
    
    if (empty($username)) {
        $errors[] = "Username is required";
    }
    
    if (empty($password)) {
        $errors[] = "Password is required";
    }
    
    // Attempt login if validation passes
    if (empty($errors)) {
        // Query user by username using prepared statement (SQL injection prevention)
        $query = "SELECT id, username, email, password, role FROM users WHERE username = ?";
        $user = fetch_single_result($query, [$username], "s");
        
        if ($user) {
            // Verify password using bcrypt
            if (password_verify($password, $user['password'])) {
                // Password correct - set user session
                set_user_session($user['id'], $user['username'], $user['role']);
                
                // Log the login action
                $log_query = "INSERT INTO admin_logs (admin_id, action, details) VALUES (?, ?, ?)";
                if ($user['role'] === 'admin') {
                    execute_query($log_query, [$user['id'], 'login', 'Admin user logged in'], "iss");
                }
                
                // Redirect based on role
                if ($user['role'] === 'admin') {
                    header("Location: ../admin/dashboard.php");
                } else {
                    header("Location: ../index.php");
                }
                exit();
            } else {
                $errors[] = "Invalid password";
            }
        } else {
            // User not found - generic message for security
            $errors[] = "Invalid username or password";
        }
    }
}
?>
