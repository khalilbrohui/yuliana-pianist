<?php
// Start session for login state management
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Database Credentials
// Database Credentials (Auto-detected environment)
$isLocal = (isset($_SERVER['HTTP_HOST']) && (strpos($_SERVER['HTTP_HOST'], 'localhost') !== false || strpos($_SERVER['HTTP_HOST'], '127.0.0.1') !== false)) || php_sapi_name() === 'cli';

if ($isLocal) {
    define('DB_HOST', '127.0.0.1');
    define('DB_NAME', 'music_academy');
    define('DB_USER', 'root');
    define('DB_PASS', '');
} else {
    define('DB_HOST', 'sql301.infinityfree.com');
    define('DB_NAME', 'if0_42108863_music_academy');
    define('DB_USER', 'if0_42108863');
    define('DB_PASS', 'ObSsMEoJQf7w');
}

// Base URL detection
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
$host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost:8000';
define('BASE_URL', $protocol . '://' . $host);

/**
 * Returns a PDO database connection instance.
 * @return PDO
 */
function getDBConnection() {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            // If the database setup has not been run, direct to setup
            if ($e->getCode() == 1049) { // Database not found
                header("Location: " . BASE_URL . "/setup.php");
                exit;
            }
            die("Database connection failed: " . $e->getMessage());
        }
    }
    return $pdo;
}

/**
 * Clean user inputs for safety and XSS prevention
 */
function cleanInput($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

/**
 * Check if current logged-in user is an admin
 */
function isAdmin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

/**
 * Check if current logged-in user is a student
 */
function isStudent() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'student';
}
?>
