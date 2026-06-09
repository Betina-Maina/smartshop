<?php
/* ============================================================
   SmartShop – Admin: Delete Product (admin/delete_product.php)
   GET request with id param – deletes product + image file
   ============================================================ */
require_once __DIR__ . '/../includes/init.php';
require_admin();

$id = (int)($_GET['id'] ?? 0);

if ($id) {
    $p = db_query('SELECT id, name, image FROM products WHERE id = ? LIMIT 1', [$id])->fetch();

    if ($p) {
        // Delete uploaded image file if it exists
        if ($p['image'] && file_exists(UPLOAD_DIR . $p['image'])) {
            @unlink(UPLOAD_DIR . $p['image']);
        }

        // Delete product (cascade will remove cart/order_items references)
        db_query('DELETE FROM products WHERE id = ?', [$id]);

        $_SESSION['flash'] = [
            'type' => 'success',
            'msg'  => 'Product "' . $p['name'] . '" deleted.',
        ];
    } else {
        $_SESSION['flash'] = ['type' => 'warning', 'msg' => 'Product not found.'];
    }
} else {
    $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'Invalid product ID.'];
}

header('Location: ' . BASE_URL . '/admin/products.php');
exit;
