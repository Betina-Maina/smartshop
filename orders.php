<?php
/* ============================================================
   SmartShop – Customer Order History (orders.php)
   ============================================================ */
$pageTitle = 'My Orders';
require_once __DIR__ . '/includes/init.php';
require_login();
require_once __DIR__ . '/includes/header.php';

$userId = (int)$_SESSION['user']['id'];

// Fetch all orders for this user, newest first
$orders = db_query(
    'SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC',
    [$userId]
)->fetchAll();
?>

<!-- Breadcrumb -->
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>/index.php">Home</a></li>
        <li class="breadcrumb-item active">My Orders</li>
    </ol>
</nav>

<h2 class="section-title">My Orders</h2>

<?php if (empty($orders)): ?>
<div class="empty-state">
    <div class="empty-icon">📦</div>
    <h4>No orders yet</h4>
    <p>You haven't placed any orders. Start shopping!</p>
    <a href="<?php echo BASE_URL; ?>/products.php" class="btn btn-primary">Browse Products</a>
</div>
<?php else: ?>

<div class="d-flex flex-column gap-3">
<?php foreach ($orders as $o):
    // Fetch items for this order
    $orderItems = db_query(
        'SELECT oi.product_name, oi.quantity, oi.unit_price, oi.subtotal
         FROM order_items oi
         WHERE oi.order_id = ?',
        [(int)$o['id']]
    )->fetchAll();
?>
<div class="card">
    <div class="card-body">
        <!-- Order header -->
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-3">
            <div>
                <span class="fw-bold" style="font-size:1.05rem">Order #<?php echo (int)$o['id']; ?></span>
                <span class="text-muted ms-2" style="font-size:.85rem">
                    <?php echo date('M j, Y g:i A', strtotime($o['created_at'])); ?>
                </span>
            </div>
            <span class="status-badge status-<?php echo htmlspecialchars($o['order_status']); ?>">
                <?php echo ucfirst(htmlspecialchars($o['order_status'])); ?>
            </span>
        </div>

        <!-- Items table -->
        <div class="table-responsive">
            <table class="table table-sm mb-2">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Unit Price</th>
                        <th>Qty</th>
                        <th class="text-end">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($orderItems as $it): ?>
                <tr>
                    <td><?php echo htmlspecialchars($it['product_name'] ?? 'N/A'); ?></td>
                    <td>$<?php echo number_format((float)$it['unit_price'], 2); ?></td>
                    <td><?php echo (int)$it['quantity']; ?></td>
                    <td class="text-end">$<?php echo number_format((float)$it['subtotal'], 2); ?></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Footer: shipping + total -->
        <div class="d-flex flex-wrap justify-content-between align-items-end gap-2">
            <div style="font-size:.85rem;color:var(--text-muted)">
                <?php if ($o['shipping_name']): ?>
                📦 Ship to: <?php echo htmlspecialchars($o['shipping_name']); ?>,
                <?php echo htmlspecialchars($o['shipping_address']); ?>,
                <?php echo htmlspecialchars($o['shipping_city']); ?>
                <?php echo htmlspecialchars($o['shipping_zip']); ?>
                <?php endif; ?>
            </div>
            <div class="fw-bold" style="font-size:1.1rem">
                Total: $<?php echo number_format((float)$o['total_amount'], 2); ?>
            </div>
        </div>
    </div>
</div>
<?php endforeach; ?>
</div>

<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
