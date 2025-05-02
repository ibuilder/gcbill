<?php

namespace App\Controllers;

use App\Database;
use App\View;
use App\Libraries\Auth; // Assuming Auth library is here
use App\Helpers\SecurityHelper; // Assuming SecurityHelper is here
use Exception;

abstract class BaseController
{
    protected Database $db;
    protected array $config;
    protected View $view;
    protected ?array $currentUser = null; // Renamed from $user for clarity
    protected Auth $auth; // Assuming Auth library instance

    // Properties to be set by router/dispatcher
    protected string $controllerName = '';
    protected string $actionName = '';

    public function __construct(Database $db, array $config = [])
    {
        $this->db = $db;
        $this->config = $config; // Store config if needed
        $this->view = new View();
        $this->auth = new Auth($this->db); // Instantiate Auth library

        // Load current user data if logged in
        if ($this->auth->isLoggedIn()) {
            $this->currentUser = $this->auth->getCurrentUser();
            $this->view->set('currentUser', $this->currentUser); // Make user data available to all views
        }

        // Pass config to all views
        $this->view->set('config', $this->config);
        // Pass flash messages to all views
        $this->view->set('flashMessages', $this->getFlashMessages());
    }

    /**
     * Sets the controller and action names, typically called by the router.
     */
    public function setRouteInfo(string $controllerName, string $actionName): void
    {
        $this->controllerName = $controllerName;
        $this->actionName = $actionName;
    }

    /**
     * Renders a view template.
     */
    protected function render(string $templatePath, array $data = []): void
    {
        // Ensure .php extension
        $templatePath = str_replace('.html', '.php', $templatePath);
        if (!str_ends_with($templatePath, '.php')) {
            $templatePath .= '.php';
        }

        // Pass common data automatically
        $data['currentUser'] = $this->currentUser;
        $data['config'] = $this->config;
        $data['flashMessages'] = $this->getFlashMessages(); // Ensure flash messages are passed

        $this->view->output($templatePath, $data);
    }

    /**
     * Redirects the user to a given URL.
     */
    protected function redirect(string $url, int $statusCode = 302): void
    {
        // Basic URL sanitization
        $url = filter_var($url, FILTER_SANITIZE_URL);

        // Handle relative URLs (assuming they start with '/')
        if (str_starts_with($url, '/') && !str_starts_with($url, '//')) {
            $baseUrl = rtrim($this->config['app']['url'] ?? '', '/');
            $url = $baseUrl . $url;
        }

        if (!headers_sent()) {
            header("Location: " . $url, true, $statusCode);
        }
        exit; // Stop script execution after redirect
    }

    /**
     * Sends a JSON response.
     */
    protected function jsonResponse(array $data, int $statusCode = 200): void
    {
        if (!headers_sent()) {
            http_response_code($statusCode);
            header('Content-Type: application/json');
        }
        echo json_encode($data);
        exit; // Stop script execution after sending JSON
    }

    /**
     * Retrieves and clears flash messages from the session.
     */
    protected function getFlashMessages(): array
    {
        $messages = [];
        if (isset($_SESSION['flash_success'])) {
            $messages['type'] = 'success';
            $messages['message'] = $_SESSION['flash_success'];
            unset($_SESSION['flash_success']);
        } elseif (isset($_SESSION['flash_error'])) {
            $messages['type'] = 'danger'; // Use Bootstrap 'danger' class
            $messages['message'] = $_SESSION['flash_error'];
            unset($_SESSION['flash_error']);
        } elseif (isset($_SESSION['flash_warning'])) {
            $messages['type'] = 'warning';
            $messages['message'] = $_SESSION['flash_warning'];
            unset($_SESSION['flash_warning']);
        } elseif (isset($_SESSION['flash_info'])) {
            $messages['type'] = 'info';
            $messages['message'] = $_SESSION['flash_info'];
            unset($_SESSION['flash_info']);
        }
        return $messages;
    }

    /**
     * Sets a flash message in the session.
     */
    protected function setFlashMessage(string $type, string $message): void
    {
        $_SESSION['flash_' . $type] = $message;
    }

    /**
     * Validates a CSRF token from POST data.
     * Redirects with error on failure.
     */
    protected function checkCsrf(string $redirectOnError): bool
    {
        $submittedToken = $_POST[SecurityHelper::getFormInputName()] ?? null;
        if (!SecurityHelper::validateToken($submittedToken)) {
            $this->setFlashMessage('error', 'Invalid request. Please try again.');
            $this->redirect($redirectOnError);
            return false; // Although redirect exits, return false for clarity
        }
        return true;
    }

     /**
     * Validates a CSRF token from POST data for AJAX requests.
     * Sends JSON error response on failure.
     */
    protected function checkCsrfAjax(): bool
    {
        $submittedToken = $_POST[SecurityHelper::getFormInputName()] ?? null;
        if (!SecurityHelper::validateToken($submittedToken)) {
            $this->jsonResponse(['success' => false, 'message' => 'Invalid request token.'], 403);
            return false; // Although jsonResponse exits, return false for clarity
        }
        return true;
    }


    /**
     * Checks if the current user has the required permission for the current action.
     * Redirects to 403 on failure.
     */
    protected function requirePermission(): bool
    {
        if (!$this->auth->isLoggedIn()) {
            $this->setFlashMessage('error', 'Please log in to access this page.');
            $this->redirect('/login');
            return false;
        }

        // Use controller/action names set by the router
        if (!$this->auth->checkPermission($this->currentUser, $this->controllerName, $this->actionName)) {
            // Log the attempt
            error_log("Permission Denied: User ID {$this->currentUser['id']} ({$this->currentUser['username']}) tried to access {$this->controllerName}::{$this->actionName}");
            // Show a generic 403 error page or redirect
            http_response_code(403);
            $this->render('errors/403'); // Assuming you have a 403 template
            exit;
        }
        return true;
    }

    /**
     * Loads a model instance.
     */
    protected function loadModel(string $modelName): ?object
    {
        $modelClass = 'App\\Models\\' . ucfirst($modelName);
        if (class_exists($modelClass)) {
            // Pass the database connection to the model constructor
            return new $modelClass($this->db);
        }
        error_log("Model not found: " . $modelClass);
        // Optionally throw an exception or return null
        // throw new Exception("Model not found: " . $modelClass);
        return null;
    }

    /**
     * Method called before any action in child controllers.
     * Can be overridden for controller-specific logic (like auth checks).
     */
    protected function before(): void
    {
        // Default: Check if user is logged in for all actions unless overridden
        if (!$this->auth->isLoggedIn()) {
             $this->setFlashMessage('error', 'Please log in to access this page.');
             $this->redirect('/login');
        }
        // Example: Call requirePermission here if all actions in extending controllers need it
        // $this->requirePermission();
    }

    /**
     * Magic method to handle calls to undefined methods (actions).
     */
    public function __call(string $method, array $args): void
    {
        error_log("Action not found: " . get_class($this) . "::" . $method);
        http_response_code(404);
        $this->render('errors/404'); // Assuming a 404 template
        exit;
    }
}
