<?php

// Load Composer Autoloader and .env file
require_once __DIR__ . '/vendor/autoload.php';
try {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->load();
    $dotenv->required(['DB_HOST', 'DB_DATABASE', 'DB_USERNAME', 'DB_PASSWORD', 'DB_PORT', 'DB_CONNECTION'])->notEmpty();
} catch (\Throwable $e) {
    echo "Error loading .env file for Phinx: " . $e->getMessage() . "\n";
    echo "Ensure your .env file exists and contains DB_HOST, DB_DATABASE, DB_USERNAME, DB_PASSWORD, DB_PORT, DB_CONNECTION.\n";
    exit(1); // Exit if DB config is missing
}

return
[
    'paths' => [
        'migrations' => __DIR__ . '/database/migrations', // Correct path
        'seeds' => __DIR__ . '/database/seeds'          // Correct path
    ],
    'environments' => [
        'default_migration_table' => 'phinxlog',
        'default_environment' => 'development', // Use 'development' or 'production' based on APP_ENV
        'development' => [ // Environment name matches APP_ENV or your choice
            'adapter' => $_ENV['DB_CONNECTION'] ?? 'mysql', // mysql, pgsql, etc.
            'host' => $_ENV['DB_HOST'],
            'name' => $_ENV['DB_DATABASE'],
            'user' => $_ENV['DB_USERNAME'],
            'pass' => $_ENV['DB_PASSWORD'],
            'port' => $_ENV['DB_PORT'],
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
        ],
        'production' => [
            'adapter' => $_ENV['DB_CONNECTION'] ?? 'mysql',
            'host' => $_ENV['DB_HOST'],
            'name' => $_ENV['DB_DATABASE'],
            'user' => $_ENV['DB_USERNAME'],
            'pass' => $_ENV['DB_PASSWORD'],
            'port' => $_ENV['DB_PORT'],
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
        ],
        // Add 'testing' environment if needed
    ],
    'version_order' => 'creation'
];