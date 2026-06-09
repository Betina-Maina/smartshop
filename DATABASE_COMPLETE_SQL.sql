-- ============================================
-- SMARTSHOP DATABASE - COMPLETE SQL SCHEMA
-- ============================================

-- CREATE DATABASE
CREATE DATABASE IF NOT EXISTS `smartshop`
  DEFAULT CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `smartshop`;

-- ============================================
-- TABLE 1: USERS
-- Stores user accounts with roles
-- ============================================
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY COMMENT 'Unique user ID',
  `username` VARCHAR(100) NOT NULL UNIQUE COMMENT 'Username (3-50 chars)',
  `email` VARCHAR(255) NOT NULL UNIQUE COMMENT 'Email address',
  `password` VARCHAR(255) NOT NULL COMMENT 'Bcrypt hashed password',
  `role` ENUM('admin', 'customer') NOT NULL DEFAULT 'customer' COMMENT 'User role',
  `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active' COMMENT 'Account status',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Account creation date',
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Last update date',
  
  -- INDEXES
  INDEX `idx_username` (`username`),
  INDEX `idx_email` (`email`),
  INDEX `idx_role` (`role`),
  INDEX `idx_status` (`status`)
  
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='User accounts table';

-- ============================================
-- TABLE 2: PRODUCTS
-- Stores product information with images
-- ============================================
CREATE TABLE IF NOT EXISTS `products` (
  `id` INT AUTO_INCREMENT PRIMARY KEY COMMENT 'Unique product ID',
  `name` VARCHAR(255) NOT NULL COMMENT 'Product name',
  `category` VARCHAR(100) NOT NULL COMMENT 'Product category',
  `price` DECIMAL(10, 2) NOT NULL COMMENT 'Product price',
  `image` VARCHAR(255) DEFAULT NULL COMMENT 'Product image filename',
  `description` LONGTEXT DEFAULT NULL COMMENT 'Product detailed description',
  `stock` INT NOT NULL DEFAULT 0 COMMENT 'Stock quantity',
  `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active' COMMENT 'Product status',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Product creation date',
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Last update date',
  
  -- INDEXES
  INDEX `idx_category` (`category`),
  INDEX `idx_status` (`status`),
  FULLTEXT INDEX `ft_search` (`name`, `description`)
  
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Products catalog table';

