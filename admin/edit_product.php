<?php
/* ============================================================
   SmartShop – Admin: Edit Product (admin/edit_product.php)
   ============================================================ */
$pageTitle = 'Edit Product';
require_once __DIR__ . '/../includes/init.php';
require_admin();

$id = (int)($_GET['id'] ?? 0);
$p  = $id ? db_query('SELECT * FROM products WHERE id = ? LIMIT 1', [$id])->fetch() : null;

if (!$p) {
    $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'Product not found.'];
    header('Location: ' . BASE_URL . '/admin/products.php');
    exit;
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid security token.';
    } else {
        $name        = trim($_POST['name']        ?? '');
        $category    = trim($_POST['category']    ?? '');
        $price       = $_POST['price']            ?? '';
        $description = trim($_POST['description'] ?? '');
        $stock       = (int)($_POST['stock']      ?? 0);

        if (!$name)                              $errors[] = 'Product name is required.';
        if (!is_numeric($price) || $price <= 0)  $errors[] = 'Price must be a positive number.';
        if ($stock < 0)                          $errors[] = 'Stock cannot be negative.';

        // Handle image replacement
        $imageName = $p['image'];
        if (!empty($_FILES['image']['tmp_name'])) {
            $newImage = handle_image_upload($_FILES['image'], $errors);
            if ($newImage) {
                // Delete old image
                if ($p['image'] && file_exists(UPLOAD_DIR . $p['image'])) {
                    @unlink(UPLOAD_DIR . $p['image']);
                }
                $imageName = $newImage;
            }
        }

        if (empty($errors)) {
            db_query(
                'UPDATE products
                 SET name=?, category=?, price=?, image=?, description=?, stock=?
                 WHERE id=?',
                [$name, $category ?: null, (float)$price, $imageName, $description, $stock, $id]
            );
            $_SESSION['flash'] = ['type' => 'success', 'msg' => "Product \"$name\" updated."];
            header('Location: ' . BASE_URL . '/admin/products.php');
            exit;
        }

        // Re-populate $p with submitted values for re-display
        $p = array_merge($p, compact('name','category','price','description','stock'));
    }
}

$existingCategories = db_query(
    'SELECT DISTINCT category FROM products WHERE category IS NOT NULL ORDER BY category'
)->fetchAll(PDO::FETCH_COLUMN);

require_once __DIR__ . '/../includes/admin_layout.php';
?>

<div class="row justify-content-center">
<div class="col-lg-8">

<?php if (!empty($errors)): ?>
<div class="alert alert-danger">
    <ul class="mb-0 ps-3">
        <?php foreach ($errors as $e): ?>
        <li><?php echo htmlspecialchars($e); ?></li>
        <?php endforeach; ?>
    </ul>
</div>
<?php endif; ?>

<div class="card">
    <div class="card-body">
        <form method="post" enctype="multipart/form-data" novalidate>
            <?php echo csrf_field(); ?>

            <div class="row g-3">
                <div class="col-md-8">
                    <label class="form-label">Product Name *</label>
                    <input name="name" class="form-control"
                           value="<?php echo htmlspecialchars($p['name']); ?>" required>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Price ($) *</label>
                    <input name="price" type="number" step="0.01" min="0.01"
                           class="form-control"
                           value="<?php echo htmlspecialchars($p['price']); ?>" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Category</label>
                    <input name="category" class="form-control"
                           list="categoryList"
                           value="<?php echo htmlspecialchars($p['category'] ?? ''); ?>">
                    <datalist id="categoryList">
                        <?php foreach ($existingCategories as $cat): ?>
                        <option value="<?php echo htmlspecialchars($cat); ?>">
                        <?php endforeach; ?>
                    </datalist>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Stock Quantity</label>
                    <input name="stock" type="number" min="0"
                           class="form-control"
                           value="<?php echo (int)$p['stock']; ?>">
                </div>

                <div class="col-12">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="4"><?php echo htmlspecialchars($p['description'] ?? ''); ?></textarea>
                </div>

                <div class="col-12">
                    <label class="form-label">Product Image</label>
                    <?php if ($p['image'] && file_exists(UPLOAD_DIR . $p['image'])): ?>
                    <div class="mb-2">
                        <img id="imgPreview"
                             src="<?php echo BASE_URL . '/uploads/' . htmlspecialchars($p['image']); ?>"
                             alt="Current image"
                             style="max-width:180px;border-radius:var(--radius-sm)">
                        <div class="form-text">Current image. Upload a new one to replace it.</div>
                    </div>
                    <?php else: ?>
                    <img id="imgPreview" src="" alt="Preview"
                         style="display:none;max-width:180px;margin-bottom:.5rem;border-radius:var(--radius-sm)">
                    <?php endif; ?>
                    <input name="image" type="file" accept="image/*"
                           class="form-control"
                           onchange="previewImage(this,'imgPreview')">
                    <div class="form-text">JPEG, PNG, GIF, or WebP. Max 2 MB.</div>
                </div>
            </div>

            <div class="d-flex gap-2 mt-4">
                <button type="submit" class="btn btn-primary">
                    💾 Save Changes
                </button>
                <a href="<?php echo BASE_URL; ?>/admin/products.php"
                   class="btn btn-outline-secondary">Cancel</a>
                <a href="<?php echo BASE_URL; ?>/product.php?id=<?php echo (int)$id; ?>"
                   target="_blank" class="btn btn-outline-secondary ms-auto">
                    👁 Preview
                </a>
            </div>
        </form>
    </div>
</div>

</div>
</div>

<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>
