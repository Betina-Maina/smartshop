<?php
/* ============================================================
   SmartShop – Admin: Add Product (admin/add_product.php)
   ============================================================ */
$pageTitle = 'Add Product';
require_once __DIR__ . '/../includes/init.php';
require_admin();

$errors = [];
$values = ['name' => '', 'category' => '', 'price' => '', 'description' => '', 'stock' => '0'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid security token.';
    } else {
        $name        = trim($_POST['name']        ?? '');
        $category    = trim($_POST['category']    ?? '');
        $price       = $_POST['price']            ?? '';
        $description = trim($_POST['description'] ?? '');
        $stock       = (int)($_POST['stock']      ?? 0);

        $values = compact('name', 'category', 'price', 'description', 'stock');

        if (!$name)                          $errors[] = 'Product name is required.';
        if (!is_numeric($price) || $price <= 0) $errors[] = 'Price must be a positive number.';
        if ($stock < 0)                      $errors[] = 'Stock cannot be negative.';

        // Handle image upload
        $imageName = null;
        if (!empty($_FILES['image']['tmp_name'])) {
            $imageName = handle_image_upload($_FILES['image'], $errors);
        }

        if (empty($errors)) {
            db_query(
                'INSERT INTO products (name, category, price, image, description, stock)
                 VALUES (?, ?, ?, ?, ?, ?)',
                [$name, $category ?: null, (float)$price, $imageName, $description, $stock]
            );
            $_SESSION['flash'] = ['type' => 'success', 'msg' => "Product \"$name\" added successfully."];
            header('Location: ' . BASE_URL . '/admin/products.php');
            exit;
        }
    }
}

// Fetch existing categories for datalist
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
                                value="<?php echo htmlspecialchars($values['name']); ?>"
                                placeholder="e.g. Wireless Headphones" required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Price ($) *</label>
                            <input name="price" type="number" step="0.01" min="0.01"
                                class="form-control"
                                value="<?php echo htmlspecialchars($values['price']); ?>"
                                placeholder="29.99" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Category</label>
                            <input name="category" class="form-control"
                                list="categoryList"
                                value="<?php echo htmlspecialchars($values['category']); ?>"
                                placeholder="Electronics, Clothing…">
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
                                value="<?php echo (int)$values['stock']; ?>">
                        </div>

                        <div class="col-12">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="4"
                                placeholder="Describe the product…"><?php echo htmlspecialchars($values['description']); ?></textarea>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Product Image</label>
                            <input name="image" type="file" accept="image/*"
                                class="form-control"
                                onchange="previewImage(this,'imgPreview')">
                            <div class="form-text">JPEG, PNG, GIF, or WebP. Max 2 MB.</div>
                            <img id="imgPreview" src="" alt="Preview"
                                style="display:none;max-width:200px;margin-top:.75rem;border-radius:var(--radius-sm)">
                        </div>
                    </div>

                    <div class="d-flex gap-2 mt-4">
                        <button type="submit" class="btn btn-primary">
                            ➕ Add Product
                        </button>
                        <a href="<?php echo BASE_URL; ?>/admin/products.php"
                            class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>

<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>