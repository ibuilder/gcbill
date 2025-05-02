<?php

use App\Helpers\AuthHelper;

// Define the root path of the application.
define('APP_ROOT', __DIR__);

// Autoload dependencies.
require_once APP_ROOT . '/vendor/autoload.php';

// Load environment variables from .env file.
$dotenv = Dotenv\Dotenv::createImmutable(APP_ROOT);
$dotenv->load();

// Load the application configuration.
require_once APP_ROOT . '/config/config.php';

// Load the application bootstrap.
require_once APP_ROOT . '/app/bootstrap.php';

// Load the Router class.
require_once APP_ROOT . '/app/Router.php';

// Start the session.
session_start();

// Authentication check.
$authHelper = new AuthHelper();
$requestPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// List of public paths (no authentication required).
$publicPaths = ['/login', '/forgot-password', '/reset-password'];

if (!in_array($requestPath, $publicPaths) && !$authHelper->isLoggedIn()) {
    header('Location: /login');
    exit;
}

// Routing.
try {
    $router = new App\Router();
    $router->loadRoutes(APP_ROOT . '/config/routes.php');
    
    // --- Load SOV routes (mostly AJAX) ---
    $router->loadSOVRoutes();

    // Match the current request to a defined route.
    $route = $router->match($_SERVER['REQUEST_URI'], $_SERVER['REQUEST_METHOD']);

    $controllerName = 'App\\Controllers\\' . ucfirst($route['controller']) . 'Controller';
    $actionName = $route['action'];

    if (class_exists($controllerName)) {
       
        // Create a new Database instance passing the config.
        $db = new App\Database($config['db']);
        
        // Instantiate the controller.
        $controller = new $controllerName($config);
        if (method_exists($controller, $actionName)) {
            // Call the action with named parameters.
            call_user_func_array([$controller, $actionName], $route['params']);
        } else {
            throw new Exception("Action {$actionName} not found in controller {$controllerName}", 404);
        }
    } else {
        throw new Exception("Controller {$controllerName} not found", 404);
    }
} catch (Exception $e) {
    throw $e;
}