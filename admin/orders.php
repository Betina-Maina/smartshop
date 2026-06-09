<?php
/* ============================================================
   SmartShop – Admin: Orders Management (admin/orders.php)
   Features: list all orders, filter by status, update status
   ============================================================ */
$pageTitle = 'Orders';
require_once __DIR__ . '/../includes/init.php';
require_admin();

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

// -------------------------------------------------------
// Handle status update POST
// -------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'Invalid security token.'];
        header('Location: ' . BASE_URL . '/admin/orders.php');
        exit;
    }

    $orderId   = (int)($_POST['order_id'] ?? 0);
    $newStatus = $_POST['order_status'] ?? '';
    $allowed   = ['pending','processing','shipped','completed','cancelled'];

    if ($orderId && in_array($newStatus, $allowed, true)) {
        db_query('UPDATE orders SET order_status = ? WHERE id = ?', [$newStatus, $orderId]);
        $_SESSION['flash'] = ['type' => 'success', 'msg' => "Order #$orderId status updated to \"$newStatus\"."];
    }

    header('Location: ' . BASE_URL . '/admin/orders.php');
    exit;
}

// -------------------------------------------------------
// Filters
// -------------------------------------------------------
$filterStatus = $_GET['status'] ?? '';
$allowed      = ['','pending','processing','shipped','completed','cancelled'];
if (!in_array($filterStatus, $allowed, true)) $filterStatus = '';

$params = [];
$where  = '1=1';
if ($filterStatus) {
    $where   .= ' AND o.order_status = ?';
    $params[] = $filterStatus;
}

$orders = db_query(
    "SELECT o.*, u.username
     FROM orders o
     LEFT JOIN users u ON u.id = o.user_id
     WHERE $where
     ORDER BY o.created_at DESC",
    $params
)->fetchAll();

// Detail view for a single order
$detailOrder = null;
$detailItems = [];
if (isset($_GET['id'])) {
    $detailOrder = db_query(
        'SELECT o.*, u.username, u.email
         FROM orders o LEFT JOIN users u ON u.id = o.user_id
         WHERE o.id = ? LIMIT 1',
        [(int)$_GET['id']]
    )->fetch();
    if ($detailOrder) {
        $detailItems = db_query(
            'SELECT * FROM order_items WHERE order_id = ?',
            [(int)$_GET['id']]
        )->fetchAll();
    }
}

require_once __DIR__ . '/../includes/admin_layout.php';
?>

<?php if ($flash): ?>
<div class="alert alert-<?php echo htmlspecialchars($flash['type']); ?> alert-auto-dismiss">
    <?php echo htmlspecialchars($flash['msg']); ?>
</div>
<?php endif; ?>

<?php if ($detailOrder): ?>
<!-- ===== Order Detail View ===== -->
<div class="mb-3">
    <a href="<?php echo BASE_URL; ?>/admin/orders.php" class="btn btn-outline-secondary btn-sm">
        ← Back to Orders
    </a>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-body">
                <h5 class="fw-bold mb-3">Order #<?php echo (int)$detailOrder['id']; ?> – Items</h5>
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead>
                            <tr><th>Product</th><th>Unit Price</th><th>Qty</th><th class="text-end">Subtotal</th></tr>
                        </thead>
                        <tbody>
                        <?php foreach ($detailItems as $it): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($it['product_name'] ?? 'N/A'); ?></td>
                            <td>$<?php echo number_format((float)$it['unit_price'], 2); ?></td>
                            <td><?php echo (int)$it['quantity']; ?></td>
                            <td class="text-end">$<?php echo number_format((float)$it['subtotal'], 2); ?></td>
                        </tr>
                        <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="3" class="text-end fw-bold">Total</td>
                                <td class="text-end fw-bold">$<?php echo number_format((float)$detailOrder['total_amount'], 2); ?></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card mb-3">
            <div class="card-body">
                <h6 class="fw-bold mb-2">Customer</h6>
                <p class="mb-1"><?php echo htmlspecialchars($detailOrder['username'] ?? 'Guest'); ?></p>
                <p class="mb-0 text-muted" style="font-size:.85rem"><?php echo htmlspecialchars($detailOrder['email'] ?? ''); ?></p>
            </div>
        </div>

        <?php if ($detailOrder['shipping_name']): ?>
        <div class="card mb-3">
            <div class="card-body">
                <h6 class="fw-bold mb-2">Shipping Address</h6>
                <p class="mb-0" style="font-size:.9rem">
                    <?php echo htmlspecialchars($detailOrder['shipping_name']); ?><br>
                    <?php echo htmlspecialchars($detailOrder['shipping_address']); ?><br>
                    <?php echo htmlspecialchars($detailOrder['shipping_city']); ?>,
                    <?php echo htmlspecialchars($detailOrder['shipping_zip']); ?>
                </p>
            </div>
        </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-body">
                <h6 class="fw-bold mb-2">Update Status</h6>
                <form method="post">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="order_id" value="<?php echo (int)$detailOrder['id']; ?>">
                    <select name="order_status" class="form-select mb-2">
                        <?php foreach (['pending','processing','shipped','completed','cancelled'] as $s): ?>
                        <option value="<?php echo $s; ?>"
                                <?php echo $detailOrder['order_status'] === $s ? 'selected' : ''; ?>>
                            <?php echo ucfirst($s); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                    <button class="btn btn-primary w-100">Update Status</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php else: ?>
<!-- ===== Orders List ===== -->

<!-- Status filter tabs -->
<div class="d-flex flex-wrap gap-2 mb-3">
    <?php
    $tabs = ['' => 'All', 'pending' => 'Pending', 'processing' => 'Processing',
             'shipped' => 'Shipped', 'completed' => 'Completed', 'cancelled' => 'Cancelled'];
    foreach ($tabs as $val => $label):
        $active = $filterStatus === $val ? 'btn-primary' : 'btn-outline-secondary';
    ?>
    <a href="?<?php echo $val ? 'status=' . $val : ''; ?>"
       class="btn btn-sm <?php echo $active; ?>">
        <?php echo $label; ?>
    </a>
    <?php endforeach; ?>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Customer</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($orders)): ?>
                <tr><td colspan="6" class="text-center text-muted py-4">No orders found.</td></tr>
                <?php else: ?>
                <?php foreach ($orders as $o): ?>
                <tr>
                    <td>#<?php echo (int)$o['id']; ?></td>
                    <td><?php echo htmlspecialchars($o['username'] ?? 'Guest'); ?></td>
                    <td>$<?php echo number_format((float)$o['total_amount'], 2); ?></td>
                    <td>
                        <span class="status-badge status-<?php echo htmlspecialchars($o['order_status']); ?>">
                            <?php echo ucfirst(htmlspecialchars($o['order_status'])); ?>
                        </span>
                    </td>
                    <td><?php echo date('M j, Y', strtotime($o['created_at'])); ?></td>
                    <td>
                        <a href="?id=<?php echo (int)$o['id']; ?>"
                           class="btn btn-sm btn-outline-primary">Manage</a>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php endif; ?>

<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>
