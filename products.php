<?php
/* ============================================================
   SmartShop – Products Listing (products.php)
   FIXED: init.php loaded first, all DB work before header.php
   ============================================================ */
$pageTitle = 'Products';
require_once __DIR__ . '/includes/init.php';  // DB + session before any output

// -------------------------------------------------------
// Input sanitization
// -------------------------------------------------------
$q        = trim($_GET['q']        ?? '');
$category = trim($_GET['category'] ?? '');
$sort     = in_array($_GET['sort'] ?? '', ['price_asc','price_desc','newest'])
            ? $_GET['sort'] : 'newest';
$page     = max(1, (int)($_GET['page'] ?? 1));
$perPage  = 9;
$offset   = ($page - 1) * $perPage;

// -------------------------------------------------------
// Build WHERE clause
// -------------------------------------------------------
$params = [];
$where  = "status = 'active'";

if ($q !== '') {
    $where   .= ' AND (name LIKE ? OR description LIKE ?)';
    $params[] = '%' . $q . '%';
    $params[] = '%' . $q . '%';
}
if ($category !== '') {
    $where   .= ' AND category = ?';
    $params[] = $category;
}

$orderBy = match ($sort) {
    'price_asc'  => 'price ASC',
    'price_desc' => 'price DESC',
    default      => 'created_at DESC',
};

// -------------------------------------------------------
// Count + paginate
// -------------------------------------------------------
$total = (int) db_query("SELECT COUNT(*) FROM products WHERE $where", $params)->fetchColumn();
$pages = $total > 0 ? (int) ceil($total / $perPage) : 1;
$page  = min($page, $pages);

$products = db_query(
    "SELECT id, name, category, price, image, description, stock
     FROM products WHERE $where ORDER BY $orderBy LIMIT ? OFFSET ?",
    array_merge($params, [$perPage, $offset])
)->fetchAll();

$allCategories = db_query(
    "SELECT category, COUNT(*) as cnt FROM products
     WHERE category IS NOT NULL AND status = 'active'
     GROUP BY category ORDER BY category"
)->fetchAll();

// -------------------------------------------------------
// Output starts here
// -------------------------------------------------------
require_once __DIR__ . '/includes/header.php';
?>

<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>/index.php">Home</a></li>
        <li class="breadcrumb-item active">Products</li>
    </ol>
</nav>

