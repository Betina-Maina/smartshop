# SmartShop – Advanced E-Commerce Management System

## Overview
SmartShop is a complete, production-ready e-commerce platform built with PHP, MySQL, Bootstrap 5, and JavaScript. It features user authentication, product management, shopping cart, checkout system, admin dashboard, and comprehensive security measures.

## 🎯 Key Features

### Core Functionality
- ✅ User registration and login with secure authentication
- ✅ Product browsing with search and category filtering
- ✅ Shopping cart management
- ✅ Secure checkout and order processing
- ✅ Order history tracking
- ✅ Admin dashboard for product management
- ✅ Responsive design (Mobile, Tablet, Desktop)
- ✅ Session management without cookies
- ✅ Role-based access control (Admin/Customer)

### Security Features
- ✅ Password hashing with bcrypt
- ✅ Prepared statements for SQL injection prevention
- ✅ XSS prevention with HTML entity encoding
- ✅ Input validation and sanitization
- ✅ Session timeout protection
- ✅ Admin activity logging
- ✅ File upload validation
- ✅ CSRF token support

## 📁 Project Structure

```
smartshop/
├── admin/                    # Admin panel files
│   ├── dashboard.php        # Admin dashboard
│   ├── products.php         # Product management
│   └── orders.php           # Order management
├── auth/                    # Authentication files
│   ├── register.php         # Registration page
│   ├── login.php            # Login page
│   ├── logout.php           # Logout handler
│   ├── register_handler.php # Registration processing
│   └── login_handler.php    # Login processing
├── ajax/                    # AJAX endpoints
│   ├── add_to_cart.php     # Add item to cart
│   └── update_cart.php     # Update cart items
├── config/                  # Configuration files
│   ├── database.php         # Database connection
│   └── session.php          # Session management
├── css/                     # Stylesheets
│   └── style.css           # Main styles
├── js/                      # JavaScript files
│   ├── validation.js        # Form validation
│   └── cart.js             # Cart functions
├── uploads/                 # Product images
├── sql/                     # Database files
│   └── smartshop.sql       # Database schema
├── includes/                # PHP includes
│   └── header.php          # Navigation header
├── index.php               # Homepage
├── products.php            # Products listing
├── product.php             # Product detail page
├── cart.php               # Shopping cart
├── checkout.php           # Checkout page
├── user_dashboard.php     # Customer dashboard
├── orders.php             # Order history
├── README.md              # This file
└── .htaccess              # Apache configuration
```

## 🛠️ Technology Stack

- **Frontend:** HTML5, CSS3, Bootstrap 5, JavaScript
- **Backend:** PHP 8.0+
- **Database:** MySQL 5.7+
- **Server:** Apache (XAMPP/WAMP compatible)
- **Libraries:** Font Awesome (icons)

## 📋 Database Schema

### users table
```sql
- id (INT, Primary Key)
- username (VARCHAR 100, Unique)
- email (VARCHAR 255, Unique)
- password (VARCHAR 255, hashed)
- role (ENUM: admin, customer)
- status (ENUM: active, inactive)
- created_at (TIMESTAMP)
- updated_at (TIMESTAMP)
```

### products table
```sql
- id (INT, Primary Key)
- name (VARCHAR 255)
- category (VARCHAR 100)
- price (DECIMAL 10,2)
- image (VARCHAR 255)
- description (LONGTEXT)
- stock (INT)
- status (ENUM: active, inactive)
- created_at (TIMESTAMP)
- updated_at (TIMESTAMP)
```

### cart table
```sql
- id (INT, Primary Key)
- user_id (INT, Foreign Key)
- product_id (INT, Foreign Key)
- quantity (INT)
- created_at (TIMESTAMP)
- updated_at (TIMESTAMP)
```

