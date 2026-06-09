<?php
/* ============================================================
   SmartShop – Admin: Users List (admin/users.php)
   ============================================================ */
$pageTitle = 'Users';
require_once __DIR__ . '/../includes/init.php';
require_admin();

$q = trim($_GET['q'] ?? '');
$params = [];
$where  = '1=1';

if ($q) {
    $where   .= ' AND (username LIKE ? OR email LIKE ?)';
    $params[] = '%' . $q . '%';
    $params[] = '%' . $q . '%';
}

$users = db_query(
    "SELECT u.*,
            (SELECT COUNT(*) FROM orders WHERE user_id = u.id) AS order_count
     FROM users u
     WHERE $where
     ORDER BY u.created_at DESC",
    $params
)->fetchAll();

require_once __DIR__ . '/../includes/admin_layout.php';
?>

<div class="card">
    <div class="card-body">
        <div class="d-flex flex-wrap gap-2 justify-content-between align-items-center mb-3">
            <form class="d-flex gap-2" method="get">
                <div class="search-bar">
                    <span class="search-icon">🔍</span>
                    <input class="form-control" name="q"
                           placeholder="Search by username or email…"
                           value="<?php echo htmlspecialchars($q); ?>">
                </div>
                <button class="btn btn-outline-secondary">Search</button>
                <?php if ($q): ?>
                <a href="<?php echo BASE_URL; ?>/admin/users.php" class="btn btn-outline-secondary">Clear</a>
                <?php endif; ?>
            </form>
            <span class="text-muted" style="font-size:.9rem">
                <?php echo count($users); ?> user<?php echo count($users) !== 1 ? 's' : ''; ?>
            </span>
        </div>

        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Orders</th>
                        <th>Joined</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($users)): ?>
                <tr><td colspan="6" class="text-center text-muted py-4">No users found.</td></tr>
                <?php else: ?>
                <?php foreach ($users as $u): ?>
                <tr>
                    <td><?php echo (int)$u['id']; ?></td>
                    <td>
                        <strong><?php echo htmlspecialchars($u['username']); ?></strong>
                        <?php if ((int)$u['id'] === (int)$_SESSION['user']['id']): ?>
                        <span class="badge ms-1" style="background:var(--primary);font-size:.65rem">You</span>
                        <?php endif; ?>
                    </td>
                    <td><?php echo htmlspecialchars($u['email']); ?></td>
                    <td>
                        <span class="badge"
                              style="background:<?php echo $u['role'] === 'admin' ? 'var(--danger)' : 'var(--success)'; ?>">
                            <?php echo ucfirst(htmlspecialchars($u['role'])); ?>
                        </span>
                    </td>
                    <td><?php echo (int)$u['order_count']; ?></td>
                    <td><?php echo date('M j, Y', strtotime($u['created_at'])); ?></td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>