<div class="row g-4">

    <!-- ===== Sidebar ===== -->
    <div class="col-lg-3 filter-sidebar">
        <div class="card">
            <div class="card-body">
                <h6 class="fw-bold mb-3"
                    style="color:var(--text-muted);text-transform:uppercase;font-size:.8rem;letter-spacing:.05em">
                    Filter by Category
                </h6>
                <div class="form-check mb-1">
                    <input class="form-check-input" type="radio" name="catFilter"
                           id="catAll" value=""
                           <?php echo $category === '' ? 'checked' : ''; ?>
                           onchange="applyFilter()">
                    <label class="form-check-label" for="catAll">
                        All Categories
                        <span class="text-muted ms-1">(<?php echo $total; ?>)</span>
                    </label>
                </div>
                <?php foreach ($allCategories as $cat): ?>
                <div class="form-check mb-1">
                    <input class="form-check-input" type="radio" name="catFilter"
                           id="cat-<?php echo htmlspecialchars($cat['category']); ?>"
                           value="<?php echo htmlspecialchars($cat['category']); ?>"
                           <?php echo $category === $cat['category'] ? 'checked' : ''; ?>
                           onchange="applyFilter()">
                    <label class="form-check-label"
                           for="cat-<?php echo htmlspecialchars($cat['category']); ?>">
                        <?php echo htmlspecialchars($cat['category']); ?>
                        <span class="text-muted ms-1">(<?php echo (int)$cat['cnt']; ?>)</span>
                    </label>
                </div>
                <?php endforeach; ?>

                <hr style="border-color:var(--border)">

                <h6 class="fw-bold mb-3"
                    style="color:var(--text-muted);text-transform:uppercase;font-size:.8rem;letter-spacing:.05em">
                    Sort By
                </h6>
                <select class="form-select form-select-sm" id="sortSelect" onchange="applyFilter()">
                    <option value="newest"     <?php echo $sort==='newest'     ? 'selected':''?>">Newest First</option>
                    <option value="price_asc"  <?php echo $sort==='price_asc'  ? 'selected':''?>">Price: Low → High</option>
                    <option value="price_desc" <?php echo $sort==='price_desc' ? 'selected':''?>">Price: High → Low</option>
                </select>
            </div>
        </div>
    </div>

    <!-- ===== Product Grid ===== -->
    <div class="col-lg-9">

        <!-- Search bar -->
        <div class="d-flex flex-wrap gap-2 align-items-center mb-3">
            <form class="d-flex flex-fill" method="get" id="searchForm" data-clean-params>
                <input type="hidden" name="category" value="<?php echo htmlspecialchars($category); ?>">
                <input type="hidden" name="sort"     value="<?php echo htmlspecialchars($sort); ?>">
                <div class="search-bar flex-fill me-2">
                    <span class="search-icon">🔍</span>
                    <input class="form-control" name="q"
                           placeholder="Search products…"
                           value="<?php echo htmlspecialchars($q); ?>">
                </div>
                <button class="btn btn-primary">Search</button>
            </form>
            <?php if ($q || $category): ?>
            <a href="<?php echo BASE_URL; ?>/products.php" class="btn btn-outline-secondary btn-sm">✕ Clear</a>
            <?php endif; ?>
        </div>

        <p class="text-muted mb-3" style="font-size:.9rem">
            Showing <?php echo count($products); ?> of <?php echo $total; ?> products
            <?php if ($q): ?> for "<strong><?php echo htmlspecialchars($q); ?></strong>"<?php endif; ?>
            <?php if ($category): ?> in <strong><?php echo htmlspecialchars($category); ?></strong><?php endif; ?>
        </p>

        <?php if (empty($products)): ?>
        <div class="empty-state">
            <div class="empty-icon">🔍</div>
            <h4>No products found</h4>
            <p>Try a different search term or category.</p>
            <a href="<?php echo BASE_URL; ?>/products.php" class="btn btn-primary">Browse All</a>
        </div>
        <?php else: ?>

        <div class="row row-cols-1 row-cols-sm-2 row-cols-xl-3 g-4">
            <?php foreach ($products as $p): ?>
            <div class="col">
                <div class="card product-card h-100">

                    <a href="<?php echo BASE_URL; ?>/product.php?id=<?php echo (int)$p['id']; ?>">
                        <div class="card-img-wrap">
                            <?php if (!empty($p['image']) && file_exists(UPLOAD_DIR . $p['image'])): ?>
                                <img src="<?php echo BASE_URL.'/uploads/'.htmlspecialchars($p['image']); ?>"
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
                              style="background:var(--surface-2);color:var(--text-muted);font-size:.7rem;width:fit-content">
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
                                <?php if ((int)($p['stock'] ?? 1) > 0): ?>
                                <button class="btn btn-primary btn-sm flex-fill"
                                        onclick="addToCart(<?php echo (int)$p['id']; ?>, 1, this)">
                                    🛒 Add to Cart
                                </button>
                                <?php else: ?>
                                <button class="btn btn-secondary btn-sm flex-fill" disabled>Out of Stock</button>
                                <?php endif; ?>
                            <?php else: ?>
                            <a href="<?php echo BASE_URL; ?>/auth/login.php"
                               class="btn btn-primary btn-sm flex-fill">Login to Buy</a>
                            <?php endif; ?>
                            <a href="<?php echo BASE_URL; ?>/product.php?id=<?php echo (int)$p['id']; ?>"
                               class="btn btn-outline-primary btn-sm">View</a>
                        </div>
                    </div>

                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Pagination -->
        <?php if ($pages > 1): ?>
        <nav class="mt-4" aria-label="Product pages">
            <ul class="pagination justify-content-center flex-wrap">
                <?php if ($page > 1): ?>
                <li class="page-item">
                    <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET,['page'=>$page-1])); ?>">‹ Prev</a>
                </li>
                <?php endif; ?>
                <?php for ($i = max(1,$page-2); $i <= min($pages,$page+2); $i++): ?>
                <li class="page-item <?php echo $i===$page?'active':''; ?>">
                    <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET,['page'=>$i])); ?>"><?php echo $i; ?></a>
                </li>
                <?php endfor; ?>
                <?php if ($page < $pages): ?>
                <li class="page-item">
                    <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET,['page'=>$page+1])); ?>">Next ›</a>
                </li>
                <?php endif; ?>
            </ul>
        </nav>
        <?php endif; ?>

        <?php endif; ?>
    </div>
</div>

<script>
function applyFilter() {
    var cat  = document.querySelector('input[name="catFilter"]:checked')?.value || '';
    var sort = document.getElementById('sortSelect')?.value || 'newest';
    var q    = document.querySelector('input[name="q"]')?.value || '';
    var params = new URLSearchParams();
    if (q)   params.set('q', q);
    if (cat) params.set('category', cat);
    if (sort !== 'newest') params.set('sort', sort);
    window.location.href = '<?php echo BASE_URL; ?>/products.php?' + params.toString();
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>