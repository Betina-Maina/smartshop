<?php
/* ============================================================
   SmartShop – Admin Dashboard (admin/dashboard.php)
   ============================================================ */
$pageTitle = 'Admin Dashboard';
require_once __DIR__ . '/../includes/init.php';
require_admin();

// -------------------------------------------------------
// Analytics data
// -------------------------------------------------------
$stats = db_query(
    'SELECT
        (SELECT COUNT(*) FROM users    WHERE role = "customer") AS customers,
        (SELECT COUNT(*) FROM products)                          AS products,
        (SELECT COUNT(*) FROM orders)                            AS orders,
        (SELECT COALESCE(SUM(total_amount),0) FROM orders
         WHERE order_status != "cancelled")                      AS revenue'
)->fetch();

// Orders by status
$statusCounts = db_query(
    'SELECT order_status, COUNT(*) as cnt FROM orders GROUP BY order_status'
)->fetchAll();
$statusMap = array_column($statusCounts, 'cnt', 'order_status');

// Revenue last 7 days (for chart)
$revenueChart = db_query(
    'SELECT DATE(created_at) as day, COALESCE(SUM(total_amount),0) as rev
     FROM orders
     WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
       AND order_status != "cancelled"
     GROUP BY DATE(created_at)
     ORDER BY day ASC'
)->fetchAll();

// Fill missing days
$chartData = [];
for ($i = 6; $i >= 0; $i--) {
    $day = date('Y-m-d', strtotime("-$i days"));
    $chartData[$day] = 0.0;
}
foreach ($revenueChart as $r) {
    $chartData[$r['day']] = (float)$r['rev'];
}

// Recent orders
$recentOrders = db_query(
    'SELECT o.*, u.username
     FROM orders o
     LEFT JOIN users u ON u.id = o.user_id
     ORDER BY o.created_at DESC
     LIMIT 10'
)->fetchAll();

require_once __DIR__ . '/../includes/admin_layout.php';
?>

<!-- Stat Cards -->
<div class="row g-3 mb-4">
    <div class="col-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon stat-icon-blue">👥</div>
            <div>
                <div class="stat-value"><?php echo number_format((int)$stats['customers']); ?></div>
                <div class="stat-label">Customers</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon stat-icon-purple">📦</div>
            <div>
                <div class="stat-value"><?php echo number_format((int)$stats['products']); ?></div>
                <div class="stat-label">Products</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon stat-icon-orange">🛒</div>
            <div>
                <div class="stat-value"><?php echo number_format((int)$stats['orders']); ?></div>
                <div class="stat-label">Total Orders</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon stat-icon-green">💰</div>
            <div>
                <div class="stat-value">$<?php echo number_format((float)$stats['revenue'], 0); ?></div>
                <div class="stat-label">Revenue</div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">

    <!-- Revenue Chart -->
    <div class="col-lg-8">
        <div class="card">
            <div class="card-body">
                <h5 class="fw-bold mb-3">Revenue – Last 7 Days</h5>
                <canvas id="revenueChart" height="100"></canvas>
            </div>
        </div>
    </div>

    <!-- Order Status Breakdown -->
    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-body">
                <h5 class="fw-bold mb-3">Orders by Status</h5>
                <?php
                $statuses = ['pending','processing','shipped','completed','cancelled'];
                foreach ($statuses as $s):
                    $cnt = (int)($statusMap[$s] ?? 0);
                    $pct = $stats['orders'] > 0 ? round($cnt / $stats['orders'] * 100) : 0;
                ?>
                <div class="mb-2">
                    <div class="d-flex justify-content-between mb-1" style="font-size:.85rem">
                        <span class="status-badge status-<?php echo $s; ?>"><?php echo ucfirst($s); ?></span>
                        <span><?php echo $cnt; ?></span>
                    </div>
                    <div style="height:6px;background:var(--surface-2);border-radius:3px">
                        <div style="height:100%;width:<?php echo $pct; ?>%;background:var(--primary);border-radius:3px;transition:width .5s"></div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<!-- Recent Orders Table -->
<div class="card">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="fw-bold mb-0">Recent Orders</h5>
            <a href="<?php echo BASE_URL; ?>/admin/orders.php" class="btn btn-outline-primary btn-sm">
                View All
            </a>
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Customer</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($recentOrders as $o): ?>
                <tr>
                    <td>#<?php echo (int)$o['id']; ?></td>
                    <td><?php echo htmlspecialchars($o['username'] ?? 'Guest'); ?></td>
                    <td>$<?php echo number_format((float)$o['total_amount'], 2); ?></td>
                    <td>
                        <span class="status-badge status-<?php echo htmlspecialchars($o['order_status']); ?>">
                            <?php echo ucfirst(htmlspecialchars($o['order_status'])); ?>
                        </span>
                    </td>
                    <td><?php echo date('M j, Y', strtotime($o['created_at'])); ?></td>
                    <td>
                        <a href="<?php echo BASE_URL; ?>/admin/orders.php?id=<?php echo (int)$o['id']; ?>"
                           class="btn btn-sm btn-outline-primary">Manage</a>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Chart.js CDN (lightweight, no local dep needed) -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
(function () {
    var labels = <?php echo json_encode(array_map(fn($d) => date('M j', strtotime($d)), array_keys($chartData))); ?>;
    var data   = <?php echo json_encode(array_values($chartData)); ?>;
    var isDark = document.body.classList.contains('dark');
    var gridColor = isDark ? 'rgba(255,255,255,.08)' : 'rgba(0,0,0,.06)';
    var textColor = isDark ? '#94a3b8' : '#6c757d';

    new Chart(document.getElementById('revenueChart'), {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Revenue ($)',
                data: data,
                backgroundColor: 'rgba(79,70,229,.7)',
                borderColor: 'rgba(79,70,229,1)',
                borderWidth: 1,
                borderRadius: 4,
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: {
                x: { grid: { color: gridColor }, ticks: { color: textColor } },
                y: { grid: { color: gridColor }, ticks: { color: textColor,
                     callback: v => '$' + v } }
            }
        }
    });
})();
</script>

<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>
