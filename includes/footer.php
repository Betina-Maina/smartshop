<?php /* SmartShop – Footer | includes/footer.php */ ?>
</main><!-- /.container (opened in header.php) -->

<footer>
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-4 mb-3 mb-md-0">
                <span style="font-size:1.2rem;font-weight:700;color:var(--primary)">🛍 SmartShop</span>
                <p class="mt-1 mb-0" style="font-size:.85rem">A modern, secure e-commerce platform.</p>
            </div>
            <div class="col-md-4 text-md-center mb-3 mb-md-0">
                <a href="<?php echo BASE_URL; ?>/products.php" class="me-3">Products</a>
                <a href="<?php echo BASE_URL; ?>/cart.php" class="me-3">Cart</a>
                <?php if (is_logged_in()): ?>
                <a href="<?php echo BASE_URL; ?>/orders.php">My Orders</a>
                <?php else: ?>
                <a href="<?php echo BASE_URL; ?>/auth/login.php">Login</a>
                <?php endif; ?>
            </div>
            <div class="col-md-4 text-md-end">
                <small>&copy; <?php echo date('Y'); ?> SmartShop. All rights reserved.</small>
            </div>
        </div>
    </div>
</footer>

<!-- Bootstrap JS (CDN) — includes Popper, required for dropdowns/modals/collapses -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- SmartShop app scripts -->
<script src="<?php echo BASE_URL; ?>/js/scripts.js"></script>

</body>
</html>