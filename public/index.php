<?php

declare(strict_types=1); // Enable strict types for better type safety

use App\Router;
use App\Database; // Assumes Database.php is correctly located in app/Database.php
use App\View;
use App\Helpers\AuthHelper;
use Dotenv\Dotenv;

// --- Define Root Path ---
// Define APP_ROOT as the directory *above* 'public'
define('APP_ROOT', dirname(__DIR__));

// --- Autoload Dependencies ---
require_once APP_ROOT . '/vendor/autoload.php';

// --- Load Environment Variables ---
// Load .env file from the project root
// Use safeLoad() to prevent errors if .env is missing, but log it.
try {
    $dotenv = Dotenv::createImmutable(APP_ROOT);
    $dotenv->safeLoad(); // Use safeLoad to avoid exceptions if file missing
} catch (\Throwable $e) {
    error_log("Error loading .env file: " . $e->getMessage());
    // Decide if this is critical; maybe exit or use defaults
}


// --- Load Configuration ---
// config.php should return the config array
$config = require APP_ROOT . '/config/config.php';
if (!is_array($config)) {
    // Handle error: config file didn't return an array
    error_log("Critical Error: Configuration file did not return an array.");
    http_response_code(500);
    echo "<h1>Internal Server Error</h1><p>Application configuration is invalid.</p>";
    exit;
}

// --- Configure PHP Error Reporting EARLY ---
// Set error reporting based on config BEFORE potential output from session functions
ini_set('display_errors', ($config['app']['debug'] ?? false) ? '1' : '0');
ini_set('display_startup_errors', ($config['app']['debug'] ?? false) ? '1' : '0');
if ($config['app']['env'] === 'development') {
    error_reporting(E_ALL);
} else {
    error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT & ~E_NOTICE); // Example for production
}
// Set default timezone early as well
if (!empty($config['app']['timezone'])) {
    date_default_timezone_set($config['app']['timezone']);
}


// --- Configure Session BEFORE Starting It ---
// Check if session isn't already active AND headers haven't been sent
if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
    if (isset($config['session'])) {
        // Set session name if defined in config
        if (!empty($config['session']['name'])) {
            session_name($config['session']['name']);
        }

        // Prepare cookie parameters array
        $cookieParams = [
            'lifetime' => ($config['session']['lifetime'] ?? 120) * 60, // Convert minutes to seconds, default 120 mins
            'path' => $config['session']['path'] ?? '/',
            'domain' => $config['session']['domain'] ?? '', // Use empty string if null
            'secure' => $config['session']['secure'] ?? false,
            'httponly' => $config['session']['httponly'] ?? true,
        ];

        // Add SameSite attribute if PHP version supports it
        if (PHP_VERSION_ID >= 70300 && isset($config['session']['samesite'])) {
            $cookieParams['samesite'] = $config['session']['samesite'];
        }

        // Set session cookie parameters
        session_set_cookie_params($cookieParams);
    }

    // --- Start Session ---
    session_start();

} elseif (headers_sent($file, $line)) {
    // Log if headers were already sent before session could start
    error_log("Session could not be started because headers were already sent in {$file} on line {$line}");
}


// --- Bootstrap Application (Error/Exception Handlers AFTER session start) ---
// Now safe to include Bootstrap, which might echo errors in debug mode
require_once APP_ROOT . '/app/Bootstrap.php';

// Instantiate the Database connection *once*
// Pass the database-specific configuration from the global $config
try {
    $db = new Database($config['db']); // Pass only the 'db' part of the config
} catch (\Throwable $e) {
    // Catch DB connection errors specifically if Bootstrap handler isn't sufficient
    error_log("Database connection failed: " . $e->getMessage());
    // Let the Bootstrap exception handler take over if it was set up
    if (isset($config['app']['debug']) && $config['app']['debug']) {
         echo "<h1>Database Error</h1><p>Could not connect to the database. Check logs.</p><pre>" . $e->getMessage() . "</pre>";
    } else {
         echo "<h1>Internal Server Error</h1><p>A critical error occurred. Please try again later.</p>";
    }
    exit;
}


// --- Authentication Check ---
$authHelper = new AuthHelper($db); // Instantiate AuthHelper, passing the Database instance
$requestPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?: '/';

// Define public paths (accessible without login)
$publicPaths = ['/login', '/auth/login', '/forgot-password', '/reset-password']; // Add API endpoints if needed

// Redirect to login if not logged in and accessing a protected route
if (!in_array($requestPath, $publicPaths) && !$authHelper->isLoggedIn()) {
    // Store intended URL before redirecting (optional)
    // $_SESSION['intended_url'] = $_SERVER['REQUEST_URI'];
    header('Location: /login'); // Adjust login path if necessary
    exit;
}

// --- Routing ---
$router = new Router();
$view = new View($config['view'] ?? []); // Instantiate View service, pass view config

try {
    // Load main application routes
    $router->loadRoutes(APP_ROOT . '/config/routes.php');

    // Load specific route groups if your Router supports it
    // Example: $router->loadSOVRoutes(); // Assuming this method exists and loads SOV routes

    // Get HTTP method and URI
    $httpMethod = $_SERVER['REQUEST_METHOD'];
    $uri = $requestPath; // Use the path parsed earlier

    // Dispatch the request using the Router
    // Assuming router->match returns ['controller' => ..., 'action' => ..., 'params' => [...]] or throws an exception
    $route = $router->match($uri, $httpMethod);

    // --- Controller Dispatch ---
    $controllerName = $route['controller']; // Assuming Router provides the full class name or namespace part
    $actionName = $route['action'];
    $params = $route['params'];

    if (class_exists($controllerName)) {
        // Instantiate the controller, passing dependencies (DB, config, view)
        // IMPORTANT: This assumes all controllers require $db, $config, and $view in their constructor.
        // Consider a DI container for more flexibility if dependencies vary.
        $controller = new $controllerName($db, $config, $view);

        if (method_exists($controller, $actionName)) {
            // Call the action, passing route parameters
            // Use call_user_func_array for variable number of parameters
            call_user_func_array([$controller, $actionName], $params);
        } else {
            // Action not found in the controller
            throw new RuntimeException("Action {$actionName} not found in controller {$controllerName}", 404);
        }
    } else {
        // Controller class not found
        throw new RuntimeException("Controller {$controllerName} not found", 404);
    }

} catch (App\Exceptions\RouteNotFoundException $e) { // Catch specific route not found exception from Router
    http_response_code(404);
    // Use the View instance to render the 404 page
    echo $view->render('errors/404.php', ['message' => $e->getMessage()]);
    exit;
} catch (App\Exceptions\MethodNotAllowedException $e) { // Catch specific method not allowed exception
    http_response_code(405);
    // Optionally set Allow header: header('Allow: ' . implode(', ', $e->getAllowedMethods()));
    echo $view->render('errors/405.php', ['message' => $e->getMessage()]);
    exit;
} catch (\Throwable $e) { // Catch any other throwable during dispatch/controller execution
    // The exception handler set in Bootstrap.php should catch this.
    // If it doesn't, this re-throw ensures PHP's default handler or the Bootstrap one logs/displays it.
    throw $e;
}

?>