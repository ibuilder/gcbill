<?php

namespace App;

class Router
{
    private array $routes = [];

    public function loadRoutes(string $path): void
    {
        if (file_exists($path)) {
            $routes = require $path;
            foreach ($routes as $route) {
                $this->addRoute($route['method'], $route['uri'], $route['controller'], $route['action']);
            }
        }
    }

    private function addRoute(string $method, string $uri, string $controller, string $action): void
    {
        $this->routes[strtoupper($method)][$uri] = [
            'controller' => $controller,
            'action' => $action,
        ];
    }

    public function get(string $uri, string $controllerAction): void 
    {
        list($controller, $action) = explode('@', $controllerAction);
        $this->addRoute('GET', $uri, $controller, $action);
    }

    public function post(string $uri, string $controllerAction): void 
    {
        list($controller, $action) = explode('@', $controllerAction);
        $this->addRoute('POST', $uri, $controller, $action);
    }

    public function match(string $requestUri, string $method): array
    {
        $method = strtoupper($method);
        $uri = parse_url($requestUri, PHP_URL_PATH);

        if (!isset($this->routes[$method])) {
            throw new \Exception("Method {$method} not allowed", 405);
        }

        foreach ($this->routes[$method] as $routeUri => $routeData) {
            if ($routeUri === $uri) {
                    return [
                        'controller' => $routeData['controller'],
                        'action' => $routeData['action'],
                        'params' => [],
                    ];
            }

            $pattern = preg_replace('/\{([a-zA-Z]+)\}/', '(?P<$1>[^/]+)', $routeUri);
            $pattern = "#^$pattern$#";

            if (preg_match($pattern, $uri, $matches)) {
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
                    return [
                        'controller' => $routeData['controller'],
                        'action' => $routeData['action'],
                        'params' => $params,
                    ];
            }
        }
                throw new \Exception("Route not found for {$method} {$uri}", 404);
    }
}