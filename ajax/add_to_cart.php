<?php
/* ============================================================
   SmartShop – AJAX: Add to Cart (ajax/add_to_cart.php)
   Accepts JSON POST, returns JSON response.
   ============================================================ */
require_once __DIR__ . '/../includes/init.php';
header('Content-Type: application/json');

// Only accept POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
    exit;
}

// Parse JSON body
$input      = json_decode(file_get_contents('php://input'), true);
$productId  = (int)($input['product_id'] ?? 0);
$quantity   = max(1, (int)($input['quantity'] ?? 1));
$csrf       = $input['csrf_token'] ?? '';

// CSRF check
if (!verify_csrf($csrf)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Invalid security token.']);
    exit;
}

// Auth check – return redirect hint so JS can redirect
if (!is_logged_in()) {
    echo json_encode([
        'success'  => false,
        'message'  => 'Please log in to add items to your cart.',
        'redirect' => BASE_URL . '/auth/login.php',
    ]);
    exit;
}

// Validate product
if (!$productId) {
    echo json_encode(['success' => false, 'message' => 'Invalid product.']);
    exit;
}

$product = db_query(
    'SELECT id FROM products WHERE id = ? LIMIT 1',
    [$productId]
)->fetch();

if (!$product) {
    echo json_encode(['success' => false, 'message' => 'Product not found.']);
    exit;
}

// Upsert cart row
try {
    $userId   = (int)$_SESSION['user']['id'];
    $existing = db_query(
        'SELECT id, quantity FROM cart WHERE user_id = ? AND product_id = ? LIMIT 1',
        [$userId, $productId]
    )->fetch();

    if ($existing) {
        db_query(
            'UPDATE cart SET quantity = quantity + ? WHERE id = ?',
            [$quantity, (int)$existing['id']]
        );
    } else {
        db_query(
            'INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)',
            [$userId, $productId, $quantity]
        );
    }

    // Return updated cart count for badge
    $cartCount = (int) db_query(
        'SELECT COALESCE(SUM(quantity), 0) FROM cart WHERE user_id = ?',
        [$userId]
    )->fetchColumn();

    echo json_encode(['success' => true, 'cart_count' => $cartCount]);

} catch (Exception $e) {
    error_log('Add to cart error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Server error. Please try again.']);
}
