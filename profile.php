<?php
/* ============================================================
   SmartShop – User Profile Page (profile.php)
   Satisfies Task 1: Responsive Personal Profile Page
   - Flexbox layout
   - Responsive profile image
   - About section
   - Contact information
   - Media queries for mobile and desktop
   ============================================================ */
$pageTitle = 'My Profile';
require_once __DIR__ . '/includes/init.php';
require_login();

$userId = (int)$_SESSION['user']['id'];
$user   = current_user();

// Stats for the about section
$orderCount = (int) db_query(
    'SELECT COUNT(*) FROM orders WHERE user_id = ?',
    [$userId]
)->fetchColumn();

$memberSince = db_query(
    'SELECT created_at FROM users WHERE id = ? LIMIT 1',
    [$userId]
)->fetchColumn();

require_once __DIR__ . '/includes/header.php';
?>

<style>
    /* ============================================================
   Profile Page, Flexbox layout + responsive media queries
   ============================================================ */

    /* Profile hero card, Flexbox row layout on desktop */
    .profile-hero {
        display: flex;
        flex-direction: row;
        align-items: center;
        gap: 2rem;
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--radius);
        box-shadow: var(--shadow);
        padding: 2rem;
        margin-bottom: 2rem;
    }

    /* Responsive profile image */
    .profile-avatar {
        width: 140px;
        height: 140px;
        min-width: 140px;
        /* prevent flex squishing */
        border-radius: 50%;
        object-fit: cover;
        border: 4px solid var(--primary);
        background: var(--surface-2);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 4rem;
        overflow: hidden;
        flex-shrink: 0;
    }

    .profile-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        border-radius: 50%;
    }

    .profile-info {
        flex: 1;
    }

    .profile-info h1 {
        font-size: 1.8rem;
        font-weight: 700;
        margin-bottom: .25rem;
        color: var(--text);
    }

    .profile-info .profile-role {
        display: inline-block;
        background: var(--primary);
        color: #fff;
        font-size: .75rem;
        font-weight: 600;
        padding: .2rem .7rem;
        border-radius: 999px;
        margin-bottom: .75rem;
    }

    .profile-info .profile-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 1rem;
        color: var(--text-muted);
        font-size: .9rem;
    }

    .profile-meta-item {
        display: flex;
        align-items: center;
        gap: .35rem;
    }

    /* Profile body — two column Flexbox */
    .profile-body {
        display: flex;
        flex-direction: row;
        gap: 1.5rem;
        align-items: flex-start;
    }

    .profile-main {
        flex: 2;
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
    }

    .profile-sidebar {
        flex: 1;
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
    }

    /* Section cards */
    .profile-section {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--radius);
        box-shadow: var(--shadow);
        padding: 1.5rem;
    }

    .profile-section h2 {
        font-size: 1rem;
        font-weight: 700;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: .07em;
        margin-bottom: 1rem;
        padding-bottom: .5rem;
        border-bottom: 2px solid var(--primary);
        display: inline-block;
    }

    /* Contact info rows */
    .contact-row {
        display: flex;
        align-items: center;
        gap: .75rem;
        padding: .6rem 0;
        border-bottom: 1px solid var(--border);
        color: var(--text);
        font-size: .92rem;
    }

    .contact-row:last-child {
        border-bottom: none;
    }

    .contact-icon {
        width: 36px;
        height: 36px;
        border-radius: var(--radius-sm);
        background: var(--surface-2);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.1rem;
        flex-shrink: 0;
    }

    /* Stat pills */
    .stat-pills {
        display: flex;
        flex-wrap: wrap;
        gap: .75rem;
    }

    .stat-pill {
        flex: 1;
        min-width: 80px;
        background: var(--surface-2);
        border-radius: var(--radius);
        padding: .9rem .75rem;
        text-align: center;
    }

    .stat-pill .pill-value {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--primary);
        line-height: 1;
    }

    .stat-pill .pill-label {
        font-size: .75rem;
        color: var(--text-muted);
        margin-top: .25rem;
    }

    /* ============================================================
   MEDIA QUERIES
   ============================================================ */

    /* Tablet — below 768px: stack profile hero vertically */
    @media (max-width: 768px) {
        .profile-hero {
            flex-direction: column;
            text-align: center;
            padding: 1.5rem 1rem;
            gap: 1.25rem;
        }

        .profile-info .profile-meta {
            justify-content: center;
        }

        /* Stack body columns */
        .profile-body {
            flex-direction: column;
        }

        .profile-main,
        .profile-sidebar {
            flex: none;
            width: 100%;
        }
    }

    /* Mobile — below 480px: tighten up spacing */
    @media (max-width: 480px) {
        .profile-avatar {
            width: 100px;
            height: 100px;
            min-width: 100px;
            font-size: 2.8rem;
        }

        .profile-info h1 {
            font-size: 1.4rem;
        }

        .profile-hero {
            padding: 1.25rem .75rem;
        }

        .stat-pills {
            gap: .5rem;
        }
    }
</style>

<!-- Breadcrumb -->
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>/index.php">Home</a></li>
        <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>/user_dashboard.php">Dashboard</a></li>
        <li class="breadcrumb-item active">My Profile</li>
    </ol>
</nav>

