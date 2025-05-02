<?php
/**
 * Construction Billing Management System
 * Main application entry point
 */

// Define application root path
define('APP_ROOT', __DIR__);

// Load Composer Autoloader
require_once APP_ROOT . '/vendor/autoload.php';

// Load environment variables (.env file)
$dotenv = Dotenv\Dotenv::createImmutable(APP_ROOT);
$dotenv->load();

// Load configuration (adjust path if needed)
require_once APP_ROOT . '/config/config.php';

// Load application bootstrap (adjust path if needed)
require_once APP_ROOT . '/app/bootstrap.php';

// Load Database class
require_once APP_ROOT . '/app/Database.php';

// --- Authentication Check (Needs AuthHelper implementation) ---
session_start(); // Ensure session is started
$authHelper = new App\Helpers\AuthHelper(); // Assuming AuthHelper is namespaced
$requestUri = $_SERVER['REQUEST_URI'];
$publicRoutesPatterns = ['/login', '/forgot-password', '/reset-password/.*']; // Use patterns

$isPublicRoute = false;
foreach ($publicRoutesPatterns as $pattern) {
    if (preg_match('#^' . $pattern . '$#', parse_url($requestUri, PHP_URL_PATH))) {
        $isPublicRoute = true;
        break;
    }
}

if (!$authHelper->isLoggedIn() && !$isPublicRoute) {
    // Use Controller's redirect if possible, or basic header    
     if (class_exists('App\Controller')) {
         // Cannot instantiate abstract Controller directly, handle differently
         // Maybe have a static redirect helper or handle here
         header('Location: /login'); // Adjust path if needed
     } else {
         header('Location: /login'); // Adjust path if needed
     }
    exit;
}

// --- Routing ---
try {
    $router = new App\Router();
    // Load routes from config/routes.php
    $router->loadRoutes(APP_ROOT . '/config/routes.php'); // Adjust path if needed

    // --- SOV Routes (mostly AJAX) ---
    $router->get('/projects/(\d+)/sov', 'App\Controllers\SOVController@getForProject'); // Get SOV items for a project
    $router->post('/sov/store', 'App\Controllers\SOVController@store');          // Add new SOV item
    $router->post('/sov/update/(\d+)', 'App\Controllers\SOVController@update');    // Update SOV item (using POST)
    $router->post('/sov/delete/(\d+)', 'App\Controllers\SOVController@delete');    // Delete SOV item (using POST)
    
    // Match the current request
    $route = $router->match($_SERVER['REQUEST_URI'], $_SERVER['REQUEST_METHOD']);

    $controllerName = 'App\\Controllers\\' . ucfirst($route['controller']) . 'Controller';
    $actionName = $route['action'];

    if (class_exists($controllerName)) {
       
        // Create Database instance
        $db = new App\Database($config['db']);
        
        // Instantiate controller 
        $controller = new $controllerName($db);
        if (method_exists($controller, $actionName)) {
            // Call the action with named parameters from the route
            call_user_func_array([$controller, $actionName], $route['params']);
        } else {
            throw new Exception("Action {$actionName} not found in controller {$controllerName}", 404);
        }
    } else {
        throw new Exception("Controller {$controllerName} not found", 404);
    }

} catch (Exception $e) {
    // Log the error (handled by bootstrap.php exception handler)
    // The exception handler in bootstrap.php will catch this and display appropriate error
    // Re-throw to ensure it's caught by the global handler
    throw $e;
}