<?php
/**
 * SmartShop Session Configuration File
 * Handles session initialization, validation, and timeout
 * Uses PHP sessions only (NO cookies for security)
 */

// Start session with secure settings
if (session_status() === PHP_SESSION_NONE) {
    // Configure session parameters for security
    ini_set('session.use_strict_mode', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_secure', 0); // Set to 1 in production with HTTPS
    ini_set('session.gc_maxlifetime', 3600); // 1 hour session timeout
    
    session_start();
}

// Session timeout duration (in seconds) - 1 hour
define('SESSION_TIMEOUT', 3600);

/**
 * Initialize or validate user session
 * Checks if user is logged in and session is not expired
 */
function check_session() {
    // Check if user session exists
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['login_time'])) {
        return false;
    }
    
    // Check if session has expired
    if (time() - $_SESSION['login_time'] > SESSION_TIMEOUT) {
        destroy_session();
        return false;
    }
    
    // Update last activity time
    $_SESSION['login_time'] = time();
    return true;
}

/**
 * Set user session after successful login
 * @param int $user_id - User ID from database
 * @param string $username - Username
 * @param string $role - User role (admin/customer)
 */
function set_user_session($user_id, $username, $role) {
    $_SESSION['user_id'] = $user_id;
    $_SESSION['username'] = $username;
    $_SESSION['role'] = $role;
    $_SESSION['login_time'] = time();
}

/**
 * Check if user is logged in
 */
function is_logged_in() {
    return check_session();
}

/**
 * Check if user is admin
 */
function is_admin() {
    return check_session() && isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

/**
 * Check if user is customer
 */
function is_customer() {
    return check_session() && isset($_SESSION['role']) && $_SESSION['role'] === 'customer';
}

/**
 * Get current user ID
 */
function get_user_id() {
    return check_session() ? $_SESSION['user_id'] : null;
}

/**
 * Get current username
 */
function get_username() {
    return check_session() ? $_SESSION['username'] : null;
}

/**
 * Destroy user session on logout
 */
function destroy_session() {
    $_SESSION = [];
    session_destroy();
}

/**
 * Require authentication - redirect if not logged in
 */
function require_login() {
    if (!check_session()) {
        header("Location: /smartshop/auth/login.php");
        exit();
    }
}

/**
 * Require admin access - redirect if not admin
 */
function require_admin() {
    if (!is_admin()) {
        header("Location: /smartshop/index.php?error=unauthorized");
        exit();
    }
}

/**
 * Require customer access - redirect if not customer
 */
function require_customer() {
    if (!is_customer()) {
        header("Location: /smartshop/auth/login.php");
        exit();
    }
}

?>
