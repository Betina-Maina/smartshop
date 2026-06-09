<?php
/* ============================================================
   SmartShop – Database & App Configuration
   Edit DB_USER / DB_PASS to match your environment.
   XAMPP defaults: root / (empty password)
   ============================================================ */

define('DB_HOST',    '127.0.0.1');
define('DB_NAME',    'smartshop');
define('DB_USER',    'root');
define('DB_PASS',    '');
define('DB_CHARSET', 'utf8mb4');

// File upload settings
define('UPLOAD_DIR',          __DIR__ . '/../uploads/');
define('UPLOAD_URL',          '/smartshop/uploads/');
define('UPLOAD_MAX_SIZE',     2 * 1024 * 1024);          // 2 MB
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/gif', 'image/webp']);

// App base URL (no trailing slash)
define('BASE_URL', '/smartshop');
// define('UPLOAD_DIR', rtrim($_SERVER['DOCUMENT_ROOT'], '/\\') . '/smartshop/uploads/');

// -------------------------------------------------------
// PDO Connection
// -------------------------------------------------------
try {
    $dsn = sprintf('mysql:host=%s;dbname=%s;charset=%s', DB_HOST, DB_NAME, DB_CHARSET);
    // $dsn = sprintf('mysql:host=127.0.0.1;port=3309;dbname=%s;charset=%s', DB_NAME, DB_CHARSET); // change back to this for sam's localhost
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    // Log error; never expose details in production
    error_log('DB connection failed: ' . $e->getMessage());
    die('<div style="font-family:sans-serif;padding:2rem;color:#991b1b">
           <h2>Database connection failed</h2>
           <p>Please check your configuration in <code>config/config.php</code> and ensure MySQL is running.</p>
         </div>');
}

// -------------------------------------------------------
// Helper: execute a prepared statement
// Returns the PDOStatement so callers can fetch results.
// -------------------------------------------------------
function db_query(string $sql, array $params = []): PDOStatement
{
    global $pdo;
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt;
}

// -------------------------------------------------------
// Helper: upload a product image
// Returns the stored filename on success, null on failure.
// Populates $errors array on validation failure.
// -------------------------------------------------------
function handle_image_upload(array $file, array &$errors): ?string
{
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errors[] = match ($file['error']) {
            UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'Image exceeds the 2 MB upload limit.',
            UPLOAD_ERR_NO_FILE => 'No image was uploaded.',
            default => 'Image upload failed. Please try again.',
        };
        return null;
    }

    if (!is_dir(UPLOAD_DIR) && !mkdir(UPLOAD_DIR, 0755, true)) {
        $errors[] = 'Upload directory could not be created.';
        return null;
    }

    if (!is_writable(UPLOAD_DIR)) {
        $errors[] = 'Upload directory is not writable. Set ownership to the web server user (nobody on Linux LAMPP).';
        return null;
    }

    if ($file['size'] > UPLOAD_MAX_SIZE) {
        $errors[] = 'Image must be smaller than 2 MB.';
        return null;
    }

    // Use finfo for reliable MIME detection (not trusting $_FILES['type'])
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime  = $finfo->file($file['tmp_name']);
    if (!in_array($mime, ALLOWED_IMAGE_TYPES, true)) {
        $errors[] = 'Only JPEG, PNG, GIF, or WebP images are allowed.';
        return null;
    }

    $ext      = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = bin2hex(random_bytes(12)) . '.' . strtolower($ext);
    $dest     = UPLOAD_DIR . $filename;

    if (!move_uploaded_file($file['tmp_name'], $dest)) {
        $errors[] = 'Failed to save image. Check server permissions.';
        return null;
    }

    return $filename;
}

// Timezone
date_default_timezone_set('UTC');
