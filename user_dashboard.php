<?php
/* ============================================================
   SmartShop – User Dashboard (user_dashboard.php)
   ============================================================ */
$pageTitle = 'My Dashboard';
require_once __DIR__ . '/includes/init.php';
require_login();
require_once __DIR__ . '/includes/header.php';

$userId = (int)$_SESSION['user']['id'];
$user   = current_user();

// Stats
$orderCount = (int) db_query(
    'SELECT COUNT(*) FROM orders WHERE user_id = ?', [$userId]
)->fetchColumn();

$totalSpent = (float) db_query(
    "SELECT COALESCE(SUM(total_amount),0) FROM orders WHERE user_id = ? AND order_status != 'cancelled'",
    [$userId]
)->fetchColumn();

$cartItems = (int) db_query(
    'SELECT COALESCE(SUM(quantity),0) FROM cart WHERE user_id = ?', [$userId]
)->fetchColumn();

// Recent orders (last 5)
$recentOrders = db_query(
    'SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC LIMIT 5',
    [$userId]
)->fetchAll();
?>

<!-- Breadcrumb -->
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>/index.php">Home</a></li>
        <li class="breadcrumb-item active">My Dashboard</li>
    </ol>
</nav>

<h2 class="section-title">Welcome back, <?php echo htmlspecialchars($user['username']); ?>!</h2>

<!-- Stat Cards -->
<div class="row g-3 mb-4">
    <div class="col-sm-4">
        <div class="stat-card">
            <div class="stat-icon stat-icon-blue">📦</div>
            <div>
                <div class="stat-value"><?php echo $orderCount; ?></div>
                <div class="stat-label">Total Orders</div>
            </div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="stat-card">
            <div class="stat-icon stat-icon-green">💰</div>
            <div>
                <div class="stat-value">$<?php echo number_format($totalSpent, 2); ?></div>
                <div class="stat-label">Total Spent</div>
            </div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="stat-card">
            <div class="stat-icon stat-icon-purple">🛒</div>
            <div>
                <div class="stat-value"><?php echo $cartItems; ?></div>
                <div class="stat-label">Items in Cart</div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">

    <!-- Recent Orders -->
    <div class="col-lg-8">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-bold mb-0">Recent Orders</h5>
                    <a href="<?php echo BASE_URL; ?>/orders.php" class="btn btn-outline-primary btn-sm">
                        View All
                    </a>
                </div>

                <?php if (empty($recentOrders)): ?>
                <p class="text-muted">No orders yet.</p>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Order #</th>
                                <th>Date</th>
                                <th>Total</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($recentOrders as $o): ?>
                        <tr>
                            <td>#<?php echo (int)$o['id']; ?></td>
                            <td><?php echo date('M j, Y', strtotime($o['created_at'])); ?></td>
                            <td>$<?php echo number_format((float)$o['total_amount'], 2); ?></td>
                            <td>
                                <span class="status-badge status-<?php echo htmlspecialchars($o['order_status']); ?>">
                                    <?php echo ucfirst(htmlspecialchars($o['order_status'])); ?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Quick Links + Account Info -->
    <div class="col-lg-4">
        <div class="card mb-3">
            <div class="card-body">
                <h5 class="fw-bold mb-3">Account Info</h5>
                <p class="mb-1"><strong>Username:</strong> <?php echo htmlspecialchars($user['username']); ?></p>
                <p class="mb-1"><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
                <p class="mb-0"><strong>Role:</strong>
                    <span class="badge" style="background:var(--primary)">
                        <?php echo ucfirst(htmlspecialchars($user['role'])); ?>
                    </span>
                </p>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <h5 class="fw-bold mb-3">Quick Links</h5>
                <div class="d-flex flex-column gap-2">
                    <a href="<?php echo BASE_URL; ?>/products.php" class="btn btn-outline-primary btn-sm">
                        🛍 Browse Products
                    </a>
                    <a href="<?php echo BASE_URL; ?>/cart.php" class="btn btn-outline-primary btn-sm">
                        🛒 View Cart
                        <?php if ($cartItems > 0): ?>
                        <span class="badge" style="background:var(--danger)"><?php echo $cartItems; ?></span>
                        <?php endif; ?>
                    </a>
                    <a href="<?php echo BASE_URL; ?>/orders.php" class="btn btn-outline-primary btn-sm">
                        📦 My Orders
                    </a>
                    <a href="<?php echo BASE_URL; ?>/auth/logout.php" class="btn btn-outline-danger btn-sm">
                        🚪 Logout
                    </a>
                </div>
            </div>
        </div>
    </div>

</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
