<?php

namespace App; // Add namespace

use Throwable; // Import Throwable for exception handler type hint

// Ensure config is loaded (might be global or passed via DI later)
global $config;

// --- Error Handler ---
set_error_handler(function($errno, $errstr, $errfile, $errline) use ($config) {
    $logMessage = "Error [$errno]: $errstr in $errfile on line $errline";
    error_log($logMessage); // Always log

    if ($config['app']['debug'] ?? false) {
        // Display detailed error in debug mode
        echo "<div style='background-color:#f8d7da; color:#721c24; padding:10px; margin:10px 0; border:1px solid #f5c6cb; border-radius:4px;'>";
        echo "<h3>Error Occurred</h3>";
        echo "<p><strong>Type:</strong> $errno</p>";
        echo "<p><strong>Message:</strong> $errstr</p>";
        echo "<p><strong>File:</strong> $errfile</p>";
        echo "<p><strong>Line:</strong> $errline</p>";
        echo "</div>";
    } elseif (($config['app']['env'] ?? 'development') !== 'development') {
         // Potentially show a generic error message page in production, but usually handled by exception handler
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
    error_log($logMessage); // Always log

    // Clear any potentially half-rendered output
    if (ob_get_level() > 0) {
        ob_end_clean();
    }

    // Send appropriate HTTP response code
    // Check for specific exception types if needed (e.g., NotFoundException -> 404)
    if (!headers_sent()) {
         header("HTTP/1.1 500 Internal Server Error");
         header('Content-Type: text/html; charset=UTF-8');
    }

    if ($config['app']['debug'] ?? false) {
        // Display detailed exception info in debug mode
        echo "<div style='background-color:#f8d7da; color:#721c24; padding:10px; margin:10px 0; border:1px solid #f5c6cb; border-radius:4px;'>";
        echo "<h3>Uncaught Exception: " . get_class($exception) . "</h3>";
        echo "<p><strong>Message:</strong> " . htmlspecialchars($exception->getMessage()) . "</p>";
        echo "<p><strong>File:</strong> " . htmlspecialchars($exception->getFile()) . "</p>";
        echo "<p><strong>Line:</strong> " . htmlspecialchars($exception->getLine()) . "</p>";
        echo "<h4>Stack Trace:</h4><pre>" . htmlspecialchars($exception->getTraceAsString()) . "</pre>";
        echo "</div>";
    } else {
        // Show generic error page in production or non-debug environments
        // Use the View class if possible and safe
        try {
             $view = new View(); // Assumes View class is available
             // Ensure the error template path is correct
             $errorTemplate = 'errors/500.html';
             $templatePath = APP_ROOT . '/templates/' . $errorTemplate;
             if (file_exists($templatePath)) {
                 $view->output($errorTemplate);
             } else {
                 echo "<h1>Internal Server Error</h1><p>An unexpected error occurred. Please try again later.</p>";
             }
        } catch (\Throwable $e) {
             // Fallback if View rendering fails
             echo "<h1>Internal Server Error</h1><p>An critical error occurred.</p>";
             error_log("Error rendering 500 page: " . $e->getMessage());
        }
    }
    exit; // Stop execution after handling the exception
});


/**
 * Base Controller Class
 */
abstract class Controller { // Make abstract as it shouldn't be instantiated directly
    protected Database $db;
    protected View $view;
    protected ?array $user = null; // Current logged-in user data (or null)
    protected \App\Helpers\AuthHelper $auth; // Use type hint if AuthHelper is namespaced

    public function __construct() {
        $this->db = Database::getInstance();
        $this->view = new View();
        $this->auth = new \App\Helpers\AuthHelper(); // Instantiate AuthHelper

        // Load current user data if logged in
        if ($this->auth->isLoggedIn()) {
            // Assuming AuthHelper has a method to get user data
            $this->user = $this->auth->getCurrentUser();
            $this->view->set('currentUser', $this->user); // Make user data available to all views
        }
    }

    /**
     * Redirect to another URL.
     * @param string $url Relative or absolute URL.
     * @param int $statusCode HTTP status code for redirection.
     */
    protected function redirect(string $url, int $statusCode = 302): void {
        // Basic security check: prevent header injection
        $url = filter_var($url, FILTER_SANITIZE_URL);
        if (strpos($url, '/') === 0 && strpos($url, '//') !== 0) {
            // Relative URL, construct full URL
            global $config;
            $baseUrl = rtrim($config['app']['url'] ?? '', '/');
            $url = $baseUrl . $url;
        }
        header("Location: " . $url, true, $statusCode);
        exit;
    }

    /**
     * Check user permissions (Placeholder - implement actual logic).
     * @param string $permission Permission identifier.
     * @return bool
     */
    protected function hasPermission(string $permission): bool {
        if (!$this->user) {
            return false; // Not logged in
        }
        // Example: Admin has all permissions
        if ($this->user['role'] === 'admin') {
            return true;
        }
        // TODO: Implement role-based permission checks
        // $userPermissions = $this->auth->getUserPermissions($this->user['id']);
        // return in_array($permission, $userPermissions);
        return false; // Default deny
    }

     /**
     * Load a model class.
     * Example helper - consider dependency injection or service locator pattern instead.
     * @param string $modelName The name of the model (e.g., 'User')
     * @return object|null An instance of the model or null if not found
     */
    protected function loadModel(string $modelName): ?object
    {
        $modelClass = 'App\\Models\\' . ucfirst($modelName);
        if (class_exists($modelClass)) {
            // Pass database connection to the model constructor if needed
            return new $modelClass($this->db);
        }
        error_log("Model not found: " . $modelClass);
        return null;
    }
}

/**
 * View Class
 */
class View {
    private array $data = [];
    private string $basePath;

    public function __construct() {
        $this->basePath = APP_ROOT . '/templates/'; // Adjust path as needed
    }

    /**
     * Set data for the view.
     * @param string $key
     * @param mixed $value
     */
    public function set(string $key, mixed $value): void {
        $this->data[$key] = $value;
    }

    /**
     * Render a template file.
     * @param string $template Path relative to the base template directory.
     * @param array $data Additional data specific to this render call.
     * @return string Rendered content.
     * @throws \RuntimeException If template file not found.
     */
    public function render(string $template, array $data = []): string {
        $templateFile = $this->basePath . ltrim($template, '/');

        if (!file_exists($templateFile)) {
            error_log("View template not found: " . $templateFile);
            throw new \RuntimeException("View template not found: {$template}");
        }

        // Merge global data and specific data
        $viewData = array_merge($this->data, $data);

        // Make the View instance itself available within the template scope
        // This allows calling $this->includePartial('...') from within a template
        $viewData['view'] = $this;

        // Extract variables into the local scope
        extract($viewData);

        // Start output buffering
        ob_start();

        try {
            // Include the template file
            include $templateFile;
        } catch (\Throwable $e) {
            ob_end_clean();
            error_log("Error rendering template {$templateFile}: " . $e->getMessage());
            throw new \RuntimeException("Error rendering template: {$template}", 0, $e);
        }

        return ob_get_clean();
    }

    /**
     * Helper method to include partial templates from within another template.
     * Usage inside a template: <?= $view->includePartial('partials/header.html', ['pageTitle' => 'My Page']) ?>
     * @param string $partial Path relative to the base template directory.
     * @param array $data Data specific to the partial.
     * @return string Rendered partial content.
     */
    public function includePartial(string $partial, array $data = []): string {
        // Note: This simple version re-renders the partial with only the passed data.
        // It doesn't automatically inherit all data from the parent template.
        // For more complex layouts, consider a dedicated template engine (Twig, Blade).
        $partialFile = $this->basePath . ltrim($partial, '/');

        if (!file_exists($partialFile)) {
            error_log("Partial template not found: " . $partialFile);
            return "<!-- Partial Not Found: {$partial} -->";
        }

        // Extract only the data passed specifically for the partial
        extract($data);

        ob_start();
        try {
            include $partialFile;
        } catch (\Throwable $e) {
            ob_end_clean();
            error_log("Error rendering partial {$partialFile}: " . $e->getMessage());
            return "<!-- Error Rendering Partial: {$partial} -->";
        }
        return ob_get_clean();
    }

    /**
     * Output the rendered template directly.
     * @param string $template
     * @param array $data
     */
    public function output(string $template, array $data = []): void {
        try {
            echo $this->render($template, $data);
        } catch (\Throwable $e) {
            // Handle rendering errors, potentially show error page via exception handler
            // Re-throwing allows the main exception handler to catch it
            throw $e;
        }
    }

    /**
     * Output data as a JSON response.
     * @param mixed $data Data to encode.
     * @param int $statusCode HTTP status code.
     */
    public function json(mixed $data, int $statusCode = 200): void {
        if (!headers_sent()) {
            http_response_code($statusCode);
            header('Content-Type: application/json; charset=UTF-8');
        } else {
             error_log("Headers already sent, cannot set JSON content type or status code.");
        }
        echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        exit; // Stop execution after sending JSON
    }
}