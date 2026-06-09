<?php
/* ============================================================
   SmartShop – Fix Admin Password (fix_admin.php)
   PUT THIS IN: C:\xampp\htdocs\smartshop\fix_admin.php
   
   STEP 1: Visit http://localhost/smartshop/fix_admin.php
   STEP 2: Delete this file immediately after.
   ============================================================ */
require_once __DIR__ . '/config/config.php';

$newPassword = 'Admin@123';
$newHash     = password_hash($newPassword, PASSWORD_DEFAULT);

// Check if admin user exists
$admin = db_query("SELECT id, username, password, role FROM users WHERE username = 'admin' LIMIT 1")->fetch();

if ($admin) {
    // Update password hash
    db_query("UPDATE users SET password = ? WHERE username = 'admin'", [$newHash]);
    echo "<h2 style='color:green'>✅ Admin password updated.</h2>";
    echo "<p>Username: <strong>admin</strong></p>";
    echo "<p>Password: <strong>Admin@123</strong></p>";
    echo "<p>Old hash was: <code>" . htmlspecialchars($admin['password']) . "</code></p>";
    echo "<p>New hash is:  <code>" . htmlspecialchars($newHash) . "</code></p>";
    echo "<p>Role in DB: <strong>" . htmlspecialchars($admin['role']) . "</strong></p>";
    
    if ($admin['role'] !== 'admin') {
        db_query("UPDATE users SET role = 'admin' WHERE username = 'admin'");
        echo "<p style='color:orange'>⚠️ Role was '{$admin['role']}' — fixed to 'admin'.</p>";
    }
} else {
    // No admin user found — create one
    db_query(
        "INSERT INTO users (username, email, password, role, created_at) VALUES (?, ?, ?, 'admin', NOW())",
        ['admin', 'admin@smartshop.local', $newHash]
    );
    echo "<h2 style='color:green'>✅ Admin user created.</h2>";
    echo "<p>Username: <strong>admin</strong></p>";
    echo "<p>Password: <strong>Admin@123</strong></p>";
}

echo "<br><p style='color:red'><strong>⚠️ DELETE THIS FILE NOW.</strong> It resets the admin password with no authentication check.</p>";
echo "<p><a href='/smartshop/auth/login.php'>→ Go to Login</a></p>";
?>