<?php
namespace App\Core;
class View
{
    public static function render(string $view, array $data = []): string
    {
        $viewPath = __DIR__ . '/../views/' . $view . '.php';
          if (!file_exists($viewPath)) {
            throw new \Exception("View {$view} not found");
        }

        extract($data);
        ob_start();
        require $viewPath;
        return ob_get_clean();
    }
}