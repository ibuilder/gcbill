<?php

namespace App\Helpers;

use App\View;

class Helper
{
    public static function sendResponse(View $view, array $data, int $statusCode): void
    {
        $view->json($data, $statusCode);
    }
}