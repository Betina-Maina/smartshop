<?php
/* ============================================================
   SmartShop – Home Page (index.php)
   ============================================================ */
require_once __DIR__ . '/includes/init.php';

$pageTitle = 'Home';

// FIXED: added 'stock' to SELECT so product cards work correctly
$featured = db_query(
    "SELECT id, name, category, price, image, description, stock
     FROM products
     WHERE status = 'active'
     ORDER BY created_at DESC
     LIMIT 8"
)->fetchAll();

// Categories for quick-filter bar
$categories = db_query(
    "SELECT DISTINCT category FROM products
     WHERE category IS NOT NULL AND status = 'active'
     ORDER BY category"
)->fetchAll(PDO::FETCH_COLUMN);

require_once __DIR__ . '/includes/header.php';
?>

<!-- Hero -->
<div class="hero mb-5">
    <div class="container">
        <h1>Welcome to SmartShop</h1>
        <p>Your Premier Destination for Quality Products</p>
        <a href="<?php echo BASE_URL; ?>/products.php" class="btn btn-dark btn-lg">
            Shop Now →
        </a>
    </div>
</div>

<!-- Flash messages -->
<?php if (isset($_GET['error'])): ?>
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <?php
    $errorMessages = [
        'unauthorized'    => 'You do not have permission to access that page.',
        'product_not_found' => 'Product not found.',
        'cart_error'      => 'An error occurred updating your cart.',
    ];
    echo htmlspecialchars($errorMessages[$_GET['error']] ?? 'An error occurred.');
    ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<?php if (isset($_GET['success'])): ?>
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <?php
    $successMessages = [
        'added_to_cart'   => 'Product added to cart!',
        'order_placed'    => 'Your order has been placed successfully!',
    ];
    echo htmlspecialchars($successMessages[$_GET['success']] ?? 'Done!');
    ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<!-- Category quick-filter -->
<?php if (!empty($categories)): ?>
<div class="mb-4 d-flex flex-wrap gap-2">
    <a href="<?php echo BASE_URL; ?>/products.php"
       class="btn btn-outline-primary btn-sm">All</a>
    <?php foreach ($categories as $cat): ?>
    <a href="<?php echo BASE_URL; ?>/products.php?category=<?php echo urlencode($cat); ?>"
       class="btn btn-outline-primary btn-sm">
        <?php echo htmlspecialchars($cat); ?>
    </a>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<!-- Featured Products -->
<h2 class="section-title">Featured Products</h2>

<?php if (empty($featured)): ?>
<div class="empty-state">
    <div class="empty-icon">📦</div>
    <h4>No products yet</h4>
    <p>Check back soon!</p>
</div>
<?php else: ?>

<div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-xl-4 g-4">
    <?php foreach ($featured as $p): ?>
    <div class="col">
        <div class="card product-card h-100">

            <a href="<?php echo BASE_URL; ?>/product.php?id=<?php echo (int)$p['id']; ?>">
                <div class="card-img-wrap">
                    <?php if (!empty($p['image']) && file_exists(UPLOAD_DIR . $p['image'])): ?>
                        <img src="<?php echo BASE_URL . '/uploads/' . htmlspecialchars($p['image']); ?>"
                             alt="<?php echo htmlspecialchars($p['name']); ?>"
                             loading="lazy">
                    <?php else: ?>
                        <div class="img-placeholder">🛍</div>
                    <?php endif; ?>
                </div>
            </a>

            <div class="card-body d-flex flex-column">
                <?php if ($p['category']): ?>
                <span class="badge mb-1"
                      style="background:var(--surface-2);color:var(--text-muted);font-size:.7rem">
                    <?php echo htmlspecialchars($p['category']); ?>
                </span>
                <?php endif; ?>

                <h5 class="card-title">
                    <a href="<?php echo BASE_URL; ?>/product.php?id=<?php echo (int)$p['id']; ?>"
                       style="color:var(--text);text-decoration:none">
                        <?php echo htmlspecialchars($p['name']); ?>
                    </a>
                </h5>

                <?php if (!empty($p['description'])): ?>
                <p style="font-size:.85rem;color:var(--text-muted);margin-bottom:.75rem;
                          display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden">
                    <?php echo htmlspecialchars($p['description']); ?>
                </p>
                <?php endif; ?>

                <div class="price mt-auto">$<?php echo number_format((float)$p['price'], 2); ?></div>

                <div class="card-footer-actions mt-2">
                    <?php if (is_logged_in()): ?>
                        <?php if ((int)$p['stock'] > 0): ?>
                        <button class="btn btn-primary btn-sm flex-fill"
                                onclick="addToCart(<?php echo (int)$p['id']; ?>, 1, this)">
                            🛒 Add to Cart
                        </button>
                        <?php else: ?>
                        <button class="btn btn-secondary btn-sm flex-fill" disabled>
                            Out of Stock
                        </button>
                        <?php endif; ?>
                    <?php else: ?>
                    <a href="<?php echo BASE_URL; ?>/auth/login.php"
                       class="btn btn-primary btn-sm flex-fill">
                        Login to Buy
                    </a>
                    <?php endif; ?>

                    <a href="<?php echo BASE_URL; ?>/product.php?id=<?php echo (int)$p['id']; ?>"
                       class="btn btn-outline-primary btn-sm">View</a>
                </div>
            </div>

        </div>
    </div>
    <?php endforeach; ?>
</div>

<div class="text-center mt-4">
    <a href="<?php echo BASE_URL; ?>/products.php" class="btn btn-outline-primary">
        View All Products
    </a>
</div>

<?php endif; ?>

<!-- Why Us section -->
<div class="row g-4 mt-5 mb-3">
    <div class="col-12">
        <h2 class="section-title">Why Choose SmartShop?</h2>
    </div>
    <div class="col-md-4">
        <div class="card h-100 text-center p-4">
            <div style="font-size:2.5rem;margin-bottom:1rem">🚚</div>
            <h5>Fast Shipping</h5>
            <p style="color:var(--text-muted);font-size:.9rem">Quick and reliable delivery to your doorstep.</p>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card h-100 text-center p-4">
            <div style="font-size:2.5rem;margin-bottom:1rem">🔒</div>
            <h5>Secure Payment</h5>
            <p style="color:var(--text-muted);font-size:.9rem">Your payment and personal data are always safe.</p>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card h-100 text-center p-4">
            <div style="font-size:2.5rem;margin-bottom:1rem">↩️</div>
            <h5>Easy Returns</h5>
            <p style="color:var(--text-muted);font-size:.9rem">30-day money-back guarantee on all items.</p>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>