<?php
/**
 * Main Configuration File
 */

// Ensure APP_ROOT is defined (usually done in index.php)
if (!defined('APP_ROOT')) {
    define('APP_ROOT', dirname(__DIR__)); // Adjust if config is moved deeper
}

$config = [
    'app' => [
        'name' => $_ENV['APP_NAME'] ?? 'Construction Billing Management System',
        'version' => '1.0.0',
        'url' => $_ENV['APP_URL'] ?? 'http://localhost',
        'env' => $_ENV['APP_ENV'] ?? 'development',
        'debug' => true,
        'timezone' => $_ENV['APP_TIMEZONE'] ?? 'America/New_York',
        'key' => $_ENV['APP_KEY'] ?? 'base64:YourSecretKeyHereGenerateOne!',
        'default_controller' => 'dashboard',
        'default_action' => 'index',
        'public_paths' => ['/login', '/auth/login', '/forgot-password', '/reset-password'],
    ],

    // Database configuration (using environment variables)
    'db' => [
        'host' => $_ENV['DB_HOST'] ?? 'localhost',
        'username' => $_ENV['DB_USERNAME'] ?? 'root',
        'password' => $_ENV['DB_PASSWORD'] ?? '',
        'database' => $_ENV['DB_DATABASE'] ?? 'construction_billing',
        'charset' => 'utf8mb4',
        'port' => $_ENV['DB_PORT'] ?? 3306,
        'driver' => $_ENV['DB_DRIVER'] ?? 'mysql',
    ],

    'session' => [
        'cookie_lifetime' => 0,
        'cookie_path' => '/',
        'cookie_domain' => null,
        'cookie_secure' => true,
        'cookie_httponly' => true,
        'use_only_cookies' => true,
        'name' => $_ENV['SESSION_COOKIE'] ?? 'construction_billing_session',
        'gc_maxlifetime' => 7200,
    ],

    'upload' => [
        'upload_path' => APP_ROOT . '/public/uploads',
    ],

    // Export configuration
    'export' => [
        // ... (keep existing pdf/excel config) ...
    ],

    // Security configuration
   'security' => [
        'password_hash_algo'    => PASSWORD_BCRYPT,
        'password_hash_options' => ['cost' => 12],
        'csrf_token_name' => '_token',
    ],

    'mail' => [
        'from_email' => $_ENV['MAIL_FROM_ADDRESS'] ?? 'no-reply@example.com',
        'from_name' => $_ENV['MAIL_FROM_NAME'] ?? 'Construction Billing App',
        'smtp_host' => $_ENV['MAIL_HOST'] ?? 'smtp.mailtrap.io',
        'smtp_port' => (int)($_ENV['MAIL_PORT'] ?? 587),
        'smtp_secure' => $_ENV['MAIL_ENCRYPTION'] ?? 'tls',
        'smtp_auth' => true,
        'smtp_username' => $_ENV['MAIL_USERNAME'] ?? null,
        'smtp_password' => $_ENV['MAIL_PASSWORD'] ?? null,
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


// --- Remove old includes/autoloading ---
// require_once APP_ROOT . '/config/database.php'; // Database class likely autoloaded
// require_once APP_ROOT . '/config/routes.php';   // Router class likely autoloaded
// require_once APP_ROOT . '/app/helpers/...'; // Helpers loaded via composer autoload:files
// spl_autoload_register(...) // Remove this, use Composer's autoloader

// --- End of File ---