### orders table
```sql
- id (INT, Primary Key)
- user_id (INT, Foreign Key)
- total_amount (DECIMAL 10,2)
- order_status (ENUM: pending, processing, completed, cancelled)
- shipping_address (TEXT)
- payment_method (VARCHAR 50)
- created_at (TIMESTAMP)
- updated_at (TIMESTAMP)
```

### order_items table
```sql
- id (INT, Primary Key)
- order_id (INT, Foreign Key)
- product_id (INT, Foreign Key)
- quantity (INT)
- unit_price (DECIMAL 10,2)
- subtotal (DECIMAL 10,2)
- created_at (TIMESTAMP)
```

## 🚀 Installation & Setup

### 1. Prerequisites
- XAMPP or WAMP installed
- PHP 8.0 or higher
- MySQL 5.7 or higher
- Modern web browser

### 2. Download & Extract
```bash
# Extract smartshop.zip to your XAMPP htdocs folder
# Path: C:\xampp\htdocs\smartshop (Windows)
# Path: /Applications/XAMPP/htdocs/smartshop (Mac)
# Path: /opt/lampp/htdocs/smartshop (Linux)
```

### 3. Start XAMPP
- Open XAMPP Control Panel
- Start **Apache** server
- Start **MySQL** server

### 4. Create Database
- Open phpMyAdmin: http://localhost/phpmyadmin
- Create new database: `smartshop`
- Select the database
- Go to "Import" tab
- Import `sql/smartshop.sql` file
- Click "Go"

### 5. Configure Database
Edit `config/database.php`:
```php
define('DB_HOST', 'localhost');    // Your host
define('DB_USER', 'root');         // Your username
define('DB_PASS', '');             // Your password (usually empty for XAMPP)
define('DB_NAME', 'smartshop');    // Database name
```

### 6. Access Application
- Homepage: http://localhost/smartshop/index.php
- Admin: http://localhost/smartshop/admin/dashboard.php

### 7. Default Admin Account
```
Username: admin
Email: admin@smartshop.com
Password: Admin@123
```

## 🔐 Security Implementation

### Password Security
- All passwords are hashed using bcrypt (PASSWORD_BCRYPT)
- Cost factor: 10 (2^10 rounds)
- Never stored in plaintext

### SQL Injection Prevention
- All database queries use prepared statements
- Parameterized queries with bind parameters
- Input validation on all user data

### XSS Prevention
- Output encoding with htmlspecialchars()
- ENT_QUOTES flag prevents attribute injection
- UTF-8 charset specified

### Session Security
- PHP sessions only (no cookies)
- Session timeout: 1 hour (3600 seconds)
- Session ID regeneration on login
- Session validation on each request

### Input Validation
- Client-side validation (Bootstrap forms)
- Server-side validation (PHP)
- Email validation with filter_var()
- Password strength requirements
- Type casting and sanitization

### File Upload Security
- File type validation
- Size limit enforcement
- Unique filename generation
- Stored outside web root (uploads directory)

## 👥 User Roles

### Admin Role
- Access admin dashboard
- View all products
- Add/Edit/Delete products
- Upload product images
- Manage orders
- View user accounts
- Access activity logs
- View analytics

### Customer Role
- Browse products
- Search products
- Filter by category
- Add items to cart
- Manage shopping cart
- Checkout and place orders
- View order history
- Update profile

## 📝 Admin Features

### Product Management
```php
// Add Product
POST /admin/products.php
- Product name
- Category
- Price
- Description
- Image upload
- Stock quantity

// Edit Product
PUT /admin/products.php?id=X
- Update all product fields
- Replace image

// Delete Product
DELETE /admin/products.php?id=X
- Soft delete (mark as inactive)
```

### Order Management
```php
// View Orders
GET /admin/orders.php
- Filter by status
- Search by order ID
- Sort by date

// Update Order Status
POST /admin/orders.php?id=X
- Change order status
- Add notes
```

### Analytics Dashboard
```php
// Metrics
- Total sales today/month
- Total orders
- Total customers
- Top selling products
- Revenue charts
```