<!-- ===== Profile Hero (Flexbox row) ===== -->
<div class="profile-hero">

    <!-- Responsive profile image / avatar -->
    <div class="profile-avatar">
        👤
    </div>

    <!-- Profile info -->
    <div class="profile-info">
        <h1><?php echo htmlspecialchars($user['username']); ?></h1>
        <span class="profile-role"><?php echo ucfirst(htmlspecialchars($user['role'])); ?></span>

        <div class="profile-meta">
            <span class="profile-meta-item">
                ✉️ <?php echo htmlspecialchars($user['email']); ?>
            </span>
            <?php if ($memberSince): ?>
                <span class="profile-meta-item">
                    📅 Member since <?php echo date('M Y', strtotime($memberSince)); ?>
                </span>
            <?php endif; ?>
            <span class="profile-meta-item">
                📦 <?php echo $orderCount; ?> order<?php echo $orderCount !== 1 ? 's' : ''; ?>
            </span>
        </div>
    </div>
</div>

<!-- ===== Profile Body (two-column Flexbox) ===== -->
<div class="profile-body">

    <!-- Main column -->
    <div class="profile-main">

        <!-- About section -->
        <div class="profile-section">
            <h2>About</h2>
            <p style="color:var(--text-muted);line-height:1.8;margin:0">
                Hi, I'm <strong><?php echo htmlspecialchars($user['username']); ?></strong>
                a valued SmartShop <?php echo htmlspecialchars($user['role']); ?>.
                I enjoy browsing quality products across electronics, furniture, kitchen essentials,
                footwear, sports gear, and accessories. SmartShop is my go-to destination for
                reliable products at great prices.
            </p>
        </div>

        <!-- Activity stats -->
        <div class="profile-section">
            <h2>Activity</h2>
            <div class="stat-pills">
                <div class="stat-pill">
                    <div class="pill-value"><?php echo $orderCount; ?></div>
                    <div class="pill-label">Orders</div>
                </div>
                <div class="stat-pill">
                    <div class="pill-value">
                        <?php
                        $cartCount = (int) db_query(
                            'SELECT COALESCE(SUM(quantity),0) FROM cart WHERE user_id = ?',
                            [$userId]
                        )->fetchColumn();
                        echo $cartCount;
                        ?>
                    </div>
                    <div class="pill-label">In Cart</div>
                </div>
                <div class="stat-pill">
                    <div class="pill-value">
                        <?php
                        $spent = (float) db_query(
                            "SELECT COALESCE(SUM(total_amount),0) FROM orders
                             WHERE user_id = ? AND order_status != 'cancelled'",
                            [$userId]
                        )->fetchColumn();
                        echo '$' . number_format($spent, 0);
                        ?>
                    </div>
                    <div class="pill-label">Total Spent</div>
                </div>
                <div class="stat-pill">
                    <div class="pill-value">
                        <?php echo $memberSince ? date('Y') - date('Y', strtotime($memberSince)) : 0; ?>
                    </div>
                    <div class="pill-label">Yr<?php echo (date('Y') - date('Y', strtotime($memberSince ?? 'now'))) !== 1 ? 's' : ''; ?> Active</div>
                </div>
            </div>
        </div>

    </div>

    <!-- Sidebar column -->
    <div class="profile-sidebar">

        <!-- Contact information -->
        <div class="profile-section">
            <h2>Contact Info</h2>

            <div class="contact-row">
                <div class="contact-icon">👤</div>
                <div>
                    <div style="font-size:.75rem;color:var(--text-muted)">Username</div>
                    <div><?php echo htmlspecialchars($user['username']); ?></div>
                </div>
            </div>

            <div class="contact-row">
                <div class="contact-icon">✉️</div>
                <div>
                    <div style="font-size:.75rem;color:var(--text-muted)">Email Address</div>
                    <div><?php echo htmlspecialchars($user['email']); ?></div>
                </div>
            </div>

            <div class="contact-row">
                <div class="contact-icon">🛡️</div>
                <div>
                    <div style="font-size:.75rem;color:var(--text-muted)">Account Role</div>
                    <div><?php echo ucfirst(htmlspecialchars($user['role'])); ?></div>
                </div>
            </div>

            <?php if ($memberSince): ?>
                <div class="contact-row">
                    <div class="contact-icon">📅</div>
                    <div>
                        <div style="font-size:.75rem;color:var(--text-muted)">Member Since</div>
                        <div><?php echo date('F j, Y', strtotime($memberSince)); ?></div>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Quick actions -->
        <div class="profile-section">
            <h2>Quick Actions</h2>
            <div class="d-flex flex-column gap-2">
                <a href="<?php echo BASE_URL; ?>/user_dashboard.php"
                    class="btn btn-outline-primary btn-sm">📊 Dashboard</a>
                <a href="<?php echo BASE_URL; ?>/orders.php"
                    class="btn btn-outline-primary btn-sm">📦 My Orders</a>
                <a href="<?php echo BASE_URL; ?>/cart.php"
                    class="btn btn-outline-primary btn-sm">🛒 My Cart</a>
                <a href="<?php echo BASE_URL; ?>/auth/logout.php"
                    class="btn btn-outline-danger btn-sm">🚪 Logout</a>
            </div>
        </div>

    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>