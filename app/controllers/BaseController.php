<?php

namespace App\Controllers;

use App\Database;

abstract class BaseController
{
    protected $db;

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    protected function view(string $path, array $data = []): string
    {
        // Assuming your view files are in a 'templates' directory
        $fullPath =  'templates/' . $path . '.html';
        
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
}