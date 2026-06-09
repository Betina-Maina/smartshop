-- ============================================================
-- SmartShop Database - Complete SQL Schema
-- Database: smartshop
-- Tables: 6
-- Compatibility: MySQL 5.7+
-- ============================================================

-- Create Database
CREATE DATABASE IF NOT EXISTS `smartshop`
  DEFAULT CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `smartshop`;

-- ============================================================
-- Table 1: users
-- Stores all registered users (admin + customers)
-- ============================================================
CREATE TABLE IF NOT EXISTS `users` (
  `id`         INT AUTO_INCREMENT PRIMARY KEY,
  `username`   VARCHAR(50)  NOT NULL UNIQUE,
  `email`      VARCHAR(100) NOT NULL UNIQUE,
  `password`   VARCHAR(255) NOT NULL,          -- bcrypt hash
  `role`       ENUM('admin','customer') NOT NULL DEFAULT 'customer',
  `status`     ENUM('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_username` (`username`),
  INDEX `idx_email` (`email`),
  INDEX `idx_role` (`role`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- Table 2: products
-- Stores product catalog with FULLTEXT search index
-- ============================================================
CREATE TABLE IF NOT EXISTS `products` (
  `id`          INT AUTO_INCREMENT PRIMARY KEY,
  `name`        VARCHAR(255) NOT NULL,
  `category`    VARCHAR(100) DEFAULT NULL,
  `price`       DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `image`       VARCHAR(255) DEFAULT NULL,
  `description` TEXT DEFAULT NULL,
  `stock`       INT NOT NULL DEFAULT 0,
  `status`      ENUM('active','inactive') NOT NULL DEFAULT 'active',
  `created_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_category` (`category`),
  INDEX `idx_status` (`status`),
  FULLTEXT INDEX `ft_search` (`name`, `description`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- Table 3: cart
-- Stores shopping cart items - one row per user+product
-- ============================================================
CREATE TABLE IF NOT EXISTS `cart` (
  `id`         INT AUTO_INCREMENT PRIMARY KEY,
  `user_id`    INT NOT NULL,
  `product_id` INT NOT NULL,
  `quantity`   INT NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY `uq_user_product` (`user_id`, `product_id`),
  FOREIGN KEY (`user_id`)    REFERENCES `users`(`id`)    ON DELETE CASCADE,
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE,
  INDEX `idx_user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- Table 4: orders
-- Stores order headers with tracking info
-- ============================================================
CREATE TABLE IF NOT EXISTS `orders` (
  `id`               INT AUTO_INCREMENT PRIMARY KEY,
  `user_id`          INT NULL DEFAULT NULL,
  `total_amount`     DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `order_status`     ENUM('pending','processing','shipped','completed','cancelled') 
                     NOT NULL DEFAULT 'pending',
  `shipping_name`    VARCHAR(100) DEFAULT NULL,
  `shipping_address` VARCHAR(255) DEFAULT NULL,
  `shipping_city`    VARCHAR(100) DEFAULT NULL,
  `shipping_zip`     VARCHAR(20)  DEFAULT NULL,
  `payment_method`   VARCHAR(50) DEFAULT NULL,
  `created_at`       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_user_id` (`user_id`),
  INDEX `idx_order_status` (`order_status`),
  INDEX `idx_created_at` (`created_at`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- Table 5: order_items
-- Stores individual line items in each order
-- Snapshot of product name/price at time of purchase
-- ============================================================
CREATE TABLE IF NOT EXISTS `order_items` (
  `id`           INT AUTO_INCREMENT PRIMARY KEY,
  `order_id`     INT NOT NULL,
  `product_id`   INT NOT NULL,
  `product_name` VARCHAR(255) DEFAULT NULL,
  `quantity`     INT NOT NULL DEFAULT 1,
  `unit_price`   DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `subtotal`     DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `created_at`   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_order_id` (`order_id`),
  FOREIGN KEY (`order_id`)    REFERENCES `orders`(`id`)   ON DELETE CASCADE,
  FOREIGN KEY (`product_id`)  REFERENCES `products`(`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- Table 6: admin_logs
-- Tracks admin activities for security and auditing
-- ============================================================
CREATE TABLE IF NOT EXISTS `admin_logs` (
  `id`         INT AUTO_INCREMENT PRIMARY KEY,
  `admin_id`   INT NOT NULL,
  `action`     VARCHAR(100) NOT NULL,
  `details`    TEXT DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`admin_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  INDEX `idx_admin_id` (`admin_id`),
  INDEX `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- Insert Sample Data
-- ============================================================

-- Insert Admin User
-- Username: admin
-- Password: Admin@123 (hashed with bcrypt)
-- Hash: $2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi
INSERT INTO `users` (`username`, `email`, `password`, `role`, `status`) VALUES
('admin', 'admin@smartshop.local', 
 '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'active');

-- Insert Sample Products
INSERT INTO `products` (`name`, `category`, `price`, `description`, `stock`, `status`) VALUES
('Wireless Noise-Cancelling Headphones', 'Electronics', 89.99,
 'Premium over-ear headphones with active noise cancellation, 30-hour battery life, and foldable design.', 50, 'active'),

('Mechanical Gaming Keyboard', 'Electronics', 59.99,
 'RGB backlit mechanical keyboard with tactile switches, anti-ghosting, and USB passthrough.', 35, 'active'),

('Ergonomic Office Chair', 'Furniture', 249.99,
 'Adjustable lumbar support, breathable mesh back, and 360° swivel for all-day comfort.', 15, 'active'),

('Stainless Steel Water Bottle', 'Kitchen', 24.99,
 'Double-wall vacuum insulated, keeps drinks cold 24h / hot 12h. BPA-free, 32 oz.', 100, 'active'),

('Running Shoes – Men', 'Footwear', 74.99,
 'Lightweight mesh upper, cushioned midsole, and durable rubber outsole for road running.', 60, 'active'),

('Yoga Mat – Non-Slip', 'Sports', 34.99,
 'Extra-thick 6mm TPE mat with alignment lines, carrying strap, and moisture-resistant surface.', 80, 'active'),

('Smart LED Desk Lamp', 'Electronics', 39.99,
 'Touch-dimming, 3 color temperatures, USB charging port, and memory function.', 45, 'active'),

('Leather Wallet – Slim', 'Accessories', 29.99,
 'Genuine leather bifold wallet with RFID blocking, 6 card slots, and slim profile.', 70, 'active'),

('Portable Bluetooth Speaker', 'Electronics', 49.99,
 '360° surround sound, IPX7 waterproof, 12-hour playtime, and built-in microphone.', 40, 'active'),

('Coffee Maker – 12 Cup', 'Kitchen', 54.99,
 'Programmable drip coffee maker with auto-shutoff, brew-strength selector, and keep-warm plate.', 25, 'active');

-- ============================================================
-- Create Views for Analytics
-- ============================================================

-- View: Sales Summary by Date
CREATE OR REPLACE VIEW `sales_summary` AS
SELECT 
    DATE(o.`created_at`) as `sale_date`,
    COUNT(DISTINCT o.`id`) as `total_orders`,
    SUM(o.`total_amount`) as `total_revenue`,
    COUNT(DISTINCT o.`user_id`) as `unique_customers`
FROM `orders` o
WHERE o.`order_status` != 'cancelled'
GROUP BY DATE(o.`created_at`)
ORDER BY `sale_date` DESC;

-- View: Product Sales Summary
CREATE OR REPLACE VIEW `product_sales` AS
SELECT 
    p.`id`,
    p.`name`,
    COUNT(oi.`id`) as `times_sold`,
    SUM(oi.`quantity`) as `total_quantity`,
    SUM(oi.`subtotal`) as `total_revenue`
FROM `products` p
LEFT JOIN `order_items` oi ON p.`id` = oi.`product_id`
GROUP BY p.`id`, p.`name`
ORDER BY `total_revenue` DESC;

-- View: Customer Orders Summary
CREATE OR REPLACE VIEW `customer_orders` AS
SELECT 
    u.`id`,
    u.`username`,
    u.`email`,
    COUNT(o.`id`) as `total_orders`,
    SUM(o.`total_amount`) as `total_spent`
FROM `users` u
LEFT JOIN `orders` o ON u.`id` = o.`user_id`
WHERE u.`role` = 'customer'
GROUP BY u.`id`, u.`username`, u.`email`
ORDER BY `total_spent` DESC;

-- ============================================================
-- End of SmartShop Database Schema
-- ============================================================

-- Summary:
-- ✓ 6 Tables created (users, products, cart, orders, order_items, admin_logs)
-- ✓ Foreign key relationships established
-- ✓ Indexes added for performance
-- ✓ 10 sample products inserted
-- ✓ 1 admin user created (admin/Admin@123)
-- ✓ 3 Views created for analytics
-- ✓ Character encoding: utf8mb4 (supports emojis & special characters)
-- ✓ Storage engine: InnoDB (supports transactions & relationships)
