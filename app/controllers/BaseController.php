<?php

namespace App\Controllers;

use App\Database;
use App\View;
use App\Helpers\AuthHelper;

abstract class BaseController
{
    protected Database $db;
    protected array $config;
    protected View $view;
    protected ?array $user = null;
    protected AuthHelper $auth;

    public function __construct(array $config)
    {
        $this->config = $config;
        $this->db = new Database($config['db']);
        $this->view = new View();
        $this->auth = new AuthHelper();

        // Load current user data if logged in
        if ($this->auth->isLoggedIn()) {
            $this->user = $this->auth->getCurrentUser();
            $this->view->set('currentUser', $this->user); // Make user data available to all views
        }
    }

    protected function view(string $path, array $data = []): string
    {
        // Assuming your view files are in a 'templates' directory
        $fullPath =  'templates/' . $path . '.php';
        
        if (!file_exists($fullPath)) {
            throw new Exception("View file not found: " . $fullPath, 404);
        }

        // Extract data into the view scope
        extract($data);

        // Start output buffering
        ob_start();

        // Include the view file
        include $fullPath;

        // Get the content from the output buffer
        $content = ob_get_clean();

        return $content;
    }

    public function before(string $action): void
    {
        // This method will be called before every action
        // You can put common logic here (e.g., authentication)
    }

    protected function redirect(string $url, int $statusCode = 302): void
    {
        $url = filter_var($url, FILTER_SANITIZE_URL);
        if (strpos($url, '/') === 0 && strpos($url, '//') !== 0) {
            // Relative URL, construct full URL
            $baseUrl = rtrim($this->config['app']['url'] ?? '', '/');
            $url = $baseUrl . $url;
        }
        if (!headers_sent()) {
            header("Location: " . $url, true, $statusCode);
        }
        exit;
    }

    protected function hasPermission(string $permission): bool
    {
        if (!$this->user) {
            return false;
        }
        // Example: Admin has all permissions
        if ($this->user['role'] === 'admin') {
            return true;
        }
        // TODO: Implement role-based permission checks
        // $userPermissions = $this->auth->getUserPermissions($this->user[\'id\']);
        // return in_array($permission, $userPermissions);\

        return false;
    }

    protected function loadModel(string $modelName): ?object
    {
        $modelClass = 'App\\Models\\' . ucfirst($modelName);
        if (class_exists($modelClass)) {
            $dbConfig = $this->config['db'];
            
            $instance = new $modelClass(
                $dbConfig
            );
            return $instance;
        }
        error_log("Model not found: " . $modelClass);
        return null;
    }
}