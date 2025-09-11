<?php

namespace App\Services;

class View
{
    private static string $area = 'client';
    private static View $instance;

    public static function getInstance(): View
    {
        if (!isset(self::$instance)) {
            self::$instance = new View();
        }
        return self::$instance;
    }

    public static function setArea(string $area): void
    {
        self::$area = $area;
    }

    public static function render(string $template, array $data = []): void
    {
        $base = __DIR__ . '/../Views/' . self::$area . '/';
        $viewFile = $base . 'layouts/'. ltrim($template, '/') . '.php';
        $layout   = $base . 'layouts/main.php';

        extract($data, EXTR_SKIP);
        ob_start();
        require $viewFile;
        $content = ob_get_clean();
        require $layout;
    }

    // for assets like CSS, JS, images
    public static function asset(string $path): string
    {
        return '/' . 'public' . '/'.$path;
    }

    public static function getArea(): string
    {
        return self::$area;
    }

}