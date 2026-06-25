<?php
/* ============================================================
   SmartShop – Register Page (auth/register.php)
   ============================================================ */
require_once __DIR__ . '/../includes/init.php';

if (is_logged_in()) {
    header('Location: ' . BASE_URL . '/index.php');
    exit;
}

$errors = [];
$values = ['username' => '', 'email' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid request. Please try again.';
    } else {
        $username  = trim($_POST['username'] ?? '');
        $email     = trim($_POST['email'] ?? '');
        $password  = $_POST['password'] ?? '';
        $password2 = $_POST['password2'] ?? '';

        $values = ['username' => $username, 'email' => $email];

        // Validation
        if (strlen($username) < 3 || strlen($username) > 50) {
            $errors[] = 'Username must be between 3 and 50 characters.';
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Please enter a valid email address.';
        }
        if (strlen($password) < 6) {
            $errors[] = 'Password must be at least 6 characters.';
        }
        if ($password !== $password2) {
            $errors[] = 'Passwords do not match.';
        }

        if (empty($errors)) {
            // Check for duplicates
            $existing = db_query(
                'SELECT id FROM users WHERE username = ? OR email = ? LIMIT 1',
                [$username, $email]
            )->fetch();

            if ($existing) {
                $errors[] = 'That username or email is already registered.';
            } else {
                $hash = password_hash($password, PASSWORD_BCRYPT);
                db_query(
                    "INSERT INTO users (username, email, password, role, status)
                     VALUES (?, ?, ?, 'customer', 'active')",
                    [$username, $email, $hash]
                );
                header('Location: ' . BASE_URL . '/auth/login.php?registered=1');
                exit;
            }
        }
    }
}
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Register – SmartShop</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?php echo BASE_URL; ?>/css/styles.css" rel="stylesheet">
    <script>
        if (localStorage.getItem('ss-dark') === '1') document.documentElement.classList.add('dark');
    </script>
</head>

<body>

    <div class="container">
        <div class="auth-card">

            <div class="auth-logo">
                <i class="fa-solid fa-store me-2"></i>SmartShop
            </div>
            <h2>Create Account</h2>
            <p class="text-center mb-4" style="color:var(--text-muted);font-size:.9rem">
                Join SmartShop today
            </p>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <?php foreach ($errors as $e): ?>
                        <div>• <?php echo htmlspecialchars($e); ?></div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <form method="POST" novalidate>
                <?php echo csrf_field(); ?>

                <div class="mb-3">
                    <label class="form-label" for="username">Username</label>
                    <input type="text" class="form-control" id="username" name="username"
                        value="<?php echo htmlspecialchars($values['username']); ?>"
                        placeholder="Choose a username"
                        minlength="3" maxlength="50" required autofocus>
                </div>

                <div class="mb-3">
                    <label class="form-label" for="email">Email Address</label>
                    <input type="email" class="form-control" id="email" name="email"
                        value="<?php echo htmlspecialchars($values['email']); ?>"
                        placeholder="your@email.com" required>
                </div>

                <div class="mb-3">
                    <label class="form-label" for="password">Password</label>
                    <div class="input-group">
                        <input type="password" class="form-control" id="password" name="password"
                            placeholder="At least 6 characters" minlength="6" required>
                        <button class="btn btn-outline-secondary password-toggle" type="button"
                            onclick="togglePassword('password', this)">👁</button>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label" for="password2">Confirm Password</label>
                    <div class="input-group">
                        <input type="password" class="form-control" id="password2" name="password2"
                            placeholder="Repeat your password" required>
                        <button class="btn btn-outline-secondary password-toggle" type="button"
                            onclick="togglePassword('password2', this)">👁</button>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary w-100 mt-2">
                    Create Account
                </button>
            </form>

            <div class="auth-footer">
                Already have an account?
                <a href="<?php echo BASE_URL; ?>/auth/login.php">Sign in</a>
            </div>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function togglePassword(fieldId, btn) {
            const f = document.getElementById(fieldId);
            if (f.type === 'password') {
                f.type = 'text';
                btn.textContent = '🙈';
            } else {
                f.type = 'password';
                btn.textContent = '👁';
            }
        }
    </script>
</body>

</html>