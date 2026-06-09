<?php
/* ============================================================
   SmartShop – Logout (auth/logout.php)
   ============================================================ */
require_once __DIR__ . '/../includes/init.php';

// Clear session data and destroy
session_unset();
session_destroy();

header('Location: ' . BASE_URL . '/auth/login.php');
exit;