-- ============================================
-- TABLE 3: CART
-- Stores shopping cart items for users
-- ============================================
CREATE TABLE IF NOT EXISTS `cart` (
  `id` INT AUTO_INCREMENT PRIMARY KEY COMMENT 'Unique cart item ID',
  `user_id` INT NOT NULL COMMENT 'User ID',
  `product_id` INT NOT NULL COMMENT 'Product ID',
  `quantity` INT NOT NULL DEFAULT 1 COMMENT 'Item quantity',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Item added date',
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Last update date',
  
  -- UNIQUE CONSTRAINT
  UNIQUE KEY `unique_user_product` (`user_id`, `product_id`),
  
  -- FOREIGN KEYS
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE,
  
  -- INDEXES
  INDEX `idx_user_id` (`user_id`)
  
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Shopping cart items table';

-- ============================================
-- TABLE 4: ORDERS
-- Stores order headers
-- ============================================
CREATE TABLE IF NOT EXISTS `orders` (
  `id` INT AUTO_INCREMENT PRIMARY KEY COMMENT 'Unique order ID',
  `user_id` INT NOT NULL COMMENT 'Customer user ID',
  `total_amount` DECIMAL(10, 2) NOT NULL COMMENT 'Order total amount',
  `order_status` ENUM('pending', 'processing', 'completed', 'cancelled') NOT NULL DEFAULT 'pending' COMMENT 'Order status',
  `shipping_address` TEXT DEFAULT NULL COMMENT 'Shipping address',
  `payment_method` VARCHAR(50) DEFAULT NULL COMMENT 'Payment method used',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Order creation date',
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Last update date',
  
  -- FOREIGN KEY
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  
  -- INDEXES
  INDEX `idx_user_id` (`user_id`),
  INDEX `idx_order_status` (`order_status`),
  INDEX `idx_created_at` (`created_at`)
  
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Orders table';

-- ============================================
-- TABLE 5: ORDER_ITEMS
-- Stores individual items in each order
-- ============================================
CREATE TABLE IF NOT EXISTS `order_items` (
  `id` INT AUTO_INCREMENT PRIMARY KEY COMMENT 'Unique order item ID',
  `order_id` INT NOT NULL COMMENT 'Order ID',
  `product_id` INT NOT NULL COMMENT 'Product ID',
  `quantity` INT NOT NULL COMMENT 'Quantity ordered',
  `unit_price` DECIMAL(10, 2) NOT NULL COMMENT 'Price per unit at time of order',
  `subtotal` DECIMAL(10, 2) NOT NULL COMMENT 'Quantity × unit_price',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Item added date',
  
  -- FOREIGN KEYS
  FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE SET NULL,
  
  -- INDEXES
  INDEX `idx_order_id` (`order_id`)
  
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Order items details table';

-- ============================================
-- TABLE 6: ADMIN_LOGS
-- Tracks admin activities for security
-- ============================================
CREATE TABLE IF NOT EXISTS `admin_logs` (
  `id` INT AUTO_INCREMENT PRIMARY KEY COMMENT 'Unique log ID',
  `admin_id` INT NOT NULL COMMENT 'Admin user ID',
  `action` VARCHAR(100) NOT NULL COMMENT 'Action performed',
  `details` TEXT DEFAULT NULL COMMENT 'Action details',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Log creation date',
  
  -- FOREIGN KEY
  FOREIGN KEY (`admin_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  
  -- INDEXES
  INDEX `idx_admin_id` (`admin_id`),
  INDEX `idx_created_at` (`created_at`)
  
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Admin activity logs table';

-- ============================================
-- CREATE VIEWS FOR ANALYTICS
-- ============================================

-- Sales summary view
CREATE OR REPLACE VIEW `sales_summary` AS
SELECT 
  DATE(o.created_at) as `sale_date`,
  COUNT(DISTINCT o.id) as `total_orders`,
  SUM(o.total_amount) as `total_revenue`,
  COUNT(DISTINCT o.user_id) as `unique_customers`
FROM `orders` o
WHERE o.order_status != 'cancelled'
GROUP BY DATE(o.created_at)
ORDER BY `sale_date` DESC;

-- Product sales view
CREATE OR REPLACE VIEW `product_sales` AS
SELECT 
  p.`id`,
  p.`name`,
  COUNT(oi.`id`) as `times_sold`,
  COALESCE(SUM(oi.`quantity`), 0) as `total_quantity`,
  COALESCE(SUM(oi.`subtotal`), 0) as `total_revenue`
FROM `products` p
LEFT JOIN `order_items` oi ON p.`id` = oi.`product_id`
GROUP BY p.`id`, p.`name`
ORDER BY `total_revenue` DESC;

-- ============================================
-- INSERT SAMPLE DATA
-- ============================================

-- Insert admin user (password: Admin@123)
INSERT INTO `users` (`username`, `email`, `password`, `role`, `status`) VALUES
('admin', 'admin@smartshop.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'active');

-- Insert sample products
INSERT INTO `products` (`name`, `category`, `price`, `description`, `stock`, `status`) VALUES

('Wireless Noise-Cancelling Headphones', 'Electronics', 89.99,
 'Premium over-ear headphones with active noise cancellation, 30-hour battery life, and foldable design. Perfect for music lovers and frequent travelers.', 50, 'active'),

('Mechanical Gaming Keyboard', 'Electronics', 59.99,
 'RGB backlit mechanical keyboard with tactile switches, anti-ghosting, and USB passthrough. Ideal for gamers and typists.', 35, 'active'),

('Ergonomic Office Chair', 'Furniture', 249.99,
 'Adjustable lumbar support, breathable mesh back, and 360° swivel for all-day comfort. Great for home and office use.', 15, 'active'),

('Stainless Steel Water Bottle', 'Kitchen', 24.99,
 'Double-wall vacuum insulated, keeps drinks cold 24h / hot 12h. BPA-free, 32 oz capacity.', 100, 'active'),

('Running Shoes – Men', 'Footwear', 74.99,
 'Lightweight mesh upper, cushioned midsole, and durable rubber outsole for road running. Available in multiple sizes.', 60, 'active'),

('Yoga Mat – Non-Slip', 'Sports', 34.99,
 'Extra-thick 6mm TPE mat with alignment lines, carrying strap, and moisture-resistant surface.', 80, 'active'),

('Smart LED Desk Lamp', 'Electronics', 39.99,
 'Touch-dimming, 3 color temperatures, USB charging port, and memory function. Energy efficient LED.', 45, 'active'),

('Leather Wallet – Slim', 'Accessories', 29.99,
 'Genuine leather bifold wallet with RFID blocking, 6 card slots, and slim profile.', 70, 'active'),

('Portable Bluetooth Speaker', 'Electronics', 49.99,
 '360° surround sound, IPX7 waterproof, 12-hour playtime, and built-in microphone. Perfect for outdoor use.', 40, 'active'),

('Coffee Maker – 12 Cup', 'Kitchen', 54.99,
 'Programmable drip coffee maker with auto-shutoff, brew-strength selector, and keep-warm plate.', 25, 'active');

-- ============================================
-- VERIFICATION QUERIES
-- ============================================

-- Verify all tables created
SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES 
WHERE TABLE_SCHEMA = 'smartshop' 
ORDER BY TABLE_NAME;

-- Verify admin user created
SELECT `id`, `username`, `email`, `role` FROM `users` WHERE `username` = 'admin';

-- Verify products inserted
SELECT COUNT(*) as `total_products` FROM `products`;

-- Display all sample products
SELECT `id`, `name`, `category`, `price`, `stock` FROM `products` ORDER BY `id`;
