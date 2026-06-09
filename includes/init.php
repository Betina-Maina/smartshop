<?php
/* ============================================================
   SmartShop – Initialization
   Loaded by every page. Sets up session, CSRF, auth helpers.
   ============================================================ */

require_once __DIR__ . '/../config/config.php';

// -------------------------------------------------------
// Security Headers
// -------------------------------------------------------
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');

// -------------------------------------------------------
// Session Configuration (must be before session_start)
// -------------------------------------------------------
ini_set('session.use_only_cookies',  1);
ini_set('session.cookie_httponly',   1);
ini_set('session.use_strict_mode',   1);
// Set SameSite=Lax via cookie params (PHP 7.3+)
session_set_cookie_params([
    'lifetime' => 0,
    'path'     => '/',
    'secure'   => false,   // set true when using HTTPS
    'httponly' => true,
    'samesite' => 'Lax',
]);

session_start();

// -------------------------------------------------------
// Session Timeout (30 minutes of inactivity)
// -------------------------------------------------------
define('SESSION_TIMEOUT', 30 * 60);

if (isset($_SESSION['last_activity']) &&
    (time() - $_SESSION['last_activity']) > SESSION_TIMEOUT) {
    session_unset();
    session_destroy();
    session_start();
}
$_SESSION['last_activity'] = time();

// -------------------------------------------------------
// CSRF Helpers
// -------------------------------------------------------

/** Return (and lazily create) the session CSRF token. */
function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/** Verify a submitted CSRF token using timing-safe comparison. */
function verify_csrf(string $token): bool
{
    return !empty($_SESSION['csrf_token']) &&
           hash_equals($_SESSION['csrf_token'], $token);
}

/** Render a hidden CSRF input field. */
function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' .
           htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') . '">';
}

// -------------------------------------------------------
// Authentication Helpers
// -------------------------------------------------------

function is_logged_in(): bool
{
    return !empty($_SESSION['user']['id']);
}

function require_login(): void
{
    if (!is_logged_in()) {
        header('Location: ' . BASE_URL . '/auth/login.php?next=' .
               urlencode($_SERVER['REQUEST_URI']));
        exit;
    }
}

function is_admin(): bool
{
    return is_logged_in() && ($_SESSION['user']['role'] ?? '') === 'admin';
}

function require_admin(): void
{
    if (!is_admin()) {
        header('Location: ' . BASE_URL . '/auth/login.php');
        exit;
    }
}

/** Return current user array or null. */
function current_user(): ?array
{
    return $_SESSION['user'] ?? null;
}

// -------------------------------------------------------
// Cart Count Helper (used in navbar badge)
// -------------------------------------------------------
function cart_count(): int
{
    if (!is_logged_in()) return 0;
    $row = db_query(
        'SELECT COALESCE(SUM(quantity),0) as cnt FROM cart WHERE user_id = ?',
        [$_SESSION['user']['id']]
    )->fetch();
    return (int)($row['cnt'] ?? 0);
}
