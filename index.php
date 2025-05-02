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

    // --- AIA Document Routes ---

    // Route to the selection page
    // Maps GET /aia to AIADocumentController->index()
    $router->addRoute('GET', '/aia', ['App\Controllers\AIADocumentController', 'index']);

    // Route to generate the PDF for a specific application ID
    // Maps GET /aia/generate/{id} to AIADocumentController->generatePdf(id)
    // The {id:\d+} part ensures the ID is numeric
    $router->addRoute('GET', '/aia/generate/{id:\d+}', ['App\Controllers\AIADocumentController', 'generatePdf']);

    // Route to preview the HTML for a specific application ID
    // Maps GET /aia/preview/{id} to AIADocumentController->previewHtml(id)
    $router->addRoute('GET', '/aia/preview/{id:\d+}', ['App\Controllers\AIADocumentController', 'previewHtml']);

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

$httpMethod = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];

// Strip query string (?foo=bar) and decode URI
if (false !== $pos = strpos($uri, '?')) {
    $uri = substr($uri, 0, $pos);
}
$uri = rawurldecode($uri);

$routeInfo = $router->dispatch($httpMethod, $uri); // Assuming $router->dispatch exists

switch ($routeInfo[0]) {
    case Dispatcher::NOT_FOUND: // Assuming Dispatcher constants exist
        // Handle 404 Not Found
        http_response_code(404);
        ViewHelper::render('errors/404'); // Render a 404 view
        break;
    case Dispatcher::METHOD_NOT_ALLOWED:
        $allowedMethods = $routeInfo[1];
        // Handle 405 Method Not Allowed
        http_response_code(405);
        echo "Method Not Allowed"; // Or render a 405 view
        break;
    case Dispatcher::FOUND:
        $handler = $routeInfo[1];
        $vars = $routeInfo[2]; // Captured route parameters (like {id})

        $controllerName = $handler[0];
        $methodName = $handler[1];

        // Instantiate controller and call method, passing parameters
        $controller = new $controllerName();
        // Call the method, unpacking the parameters from the route
        call_user_func_array([$controller, $methodName], $vars);
        break;
}

?>