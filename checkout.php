<?php
/* ============================================================
   SmartShop – Checkout (checkout.php)
   Features: shipping form, order review, DB transaction,
             price re-validation server-side, CSRF protection
   ============================================================ */
$pageTitle = 'Checkout';
require_once __DIR__ . '/includes/init.php';
require_login();

$userId = (int)$_SESSION['user']['id'];

// Fetch cart items
$items = db_query(
    'SELECT c.id as cart_id, c.quantity,
            p.id as product_id, p.name, p.price, p.image
     FROM cart c
     JOIN products p ON p.id = c.product_id
     WHERE c.user_id = ?',
    [$userId]
)->fetchAll();

// Redirect to cart if empty
if (empty($items)) {
    header('Location: ' . BASE_URL . '/cart.php');
    exit;
}

$errors      = [];
$success     = false;
$newOrderId  = null;

// -------------------------------------------------------
// Handle POST: place order
// -------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid security token. Please try again.';
    } else {
        // Validate shipping fields
        $shipName    = trim($_POST['ship_name']    ?? '');
        $shipAddress = trim($_POST['ship_address'] ?? '');
        $shipCity    = trim($_POST['ship_city']    ?? '');
        $shipZip     = trim($_POST['ship_zip']     ?? '');

        if (!$shipName)    $errors[] = 'Full name is required.';
        if (!$shipAddress) $errors[] = 'Address is required.';
        if (!$shipCity)    $errors[] = 'City is required.';
        if (!$shipZip)     $errors[] = 'ZIP / Postal code is required.';
    }

    if (empty($errors)) {
        global $pdo;
        try {
            $pdo->beginTransaction();

            // Re-fetch prices from DB to prevent client-side tampering
            $orderTotal    = 0.0;
            $itemsDetailed = [];
            $stmtProd = $pdo->prepare('SELECT id, name, price FROM products WHERE id = ? FOR UPDATE');

            foreach ($items as $it) {
                $stmtProd->execute([(int)$it['product_id']]);
                $prod = $stmtProd->fetch();
                if (!$prod) throw new RuntimeException('Product not found: ' . $it['product_id']);

                $unit     = (float)$prod['price'];
                $qty      = (int)$it['quantity'];
                $subtotal = $unit * $qty;
                $orderTotal += $subtotal;

                $itemsDetailed[] = [
                    'product_id'   => (int)$prod['id'],
                    'product_name' => $prod['name'],
                    'unit_price'   => $unit,
                    'quantity'     => $qty,
                    'subtotal'     => $subtotal,
                ];
            }

            // Shipping cost
            $shipping   = $orderTotal >= 50 ? 0.0 : 5.99;
            $orderTotal += $shipping;

            // Insert order
            $pdo->prepare(
                'INSERT INTO orders (user_id, total_amount, order_status,
                                     shipping_name, shipping_address, shipping_city, shipping_zip)
                 VALUES (?, ?, ?, ?, ?, ?, ?)'
            )->execute([
                $userId, $orderTotal, 'pending',
                $shipName, $shipAddress, $shipCity, $shipZip,
            ]);
            $newOrderId = (int)$pdo->lastInsertId();

            // Insert order items
            $stmtItem = $pdo->prepare(
                'INSERT INTO order_items
                    (order_id, product_id, product_name, quantity, unit_price, subtotal)
                 VALUES (?, ?, ?, ?, ?, ?)'
            );
            foreach ($itemsDetailed as $d) {
                $stmtItem->execute([
                    $newOrderId, $d['product_id'], $d['product_name'],
                    $d['quantity'], $d['unit_price'], $d['subtotal'],
                ]);
            }

            // Clear cart
            db_query('DELETE FROM cart WHERE user_id = ?', [$userId]);

            $pdo->commit();
            $success = true;

        } catch (Exception $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            error_log('Checkout error: ' . $e->getMessage());
            $errors[] = 'Order could not be placed. Please try again or contact support.';
        }
    }
}

require_once __DIR__ . '/includes/header.php';

// -------------------------------------------------------
// Compute display totals (for the review section)
// -------------------------------------------------------
$subtotal = 0.0;
foreach ($items as $it) $subtotal += (float)$it['price'] * (int)$it['quantity'];
$shipping = $subtotal >= 50 ? 0.0 : 5.99;
$total    = $subtotal + $shipping;
?>

