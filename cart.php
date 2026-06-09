<?php
/* ============================================================
   SmartShop – Shopping Cart (cart.php)
   Features: view items, update qty, remove, order summary
   ============================================================ */
$pageTitle = 'Shopping Cart';
require_once __DIR__ . '/includes/init.php';
require_login();
require_once __DIR__ . '/includes/header.php';

$userId = (int)$_SESSION['user']['id'];

// -------------------------------------------------------
// Handle POST actions: update qty or remove item
// -------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'Invalid request.'];
        header('Location: ' . BASE_URL . '/cart.php');
        exit;
    }

    $cartId = (int)($_POST['cart_id'] ?? 0);

    if (isset($_POST['remove'])) {
        // Remove item
        db_query('DELETE FROM cart WHERE id = ? AND user_id = ?', [$cartId, $userId]);
        $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Item removed from cart.'];

    } elseif (isset($_POST['update'])) {
        // Update quantity
        $qty = max(1, (int)($_POST['quantity'] ?? 1));
        db_query('UPDATE cart SET quantity = ? WHERE id = ? AND user_id = ?',
                 [$qty, $cartId, $userId]);
        $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Cart updated.'];
    }

    header('Location: ' . BASE_URL . '/cart.php');
    exit;
}

// -------------------------------------------------------
// Fetch cart items with product details
// -------------------------------------------------------
$items = db_query(
    'SELECT c.id as cart_id, c.quantity,
            p.id as product_id, p.name, p.price, p.image, p.category
     FROM cart c
     JOIN products p ON p.id = c.product_id
     WHERE c.user_id = ?
     ORDER BY c.id ASC',
    [$userId]
)->fetchAll();

$subtotal = 0.0;
foreach ($items as $it) {
    $subtotal += (float)$it['price'] * (int)$it['quantity'];
}
$shipping = $subtotal > 0 ? ($subtotal >= 50 ? 0.0 : 5.99) : 0.0;
$total    = $subtotal + $shipping;

// Flash message
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
?>

<!-- Breadcrumb -->
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>/index.php">Home</a></li>
        <li class="breadcrumb-item active">Shopping Cart</li>
    </ol>
</nav>

<h2 class="section-title">Shopping Cart</h2>

<?php if ($flash): ?>
<div class="alert alert-<?php echo htmlspecialchars($flash['type']); ?> alert-auto-dismiss">
    <?php echo htmlspecialchars($flash['msg']); ?>
</div>
<?php endif; ?>

<?php if (empty($items)): ?>
<div class="empty-state">
    <div class="empty-icon">🛒</div>
    <h4>Your cart is empty</h4>
    <p>Looks like you haven't added anything yet.</p>
    <a href="<?php echo BASE_URL; ?>/products.php" class="btn btn-primary">Start Shopping</a>
</div>
<?php else: ?>

<div class="row g-4">

    <!-- Cart Items -->
    <div class="col-lg-8">
        <div class="card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead>
                            <tr>
                                <th style="width:80px"></th>
                                <th>Product</th>
                                <th>Price</th>
                                <th>Quantity</th>
                                <th>Subtotal</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($items as $it): ?>
                        <tr>
                            <!-- Thumbnail -->
                            <td>
                                <?php if ($it['image'] && file_exists(UPLOAD_DIR . $it['image'])): ?>
                                    <img src="<?php echo BASE_URL . '/uploads/' . htmlspecialchars($it['image']); ?>"
                                         class="cart-item-img"
                                         alt="<?php echo htmlspecialchars($it['name']); ?>">
                                <?php else: ?>
                                    <div class="cart-item-img d-flex align-items-center justify-content-center"
                                         style="background:var(--surface-2);border-radius:var(--radius-sm);font-size:1.8rem">
                                        🛍
                                    </div>
                                <?php endif; ?>
                            </td>

                            <!-- Name -->
                            <td>
                                <a href="<?php echo BASE_URL; ?>/product.php?id=<?php echo (int)$it['product_id']; ?>"
                                   style="color:var(--text);font-weight:500">
                                    <?php echo htmlspecialchars($it['name']); ?>
                                </a>
                                <?php if ($it['category']): ?>
                                <br><small class="text-muted"><?php echo htmlspecialchars($it['category']); ?></small>
                                <?php endif; ?>
                            </td>

                            <!-- Unit price -->
                            <td>$<?php echo number_format((float)$it['price'], 2); ?></td>

                            <!-- Quantity update form -->
                            <td>
                                <form method="post" class="d-flex align-items-center gap-1">
                                    <?php echo csrf_field(); ?>
                                    <input type="hidden" name="cart_id" value="<?php echo (int)$it['cart_id']; ?>">
                                    <div class="qty-control">
                                        <button type="button" data-action="dec">−</button>
                                        <input type="number" name="quantity"
                                               value="<?php echo (int)$it['quantity']; ?>"
                                               min="1" max="99">
                                        <button type="button" data-action="inc">+</button>
                                    </div>
                                    <button name="update" class="btn btn-outline-secondary btn-sm ms-1"
                                            title="Update quantity">↻</button>
                                </form>
                            </td>

                            <!-- Subtotal -->
                            <td class="fw-bold">
                                $<?php echo number_format((float)$it['price'] * (int)$it['quantity'], 2); ?>
                            </td>

                            <!-- Remove -->
                            <td>
                                <form method="post">
                                    <?php echo csrf_field(); ?>
                                    <input type="hidden" name="cart_id" value="<?php echo (int)$it['cart_id']; ?>">
                                    <button name="remove" class="btn btn-sm btn-danger"
                                            onclick="return confirm('Remove this item?')"
                                            title="Remove">✕</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="mt-3">
            <a href="<?php echo BASE_URL; ?>/products.php" class="btn btn-outline-secondary">
                ← Continue Shopping
            </a>
        </div>
    </div>

    <!-- Order Summary -->
    <div class="col-lg-4">
        <div class="order-summary">
            <h5 class="fw-bold mb-3">Order Summary</h5>

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
            <?php if ($shipping > 0): ?>
            <div class="summary-row" style="font-size:.8rem;color:var(--text-muted);border-bottom:none;padding-top:0">
                <span colspan="2">Add $<?php echo number_format(50 - $subtotal, 2); ?> more for free shipping</span>
            </div>
            <?php endif; ?>
            <div class="summary-row" style="font-size:1.15rem">
                <span>Total</span>
                <span>$<?php echo number_format($total, 2); ?></span>
            </div>

            <a href="<?php echo BASE_URL; ?>/checkout.php"
               class="btn btn-success w-100 mt-3 py-2 fw-bold">
                Proceed to Checkout →
            </a>
        </div>
    </div>

</div>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
