<?php
declare(strict_types=1);

namespace App;

use Throwable; // Import Throwable for exception handler type hint

// Dotenv loading removed - it's handled in public/index.php

// Ensure config is loaded (might be global or passed via DI later)
global $config;
if (!isset($config) || !is_array($config)) {
    // Attempt to load config if not already available (fallback, should be loaded by index.php)
    $configFile = dirname(__DIR__) . '/config/config.php';
    if (file_exists($configFile)) {
        $config = require $configFile;
    } else {
        // Handle error: config file missing
        error_log("Critical Error: Configuration file missing at {$configFile}");
        http_response_code(500);
        echo "<h1>Internal Server Error</h1><p>Application configuration is missing.</p>";
        exit;
    }
}

// --- Error Handler ---
set_error_handler(function($errno, $errstr, $errfile, $errline) use ($config) {
    $logMessage = sprintf(
        "Error [%d]: %s in %s on line %d",
        $errno,
        $errstr,
        $errfile,
        $errline
    );
    error_log($logMessage); // Log the error

    // Check if the error level is included in error_reporting
    if (!(error_reporting() & $errno)) {
        // This error code is not included in error_reporting, so let it fall through
        // to the standard PHP error handler (respects @ suppression)
        return false;
    }

    // Display error details only if debug mode is enabled
    if (($config['app']['debug'] ?? false) && !headers_sent()) {
        echo "<div style='background-color:#f8d7da; color:#721c24; padding:10px; margin:10px 0; border:1px solid #f5c6cb; border-radius:4px;'>";
        echo "<h3>Error Occurred</h3>";
        echo "<p><strong>Type:</strong> ". htmlspecialchars((string)$errno) ."</p>"; // Cast errno to string
        echo "<p><strong>Message:</strong> ". htmlspecialchars($errstr) ."</p>";
        echo "<p><strong>File:</strong> ". htmlspecialchars($errfile) ."</p>";
        echo "<p><strong>Line:</strong> ". htmlspecialchars((string)$errline) ."</p>"; // Cast errline to string
        echo "</div>";
    }

    // Don't execute PHP internal error handler after this, unless we returned false above
    // Returning true typically stops the standard handler, false lets it continue.
    // For critical errors, PHP might terminate anyway.
    return true;
});

// --- Exception Handler ---
set_exception_handler(function(Throwable $exception) use ($config) {
    // Log the exception details
    error_log(sprintf(
        "Uncaught Exception '%s': %s in %s:%d\nStack trace:\n%s",
        get_class($exception),
        $exception->getMessage(),
        $exception->getFile(),
        $exception->getLine(),
        $exception->getTraceAsString()
    ));

    // Display a user-friendly error page or detailed info based on debug mode
    if (!headers_sent()) {
        http_response_code(500); // Set appropriate HTTP status code

        if ($config['app']['debug'] ?? false) {
            // Display detailed error information in debug mode
            echo "<div style='background-color:#f8d7da; color:#721c24; padding:10px; margin:10px 0; border:1px solid #f5c6cb; border-radius:4px;'>";
            echo "<h3>Uncaught Exception</h3>";
            echo "<p><strong>Type:</strong> ". htmlspecialchars(get_class($exception)) ."</p>";
            echo "<p><strong>Message:</strong> ". htmlspecialchars($exception->getMessage()) ."</p>";
            echo "<p><strong>File:</strong> ". htmlspecialchars($exception->getFile()) ."</p>";
            echo "<p><strong>Line:</strong> ". htmlspecialchars((string)$exception->getLine()) ."</p>";
            echo "<h4>Stack Trace:</h4><pre>" . htmlspecialchars($exception->getTraceAsString()) . "</pre>";
            echo "</div>";
        } else {
            // Show a generic error message in production
            // Consider rendering a dedicated error view file
            echo "<h1>Internal Server Error</h1>";
            echo "<p>Sorry, something went wrong. Please try again later.</p>";
            // Example: Render a view if View class is available/instantiable here
            // try {
            //     $view = new \App\View(); // Assuming View class is available
            //     echo $view->render('errors/500.php');
            // } catch (\Throwable $e) {
            //     // Fallback if view rendering fails
            //     echo "<h1>Internal Server Error</h1><p>Sorry, something went wrong.</p>";
            // }
        }
    }
    exit; // Terminate script execution after handling the exception
});

// --- Other Bootstrap Tasks ---
// Set default timezone if configured
if (!empty($config['app']['timezone'])) {
    date_default_timezone_set($config['app']['timezone']);
}

// Configure PHP settings if needed (e.g., display_errors based on debug mode)
ini_set('display_errors', ($config['app']['debug'] ?? false) ? '1' : '0');
ini_set('display_startup_errors', ($config['app']['debug'] ?? false) ? '1' : '0');
// error_reporting should be set based on environment (e.g., E_ALL for dev, less for prod)
if ($config['app']['env'] === 'development') {
    error_reporting(E_ALL);
} else {
    error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT & ~E_NOTICE); // Example for production
}

// Any other global setup...

?>