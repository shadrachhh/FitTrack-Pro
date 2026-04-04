<?php

namespace App\Framework;

use RuntimeException;

class View
{
    public static function render(string $template, array $data = []): string
    {
        $viewPath = __DIR__ . '/../Views/' . $template . '.php';

        if (!file_exists($viewPath)) {
            throw new RuntimeException("View [{$template}] not found.");
        }

        extract($data, EXTR_SKIP);

        ob_start();
        require $viewPath;

        return (string) ob_get_clean();
    }
}
