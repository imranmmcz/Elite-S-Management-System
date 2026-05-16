<?php
declare(strict_types=1);
/**
 * Elite School Management - Application Configuration
 */

// Application settings
define('APP_NAME', 'Elite School Management');
define('APP_VERSION', '2.0.0');
define('APP_URL', 'http://localhost/elite-school-management');
define('ROOT', __DIR__ . '/..');

// Timezone
date_default_timezone_set('Asia/Dhaka');

// Error reporting (change to 0 in production)
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('log_errors', '1');
ini_set('error_log', ROOT . '/logs/php_errors.log');

// Session configuration
ini_set('session.cookie_httponly', '1');
ini_set('session.use_strict_mode', '1');
ini_set('session.cookie_samesite', 'Strict');
ini_set('session.gc_maxlifetime', '7200'); // 2 hours

// Upload limits
ini_set('upload_max_filesize', '10M');
ini_set('post_max_size', '12M');
ini_set('max_file_uploads', '10');

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
    
    // Session fixation prevention
    if (!isset($_SESSION['initiated'])) {
        session_regenerate_id(true);
        $_SESSION['initiated'] = true;
        $_SESSION['ip'] = $_SERVER['REMOTE_ADDR'] ?? '';
        $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'] ?? '';
    }
    
    // Session hijacking check
    if (isset($_SESSION['ip']) && $_SESSION['ip'] !== ($_SERVER['REMOTE_ADDR'] ?? '')) {
        session_destroy();
        session_start();
    }
}

// Autoload helpers
require_once ROOT . '/config/db.php';
require_once ROOT . '/helpers/functions.php';
require_once ROOT . '/helpers/auth_helper.php';

// Create required directories
$dirs = ['logs', 'uploads/students', 'uploads/staff', 'uploads/documents', 'uploads/certificates'];
foreach ($dirs as $dir) {
    $path = ROOT . '/' . $dir;
    if (!is_dir($path)) {
        @mkdir($path, 0755, true);
    }
}
