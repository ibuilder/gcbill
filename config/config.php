<?php
/**
 * Application Configuration
 *
 * Use environment variables for sensitive data.
 */

// Function to get environment variables with a default value
function env(string $key, $default = null) {
    // Check $_ENV first, then $_SERVER, then return default
    return $_ENV[$key] ?? $_SERVER[$key] ?? $default;
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
        'database' => env('DB_DATABASE', 'ibuilder_gcbill'),
        'username' => env('DB_USERNAME', 'ibuilder_gcbill'),
        'password' => env('DB_PASSWORD', 'Rockville@98765'),
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
        'name' => env('SESSION_NAME', 'GCBILL_SESSION'), // Session cookie name
        'driver' => env('SESSION_DRIVER', 'file'), // 'file', 'database', 'redis'
        'lifetime' => env('SESSION_LIFETIME', 120), // Session lifetime in minutes
        'expire_on_close' => false,
        'encrypt' => false, // Encrypt session data?
        'path' => '/', // Cookie path
        'domain' => env('SESSION_DOMAIN', null), // Cookie domain (null for current domain)
        'secure' => filter_var(env('SESSION_SECURE_COOKIE', false), FILTER_VALIDATE_BOOLEAN), // Send cookie only over HTTPS? Set to true in production
        'httponly' => true, // Prevent JavaScript access to session cookie
        'samesite' => 'Lax', // CSRF protection: 'Lax' or 'Strict'
        // For file driver:
        'files' => APP_ROOT . '/storage/sessions', // Ensure this directory exists and is writable
        // For database driver:
        // 'table' => 'sessions',
        // 'connection' => null, // Use default DB connection
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

    // Add other configuration sections as needed (e.g., mail, services)
];

// Ensure NO session_set_cookie_params() calls are here.

return $config; // Return the config array
?>