<?php

declare(strict_types=1); // Enable strict types for better type safety

use App\Router;
use App\Database;
use App\View;
use App\Helpers\AuthHelper;
use Dotenv\Dotenv;

// --- 0. Global Exception Handler ---
function handleException($exception) {
    error_log("Uncaught Exception: " . $exception->getMessage() . " in " . $exception->getFile() . ":" . $exception->getLine());
    http_response_code(500);
    $view = new View();
    echo $view->render('errors/500.php', ['message' => $exception->getMessage()]);
}

// Set the exception handler function
set_exception_handler('handleException');



// --- 1. Define Root Path ---
// Define APP_ROOT as the directory *above* 'public'
define('APP_ROOT', dirname(__DIR__));

// --- 2. Autoload Dependencies ---
require_once APP_ROOT . '/vendor/autoload.php';

// --- 3. Load Environment Variables ---
// Load .env file from the project root
$dotenv = Dotenv::createImmutable(APP_ROOT);
$dotenv->load();

// --- 4. Load Configuration ---
// $config should be available globally after this include
require_once APP_ROOT . '/config/config.php';

// --- 5. Bootstrap Application (Error Handling, etc.) ---
require_once APP_ROOT . '/app/Bootstrap.php';

// --- 6. Start Session ---
// Start session only if not already started (good practice)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// --- 7. Authentication Check ---
$authHelper = new AuthHelper(); // Assuming AuthHelper doesn't need DB/config yet
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

// --- 8. Routing ---
$router = new Router();
$view = new View(); // Instantiate View service

try {
    // Load main application routes
    $router->loadRoutes(APP_ROOT . '/config/routes.php');

    // Load specific route groups if your Router supports it
    // Example: $router->loadSOVRoutes(); // Assuming this method exists and loads SOV routes

    // Get HTTP method and URI
    $httpMethod = $_SERVER['REQUEST_METHOD'];
    $uri = $requestPath; // Use the path parsed earlier

    // Dispatch the request using the Router
    // Assuming router->dispatch returns ['controller' => ..., 'action' => ..., 'params' => [...]] or throws an exception
    $route = $router->match($uri, $httpMethod);

    // --- 9. Controller Dispatch ---
    $controllerName = $route['controller']; // Assuming Router provides the full class name or namespace part
    $actionName = $route['action'];
    $params = $route['params'];

    if (class_exists($controllerName)) {
        // Instantiate the Database connection *once*
        // Pass the database configuration from the global $config
        $db = new Database($config['db']);

        // Instantiate the controller, passing dependencies (DB, config, view)
        // Adjust constructor dependencies as needed for your BaseController or specific controllers
        $controller = new $controllerName($db, $config, $view);

        if (method_exists($controller, $actionName)) {
            // Call the action, passing route parameters
            // Use call_user_func_array for variable number of parameters
            call_user_func_array([$controller, $actionName], $params);
        } else {
            // Action not found in the controller
            throw new \RuntimeException("Action {$actionName} not found in controller {$controllerName}", 404);
        }
    } else {
        // Controller class not found
        throw new \RuntimeException("Controller {$controllerName} not found", 404);
    }

} catch (\App\Exceptions\RouteNotFoundException $e) { // Catch specific route not found exception from Router
    http_response_code(404);
    // Use the View instance to render the 404 page
    echo $view->render('errors/404.php', ['message' => $e->getMessage()]);
    exit;
} catch (\App\Exceptions\MethodNotAllowedException $e) { // Catch specific method not allowed exception
    http_response_code(405);
    // Optionally set Allow header: header('Allow: ' . implode(', ', $e->getAllowedMethods()));
    echo $view->render('errors/405.php', ['message' => $e->getMessage()]);
    exit;
} catch (\Throwable $e) { // Catch any other throwable during dispatch/controller execution
    // Log the detailed error (handled by Bootstrap's exception handler)
    // The Bootstrap exception handler should render the 500 page or display debug info
    // Re-throw the exception to let the handler configured in Bootstrap.php take over
    throw $e;
}

// load routes
require_once APP_ROOT . '/config/routes.php';
?>