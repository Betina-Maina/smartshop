<?php
/* ============================================================
   SmartShop – Header / Navbar
   Location: C:\xampp\htdocs\smartshop\includes\header.php
   ============================================================ */
if (!function_exists('is_logged_in')) {
    require_once __DIR__ . '/init.php';
}

$_cartCount   = cart_count();
$_user        = current_user();
$_isAdmin     = is_admin();
$_currentPage = basename($_SERVER['PHP_SELF']);
$_currentDir  = basename(dirname($_SERVER['PHP_SELF']));
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?php echo htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8'); ?>">
    <title><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) . ' – SmartShop' : 'SmartShop'; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="<?php echo BASE_URL; ?>/css/style.css" rel="stylesheet">
    <script>if(localStorage.getItem('ss-dark')==='1')document.documentElement.classList.add('dark');</script>
    <style>
        .cart-nav-wrap{position:relative;display:inline-flex;align-items:center;padding:.35rem .45rem;font-size:1.2rem;text-decoration:none;color:var(--text,#333);line-height:1}
        .cart-nav-wrap:hover{color:#007bff}
        .cart-bubble{position:absolute;top:-2px;right:-4px;min-width:18px;height:18px;padding:0 4px;background:#007bff;color:#fff;font-size:.62rem;font-weight:700;border-radius:999px;display:flex;align-items:center;justify-content:center;border:2px solid #fff;pointer-events:none}
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg">
    <div class="container">
        <a class="navbar-brand" href="<?php echo BASE_URL; ?>/index.php">🛍 SmartShop</a>
        <button class="navbar-toggler" type="button"
                data-bs-toggle="collapse" data-bs-target="#mainNav"
                aria-controls="mainNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="mainNav">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link <?php echo $_currentPage==='index.php'?'active':''; ?>"
                       href="<?php echo BASE_URL; ?>/index.php">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $_currentPage==='products.php'?'active':''; ?>"
                       href="<?php echo BASE_URL; ?>/products.php">Products</a>
                </li>
                <?php if($_isAdmin): ?>
                <li class="nav-item">
                    <a class="nav-link <?php echo $_currentDir==='admin'?'active':''; ?>"
                       href="<?php echo BASE_URL; ?>/admin/dashboard.php">Admin</a>
                </li>
                <?php endif; ?>
            </ul>
            <ul class="navbar-nav align-items-center gap-1">
                <?php if(is_logged_in()): ?>
                <li class="nav-item">
                    <a href="<?php echo BASE_URL; ?>/cart.php" class="cart-nav-wrap" title="Cart">
                        🛒
                        <span id="cartBadge" class="cart-bubble"
                              <?php echo $_cartCount>0?'':'style="display:none"'; ?>>
                            <?php echo $_cartCount; ?>
                        </span>
                    </a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#"
                       role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        👤 <?php echo htmlspecialchars($_user['username']); ?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>/user_dashboard.php">My Dashboard</a></li>
                        <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>/orders.php">My Orders</a></li>
                        <?php if($_isAdmin): ?>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>/admin/dashboard.php">⚙️ Admin Panel</a></li>
                        <?php endif; ?>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="<?php echo BASE_URL; ?>/auth/logout.php">🚪 Logout</a></li>
                    </ul>
                </li>
                <?php else: ?>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo BASE_URL; ?>/auth/login.php">Login</a>
                </li>
                <li class="nav-item">
                    <a class="btn btn-primary btn-sm ms-1" href="<?php echo BASE_URL; ?>/auth/register.php">Register</a>
                </li>
                <?php endif; ?>
                <li class="nav-item ms-2">
                    <button id="darkToggle" title="Toggle dark mode">🌙</button>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div class="toast-container" id="toastContainer"></div>

<main class="container py-4">