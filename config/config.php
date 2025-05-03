<?php
/**
 * Main Configuration File
 */

// Ensure APP_ROOT is defined (usually done in index.php)
if (!defined('APP_ROOT')) {
    define('APP_ROOT', dirname(__DIR__)); // Adjust if config is moved deeper
}

// Function to get environment variables with a default value
function env(string $key, $default = null) {
    return $_ENV[$key] ?? $default;
}

$config = [
    'app' => [
        'name' => env('APP_NAME', 'GCBill'),
        'env' => env('APP_ENV', 'production'), // 'development', 'production', 'testing'
        'debug' => filter_var(env('APP_DEBUG', false), FILTER_VALIDATE_BOOLEAN), // Enable debug mode (shows detailed errors)
        'url' => env('APP_URL', 'http://localhost'), // Base URL of the application
        'timezone' => 'UTC', // Set your application's timezone
        'key' => env('APP_KEY'), // Encryption key (generate using base64_encode(random_bytes(32)))
    ],

    'db' => [
        'driver' => env('DB_CONNECTION', 'mysql'), // E.g., 'mysql', 'pgsql', 'sqlite'
        'host' => env('DB_HOST', '127.0.0.1'),
        'port' => env('DB_PORT', 3306),
        'database' => env('DB_DATABASE', 'gcbill_db'),
        'username' => env('DB_USERNAME', 'root'),
        'password' => env('DB_PASSWORD', ''),
        'charset' => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
        'prefix' => '',
        'options' => [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, // Throw exceptions on errors
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, // Fetch results as associative arrays
            PDO::ATTR_EMULATE_PREPARES => false, // Use native prepared statements
        ],
    ],

    'session' => [
        'driver' => env('SESSION_DRIVER', 'file'), // 'file', 'database', 'redis'
        'lifetime' => env('SESSION_LIFETIME', 120), // Session lifetime in minutes
        'expire_on_close' => false,
        'encrypt' => false, // Encrypt session data?
        'path' => '/', // Cookie path
        'domain' => env('SESSION_DOMAIN', null), // Cookie domain (null for current domain)
        'secure' => env('SESSION_SECURE_COOKIE', false), // Send cookie only over HTTPS? Set to true in production
        'httponly' => true, // Prevent JavaScript access to session cookie
        'samesite' => 'Lax', // CSRF protection: 'Lax' or 'Strict'
        // For file driver:
        'files' => APP_ROOT . '/storage/sessions', // Ensure this directory exists and is writable
    ],

    'view' => [
        'paths' => [
            APP_ROOT . '/app/Views' // Directory where view files are located
        ],
        'cache' => false, // Or APP_ROOT . '/storage/views' for caching (ensure writable)
    ],

    'log' => [
        'channel' => env('LOG_CHANNEL', 'single'), // 'single', 'daily', 'stderr', 'syslog'
        'path' => APP_ROOT . '/storage/logs/app.log', // Ensure directory exists and is writable
        'level' => env('LOG_LEVEL', 'debug'), // 'debug', 'info', 'notice', 'warning', 'error', 'critical', 'alert', 'emergency'
    ],

    'mail' => [
        'from_email' => env('MAIL_FROM_ADDRESS', 'no-reply@example.com'),
        'from_name' => env('MAIL_FROM_NAME', 'Construction Billing App'),
        'smtp_host' => env('MAIL_HOST', 'smtp.mailtrap.io'),
        'smtp_port' => (int)(env('MAIL_PORT', 587)),
        'smtp_secure' => env('MAIL_ENCRYPTION', 'tls'),
        'smtp_auth' => true,
        'smtp_username' => env('MAIL_USERNAME', null),
        'smtp_password' => env('MAIL_PASSWORD', null),
    ],

    'aia' => [
        'g702_template' => APP_ROOT . '/templates/aia/g702_template.html',
        'g703_template' => APP_ROOT . '/templates/aia/g703_template.html',
    ],
];

$GLOBALS['config'] = $config;
date_default_timezone_set($config['app']['timezone']);

if ($config['app']['env'] === 'development' || $config['app']['debug']) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
}

session_name($config['session']['name']);
session_set_cookie_params($config['session']['cookie_lifetime'],$config['session']['cookie_path'], $config['session']['cookie_domain'],$config['session']['cookie_secure'],$config['session']['cookie_httponly']);

// --- Session Cookie Settings ---
// Apply session cookie parameters based on config
// Note: This should ideally happen *before* session_start() in index.php if possible,
// or ensure session_start() is called after this config is loaded.
// If session_start() is already called in index.php before this, these might not take effect immediately.
session_set_cookie_params(
    $config['session']['lifetime'] * 60, // Convert minutes to seconds
    $config['session']['path'],
    $config['session']['domain'] ?? '', // Use empty string if null
    $config['session']['secure'],
    $config['session']['httponly']
);
// Note: SameSite attribute needs PHP 7.3+ and is set differently:
if (PHP_VERSION_ID >= 70300) {
    session_set_cookie_params([
        'lifetime' => $config['session']['lifetime'] * 60,
        'path' => $config['session']['path'],
        'domain' => $config['session']['domain'] ?? '',
        'secure' => $config['session']['secure'],
        'httponly' => $config['session']['httponly'],
        'samesite' => $config['session']['samesite'] // Added SameSite
    ]);
}

return $config; // Return the config array
?>