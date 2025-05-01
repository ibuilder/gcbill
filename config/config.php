<?php
/**
 * Main Configuration File
 */

// Ensure APP_ROOT is defined (usually done in index.php)
if (!defined('APP_ROOT')) {
    define('APP_ROOT', dirname(__DIR__)); // Adjust if config is moved deeper
}

// Application configuration
$config = [
    // Application settings
    'app' => [
        'name' => $_ENV['APP_NAME'] ?? 'Construction Billing Management System',
        'version' => '1.0.0',
        'url' => $_ENV['APP_URL'] ?? 'http://localhost',
        'env' => $_ENV['APP_ENV'] ?? 'development', // development, testing, production
        'debug' => filter_var($_ENV['APP_DEBUG'] ?? true, FILTER_VALIDATE_BOOLEAN),
        'timezone' => $_ENV['APP_TIMEZONE'] ?? 'America/New_York',
        'key' => $_ENV['APP_KEY'] ?? 'base64:YourSecretKeyHereGenerateOne!', // Generate a secure key
        'default_controller' => 'dashboard',
        'default_action' => 'index',
    ],

    // Database configuration (using environment variables)
    'db' => [
        'host' => $_ENV['DB_HOST'] ?? 'localhost',
        'username' => $_ENV['DB_USERNAME'] ?? 'root',
        'password' => $_ENV['DB_PASSWORD'] ?? '',
        'database' => $_ENV['DB_DATABASE'] ?? 'construction_billing',
        'charset' => 'utf8mb4',
        'port' => $_ENV['DB_PORT'] ?? 3306,
        'driver' => $_ENV['DB_DRIVER'] ?? 'mysql', // e.g., mysql, pgsql
    ],

    // Session configuration
    'session' => [
        'cookie_lifetime' => 0,
        'cookie_path' => '/',
        'cookie_domain' => $_ENV['SESSION_DOMAIN'] ?? '',
        'cookie_secure' => filter_var($_ENV['SESSION_SECURE_COOKIE'] ?? false, FILTER_VALIDATE_BOOLEAN),
        'cookie_httponly' => true,
        'use_only_cookies' => true,
        'name' => $_ENV['SESSION_COOKIE'] ?? 'construction_billing_session',
        'gc_maxlifetime' => (int)($_ENV['SESSION_LIFETIME'] ?? 86400), // 24 hours in seconds
    ],

    // File upload configuration
    'upload' => [
        'max_size' => 10485760, // 10MB
        'allowed_extensions' => [
            'jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx', 'xls', 'xlsx', 'csv'
        ],
        'upload_path' => APP_ROOT . '/public/uploads',
    ],

    // Export configuration
    'export' => [
        // ... (keep existing pdf/excel config) ...
    ],

    // Security configuration
    'security' => [
        'password_hash_algo' => PASSWORD_BCRYPT,
        'password_hash_options' => ['cost' => 12],
        'csrf_token_name' => '_token', // Common name for CSRF token
    ],

    // Email configuration (using environment variables)
    'mail' => [
        'from_email' => $_ENV['MAIL_FROM_ADDRESS'] ?? 'no-reply@example.com',
        'from_name' => $_ENV['MAIL_FROM_NAME'] ?? 'Construction Billing App',
        'smtp_host' => $_ENV['MAIL_HOST'] ?? 'smtp.mailtrap.io',
        'smtp_port' => (int)($_ENV['MAIL_PORT'] ?? 587),
        'smtp_secure' => $_ENV['MAIL_ENCRYPTION'] ?? 'tls', // tls, ssl, null
        'smtp_auth' => true, // Typically true if using username/password
        'smtp_username' => $_ENV['MAIL_USERNAME'] ?? null,
        'smtp_password' => $_ENV['MAIL_PASSWORD'] ?? null,
    ],

    // AIA Document configuration
    'aia' => [
        'g702_template' => APP_ROOT . '/templates/aia/g702_template.html', // Adjust path as needed
        'g703_template' => APP_ROOT . '/templates/aia/g703_template.html', // Adjust path as needed
    ],
];

// Make configuration globally available (Consider dependency injection instead for better practice)
$GLOBALS['config'] = $config;

// Set application timezone
date_default_timezone_set($config['app']['timezone']);

// Error reporting based on environment
if ($config['app']['env'] === 'development' || $config['app']['debug']) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
}

// Session configuration (Ensure session started *before* output)
// Consider moving session start to index.php or bootstrap.php if needed earlier
session_name($config['session']['name']);
session_set_cookie_params(
    $config['session']['cookie_lifetime'],
    $config['session']['cookie_path'],
    $config['session']['cookie_domain'],
    $config['session']['cookie_secure'],
    $config['session']['cookie_httponly']
);
// Note: session_start() might be called in index.php or bootstrap.php now

// --- Remove old includes/autoloading ---
// require_once APP_ROOT . '/config/database.php'; // Database class likely autoloaded
// require_once APP_ROOT . '/config/routes.php';   // Router class likely autoloaded
// require_once APP_ROOT . '/app/helpers/...'; // Helpers loaded via composer autoload:files
// spl_autoload_register(...) // Remove this, use Composer's autoloader

// --- End of File ---