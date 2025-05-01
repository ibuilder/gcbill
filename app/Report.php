<?php

namespace App;

use Exception;

class Router {
    // Store routes: ['METHOD' => ['/path/pattern' => ['controller' => 'Name', 'action' => 'method', 'params' => ['name']]]]
    private array $routes = [];
    private string $basePath = ''; // Optional: If app is not in web root

    public function __construct(string $basePath = '') {
        $this->basePath = rtrim($basePath, '/');
    }

    /**
     * Add a route for GET requests.
     */
    public function get(string $pattern, array $handler): void {
        $this->addRoute('GET', $pattern, $handler);
    }

    /**
     * Add a route for POST requests.
     */
    public function post(string $pattern, array $handler): void {
        $this->addRoute('POST', $pattern, $handler);
    }

    /**
     * Add a route for any method (useful for PUT, DELETE etc. if needed).
     */
    public function addRoute(string $method, string $pattern, array $handler): void {
        $method = strtoupper($method);
        // Basic validation
        if (!isset($handler['controller']) || !isset($handler['action'])) {
            throw new InvalidArgumentException("Route handler must contain 'controller' and 'action'.");
        }
        $this->routes[$method][$this->basePath . $pattern] = $handler;
    }

    /**
     * Match the current request URI and method against defined routes.
     *
     * @param string $requestUri Usually $_SERVER['REQUEST_URI']
     * @param string $requestMethod Usually $_SERVER['REQUEST_METHOD']
     * @return array Matched route information ['controller', 'action', 'params']
     * @throws Exception If no route matches (consider a specific NotFoundException)
     */
    public function match(string $requestUri, string $requestMethod): array {
        $method = strtoupper($requestMethod);
        $uri = parse_url($requestUri, PHP_URL_PATH); // Get path part, ignore query string
        $uri = rtrim($uri, '/'); // Normalize trailing slash (optional)
         if (empty($uri)) $uri = '/'; // Handle root path

        if (!isset($this->routes[$method])) {
            throw new Exception("No routes defined for method {$method}", 405); // Method Not Allowed
        }

        foreach ($this->routes[$method] as $pattern => $handler) {
            // Convert pattern to regex: /users/{id:\d+} -> #^/users/(\d+)$#
            $paramNames = [];
            $regex = preg_replace_callback('/\{([a-zA-Z_][a-zA-Z0-9_-]*)(?::([^\}]+))?\}/', function ($matches) use (&$paramNames) {
                $paramNames[] = $matches[1]; // Store param name
                $constraint = $matches[2] ?? '[^/]+'; // Default constraint: match anything except slash
                return '(' . $constraint . ')';
            }, $pattern);

            $regex = '#^' . $regex . '$#';

            if (preg_match($regex, $uri, $matches)) {
                array_shift($matches); // Remove the full match
                $params = array_combine($paramNames, $matches); // Combine names with matched values

                return [
                    'controller' => $handler['controller'],
                    'action' => $handler['action'],
                    'params' => $params ?? []
                ];
            }
        }

        throw new Exception("No route found for {$method} {$uri}", 404); // Not Found
    }

    /**
     * Load routes from the configuration file.
     * Assumes routes.php returns an array or defines routes on this instance.
     */
    public function loadRoutes(string $routesFile): void
    {
         if (!file_exists($routesFile)) {
             throw new Exception("Routes file not found: {$routesFile}");
         }
         // Example: routes.php defines $router->get(...), $router->post(...)
         $router = $this; // Make $this available in the included file scope
         require $routesFile;
    }
}