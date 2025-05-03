<?php

namespace App;

use Exception;

class View
{
    public function json(mixed $data, int $statusCode = 200): string
    {
        if (!headers_sent()) {
            http_response_code($statusCode);
            header('Content-Type: application/json; charset=UTF-8');
        } else {
            error_log("Headers already sent, cannot set JSON content type or status code.");
            return "";
        }
        
        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        if ($json === false) {
            throw new Exception("Error encoding JSON: " . json_last_error_msg());
        }
        return $json;
    }

    private array $data = [];

    public function render(string $template, array $data = []): string
    {
        // Correctly uses app/views as the base
        $templateFile = APP_ROOT . '/app/views/' . $template;
        if (!file_exists($templateFile)) {
            error_log("View template not found: " . $templateFile);
            throw new \RuntimeException("View template not found: {$template}");
        }

        $viewData = array_merge($this->data, $data);
        $viewData['view'] = $this;
        extract($viewData);
        ob_start();

        try {
            include $templateFile;
        } catch (\Throwable $e) {
            ob_end_clean();
            error_log("Error rendering template {$templateFile}: " . $e->getMessage());
            throw new \RuntimeException("Error rendering template: {$template}", 0, $e);
        }

        return ob_get_clean();
    }

        public function output(string $template, array $data = []): void
    {
        try {
            echo $this->render($template, $data);
        } catch (\Throwable $e) {
            error_log("Error rendering template {$template}: " . $e->getMessage());
        }
    }

    public function includePartial(string $partial, array $data = []): string
    {
        // Correctly uses app/views as the base
        $partialFile = APP_ROOT . '/app/views/' . $partial;
        if (!file_exists($partialFile)) {
            error_log("Partial template not found: " . $partialFile);
            return "<!-- Partial Not Found: {$partial} -->";
        }

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
    
}
