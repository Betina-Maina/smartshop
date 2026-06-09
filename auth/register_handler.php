<?php
/**
 * SmartShop User Registration Handler
 * Handles new user registration with validation and security
 */

require_once '../config/database.php';

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Get form inputs
    $username = sanitize_input($_POST['username'] ?? '');
    $email = sanitize_input($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Validation
    $errors = [];
    
    // Validate username
    if (empty($username)) {
        $errors[] = "Username is required";
    } elseif (strlen($username) < 3) {
        $errors[] = "Username must be at least 3 characters";
    } elseif (strlen($username) > 50) {
        $errors[] = "Username must be less than 50 characters";
    } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        $errors[] = "Username can only contain letters, numbers, and underscores";
    }
    
    // Validate email
    if (empty($email)) {
        $errors[] = "Email is required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    }
    
    // Validate password
    if (empty($password)) {
        $errors[] = "Password is required";
    } elseif (strlen($password) < 8) {
        $errors[] = "Password must be at least 8 characters";
    } elseif (!preg_match('/[A-Z]/', $password)) {
        $errors[] = "Password must contain at least one uppercase letter";
    } elseif (!preg_match('/[a-z]/', $password)) {
        $errors[] = "Password must contain at least one lowercase letter";
    } elseif (!preg_match('/[0-9]/', $password)) {
        $errors[] = "Password must contain at least one number";
    }
    
    // Check password match
    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match";
    }
    
    // If no validation errors, check for duplicate username/email
    if (empty($errors)) {
        $check_query = "SELECT id FROM users WHERE username = ? OR email = ?";
        $existing = fetch_single_result($check_query, [$username, $email], "ss");
        
        if ($existing) {
            $errors[] = "Username or email already exists. Please try another.";
        }
    }
    
    // If validation passes, insert user
    if (empty($errors)) {
        // Hash password using bcrypt
        $hashed_password = password_hash($password, PASSWORD_BCRYPT, ['cost' => 10]);
        
        // Insert user into database
        $insert_query = "INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, 'customer')";
        $stmt = execute_query($insert_query, [$username, $email, $hashed_password], "sss");
        
        if ($stmt !== false) {
            // Registration successful - redirect to login
            header("Location: login.php?success=registered");
            exit();
        } else {
            $errors[] = "Registration failed. Please try again.";
        }
    }
}
?>
