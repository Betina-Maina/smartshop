<?php
/* ============================================================
   SmartShop – Admin: Product List (admin/products.php)
   ============================================================ */
$pageTitle = 'Products';
require_once __DIR__ . '/../includes/init.php';
require_admin();

$q = trim($_GET['q'] ?? '');
$params = [];
$where  = '1=1';

if ($q) {
    $where   .= ' AND (name LIKE ? OR category LIKE ?)';
    $params[] = '%' . $q . '%';
    $params[] = '%' . $q . '%';
}

$products = db_query(
    "SELECT * FROM products WHERE $where ORDER BY created_at DESC",
    $params
)->fetchAll();

// Flash message
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

require_once __DIR__ . '/../includes/admin_layout.php';
?>

<?php if ($flash): ?>
<div class="alert alert-<?php echo htmlspecialchars($flash['type']); ?> alert-auto-dismiss">
    <?php echo htmlspecialchars($flash['msg']); ?>
</div>
<?php endif; ?>

<div class="card">
    <div class="card-body">
        <div class="d-flex flex-wrap gap-2 justify-content-between align-items-center mb-3">
            <form class="d-flex gap-2" method="get">
                <div class="search-bar">
                    <span class="search-icon">🔍</span>
                    <input class="form-control" name="q"
                           placeholder="Search products…"
                           value="<?php echo htmlspecialchars($q); ?>">
                </div>
                <button class="btn btn-outline-secondary">Search</button>
                <?php if ($q): ?>
                <a href="<?php echo BASE_URL; ?>/admin/products.php" class="btn btn-outline-secondary">Clear</a>
                <?php endif; ?>
            </form>
            <a href="<?php echo BASE_URL; ?>/admin/add_product.php" class="btn btn-primary">
                ➕ Add Product
            </a>
        </div>

        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th style="width:60px">ID</th>
                        <th style="width:70px">Image</th>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Stock</th>
                        <th>Added</th>
                        <th style="width:140px">Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($products)): ?>
                <tr>
                    <td colspan="8" class="text-center text-muted py-4">
                        No products found.
                        <a href="<?php echo BASE_URL; ?>/admin/add_product.php">Add one?</a>
                    </td>
                </tr>
                <?php else: ?>
                <?php foreach ($products as $p): ?>
                <tr>
                    <td><?php echo (int)$p['id']; ?></td>
                    <td>
                        <?php if ($p['image'] && file_exists(UPLOAD_DIR . $p['image'])): ?>
                        <img src="<?php echo BASE_URL . '/uploads/' . htmlspecialchars($p['image']); ?>"
                             style="width:48px;height:48px;object-fit:cover;border-radius:var(--radius-sm)"
                             alt="">
                        <?php else: ?>
                        <div style="width:48px;height:48px;background:var(--surface-2);border-radius:var(--radius-sm);display:flex;align-items:center;justify-content:center;font-size:1.3rem">🛍</div>
                        <?php endif; ?>
                    </td>
                    <td>
                        <a href="<?php echo BASE_URL; ?>/product.php?id=<?php echo (int)$p['id']; ?>"
                           target="_blank" style="color:var(--text);font-weight:500">
                            <?php echo htmlspecialchars($p['name']); ?>
                        </a>
                    </td>
                    <td><?php echo htmlspecialchars($p['category'] ?? '—'); ?></td>
                    <td>$<?php echo number_format((float)$p['price'], 2); ?></td>
                    <td><?php echo (int)$p['stock']; ?></td>
                    <td><?php echo date('M j, Y', strtotime($p['created_at'])); ?></td>
                    <td>
                        <a href="<?php echo BASE_URL; ?>/admin/edit_product.php?id=<?php echo (int)$p['id']; ?>"
                           class="btn btn-sm btn-outline-primary me-1">Edit</a>
                        <a href="<?php echo BASE_URL; ?>/admin/delete_product.php?id=<?php echo (int)$p['id']; ?>"
                           class="btn btn-sm btn-danger"
                           onclick="return confirmDelete('Delete &quot;<?php echo htmlspecialchars(addslashes($p['name'])); ?>&quot;?')">
                           Delete
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>
