<?php
/* ============================================================
   SmartShop – Login Page (auth/login.php)
   ============================================================ */
require_once __DIR__ . '/../includes/init.php';

// Already logged in → redirect
if (is_logged_in()) {
    $dest = is_admin()
        ? BASE_URL . '/admin/dashboard.php'
        : BASE_URL . '/index.php';
    header('Location: ' . $dest);
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid request. Please try again.';
    } else {
        $identifier = trim($_POST['identifier'] ?? '');
        $password   = $_POST['password'] ?? '';

        if (!$identifier || !$password) {
            $error = 'Please enter your username/email and password.';
        } else {
            // *** NO status check — sample data has no status column ***
            // If your users table has a status column, add: AND status = 'active'
            $user = db_query(
                'SELECT * FROM users WHERE username = ? OR email = ? LIMIT 1',
                [$identifier, $identifier]
            )->fetch();

            if ($user && password_verify($password, $user['password'])) {
                session_regenerate_id(true);

                $_SESSION['user'] = [
                    'id'       => $user['id'],
                    'username' => $user['username'],
                    'email'    => $user['email'],
                    'role'     => $user['role'],
                ];

                if ($user['role'] === 'admin') {
                    header('Location: ' . BASE_URL . '/admin/dashboard.php');
                } else {
                    $next = $_GET['next'] ?? '';
                    $safe = ($next && str_starts_with($next, '/')) ? $next : BASE_URL . '/index.php';
                    header('Location: ' . $safe);
                }
                exit;
            } else {
                $error = 'Invalid username/email or password.';
            }
        }
    }
}

$pageTitle = 'Login';
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login – SmartShop</title>
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
            <h2 class="text-center mb-1">Welcome back</h2>
            <p class="text-center mb-4" style="color:var(--text-muted);font-size:.9rem">
                Sign in to your account
            </p>

            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <?php if (isset($_GET['registered'])): ?>
                <div class="alert alert-success">Account created! You can now log in.</div>
            <?php endif; ?>

            <?php if (isset($_GET['timeout'])): ?>
                <div class="alert alert-warning">Your session expired. Please sign in again.</div>
            <?php endif; ?>

            <form method="POST" novalidate>
                <?php echo csrf_field(); ?>

                <div class="mb-3">
                    <label class="form-label" for="identifier">Username or Email</label>
                    <input type="text" class="form-control" id="identifier" name="identifier"
                        value="<?php echo htmlspecialchars($_POST['identifier'] ?? ''); ?>"
                        placeholder="Enter username or email"
                        required autofocus autocomplete="username">
                </div>

                <div class="mb-3">
                    <label class="form-label" for="password">Password</label>
                    <div class="input-group">
                        <input type="password" class="form-control" id="password" name="password"
                            placeholder="Enter password" required autocomplete="current-password">
                        <button class="btn btn-outline-secondary" type="button"
                            onclick="togglePassword('password', this)">👁</button>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary w-100 mt-2">Sign In</button>
            </form>

            <div class="auth-footer mt-3 text-center" style="font-size:.88rem;color:var(--text-muted)">
                Don't have an account?
                <a href="<?php echo BASE_URL; ?>/auth/register.php">Create one</a>
            </div>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function togglePassword(id, btn) {
            var f = document.getElementById(id);
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