## 🛒 Customer Features

### Browse Products
- Grid view with product cards
- Search functionality
- Category filtering
- Product details page
- Stock availability
- Related products

### Shopping Cart
- Add items
- Update quantities
- Remove items
- Cart summary
- Subtotal calculation
- Tax computation

### Checkout
- Shipping address form
- Order review
- Payment method selection
- Order confirmation
- Email notification

### Order History
- List all orders
- Filter by status
- Download invoices
- Track shipment
- Return requests

## 🔧 Configuration Files

### config/database.php
Database connection credentials and query execution helpers with prepared statements

### config/session.php
Session initialization, timeout settings, user authentication checks, and role validation

### css/style.css
Bootstrap customizations, responsive breakpoints, animations, and dark mode support

### js/validation.js
Form validation, password strength check, notifications, and loading states

## 📱 Responsive Design

### Breakpoints
- **Desktop:** 1200px and up (4-column product grid)
- **Tablet:** 768px - 1199px (2-column product grid)
- **Mobile:** Below 768px (1-column product grid)

### Mobile Features
- Hamburger menu navigation
- Touch-friendly buttons
- Optimized touch targets
- Responsive image sizes
- Flexible layouts

## 🎨 UI/UX Design

### Color Scheme
- Primary: #007bff (Blue)
- Secondary: #6c757d (Gray)
- Success: #28a745 (Green)
- Danger: #dc3545 (Red)
- Warning: #ffc107 (Yellow)

### Typography
- Font: Segoe UI, Tahoma, Geneva, Verdana
- Headings: Bold, 700 weight
- Body: Regular, 400 weight
- Sizes: 0.875rem to 3rem

## 🚀 Deployment

### Production Checklist
- [ ] Set `session.cookie_secure = 1` for HTTPS
- [ ] Set `session.cookie_httponly = 1`
- [ ] Disable `display_errors` in php.ini
- [ ] Enable `log_errors` in php.ini
- [ ] Update database credentials
- [ ] Change default admin password
- [ ] Configure email notifications
- [ ] Set up SSL certificate
- [ ] Configure firewall rules
- [ ] Enable CORS if needed
- [ ] Set up backup schedule
- [ ] Monitor error logs

### Hosting Requirements
- PHP 8.0+ with mysqli extension
- MySQL 5.7+ database
- Apache with mod_rewrite
- SSL certificate (recommended)
- 500MB+ disk space
- Regular backups

## 📊 API Endpoints

### Authentication
- `POST /auth/register.php` - Register new user
- `POST /auth/login.php` - User login
- `GET /auth/logout.php` - User logout

### Products
- `GET /products.php` - List all products
- `GET /product.php?id=X` - Product details
- `POST /admin/products.php` - Add product (Admin)
- `PUT /admin/products.php?id=X` - Edit product (Admin)
- `DELETE /admin/products.php?id=X` - Delete product (Admin)

### Cart
- `POST /ajax/add_to_cart.php` - Add to cart
- `POST /ajax/update_cart.php` - Update cart
- `GET /cart.php` - View cart

### Orders
- `GET /orders.php` - User orders
- `POST /checkout.php` - Place order
- `GET /admin/orders.php` - All orders (Admin)

## 🐛 Troubleshooting

### Database Connection Error
```
Solution:
1. Check database credentials in config/database.php
2. Verify MySQL is running
3. Create smartshop database
4. Import sql/smartshop.sql
```

### Session Not Working
```
Solution:
1. Check session timeout in config/session.php
2. Verify php.ini session settings
3. Clear browser cookies
4. Check file permissions on session directory
```

### Image Upload Failed
```
Solution:
1. Check uploads/ folder exists
2. Verify folder permissions (755)
3. Check file size limit in php.ini
4. Verify file type is image
```

### Admin Access Denied
```
Solution:
1. Login with admin account
2. Check user role in database
3. Verify session is active
4. Clear browser cache
```