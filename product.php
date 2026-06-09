<?php
/* ============================================================
   SmartShop – Product Detail Page (product.php)
   ============================================================ */
require_once __DIR__ . '/includes/init.php';

$id = (int)($_GET['id'] ?? 0);
if (!$id) {
    header('Location: ' . BASE_URL . '/products.php');
    exit;
}

$p = db_query('SELECT * FROM products WHERE id = ? LIMIT 1', [$id])->fetch();
if (!$p) {
    header('Location: ' . BASE_URL . '/products.php');
    exit;
}

$pageTitle = $p['name'];
require_once __DIR__ . '/includes/header.php';

// Related products (same category, excluding current)
$related = [];
if ($p['category']) {
    $related = db_query(
        'SELECT id, name, price, image FROM products
         WHERE category = ? AND id != ?
         ORDER BY RAND() LIMIT 4',
        [$p['category'], $id]
    )->fetchAll();
}
?>

<!-- Breadcrumb -->
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>/index.php">Home</a></li>
        <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>/products.php">Products</a></li>
        <?php if ($p['category']): ?>
        <li class="breadcrumb-item">
            <a href="<?php echo BASE_URL; ?>/products.php?category=<?php echo urlencode($p['category']); ?>">
                <?php echo htmlspecialchars($p['category']); ?>
            </a>
        </li>
        <?php endif; ?>
        <li class="breadcrumb-item active"><?php echo htmlspecialchars($p['name']); ?></li>
    </ol>
</nav>

<!-- Product Detail -->
<div class="row g-5 mb-5">

    <!-- Image -->
    <div class="col-md-5">
        <div class="card" style="overflow:hidden;border-radius:var(--radius)">
            <?php if ($p['image'] && file_exists(UPLOAD_DIR . $p['image'])): ?>
                <img src="<?php echo BASE_URL . '/uploads/' . htmlspecialchars($p['image']); ?>"
                     alt="<?php echo htmlspecialchars($p['name']); ?>"
                     class="img-fluid"
                     style="width:100%;height:400px;object-fit:cover">
            <?php else: ?>
                <div class="img-placeholder" style="height:400px;font-size:6rem">🛍</div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Info -->
    <div class="col-md-7">
        <?php if ($p['category']): ?>
        <a href="<?php echo BASE_URL; ?>/products.php?category=<?php echo urlencode($p['category']); ?>"
           class="badge mb-2"
           style="background:var(--surface-2);color:var(--primary);text-decoration:none;font-size:.8rem">
            <?php echo htmlspecialchars($p['category']); ?>
        </a>
        <?php endif; ?>

        <h1 style="font-size:1.8rem;font-weight:700;margin-bottom:.5rem">
            <?php echo htmlspecialchars($p['name']); ?>
        </h1>

        <div class="price mb-3" style="font-size:2rem">
            $<?php echo number_format((float)$p['price'], 2); ?>
        </div>

        <?php if ($p['description']): ?>
        <p style="color:var(--text-muted);line-height:1.8;margin-bottom:1.5rem">
            <?php echo nl2br(htmlspecialchars($p['description'])); ?>
        </p>
        <?php endif; ?>

        <!-- Quantity + Add to Cart -->
        <div class="d-flex align-items-center gap-3 flex-wrap">
            <div class="qty-control">
                <button type="button" data-action="dec" aria-label="Decrease quantity">−</button>
                <input type="number" id="qtyInput" value="1" min="1" max="99"
                       aria-label="Quantity">
                <button type="button" data-action="inc" aria-label="Increase quantity">+</button>
            </div>

            <button class="btn btn-primary px-4"
                    id="addToCartBtn"
                    onclick="addToCart(<?php echo (int)$p['id']; ?>, parseInt(document.getElementById('qtyInput').value)||1, this)">
                🛒 Add to Cart
            </button>

            <a href="<?php echo BASE_URL; ?>/products.php" class="btn btn-outline-secondary">
                ← Back
            </a>
        </div>

        <!-- Meta info -->
        <div class="mt-4 pt-3" style="border-top:1px solid var(--border);font-size:.85rem;color:var(--text-muted)">
            <span>Added: <?php echo date('M j, Y', strtotime($p['created_at'])); ?></span>
        </div>
    </div>
</div>

<!-- Related Products -->
<?php if (!empty($related)): ?>
<h2 class="section-title">Related Products</h2>
<div class="row row-cols-2 row-cols-md-4 g-3">
    <?php foreach ($related as $r): ?>
    <div class="col">
        <div class="card product-card h-100">
            <a href="<?php echo BASE_URL; ?>/product.php?id=<?php echo (int)$r['id']; ?>">
                <div class="card-img-wrap" style="height:160px">
                    <?php if ($r['image'] && file_exists(UPLOAD_DIR . $r['image'])): ?>
                        <img src="<?php echo BASE_URL . '/uploads/' . htmlspecialchars($r['image']); ?>"
                             alt="<?php echo htmlspecialchars($r['name']); ?>"
                             loading="lazy">
                    <?php else: ?>
                        <div class="img-placeholder">🛍</div>
                    <?php endif; ?>
                </div>
            </a>
            <div class="card-body">
                <h6 class="card-title"><?php echo htmlspecialchars($r['name']); ?></h6>
                <div class="price" style="font-size:1rem">$<?php echo number_format((float)$r['price'], 2); ?></div>
                <button class="btn btn-primary btn-sm w-100 mt-2"
                        onclick="addToCart(<?php echo (int)$r['id']; ?>, 1, this)">
                    Add to Cart
                </button>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
