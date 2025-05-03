<?php

namespace App;

use Throwable; // Import Throwable for exception handler type hint

// Example in your bootstrap file (e.g., public/index.php)
$dotenvPath = __DIR__ . '/../'; // Adjust path if needed
if (file_exists($dotenvPath . '.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable($dotenvPath);
    $dotenv->load(); // Use load() or safeLoad()
} 
// Continue with loading config.php or using default values

// Ensure config is loaded (might be global or passed via DI later)
global $config;
// --- Error Handler ---
set_error_handler(function($errno, $errstr, $errfile, $errline) use ($config) {
    $logMessage = sprintf(
        "Error [%d]: %s in %s on line %d",
        $errno,
        $errstr,
        $errfile,
        $errline
    );
    error_log($logMessage);

    if (($config['app']['debug'] ?? false) && !headers_sent()) {
        echo "<div style='background-color:#f8d7da; color:#721c24; padding:10px; margin:10px 0; border:1px solid #f5c6cb; border-radius:4px;'>";
        echo "<h3>Error Occurred</h3>";
        echo "<p><strong>Type:</strong> ". htmlspecialchars($errno) ."</p>";
        echo "<p><strong>Message:</strong> ". htmlspecialchars($errstr) ."</p>";
        echo "<p><strong>File:</strong> ". htmlspecialchars($errfile) ."</p>";
        echo "<p><strong>Line:</strong> ". htmlspecialchars($errline) ."</p>";
        echo "</div>";
    }

    // Don't exit here, let PHP continue standard handling if needed, unless it's a fatal error type
    return false; // Let PHP standard error handler run too
});

// --- Exception Handler ---
set_exception_handler(function(Throwable $exception) use ($config) {
    $logMessage = sprintf(
        "Uncaught Exception: %s\nMessage: %s\nFile: %s\nLine: %d\nTrace:\n%s",
        get_class($exception),
        $exception->getMessage(),
        $exception->getFile(),
        $exception->getLine(),
        $exception->getTraceAsString()
    );
    error_log($logMessage);

    if (ob_get_level() > 0) ob_end_clean();
        
    if (!headers_sent()) {
            header("HTTP/1.1 500 Internal Server Error");
            header('Content-Type: text/html; charset=UTF-8');
    }

    if (($config['app']['debug'] ?? false)) {
       
        if (!headers_sent())
        echo "<div style='background-color:#f8d7da; color:#721c24; padding:10px; margin:10px 0; border:1px solid #f5c6cb; border-radius:4px;'>";
        echo "<h3>Uncaught Exception: " . get_class($exception) . "</h3>";
        echo "<p><strong>Message:</strong> " . htmlspecialchars($exception->getMessage()) . "</p>";
        echo "<p><strong>File:</strong> " . htmlspecialchars($exception->getFile()) . "</p>";
        echo "<p><strong>Line:</strong> " . htmlspecialchars($exception->getLine()) . "</p>";
        echo "<h4>Stack Trace:</h4><pre>" . htmlspecialchars($exception->getTraceAsString()) . "</pre>";
        echo "</div>";
    } else {
        try {
            $view = new View();
            $errorTemplate = 'errors/500.php';
            $templatePath = APP_ROOT . '/app/views/' . $errorTemplate;
            if (file_exists($templatePath)) {
               if (!headers_sent()) {
                   // Use render method which handles output buffering and data extraction
                   echo $view->render($errorTemplate);
               }
            } else {
                // Fallback if template doesn't exist
                if (!headers_sent()) {
                    echo "Internal Server Error"; // Simple fallback message
                }
                error_log("Error template not found: " . $templatePath);
            }
        } catch (\Throwable $e) {
            error_log("Error rendering 500 page: " . $e->getMessage());
            // Final fallback
            if (!headers_sent()) {
                 echo "An critical error occurred.";
            }
        }
    }    
    exit; // Stop execution after handling the exception
});