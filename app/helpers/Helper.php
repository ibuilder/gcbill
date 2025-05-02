<?php

namespace App\Helpers;

// Assuming App\View exists and has a compatible json method
use App\View;

class Helper
{
    /**
     * Sends a JSON response using the View class.
     *
     * @param View $view The View instance.
     * @param array $data Data to encode as JSON.
     * @param int $statusCode HTTP status code (default: 200).
     * @return void
     */
    public static function sendResponse(View $view, array $data, int $statusCode = 200): void
    {
        // Consider adding error handling for json encoding if needed
        $view->json($data, $statusCode);
    }

    // Add other generic, application-wide static helper methods here if needed.
}