<!-- Breadcrumb -->
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>/index.php">Home</a></li>
        <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>/cart.php">Cart</a></li>
        <li class="breadcrumb-item active">Checkout</li>
    </ol>
</nav>

<?php if ($success): ?>
<!-- ===== Order Confirmation ===== -->
<div class="text-center py-5">
    <div style="font-size:4rem;margin-bottom:1rem">✅</div>
    <h2 class="fw-bold mb-2">Order Placed Successfully!</h2>
    <p class="text-muted mb-1">Thank you for your purchase.</p>
    <p>Your order ID is <strong>#<?php echo $newOrderId; ?></strong>.</p>
    <div class="mt-4 d-flex justify-content-center gap-3">
        <a href="<?php echo BASE_URL; ?>/orders.php" class="btn btn-primary">View My Orders</a>
        <a href="<?php echo BASE_URL; ?>/products.php" class="btn btn-outline-secondary">Continue Shopping</a>
    </div>
</div>

<?php else: ?>

<h2 class="section-title">Checkout</h2>

<?php if (!empty($errors)): ?>
<div class="alert alert-danger">
    <ul class="mb-0">
        <?php foreach ($errors as $e): ?>
        <li><?php echo htmlspecialchars($e); ?></li>
        <?php endforeach; ?>
    </ul>
</div>
<?php endif; ?>

<form method="post" novalidate>
    <?php echo csrf_field(); ?>

    <div class="row g-4">

        <!-- Shipping Form -->
        <div class="col-lg-7">
            <div class="card">
                <div class="card-body">
                    <h5 class="fw-bold mb-3">Shipping Information</h5>

                    <div class="mb-3">
                        <label class="form-label">Full Name *</label>
                        <input name="ship_name" class="form-control"
                               value="<?php echo htmlspecialchars($_POST['ship_name'] ?? ''); ?>"
                               placeholder="John Doe" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Street Address *</label>
                        <input name="ship_address" class="form-control"
                               value="<?php echo htmlspecialchars($_POST['ship_address'] ?? ''); ?>"
                               placeholder="123 Main St" required>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-7">
                            <label class="form-label">City *</label>
                            <input name="ship_city" class="form-control"
                                   value="<?php echo htmlspecialchars($_POST['ship_city'] ?? ''); ?>"
                                   placeholder="New York" required>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label">ZIP / Postal Code *</label>
                            <input name="ship_zip" class="form-control"
                                   value="<?php echo htmlspecialchars($_POST['ship_zip'] ?? ''); ?>"
                                   placeholder="10001" required>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Order Summary -->
        <div class="col-lg-5">
            <div class="order-summary">
                <h5 class="fw-bold mb-3">Order Review</h5>

                <?php foreach ($items as $it): ?>
                <div class="d-flex justify-content-between align-items-center mb-2"
                     style="font-size:.9rem">
                    <span>
                        <?php echo htmlspecialchars($it['name']); ?>
                        <span class="text-muted">× <?php echo (int)$it['quantity']; ?></span>
                    </span>
                    <span>$<?php echo number_format((float)$it['price'] * (int)$it['quantity'], 2); ?></span>
                </div>
                <?php endforeach; ?>

                <hr style="border-color:var(--border)">

                <div class="summary-row">
                    <span>Subtotal</span>
                    <span>$<?php echo number_format($subtotal, 2); ?></span>
                </div>
                <div class="summary-row">
                    <span>Shipping</span>
                    <span>
                        <?php if ($shipping === 0.0): ?>
                            <span class="text-success fw-bold">FREE</span>
                        <?php else: ?>
                            $<?php echo number_format($shipping, 2); ?>
                        <?php endif; ?>
                    </span>
                </div>
                <div class="summary-row">
                    <span>Total</span>
                    <span>$<?php echo number_format($total, 2); ?></span>
                </div>

                <button type="submit" class="btn btn-success w-100 mt-3 py-2 fw-bold">
                    Place Order
                </button>
                <a href="<?php echo BASE_URL; ?>/cart.php"
                   class="btn btn-outline-secondary w-100 mt-2">
                    Back to Cart
                </a>
            </div>
        </div>

    </div>
</form>

<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
