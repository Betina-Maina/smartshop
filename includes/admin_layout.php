<?php
/* ============================================================
   SmartShop – Admin Layout Header
   ============================================================ */
if (!function_exists('is_admin')) require_once __DIR__ . '/init.php';

$_currentAdminPage = basename($_SERVER['PHP_SELF']);
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?php echo htmlspecialchars(csrf_token()); ?>">
    <title><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) . ' – Admin' : 'Admin – SmartShop'; ?></title>

    <!-- Bootstrap CSS via CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome via CDN -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <!-- Custom Styles - FIXED: was 'styles.css', correct name is 'style.css' -->
    <link href="<?php echo BASE_URL; ?>/css/styles.css" rel="stylesheet">

    <script>
        if (localStorage.getItem('ss-dark') === '1') {
            document.documentElement.classList.add('dark');
        }
    </script>
</head>

<body>

    <nav class="navbar navbar-expand-lg">
        <div class="container-fluid px-3">
            <a class="navbar-brand" href="<?php echo BASE_URL; ?>/admin/dashboard.php">
                <i class="fa-solid fa-store me-1"></i>Smart<span>Shop</span>
                <span class="badge ms-1" style="background:var(--primary);font-size:.65rem;vertical-align:middle">Admin</span>
            </a>

            <div class="d-flex align-items-center gap-2 ms-auto">
                <a href="<?php echo BASE_URL; ?>/index.php" class="btn btn-outline-secondary btn-sm">
                    ← Storefront
                </a>
                <a href="<?php echo BASE_URL; ?>/auth/logout.php" class="btn btn-outline-danger btn-sm">
                    Logout
                </a>
                <button id="darkToggle" title="Toggle dark mode">🌙</button>
            </div>
        </div>
    </nav>

    <div class="toast-container" id="toastContainer"></div>

    <div class="admin-layout">

        <aside class="admin-sidebar" id="adminSidebar">
            <div class="sidebar-brand">Navigation</div>
            <nav>
                <?php
                $navItems = [
                    ['href' => 'dashboard.php',   'icon' => '📊', 'label' => 'Dashboard'],
                    ['href' => 'products.php',    'icon' => '📦', 'label' => 'Products'],
                    ['href' => 'add_product.php', 'icon' => '➕', 'label' => 'Add Product'],
                    ['href' => 'orders.php',      'icon' => '🛒', 'label' => 'Orders'],
                    ['href' => 'users.php',       'icon' => '👥', 'label' => 'Users'],
                ];
                foreach ($navItems as $item):
                    $active = $_currentAdminPage === $item['href'] ? 'active' : '';
                ?>
                    <a href="<?php echo BASE_URL . '/admin/' . $item['href']; ?>"
                        class="nav-link <?php echo $active; ?>">
                        <span class="icon"><?php echo $item['icon']; ?></span>
                        <?php echo $item['label']; ?>
                    </a>
                <?php endforeach; ?>
            </nav>
        </aside>

        <div class="admin-content">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="fw-bold mb-0">
                    <?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) : 'Admin'; ?>
                </h4>
                <!-- FIXED: was $_SESSION['user']['username'], matches init.php structure -->
                <small class="text-muted">
                    Logged in as <strong><?php echo htmlspecialchars($_SESSION['user']['username'] ?? 'Admin'); ?></strong>
                </small>
            </div>